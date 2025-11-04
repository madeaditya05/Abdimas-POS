@extends('layouts.main')
@section('title','Bahan Baku')

@section('content')
  <div class="card">
    <h2>Create Bahan Baku</h2>
    @include('bahan-baku._form', ['mode' => 'create'])
  </div>
@endsection
