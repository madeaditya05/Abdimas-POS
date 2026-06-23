<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Tampilkan daftar customer + search.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = Customer::query()
            ->withCount(['completedPenjualans', 'penjualans', 'orders']);

        if ($search !== '') {
            // normalisasi sama seperti di model
            $norm = Customer::normalizeName($search);

            $query->where(function ($q) use ($search, $norm) {
                if ($norm) {
                    $q->where('normalized_name', $norm);
                }

                // fallback LIKE kalau normalized_name belum kepakai
                $q->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $items = $query->paginate(15)->withQueryString();

        return view('customer.index', [
            'items'  => $items,
            'search' => $search,
        ]);
    }

    /**
     * Form create customer.
     */
    public function create()
    {
        $row  = new Customer();
        $mode = 'create';

        return view('customer.form', [
            'row'  => $row,
            'mode' => $mode,
        ]);
    }

    /**
     * Simpan customer baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
        ]);

        $data['name'] = trim($data['name']);

        Customer::create($data);

        return redirect()
            ->route('customer.index')
            ->with('success', 'Customer berhasil dibuat.');
    }

    /**
     * Form edit customer.
     */
    public function edit(Customer $customer)
    {
        $row  = $customer;
        $mode = 'edit';

        return view('customer.form', [
            'row'  => $row,
            'mode' => $mode,
        ]);
    }

    /**
     * Update data customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
        ]);

        $data['name'] = trim($data['name']);

        $customer->update($data);

        return redirect()
            ->route('customer.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    /**
     * Hapus customer.
     */
    public function destroy(Customer $customer)
    {
        if ($customer->penjualans()->exists() || $customer->orders()->exists()) {
            return back()->with('error', 'Customer sudah dipakai di transaksi atau order, jadi tidak bisa dihapus.');
        }

        $customer->delete();

        return redirect()
            ->route('customer.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
