@push('styles')
  {{-- sementara pakai CSS yang sama dengan Bahan Baku biar look & feel konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Pembelian Bahan')

@section('content')
<div class="card">
  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Pembelian Bahan</h2>

    <a href="{{ route('pembelian-bahan.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <x-heroicon-o-plus class="icon-inline" />
      <span>Buat Baru</span>
    </a>
  </div>

  {{-- Search bar --}}
  <form method="GET"
        action="{{ route('pembelian-bahan.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari kode / supplier…"/>

    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="dir"  value="{{ $dir  }}">

    <button class="btn btn--outline-coffee">Terapkan</button>
    <a href="{{ route('pembelian-bahan.index') }}"
       class="btn btn--outline-coffee">
      Reset
    </a>
    <a href="{{ route('pembelian-bahan-detail.index') }}"
       class="btn btn--outline-coffee">
        Pembelian Bahan Detail
    </a>
  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Tanggal</th>
          <th>Supplier</th>
          <th>Kontak</th>
          <th class="num">Total</th>
          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $row)
          <tr>
            <td>{{ $row->kode_pembelian }}</td>

            <td>
              {{ $row->tanggal ? $row->tanggal->format('d M Y H:i') : '–' }}
            </td>

            <td>{{ $row->supplier_nama ?: '–' }}</td>

            <td>{{ $row->supplier_kontak ?: '–' }}</td>

            <td class="num">
              @php $total = (float) ($row->total ?? 0); @endphp
              {{ $total > 0 ? 'Rp '.number_format($total, 0, ',', '.') : '–' }}
            </td>

            <td>
              <div class="actions">
                {{-- EDIT --}}
                <a href="{{ route('pembelian-bahan.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <x-heroicon-o-pencil-square class="icon-aksi" />
                  <span class="sr-only">Edit</span>
                </a>

                {{-- HAPUS --}}
                <form action="{{ route('pembelian-bahan.destroy', $row) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus pembelian ini?')">
                  @csrf
                  @method('DELETE')

                  <button type="submit"
                          class="btn btn--outline-danger btn--sm btn--icon"
                          title="Hapus">
                    <x-heroicon-o-trash class="icon-aksi" />
                    <span class="sr-only">Hapus</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="muted" style="text-align:center;">
              Belum ada data pembelian.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:12px;">
    {{ $items->links('pagination::cofit') }}
  </div>
</div>
@endsection
