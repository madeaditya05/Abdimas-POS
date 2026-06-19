            <aside class="sidebar" id="sidebar">
                        <div class="sidebar-header">
            <div class="sidebar-logo">
                <!-- tombol toggle (klik logo atau ikon menu) -->
                <div class="sidebar-logo-icon" id="menuToggle" role="button" tabindex="0" aria-label="Toggle sidebar">
                <!-- Logo gambar dari public/images -->
                <img src="{{ asset('images/foto logo pasta nafisa.png') }}" alt="Pasta Nafisa" class="sidebar-logo-img">
                </div>
                <div class="sidebar-logo-text">

                <h1>Pasta Nafisa</h1>

                <p>Kasir Dashboard</p>

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
                
                <a href="/kasir" class="nav-item {{ request()->is('kasir') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span>Kasir</span>
                </a>
                <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M8 13h8"/>
                        <path d="M8 17h5"/>
                    </svg>
                    <span>Piutang Invoice</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="{{ route('panel.switch', ['panel' => 'owner']) }}" class="btn-logout {{ request()->is('dashboard') ? 'active' : '' }}" style="text-decoration:none; margin-bottom:12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <path d="M3 10h18"/>
                        <path d="M8 15h.01"/>
                        <path d="M12 15h4"/>
                    </svg>
                    <span>Panel Owner</span>
                </a>
                <a href="{{ route('logout.reconcile') }}" class="btn-logout" style="text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" x2="9" y1="12" y2="12"/>
                    </svg>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>
