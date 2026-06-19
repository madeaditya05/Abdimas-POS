<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon" id="menuToggle" role="button" tabindex="0" aria-label="Toggle sidebar">
        <img src="{{ asset('images/foto logo pasta nafisa.png') }}" alt="Pasta Nafisa" class="sidebar-logo-img">
      </div>
      <div class="sidebar-logo-text">
        <h1>Pasta Nafisa</h1>
        <p>Owner Dashboard</p>
      </div>
    </div>
  </div>

  @php
    // ====== STATUS AKTIF PER GRUP (dipakai untuk buka/tutup dropdown) ======

    // Master Data: produk, kategori produk, bahan baku, customer
    $isProdukActive =
        request()->routeIs('produk.*')
        || request()->routeIs('kategori-produk.*')
        || request()->routeIs('bahan-baku.*')
        || request()->routeIs('chart-of-accounts.*')
        || request()->routeIs('customer.*'); // (biar Daftar Akun & Customer ikut aktif)

    // Persediaan: mutasi stok + penyesuaian stok
    $isInvActive =
        request()->routeIs('mutasi-stok.*')
        || request()->is('persediaan/penyesuaian*')
        || request()->routeIs('penyesuaian-stok.*');

    // Transaksi: penjualan + pembelian
    $isTransActive =
        request()->is('penjualan*')
        || request()->routeIs('pembelian-bahan.*')
        || request()->routeIs('beban-operasional.*');

    // ==== Laporan kasir (pakai halaman owner.labarugi dengan filter sec[]) ====
    $secParam = \Illuminate\Support\Arr::wrap(request('sec', []));
    $isOwnerReport = request()->routeIs('owner.labarugi*');   // halaman laporan owner (yang kamu kirim)
    $isKasirReport = request()->routeIs('kasir.rekap*');      // halaman laporan kasir (reports/kasir)
    $isJurnalMaster = request()->routeIs('laporan.jurnal.*'); // halaman jurnal read-only (laporan/jurnal)

    // Group laporan dibuka kalau lagi di salah satu halaman laporan
    $isCashActive = $isOwnerReport || $isKasirReport || $isJurnalMaster || request()->routeIs('invoices.*');

    // highlight submenu laporan owner:
    $isSecAll      = $isOwnerReport && empty($secParam);
    $isSecLabaRugi = $isOwnerReport && in_array('labarugi', $secParam);
    $isSecItems    = $isOwnerReport && in_array('items', $secParam);
    $isSecPayments = $isOwnerReport && in_array('payments', $secParam);
    $isSecUnified  = $isOwnerReport && in_array('unified', $secParam);
    $isSecJournal  = $isOwnerReport && in_array('journal', $secParam);
    $isSecLedger   = $isOwnerReport && in_array('ledger', $secParam);



        $isLaporanActive =
    request()->routeIs('owner.reports.menu')
    || request()->routeIs('owner.labarugi*')
    || request()->routeIs('kasir.rekap*')
    || request()->routeIs('laporan.jurnal.*')
    || request()->routeIs('invoices.*');

  @endphp

  <nav class="sidebar-nav" id="ownerSidebarNav">

    {{-- DASHBOARD (single link) --}}
    <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'is-active' : '' }}">
      <span class="nav-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/>
          <rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
        </svg>
      </span>
      <span class="nav-label">Dashboard</span>
    </a>

    {{-- ===== MASTER DATA (Produk, Kategori Produk, Bahan Baku) ===== --}}
    <div class="nav-group {{ $isProdukActive ? 'has-active is-open' : '' }}" data-key="master-data">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isProdukActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="16" rx="2"/>
            <path d="M3 10h18"/>
          </svg>
        </span>
        <span class="nav-label">Master Data</span>
        {{-- <span class="nav-caret"></span> --}}
      </button>

      <div class="subnav {{ $isProdukActive ? 'show' : '' }}">
        {{-- Produk --}}
        <a href="{{ route('produk.index') }}"
          class="subnav-item {{ request()->routeIs('produk.*') ? 'is-active' : '' }}">
          <span>Produk</span>
        </a>

        <a href="{{ route('kategori-produk.index') }}"
          class="subnav-item {{ request()->routeIs('kategori-produk.*') ? 'is-active' : '' }}">
          <span>Kategori Produk</span>
        </a>
        
        <a href="{{ route('chart-of-accounts.index') }}"
          class="subnav-item {{ request()->routeIs('chart-of-accounts.*') ? 'is-active' : '' }}">
          <span>Daftar Akun</span>
        </a>

        {{-- Bahan Baku --}}
        <a href="{{ route('bahan-baku.index') }}"
           class="subnav-item {{ request()->routeIs('bahan-baku.*') ? 'is-active' : '' }}">
          Bahan Baku
        </a>

        {{-- Customer --}}
        <a href="{{ route('customer.index') }}"
           class="subnav-item {{ request()->routeIs('customer.*') ? 'is-active' : '' }}">
          <span>Customer</span>
        </a>

      </div>
    </div>

    {{-- ===== TRANSAKSI (Penjualan, Pembelian Bahan, Penyesuaian) ===== --}}
    <div class="nav-group {{ $isTransActive ? 'has-active is-open' : '' }}" data-key="transaksi">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isTransActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
            <path d="M2.05 2.05h2l2.66 12.42A2 2 0 0 0 8.71 16h9.78a2 2 0 0 0 1.95-1.57L22.09 7H5.12"/>
          </svg>
        </span>
        <span class="nav-label">Transaksi</span>
        {{-- <span class="nav-caret"></span> --}}
      </button>

      <div class="subnav {{ $isTransActive ? 'show' : '' }}">
        {{-- Penjualan --}}
        {{--
        <a href="{{ url('/penjualan') }}"
           class="subnav-item {{ request()->is('penjualan*') ? 'is-active' : '' }}">
          Penjualan
        </a>
        --}}

        {{-- Pembelian Bahan --}}
        <a href="{{ route('pembelian-bahan.index') }}"
           class="subnav-item {{ request()->routeIs('pembelian-bahan.*') ? 'is-active' : '' }}">
          Pembelian Bahan
        </a>
        <a href="{{ route('beban-operasional.index') }}"
           class="subnav-item {{ request()->routeIs('beban-operasional.*') ? 'is-active' : '' }}">
          Input Beban
        </a>
      </div>
    </div>

    {{-- ===== PERSEDIAAN (Mutasi Stok, Penyesuaian Stok versi lama) ===== --}}
    <div class="nav-group {{ $isInvActive ? 'has-active is-open' : '' }}" data-key="persediaan">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isInvActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
            <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
          </svg>
        </span>
        <span class="nav-label">Persediaan</span>
        {{-- <span class="nav-caret"></span> --}}
      </button>

      <div class="subnav {{ $isInvActive ? 'show' : '' }}">
        {{-- Mutasi Stok --}}
        <a href="{{ route('mutasi-stok.index') }}"
           class="subnav-item {{ request()->routeIs('mutasi-stok.*') ? 'is-active' : '' }}">
          Mutasi Stok
        </a>

        {{-- Penyesuaian Stok (versi route baru) --}}
        <a href="{{ route('penyesuaian-stok.index') }}"
           class="subnav-item {{ request()->routeIs('penyesuaian-stok.*') ? 'is-active' : '' }}">
          Penyesuaian Stok
        </a>
      </div>
    </div>

    <a href="{{ route('owner.reports.menu') }}" class="nav-item {{ $isLaporanActive ? 'is-active' : '' }}">
  <span class="nav-icon">
    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/>
      <circle cx="12" cy="12" r="3"/>
      <path d="M5 9h2M17 9h2M5 15h2M17 15h2"/>
    </svg>
  </span>
  <span class="nav-label">Keuangan</span>
</a>





    {{-- PENGATURAN (single link) --}}
    {{--
    <a href="{{ url('/pengaturan') }}" class="nav-item {{ request()->is('pengaturan*') ? 'is-active' : '' }}">
      <span class="nav-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </span>
      <span class="nav-label">Pengaturan</span>
    </a>
    --}}

  </nav>

  <div class="sidebar-footer">
    <a href="{{ route('panel.switch', ['panel' => 'kasir']) }}" class="btn-logout {{ request()->routeIs('kasir.*') ? 'is-active' : '' }}" style="text-decoration:none; margin-bottom:12px;">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
        <path d="M2.05 2.05h2l2.66 12.42A2 2 0 0 0 8.71 16h9.78a2 2 0 0 0 1.95-1.57L22.09 7H5.12"/>
      </svg>
      <span>Panel Kasir</span>
    </a>
    <a href="{{ route('logout.reconcile') }}" class="btn-logout" style="text-decoration:none;">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
           stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" x2="9" y1="12" y2="12"/>
      </svg>
      <span>Keluar</span>
    </a>
  </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const PREFIX = 'cofitev_nav_';
  const groups = Array.from(document.querySelectorAll('.sidebar .nav-group'));

  function setGroup(group, open, persist = true) {
    const key = group.dataset.key || '';
    const btn = group.querySelector('.nav-toggle');
    const panel = group.querySelector('.subnav');

    group.classList.toggle('is-open', open);
    panel?.classList.toggle('show', open);
    btn?.setAttribute('aria-expanded', open ? 'true' : 'false');

    if (persist && key) {
      localStorage.setItem(PREFIX + key, open ? 'open' : 'closed');
    }
  }

  function openOnly(target, persist = true) {
    groups.forEach(group => setGroup(group, group === target, persist));
  }

  const activeGroup = groups.find(group => group.classList.contains('has-active'));
  const savedGroup = groups.find(group => localStorage.getItem(PREFIX + (group.dataset.key || '')) === 'open');
  const initialGroup = activeGroup || savedGroup || groups.find(group => group.classList.contains('is-open'));

  groups.forEach(group => setGroup(group, false, false));
  if (initialGroup) {
    openOnly(initialGroup, true);
  }

  groups.forEach(g => {
    const btn = g.querySelector('.nav-toggle');

    btn?.addEventListener('click', (e) => {
      e.preventDefault();
      const open = !g.classList.contains('is-open');
      if (open) {
        openOnly(g);
      } else {
        setGroup(g, false);
      }
    });
  });
});
</script>
