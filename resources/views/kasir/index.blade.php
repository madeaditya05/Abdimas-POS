@extends('layouts.main')
@section('title', 'Kasir')

@section('content')
<form action="{{ route('kasir.store') }}" method="POST">
  @csrf

  <div class="grid-kasir">
    {{-- Kiri: Produk --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <h3>Produk</h3>
          <p>Pilih item untuk ditambahkan ke keranjang</p>
        </div>
      </div>

      <div class="produk-toolbar" style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
        <input type="text" id="cariProduk" class="form-input" placeholder="Cari produk…">
        <select id="filterKategori" class="form-input" style="max-width:220px;">
          <option value="">Semua kategori</option>
          @foreach($kategoris as $kat)
            <option value="{{ Str::lower($kat) }}">{{ $kat }}</option>
          @endforeach
        </select>
      </div>

      <div class="produk-grid" id="gridProduk">
        @foreach($produks as $p)
          <div class="produk-card"
               data-id="{{ $p->id }}"
               data-name="{{ Str::lower($p->nama_barang) }}"
               data-price="{{ (int)$p->harga }}"
               data-kategori="{{ Str::lower($p->kategori ?? '') }}">
            <div class="produk-main">
              <div class="produk-avatar">{{ strtoupper(mb_substr($p->nama_barang,0,1)) }}</div>
              <div class="produk-info">
                <div class="produk-name">{{ $p->nama_barang }}</div>
                <div class="produk-price">Rp {{ number_format($p->harga,0,',','.') }}</div>
                @if(!is_null($p->stok))
                  <div class="produk-stock" style="font-size:12px; color:#64748b;">Stok: {{ $p->stok }}</div>
                @endif
              </div>
            </div>
            <div class="produk-qty">
              <button type="button" class="btn btn-primary btn-sm btn-tambah" data-id="{{ $p->id }}">Tambah</button>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Kanan: Keranjang + Pembayaran --}}
    <div class="card card--sticky">
      <div class="card-header">
        <div class="card-title">
          <h3>Pembayaran</h3>
          <p>Pilih metode pembayaran</p>
        </div>
      </div>

      <div class="card-subtitle" style="font-size:14px; color:#6b7280; margin-bottom:8px;">Keranjang</div>

      <div id="ringkasanKeranjang" class="order-list" style="max-height:320px; overflow:auto; margin-bottom:12px;">
        <p class="text-muted">Memuat keranjang…</p>
      </div>

      <div class="order-footer" style="margin-bottom:12px; display:flex; align-items:center; gap:8px; justify-content:space-between;">
        <span class="order-total">Total: <span id="grandTotal">Rp 0</span></span>
        <button type="button" class="btn btn-sm" id="btnKosongkan">Kosongkan</button>
      </div>

      {{-- Nama pelanggan --}}
      <div class="kasir-form-inline" style="margin-bottom:8px;">
        <input type="text" name="customer_name" class="form-input"
               value="{{ $pendingName ?? '' }}"
               placeholder="Nama pelanggan (opsional)">
      </div>

      <div class="kasir-form-inline" style="margin-bottom:12px;">
        <select name="metode" class="form-input">
          <option value="qris">QRIS (disarankan)</option>
          <option value="va_bca">VA BCA</option>
          <option value="va_bri">VA BRI</option>
          <option value="va_bni">VA BNI</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Proses Pembayaran</button>

      {{-- Badge status + nama pelanggan saat pending --}}
      <div id="statusWrap" style="display:none; margin-top:10px;">
        <span id="statusBadge"
              style="padding:6px 10px; border-radius:999px; font-weight:600; font-size:13px; background:#fff3cd; color:#7a5a00;">
          Menunggu pembayaran…
        </span>
        <span id="statusInfo" style="margin-left:8px; font-size:13px; color:#334155;"></span>
      </div>
    </div>
  </div>
</form>

{{-- ===== JS ===== --}}
<script>
$(function(){
  const wadahKeranjang = $('#ringkasanKeranjang');
  const elTotal = $('#grandTotal');
  const wadahProduk = $('#gridProduk');
  const statusWrap = $('#statusWrap');
  const statusBadge = $('#statusBadge');
  const statusInfo = $('#statusInfo');
  const inputNama = $('[name=customer_name]');

  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  function renderKeranjang(data){
    wadahKeranjang.html('');
    if(!data.items || data.items.length===0){
      wadahKeranjang.html('<p class="text-muted">Belum ada item dipilih.</p>');
      elTotal.text('Rp 0');
      return;
    }
    data.items.forEach(i=>{
      wadahKeranjang.append(`
        <div class="order-item">
          <div class="order-header">
            <div>
              <div class="order-id">${i.name}</div>
              <div class="order-customer">x${i.qty} • ${i.price_text}</div>
            </div>
            <div>
              <button type="button" class="btn btn-sm aksi" data-act="kurang" data-id="${i.produk_id}">−</button>
              <button type="button" class="btn btn-sm aksi" data-act="tambah" data-id="${i.produk_id}">+</button>
              <button type="button" class="btn btn-sm aksi" data-act="hapus"  data-id="${i.produk_id}">Hapus</button>
            </div>
          </div>
          <div class="order-footer">
            <span class="order-total">${i.line_total_text}</span>
          </div>
        </div>
      `);
    });
    elTotal.text(data.subtotal_text);
  }

  // Tambah/kurang/hapus/kosongkan
  wadahProduk.on('click','.btn-tambah',e=>{
    $.post('{{ route('kasir.cart.tambah') }}',{produk_id:$(e.currentTarget).data('id')},renderKeranjang);
  });
  $('#ringkasanKeranjang').on('click','.aksi',function(){
    const id=$(this).data('id'), act=$(this).data('act');
    let url='', method='POST', payload={produk_id:id};
    if(act==='tambah') url='{{ route('kasir.cart.tambah') }}';
    if(act==='kurang') url='{{ route('kasir.cart.kurang') }}';
    if(act==='hapus'){ url='{{ route('kasir.cart.hapus') }}'; method='DELETE'; }
    $.ajax({url, type:method, data:payload, success:renderKeranjang});
  });
  $('#btnKosongkan').on('click',()=>$.post('{{ route('kasir.cart.kosongkan') }}',{},renderKeranjang));

  // Search + filter kategori
  function applyFilter(){
    const q = ($('#cariProduk').val()||'').trim().toLowerCase();
    const k = ($('#filterKategori').val()||'').trim().toLowerCase();
    $('.produk-card').each(function(){
      const n = ($(this).data('name')||'');
      const c = ($(this).data('kategori')||'');
      const matchName = n.includes(q);
      const matchCat  = k ? (c === k) : true;
      $(this).toggle(matchName && matchCat);
    });
  }
  $('#cariProduk').on('input', applyFilter);
  $('#filterKategori').on('change', applyFilter);

  // Load awal keranjang
  $.get('{{ route('kasir.cart.data') }}',renderKeranjang);

  // ===== Polling status (pakai ORDER ID) =====
  let flashOrderId = {!! json_encode(session('order_id')) !!};
  let orderId = flashOrderId || localStorage.getItem('last_order_id');
  if (flashOrderId) localStorage.setItem('last_order_id', flashOrderId);

  if (orderId) {
    statusWrap.show();
    statusBadge.text('Menunggu pembayaran…')
               .css({background:'#fff3cd', color:'#7a5a00'});
    const pendingName = {!! json_encode($pendingName ?? null) !!};
    if (pendingName) statusInfo.text('Atas nama: ' + pendingName);

    function cekStatus(){
      $.get("{{ url('/kasir/orders') }}/"+orderId)
        .done(function(d){
          if (d.status === 'paid') {
            statusBadge.text('Lunas ✅').css({background:'#dcfce7', color:'#14532d'});
            $.post('{{ route('kasir.cart.kosongkan') }}',{}, function(data){
              renderKeranjang(data);
              inputNama.val('');
              statusInfo.text('');
            });
            localStorage.removeItem('last_order_id');
            return;
          }
          if (d.status === 'expired') {
            statusBadge.text('Kedaluwarsa ❌').css({background:'#fee2e2', color:'#7f1d1d'});
            localStorage.removeItem('last_order_id'); return;
          }
          if (d.status === 'cancelled') {
            statusBadge.text('Dibatalkan ❌').css({background:'#fee2e2', color:'#7f1d1d'});
            localStorage.removeItem('last_order_id'); return;
          }
          setTimeout(cekStatus, 1800);
        })
        .fail(()=> setTimeout(cekStatus, 2500));
    }
    cekStatus();
  }
});
</script>
@endsection
