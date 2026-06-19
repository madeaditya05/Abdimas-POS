@extends('layouts.main')
@section('title', 'Dashboard')

@push('styles')
<style>
  .card .chart-container{height:320px; position:relative;}
  .chart-container canvas{width:100% !important; height:100% !important; display:block;}
  .dashboard-topbar{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:24px;}
  .dashboard-period{display:flex;flex-direction:column;gap:4px;}
  .dashboard-period span{font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;}
  .dashboard-period strong{font-size:20px;color:#111827;}
  .dashboard-filter{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;}
  .dashboard-filter .filter-field{display:flex;flex-direction:column;gap:6px;min-width:150px;}
  .dashboard-filter label{font-size:12px;font-weight:600;color:#6b7280;}
  .dashboard-filter .filter-actions{display:flex;gap:8px;flex-wrap:wrap;}
  .dashboard-filter [hidden]{display:none !important;}
  .comparison-card{background:#fff;border-radius:12px;padding:24px;box-shadow:var(--shadow-1);border:1px solid #e5e7eb;}
  .comparison-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap;}
  .comparison-title{display:flex;align-items:center;gap:12px;}
  .comparison-icon{width:24px;height:24px;color:#0f766e;flex:0 0 auto;}
  .comparison-title h3{font-size:20px;font-weight:600;color:var(--ink);}
  .comparison-title p{font-size:14px;color:var(--muted);}
  .channel-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;}
  .channel-item{padding:16px;border:1px solid #e5e7eb;border-radius:10px;background:#f8fafc;}
  .channel-item.is-winner{border-color:#99f6e4;background:#f0fdfa;}
  .channel-item-header{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;}
  .channel-name{font-size:15px;font-weight:700;color:#0f172a;}
  .channel-revenue{font-size:24px;font-weight:700;color:#111827;margin-bottom:6px;}
  .channel-meta{font-size:13px;color:#4b5563;margin-bottom:12px;}
  .channel-progress{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;}
  .channel-progress span{display:block;height:100%;background:#0f766e;border-radius:inherit;}
  .comparison-summary{margin-top:16px;padding:12px 14px;border-radius:10px;background:#f8fafc;color:#475569;font-size:14px;}
  @media (max-width: 900px){
    .dashboard-topbar{align-items:stretch;flex-direction:column;}
    .dashboard-filter,.dashboard-filter .filter-field,.dashboard-filter .filter-actions{width:100%;}
    .dashboard-filter .btn{justify-content:center;}
    .channel-grid{grid-template-columns:1fr;}
  }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
  <main class="dashboard-main">

    <div class="dashboard-topbar">
      <div class="dashboard-period">
        <span>Periode Data</span>
        <strong>{{ $periodLabel }}</strong>
      </div>

      <form method="GET" action="{{ route('dashboard') }}" class="dashboard-filter" id="dashboardFilter">
        <div class="filter-field">
          <label for="periodFilter">Jenis Filter</label>
          <select name="period" id="periodFilter" class="form-select">
            <option value="day" {{ $filters['period'] === 'day' ? 'selected' : '' }}>Per Hari</option>
            <option value="week" {{ $filters['period'] === 'week' ? 'selected' : '' }}>Per Minggu</option>
            <option value="month" {{ $filters['period'] === 'month' ? 'selected' : '' }}>Per Bulan</option>
            <option value="year" {{ $filters['period'] === 'year' ? 'selected' : '' }}>Per Tahun</option>
          </select>
        </div>

        <div class="filter-field period-input" data-period-input="day" {{ $filters['period'] !== 'day' ? 'hidden' : '' }}>
          <label for="dateFilter">Tanggal</label>
          <input type="date" name="date" id="dateFilter" class="form-input" value="{{ $filters['date'] }}">
        </div>

        <div class="filter-field period-input" data-period-input="week" {{ $filters['period'] !== 'week' ? 'hidden' : '' }}>
          <label for="weekFilter">Minggu</label>
          <input type="week" name="week" id="weekFilter" class="form-input" value="{{ $filters['week'] }}">
        </div>

        <div class="filter-field period-input" data-period-input="month" {{ $filters['period'] !== 'month' ? 'hidden' : '' }}>
          <label for="monthFilter">Bulan</label>
          <input type="month" name="month" id="monthFilter" class="form-input" value="{{ $filters['month'] }}">
        </div>

        <div class="filter-field period-input" data-period-input="year" {{ $filters['period'] !== 'year' ? 'hidden' : '' }}>
          <label for="yearFilter">Tahun</label>
          <input type="number" name="year" id="yearFilter" class="form-input" min="2000" max="2100" value="{{ $filters['year'] }}">
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
          <a href="{{ route('dashboard') }}" class="btn btn-sm">Reset</a>
        </div>
      </form>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">{{ $salesSummaryTitle }}</span>
          <div class="stat-icon green">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" x2="12" y1="2" y2="22"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">Rp {{ number_format($salesTotal, 0, ',', '.') }}</div>
        <p class="stat-subtitle">{{ $periodLabel }}</p>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">Transaksi Berhasil</span>
          <div class="stat-icon blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
              <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">{{ $paidOrdersCount }}</div>
        <p class="stat-subtitle">Status paid pada periode ini. Pending: {{ $newOrders }}</p>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">Channel Terbanyak</span>
          <div class="stat-icon amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 3v18h18"/>
              <path d="M7 16V9"/>
              <path d="M12 16V5"/>
              <path d="M17 16v-3"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">{{ $channelComparison['dominant']['label'] }}</div>
        <p class="stat-subtitle">
          Offline {{ $channelComparison['channels']['offline']['count'] }}
          vs Online {{ $channelComparison['channels']['online']['count'] }} transaksi
        </p>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <span class="stat-card-title">Pelanggan Baru</span>
          <div class="stat-icon purple">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
        </div>
        <div class="stat-value">{{ $newCustomers }}</div>
        <p class="stat-subtitle">Pelanggan baru pada periode ini</p>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <h3>Grafik Penjualan</h3>
          <p>{{ $salesChartData['chartTitle'] ?? 'Penjualan' }} - {{ $periodLabel }}</p>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <h3>Produk Terlaris</h3>
          <p>Distribusi penjualan 5 produk teratas - {{ $periodLabel }}</p>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="topProductsChart"></canvas>
      </div>
    </div>

    <div class="comparison-card">
      <div class="comparison-header">
        <div class="comparison-title">
          <svg class="comparison-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3v18h18"/>
            <path d="M7 16V9"/>
            <path d="M12 16V5"/>
            <path d="M17 16v-3"/>
          </svg>
          <div>
            <h3>Perbandingan Offline vs Online</h3>
            <p>Channel penjualan paling banyak pada {{ $periodLabel }}</p>
          </div>
        </div>
        <span class="badge badge-green">Terbanyak: {{ $channelComparison['dominant']['label'] }}</span>
      </div>

      <div class="channel-grid">
        @foreach($channelComparison['channels'] as $channel)
          <div class="channel-item {{ $channelComparison['dominant']['key'] === $channel['key'] ? 'is-winner' : '' }}">
            <div class="channel-item-header">
              <span class="channel-name">{{ $channel['label'] }}</span>
              <span class="badge badge-green">{{ number_format($channel['count_percent'], 1, ',', '.') }}%</span>
            </div>
            <div class="channel-revenue">Rp {{ number_format($channel['revenue'], 0, ',', '.') }}</div>
            <div class="channel-meta">
              {{ number_format($channel['count'], 0, ',', '.') }} transaksi,
              {{ number_format($channel['revenue_percent'], 1, ',', '.') }}% dari omzet periode ini
            </div>
            <div class="channel-progress">
              <span style="width: {{ min(100, max(0, $channel['count_percent'])) }}%"></span>
            </div>
          </div>
        @endforeach
      </div>

      <div class="comparison-summary">
        @if($channelComparison['total_count'] > 0)
          {{ $channelComparison['dominant']['label'] }} paling banyak berdasarkan jumlah transaksi.
          Selisihnya {{ number_format($channelComparison['difference_count'], 0, ',', '.') }} transaksi
          dan Rp {{ number_format($channelComparison['difference_revenue'], 0, ',', '.') }} omzet.
        @else
          Belum ada transaksi offline maupun online pada periode ini.
        @endif
      </div>
    </div>

  </main>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  const salesChartData = @json($salesChartData);
  const topProducts = @json($topProductsChart);

  Chart.defaults.font.family = '"Inter",-apple-system,BlinkMacSystemFont,sans-serif';
  Chart.defaults.color = '#111827';

  const moneyID = v => 'Rp ' + Number(v || 0).toLocaleString('id-ID');

  function syncPeriodInputs(){
    const selected = $('#periodFilter').val();
    $('.period-input').each(function(){
      const isActive = $(this).data('period-input') === selected;
      $(this).prop('hidden', !isActive);
      $(this).find('input').prop('disabled', !isActive);
    });
  }

  const ctxSales = document.getElementById('salesChart').getContext('2d');
  new Chart(ctxSales, {
    type: 'bar',
    data: {
      labels: salesChartData.labels || [],
      datasets: [{
        label: salesChartData.chartTitle || 'Penjualan',
        data: (salesChartData.values || []).map(Number),
        backgroundColor: 'rgba(16,185,129,.6)',
        borderColor: '#059669',
        borderWidth: 1,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true, ticks: { callback: moneyID }, grid: { color: 'rgba(0,0,0,.06)' } },
        x: { grid: { display: false } }
      },
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (c) => moneyID(c.parsed.y) } }
      }
    }
  });

  $('#periodFilter').on('change', syncPeriodInputs);
  syncPeriodInputs();

  const ctxPie = document.getElementById('topProductsChart').getContext('2d');
  const labelsPie = (topProducts || []).map(p => p.name || ('Produk #' + p.product_id));
  const dataPie = (topProducts || []).map(p => Number(p.sold));

  if (labelsPie.length) {
    new Chart(ctxPie, {
      type: 'pie',
      data: {
        labels: labelsPie,
        datasets: [{
          data: dataPie,
          backgroundColor: ['#22C55E', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: (c) => {
                const total = c.dataset.data.reduce((a, b) => a + Number(b), 0) || 1;
                const val = Number(c.parsed);
                const pct = ((val / total) * 100).toFixed(1);
                return `${c.label}: ${val} (${pct}%)`;
              }
            }
          }
        }
      }
    });
  }
});
</script>
@endpush
