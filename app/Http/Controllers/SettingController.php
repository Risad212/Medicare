<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralSetting;

class SettingController extends Controller
{
    /**
     * General UI Show From Settings
     *
     * @return void
     */
    public function general()
    {
        $setting = GeneralSetting::first();
        return view('backend.settings.general', compact('setting'));
    }


    /**
     * General Data Save In DB
     *
     * @return void
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        if ($request->hasFile('footer_logo')) {
            $data['footer_logo'] = $request->file('footer_logo')->store('settings', 'public');
        }

        $setting = GeneralSetting::first();

        if ($setting) {
            $setting->update($data);
        } else {
            GeneralSetting::create($data);
        }

        return back();
    }
}