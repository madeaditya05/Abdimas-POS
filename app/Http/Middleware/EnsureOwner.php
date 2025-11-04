<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();  // aman setelah check()

        if ($user->user_group !== 'owner') {
            return redirect()->route('dashboard')->with('error', 'Menu khusus owner');
            // atau: abort(403, 'Hanya owner yang boleh mengakses.');
        }

        return $next($request);
    }
}
