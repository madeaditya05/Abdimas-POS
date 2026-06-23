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

        $discountMinTransactions = Setting::get('discount_min_transactions', '10');
        $discountPercent = Setting::get('discount_percent', '0');

        return view('pengaturan.index', compact('signupCode', 'discountMinTransactions', 'discountPercent'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'signup_code' => 'nullable|string|max:255',
            'discount_min_transactions' => 'required|integer|min:1|max:100000',
            'discount_percent' => 'required|numeric|min:0|max:99.99',
        ]);

        Setting::set('signup_code', $data['signup_code']);
        Setting::set('discount_min_transactions', (string)$data['discount_min_transactions']);
        Setting::set('discount_percent', (string)$data['discount_percent']);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
