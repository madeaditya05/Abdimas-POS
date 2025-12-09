@push('styles')
  {{-- sementara pakai CSS yang sama dengan Bahan Baku biar look & feel konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Resep Detail')

@section('content')
<div class="card">
  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Resep Detail</h2>
  </div>

  {{-- Filter & Search --}}
  <form method="GET"
        action="{{ route('resep-detail.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    <input type="text"
           name="q"
           value="{{ $search ?? '' }}"
           placeholder="Cari produk / nama bahan…"
    />

    <button class="btn btn--outline-coffee">Terapkan</button>

    <a href="{{ route('resep-detail.index') }}"
       class="btn btn--outline-coffee">
      Reset
    </a>

    {{-- Tombol balik ke header Resep --}}
    <a href="{{ route('resep.index') }}"
       class="btn btn--outline-coffee">
      Resep
    </a>
  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>ID Resep</th>
          <th>Produk</th>
          <th>Bahan Baku</th>
          <th class="num">Qty per porsi</th>
          <th>Diupdate</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $row)
          <tr>
            {{-- ID Resep --}}
            <td>
              {{ optional($row->resep)->id ?? '-' }}
            </td>

            {{-- Nama produk --}}
            <td>
              {{ optional(optional($row->resep)->produk)->nama_barang ?? '-' }}
            </td>

            {{-- Nama bahan baku --}}
            <td>
              {{ optional($row->bahanBaku)->nama_bahan ?? '-' }}
            </td>

            {{-- Qty per porsi (angka -> rata kanan) --}}
            <td class="num">
              {{ $row->qty_per_porsi ?? 0 }}
            </td>

            {{-- Diupdate --}}
            <td>
              {{ optional($row->updated_at)->format('d M Y H:i') }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="muted" style="text-align:center;">
              Belum ada data resep detail.
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
