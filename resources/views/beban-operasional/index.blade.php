@extends('layouts.main')
@section('title','Beban Operasional')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/beban.css') }}">
@endpush

@section('content')
@php
  use Illuminate\Support\Facades\DB;

  $entryIds = collect($entries)->pluck('id')->all();
  $totals = [];

  if (!empty($entryIds)) {
      $rows = DB::table('journal_line as jl')
          ->join('chart_of_account as coa', 'coa.id', '=', 'jl.account_id')
          ->select('jl.journal_entry_id', DB::raw('SUM(jl.debit) as total_debit_beban'))
          ->whereIn('jl.journal_entry_id', $entryIds)
          ->where('coa.code', 'like', '6%')
          ->groupBy('jl.journal_entry_id')
          ->get();

      foreach ($rows as $r) {
          $totals[$r->journal_entry_id] = (float) $r->total_debit_beban;
      }
  }

  $fmt = fn($n) => number_format((float)$n, 0, ',', '.');
@endphp

<div class="beban-page">
  <div class="beban-card">

    <div class="beban-header">
      <div>
        <div class="beban-title">Beban Operasional</div>
        <div class="beban-subtitle">
          Input dan kelola beban usaha. Data tersimpan ke jurnal (<span class="chip">BEBAN_OPERASIONAL</span>).
        </div>
      </div>

      <div class="beban-header-actions">
        <a class="btn btn-primary" href="{{ route('beban-operasional.create') }}">+ Input Beban</a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form class="beban-filter" method="GET" action="{{ route('beban-operasional.index') }}">
      <div class="field">
        <label>Dari</label>
        <input type="date" name="start_date" value="{{ $start }}">
      </div>
      <div class="field">
        <label>Sampai</label>
        <input type="date" name="end_date" value="{{ $end }}">
      </div>
      <div class="filter-actions">
        <button class="btn btn-ghost" type="submit">Terapkan</button>
        <a class="btn btn-ghost" target="_blank" href="{{ route('beban-operasional.pdf', request()->all()) }}">PDF</a>
        <a class="btn btn-ghost" target="_blank" href="{{ route('beban-operasional.excel', request()->all()) }}">Excel</a>
      </div>
    </form>

    <div class="beban-divider"></div>

    <div class="table-wrap">
      <table class="beban-table">
        <thead>
          <tr>
            <th style="width:130px;">Tanggal</th>
            <th style="width:180px;">No Jurnal</th>
            <th style="width:180px;">Ref</th>
            <th>Keterangan</th>
            <th style="width:160px;text-align:right;">Nominal</th>
            <th style="width:110px;text-align:center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($entries as $e)
            @php $nominal = $totals[$e->id] ?? 0; @endphp
            <tr>
              <td class="muted">{{ $e->date }}</td>
              <td class="mono">{{ $e->entry_no }}</td>
              <td class="mono">{{ $e->ref_no ?? '-' }}</td>
              <td>{{ $e->memo ?? '-' }}</td>
              <td class="money">Rp {{ $fmt($nominal) }}</td>
              <td class="center">
                <form method="POST" action="{{ route('beban-operasional.destroy', $e->id) }}"
                      onsubmit="return confirm('Hapus beban ini? (journal_entry + journal_line ikut terhapus)');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="empty">Belum ada data beban operasional pada periode ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection
