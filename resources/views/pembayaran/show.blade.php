@extends('layouts.customer')
@section('title', 'Layar Pembayaran')

@section('content')
<div class="cust-wrap">
  <header class="cust-header">
    <div class="brand">
      <div class="brand-badge">
        <img src="{{ asset('images/foto logo pasta nafisa.png') }}" alt="Pasta Nafisa" class="brand-logo">
      </div>
      <div>
        <div class="brand-title">Pasta Nafisa</div>
        <div id="judulOrder" class="brand-sub">Menunggu order...</div>
      </div>
    </div>
    <div class="badges">
      <div id="badgeStatus" class="pill warn">Idle</div>
    </div>
  </header>

  <section class="cust-main">
    <div class="c-card">
      <div class="c-head">
        <h2>Daftar Belanja</h2>
        <p class="c-sub">Akan terisi otomatis saat kasir memproses</p>
      </div>
      <div id="listBelanja" class="bill-wrap"></div>
      <div class="bill-total">
        <span>Total</span>
        <span id="totalText">Rp 0</span>
      </div>
    </div>

    <div class="c-card">
      <div class="c-head">
        <h2>Scan untuk Bayar</h2>
        <p class="c-sub">QRIS / VA akan tampil sesuai pilihan kasir</p>
      </div>

      <div class="pay-wrap">
        <div class="qr-box">
          <img id="imgQris" class="qr-img" alt="QRIS" style="display:none;">
          <div id="fallbackQris" class="qr-fallback" style="display:none;">
            <p>QR String:</p>
            <code id="qrString"></code>
          </div>
          <div id="infoQris" class="info">Menunggu metode pembayaran...</div>
        </div>

        <div id="boxVa" class="va-box" style="display:none;">
          <div>Bank <span id="vaBank" class="va-bank"></span></div>
          <div id="vaNumber" class="va-no">-</div>
        </div>

        <div id="stateRow" class="state">
          <span class="dot"></span>
          <span id="stateText">Menunggu order dari kasir...</span>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
const IDLE_CHECK_MS  = 2000;
const ACTIVE_POLL_MS = 2000;
const KODE_LAYAR = @json($kode ?? 'utama');

const el = {
  title:  document.getElementById('judulOrder'),
  badge:  document.getElementById('badgeStatus'),
  list:   document.getElementById('listBelanja'),
  total:  document.getElementById('totalText'),
  info:   document.getElementById('infoQris'),
  img:    document.getElementById('imgQris'),
  fb:     document.getElementById('fallbackQris'),
  qrstr:  document.getElementById('qrString'),
  boxVa:  document.getElementById('boxVa'),
  vaBank: document.getElementById('vaBank'),
  vaNo:   document.getElementById('vaNumber'),
  state:  document.getElementById('stateRow'),
  stateText: document.getElementById('stateText'),
};

let TIMER=null, HOLD={active:false,until:0}, LAST=null;

function rupiah(n){ return 'Rp ' + (n||0).toLocaleString('id-ID'); }
function isPaid(s){ return ['paid','settlement','capture','success'].includes(String(s||'').toLowerCase()); }

function setBadge(stat){
  const s = String(stat||'').toLowerCase();
  const map = {paid:'ok', settlement:'ok', capture:'ok', success:'ok', expired:'bad', cancelled:'bad', pending:'warn', idle:'warn'};
  el.badge.textContent = s ? s.charAt(0).toUpperCase()+s.slice(1) : 'Idle';
  el.badge.className = 'pill ' + (map[s] || 'warn');
  el.state.className = 'state ' + (isPaid(s) ? 'ok' : (s==='expired'||s==='cancelled') ? 'bad' : '');
  el.stateText.textContent =
    isPaid(s)       ? 'Pembayaran berhasil.' :
    s==='expired'   ? 'Transaksi kedaluwarsa.' :
    s==='cancelled' ? 'Transaksi dibatalkan.' :
    s==='pending'   ? 'Menunggu pembayaran...' :
                      'Menunggu order dari kasir...';
}

function renderBelanja(items){
  el.list.innerHTML = '';
  if (!items || !items.length) {
    el.list.innerHTML = '<div class="info" style="width:100%;text-align:center;">Tidak ada item.</div>';
    return;
  }
  items.forEach(i=>{
    const row = document.createElement('div');
    row.className='bill-item';
    row.innerHTML = `
      <div>
        <div class="bill-title">${i.name}</div>
        <div class="bill-meta">x${i.qty}</div>
      </div>
      <div class="bill-line">${rupiah(i.line_total)}</div>
    `;
    el.list.appendChild(row);
  });
}

function showQris(url, str){
  el.img.style.display='none'; el.fb.style.display='none'; el.info.style.display='block';
  if (url || str) el.info.style.display='none';
  if (url){ el.img.src = url; el.img.style.display='block'; }
  else if (str){ el.qrstr.textContent = str; el.fb.style.display='block'; }
}
function showVa(bankRaw, no){
  if (!no){ el.boxVa.style.display='none'; return; }
  let bank = (bankRaw||'').toUpperCase();
  if (bank.startsWith('VA_')) bank = bank.split('_')[1];
  el.boxVa.style.display='block';
  el.vaBank.textContent = bank;
  el.vaNo.textContent   = no;
}

function clearUI(){
  el.title.textContent='Menunggu order...';
  el.list.innerHTML=''; el.total.textContent='Rp 0';
  el.img.style.display='none'; el.fb.style.display='none';
  el.info.style.display='block'; el.info.textContent='Menunggu metode pembayaran...';
  el.boxVa.style.display='none'; setBadge('idle');
}

async function fetchDisplay(){
  const res = await fetch(`{{ url('/api/public/display') }}/${encodeURIComponent(KODE_LAYAR)}`, { headers:{'Accept':'application/json'} });
  if (!res.ok) throw new Error('HTTP '+res.status);
  return res.json();
}

function render(data){
  LAST = data;
  el.title.textContent = data.order_no ? ('Order ' + data.order_no) : 'Menunggu order...';
  renderBelanja(data.items || []);
  el.total.textContent = rupiah(data.grand_total || 0);
  setBadge(data.status || 'pending');
  showQris(data?.qris?.qr_url || null, data?.qris?.qr_string || null);
  showVa(data?.va?.bank || null, data?.va?.va_number || null);
}

async function idleLoop(){
  try{
    const d = await fetchDisplay();
    // Aktif hanya jika ada order dan status pending
    if (d && d.order_no && String(d.status).toLowerCase() === 'pending') {
      render(d);
      schedule(activeLoop, ACTIVE_POLL_MS);
      return;
    }
    clearUI(); schedule(idleLoop, IDLE_CHECK_MS);
  }catch{ schedule(idleLoop, IDLE_CHECK_MS); }
}

async function activeLoop(){
  try{
    const d = await fetchDisplay();
    const st = String(d?.status||'').toLowerCase();

    if (d && d.order_no && st === 'pending'){ render(d); schedule(activeLoop, ACTIVE_POLL_MS); return; }

    if (d && d.order_no && isPaid(st)){
      if (!HOLD.active){ HOLD.active=true; HOLD.until=Date.now()+3000; render(d); }
      else { render(LAST || d); }
      if (Date.now() >= HOLD.until){ HOLD.active=false; clearUI(); schedule(idleLoop, IDLE_CHECK_MS); }
      else { schedule(activeLoop, 200); }
      return;
    }

    clearUI(); schedule(idleLoop, IDLE_CHECK_MS);
  }catch{ schedule(activeLoop, 800); }
}

function schedule(fn, ms){ if (TIMER) clearTimeout(TIMER); TIMER = setTimeout(fn, ms); }
clearUI(); idleLoop();
</script>

@endsection
