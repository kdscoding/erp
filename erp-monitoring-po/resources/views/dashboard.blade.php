@extends('layouts.erp')

@php($title = 'Dashboard')
@php($header = 'Executive Dashboard')
@php($headerSubtitle = 'Grafik pemantauan Purchase Order, Tracking, dan Shipment.')

@section('content')
    <style>
        .dashboard-grid { display: grid; gap: 1.25rem; }
        .dashboard-hero {
            padding: 1.25rem;
            border-radius: 14px;
            border: 1px solid rgba(111, 150, 40, .1);
            background: linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(244, 248, 219, .92));
            box-shadow: 0 6px 16px rgba(111, 150, 40, .03);
        }
        .dashboard-hero h2 { margin: .25rem 0 .15rem; font-size: 1.2rem; color: #2d3d15; }
        .dashboard-muted { font-size: .85rem; color: #728058; }
        .quick-links { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .5rem; }
        .quick-link {
            padding: .4rem .75rem;
            border-radius: 10px;
            border: 1px solid #e4eabc;
            background: #fbfcf3;
            color: #314216;
            text-decoration: none;
            font-size: .78rem;
            font-weight: 600;
            transition: all .15s ease;
        }
        .quick-link:hover { background: #eef7d2; border-color: #b9d044; }

        .chart-section-title {
            font-size: .85rem;
            font-weight: 800;
            color: #2d3d15;
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .chart-section-title .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .chart-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }

        .chart-card {
            padding: 1.25rem;
            border-radius: 14px;
            border: 1px solid rgba(111, 150, 40, .1);
            background: linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(247, 248, 234, .94));
            box-shadow: 0 4px 12px rgba(111, 150, 40, .03);
        }
        .chart-card .chart-title { font-size: .82rem; font-weight: 800; color: #2d3d15; margin-bottom: .75rem; }
        .chart-canvas-wrap { position: relative; width: 100%; height: 280px; }

        @media (max-width: 767.98px) {
            .chart-section-title { font-size: .78rem; }
            .chart-card { padding: 1rem; }
            .chart-row { grid-template-columns: 1fr; }
        }
    </style>

    <div class="dashboard-grid">
        <section class="dashboard-hero">
            <h2>Executive Overview</h2>
            <div class="dashboard-muted">Pemantauan visual Purchase Order, Tracking, dan Shipment.</div>
        </section>

        <section>
            <div class="chart-section-title">
                <span class="dot" style="background:#9ecb3c;"></span>
                Purchase Order
            </div>
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-title">Distribusi Status PO</div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartPoStatusDist"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Tren PO Bulanan (6 bulan terakhir)</div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartMonthlyTrend"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="chart-section-title">
                <span class="dot" style="background:#f59e0b;"></span>
                Tracking
            </div>
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-title">Distribusi Status Item</div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartStatusBreakdown"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Top Supplier Terlambat (Item)</div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartSupplierDelay"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="chart-section-title">
                <span class="dot" style="background:#3b82f6;"></span>
                Shipment
            </div>
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-title">Distribusi Status Shipment</div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartShipmentStatusDist"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Tren Shipment Bulanan (6 bulan terakhir)</div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartShipmentMonthlyTrend"></canvas>
                    </div>
                </div>
            </div>
        </section>

        @if($receivingMonthlyTrend)
        <section>
            <div class="chart-section-title">
                <span class="dot" style="background:#10b981;"></span>
                Receiving
            </div>
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-title">Tren Receiving Bulanan (6 bulan terakhir)</div>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartReceivingMonthlyTrend"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-title" style="color:#728058;">Klik tautan di bawah untuk detail lanjutan.</div>
                    <div class="quick-links">
                        <a href="{{ route('monitoring.index', request()->query()) }}" class="quick-link">Monitoring Hub</a>
                        <a href="{{ route('tracking.index') }}" class="quick-link">Tracking</a>
                        <a href="{{ route('receiving.index') }}" class="quick-link">Receiving</a>
                    </div>
                </div>
            </div>
        </section>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const lemonColors = {
                green: '#9ecb3c',
                greenDeep: '#6f9628',
                yellow: '#f1d93b',
                olive: '#566d2a',
                ink: '#304218',
                muted: '#728058',
                line: '#dfe6b8',
            };

            const poStatusColors = {
                'Full': '#9ecb3c',
                'Partial': '#f59e0b',
                'Delayed': '#ef4444',
                'PO Issued': '#6366f1',
                'Open': '#3b82f6',
                'Late': '#ef4444',
            };

            const shipmentStatusColors = {
                'Draft': '#9ca3af',
                'Shipped': '#3b82f6',
                'Partial Received': '#f59e0b',
                'Received': '#10b981',
                'Cancelled': '#ef4444',
            };

            const statusColorPalette = ['#f1d93b', '#9ecb3c', '#ef4444', '#f59e0b', '#9ca3af', '#6b7280'];

            /* ===== PURCHASE ORDER ===== */

            const poStatusLabels = @json(array_keys($chartPoStatusDist));
            const poStatusData = @json(array_values($chartPoStatusDist));
            const poStatusBgColors = poStatusLabels.map(function (l) { return poStatusColors[l] || '#9ca3af'; });

            new Chart(document.getElementById('chartPoStatusDist'), {
                type: 'doughnut',
                data: {
                    labels: poStatusLabels,
                    datasets: [{
                        data: poStatusData,
                        backgroundColor: poStatusBgColors,
                        borderColor: '#fff',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    },
                },
            });

            const monthLabels = @json(array_keys($chartMonthlyTrend));
            const monthData = @json(array_values($chartMonthlyTrend));

            new Chart(document.getElementById('chartMonthlyTrend'), {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Jumlah PO',
                        data: monthData,
                        borderColor: lemonColors.greenDeep,
                        backgroundColor: 'rgba(111, 150, 40, .12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: lemonColors.greenDeep,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: lemonColors.line } },
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    },
                },
            });

            /* ===== TRACKING ===== */

            const statusLabels = @json(array_keys($chartStatusBreakdown));
            const statusData = @json(array_values($chartStatusBreakdown));

            new Chart(document.getElementById('chartStatusBreakdown'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: statusColorPalette,
                        borderColor: '#fff',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    },
                },
            });

            const supplierData = @json($chartSupplierDelay);
            const supplierLabels = [...new Set(supplierData.map(item => item.supplier_name))];
            const supplierCategories = [...new Set(supplierData.map(item => item.category_name || 'Tanpa Kategori'))];

            const supplierLateDatasets = supplierCategories.map((cat, idx) => {
                const color = ['#ef4444', '#f59e0b', '#8b5cf6', '#10b981', '#3b82f6', '#f97316', '#06b6d4', '#db2777'][idx % 8];
                return {
                    label: cat,
                    data: supplierLabels.map(supplier => {
                        const row = supplierData.find(r => r.supplier_name === supplier && (r.category_name || 'Tanpa Kategori') === cat);
                        return row ? parseInt(row.late_item_count) : 0;
                    }),
                    backgroundColor: color,
                    borderRadius: 4,
                };
            });

            const supplierOutstandingDatasets = supplierCategories.map((cat, idx) => {
                const color = ['#f1d93b', '#fbbf24', '#a78bfa', '#34d399', '#60a5fa', '#fdba74', '#22d3ee', '#facc19'][idx % 8];
                return {
                    label: cat,
                    data: supplierLabels.map(supplier => {
                        const row = supplierData.find(r => r.supplier_name === supplier && (r.category_name || 'Tanpa Kategori') === cat);
                        return row ? parseFloat(row.outstanding_qty) : 0;
                    }),
                    backgroundColor: color,
                    borderRadius: 4,
                };
            });

            new Chart(document.getElementById('chartSupplierDelay'), {
                type: 'bar',
                data: {
                    labels: supplierLabels,
                    datasets: supplierLateDatasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: lemonColors.line } },
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${supplierCategories[context.datasetIndex]}: ${context.parsed.y} item`;
                                }
                            }
                        },
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    },
                },
            });

            /* ===== SHIPMENT ===== */

            const shipStatusLabels = @json(array_keys($chartShipmentStatusDist));
            const shipStatusData = @json(array_values($chartShipmentStatusDist));
            const shipStatusBgColors = shipStatusLabels.map(function (l) { return shipmentStatusColors[l] || '#9ca3af'; });

            new Chart(document.getElementById('chartShipmentStatusDist'), {
                type: 'doughnut',
                data: {
                    labels: shipStatusLabels,
                    datasets: [{
                        data: shipStatusData,
                        backgroundColor: shipStatusBgColors,
                        borderColor: '#fff',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    },
                },
            });

            const shipMonthLabels = @json(array_keys($chartShipmentMonthlyTrend));
            const shipMonthData = @json(array_values($chartShipmentMonthlyTrend));

            new Chart(document.getElementById('chartShipmentMonthlyTrend'), {
                type: 'line',
                data: {
                    labels: shipMonthLabels,
                    datasets: [{
                        label: 'Jumlah Shipment',
                        data: shipMonthData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, .12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: lemonColors.line } },
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    },
                },
            });

            /* ===== RECEIVING ===== */

            const recvMonthLabels = @json(array_keys($receivingMonthlyTrend));
            const recvMonthData = @json(array_values($receivingMonthlyTrend));

            new Chart(document.getElementById('chartReceivingMonthlyTrend'), {
                type: 'line',
                data: {
                    labels: recvMonthLabels,
                    datasets: [{
                        label: 'Jumlah GR',
                        data: recvMonthData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, .12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: lemonColors.line } },
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                    },
                },
            });
        })();
    </script>
@endsection
