@extends('layouts.main')
@section('title','Input Beban Operasional')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/beban.css') }}">
@endpush

@section('content')
<div class="beban-page">
  <div class="beban-card">

    <div class="beban-header">
      <div>
        <div class="beban-title">Input Beban Operasional</div>
        <div class="beban-subtitle">
          Akan dicatat sebagai jurnal:
          <span class="chip">Debit</span> akun beban (6xxx) dan
          <span class="chip">Kredit</span> kas/bank (1001/1002).
        </div>
      </div>

      <div class="beban-header-actions">
        <a class="btn btn-ghost" href="{{ route('beban-operasional.index') }}">Kembali</a>
      </div>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger">
        <div class="alert-title">Ada yang perlu dibenerin:</div>
        <ul>
          @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('beban-operasional.store') }}" class="beban-form">
      @csrf

      <div class="form-grid">
        <div class="field">
          <label>Tanggal <span class="req">*</span></label>
          <input type="date" name="date" value="{{ old('date', $date) }}" required>
          <div class="hint">Tanggal transaksi beban.</div>
        </div>

        <div class="field">
          <label>Nominal <span class="req">*</span></label>
          <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}"
                 placeholder="contoh: 150000" required>
          <div class="hint">Isi angka saja, nanti tampil sebagai Rupiah.</div>
        </div>
      </div>

      <div class="form-grid">
        <div class="field">
          <label>Akun Beban (6xxx) <span class="req">*</span></label>
          <select name="expense_account" required>
            <option value="">- pilih akun beban -</option>
            @foreach($akunBeban as $a)
              <option value="{{ $a->id }}" @selected(old('expense_account') == $a->id)>
                {{ $a->code }} - {{ $a->name }}
              </option>
            @endforeach
          </select>
          <div class="hint">Contoh: 6001 Gaji, 6002 Listrik, dst.</div>
        </div>

        <div class="field">
          <label>Dibayar dari <span class="req">*</span></label>
          <select name="pay_account" required>
            <option value="">- pilih kas/bank -</option>
            @foreach($akunBayar as $a)
              <option value="{{ $a->id }}" @selected(old('pay_account') == $a->id)>
                {{ $a->code }} - {{ $a->name }}
              </option>
            @endforeach
          </select>
          <div class="hint">Pilih 1001 (Kas) atau 1002 (Bank).</div>
        </div>
      </div>

      <div class="form-grid">
        <div class="field">
          <label>No Referensi (opsional)</label>
          <input type="text" name="ref_no" value="{{ old('ref_no') }}" maxlength="50"
                 placeholder="contoh: INV-001 / STRUK-123">
        </div>

        <div class="field">
          <label>Keterangan (memo)</label>
          <input type="text" name="memo" value="{{ old('memo') }}" maxlength="255"
                 placeholder="contoh: Bayar listrik bulan Januari">
        </div>
      </div>

      <div class="beban-divider"></div>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Simpan</button>
        <a class="btn btn-ghost" href="{{ route('beban-operasional.index') }}">Batal</a>
      </div>
    </form>

  </div>
</div>
@endsection
