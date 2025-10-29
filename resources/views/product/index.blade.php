@extends('layouts.main')
@section('title', 'Kasir')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/product.css') }}">
@endpush

@section('content')
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
      <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
          <a href="#" class="btn btn-primary">-</a>
          <li class="nav-item dropdown">
            <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="{{asset('images/profile/user-1.jpg')}}" alt="" width="35" height="35" class="rounded-circle">
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
              <div class="message-body">
                <form action="{{ route('logout') }}" method="POST">@csrf
                  <button type="submit" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</button>
                </form>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <div class="container-fluid">
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

          <div class="row">
            <div class="col-md-12">
              <h5 class="card-title fw-semibold mb-4">Produk</h5>

              <div class="card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Master Data Produk</h6>
                  <a href="{{ route('product.create') }}" class="btn btn-primary btn-icon-split btn-sm">
                    <span class="icon text-white-50"><i class="ti ti-plus"></i></span>
                    <span class="text">Tambah Produk</span>
                  </a>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                      <thead class="thead-dark">
                        <tr>
                          <th>Nama</th>
                          <th>SKU</th>
                          <th>Harga</th>
                          <th>Status</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($product as $p)
                          <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->sku }}</td>
                            <td>Rp {{ number_format($p->price,0,',','.') }}</td>
                            <td>
                              @if($p->is_active)
                                <span class="badge bg-success">Aktif</span>
                              @else
                                <span class="badge bg-secondary">Nonaktif</span>
                              @endif
                            </td>
                            <td>
                              <a href="{{ route('product.edit', $p->id) }}" class="btn btn-success btn-sm">Edit</a>
                              <a href="#" onclick="deleteConfirm(this); return false;" data-id="{{ $p->id }}" class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>

              </div><!-- card inner -->
            </div>
          </div>

        </div>
      </div>
    </div>

    {{-- Modal Hapus --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Apakah anda yakin?</h5></div>
          <div class="modal-body" id="xid"></div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
            <a id="btn-delete" class="btn btn-danger" href="#">Hapus</a>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /container-fluid -->
</div><!-- /wrapper -->

<script>
function deleteConfirm(e){
  const tomboldelete = document.getElementById('btn-delete');
  const id = e.getAttribute('data-id');
  const url = "{{ route('product.destroy','__id__') }}".replace('__id__', id);
  tomboldelete.setAttribute("href", url);
  document.getElementById("xid").innerHTML = "Data dengan ID <b>"+id+"</b> akan dihapus";
  const myModal = new bootstrap.Modal(document.getElementById('deleteModal'), { keyboard: false });
  myModal.show();
}
</script>
@endsection
