<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>SocietyHub · Register Administrator</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet" />
	<style>
		:root {
			--primary: #3498db;
			--primary-dark: #2980b9;
			--border: #e2e8f0;
			--light: #f8fafc;
			--text: #1e293b;
			--text-light: #64748b;
			--green: #059669;
			--red: #dc2626;
			--amber: #d97706;
		}

		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		body {
			font-family: Inter, sans-serif;
			background: #f0f4f8;
			min-height: 100vh;
			display: flex;
			align-items: flex-start;
			justify-content: center;
			padding: 32px 16px;
		}

		.wrap {
			width: 100%;
			max-width: 680px;
		}

		/* ── Progress bar ── */
		.progress-bar {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0;
			margin-bottom: 28px;
		}

		.pb-step {
			display: flex;
			align-items: center;
			gap: 7px;
			font-size: .76rem;
			font-weight: 600;
			color: var(--text-light);
		}

		.pb-step.done {
			color: var(--green);
		}

		.pb-step.active {
			color: var(--primary);
		}

		.pb-circle {
			width: 26px;
			height: 26px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: .72rem;
			font-weight: 800;
			border: 2px solid #cbd5e1;
			background: #fff;
			color: var(--text-light);
		}

		.pb-step.done .pb-circle {
			background: var(--green);
			border-color: var(--green);
			color: #fff;
		}

		.pb-step.active .pb-circle {
			background: var(--primary);
			border-color: var(--primary);
			color: #fff;
		}

		.pb-line {
			width: 50px;
			height: 2px;
			background: #cbd5e1;
		}

		.pb-line.done {
			background: var(--green);
		}

		/* ── Card ── */
		.card {
			background: #fff;
			border-radius: 20px;
			box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
			border: 1px solid var(--border);
			overflow: hidden;
		}

		.card-head {
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			padding: 24px 28px;
			color: #fff;
		}

		.card-head h2 {
			font-size: 1.25rem;
			font-weight: 800;
			margin-bottom: 3px;
		}

		.card-head p {
			font-size: .83rem;
			opacity: .85;
		}

		.card-head .society-pill {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: rgba(255, 255, 255, .2);
			border-radius: 20px;
			padding: 4px 12px;
			font-size: .75rem;
			font-weight: 600;
			margin-top: 10px;
		}

		.card-body {
			padding: 28px;
		}

		/* ── Alerts ── */
		.alert {
			display: flex;
			align-items: flex-start;
			gap: 10px;
			padding: 12px 16px;
			border-radius: 10px;
			margin-bottom: 18px;
			font-size: .84rem;
			font-weight: 500;
		}

		.alert.success {
			background: #d1fae5;
			color: #065f46;
			border: 1px solid #a7f3d0;
		}

		.alert.error {
			background: #fee2e2;
			color: #991b1b;
			border: 1px solid #fca5a5;
		}

		.alert i {
			margin-top: 1px;
			flex-shrink: 0;
		}

		/* ── Section divider ── */
		.sec-title {
			display: flex;
			align-items: center;
			gap: 10px;
			font-size: .68rem;
			font-weight: 700;
			letter-spacing: .1em;
			text-transform: uppercase;
			color: var(--text-light);
			margin: 20px 0 14px;
		}

		.sec-title::before,
		.sec-title::after {
			content: '';
			flex: 1;
			height: 1px;
			background: var(--border);
		}

		/* ── Form ── */
		.form-group {
			margin-bottom: 14px;
		}

		.form-group label {
			display: block;
			margin-bottom: 6px;
			font-size: .82rem;
			font-weight: 600;
			color: var(--text);
		}

		.form-group label .req {
			color: var(--red);
		}

		.form-control {
			width: 100%;
			padding: 10px 13px;
			border: 1.5px solid var(--border);
			border-radius: 10px;
			font-family: Inter, sans-serif;
			font-size: .85rem;
			color: var(--text);
			background: var(--light);
			outline: none;
			transition: border-color .2s, background .2s;
		}

		.form-control:focus {
			border-color: var(--primary);
			background: #fff;
		}

		select.form-control {
			cursor: pointer;
		}

		.form-row {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 13px;
		}

		@media(max-width:520px) {
			.form-row {
				grid-template-columns: 1fr;
			}
		}

		/* ── Flat picker ── */
		.flat-picker-wrap {
			border: 1.5px solid var(--border);
			border-radius: 12px;
			background: var(--light);
			overflow: hidden;
			transition: border-color .2s;
		}

		.flat-picker-wrap.active {
			border-color: var(--primary);
		}

		.fp-toolbar {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 8px 12px;
			background: #f1f5f9;
			border-bottom: 1px solid var(--border);
			font-size: .74rem;
			color: var(--text-light);
			font-weight: 500;
		}

		.fp-count {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: #d1fae5;
			color: #065f46;
			border-radius: 20px;
			padding: 1px 8px;
			font-size: .68rem;
			font-weight: 700;
		}

		.fp-count.loading {
			background: #fef3c7;
			color: var(--amber);
		}

		.fp-count.none {
			background: #fee2e2;
			color: var(--red);
		}

		.fp-floor-filter {
			padding: 6px 12px;
			background: #f8fafc;
			border-bottom: 1px solid var(--border);
		}

		.fp-floor-filter select {
			padding: 5px 10px;
			border: 1.5px solid var(--border);
			border-radius: 7px;
			font-size: .78rem;
			font-family: Inter, sans-serif;
			background: #fff;
			color: var(--text);
			outline: none;
			cursor: pointer;
		}

		.fp-floor-filter select:focus {
			border-color: var(--primary);
		}

		.fp-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(78px, 1fr));
			gap: 6px;
			padding: 10px;
			max-height: 200px;
			overflow-y: auto;
		}

		.fp-tile {
			padding: 7px 4px;
			border-radius: 8px;
			border: 1.5px solid var(--border);
			background: #fff;
			cursor: pointer;
			text-align: center;
			font-family: Inter, sans-serif;
			transition: all .15s;
		}

		.fp-tile:hover {
			border-color: var(--primary);
			background: #eff6ff;
		}

		.fp-tile.selected {
			border-color: var(--primary);
			background: #dbeafe;
		}

		.fp-tile .ft-no {
			font-size: .8rem;
			font-weight: 800;
			color: var(--text);
			line-height: 1.2;
		}

		.fp-tile .ft-type {
			font-size: .62rem;
			color: var(--text-light);
			margin-top: 1px;
		}

		.fp-tile.selected .ft-no,
		.fp-tile.selected .ft-type {
			color: #1d4ed8;
		}

		.fp-floor-label {
			grid-column: 1 / -1;
			font-size: .65rem;
			font-weight: 700;
			color: var(--text-light);
			text-transform: uppercase;
			letter-spacing: .06em;
			padding: 3px 0 2px;
			border-bottom: 1px solid var(--border);
		}

		.fp-empty {
			grid-column: 1/-1;
			text-align: center;
			padding: 20px;
			color: var(--text-light);
			font-size: .82rem;
		}

		/* ── Selected flat bar ── */
		.sel-flat-bar {
			display: none;
			align-items: center;
			justify-content: space-between;
			background: #e0f2fe;
			border: 1.5px solid #7dd3fc;
			border-radius: 10px;
			padding: 9px 13px;
			margin-bottom: 10px;
		}

		.sel-flat-bar.show {
			display: flex;
		}

		.sfb-info {
			display: flex;
			align-items: center;
			gap: 9px;
		}

		.sfb-icon {
			width: 32px;
			height: 32px;
			border-radius: 8px;
			background: #0369a1;
			color: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: .85rem;
			flex-shrink: 0;
		}

		.sfb-no {
			font-size: .95rem;
			font-weight: 800;
			color: #0c4a6e;
		}

		.sfb-meta {
			font-size: .72rem;
			color: #0369a1;
		}

		.sfb-clear {
			background: #fee2e2;
			color: var(--red);
			border: none;
			border-radius: 7px;
			padding: 4px 10px;
			font-size: .71rem;
			font-weight: 600;
			cursor: pointer;
			font-family: Inter, sans-serif;
		}

		/* ── Prompt when no wing selected ── */
		.fp-prompt {
			padding: 18px;
			text-align: center;
			color: var(--text-light);
			font-size: .82rem;
		}

		.fp-prompt i {
			display: block;
			font-size: 1.4rem;
			opacity: .3;
			margin-bottom: 6px;
		}

		/* ── Password strength ── */
		.pw-strength {
			height: 3px;
			border-radius: 99px;
			margin-top: 5px;
			background: #e2e8f0;
			transition: all .3s;
		}

		.pw-strength.w {
			background: var(--red);
			width: 33%;
		}

		.pw-strength.m {
			background: var(--amber);
			width: 66%;
		}

		.pw-strength.s {
			background: var(--green);
			width: 100%;
		}

		/* ── Submit ── */
		.btn-register {
			width: 100%;
			padding: 13px;
			border-radius: 999px;
			border: 0;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			color: #fff;
			font-family: Inter, sans-serif;
			font-size: .92rem;
			font-weight: 700;
			cursor: pointer;
			transition: all .2s;
			margin-top: 6px;
		}

		.btn-register:hover:not(:disabled) {
			transform: translateY(-1px);
			box-shadow: 0 6px 20px rgba(52, 152, 219, .4);
		}

		.btn-register:disabled {
			opacity: .65;
			cursor: not-allowed;
		}

		.login-link {
			text-align: center;
			margin-top: 14px;
			font-size: .82rem;
			color: var(--text-light);
		}

		.login-link a {
			color: var(--primary);
			font-weight: 600;
			text-decoration: none;
		}

		/* ── Spinner ── */
		@keyframes sp {
			to {
				transform: rotate(360deg);
			}
		}

		.spin {
			display: inline-block;
			width: 14px;
			height: 14px;
			border: 2px solid rgba(255, 255, 255, .4);
			border-top-color: #fff;
			border-radius: 50%;
			animation: sp .7s linear infinite;
			vertical-align: middle;
			margin-right: 4px;
		}

		/* ── Scrollbar ── */
		.fp-grid::-webkit-scrollbar {
			width: 4px;
		}

		.fp-grid::-webkit-scrollbar-thumb {
			background: #cbd5e1;
			border-radius: 4px;
		}
	</style>
</head>

<body>
	<div class="wrap">

		<!-- Progress -->
		<div class="progress-bar">
			<div class="pb-step done">
				<div class="pb-circle"><i class="fas fa-check"></i></div>
				<span>Society Registered</span>
			</div>
			<div class="pb-line done"></div>
			<div class="pb-step active">
				<div class="pb-circle">2</div>
				<span>Admin Account</span>
			</div>
			<div class="pb-line"></div>
			<div class="pb-step">
				<div class="pb-circle">3</div>
				<span>Done</span>
			</div>
		</div>

		<div class="card">
			<!-- Card header -->
			<div class="card-head">
				<h2><i class="fas fa-user-shield" style="margin-right:8px;"></i>Register Administrator</h2>
				<p>Create the Chairman / Admin account for your society.</p>
				<?php if (!empty($society)): ?>
					<div class="society-pill">
						<i class="fas fa-building"></i>
						<?= html_escape($society->name) ?>
						&nbsp;·&nbsp;
						<?= ucfirst($society->society_type) ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="card-body">

				<!-- Flash messages -->
				<?php if ($this->session->flashdata('success')): ?>
					<div class="alert success"><i class="fas fa-check-circle"></i>
						<?= $this->session->flashdata('success') ?></div>
				<?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?>
					<div class="alert error"><i class="fas fa-exclamation-circle"></i>
						<?= $this->session->flashdata('error') ?></div>
				<?php endif; ?>
				<?php $ve = validation_errors();
				if (!empty($ve)): ?>
					<div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $ve ?></div>
				<?php endif; ?>

				<form method="post" action="<?= site_url('plan_controller/admin_register') ?>" id="adminForm"
					novalidate>
					<?= form_open('') /* CSRF token */ ?>

					<!-- Hidden flat/wing values set by JS picker -->
					<input type="hidden" name="flat_id" id="hFlatId" value="">
					<input type="hidden" name="wing_id" id="hWingId" value="">

					<!-- ══ Personal Info ══ -->
					<div class="sec-title">Personal Information</div>

					<div class="form-row">
						<div class="form-group">
							<label>First Name <span class="req">*</span></label>
							<input type="text" name="adminFirstName" class="form-control" required
								placeholder="First name" value="<?= set_value('adminFirstName') ?>">
						</div>
						<div class="form-group">
							<label>Last Name <span class="req">*</span></label>
							<input type="text" name="adminLastName" class="form-control" required
								placeholder="Last name" value="<?= set_value('adminLastName') ?>">
						</div>
					</div>

					<!-- hidden combined name field -->
					<input type="hidden" name="adminName" id="adminName" value="">

					<div class="form-row">
						<div class="form-group">
							<label>Email Address <span class="req">*</span></label>
							<input type="email" name="adminEmail" class="form-control" required
								placeholder="admin@example.com" value="<?= set_value('adminEmail') ?>">
						</div>
						<div class="form-group">
							<label>Phone Number <span class="req">*</span></label>
							<input type="tel" name="adminPhone" class="form-control" required
								placeholder="10-digit number" maxlength="10" value="<?= set_value('adminPhone') ?>">
						</div>
					</div>

					<!-- ══ Flat Assignment ══ -->
					<div class="sec-title">Flat Assignment</div>

					<?php if (empty($wings)): ?>
						<div class="alert error">
							<i class="fas fa-exclamation-triangle"></i>
							No wings found for this society. Please go back and ensure society setup completed correctly.
						</div>
					<?php else: ?>

						<div class="form-row">
							<div class="form-group">
								<label>Wing / Block <span class="req">*</span></label>
								<select id="wingSelect" class="form-control" onchange="loadFlats(this.value)">
									<option value="">— Select Wing —</option>
									<?php foreach ($wings as $w): ?>
										<option value="<?= (int) $w->id ?>"><?= html_escape($w->wing_name) ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="form-group">
								<label>Floor Filter</label>
								<select id="floorFilter" class="form-control" onchange="filterByFloor(this.value)" disabled>
									<option value="">All Floors</option>
								</select>
							</div>
						</div>

						<!-- Selected flat display -->
						<div class="sel-flat-bar" id="selFlatBar">
							<div class="sfb-info">
								<div class="sfb-icon"><i class="fas fa-door-closed"></i></div>
								<div>
									<div class="sfb-no" id="selFlatNo"></div>
									<div class="sfb-meta" id="selFlatMeta"></div>
								</div>
							</div>
							<button type="button" class="sfb-clear" onclick="clearFlat()">
								<i class="fas fa-times"></i> Clear
							</button>
						</div>

						<!-- Flat picker -->
						<div class="form-group">
							<label>Select Your Flat <span class="req">*</span></label>
							<div class="flat-picker-wrap" id="fpWrap">
								<div class="fp-toolbar">
									<span><i class="fas fa-th" style="margin-right:4px;color:var(--primary);"></i>Available
										Flats</span>
									<span class="fp-count" id="fpCount">—</span>
								</div>
								<div class="fp-grid" id="fpGrid">
									<div class="fp-prompt">
										<i class="fas fa-layer-group"></i>
										Select a wing above to see available flats.
									</div>
								</div>
							</div>
						</div>

					<?php endif; ?>

					<!-- ══ Credentials ══ -->
					<div class="sec-title">Login Credentials</div>

					<div class="form-row">
						<div class="form-group">
							<label>Password <span class="req">*</span></label>
							<input type="password" name="adminPassword" id="adminPassword" class="form-control" required
								placeholder="Min 6 characters" minlength="6" oninput="checkStrength(this.value)">
							<div class="pw-strength" id="pwBar"></div>
						</div>
						<div class="form-group">
							<label>Confirm Password <span class="req">*</span></label>
							<input type="password" name="confirmPassword" id="confirmPassword" class="form-control"
								required placeholder="Re-enter password">
						</div>
					</div>

					<button type="submit" class="btn-register" id="submitBtn">
						<i class="fas fa-user-plus"></i> Create Admin Account
					</button>
				</form>

				<div class="login-link">Already registered? <a href="<?= base_url('login') ?>">Login here</a></div>
			</div>
		</div>
	</div>

	<script>
		/* ════════════════════════════════════════════════
		   FLAT PICKER  (minimal JS, no jQuery needed)
		════════════════════════════════════════════════ */
		let allFlats = [];   // full list for current wing
		let filteredFlats = [];
		let pickedFlat = null;

		/* Called when wing dropdown changes */
		function loadFlats(wingId) {
			const grid = document.getElementById('fpGrid');
			const count = document.getElementById('fpCount');
			const ffSel = document.getElementById('floorFilter');
			const fpWrap = document.getElementById('fpWrap');

			clearFlat();
			ffSel.innerHTML = '<option value="">All Floors</option>';
			ffSel.disabled = true;

			if (!wingId) {
				grid.innerHTML = `<div class="fp-prompt"><i class="fas fa-layer-group"></i>Select a wing above.</div>`;
				count.textContent = '—'; count.className = 'fp-count';
				document.getElementById('hWingId').value = '';
				return;
			}

			document.getElementById('hWingId').value = wingId;
			grid.innerHTML = `<div class="fp-prompt"><i class="fas fa-spinner fa-spin"></i>Loading flats…</div>`;
			count.textContent = '…'; count.className = 'fp-count loading';

			fetch(`<?= base_url('plan_controller/get_wing_flats') ?>?wing_id=${wingId}`)
				.then(r => r.json())
				.then(data => {
					allFlats = data.flats || [];
					filteredFlats = [...allFlats];

					/* Build floor filter */
					const floors = [...new Set(allFlats.map(f => f.floor))].sort((a, b) => a - b);
					floors.forEach(fl => {
						const o = document.createElement('option');
						o.value = fl;
						o.textContent = fl === '0' || fl === 0 ? 'Ground Floor' : ordinal(parseInt(fl)) + ' Floor';
						ffSel.appendChild(o);
					});
					ffSel.disabled = floors.length === 0;

					renderGrid(allFlats);
					fpWrap.classList.add('active');
				})
				.catch(() => {
					grid.innerHTML = `<div class="fp-empty"><i class="fas fa-exclamation-circle" style="color:var(--red);"></i> Failed to load flats.</div>`;
					count.textContent = 'Error'; count.className = 'fp-count none';
				});
		}

		/* Floor filter change */
		function filterByFloor(floor) {
			filteredFlats = floor === '' ? [...allFlats] : allFlats.filter(f => String(f.floor) === String(floor));
			clearFlat();
			renderGrid(filteredFlats);
		}

		/* Render flat tiles grouped by floor */
		function renderGrid(flats) {
			const grid = document.getElementById('fpGrid');
			const count = document.getElementById('fpCount');
			grid.innerHTML = '';

			if (!flats.length) {
				grid.innerHTML = `<div class="fp-empty"><i class="fas fa-door-open" style="font-size:1.3rem;display:block;opacity:.3;margin-bottom:5px;"></i>No vacant flats for this selection.</div>`;
				count.textContent = '0 vacant'; count.className = 'fp-count none';
				return;
			}

			count.textContent = flats.length + ' vacant'; count.className = 'fp-count';

			/* Group by floor */
			const grouped = {};
			flats.forEach(f => {
				const k = f.floor;
				if (!grouped[k]) grouped[k] = [];
				grouped[k].push(f);
			});

			Object.keys(grouped).sort((a, b) => a - b).forEach(fl => {
				/* Floor label */
				const lbl = document.createElement('div');
				lbl.className = 'fp-floor-label';
				lbl.innerHTML = `<i class="fas fa-stairs" style="margin-right:3px;color:var(--primary);"></i>${fl === 0 || fl === '0' ? 'Ground Floor' : ordinal(parseInt(fl)) + ' Floor'}`;
				grid.appendChild(lbl);

				grouped[fl].forEach(f => {
					const tile = document.createElement('div');
					tile.className = 'fp-tile';
					tile.dataset.id = f.id;
					tile.innerHTML = `<div class="ft-no">${f.flat_no}</div><div class="ft-type">${f.flat_type}</div>`;
					tile.addEventListener('click', () => pickFlat(f, tile));
					grid.appendChild(tile);
				});
			});
		}

		/* Select a flat tile */
		function pickFlat(flat, tile) {
			document.querySelectorAll('.fp-tile.selected').forEach(t => t.classList.remove('selected'));
			tile.classList.add('selected');
			pickedFlat = flat;

			document.getElementById('hFlatId').value = flat.id;

			const bar = document.getElementById('selFlatBar');
			bar.classList.add('show');
			document.getElementById('selFlatNo').textContent = flat.flat_no;
			document.getElementById('selFlatMeta').textContent =
				[flat.flat_type, flat.wing_name ? 'Wing ' + flat.wing_name : '', flat.floor_label]
					.filter(Boolean).join(' · ');
		}

		/* Clear selection */
		function clearFlat() {
			pickedFlat = null;
			document.getElementById('hFlatId').value = '';
			document.getElementById('selFlatBar').classList.remove('show');
			document.querySelectorAll('.fp-tile.selected').forEach(t => t.classList.remove('selected'));
		}

		/* Ordinal helper */
		function ordinal(n) {
			const s = ['th', 'st', 'nd', 'rd'], v = n % 100;
			return n + (s[(v - 20) % 10] || s[v] || s[0]);
		}

		/* ════════════════════════════════════════════════
		   PASSWORD STRENGTH
		════════════════════════════════════════════════ */
		function checkStrength(val) {
			const bar = document.getElementById('pwBar');
			if (!val) { bar.className = 'pw-strength'; return; }
			if (val.length < 6) { bar.className = 'pw-strength w'; return; }
			const strong = /[A-Z]/.test(val) && /[0-9]/.test(val) && val.length >= 8;
			const medium = val.length >= 6;
			bar.className = 'pw-strength ' + (strong ? 's' : medium ? 'm' : 'w');
		}

		/* ════════════════════════════════════════════════
		   FORM SUBMIT VALIDATION
		════════════════════════════════════════════════ */
		document.getElementById('adminForm').addEventListener('submit', function (e) {
			/* Combine first + last name */
			const fn = this.querySelector('[name="adminFirstName"]').value.trim();
			const ln = this.querySelector('[name="adminLastName"]').value.trim();
			document.getElementById('adminName').value = (fn + ' ' + ln).trim();

			/* Flat required */
			if (!document.getElementById('hFlatId').value) {
				e.preventDefault();
				const fpWrap = document.getElementById('fpWrap');
				fpWrap.style.borderColor = 'var(--red)';
				fpWrap.style.boxShadow = '0 0 0 3px rgba(220,38,38,.15)';
				fpWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
				setTimeout(() => { fpWrap.style.borderColor = ''; fpWrap.style.boxShadow = ''; }, 2500);
				return;
			}

			/* Password match */
			const p = document.getElementById('adminPassword').value;
			const c = document.getElementById('confirmPassword').value;
			if (p !== c) {
				e.preventDefault();
				document.getElementById('confirmPassword').style.borderColor = 'var(--red)';
				alert('Passwords do not match.');
				return;
			}

			/* Loading state */
			const btn = document.getElementById('submitBtn');
			btn.innerHTML = '<span class="spin"></span> Creating Account…';
			btn.disabled = true;
		});
	</script>
</body>

</html>
