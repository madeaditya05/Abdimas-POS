<svg class="cup-svg" viewBox="0 0 420 320" aria-hidden="true">
  <defs>
    <linearGradient id="cupBody" x1="0" x2="0" y1="0" y2="1">
      <stop offset="0%" stop-color="#ffffff"/>
      <stop offset="100%" stop-color="#f1e4d6"/>
    </linearGradient>
    <linearGradient id="handleGrad" x1="0" x2="1">
      <stop offset="0%" stop-color="#ecd9c6"/>
      <stop offset="100%" stop-color="#dcc5ae"/>
    </linearGradient>
    <linearGradient id="coffeeGrad" x1="0" x2="0" y1="0" y2="1">
      <stop offset="0%" stop-color="#b98256"/>
      <stop offset="100%" stop-color="#603c1c"/>
    </linearGradient>
    <radialGradient id="gloss" cx="35%" cy="25%" r="60%">
      <stop offset="0%" stop-color="rgba(255,255,255,.55)"/>
      <stop offset="60%" stop-color="rgba(255,255,255,.12)"/>
      <stop offset="100%" stop-color="transparent"/>
    </radialGradient>

    <clipPath id="cupClip">
      <rect x="70" y="68" rx="36" ry="36" width="280" height="190"/>
    </clipPath>
  </defs>

  <ellipse cx="210" cy="300" rx="120" ry="18" fill="#decbb5"/>
  <g>
    <rect x="60" y="56" width="300" height="36" rx="18" fill="#fff" stroke="#e6d7c3"/>
    <rect x="70" y="72" width="280" height="200" rx="40" ry="40" fill="url(#cupBody)" stroke="#e6d7c3"/>
    <path d="M335 115 q60 30 60 80 t-60 80"
          fill="none" stroke="url(#handleGrad)" stroke-width="16" stroke-linecap="round"/>
    <ellipse cx="150" cy="110" rx="60" ry="28" fill="url(#gloss)" opacity=".55"/>
  </g>

  <g clip-path="url(#cupClip)">
    <g class="liquid" transform="translate(0,35)">
      <rect x="70" y="90" width="280" height="190" fill="url(#coffeeGrad)"/>
      <g class="waves" transform="translate(0,70)">
        <path class="wavePath"
          d="M0 0 C30 -8 60 8 90 0 S150 -8 180 0 S240 8 270 0 S330 -8 360 0 S420 8 450 0"
          fill="none" stroke="rgba(255,255,255,.35)" stroke-width="10" stroke-linecap="round"/>
      </g>
    </g>
  </g>

  <g opacity=".35">
    <circle class="bubble" cx="110" cy="96" r="5" fill="#fff"/>
    <circle class="bubble" cx="150" cy="98" r="4" fill="#fff"/>
    <circle class="bubble" cx="190" cy="96" r="5" fill="#fff"/>
    <circle class="bubble" cx="230" cy="98" r="4" fill="#fff"/>
    <circle class="bubble" cx="270" cy="96" r="5" fill="#fff"/>
  </g>

  <g class="steam">
    <path d="M150 60 q-10 -30 0 -60" stroke="#bca28a" stroke-width="3" fill="none"/>
    <path d="M190 60 q10 -35 0 -65" stroke="#bca28a" stroke-width="3" fill="none"/>
    <path d="M230 60 q-8 -35 8 -65" stroke="#bca28a" stroke-width="3" fill="none"/>
  </g>
</svg>
