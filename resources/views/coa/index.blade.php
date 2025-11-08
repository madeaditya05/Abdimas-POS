@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/product.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'COA')

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

          <h2 class="page-title m-0 mb-3">Chart of Accounts (COA)</h2>

          {{-- Filter --}}
          <form method="get" action="{{ route('coa.search') }}" class="mb-3 d-flex gap-2 flex-wrap">
            <input type="text" name="s" value="{{ request('s') }}" class="form-control" placeholder="Cari kode / nama akun / tipe" style="max-width:300px">
            <button class="btn btn-secondary" type="submit">Cari</button>
            <a href="{{ route('coa.index') }}" class="btn btn-light">Reset</a>
          </form>

          <div class="card">
            <div class="subcard-header">
              <span class="muted">Daftar Akun</span>
              <a href="{{ route('coa.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus"></i> Tambah Akun
              </a>
            </div>

            <div class="card-body">
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Kode</th>
                      <th>Nama Akun</th>
                      <th>Tipe</th>
                      <th style="width:160px">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($coa as $c)
                      <tr>
                        <td>{{ $c->code }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->type }}</td>
                        <td>
                          <div class="d-flex gap-2">
                            <a href="{{ route('coa.edit', $c->id) }}" class="btn btn-edit btn-sm">Edit</a>
                            <a href="#" class="btn btn-delete btn-sm"
                               data-id="{{ $c->id }}"
                               onclick="deleteConfirm(this); return false;">Hapus</a>
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="5" class="muted">Belum ada data</td></tr>
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

{{-- Modal konfirmasi (custom) --}}
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
  const DELETE_URL = @json(route('coa.delete',['id'=>'__ID__']));
  function deleteConfirm(el){
    const id = el.getAttribute('data-id');
    const url = DELETE_URL.replace('__ID__', id);
    document.getElementById('btn-delete').setAttribute('href', url);
    document.getElementById('xid').innerHTML = "Akun dengan ID <b>"+id+"</b> akan dihapus";
    openConfirm();
  }
  function openConfirm(){ document.getElementById('confirmModal').classList.add('open'); document.body.style.overflow='hidden'; }
  function closeConfirm(){ document.getElementById('confirmModal').classList.remove('open'); document.body.style.overflow=''; }
  document.getElementById('confirmModal').addEventListener('click', e => { if(e.target.id==='confirmModal') closeConfirm(); });
</script>
@endpush
@endsection
