@push('styles')
  {{-- sementara pakai stylesheet yang sama dengan modul bahan baku --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">

  {{-- FIX: kecilkan icon SVG pagination biar nggak jadi raksasa --}}
  <style>
    /* khusus pagination bawaan Laravel */
    nav[role="navigation"] svg {
      width: 16px !important;
      height: 16px !important;
      display: inline-block;
    }
  </style>
@endpush

@extends('layouts.main')
@section('title', 'Pembelian Bahan Detail')

@section('content')
<div class="card">

  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Pembelian Bahan Detail</h2>
    {{-- tombol di header DIHAPUS, pakai yang di baris filter aja --}}
  </div>

  {{-- Filter & Search --}}
  <form method="GET"
        action="{{ route('pembelian-bahan-detail.index') }}"
        class="filter-bar"
        style="
          margin:12px 0;
          display:flex;
          align-items:center;
          gap:10px;
          flex-wrap:wrap;
          justify-content:flex-start;
        ">

    {{-- Search + hidden sort/dir --}}
    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari kode pembelian / nama bahan…" />

    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="dir"  value="{{ $dir  }}">

    {{-- Tombol aksi --}}
    <button class="btn btn--outline-coffee">
      Terapkan
    </button>

    <a href="{{ route('pembelian-bahan-detail.index') }}"
       class="btn btn--outline-coffee">
      Reset
    </a>

    <a href="{{ route('pembelian-bahan.index') }}"
       class="btn btn--outline-coffee">
      Pembelian Bahan
    </a>
  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Pembelian</th>
          <th>Nama bahan</th>
          <th>Satuan beli</th>
          <th class="num">Qty beli</th>
          <th class="num">Harga satuan</th>
          <th class="num">Subtotal</th>
          <th>Expired date</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $row)
          @php
            $header = $row->header; // relasi ke PembelianBahan
          @endphp

          <tr>
            {{-- Kode pembelian (header) --}}
            <td>
              {{ $header?->kode_pembelian ?? '–' }}
            </td>

            {{-- Nama bahan --}}
            <td>{{ $row->nama_bahan ?? '–' }}</td>

            {{-- Satuan beli --}}
            <td>{{ $row->satuan_beli ?? '–' }}</td>

            {{-- Qty beli --}}
            <td class="num">
              {{ $row->qty_beli !== null ? number_format((float) $row->qty_beli, 0, ',', '.') : '–' }}
            </td>

            {{-- Harga satuan --}}
            <td class="num">
              {{ $row->harga_satuan !== null ? 'Rp '.number_format((float) $row->harga_satuan, 0, ',', '.') : '–' }}
            </td>

            {{-- Subtotal --}}
            <td class="num">
              {{ $row->subtotal !== null ? 'Rp '.number_format((float) $row->subtotal, 0, ',', '.') : '–' }}
            </td>

            {{-- Expired date --}}
            <td>
              {{ $row->expired_date ?? '–' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="muted" style="text-align:center;">
              Belum ada data detail pembelian.
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
