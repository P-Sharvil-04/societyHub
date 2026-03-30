<?php defined('BASEPATH') OR exit('No direct script access allowed');
$tab = $tab ?? 'events';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>SocietyHub · Events &amp; Bookings</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<!-- Razorpay checkout script -->
	<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
	<style>
		/* Tab switcher */
		.tab-bar { display:flex; gap:0; background:var(--card-bg); border:1px solid var(--border); border-radius:12px; overflow:hidden; margin-bottom:24px; width:fit-content; }
		.tab-btn { padding:10px 28px; font-size:.9rem; font-weight:600; color:var(--text-light); background:transparent; border:none; cursor:pointer; transition:.15s; display:flex;align-items:center;gap:8px; }
		.tab-btn.active { background:var(--primary); color:#fff; }
		.tab-btn:not(.active):hover { background:var(--bg-light,#f5f5f5); color:var(--primary); }

		/* Event cards */
		.events-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px; margin-bottom:30px; }
		.event-card { background:var(--card-bg); border-radius:16px; border:1px solid var(--border); overflow:hidden; display:flex; flex-direction:column; transition:.2s; }
		.event-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.08); transform:translateY(-2px); }
		.event-card-header { padding:14px 16px 12px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:12px; }
		.event-type-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
		.event-type-icon.festival    { background:#fff7e6; color:#d48806; }
		.event-type-icon.celebration { background:#f0f5ff; color:#2f54eb; }
		.event-type-icon.cultural    { background:#f9f0ff; color:#722ed1; }
		.event-type-icon.sports      { background:#f6ffed; color:#389e0d; }
		.event-type-icon.meeting     { background:#e6fffb; color:#08979c; }
		.event-card-body  { padding:12px 16px; flex:1; }
		.event-card-footer{ padding:10px 16px; border-top:1px solid var(--border); display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
		.event-title { font-size:.95rem; font-weight:700; color:var(--text-dark); margin-bottom:4px; }
		.event-meta  { font-size:.77rem; color:var(--text-light); display:flex; flex-wrap:wrap; gap:8px; margin-top:6px; }
		.event-meta span { display:flex; align-items:center; gap:4px; }
		.evt-badge { padding:2px 9px; border-radius:20px; font-size:.7rem; font-weight:700; text-transform:uppercase; }
		.evt-badge.upcoming  { background:#e6f7ff; color:#0958d9; }
		.evt-badge.ongoing   { background:#f6ffed; color:#389e0d; }
		.evt-badge.completed { background:#f5f5f5; color:#595959; }
		.evt-badge.cancelled { background:#fff1f0; color:#cf1322; }
		.fund-banner { background:linear-gradient(135deg,#fff7e6,#fff1b8); border:1px solid #ffe58f; border-radius:10px; padding:10px 12px; margin-top:10px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
		.fund-progress { height:5px; border-radius:3px; background:#f5f5f5; margin-top:5px; overflow:hidden; }
		.fund-progress-bar { height:100%; border-radius:3px; background:linear-gradient(90deg,#faad14,#fa8c16); }

		/* Booking table */
		.status-badge.pending  { background:#fff7e6; color:#d46b08; }
		.status-badge.approved { background:#f6ffed; color:#389e0d; }
		.status-badge.rejected { background:#fff1f0; color:#cf1322; }
		.pay-badge { padding:3px 9px; border-radius:20px; font-size:.7rem; font-weight:600; text-transform:capitalize; display:inline-block; }
		.pay-badge.pending { background:#fff1f0; color:#cf1322; }
		.pay-badge.paid    { background:#f6ffed; color:#389e0d; }
		.pay-badge.waived  { background:#e6fffb; color:#08979c; }
		.booking-area { display:flex; align-items:center; gap:10px; }
		.area-icon { width:38px; height:38px; border-radius:9px; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:.95rem; flex-shrink:0; }
		.area-details h4 { font-weight:700; font-size:.88rem; color:var(--text-dark); margin-bottom:2px; }
		.area-details span { font-size:.74rem; color:var(--text-light); }

		/* Shared */
		.society-badge { display:inline-flex; align-items:center; gap:4px; background:#eef1fb; color:#3b5bdb; border:1px solid #c5d0f5; border-radius:12px; padding:2px 8px; font-size:.72rem; font-weight:500; }
		.active-filter-pill { display:inline-flex; align-items:center; gap:6px; background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; border-radius:20px; padding:3px 10px 3px 12px; font-size:.78rem; font-weight:500; }
		.active-filter-pill a { color:#6366f1; text-decoration:none; font-weight:700; }
	</style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<?php $activePage = 'events_booking'; include('sidebar.php'); ?>

<div class="main" id="main">

	<?php if ($this->session->flashdata('success')): ?>
		<div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?></div>
	<?php endif; ?>
	<?php if ($this->session->flashdata('error')): ?>
		<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?></div>
	<?php endif; ?>

	<!-- ══ Tab Switcher ══ -->
	<div class="tab-bar">
		<a href="<?= site_url('events_booking_controller/events') ?>" class="tab-btn <?= $tab === 'events' ? 'active' : '' ?>">
			<i class="fas fa-calendar-alt"></i> Events & Festivals
		</a>
		<a href="<?= site_url('events_booking_controller/bookings') ?>" class="tab-btn <?= $tab === 'bookings' ? 'active' : '' ?>">
			<i class="fas fa-calendar-days"></i> Area Bookings
		</a>
	</div>

<?php if ($tab === 'events'): /* ══════════════ EVENTS TAB ══════════════ */ ?>

	<!-- Event Stats -->
	<div class="stats-grid">
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
			<div class="stat-info"><h4>Total Events</h4><h2><?= (int)($event_stats['total']    ?? 0) ?></h2></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
			<div class="stat-info"><h4>Upcoming</h4><h2><?= (int)($event_stats['upcoming']  ?? 0) ?></h2></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-circle-play"></i></div>
			<div class="stat-info"><h4>Ongoing</h4><h2><?= (int)($event_stats['ongoing']   ?? 0) ?></h2></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-hand-holding-dollar"></i></div>
			<div class="stat-info"><h4>Fund Requests</h4><h2><?= (int)($event_stats['fund_open'] ?? 0) ?></h2></div>
		</div>
	</div>

	<!-- Event Filters -->
	<form method="GET" action="<?= site_url('events_booking_controller/events') ?>" id="filterForm">
		<div class="filter-section">
			<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
			<div class="filter-group">
				<label><i class="fas fa-building"></i> Society</label>
				<select name="society_id" class="filter-select" onchange="this.form.submit()">
					<option value="">All Societies</option>
					<?php foreach ($societies as $soc): ?>
						<option value="<?= (int)$soc['id'] ?>" <?= ((int)($filters['society_id'] ?? 0) === (int)$soc['id']) ? 'selected' : '' ?>><?= html_escape($soc['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<div class="filter-group">
				<label><i class="fas fa-tag"></i> Type</label>
				<select name="event_type" class="filter-select" onchange="this.form.submit()">
					<option value="">All Types</option>
					<?php foreach (['festival','celebration','cultural','sports','meeting'] as $t): ?>
						<option value="<?= $t ?>" <?= ($filters['event_type'] === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="filter-group">
				<label><i class="fas fa-circle"></i> Status</label>
				<select name="status" class="filter-select" onchange="this.form.submit()">
					<option value="">All Status</option>
					<?php foreach (['upcoming','ongoing','completed','cancelled'] as $s): ?>
						<option value="<?= $s ?>" <?= ($filters['status'] === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="search-box">
				<i class="fas fa-search"></i>
				<input type="text" name="search" id="searchInput" placeholder="Search events..." value="<?= html_escape($filters['search'] ?? '') ?>" autocomplete="off">
			</div>
			<div style="display:flex;gap:8px;align-items:flex-end;">
				<button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
				<?php
				$anyFilter = !empty($filters['search']) || !empty($filters['event_type']) || !empty($filters['status']) || !empty($filters['society_id']);
				if ($anyFilter): ?>
					<a href="<?= site_url('events_booking_controller/events') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
				<?php endif; ?>
			</div>
		</div>
	</form>

	<!-- Active pills -->
	<?php if ($anyFilter ?? false): ?>
	<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">
		<span style="font-size:.8rem;color:var(--text-light);font-weight:600;">Active filters:</span>
		<?php if (!empty($filters['society_id']) && $isSuperAdmin): ?>
			<?php $sl=''; foreach($societies as $s){if((int)$s['id']===(int)$filters['society_id']){$sl=$s['name'];break;}} ?>
			<span class="active-filter-pill"><i class="fas fa-building"></i> <?= html_escape($sl) ?> <a href="<?= site_url('events_booking_controller/events?'.http_build_query(array_merge($filters,['society_id'=>'']))) ?>">×</a></span>
		<?php endif; ?>
		<?php if (!empty($filters['event_type'])): ?>
			<span class="active-filter-pill"><i class="fas fa-tag"></i> <?= ucfirst(html_escape($filters['event_type'])) ?> <a href="<?= site_url('events_booking_controller/events?'.http_build_query(array_merge($filters,['event_type'=>'']))) ?>">×</a></span>
		<?php endif; ?>
		<?php if (!empty($filters['status'])): ?>
			<span class="active-filter-pill"><i class="fas fa-circle"></i> <?= ucfirst(html_escape($filters['status'])) ?> <a href="<?= site_url('events_booking_controller/events?'.http_build_query(array_merge($filters,['status'=>'']))) ?>">×</a></span>
		<?php endif; ?>
		<?php if (!empty($filters['search'])): ?>
			<span class="active-filter-pill"><i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>" <a href="<?= site_url('events_booking_controller/events?'.http_build_query(array_merge($filters,['search'=>'']))) ?>">×</a></span>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- Toolbar -->
	<div class="table-header" style="margin-bottom:16px;">
		<h3><i class="fas fa-calendar-alt"></i> Events &amp; Festivals <small style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= count($events ?? []) ?> records)</small></h3>
		<a href="<?= site_url('events_booking_controller/events') ?>" class="btn btn-outline" style="display:flex;align-items:center;gap:6px;"><i class="fas fa-sync-alt"></i> Refresh</a>
		<div class="page-actions">
			<?php if (!empty($canManage)): ?>
				<button type="button" class="btn btn-primary" onclick="openAddEventModal()"><i class="fas fa-plus-circle"></i> Create Event</button>
			<?php endif; ?>
		</div>
	</div>

	<!-- Events Grid -->
	<?php if (empty($events)): ?>
		<div style="text-align:center;padding:60px;color:var(--text-light);">
			<i class="fas fa-calendar-xmark" style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>No events found.
			<?php if (!empty($canManage)): ?><br><br><button type="button" class="btn btn-primary" onclick="openAddEventModal()"><i class="fas fa-plus-circle"></i> Create First Event</button><?php endif; ?>
		</div>
	<?php else: ?>
	<div class="events-grid">
		<?php
		$typeIcons = ['festival'=>'fa-star','celebration'=>'fa-champagne-glasses','cultural'=>'fa-masks-theater','sports'=>'fa-trophy','meeting'=>'fa-people-group'];
		foreach ($events as $e):
			$icon = $typeIcons[$e['event_type']] ?? 'fa-calendar-alt';
			$fundPct = (!empty($e['fund_amount']) && $e['fund_amount'] > 0)
				? min(100, round(($e['fund_raised'] ?? 0) / $e['fund_amount'] * 100)) : 0;
		?>
		<div class="event-card">
			<div class="event-card-header">
				<div class="event-type-icon <?= html_escape($e['event_type']) ?>"><i class="fas <?= $icon ?>"></i></div>
				<div style="flex:1;min-width:0;">
					<div class="event-title"><?= html_escape($e['title']) ?></div>
					<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:3px;">
						<span class="evt-badge <?= html_escape($e['status']) ?>"><?= ucfirst($e['status']) ?></span>
						<?php if (!empty($isSuperAdmin) && !empty($e['society_name'])): ?>
							<span class="society-badge"><i class="fas fa-city"></i> <?= html_escape($e['society_name']) ?></span>
						<?php endif; ?>
					</div>
				</div>
				<?php if (!empty($canManage)): ?>
				<div style="display:flex;gap:4px;flex-shrink:0;">
					<button class="btn-icon" title="Edit" onclick='editEvent(<?= htmlspecialchars(json_encode($e),ENT_QUOTES) ?>)'><i class="fas fa-edit"></i></button>
					<a href="<?= site_url('events_booking_controller/delete_event/'.(int)$e['id']) ?>" class="btn-icon delete" title="Delete" onclick="return confirm('Delete this event?')"><i class="fas fa-trash"></i></a>
				</div>
				<?php endif; ?>
			</div>
			<div class="event-card-body">
				<?php if (!empty($e['description'])): ?>
					<p style="font-size:.82rem;color:var(--text-light);margin-bottom:8px;line-height:1.5;"><?= html_escape(substr($e['description'],0,100)) ?><?= strlen($e['description'])>100?'...':'' ?></p>
				<?php endif; ?>
				<div class="event-meta">
					<span><i class="fas fa-calendar"></i> <?= !empty($e['event_date']) ? date('d M Y',strtotime($e['event_date'])) : '—' ?></span>
					<?php if (!empty($e['start_time'])): ?>
						<span><i class="fas fa-clock"></i> <?= date('h:i A',strtotime($e['start_time'])) ?><?= !empty($e['end_time']) ? ' – '.date('h:i A',strtotime($e['end_time'])) : '' ?></span>
					<?php endif; ?>
					<?php if (!empty($e['venue'])): ?><span><i class="fas fa-location-dot"></i> <?= html_escape($e['venue']) ?></span><?php endif; ?>
					<span><i class="fas fa-tag"></i> <?= ucfirst(html_escape($e['event_type'])) ?></span>
				</div>
				<?php if (!empty($e['fund_required']) && !empty($e['fund_amount'])): ?>
				<div class="fund-banner">
					<div style="flex:1;min-width:160px;">
						<div style="font-size:.8rem;font-weight:700;color:#d48806;margin-bottom:3px;">
							<i class="fas fa-hand-holding-dollar"></i> Fund Request
							<span style="margin-left:5px;font-size:.72rem;background:#fff;border:1px solid #ffe58f;padding:1px 6px;border-radius:10px;color:#5c3d11;"><?= ucfirst($e['fund_status'] ?? 'open') ?></span>
						</div>
						<div style="font-size:.78rem;color:#7c5319;">Target: <strong>₹<?= number_format($e['fund_amount'],0) ?></strong> &nbsp;·&nbsp; Raised: <strong>₹<?= number_format($e['fund_raised']??0,0) ?></strong></div>
						<div class="fund-progress"><div class="fund-progress-bar" style="width:<?= $fundPct ?>%"></div></div>
						<div style="font-size:.7rem;color:#7c5319;margin-top:2px;"><?= $fundPct ?>% raised</div>
					</div>
					<?php if (!empty($isOwner) && ($e['fund_status'] ?? '') === 'open'): ?>
						<button type="button" class="btn btn-primary"
						        style="font-size:.76rem;padding:6px 12px;background:#faad14;border-color:#d48806;color:#1a1a1a;white-space:nowrap;"
						        onclick='openContributeModal(<?= (int)$e["id"] ?>,"<?= html_escape($e["title"]) ?>",<?= (float)$e["fund_amount"] ?>)'>
							<i class="fas fa-hand-holding-dollar"></i> Contribute
						</button>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			<div class="event-card-footer">
				<span style="font-size:.73rem;color:var(--text-light);"><i class="fas fa-user"></i> <?= html_escape($e['created_by_name'] ?? 'Admin') ?></span>
				<span style="font-size:.73rem;color:var(--text-light);margin-left:auto;"><i class="fas fa-calendar-plus"></i> <?= date('d M Y',strtotime($e['created_at'])) ?></span>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<!-- Recent Events -->
	<?php if (!empty($recent_events)): ?>
	<div class="management-card" style="margin-bottom:30px;">
		<div class="section-header"><h3><i class="fas fa-clock"></i> Recent Events</h3></div>
		<div class="member-list">
			<?php foreach ($recent_events as $r): ?>
				<div class="member-item">
					<div class="member-info">
						<div class="member-avatar"><i class="fas fa-calendar-alt" style="color:var(--primary);"></i></div>
						<div class="member-details">
							<h4><?= html_escape($r['title']) ?></h4>
							<span><?= ucfirst(html_escape($r['event_type'])) ?><?= !empty($r['event_date']) ? ' · '.date('d M Y',strtotime($r['event_date'])) : '' ?><?= (!empty($isSuperAdmin) && !empty($r['society_name'])) ? ' · <em>'.html_escape($r['society_name']).'</em>' : '' ?></span>
						</div>
					</div>
					<span class="evt-badge <?= html_escape($r['status']) ?>"><?= ucfirst($r['status']) ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

<?php else: /* ══════════════ BOOKINGS TAB ══════════════ */ ?>

	<!-- Booking Stats -->
	<div class="stats-grid">
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-calendar-days"></i></div>
			<div class="stat-info"><h4>Total Bookings</h4><h2><?= (int)($booking_stats['total']    ?? 0) ?></h2></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
			<div class="stat-info"><h4>Pending</h4><h2><?= (int)($booking_stats['pending']  ?? 0) ?></h2></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-circle-check"></i></div>
			<div class="stat-info"><h4>Approved</h4><h2><?= (int)($booking_stats['approved'] ?? 0) ?></h2></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-coins"></i></div>
			<div class="stat-info"><h4>Payments Done</h4><h2><?= (int)($booking_stats['paid']    ?? 0) ?></h2></div>
		</div>
	</div>

	<!-- Booking Filters -->
	<form method="GET" action="<?= site_url('events_booking_controller/bookings') ?>" id="filterForm">
		<div class="filter-section">
			<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
			<div class="filter-group">
				<label><i class="fas fa-building"></i> Society</label>
				<select name="society_id" class="filter-select" onchange="this.form.submit()">
					<option value="">All Societies</option>
					<?php foreach ($societies as $soc): ?>
						<option value="<?= (int)$soc['id'] ?>" <?= ((int)($filters['society_id'] ?? 0) === (int)$soc['id']) ? 'selected' : '' ?>><?= html_escape($soc['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<div class="filter-group">
				<label><i class="fas fa-circle"></i> Status</label>
				<select name="status" class="filter-select" onchange="this.form.submit()">
					<option value="">All Status</option>
					<option value="pending"  <?= ($filters['status'] === 'pending')  ? 'selected' : '' ?>>Pending</option>
					<option value="approved" <?= ($filters['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
					<option value="rejected" <?= ($filters['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
				</select>
			</div>
			<div class="filter-group">
				<label><i class="fas fa-coins"></i> Payment</label>
				<select name="payment_status" class="filter-select" onchange="this.form.submit()">
					<option value="">All Payments</option>
					<option value="pending" <?= ($filters['payment_status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
					<option value="paid"    <?= ($filters['payment_status'] === 'paid')    ? 'selected' : '' ?>>Paid</option>
					<option value="waived"  <?= ($filters['payment_status'] === 'waived')  ? 'selected' : '' ?>>Waived</option>
				</select>
			</div>
			<div class="search-box">
				<i class="fas fa-search"></i>
				<input type="text" name="search" id="searchInput" placeholder="Search name, area, flat..." value="<?= html_escape($filters['search'] ?? '') ?>" autocomplete="off">
			</div>
			<div style="display:flex;gap:8px;align-items:flex-end;">
				<button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
				<?php
				$anyFilter = !empty($filters['search']) || !empty($filters['status']) || !empty($filters['payment_status']) || !empty($filters['society_id']);
				if ($anyFilter): ?>
					<a href="<?= site_url('events_booking_controller/bookings') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
				<?php endif; ?>
			</div>
		</div>
	</form>

	<!-- Active pills (bookings) -->
	<?php if ($anyFilter ?? false): ?>
	<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">
		<span style="font-size:.8rem;color:var(--text-light);font-weight:600;">Active filters:</span>
		<?php if (!empty($filters['society_id']) && $isSuperAdmin): ?>
			<?php $sl=''; foreach($societies as $s){if((int)$s['id']===(int)$filters['society_id']){$sl=$s['name'];break;}} ?>
			<span class="active-filter-pill"><i class="fas fa-building"></i> <?= html_escape($sl) ?> <a href="<?= site_url('events_booking_controller/bookings?'.http_build_query(array_merge($filters,['society_id'=>'']))) ?>">×</a></span>
		<?php endif; ?>
		<?php if (!empty($filters['status'])): ?>
			<span class="active-filter-pill"><i class="fas fa-circle"></i> <?= ucfirst(html_escape($filters['status'])) ?> <a href="<?= site_url('events_booking_controller/bookings?'.http_build_query(array_merge($filters,['status'=>'']))) ?>">×</a></span>
		<?php endif; ?>
		<?php if (!empty($filters['payment_status'])): ?>
			<span class="active-filter-pill"><i class="fas fa-coins"></i> <?= ucfirst(html_escape($filters['payment_status'])) ?> <a href="<?= site_url('events_booking_controller/bookings?'.http_build_query(array_merge($filters,['payment_status'=>'']))) ?>">×</a></span>
		<?php endif; ?>
		<?php if (!empty($filters['search'])): ?>
			<span class="active-filter-pill"><i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>" <a href="<?= site_url('events_booking_controller/bookings?'.http_build_query(array_merge($filters,['search'=>'']))) ?>">×</a></span>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- Toolbar -->
	<div class="table-header" style="margin-bottom:16px;">
		<h3><i class="fas fa-calendar-days"></i> Area Booking Requests <small style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= count($bookings ?? []) ?> records)</small></h3>
		<a href="<?= site_url('events_booking_controller/bookings') ?>" class="btn btn-outline" style="display:flex;align-items:center;gap:6px;"><i class="fas fa-sync-alt"></i> Refresh</a>
		<div class="page-actions">
			<button type="button" class="btn btn-primary" onclick="openAddBookingModal()"><i class="fas fa-plus-circle"></i> New Booking</button>
		</div>
	</div>

	<!-- Booking Table -->
	<div class="table-section">
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th>Area / Purpose</th>
						<th>Resident</th>
						<?php if (!empty($isSuperAdmin)): ?><th>Society</th><?php endif; ?>
						<th>Date</th>
						<th>Time Slot</th>
						<th>Amount</th>
						<th>Payment</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($bookings)): ?>
						<?php foreach ($bookings as $b): ?>
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
									<span style="font-size:.75rem;color:var(--text-light);">Flat <?= html_escape($b['flat_no'] ?? '—') ?></span>
								</td>
								<?php if (!empty($isSuperAdmin)): ?>
									<td><?= !empty($b['society_name']) ? '<span class="society-badge"><i class="fas fa-city"></i> '.html_escape($b['society_name']).'</span>' : '—' ?></td>
								<?php endif; ?>
								<td><?= !empty($b['booking_date']) ? date('d M Y',strtotime($b['booking_date'])) : '—' ?></td>
								<td style="font-size:.82rem;white-space:nowrap;">
									<?= !empty($b['start_time']) ? date('h:i A',strtotime($b['start_time'])) : '—' ?>
									<?= !empty($b['end_time']) ? ' – '.date('h:i A',strtotime($b['end_time'])) : '' ?>
								</td>
								<td><?= $b['amount'] > 0 ? '₹'.number_format($b['amount'],0) : '<span style="color:var(--text-light);">—</span>' ?></td>
								<td><span class="pay-badge <?= html_escape($b['payment_status']) ?>"><?= ucfirst(html_escape($b['payment_status'])) ?></span></td>
								<td><span class="status-badge <?= html_escape($b['status']) ?>"><?= ucfirst(html_escape($b['status'])) ?></span></td>
								<td>
									<div class="action-buttons">
										<button class="btn-icon" title="View" onclick='viewBooking(<?= htmlspecialchars(json_encode($b),ENT_QUOTES) ?>)'><i class="fas fa-eye"></i></button>
										<button class="btn-icon" title="Edit" onclick='editBooking(<?= htmlspecialchars(json_encode($b),ENT_QUOTES) ?>)'><i class="fas fa-edit"></i></button>
										<?php if (!empty($canApprove) && $b['status'] === 'pending'): ?>
											<form method="POST" action="<?= base_url('events_booking_controller/approve_booking/'.(int)$b['id']) ?>" style="display:inline;">
												<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
												<input type="hidden" name="status" value="approved">
												<button type="submit" class="btn-icon" style="color:#389e0d;" title="Approve" onclick="return confirm('Approve?')"><i class="fas fa-check"></i></button>
											</form>
											<form method="POST" action="<?= base_url('events_booking_controller/approve_booking/'.(int)$b['id']) ?>" style="display:inline;">
												<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
												<input type="hidden" name="status" value="rejected">
												<button type="submit" class="btn-icon delete" title="Reject" onclick="return confirm('Reject?')"><i class="fas fa-times"></i></button>
											</form>
										<?php endif; ?>
										<?php if ($b['status'] === 'approved' && $b['payment_status'] === 'pending' && $b['amount'] > 0): ?>
											<button type="button" class="btn-icon" title="Pay ₹<?= number_format($b['amount'],0) ?>"
											        style="color:#fff;background:#52c41a;border-radius:6px;padding:4px 10px;font-size:.74rem;font-weight:700;"
											        onclick="startRazorpay('booking', <?= (int)$b['id'] ?>, <?= (float)$b['amount'] ?>, '<?= html_escape(addslashes($b['area_name'])) ?>')">
												<i class="fas fa-coins"></i> Pay ₹<?= number_format($b['amount'],0) ?>
											</button>
										<?php endif; ?>
										<a href="<?= base_url('events_booking_controller/delete_booking/'.(int)$b['id']) ?>" class="btn-icon delete" title="Delete" onclick="return confirm('Delete this booking?')"><i class="fas fa-trash"></i></a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="<?= !empty($isSuperAdmin) ? 9 : 8 ?>" style="text-align:center;padding:40px;color:var(--text-light);">
								<i class="fas fa-calendar-xmark" style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>
								No bookings found<br>
								<button type="button" class="btn btn-primary" style="margin-top:15px;" onclick="openAddBookingModal()"><i class="fas fa-plus"></i> New Booking</button>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Recent Bookings -->
	<?php if (!empty($recent_bookings)): ?>
	<div class="management-card" style="margin-top:20px;margin-bottom:30px;">
		<div class="section-header"><h3><i class="fas fa-clock"></i> Recent Bookings</h3></div>
		<div class="member-list">
			<?php foreach ($recent_bookings as $r): ?>
				<div class="member-item">
					<div class="member-info">
						<div class="member-avatar"><i class="fas fa-door-open" style="color:var(--primary);"></i></div>
						<div class="member-details">
							<h4><?= html_escape($r['area_name']) ?></h4>
							<span><?= html_escape($r['user_name']) ?> · Flat <?= html_escape($r['flat_no'] ?? '—') ?><?= (!empty($isSuperAdmin) && !empty($r['society_name'])) ? ' · <em>'.html_escape($r['society_name']).'</em>' : '' ?></span>
						</div>
					</div>
					<span class="status-badge <?= html_escape($r['status']) ?>" style="font-size:.72rem;"><?= ucfirst(html_escape($r['status'])) ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

<?php endif; /* end tab if/else */ ?>

</div><!-- /.main -->

<!-- ══ ADD / EDIT EVENT MODAL ══ -->
<div class="modal" id="eventFormModal">
	<div class="modal-content" style="max-width:680px;">
		<div class="modal-header">
			<h3><i class="fas fa-calendar-alt" style="color:var(--primary)"></i> <span id="evtModalTitle">Create Event</span></h3>
			<span class="modal-close" onclick="closeModal('eventFormModal')">&times;</span>
		</div>
		<div class="modal-body" style="padding:16px 20px;">
			<form id="eventForm" method="POST" action="<?= base_url('events_booking_controller/save_event') ?>">
				<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
				<input type="hidden" name="event_id" id="formEventId" value="">

				<?php if (!empty($isSuperAdmin)): ?>
					<div class="form-group" style="margin-bottom:14px;">
						<label><i class="fas fa-building"></i> Society *</label>
						<select name="society_id" id="formEvtSociety" class="form-control">
							<option value="">— Select Society —</option>
							<?php foreach ($societies ?? [] as $soc): ?>
								<option value="<?= (int)$soc['id'] ?>"><?= html_escape($soc['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php else: ?>
					<input type="hidden" name="society_id" value="<?= (int)$this->session->userdata('society_id') ?>">
				<?php endif; ?>

				<div class="form-group" style="margin-bottom:14px;">
					<label>Event Title *</label>
					<input type="text" name="title" id="formEvtTitle" class="form-control" required>
				</div>
				<div class="form-group" style="margin-bottom:14px;">
					<label>Description</label>
					<textarea name="description" id="formEvtDesc" class="form-control" rows="3"></textarea>
				</div>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
					<div class="form-group">
						<label>Event Type *</label>
						<select name="event_type" id="formEvtType" class="form-control" required>
							<option value="">— Select —</option>
							<?php foreach (['festival','celebration','cultural','sports','meeting'] as $t): ?>
								<option value="<?= $t ?>"><?= ucfirst($t) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label>Status *</label>
						<select name="status" id="formEvtStatus" class="form-control" required>
							<?php foreach (['upcoming','ongoing','completed','cancelled'] as $s): ?>
								<option value="<?= $s ?>"><?= ucfirst($s) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:14px;">
					<div class="form-group"><label>Event Date *</label><input type="date" name="event_date" id="formEvtDate" class="form-control" required></div>
					<div class="form-group"><label>Start Time</label><input type="time" name="start_time" id="formEvtStart" class="form-control"></div>
					<div class="form-group"><label>End Time</label><input type="time" name="end_time" id="formEvtEnd" class="form-control"></div>
				</div>
				<div class="form-group" style="margin-bottom:14px;">
					<label>Venue</label>
					<input type="text" name="venue" id="formEvtVenue" class="form-control" placeholder="Clubhouse, Garden, etc.">
				</div>
				<div style="background:#fffbe6;border:1px solid #ffe58f;border-radius:10px;padding:14px;margin-bottom:14px;">
					<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
						<input type="checkbox" name="fund_required" id="fundRequired" value="1" onchange="toggleFund(this.checked)">
						<label for="fundRequired" style="font-weight:600;color:#d48806;cursor:pointer;margin:0;"><i class="fas fa-hand-holding-dollar"></i> This event requires fund contribution</label>
					</div>
					<div id="fundFields" style="display:none;">
						<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
							<div class="form-group"><label>Target Amount (₹)</label><input type="number" name="fund_amount" id="formFundAmt" class="form-control" placeholder="0.00" step="0.01"></div>
							<div class="form-group"><label>Fund Status</label><select name="fund_status" id="formFundStatus" class="form-control"><option value="open">Open</option><option value="closed">Closed</option></select></div>
						</div>
					</div>
				</div>
				<div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:0;">
					<button type="button" class="btn btn-outline" onclick="closeModal('eventFormModal')"><i class="fas fa-times"></i> Cancel</button>
					<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Event</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- ══ CONTRIBUTE MODAL ══ -->
<div class="modal" id="contributeModal">
	<div class="modal-content" style="max-width:420px;">
		<div class="modal-header">
			<h3><i class="fas fa-hand-holding-dollar" style="color:#d48806"></i> Contribute to Fund</h3>
			<span class="modal-close" onclick="closeModal('contributeModal')">&times;</span>
		</div>
		<div class="modal-body" style="padding:20px;">
			<p style="font-size:.9rem;color:var(--text-light);margin-bottom:16px;">Event: <strong id="ctbEventTitle"></strong></p>
			<!-- Amount input only — actual payment via Razorpay, no form POST here -->
			<div class="form-group">
				<label>Amount (₹) *</label>
				<input type="number" id="ctbAmount" class="form-control" placeholder="Enter amount" step="0.01" min="1">
				<small id="ctbHint" style="color:var(--text-light);font-size:.78rem;margin-top:4px;display:block;"></small>
			</div>
			<div id="ctbError" style="color:var(--danger);font-size:.82rem;margin-top:8px;display:none;"></div>
			<div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:14px 0 0;">
				<button type="button" class="btn btn-outline" onclick="closeModal('contributeModal')">Cancel</button>
				<button type="button" id="ctbPayBtn" class="btn btn-primary"
				        style="background:#faad14;border-color:#d48806;color:#1a1a1a;"
				        onclick="startRazorpay('contribution')">
					<i class="fas fa-hand-holding-dollar"></i> Pay Now
				</button>
			</div>
		</div>
	</div>
</div>

<!-- ══ ADD / EDIT BOOKING MODAL ══ -->
<div class="modal" id="bookingFormModal">
	<div class="modal-content" style="max-width:660px;">
		<div class="modal-header">
			<h3><i class="fas fa-calendar-plus" style="color:var(--primary)"></i> <span id="bkgModalTitle">New Booking</span></h3>
			<span class="modal-close" onclick="closeModal('bookingFormModal')">&times;</span>
		</div>
		<div class="modal-body" style="padding:16px 20px;">
			<form id="bookingForm" method="POST" action="<?= base_url('events_booking_controller/save_booking') ?>">
				<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
				<input type="hidden" name="booking_id" id="formBkgId" value="">

				<?php if (!empty($isSuperAdmin)): ?>
					<div class="form-group" style="margin-bottom:14px;">
						<label><i class="fas fa-building"></i> Society *</label>
						<select name="society_id" id="formBkgSociety" class="form-control">
							<option value="">— Select Society —</option>
							<?php foreach ($societies ?? [] as $soc): ?>
								<option value="<?= (int)$soc['id'] ?>"><?= html_escape($soc['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php else: ?>
					<input type="hidden" name="society_id" value="<?= (int)$this->session->userdata('society_id') ?>">
				<?php endif; ?>

				<?php if (!empty($isOwner) && !empty($logged_user)): ?>
					<input type="hidden" name="user_name" value="<?= html_escape($logged_user['name'] ?? '') ?>">
					<input type="hidden" name="flat_no"   value="<?= html_escape($logged_user['flat_no'] ?? '') ?>">
					<div style="background:var(--bg,#f5f5f5);border-radius:10px;padding:12px 14px;margin-bottom:14px;display:flex;gap:20px;">
						<div><div style="font-size:.74rem;color:var(--text-light);margin-bottom:2px;">Resident</div><strong><?= html_escape($logged_user['name'] ?? '') ?></strong></div>
						<div><div style="font-size:.74rem;color:var(--text-light);margin-bottom:2px;">Flat</div><strong><?= html_escape($logged_user['flat_no'] ?? '—') ?></strong></div>
					</div>
				<?php else: ?>
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
						<div class="form-group"><label>Resident Name *</label><input type="text" name="user_name" id="formBkgName" class="form-control" required></div>
						<div class="form-group"><label>Flat No</label><input type="text" name="flat_no" id="formBkgFlat" class="form-control"></div>
					</div>
				<?php endif; ?>

				<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
					<div class="form-group">
						<label>Area / Facility Name *</label>
						<input type="text" name="area_name" id="formBkgArea" class="form-control" placeholder="e.g. Garden, Parking, Clubhouse" required>
					</div>
					<div class="form-group">
						<label>Purpose</label>
						<input type="text" name="purpose" id="formBkgPurpose" class="form-control" placeholder="Birthday party, Car wash...">
					</div>
				</div>
				<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:14px;">
					<div class="form-group"><label>Booking Date *</label><input type="date" name="booking_date" id="formBkgDate" class="form-control" required></div>
					<div class="form-group"><label>Start Time *</label><input type="time" name="start_time" id="formBkgStart" class="form-control" required></div>
					<div class="form-group"><label>End Time *</label><input type="time" name="end_time" id="formBkgEnd" class="form-control" required></div>
				</div>
				<div style="display:grid;grid-template-columns:<?= !empty($canApprove) ? '1fr 1fr 1fr' : '1fr 1fr' ?>;gap:16px;margin-bottom:14px;">
					<div class="form-group"><label>Amount (₹)</label><input type="number" name="amount" id="formBkgAmt" class="form-control" placeholder="0" step="0.01"></div>
					<div class="form-group"><label>Payment Status</label><select name="payment_status" id="formBkgPayment" class="form-control"><option value="pending">Pending</option><option value="paid">Paid</option><option value="waived">Waived</option></select></div>
					<?php if (!empty($canApprove)): ?>
					<div class="form-group"><label>Booking Status</label><select name="status" id="formBkgStatus" class="form-control"><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select></div>
					<?php endif; ?>
				</div>
				<div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:0;">
					<button type="button" class="btn btn-outline" onclick="closeModal('bookingFormModal')"><i class="fas fa-times"></i> Cancel</button>
					<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Submit Booking</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- ══ VIEW BOOKING MODAL ══ -->
<div class="modal" id="viewBookingModal">
	<div class="modal-content" style="max-width:520px;">
		<div class="modal-header"><h3><i class="fas fa-calendar-days"></i> Booking Details</h3><span class="modal-close" onclick="closeModal('viewBookingModal')">&times;</span></div>
		<div class="modal-body" style="padding:20px;">
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 24px;">
				<p><strong>Area:</strong><br><span id="vbArea"></span></p>
				<p><strong>Purpose:</strong><br><span id="vbPurpose"></span></p>
				<p><strong>Resident:</strong><br><span id="vbName"></span></p>
				<p><strong>Flat:</strong><br><span id="vbFlat"></span></p>
				<?php if (!empty($isSuperAdmin)): ?><p style="grid-column:1/-1"><strong>Society:</strong><br><span id="vbSociety"></span></p><?php endif; ?>
				<p><strong>Date:</strong><br><span id="vbDate"></span></p>
				<p><strong>Time Slot:</strong><br><span id="vbTime"></span></p>
				<p><strong>Amount:</strong><br><span id="vbAmt"></span></p>
				<p><strong>Payment:</strong><br><span id="vbPayment"></span></p>
				<p><strong>Status:</strong><br><span id="vbStatus"></span></p>
				<p><strong>Approved By:</strong><br><span id="vbApprover"></span></p>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-outline" onclick="closeModal('viewBookingModal')"><i class="fas fa-times"></i> Close</button>
			<button class="btn btn-primary" onclick="editFromView()"><i class="fas fa-edit"></i> Edit</button>
		</div>
	</div>
</div>

<!-- ══ HIDDEN FORM — Razorpay payment verification (submitted after successful payment) ══ -->
<form id="rzpVerifyForm" method="POST" action="<?= base_url('events_booking_controller/razorpay_verify') ?>" style="display:none;">
	<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
	<input type="hidden" name="razorpay_order_id"   id="rzpOrderId">
	<input type="hidden" name="razorpay_payment_id"  id="rzpPaymentId">
	<input type="hidden" name="razorpay_signature"   id="rzpSignature">
	<input type="hidden" name="type"                 id="rzpType">
	<input type="hidden" name="ref_id"               id="rzpRefId">
	<input type="hidden" name="amount"               id="rzpAmount">
</form>

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
/* ── Razorpay state ── */
var _rzpType     = '';   // 'booking' or 'contribution'
var _rzpRefId    = 0;
var _rzpAmount   = 0;
var _rzpLabel    = '';
var _ctbEventId  = 0;    // set when contribute modal opens

/* ── Razorpay: create order then open checkout ── */
function startRazorpay(type, refId, amount, label) {
	// For contribution, read from modal inputs
	if (type === 'contribution') {
		refId  = _ctbEventId;
		amount = parseFloat(document.getElementById('ctbAmount').value);
		label  = document.getElementById('ctbEventTitle').innerText;
		if (!amount || amount <= 0) {
			document.getElementById('ctbError').innerText  = 'Please enter a valid amount.';
			document.getElementById('ctbError').style.display = 'block';
			return;
		}
		document.getElementById('ctbError').style.display = 'none';
	}

	_rzpType   = type;
	_rzpRefId  = refId;
	_rzpAmount = amount;
	_rzpLabel  = label;

	// Show loading on button
	var btn = type === 'contribution'
		? document.getElementById('ctbPayBtn')
		: event.target.closest('button');
	if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; }

	// Create Razorpay order via AJAX
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?= base_url('events_booking_controller/razorpay_create_order') ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	xhr.onload = function() {
		if (btn) { btn.disabled = false; btn.innerHTML = type === 'contribution' ? '<i class="fas fa-hand-holding-dollar"></i> Pay Now' : '<i class="fas fa-coins"></i> Pay ₹' + Math.round(amount).toLocaleString('en-IN'); }
		var res = JSON.parse(xhr.responseText);
		if (!res.success) { showAlert(res.message || 'Failed to initiate payment.'); return; }
		_openRazorpayCheckout(res.order_id, res.key_id, res.amount);
	};
	xhr.onerror = function() {
		if (btn) { btn.disabled = false; }
		showAlert('Network error. Please try again.');
	};
	xhr.send(
		'<?= $this->security->get_csrf_token_name() ?>=<?= $this->security->get_csrf_hash() ?>'
		+ '&amount=' + encodeURIComponent(amount)
		+ '&type='   + encodeURIComponent(type)
		+ '&ref_id=' + encodeURIComponent(refId)
	);
}

function _openRazorpayCheckout(order_id, key_id, amount_paise) {
	var user_name  = '<?= html_escape($this->session->userdata('name')     ?? '') ?>';
	var user_email = '<?= html_escape($this->session->userdata('email')    ?? '') ?>';
	var user_phone = '<?= html_escape($this->session->userdata('phone')    ?? '') ?>';

	var options = {
		key:          key_id,
		amount:       amount_paise,
		currency:     'INR',
		name:         'SocietyHub',
		description:  _rzpType === 'contribution'
			? 'Fund Contribution — ' + _rzpLabel
			: 'Area Booking — '      + _rzpLabel,
		order_id:     order_id,
		prefill: {
			name:    user_name,
			email:   user_email,
			contact: user_phone,
		},
		theme:  { color: '#6366f1' },
		modal: {
			ondismiss: function() {
				showToast('error', 'Payment cancelled.');
			}
		},
		handler: function(response) {
			/* Payment successful — fill hidden form and submit to verify */
			document.getElementById('rzpOrderId').value   = response.razorpay_order_id;
			document.getElementById('rzpPaymentId').value = response.razorpay_payment_id;
			document.getElementById('rzpSignature').value = response.razorpay_signature;
			document.getElementById('rzpType').value      = _rzpType;
			document.getElementById('rzpRefId').value     = _rzpRefId;
			document.getElementById('rzpAmount').value    = _rzpAmount;
			document.getElementById('rzpVerifyForm').submit();
		},
	};

	var rzp = new Razorpay(options);
	rzp.on('payment.failed', function(resp) {
		showToast('error', 'Payment failed: ' + (resp.error.description || 'Unknown error'));
	});
	rzp.open();

	// Close contribute modal if open
	if (_rzpType === 'contribution') closeModal('contributeModal');
}

/* Modal helpers */
function openModal(id)  { document.getElementById(id).classList.add('active');    document.getElementById('overlay').classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); document.getElementById('overlay').classList.remove('active'); }
document.getElementById('overlay').addEventListener('click', function() {
	document.querySelectorAll('.modal.active').forEach(function(m){ m.classList.remove('active'); });
	this.classList.remove('active');
});
window.addEventListener('keydown', function(e) {
	if (e.key !== 'Escape') return;
	document.querySelectorAll('.modal.active').forEach(function(m){ m.classList.remove('active'); });
	document.getElementById('overlay').classList.remove('active');
});

/* Flash dismiss */
document.addEventListener('DOMContentLoaded', function() {
	var f = document.getElementById('flashMsg');
	if (f) setTimeout(function(){ f.style.transition='opacity .5s'; f.style.opacity='0'; setTimeout(function(){f.remove();},500); }, 3500);
});

/* Live search debounce */
(function() {
	var inp = document.getElementById('searchInput'); if (!inp) return;
	var t;
	inp.addEventListener('input', function() { clearTimeout(t); t = setTimeout(function(){ document.getElementById('filterForm').submit(); }, 500); });
})();

/* ── EVENT MODAL ── */
function openAddEventModal() {
	document.getElementById('evtModalTitle').innerText = 'Create Event';
	document.getElementById('eventForm').reset();
	document.getElementById('formEventId').value = '';
	document.getElementById('fundFields').style.display = 'none';
	openModal('eventFormModal');
}
function toggleFund(show) { document.getElementById('fundFields').style.display = show ? 'block' : 'none'; }
function editEvent(e) {
	document.getElementById('evtModalTitle').innerText  = 'Edit Event';
	document.getElementById('formEventId').value        = e.id;
	document.getElementById('formEvtTitle').value       = e.title         || '';
	document.getElementById('formEvtDesc').value        = e.description   || '';
	document.getElementById('formEvtType').value        = e.event_type    || '';
	document.getElementById('formEvtStatus').value      = e.status        || 'upcoming';
	document.getElementById('formEvtDate').value        = (e.event_date   || '').split(' ')[0];
	document.getElementById('formEvtStart').value       = (e.start_time   || '').substring(0,5);
	document.getElementById('formEvtEnd').value         = (e.end_time     || '').substring(0,5);
	document.getElementById('formEvtVenue').value       = e.venue         || '';
	var sc = document.getElementById('formEvtSociety'); if (sc) sc.value = e.society_id || '';
	var hasFund = parseInt(e.fund_required) === 1;
	document.getElementById('fundRequired').checked = hasFund;
	toggleFund(hasFund);
	if (hasFund) { document.getElementById('formFundAmt').value = e.fund_amount || ''; document.getElementById('formFundStatus').value = e.fund_status || 'open'; }
	openModal('eventFormModal');
}
function openContributeModal(id, title, target) {
	_ctbEventId = id;
	document.getElementById('ctbEventId') && (document.getElementById('ctbEventId').value = id);
	document.getElementById('ctbEventTitle').innerText = title;
	document.getElementById('ctbAmount').value         = '';
	document.getElementById('ctbHint').innerText       = 'Fund target: ₹' + Number(target).toLocaleString('en-IN');
	document.getElementById('ctbError').style.display  = 'none';
	openModal('contributeModal');
}

/* ── BOOKING MODAL ── */
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
	document.getElementById('vbArea').textContent     = b.area_name      || '—';
	document.getElementById('vbPurpose').textContent  = b.purpose         || '—';
	document.getElementById('vbName').textContent     = b.user_name       || '—';
	document.getElementById('vbFlat').textContent     = b.flat_no         || '—';
	document.getElementById('vbDate').textContent     = b.booking_date    ? fmtDate(b.booking_date) : '—';
	document.getElementById('vbTime').textContent     = fmtTime(b.start_time) + (b.end_time ? ' – ' + fmtTime(b.end_time) : '');
	document.getElementById('vbAmt').textContent      = b.amount > 0 ? '₹' + Number(b.amount).toLocaleString('en-IN') : '—';
	document.getElementById('vbPayment').textContent  = b.payment_status  ? b.payment_status.charAt(0).toUpperCase() + b.payment_status.slice(1) : '—';
	document.getElementById('vbStatus').textContent   = b.status          ? b.status.charAt(0).toUpperCase() + b.status.slice(1) : '—';
	document.getElementById('vbApprover').textContent = b.approver_name   || '—';
	var vs = document.getElementById('vbSociety'); if (vs) vs.textContent = b.society_name || '—';
	openModal('viewBookingModal');
}
function editFromView() { closeModal('viewBookingModal'); if (_viewingBooking) editBooking(_viewingBooking); }
function editBooking(b) {
	document.getElementById('bkgModalTitle').innerText = 'Edit Booking';
	document.getElementById('formBkgId').value         = b.id;
	var fn = document.getElementById('formBkgName');    if (fn) fn.value = b.user_name      || '';
	var ff = document.getElementById('formBkgFlat');    if (ff) ff.value = b.flat_no        || '';
	var fa = document.getElementById('formBkgArea');    if (fa) fa.value = b.area_name      || '';
	var fp = document.getElementById('formBkgPurpose'); if (fp) fp.value = b.purpose        || '';
	var fd = document.getElementById('formBkgDate');    if (fd) fd.value = (b.booking_date  || '').split(' ')[0];
	var fs = document.getElementById('formBkgStart');   if (fs) fs.value = (b.start_time    || '').substring(0,5);
	var fe = document.getElementById('formBkgEnd');     if (fe) fe.value = (b.end_time      || '').substring(0,5);
	var fm = document.getElementById('formBkgAmt');     if (fm) fm.value = b.amount         || '';
	var fpy= document.getElementById('formBkgPayment'); if (fpy)fpy.value= b.payment_status || 'pending';
	var fst= document.getElementById('formBkgStatus');  if (fst)fst.value= b.status         || 'pending';
	var sc = document.getElementById('formBkgSociety'); if (sc) sc.value = b.society_id     || '';
	openModal('bookingFormModal');
}

/* Helpers */
function fmtDate(d) { if(!d) return ''; var p=d.split(' ')[0].split('-'); return p.length===3?p[2]+'/'+p[1]+'/'+p[0]:d; }
function fmtTime(t) {
	if (!t) return '—';
	var parts = t.split(':'); if(parts.length<2) return t;
	var h=parseInt(parts[0],10), m=parts[1], ap=h>=12?'PM':'AM';
	h=h%12||12; return h+':'+m+' '+ap;
}
function showAlert(msg) {
	alert(msg); // simple fallback; replace with your toast if preferred
}
function showToast(type, msg) {
	var n = document.createElement('div');
	n.className = 'notification ' + (type === 'success' ? 'success' : 'error');
	n.innerHTML = '<i class="fas ' + (type==='success'?'fa-check-circle':'fa-exclamation-circle') + '"></i> ' + msg;
	document.body.appendChild(n);
	setTimeout(function(){ n.style.animation='slideOut .3s ease'; setTimeout(function(){n.remove();},300); }, 3000);
}
</script>
</body>
</html>
