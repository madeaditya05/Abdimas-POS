<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function switch(Request $request, string $panel)
    {
        $panel = strtolower(trim($panel));
        if (!in_array($panel, ['owner', 'kasir'], true)) {
            abort(404);
        }

        $request->session()->put('panel', $panel);

        return $panel === 'kasir'
            ? redirect()->route('kasir.index')
            : redirect()->route('dashboard');
    }
}

