<!-- TOPBAR tanpa logo brand -->
<header class="topbar" id="topbar">
  <div class="topbar-left">
    <!-- Tombol burger untuk hide/show sidebar -->
    <button class="icon-btn" id="btnSidebar" type="button"
        data-action="toggle-sidebar" aria-label="Toggle sidebar">
      <!-- burger -->
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>

    <!-- Switch Theme Day/Coffee -->
    <button class="icon-btn" id="btnTheme" data-action="toggle-theme" aria-label="Toggle theme" title="Switch Day/Coffee">
      <!-- Sun (siang) -->
      <svg class="icon-sun" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="5"></circle>
        <line x1="12" y1="1" x2="12" y2="3"></line>
        <line x1="12" y1="21" x2="12" y2="23"></line>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
        <line x1="1" y1="12" x2="3" y2="12"></line>
        <line x1="21" y1="12" x2="23" y2="12"></line>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
      </svg>

      <!-- Moon (malam) -->
      <svg class="icon-moon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"></path>
      </svg>
    </button>

    <!-- Search (dipindah ke kiri, tetap satu & compact) -->
    <form class="search search--compact" action="#" method="GET">
      <span class="search-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </span>
      <input type="text" name="q" placeholder="Search now" value="{{ request('q') }}" />
    </form>
  </div>

  <!-- (kosongkan center agar tidak ada search kedua; struktur dipertahankan) -->
  <div class="topbar-center">
    <!-- intentionally left blank to keep layout structure -->
  </div>

  <!-- Notif + User -->
  <div class="topbar-right">
    <div class="dropdown" id="notifDropdown">
      <button class="icon-btn" id="btnNotif" type="button"
        aria-haspopup="true" aria-expanded="false" aria-controls="menuNotif">
        <!-- Bell with hover animation (vanilla) -->
        <svg class="bell-vanilla" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <!-- ring lines -->
          <path class="bell-ringline" d="M22 8c0-2.3-.8-4.3-2-6"></path>
          <path class="bell-ringline" d="M4 2C2.8 3.7 2 5.7 2 8"></path>
          <!-- clapper -->
          <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
          <!-- body -->
          <path class="bell-body"
            d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>
        </svg>
        <span class="badge" id="notifBadge">3</span>
      </button>

      <!-- Dropdown notif -->
      <div class="dropdown-menu notifications" id="menuNotif" role="menu">
        <div class="dropdown-title">Notifications</div>

        <a href="#" class="notif-item">
          <span class="notif-icon" style="background:#22c55e">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          </span>
          <div class="notif-text">
            <div class="title">Application Error</div>
            <div class="subtitle">Just now</div>
          </div>
        </a>

        <a href="#" class="notif-item">
          <span class="notif-icon" style="background:#f59e0b">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
          </span>
          <div class="notif-text">
            <div class="title">Settings</div>
            <div class="subtitle">Private message</div>
          </div>
        </a>

        <a href="#" class="notif-item">
          <span class="notif-icon" style="background:#3b82f6">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="7" r="4"></circle><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"></path></svg>
          </span>
          <div class="notif-text">
            <div class="title">New user registration</div>
            <div class="subtitle">2 days ago</div>
          </div>
        </a>
      </div>
    </div>

    <!-- User -->
    <div class="user">
      <img class="avatar" src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?background=ffffff&color=111111&name='.urlencode(auth()->user()->name ?? 'User') }}" alt="avatar">
      <span class="user-name">{{ auth()->user()->name ?? 'John Doe' }}</span>
    </div>
  </div>
</header>
