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
        $messages = [
            'name.required' => __('messages.contact.validation.required'),
            'email.required' => __('messages.contact.validation.required'),
            'email.email' => __('messages.contact.validation.email_invalid'),
            'phone.required' => __('messages.contact.validation.required'),
            'subject.required' => __('messages.contact.validation.required'),
            'message.required' => __('messages.contact.validation.required'),
        ];

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], $messages);

        $recipient = GeneralSetting::first()?->email ?: config('mail.from.address');

        // Queued so a slow/unavailable SMTP server can't fail the request.
        Mail::to($recipient)
            ->locale(app()->getLocale())
            ->queue(new ContactFormMail($data));

        return back()->with('success', __('messages.contact.sent'));
    }
}
