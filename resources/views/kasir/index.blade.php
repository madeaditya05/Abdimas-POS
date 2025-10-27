@extends('layouts.main')
@section('title', 'Kasir')

@section('content')


<form action="{{ route('kasir.store') }}" method="POST">
  @csrf

  <div class="grid-kasir">
    {{-- Kolom kiri: Produk --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <h3>Produk</h3>
          <p>Pilih item untuk ditambahkan ke keranjang</p>
        </div>
      </div>

      <div class="produk-toolbar">
        <input type="text" id="cariProduk" class="form-input" placeholder="Cari produk…">
      </div>

      <div class="produk-grid" id="gridProduk">
        @foreach($products as $p)
          <div class="produk-card"
               data-id="{{ $p->id }}"
               data-name="{{ Str::lower($p->name) }}"
               data-price="{{ $p->price }}">
            <div class="produk-main">
              <div class="produk-avatar">{{ strtoupper(mb_substr($p->name,0,1)) }}</div>
              <div class="produk-info">
                <div class="produk-name">{{ $p->name }}</div>
                <div class="produk-price">Rp {{ number_format($p->price,0,',','.') }}</div>
              </div>
            </div>
            <div class="produk-qty">
              <button type="button" class="btn btn-primary btn-sm btn-tambah" data-id="{{ $p->id }}">Tambah</button>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Kolom kanan: Keranjang + Pembayaran --}}
    <div class="card card--sticky">
      <div class="card-header">
        <div class="card-title">
          <h3>Pembayaran</h3>
          <p>Pilih metode pembayaran</p>
        </div>
      </div>

      <div class="card-subtitle" style="font-size:14px; color:#6b7280; margin-bottom:8px;">Keranjang</div>

      <div id="ringkasanKeranjang" class="order-list" style="max-height:320px; overflow:auto; margin-bottom:12px;">
        <p class="text-muted" style="color:#6b7280;">Memuat keranjang…</p>
      </div>

      <div class="order-footer" style="margin-bottom:12px;">
        <span class="order-total">Total: <span id="grandTotal">Rp 0</span></span>
        <button type="button" class="btn btn-sm" id="btnKosongkan">Kosongkan</button>
      </div>

      <div class="kasir-form-inline" style="margin-bottom:12px;">
        <select name="metode" class="form-input">
          <option value="qris">QRIS (disarankan)</option>
          <option value="va_bca">VA BCA</option>
          <option value="va_bri">VA BRI</option>
          <option value="va_bni">VA BNI</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-block">
  Proses Pembayaran
</button>
      {{-- di card Pembayaran, letakkan di bawah tombol "Proses Pembayaran" --}}
<p id="indikatorTransaksi" class="demo-info" style="margin-top:8px;display:none;"></p>
    </div>
  </div>
</form>

{{-- ===== JS minimal: hanya panggil API & render ===== --}}
<script>
// $(document).ready(...) artinya:
// "Jalankan semua kode di dalam ini HANYA JIKA halaman HTML sudah 100% siap"
$(document).ready(function() {

  // === BAGIAN 1: PERSIAPAN ===

  // Ambil elemen-elemen penting (versi jQuery)
  const wadahKeranjang = $('#ringkasanKeranjang');
  const elTotal = $('#grandTotal');
  const inputCari = $('#cariProduk');
  const wadahProduk = $('#gridProduk');
  
  // Ambil Token CSRF
  const CSRF = $('meta[name="csrf-token"]').attr('content');

  // Perintah AJAIB:
  // "Tolong, setiap kali jQuery kirim data (POST/DELETE),
  // selalu sertakan 'Stempel Resmi' (CSRF) ini."
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': CSRF }
  });

  // === BAGIAN 2: FUNGSI "MENGGAMBAR" ===
  
  /**
   * FUNGSI MENGGAMBAR KERANJANG (Versi jQuery)
   * Tugasnya sama: 'menggambar' data di papan keranjang.
   */
  function renderKeranjang(data) {
    // 1. Bersihkan papan tulis
    wadahKeranjang.html(''); // Dulu: .innerHTML = ''

    // 2. Cek jika datanya kosong
    if (!data.items || data.items.length === 0) {
      wadahKeranjang.html('<p class="text-muted" style="color:#6b7280;">Belum ada item dipilih.</p>');
      elTotal.text('Rp 0'); // Dulu: .textContent = ''
      return; // Selesai
    }

    // 3. Jika ada data, tulis ulang satu per satu
    data.items.forEach(i => {
      // Kita tidak perlu 'createElement', kita bisa langsung 'append' (tambahkan)
      // string HTML-nya. Lebih cepat!
      const htmlString = `
        <div class="order-item">
          <div class="order-header">
            <div>
              <div class="order-id">${i.name}</div>
              <div class="order-customer">x${i.qty} • ${i.price_text}</div>
            </div>
            <div>
              <button type="button" class="btn btn-sm aksi" data-act="kurang" data-id="${i.product_id}">−</button>
              <button type="button" class="btn btn-sm aksi" data-act="tambah" data-id="${i.product_id}">+</button>
              <button type="button" class="btn btn-sm aksi" data-act="hapus"  data-id="${i.product_id}">Hapus</button>
            </div>
          </div>
          <div class="order-footer">
            <span class="order-total">${i.line_total_text}</span>
            <span class="order-time"></span>
          </div>
        </div>
      `;
      wadahKeranjang.append(htmlString); // Langsung tambahkan
    });

    // 4. Update total harga
    elTotal.text(data.subtotal_text);

    // TIDAK PERLU 'addEventListener' DI SINI LAGI! (Ini kuncinya)
  }

  // === BAGIAN 3: "MEMASANG TELINGA" (Event Listeners) ===
  
  // Telinga 1: Di Wadah Produk (Kolom Kiri)
  // "Hei #gridProduk, kalau ada yang klik tombol '.btn-tambah' DI DALAM dirimu,
  // jalankan fungsi ini." (Ini namanya Event Delegation)
  wadahProduk.on('click', '.btn-tambah', function() {
    const id = $(this).data('id'); // Ambil 'data-id'

    // Kirim data ke Dapur (Controller)
    // $.post(URL, DATA_YG_DIKIRIM, FUNGSI_SETELAH_DAPAT_JAWABAN)
    $.post('{{ route('kasir.cart.tambah') }}', { product_id: id }, function(data) {
      // 'data' adalah jawaban JSON dari controller
      renderKeranjang(data); // Langsung gambar ulang
    });
  });

  // Telinga 2: Di Wadah Keranjang (Kolom Kanan)
  // "Hei #ringkasanKeranjang, kalau ada yang klik tombol '.aksi' DI DALAM dirimu..."
  wadahKeranjang.on('click', '.aksi', function() {
    const id = $(this).data('id');
    const act = $(this).data('act');
    
    let url = '';
    let method = 'POST'; // Default

    if (act === 'tambah') {
      url = '{{ route('kasir.cart.tambah') }}';
    } else if (act === 'kurang') {
      url = '{{ route('kasir.cart.kurang') }}';
    } else if (act === 'hapus') {
      url = '{{ route('kasir.cart.hapus') }}';
      method = 'DELETE'; // Khusus hapus, pakai DELETE
    }
    
    // Ini '$.ajax', versi lebih lengkap dari '$.post'
    // Kita pakai ini karena ada method 'DELETE'
    $.ajax({
      url: url,
      type: method, // 'POST' atau 'DELETE'
      data: { product_id: id },
      success: function(data) {
        // 'success' sama kayak 'function(data)' di $.post
        renderKeranjang(data);
      }
    });
  });

  // Telinga 3: Di Tombol "Kosongkan"
  $('#btnKosongkan').on('click', function() {
    $.post('{{ route('kasir.cart.kosongkan') }}', {}, function(data) {
      renderKeranjang(data);
    });
  });

  // Telinga 4: Di Kotak Pencarian
  inputCari.on('input', function() {
    const q = $(this).val().trim().toLowerCase();
    
    // "Untuk setiap '.produk-card'..."
    $('.produk-card').each(function() {
      const name = $(this).data('name') || '';
      
      // Versi jQuery dari 'sembunyi/tampil'
      if (name.includes(q)) {
        $(this).show(); // Dulu: .style.display = ''
      } else {
        $(this).hide(); // Dulu: .style.display = 'none'
      }
    });
  });

  // === BAGIAN 4: KODE YANG JALAN SAAT HALAMAN DIBUKA ===

  // 1. Muat Keranjang Awal
  // Ini 'GET' (minta data), jadi kita pakai '$.get'
  $.get('{{ route('kasir.cart.data') }}', function(dataAwal) {
    renderKeranjang(dataAwal);
  }).fail(function() {
    // 'fail' jalan kalau ada error
    wadahKeranjang.html('<p style="color:red;">Gagal memuat keranjang.</p>');
  });

  
  // 2. Script Pengecekan Status Bayar (juga pakai jQuery)
  @if (session('order_id'))
    const lbl = $('#indikatorTransaksi');
    lbl.show(); // Tampilkan indikator
    let done = false;

    function cekStatus() {
      if (done) return; // Berhenti jika sudah
      
      // Minta status ke Dapur (Controller)
      $.get("{{ route('kasir.orders.show', ['order' => session('order_id')]) }}")
        .done(function(d) { // '.done' = 'success'
          if (d.status === 'paid') {
            lbl.text('Status: Lunas ✅');
            done = true;
            // Kosongkan keranjang di server & gambar ulang
            $.post('{{ route('kasir.cart.kosongkan') }}', {}, function(data) {
              renderKeranjang(data);
            });
            return;
          }
          
          if (d.status === 'expired')   { lbl.text('Status: Kedaluwarsa ❌'); done = true; return; }
          if (d.status === 'cancelled') { lbl.text('Status: Dibatalkan ❌');  done = true; return; }

          lbl.text('Status: Menunggu pembayaran…');
          setTimeout(cekStatus, 1500); // Ulangi
        })
        .fail(function() { // '.fail' = 'error'
          setTimeout(cekStatus, 2500); // Coba lagi nanti
        });
    }

    cekStatus(); // Mulai cek pertama kali
  @endif

}); // Penutup dari $(document).ready
</script>
@endsection
