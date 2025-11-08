<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // <— penting!
use Carbon\Carbon;

class EspressoController extends Controller
{
    public function index()
    {
        $data = $this->calculateTodayShots();
        return view('landing.espresso', $data);
    }

    public function getData()
    {
        return response()->json($this->calculateTodayShots());
    }

    private function calculateTodayShots()
    {
        $capacity = 30;
        $tz = 'Asia/Jakarta';
        $today = Carbon::now($tz)->toDateString(); // 'YYYY-MM-DD'

        // --- QUERY DASAR ---
        $q = DB::table('penjualan')
            ->join('penjualan_detail', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->join('produk', 'produk.id', '=', 'penjualan_detail.produk_id')
            ->whereDate('penjualan.tanggal', $today); // filter per tanggal

        // --- JANGAN ERROR KALAU 'status' NGGAK ADA ---
        if (Schema::hasColumn('penjualan', 'status')) {
            $q->where('penjualan.status', 'paid');
        }

        // Ambil data untuk dihitung di PHP (tanpa kolom 'takaran')
        $details = $q->get([
            'penjualan_detail.qty',
            'produk.id as produk_id',
            'produk.kode_barang',
            'produk.nama_barang',
            'produk.kategori',
        ]);

        // ===== RULES/MAPPING dari config/espresso.php =====
        $byKode   = config('espresso.shots_map_by_kode', []);
        $byId     = config('espresso.shots_map_by_id', []);
        $keywords = config('espresso.keywords', []);
        $likeList = array_map('strtolower', config('espresso.espresso_like', []));

        $totalShots = 0;

        foreach ($details as $row) {
            $name = strtolower($row->nama_barang ?? '');
            $kat  = strtolower($row->kategori ?? '');
            $shotsPerUnit = 0;

            // 1) Mapping manual (paling akurat)
            if (isset($byId[$row->produk_id])) {
                $shotsPerUnit = (float) $byId[$row->produk_id];
            } elseif (!empty($row->kode_barang) && isset($byKode[$row->kode_barang])) {
                $shotsPerUnit = (float) $byKode[$row->kode_barang];
            } else {
                // 2) Kata kunci di nama produk
                foreach ($keywords as $k => $v) {
                    if (str_contains($name, $k)) { $shotsPerUnit = (float) $v; break; }
                }
                // 3) Kalau belum, produk “espresso-like” → 1 shot
                if ($shotsPerUnit === 0) {
                    foreach ($likeList as $l) {
                        if ($l !== '' && (str_contains($name, $l) || str_contains($kat, $l))) {
                            $shotsPerUnit = 1; break;
                        }
                    }
                }
            }

            $totalShots += $row->qty * $shotsPerUnit;
        }

        $fill    = min($totalShots, $capacity);
        $percent = $capacity ? (int) round(($fill / $capacity) * 100) : 0;

        return [
            'totalShots' => $totalShots,
            'fill'       => $fill,
            'percent'    => $percent,
            'isFull'     => $totalShots >= $capacity,
        ];
    }

    public function screen()
{
    // kirim nilai awal biar gak kosong saat pertama render
    $data = $this->calculateTodayShots();
    return view('landing.espresso-screen', $data);
}
}
