<?php

namespace App\Modules\Ambulance\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Http\Requests\StoreAmbulanceRequest;
use App\Modules\Ambulance\Services\AmbulanceService;

class AmbulanceController extends Controller
{
    public function index()
    {
        return view('ambulance.frontend');
    }

    public function store(StoreAmbulanceRequest $request, AmbulanceService $service)
    {
        $service->create($request->validated());

        return back()->with('success', 'Ambulance request received. Our team will call you back immediately.');
    }
}
