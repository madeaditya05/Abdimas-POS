@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Buat Resep')

@section('content')
<div class="card">
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Buat Resep (Bill of Material)</h2>
  </div>

  @include('resep._form', [
      'mode'        => 'create',
      'row'         => $row,
      'opsiProduk'  => $opsiProduk,
      'opsiBahan'   => $opsiBahan,
      'detailRows'  => $detailRows ?? collect(),
  ])
</div>
@endsection
