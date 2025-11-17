@extends('layouts.main')
@section('title', 'Kasir')

@section('content')
<form action="{{ route('kasir.store') }}" method="POST" id="formPembayaran" autocomplete="off">
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
              <button type="button" class="btn btn-primary btn-sm btn-tambah"
                      data-id="{{ $p->id }}"
                      data-name="{{ $p->nama_barang }}"
                      data-price="{{ (int)$p->harga }}">Tambah</button>
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

      {{-- Metode bayar --}}
      <div class="kasir-form-inline" style="margin-bottom:12px;">
        <select name="metode" id="metodeBayar" class="form-input">
          <option value="qris">QRIS (disarankan)</option>
          <option value="va_bca">VA BCA</option>
          <option value="va_bri">VA BRI</option>
          <option value="va_bni">VA BNI</option>
          <option value="cash">CASH</option>
        </select>
      </div>

      {{-- Panel CASH --}}
      <div id="panelCash" style="display:none; margin-bottom:12px;">
        <div class="kr-card" style="padding:12px; border:1px dashed #d1d5db; border-radius:12px;">
          <div style="display:grid; grid-template-columns: 1fr auto; gap:8px; align-items:center;">
            <div>Total</div>
            <div style="font-weight:700;" id="cashTotal">Rp 0</div>

            <div>Uang diterima</div>
            <div>
              <input type="text" id="inputCash" class="form-input" placeholder="Rp 0" inputmode="numeric" autocomplete="off" style="text-align:right; min-width:180px;">
              <input type="hidden" name="cash_tendered" id="cashTenderedRaw" value="0">
            </div>

            <div>Kembalian</div>
            <div style="font-weight:700;" id="cashChange">Rp 0</div>
          </div>

          <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:6px;">
            <button type="button" class="btn btn-sm btn-quick" data-amt="10000">10.000</button>
            <button type="button" class="btn btn-sm btn-quick" data-amt="20000">20.000</button>
            <button type="button" class="btn btn-sm btn-quick" data-amt="50000">50.000</button>
            <button type="button" class="btn btn-sm btn-quick" data-amt="100000">100.000</button>
            <button type="button" class="btn btn-sm" id="btnPas">Pas</button>
            <button type="button" class="btn btn-sm" id="btnClearCash">Clear</button>
          </div>

          <small id="cashHint" class="text-muted" style="display:block; margin-top:8px;"></small>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="btnProses">Proses Pembayaran</button>

      {{-- Badge status --}}
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

<script>
$(function(){
  const wadahKeranjang = $('#ringkasanKeranjang');
  const elTotal = $('#grandTotal');
  const wadahProduk = $('#gridProduk');
  const statusWrap = $('#statusWrap');
  const statusBadge = $('#statusBadge');
  const statusInfo = $('#statusInfo');
  const inputNama = $('[name=customer_name]');
  const metodeBayar = $('#metodeBayar');
  const panelCash = $('#panelCash');
  const cashTotal = $('#cashTotal');
  const cashChange = $('#cashChange');
  const inputCash = $('#inputCash');
  const cashTenderedRaw = $('#cashTenderedRaw');
  const cashHint = $('#cashHint');
  const btnProses = $('#btnProses');

  // Kode aktif dari server/localStorage
  const serverActiveCode = {!! json_encode($activeCode ?? null) !!};
  let salesCode = serverActiveCode || localStorage.getItem('last_sales_code');
  if (serverActiveCode) localStorage.setItem('last_sales_code', serverActiveCode);

  // ===== State keranjang
  let CART = { items: [], subtotal: 0, subtotal_text: 'Rp 0' };

  function formatRupiah(n){ n=parseInt(n||0,10); return 'Rp ' + n.toLocaleString('id-ID'); }
  function parseRupiahToInt(s){ s=String(s||'').replace(/[^\d]/g,''); return parseInt(s||'0',10); }

  $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, cache:false });

  function renderKeranjang(data){
    if (data) CART = { ...CART, ...data };
    wadahKeranjang.html('');
    if(!CART.items || CART.items.length===0){
      wadahKeranjang.html('<p class="text-muted">Belum ada item dipilih.</p>');
      elTotal.text('Rp 0'); updateCashPanel(); return;
    }
    CART.items.forEach(i=>{
      wadahKeranjang.append(`
        <div class="order-item">
          <div class="order-header">
            <div>
              <div class="order-id">${i.name}</div>
              <div class="order-customer">x${i.qty} • ${formatRupiah(i.price)}</div>
            </div>
            <div>
              <button type="button" class="btn btn-sm aksi" data-act="kurang" data-id="${i.produk_id}">−</button>
              <button type="button" class="btn btn-sm aksi" data-act="tambah" data-id="${i.produk_id}">+</button>
              <button type="button" class="btn btn-sm aksi" data-act="hapus"  data-id="${i.produk_id}">Hapus</button>
            </div>
          </div>
          <div class="order-footer"><span class="order-total">${formatRupiah(i.price * i.qty)}</span></div>
        </div>`);
    });
    elTotal.text(formatRupiah(CART.subtotal));
    updateCashPanel();
  }

  function optimisticUpdate(act, payload){
    const before = JSON.parse(JSON.stringify(CART));
    const map = {}; (CART.items||[]).forEach(it=>map[it.produk_id]=it);
    const id=payload.produk_id, name=payload.name, price=payload.price;

    if (act==='tambah'){ if(!map[id]) map[id]={produk_id:id,name,price,qty:0}; map[id].qty++; }
    if (act==='kurang'){ if(map[id]){ map[id].qty=Math.max(0,map[id].qty-1); if(map[id].qty===0) delete map[id]; } }
    if (act==='hapus'){ if(map[id]) delete map[id]; }
    if (act==='kosongkan'){ for(const k in map) delete map[k]; }

    CART.items = Object.values(map);
    CART.subtotal = CART.items.reduce((a,b)=>a+(b.price*b.qty),0);
    CART.subtotal_text = formatRupiah(CART.subtotal);
    renderKeranjang();

    return ()=>{ CART = before; renderKeranjang(); };
  }

  // Tambah/kurang/hapus/kosongkan
  wadahProduk.on('click','.btn-tambah',e=>{
    const id = +$(e.currentTarget).data('id');
    const name = $(e.currentTarget).data('name');
    const price = +$(e.currentTarget).data('price');
    const rb = optimisticUpdate('tambah',{produk_id:id,name,price});
    $.post('{{ route('kasir.cart.tambah') }}',{produk_id:id}).done(renderKeranjang).fail(rb);
  });
  $('#ringkasanKeranjang').on('click','.aksi',function(){
    const id=+$(this).data('id'), act=String($(this).data('act'));
    const ex=(CART.items||[]).find(i=>i.produk_id===id)||{};
    const rb=optimisticUpdate(act,{produk_id:id,name:ex.name,price:ex.price||0});
    let url='',method='POST',data={produk_id:id};
    if(act==='tambah') url='{{ route('kasir.cart.tambah') }}';
    if(act==='kurang') url='{{ route('kasir.cart.kurang') }}';
    if(act==='hapus'){ url='{{ route('kasir.cart.hapus') }}'; method='DELETE'; }
    $.ajax({url,type:method,data}).done(renderKeranjang).fail(rb);
  });
  $('#btnKosongkan').on('click',()=>{
    const rb=optimisticUpdate('kosongkan',{});
    $.post('{{ route('kasir.cart.kosongkan') }}',{}).done(renderKeranjang).fail(rb);
  });

  // Filter
  function applyFilter(){
    const q=($('#cariProduk').val()||'').trim().toLowerCase();
    const k=($('#filterKategori').val()||'').trim().toLowerCase();
    $('.produk-card').each(function(){
      const n=($(this).data('name')||''), c=($(this).data('kategori')||'');
      $(this).toggle(n.includes(q) && (!k || c===k));
    });
  }
  $('#cariProduk').on('input',applyFilter);
  $('#filterKategori').on('change',applyFilter);

  // Load awal keranjang
  $.get('{{ route('kasir.cart.data') }}',d=>{ CART={...CART,...d}; renderKeranjang(); });

  // CASH panel
  function updateCashPanel(){
    cashTotal.text(formatRupiah(CART.subtotal));
    const bayar = parseInt(cashTenderedRaw.val()||'0',10);
    const selisih = bayar - CART.subtotal;
    cashChange.text(formatRupiah(Math.max(0, selisih)));
    if (metodeBayar.val()==='cash') {
      if (bayar < CART.subtotal) { cashHint.text('Uang kurang ' + formatRupiah(CART.subtotal - bayar)); btnProses.prop('disabled', true); }
      else { cashHint.text('Siap proses. Kembalian: ' + formatRupiah(selisih)); btnProses.prop('disabled', false); }
    } else {
      btnProses.prop('disabled', false);
    }
  }
  function showHideCash(){ panelCash.toggle(metodeBayar.val()==='cash'); updateCashPanel(); }
  metodeBayar.on('change',showHideCash); showHideCash();

  inputCash.on('input', function(){
    const v=parseRupiahToInt($(this).val()); cashTenderedRaw.val(String(v)); $(this).val(formatRupiah(v)); updateCashPanel();
  });
  $('.btn-quick').on('click', function(){
    const add=+$(this).data('amt'), cur=+(cashTenderedRaw.val()||'0'); const next=cur+add;
    cashTenderedRaw.val(String(next)); inputCash.val(formatRupiah(next)); updateCashPanel();
  });
  $('#btnPas').on('click', ()=>{ const t=CART.subtotal||0; cashTenderedRaw.val(String(t)); inputCash.val(formatRupiah(t)); updateCashPanel(); });
  $('#btnClearCash').on('click', ()=>{ cashTenderedRaw.val('0'); inputCash.val(''); updateCashPanel(); });

  // ===== Polling status (TIDAK tergantung pilihan dropdown)
  let pollTimer=null, pollCount=0;

  function startPolling(kode){
    if (!kode) return;

    statusWrap.show();
    statusBadge.text('Menunggu pembayaran…').css({background:'#fff3cd', color:'#7a5a00'});
    const pendingName = {!! json_encode($pendingName ?? null) !!};
    if (pendingName) statusInfo.text('Atas nama: ' + pendingName);

    function selesaiUI(text,bg,fg){
      statusBadge.text(text).css({background:bg,color:fg});
      $.post('{{ route('kasir.cart.kosongkan') }}',{},data=>{
        renderKeranjang(data); inputNama.val(''); statusInfo.text('');
      }).always(()=>{ statusWrap.hide(); try{ localStorage.removeItem('last_sales_code'); }catch(e){} });
    }

    function cek(){
      $.ajax({
        url: "{{ url('/kasir/status') }}/" + encodeURIComponent(kode),
        type:'GET', data:{ _:Date.now() }, cache:false
      }).done(function(d){
        const st=(d&&d.status)?String(d.status).toLowerCase():'pending';
        if (st==='paid'){ selesaiUI('Lunas ✅','#dcfce7','#14532d'); clearTimeout(pollTimer); return; }
        if (st==='expired' || st==='cancelled'){ selesaiUI(st==='expired'?'Kedaluwarsa ❌':'Dibatalkan ❌','#fee2e2','#7f1d1d'); clearTimeout(pollTimer); return; }
        pollCount++; pollTimer=setTimeout(cek,1500);
      }).fail(function(){ pollTimer=setTimeout(cek,2000); });
    }

    clearTimeout(pollTimer); pollCount=0; cek();
  }

  if (salesCode) startPolling(salesCode);

  document.addEventListener('visibilitychange', function(){
    if (document.visibilityState==='visible') {
      const code = localStorage.getItem('last_sales_code');
      if (code) startPolling(code);
    }
  });

  // Anti double submit
  $('#formPembayaran').on('submit', function(){ $('#btnProses').prop('disabled',true).text('Memproses…'); });
});
</script>
@endsection
