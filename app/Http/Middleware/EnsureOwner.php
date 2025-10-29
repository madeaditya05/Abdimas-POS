<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOwner
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->user_group !== 'owner') {
            // pilih salah satu:
            // abort(403, 'Hanya owner yang boleh mengakses.');
            return redirect()->route('dashboard')->with('error','Menu khusus owner');
        }
        return $next($request);
    }
}
