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
        $data = $r->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email',
            'password'     => 'required|string|min:6|confirmed',
            'owner_token'  => 'required|string',
        ], [
            'owner_token.required' => 'Kode proteksi wajib diisi.',
        ]);

        $userGroup = $this->resolveSignupUserGroup($data['owner_token']);
        if (! $userGroup) {
            return back()
                ->withErrors(['owner_token' => 'Kode proteksi tidak valid.'])
                ->withInput($r->except('password', 'password_confirmation', 'owner_token'));
        }

        User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'user_group' => $userGroup,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.')
            ->with('prefill_email', $data['email']);
    }

    private function resolveSignupUserGroup(string $token): ?string
    {
        $codes = [
            'owner' => config('auth.owner_signup_code'),
            'kasir' => config('auth.kasir_signup_code'),
        ];

        foreach ($codes as $group => $expected) {
            if (is_string($expected) && $expected !== '' && hash_equals($expected, $token)) {
                return $group;
            }
        }

        return null;
    }

    public function logout(Request $request)
    {
        // Wajib rekonsiliasi kas sebelum logout.
        return redirect()->route('logout.reconcile');
    }
}
