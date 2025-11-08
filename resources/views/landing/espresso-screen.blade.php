<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Espresso Today</title>
  <link rel="stylesheet" href="{{ asset('assets/espresso.css') }}">
  <style>
    /* Halaman kosong, fullscreen, center */
    html,body{height:100%;margin:0}
    body{
      background:#fff; /* ganti ke #FAF7F2 kalau mau krem */
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
      color:#3a2a22;
      display:flex; align-items:center; justify-content:center;
    }
    .screen-wrap{ text-align:center; }
    .espresso-bottle{ width:min(18vw,180px); height:min(50vh,520px); }
    .espresso-count{ font-size:min(5vw,44px); font-weight:700; margin-top:18px; }
    .espresso-sub{ font-size:min(3vw,18px); opacity:.7; margin-top:6px; }
    .badge{ display:inline-block; margin-left:10px; padding:6px 10px; border-radius:999px; background:#d97706; color:#fff; font-size:min(2.6vw,14px); vertical-align:middle;}
    /* opsional: sembunyikan kursor setelah 3 detik mouse idle (untuk layar display) */
    body.idle { cursor: none; }
  </style>
</head>
<body>
  <div class="screen-wrap">
    <h1 class="espresso-title">ESPRESSO SHOTS TODAY</h1>

    <div class="glass-cup">
      <div class="espresso-liquid" id="espresso-fill" style="height: {{ $percent }}%"></div>
      <div class="glass-reflection"></div>
    </div>

    <div class="espresso-count" id="espresso-count">
      {{ min((int)$fill, 30) }}/30 shot
      @if($totalShots >= 30)
        <span class="badge">30+</span>
      @endif
    </div>
    <div class="espresso-sub">Hari ini</div>
  </div>

  <script>
    async function fetchEspressoData(){
      try{
        const res = await fetch("{{ route('espresso.data') }}", {cache:'no-store'});
        const data = await res.json();
        document.getElementById('espresso-fill').style.height = data.percent + '%';
        const text = `${Math.min(Math.round(data.fill), 30)}/30 shot`;
        document.getElementById('espresso-count').innerHTML =
          text + (data.totalShots >= 30 ? ' <span class="badge">30+</span>' : '');
      }catch(e){ console.error(e); }
    }
    fetchEspressoData();
    setInterval(fetchEspressoData, 5000);
  </script>
</body>

</html>
