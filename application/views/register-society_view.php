<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SocietyHub · Register Your Society</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
	<style>
		:root {
			--primary: #3498db;
			--primary-dark: #2980b9;
			--border: #e1e8ed;
			--light-bg: #f8f9fa;
			--green: #059669;
			--amber: #d97706;
			--text: #1e293b;
			--text-light: #64748b;
		}

		* {
			box-sizing: border-box;
		}

		body {
			font-family: Inter, sans-serif;
			background: #f0f4f8;
			margin: 0;
			padding: 24px 16px;
			display: flex;
			justify-content: center;
		}

		.register-container {
			max-width: 1240px;
			width: 100%;
		}

		.page-header {
			text-align: center;
			margin-bottom: 24px;
		}

		.page-header h1 {
			font-size: 1.8rem;
			font-weight: 800;
			color: var(--text);
			margin: 0 0 6px;
		}

		.page-header p {
			color: var(--text-light);
			font-size: .88rem;
			margin: 0;
		}

		.steps {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0;
			margin-bottom: 28px;
			flex-wrap: wrap;
		}

		.step-item {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: .78rem;
			font-weight: 600;
			color: var(--text-light);
		}

		.step-item.active {
			color: var(--primary);
		}

		.step-item.done {
			color: var(--green);
		}

		.step-circle {
			width: 28px;
			height: 28px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 800;
			font-size: .75rem;
			flex-shrink: 0;
			border: 2px solid #cbd5e1;
			background: #fff;
			color: var(--text-light);
		}

		.step-item.active .step-circle {
			background: var(--primary);
			border-color: var(--primary);
			color: #fff;
		}

		.step-item.done .step-circle {
			background: var(--green);
			border-color: var(--green);
			color: #fff;
		}

		.step-line {
			width: 40px;
			height: 2px;
			background: #cbd5e1;
			flex-shrink: 0;
		}

		.step-line.done {
			background: var(--green);
		}

		.form-card {
			background: #fff;
			padding: 32px;
			border-radius: 20px;
			box-shadow: 0 4px 24px rgba(0, 0, 0, .06);
			border: 1px solid var(--border);
		}

		.section-title {
			display: flex;
			align-items: center;
			gap: 10px;
			font-size: .72rem;
			font-weight: 700;
			letter-spacing: .1em;
			text-transform: uppercase;
			color: var(--text-light);
			margin: 20px 0 14px;
		}

		.section-title::before,
		.section-title::after {
			content: '';
			flex: 1;
			height: 1px;
			background: var(--border);
		}

		.form-grid {
			display: grid;
			grid-template-columns: 1fr;
			gap: 24px;
		}

		@media(min-width:992px) {
			.form-grid {
				grid-template-columns: 400px 1fr;
			}
		}

		.form-group {
			margin-bottom: 14px;
		}

		.form-group label {
			display: block;
			margin-bottom: 6px;
			font-weight: 600;
			font-size: .84rem;
			color: var(--text);
		}

		.form-group label .req {
			color: #ef4444;
			margin-left: 2px;
		}

		.form-control {
			width: 100%;
			padding: 10px 13px;
			border-radius: 10px;
			border: 1.5px solid var(--border);
			font-family: Inter, sans-serif;
			font-size: .85rem;
			color: var(--text);
			background: #f9fafc;
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

		/* ── Map ── */
		.map-container {
			position: relative;
			height: 300px;
			border-radius: 12px;
			border: 1.5px solid var(--border);
			overflow: hidden;
		}

		#map {
			height: 100%;
			width: 100%;
			border-radius: 12px;
		}

		.leaflet-container {
			font-family: Inter, sans-serif;
		}

		#posInfo {
			margin-top: 8px;
			text-align: center;
			color: var(--text-light);
			font-size: .8rem;
		}

		/* ── Address autocomplete ── */
		.ac-wrap {
			position: relative;
		}

		.ac-dropdown {
			position: absolute;
			top: calc(100% + 4px);
			left: 0;
			right: 0;
			z-index: 99999;
			background: #fff;
			border: 1.5px solid var(--primary);
			border-radius: 12px;
			box-shadow: 0 12px 36px rgba(0, 0, 0, .14);
			overflow: hidden;
			max-height: 220px;
			overflow-y: auto;
		}

		.ac-item {
			padding: 10px 14px;
			cursor: pointer;
			border-bottom: 1px solid var(--border);
			transition: background .12s;
		}

		.ac-item:last-child {
			border-bottom: none;
		}

		.ac-item:hover,
		.ac-item.ac-active {
			background: #eff6ff;
		}

		.ac-item .ac-main {
			color: var(--text);
			font-size: .82rem;
			font-weight: 600;
		}

		.ac-item .ac-sub {
			color: var(--text-light);
			font-size: .70rem;
			margin-top: 2px;
		}

		/* ── Wing config ── */
		#structureSection {
			display: none;
			margin-top: 4px;
		}

		.wing-config-list {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.wing-config-row {
			background: #f8fafc;
			border: 1.5px solid var(--border);
			border-radius: 14px;
			padding: 16px 18px;
			transition: border-color .2s;
		}

		.wing-config-row:hover {
			border-color: var(--primary);
		}

		.wing-config-header {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-bottom: 14px;
		}

		.wing-badge {
			width: 28px;
			height: 28px;
			border-radius: 7px;
			background: #e0e7ff;
			color: #3730a3;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 800;
			font-size: .72rem;
			flex-shrink: 0;
		}

		.wing-config-title {
			font-weight: 700;
			font-size: .88rem;
			color: var(--text);
			flex: 1;
		}

		.wing-config-fields {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
			gap: 10px;
		}

		.wfld {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.wfld label {
			font-size: .7rem;
			font-weight: 600;
			color: var(--text-light);
		}

		.wfld input,
		.wfld select {
			padding: 8px 10px;
			border: 1.5px solid var(--border);
			border-radius: 8px;
			font-size: .82rem;
			font-family: Inter, sans-serif;
			background: #fff;
			color: var(--text);
			outline: none;
			transition: border-color .2s;
		}

		.wfld input:focus,
		.wfld select:focus {
			border-color: var(--primary);
		}

		.naming-preview {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: #e0e7ff;
			color: #3730a3;
			border-radius: 20px;
			padding: 2px 9px;
			font-size: .68rem;
			font-weight: 600;
			margin-top: 4px;
			font-family: monospace;
			letter-spacing: .02em;
		}

		.flat-count-preview {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: #d1fae5;
			color: #065f46;
			border-radius: 20px;
			padding: 2px 9px;
			font-size: .68rem;
			font-weight: 600;
			margin-top: 4px;
		}

		.total-preview-bar {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 10px;
			background: #eff6ff;
			border: 1px solid #bfdbfe;
			border-radius: 12px;
			padding: 12px 16px;
			margin-top: 14px;
		}

		.total-preview-bar .tp-item {
			text-align: center;
		}

		.total-preview-bar .tp-num {
			font-size: 1.4rem;
			font-weight: 800;
			color: var(--primary);
			line-height: 1;
		}

		.total-preview-bar .tp-lbl {
			font-size: .68rem;
			color: var(--text-light);
			font-weight: 500;
			margin-top: 2px;
		}

		.sample-flats {
			display: flex;
			flex-wrap: wrap;
			gap: 5px;
			margin-top: 8px;
		}

		.sample-flat {
			background: #e0f2fe;
			color: #0369a1;
			border: 1px solid #bae6fd;
			border-radius: 6px;
			padding: 2px 8px;
			font-size: .68rem;
			font-weight: 700;
			font-family: monospace;
		}

		.help-tip {
			background: #eff6ff;
			border: 1px solid #bfdbfe;
			border-radius: 10px;
			padding: 10px 14px;
			font-size: .78rem;
			color: #1d4ed8;
			margin-bottom: 14px;
			display: flex;
			gap: 8px;
			align-items: flex-start;
		}

		.help-tip i {
			margin-top: 1px;
			flex-shrink: 0;
		}

		.btn-register {
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			color: #fff;
			padding: 12px 20px;
			border-radius: 999px;
			border: 0;
			width: 100%;
			font-family: Inter, sans-serif;
			font-size: .9rem;
			font-weight: 700;
			cursor: pointer;
			transition: all .2s;
		}

		.btn-register:hover {
			transform: translateY(-1px);
			box-shadow: 0 6px 20px rgba(52, 152, 219, .4);
		}

		.btn-register:disabled {
			opacity: .6;
			cursor: not-allowed;
			transform: none;
		}

		.btn-outline {
			background: #fff;
			color: var(--primary);
			border: 1.5px solid var(--primary);
			padding: 10px 14px;
			border-radius: 999px;
			font-family: Inter, sans-serif;
			font-size: .84rem;
			font-weight: 600;
			cursor: pointer;
			transition: all .2s;
		}

		.btn-outline:hover {
			background: #eff6ff;
		}

		.error-text {
			color: #e74c3c;
			margin-bottom: 14px;
			background: #fee2e2;
			padding: 12px 16px;
			border-radius: 10px;
			font-size: .84rem;
		}

		@keyframes sp {
			to {
				transform: rotate(360deg);
			}
		}

		.spinner {
			display: inline-block;
			width: 16px;
			height: 16px;
			border: 2.5px solid rgba(255, 255, 255, .4);
			border-top-color: #fff;
			border-radius: 50%;
			animation: sp .7s linear infinite;
			vertical-align: middle;
			margin-right: 4px;
		}

		/* searching indicator */
		.ac-searching {
			padding: 10px 14px;
			color: var(--text-light);
			font-size: .80rem;
			font-style: italic;
		}
	</style>
</head>

<body>
	<div class="register-container">

		<div class="page-header">
			<h1><i class="fas fa-building" style="color:var(--primary);margin-right:8px;"></i>Register Your Society</h1>
			<p>Fill in the society details, configure wings &amp; floors — all flats will be auto-generated.</p>
		</div>

		<div class="steps">
			<div class="step-item active">
				<div class="step-circle">1</div>
				<span>Society Info</span>
			</div>
			<div class="step-line"></div>
			<div class="step-item active">
				<div class="step-circle">2</div>
				<span>Wing &amp; Floor Setup</span>
			</div>
			<div class="step-line"></div>
			<div class="step-item">
				<div class="step-circle">3</div>
				<span>Admin Account</span>
			</div>
		</div>

		<div class="form-card">

			<?php if (!empty($errors)): ?>
				<div class="error-text"><i class="fas fa-exclamation-circle"></i> <?= $errors ?></div>
			<?php endif; ?>

			<form method="post" action="<?= site_url('plan_controller/register_society') ?>" id="registerForm">

				<div class="form-grid">

					<!-- ══════════════ LEFT COLUMN ══════════════ -->
					<div>
						<div class="section-title">Society Details</div>

						<div class="form-group">
							<label for="societyName">Society Name <span class="req">*</span></label>
							<div style="display:flex;gap:8px;">
								<input type="text" id="societyName" name="societyName" required class="form-control"
									placeholder="e.g. Sunshine Apartments" value="<?= set_value('societyName') ?>">
								<button type="button" id="locateSocietyBtn" title="Search society on map"
									style="padding:10px 13px;border-radius:10px;border:1.5px solid var(--border);background:#f9fafc;cursor:pointer;color:var(--primary);flex-shrink:0;">
									<i class="fas fa-search-location"></i>
								</button>
							</div>
						</div>

						<div class="form-group">
							<label for="societyType">Society Type <span class="req">*</span></label>
							<select id="societyType" name="societyType" required class="form-control"
								onchange="handleTypeChange()">
								<option value="">Select type</option>
								<option value="flat" <?= set_select('societyType', 'flat') ?>>Flat</option>
								<option value="bungalow" <?= set_select('societyType', 'bungalow') ?>>Bungalow</option>
							</select>
						</div>

						<div id="dynamicFields"></div>

						<div class="form-group">
							<label for="totalFlats">Total Units / Flats <span class="req">*</span>
								<small style="font-weight:400;color:var(--text-light);">(auto-calculated below)</small>
							</label>
							<input type="number" id="totalFlats" name="totalFlats" required min="1" class="form-control"
								placeholder="Auto-filled when you configure wings"
								value="<?= set_value('totalFlats') ?>" readonly>
						</div>

						<!-- ── Address with autocomplete ── -->
						<div class="form-group">
							<label for="address">Address <span class="req">*</span></label>
							<div class="ac-wrap">
								<input type="text" id="address" name="address" required class="form-control"
									autocomplete="off" placeholder="Type to search address…"
									value="<?= set_value('address') ?>">
								<div id="addressDropdown" class="ac-dropdown" style="display:none;"></div>
							</div>
						</div>

						<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
							<div class="form-group">
								<label for="city">City <span class="req">*</span></label>
								<input type="text" id="city" name="city" required class="form-control"
									value="<?= set_value('city') ?>">
							</div>
							<div class="form-group">
								<label for="state">State <span class="req">*</span></label>
								<input type="text" id="state" name="state" required class="form-control"
									value="<?= set_value('state') ?>">
							</div>
						</div>

						<div class="form-group">
							<label for="pincode">Pincode <span class="req">*</span></label>
							<input type="text" id="pincode" name="pincode" required pattern="[0-9]{6}"
								class="form-control" value="<?= set_value('pincode') ?>">
						</div>

						<input type="hidden" name="lat" id="lat" value="<?= set_value('lat') ?>">
						<input type="hidden" name="lng" id="lng" value="<?= set_value('lng') ?>">

						<div style="display:flex;gap:10px;margin-top:16px;">
							<button type="button" id="locateBtn" class="btn-outline">
								<i class="fas fa-location-dot"></i> My Location
							</button>
							<button type="submit" class="btn-register" id="submitBtn">
								Continue to Admin Setup →
							</button>
						</div>

						<div style="text-align:center;margin-top:14px;color:#666;font-size:.82rem;">
							Already registered? <a href="<?= base_url('login') ?>"
								style="color:var(--primary);font-weight:600;">Login</a>
						</div>
					</div>

					<!-- ══════════════ RIGHT COLUMN ══════════════ -->
					<div>
						<div class="map-container">
							<div id="map"></div>
						</div>
						<div id="posInfo">📍 Click on map, search address, or use "My Location"</div>

						<!-- Wing Structure Section -->
						<div id="structureSection">
							<div class="section-title" style="margin-top:20px;">Wing &amp; Floor Configuration</div>
							<div class="help-tip">
								<i class="fas fa-lightbulb"></i>
								<span>Configure each wing below. All flats will be <strong>auto-generated</strong> after
									registration.
									Use <code>{W}-{F}{U}</code> as naming format → <strong>A-101, A-102…</strong></span>
							</div>
							<div class="wing-config-list" id="wingConfigList"></div>
							<div class="total-preview-bar" id="totalPreviewBar">
								<div class="tp-item">
									<div class="tp-num" id="previewWings">0</div>
									<div class="tp-lbl">Wings</div>
								</div>
								<div class="tp-item">
									<div class="tp-num" id="previewTotalFlats">0</div>
									<div class="tp-lbl">Total Flats</div>
								</div>
								<div class="tp-item">
									<div class="tp-num" id="previewFloors">0</div>
									<div class="tp-lbl">Avg Floors</div>
								</div>
								<div class="tp-item">
									<div class="tp-num" id="previewUPF">0</div>
									<div class="tp-lbl">Avg Units/Floor</div>
								</div>
							</div>
							<div style="margin-top:10px;">
								<div
									style="font-size:.7rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;">
									<i class="fas fa-th" style="color:var(--primary);margin-right:3px;"></i>Sample flat
									numbers
								</div>
								<div class="sample-flats" id="sampleFlats"></div>
							</div>
							<input type="hidden" name="wing_structure" id="wingStructureInput" value="">
						</div>
					</div>

				</div><!-- /.form-grid -->
			</form>
		</div>
	</div>

	<!-- Leaflet JS -->
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

	<script>
		/* ═══════════════════════════════════════════
		   MAP — Leaflet + Nominatim
		═══════════════════════════════════════════ */
		let map, marker;

		const latInput = document.getElementById('lat');
		const lngInput = document.getElementById('lng');
		const posInfo = document.getElementById('posInfo');
		const addressInput = document.getElementById('address');
		const cityInput = document.getElementById('city');
		const stateInput = document.getElementById('state');
		const pincodeInput = document.getElementById('pincode');
		const locateBtn = document.getElementById('locateBtn');
		const locateSocietyBtn = document.getElementById('locateSocietyBtn');
		const societyNameInput = document.getElementById('societyName');

		function setStatusText(lat, lng) {
			posInfo.textContent = `📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
		}

		function fillAddressParts(address = {}) {
			const city = address.city || address.town || address.village || address.hamlet || address.suburb || address.county || '';
			const state = address.state || address.region || '';
			const pincode = address.postcode || '';
			if (city) cityInput.value = city;
			if (state) stateInput.value = state;
			if (pincode) pincodeInput.value = pincode;
		}

		async function reverseGeocode(lat, lng) {
			try {
				const res = await fetch(
					'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1' +
					'&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng),
					{ headers: { 'Accept': 'application/json' } }
				);
				if (!res.ok) return;
				const data = await res.json();
				if (data && data.display_name) addressInput.value = data.display_name;
				if (data && data.address) fillAddressParts(data.address);
			} catch (err) {
				console.warn('Reverse geocode failed:', err);
			}
		}

		function setLocation(lat, lng, zoom = 16) {
			const ll = L.latLng(lat, lng);
			map.setView(ll, zoom);
			marker.setLatLng(ll);
			latInput.value = lat;
			lngInput.value = lng;
			setStatusText(lat, lng);
		}

		/* Set location AND fill address fields (no extra reverse-geocode needed) */
		function setLocationWithAddress(lat, lng, displayName, addressParts, zoom = 16) {
			setLocation(lat, lng, zoom);
			if (displayName) addressInput.value = displayName;
			if (addressParts) fillAddressParts(addressParts);
		}

		/* Search by society name (locate button next to name field) */
		async function searchByName(name) {
			try {
				const res = await fetch(
					'https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=1&q=' +
					encodeURIComponent(name),
					{ headers: { 'Accept': 'application/json' } }
				);
				if (!res.ok) { alert('Search failed'); return; }
				const results = await res.json();
				if (!results || !results.length) { alert('Location not found'); return; }
				const place = results[0];
				setLocationWithAddress(
					parseFloat(place.lat), parseFloat(place.lon),
					place.display_name, place.address, 16
				);
			} catch (err) {
				console.warn('Search error:', err);
				alert('Location search failed');
			}
		}

		function initMap() {
			const defaultCenter = [20.5937, 78.9629];
			map = L.map('map', { scrollWheelZoom: true }).setView(defaultCenter, 5);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
			}).addTo(map);

			marker = L.marker(defaultCenter, { draggable: true }).addTo(map);

			map.on('click', function (e) {
				setLocation(e.latlng.lat, e.latlng.lng, 16);
				reverseGeocode(e.latlng.lat, e.latlng.lng);
			});

			marker.on('dragend', function (e) {
				const ll = e.target.getLatLng();
				setLocation(ll.lat, ll.lng, 16);
				reverseGeocode(ll.lat, ll.lng);
			});

			/* Restore saved values on validation error */
			const savedLat = parseFloat(latInput.value);
			const savedLng = parseFloat(lngInput.value);
			if (!Number.isNaN(savedLat) && !Number.isNaN(savedLng)) {
				setLocation(savedLat, savedLng, 16);
			}

			/* My Location */
			locateBtn.addEventListener('click', function () {
				if (!navigator.geolocation) { alert('Geolocation not supported'); return; }
				navigator.geolocation.getCurrentPosition(
					function (pos) {
						setLocation(pos.coords.latitude, pos.coords.longitude, 16);
						reverseGeocode(pos.coords.latitude, pos.coords.longitude);
					},
					function () { alert('Unable to retrieve your location.'); }
				);
			});

			/* Search by society name */
			locateSocietyBtn.addEventListener('click', function () {
				const name = (societyNameInput.value || '').trim();
				if (name.length < 3) { alert('Enter society name first'); return; }
				searchByName(name);
			});
		}

		window.addEventListener('load', initMap);


		/* ═══════════════════════════════════════════
		   ADDRESS AUTOCOMPLETE (Nominatim)
		   — same pattern as the ride-booking view —
		═══════════════════════════════════════════ */
		(function () {
			const input = document.getElementById('address');
			const dropdown = document.getElementById('addressDropdown');
			let timer = null;
			let activeIdx = -1;

			function escHtml(s) {
				return String(s)
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;');
			}

			function hideDropdown() {
				dropdown.style.display = 'none';
				dropdown.innerHTML = '';
				activeIdx = -1;
			}

			function highlightItem(items, idx) {
				items.forEach(function (el, i) {
					el.classList.toggle('ac-active', i === idx);
				});
			}

			function renderDropdown(items) {
				dropdown.innerHTML = '';
				if (!items.length) { hideDropdown(); return; }

				items.forEach(function (item) {
					const div = document.createElement('div');
					div.className = 'ac-item';
					const parts = item.display_name.split(', ');
					const main = escHtml(parts.slice(0, 2).join(', '));
					const sub = escHtml(parts.slice(2).join(', '));
					div.innerHTML =
						'<div class="ac-main">' + main + '</div>' +
						(sub ? '<div class="ac-sub">' + sub + '</div>' : '');

					div.addEventListener('mousedown', function (e) {
						e.preventDefault();
						/* Fill the input immediately */
						input.value = item.display_name;
						hideDropdown();

						/* Move map + fill city / state / pincode */
						const lat = parseFloat(item.lat);
						const lng = parseFloat(item.lon);
						setLocationWithAddress(lat, lng, item.display_name, item.address, 16);
					});

					dropdown.appendChild(div);
				});

				dropdown.style.display = 'block';
			}

			function showSearching() {
				dropdown.innerHTML = '<div class="ac-searching"><i class="fas fa-circle-notch fa-spin" style="color:var(--primary);margin-right:6px;"></i>Searching…</div>';
				dropdown.style.display = 'block';
			}

			input.addEventListener('input', function () {
				clearTimeout(timer);
				const val = this.value.trim();
				activeIdx = -1;

				if (val.length < 3) { hideDropdown(); return; }

				showSearching();

				timer = setTimeout(function () {
					fetch(
						'https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=6&q=' +
						encodeURIComponent(val),
						{ headers: { 'Accept': 'application/json' } }
					)
						.then(function (r) { return r.json(); })
						.then(function (data) { renderDropdown(data || []); })
						.catch(function () { hideDropdown(); });
				}, 350);
			});

			input.addEventListener('keydown', function (e) {
				const items = dropdown.querySelectorAll('.ac-item');
				if (!items.length) return;

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					activeIdx = Math.min(activeIdx + 1, items.length - 1);
					highlightItem(items, activeIdx);
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					activeIdx = Math.max(activeIdx - 1, 0);
					highlightItem(items, activeIdx);
				} else if (e.key === 'Enter') {
					e.preventDefault();
					if (activeIdx >= 0 && items[activeIdx]) {
						items[activeIdx].dispatchEvent(new MouseEvent('mousedown'));
					}
				} else if (e.key === 'Escape') {
					hideDropdown();
				}
			});

			/* Close dropdown when clicking outside */
			document.addEventListener('click', function (e) {
				if (!input.contains(e.target) && !dropdown.contains(e.target)) {
					hideDropdown();
				}
			});
		})();


		/* ═══════════════════════════════════════════
		   WING CONFIGURATION
		═══════════════════════════════════════════ */
		const FT = ['1BHK', '2BHK', '3BHK', '4BHK', 'Penthouse', 'Shop', 'Office'];
		const LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		let wingRows = [];

		function makeFlatNo(prefix, fmt, floor, unit) {
			const f = floor === 0 ? 'G' : String(floor);
			const u = String(unit).padStart(2, '0');
			return fmt.replace('{W}', prefix).replace('{F}', f).replace('{U}', u)
				.replace('{FU}', f + u).toUpperCase();
		}

		function calcWingTotal(floors, upf, gf) {
			return (parseInt(floors) || 0) * (parseInt(upf) || 0) + (gf ? (parseInt(upf) || 0) : 0);
		}

		function renderWingRows(count) {
			const list = document.getElementById('wingConfigList');
			list.innerHTML = '';
			while (wingRows.length < count) {
				const i = wingRows.length;
				wingRows.push({
					wingName: LETTERS[i] || ('Wing' + (i + 1)),
					prefix: LETTERS[i] || ('W' + (i + 1)),
					floors: 4, upf: 4, type: '2BHK', gf: false, fmt: '{W}-{F}{U}',
				});
			}
			wingRows.length = count;
			wingRows.forEach(function (w, i) {
				const row = document.createElement('div');
				row.className = 'wing-config-row';
				row.id = 'wcr-' + i;
				const ftOpts = FT.map(f => `<option${f === w.type ? ' selected' : ''}>${f}</option>`).join('');
				const sample = makeFlatNo(w.prefix, w.fmt, 1, 1);
				const total = calcWingTotal(w.floors, w.upf, w.gf);
				row.innerHTML = `
			<div class="wing-config-header">
				<div class="wing-badge">${w.prefix || '?'}</div>
				<div class="wing-config-title">Wing ${w.wingName}</div>
			</div>
			<div class="wing-config-fields">
				<div class="wfld"><label>Wing Name</label>
					<input type="text" value="${w.wingName}" maxlength="10"
						oninput="updateWingField(${i},'wingName',this.value);
						this.closest('.wing-config-row').querySelector('.wing-config-title').textContent='Wing '+this.value;"></div>
				<div class="wfld"><label>Flat Prefix</label>
					<input type="text" value="${w.prefix}" maxlength="10"
						oninput="updateWingField(${i},'prefix',this.value.toUpperCase());
						this.value=this.value.toUpperCase();
						this.closest('.wing-config-row').querySelector('.wing-badge').textContent=this.value||'?';
						refreshSample(${i});"></div>
				<div class="wfld"><label>No. of Floors</label>
					<input type="number" value="${w.floors}" min="1" max="50"
						oninput="updateWingField(${i},'floors',this.value); refreshWingTotals();">
					<div class="flat-count-preview" id="wc-total-${i}">~${total} flats</div></div>
				<div class="wfld"><label>Units / Floor</label>
					<input type="number" value="${w.upf}" min="1" max="50"
						oninput="updateWingField(${i},'upf',this.value); refreshWingTotals();"></div>
				<div class="wfld"><label>Flat Type</label>
					<select oninput="updateWingField(${i},'type',this.value)">${ftOpts}</select></div>
				<div class="wfld"><label>Naming Format</label>
					<input type="text" value="${w.fmt}" maxlength="30"
						oninput="updateWingField(${i},'fmt',this.value); refreshSample(${i});">
					<div class="naming-preview" id="wc-sample-${i}">${sample}</div></div>
				<div class="wfld" style="justify-content:flex-end;padding-top:12px;">
					<label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
						<input type="checkbox" ${w.gf ? 'checked' : ''}
							onchange="updateWingField(${i},'gf',this.checked); refreshWingTotals();"> Ground Floor
					</label></div>
			</div>`;
				list.appendChild(row);
			});
			refreshWingTotals();
		}

		function updateWingField(i, field, val) {
			if (field === 'floors' || field === 'upf') val = parseInt(val) || 1;
			if (field === 'gf') val = !!val;
			wingRows[i][field] = val;
			refreshWingTotals();
		}

		function refreshSample(i) {
			const w = wingRows[i];
			const el = document.getElementById('wc-sample-' + i);
			if (el) el.textContent = makeFlatNo(w.prefix, w.fmt, 1, 1) + ', ' + makeFlatNo(w.prefix, w.fmt, 1, 2) + '…';
		}

		function refreshWingTotals() {
			let grandTotal = 0, totalFloors = 0, totalUPF = 0;
			wingRows.forEach(function (w, i) {
				const t = calcWingTotal(w.floors, w.upf, w.gf);
				grandTotal += t;
				totalFloors += parseInt(w.floors) || 0;
				totalUPF += parseInt(w.upf) || 0;
				const el = document.getElementById('wc-total-' + i);
				if (el) el.textContent = '~' + t + ' flats';
			});
			const n = wingRows.length || 1;
			document.getElementById('previewWings').textContent = wingRows.length;
			document.getElementById('previewTotalFlats').textContent = grandTotal;
			document.getElementById('previewFloors').textContent = Math.round(totalFloors / n);
			document.getElementById('previewUPF').textContent = Math.round(totalUPF / n);
			document.getElementById('totalFlats').value = grandTotal;

			const samples = document.getElementById('sampleFlats');
			samples.innerHTML = '';
			if (wingRows.length > 0) {
				const w = wingRows[0];
				const sf = w.gf ? 0 : 1;
				for (let fl = sf; fl <= Math.min(sf + 1, sf + parseInt(w.floors) - 1); fl++) {
					for (let u = 1; u <= Math.min(parseInt(w.upf), 4); u++) {
						const s = document.createElement('span');
						s.className = 'sample-flat';
						s.textContent = makeFlatNo(w.prefix, w.fmt, fl, u);
						samples.appendChild(s);
					}
				}
				if (wingRows.length > 1) {
					const sp = document.createElement('span');
					sp.className = 'sample-flat';
					sp.style.cssText = 'background:#e0e7ff;color:#3730a3;border-color:#c7d2fe;';
					sp.textContent = '+ more wings…';
					samples.appendChild(sp);
				}
			}

			document.getElementById('wingStructureInput').value = JSON.stringify(
				wingRows.map(function (w) {
					return {
						wing_name: w.wingName,
						wing_prefix: w.prefix,
						floors: parseInt(w.floors) || 1,
						units_per_floor: parseInt(w.upf) || 1,
						flat_type: w.type,
						has_ground_floor: w.gf ? 1 : 0,
						naming_format: w.fmt,
					};
				})
			);
		}

		function handleTypeChange() {
			const type = document.getElementById('societyType').value;
			const dynFlds = document.getElementById('dynamicFields');
			const strSec = document.getElementById('structureSection');

			if (!type) { dynFlds.innerHTML = ''; strSec.style.display = 'none'; wingRows = []; return; }

			if (type === 'bungalow') {
				dynFlds.innerHTML = `
			<div class="form-group">
				<label for="number_of_bungalows">Number of Bungalows <span class="req">*</span></label>
				<input type="number" id="number_of_bungalows" name="number_of_bungalows"
					required min="1" class="form-control"
					value="<?= set_value('number_of_bungalows') ?>"
					oninput="syncBungalows(this.value)">
			</div>`;
				strSec.style.display = 'none';
				document.getElementById('totalFlats').removeAttribute('readonly');
				document.getElementById('totalFlats').value = '<?= set_value('totalFlats') ?>';
				return;
			}

			dynFlds.innerHTML = `
		<div class="form-group">
			<label for="number_of_wings">Number of Wings <span class="req">*</span></label>
			<input type="number" id="number_of_wings" name="number_of_wings"
				required min="1" max="26" class="form-control"
				value="${wingRows.length || ''}"
				oninput="onWingCountChange(this.value)">
		</div>`;
			document.getElementById('totalFlats').setAttribute('readonly', 'readonly');

			const existing = parseInt(document.getElementById('number_of_wings')?.value) || 0;
			if (existing > 0) { strSec.style.display = 'block'; renderWingRows(existing); }
			else { strSec.style.display = 'none'; }
		}

		function onWingCountChange(val) {
			const n = parseInt(val) || 0;
			const strSec = document.getElementById('structureSection');
			if (n < 1 || n > 26) { strSec.style.display = 'none'; return; }
			strSec.style.display = 'block';
			renderWingRows(n);
		}

		function syncBungalows(val) {
			document.getElementById('totalFlats').value = parseInt(val) || '';
		}

		/* Form submit validation */
		document.getElementById('registerForm').addEventListener('submit', function (e) {
			if (!document.getElementById('lat').value || !document.getElementById('lng').value) {
				e.preventDefault();
				alert('Please select a location on the map, search an address, or use "My Location".');
				return;
			}

			const type = document.getElementById('societyType').value;

			if (type === 'bungalow') {
				const b = document.getElementById('number_of_bungalows');
				if (!b || parseInt(b.value) < 1) { e.preventDefault(); alert('Enter a valid number of bungalows.'); return; }
			} else if (type) {
				const wc = document.getElementById('number_of_wings');
				if (!wc || parseInt(wc.value) < 1) { e.preventDefault(); alert('Enter a valid number of wings.'); return; }
				if (wingRows.length === 0) { e.preventDefault(); alert('Please configure at least one wing.'); return; }
				for (let i = 0; i < wingRows.length; i++) {
					const w = wingRows[i];
					if (!w.wingName) { e.preventDefault(); alert(`Wing ${i + 1}: Name is required.`); return; }
					if (parseInt(w.floors) < 1) { e.preventDefault(); alert(`Wing ${w.wingName}: Floors must be ≥ 1.`); return; }
					if (parseInt(w.upf) < 1) { e.preventDefault(); alert(`Wing ${w.wingName}: Units/floor must be ≥ 1.`); return; }
				}
			}

			const btn = document.getElementById('submitBtn');
			btn.innerHTML = '<span class="spinner"></span> Saving…';
			btn.disabled = true;
		});

		document.addEventListener('DOMContentLoaded', function () {
			const type = document.getElementById('societyType').value;
			if (type) handleTypeChange();
		});
	</script>
</body>

</html>
