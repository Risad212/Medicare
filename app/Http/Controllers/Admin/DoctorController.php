<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\User;



class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
            $doctors = Doctor::when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('department', 'like', '%'.$request->search.'%')
                ->orWhere('specialist', 'like', '%'.$request->search.'%');
        })->paginate(10);
        $departments = Department::where('status', 1)->get();

        return view('backend.doctors.index', compact('doctors', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      $departments = Department::where('status', 1)->get();
      return view('backend.doctors.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
     {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'image'    => 'nullable|image|max:2048',
        ]);

        // Create User first
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'doctor',
        ]);

        // Doctor data
        $data            = $request->except(['image', 'password', 'email', '_token']);
        $data['slug']    = Str::slug($request->name) . '-' . uniqid();
        $data['user_id'] = $user->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('doctors', 'public');
        }

        Doctor::create($data);

        return back()->with('success', 'Doctor added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
         $doctor      = Doctor::findOrFail($id);
         $departments = Department::where('status', 1)->get();
         
        return view('backend.doctors.edit', compact('doctor', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
        {
            $doctor = Doctor::findOrFail($id);

            $request->validate([
                'name'  => 'required',
                'email' => 'required|email|unique:users,email,' . $doctor->user_id,
                'image' => 'nullable|image',
            ]);

            // Update User
            if ($doctor->user_id) {
                $userData = [
                    'name'  => $request->name,
                    'email' => $request->email,
                ];

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                User::where('id', $doctor->user_id)->update($userData);
            }

            $data         = $request->except(['image', 'password', 'email', '_token']);
            $data['slug'] = Str::slug($request->name);

            if ($request->hasFile('image')) {
                if ($doctor->image) {
                    Storage::disk('public')->delete($doctor->image);
                }
                $data['image'] = $request->file('image')->store('doctors', 'public');
            }

            $doctor->update($data);

            return back()->with('success', 'Doctor updated successfully!');
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
