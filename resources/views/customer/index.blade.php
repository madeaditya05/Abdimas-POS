@extends('layouts.main')
@section('title','Customer')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@section('content')
<div class="card" id="customer-table">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <h2 style="margin:0;">Customer</h2>
  </div>

  @if (session('success'))
    <div class="alert alert--success" style="margin:12px 0;">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert--error" style="margin:12px 0;">
      {{ session('error') }}
    </div>
  @endif

  <form method="GET"
        action="{{ route('customer.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">
    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari nama customer..."
           style="min-width:240px;">

    <button class="btn btn--outline-coffee">Cari</button>

    @if($search !== '')
      <a href="{{ route('customer.index') }}" class="btn btn--outline-coffee">
        Reset
      </a>
    @endif
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="width:60px;">#</th>
          <th>Nama</th>
          <th style="width:130px;">Pembelian</th>
          <th style="width:190px;">Aturan Diskon</th>
          <th>Created</th>
          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $idx => $row)
          @php
            $jumlahBeli = (int) ($row->completed_penjualans_count ?? 0);
            $isUsed = ((int) ($row->penjualans_count ?? 0) > 0)
                || ((int) ($row->orders_count ?? 0) > 0);
            $minBeli = (int) ($row->discount_min_transactions ?? 10);
            $diskonPersen = (float) ($row->discount_percent ?? 0);
            $diskonAktif = $diskonPersen > 0 && $jumlahBeli >= $minBeli;
          @endphp

          <tr>
            <td>{{ $items->firstItem() + $idx }}</td>
            <td>{{ $row->name ?? '-' }}</td>
            <td>{{ number_format($jumlahBeli, 0, ',', '.') }} kali</td>
            <td>
              <div style="font-weight:600;">
                {{ number_format($diskonPersen, 2, ',', '.') }}%
              </div>
              <div class="muted" style="font-size:12px;">
                Minimal {{ number_format($minBeli, 0, ',', '.') }} kali beli
              </div>
              <div style="font-size:12px; color:{{ $diskonAktif ? '#166534' : '#64748b' }};">
                {{ $diskonAktif ? 'Sedang memenuhi syarat' : 'Belum memenuhi syarat' }}
              </div>
            </td>
            <td>{{ optional($row->created_at)->diffForHumans() ?? '-' }}</td>

            <td>
              <div class="actions">
                <a href="{{ route('customer.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M4 20h4l10.5-10.5a2.121 2.121 0 1 0-3-3L5 17v3Z" /><path d="m13.5 6.5 4 4" /></svg>
                  <span class="sr-only">Edit</span>
                </a>

                @if (!$isUsed)
                  <form action="{{ route('customer.destroy', $row) }}"
                        method="POST"
                        onsubmit="return confirm('Hapus customer ini?')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn--outline-danger btn--sm btn--icon"
                            title="Hapus">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M3 6h18" /><path d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" /><path d="M6.75 6l.7 11.2A2 2 0 0 0 9.44 19h5.12a2 2 0 0 0 1.99-1.8L17.25 6" /><path d="M10 10.25v5.5" /><path d="M14 10.25v5.5" /></svg>
                      <span class="sr-only">Hapus</span>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="muted" style="text-align:center;">
              Belum ada customer.
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
