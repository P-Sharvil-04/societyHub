<?php
$is_super = $is_super ?? false;
$role = $role ?? '';
$societies = $societies ?? [];
$my_society_id = $my_society_id ?? 0;
$my_society_name = $my_society_name ?? 'My Society';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover, shrink-to-fit=no">
    <title>SocietyHub · Reports & Analytics</title>
    <link rel="icon" href="<?= base_url('assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png') ?>" type="image/png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">

    <style>
        /* (your existing styles – unchanged) */
        .reports-filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 20px;
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        .filter-group label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-light);
            margin-bottom: 6px;
        }
        .filter-group input,
        .filter-group select {
            background: var(--light-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 14px;
            color: var(--text-dark);
            font-size: 0.9rem;
            outline: none;
            transition: 0.15s;
            width: 100%;
        }
        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .filter-group .date-range {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .filter-group .date-range input {
            width: 140px;
        }
        .btn-generate {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 28px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
            height: fit-content;
        }
        .btn-generate:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .btn-generate:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .report-loading {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 14px;
        }
        .report-loading.active {
            display: flex;
        }
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .report-loading p {
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .report-error {
            display: none;
            background: #fdecea;
            color: #c0392b;
            border: 1px solid #e74c3c;
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .report-error.active {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .report-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .report-stat-card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 22px 18px;
            border: 1px solid var(--border);
            transition: 0.2s;
        }
        .report-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }
        .report-stat-card .label {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 8px;
            font-weight: 500;
        }
        .report-stat-card .value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.2;
        }
        .report-stat-card .trend {
            font-size: 0.8rem;
            margin-top: 8px;
            color: var(--text-light);
        }
        .trend.up { color: var(--success); }
        .trend.down { color: var(--danger); }
        .skeleton {
            background: linear-gradient(90deg, var(--light-bg) 25%, var(--border) 50%, var(--light-bg) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 10px;
            height: 2.4rem;
        }
        @keyframes shimmer {
            to { background-position: -200% 0; }
        }
        .chart-card.large {
            grid-column: span 2;
        }
        @media (max-width: 700px) {
            .chart-card.large {
                grid-column: span 1;
            }
        }
        .report-table-wrapper {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid var(--border);
            overflow-x: auto;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        .report-table th {
            text-align: left;
            padding: 16px 12px;
            background: var(--light-bg);
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            position: sticky;
            top: 0;
        }
        .report-table td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-dark);
            font-size: 0.9rem;
        }
        .report-table tbody tr:hover {
            background: var(--light-bg);
        }
        .report-table tbody tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .badge-paid,
        .badge-resolved,
        .badge-active,
        .badge-completed,
        .badge-checked-in,
        .badge-checked-out {
            background: #e8f8f0;
            color: #1e8449;
        }
        .badge-pending,
        .badge-inactive,
        .badge-on-leave {
            background: #fef9e7;
            color: #b7950b;
        }
        .badge-in-progress {
            background: #eaf4fb;
            color: #1a5276;
        }
        .badge-closed {
            background: #f2f3f4;
            color: #5d6d7e;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
            opacity: 0.4;
        }
        .report-pagination {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            align-items: center;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .report-pagination .btn-secondary {
            background: var(--light-bg);
            border: 1px solid var(--border);
            color: var(--text-dark);
            border-radius: 10px;
            padding: 8px 12px;
            min-width: 42px;
        }
        .report-pagination .btn-secondary:hover {
            background: var(--primary);
            color: #fff;
        }
        .society-context {
            margin-top: 10px;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        .society-context strong {
            color: var(--text-dark);
        }
        @media (max-width: 600px) {
            .reports-filter-bar {
                flex-direction: column;
                align-items: stretch;
                padding: 18px 16px;
                gap: 16px;
            }
            .filter-group {
                width: 100%;
                min-width: unset;
            }
            .filter-group .date-range {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .filter-group .date-range input {
                width: 100% !important;
            }
            .filter-group .date-range span {
                text-align: center;
            }
            .btn-generate {
                width: 100%;
                margin-left: 0;
                justify-content: center;
                padding: 12px 20px;
            }
        }
    </style>
</head>

<body>
<div class="overlay" id="overlay"></div>

<?php $activePage = "reports"; ?>
<?php include('sidebar.php'); ?>

<!-- <div class="header" id="header"> -->
       
        <!-- <div class="search-bar"> -->
            <!-- <i class="fas fa-search"></i> -->
        <!-- </div> -->
	<!-- </div> -->
	
	<div class="report-loading" id="reportLoading">
		<div class="spinner"></div>
		<input type="text" placeholder="" id="globalSearch" readonly>
    <p>Fetching report data…</p>
</div>

<div class="main" id="main">
    <div class="report-error" id="reportError">
        <i class="fas fa-exclamation-circle"></i>
        <span id="reportErrorMsg">Failed to load report data.</span>
    </div>

    <div class="reports-filter-bar">
        <?php if (!empty($is_super)): ?>
            <div class="filter-group">
                <label>Society</label>
                <select id="societyId">
                    <option value="all" selected>All Societies</option>
                    <?php foreach ($societies as $soc): ?>
                        <option value="<?= (int) $soc['id'] ?>">
                            <?= htmlspecialchars($soc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <div class="filter-group">
                <label>Society</label>
                <input type="text" value="<?= htmlspecialchars($my_society_name ?: 'My Society') ?>" disabled>
            </div>
        <?php endif; ?>

        <div class="filter-group">
            <label>Report Type</label>
            <select id="reportType">
                <option value="financial" selected>Financial</option>
                <option value="complaints">Complaints</option>
                <option value="visitors">Visitors</option>
                <option value="maintenance">Maintenance / Staff</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Date Range</label>
            <div class="date-range">
                <input type="date" id="startDate" value="<?= date('Y-01-01') ?>">
                <span>to</span>
                <input type="date" id="endDate" value="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <button class="btn-generate" id="generateReportBtn">
            <i class="fas fa-sync-alt"></i> Generate
        </button>
    </div>

    <!-- <div class="society-context" id="societyContext">
        <?php if (!empty($is_super)): ?>
            <strong>Scope:</strong> <span id="societyContextText">All Societies</span>
        <?php else: ?>
            <strong>Society:</strong> <?= htmlspecialchars($my_society_name ?: 'My Society') ?>
        <?php endif; ?>
    </div> -->

    <div class="report-stats-grid" id="kpiContainer"></div>

    <div class="charts-row" style="margin-bottom: 30px;">
        <div class="chart-card large" id="chart1Card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-bar"></i> <span id="chart1Title">Income vs Expenses</span></h3>
                <span class="chart-period" id="chart1Period"></span>
            </div>
            <div class="chart-container" style="height: 280px;">
                <canvas id="primaryChart"></canvas>
            </div>
        </div>
        <div class="chart-card" id="chart2Card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> <span id="chart2Title">Breakdown</span></h3>
            </div>
            <div class="chart-container" style="height: 280px;">
                <canvas id="secondaryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="report-table-wrapper">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
            <h3 style="font-weight:700;" id="tableTitle">Transaction Details</h3>
            <button class="btn btn-secondary" id="exportBtn"><i class="fas fa-download"></i> Export CSV</button>
        </div>

        <div class="table-responsive">
            <table class="report-table" id="reportTable">
                <thead>
                <tr id="tableHeader"></tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>

        <div id="tableEmpty" class="empty-state" style="display:none;">
            <i class="fas fa-inbox"></i>
            No records found for the selected period.
        </div>

        <div id="reportPagination" class="report-pagination"></div>
    </div>
</div>

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
    (function () {
        let primaryChart, secondaryChart;
        const PAGE_SIZE = 10;

        const CHART1_TITLES = {
            financial: 'Income vs Pending Dues',
            complaints: 'Complaints by Category',
            visitors: 'Daily Visitors',
            maintenance: 'Staff by Department',
        };

        const CHART2_TITLES = {
            financial: 'Payment Type Breakdown',
            complaints: 'Complaint Status',
            visitors: 'Visitor Purpose',
            maintenance: 'Maintenance Request Status',
        };

        const TABLE_TITLES = {
            financial: 'Payment Records',
            complaints: 'Complaint Details',
            visitors: 'Visitor Log',
            maintenance: 'Staff Directory',
        };

        function badge(text) {
            const cls = (text || '').toLowerCase().replace(/\s+/g, '-');
            return `<span class="badge badge-${cls}">${text}</span>`;
        }

        function renderKPI(kpiData) {
            const c = document.getElementById('kpiContainer');
            c.innerHTML = '';
            kpiData.forEach(k => {
                const card = document.createElement('div');
                card.className = 'report-stat-card';
                card.innerHTML = `
                    <div class="label">${k.label}</div>
                    <div class="value">${k.value || '—'}</div>
                    ${k.trendDir ? `<div class="trend ${k.trendDir}">${k.trendDir === 'up' ? '▲' : '▼'}</div>` : ''}
                `;
                c.appendChild(card);
            });
        }

        function showKPISkeleton() {
            const c = document.getElementById('kpiContainer');
            c.innerHTML = '';
            for (let i = 0; i < 4; i++) {
                const card = document.createElement('div');
                card.className = 'report-stat-card';
                card.innerHTML = `<div class="skeleton" style="width:60%;margin-bottom:10px;"></div><div class="skeleton" style="height:2.4rem;width:80%;"></div>`;
                c.appendChild(card);
            }
        }

        function renderCharts(data, type, startStr, endStr) {
            const ctx1 = document.getElementById('primaryChart').getContext('2d');
            const ctx2 = document.getElementById('secondaryChart').getContext('2d');

            if (primaryChart) primaryChart.destroy();
            if (secondaryChart) secondaryChart.destroy();

            const isDark = document.body.classList.contains('dark-mode');
            const textColor = isDark ? '#e2e8f0' : '#2c3e50';
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.05)';

            document.getElementById('chart1Title').innerText = CHART1_TITLES[type] || 'Chart';
            document.getElementById('chart2Title').innerText = CHART2_TITLES[type] || 'Breakdown';
            document.getElementById('chart1Period').innerText = `${startStr}  →  ${endStr}`;

            const pd = data.primaryChart || { labels: [], datasets: [], type: 'bar' };
            primaryChart = new Chart(ctx1, {
                type: pd.type || 'bar',
                data: {
                    labels: pd.labels || [],
                    datasets: pd.datasets || [],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: textColor } },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const val = ctx.raw;
                                    return type === 'financial' ? ` ₹${Number(val).toLocaleString('en-IN')}` : ` ${val}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { grid: { color: gridColor }, ticks: { color: textColor } },
                        x: { ticks: { color: textColor } }
                    }
                }
            });

            const sd = data.secondaryChart || { labels: [], data: [], colors: [] };
            secondaryChart = new Chart(ctx2, {
                type: sd.type || 'doughnut',
                data: {
                    labels: sd.labels || [],
                    datasets: [{
                        data: sd.data || [],
                        backgroundColor: sd.colors || ['#3498db', '#2c3e50', '#f39c12', '#95a5a6'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, padding: 12 } },
                    },
                    cutout: sd.type === 'doughnut' ? '65%' : undefined,
                }
            });
        }

        function renderTable(data, type) {
            const thead = document.getElementById('tableHeader');
            const tbody = document.getElementById('tableBody');
            const empty = document.getElementById('tableEmpty');
            const title = document.getElementById('tableTitle');

            thead.innerHTML = '';
            tbody.innerHTML = '';
            title.innerText = TABLE_TITLES[type] || 'Details';

            (data.table.headers || []).forEach(h => {
                const th = document.createElement('th');
                th.innerText = h;
                thead.appendChild(th);
            });

            if (!data.table.rows || !data.table.rows.length) {
                empty.style.display = 'block';
                return;
            }
            empty.style.display = 'none';

            const statusIdx = data.table.headers.findIndex(h => h.toLowerCase() === 'status');

            data.table.rows.forEach(row => {
                const tr = document.createElement('tr');
                row.forEach((cell, i) => {
                    const td = document.createElement('td');
                    if (i === statusIdx && cell) {
                        td.innerHTML = badge(cell);
                    } else {
                        td.innerText = cell;
                    }
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        function renderPagination(meta) {
            const box = document.getElementById('reportPagination');
            box.innerHTML = '';

            const totalPages = meta?.totalPages || 1;
            const page = meta?.page || 1;

            if (totalPages <= 1) return;

            const makeBtn = (label, targetPage, disabled = false, active = false) => {
                const btn = document.createElement('button');
                btn.className = 'btn btn-secondary';
                btn.disabled = disabled;
                btn.innerText = label;
                if (active) btn.style.fontWeight = '700';
                btn.addEventListener('click', () => updateReports(targetPage));
                return btn;
            };

            box.appendChild(makeBtn('«', Math.max(1, page - 1), page === 1));

            const start = Math.max(1, page - 2);
            const end = Math.min(totalPages, page + 2);

            if (start > 1) {
                box.appendChild(makeBtn('1', 1, false, page === 1));
                if (start > 2) {
                    const dots = document.createElement('span');
                    dots.innerText = '...';
                    box.appendChild(dots);
                }
            }

            for (let p = start; p <= end; p++) {
                box.appendChild(makeBtn(String(p), p, false, p === page));
            }

            if (end < totalPages) {
                if (end < totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.innerText = '...';
                    box.appendChild(dots);
                }
                box.appendChild(makeBtn(String(totalPages), totalPages, false, page === totalPages));
            }

            box.appendChild(makeBtn('»', Math.min(totalPages, page + 1), page === totalPages));

            const info = document.createElement('span');
            info.style.marginLeft = '10px';
            info.innerText = `Page ${page} of ${totalPages}`;
            box.appendChild(info);
        }

        function updateReports(page = 1) {
            const type = document.getElementById('reportType').value;
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            if (!start || !end) return;

            const startStr = start.split('-').reverse().join('/');
            const endStr = end.split('-').reverse().join('/');

            document.getElementById('reportLoading').classList.add('active');
            document.getElementById('reportError').classList.remove('active');
            document.getElementById('generateReportBtn').disabled = true;
            showKPISkeleton();

            const params = new URLSearchParams({
                type,
                start_date: start,
                end_date: end,
                page: String(page),
                per_page: String(PAGE_SIZE)
            });

            const societySelect = document.getElementById('societyId');
            if (societySelect) {
                // IMPORTANT: send the exact value ('all' or numeric id)
                params.append('society_id', societySelect.value);
                const scopeText = societySelect.value === 'all'
                    ? 'All Societies'
                    : societySelect.options[societySelect.selectedIndex]?.text || '';
                const scopeEl = document.getElementById('societyContextText');
                if (scopeEl) scopeEl.innerText = scopeText;
            }

            fetch(`<?= base_url('reports/get_data') ?>?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => {
                    if (!res.ok) throw new Error(`Server error ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    renderKPI(data.kpi || []);
                    renderCharts(data, type, startStr, endStr);
                    renderTable(data, type);
                    renderPagination(data.pagination || {});
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('reportErrorMsg').innerText = `Could not load report: ${err.message}`;
                    document.getElementById('reportError').classList.add('active');
                    document.getElementById('kpiContainer').innerHTML = '';
                })
                .finally(() => {
                    document.getElementById('reportLoading').classList.remove('active');
                    document.getElementById('generateReportBtn').disabled = false;
                });
        }

        document.getElementById('exportBtn').addEventListener('click', () => {
            const type = document.getElementById('reportType').value;
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            const params = new URLSearchParams({
                type,
                start_date: start,
                end_date: end
            });

            const societySelect = document.getElementById('societyId');
            if (societySelect) {
                params.append('society_id', societySelect.value);
            }

            window.location.href = `<?= base_url('reports/export_csv') ?>?${params}`;
        });

        document.getElementById('globalSearch').addEventListener('input', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#tableBody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });

        document.getElementById('generateReportBtn').addEventListener('click', () => updateReports(1));
        document.getElementById('reportType').addEventListener('change', () => updateReports(1));

        const societySelect = document.getElementById('societyId');
        if (societySelect) {
            societySelect.addEventListener('change', () => updateReports(1));
        }

        // Initial load
        updateReports(1);
    })();
</script>
</body>
</html>
