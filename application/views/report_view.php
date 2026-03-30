<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<!-- ULTRA‑RESPONSIVE META CONFIG (same as dashboard) -->
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover, shrink-to-fit=no">
	<title>SocietyHub · Reports & Analytics</title>
	<link rel="icon" href="<?= base_url('assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png') ?>" type="image/png">

	<!-- Icons & Fonts -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

	<!-- External CSS (same as dashboard) -->
	<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">

	<!-- Additional styles for report filters and layout + responsive fix -->
	<style>
		/* Filter bar – consistent with dashboard design */
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
			/* default for larger screens */
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

		/* === RESPONSIVE FIX FOR DATE RANGE ON SMALL SCREENS === */
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
				/* override fixed width */
			}

			.filter-group .date-range span {
				text-align: center;
				margin: 0;
				font-size: 0.9rem;
				color: var(--text-light);
			}

			.btn-generate {
				width: 100%;
				margin-left: 0;
				justify-content: center;
				padding: 12px 20px;
			}
		}

		/* Report summary cards (KPI) */
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

		.trend.up {
			color: var(--success);
		}

		.trend.down {
			color: var(--danger);
		}

		/* Chart cards – reuse chart-card but ensure proper height */
		.chart-card.large {
			grid-column: span 2;
		}

		@media (max-width: 700px) {
			.chart-card.large {
				grid-column: span 1;
			}
		}

		/* Table for report details (simple) */
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
		}

		.report-table td {
			padding: 14px 12px;
			border-bottom: 1px solid var(--border);
			color: var(--text-dark);
		}

		.report-table tbody tr:hover {
			background: var(--light-bg);
		}
	</style>
</head>

<body>
	<!-- Overlay (mobile sidebar) -->
	<div class="overlay" id="overlay"></div>

	<!-- SIDEBAR (same structure, "Reports" active) -->
	<?php $activePage = "reports" ?>
	<?php include('sidebar.php') ?>

	<!-- HEADER (identical) -->
	<div class="header" id="header">
		<div class="header-left">
			<i class="fas fa-bars hamburger" id="hamburger"></i>
			<div class="header-title">Reports & Analytics</div>
			<div class="search-bar">
				<i class="fas fa-search"></i>
				<input type="text" placeholder="Search reports..." id="globalSearch">
			</div>
		</div>
		<?php $this->load->view('header'); ?>
	</div>

	<!-- MAIN CONTENT (Reports page) -->
	<div class="main" id="main">
		<!-- Filter bar (now fully responsive) -->
		<div class="reports-filter-bar">
			<div class="filter-group">
				<label>Report type</label>
				<select id="reportType">
					<option value="financial" selected>Financial</option>
					<option value="complaints">Complaints</option>
					<option value="visitors">Visitors</option>
					<option value="maintenance">Maintenance</option>
				</select>
			</div>
			<div class="filter-group">
				<label>Date range</label>
				<div class="date-range">
					<input type="date" id="startDate" value="2025-01-01">
					<span>to</span>
					<input type="date" id="endDate" value="2025-02-13">
				</div>
			</div>
			<button class="btn-generate" id="generateReportBtn"><i class="fas fa-sync-alt"></i> Generate</button>
		</div>

		<!-- Summary KPI cards (dynamic) -->
		<div class="report-stats-grid" id="kpiContainer">
			<!-- filled by JS -->
		</div>

		<!-- Charts grid (responsive) -->
		<div class="charts-row" style="margin-bottom: 30px;">
			<div class="chart-card large" id="chart1Card">
				<div class="chart-header">
					<h3><i class="fas fa-chart-bar"></i> <span id="chart1Title">Income vs Expenses</span></h3>
					<span class="chart-period" id="chart1Period">Jan 1 - Feb 13, 2025</span>
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

		<!-- Detailed data table -->
		<div class="report-table-wrapper">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
				<h3 style="font-weight: 700;">Transaction Details</h3>
				<button class="btn btn-secondary" id="exportBtn"><i class="fas fa-download"></i> Export CSV</button>
			</div>
			<div class="table-responsive">
				<table class="report-table" id="reportTable">
					<thead>
						<tr id="tableHeader">
							<!-- dynamic headers -->
						</tr>
					</thead>
					<tbody id="tableBody">
						<!-- dynamic rows -->
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<!-- SCRIPT: Theme, sidebar, and report logic (unchanged) -->
	<script>
		(function () {
			// ---------- DARK MODE + SIDEBAR (from dashboard) ----------

			// ---------- REPORT GENERATION (dynamic data) ----------
			// Chart instances
			let primaryChart, secondaryChart;

			// Helper: format currency
			const formatINR = (val) => '₹' + val.toLocaleString('en-IN');

			// Generate random data based on report type and date range
			function generateReportData(type, start, end) {
				const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) || 30;
				const monthCount = Math.max(1, Math.ceil(daysDiff / 30));

				if (type === 'financial') {
					return {
						kpi: [
							{ label: 'Total Income', value: formatINR(1250000 + Math.floor(Math.random() * 500000)), trend: '+8%', trendDir: 'up' },
							{ label: 'Total Expenses', value: formatINR(870000 + Math.floor(Math.random() * 300000)), trend: '+5%', trendDir: 'up' },
							{ label: 'Net Profit', value: formatINR(380000 + Math.floor(Math.random() * 200000)), trend: '+12%', trendDir: 'up' },
							{ label: 'Pending Dues', value: formatINR(184000 + Math.floor(Math.random() * 60000)), trend: '-3%', trendDir: 'down' }
						],
						primaryChart: {
							labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'].slice(0, monthCount),
							datasets: [
								{ label: 'Income', data: [850000, 920000, 880000, 950000, 1020000, 1080000].slice(0, monthCount), borderColor: '#27ae60', backgroundColor: 'rgba(39,174,96,0.1)' },
								{ label: 'Expenses', data: [620000, 680000, 650000, 720000, 750000, 780000].slice(0, monthCount), borderColor: '#e74c3c', backgroundColor: 'rgba(231,76,60,0.1)' }
							]
						},
						secondaryChart: {
							type: 'doughnut',
							labels: ['Maintenance', 'Salaries', 'Utilities', 'Others'],
							data: [45, 25, 20, 10],
							colors: ['#3498db', '#2c3e50', '#f39c12', '#95a5a6']
						},
						table: {
							headers: ['Date', 'Description', 'Category', 'Amount', 'Status'],
							rows: [
								['02/13/2025', 'Maintenance fee A-101', 'Income', '₹5,200', 'Paid'],
								['02/12/2025', 'Electricity bill', 'Expense', '₹12,800', 'Paid'],
								['02/11/2025', 'Plumbing repair', 'Expense', '₹3,500', 'Pending'],
								['02/10/2025', 'Clubhouse booking', 'Income', '₹2,000', 'Paid'],
								['02/09/2025', 'Salary - security', 'Expense', '₹18,000', 'Paid']
							]
						}
					};
				} else if (type === 'complaints') {
					return {
						kpi: [
							{ label: 'Open Complaints', value: '18', trend: '+2', trendDir: 'up' },
							{ label: 'Resolved (30d)', value: '42', trend: '+15%', trendDir: 'up' },
							{ label: 'Avg Resolution', value: '2.4 days', trend: '-0.3d', trendDir: 'down' },
							{ label: 'Urgent', value: '5', trend: '+1', trendDir: 'up' }
						],
						primaryChart: {
							labels: ['Plumbing', 'Electrical', 'Noise', 'Cleanliness', 'Other'],
							datasets: [
								{ label: 'Complaints by category', data: [12, 9, 7, 5, 4], backgroundColor: '#e74c3c', borderColor: '#c0392b' }
							]
						},
						secondaryChart: {
							type: 'pie',
							labels: ['Resolved', 'In Progress', 'Pending'],
							data: [58, 24, 18],
							colors: ['#27ae60', '#f39c12', '#e74c3c']
						},
						table: {
							headers: ['ID', 'Complaint', 'Category', 'Status', 'Date'],
							rows: [
								['#101', 'Water leakage', 'Plumbing', 'In Progress', '02/12/2025'],
								['#102', 'Power outage', 'Electrical', 'Resolved', '02/10/2025'],
								['#103', 'Loud music', 'Noise', 'Pending', '02/13/2025'],
								['#104', 'Garbage not collected', 'Cleanliness', 'Resolved', '02/09/2025']
							]
						}
					};
				} else if (type === 'visitors') {
					return {
						kpi: [
							{ label: 'Total Visitors', value: '342', trend: '+23%', trendDir: 'up' },
							{ label: 'Checked In', value: '78', trend: '+5', trendDir: 'up' },
							{ label: 'Pending', value: '12', trend: '-2', trendDir: 'down' },
							{ label: 'Avg Visit Time', value: '47 min', trend: '+5min', trendDir: 'up' }
						],
						primaryChart: {
							labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
							datasets: [
								{ label: 'Visitors per day', data: [45, 52, 38, 61, 73, 42, 31], borderColor: '#3498db', backgroundColor: 'rgba(52,152,219,0.1)' }
							]
						},
						secondaryChart: {
							type: 'doughnut',
							labels: ['Guest', 'Delivery', 'Service', 'Other'],
							data: [48, 32, 15, 5],
							colors: ['#3498db', '#2ecc71', '#f1c40f', '#95a5a6']
						},
						table: {
							headers: ['Visitor', 'Flat', 'Purpose', 'Check-in', 'Status'],
							rows: [
								['Rahul Mehta', 'C-302', 'Plumbing', '09:30 AM', 'Checked Out'],
								['Anjali Kapoor', 'A-101', 'Guest', '02:20 PM', 'Checked In'],
								['Delivery', 'B-205', 'Food', '06:00 PM', 'Pending']
							]
						}
					};
				} else { // maintenance
					return {
						kpi: [
							{ label: 'Pending Requests', value: '23', trend: '+4', trendDir: 'up' },
							{ label: 'Completed', value: '156', trend: '+12%', trendDir: 'up' },
							{ label: 'Avg Cost/Job', value: '₹1,850', trend: '-5%', trendDir: 'down' },
							{ label: 'Overdue', value: '7', trend: '+2', trendDir: 'up' }
						],
						primaryChart: {
							labels: ['Plumbing', 'Electrical', 'HVAC', 'Cleaning', 'Painting'],
							datasets: [
								{ label: 'Requests by type', data: [28, 19, 12, 16, 8], backgroundColor: '#f39c12', borderColor: '#e67e22' }
							]
						},
						secondaryChart: {
							type: 'pie',
							labels: ['Completed', 'In Progress', 'Pending'],
							data: [65, 20, 15],
							colors: ['#27ae60', '#f39c12', '#e74c3c']
						},
						table: {
							headers: ['Request', 'Flat', 'Category', 'Status', 'Date'],
							rows: [
								['Leaky faucet', 'A-101', 'Plumbing', 'Completed', '02/12/2025'],
								['AC not working', 'B-203', 'HVAC', 'In Progress', '02/11/2025'],
								['Light fixture', 'C-304', 'Electrical', 'Pending', '02/13/2025']
							]
						}
					};
				}
			}

			// Render KPI cards
			function renderKPI(kpiData) {
				const container = document.getElementById('kpiContainer');
				container.innerHTML = '';
				kpiData.forEach(k => {
					const card = document.createElement('div');
					card.className = 'report-stat-card';
					card.innerHTML = `
						<div class="label">${k.label}</div>
						<div class="value">${k.value}</div>
						<div class="trend ${k.trendDir}">${k.trend}</div>
					`;
					container.appendChild(card);
				});
			}

			// Update charts with theme colors
			function updateChartsTheme() {
				if (primaryChart) primaryChart.update();
				if (secondaryChart) secondaryChart.update();
			}

			// Render charts based on data
			function renderCharts(data, type, startDateStr, endDateStr) {
				const ctx1 = document.getElementById('primaryChart').getContext('2d');
				const ctx2 = document.getElementById('secondaryChart').getContext('2d');

				// Destroy existing charts
				if (primaryChart) primaryChart.destroy();
				if (secondaryChart) secondaryChart.destroy();

				const isDark = document.body.classList.contains('dark-mode');
				const textColor = isDark ? '#e2e8f0' : '#2c3e50';
				const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';

				// Set titles
				document.getElementById('chart1Title').innerText = type === 'financial' ? 'Income vs Expenses' :
					type === 'complaints' ? 'Complaints by Category' :
						type === 'visitors' ? 'Daily Visitors' : 'Maintenance Requests';
				document.getElementById('chart2Title').innerText = type === 'financial' ? 'Expense Breakdown' :
					type === 'complaints' ? 'Complaint Status' :
						type === 'visitors' ? 'Visitor Purpose' : 'Request Status';
				document.getElementById('chart1Period').innerText = `${startDateStr} to ${endDateStr}`;

				// Primary chart (bar/line)
				const primaryData = data.primaryChart;
				primaryChart = new Chart(ctx1, {
					type: primaryData.datasets.length > 1 ? 'line' : 'bar',
					data: primaryData,
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: { legend: { labels: { color: textColor } } },
						scales: { y: { grid: { color: gridColor }, ticks: { color: textColor } }, x: { ticks: { color: textColor } } }
					}
				});

				// Secondary chart (doughnut/pie)
				const secData = data.secondaryChart;
				secondaryChart = new Chart(ctx2, {
					type: secData.type || 'doughnut',
					data: {
						labels: secData.labels,
						datasets: [{
							data: secData.data,
							backgroundColor: secData.colors || ['#3498db', '#2c3e50', '#f39c12', '#95a5a6'],
							borderWidth: 0
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true } },
							tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw}%` } }
						},
						cutout: secData.type === 'doughnut' ? '65%' : undefined
					}
				});
			}

			// Render table
			function renderTable(data) {
				const thead = document.getElementById('tableHeader');
				const tbody = document.getElementById('tableBody');
				thead.innerHTML = '';
				tbody.innerHTML = '';

				data.table.headers.forEach(h => {
					const th = document.createElement('th');
					th.innerText = h;
					thead.appendChild(th);
				});

				data.table.rows.forEach(row => {
					const tr = document.createElement('tr');
					row.forEach(cell => {
						const td = document.createElement('td');
						td.innerText = cell;
						tr.appendChild(td);
					});
					tbody.appendChild(tr);
				});
			}

			// Main update function
			function updateReports() {
				const type = document.getElementById('reportType').value;
				const startDate = new Date(document.getElementById('startDate').value);
				const endDate = new Date(document.getElementById('endDate').value);
				if (isNaN(startDate) || isNaN(endDate)) return;

				const startStr = document.getElementById('startDate').value.split('-').reverse().join('/');
				const endStr = document.getElementById('endDate').value.split('-').reverse().join('/');

				const data = generateReportData(type, startDate, endDate);
				renderKPI(data.kpi);
				renderCharts(data, type, startStr, endStr);
				renderTable(data);
			}

			// Event listeners
			document.getElementById('generateReportBtn').addEventListener('click', updateReports);
			document.getElementById('exportBtn').addEventListener('click', () => {
				alert('Demo: Report exported as CSV (simulated).');
			});

			// Initial load
			updateReports();

			// Global search (demo)
			document.getElementById('globalSearch').addEventListener('input', function (e) {
				// Just a demo: filter table rows (simple)
				const term = e.target.value.toLowerCase();
				const rows = document.querySelectorAll('#tableBody tr');
				rows.forEach(row => {
					const text = row.innerText.toLowerCase();
					row.style.display = text.includes(term) ? '' : 'none';
				});
			});
		})();
	</script>
</body>

</html>
