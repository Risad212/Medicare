<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Prescription;
use App\Models\User;
use App\Modules\Lab\Models\LabOrder;
use App\Support\Module;
use Illuminate\Support\Facades\DB;

class GuestRecordLinker
{
    /**
     * Backfill guest bookings (user_id=null) to a user matched by email.
     * Called after register / login / Google link so guests who later
     * create an account see their appointment history.
     *
     * Emails are compared case-insensitively and trimmed on both sides so
     * "Test@X.com " still links to "test@x.com".
     */
    public static function link(User $user): void
    {
        // Only a verified mailbox owner may claim guest records that were
        // booked with this address. An unverified email is self-asserted
        // and must not silently take over another patient's history.
        if (empty($user->email_verified_at)) {
            return;
        }

        $email = strtolower(trim((string) $user->email));

        if ($email === '') {
            return;
        }

        DB::transaction(function () use ($user, $email) {
            Appointment::whereNull('user_id')
                ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                ->update(['user_id' => $user->id]);

            if (Module::enabled('pharmacy')) {
                Prescription::whereNull('patient_user_id')
                    ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                    ->update(['patient_user_id' => $user->id]);
            }

            Invoice::whereNull('user_id')
                ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                ->update(['user_id' => $user->id]);

            if (Module::enabled('lab')) {
                LabOrder::whereNull('user_id')
                    ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                    ->update(['user_id' => $user->id]);
            }
        });
    }
}
