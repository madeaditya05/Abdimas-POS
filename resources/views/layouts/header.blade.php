<!-- TOPBAR tanpa logo brand -->
<header class="topbar" id="topbar">
  <div class="topbar-left">
    <!-- Tombol burger untuk hide/show sidebar -->
    <button class="icon-btn" id="btnSidebar" aria-label="Toggle sidebar">
      <!-- burger -->
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>
  </div>

  <!-- Search -->
  <div class="topbar-center">
    <form class="search" action="#" method="GET">
      <span class="search-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </span>
      <input type="text" name="q" placeholder="Search now" />
    </form>
  </div>

  <!-- Notif + User -->
  <div class="topbar-right">
    <div class="dropdown" id="notifDropdown">
      <button class="icon-btn" id="btnNotif" aria-haspopup="true" aria-expanded="false" aria-controls="menuNotif">
        <!-- bell -->
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 8a6 6 0 1 1 12 0c0 7 3 5 3 8H3c0-3 3-1 3-8"></path>
          <path d="M10 21a2 2 0 0 0 4 0"></path>
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
      <img class="avatar" src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?background=4A9E6B&color=fff&name='.urlencode(auth()->user()->name ?? 'User') }}" alt="avatar">
      <span class="user-name">{{ auth()->user()->name ?? 'John Doe' }}</span>
    </div>
  </div>
</header>
