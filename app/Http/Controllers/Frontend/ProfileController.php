<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Prescription;
use App\Modules\Lab\Models\LabOrder;
use App\Modules\Lab\Models\LabReport;
use App\Services\PdfService;
use App\Support\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show user profile.
     */
    public function index()
    {
        $user = auth()->user();

        $appointments = Appointment::with(['doctor', 'timeSlot'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $labOrders = collect();

        if (Module::enabled('lab')) {
            $labOrders = LabOrder::with(['items.test', 'doctor', 'reports'])
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                    // Email is self-asserted (profile email can be changed to any
                    // unique address), so only trust it once verified. Guest
                    // orders are linked by user_id at login/register via
                    // GuestRecordLinker, keeping pre-account history visible.
                    if (! empty($user->email_verified_at) && ! empty($user->email)) {
                        $query->orWhere('email', $user->email);
                    }
                })
                ->latest()
                ->get();
        }

        $prescriptions = Prescription::with(['doctor', 'items'])
            ->where(function ($query) use ($user) {
                $query->where('patient_user_id', $user->id);
                if (! empty($user->email_verified_at) && ! empty($user->email)) {
                    $query->orWhere('email', $user->email);
                }
            })
            ->latest()
            ->get();

        // The lab-order link only exists while the lab module ships.
        $invoiceWith = Module::enabled('lab') ? ['order', 'items'] : ['items'];

        $invoices = Invoice::with($invoiceWith)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                if (! empty($user->email_verified_at) && ! empty($user->email)) {
                    $query->orWhere('email', $user->email);
                }
            })
            ->latest()
            ->get();

        return view(
            'frontend.profile.index',
            compact('user', 'appointments', 'labOrders', 'prescriptions', 'invoices')
        );
    }

    /**
     * Update user profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {

            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $validated['profile_image'] = $request
                ->file('profile_image')
                ->store('profile-images', 'public');
        }

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Download a lab report that belongs to the current patient.
     */
    public function downloadReport(LabReport $report)
    {
        abort_if(! Module::enabled('lab'), 404);

        $user = Auth::user();

        $emailTrusted = ! empty($user->email_verified_at) && ! empty($user->email);
        $ownsOrder = $report->order
            && ($report->order->user_id === $user->id || ($emailTrusted && $report->order->email === $user->email));

        abort_unless($ownsOrder, 403, 'You do not have access to this report.');
        abort_if(! Storage::disk('public')->exists($report->file_path), 404, 'Report file not found.');

        return Storage::disk('public')->download($report->file_path);
    }

    /**
     * Stream a printable PDF report sheet for one of the patient's lab orders.
     */
    public function downloadOrderPdf(LabOrder $order, PdfService $pdf)
    {
        abort_if(! Module::enabled('lab'), 404);

        $user = Auth::user();

        $emailTrusted = ! empty($user->email_verified_at) && ! empty($user->email);
        $ownsOrder = $order->user_id === $user->id || ($emailTrusted && $order->email === $user->email);

        abort_unless($ownsOrder, 403, 'You do not have access to this report.');

        $order->load(['items.test', 'doctor', 'reports']);

        return $pdf->stream('lab.order-result-pdf', ['order' => $order], 'lab-order-'.$order->id.'-report.pdf');
    }

    /**
     * Stream a printable PDF of one of the patient's prescriptions.
     */
    public function downloadPrescriptionPdf(Prescription $prescription, PdfService $pdf)
    {
        $user = Auth::user();

        $emailTrusted = ! empty($user->email_verified_at) && ! empty($user->email);
        $ownsPrescription = $prescription->patient_user_id === $user->id
            || ($emailTrusted && $prescription->email === $user->email);

        abort_unless($ownsPrescription, 403, 'You do not have access to this prescription.');

        $prescription->load(['items', 'doctor', 'appointment.timeSlot']);

        return $pdf->stream('pdf.prescription', ['prescription' => $prescription], 'prescription-'.$prescription->id.'.pdf');
    }
}
