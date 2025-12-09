@push('styles')
  {{-- pakai tema yang sama dengan Bahan Baku biar konsisten --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Detail Jurnal')

@section('content')
<div class="card">
  {{-- HEADER --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;">Detail Jurnal</h2>
      <p class="muted" style="margin:4px 0 0;font-size:0.9rem;">
        No. Jurnal: <strong>{{ $entry->entry_no ?? '-' }}</strong>
      </p>
    </div>

    <a href="{{ route('laporan.jurnal.index') }}"
       class="btn btn--outline-coffee btn--with-icon">
      <x-heroicon-o-arrow-left class="icon-inline" />
      <span>Kembali ke Laporan Jurnal</span>
    </a>
  </div>

  {{-- INFO HEADER JURNAL --}}
  <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
    <div class="info-block">
      <div class="muted" style="font-size:0.8rem;">Tanggal</div>
      <div>
        @if($entry->date)
          {{ \Illuminate\Support\Carbon::parse($entry->date)->format('d M Y') }}
        @else
          —
        @endif
      </div>
    </div>

    <div class="info-block">
      <div class="muted" style="font-size:0.8rem;">No. Referensi</div>
      <div>{{ $entry->ref_no ?? '—' }}</div>
    </div>

    <div class="info-block">
      <div class="muted" style="font-size:0.8rem;">Sumber Transaksi</div>
      <div>
        @php
          $sourceType = $entry->source_type ?? null;
          $sourceLabel = null;

          if ($sourceType === \App\Models\Penjualan::class) {
              $sourceLabel = 'Penjualan';
          } elseif ($sourceType === \App\Models\PembelianBahan::class) {
              $sourceLabel = 'Pembelian Bahan';
          } elseif ($sourceType) {
              $sourceLabel = class_basename($sourceType);
          }
        @endphp

        @if($sourceLabel)
          <span class="badge">{{ $sourceLabel }}</span>
          @if($entry->ref_no)
            <span class="muted" style="font-size:0.8rem;margin-left:4px;">
              ({{ $entry->ref_no }})
            </span>
          @endif
        @else
          <span class="muted">—</span>
        @endif
      </div>
    </div>

    <div class="info-block">
      <div class="muted" style="font-size:0.8rem;">Dibuat Oleh</div>
      <div>
        @if(method_exists($entry, 'createdBy') && $entry->createdBy)
          {{ $entry->createdBy->name }}
        @else
          —
        @endif
      </div>
    </div>
  </div>

  {{-- MEMO UTAMA --}}
  @if(!empty($entry->memo))
    <div style="padding:0 16px 12px;">
      <div class="muted" style="font-size:0.8rem;margin-bottom:4px;">Memo</div>
      <div style="background:var(--bb-chip-bg,#f7f3ee);border-radius:8px;padding:8px 10px;font-size:0.9rem;">
        {{ $entry->memo }}
      </div>
    </div>
  @endif

  {{-- TABEL DETAIL JURNAL --}}
  @php
    $totalDebit  = $lines->sum('debit');
    $totalCredit = $lines->sum('credit');
  @endphp

  <div class="table-wrap" style="margin-top:4px;">
    <table class="table">
      <thead>
        <tr>
          <th style="width:40px;text-align:center;">#</th>
          <th style="width:130px;">Kode Akun</th>
          <th>Nama Akun</th>
          <th>Keterangan</th>
          <th class="num" style="width:140px;">Debit</th>
          <th class="num" style="width:140px;">Kredit</th>
        </tr>
      </thead>
      <tbody>
        @forelse($lines as $line)
          <tr>
            <td style="text-align:center;">
              {{ $line->line_no ?? $loop->iteration }}
            </td>
            <td>
              <strong>{{ $line->coa->code ?? '-' }}</strong>
            </td>
            <td>
              {{ $line->coa->name ?? '—' }}
            </td>
            <td>
              {{ $line->memo ?? '—' }}
            </td>
            <td class="num">
              {{ $line->debit > 0 ? 'Rp '.number_format((float)$line->debit, 0, ',', '.') : '—' }}
            </td>
            <td class="num">
              {{ $line->credit > 0 ? 'Rp '.number_format((float)$line->credit, 0, ',', '.') : '—' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="muted" style="text-align:center;">
              Tidak ada baris jurnal.
            </td>
          </tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4" style="text-align:right;">Total</th>
          <th class="num">
            {{ $totalDebit > 0 ? 'Rp '.number_format((float)$totalDebit, 0, ',', '.') : '—' }}
          </th>
          <th class="num">
            {{ $totalCredit > 0 ? 'Rp '.number_format((float)$totalCredit, 0, ',', '.') : '—' }}
          </th>
        </tr>
      </tfoot>
    </table>
  </div>

</div>
@endsection
