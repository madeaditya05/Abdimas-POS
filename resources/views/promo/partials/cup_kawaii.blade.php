<svg class="cup-kawaii" viewBox="0 0 520 360" aria-hidden="true">
  <defs>
    <!-- warna kopi -->
    <linearGradient id="kawaiiCoffee" x1="0" x2="0" y1="0" y2="1">
      <stop offset="0%" stop-color="#b98256"/>
      <stop offset="100%" stop-color="#603c1c"/>
    </linearGradient>
    <!-- area isi kopi -->
    <clipPath id="cupHole">
      <!-- rongga cangkir (sesuaikan jika mau): -->
      <rect x="180" y="120" width="220" height="150" rx="20" ry="20"/>
    </clipPath>
  </defs>

  <!-- Bayangan saucer -->
  <ellipse cx="260" cy="335" rx="140" ry="16" fill="#e5d6c7"/>

  <!-- UAP (3 garis) -->
  <g class="steam">
    <path d="M240 70 C230 45 250 30 240 10" stroke="#2b2017" stroke-width="8" stroke-linecap="round" fill="none"/>
    <path d="M270 70 C280 45 260 30 270 10" stroke="#2b2017" stroke-width="8" stroke-linecap="round" fill="none"/>
    <path d="M300 70 C290 45 310 30 300 10" stroke="#2b2017" stroke-width="8" stroke-linecap="round" fill="none"/>
  </g>

  <!-- CANGKIR (silhouette clean) -->
  <g class="cup">
    <!-- piring kecil -->
    <rect x="190" y="300" width="170" height="12" rx="6" fill="#2b2017" opacity=".85"/>

    <!-- badan cangkir -->
    <rect x="150" y="150" width="260" height="160" rx="36" ry="36" fill="#ffffff" stroke="#2b2017" stroke-width="12"/>
    <!-- bibir cangkir -->
    <rect x="150" y="135" width="260" height="30" rx="14" ry="14" fill="#ffffff" stroke="#2b2017" stroke-width="6"/>

    <!-- handle -->
    <path d="M410 170
             q60 30 60 60
             t-60 60"
          fill="none" stroke="#2b2017" stroke-width="12" stroke-linecap="round"/>

    <!-- isi kopi (clip ke cupHole) -->
    <g clip-path="url(#cupHole)">
      <!-- foam putih -->
      <rect x="180" y="135" width="220" height="20" fill="#fff" opacity=".92"/>
      <!-- permukaan meniskus -->
      <path d="M180 155 Q290 175 400 155 V270 H180 Z" fill="url(#kawaiiCoffee)" opacity=".95"/>
      <!-- blok isi yang akan diskalakan -->
      <rect class="liquid" x="180" y="170" width="220" height="130" fill="url(#kawaiiCoffee)" transform-origin="290 300" />
      <!-- gelombang halus -->
      <g class="waves" transform="translate(0,165)">
        <path d="M170 0 C200 -8 230 8 260 0 S320 -8 350 0 S410 8 440 0"
              fill="none" stroke="rgba(255,255,255,.4)" stroke-width="9" stroke-linecap="round"/>
      </g>
    </g>

    <!-- wajah kawaii -->
    <!-- pipi -->
    <ellipse cx="230" cy="235" rx="14" ry="9" fill="#f6b6b6" opacity=".7"/>
    <ellipse cx="350" cy="235" rx="14" ry="9" fill="#f6b6b6" opacity=".7"/>
    <!-- mata -->
    <g class="eyes">
      <g class="eye">
        <circle cx="250" cy="230" r="10" fill="#2b2017"/>
        <rect class="lid" x="240" y="218" width="20" height="12" rx="6" fill="#ffffff"/>
      </g>
      <g class="eye">
        <circle cx="330" cy="230" r="10" fill="#2b2017"/>
        <rect class="lid" x="320" y="218" width="20" height="12" rx="6" fill="#ffffff"/>
      </g>
    </g>
    <!-- senyum -->
    <path d="M270 250 q20 18 40 0" stroke="#2b2017" stroke-width="6" stroke-linecap="round" fill="none"/>
  </g>

  <!-- hearts lucu muncul sesekali -->
  <g class="hearts">
    <path d="M120 160
             c0 -12 16 -18 24 -6
             c8 -12 24 -6 24 6
             c0 18 -24 26 -24 26
             c0 0 -24 -8 -24 -26z"
          fill="#ff9db6" opacity=".75"/>
  </g>
</svg>
