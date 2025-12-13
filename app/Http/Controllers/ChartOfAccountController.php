<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    private function opsi(): array
    {
        return [
            'opsiType' => [
                'asset'     => 'Aset',
                'liability' => 'Liabilitas',
                'equity'    => 'Ekuitas',
                'revenue'   => 'Pendapatan',
                'expense'   => 'Beban',
            ],
            'opsiNormalSide' => [
                'debit'  => 'Debit',
                'credit' => 'Kredit',
            ],
            'opsiAccountNames' => [
                'Kas'                                   => 'Kas',
                'Bank'                                  => 'Bank',
                'Piutang Usaha'                         => 'Piutang Usaha',
                'Persediaan Bahan Baku'                 => 'Persediaan Bahan Baku',
                'Persediaan Bahan Penolong'            => 'Persediaan Bahan Penolong',
                'Peralatan Kedai'                       => 'Peralatan Kedai',
                'Perabot & Perlengkapan'                => 'Perabot & Perlengkapan',
                'Akumulasi Penyusutan Peralatan'        => 'Akumulasi Penyusutan Peralatan',
                'Hutang Usaha'                          => 'Hutang Usaha',
                'Hutang Gaji'                           => 'Hutang Gaji',
                'Hutang Pajak'                          => 'Hutang Pajak',
                'Pinjaman Bank'                         => 'Pinjaman Bank',
                'Modal Pemilik'                         => 'Modal Pemilik',
                'Prive'                                 => 'Prive',
                'Laba Ditahan'                          => 'Laba Ditahan',
                'Penjualan Minuman'                     => 'Penjualan Minuman',
                'Penjualan Makanan'                     => 'Penjualan Makanan',
                'Penjualan Lain-lain'                   => 'Penjualan Lain-lain',
                'Retur & Potongan Penjualan'            => 'Retur & Potongan Penjualan',
                'HPP Bahan Baku Minuman'                => 'HPP Bahan Baku Minuman',
                'HPP Bahan Baku Makanan'                => 'HPP Bahan Baku Makanan',
                'Beban Gaji Karyawan'                   => 'Beban Gaji Karyawan',
                'Beban Listrik & Air'                   => 'Beban Listrik & Air',
                'Beban Sewa'                            => 'Beban Sewa',
                'Beban Perlengkapan Kedai'              => 'Beban Perlengkapan Kedai',
                'Beban Perawatan & Servis Mesin'        => 'Beban Perawatan & Servis Mesin',
                'Beban Transportasi / Delivery'         => 'Beban Transportasi / Delivery',
                'Beban Marketing & Promosi'             => 'Beban Marketing & Promosi',
                'Beban Lain-lain'                       => 'Beban Lain-lain',
            ],
        ];
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $type   = (string) $request->query('type', '');
        $aktif  = (string) $request->query('aktif', ''); // '' / '1' / '0'

        $allowedSort = ['code', 'name', 'type', 'normal_side', 'is_active', 'created_at'];
        $sort = (string) $request->query('sort', 'code');
        $dir  = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (!in_array($sort, $allowedSort, true)) $sort = 'code';

        $q = ChartOfAccount::query()
            ->when($search !== '', function ($qq) use ($search) {
                $qq->where(function ($w) use ($search) {
                    $w->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', fn ($qq) => $qq->where('type', $type))
            ->when($aktif !== '', fn ($qq) => $qq->where('is_active', $aktif === '1'))
            ->orderBy($sort, $dir);

        $items = $q->paginate(10)->withQueryString();

        $opsi = $this->opsi();

        // ✅ karena folder view kamu di resources/views/chart-of-accounts
        return view('chart-of-accounts.index', [
            'items'         => $items,
            'search'        => $search,
            'selectedType'  => $type,
            'selectedAktif' => $aktif,
            'sort'          => $sort,
            'dir'           => $dir,

            'opsiType'       => $opsi['opsiType'],
            'opsiNormalSide' => $opsi['opsiNormalSide'],
        ]);
    }

    public function create()
    {
        $opsi = $this->opsi();

        return view('chart-of-accounts.create', [
            'mode'             => 'create',
            'row'              => new ChartOfAccount(),
            'opsiType'         => $opsi['opsiType'],
            'opsiNormalSide'   => $opsi['opsiNormalSide'],
            'opsiAccountNames' => $opsi['opsiAccountNames'],
        ]);
    }

    public function store(Request $request)
    {
        $opsi = $this->opsi();

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:20', 'unique:chart_of_account,code'],
            'name'        => ['required', 'string', Rule::in(array_keys($opsi['opsiAccountNames']))],
            'type'        => ['required', 'string', Rule::in(array_keys($opsi['opsiType']))],
            'normal_side' => ['required', 'string', Rule::in(array_keys($opsi['opsiNormalSide']))],
            'is_active'   => ['nullable', 'boolean'],
        ], [], [
            'code'        => 'Kode Akun',
            'name'        => 'Nama Akun',
            'type'        => 'Tipe Akun',
            'normal_side' => 'Saldo Normal',
            'is_active'   => 'Aktif',
        ]);

        $data['is_active'] = (bool) ($request->input('is_active', false));

        ChartOfAccount::create($data);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(ChartOfAccount $chart_of_account)
    {
        $opsi = $this->opsi();

        return view('chart-of-accounts.edit', [
            'mode'             => 'edit',
            'row'              => $chart_of_account,
            'opsiType'         => $opsi['opsiType'],
            'opsiNormalSide'   => $opsi['opsiNormalSide'],
            'opsiAccountNames' => $opsi['opsiAccountNames'],
        ]);
    }

    public function update(Request $request, ChartOfAccount $chart_of_account)
    {
        $opsi = $this->opsi();

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:20', Rule::unique('chart_of_account', 'code')->ignore($chart_of_account->id)],
            'name'        => ['required', 'string', Rule::in(array_keys($opsi['opsiAccountNames']))],
            'type'        => ['required', 'string', Rule::in(array_keys($opsi['opsiType']))],
            'normal_side' => ['required', 'string', Rule::in(array_keys($opsi['opsiNormalSide']))],
            'is_active'   => ['nullable', 'boolean'],
        ], [], [
            'code'        => 'Kode Akun',
            'name'        => 'Nama Akun',
            'type'        => 'Tipe Akun',
            'normal_side' => 'Saldo Normal',
            'is_active'   => 'Aktif',
        ]);

        $data['is_active'] = (bool) ($request->input('is_active', false));

        $chart_of_account->update($data);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(ChartOfAccount $chart_of_account)
    {
        $chart_of_account->delete();

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}
