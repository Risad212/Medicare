<?php

namespace App\Http\Controllers;

use App\Models\PageSection;
use Illuminate\Http\Request;

class PageSectionController extends Controller
{
    public function index()
    {
        return PageSection::all();
    }

    public function store(Request $request)
    {
         $content = $request->content;

        if ($request->hasFile('left_top_image')) {
            $content['left_top_image'] = $request->file('left_top_image')->store('sections');
        }

        if ($request->hasFile('left_bottom_image')) {
            $content['left_bottom_image'] = $request->file('left_bottom_image')->store('sections');
        }

        if ($request->hasFile('right_image')) {
            $content['right_image'] = $request->file('right_image')->store('sections');
        }

        PageSection::create([
            'content' => $content,
            'status' => 1
        ]);

        return back();
    }

    public function show($id)
    {
        return PageSection::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $section = PageSection::findOrFail($id);
        $section->update($request->all());

        return $section;
    }

    public function destroy($id)
    {
        return PageSection::destroy($id);
    }
}
