<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::latest()->get();
        return view('backend.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('backend.sliders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'bg_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['title', 'description', 'button_text']);

        if ($request->hasFile('bg_image')) {
            $data['bg_image'] = $request->file('bg_image')->store('sliders', 'public');
        }

        Slider::create($data);

        return back()->with('success', 'Slider added successfully!');
    }

    public function edit(Slider $slider)
    {
        return view('backend.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'bg_image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['title', 'description', 'button_text']);

        if ($request->hasFile('bg_image')) {
            if ($slider->bg_image) {
                Storage::delete('public/' . $slider->bg_image);
            }
            $data['bg_image'] = $request->file('bg_image')->store('sliders', 'public');
        }

        $slider->update($data);

        return redirect()->route('admin.sliders.index')
                          ->with('success', 'Slider updated successfully!');
    }

    public function destroy(Slider $slider)
    {
        if ($slider->bg_image) {
            Storage::delete('public/' . $slider->bg_image);
        }
        $slider->delete();

        return redirect()->route('admin.sliders.index')
                          ->with('success', 'Slider deleted successfully!');
    }
}