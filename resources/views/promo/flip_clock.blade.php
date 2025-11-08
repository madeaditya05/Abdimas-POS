@extends('layouts.blank')
@section('title','Promo Flip Display')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/promosi_flip.css') }}">
@endpush

@section('content')
<div class="scene">

  {{-- Header --}}
  <header class="brand">
    <div class="logo"></div>
    <div class="title">Co-FIT EV Coffee • Flip Display</div>
  </header>

  <main class="grid">

    {{-- ===== LEFT: BIG FLIP CLOCK (Sales) ===== --}}
    <section class="sales-panel">
      <div class="sales-wood">
        <div class="sales-head">
          <h3>Penjualan <span id="scope-label">Hari Ini</span></h3>
          <small id="today-label"></small>
        </div>

        <div class="sales-body">
          <div class="cup-steam" aria-hidden="true">
            <svg viewBox="0 0 140 160" class="steam">
              <path d="M45 120 C35 90 60 70 45 40" />
              <path d="M70 120 C80 90 60 70 70 40" />
              <path d="M95 120 C85 90 110 70 95 40" />
            </svg>
            <div class="cup"></div>
          </div>

          <div id="big-flip" class="flip-clock big"></div>
        </div>
        <div class="hint">Tekan <kbd>F</kbd> untuk fullscreen • flip berbunyi “klik”</div>
      </div>
    </section>

    {{-- ===== RIGHT: MINI FLIP MENUS ===== --}}
    <aside class="menu-panel">
      <div class="menu-title">Menu Pilihan</div>

      <div class="mini-grid">
        <div class="mini" id="m1">
          <div class="mini-flip"></div>
        </div>
        <div class="mini" id="m2">
          <div class="mini-flip"></div>
        </div>
        <div class="mini" id="m3">
          <div class="mini-flip"></div>
        </div>
      </div>

      <div class="menu-footer">
        Scan QR untuk lihat menu & poin member • Diskon QRIS s/d 10%
      </div>
    </aside>

  </main>

  {{-- Background layers --}}
  <div class="bg-leaves" aria-hidden="true"></div>
  <div class="beans" id="beans" aria-hidden="true"></div>
</div>
@endsection

@push('scripts')
<script>
/* ========= helpers ========= */
const $ = (s)=>document.querySelector(s);
const $$ = (s)=>document.querySelectorAll(s);
$('#today-label').textContent = new Date().toLocaleDateString('id-ID',
  {weekday:'long', day:'2-digit', month:'long', year:'numeric'});

/* ========= Fullscreen ========= */
addEventListener('keydown',e=>{
  if(e.key.toLowerCase()==='f'){
    if(!document.fullscreenElement) document.documentElement.requestFullscreen();
    else document.exitFullscreen();
  }
});

/* ========= Background beans ========= */
const beans = $('#beans');
for(let i=0;i<10;i++){
  const b=document.createElement('div');
  b.className='bean';
  b.style.setProperty('--x',Math.random()*100+'vw');
  b.style.setProperty('--y',Math.random()*100+'vh');
  b.style.setProperty('--r',(-20+Math.random()*40)+'deg');
  b.style.setProperty('--d',(18+Math.random()*8)+'s');
  beans.appendChild(b);
}

/* ========= Click sound via WebAudio (no file needed) ========= */
const ctx = new (window.AudioContext||window.webkitAudioContext)();
function playClick(){
  const o = ctx.createOscillator();
  const g = ctx.createGain();
  o.connect(g); g.connect(ctx.destination);
  o.type='square'; o.frequency.value=1400;
  const now=ctx.currentTime;
  g.gain.setValueAtTime(0.12, now);
  g.gain.exponentialRampToValueAtTime(0.0001, now+0.05);
  o.start(now); o.stop(now+0.06);
}

/* ========= FLIP CLOCK CORE ========= */
function createDigit(parent, size='big'){
  const d = document.createElement('div');
  d.className = 'digit '+size;
  d.innerHTML = `
    <div class="card">
      <div class="top">0</div>
      <div class="bottom">0</div>
      <div class="flip">
        <div class="flip-top">0</div>
        <div class="flip-bottom">0</div>
      </div>
    </div>`;
  parent.appendChild(d);
  return d;
}
function setDigitInstant(digitEl, val){
  digitEl.querySelector('.top').textContent    = val;
  digitEl.querySelector('.bottom').textContent = val;
}
function flipToDigit(digitEl, val){
  const top   = digitEl.querySelector('.top');
  const bottom= digitEl.querySelector('.bottom');
  const fTop  = digitEl.querySelector('.flip-top');
  const fBot  = digitEl.querySelector('.flip-bottom');

  if(bottom.textContent == val) return;
  fTop.textContent = top.textContent;
  fBot.textContent = val;
  digitEl.classList.add('play');
  playClick();

  digitEl.addEventListener('animationend', ()=>{
    digitEl.classList.remove('play');
    top.textContent = val;
    bottom.textContent = val;
  }, {once:true});
}

/* ========= BIG SALES CLOCK ========= */
let SALES_INIT = {{ (int)($salesToday ?? 0) }}; // kirim dari controller kalau ada
if(isNaN(SALES_INIT)) SALES_INIT = 0;

const big = $('#big-flip');
const bigDigits = [];
const pad = (n)=>String(n).padStart(3,'0'); // 3 digit default
pad(SALES_INIT).split('').forEach(()=> bigDigits.push(createDigit(big,'big')) );
function renderBigInstant(num){
  const s = pad(num);
  s.split('').forEach((v,i)=>setDigitInstant(bigDigits[i],v));
}
function flipBigTo(num){
  const oldS = pad(SALES_INIT);
  const newS = pad(num);
  [...newS].forEach((v,i)=> flipToDigit(bigDigits[i], v));
  SALES_INIT = num;
}
renderBigInstant(SALES_INIT);

// simulasi update setiap 8s (ganti ke event order realtime nanti)
setInterval(()=>{
  const inc = Math.floor(Math.random()*4); // 0..3
  if(inc>0) flipBigTo(SALES_INIT + inc);
}, 8000);

/* ========= MINI FLIP MENUS ========= */
const menus = [
  {name:'Espresso', img:'☕', desc:'Shot pekat & bold'},
  {name:'Latte Art', img:'🥛', desc:'Susu creamy & foam'},
  {name:'Mocha', img:'🍫', desc:'Cokelat + espresso'},
  {name:'Cold Brew', img:'🧊', desc:'Seduh dingin 18 jam'},
  {name:'Matcha Latte', img:'🍵', desc:'Matcha Jepang lembut'},
  {name:'Non-Coffee', img:'🥤', desc:'Yakult, tea, soda'},
  {name:'Croissant', img:'🥐', desc:'Renyah hangat'},
  {name:'Cookies', img:'🍪', desc:'Choco chip legit'},
];

function createMini(el, offset=0){
  const box = el.querySelector('.mini-flip');
  box.innerHTML = `
    <div class="mini-card">
      <div class="front"><span class="ico"></span><div class="txt"><h4></h4><p></p></div></div>
      <div class="back"><span class="ico"></span><div class="txt"><h4></h4><p></p></div></div>
    </div>`;
  let idx = offset % menus.length;

  function setFace(face, item){
    face.querySelector('.ico').textContent = item.img;
    face.querySelector('h4').textContent   = item.name;
    face.querySelector('p').textContent    = item.desc;
  }
  function flip(){
    const card = box.querySelector('.mini-card');
    const front= card.querySelector('.front');
    const back = card.querySelector('.back');
    const next = menus[idx % menus.length];
    setFace(back, next);
    card.classList.add('flip');
    setTimeout(()=>{
      // swap content ke front
      setFace(front, next);
      card.classList.remove('flip');
    }, 700);
    idx++;
  }
  // init
  setFace(box.querySelector('.front'), menus[idx++ % menus.length]);
  setFace(box.querySelector('.back'),  menus[idx % menus.length]);
  // auto flip setiap 7s
  setInterval(flip, 7000);
}

createMini($('#m1'), 0);
createMini($('#m2'), 2);
createMini($('#m3'), 4);
</script>
@endpush
