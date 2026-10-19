<?php

namespace App\Modules\Publishing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Modules\Publishing\Http\Requests\GalleryImageRequest;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::orderBy('sort_order')->orderBy('id')->get();

        return view('publishing.admin-gallery.index', compact('images'));
    }

    public function create()
    {
        return view('publishing.admin-gallery.create');
    }

    public function store(GalleryImageRequest $request)
    {
        $data = $request->validated();

        $data['image'] = $request->file('image')->store('gallery-images', 'public');

        GalleryImage::create($data);

        return redirect()->route('admin.gallery.index')->with('success', 'Photo added successfully!');
    }

    public function edit(GalleryImage $gallery)
    {
        return view('publishing.admin-gallery.edit', compact('gallery'));
    }

    public function update(GalleryImageRequest $request, GalleryImage $gallery)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $oldPath = $gallery->image;

            $data['image'] = $request->file('image')->store('gallery-images', 'public');

            // Only remove the previous file once the replacement is stored,
            // and only if it still exists on disk.
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        } else {
            unset($data['image']);
        }

        $gallery->update($data);

        return back()->with('success', 'Photo updated successfully!');
    }

    public function destroy(GalleryImage $gallery)
    {
        if ($gallery->image && Storage::disk('public')->exists($gallery->image)) {
            Storage::disk('public')->delete($gallery->image);
        }

        $gallery->delete();

        return back()->with('success', 'Photo deleted successfully!');
    }
}
