<?php

namespace App\Http\Controllers;

use App\Models\CashReconciliation;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashReconciliationController extends Controller
{
    public function show(Request $request)
    {
        $userId = (int) Auth::id();

        $last = CashReconciliation::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->first();

        $rangeStart = $last?->range_end ?? now()->startOfDay();
        $rangeEnd = now();

        // Cash dianggap "lunas" kalau bayar >= total.
        $cashQuery = Penjualan::query()
            ->where('user_id', $userId)
            ->where('tanggal', '>=', $rangeStart)
            ->where('tanggal', '<=', $rangeEnd)
            ->whereIn('metode', ['cash', 'tunai'])
            ->whereColumn('bayar', '>=', 'total');

        $appCashTotal = (float) ($cashQuery->sum('total') ?? 0);
        $cashCount = (int) ($cashQuery->count() ?? 0);

        $nonCashTotal = (float) (Penjualan::query()
            ->where('user_id', $userId)
            ->where('tanggal', '>=', $rangeStart)
            ->where('tanggal', '<=', $rangeEnd)
            ->whereNotIn('metode', ['cash', 'tunai'])
            ->sum('total') ?? 0);

        return view('auth.logout_reconcile', [
            'rangeStart'   => $rangeStart,
            'rangeEnd'     => $rangeEnd,
            'appCashTotal' => $appCashTotal,
            'cashCount'    => $cashCount,
            'nonCashTotal' => $nonCashTotal,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'physical_cash' => ['required', 'numeric', 'min:0'],
            'notes'         => ['nullable', 'string', 'max:255'],
        ]);

        $userId = (int) Auth::id();

        $last = CashReconciliation::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->first();

        $rangeStart = $last?->range_end ?? now()->startOfDay();
        $rangeEnd = now();

        $cashQuery = Penjualan::query()
            ->where('user_id', $userId)
            ->where('tanggal', '>=', $rangeStart)
            ->where('tanggal', '<=', $rangeEnd)
            ->whereIn('metode', ['cash', 'tunai'])
            ->whereColumn('bayar', '>=', 'total');

        $appCashTotal = (float) ($cashQuery->sum('total') ?? 0);
        $physical = (float) $data['physical_cash'];
        $diff = $physical - $appCashTotal;

        CashReconciliation::create([
            'user_id'        => $userId,
            'range_start'    => $rangeStart,
            'range_end'      => $rangeEnd,
            'app_cash_total' => $appCashTotal,
            'physical_cash'  => $physical,
            'difference'     => $diff,
            'notes'          => $data['notes'] ?? null,
        ]);

        // Logout setelah rekonsiliasi
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Rekonsiliasi tersimpan. Anda sudah logout.');
    }
}

