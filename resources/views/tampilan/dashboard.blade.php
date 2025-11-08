@extends('layouts.main')
@section('title', 'Dashboard')

{{-- PATCH kecil buat canvas (lihat di atas) --}}
@push('styles')
<style>
  .card .chart-container{height:320px; position:relative;}
  .chart-container canvas{width:100% !important; height:100% !important; display:block;}
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
  <main class="dashboard-main">

    {{-- === KARTU STATISTIK (kelas & spacing sesuai CSS kamu) === --}}
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">Penjualan Hari Ini</span>
          <div class="stat-icon green">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" x2="12" y1="2" y2="22"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">Rp {{ number_format($salesToday, 0, ',', '.') }}</div>
        <p class="stat-subtitle">Transaksi hari ini</p>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">Pesanan Baru</span>
          <div class="stat-icon blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
              <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">{{ $newOrders }}</div>
        <p class="stat-subtitle">Menunggu diproses</p>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">Stok Hampir Habis</span>
          <div class="stat-icon amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
              <line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">{{ $lowStockCount }}</div>
        <p class="stat-subtitle">Item perlu restok</p>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">Pelanggan Baru</span>
          <div class="stat-icon purple">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">{{ $newCustomers }}</div>
        <p class="stat-subtitle">Bulan ini</p>
      </div>
    </div>

    {{-- === GRAFIK PENJUALAN (pakai .card, .card-header, .btn yang sudah ada) === --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <h3>Grafik Penjualan</h3>
          <p>Periode waktu tertentu</p>
        </div>
        <div class="card-actions">
          <button class="btn btn-primary btn-sm period-btn" data-period="weekly">Mingguan</button>
          <button class="btn btn-sm period-btn" data-period="monthly">Bulanan</button>
          <button class="btn btn-sm period-btn" data-period="yearly">Tahunan</button>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    {{-- === PIE: PRODUK TERLARIS === --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <h3>Produk Terlaris</h3>
          <p>Distribusi penjualan 5 produk teratas</p>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="topProductsChart"></canvas>
      </div>
    </div>

    {{-- === PERINGATAN STOK (pakai komponen alert kamu) === --}}
    <div class="alert-card">
      <div class="alert-header">
        <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
          <line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
        </svg>
        <div class="alert-title">
          <h3>Peringatan Stok</h3>
          <p>Item dengan stok di bawah minimum</p>
        </div>
      </div>
      <div class="inventory-grid">
        @forelse($lowStockItems as $p)
          <div class="inventory-item">
            <div class="inventory-item-header">
              <svg class="inventory-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
              </svg>
              <span class="badge {{ $p->stock <= 0 ? 'badge-red' : 'badge-amber' }}">
                {{ $p->stock <= 0 ? 'Habis' : 'Rendah' }}
              </span>
            </div>
            <p class="inventory-name">{{ $p->name }}</p>
            <p class="inventory-stock">Stok: <span class="stock-current">{{ $p->stock }}</span> / Min: {{ $p->min_stock }} {{ $p->unit }}</p>
          </div>
        @empty
          <p class="text-muted">Semua stok aman 🎉</p>
        @endforelse
      </div>
    </div>

  </main>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  const weekly  = @json($weekly);
  const monthly = @json($monthly);
  const yearly  = @json($yearly);
  const topProducts = @json($topProductsChart);

  Chart.defaults.font.family = '"Inter",-apple-system,BlinkMacSystemFont,sans-serif';
  Chart.defaults.color = '#111827';

  const moneyID = v => 'Rp ' + Number(v||0).toLocaleString('id-ID');

  // ---- formatter label per periode
  const fmtWeekly = d => {
  const dt = new Date(d + 'T00:00:00');
  const hari = dt.toLocaleDateString('id-ID', { weekday: 'long' });
  return hari.charAt(0).toUpperCase() + hari.slice(1);
  };
  const fmtMonthly = ym => {
    // ym = "YYYY-MM" → pakai tanggal 1 agar valid
    const dt = new Date(ym + '-01T00:00:00');
    return dt.toLocaleDateString('id-ID', { month:'long' });
  };
  const fmtYearly  = y  => String(y);

  const ctxSales = document.getElementById('salesChart').getContext('2d');
  let salesChart;

  function renderSales(obj, label, type){
    const rawLabels = Object.keys(obj);
    const values    = Object.values(obj).map(Number);

    // pilih formatter
    let labels = rawLabels;
    if(type==='weekly')  labels = rawLabels.map(fmtWeekly);
    if(type==='monthly') labels = rawLabels.map(fmtMonthly);
    if(type==='yearly')  labels = rawLabels.map(fmtYearly);

    if (salesChart) salesChart.destroy();
    salesChart = new Chart(ctxSales, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label,
          data: values,
          backgroundColor: 'rgba(16,185,129,.6)',
          borderColor:'#059669',
          borderWidth:1,
          borderRadius:6
        }]
      },
      options: {
        responsive:true, maintainAspectRatio:false,
        scales:{
          y:{ beginAtZero:true, ticks:{ callback: moneyID }, grid:{ color:'rgba(0,0,0,.06)' } },
          x:{ grid:{ display:false } }
        },
        plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(c)=> moneyID(c.parsed.y) }}}
      }
    });
  }

  // tombol periode
  $('.period-btn').on('click', function(){
    $('.period-btn').removeClass('btn-primary');
    $(this).addClass('btn-primary');
    const p = $(this).data('period');
    if(p==='weekly')  return renderSales(weekly,  'Penjualan Mingguan', 'weekly');
    if(p==='monthly') return renderSales(monthly, 'Penjualan Bulanan',  'monthly');
    if(p==='yearly')  return renderSales(yearly,  'Penjualan Tahunan',  'yearly');
  });

  // default: mingguan
  renderSales(weekly, 'Penjualan Mingguan', 'weekly');

  // --- PIE chart tetap ---
  const ctxPie = document.getElementById('topProductsChart').getContext('2d');
  const labelsPie = (topProducts||[]).map(p => p.product?.name ?? ('Produk #' + p.product_id));
  const dataPie   = (topProducts||[]).map(p => Number(p.sold));
  if(labelsPie.length){
    new Chart(ctxPie,{
      type:'pie',
      data:{ labels: labelsPie, datasets:[{ data: dataPie, backgroundColor:['#22C55E','#3B82F6','#F59E0B','#EF4444','#8B5CF6'] }]},
      options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' }, tooltip:{ callbacks:{ label:(c)=> {
        const total = c.dataset.data.reduce((a,b)=>a+Number(b),0)||1;
        const val = Number(c.parsed); const pct = ((val/total)*100).toFixed(1);
        return `${c.label}: ${val} (${pct}%)`;
      }}}}}
    });
  }
});
</script>
@endpush
