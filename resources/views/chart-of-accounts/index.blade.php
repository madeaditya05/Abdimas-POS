@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Daftar Akun (COA)')

@section('content')
<div class="card">

  {{-- Header --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Daftar Akun (COA)</h2>
      <div class="muted" style="margin-top:4px;">Chart of Accounts untuk modul akuntansi</div>
    </div>

    <a href="{{ route('chart-of-accounts.create') }}"
       class="btn btn--outline-success btn--with-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-inline"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
      <span>Buat Baru</span>
    </a>
  </div>

  {{-- Flash --}}
  @if (session('success'))
    <div class="form-section" style="border-color:#bbf7d0;background:#f0fdf4;margin-top:12px;">
      <strong>Sukses:</strong> {{ session('success') }}
    </div>
  @endif

  {{-- Filter & Search --}}
  <form method="GET" action="{{ route('chart-of-accounts.index') }}"
        class="filter-bar"
        style="margin:12px 0;gap:10px;display:flex;align-items:center;flex-wrap:wrap;">

    <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode/nama akun…" />

    {{-- Filter Type --}}
    @php $selType = $selectedType ?? ''; @endphp
    <div class="dd" style="min-width:220px">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          {{ $selType ? ($opsiType[$selType] ?? ucfirst($selType)) : 'Semua tipe akun' }}
        </span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selType==='' ? 'active':'' }}" data-value="">Semua tipe akun</div>
        @foreach($opsiType as $k => $label)
          <div class="dd-item {{ $selType===$k ? 'active':'' }}" data-value="{{ $k }}">{{ $label }}</div>
        @endforeach
      </div>
      <input type="hidden" name="type" value="{{ $selType }}">
    </div>

    {{-- Filter Aktif / Nonaktif --}}
    @php $selAkt = $selectedAktif ?? ''; @endphp
    <div class="dd" style="min-width:200px">
      <button type="button" class="dd-toggle">
        <span class="dd-label">
          @switch($selAkt)
            @case('1') Aktif @break
            @case('0') Nonaktif @break
            @default Status Aktif
          @endswitch
        </span>
        <span class="dd-caret"></span>
      </button>
      <div class="dd-menu">
        <div class="dd-item {{ $selAkt==='' ? 'active':'' }}" data-value="">Status Aktif</div>
        <div class="dd-item {{ $selAkt==='1' ? 'active':'' }}" data-value="1">Aktif</div>
        <div class="dd-item {{ $selAkt==='0' ? 'active':'' }}" data-value="0">Nonaktif</div>
      </div>
      <input type="hidden" name="aktif" value="{{ $selAkt }}">
    </div>

    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="dir"  value="{{ $dir  }}">

    <button class="btn btn--outline-coffee">Terapkan</button>
    <a class="btn btn--outline-coffee" href="{{ route('chart-of-accounts.index') }}">Reset</a>
  </form>

  {{-- Tabel --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama Akun</th>
          <th>Tipe</th>
          <th>Saldo Normal</th>
          <th>Aktif</th>
          <th style="width:130px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($items as $row)
          <tr>
            <td>{{ $row->code }}</td>
            <td>{{ $row->name }}</td>

            <td>
              <span class="badge">{{ $opsiType[$row->type] ?? $row->type }}</span>
            </td>

            <td>
              <span class="badge">{{ $row->normal_side === 'debit' ? 'Debit' : 'Kredit' }}</span>
            </td>

            <td>
              <span class="bool {{ $row->is_active ? 'bool--yes' : 'bool--no' }}">
                <span class="bool-dot"></span>
                {{ $row->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>

            <td>
              <div class="actions">
                {{-- EDIT --}}
                <a href="{{ route('chart-of-accounts.edit', $row) }}"
                   class="btn btn--outline-warning btn--sm btn--icon"
                   title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="icon-aksi"><path d="M4 20h4l10.5-10.5a2.121 2.121 0 1 0-3-3L5 17v3Z" /><path d="m13.5 6.5 4 4" /></svg>
                  <span class="sr-only">Edit</span>
                </a>

                {{-- HAPUS --}}
                <form action="{{ route('chart-of-accounts.destroy', $row) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus akun ini?')">
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
            <td colspan="6" class="muted" style="text-align:center;">
              Belum ada data akun.
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

@push('scripts')
<script>
(() => {
  const closeAll = () => document.querySelectorAll('.dd.open').forEach(dd => dd.classList.remove('open'));
  document.addEventListener('click', e => { if (!e.target.closest('.dd')) closeAll(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });

  document.querySelectorAll('.dd').forEach(dd => {
    const btn   = dd.querySelector('.dd-toggle');
    const menu  = dd.querySelector('.dd-menu');
    const label = dd.querySelector('.dd-label');
    const input = dd.querySelector('input[type="hidden"]');

    btn?.addEventListener('click', e => {
      e.stopPropagation();
      const willOpen = !dd.classList.contains('open');
      closeAll(); if (willOpen) dd.classList.add('open');
    });

    menu?.querySelectorAll('.dd-item').forEach(item => {
      item.addEventListener('click', () => {
        menu.querySelectorAll('.dd-item.active').forEach(x => x.classList.remove('active'));
        item.classList.add('active');
        input.value = item.dataset.value ?? '';
        label.textContent = item.textContent.trim();
        dd.classList.remove('open');
      });
    });
  });
})();
</script>
@endpush
