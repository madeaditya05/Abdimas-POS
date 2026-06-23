<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $signupCode = Setting::get('signup_code');
        if (is_null($signupCode) || $signupCode === '') {
            $signupCode = config('auth.owner_signup_code');
        }

        return view('pengaturan.index', compact('signupCode'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'signup_code' => 'nullable|string|max:255',
        ]);

        Setting::set('signup_code', $data['signup_code']);

        return back()->with('success', 'Kode proteksi berhasil disimpan.');
    }
}
