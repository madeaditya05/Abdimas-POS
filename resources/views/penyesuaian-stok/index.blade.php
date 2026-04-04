@push('styles')
  {{-- sementara pakai CSS yang sama dengan Bahan Baku biar look & feel konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Riwayat Penyesuaian Stok')

@section('content')
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Riwayat Penyesuaian Stok</h2>

    <a href="{{ route('penyesuaian-stok.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-inline"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
      <span>Penyesuaian Baru</span>
    </a>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Kode</th>
          <th>Nama Bahan</th>
          <th>Jenis</th>
          <th class="num">Qty</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $row)
          @php
            $bahan = $row->bahan;

            // DB: IN / OUT / ADJ
            $tipe  = strtoupper((string) $row->tipe);

            if ($tipe === 'IN') {
              $jenis = 'Bertambah';
              $sign  = '+';
            } elseif ($tipe === 'OUT') {
              $jenis = 'Berkurang';
              $sign  = '-';
            } else {
              $jenis = 'Penyesuaian';
              $sign  = '';
            }

            // qty di DB sudah angka positif
            $qtyText = rtrim(rtrim(number_format((float) $row->qty, 2, ',', '.'), '0'), ',');
          @endphp

          <tr>
            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }}</td>
            <td>{{ $bahan?->kode_bahan ?? '-' }}</td>
            <td>{{ $bahan?->nama_bahan ?? '(bahan dihapus)' }}</td>
            <td>{{ $jenis }}</td>
            <td class="num">
              {{ $sign }} {{ $qtyText }}
            </td>
            <td>{{ $row->note ?: '—' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="muted" style="text-align:center;">
              Belum ada penyesuaian stok.
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
