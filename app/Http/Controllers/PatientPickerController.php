<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Typeahead source for patient <select> pickers.
 *
 * The Beds dashboard and the Vaccination create/edit forms render patient
 * option lists inside POST forms, so they cannot carry their own GET search
 * form. These endpoints back a small ajax filter instead, and are bounded
 * (limit 25) so a large patient table never has to be prefetched.
 */
class PatientPickerController extends Controller
{
    /**
     * Patients as users (role = patient) — used by Beds and Vaccination.
     */
    public function users(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $query = User::where('role', 'patient')
            ->orderBy('name')
            ->limit(25);

        if ($search !== '') {
            $search = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($w) use ($search) {
                $w->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        return response()->json([
            'data' => $query->get(['id', 'name'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name.' (#'.$user->id.')',
                ])
                ->values(),
        ]);
    }

    /**
     * Patient register entries (App\Models\Patient) — used by Vaccination
     * for children without a login account.
     */
    public function records(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $query = Patient::orderBy('name')->limit(25);

        if ($search !== '') {
            $search = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where('name', 'like', '%'.$search.'%');
        }

        return response()->json([
            'data' => $query->get(['id', 'name'])
                ->map(fn (Patient $patient) => [
                    'id' => $patient->id,
                    'name' => $patient->name.' (#'.$patient->id.')',
                ])
                ->values(),
        ]);
    }
}
