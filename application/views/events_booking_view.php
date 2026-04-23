<?php defined('BASEPATH') OR exit('No direct script access allowed');
$tab = $tab ?? 'events';

/*
 * ─── PERMISSION HELPERS ───────────────────────────────────────────────────────
 * Super admin gets every privilege: manage events, approve bookings, QR scan.
 */
$_canManage = !empty($canManage) || !empty($isSuperAdmin);
$_canApprove = !empty($canApprove) || !empty($isSuperAdmin);
$_isChairman = !empty($isChairman) || !empty($isSuperAdmin);

/*
 * ─── PAGINATION DEFAULTS ──────────────────────────────────────────────────────
 * Controller should pass: $events_page, $events_total_pages,
 *                         $bookings_page, $bookings_total_pages
 */
$events_page = (int) ($events_page ?? 1);
$events_total_pages = (int) ($events_total_pages ?? 1);
$bookings_page = (int) ($bookings_page ?? 1);
$bookings_total_pages = (int) ($bookings_total_pages ?? 1);
$per_page = (int) ($per_page ?? 9);

/* Build pagination URL helper */
function paginationUrl(string $base, array $params, int $page): string
{
	$params['page'] = $page;
	return site_url($base . '?' . http_build_query($params));
}

/* Render pagination bar (returns HTML) */
function renderPagination(int $current, int $total, string $baseRoute, array $params, string $anchor = ''): string
{
	if ($total <= 1)
		return '';
	$html = '<nav class="pagination-nav" aria-label="Page navigation">';
	$html .= '<ul class="pagination">';

	/* Prev */
	$prevDisabled = $current <= 1 ? 'disabled' : '';
	$prevHref = $current > 1 ? paginationUrl($baseRoute, $params, $current - 1) . $anchor : '#';
	$html .= '<li class="page-item ' . $prevDisabled . '">';
	$html .= '<a class="page-link" href="' . $prevHref . '"><i class="fas fa-chevron-left"></i></a></li>';

	/* Numbered pages – show window of 5 */
	$window = 2;
	for ($p = 1; $p <= $total; $p++) {
		if ($p === 1 || $p === $total || ($p >= $current - $window && $p <= $current + $window)) {
			$active = ($p === $current) ? 'active' : '';
			$href = paginationUrl($baseRoute, $params, $p) . $anchor;
			$html .= '<li class="page-item ' . $active . '">';
			$html .= '<a class="page-link" href="' . $href . '">' . $p . '</a></li>';
		} elseif ($p === $current - $window - 1 || $p === $current + $window + 1) {
			$html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
		}
	}

	/* Next */
	$nextDisabled = $current >= $total ? 'disabled' : '';
	$nextHref = $current < $total ? paginationUrl($baseRoute, $params, $current + 1) . $anchor : '#';
	$html .= '<li class="page-item ' . $nextDisabled . '">';
	$html .= '<a class="page-link" href="' . $nextHref . '"><i class="fas fa-chevron-right"></i></a></li>';

	$html .= '</ul></nav>';
	return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>SocietyHub · Events &amp; Bookings</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
	<style>
		/* ── QR ── */
		.qr-box {
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 14px;
			background: #fff;
			border: 1px dashed var(--border);
			border-radius: 14px;
			margin-top: 12px;
			min-height: 250px;
		}

		.qr-link-box {
			margin-top: 10px;
			font-size: .78rem;
			word-break: break-all;
			color: var(--text-light);
			background: #f8fafc;
			border: 1px solid var(--border);
			border-radius: 10px;
			padding: 10px 12px;
		}

		.btn-qr {
			color: #fff;
			background: #6366f1;
			border: none;
			border-radius: 6px;
			padding: 4px 12px;
			font-size: .74rem;
			font-weight: 700;
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			gap: 5px;
			white-space: nowrap;
			transition: .15s;
		}

		.btn-qr:hover {
			background: #4f46e5;
		}

		.btn-qr.scanned {
			background: #389e0d;
		}

		.btn-qr.scanned:hover {
			background: #237804;
		}

		/* ── Tabs ── */
		.tab-bar {
			display: flex;
			gap: 0;
			background: var(--card-bg);
			border: 1px solid var(--border);
			border-radius: 12px;
			overflow: hidden;
			margin-bottom: 24px;
			width: fit-content;
		}

		.tab-btn {
			padding: 10px 28px;
			font-size: .9rem;
			font-weight: 600;
			color: var(--text-light);
			background: transparent;
			border: none;
			cursor: pointer;
			transition: .15s;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.tab-btn.active {
			background: var(--primary);
			color: #fff;
		}

		.tab-btn:not(.active):hover {
			background: var(--bg-light, #f5f5f5);
			color: var(--primary);
		}

		/* ── Events grid ── */
		.events-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
			gap: 20px;
			margin-bottom: 24px;
		}

		.event-card {
			background: var(--card-bg);
			border-radius: 16px;
			border: 1px solid var(--border);
			overflow: hidden;
			display: flex;
			flex-direction: column;
			transition: .2s;
		}

		.event-card:hover {
			box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
			transform: translateY(-2px);
		}

		.event-card-header {
			padding: 14px 16px 12px;
			border-bottom: 1px solid var(--border);
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.event-type-icon {
			width: 40px;
			height: 40px;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1rem;
			flex-shrink: 0;
		}

		.event-type-icon.festival {
			background: #fff7e6;
			color: #d48806;
		}

		.event-type-icon.celebration {
			background: #f0f5ff;
			color: #2f54eb;
		}

		.event-type-icon.cultural {
			background: #f9f0ff;
			color: #722ed1;
		}

		.event-type-icon.sports {
			background: #f6ffed;
			color: #389e0d;
		}

		.event-type-icon.meeting {
			background: #e6fffb;
			color: #08979c;
		}

		.event-card-body {
			padding: 12px 16px;
			flex: 1;
		}

		.event-card-footer {
			padding: 10px 16px;
			border-top: 1px solid var(--border);
			display: flex;
			align-items: center;
			gap: 8px;
			flex-wrap: wrap;
		}

		.event-title {
			font-size: .95rem;
			font-weight: 700;
			color: var(--text-dark);
			margin-bottom: 4px;
		}

		.event-meta {
			font-size: .77rem;
			color: var(--text-light);
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			margin-top: 6px;
		}

		.event-meta span {
			display: flex;
			align-items: center;
			gap: 4px;
		}

		.evt-badge {
			padding: 2px 9px;
			border-radius: 20px;
			font-size: .7rem;
			font-weight: 700;
			text-transform: uppercase;
		}

		.evt-badge.upcoming {
			background: #e6f7ff;
			color: #0958d9;
		}

		.evt-badge.ongoing {
			background: #f6ffed;
			color: #389e0d;
		}

		.evt-badge.completed {
			background: #f5f5f5;
			color: #595959;
		}

		.evt-badge.cancelled {
			background: #fff1f0;
			color: #cf1322;
		}

		/* ── Fund ── */
		.fund-banner {
			background: linear-gradient(135deg, #fff7e6, #fff1b8);
			border: 1px solid #ffe58f;
			border-radius: 10px;
			padding: 10px 12px;
			margin-top: 10px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			flex-wrap: wrap;
		}

		.fund-progress {
			height: 5px;
			border-radius: 3px;
			background: #f5f5f5;
			margin-top: 5px;
			overflow: hidden;
		}

		.fund-progress-bar {
			height: 100%;
			border-radius: 3px;
			background: linear-gradient(90deg, #faad14, #fa8c16);
		}

		/* ── Badges ── */
		.status-badge.pending {
			background: #fff7e6;
			color: #d46b08;
		}

		.status-badge.approved {
			background: #f6ffed;
			color: #389e0d;
		}

		.status-badge.rejected {
			background: #fff1f0;
			color: #cf1322;
		}

		.pay-badge {
			padding: 3px 9px;
			border-radius: 20px;
			font-size: .7rem;
			font-weight: 600;
			text-transform: capitalize;
			display: inline-block;
		}

		.pay-badge.pending {
			background: #fff1f0;
			color: #cf1322;
		}

		.pay-badge.paid {
			background: #f6ffed;
			color: #389e0d;
		}

		.pay-badge.waived {
			background: #e6fffb;
			color: #08979c;
		}

		.society-badge {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: #eef1fb;
			color: #3b5bdb;
			border: 1px solid #c5d0f5;
			border-radius: 12px;
			padding: 2px 8px;
			font-size: .72rem;
			font-weight: 500;
		}

		/* ── Booking area ── */
		.booking-area {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.area-icon {
			width: 38px;
			height: 38px;
			border-radius: 9px;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			font-size: .95rem;
			flex-shrink: 0;
		}

		.area-details h4 {
			font-weight: 700;
			font-size: .88rem;
			color: var(--text-dark);
			margin-bottom: 2px;
		}

		.area-details span {
			font-size: .74rem;
			color: var(--text-light);
		}

		/* ── Tickets ── */
		.ticket-btn-wrap {
			position: relative;
			display: inline-flex;
			flex-direction: column;
			align-items: center;
			gap: 3px;
		}

		.ticket-scanned-badge {
			font-size: .65rem;
			font-weight: 700;
			color: #389e0d;
			background: #f6ffed;
			border: 1px solid #b7eb8f;
			border-radius: 8px;
			padding: 1px 7px;
			display: flex;
			align-items: center;
			gap: 3px;
			white-space: nowrap;
		}

		.qr-scan-info {
			font-size: .68rem;
			color: #389e0d;
			display: flex;
			align-items: center;
			gap: 3px;
			margin-top: 2px;
			white-space: nowrap;
		}

		.scan-list-item {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 8px 0;
			border-bottom: 1px solid var(--border);
		}

		.scan-list-item:last-child {
			border-bottom: none;
		}

		.scan-avatar {
			width: 34px;
			height: 34px;
			border-radius: 50%;
			background: var(--primary);
			color: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: .8rem;
			flex-shrink: 0;
		}

		.btn-pay {
			color: #fff;
			background: #52c41a;
			border: none;
			border-radius: 6px;
			padding: 4px 12px;
			font-size: .74rem;
			font-weight: 700;
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			gap: 5px;
			white-space: nowrap;
			transition: .15s;
		}

		.btn-pay:hover {
			background: #389e0d;
		}

		.role-info-badge {
			background: var(--bg, #f5f7ff);
			border: 1px solid var(--border);
			border-radius: 10px;
			padding: 10px 14px;
			margin-bottom: 14px;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.role-info-badge i {
			color: var(--primary);
			font-size: 1.1rem;
		}

		.role-info-badge span {
			font-size: .83rem;
			color: var(--text-light);
		}

		/* ── Camera scanner ── */
		#qrScannerModal .modal-content {
			max-width: 500px;
		}

		#qr-reader {
			width: 100%;
			border-radius: 12px;
			overflow: hidden;
			border: 1px solid var(--border);
		}

		#qr-reader__scan_region {
			min-height: 300px;
		}

		#qr-reader__scan_region video {
			border-radius: 12px;
		}

		/* ══════════════════════════════════════════════════
		   PAGINATION
		══════════════════════════════════════════════════ */
		.pagination-nav {
			display: flex;
			justify-content: center;
			margin: 24px 0 8px;
		}

		.pagination {
			display: flex;
			gap: 4px;
			list-style: none;
			margin: 0;
			padding: 0;
			align-items: center;
			flex-wrap: wrap;
		}

		.page-item .page-link {
			display: flex;
			align-items: center;
			justify-content: center;
			min-width: 36px;
			height: 36px;
			padding: 0 10px;
			border: 1.5px solid var(--border);
			border-radius: 9px;
			font-size: .82rem;
			font-weight: 600;
			color: var(--text-dark);
			background: var(--card-bg);
			text-decoration: none;
			transition: .15s;
		}

		.page-item .page-link:hover {
			border-color: var(--primary);
			color: var(--primary);
			background: #eff6ff;
		}

		.page-item.active .page-link {
			background: var(--primary);
			border-color: var(--primary);
			color: #fff;
		}

		.page-item.disabled .page-link {
			opacity: .45;
			pointer-events: none;
		}

		/* ── Pagination info strip ── */
		.pagination-info {
			text-align: center;
			font-size: .76rem;
			color: var(--text-light);
			margin-top: 6px;
		}

		/* ── Responsive ── */
		@media (max-width:768px) {
			.tab-bar {
				width: 100%;
			}

			.tab-btn {
				flex: 1;
				justify-content: center;
				padding: 10px 16px;
			}

			.events-grid {
				grid-template-columns: 1fr;
			}

			.table-wrapper {
				overflow-x: auto;
			}

			.modal-content {
				width: 95%;
				margin: 10px;
			}
		}

		.management-card {
			background: var(--card-bg);
			border-radius: 20px;
			padding: 20px;
			border: 1px solid var(--border);
			width: 100%;
		}
	</style>
</head>

<body>
	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'events_booking';
	include('sidebar.php'); ?>

	<div class="main" id="main">

		<?php if ($this->session->flashdata('success')): ?>
			<div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i>
				<?= $this->session->flashdata('success') ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i>
				<?= $this->session->flashdata('error') ?></div>
		<?php endif; ?>

		<!-- ── Tab Bar ── -->
		<div class="tab-bar">
			<a href="<?= site_url('events_booking_controller/events') ?>"
				class="tab-btn <?= $tab === 'events' ? 'active' : '' ?>">
				<i class="fas fa-calendar-alt"></i> Events &amp; Festivals
			</a>
			<a href="<?= site_url('events_booking_controller/bookings') ?>"
				class="tab-btn <?= $tab === 'bookings' ? 'active' : '' ?>">
				<i class="fas fa-calendar-days"></i> Area Bookings
			</a>
		</div>



		<?php if ($tab === 'events'): ?>
			<!-- ══════════════════════════════════════════════════
		 EVENTS TAB
	══════════════════════════════════════════════════ -->

			<div class="stats-grid">
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
					<div class="stat-info">
						<h4>Total Events</h4>
						<h2><?= (int) ($event_stats['total'] ?? 0) ?></h2>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
					<div class="stat-info">
						<h4>Upcoming</h4>
						<h2><?= (int) ($event_stats['upcoming'] ?? 0) ?></h2>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-circle-play"></i></div>
					<div class="stat-info">
						<h4>Ongoing</h4>
						<h2><?= (int) ($event_stats['ongoing'] ?? 0) ?></h2>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-hand-holding-dollar"></i></div>
					<div class="stat-info">
						<h4>Fund Requests</h4>
						<h2><?= (int) ($event_stats['fund_open'] ?? 0) ?></h2>
					</div>
				</div>
			</div>

			<!-- Filter -->
			<form method="GET" action="<?= site_url('events_booking_controller/events') ?>" id="filterForm">
				<input type="hidden" name="page" value="1">
				<div class="filter-section">
					<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
						<div class="filter-group"><label><i class="fas fa-building"></i> Society</label>
							<select name="society_id" class="filter-select" onchange="this.form.submit()">
								<option value="">All Societies</option>
								<?php foreach ($societies as $soc): ?>
									<option value="<?= (int) $soc['id'] ?>" <?= ((int) ($filters['society_id'] ?? 0) === (int) $soc['id']) ? 'selected' : '' ?>><?= html_escape($soc['name']) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
					<div class="filter-group"><label><i class="fas fa-tag"></i> Type</label>
						<select name="event_type" class="filter-select" onchange="this.form.submit()">
							<option value="">All Types</option>
							<?php foreach (['festival', 'celebration', 'cultural', 'sports', 'meeting'] as $t): ?>
								<option value="<?= $t ?>" <?= ($filters['event_type'] === $t) ? 'selected' : '' ?>>
									<?= ucfirst($t) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="filter-group"><label><i class="fas fa-circle"></i> Status</label>
						<select name="status" class="filter-select" onchange="this.form.submit()">
							<option value="">All Status</option>
							<?php foreach (['upcoming', 'ongoing', 'completed', 'cancelled'] as $s): ?>
								<option value="<?= $s ?>" <?= ($filters['status'] === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="search-box"><i class="fas fa-search"></i>
						<input type="text" name="search" id="searchInput" placeholder="Search events..."
							value="<?= html_escape($filters['search'] ?? '') ?>" autocomplete="off">
					</div>
					<div style="display:flex;gap:8px;align-items:flex-end;">
						<button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
						<?php $anyFilter = !empty($filters['search']) || !empty($filters['event_type']) || !empty($filters['status']) || !empty($filters['society_id']);
						if ($anyFilter): ?>
							<a href="<?= site_url('events_booking_controller/events') ?>" class="btn btn-outline"><i
									class="fas fa-times"></i> Clear</a>
						<?php endif; ?>
					</div>
				</div>
			</form>

			<!-- Table header -->
			<div class="table-header" style="margin-bottom:16px;">
				<h3>
					<i class="fas fa-calendar-alt"></i> Events &amp; Festivals
					<small
						style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= (int) ($event_stats['total'] ?? count($events ?? [])) ?>
						records)</small>
				</h3>
				<a href="<?= site_url('events_booking_controller/events') ?>" class="btn btn-outline"><i
						class="fas fa-sync-alt"></i> Refresh</a>
				
				<div class="page-actions">
					<!-- ── Camera Scan Button (Chairman + Super Admin) ── -->
				<?php if ($_isChairman): ?>
					<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
						<button type="button" class="btn btn-primary" onclick="openQrScanner()">
							<i class="fas fa-camera"></i> Scan QR Code
						</button>
					</div>
				<?php endif; ?>
					<?php if ($_canManage): ?>
						<button type="button" class="btn btn-primary" onclick="openAddEventModal()">
							<i class="fas fa-plus-circle"></i> Create Event
						</button>
					<?php endif; ?>
				</div>
			</div>

			<?php if (empty($events)): ?>
				<div style="text-align:center;padding:60px;color:var(--text-light);">
					<i class="fas fa-calendar-xmark" style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>
					No events found.
					<?php if ($_canManage): ?>
						<br><br>
						<button type="button" class="btn btn-primary" onclick="openAddEventModal()"><i
								class="fas fa-plus-circle"></i> Create First Event</button>
					<?php endif; ?>
				</div>
			<?php else: ?>
				<div class="events-grid">
					<?php
					$typeIcons = [
						'festival' => 'fa-star',
						'celebration' => 'fa-champagne-glasses',
						'cultural' => 'fa-masks-theater',
						'sports' => 'fa-trophy',
						'meeting' => 'fa-people-group',
					];
					foreach ($events as $e):
						$icon = $typeIcons[$e['event_type']] ?? 'fa-calendar-alt';
						$fundPct = (!empty($e['fund_amount']) && $e['fund_amount'] > 0)
							? min(100, round(($e['fund_raised'] ?? 0) / $e['fund_amount'] * 100)) : 0;
						$qrAlreadyScanned = !empty($e['qr_scanned_at']);
						?>
						<div class="event-card">
							<!-- ── Card Header ── -->
							<div class="event-card-header">
								<div class="event-type-icon <?= html_escape($e['event_type']) ?>">
									<i class="fas <?= $icon ?>"></i>
								</div>
								<div style="flex:1;min-width:0;">
									<div class="event-title"><?= html_escape($e['title']) ?></div>
									<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:3px;">
										<span class="evt-badge <?= html_escape($e['status']) ?>"><?= ucfirst($e['status']) ?></span>
										<?php if (!empty($isSuperAdmin) && !empty($e['society_name'])): ?>
											<span class="society-badge"><i class="fas fa-city"></i>
												<?= html_escape($e['society_name']) ?></span>
										<?php endif; ?>
									</div>
								</div>

								<!-- Edit / Delete — managers + super admin -->
								<?php if ($_canManage): ?>
									<div style="display:flex;gap:4px;flex-shrink:0;">
										<button class="btn-icon" title="Edit"
											onclick='editEvent(<?= htmlspecialchars(json_encode($e), ENT_QUOTES) ?>)'>
											<i class="fas fa-edit"></i>
										</button>
										<a href="<?= site_url('events_booking_controller/delete_event/' . (int) $e['id']) ?>"
											class="btn-icon delete" onclick="return confirm('Delete this event?')">
											<i class="fas fa-trash"></i>
										</a>
									</div>
								<?php endif; ?>

								<!-- QR Button — chairman + super admin -->
								<?php if ($_isChairman && !empty($e['qr_token'])): ?>
									<div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;">
										<button type="button" class="btn-qr <?= $qrAlreadyScanned ? 'scanned' : '' ?>" onclick='openQrModal(
									<?= (int) $e["id"] ?>,
									"<?= html_escape($e["title"]) ?>",
									"<?= site_url('events_booking_controller/scan_event_qr/' . $e["qr_token"]) ?>",
									<?= $qrAlreadyScanned ? 'true' : 'false' ?>
								)'>
											<i class="fas fa-qrcode"></i> <?= $qrAlreadyScanned ? 'Re-QR' : 'QR' ?>
										</button>
										<?php if ($qrAlreadyScanned): ?>
											<span class="qr-scan-info"><i class="fas fa-check-circle"></i> Scanned
												<?= date('d M, h:i A', strtotime($e['qr_scanned_at'])) ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div><!-- /.event-card-header -->

							<!-- ── Card Body ── -->
							<div class="event-card-body">
								<?php if (!empty($e['description'])): ?>
									<p style="font-size:.82rem;color:var(--text-light);margin-bottom:8px;">
										<?= html_escape(substr($e['description'], 0, 100)) ?>				<?= strlen($e['description']) > 100 ? '…' : '' ?>
									</p>
								<?php endif; ?>
								<div class="event-meta">
									<span><i class="fas fa-calendar"></i>
										<?= !empty($e['event_date']) ? date('d M Y', strtotime($e['event_date'])) : '—' ?></span>
									<?php if (!empty($e['start_time'])): ?>
										<span><i class="fas fa-clock"></i>
											<?= date('h:i A', strtotime($e['start_time'])) ?>				<?= !empty($e['end_time']) ? ' – ' . date('h:i A', strtotime($e['end_time'])) : '' ?></span>
									<?php endif; ?>
									<?php if (!empty($e['venue'])): ?>
										<span><i class="fas fa-location-dot"></i> <?= html_escape($e['venue']) ?></span>
									<?php endif; ?>
									<span><i class="fas fa-tag"></i> <?= ucfirst(html_escape($e['event_type'])) ?></span>
								</div>

								<!-- Fund / contribution banner -->
								<?php if (!empty($e['fund_required']) && !empty($e['fund_amount'])): ?>
									<div class="fund-banner">
										<div style="flex:1;min-width:160px;">
											<div style="font-size:.8rem;font-weight:700;color:#d48806;margin-bottom:3px;">
												<i class="fas fa-hand-holding-dollar"></i> Event Amount
												<span
													style="margin-left:5px;font-size:.72rem;background:#fff;border:1px solid #ffe58f;padding:1px 6px;border-radius:10px;color:#5c3d11;">
													<?= ucfirst($e['fund_status'] ?? 'open') ?>
												</span>
											</div>
											<div style="font-size:.78rem;color:#7c5319;">
												Target: <strong>₹<?= number_format($e['fund_amount'], 0) ?></strong>
												&nbsp;·&nbsp;
												Raised: <strong>₹<?= number_format($e['fund_raised'] ?? 0, 0) ?></strong>
											</div>
											<div class="fund-progress">
												<div class="fund-progress-bar" style="width:<?= $fundPct ?>%"></div>
											</div>
											<div style="font-size:.7rem;color:#7c5319;margin-top:2px;"><?= $fundPct ?>% raised</div>
											<div style="font-size:.7rem;color:#7c5319;">Your share:
												<strong>₹<?= number_format($e['per_person_share'], 0) ?></strong></div>
										</div>

										<!-- Resident: pay / ticket -->
										<?php if (!empty($isResident) && ($e['fund_status'] ?? '') === 'open'): ?>
											<?php if (!$e['user_has_paid']): ?>
												<button type="button" class="btn btn-primary"
													style="background:#faad14;border-color:#d48806;color:#1a1a1a;white-space:nowrap;"
													onclick='openContributeModal(<?= (int) $e["id"] ?>,"<?= html_escape($e["title"]) ?>",<?= (float) $e["per_person_share"] ?>,<?= (float) $e["fund_amount"] ?>)'>
													<i class="fas fa-hand-holding-dollar"></i> Pay Share
													(₹<?= number_format($e['per_person_share'], 0) ?>)
												</button>
											<?php else: ?>
												<div class="ticket-btn-wrap">
													<button type="button" class="btn btn-primary"
														style="background:<?= $e['ticket_scanned'] ? '#73d13d' : '#1890ff' ?>;border-color:<?= $e['ticket_scanned'] ? '#52c41a' : '#096dd9' ?>;color:#fff;"
														onclick='showTicketQr("<?= html_escape($e['user_ticket_token']) ?>","<?= html_escape($e['title']) ?>",<?= $e['ticket_scanned'] ? 'true' : 'false' ?>)'>
														<i class="fas fa-qrcode"></i> <?= $e['ticket_scanned'] ? 'My Ticket ✓' : 'My Ticket' ?>
													</button>
													<?php if ($e['ticket_scanned']): ?>
														<span class="ticket-scanned-badge"><i class="fas fa-check-circle"></i> Entry Scanned</span>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										<?php endif; ?>

										<!-- Chairman + Super Admin: view scanned tickets -->
										<?php if ($_isChairman && !empty($e['fund_required'])): ?>
											<button type="button" class="btn btn-outline"
												style="font-size:.74rem;padding:4px 10px;white-space:nowrap;"
												onclick='openScannedListModal(<?= (int) $e["id"] ?>,"<?= html_escape($e["title"]) ?>")'
												title="View scanned tickets">
												<i class="fas fa-list-check"></i> Tickets
											</button>
										<?php endif; ?>

										<!-- Super Admin: also show per-society ticket scan list regardless of isResident flag -->
										<?php if (!empty($isSuperAdmin) && !empty($e['fund_required']) && empty($isResident)): ?>
											<?php /* already shown via $_isChairman above */ ?>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div><!-- /.event-card-body -->

							<div class="event-card-footer">
								<span style="font-size:.73rem;color:var(--text-light);"><i class="fas fa-user"></i>
									<?= html_escape($e['created_by_name'] ?? 'Admin') ?></span>
								<span style="font-size:.73rem;color:var(--text-light);margin-left:auto;"><i
										class="fas fa-calendar-plus"></i> <?= date('d M Y', strtotime($e['created_at'])) ?></span>
							</div>
						</div><!-- /.event-card -->
					<?php endforeach; ?>
				</div><!-- /.events-grid -->

				<!-- ── Events Pagination ── -->
				<?php
				$evtParams = array_filter([
					'event_type' => $filters['event_type'] ?? '',
					'status' => $filters['status'] ?? '',
					'search' => $filters['search'] ?? '',
					'society_id' => $filters['society_id'] ?? '',
				]);
				echo renderPagination($events_page, $events_total_pages, 'events_booking_controller/events', $evtParams);
				if ($events_total_pages > 1):
					?>
					<div class="pagination-info">
						Page <?= $events_page ?> of <?= $events_total_pages ?>
						&nbsp;·&nbsp; <?= (int) ($event_stats['total'] ?? 0) ?> total events
						&nbsp;·&nbsp; <?= $per_page ?> per page
					</div>
				<?php endif; ?>
			<?php endif; // empty($events) ?>

			<?php if (!empty($recent_events)): ?>
				<div class="management-card" style="margin-bottom:30px;">
					<div class="section-header">
						<h3><i class="fas fa-clock"></i> Recent Events</h3>
					</div>
					<div class="member-list">
						<?php foreach ($recent_events as $r): ?>
							<div class="member-item">
								<div class="member-info">
									<div class="member-avatar"><i class="fas fa-calendar-alt" style="color:var(--primary);"></i>
									</div>
									<div class="member-details">
										<h4><?= html_escape($r['title']) ?></h4>
										<span><?= ucfirst(html_escape($r['event_type'])) ?><?= !empty($r['event_date']) ? ' · ' . date('d M Y', strtotime($r['event_date'])) : '' ?><?= (!empty($isSuperAdmin) && !empty($r['society_name'])) ? ' · <em>' . html_escape($r['society_name']) . '</em>' : '' ?></span>
									</div>
								</div>
								<span class="evt-badge <?= html_escape($r['status']) ?>"><?= ucfirst($r['status']) ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

		<?php else: ?>
			<!-- ══════════════════════════════════════════════════
		 BOOKINGS TAB
	══════════════════════════════════════════════════ -->

			<div class="stats-grid">
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-calendar-days"></i></div>
					<div class="stat-info">
						<h4>Total Bookings</h4>
						<h2><?= (int) ($booking_stats['total'] ?? 0) ?></h2>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
					<div class="stat-info">
						<h4>Pending</h4>
						<h2><?= (int) ($booking_stats['pending'] ?? 0) ?></h2>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-circle-check"></i></div>
					<div class="stat-info">
						<h4>Approved</h4>
						<h2><?= (int) ($booking_stats['approved'] ?? 0) ?></h2>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon"><i class="fas fa-coins"></i></div>
					<div class="stat-info">
						<h4>Payments Done</h4>
						<h2><?= (int) ($booking_stats['paid'] ?? 0) ?></h2>
					</div>
				</div>
			</div>

			<!-- Filter -->
			<form method="GET" action="<?= site_url('events_booking_controller/bookings') ?>" id="filterForm">
				<input type="hidden" name="page" value="1">
				<div class="filter-section">
					<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
						<div class="filter-group"><label><i class="fas fa-building"></i> Society</label>
							<select name="society_id" class="filter-select" onchange="this.form.submit()">
								<option value="">All Societies</option>
								<?php foreach ($societies as $soc): ?>
									<option value="<?= (int) $soc['id'] ?>" <?= ((int) ($filters['society_id'] ?? 0) === (int) $soc['id']) ? 'selected' : '' ?>><?= html_escape($soc['name']) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
					<div class="filter-group"><label><i class="fas fa-circle"></i> Status</label>
						<select name="status" class="filter-select" onchange="this.form.submit()">
							<option value="">All Status</option>
							<option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : '' ?>>Pending
							</option>
							<option value="approved" <?= ($filters['status'] === 'approved') ? 'selected' : '' ?>>Approved
							</option>
							<option value="rejected" <?= ($filters['status'] === 'rejected') ? 'selected' : '' ?>>Rejected
							</option>
						</select>
					</div>
					<div class="filter-group"><label><i class="fas fa-coins"></i> Payment</label>
						<select name="payment_status" class="filter-select" onchange="this.form.submit()">
							<option value="">All Payments</option>
							<option value="pending" <?= ($filters['payment_status'] === 'pending') ? 'selected' : '' ?>>Pending
							</option>
							<option value="paid" <?= ($filters['payment_status'] === 'paid') ? 'selected' : '' ?>>Paid</option>
							<option value="waived" <?= ($filters['payment_status'] === 'waived') ? 'selected' : '' ?>>Waived
							</option>
						</select>
					</div>
					<div class="search-box"><i class="fas fa-search"></i>
						<input type="text" name="search" id="searchInput" placeholder="Search name, area, flat..."
							value="<?= html_escape($filters['search'] ?? '') ?>" autocomplete="off">
					</div>
					<div style="display:flex;gap:8px;align-items:flex-end;">
						<button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
						<?php $anyFilter = !empty($filters['search']) || !empty($filters['status']) || !empty($filters['payment_status']) || !empty($filters['society_id']);
						if ($anyFilter): ?>
							<a href="<?= site_url('events_booking_controller/bookings') ?>" class="btn btn-outline"><i
									class="fas fa-times"></i> Clear</a>
						<?php endif; ?>
					</div>
				</div>
			</form>

			<!-- Table header -->
			<div class="table-header" style="margin-bottom:16px;">
				<h3>
					<i class="fas fa-calendar-days"></i> Area Booking Requests
					<small
						style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= (int) ($booking_stats['total'] ?? count($bookings ?? [])) ?>
						records)</small>
				</h3>
				<a href="<?= site_url('events_booking_controller/bookings') ?>" class="btn btn-outline"><i
						class="fas fa-sync-alt"></i> Refresh</a>
				<div class="page-actions">
					<button type="button" class="btn btn-primary" onclick="openAddBookingModal()">
						<i class="fas fa-plus-circle"></i> New Booking
					</button>
				</div>
			</div>

			<div class="table-section">
				<div class="table-wrapper">
					<table>
						<thead>
							<tr>
								<th>Area / Purpose</th>
								<th>Resident</th>
								<?php if (!empty($isSuperAdmin)): ?>
									<th>Society</th><?php endif; ?>
								<th>Date</th>
								<th>Time Slot</th>
								<th>Amount</th>
								<th>Payment</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($bookings)):
								foreach ($bookings as $b): ?>
									<tr>
										<td>
											<div class="booking-area">
												<div class="area-icon"><i class="fas fa-door-open"></i></div>
												<div class="area-details">
													<h4><?= html_escape($b['area_name']) ?></h4>
													<span><?= html_escape($b['purpose'] ?? '—') ?></span>
												</div>
											</div>
										</td>
										<td>
											<strong><?= html_escape($b['user_name']) ?></strong><br>
											<span style="font-size:.75rem;">Flat <?= html_escape($b['flat_no'] ?? '—') ?></span>
										</td>
										<?php if (!empty($isSuperAdmin)): ?>
											<td><?= !empty($b['society_name']) ? '<span class="society-badge"><i class="fas fa-city"></i> ' . html_escape($b['society_name']) . '</span>' : '—' ?>
											</td>
										<?php endif; ?>
										<td><?= !empty($b['booking_date']) ? date('d M Y', strtotime($b['booking_date'])) : '—' ?>
										</td>
										<td><?= !empty($b['start_time']) ? date('h:i A', strtotime($b['start_time'])) : '—' ?><?= !empty($b['end_time']) ? ' – ' . date('h:i A', strtotime($b['end_time'])) : '' ?>
										</td>
										<td><?= $b['amount'] > 0 ? '₹' . number_format($b['amount'], 0) : '<span style="color:var(--text-light);">—</span>' ?>
										</td>
										<td><span
												class="pay-badge <?= html_escape($b['payment_status']) ?>"><?= ucfirst(html_escape($b['payment_status'])) ?></span>
										</td>
										<td><span
												class="status-badge <?= html_escape($b['status']) ?>"><?= ucfirst(html_escape($b['status'])) ?></span>
										</td>
										<td>
											<div class="action-buttons">
												<!-- View -->
												<button class="btn-icon"
													onclick='viewBooking(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)'>
													<i class="fas fa-eye"></i>
												</button>
												<!-- Edit -->
												<button class="btn-icon"
													onclick='editBooking(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)'>
													<i class="fas fa-edit"></i>
												</button>
												<!-- Approve / Reject — chairman + super admin -->
												<?php if ($_canApprove && $b['status'] === 'pending'): ?>
													<form method="POST"
														action="<?= base_url('events_booking_controller/approve_booking/' . (int) $b['id']) ?>"
														style="display:inline;">
														<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
															value="<?= $this->security->get_csrf_hash() ?>">
														<input type="hidden" name="status" value="approved">
														<button type="submit" class="btn-icon" style="color:#389e0d;"
															onclick="return confirm('Approve?')"><i class="fas fa-check"></i></button>
													</form>
													<form method="POST"
														action="<?= base_url('events_booking_controller/approve_booking/' . (int) $b['id']) ?>"
														style="display:inline;">
														<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
															value="<?= $this->security->get_csrf_hash() ?>">
														<input type="hidden" name="status" value="rejected">
														<button type="submit" class="btn-icon delete"
															onclick="return confirm('Reject?')"><i class="fas fa-times"></i></button>
													</form>
												<?php endif; ?>
												<!-- Pay button (only self payment) -->
												<?php
												$canPay = $b['status'] === 'approved'
													&& $b['payment_status'] === 'pending'
													&& (float) $b['amount'] > 0
													&& (int) $b['user_id'] === (int) ($logged_user_id ?? 0);
												if ($canPay): ?>
													<button type="button" class="btn-pay"
														onclick="startRazorpay('booking',<?= (int) $b['id'] ?>,<?= (float) $b['amount'] ?>,'<?= html_escape(addslashes($b['area_name'])) ?>')">
														<i class="fas fa-coins"></i> Pay ₹<?= number_format((float) $b['amount'], 0) ?>
													</button>
												<?php endif; ?>
												<!-- Delete -->
												<a href="<?= base_url('events_booking_controller/delete_booking/' . (int) $b['id']) ?>"
													class="btn-icon delete" onclick="return confirm('Delete?')">
													<i class="fas fa-trash"></i>
												</a>
											</div>
										</td>
									</tr>
								<?php endforeach; else: ?>
								<tr>
									<td colspan="<?= !empty($isSuperAdmin) ? 9 : 8 ?>" style="text-align:center;padding:40px;">
										<i class="fas fa-calendar-xmark"
											style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>
										No bookings found.<br>
										<button type="button" class="btn btn-primary" style="margin-top:15px;"
											onclick="openAddBookingModal()">
											<i class="fas fa-plus"></i> New Booking
										</button>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<!-- ── Bookings Pagination ── -->
				<?php
				$bkgParams = array_filter([
					'status' => $filters['status'] ?? '',
					'payment_status' => $filters['payment_status'] ?? '',
					'search' => $filters['search'] ?? '',
					'society_id' => $filters['society_id'] ?? '',
				]);
				echo renderPagination($bookings_page, $bookings_total_pages, 'events_booking_controller/bookings', $bkgParams);
				if ($bookings_total_pages > 1):
					?>
					<div class="pagination-info">
						Page <?= $bookings_page ?> of <?= $bookings_total_pages ?>
						&nbsp;·&nbsp; <?= (int) ($booking_stats['total'] ?? 0) ?> total bookings
						&nbsp;·&nbsp; <?= $per_page ?> per page
					</div>
				<?php endif; ?>
			</div><!-- /.table-section -->

			<?php if (!empty($recent_bookings)): ?>
				<div class="management-card" style="margin-top:20px;">
					<div class="section-header">
						<h3><i class="fas fa-clock"></i> Recent Bookings</h3>
					</div>
					<div class="member-list">
						<?php foreach ($recent_bookings as $r): ?>
							<div class="member-item">
								<div class="member-info">
									<div class="member-avatar"><i class="fas fa-door-open" style="color:var(--primary);"></i></div>
									<div class="member-details">
										<h4><?= html_escape($r['area_name']) ?></h4>
										<span><?= html_escape($r['user_name']) ?> · Flat
											<?= html_escape($r['flat_no'] ?? '—') ?>			<?= (!empty($isSuperAdmin) && !empty($r['society_name'])) ? ' · <em>' . html_escape($r['society_name']) . '</em>' : '' ?></span>
									</div>
								</div>
								<span
									class="status-badge <?= html_escape($r['status']) ?>"><?= ucfirst(html_escape($r['status'])) ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

		<?php endif; // $tab === 'events' ?>
	</div><!-- /.main -->

	<!-- ══════════════════════════════════════════════════
	 MODALS
══════════════════════════════════════════════════ -->

	<!-- ════ Event Create / Edit ════ -->
	<div class="modal" id="eventFormModal">
		<div class="modal-content" style="max-width:680px;">
			<div class="modal-header">
				<h3><i class="fas fa-calendar-alt"></i> <span id="evtModalTitle">Create Event</span></h3>
				<span class="modal-close" onclick="closeModal('eventFormModal')">&times;</span>
			</div>
			<div class="modal-body">
				<form id="eventForm" method="POST" action="<?= base_url('events_booking_controller/save_event') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
						value="<?= $this->security->get_csrf_hash() ?>">
					<input type="hidden" name="event_id" id="formEventId" value="">

					<?php if (!empty($isSuperAdmin)): ?>
						<div class="form-group"><label>Society *</label>
							<select name="society_id" id="formEvtSociety" class="form-control">
								<option value="">— Select Society —</option>
								<?php foreach ($societies ?? [] as $soc): ?>
									<option value="<?= (int) $soc['id'] ?>"><?= html_escape($soc['name']) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php else: ?>
						<input type="hidden" name="society_id" value="<?= (int) $this->session->userdata('society_id') ?>">
					<?php endif; ?>

					<div class="form-group"><label>Event Title *</label><input type="text" name="title"
							id="formEvtTitle" class="form-control" required></div>
					<div class="form-group"><label>Description</label><textarea name="description" id="formEvtDesc"
							class="form-control" rows="3"></textarea></div>

					<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
						<div class="form-group"><label>Event Type *</label>
							<select name="event_type" id="formEvtType" class="form-control" required>
								<option value="">— Select —</option>
								<?php foreach (['festival', 'celebration', 'cultural', 'sports', 'meeting'] as $t): ?>
									<option value="<?= $t ?>"><?= ucfirst($t) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group"><label>Status *</label>
							<select name="status" id="formEvtStatus" class="form-control" required>
								<?php foreach (['upcoming', 'ongoing', 'completed', 'cancelled'] as $s): ?>
									<option value="<?= $s ?>"><?= ucfirst($s) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
						<div class="form-group"><label>Event Date *</label><input type="date" name="event_date"
								id="formEvtDate" class="form-control" required></div>
						<div class="form-group"><label>Start Time</label><input type="time" name="start_time"
								id="formEvtStart" class="form-control"></div>
						<div class="form-group"><label>End Time</label><input type="time" name="end_time"
								id="formEvtEnd" class="form-control"></div>
					</div>
					<div class="form-group"><label>Venue</label><input type="text" name="venue" id="formEvtVenue"
							class="form-control"></div>

					<div
						style="background:#fffbe6;border:1px solid #ffe58f;border-radius:10px;padding:14px;margin-bottom:14px;">
						<div style="display:flex;align-items:center;gap:10px;">
							<input type="checkbox" name="fund_required" id="fundRequired" value="1"
								onchange="toggleFund(this.checked)">
							<label for="fundRequired" style="font-weight:600;color:#d48806;"><i
									class="fas fa-hand-holding-dollar"></i> This event requires fund
								contribution</label>
						</div>
						<div id="fundFields" style="display:none;">
							<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
								<div class="form-group"><label>Target Amount (₹)</label><input type="number"
										name="fund_amount" id="formFundAmt" class="form-control" step="0.01"></div>
								<div class="form-group"><label>Fund Status</label>
									<select name="fund_status" id="formFundStatus" class="form-control">
										<option value="open">Open</option>
										<option value="closed">Closed</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-outline"
							onclick="closeModal('eventFormModal')">Cancel</button>
						<button type="submit" class="btn btn-primary">Save Event</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- ════ Chairman / Super Admin Event QR ════ -->
	<div class="modal" id="qrModal">
		<div class="modal-content" style="max-width:430px;">
			<div class="modal-header">
				<h3><i class="fas fa-qrcode"></i> Event QR Code</h3>
				<span class="modal-close" onclick="closeModal('qrModal')">&times;</span>
			</div>
			<div class="modal-body">
				<p><strong id="qrEventTitle"></strong></p>
				<div id="qrAlreadyScannedBanner"
					style="display:none;background:#f6ffed;border:1px solid #b7eb8f;border-radius:8px;padding:8px 12px;margin-bottom:10px;font-size:.82rem;color:#389e0d;">
					<i class="fas fa-check-circle"></i> This QR was previously scanned. A <strong>new QR</strong> has
					been generated automatically.
				</div>
				<div class="qr-box" id="qrBox"></div>
				<div class="qr-link-box"><strong>Scan link:</strong><br><span id="qrLinkText"></span></div>
				<p style="font-size:.8rem;margin-top:8px;"><i class="fas fa-info-circle"></i> Each scan rotates to a
					fresh one-time code automatically.</p>
			</div>
			<div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('qrModal')">Close</button>
			</div>
		</div>
	</div>

	<!-- ════ Resident Contribute ════ -->
	<div class="modal" id="contributeModal">
		<div class="modal-content" style="max-width:420px;">
			<div class="modal-header">
				<h3><i class="fas fa-hand-holding-dollar"></i> Pay Event Share</h3>
				<span class="modal-close" onclick="closeModal('contributeModal')">&times;</span>
			</div>
			<div class="modal-body">
				<p><strong id="ctbEventTitle"></strong></p>
				<div class="form-group">
					<label>Your share (₹) *</label>
					<input type="number" id="ctbAmount" class="form-control" readonly>
					<small id="ctbHint" style="color:var(--text-light);"></small>
				</div>
				<div id="ctbError" style="color:red;display:none;"></div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-outline" onclick="closeModal('contributeModal')">Cancel</button>
				<button class="btn btn-primary" id="ctbPayBtn" onclick="startRazorpay('contribution')">
					<i class="fas fa-hand-holding-dollar"></i> Pay Now
				</button>
			</div>
		</div>
	</div>

	<!-- ════ Resident Ticket QR ════ -->
	<div class="modal" id="ticketModal">
		<div class="modal-content" style="max-width:400px;">
			<div class="modal-header">
				<h3><i class="fas fa-ticket-alt"></i> Event Entry Ticket</h3>
				<span class="modal-close" onclick="closeModal('ticketModal')">&times;</span>
			</div>
			<div class="modal-body" style="text-align:center;">
				<p><strong id="ticketEventTitle"></strong></p>
				<div id="ticketScannedBanner"
					style="display:none;background:#f6ffed;border:1px solid #b7eb8f;border-radius:10px;padding:10px;margin-bottom:10px;">
					<i class="fas fa-check-circle" style="color:#52c41a;font-size:1.4rem;"></i>
					<div style="font-weight:700;color:#389e0d;margin-top:4px;">Entry Verified!</div>
					<div style="font-size:.8rem;color:#52c41a;">Your ticket has been scanned by the chairman.</div>
				</div>
				<div id="ticketQrBox" style="display:flex;justify-content:center;margin:15px 0;"></div>
				<p style="font-size:0.8rem;">Show this QR to the chairman for entry.<br><i
						class="fas fa-info-circle"></i> One-time scan only.</p>
			</div>
			<div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('ticketModal')">Close</button>
			</div>
		</div>
	</div>

	<!-- ════ Chairman / Super Admin: Scanned Ticket List ════ -->
	<div class="modal" id="scannedListModal">
		<div class="modal-content" style="max-width:480px;">
			<div class="modal-header">
				<h3><i class="fas fa-list-check"></i> Ticket Scan Log — <span id="slEventTitle"></span></h3>
				<span class="modal-close" onclick="closeModal('scannedListModal')">&times;</span>
			</div>
			<div class="modal-body">
				<div id="slLoading" style="text-align:center;padding:30px;color:var(--text-light);"><i
						class="fas fa-spinner fa-spin"></i> Loading…</div>
				<div id="slContent" style="display:none;"></div>
			</div>
			<div class="modal-footer"><button class="btn btn-outline"
					onclick="closeModal('scannedListModal')">Close</button></div>
		</div>
	</div>

	<!-- ════ Camera QR Scanner (Chairman + Super Admin) ════ -->
	<div class="modal" id="qrScannerModal">
		<div class="modal-content" style="max-width:500px;">
			<div class="modal-header">
				<h3><i class="fas fa-camera"></i> Scan QR Code</h3>
				<span class="modal-close" onclick="closeQrScanner()">&times;</span>
			</div>
			<div class="modal-body">
				<div id="qr-reader" style="width:100%"></div>
				<div id="scanResultMessage" style="margin-top:10px;font-size:.85rem;"></div>
			</div>
			<div class="modal-footer"><button class="btn btn-outline" onclick="closeQrScanner()">Cancel</button></div>
		</div>
	</div>

	<!-- ════ Booking Form ════ -->
	<div class="modal" id="bookingFormModal">
		<div class="modal-content" style="max-width:660px;">
			<div class="modal-header">
				<h3><i class="fas fa-calendar-plus"></i> <span id="bkgModalTitle">New Booking</span></h3>
				<span class="modal-close" onclick="closeModal('bookingFormModal')">&times;</span>
			</div>
			<div class="modal-body">
				<form id="bookingForm" method="POST" action="<?= base_url('events_booking_controller/save_booking') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
						value="<?= $this->security->get_csrf_hash() ?>">
					<input type="hidden" name="booking_id" id="formBkgId" value="">

					<?php if (!empty($isSuperAdmin)): ?>
						<div class="form-group"><label>Society *</label>
							<select name="society_id" id="formBkgSociety" class="form-control">
								<option value="">— Select Society —</option>
								<?php foreach ($societies ?? [] as $soc): ?>
									<option value="<?= (int) $soc['id'] ?>"><?= html_escape($soc['name']) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php else: ?>
						<input type="hidden" name="society_id" value="<?= (int) $this->session->userdata('society_id') ?>">
					<?php endif; ?>

					<?php if (!empty($isResident) && !empty($logged_user)): ?>
						<input type="hidden" name="user_name" value="<?= html_escape($logged_user['name'] ?? '') ?>">
						<input type="hidden" name="flat_no" value="<?= html_escape($logged_user['flat_no'] ?? '') ?>">
						<div
							style="background:#f5f5f5;border-radius:10px;padding:12px;margin-bottom:14px;display:flex;gap:20px;">
							<div>
								<div style="font-size:.74rem;">Resident</div>
								<strong><?= html_escape($logged_user['name'] ?? '') ?></strong>
							</div>
							<div>
								<div style="font-size:.74rem;">Flat</div>
								<strong><?= html_escape($logged_user['flat_no'] ?? '—') ?></strong>
							</div>
						</div>
					<?php else: ?>
						<?php if ($_canManage): ?>
							<div class="role-info-badge">
								<i class="fas fa-shield-halved"></i>
								<span>Booking as
									<strong><?= ucfirst(str_replace('_', ' ', $this->session->userdata('role_name'))) ?></strong>
									— fill resident details below.</span>
							</div>
						<?php endif; ?>
						<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
							<div class="form-group"><label>Resident Name *</label><input type="text" name="user_name"
									id="formBkgName" class="form-control" required></div>
							<div class="form-group"><label>Flat No</label><input type="text" name="flat_no" id="formBkgFlat"
									class="form-control"></div>
						</div>
					<?php endif; ?>

					<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
						<div class="form-group"><label>Area / Facility Name *</label><input type="text" name="area_name"
								id="formBkgArea" class="form-control" required></div>
						<div class="form-group"><label>Purpose</label><input type="text" name="purpose"
								id="formBkgPurpose" class="form-control"></div>
					</div>
					<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
						<div class="form-group"><label>Booking Date *</label><input type="date" name="booking_date"
								id="formBkgDate" class="form-control" required></div>
						<div class="form-group"><label>Start Time *</label><input type="time" name="start_time"
								id="formBkgStart" class="form-control" required></div>
						<div class="form-group"><label>End Time *</label><input type="time" name="end_time"
								id="formBkgEnd" class="form-control" required></div>
					</div>

					<div
						style="display:grid;grid-template-columns:<?= $_canApprove ? '1fr 1fr 1fr' : '1fr 1fr' ?>;gap:16px;">
						<div class="form-group"><label>Amount (₹)</label><input type="number" name="amount"
								id="formBkgAmt" class="form-control" step="0.01"></div>
						<div class="form-group"><label>Payment Status</label>
							<select name="payment_status" id="formBkgPayment" class="form-control">
								<option value="pending">Pending</option>
								<option value="paid">Paid</option>
								<option value="waived">Waived</option>
							</select>
						</div>
						<?php if ($_canApprove): ?>
							<div class="form-group"><label>Booking Status</label>
								<select name="status" id="formBkgStatus" class="form-control">
									<option value="pending">Pending</option>
									<option value="approved">Approved</option>
									<option value="rejected">Rejected</option>
								</select>
							</div>
						<?php endif; ?>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-outline"
							onclick="closeModal('bookingFormModal')">Cancel</button>
						<button type="submit" class="btn btn-primary">Submit Booking</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- ════ View Booking ════ -->
	<div class="modal" id="viewBookingModal">
		<div class="modal-content" style="max-width:520px;">
			<div class="modal-header">
				<h3><i class="fas fa-calendar-days"></i> Booking Details</h3>
				<span class="modal-close" onclick="closeModal('viewBookingModal')">&times;</span>
			</div>
			<div class="modal-body">
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 24px;">
					<p><strong>Area:</strong><br><span id="vbArea"></span></p>
					<p><strong>Purpose:</strong><br><span id="vbPurpose"></span></p>
					<p><strong>Resident:</strong><br><span id="vbName"></span></p>
					<p><strong>Flat:</strong><br><span id="vbFlat"></span></p>
					<?php if (!empty($isSuperAdmin)): ?>
						<p style="grid-column:1/-1"><strong>Society:</strong><br><span id="vbSociety"></span></p>
					<?php endif; ?>
					<p><strong>Date:</strong><br><span id="vbDate"></span></p>
					<p><strong>Time Slot:</strong><br><span id="vbTime"></span></p>
					<p><strong>Amount:</strong><br><span id="vbAmt"></span></p>
					<p><strong>Payment:</strong><br><span id="vbPayment"></span></p>
					<p><strong>Status:</strong><br><span id="vbStatus"></span></p>
					<p><strong>Approved By:</strong><br><span id="vbApprover"></span></p>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-outline" onclick="closeModal('viewBookingModal')">Close</button>
				<button class="btn btn-primary" onclick="editFromView()">Edit</button>
			</div>
		</div>
	</div>

	<!-- Razorpay hidden verify form -->
	<form id="rzpVerifyForm" method="POST" action="<?= base_url('events_booking_controller/razorpay_verify') ?>"
		style="display:none;">
		<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
			value="<?= $this->security->get_csrf_hash() ?>">
		<input type="hidden" name="razorpay_order_id" id="rzpOrderId">
		<input type="hidden" name="razorpay_payment_id" id="rzpPaymentId">
		<input type="hidden" name="razorpay_signature" id="rzpSignature">
		<input type="hidden" name="type" id="rzpType">
		<input type="hidden" name="ref_id" id="rzpRefId">
		<input type="hidden" name="amount" id="rzpAmount">
	</form>

	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		/* ═══════════════════════════════════════════════════
		 *  MODAL HELPERS
		 * ═══════════════════════════════════════════════════ */
		function openModal(id) { document.getElementById(id).classList.add('active'); document.getElementById('overlay').classList.add('active'); }
		function closeModal(id) { document.getElementById(id).classList.remove('active'); document.getElementById('overlay').classList.remove('active'); }

		document.getElementById('overlay').addEventListener('click', function () {
			document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
			this.classList.remove('active');
		});
		window.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') return;
			document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
			document.getElementById('overlay').classList.remove('active');
			stopQrScanner();
		});

		/* ═══════════════════════════════════════════════════
		 *  FLASH AUTO-DISMISS
		 * ═══════════════════════════════════════════════════ */
		document.addEventListener('DOMContentLoaded', function () {
			var f = document.getElementById('flashMsg');
			if (f) setTimeout(function () { f.style.transition = 'opacity .5s'; f.style.opacity = '0'; setTimeout(function () { f.remove(); }, 500); }, 3500);
		});

		/* ═══════════════════════════════════════════════════
		 *  LIVE SEARCH (debounced)
		 * ═══════════════════════════════════════════════════ */
		(function () {
			var inp = document.getElementById('searchInput'); if (!inp) return;
			var t;
			inp.addEventListener('input', function () {
				clearTimeout(t);
				t = setTimeout(function () {
					var form = document.getElementById('filterForm');
					var pg = form.querySelector('input[name="page"]');
					if (pg) pg.value = 1;
					form.submit();
				}, 500);
			});
		})();

		/* ═══════════════════════════════════════════════════
		 *  CHAIRMAN + SUPER ADMIN EVENT QR
		 * ═══════════════════════════════════════════════════ */
		function openQrModal(id, title, qrUrl, alreadyScanned) {
			document.getElementById('qrEventTitle').innerText = title;
			document.getElementById('qrLinkText').innerText = qrUrl;
			document.getElementById('qrAlreadyScannedBanner').style.display = alreadyScanned ? 'block' : 'none';
			document.getElementById('qrBox').innerHTML = '';
			new QRCode(document.getElementById('qrBox'), {
				text: qrUrl, width: 220, height: 220,
				colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H
			});
			openModal('qrModal');
		}

		/* ═══════════════════════════════════════════════════
		 *  RESIDENT TICKET QR
		 * ═══════════════════════════════════════════════════ */
		function showTicketQr(token, eventTitle, isScanned) {
			document.getElementById('ticketEventTitle').innerText = eventTitle;
			document.getElementById('ticketScannedBanner').style.display = isScanned ? 'block' : 'none';
			var qrBox = document.getElementById('ticketQrBox'); qrBox.innerHTML = '';
			var ticketUrl = '<?= site_url('events_booking_controller/scan_ticket/') ?>' + token;
			new QRCode(qrBox, { text: ticketUrl, width: 200, height: 200, colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H });
			openModal('ticketModal');
		}

		/* ═══════════════════════════════════════════════════
		 *  CHAIRMAN + SUPER ADMIN: SCANNED TICKET LIST
		 * ═══════════════════════════════════════════════════ */
		function openScannedListModal(eventId, eventTitle) {
			document.getElementById('slEventTitle').innerText = eventTitle;
			document.getElementById('slLoading').style.display = 'block';
			document.getElementById('slContent').style.display = 'none';
			openModal('scannedListModal');

			var xhr = new XMLHttpRequest();
			xhr.open('GET', '<?= base_url('events_booking_controller/get_scanned_tickets/') ?>' + eventId);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.onload = function () {
				document.getElementById('slLoading').style.display = 'none';
				var res = JSON.parse(xhr.responseText);
				var html = '';
				if (!res.success || !res.tickets || !res.tickets.length) {
					html = '<p style="text-align:center;color:var(--text-light);padding:20px;"><i class="fas fa-ticket-alt" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>No tickets scanned yet.</p>';
				} else {
					html += '<p style="font-size:.8rem;color:var(--text-light);margin-bottom:10px;">' + res.tickets.length + ' ticket(s) scanned</p>';
					res.tickets.forEach(function (t) {
						var initials = (t.user_name || 'U').split(' ').map(function (w) { return w[0]; }).join('').substring(0, 2).toUpperCase();
						html += '<div class="scan-list-item">'
							+ '<div class="scan-avatar">' + initials + '</div>'
							+ '<div style="flex:1;"><div style="font-weight:700;font-size:.88rem;">' + (t.user_name || '—') + '</div>'
							+ '<div style="font-size:.75rem;color:var(--text-light);">Flat ' + (t.flat_no || '—') + '</div></div>'
							+ (t.ticket_scanned_at
								? '<span style="font-size:.7rem;color:#389e0d;background:#f6ffed;border:1px solid #b7eb8f;border-radius:8px;padding:2px 8px;white-space:nowrap;"><i class="fas fa-check-circle"></i> ' + fmtDateTime(t.ticket_scanned_at) + '</span>'
								: '<span style="font-size:.7rem;color:#faad14;background:#fff7e6;border:1px solid #ffe58f;border-radius:8px;padding:2px 8px;">Paid, not scanned</span>'
							) + '</div>';
					});
				}
				document.getElementById('slContent').innerHTML = html;
				document.getElementById('slContent').style.display = 'block';
			};
			xhr.onerror = function () {
				document.getElementById('slLoading').style.display = 'none';
				document.getElementById('slContent').innerHTML = '<p style="color:red;">Failed to load.</p>';
				document.getElementById('slContent').style.display = 'block';
			};
			xhr.send();
		}

		/* ═══════════════════════════════════════════════════
		 *  RAZORPAY
		 * ═══════════════════════════════════════════════════ */
		var _rzpType = '', _rzpRefId = 0, _rzpAmount = 0, _rzpLabel = '';
		var _ctbEventId = 0, _ctbFixedAmount = 0;

		function openContributeModal(id, title, fixedAmount, targetAmount) {
			_ctbEventId = id; _ctbFixedAmount = fixedAmount;
			document.getElementById('ctbEventTitle').innerText = title;
			document.getElementById('ctbAmount').value = fixedAmount.toFixed(2);
			document.getElementById('ctbHint').innerHTML = 'Equal share of total ₹' + targetAmount.toLocaleString('en-IN');
			document.getElementById('ctbError').style.display = 'none';
			openModal('contributeModal');
		}

		function startRazorpay(type, refId, amount, label) {
			if (type === 'contribution') {
				refId = _ctbEventId; amount = _ctbFixedAmount;
				if (!amount || amount <= 0) { document.getElementById('ctbError').innerText = 'Invalid amount.'; document.getElementById('ctbError').style.display = 'block'; return; }
				label = document.getElementById('ctbEventTitle').innerText;
				document.getElementById('ctbError').style.display = 'none';
			}
			_rzpType = type; _rzpRefId = refId; _rzpAmount = amount; _rzpLabel = label;
			var btn = type === 'contribution' ? document.getElementById('ctbPayBtn') : null;
			if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…'; }

			var xhr = new XMLHttpRequest();
			xhr.open('POST', '<?= base_url('events_booking_controller/razorpay_create_order') ?>');
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.onload = function () {
				if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-hand-holding-dollar"></i> Pay Now'; }
				var res = JSON.parse(xhr.responseText);
				if (!res.success) { showAlert(res.message || 'Failed.'); return; }
				_openRazorpayCheckout(res.order_id, res.key_id, res.amount);
			};
			xhr.onerror = function () { if (btn) btn.disabled = false; showAlert('Network error.'); };
			xhr.send('<?= $this->security->get_csrf_token_name() ?>=<?= $this->security->get_csrf_hash() ?>'
				+ '&amount=' + encodeURIComponent(amount) + '&type=' + encodeURIComponent(type) + '&ref_id=' + encodeURIComponent(refId));
		}

		function _openRazorpayCheckout(order_id, key_id, amount_paise) {
			var options = {
				key: key_id, amount: amount_paise, currency: 'INR', name: 'SocietyHub',
				description: _rzpType === 'contribution' ? 'Fund Contribution — ' + _rzpLabel : 'Area Booking — ' + _rzpLabel,
				order_id: order_id,
				prefill: { name: '<?= html_escape($this->session->userdata('name') ?? '') ?>', email: '<?= html_escape($this->session->userdata('email') ?? '') ?>', contact: '<?= html_escape($this->session->userdata('phone') ?? '') ?>' },
				theme: { color: '#6366f1' },
				modal: { ondismiss: function () { showToast('error', 'Payment cancelled.'); } },
				handler: function (response) {
					document.getElementById('rzpOrderId').value = response.razorpay_order_id;
					document.getElementById('rzpPaymentId').value = response.razorpay_payment_id;
					document.getElementById('rzpSignature').value = response.razorpay_signature;
					document.getElementById('rzpType').value = _rzpType;
					document.getElementById('rzpRefId').value = _rzpRefId;
					document.getElementById('rzpAmount').value = _rzpAmount;
					document.getElementById('rzpVerifyForm').submit();
				}
			};
			var rzp = new Razorpay(options);
			rzp.on('payment.failed', function (resp) { showToast('error', 'Payment failed: ' + (resp.error.description || 'Unknown')); });
			rzp.open();
			if (_rzpType === 'contribution') closeModal('contributeModal');
		}

		/* ═══════════════════════════════════════════════════
		 *  EVENT FORM
		 * ═══════════════════════════════════════════════════ */
		function openAddEventModal() {
			document.getElementById('evtModalTitle').innerText = 'Create Event';
			document.getElementById('eventForm').reset();
			document.getElementById('formEventId').value = '';
			document.getElementById('fundFields').style.display = 'none';
			openModal('eventFormModal');
		}
		function toggleFund(show) { document.getElementById('fundFields').style.display = show ? 'block' : 'none'; }
		function editEvent(e) {
			document.getElementById('evtModalTitle').innerText = 'Edit Event';
			document.getElementById('formEventId').value = e.id;
			document.getElementById('formEvtTitle').value = e.title || '';
			document.getElementById('formEvtDesc').value = e.description || '';
			document.getElementById('formEvtType').value = e.event_type || '';
			document.getElementById('formEvtStatus').value = e.status || 'upcoming';
			document.getElementById('formEvtDate').value = (e.event_date || '').split(' ')[0];
			document.getElementById('formEvtStart').value = (e.start_time || '').substring(0, 5);
			document.getElementById('formEvtEnd').value = (e.end_time || '').substring(0, 5);
			document.getElementById('formEvtVenue').value = e.venue || '';
			var sc = document.getElementById('formEvtSociety'); if (sc) sc.value = e.society_id || '';
			var hasFund = parseInt(e.fund_required) === 1;
			document.getElementById('fundRequired').checked = hasFund; toggleFund(hasFund);
			if (hasFund) { document.getElementById('formFundAmt').value = e.fund_amount || ''; document.getElementById('formFundStatus').value = e.fund_status || 'open'; }
			openModal('eventFormModal');
		}

		/* ═══════════════════════════════════════════════════
		 *  BOOKING FORM
		 * ═══════════════════════════════════════════════════ */
		function openAddBookingModal() {
			document.getElementById('bkgModalTitle').innerText = 'New Booking Request';
			document.getElementById('bookingForm').reset();
			document.getElementById('formBkgId').value = '';
			var d = document.getElementById('formBkgDate'); if (d) d.value = new Date().toISOString().split('T')[0];
			var sc = document.getElementById('formBkgSociety'); if (sc) sc.value = '';
			openModal('bookingFormModal');
		}
		var _viewingBooking = null;
		function viewBooking(b) {
			_viewingBooking = b;
			document.getElementById('vbArea').textContent = b.area_name || '—';
			document.getElementById('vbPurpose').textContent = b.purpose || '—';
			document.getElementById('vbName').textContent = b.user_name || '—';
			document.getElementById('vbFlat').textContent = b.flat_no || '—';
			document.getElementById('vbDate').textContent = b.booking_date ? fmtDate(b.booking_date) : '—';
			document.getElementById('vbTime').textContent = fmtTime(b.start_time) + (b.end_time ? ' – ' + fmtTime(b.end_time) : '');
			document.getElementById('vbAmt').textContent = b.amount > 0 ? '₹' + Number(b.amount).toLocaleString('en-IN') : '—';
			document.getElementById('vbPayment').textContent = b.payment_status ? b.payment_status.charAt(0).toUpperCase() + b.payment_status.slice(1) : '—';
			document.getElementById('vbStatus').textContent = b.status ? b.status.charAt(0).toUpperCase() + b.status.slice(1) : '—';
			document.getElementById('vbApprover').textContent = b.approver_name || '—';
			var vs = document.getElementById('vbSociety'); if (vs) vs.textContent = b.society_name || '—';
			openModal('viewBookingModal');
		}
		function editFromView() { closeModal('viewBookingModal'); if (_viewingBooking) editBooking(_viewingBooking); }
		function editBooking(b) {
			document.getElementById('bkgModalTitle').innerText = 'Edit Booking';
			document.getElementById('formBkgId').value = b.id;
			var fn = document.getElementById('formBkgName'); if (fn) fn.value = b.user_name || '';
			var ff = document.getElementById('formBkgFlat'); if (ff) ff.value = b.flat_no || '';
			var fa = document.getElementById('formBkgArea'); if (fa) fa.value = b.area_name || '';
			var fp = document.getElementById('formBkgPurpose'); if (fp) fp.value = b.purpose || '';
			var fd = document.getElementById('formBkgDate'); if (fd) fd.value = (b.booking_date || '').split(' ')[0];
			var fs = document.getElementById('formBkgStart'); if (fs) fs.value = (b.start_time || '').substring(0, 5);
			var fe = document.getElementById('formBkgEnd'); if (fe) fe.value = (b.end_time || '').substring(0, 5);
			var fm = document.getElementById('formBkgAmt'); if (fm) fm.value = b.amount || '';
			var fpy = document.getElementById('formBkgPayment'); if (fpy) fpy.value = b.payment_status || 'pending';
			var fst = document.getElementById('formBkgStatus'); if (fst) fst.value = b.status || 'pending';
			var sc = document.getElementById('formBkgSociety'); if (sc) sc.value = b.society_id || '';
			openModal('bookingFormModal');
		}

		/* ═══════════════════════════════════════════════════
		 *  CAMERA QR SCANNER (Chairman + Super Admin)
		 * ═══════════════════════════════════════════════════ */
		let html5QrCode = null;
		function openQrScanner() {
			openModal('qrScannerModal');
			document.getElementById('scanResultMessage').innerHTML = '';
			html5QrCode = new Html5Qrcode("qr-reader");
			html5QrCode.start(
				{ facingMode: "environment" },
				{ fps: 10, qrbox: { width: 250, height: 250 } },
				onScanSuccess, onScanFailure
			).catch(err => {
				document.getElementById('scanResultMessage').innerHTML = `<span style="color:red;">Camera error: ${err}</span>`;
			});
		}
		function onScanSuccess(decodedText) {
			stopQrScanner();
			const baseUrl = '<?= base_url() ?>';
			if (decodedText.startsWith(baseUrl) && (decodedText.includes('/scan_event_qr/') || decodedText.includes('/scan_ticket/'))) {
				closeModal('qrScannerModal');
				window.location.href = decodedText;
			} else {
				document.getElementById('scanResultMessage').innerHTML = '<span style="color:#cf1322;"><i class="fas fa-exclamation-triangle"></i> Invalid QR code. Please scan a valid SocietyHub QR.</span>';
				setTimeout(() => { if (document.getElementById('qrScannerModal').classList.contains('active')) openQrScanner(); }, 2000);
			}
		}
		function onScanFailure() { /* ignore */ }
		function stopQrScanner() {
			if (html5QrCode && html5QrCode.isScanning) {
				html5QrCode.stop().then(() => html5QrCode.clear()).catch(err => console.warn("Stop error:", err));
			}
		}
		function closeQrScanner() { stopQrScanner(); closeModal('qrScannerModal'); }

		/* ═══════════════════════════════════════════════════
		 *  UTILITIES
		 * ═══════════════════════════════════════════════════ */
		function fmtDate(d) {
			if (!d) return '';
			var p = d.split(' ')[0].split('-');
			return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : d;
		}
		function fmtTime(t) {
			if (!t) return '—';
			var parts = t.split(':'); if (parts.length < 2) return t;
			var h = parseInt(parts[0], 10), m = parts[1], ap = h >= 12 ? 'PM' : 'AM';
			h = h % 12 || 12; return h + ':' + m + ' ' + ap;
		}
		function fmtDateTime(dt) {
			if (!dt) return '';
			var parts = dt.split(' ');
			return (parts[0] ? fmtDate(parts[0]) : '') + (parts[1] ? ' ' + fmtTime(parts[1]) : '');
		}
		function showAlert(msg) { alert(msg); }
		function showToast(type, msg) {
			var n = document.createElement('div');
			n.className = 'notification ' + (type === 'success' ? 'success' : 'error');
			n.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + msg;
			document.body.appendChild(n);
			setTimeout(function () { n.style.animation = 'slideOut .3s ease'; setTimeout(function () { n.remove(); }, 300); }, 3000);
		}
	</script>
</body>

</html>
