<aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
  <div class="sidebar-logo">
    <!-- tombol toggle (klik logo atau ikon menu) -->
    <div class="sidebar-logo-icon" id="menuToggle" role="button" tabindex="0" aria-label="Toggle sidebar">
      <!-- Logo gambar dari public/images -->
      <img src="{{ asset('images/logo.png') }}" alt="Cofit EV" class="sidebar-logo-img">
    </div>
    <div class="sidebar-logo-text">
    <h1>Cofit EV</h1>
    <p>
        {{-- @auth adalah directive Blade untuk mengecek 
             apakah ada user yang sedang login --}}
        @auth
            
            {{-- Kita cek kolom 'user_group' dari user yang login --}}
            @if (auth()->user()->user_group == 'owner')
                
                Owner Dashboard
            
            @elseif (auth()->user()->user_group == 'kasir')
                
                Kasir Dashboard
            
            @else
                {{-- Jika ada role lain, tampilkan ini --}}
                Dashboard
            @endif

        @else
            {{-- Ini akan tampil jika user belum login (misal di halaman login) --}}
            Admin Dashboard
        @endauth
    </p>
</div>
  </div>
</div>


            <nav class="sidebar-nav">
                <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1"/>
                        <rect width="7" height="5" x="14" y="3" rx="1"/>
                        <rect width="7" height="9" x="14" y="12" rx="1"/>
                        <rect width="7" height="5" x="3" y="16" rx="1"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('product.index') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span>Produk</span>
                </a>
                <a href="/kasir" class="nav-item {{ request()->is('kasir') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span>Kasir</span>
                </a>
                <a href="#" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Pelanggan</span>
                </a>
                <a href="#" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>
                    <span>Inventaris</span>
                </a>
                <a href="#" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" x2="8" y1="13" y2="13"/>
                        <line x1="16" x2="8" y1="17" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span>Laporan</span>
                </a>
                <a href="#" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>Pengaturan</span>
                </a>
                <a href="{{ route('bahan-baku.index') }}"
        class="nav-item {{ request()->routeIs('bahan-baku.*') ? 'active' : '' }}">
        <i data-feather="package"></i>
        <span>Bahan Baku</span>
        </a>


        
            </nav>

            <div class="sidebar-footer">
                <button type="button" class="btn-logout" onclick="document.getElementById('logoutForm').submit()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" x2="9" y1="12" y2="12"/>
                    </svg>
                    <span>Keluar</span>
                </button>
            </div>
        </aside>
        
        <script>
document.addEventListener('DOMContentLoaded', function () {
  const body = document.body;
  const logoToggle = document.getElementById('menuToggle');   // ikon di sidebar header
  const btnSidebar = document.getElementById('btnSidebar');   // burger di topbar

  // restore state
  if (localStorage.getItem('sidebar') === 'collapsed') {
    body.classList.add('sidebar-collapsed');
  }

  function toggleSidebar(){
    body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar', body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
  }

  logoToggle?.addEventListener('click', toggleSidebar);
  btnSidebar?.addEventListener('click', toggleSidebar);

  // ===== Notification dropdown
  const btnNotif   = document.getElementById('btnNotif');
  const menuNotif  = document.getElementById('menuNotif');

  function closeAllDropdowns(){ menuNotif?.classList.remove('show'); btnNotif?.setAttribute('aria-expanded','false'); }

  btnNotif?.addEventListener('click', (e)=>{
    e.stopPropagation();
    const willShow = !menuNotif.classList.contains('show');
    closeAllDropdowns();
    if (willShow){ menuNotif.classList.add('show'); btnNotif.setAttribute('aria-expanded','true'); }
  });

  document.addEventListener('click', (e)=>{
    if (!menuNotif.contains(e.target) && e.target !== btnNotif){ closeAllDropdowns(); }
  });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeAllDropdowns(); });
});
</script>
