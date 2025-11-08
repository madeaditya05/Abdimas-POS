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
    // status aktif per grup
    $isProdukActive = request()->routeIs('product.*') || request()->is('kategori*');
    $isInvActive    = request()->routeIs('bahan-baku.*') || request()->is('inventaris*');
    $isLapActive    = request()->is('laporan*') || request()->routeIs('owner.labarugi');
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

    {{-- DATA PRODUK (dropdown) --}}
    <div class="nav-group {{ $isProdukActive ? 'has-active is-open' : '' }}" data-key="produk">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isProdukActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
          </svg>
        </span>
        <span class="nav-label">Data Produk</span>
        <span class="nav-caret"></span>
      </button>
      <div class="subnav {{ $isProdukActive ? 'show' : '' }}">
        <a href="{{ route('product.index') }}" class="subnav-item {{ request()->routeIs('product.*') ? 'is-active' : '' }}">Semua Produk</a>
        <a href="{{ url('/kategori') }}" class="subnav-item {{ request()->is('kategori*') ? 'is-active' : '' }}">Kategori</a>
      </div>
    </div>

    {{-- INVENTARIS (dropdown) --}}
    <div class="nav-group {{ $isInvActive ? 'has-active is-open' : '' }}" data-key="inventaris">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isInvActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
            <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
          </svg>
        </span>
        <span class="nav-label">Inventaris</span>
        <span class="nav-caret"></span>
      </button>
      <div class="subnav {{ $isInvActive ? 'show' : '' }}">
        <a href="{{ route('bahan-baku.index') }}" class="subnav-item {{ request()->routeIs('bahan-baku.*') ? 'is-active' : '' }}">Bahan Baku</a>
        <a href="{{ url('/inventaris/mutasi') }}" class="subnav-item {{ request()->is('inventaris/mutasi*') ? 'is-active' : '' }}">Mutasi Stok</a>
      </div>
    </div>

    {{-- DATA PELANGGAN (single link) --}}
    <a href="{{ url('/pelanggan') }}" class="nav-item {{ request()->is('pelanggan*') ? 'is-active' : '' }}">
      <span class="nav-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </span>
      <span class="nav-label">Data Pelanggan</span>
    </a>

    {{-- LAPORAN (dropdown) --}}
    <div class="nav-group {{ $isLapActive ? 'has-active is-open' : '' }}" data-key="laporan">
      <button type="button" class="nav-item nav-toggle" aria-expanded="{{ $isLapActive ? 'true' : 'false' }}">
        <span class="nav-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/>
            <line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/>
          </svg>
        </span>
        <span class="nav-label">Laporan</span>
        <span class="nav-caret"></span>
      </button>
      <div class="subnav {{ $isLapActive ? 'show' : '' }}">
        <a href="{{ route('owner.labarugi') }}" class="subnav-item {{ request()->routeIs('owner.labarugi') ? 'is-active' : '' }}">Laba-Rugi</a>
        <a href="{{ url('/laporan/penjualan') }}" class="subnav-item {{ request()->is('laporan/penjualan*') ? 'is-active' : '' }}">Penjualan</a>
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

