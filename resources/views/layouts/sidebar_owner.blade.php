<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon" id="menuToggle" role="button" tabindex="0" aria-label="Toggle sidebar">
        <img src="{{ asset('images/logo.png') }}" alt="Cofit EV" class="sidebar-logo-img">
      </div>
      <div class="sidebar-logo-text">
        <h1>Cofit EV</h1>
        <p>Owner Dashboard</p>
      </div>
    </div>
  </div>

  @php
    // ====== STATUS AKTIF PER GRUP (dipakai untuk buka/tutup dropdown) ======

    // Master Data: produk, kategori, bahan baku, resep
    $isProdukActive =
        request()->routeIs('product.*')
        || request()->is('kategori*')
        || request()->routeIs('bahan-baku.*')
        || request()->routeIs('resep.*');

    // Persediaan: mutasi stok + penyesuaian stok
    $isInvActive =
        request()->routeIs('mutasi-stok.*')
        || request()->is('persediaan/penyesuaian*');

    // Transaksi: penjualan + pembelian + penyesuaian stok (versi route baru)
    $isTransActive =
        request()->is('penjualan*')
        || request()->routeIs('pembelian-bahan.*')
        || request()->routeIs('penyesuaian-stok.*');

    // Cash Flow: sekarang cuma Laporan Jurnal (laporan.jurnal.*)
    $isCashActive =
        request()->routeIs('laporan.jurnal.*');

    // Laporan: laporan penjualan + laporan pembelian
    $isLapActive =
        request()->is('laporan/penjualan*')
        || request()->is('laporan/pembelian*');

    // Manajemen User: pengguna + pelanggan
    $isUserActive =
        request()->is('users*')
        || request()->is('pelanggan*');
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

    {{-- ===== MASTER DATA (Produk, Kategori, Bahan Baku, Resep) ===== --}}
    <div class="nav-group {{ $isProdukActive ? 'has-active is-open' : '' }}" data-key="master-data">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isProdukActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="16" rx="2"/>
            <path d="M3 10h18"/>
          </svg>
        </span>
        <span class="nav-label">Master Data</span>
        <span class="nav-caret"></span>
      </button>

      <div class="subnav {{ $isProdukActive ? 'show' : '' }}">
        {{-- Produk --}}
        <a href="{{ route('product.index') }}"
           class="subnav-item {{ request()->routeIs('product.*') ? 'is-active' : '' }}">
          Produk
        </a>

        {{-- Kategori --}}
        <a href="{{ url('/kategori') }}"
           class="subnav-item {{ request()->is('kategori*') ? 'is-active' : '' }}">
          Kategori
        </a>

        {{-- Bahan Baku --}}
        <a href="{{ route('bahan-baku.index') }}"
           class="subnav-item {{ request()->routeIs('bahan-baku.*') ? 'is-active' : '' }}">
          Bahan Baku
        </a>

        {{-- Resep --}}
        <a href="{{ route('resep.index') }}"
           class="subnav-item {{ request()->routeIs('resep.*') ? 'is-active' : '' }}">
          Resep
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
        <span class="nav-caret"></span>
      </button>

      <div class="subnav {{ $isTransActive ? 'show' : '' }}">
        {{-- Penjualan --}}
        <a href="{{ url('/penjualan') }}"
           class="subnav-item {{ request()->is('penjualan*') ? 'is-active' : '' }}">
          Penjualan
        </a>

        {{-- Pembelian Bahan --}}
        <a href="{{ route('pembelian-bahan.index') }}"
           class="subnav-item {{ request()->routeIs('pembelian-bahan.*') ? 'is-active' : '' }}">
          Pembelian Bahan
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
        <span class="nav-caret"></span>
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

    {{-- ===== CASH FLOW (CUMA LAPORAN JURNAL) ===== --}}
    <div class="nav-group {{ $isCashActive ? 'has-active is-open' : '' }}" data-key="cashflow">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isCashActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/>
            <circle cx="12" cy="12" r="3"/>
            <path d="M5 9h2M17 9h2M5 15h2M17 15h2"/>
          </svg>
        </span>
        <span class="nav-label">Cash Flow</span>
        <span class="nav-caret"></span>
      </button>

      <div class="subnav {{ $isCashActive ? 'show' : '' }}">
        {{-- Laporan Jurnal (laporan.jurnal.index) --}}
        <a href="{{ route('laporan.jurnal.index') }}"
           class="subnav-item {{ request()->routeIs('laporan.jurnal.*') ? 'is-active' : '' }}">
          Laporan Keuangan
        </a>
      </div>
    </div>

    {{-- ===== MANAJEMEN USER (Pengguna, Pelanggan) ===== --}}
    <div class="nav-group {{ $isUserActive ? 'has-active is-open' : '' }}" data-key="users">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isUserActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </span>
        <span class="nav-label">Manajemen User</span>
        <span class="nav-caret"></span>
      </button>

      <div class="subnav {{ $isUserActive ? 'show' : '' }}">
        {{-- Pengguna --}}
        <a href="{{ url('/users') }}"
           class="subnav-item {{ request()->is('users*') ? 'is-active' : '' }}">
          Pengguna
        </a>

        {{-- Pelanggan --}}
        <a href="{{ url('/pelanggan') }}"
           class="subnav-item {{ request()->is('pelanggan*') ? 'is-active' : '' }}">
          Pelanggan
        </a>
      </div>
    </div>

    {{-- PENGATURAN (single link) --}}
    <a href="{{ url('/pengaturan') }}" class="nav-item {{ request()->is('pengaturan*') ? 'is-active' : '' }}">
      <span class="nav-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </span>
      <span class="nav-label">Pengaturan</span>
    </a>

  </nav>

  <div class="sidebar-footer">
    <button type="button" class="btn-logout" onclick="document.getElementById('logoutForm').submit()">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
           stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" x2="9" y1="12" y2="12"/>
      </svg>
      <span>Keluar</span>
    </button>
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
  </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const KEY  = 'cofit.sidebar.state';
  const toggles = [document.getElementById('menuToggle'), document.getElementById('btnSidebar')].filter(Boolean);

  // restore
  if (localStorage.getItem(KEY) === 'collapsed') body.classList.add('sidebar-collapsed');

  // bind sekali saja
  toggles.forEach(btn => {
    if (btn.dataset.bound) return;
    btn.dataset.bound = '1';
    btn.addEventListener('click', () => {
      body.classList.toggle('sidebar-collapsed');
      localStorage.setItem(KEY, body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
    });
  });

  // === dropdown sidebar (sesuai markup-mu: .nav-group / .nav-toggle / .subnav)
  const PREFIX = 'cofitev_nav_';
  document.querySelectorAll('.sidebar .nav-group').forEach(g => {
    const key = g.dataset.key || '';
    const btn = g.querySelector('.nav-toggle');
    const panel = g.querySelector('.subnav');

    const saved = localStorage.getItem(PREFIX + key);
    if (saved === 'open') { g.classList.add('is-open'); panel?.classList.add('show'); btn?.setAttribute('aria-expanded','true'); }

    btn?.addEventListener('click', (e) => {
      e.preventDefault();
      const open = !g.classList.contains('is-open');
      g.classList.toggle('is-open', open);
      panel?.classList.toggle('show', open);
      btn?.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (key) localStorage.setItem(PREFIX + key, open ? 'open' : 'closed');
    });
  });
});
</script>
