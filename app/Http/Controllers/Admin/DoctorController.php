<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $doctors = Doctor::all();

      return view('backend.doctors.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      return view('backend.doctors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          $request->validate([
            'name'  => 'required',
            'image' => 'nullable|image',
          ]);

        $data = $request->except('image');

        // create slug
        $data['slug'] = Str::slug($request->name);

        // upload image
        if($request->hasFile('image')){
            $data['image'] = $request->file('image')
            ->store('doctors','public');

        }

        Doctor::create($data);

        return back()->with('success', 'Doctor added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
         $doctor = Doctor::findOrFail($id);

        return view('backend.doctors.edit', compact('doctor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $doctor = Doctor::findOrFail($id);


        $request->validate([
            'name'  => 'required',
            'image' => 'nullable|image',
        ]);


        $data = $request->except('image');


        $data['slug'] = Str::slug($request->name);

        if($request->hasFile('image')){

            if($doctor->image){
                Storage::disk('public')->delete($doctor->image);

            }

            $data['image'] = $request->file('image')
                                    ->store('doctors','public');
        }

        $doctor->update($data);

        return redirect()
                ->route('admin.doctors.index')
                ->with('success','Doctor updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctor = Doctor::findOrFail($id);

        if ($doctor->image) {
            Storage::delete('public/' . $doctor->image);
        }

        $doctor->delete();

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'Doctor deleted successfully!');
    }
}
