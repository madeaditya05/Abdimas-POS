{{-- Feather Icons --}}
<script src="https://unpkg.com/feather-icons"></script>

{{-- Script global: theme, sidebar, notification, icons --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.__pastaShellReady) return;
  window.__pastaShellReady = true;

  try {
    var savedTheme = localStorage.getItem('uiTheme') || 'day';
    document.body.classList.toggle('theme-coffee', savedTheme === 'coffee');
    document.body.classList.toggle('theme-day', savedTheme !== 'coffee');
  } catch (e) {}

  if (window.feather) {
    feather.replace({ width: 20, height: 20, 'stroke-width': 2 });
  }

  const body = document.body;
  const sidebar = document.getElementById('sidebar');
  const btnSidebar = document.getElementById('btnSidebar');
  const mobileSidebar = window.matchMedia('(max-width: 900px)');
  const SIDEBAR_KEY = 'cofit.sidebar.state';

  function syncSidebarButton(){
    btnSidebar?.setAttribute('aria-controls', 'sidebar');
    btnSidebar?.setAttribute('aria-expanded', body.classList.contains('sidebar-open') ? 'true' : 'false');
  }

  function closeMobileSidebar(){
    body.classList.remove('sidebar-open');
    syncSidebarButton();
  }

  function restoreDesktopSidebar(){
    body.classList.remove('sidebar-open');
    const saved = localStorage.getItem(SIDEBAR_KEY) || localStorage.getItem('sidebar');
    body.classList.toggle('sidebar-collapsed', saved === 'collapsed');
    syncSidebarButton();
  }

  function normalizeSidebarMode(){
    if (mobileSidebar.matches) {
      body.classList.remove('sidebar-collapsed');
      closeMobileSidebar();
    } else {
      restoreDesktopSidebar();
    }
  }

  function toggleSidebar(){
    if (mobileSidebar.matches) {
      body.classList.toggle('sidebar-open');
      body.classList.remove('sidebar-collapsed');
      syncSidebarButton();
      return;
    }

    body.classList.toggle('sidebar-collapsed');
    const state = body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded';
    localStorage.setItem(SIDEBAR_KEY, state);
    localStorage.setItem('sidebar', state);
    closeMobileSidebar();
  }

  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('[data-action="toggle-sidebar"], #btnSidebar, #menuToggle');
    if (toggle) {
      e.preventDefault();
      toggleSidebar();
      return;
    }

    if (!mobileSidebar.matches || !body.classList.contains('sidebar-open')) return;
    if (e.target.closest('#sidebar')) return;
    closeMobileSidebar();
  });

  sidebar?.addEventListener('click', function(e){
    if (!mobileSidebar.matches) return;
    if (e.target.closest('a.nav-item, a.subnav-item, .sidebar-footer a')) {
      closeMobileSidebar();
    }
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeMobileSidebar();
  });

  if (mobileSidebar.addEventListener) {
    mobileSidebar.addEventListener('change', normalizeSidebarMode);
  } else {
    mobileSidebar.addListener(normalizeSidebarMode);
  }
  normalizeSidebarMode();

  (function(){
    const menu = document.getElementById('menuNotif');
    if (!menu) return;

    function closeAll(){
      menu.classList.remove('show');
      const btn = document.getElementById('btnNotif');
      btn?.setAttribute('aria-expanded','false');
    }

    document.addEventListener('click', function(e){
      const btn = e.target.closest('#btnNotif');
      const insidePanel = e.target.closest('#menuNotif');

      if (btn) {
        e.preventDefault();
        const willShow = !menu.classList.contains('show');
        closeAll();
        if (willShow){
          menu.classList.add('show');
          btn.setAttribute('aria-expanded','true');
        }
        return;
      }

      if (!insidePanel) closeAll();
    });

    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape') closeAll();
    });
  })();

  document.addEventListener('click', function(e){
    var themeToggle = e.target.closest('[data-action="toggle-theme"], #btnTheme');
    if (!themeToggle) return;
    var isCoffee = document.body.classList.contains('theme-coffee');
    document.body.classList.toggle('theme-coffee', !isCoffee);
    document.body.classList.toggle('theme-day', isCoffee);
    try { localStorage.setItem('uiTheme', !isCoffee ? 'coffee' : 'day'); } catch(e){}
  });
});
</script>
