@extends('layouts.main')
@section('title', 'Kasir')

@push('styles')
<style>
  .produk-card.is-disabled {
    opacity: .68;
    border-style: dashed;
    background: #f8fafc;
  }

  .produk-card.is-disabled:hover {
    transform: none;
    box-shadow: none;
  }

  .produk-card .produk-meta {
    margin-top: 6px;
    font-size: 12px;
    color: #64748b;
  }

  .produk-card .produk-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border-radius: 999px;
    font-weight: 600;
    background: #dcfce7;
    color: #166534;
  }

  .produk-card .produk-status.is-off {
    background: #fee2e2;
    color: #991b1b;
  }

  /* Tablet layout overrides to optimize space */
  @media (max-width: 1200px) {
    .dashboard-main {
      padding: 12px !important;
    }
  }

  @media (min-width: 901px) and (max-width: 1200px) {
    .kasir-pos-grid {
      grid-template-columns: minmax(0, 1fr) 310px !important;
      gap: 12px !important;
    }
    .produk-grid--img {
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important;
      gap: 10px !important;
    }
    .produk-card--img {
      min-height: 180px !important;
    }
    .produk-body {
      padding: 8px !important;
      min-height: 80px !important;
    }
    .produk-name {
      font-size: 12px !important;
      line-height: 1.2 !important;
      margin-bottom: 4px !important;
    }
    .produk-price {
      font-size: 11px !important;
      margin-bottom: 4px !important;
    }
    .produk-kat {
      font-size: 10px !important;
    }
    .order-item {
      padding: 10px !important;
    }
    .order-header {
      margin-bottom: 6px !important;
    }
    .order-item .btn-sm {
      padding: 4px 6px !important;
      font-size: 11px !important;
    }
    .produk-toolbar {
      grid-template-columns: minmax(0, 1fr) 140px !important;
      gap: 8px !important;
    }
    .kategori-pills {
      gap: 6px !important;
      margin-bottom: 8px !important;
    }
    .kategori-pill {
      padding: 7px 10px !important;
      font-size: 11px !important;
    }
  }

  /* Channel Switch styling */
  .channel-toggle-wrapper {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border-radius: 999px;
    padding: 3px;
    gap: 4px;
    border: 1px solid #e2e8f0;
  }
  .btn-channel-toggle {
    border: none;
    background: transparent;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
  }
  .btn-channel-toggle:hover {
    color: #334155;
  }
  .btn-channel-toggle.active {
    background: #ffffff;
    color: #10b981; /* Emerald/Green representing active */
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
  }
</style>
@endpush

@section('content')
@php
  $kategoriLabels = $kategoris->pluck('nama', 'slug');
@endphp

<form action="{{ route('kasir.store') }}" method="POST" id="formPembayaran" class="kasir-page" autocomplete="off">
  @csrf

  <div class="grid-kasir kasir-pos-grid">
    {{-- Kiri: Produk --}}
    <div class="card kasir-menu-card">
      <div class="card-header kasir-menu-head">
        <div class="card-title">
          <h3>Produk</h3>
          <p>Pilih item untuk ditambahkan ke keranjang</p>
        </div>
        <div class="channel-toggle-wrapper">
          <button type="button" class="btn-channel-toggle {{ session('pos_channel', 'offline') === 'offline' ? 'active' : '' }}" data-channel="offline">
            🏪 Offline
          </button>
          <button type="button" class="btn-channel-toggle {{ session('pos_channel') === 'online' ? 'active' : '' }}" data-channel="online">
            🌐 Online
          </button>
        </div>
        <span class="menu-count" id="menuCount">{{ $produks->count() }} menu</span>
      </div>

      <div class="produk-toolbar">
        <input type="text" id="cariProduk" class="form-input" placeholder="Cari produk…">
        <select id="filterKategori" class="form-input">
          <option value="">Semua kategori</option>
          @foreach($kategoris as $kategori)
            <option value="{{ Str::lower($kategori->slug) }}">{{ $kategori->nama }}</option>
          @endforeach
        </select>
      </div>

      <div class="kategori-pills" id="kategoriPills" aria-label="Filter kategori produk">
        <button type="button" class="kategori-pill is-active" data-kategori="">Semua</button>
        @foreach($kategoris as $kategori)
          <button type="button" class="kategori-pill" data-kategori="{{ Str::lower($kategori->slug) }}">
            {{ $kategori->nama }}
          </button>
        @endforeach
      </div>

      {{-- Grid Produk bergambar --}}
      <div class="produk-scroll">
        <div class="produk-grid produk-grid--img" id="gridProduk">
          @foreach($produks as $p)
            @php
              $img = null;

              // kalau kamu cuma pakai kolom `gambar` dari DB (produk/latte.jpg)
              if (!empty($p->gambar)) {
                $img = asset('storage/' . ltrim($p->gambar, '/'));
              }

              // kalau suatu saat kamu juga punya image_url, boleh taruh di atas ini
              // if (!empty($p->image_url)) $img = $p->image_url;

              $initial = strtoupper(mb_substr($p->nama_barang,0,1));
              $kategoriLabel = $kategoriLabels[$p->kategori] ?? \Illuminate\Support\Str::of((string) $p->kategori)->replace('_', ' ')->title();
              $activeChannel = session('pos_channel', 'offline');
              $initialPrice = $activeChannel === 'online' ? ($p->harga_online ?? $p->harga) : $p->harga;
            @endphp

            <button
              type="button"
              class="produk-card produk-card--img"
              data-id="{{ $p->id }}"
              data-name="{{ Str::lower($p->nama_barang) }}"
              data-realname="{{ $p->nama_barang }}"
              data-price="{{ (int)$p->harga }}"
              data-price-online="{{ (int)($p->harga_online ?? $p->harga) }}"
              data-kategori="{{ Str::lower($p->kategori ?? '') }}"
              style="text-align:left;"
            >
              <div class="produk-thumb">
                @if($img)
                  <img src="{{ $img }}" alt="{{ $p->nama_barang }}" loading="lazy">
                @else
                  <div class="produk-thumb--ph">{{ $initial }}</div>
                @endif

                @if(!is_null($p->stok))
                  <span class="badge-stok {{ $p->stok <= 0 ? 'badge-stok--habis' : '' }}">
                    Stok: {{ $p->stok }}
                  </span>
                @endif

                <span class="badge-tap">+</span>
              </div>

              <div class="produk-body">
                <div class="produk-name" title="{{ $p->nama_barang }}">{{ $p->nama_barang }}</div>
                <div class="produk-price">Rp {{ number_format($initialPrice,0,',','.') }}</div>
                @if(!empty($p->kategori))
                  <div class="produk-kat">{{ $kategoriLabel }}</div>
                @endif
              </div>
            </button>
          @endforeach
        </div>
        <div class="produk-empty" id="produkEmpty" style="display:none;">Tidak ada menu yang cocok.</div>
      </div>
    </div>

    {{-- Kanan: Keranjang + pencatatan --}}
    <div class="card card--sticky kasir-payment-card" id="kasirPaymentPanel" aria-label="Keranjang dan pencatatan penjualan">
      <div class="card-header kasir-payment-head">
        <div class="card-title">
          <h3>Catat Penjualan</h3>
          <p>Simpan transaksi tanpa display pembayaran</p>
        </div>
        <button type="button" class="kasir-drawer-close" id="btnCloseCart" aria-label="Tutup keranjang">&times;</button>
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
               required
               placeholder="Nama pelanggan (wajib)">
      </div>

      <div id="customerDiscountBox" style="display:none; margin-bottom:12px; padding:10px 12px; border:1px solid #d1fae5; border-radius:8px; background:#f0fdf4; color:#14532d; font-size:12.5px;">
        <div style="display:flex; justify-content:space-between; gap:10px; align-items:center;">
          <span id="customerDiscountText">Diskon customer</span>
          <strong id="customerDiscountAmount">Rp 0</strong>
        </div>
      </div>

      {{-- Metode pencatatan --}}
      <div class="kasir-form-inline" style="margin-bottom:12px;">
        <select name="metode" id="metodeBayar" class="form-input">
          <option value="cash">Tunai</option>
          <option value="qris">QRIS</option>
          <option value="transfer">Transfer</option>
          <option value="debit">Debit / kartu</option>
          <option value="tempo">Piutang / invoice</option>
        </select>
      </div>

      {{-- Panel CASH --}}
      <div id="panelCash" style="display:none; margin-bottom:12px;">
        <div class="kr-card" style="padding:12px; border:1px dashed #d1d5db; border-radius:12px; background: #fafafa;">
          <div style="font-weight:700; margin-bottom:10px; font-size: 13.5px; color: #1e293b;">Rincian Pembayaran Tunai</div>
          
          <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px; color:#475569;">
            <span>Total Tagihan:</span>
            <strong id="cashTotal" style="color:#0f172a;">Rp 0</strong>
          </div>

          <div class="kasir-form-inline" style="margin-bottom:8px; position:relative;">
            <label style="display:block; font-size:12px; color:#64748b; margin-bottom:4px;">Uang Diterima:</label>
            <input type="text" id="inputCash" class="form-input" placeholder="Rp 0" style="font-weight:700; font-size:15px; color:#0f766e;">
            <input type="hidden" name="bayar" id="cashTenderedRaw" value="0">
          </div>

          <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:13px; color:#475569;">
            <span>Kembalian:</span>
            <strong id="cashChange" style="color:#0f766e; font-size:15px;">Rp 0</strong>
          </div>

          <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:6px; margin-bottom:8px;">
            <button type="button" class="btn btn-sm btn-quick" data-amt="10000" style="font-size:12px; padding:6px 4px; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:6px; cursor:pointer;">10K</button>
            <button type="button" class="btn btn-sm btn-quick" data-amt="20000" style="font-size:12px; padding:6px 4px; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:6px; cursor:pointer;">20K</button>
            <button type="button" class="btn btn-sm btn-quick" data-amt="50000" style="font-size:12px; padding:6px 4px; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:6px; cursor:pointer;">50K</button>
            <button type="button" class="btn btn-sm btn-quick" data-amt="100000" style="font-size:12px; padding:6px 4px; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:6px; cursor:pointer;">100K</button>
            <button type="button" class="btn btn-sm" id="btnPas" style="font-size:12px; padding:6px 4px; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:6px; cursor:pointer;">Uang Pas</button>
            <button type="button" class="btn btn-sm" id="btnClearCash" style="font-size:12px; padding:6px 4px; border:1px solid #fee2e2; background:#fff; color:#991b1b; border-radius:6px; cursor:pointer;">Hapus</button>
          </div>

          <small id="cashHint" class="text-muted" style="display:block; font-size:12px; color:#ef4444; font-weight:500;"></small>
        </div>
      </div>

      {{-- Panel TEMPO --}}
      <div id="panelTempo" style="display:none; margin-bottom:12px;">
        <div class="kr-card" style="padding:12px; border:1px dashed #d1d5db; border-radius:12px;">
          <div style="font-weight:700; margin-bottom:8px; font-size: 13.5px; color: #1e293b;">Bayar Nanti (Tempo)</div>

          <div class="kasir-form-inline" style="margin-bottom:8px;">
            <input type="text" name="invoice_to_company" class="form-input"
                   value="{{ old('invoice_to_company') }}"
                   placeholder="Nama perusahaan (opsional)">
          </div>

          <div class="kasir-form-inline" style="margin-bottom:8px;">
            <input type="date" name="tempo_due_date" class="form-input"
                   value="{{ old('tempo_due_date', now()->addDays(7)->toDateString()) }}">
          </div>

          <small id="tempoHint" class="text-muted" style="display:block; margin-top:8px; font-size:12px; color:#64748b;"></small>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="btnProses">Catat Penjualan</button>

      {{-- Badge status --}}
<div id="statusWrap" style="display:none; margin-top:10px;">
    <span id="statusBadge" style="padding:6px 10px; border-radius:999px; font-weight:600; font-size:13px; background:#fff3cd; color:#7a5a00;">
        Siap mencatat
    </span>
    <div id="btnCetakWrap" style="display:none; margin-top:12px; gap:8px; align-items:center;">
        <button type="button" id="btnKonfirmasiPembayaran" class="btn btn-sm btn-success" style="background:#10b981; color:#fff; border:none; padding:8px 12px; border-radius:8px; display:none; font-weight:600;">
            Konfirmasi Uang Masuk
        </button>
        <a href="#" id="linkCetakRawBT" class="btn btn-sm" style="text-decoration:none; border:1px solid #0f766e; color:#0f766e;">
            Cetak via Bluetooth
        </a>
        <button type="button" id="btnSelesaiTransaksi" class="btn btn-sm">
            Selesaikan
        </button>
        <button type="button" id="btnBatalTransaksi" class="btn btn-sm btn-outline-danger" style="border:1px solid #ef4444; color:#ef4444; background:transparent;">
            Batalkan
        </button>
    </div>
    <span id="statusInfo" style="margin-left:8px; font-size:13px; color:#334155;"></span>
</div>
    </div>
  </div>

  <div class="kasir-cart-backdrop" id="cartBackdrop" aria-hidden="true"></div>
  <button type="button" class="kasir-cart-fab" id="btnCartDrawer" aria-controls="kasirPaymentPanel" aria-expanded="false">
    <span class="kasir-cart-fab__label">Keranjang</span>
    <span class="kasir-cart-fab__meta">
      <strong id="cartFabTotal">Rp 0</strong>
      <span id="cartFabCount">0 item</span>
    </span>
  </button>
</form>

<style>
  .kasir-page {
    position: relative;
  }

  .kasir-pos-grid {
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 18px;
    align-items: start;
  }

  .kasir-menu-card,
  .kasir-payment-card {
    padding: 18px;
    margin-bottom: 0;
  }

  .kasir-payment-card.card--sticky {
    top: calc(var(--topbar-h, 64px) + 16px);
  }

  .kasir-payment-card {
    max-height: calc(100vh - var(--topbar-h, 64px) - 32px);
    overflow: auto;
  }

  .kasir-payment-head {
    gap: 12px;
    align-items: flex-start;
  }

  .kasir-payment-card #ringkasanKeranjang {
    max-height: min(320px, calc(100vh - 460px)) !important;
  }

  .kasir-payment-card .order-header {
    gap: 10px;
  }

  .kasir-payment-card .order-header > div:last-child {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 6px;
  }

  .kasir-drawer-close,
  .kasir-cart-fab,
  .kasir-cart-backdrop {
    display: none;
  }

  .kasir-menu-head {
    gap: 14px;
    margin-bottom: 14px;
  }

  .menu-count {
    flex: 0 0 auto;
    padding: 6px 10px;
    border-radius: 999px;
    background: #ecfdf5;
    color: #047857;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
  }

  .produk-toolbar {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 190px;
    gap: 10px;
    margin-bottom: 10px;
  }

  .kategori-pills {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 0 2px 10px;
    margin-bottom: 10px;
    scrollbar-width: thin;
  }

  .kategori-pill {
    flex: 0 0 auto;
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    background: #fff;
    color: #475569;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    padding: 9px 12px;
  }

  .kategori-pill:hover,
  .kategori-pill.is-active {
    background: #0f766e;
    border-color: #0f766e;
    color: #fff;
  }

  .produk-scroll {
    max-height: calc(100vh - 238px);
    overflow: auto;
    padding-right: 4px;
    scrollbar-width: thin;
  }

  .produk-grid--img {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    align-items: stretch;
  }

  .produk-card--img {
    display: flex !important;
    flex-direction: column;
    align-items: stretch !important;
    gap: 0 !important;
    min-height: 212px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    padding: 0 !important;
    overflow: hidden;
    cursor: pointer;
    transition: transform .08s ease, box-shadow .08s ease, border-color .08s ease;
  }

  .produk-card--img:hover {
    border-color: #99f6e4;
    box-shadow: 0 10px 22px rgba(15, 118, 110, .10);
    transform: translateY(-1px);
  }

  .produk-card--img.is-hidden {
    display: none !important;
  }

  .produk-thumb {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  .produk-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .produk-thumb--ph {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 34px;
    color: #334155;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  }

  .produk-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-height: 92px;
    padding: 10px;
  }

  .produk-name {
    color: #0f172a;
    display: -webkit-box;
    font-size: 13px;
    font-weight: 800;
    line-height: 1.25;
    margin-bottom: 6px;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
  }

  .produk-price {
    color: #0f766e;
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 6px;
  }

  .produk-kat {
    color: #64748b;
    font-size: 11px;
    line-height: 1.2;
    margin-top: auto;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .badge-stok {
    position: absolute;
    left: 8px;
    top: 8px;
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 999px;
    background: #fff;
    color: #0f172a;
    border: 1px solid #e5e7eb;
  }

  .badge-stok--habis {
    background: #fee2e2;
    border-color: #fecaca;
    color: #7f1d1d;
  }

  .badge-tap {
    position: absolute;
    right: 8px;
    top: 8px;
    width: 26px;
    height: 26px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #0f766e;
    color: #fff;
    font-size: 18px;
    font-weight: 800;
    line-height: 1;
    box-shadow: 0 6px 14px rgba(15, 118, 110, .22);
  }

  .produk-empty {
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
    margin-top: 12px;
    padding: 18px;
    text-align: center;
  }

  .tap-fx {
    position: fixed;
    z-index: 9999;
    padding: 6px 10px;
    border-radius: 999px;
    background: #0f766e;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    pointer-events: none;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity .18s ease, transform .18s ease;
  }

  .tap-fx.show {
    opacity: 1;
    transform: translate(-50%, calc(-50% - 10px));
  }

  @media (max-width: 1180px) {
    .kasir-pos-grid {
      grid-template-columns: minmax(0, 1fr) 330px;
    }

    .produk-grid--img {
      grid-template-columns: repeat(auto-fill, minmax(136px, 1fr));
    }
  }

  @media (max-width: 1024px) {
    .kasir-pos-grid {
      grid-template-columns: minmax(0, 1fr) minmax(280px, 34vw);
      gap: 14px;
    }

    .kasir-menu-card,
    .kasir-payment-card {
      padding: 14px;
    }

    .kasir-payment-card.card--sticky {
      top: calc(var(--topbar-h, 64px) + 12px);
    }

    .produk-scroll {
      max-height: calc(100vh - 220px);
      overflow: auto;
      padding-right: 4px;
    }

    .produk-toolbar {
      grid-template-columns: minmax(0, 1fr) 150px;
    }

    .produk-grid--img {
      grid-template-columns: repeat(auto-fill, minmax(118px, 1fr));
      gap: 10px;
    }

    .produk-card--img {
      min-height: 178px;
    }

    .produk-body {
      min-height: 78px;
      padding: 8px;
    }

    .produk-thumb--ph {
      font-size: 28px;
    }

    .produk-name {
      font-size: 12px;
    }

    .kasir-payment-card #ringkasanKeranjang {
      max-height: min(300px, calc(100vh - 440px)) !important;
    }
  }

  @media (max-width: 900px) {
    .kasir-pos-grid {
      grid-template-columns: 1fr;
    }

    .produk-scroll {
      max-height: none;
      overflow: visible;
      padding: 0 0 96px;
    }

    .produk-toolbar {
      grid-template-columns: minmax(0, 1fr) 170px;
    }

    .kasir-payment-card.card--sticky {
      position: fixed;
      left: max(12px, env(safe-area-inset-left));
      right: max(12px, env(safe-area-inset-right));
      bottom: max(12px, env(safe-area-inset-bottom));
      top: auto;
      z-index: 1250;
    }

    .kasir-payment-card {
      max-height: min(86vh, 680px);
      margin: 0;
      opacity: 0;
      overflow: auto;
      pointer-events: none;
      transform: translateY(calc(100% + 24px));
      transition: transform .22s ease, opacity .18s ease;
      box-shadow: 0 24px 60px rgba(15, 23, 42, .24);
      border: 1px solid #e2e8f0;
    }

    .kasir-payment-card.is-open {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0);
    }

    .kasir-payment-card #ringkasanKeranjang {
      max-height: min(38vh, 320px) !important;
    }

    .kasir-drawer-close {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
      width: 36px;
      height: 36px;
      border: 1px solid #e5e7eb;
      border-radius: 999px;
      background: #fff;
      color: #0f172a;
      cursor: pointer;
      font-size: 24px;
      line-height: 1;
    }

    .kasir-cart-backdrop {
      position: fixed;
      inset: 0;
      z-index: 1230;
      display: block;
      background: rgba(15, 23, 42, .38);
      opacity: 0;
      pointer-events: none;
      transition: opacity .18s ease;
    }

    .kasir-cart-backdrop.is-open {
      opacity: 1;
      pointer-events: auto;
    }

    .kasir-cart-fab {
      position: fixed;
      left: max(16px, env(safe-area-inset-left));
      right: max(16px, env(safe-area-inset-right));
      bottom: max(16px, env(safe-area-inset-bottom));
      z-index: 1240;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      min-height: 58px;
      padding: 12px 16px;
      border: 0;
      border-radius: 16px;
      background: #0f766e;
      color: #fff;
      box-shadow: 0 18px 38px rgba(15, 118, 110, .28);
      cursor: pointer;
      transition: transform .2s ease, opacity .2s ease;
    }

    .kasir-cart-fab.is-hidden {
      opacity: 0;
      pointer-events: none;
      transform: translateY(120%);
    }

    .kasir-cart-fab__label {
      font-size: 14px;
      font-weight: 800;
    }

    .kasir-cart-fab__meta {
      display: grid;
      gap: 2px;
      text-align: right;
      font-size: 11px;
      line-height: 1.1;
    }

    .kasir-cart-fab__meta strong {
      font-size: 14px;
    }

    body.kasir-drawer-open {
      overflow: hidden;
    }
  }

  @media (max-width: 700px) {
    .produk-toolbar {
      grid-template-columns: 1fr;
    }

    .produk-grid--img {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .produk-card--img {
      min-height: 198px;
    }
  }

  @media (max-width: 480px) {
    .kasir-menu-card,
    .kasir-payment-card {
      padding: 12px;
    }

    .kasir-menu-head,
    .kasir-payment-head,
    .order-footer {
      align-items: flex-start !important;
      flex-direction: column;
    }

    .menu-count {
      align-self: flex-start;
    }

    .cash-panel-grid {
      grid-template-columns: 1fr !important;
    }

    .cash-panel-grid > div:nth-child(even) {
      justify-self: stretch;
      width: 100%;
    }

    #inputCash {
      min-width: 0 !important;
      width: 100%;
    }
  }
</style>

<script>
$(function(){
  const wadahKeranjang = $('#ringkasanKeranjang');
  const elTotal = $('#grandTotal');
  const wadahProduk = $('#gridProduk');
  const menuCount = $('#menuCount');
  const produkEmpty = $('#produkEmpty');
  const kategoriPills = $('#kategoriPills');
  const statusWrap = $('#statusWrap');
  const statusBadge = $('#statusBadge');
  const statusInfo = $('#statusInfo');
  const inputNama = $('[name=customer_name]');
  const customerDiscountBox = $('#customerDiscountBox');
  const customerDiscountText = $('#customerDiscountText');
  const customerDiscountAmount = $('#customerDiscountAmount');
  const metodeBayar = $('#metodeBayar');
  const panelTempo = $('#panelTempo');
  const tempoHint = $('#tempoHint');
  const panelCash = $('#panelCash');
  const cashTotal = $('#cashTotal');
  const cashChange = $('#cashChange');
  const inputCash = $('#inputCash');
  const cashTenderedRaw = $('#cashTenderedRaw');
  const cashHint = $('#cashHint');
  const btnProses = $('#btnProses');
  const btnKosongkan = $('#btnKosongkan');
  const paymentCard = $('#kasirPaymentPanel');
  const btnCartDrawer = $('#btnCartDrawer');
  const btnCloseCart = $('#btnCloseCart');
  const cartBackdrop = $('#cartBackdrop');
  const cartFabCount = $('#cartFabCount');
  const cartFabTotal = $('#cartFabTotal');
  const cartDrawerMedia = window.matchMedia('(max-width: 900px)');

  // Selektor Tombol Cetak & Selesaikan
  const btnCetakWrap = $('#btnCetakWrap'); 
  const linkCetakRawBT = $('#linkCetakRawBT');

  // Kode aktif dari server/localStorage
  const serverActiveCode = {!! json_encode($activeCode ?? null) !!};
  const salesStorageKey = 'last_sales_code';
  const metodeStorageKey = 'kasir_selected_method';
  const cachedSalesCode = localStorage.getItem(salesStorageKey);
  let salesCode = serverActiveCode || cachedSalesCode;
  if (serverActiveCode) {
    localStorage.setItem(salesStorageKey, serverActiveCode);
  }

  const savedMetode = localStorage.getItem(metodeStorageKey);
  if (savedMetode && metodeBayar.find('option').filter(function(){ return this.value === savedMetode; }).length) {
    metodeBayar.val(savedMetode);
  }

  let transactionLocked = Boolean(salesCode);

  // Restore values if transaction is locked
  if (transactionLocked) {
    const savedName = localStorage.getItem('kasir_customer_name');
    if (savedName !== null) inputNama.val(savedName);

    const savedCompany = localStorage.getItem('kasir_invoice_to_company');
    if (savedCompany !== null) $('[name=invoice_to_company]').val(savedCompany);

    const savedDueDate = localStorage.getItem('kasir_tempo_due_date');
    if (savedDueDate !== null) $('[name=tempo_due_date]').val(savedDueDate);

    const savedInputCash = localStorage.getItem('kasir_input_cash');
    if (savedInputCash !== null) inputCash.val(savedInputCash);

    const savedCashRaw = localStorage.getItem('kasir_cash_tendered_raw');
    if (savedCashRaw !== null) cashTenderedRaw.val(savedCashRaw);
  } else {
    localStorage.removeItem('kasir_customer_name');
    localStorage.removeItem('kasir_invoice_to_company');
    localStorage.removeItem('kasir_tempo_due_date');
    localStorage.removeItem('kasir_input_cash');
    localStorage.removeItem('kasir_cash_tendered_raw');
  }

  // ===== State keranjang
  let CART = { items: [], subtotal: 0, subtotal_text: 'Rp 0' };
  let DISCOUNT = {
    exists: false,
    purchase_count: 0,
    min_transactions: 10,
    configured_percent: 0,
    discount_percent: 0,
    discount_amount: 0,
    total_after_discount: 0,
    eligible: false,
    remaining_transactions: 10
  };
  let cartMutationSeq = 0; // cegah response out-of-order bikin flicker
  let discountTimer = null;
  let discountRequestSeq = 0;
  let pollTimer = null;

  function updateTransactionUI(kode, metode, status) {
    salesCode = kode;
    localStorage.setItem(salesStorageKey, kode);
    setTransactionLocked(true);
    statusWrap.show();

    if (status === 'paid') {
      statusBadge.text('Lunas ✅').css({background:'#dcfce7', color:'#14532d'});
      
      let cetakUrl = (metode === 'tempo')
        ? "{{ url('/kasir/invoice') }}/" + encodeURIComponent(kode) + "?print=0"
        : "{{ url('/kasir/struk') }}/" + encodeURIComponent(kode) + "?print=0&rawbt=1";
        
      linkCetakRawBT
        .attr('href', cetakUrl)
        .attr('data-kode', kode)
        .attr('data-metode', metode);
        
      if (metode === 'tempo') {
        linkCetakRawBT.text('Cetak Invoice').removeAttr('target');
        statusInfo.text('Invoice siap dicetak.');
      } else {
        linkCetakRawBT.text('Cetak via Bluetooth').attr('target', '_blank');
        statusInfo.text('Struk siap dicetak via Bluetooth.');
      }
      
      linkCetakRawBT.show();
      $('#btnSelesaiTransaksi').show();
      $('#btnKonfirmasiPembayaran').hide();
      
      if (metode === 'qris' || metode === 'transfer' || metode === 'debit') {
        $('#btnBatalTransaksi').hide();
      } else {
        $('#btnBatalTransaksi').show();
      }
      
      btnCetakWrap.css({display:'flex'});
    } else {
      statusBadge.text('Menunggu Uang Masuk ⌛').css({background:'#fef3c7', color:'#92400e'});
      statusInfo.text('Silakan verifikasi pembayaran masuk. Klik tombol konfirmasi jika sudah lunas.');
      
      linkCetakRawBT.hide();
      $('#btnSelesaiTransaksi').hide();
      $('#btnKonfirmasiPembayaran').show();
      $('#btnBatalTransaksi').hide();
      btnCetakWrap.css({display:'flex'});
    }
  }

  if (transactionLocked) {
    statusWrap.show();
    statusBadge.text('Memuat... ⌛').css({background:'#f8fafc', color:'#64748b'});
    statusInfo.text('Sedang memuat status transaksi dari server...');
    
    $.ajax({
      url: "{{ url('/kasir/status') }}/" + encodeURIComponent(salesCode),
      type: 'GET', cache: false
    }).done(function(d) {
      const st = (d && d.status) ? String(d.status).toLowerCase() : 'pending';
      const mt = (d && d.metode) ? String(d.metode).toLowerCase() : 'cash';
      updateTransactionUI(salesCode, mt, st);
    }).fail(function() {
      const mt = localStorage.getItem(metodeStorageKey) || 'cash';
      updateTransactionUI(salesCode, mt, 'pending');
    });
  }

  function formatRupiah(n){ n=parseInt(n||0,10); return 'Rp ' + n.toLocaleString('id-ID'); }
  function parseRupiahToInt(s){ s=String(s||'').replace(/[^\d]/g,''); return parseInt(s||'0',10); }
  function getGrandTotal(){ return Math.max(0, parseInt(DISCOUNT.total_after_discount ?? CART.subtotal ?? 0, 10)); }
  function hasCart(){ return Boolean(CART.items && CART.items.length); }
  function cartItemCount(){
    return (CART.items || []).reduce((total, item) => total + (parseInt(item.qty || 0, 10) || 0), 0);
  }

  function updateCartFab(){
    const count = cartItemCount();
    cartFabCount.text(count + ' item');
    cartFabTotal.text(formatRupiah(getGrandTotal()));
    btnCartDrawer.toggleClass('has-items', count > 0);
  }

  function setCartDrawer(open){
    const shouldOpen = Boolean(open && cartDrawerMedia.matches);
    paymentCard.toggleClass('is-open', shouldOpen);
    cartBackdrop.toggleClass('is-open', shouldOpen);
    btnCartDrawer
      .toggleClass('is-hidden', shouldOpen)
      .attr('aria-expanded', shouldOpen ? 'true' : 'false');
    paymentCard.attr('aria-hidden', cartDrawerMedia.matches && !shouldOpen ? 'true' : 'false');
    paymentCard.prop('inert', cartDrawerMedia.matches && !shouldOpen);
    $('body').toggleClass('kasir-drawer-open', shouldOpen);
  }

  function resetCashInput(){
    cashTenderedRaw.val('0');
    inputCash.val('');
    cashChange.text('Rp 0');
    cashHint.text('');
  }

  function processButtonShouldDisable(){
    if (transactionLocked || !hasCart()) return true;
    if (!String(inputNama.val() || '').trim()) return true;
    if (metodeBayar.val() === 'cash') {
      const totalBayar = getGrandTotal();
      const bayar = parseInt(cashTenderedRaw.val()||'0', 10);
      if (bayar < totalBayar) return true;
    }
    return false;
  }

  function updateProcessButtonState(){
    btnProses.prop('disabled', processButtonShouldDisable());
  }

  function setTransactionLocked(locked){
    transactionLocked = Boolean(locked);
    wadahProduk.find('.produk-card')
      .prop('disabled', transactionLocked)
      .toggleClass('is-disabled', transactionLocked);
    wadahKeranjang.find('.aksi').prop('disabled', transactionLocked);
    btnKosongkan.prop('disabled', transactionLocked || !hasCart());
    
    // Disable inputs and buttons on transaction lock
    inputNama.prop('disabled', transactionLocked);
    metodeBayar.prop('disabled', transactionLocked);
    $('[name=invoice_to_company]').prop('disabled', transactionLocked);
    $('[name=tempo_due_date]').prop('disabled', transactionLocked);
    inputCash.prop('disabled', transactionLocked);
    $('.btn-quick').prop('disabled', transactionLocked);
    $('#btnPas').prop('disabled', transactionLocked);
    $('#btnClearCash').prop('disabled', transactionLocked);
    
    updateProcessButtonState();
  }

  function resetKasirState(options = {}){
    const cfg = {
      hideStatus: true,
      clearCustomer: true,
      clearCart: true,
      ...options
    };

    clearTimeout(pollTimer);
    salesCode = null;
    localStorage.removeItem(salesStorageKey);
    localStorage.removeItem('kasir_customer_name');
    localStorage.removeItem('kasir_invoice_to_company');
    localStorage.removeItem('kasir_tempo_due_date');
    localStorage.removeItem('kasir_input_cash');
    localStorage.removeItem('kasir_cash_tendered_raw');
    setCartDrawer(false);
    setTransactionLocked(false);
    btnProses.text('Catat Penjualan');
    linkCetakRawBT.attr('href', '#').text('Cetak via Bluetooth').removeAttr('data-kode data-metode target');
    btnCetakWrap.hide();

    if (cfg.clearCustomer) {
      inputNama.val('');
      $('[name=invoice_to_company]').val('');
      // Set tempo due date back to default (today + 7 days)
      const defaultDueDate = new Date();
      defaultDueDate.setDate(defaultDueDate.getDate() + 7);
      const yyyy = defaultDueDate.getFullYear();
      const mm = String(defaultDueDate.getMonth() + 1).padStart(2, '0');
      const dd = String(defaultDueDate.getDate()).padStart(2, '0');
      $('[name=tempo_due_date]').val(`${yyyy}-${mm}-${dd}`);
    }

    resetCashInput();

    if (cfg.clearCart) {
      CART = { items: [], subtotal: 0, subtotal_text: 'Rp 0' };
      renderKeranjang();
    } else {
      showHidePanels();
    }

    if (cfg.hideStatus) {
      statusWrap.hide();
      statusBadge.text('Siap mencatat').css({background:'#f8fafc', color:'#334155'});
      statusInfo.text('');
    }
  }

  function resetDiscount(renderTotal = true){
    discountRequestSeq++;
    window.clearTimeout(discountTimer);
    DISCOUNT = {
      exists: false,
      purchase_count: 0,
      min_transactions: 10,
      configured_percent: 0,
      discount_percent: 0,
      discount_amount: 0,
      total_after_discount: CART.subtotal || 0,
      eligible: false,
      remaining_transactions: 10
    };
    customerDiscountBox.hide();
    if (renderTotal) {
      elTotal.text(formatRupiah(CART.subtotal || 0));
      updateCashPanel();
    }
    updateCartFab();
  }

  function applyDiscountInfo(data){
    DISCOUNT = { ...DISCOUNT, ...(data || {}) };
    DISCOUNT.total_after_discount = parseInt(DISCOUNT.total_after_discount ?? CART.subtotal ?? 0, 10);

    if (DISCOUNT.discount_amount > 0) {
      customerDiscountText.text(
        '🎉 Selamat! Diskon Loyalti ' + Number(DISCOUNT.discount_percent || 0).toLocaleString('id-ID') +
        '% Aktif (Pembelian ke-' + (DISCOUNT.purchase_count + 1) + ')'
      );
      customerDiscountAmount.text('-' + formatRupiah(DISCOUNT.discount_amount));
      customerDiscountBox.show();
    } else if (String(inputNama.val() || '').trim() && DISCOUNT.exists && DISCOUNT.configured_percent > 0) {
      const minTx = parseInt(DISCOUNT.min_transactions || 10, 10);
      const nextTarget = (Math.floor((DISCOUNT.purchase_count || 0) / minTx) + 1) * minTx;
      customerDiscountText.text(
        'Beli ' + (DISCOUNT.remaining_transactions || minTx) + 'x lagi untuk diskon berikutnya (Target: ke-' + nextTarget + ')'
      );
      customerDiscountAmount.text(Number(DISCOUNT.configured_percent || 0).toLocaleString('id-ID') + '%');
      customerDiscountBox.show();
    } else {
      customerDiscountBox.hide();
    }

    elTotal.text(formatRupiah(getGrandTotal()));
    updateCashPanel();
    updateCartFab();
  }

  function refreshDiscountInfo(){
    const customerName = String(inputNama.val() || '').trim();
    if (!customerName || !CART.items || CART.items.length === 0) {
      resetDiscount(true);
      return;
    }

    const seq = ++discountRequestSeq;
    window.clearTimeout(discountTimer);
    DISCOUNT.total_after_discount = CART.subtotal || 0;
    elTotal.text(formatRupiah(CART.subtotal || 0));
    updateCartFab();
    updateCashPanel();
    discountTimer = window.setTimeout(function(){
      $.get('{{ route('kasir.customer.discount') }}', {
        customer_name: customerName,
        subtotal: CART.subtotal || 0
      }).done(function(data){
        if (seq === discountRequestSeq) applyDiscountInfo(data);
      }).fail(function(){
        if (seq === discountRequestSeq) resetDiscount(true);
      });
    }, 250);
  }

  $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, cache:false });

  btnCartDrawer.on('click', function(){
    setCartDrawer(true);
  });

  btnCloseCart.add(cartBackdrop).on('click', function(){
    setCartDrawer(false);
  });

  $(document).on('keydown', function(e){
    if (e.key === 'Escape') setCartDrawer(false);
  });

  $(window).on('resize', function(){
    if (!cartDrawerMedia.matches) setCartDrawer(false);
    paymentCard.attr('aria-hidden', cartDrawerMedia.matches && !paymentCard.hasClass('is-open') ? 'true' : 'false');
    paymentCard.prop('inert', cartDrawerMedia.matches && !paymentCard.hasClass('is-open'));
  });

  setCartDrawer(false);

  function renderKeranjang(data){
    if (data) CART = { ...CART, ...data };
    let html = '';
    if(!CART.items || CART.items.length===0){
      wadahKeranjang.html('<p class="text-muted">Belum ada item dipilih.</p>');
      resetDiscount(false);
      elTotal.text('Rp 0');
      updateCartFab();
      showHidePanels();
      setTransactionLocked(transactionLocked);
      return;
    }
    CART.items.forEach(i=>{
      html += `
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
        </div>`;
    });
    wadahKeranjang.html(html);
    updateCartFab();
    refreshDiscountInfo();
    showHidePanels();
    setTransactionLocked(transactionLocked);
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

  // Channel Click Toggle
  $('.btn-channel-toggle').on('click', function() {
    if (transactionLocked) {
      alert('Selesaikan transaksi yang sedang berjalan terlebih dahulu.');
      return;
    }
    const btn = $(this);
    if (btn.hasClass('active')) return;

    const channel = btn.data('channel');

    // Visually toggle active class
    $('.btn-channel-toggle').removeClass('active');
    btn.addClass('active');

    // Update catalog cards prices visually
    wadahProduk.find('.produk-card').each(function() {
      const card = $(this);
      const priceOffline = +card.data('price');
      const priceOnline = +card.data('price-online');
      const priceToUse = channel === 'online' ? priceOnline : priceOffline;
      card.find('.produk-price').text(formatRupiah(priceToUse));
    });

    // Send POST to /kasir/cart/channel
    $.post('{{ route('kasir.cart.channel') }}', { channel: channel })
      .done(function(d) {
        renderKeranjang(d);
      })
      .fail(function() {
        alert('Gagal mengganti saluran penjualan.');
      });
  });

  // Klik CARD Produk
  wadahProduk.on('click', '.produk-card', function(e){
    if (transactionLocked) {
      showTapFx(e.clientX, e.clientY, 'Selesaikan dulu');
      return;
    }

    const $card = $(this);
    const id = +$card.data('id');
    const name = $card.data('realname');
    const currentChannel = $('.btn-channel-toggle.active').data('channel') || 'offline';
    const price = currentChannel === 'online' ? +$card.data('price-online') : +$card.data('price');

    const stokText = $card.find('.badge-stok').text() || '';
    const stokNum = parseInt(stokText.replace(/[^\d]/g,''),10);
    if (!isNaN(stokNum) && stokNum <= 0){
      showTapFx(e.clientX, e.clientY, 'Stok habis');
      return;
    }

    const rb = optimisticUpdate('tambah',{produk_id:id,name,price});
    const seq = ++cartMutationSeq;
    showTapFx(e.clientX, e.clientY, '+1');
    $.post('{{ route('kasir.cart.tambah') }}',{produk_id:id})
      .done(function(d){ if (seq === cartMutationSeq) renderKeranjang(d); })
      .fail(function(){ if (seq === cartMutationSeq) rb(); });
  });

  // Aksi Keranjang
  $('#ringkasanKeranjang').on('click','.aksi',function(){
    if (transactionLocked) return;

    const id=+$(this).data('id'), act=String($(this).data('act'));
    const ex=(CART.items||[]).find(i=>i.produk_id===id)||{};
    const rb=optimisticUpdate(act,{produk_id:id,name:ex.name,price:ex.price||0});
    const seq = ++cartMutationSeq;
    let url='',method='POST',data={produk_id:id};
    if(act==='tambah') url='{{ route('kasir.cart.tambah') }}';
    if(act==='kurang') url='{{ route('kasir.cart.kurang') }}';
    if(act==='hapus'){ url='{{ route('kasir.cart.hapus') }}'; method='DELETE'; }
    $.ajax({url,type:method,data})
      .done(function(d){ if (seq === cartMutationSeq) renderKeranjang(d); })
      .fail(function(){ if (seq === cartMutationSeq) rb(); });
  });

  btnKosongkan.on('click',()=>{
    if (transactionLocked || !hasCart()) return;

    const rb=optimisticUpdate('kosongkan',{});
    const seq = ++cartMutationSeq;
    $.post('{{ route('kasir.cart.kosongkan') }}',{})
      .done(function(d){ if (seq === cartMutationSeq) renderKeranjang(d); })
      .fail(function(){ if (seq === cartMutationSeq) rb(); });
  });

  // Filter Produk
  function applyFilter(){
    const q=($('#cariProduk').val()||'').trim().toLowerCase();
    const k=($('#filterKategori').val()||'').trim().toLowerCase();
    let visible = 0;
    wadahProduk.find('.produk-card').each(function(){
      const n=($(this).data('name')||''), c=($(this).data('kategori')||'');
      const match = n.includes(q) && (!k || c===k);
      $(this).toggleClass('is-hidden', !match);
      if (match) visible++;
    });
    menuCount.text(visible + ' menu');
    produkEmpty.toggle(visible === 0);
  }
  $('#cariProduk').on('input',applyFilter);
  $('#filterKategori').on('change', function(){
    const value = ($(this).val() || '').toLowerCase();
    kategoriPills.find('.kategori-pill').removeClass('is-active');
    kategoriPills.find(`.kategori-pill[data-kategori="${value}"]`).addClass('is-active');
    applyFilter();
  });
  kategoriPills.on('click', '.kategori-pill', function(){
    const value = ($(this).data('kategori') || '').toLowerCase();
    $('#filterKategori').val(value);
    kategoriPills.find('.kategori-pill').removeClass('is-active');
    $(this).addClass('is-active');
    applyFilter();
  });
  applyFilter();

  // Load Awal
  $.get('{{ route('kasir.cart.data') }}',d=>{ CART={...CART,...d}; renderKeranjang(); });

  // CASH & Tempo Panel Logic
  function updateCashPanel(){
    const totalBayar = getGrandTotal();
    cashTotal.text(formatRupiah(totalBayar));
    const bayar = parseInt(cashTenderedRaw.val()||'0',10);
    const selisih = bayar - totalBayar;
    cashChange.text(formatRupiah(Math.max(0, selisih)));
    
    if (metodeBayar.val() === 'cash') {
      if (bayar < totalBayar) {
        cashHint.text('Uang kurang ' + formatRupiah(totalBayar - bayar));
      } else {
        cashHint.text('');
      }
    } else {
      cashHint.text('');
    }
    updateProcessButtonState();
  }

  function updateTempoPanel(){
    if (metodeBayar.val() !== 'tempo') { tempoHint.text(''); return; }

    const nm = String(inputNama.val()||'').trim();
    if (!nm) {
      tempoHint.text('Isi nama pelanggan di atas untuk tagihan invoice.');
    } else {
      tempoHint.text('Setelah proses, halaman invoice akan terbuka untuk dicetak/dikirim.');
    }
    updateProcessButtonState();
  }

  function showHidePanels(){
    const m = metodeBayar.val();
    panelCash.toggle(m === 'cash');
    panelTempo.toggle(m === 'tempo');

    updateCashPanel(); updateTempoPanel();
    updateProcessButtonState();
  }

  metodeBayar.on('change', function(){
    localStorage.setItem(metodeStorageKey, $(this).val());
    showHidePanels();
  });
  inputNama.on('input', function(){
    refreshDiscountInfo();
    updateTempoPanel();
  });
  inputCash.on('input', function(){
    const v=parseRupiahToInt($(this).val()); cashTenderedRaw.val(String(v)); $(this).val(formatRupiah(v)); updateCashPanel();
  });
  $('.btn-quick').on('click', function(){
    const next=+$(this).data('amt');
    cashTenderedRaw.val(String(next)); inputCash.val(formatRupiah(next)); updateCashPanel();
  });
  $('#btnPas').on('click', ()=>{ const t=getGrandTotal(); cashTenderedRaw.val(String(t)); inputCash.val(formatRupiah(t)); updateCashPanel(); });
  $('#btnClearCash').on('click', ()=>{ cashTenderedRaw.val('0'); inputCash.val(''); updateCashPanel(); });

  // ===== POLLING PEMBAYARAN
  function startPolling(kode){
    if (!kode) return;

    salesCode = kode;
    localStorage.setItem(salesStorageKey, kode);
    setTransactionLocked(true);
    btnProses.text('Transaksi berjalan');
    statusWrap.show();
    statusBadge.text('Transaksi tercatat').css({background:'#dcfce7', color:'#14532d'});
    btnCetakWrap.hide();
    statusInfo.text('Transaksi sedang berjalan. Selesaikan transaksi ini sebelum membuat order baru.');

    function selesaiUI(text, bg, fg, isPaid = false, metode = 'cash'){
      statusBadge.text(text).css({background:bg, color:fg});
      
      if (isPaid) {
        let printUrl = (metode === 'tempo')
            ? "{{ url('/kasir/invoice') }}/" + encodeURIComponent(kode) + "?print=0"
            : "{{ url('/kasir/struk') }}/" + encodeURIComponent(kode) + "?print=0&rawbt=1";
        
        linkCetakRawBT
          .attr('href', printUrl)
          .attr('data-kode', kode)
          .attr('data-metode', metode);
        
        if (metode === 'tempo') {
          linkCetakRawBT.text('Cetak Invoice').removeAttr('target');
          statusInfo.text('Invoice siap dicetak.');
        } else {
          linkCetakRawBT.text('Cetak via Bluetooth').attr('target', '_blank');
          statusInfo.text('Struk siap dicetak via Bluetooth.');
        }
        btnCetakWrap.css({display:'flex'});
      } else {
        setTimeout(() => {
          resetKasirState({clearCustomer:false});
        }, 3000);
      }
    }

    function cek(){
      $.ajax({
        url: "{{ url('/kasir/status') }}/" + encodeURIComponent(kode),
        type:'GET', data:{ _:Date.now() }, cache:false
      }).done(function(d){
        const st=(d&&d.status)?String(d.status).toLowerCase():'pending';
        const mt=(d&&d.metode)?String(d.metode).toLowerCase():'cash';
        
        if (st==='paid'){ 
            selesaiUI('Lunas ✅','#dcfce7','#14532d', true, mt); 
            clearTimeout(pollTimer); 
            return; 
        }
        if (st==='expired' || st==='cancelled'){ 
            selesaiUI('Gagal/Batal ❌','#fee2e2','#7f1d1d', false); 
            clearTimeout(pollTimer); 
            return; 
        }
        pollTimer=setTimeout(cek, 1500);
      }).fail(function(){ pollTimer=setTimeout(cek, 2000); });
    }

    clearTimeout(pollTimer); 
    cek();
  }


  // Polling awal dipanggil setelah handler final dipasang.

  $('#btnSelesaiTransaksi').off('click');
  $(document).off('click', '#btnSelesaiTransaksi').on('click', '#btnSelesaiTransaksi', function() {
    const kode = localStorage.getItem(salesStorageKey) || salesCode;
    if (!kode) {
      resetKasirState();
      return;
    }

    const btn = $(this);
    btn.prop('disabled', true).text('Mereset...');

    $.post("{{ url('/kasir/selesai-cetak') }}/" + encodeURIComponent(kode), { dicetak: 1 })
      .done(function() {
        $.post('{{ route('kasir.cart.kosongkan') }}', {}, function(data){
          resetKasirState({clearCart:false});
          renderKeranjang(data);
        }).fail(function(){
          resetKasirState();
        });
      })
      .fail(function(xhr) {
        const res = xhr.responseJSON || {};
        statusInfo.text(res.message || 'Gagal menyelesaikan transaksi.');
        btn.prop('disabled', false).text('Selesaikan');
      });
  });

  $(document).off('click', '#btnBatalTransaksi').on('click', '#btnBatalTransaksi', function() {
    const kode = localStorage.getItem(salesStorageKey) || salesCode;
    if (!kode) {
      alert('Tidak ada transaksi aktif untuk dibatalkan.');
      return;
    }

    if (!confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Transaksi yang sudah dicatat akan dihapus secara permanen dari database.')) {
      return;
    }

    const btn = $(this);
    const originalText = btn.text();
    btn.prop('disabled', true).text('Membatalkan...');

    $.post("{{ url('/kasir/batal') }}/" + encodeURIComponent(kode))
      .done(function(res) {
        alert(res.message || 'Transaksi berhasil dibatalkan.');
        // Kosongkan keranjang server-side untuk memastikan sinkronisasi
        $.post('{{ route('kasir.cart.kosongkan') }}', {}, function(data) {
          resetKasirState({clearCart: true, clearCustomer: true});
          renderKeranjang(data);
        }).fail(function() {
          resetKasirState({clearCart: true, clearCustomer: true});
        });
      })
      .fail(function(xhr) {
        const res = xhr.responseJSON || {};
        alert(res.message || 'Gagal membatalkan transaksi.');
        btn.prop('disabled', false).text(originalText);
      });
  });

  $(document).off('click', '#btnKonfirmasiPembayaran').on('click', '#btnKonfirmasiPembayaran', function() {
    const kode = localStorage.getItem(salesStorageKey) || salesCode;
    if (!kode) return;

    const btn = $(this);
    btn.prop('disabled', true).text('Mengonfirmasi...');

    $.post("{{ url('/kasir/konfirmasi-pembayaran') }}/" + encodeURIComponent(kode))
      .done(function(res) {
        alert(res.message || 'Pembayaran berhasil dikonfirmasi.');
        const mt = localStorage.getItem(metodeStorageKey) || 'qris';
        updateTransactionUI(kode, mt, 'paid');
      })
      .fail(function(xhr) {
        const res = xhr.responseJSON || {};
        alert(res.message || 'Gagal mengonfirmasi pembayaran.');
        btn.prop('disabled', false).text('Konfirmasi Uang Masuk');
      });
  });

  $('#formPembayaran').off('submit').on('submit', function(e){
    e.preventDefault();

    if (transactionLocked) {
      statusInfo.text('Selesaikan transaksi yang aktif terlebih dahulu.');
      return false;
    }

    if (!String(inputNama.val() || '').trim()) {
      alert('Nama pelanggan wajib diisi.');
      inputNama.trigger('focus');
      updateProcessButtonState();
      return false;
    }

    if (!hasCart()) {
      alert('Keranjang kosong. Tambahkan produk terlebih dahulu.');
      updateProcessButtonState();
      return false;
    }

    showHidePanels();
    if (processButtonShouldDisable()) {
      return false;
    }

    const form = $(this);
    const prosesText = btnProses.text();
    btnProses.prop('disabled', true).text('Memproses...');
    localStorage.setItem(metodeStorageKey, metodeBayar.val());

    $.ajax({
      url: form.attr('action'),
      type: form.attr('method') || 'POST',
      data: form.serialize(),
      dataType: 'json'
    }).done(function(res){
      if (!res || !res.ok) {
        statusWrap.show();
        statusBadge.text('Gagal').css({background:'#fee2e2', color:'#7f1d1d'});
        statusInfo.text((res && res.message) ? res.message : 'Gagal mencatat penjualan.');
        return;
      }

      salesCode = res.sales_code;
      if (salesCode) {
        localStorage.setItem(salesStorageKey, salesCode);
        localStorage.setItem('kasir_customer_name', inputNama.val() || '');
        localStorage.setItem('kasir_invoice_to_company', $('[name=invoice_to_company]').val() || '');
        localStorage.setItem('kasir_tempo_due_date', $('[name=tempo_due_date]').val() || '');
        localStorage.setItem('kasir_input_cash', inputCash.val() || '');
        localStorage.setItem('kasir_cash_tendered_raw', cashTenderedRaw.val() || '0');

        const initialStatus = (res.metode === 'cash' || res.metode === 'tempo') ? 'paid' : 'pending';
        updateTransactionUI(salesCode, res.metode || 'cash', initialStatus);
        setCartDrawer(false);
      }
    }).fail(function(xhr){
      const res = xhr.responseJSON || {};
      statusWrap.show();
      statusBadge.text('Gagal').css({background:'#fee2e2', color:'#7f1d1d'});
      statusInfo.text(res.message || 'Gagal mencatat penjualan.');
      setTransactionLocked(false);
    }).always(function(){
      if (!transactionLocked) {
        btnProses.text(prosesText || 'Catat Penjualan');
        updateProcessButtonState();
      }
    });

    return false;
  });



  // Efek Animasi
  let fxEl = null;
  function showTapFx(x,y,text){
    if(!fxEl){
      fxEl = document.createElement('div');
      fxEl.className = 'tap-fx';
      document.body.appendChild(fxEl);
    }
    fxEl.textContent = text;
    fxEl.style.left = x + 'px'; fxEl.style.top = y + 'px';
    fxEl.classList.remove('show');
    requestAnimationFrame(()=> fxEl.classList.add('show'));
    setTimeout(()=> fxEl && fxEl.classList.remove('show'), 400);
  }
});
</script>
@endsection
