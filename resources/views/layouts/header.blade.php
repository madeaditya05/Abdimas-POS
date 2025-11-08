<header class="topbar" id="topbar">
  <div class="topbar-left">
    <button class="icon-btn" id="btnSidebar" type="button">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round">
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

  <div class="topbar-right">
    <!-- 🔔 Notification -->
    <div class="dropdown" id="notifDropdown">
      <button class="icon-btn" id="btnNotif" type="button">
        <svg class="bell-vanilla" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path class="bell-body"
            d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
          <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
        </svg>
        <span class="badge" id="notifBadge" style="display:none;">0</span>
      </button>

      <div class="dropdown-menu notifications" id="menuNotif" role="menu">
        <div class="dropdown-title">Notifikasi Stok</div>
        <div id="notifList" style="max-height:260px;overflow-y:auto;"></div>
      </div>
    </div>

    <!-- 👤 User -->
    <div class="user">
      <img class="avatar"
        src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?background=ffffff&color=111111&name='.urlencode(auth()->user()->name ?? 'User') }}"
        alt="avatar">
      <span class="user-name">{{ auth()->user()->name ?? 'John Doe' }}</span>
    </div>
  </div>
</header>

{{-- ====== Script untuk Notifikasi Dinamis ====== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const wrap       = document.getElementById('notifDropdown'); // wrapper .dropdown
  const btn        = document.getElementById('btnNotif');
  const menu       = document.getElementById('menuNotif');
  const list       = document.getElementById('notifList');
  const badge      = document.getElementById('notifBadge');
  const NOTIF_URL  = "{{ route('notifications') }}";

  let currentAbort = null;
  let pollTimer    = null;
  let lastHash     = "";

  // === Utils
  const showBadge = (n) => {
    if (!n) { badge.style.display = 'none'; badge.textContent = '0'; }
    else    { badge.style.display = 'inline-flex'; badge.textContent = n; }
  };
  const hashJson = (o) => { try { return JSON.stringify(o); } catch { return Math.random()+""; } };
  const renderEmpty = () => {
    list.innerHTML = `
      <div class="notif-item">
        <div class="notif-text">
          <div class="title">Tidak ada notifikasi</div>
          <div class="subtitle">Semua stok aman</div>
        </div>
      </div>`;
  };
  const renderItems = (items=[]) => {
    if (!items.length) return renderEmpty();
    const frag = document.createDocumentFragment();
    for (const it of items) {
      const a = document.createElement('a');
      a.href = "#";
      a.className = "notif-item";
      a.innerHTML = `
      <span class="notif-icon" style="background:${it.color}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
     stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.29 3.86 2.02 18.14A1 1 0 0 0 3 20h18a1 1 0 0 0 .86-1.5L13.71 3.86a1 1 0 0 0-1.72 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
      </span>
        <div class="notif-text">
          <div class="title">${it.title}</div>
          <div class="subtitle">${it.subtitle}</div>
        </div>`;
      frag.appendChild(a);
    }
    list.innerHTML = "";
    list.appendChild(frag);
  };

  // === Fetch cepat + hemat
  async function loadNotif({force=false} = {}) {
    if (currentAbort) currentAbort.abort();
    currentAbort = new AbortController();
    try {
      const res = await fetch(NOTIF_URL, {
        headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'application/json' },
        cache: 'no-store',
        signal: currentAbort.signal
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();

      const now = hashJson(data);
      if (!force && now === lastHash) return;
      lastHash = now;

      showBadge(data.count || 0);
      renderItems(data.items || []);
    } catch (e) {
      if (e.name !== 'AbortError') {
        console.error('Gagal load notif:', e);
        if (!list.innerHTML.trim()) renderEmpty();
      }
    }
  }

  function startPolling() {
    if (pollTimer) return;
    pollTimer = setInterval(() => loadNotif(), 15000);
  }
  function stopPolling() {
    if (!pollTimer) return;
    clearInterval(pollTimer);
    pollTimer = null;
  }

  // === Dropdown yang pasti kebuka
  const open  = () => wrap.classList.add('open');
  const close = () => wrap.classList.remove('open');
  const toggle= () => wrap.classList.toggle('open');

  // klik tombol → toggle + fetch force
  btn?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggle();
    loadNotif({force:true});
  });

  // klik di dalam menu → jangan tutup
  menu?.addEventListener('click', (e) => e.stopPropagation());

  // klik di luar wrapper → tutup (pakai composedPath untuk robust ke SVG)
  document.addEventListener('click', (e) => {
    const path = e.composedPath ? e.composedPath() : [];
    if (!path.includes(wrap)) close();
  });

  // ESC untuk tutup
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });

  // Hemat baterai saat tab disembunyikan
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stopPolling();
    else { loadNotif({force:true}); startPolling(); }
  });

  // Init
  loadNotif({force:true});  // preload cepat (badge & isi)
  startPolling();           // polling cepat
});
</script>


