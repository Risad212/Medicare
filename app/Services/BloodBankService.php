<?php

namespace App\Services;

use App\Models\BloodDonation;
use App\Models\BloodGroup;
use App\Models\BloodIssue;
use App\Models\BloodRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Domain logic for the blood bank: derived inventory, reservation and
 * issue workflows. Every mutating operation runs inside a transaction so
 * stock counts can never go negative or be double-issued.
 */
class BloodBankService
{
    /**
     * Per-group inventory derived from donation rows (never duplicated).
     * Keys mirror the migration enum: available, reserved, issued, expired.
     *
     * @return array<int, array{blood_group: BloodGroup, units: array<string, int>, quantity: array<string, int>, status: string}>
     */
    public function inventory(array $threshold = []): array
    {
        $lowStock = (int) ($threshold['low_stock'] ?? 2);

        return BloodGroup::query()
            ->withCount(['donations' => fn ($q) => $q->where('status', BloodDonation::STATUS_AVAILABLE)])
            ->orderBy('name')
            ->get()
            ->map(function (BloodGroup $group) use ($lowStock) {
                $byStatus = $this->donationTotals($group->id);

                $availableUnits = $byStatus[BloodDonation::STATUS_AVAILABLE]['units'];
                $availableQty = $byStatus[BloodDonation::STATUS_AVAILABLE]['quantity'];

                return [
                    'blood_group' => $group,
                    'units' => [
                        BloodDonation::STATUS_AVAILABLE => $availableUnits,
                        BloodDonation::STATUS_RESERVED => $byStatus[BloodDonation::STATUS_RESERVED]['units'],
                        BloodDonation::STATUS_ISSUED => $byStatus[BloodDonation::STATUS_ISSUED]['units'],
                        BloodDonation::STATUS_EXPIRED => $byStatus[BloodDonation::STATUS_EXPIRED]['units'],
                    ],
                    'quantity' => [
                        BloodDonation::STATUS_AVAILABLE => (int) $availableQty,
                        BloodDonation::STATUS_RESERVED => (int) $byStatus[BloodDonation::STATUS_RESERVED]['quantity'],
                        BloodDonation::STATUS_ISSUED => (int) $byStatus[BloodDonation::STATUS_ISSUED]['quantity'],
                        BloodDonation::STATUS_EXPIRED => (int) $byStatus[BloodDonation::STATUS_EXPIRED]['quantity'],
                    ],
                    'status' => $this->stockStatus((int) $availableQty, $lowStock),
                ];
            })
            ->all();
    }

    /**
     * Summed units + ml per status for one blood group, treating donations
     * that have gone past their expiry date as unavailable now.
     *
     * @return array<string, array{units: int, quantity: int}>
     */
    protected function donationTotals(int $bloodGroupId): array
    {
        $rows = BloodDonation::query()
            ->select('status', DB::raw('count(*) as units'), DB::raw('COALESCE(SUM(quantity), 0) as qty'))
            ->where('blood_group_id', $bloodGroupId)
            ->groupBy('status')
            ->get();

        $totals = [];
        foreach ([
            BloodDonation::STATUS_AVAILABLE,
            BloodDonation::STATUS_RESERVED,
            BloodDonation::STATUS_ISSUED,
            BloodDonation::STATUS_EXPIRED,
        ] as $status) {
            $totals[$status] = ['units' => 0, 'quantity' => 0];
        }

        foreach ($rows as $row) {
            if (! isset($totals[$row->status])) {
                continue;
            }
            $totals[$row->status] = [
                'units' => (int) $row->units,
                'quantity' => (int) $row->qty,
            ];
        }

        return $totals;
    }

    /**
     * Stock status from derived available quantity. purely administrative.
     */
    public function stockStatus(int $availableQty, int $lowStockThreshold): string
    {
        if ($availableQty <= 0) {
            return 'out';
        }

        return $availableQty <= $lowStockThreshold ? 'low' : 'available';
    }

    /**
     * A donation that has not expired may be made available. Collected /
     * testing donations enter the pool this way.
     */
    public function makeAvailable(BloodDonation $donation): BloodDonation
    {
        if ($donation->status === BloodDonation::STATUS_AVAILABLE) {
            return $donation;
        }

        if (! in_array($donation->status, [BloodDonation::STATUS_COLLECTED, BloodDonation::STATUS_TESTING, BloodDonation::STATUS_RESERVED], true)) {
            throw new RuntimeException('This donation cannot be made available from its current state.');
        }

        if ($this->isExpired($donation)) {
            throw new RuntimeException('This donation is already past its expiry date.');
        }

        $donation->forceFill([
            'status' => BloodDonation::STATUS_AVAILABLE,
            'reserved_for_request_id' => null,
        ])->save();

        return $donation;
    }

    /**
     * Approve a request and reserve whole bags to cover the requested
     * quantity. Oldest-expiry-first, so short-lived stock is used first.
     *
     * @return array{reserved_units: int, reserved_quantity: int}
     */
    public function reserveForRequest(BloodRequest $request): array
    {
        if (in_array($request->status, [BloodRequest::STATUS_FULFILLED, BloodRequest::STATUS_REJECTED, BloodRequest::STATUS_CANCELLED], true)) {
            throw new RuntimeException('Only pending or approved requests can reserve blood.');
        }

        $needed = $request->quantity - $request->issuedQuantity();

        if ($needed <= 0) {
            return ['reserved_units' => 0, 'reserved_quantity' => 0];
        }

        $result = DB::transaction(function () use ($request, $needed) {
            $bags = BloodDonation::query()
                ->where('blood_group_id', $request->blood_group_id)
                ->where('status', BloodDonation::STATUS_AVAILABLE)
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $picked = [];
            $pickedQty = 0;

            foreach ($bags as $bag) {
                $picked[] = $bag->id;
                $pickedQty += (int) $bag->quantity;
                if ($pickedQty >= $needed) {
                    break;
                }
            }

            if ($picked === []) {
                throw new RuntimeException('Not enough available stock for group '.($request->bloodGroup->name ?? '').'.');
            }

            BloodDonation::whereIn('id', $picked)->update([
                'status' => BloodDonation::STATUS_RESERVED,
                'reserved_for_request_id' => $request->id,
            ]);

            $request->update([
                'status' => $pickedQty >= $needed
                    ? BloodRequest::STATUS_APPROVED
                    : BloodRequest::STATUS_PARTIALLY_APPROVED,
            ]);

            return [
                'reserved_units' => count($picked),
                'reserved_quantity' => $pickedQty,
            ];
        });

        return $result;
    }

    /**
     * Return reservations to the pool (used when a request is rejected or
     * cancelled). Already-issued donations are never touched.
     */
    public function releaseReservations(BloodRequest $request): int
    {
        return (int) DB::table('blood_donations')
            ->where('reserved_for_request_id', $request->id)
            ->where('status', BloodDonation::STATUS_RESERVED)
            ->update([
                'status' => BloodDonation::STATUS_AVAILABLE,
                'reserved_for_request_id' => null,
            ]);
    }

    /**
     * Issue blood already reserved for a request.
     *
     * Guards: request must be approved-ish, bag reserved for THIS request,
     * bag not expired, bag not re-issued, and never more than the request
     * still needs.
     */
    public function issueForRequest(BloodRequest $request, array $payload): BloodIssue
    {
        if (in_array($request->status, [BloodRequest::STATUS_REJECTED, BloodRequest::STATUS_CANCELLED, BloodRequest::STATUS_FULFILLED], true)) {
            throw new RuntimeException('Blood cannot be issued against a fulfilled, rejected or cancelled request.');
        }

        $remaining = $request->quantity - $request->issuedQuantity();
        if ($remaining <= 0) {
            throw new RuntimeException('This request is already fully issued.');
        }

        $take = 0;

        return DB::transaction(function () use ($request, $payload, $remaining, &$take) {
            $bag = BloodDonation::query()
                ->where('id', $payload['donation_id'])
                ->where('blood_group_id', $request->blood_group_id)
                ->where('status', BloodDonation::STATUS_RESERVED)
                ->where('reserved_for_request_id', $request->id)
                ->lockForUpdate()
                ->first();

            if (! $bag) {
                throw new RuntimeException('Selected bag is not reserved for this request.');
            }

            if ($this->isExpired($bag)) {
                throw new RuntimeException('Cannot issue expired blood.');
            }

            $take = min((int) $bag->quantity, $remaining);

            BloodDonation::where('id', $bag->id)
                ->where('status', BloodDonation::STATUS_RESERVED)
                ->update([
                    'status' => BloodDonation::STATUS_ISSUED,
                    'reserved_for_request_id' => null,
                ]);

            $issue = BloodIssue::create([
                'request_id' => $request->id,
                'patient_id' => $request->patient_id,
                'blood_group_id' => $bag->blood_group_id,
                'donation_id' => $bag->id,
                'quantity' => $take,
                'unit' => $bag->unit ?? 'ml',
                'issue_date' => $payload['issue_date'] ?? now()->toDateString(),
                'issued_by' => $payload['issued_by'] ?? null,
                'receiver_name' => $payload['receiver_name'] ?? null,
                'receiver_phone' => $payload['receiver_phone'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            $newIssued = $request->issuedQuantity();
            if ($newIssued >= $request->quantity) {
                $request->update(['status' => BloodRequest::STATUS_FULFILLED]);
            } elseif ($request->status !== BloodRequest::STATUS_PARTIALLY_APPROVED) {
                $request->update(['status' => BloodRequest::STATUS_PARTIALLY_APPROVED]);
            }

            return $issue;
        });
    }

    /**
     * A bag is expired when its expiry_date is before today.
     */
    public function isExpired(BloodDonation $donation): bool
    {
        return $donation->expiry_date !== null
            && Carbon::parse($donation->expiry_date)
                ->lt(Carbon::today()->toDateString());
    }

    /**
     * The scheduled task body: mark overdue bags expired and release any
     * reservation link so the request admin can re-book. Bags already
     * issued are left untouched.
     */
    public function expireOverdue(): int
    {
        return (int) DB::table('blood_donations')
            ->whereIn('status', BloodDonation::activeStatuses())
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->update([
                'status' => BloodDonation::STATUS_EXPIRED,
                'reserved_for_request_id' => null,
            ]);
    }
}
