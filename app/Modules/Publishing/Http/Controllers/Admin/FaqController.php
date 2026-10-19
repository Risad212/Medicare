<?php

namespace App\Modules\Publishing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Modules\Publishing\Http\Requests\FaqRequest;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('id')->get();

        return view('publishing.admin-faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('publishing.admin-faqs.create');
    }

    public function store(FaqRequest $request)
    {
        Faq::create($request->validated());

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ added successfully!');
    }

    public function edit(Faq $faq)
    {
        return view('publishing.admin-faqs.edit', compact('faq'));
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());

        return back()->with('success', 'FAQ updated successfully!');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('success', 'FAQ deleted successfully!');
    }
}
