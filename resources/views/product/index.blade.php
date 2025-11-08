@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/product.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Produk')

@section('content')
<div class="product-admin">
  <div class="body-wrapper">
    <header class="app-header">
      <nav class="navbar navbar-expand-lg navbar-light">
        <ul class="navbar-nav">
          <li class="nav-item d-block d-xl-none">
            <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
              <i class="ti ti-menu-2"></i>
            </a>
          </li>
        </ul>
      </nav>
    </header>

    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

          <h2 class="page-title m-0 mb-3">Produk</h2>

          {{-- Filter --}}
          <form method="get" action="{{ route('product.search') }}" class="mb-3 d-flex gap-2 flex-wrap">
            <input type="text" name="s" value="{{ request('s') }}" class="form-control" placeholder="Cari nama atau SKU" style="max-width:260px">
            <button class="btn btn-secondary" type="submit">Cari</button>
            <a href="{{ route('product.index') }}" class="btn btn-light">Reset</a>
          </form>

          <div class="card">
            <div class="subcard-header">
              <span class="muted">Daftar Produk</span>
              <a href="{{ route('product.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus"></i> Tambah Produk
              </a>
            </div>

            <div class="card-body">
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Nama</th>
                      <th>SKU</th>
                      <th>Harga</th>
                      <th style="width:160px">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($product as $p)
                      <tr>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->sku }}</td>
                        <td>Rp {{ number_format($p->price,0,',','.') }}</td>
                        <td>
                          <div class="d-flex gap-2">
                            <a href="{{ route('product.edit', $p->id) }}" class="btn btn-edit btn-sm">Edit</a>
                            <a href="#" class="btn btn-delete btn-sm"
                               data-id="{{ $p->id }}"
                               onclick="deleteConfirm(this); return false;">Hapus</a>
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="muted">Belum ada data</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div><!-- /inner card -->
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal konfirmasi (custom, tidak tergantung Bootstrap) --}}
<div id="confirmModal" class="pa-modal">
  <div class="box">
    <div class="box-header">Apakah anda yakin?</div>
    <div class="box-body"><div id="xid"></div></div>
    <div class="box-footer">
      <button type="button" class="btn btn-light" onclick="closeConfirm()">Cancel</button>
      <a id="btn-delete" class="btn btn-delete">Hapus</a>
    </div>
  </div>
</div>

@push('scripts')
<script>
  const DELETE_URL = @json(route('product.delete',['id'=>'__ID__']));

  function deleteConfirm(el){
    const id = el.getAttribute('data-id');
    const url = DELETE_URL.replace('__ID__', id);
    document.getElementById('btn-delete').setAttribute('href', url);
    document.getElementById('xid').innerHTML = "Data dengan ID <b>"+id+"</b> akan dihapus";
    openConfirm();
  }
  function openConfirm(){
    document.getElementById('confirmModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeConfirm(){
    document.getElementById('confirmModal').classList.remove('open');
    document.body.style.overflow = '';
  }
  // Klik backdrop untuk tutup
  document.getElementById('confirmModal').addEventListener('click', function(e){
    if(e.target.id === 'confirmModal') closeConfirm();
  });
</script>
@endpush
@endsection
