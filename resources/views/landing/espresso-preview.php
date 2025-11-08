@extends('layouts.main')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/espresso.css') }}">

<h2 style="margin:12px 0;">Preview Hitungan Shots Hari Ini</h2>
<table class="table">
  <thead>
    <tr>
      <th>ID</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Qty</th><th>Shots/Unit</th><th>Total Shots</th>
    </tr>
  </thead>
  <tbody>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->produk_id }}</td>
      <td>{{ $r->kode_barang }}</td>
      <td>{{ $r->nama_barang }}</td>
      <td>{{ $r->kategori }}</td>
      <td>{{ $r->qty }}</td>
      <td>{{ $r->shots_per_unit }}</td>
      <td>{{ $r->shots_total }}</td>
    </tr>
  @empty
    <tr><td colspan="7">Belum ada transaksi paid hari ini.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
