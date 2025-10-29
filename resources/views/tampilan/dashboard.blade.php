@extends('layouts.main')
@section('title', 'Dashboard')

@section('content')
    <div class="dashboard-wrapper">

        <!-- Main Content -->
            

            <!-- Main Dashboard -->
            <main class="dashboard-main">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Penjualan Hari Ini</span>
                            <div class="stat-icon green">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" x2="12" y1="2" y2="22"/>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">Rp 2.450.000</div>
                        <div class="stat-change positive">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                                <polyline points="16 7 22 7 22 13"/>
                            </svg>
                            <span>+16.7%</span>
                            <span class="text-muted">dari kemarin</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Pesanan Baru</span>
                            <div class="stat-icon blue">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="8" cy="21" r="1"/>
                                    <circle cx="19" cy="21" r="1"/>
                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">8</div>
                        <p class="stat-subtitle">Menunggu diproses</p>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Stok Hampir Habis</span>
                            <div class="stat-icon amber">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                    <line x1="12" x2="12" y1="9" y2="13"/>
                                    <line x1="12" x2="12.01" y1="17" y2="17"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">4</div>
                        <p class="stat-subtitle">Item perlu restok</p>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Pelanggan Baru</span>
                            <div class="stat-icon purple">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stat-value">12</div>
                        <p class="stat-subtitle">Bulan ini</p>
                    </div>
                </div>

                <!-- Sales Chart -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <h3>Grafik Penjualan</h3>
                            <p>Tren penjualan dalam periode waktu</p>
                        </div>
                        <div class="card-actions">
                            <button class="btn btn-primary btn-sm" onclick="changeChartPeriod('weekly')" id="btnWeekly">Mingguan</button>
                            <button class="btn btn-sm" onclick="changeChartPeriod('monthly')" id="btnMonthly">Bulanan</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-bars" id="salesChart"></div>
                    </div>
                </div>

                <!-- Grid Section -->
                <div class="grid-2">
                    <!-- Top Products -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <h3>Produk Terlaris</h3>
                                <p>Item paling banyak terjual</p>
                            </div>
                        </div>
                        <div class="product-list">
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-rank">1</div>
                                    <div class="product-details">
                                        <h4>Cappuccino</h4>
                                        <p>145 terjual</p>
                                    </div>
                                </div>
                                <div class="product-revenue">Rp 7.250.000</div>
                            </div>
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-rank">2</div>
                                    <div class="product-details">
                                        <h4>Latte</h4>
                                        <p>132 terjual</p>
                                    </div>
                                </div>
                                <div class="product-revenue">Rp 6.600.000</div>
                            </div>
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-rank">3</div>
                                    <div class="product-details">
                                        <h4>Americano</h4>
                                        <p>98 terjual</p>
                                    </div>
                                </div>
                                <div class="product-revenue">Rp 3.920.000</div>
                            </div>
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-rank">4</div>
                                    <div class="product-details">
                                        <h4>Espresso</h4>
                                        <p>87 terjual</p>
                                    </div>
                                </div>
                                <div class="product-revenue">Rp 3.480.000</div>
                            </div>
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-rank">5</div>
                                    <div class="product-details">
                                        <h4>Croissant</h4>
                                        <p>76 terjual</p>
                                    </div>
                                </div>
                                <div class="product-revenue">Rp 2.280.000</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <h3>Pesanan Terbaru</h3>
                                <p>Aktivitas pesanan terkini</p>
                            </div>
                            <button class="btn btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Lihat Semua
                            </button>
                        </div>
                        <div class="order-list">
                            <div class="order-item">
                                <div class="order-header">
                                    <div>
                                        <div class="order-id">ORD-2401</div>
                                        <div class="order-customer">Ahmad Rizki</div>
                                    </div>
                                    <span class="badge badge-blue">Baru</span>
                                </div>
                                <div class="order-items">Cappuccino x2, Croissant x1</div>
                                <div class="order-footer">
                                    <span class="order-total">Rp 125.000</span>
                                    <span class="order-time">10 menit yang lalu</span>
                                </div>
                            </div>
                            <div class="order-item">
                                <div class="order-header">
                                    <div>
                                        <div class="order-id">ORD-2402</div>
                                        <div class="order-customer">Siti Nurhaliza</div>
                                    </div>
                                    <span class="badge badge-amber">Diproses</span>
                                </div>
                                <div class="order-items">Latte x1, Espresso x1</div>
                                <div class="order-footer">
                                    <span class="order-total">Rp 90.000</span>
                                    <span class="order-time">15 menit yang lalu</span>
                                </div>
                            </div>
                            <div class="order-item">
                                <div class="order-header">
                                    <div>
                                        <div class="order-id">ORD-2403</div>
                                        <div class="order-customer">Budi Santoso</div>
                                    </div>
                                    <span class="badge badge-green">Selesai</span>
                                </div>
                                <div class="order-items">Americano x3</div>
                                <div class="order-footer">
                                    <span class="order-total">Rp 120.000</span>
                                    <span class="order-time">22 menit yang lalu</span>
                                </div>
                            </div>
                            <div class="order-item">
                                <div class="order-header">
                                    <div>
                                        <div class="order-id">ORD-2404</div>
                                        <div class="order-customer">Dewi Lestari</div>
                                    </div>
                                    <span class="badge badge-amber">Diproses</span>
                                </div>
                                <div class="order-items">Cappuccino x1, Latte x2</div>
                                <div class="order-footer">
                                    <span class="order-total">Rp 150.000</span>
                                    <span class="order-time">28 menit yang lalu</span>
                                </div>
                            </div>
                            <div class="order-item">
                                <div class="order-header">
                                    <div>
                                        <div class="order-id">ORD-2405</div>
                                        <div class="order-customer">Eko Prasetyo</div>
                                    </div>
                                    <span class="badge badge-green">Selesai</span>
                                </div>
                                <div class="order-items">Espresso x2, Croissant x2</div>
                                <div class="order-footer">
                                    <span class="order-total">Rp 130.000</span>
                                    <span class="order-time">35 menit yang lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Alert -->
                <div class="alert-card">
                    <div class="alert-header">
                        <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" x2="12" y1="9" y2="13"/>
                            <line x1="12" x2="12.01" y1="17" y2="17"/>
                        </svg>
                        <div class="alert-title">
                            <h3>Peringatan Stok</h3>
                            <p>Item dengan stok di bawah minimum</p>
                        </div>
                    </div>
                    <div class="inventory-grid">
                        <div class="inventory-item">
                            <div class="inventory-item-header">
                                <svg class="inventory-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                    <path d="M12 22V12"/>
                                </svg>
                                <span class="badge badge-red">Rendah</span>
                            </div>
                            <p class="inventory-name">Biji Kopi Arabika</p>
                            <p class="inventory-stock">Stok: <span class="stock-current">2</span> / Min: 5 kg</p>
                        </div>
                        <div class="inventory-item">
                            <div class="inventory-item-header">
                                <svg class="inventory-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                    <path d="M12 22V12"/>
                                </svg>
                                <span class="badge badge-red">Rendah</span>
                            </div>
                            <p class="inventory-name">Susu Full Cream</p>
                            <p class="inventory-stock">Stok: <span class="stock-current">3</span> / Min: 10 liter</p>
                        </div>
                        <div class="inventory-item">
                            <div class="inventory-item-header">
                                <svg class="inventory-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                    <path d="M12 22V12"/>
                                </svg>
                                <span class="badge badge-red">Rendah</span>
                            </div>
                            <p class="inventory-name">Sirup Vanilla</p>
                            <p class="inventory-stock">Stok: <span class="stock-current">1</span> / Min: 3 botol</p>
                        </div>
                        <div class="inventory-item">
                            <div class="inventory-item-header">
                                <svg class="inventory-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                    <path d="M12 22V12"/>
                                </svg>
                                <span class="badge badge-red">Rendah</span>
                            </div>
                            <p class="inventory-name">Gula Pasir</p>
                            <p class="inventory-stock">Stok: <span class="stock-current">4</span> / Min: 10 kg</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection