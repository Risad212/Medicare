<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeSetting;
use App\Models\SeoSetting;

class HomeSettingController extends Controller
{
    /**
     * Home Setting Form UI Show
     *
     * @return void
     */
    public function home()
    {
        $homeSetting = HomeSetting::first();
        $seo = SeoSetting::where('page', 'home')->first();

       return view('backend.settings.home', compact(
            'homeSetting',
            'seo'
        ));
    }

    /**
     * Home Setting Data Save In DB
     *
     * @return void
     */
    public function update(Request $request)
    {
       $data = $request->except(['_token']);

        if($request->hasFile('about_image_one')){
            $data['about_image_one'] = $request->file('about_image_one')->store('home','public');
        }

        if($request->hasFile('about_image_two')){
            $data['about_image_two'] = $request->file('about_image_two')->store('home','public');
        }

        if($request->hasFile('about_image_three')){
            $data['about_image_three'] = $request->file('about_image_three')->store('home','public');
        }

        $setting = HomeSetting::first();

        if ($setting) {
            $setting->update($data);
        } else {
            HomeSetting::create($data);
        }

        return back();
    }
}