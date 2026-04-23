<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>Society · Structure Setup</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
	<style>
		:root {
			--primary: #3498db;
			--primary-dark: #2980b9;
			--text-dark: #1e293b;
			--text-light: #64748b;
			--border: #e2e8f0;
			--light-bg: #f8fafc;
			--green: #059669;
			--amber: #d97706;
			--red: #dc2626;
		}

		body {
			font-family: 'Inter', sans-serif;
			background: #f4f7fe;
			color: var(--text-dark);
		}

		/* ── Notification ── */
		.notification {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 13px 18px;
			border-radius: 12px;
			margin-bottom: 20px;
			font-weight: 600;
			font-size: .87rem;
		}

		.notification.success {
			background: #d1fae5;
			color: #065f46;
			border: 1px solid #a7f3d0;
		}

		.notification.error {
			background: #fee2e2;
			color: #991b1b;
			border: 1px solid #fca5a5;
		}

		/* ── Page header ── */
		.page-heading {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 12px;
			margin-bottom: 24px;
		}

		.page-heading h2 {
			font-size: 1.35rem;
			font-weight: 800;
			margin: 0;
		}

		.breadcrumb {
			font-size: .78rem;
			color: var(--text-light);
			margin-top: 4px;
		}

		.breadcrumb span {
			color: var(--primary);
			font-weight: 600;
		}

		/* ── Stepper ── */
		.stepper {
			display: flex;
			gap: 0;
			margin-bottom: 28px;
		}

		.step {
			display: flex;
			align-items: center;
			gap: 8px;
			flex: 1;
			position: relative;
		}

		.step:not(:last-child)::after {
			content: '';
			position: absolute;
			left: calc(50% + 20px);
			top: 18px;
			width: calc(100% - 40px);
			height: 2px;
			background: #e2e8f0;
			z-index: 0;
		}

		.step.done:not(:last-child)::after {
			background: var(--primary);
		}

		.step-circle {
			width: 36px;
			height: 36px;
			border-radius: 50%;
			flex-shrink: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: .8rem;
			font-weight: 800;
			z-index: 1;
			border: 2px solid #e2e8f0;
			background: #fff;
			color: var(--text-light);
			transition: all .3s;
		}

		.step.active .step-circle {
			background: var(--primary);
			border-color: var(--primary);
			color: #fff;
			box-shadow: 0 4px 14px rgba(52, 152, 219, .35);
		}

		.step.done .step-circle {
			background: var(--primary);
			border-color: var(--primary);
			color: #fff;
		}

		.step-label {
			font-size: .73rem;
			font-weight: 600;
			color: var(--text-light);
		}

		.step.active .step-label,
		.step.done .step-label {
			color: var(--primary);
		}

		@media(max-width:600px) {
			.step-label {
				display: none;
			}
		}

		/* ── Card ── */
		.card {
			background: #fff;
			border-radius: 18px;
			box-shadow: 0 2px 12px rgba(52, 152, 219, .07);
			border: 1px solid #e8f0fe;
			margin-bottom: 24px;
		}

		.card-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 10px;
			padding: 18px 24px;
			border-bottom: 1px solid #f0f4fb;
		}

		.card-header h3 {
			font-size: 1rem;
			font-weight: 700;
			margin: 0;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.card-header h3 i {
			color: var(--primary);
		}

		.card-body {
			padding: 24px;
		}

		/* ── Stats ── */
		.stats-row {
			display: flex;
			gap: 14px;
			flex-wrap: wrap;
			margin-bottom: 24px;
		}

		.stat-pill {
			background: #fff;
			border: 1px solid #e8f0fe;
			border-radius: 14px;
			padding: 14px 20px;
			min-width: 130px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
		}

		.stat-pill .sp-n {
			font-size: 1.7rem;
			font-weight: 800;
			line-height: 1;
		}

		.stat-pill .sp-l {
			font-size: .73rem;
			color: var(--text-light);
			font-weight: 500;
			margin-top: 3px;
		}

		.sp-n.blue {
			color: var(--primary);
		}

		.sp-n.green {
			color: var(--green);
		}

		.sp-n.amber {
			color: var(--amber);
		}

		/* ── Wing config rows ── */
		.wings-list {
			display: flex;
			flex-direction: column;
			gap: 14px;
		}

		.wing-row {
			background: var(--light-bg);
			border: 1.5px solid var(--border);
			border-radius: 14px;
			padding: 18px 20px;
			transition: border-color .2s;
		}

		.wing-row:hover {
			border-color: var(--primary);
		}

		.wing-row-header {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 16px;
		}

		.wing-num {
			width: 30px;
			height: 30px;
			border-radius: 8px;
			background: #e0e7ff;
			color: #3730a3;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 800;
			font-size: .78rem;
			flex-shrink: 0;
		}

		.wing-title {
			font-weight: 700;
			font-size: .9rem;
			flex: 1;
		}

		.rm-wing {
			width: 28px;
			height: 28px;
			border-radius: 50%;
			background: #fee2e2;
			color: var(--red);
			border: none;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: .75rem;
			transition: all .2s;
		}

		.rm-wing:hover {
			background: var(--red);
			color: #fff;
		}

		.wing-fields {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
			gap: 10px;
		}

		.fld {
			display: flex;
			flex-direction: column;
			gap: 5px;
		}

		.fld label {
			font-size: .73rem;
			font-weight: 600;
			color: var(--text-light);
		}

		.fld input,
		.fld select {
			padding: 9px 11px;
			border: 1.5px solid var(--border);
			border-radius: 9px;
			font-size: .83rem;
			font-family: 'Inter', sans-serif;
			background: #fff;
			color: var(--text-dark);
			outline: none;
			transition: border-color .2s;
		}

		.fld input:focus,
		.fld select:focus {
			border-color: var(--primary);
		}

		.preview-lbl {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			background: #e0e7ff;
			color: #3730a3;
			border-radius: 20px;
			padding: 2px 10px;
			font-size: .72rem;
			font-weight: 600;
			margin-top: 5px;
			font-family: monospace;
		}

		.total-lbl {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			background: #d1fae5;
			color: #065f46;
			border-radius: 20px;
			padding: 2px 10px;
			font-size: .72rem;
			font-weight: 600;
			margin-top: 5px;
		}

		/* ── Add wing button ── */
		.add-wing-btn {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			padding: 13px;
			border: 2px dashed #c7d2fe;
			border-radius: 14px;
			background: #f8f9ff;
			color: #3730a3;
			font-size: .84rem;
			font-weight: 600;
			cursor: pointer;
			transition: all .2s;
			width: 100%;
		}

		.add-wing-btn:hover {
			background: #e0e7ff;
			border-color: var(--primary);
		}

		/* ── Buttons ── */
		.btn {
			display: inline-flex;
			align-items: center;
			gap: 7px;
			padding: 9px 18px;
			border-radius: 10px;
			font-size: .84rem;
			font-weight: 600;
			cursor: pointer;
			border: none;
			font-family: 'Inter', sans-serif;
			transition: all .2s;
			text-decoration: none;
		}

		.btn-primary {
			background: var(--primary);
			color: #fff;
			box-shadow: 0 3px 12px rgba(52, 152, 219, .35);
		}

		.btn-primary:hover {
			background: var(--primary-dark);
			transform: translateY(-1px);
		}

		.btn-outline {
			background: #fff;
			color: var(--primary);
			border: 1.5px solid var(--primary);
		}

		.btn-outline:hover {
			background: #e8f4fd;
		}

		.btn-danger {
			background: var(--red);
			color: #fff;
		}

		.btn-danger:hover {
			background: #b91c1c;
		}

		.btn-success {
			background: var(--green);
			color: #fff;
			box-shadow: 0 3px 12px rgba(5, 150, 105, .3);
		}

		.btn-success:hover {
			background: #047857;
		}

		.btn-sm {
			padding: 6px 13px;
			font-size: .77rem;
		}

		/* ── Setup status badge ── */
		.setup-badge {
			display: inline-flex;
			align-items: center;
			gap: 7px;
			padding: 5px 14px;
			border-radius: 20px;
			font-size: .78rem;
			font-weight: 600;
		}

		.setup-badge.done {
			background: #d1fae5;
			color: #065f46;
		}

		.setup-badge.pending {
			background: #fef3c7;
			color: #92400e;
		}

		.setup-badge .dot {
			width: 7px;
			height: 7px;
			border-radius: 50%;
		}

		.setup-badge.done .dot {
			background: var(--green);
		}

		.setup-badge.pending .dot {
			background: var(--amber);
		}

		/* ── Wing overview cards ── */
		.wing-cards {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
			gap: 14px;
		}

		.wc {
			background: #fff;
			border: 1.5px solid var(--border);
			border-radius: 14px;
			padding: 16px;
		}

		.wc-top {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 12px;
		}

		.wc-icon {
			width: 40px;
			height: 40px;
			border-radius: 10px;
			background: #e0e7ff;
			color: #3730a3;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 800;
			font-size: .95rem;
		}

		.wc-name {
			font-size: .95rem;
			font-weight: 700;
		}

		.wc-sub {
			font-size: .73rem;
			color: var(--text-light);
		}

		.wc-stats {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
			margin-bottom: 10px;
		}

		.wc-stat {
			background: var(--light-bg);
			border-radius: 8px;
			padding: 4px 10px;
			font-size: .72rem;
			font-weight: 600;
		}

		.wc-stat.g {
			color: var(--green);
			background: #d1fae5;
		}

		.wc-stat.a {
			color: var(--amber);
			background: #fef3c7;
		}

		.wc-stat.b {
			color: var(--primary);
			background: #dbeafe;
		}

		.wc-actions {
			display: flex;
			gap: 7px;
		}

		/* ── Preview flats panel ── */
		.preview-wing {
			margin-bottom: 20px;
		}

		.preview-wing-hdr {
			display: flex;
			align-items: center;
			gap: 10px;
			font-size: .88rem;
			font-weight: 700;
			margin-bottom: 8px;
		}

		.pf-grid {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
		}

		.pf {
			background: #e0f2fe;
			color: #0369a1;
			border: 1px solid #bae6fd;
			border-radius: 7px;
			padding: 3px 9px;
			font-size: .73rem;
			font-weight: 700;
			font-family: monospace;
		}

		.pf.exists {
			background: #fef3c7;
			color: #92400e;
			border-color: #fcd34d;
		}

		.pf-floor-lbl {
			font-size: .68rem;
			font-weight: 700;
			color: var(--text-light);
			text-transform: uppercase;
			letter-spacing: .06em;
			width: 100%;
			padding: 4px 0 2px;
			border-bottom: 1px solid #e8f0fe;
			margin-bottom: 2px;
		}

		/* ── Generate box ── */
		.gen-box {
			background: linear-gradient(135deg, #eff6ff, #e0f2fe);
			border: 1.5px solid #bfdbfe;
			border-radius: 16px;
			padding: 24px;
			text-align: center;
		}

		.gen-box h3 {
			font-size: 1.05rem;
			font-weight: 800;
			margin: 0 0 6px;
		}

		.gen-box p {
			font-size: .83rem;
			color: var(--text-light);
			margin: 0 0 16px;
		}

		.gen-stats {
			display: flex;
			justify-content: center;
			gap: 18px;
			flex-wrap: wrap;
			margin-bottom: 20px;
		}

		.gs {
			background: rgba(255, 255, 255, .7);
			border: 1px solid rgba(255, 255, 255, .8);
			border-radius: 10px;
			padding: 10px 18px;
			text-align: center;
		}

		.gs-n {
			font-size: 1.6rem;
			font-weight: 800;
		}

		.gs-l {
			font-size: .7rem;
			color: var(--text-light);
			font-weight: 500;
		}

		/* ── Help box ── */
		.help-box {
			background: #eff6ff;
			border: 1px solid #bfdbfe;
			border-radius: 12px;
			padding: 13px 16px;
			margin-bottom: 18px;
		}

		.help-box h4 {
			font-size: .8rem;
			font-weight: 700;
			color: #1d4ed8;
			margin: 0 0 7px;
		}

		.help-box ul {
			margin: 0;
			padding-left: 16px;
		}

		.help-box li {
			font-size: .77rem;
			color: #1d4ed8;
			margin-bottom: 3px;
		}

		/* ── Spinner ── */
		.spin {
			display: inline-block;
			width: 16px;
			height: 16px;
			border: 2.5px solid rgba(255, 255, 255, .4);
			border-top-color: #fff;
			border-radius: 50%;
			animation: sp .7s linear infinite;
		}

		@keyframes sp {
			to {
				transform: rotate(360deg)
			}
		}
	</style>
</head>

<body>
	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'society_setup';
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

		<!-- Page heading -->
		<div class="page-heading">
			<div>
				<h2><i class="fas fa-layer-group" style="color:var(--primary);margin-right:8px;"></i>Society Structure
					Setup</h2>
				<div class="breadcrumb">Settings &rsaquo; <span>Structure Setup</span>
					<?php if ($society): ?> &rsaquo; <span><?= html_escape($society->name) ?></span><?php endif; ?>
				</div>
			</div>
			<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
				<?php if ($setupDone): ?>
					<div class="setup-badge done">
						<div class="dot"></div> Setup Complete
					</div>
				<?php else: ?>
					<div class="setup-badge pending">
						<div class="dot"></div> Not Configured
					</div>
				<?php endif; ?>

				<?php if ($isSuper && !empty($societies)): ?>
					<form method="get" action="<?= site_url('society_setup') ?>"
						style="display:flex;align-items:center;gap:6px;">
						<select name="society_id" onchange="this.form.submit()"
							style="padding:7px 12px;border-radius:9px;border:1.5px solid var(--border);font-size:.82rem;font-family:'Inter',sans-serif;">
							<option value="">Select Society</option>
							<?php foreach ($societies as $s): ?>
								<option value="<?= $s->id ?>" <?= $s->id == $selectedSoc ? 'selected' : '' ?>>
									<?= html_escape($s->name) ?> 		<?= $s->setup_done ? ' ✓' : '' ?>
								</option>
							<?php endforeach; ?>
						</select>
					</form>
				<?php endif; ?>

				<?php if ($isSuper): ?>
					<a href="<?= base_url('register-society') ?>" class="btn btn-primary btn-sm">
						<i class="fas fa-user-plus"></i> Create Society
					</a>
				<?php endif; ?>

				<a href="<?= base_url('flat_unit') ?>" class="btn btn-outline btn-sm">
					<i class="fas fa-building"></i> View Flats
				</a>
			</div>
		</div>

		<!-- Stepper -->
		<div class="stepper">
			<div class="step <?= empty($wings) ? 'active' : ($setupDone ? 'done' : 'done') ?>">
				<div class="step-circle"><?= empty($wings) ? '1' : '<i class="fas fa-check"></i>' ?></div>
				<div class="step-label">Configure Wings</div>
			</div>
			<div class="step <?= (!empty($wings) && !$setupDone) ? 'active' : ($setupDone ? 'done' : '') ?>">
				<div class="step-circle"><?= $setupDone ? '<i class="fas fa-check"></i>' : '2' ?></div>
				<div class="step-label">Preview & Generate</div>
			</div>
			<div class="step <?= $setupDone ? 'done active' : '' ?>">
				<div class="step-circle"><?= $setupDone ? '<i class="fas fa-check"></i>' : '3' ?></div>
				<div class="step-label">Flats Ready</div>
			</div>
		</div>

		<!-- Stats pills -->
		<div class="stats-row">
			<div class="stat-pill">
				<div class="sp-n blue"><?= $stats['total'] ?></div>
				<div class="sp-l">Total Flats</div>
			</div>
			<div class="stat-pill">
				<div class="sp-n green"><?= $stats['vacant'] ?></div>
				<div class="sp-l">Vacant</div>
			</div>
			<div class="stat-pill">
				<div class="sp-n amber"><?= $stats['occupied'] ?></div>
				<div class="sp-l">Occupied</div>
			</div>
			<div class="stat-pill">
				<div class="sp-n" style="color:#6366f1;"><?= count($wings) ?></div>
				<div class="sp-l">Wings</div>
			</div>
		</div>

		<!-- ══ STEP 1: Configure Wings ══ -->
		<div class="card">
			<div class="card-header">
				<h3><i class="fas fa-layer-group"></i> Step 1 — Configure Wings & Floors</h3>
				<?php if (!empty($wings)): ?>
					<span style="font-size:.77rem;color:var(--text-light);"><?= count($wings) ?> wing(s) saved — edit &
						re-save to update</span>
				<?php endif; ?>
			</div>
			<div class="card-body">

				<div class="help-box">
					<h4><i class="fas fa-lightbulb"></i> Quick guide</h4>
					<ul>
						<li>Add each <strong>wing</strong> (e.g. A, B, C). You can add multiple.</li>
						<li><strong>Floors</strong> = number of habitable floors (ground floor is optional separately).
						</li>
						<li><strong>Units/floor</strong> = same count on every floor of this wing.</li>
						<li><strong>Naming format:</strong> <code>{W}-{F}{U}</code> → A-101, A-102 …
							<code>{W}{FU}</code> → A101, A102
						</li>
						<li>After saving, preview the flat map, then click <strong>Generate</strong>. Occupied flats are
							never touched.</li>
					</ul>
				</div>

				<form id="wingsForm" method="post" action="<?= base_url('society_setup/save_wings') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
						value="<?= $this->security->get_csrf_hash() ?>">
					<input type="hidden" name="society_id" value="<?= $selectedSoc ?>">

					<div class="wings-list" id="wingsList">
						<?php foreach ($wings as $wi => $w): ?>
							<div class="wing-row" id="wr-<?= $wi ?>">
								<div class="wing-row-header">
									<div class="wing-num"><?= $wi + 1 ?></div>
									<div class="wing-title">Wing <?= html_escape($w->wing_name) ?></div>
									<button type="button" class="rm-wing" onclick="removeWing(this)"><i
											class="fas fa-times"></i></button>
								</div>
								<div class="wing-fields">
									<div class="fld">
										<label>Wing Name *</label>
										<input type="text" name="wings[<?= $wi ?>][wing_name]"
											value="<?= html_escape($w->wing_name) ?>" placeholder="e.g. A" required
											maxlength="10"
											oninput="this.closest('.wing-row').querySelector('.wing-title').textContent='Wing '+(this.value||'?')">
									</div>
									<div class="fld">
										<label>Flat Prefix</label>
										<input type="text" name="wings[<?= $wi ?>][wing_prefix]"
											value="<?= html_escape($w->wing_prefix) ?>" placeholder="e.g. A" maxlength="10"
											oninput="refreshPreview(this.closest('.wing-row'),<?= $wi ?>)">
									</div>
									<div class="fld">
										<label>No. of Floors *</label>
										<input type="number" name="wings[<?= $wi ?>][floors]"
											value="<?= (int) $w->floors ?>" min="1" max="50" required
											oninput="refreshTotal(this.closest('.wing-row'),<?= $wi ?>)">
									</div>
									<div class="fld">
										<label>Units / Floor *</label>
										<input type="number" name="wings[<?= $wi ?>][units_per_floor]"
											value="<?= (int) $w->units_per_floor ?>" min="1" max="50" required
											oninput="refreshTotal(this.closest('.wing-row'),<?= $wi ?>)">
									</div>
									<div class="fld">
										<label>Default Flat Type</label>
										<select name="wings[<?= $wi ?>][flat_type]">
											<?php foreach (['1BHK', '2BHK', '3BHK', '4BHK', 'Penthouse', 'Shop', 'Office'] as $ft): ?>
												<option <?= $w->flat_type == $ft ? 'selected' : '' ?>><?= $ft ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="fld">
										<label>Naming Format <small
												style="color:var(--text-light);">({W}{F}{U})</small></label>
										<input type="text" name="wings[<?= $wi ?>][naming_format]"
											value="<?= html_escape($w->naming_format ?: '{W}-{F}{U}') ?>" maxlength="30"
											placeholder="{W}-{F}{U}"
											oninput="refreshPreview(this.closest('.wing-row'),<?= $wi ?>)">
										<div class="preview-lbl" id="np-<?= $wi ?>">
											<?= makeNamePHP($w->wing_prefix ?: $w->wing_name, $w->naming_format ?: '{W}-{F}{U}', 1, 1) ?>
										</div>
									</div>
									<div class="fld" style="padding-top:14px;">
										<label style="display:flex;align-items:center;gap:7px;cursor:pointer;">
											<input type="checkbox" name="wings[<?= $wi ?>][has_ground_floor]" value="1"
												<?= $w->has_ground_floor ? 'checked' : '' ?>
												onchange="refreshTotal(this.closest('.wing-row'),<?= $wi ?>)">
											Include Ground Floor
										</label>
										<div class="total-lbl" id="tot-<?= $wi ?>">
											~<?= ($w->floors * $w->units_per_floor) + ($w->has_ground_floor ? $w->units_per_floor : 0) ?>
											flats
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<div style="margin-top:14px;">
						<button type="button" class="add-wing-btn" onclick="addWingRow()">
							<i class="fas fa-plus-circle"></i> Add Wing
						</button>
					</div>

					<div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;">
						<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save
							Structure</button>
						<?php if (!empty($wings)): ?>
							<button type="button" class="btn btn-outline" onclick="loadPreview()"><i class="fas fa-eye"></i>
								Preview Flats</button>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>

		<!-- ══ PREVIEW PANEL ══ -->
		<?php if (!empty($wings)): ?>
			<div class="card" id="previewCard" style="display:none;">
				<div class="card-header">
					<h3><i class="fas fa-th"></i> Step 2 — Flat Map Preview</h3>
					<span id="previewLoader" style="display:none;font-size:.8rem;color:var(--text-light);">
						<span class="spin" style="border-color:rgba(52,152,219,.3);border-top-color:var(--primary);"></span>
						Loading…
					</span>
				</div>
				<div class="card-body" id="previewBody">
					<p style="text-align:center;color:var(--text-light);">Click "Preview Flats" above to load the flat map.
					</p>
				</div>
			</div>

			<!-- ══ GENERATE ══ -->
			<div class="card">
				<div class="card-header">
					<h3><i class="fas fa-rocket"></i> Step 3 — Generate All Flats</h3>
				</div>
				<div class="card-body">
					<div class="gen-box">
						<h3>🏗️ Ready to Generate Flats?</h3>
						<p>All flats will be created as <strong>vacant</strong> based on your wing configuration.<br>
							Existing occupied flats are <strong>never overwritten</strong>. Duplicates are skipped.</p>
						<?php
						$totalExpected = 0;
						foreach ($wings as $w) {
							$gf = $w->has_ground_floor ? $w->units_per_floor : 0;
							$totalExpected += ($w->floors * $w->units_per_floor) + $gf;
						}
						$toCreate = max(0, $totalExpected - $stats['total']);
						?>
						<div class="gen-stats">
							<div class="gs">
								<div class="gs-n" style="color:var(--primary);"><?= count($wings) ?></div>
								<div class="gs-l">Wings</div>
							</div>
							<div class="gs">
								<div class="gs-n" style="color:#6366f1;" id="gsTotalExpected"><?= $totalExpected ?></div>
								<div class="gs-l">Total Expected</div>
							</div>
							<div class="gs">
								<div class="gs-n" style="color:var(--green);"><?= $stats['total'] ?></div>
								<div class="gs-l">Already Exist</div>
							</div>
							<div class="gs">
								<div class="gs-n" style="color:var(--amber);" id="gsToCreate"><?= $toCreate ?></div>
								<div class="gs-l">Will Be Created</div>
							</div>
						</div>
						<form method="post" action="<?= base_url('society_setup/generate') ?>" id="genForm">
							<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
								value="<?= $this->security->get_csrf_hash() ?>">
							<input type="hidden" name="society_id" value="<?= $selectedSoc ?>">
							<button type="submit" class="btn btn-success" id="genBtn">
								<i class="fas fa-magic"></i> Generate All Flats
							</button>
						</form>
					</div>
				</div>
			</div>

			<!-- ══ EXISTING WINGS OVERVIEW ══ -->
			<div class="card">
				<div class="card-header">
					<h3><i class="fas fa-building"></i> Wings Overview</h3>
				</div>
				<div class="card-body">
					<div class="wing-cards">
						<?php foreach ($wings as $w):
							$wc = $wingStats[$w->id] ?? ['total' => 0, 'vacant' => 0, 'occupied' => 0];
							$gf = $w->has_ground_floor ? $w->units_per_floor : 0;
							$expected = ($w->floors * $w->units_per_floor) + $gf;
							?>
							<div class="wc">
								<div class="wc-top">
									<div class="wc-icon"><?= strtoupper(substr($w->wing_prefix ?: $w->wing_name, 0, 1)) ?></div>
									<div>
										<div class="wc-name">Wing <?= html_escape($w->wing_name) ?></div>
										<div class="wc-sub"><?= html_escape($w->flat_type) ?> · <?= $w->floors ?> fl ·
											<?= $w->units_per_floor ?> u/fl
										</div>
									</div>
								</div>
								<div class="wc-stats">
									<span class="wc-stat b"><i class="fas fa-building"></i> <?= $expected ?> planned</span>
									<span class="wc-stat g"><i class="fas fa-door-open"></i> <?= $wc['vacant'] ?> vacant</span>
									<span class="wc-stat a"><i class="fas fa-user-check"></i> <?= $wc['occupied'] ?> occ</span>
								</div>
								<div class="wc-actions">
									<a href="<?= base_url('flat_unit?wing_id=' . $w->id) ?>" class="btn btn-outline btn-sm">
										<i class="fas fa-eye"></i> View
									</a>
									<a href="<?= base_url('society_setup/delete_wing/' . $w->id) ?>"
										class="btn btn-danger btn-sm"
										onclick="return confirm('Remove Wing <?= html_escape($w->wing_name) ?> and all its vacant flats? Occupied flats will block deletion.')">
										<i class="fas fa-trash"></i> Remove
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div><!-- /.main -->

	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		let wingIdx = <?= count($wings) ?>;
		const FT = ['1BHK', '2BHK', '3BHK', '4BHK', 'Penthouse', 'Shop', 'Office'];

		function addWingRow(def) {
			const i = wingIdx++;
			def = def || {};
			const nm = def.wing_name || ''; const px = def.wing_prefix || '';
			const fl = def.floors || 4; const upf = def.units_per_floor || 4;
			const ft = def.flat_type || '2BHK'; const gf = def.has_ground_floor || false;
			const fmt = def.naming_format || '{W}-{F}{U}';
			const ftopts = FT.map(f => `<option${f === ft ? ' selected' : ''}>${f}</option>`).join('');
			const sample = makeFlatNo(px || nm.charAt(0).toUpperCase() || 'X', fmt, 1, 1);

			document.getElementById('wingsList').insertAdjacentHTML('beforeend', `
	<div class="wing-row" id="wr-${i}">
		<div class="wing-row-header">
			<div class="wing-num">${wingIdx}</div>
			<div class="wing-title">New Wing</div>
			<button type="button" class="rm-wing" onclick="removeWing(this)"><i class="fas fa-times"></i></button>
		</div>
		<div class="wing-fields">
			<div class="fld"><label>Wing Name *</label>
				<input type="text" name="wings[${i}][wing_name]" value="${nm}" placeholder="e.g. A" required maxlength="10"
					oninput="this.closest('.wing-row').querySelector('.wing-title').textContent='Wing '+(this.value||'?')"></div>
			<div class="fld"><label>Flat Prefix</label>
				<input type="text" name="wings[${i}][wing_prefix]" value="${px}" placeholder="e.g. A" maxlength="10"
					oninput="refreshPreview(this.closest('.wing-row'),${i})"></div>
			<div class="fld"><label>No. of Floors *</label>
				<input type="number" name="wings[${i}][floors]" value="${fl}" min="1" max="50" required
					oninput="refreshTotal(this.closest('.wing-row'),${i})"></div>
			<div class="fld"><label>Units / Floor *</label>
				<input type="number" name="wings[${i}][units_per_floor]" value="${upf}" min="1" max="50" required
					oninput="refreshTotal(this.closest('.wing-row'),${i})"></div>
			<div class="fld"><label>Default Flat Type</label>
				<select name="wings[${i}][flat_type]">${ftopts}</select></div>
			<div class="fld"><label>Naming Format</label>
				<input type="text" name="wings[${i}][naming_format]" value="${fmt}" maxlength="30" placeholder="{W}-{F}{U}"
					oninput="refreshPreview(this.closest('.wing-row'),${i})">
				<div class="preview-lbl" id="np-${i}">${sample}</div></div>
			<div class="fld" style="padding-top:14px;">
				<label style="display:flex;align-items:center;gap:7px;cursor:pointer;">
					<input type="checkbox" name="wings[${i}][has_ground_floor]" value="1" ${gf ? 'checked' : ''}
						onchange="refreshTotal(this.closest('.wing-row'),${i})">
					Include Ground Floor</label>
				<div class="total-lbl" id="tot-${i}">~${calcTotal(fl, upf, gf)} flats</div>
			</div>
		</div>
	</div>`);
			renumber();
		}

		function removeWing(btn) {
			if (document.querySelectorAll('.wing-row').length <= 1) { alert('At least one wing required.'); return; }
			btn.closest('.wing-row').remove(); renumber();
		}
		function renumber() {
			document.querySelectorAll('.wing-num').forEach((el, i) => el.textContent = i + 1);
		}
		function calcTotal(f, u, gf) { return (parseInt(f) || 0) * (parseInt(u) || 0) + (gf ? (parseInt(u) || 0) : 0); }
		function refreshTotal(row, i) {
			const f = row.querySelector('[name*="[floors]"]').value;
			const u = row.querySelector('[name*="[units_per_floor]"]').value;
			const gf = row.querySelector('[name*="[has_ground_floor]"]').checked;
			const el = document.getElementById('tot-' + i);
			if (el) el.textContent = '~' + calcTotal(f, u, gf) + ' flats';
		}
		function refreshPreview(row, i) {
			const px = (row.querySelector('[name*="[wing_prefix]"]').value ||
				row.querySelector('[name*="[wing_name]"]').value.charAt(0) || 'A').toUpperCase();
			const fmt = row.querySelector('[name*="[naming_format]"]').value || '{W}-{F}{U}';
			const el = document.getElementById('np-' + i);
			if (el) el.textContent = makeFlatNo(px, fmt, 1, 1) + ', ' + makeFlatNo(px, fmt, 1, 2) + '…';
		}
		function makeFlatNo(prefix, fmt, floor, unit) {
			const f = floor === 0 ? 'G' : String(floor);
			const u = String(unit).padStart(2, '0');
			return fmt.replace('{W}', prefix).replace('{F}', f).replace('{U}', u).replace('{FU}', f + u).toUpperCase();
		}

		/* ── Preview loader ── */
		function loadPreview() {
			const card = document.getElementById('previewCard');
			const body = document.getElementById('previewBody');
			const ldr = document.getElementById('previewLoader');
			card.style.display = 'block';
			card.scrollIntoView({ behavior: 'smooth', block: 'start' });
			ldr.style.display = 'inline-flex';
			body.innerHTML = '';

			const sid = document.querySelector('[name="society_id"]').value;
			fetch('<?= base_url('society_setup/preview') ?>', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: '<?= $this->security->get_csrf_token_name() ?>=<?= $this->security->get_csrf_hash() ?>&society_id=' + sid
			}).then(r => r.json()).then(data => {
				ldr.style.display = 'none';
				if (!data.success) { body.innerHTML = '<p style="color:red;">Error loading preview.</p>'; return; }

				let html = `<div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
			<div class="gs"><div class="gs-n" style="color:var(--primary);">${data.total}</div><div class="gs-l">Total Flats</div></div>
			<div class="gs"><div class="gs-n" style="color:#6366f1;">${data.wings.length}</div><div class="gs-l">Wings</div></div>
		</div>`;

				data.wings.forEach(wing => {
					html += `<div class="preview-wing">
				<div class="preview-wing-hdr">
					<div style="width:26px;height:26px;background:#e0e7ff;color:#3730a3;border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.75rem;">${wing.wing_prefix}</div>
					Wing ${wing.wing_name}
					<span style="font-size:.73rem;color:var(--text-light);font-weight:400;">${wing.flat_type}</span>
				</div>`;
					wing.floor_groups.forEach(fg => {
						html += `<div style="margin-bottom:10px;">
					<div class="pf-grid">
						<div class="pf-floor-lbl"><i class="fas fa-stairs" style="margin-right:3px;color:var(--primary);"></i>${fg.floor_label}</div>`;
						fg.flats.forEach(f => { html += `<div class="pf ${f.exists ? 'exists' : ''}">${f.flat_no}</div>`; });
						html += `</div></div>`;
					});
					html += `</div>`;
				});

				html += `<div style="display:flex;gap:10px;margin-top:8px;font-size:.73rem;">
			<span><span style="display:inline-block;width:11px;height:11px;background:#e0f2fe;border:1px solid #bae6fd;border-radius:3px;vertical-align:middle;"></span> Will be created</span>
			<span><span style="display:inline-block;width:11px;height:11px;background:#fef3c7;border:1px solid #fcd34d;border-radius:3px;vertical-align:middle;"></span> Already exists</span>
		</div>`;
				body.innerHTML = html;
			}).catch(() => { ldr.style.display = 'none'; body.innerHTML = '<p style="color:red;">Failed to load preview.</p>'; });
		}

		/* ── Generate button loading ── */
		document.getElementById('genForm')?.addEventListener('submit', function () {
			const b = document.getElementById('genBtn');
			b.innerHTML = '<span class="spin"></span> Generating…';
			b.disabled = true;
		});

		/* ── Flash dismiss ── */
		document.addEventListener('DOMContentLoaded', () => {
			const f = document.getElementById('flashMsg');
			if (f) setTimeout(() => { f.style.transition = 'opacity .5s'; f.style.opacity = '0'; setTimeout(() => f.remove(), 500); }, 4000);
	<?php if (!empty($wings)): ?> loadPreview(); <?php endif; ?>
		});
	</script>

	<?php
	// PHP helper to render naming preview in existing wings on server-side
	function makeNamePHP(string $px, string $fmt, int $floor, int $unit): string
	{
		$f = $floor === 0 ? 'G' : (string) $floor;
		$u = str_pad($unit, 2, '0', STR_PAD_LEFT);
		return strtoupper(str_replace(['{W}', '{F}', '{U}', '{FU}'], [$px, $f, $u, $f . $u], $fmt));
	}
	?>
</body>

</html>
