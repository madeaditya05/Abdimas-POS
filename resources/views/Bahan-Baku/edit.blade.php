@extends('layouts.main')
@section('title','Bahan Baku')

@section('content')
  <div class="card">
    <h2>Edit Bahan Baku</h2>
    @include('bahan-baku._form', ['mode' => 'edit'])
  </div>
@endsection
