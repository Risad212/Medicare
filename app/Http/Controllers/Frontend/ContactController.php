<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use App\Models\SeoSetting;

class ContactController extends Controller
{
    public function index()
    {
        $seo = SeoSetting::where('page', 'contact')->first();

        return view('frontend.contact', compact('seo'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        Mail::to('hospital@gmail.com')
            ->send(new ContactFormMail($data));

        return back()->with('success', 'Message sent successfully.');
    }
}