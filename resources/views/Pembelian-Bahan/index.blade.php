@push('styles')
  {{-- sementara pakai CSS yang sama dengan Bahan Baku biar look & feel konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Pembelian Bahan')

@section('content')
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Pembelian Bahan</h2>

    <a href="{{ route('pembelian-bahan.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-inline"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
      <span>Tambah Pembelian</span>
    </a>
  </div>

  <form method="GET"
        action="{{ route('pembelian-bahan.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari kode / pemasok..."/>

    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="dir"  value="{{ $dir  }}">

    <button class="btn btn--outline-coffee">Terapkan</button>
    <a href="{{ route('pembelian-bahan.index') }}"
       class="btn btn--outline-coffee">
      Reset
    </a>
    <a href="{{ route('pembelian-bahan-detail.index') }}"
       class="btn btn--outline-coffee">
      Rekap Detail
    </a>
  </form>

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
              {{ $row->tanggal ? $row->tanggal->format('d M Y H:i') : '-' }}
            </td>

            <td>{{ $row->supplier_nama ?: '-' }}</td>

            <td>{{ $row->supplier_kontak ?: '-' }}</td>

            <td class="num">
              @php $total = (float) ($row->total ?? 0); @endphp
              {{ $total > 0 ? 'Rp '.number_format($total, 0, ',', '.') : '-' }}
            </td>

            <td>
              <div class="actions">
                <a href="{{ route('pembelian-bahan.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M4 20h4l10.5-10.5a2.121 2.121 0 1 0-3-3L5 17v3Z" /><path d="m13.5 6.5 4 4" /></svg>
                  <span class="sr-only">Edit</span>
                </a>

                <form action="{{ route('pembelian-bahan.destroy', $row) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus pembelian ini?')">
                  @csrf
                  @method('DELETE')

                  <button type="submit"
                          class="btn btn--outline-danger btn--sm btn--icon"
                          title="Hapus">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M3 6h18" /><path d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" /><path d="M6.75 6l.7 11.2A2 2 0 0 0 9.44 19h5.12a2 2 0 0 0 1.99-1.8L17.25 6" /><path d="M10 10.25v5.5" /><path d="M14 10.25v5.5" /></svg>
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
