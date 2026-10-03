<?php

namespace App\Modules\Vaccination\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class VaccinationController extends Controller
{
    /**
     * Patient timeline: own account records + children's (linked by user_id).
     */
    public function mine()
    {
        $user = auth()->user();

        $vaccinations = $user->vaccinations()->latest()->get();

        return view('vaccinations.frontend', compact('vaccinations'));
    }
}
