<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover, shrink-to-fit=no">
	<title>SocietyHub · Super Admin Dashboard</title>
	<link rel="icon" href="<?= base_url('assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png') ?>" type="image/png">

	<!-- Icons & Fonts -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

	<!-- External CSS -->
	<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">

</head>

<body>
	<div class="overlay" id="overlay"></div>

	<?php $activePage = 'dashboard'; ?>
	<!-- <?php include('sidebar.php') ?> -->


	<!-- <div class="header" id="header">
		<div class="header-left">
			<i class="fas fa-bars hamburger" id="hamburger"></i>
			<div class="header-title">Dashboard</div>
			<div class="search-bar">
				<i class="fas fa-search"></i>
				<input type="text" placeholder="Search...">
			</div>
		</div>
		<?php
		// $this->load->view('header');
		?>
	</div> -->
	<!-- MAIN CONTENT (page‑specific) -->
	<div class="main" id="main">
		<!-- STATS adaptive grid -->
		<div class="stats-grid">
			<?php foreach ($stats as $card): ?>
				<div class="stat-card">
					<div class="stat-icon"><i class="fas <?= html_escape($card['icon']) ?>"></i></div>
					<div class="stat-info">
						<h4>
							<?= html_escape($card['label']) ?>
						</h4>
						<h2>
							<?php
							if (is_numeric($card['value'])) {
								echo number_format($card['value']);
							} else {
								echo html_escape($card['value']);
							}
							?>
						</h2>
						<div class="stat-trend">
							<i class="fas fa-arrow-up"></i>
							<span>
								<?= html_escape($card['trend']) ?>
							</span>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ($isSuperAdmin && !empty($societies)): ?>
			<form method="get" action="<?= site_url('dashboard_controller') ?>" style="margin-bottom:16px;">
				<select name="society_id" onchange="this.form.submit()" style="padding:8px 12px;border-radius:10px;">
					<option value="">All Societies</option>
					<?php foreach ($societies as $soc): ?>
						<option value="<?= (int) $soc['id'] ?>" <?= ((int) $selectedSocietyId === (int) $soc['id']) ? 'selected' : '' ?>>
							<?= html_escape($soc['name']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</form>
		<?php endif; ?>
		<!-- CHARTS -->
		<div class="charts-row">
			<div class="chart-card">
				<div class="chart-header">
					<h3><i class="fas fa-chart-line"></i> Income vs Expenses</h3>
					<span class="chart-period">This Year</span>
				</div>
				<div class="chart-container">
					<canvas id="incomeChart"></canvas>
				</div>
			</div>
			<div class="chart-card">
				<div class="chart-header">
					<h3><i class="fas fa-chart-pie"></i> Payment Status</h3>
					<span class="chart-period">This Month</span>
				</div>
				<div class="chart-container">
					<canvas id="paymentChart"></canvas>
				</div>
			</div>
		</div>

		<!-- MANAGEMENT -->
		<div class="management-grid">
			<div class="management-card">
				<div class="section-header">
					<h3><i class="fas fa-user-plus"></i> Recent Members</h3><a href="#" class="view-all">View All →</a>
				</div>
				<div class="member-list">
					<?php if (!empty($recentMembers)): ?>
						<?php foreach ($recentMembers as $m): ?>
							<div class="member-item">
								<div class="member-info">
									<div class="member-avatar">
										<?= strtoupper(substr($m['name'] ?? 'U', 0, 2)) ?>
									</div>
									<div class="member-details">
										<h4>
											<?= html_escape($m['name'] ?? '') ?>
										</h4>
										<span>
											<?= html_escape($m['flat_no'] ?? '—') ?>
											<?php if (!empty($m['society_name'])): ?>
												·
												<?= html_escape($m['society_name']) ?>
											<?php endif; ?>
											·
											<?= html_escape(ucfirst($m['member_type'] ?? 'member')) ?>
										</span>
									</div>
								</div>
								<span
									class="member-status <?= ((int) ($m['status'] ?? 0) === 1) ? 'status-active' : 'status-pending' ?>">
									<?= ((int) ($m['status'] ?? 0) === 1) ? 'Active' : 'Pending' ?>
								</span>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<div style="padding:16px;color:var(--text-light);text-align:center;">No records found</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="management-card">
				<div class="section-header">
					<h3><i class="fas fa-bolt"></i> Quick Actions</h3>
				</div>
				<div class="quick-actions-grid">
					<a href="<?= base_url('manage_member') ?>" class="quick-action-item"><i
							class="fas fa-user-plus"></i><span>Add Member</span></a>
					<a href="<?= base_url('payments') ?>" class="quick-action-item"><i
							class="fas fa-file-invoice"></i><span>Generate
							Bill</span></a>
					<a href="<?= base_url('aminities') ?>" class="quick-action-item"><i
							class="fas fa-calendar-plus"></i><span>
							Amenity</span></a>
					<a href="<?= base_url('notice') ?>" class="quick-action-item"><i
							class="fas fa-bullhorn"></i><span>Create Notice</span></a>
				</div>
			</div>
		</div>

		<!-- FEATURE MODULES -->
		<div class="management-card" style="margin-bottom: 30px;">
			<div class="section-header">
				<h3><i class="fas fa-tasks"></i> Management Modules</h3>
			</div>
			<div class="feature-grid">
				<a href="<?= base_url('manage_member') ?>" class="feature-item"><i class="fas fa-user-plus"></i>
					<h4>Member</h4>
					<p>Add, edit</p>
				</a>
				<a href="<?= base_url('flat_unit') ?>" class="feature-item"><i class="fas fa-building"></i>
					<h4>Flat/Unit</h4>
					<p>Properties</p>
				</a>
				<a href="<?= base_url('payments') ?>" class="feature-item"><i class="fas fa-money-bill-wave"></i>
					<h4>Payment</h4>
					<p>Track</p>
				</a>
				<!-- <a href="#" class="feature-item"><i class="fas fa-hand-holding-usd"></i>
					<h4>Income</h4>
					<p>Track</p>
				</a> -->
				<a href="<?= base_url('parking/dashboard') ?>" class="feature-item"><i class="fas fa-parking"></i>
					<h4>Parking</h4>
					<p>Allocate</p>
				</a>
				<a href="<?= base_url('documents') ?>" class="feature-item"><i class="fas fa-folder"></i>
					<h4>Documents</h4>
					<p>Store</p>
				</a>
				<!-- <a href="#" class="feature-item"><i class="fas fa-phone-alt"></i>
					<h4>Emergency</h4>
					<p>Contacts</p>
				</a> -->
				<!-- <a href="#" class="feature-item"><i class="fas fa-truck"></i>
					<h4>Vendor</h4>
					<p>Providers</p>
				</a> -->
				<a href="<?= base_url('events') ?>" class="feature-item"><i class="fas fa-calendar-alt"></i>
					<h4>Events</h4>
					<p>Booking</p>
				</a>
			</div>
		</div>

		<!-- AI INSIGHTS -->
		<div class="insights-section">
			<div class="insights-header">
				<h3><i class="fas fa-robot"></i> AI Insights</h3>
				<span>Updated 5 min ago</span>
			</div>
			<div class="insights-grid">
				<div class="insight-card">
					<h4>Maintenance Prediction</h4>
					<div class="insight-value">
						<?= html_escape($insights['maintenancePrediction']) ?>
					</div>
					<div class="insight-trend"><i class="fas fa-arrow-up"></i> dynamic from dues</div>
				</div>
				<div class="insight-card">
					<h4>Complaint Resolution</h4>
					<div class="insight-value">
						<?= html_escape($insights['avgComplaintTime']) ?>
					</div>
					<div class="insight-trend"><i class="fas fa-arrow-down"></i> average resolved time</div>
				</div>
				<div class="insight-card">
					<h4>Occupancy Forecast</h4>
					<div class="insight-value">
						<?= html_escape($insights['occupancyForecast']) ?>
					</div>
					<div class="insight-trend"><i class="fas fa-arrow-up"></i> based on flats</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Global JS -->
	<script src="<?= base_url('assets/js/main.js') ?>"></script>

	<!-- Page‑specific chart initialization -->
	<script>
		const chartLabels = <?= json_encode($chartLabels) ?>;
		const incomeSeries = <?= json_encode($incomeSeries) ?>;
		const expenseSeries = <?= json_encode($expenseSeries) ?>;
		const paymentSummary = <?= json_encode($paymentSummary) ?>;
		function initCharts() {
			const isDark = document.body.classList.contains('dark-mode');
			const textColor = isDark ? '#e2e8f0' : '#2c3e50';
			const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';

			const incomeCtx = document.getElementById('incomeChart').getContext('2d');
			if (window.incomeChart instanceof Chart) window.incomeChart.destroy();
			window.incomeChart = new Chart(incomeCtx, {
				type: 'line',
				data: {
					labels: chartLabels,
					datasets: [
						{
							label: 'Income',
							data: incomeSeries,
							borderColor: '#27ae60',
							backgroundColor: 'rgba(39,174,96,0.1)',
							tension: 0.4,
							borderWidth: 3,
							pointRadius: 4,
							fill: true
						},
						{
							label: 'Expenses',
							data: expenseSeries,
							borderColor: '#e74c3c',
							backgroundColor: 'rgba(231,76,60,0.1)',
							tension: 0.4,
							borderWidth: 3,
							pointRadius: 4,
							fill: true
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false },
						tooltip: {
							mode: 'index',
							intersect: false,
							callbacks: {
								label: ctx => ctx.dataset.label + ': ₹' + Number(ctx.parsed.y).toLocaleString()
							}
						}
					},
					scales: {
						y: { grid: { color: gridColor } },
						x: { grid: { display: false } }
					}
				}
			});

			const paymentCtx = document.getElementById('paymentChart').getContext('2d');
			if (window.paymentChart instanceof Chart) window.paymentChart.destroy();
			window.paymentChart = new Chart(paymentCtx, {
				type: 'doughnut',
				data: {
					labels: ['Paid', 'Pending', 'Overdue'],
					datasets: [{
						data: [
							paymentSummary.paid || 0,
							paymentSummary.pending || 0,
							paymentSummary.overdue || 0
						],
						backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'],
						borderWidth: 0,
						hoverOffset: 15
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					cutout: '70%',
					plugins: {
						legend: {
							position: 'bottom',
							labels: { usePointStyle: true, padding: 20, color: textColor }
						}
					}
				}
			});
		}

		document.addEventListener('DOMContentLoaded', initCharts);
		document.addEventListener('themeChanged', initCharts);

		// Initial load
		document.addEventListener('DOMContentLoaded', initCharts);

		// Re‑initialize charts when theme changes (global event from main.js)
		document.addEventListener('themeChanged', initCharts);

		// Resize handler – smooth update
		let resizeTimer;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(() => {
				if (window.incomeChart) window.incomeChart.update();
				if (window.paymentChart) window.paymentChart.update();
			}, 100);
		});
	</script>
</body>

</html>
