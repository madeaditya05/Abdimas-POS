@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Customer')

@section('content')
<div class="card" id="customer-table">

  {{-- HEADER --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <h2 style="margin:0;">Customer</h2>

    <a href="{{ route('customer.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-inline"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
      <span>Customer Baru</span>
    </a>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="alert alert--success" style="margin:12px 0;">
      {{ session('success') }}
    </div>
  @endif

  {{-- SEARCH BAR --}}
  <form method="GET"
        action="{{ route('customer.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    <input type="text"
           name="q"
           value="{{ $search }}"
           placeholder="Cari nama customer…"
           style="min-width:240px;">

    <button class="btn btn--outline-coffee">Cari</button>

    @if($search !== '')
      <a href="{{ route('customer.index') }}" class="btn btn--outline-coffee">
        Reset
      </a>
    @endif
  </form>

  {{-- TABEL --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="width:60px;">#</th>
          <th>Nama</th>
          <th>Created</th>
          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($items as $idx => $row)
          <tr>
            <td>{{ $items->firstItem() + $idx }}</td>
            <td>{{ $row->name ?? '—' }}</td>
            <td>{{ optional($row->created_at)->diffForHumans() ?? '—' }}</td>

            <td>
              <div class="actions">
                {{-- EDIT --}}
                <a href="{{ route('customer.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M4 20h4l10.5-10.5a2.121 2.121 0 1 0-3-3L5 17v3Z" /><path d="m13.5 6.5 4 4" /></svg>
                  <span class="sr-only">Edit</span>
                </a>

                {{-- HAPUS --}}
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
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="muted" style="text-align:center;">
              Belum ada customer.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div style="margin-top:12px;">
    {{ $items->links('pagination::cofit') }}
  </div>

</div>
@endsection
