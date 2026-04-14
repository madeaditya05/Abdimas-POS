<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('tampilan.login');
    }

    public function login(Request $r)
    {
        $cred = $r->validate([
            'email'    => ['required','email','max:255'],
            'password' => ['required','string'],
        ]);

        if (Auth::attempt(['email' => $cred['email'], 'password' => $cred['password']], $r->boolean('remember'))) {
            $r->session()->regenerate();

            // 🔁 Semua user diarahkan ke dashboard
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Email atau password tidak cocok.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('tampilan.register');
    }

    public function register(Request $r)
    {
        $r->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email',
            'password'     => 'required|string|min:6|confirmed',
            'owner_token'  => 'required|string',
        ], [
            'owner_token.required' => 'Kode Owner wajib diisi.',
        ]);

        $expected = config('auth.owner_signup_code'); // dari .env OWNER_SIGNUP_CODE
        if (!$expected || $r->owner_token !== $expected) {
            return back()->withErrors(['owner_token' => 'Kode Owner tidak valid.'])->withInput();
        }

        User::create([
            'name'       => $r->name,
            'email'      => $r->email,
            'password'   => Hash::make($r->password),
            'user_group' => 'owner',
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.')
            ->with('prefill_email', $r->email);
    }

    public function logout(Request $request)
    {
        // Wajib rekonsiliasi kas sebelum logout.
        return redirect()->route('logout.reconcile');
    }
}
