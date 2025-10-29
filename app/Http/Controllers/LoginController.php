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

        if (Auth::attempt(['email'=>$cred['email'], 'password'=>$cred['password']], $r->boolean('remember'))) {
            $r->session()->regenerate();
            return $this->redirectBasedOnUserGroup(Auth::user());
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
            'user_group'   => 'required|in:owner,kasir',
            'owner_token'  => 'nullable|required_if:user_group,owner',
        ], [
            'owner_token.required_if' => 'Kode Owner wajib diisi jika memilih Owner.',
        ]);

        if ($r->user_group === 'owner') {
            $expected = config('auth.owner_signup_code'); // dari .env OWNER_SIGNUP_CODE
            if (!$expected || $r->owner_token !== $expected) {
                return back()->withErrors(['owner_token'=>'Kode Owner tidak valid.'])->withInput();
            }
        }

        User::create([
            'name'       => $r->name,
            'email'      => $r->email,
            'password'   => Hash::make($r->password),
            'user_group' => $r->user_group,
        ]);

        // JANGAN Auth::login($user) —> balik ke halaman login
        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.')
            ->with('prefill_email', $r->email);
    }

    // === Redirect sesuai group ===
    private function redirectBasedOnUserGroup($user)
    {
        // Pakai intended supaya kalau user akses URL tertentu lalu login, tetap balik ke sana.
        return match ($user->user_group) {
            'owner', 'admin'   => redirect()->intended(route('dashboard_admin')), // atau route('dashboard_admin')
            'kasir'            => redirect()->intended(route('dashboard')),
            default            => redirect()->intended(route('dashboard')),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
