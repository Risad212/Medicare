<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use App\Models\GeneralSetting;
use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $recipient = GeneralSetting::first()?->email ?: config('mail.from.address');

        Mail::to($recipient)
            ->send(new ContactFormMail($data));

        return back()->with('success', 'Message sent successfully.');
    }
}
