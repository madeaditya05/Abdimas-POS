{{-- Feather Icons --}}
  <script src="https://unpkg.com/feather-icons"></script>

  {{-- Script global (sidebar toggle, notif dropdown, init icons) --}}
  <script>
document.addEventListener('DOMContentLoaded', function () {
  /* ==== THEME INIT (baru, tidak mengubah logika lain) ==== */
  try {
    var saved = localStorage.getItem('uiTheme') || 'day';
    document.body.classList.toggle('theme-coffee', saved === 'coffee');
    document.body.classList.toggle('theme-day', saved !== 'coffee');
  } catch (e) {}

  // Feather init
  feather.replace({ width: 20, height: 20, 'stroke-width': 2 });

  // Sidebar toggle
  const body = document.body;
  const logoToggle = document.getElementById('menuToggle');
  const btnSidebar = document.getElementById('btnSidebar');

  if (localStorage.getItem('sidebar') === 'collapsed') {   // ✅ sudah benar
    body.classList.add('sidebar-collapsed');
  }
  function toggleSidebar(){
    body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar',
      body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded'
    );
  }
  logoToggle?.addEventListener('click', toggleSidebar);
  btnSidebar?.addEventListener('click', toggleSidebar);

  /* === Sidebar toggle (delegated, supaya klik di SVG/area tombol tetap kena) === */
  document.addEventListener('click', function (e) {
    const t = e.target.closest('[data-action="toggle-sidebar"], #btnSidebar, #menuToggle');
    if (!t) return;
    e.preventDefault();
    toggleSidebar();
  });

  // === Notification dropdown (robust & delegated) ===
  (function(){
    const menu = document.getElementById('menuNotif');
    if (!menu) return;

    function closeAll(){
      menu.classList.remove('show');
      const btn = document.getElementById('btnNotif');
      btn?.setAttribute('aria-expanded','false');
    }

    // Delegasi klik: bekerja walau klik di SVG/badge di dalam tombol
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
        return; // jangan terus ke “klik di luar”
      }

      // klik di luar panel
      if (!insidePanel) closeAll();
    });

    document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') closeAll(); });
  })();


  /* ==== THEME TOGGLE HANDLER (baru) ==== */
  document.addEventListener('click', function(e){
    var t = e.target.closest('[data-action="toggle-theme"], #btnTheme');
    if (!t) return;
    var isCoffee = document.body.classList.contains('theme-coffee');
    document.body.classList.toggle('theme-coffee', !isCoffee);
    document.body.classList.toggle('theme-day', isCoffee);
    try { localStorage.setItem('uiTheme', !isCoffee ? 'coffee' : 'day'); } catch(e){}
  });
});
</script>