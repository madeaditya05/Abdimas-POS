<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Http\Requests\StoreBahanBakuRequest;
use App\Http\Requests\UpdateBahanBakuRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BahanBakuController extends Controller
{
    /** Kumpulan opsi select (nyamain dengan Filament) */
    private function opsi(): array
    {
        return [
            'opsiKategori'     => ['kopi'=>'Kopi','susu'=>'Susu','bumbu'=>'Bumbu','kemasan'=>'Kemasan','lainnya'=>'Lainnya'],
            'opsiSatuan'       => ['pcs'=>'pcs','gram'=>'gram','kg'=>'kg','ml'=>'ml','liter'=>'liter','pack'=>'pack','box'=>'box'],
            'opsiPenyimpanan'  => ['room'=>'Ruang (room)','chiller'=>'Chiller','freezer'=>'Freezer'],
            'opsiStatusHalal'  => ['halal'=>'Halal','non_halal'=>'Non-Halal','unknown'=>'Tidak diketahui'],
            'opsiAlergen'      => ['none'=>'Tidak ada','gluten'=>'Gluten','dairy'=>'Susu','nut'=>'Kacang','soy'=>'Kedelai','egg'=>'Telur'],
            'opsiLeadTime'     => [0=>0,1=>1,2=>2,3=>3,5=>5,7=>7,10=>10,14=>14,21=>21,30=>30],
            'opsiMinOrder'     => [1=>1,5=>5,10=>10,20=>20,50=>50,100=>100,200=>200,500=>500],
            'opsiYield'        => [50=>'50 %',60=>'60 %',70=>'70 %',80=>'80 %',85=>'85 %',90=>'90 %',95=>'95 %',100=>'100 %'],
        ];
    }

    public function index(Request $req)
    {
        $q = BahanBaku::query();

        // search
        if ($s = $req->input('q')) {
            $q->where(function($x) use ($s) {
                $x->where('kode_bahan','like',"%$s%")
                  ->orWhere('nama_bahan','like',"%$s%");
            });
        }

        // filter kategori & aktif
        if ($kat = $req->input('kategori')) {
            $q->where('kategori', $kat);
        }
        if ($req->filled('aktif')) {
            $q->where('aktif', (bool) $req->boolean('aktif'));
        }

        // sort
        $allowedSort = ['kode_bahan','nama_bahan','kategori','satuan_beli','satuan_pakai','created_at'];

        // default lama: urut nama_bahan
        // $sort = in_array($req->input('sort'), $allowedSort) ? $req->input('sort') : 'nama_bahan';

        // default baru: urut kode_bahan (BHK0001, BHK0002, ...)
        $defaultSort = 'kode_bahan';
        $sortInput   = $req->input('sort');
        $sort        = in_array($sortInput, $allowedSort) ? $sortInput : $defaultSort;

        $dir  = $req->input('dir') === 'desc' ? 'desc' : 'asc';

        $items = $q->orderBy($sort, $dir)
            ->with(['pembelianDetails' => fn($qq) => $qq->latest('created_at')->limit(1)])
            ->paginate(10)->withQueryString();

        return view('bahan-baku.index', [
            'items'            => $items,
            'search'           => $req->input('q'),
            'selectedKategori' => $req->input('kategori'),
            'selectedAktif'    => $req->input('aktif'),
            'sort'             => $sort,
            'dir'              => $dir,
            'opsiKategori'     => array_keys($this->opsi()['opsiKategori']),
        ]);
    }

    public function create()
    {
        return view('bahan-baku.create', array_merge($this->opsi(), [
            'row' => new BahanBaku([
                'aktif' => true,
                'penyimpanan' => 'room',
                'konversi_beli_ke_pakai' => 1,
                'yield_persen' => 100,
                'dipakai_di_resep' => true,
            ]),
        ]));
    }

    public function store(StoreBahanBakuRequest $req)
    {
        $data = $req->validated();

        if ($file = $req->file('foto_path')) {
            $data['foto_path'] = $file->store('bahan-baku', 'public');
        }

        BahanBaku::create($data);

        return redirect()->route('bahan-baku.index')->with('success','Bahan baku berhasil dibuat.');
    }

    public function edit(BahanBaku $bahan_baku)
    {
        return view('bahan-baku.edit', array_merge($this->opsi(), [
            'row' => $bahan_baku,
        ]));
    }

    public function update(UpdateBahanBakuRequest $req, BahanBaku $bahan_baku)
    {
        $data = $req->validated();

        if ($file = $req->file('foto_path')) {
            if ($bahan_baku->foto_path) {
                Storage::disk('public')->delete($bahan_baku->foto_path);
            }
            $data['foto_path'] = $file->store('bahan-baku', 'public');
        }

        $bahan_baku->update($data);

        return redirect()->route('bahan-baku.index')->with('success','Bahan baku berhasil diperbarui.');
    }

    public function destroy(BahanBaku $bahan_baku)
    {
        if ($bahan_baku->foto_path) {
            Storage::disk('public')->delete($bahan_baku->foto_path);
        }
        $bahan_baku->delete();

        return back()->with('success','Bahan baku dihapus.');
    }
}
