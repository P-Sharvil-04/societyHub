<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Sidebar</title>
	<!-- Fonts & Icons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<style>
		/* ================ SIDEBAR SUBMENU & CUSTOMIZATIONS ================ */

		.sidebar-header h2 {
			font-size: 1.4rem;
		}

		.user-avatar {
			width: 100px;
			height: 100px;
		}

		/* ── LOGO BLOCK ── */
		.sidebar-logo-block {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 24px 20px 20px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.08);
			background: linear-gradient(160deg, rgba(255, 255, 255, 0.03) 0%, transparent 100%);
			position: relative;
			overflow: hidden;
		}

		/* subtle radial glow behind icon */
		.sidebar-logo-block::before {
			content: '';
			position: absolute;
			top: -20px;
			left: 50%;
			transform: translateX(-50%);
			width: 200px;
			height: 180px;
			background: radial-gradient(circle, rgba(240, 192, 96, 0.07) 0%, transparent 70%);
			pointer-events: none;
		}

		.logo-svg-wrap {
			filter: drop-shadow(0 0 12px rgba(240, 192, 96, 0.3));
			margin-bottom: 12px;
		}

		.logo-society-name {
			font-family: 'Cinzel', serif;
			font-size: 1rem;
			font-weight: 700;
			color: #ffffff;
			letter-spacing: 0.07em;
			text-transform: uppercase;
			text-align: center;
			line-height: 1.3;
		}

		.logo-gold-bar {
			width: 48px;
			height: 1.5px;
			background: linear-gradient(90deg, transparent, #3498db, transparent);
			border-radius: 2px;
			margin: 8px auto 8px;
		}

		.logo-society-sub {
			font-size: 0.62rem;
			font-weight: 500;
			letter-spacing: 0.2em;
			text-transform: uppercase;
			color: #3498db;
			text-align: center;
		}

		.logo-role-badge {
			margin-top: 10px;
			font-size: 0.62rem;
			font-weight: 600;
			letter-spacing: 0.16em;
			text-transform: uppercase;
			color: #3498db;
			background: rgba(240, 192, 96, 0.1);
			border: 1px solid rgba(240, 192, 96, 0.2);
			padding: 3px 12px;
			border-radius: 20px;
		}

		/* ── SUBMENU SECTION STYLES ── */
		.menu-section-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 8px 12px;
			cursor: pointer;
			border-radius: 8px;
			transition: background 0.2s;
			user-select: none;
		}

		.menu-section-header:hover {
			background: rgba(255, 255, 255, 0.05);
		}

		.menu-section-arrow {
			color: rgba(255, 255, 255, 0.5);
			font-size: 0.8rem;
			transition: transform 0.2s;
		}

		.menu-section.collapsed .menu-section-arrow {
			transform: rotate(-90deg);
		}

		.menu-section.collapsed .menu-items {
			display: none;
		}

		.menu-items {
			overflow: hidden;
			transition: max-height 0.3s ease;
		}

		.menu-item.has-submenu {
			justify-content: space-between;
			cursor: pointer;
		}

		.submenu {
			margin-top: 6px;
			margin-left: 6px;
			border-left: 1px solid rgba(255, 255, 255, 0.04);
			padding-left: 8px;
		}

		.submenu a {
			display: flex;
			padding: 8px 12px;
			margin: 4px 0;
			border-radius: 8px;
			align-items: center;
			gap: 12px;
			font-size: 0.9rem;
			color: rgba(255, 255, 255, 0.8);
			text-decoration: none;
		}

		.submenu a .mini {
			width: 18px;
			text-align: center;
			font-size: 0.9rem;
		}

		.submenu a:hover {
			background: rgba(255, 255, 255, 0.05);
			color: white;
		}

		.submenu.collapsed {
			display: none;
		}

		.member-toggle {
			cursor: pointer;
			justify-content: space-between;
		}

		.member-arrow {
			margin-left: auto;
			font-size: 0.8rem;
			transition: transform 0.3s ease;
		}

		.member-submenu {
			display: none;
			padding-left: 34px;
			margin-top: 6px;
		}

		.member-submenu.open {
			display: block;
		}

		.member-submenu .submenu-item {
			display: block;
			padding: 10px 14px;
			margin: 4px 0;
			font-size: 0.85rem;
			color: rgba(255, 255, 255, 0.75);
			text-decoration: none;
			border-radius: 8px;
			transition: all 0.2s;
		}

		.member-submenu .submenu-item:hover {
			background: rgba(255, 255, 255, 0.1);
			color: #fff;
		}

		.member-submenu .submenu-item.active {
			background: var(--primary);
			color: #fff;
		}

		.member-toggle.active {
			background: var(--primary);
			color: #fff;
		}

		.member-toggle.active i {
			color: #fff;
		}

		.sidebar-logo-block {
			flex-shrink: 0;
			min-height: 170px;
		}

		.sidebar {
			display: flex;
			flex-direction: column;
			height: 100vh;
		}

		.sidebar-menu {
			flex: 1;
			overflow-y: auto;
			padding-bottom: 10px;
		}

		.menu-items {
			overflow: hidden;
			transition: max-height 0.3s ease;
			max-height: 2000px;
		}

		.menu-section.collapsed .menu-items {
			max-height: 0;
		}

		.menu-item {
			display: flex;
			align-items: center;
			gap: 12px;
		}
	</style>
</head>

<body>
	<?php
	$CI =& get_instance();

	// =====================================================================
	// STEP 1 — Read role from session
	// =====================================================================
	$role = $CI->session->userdata('role_name') ?? $CI->session->userdata('role') ?? null;

	if (is_string($role)) {
		$role = strtolower(trim($role));
		$role = str_replace(' ', '_', $role);
	}

	echo '<!-- role: ' . htmlspecialchars(is_array($role) ? json_encode($role) : (string) $role) . ' -->';

	$activePage = $activePage ?? null;
	$activeSubPage = $activeSubPage ?? null;

	// =====================================================================
	// STEP 2 — Load menu & permission configs
	// =====================================================================
	$CI->config->load('menus', TRUE);
	$CI->config->load('permissions', TRUE);

	$menus = $CI->config->item('menus', 'menus') ?: [];
	$permissions = $CI->config->item('permissions', 'permissions') ?: [];

	$allowedMenus = $permissions[$role] ?? [];

	$hasWildcard = ($allowedMenus === '*')
		|| (is_array($allowedMenus) && in_array('*', $allowedMenus, true));

	if ($hasWildcard) {
		$allKeys = [];
		foreach ($menus as $mKey => $m) {
			$allKeys[] = $mKey;
			if (!empty($m['children']) && is_array($m['children'])) {
				foreach ($m['children'] as $cKey => $c) {
					$allKeys[] = $cKey;
				}
			}
		}
		$allowedMenus = $allKeys;
	}

	if (!is_array($allowedMenus)) {
		$allowedMenus = (array) $allowedMenus;
	}

	// =====================================================================
	// STEP 3 — Fetch society name FROM DATABASE based on logged-in user
	//
	//  How it works:
	//  1. First checks if society data is already cached in session
	//     (avoids a DB hit on every page load).
	//  2. If not cached, reads the logged-in user's `society_id` from
	//     session, then queries the `societies` table for that row.
	//  3. Stores the result back in session for subsequent requests.
	//  4. Falls back to config / hard-coded defaults if nothing is found.
	//
	//  Adjust table / column names below to match YOUR schema:
	//    • societies table  → 'societies'
	//    • name column      → 'society_name'
	//    • tagline column   → 'society_tagline'   (or 'tagline', 'address', etc.)
	//    • PK column        → 'id'
	//
	//  If your users table stores society_id directly, also adjust the
	//  session key used to read it ('society_id' by default).
	// =====================================================================
	
	$societyName = $CI->session->userdata('society_name');
	$societyTagline = $CI->session->userdata('society_tagline');

	// Fallback: if session is empty (e.g. older session before this change)
	// re-query DB once and re-cache
	if (empty($societyName)) {
		$societyId = $CI->session->userdata('society_id');

		if (!empty($societyId)) {
			if (!isset($CI->db) || $CI->db === false) {
				$CI->load->database();
			}

			$societyRow = $CI->db
				->select('name AS society_name, society_tagline')
				->from('societies')
				->where('id', $societyId)
				->limit(1)
				->get()
				->row();

			if ($societyRow) {
				$societyName = $societyRow->society_name ?? '';
				$societyTagline = $societyRow->society_tagline ?? '';

				$CI->session->set_userdata([
					'society_name' => $societyName,
					'society_tagline' => $societyTagline,
				]);
			}
		}

		// Hard fallback if DB also has nothing
		if (empty($societyName)) {
			$societyName = 'Your Society Name';
			$societyTagline = 'Residential Society';
		}
	}

	$societyName = htmlspecialchars((string) $societyName);
	$societyTagline = htmlspecialchars((string) $societyTagline);
	$roleLabel = $role
		? ucwords(str_replace('_', ' ', is_array($role) ? implode(', ', $role) : $role))
		: '';
	?>

	<!-- Overlay for mobile -->
	<div class="overlay" id="overlay"></div>

	<div class="sidebar" id="sidebar">

		<!-- ══════════════ LOGO BLOCK ══════════════ -->
		<div class="sidebar-logo-block">

			<!-- Building / Society SVG Logo -->
			<div class="logo-svg-wrap">
				<svg width="70" height="70" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
					<!-- outer ring -->
					<circle cx="36" cy="36" r="34" stroke="url(#sb_ringGrad)" stroke-width="1.2" opacity="0.55" />

					<!-- building body -->
					<rect x="22" y="24" width="28" height="28" rx="2" fill="url(#sb_buildGrad)" opacity="0.97" />

					<!-- roof -->
					<path d="M36 10 L52 24 H20 Z" fill="url(#sb_roofGrad)" />

					<!-- window row 1 -->
					<rect x="26" y="28" width="6" height="6" rx="1" fill="rgba(255,255,255,0.14)" />
					<rect x="34" y="28" width="6" height="6" rx="1" fill="rgba(255,255,255,0.14)" />
					<rect x="42" y="28" width="4" height="6" rx="1" fill="rgba(255,255,255,0.14)" />

					<!-- window row 2 -->
					<rect x="26" y="36" width="6" height="6" rx="1" fill="rgba(255,255,255,0.14)" />
					<rect x="34" y="36" width="6" height="6" rx="1" fill="rgba(255,255,255,0.14)" />
					<rect x="42" y="36" width="4" height="6" rx="1" fill="rgba(255,255,255,0.14)" />

					<!-- door -->
					<rect x="31" y="43" width="10" height="9" rx="1" fill="url(#sb_doorGrad)" />

					<!-- gold star at peak -->
					<polygon points="36,6 37.2,9 40.5,9 37.9,11 38.9,14.2 36,12.4 33.1,14.2 34.1,11 31.5,9 34.8,9"
						fill="#3498db" opacity="0.95" />

					<defs>
						<linearGradient id="sb_ringGrad" x1="0" y1="0" x2="72" y2="72" gradientUnits="userSpaceOnUse">
							<stop offset="0%" stop-color="#3498db" />
							<stop offset="100%" stop-color="rgba(240,192,96,0.05)" />
						</linearGradient>
						<linearGradient id="sb_buildGrad" x1="22" y1="24" x2="50" y2="52"
							gradientUnits="userSpaceOnUse">
							<stop offset="0%" stop-color="#2a2a4e" />
							<stop offset="100%" stop-color="#1a1a32" />
						</linearGradient>
						<linearGradient id="sb_roofGrad" x1="20" y1="10" x2="52" y2="24" gradientUnits="userSpaceOnUse">
							<stop offset="0%" stop-color="#3498db" />
							<stop offset="100%" stop-color="#2c7fb7" />
						</linearGradient>
						<linearGradient id="sb_doorGrad" x1="31" y1="43" x2="41" y2="52" gradientUnits="userSpaceOnUse">
							<stop offset="0%" stop-color="#3498db" stop-opacity="0.45" />
							<stop offset="100%" stop-color="#2c7fb7" stop-opacity="0.25" />
						</linearGradient>
					</defs>
				</svg>
			</div>

			<!-- Society Name loaded from DB / session -->
			<div class="logo-society-name"><?= $societyName ?></div>
			<div class="logo-gold-bar"></div>
			<div class="logo-society-sub"><?= $societyTagline ?></div>

			<!-- Role badge -->
			<?php if ($roleLabel): ?>
				<div class="logo-role-badge"><?= htmlspecialchars($roleLabel) ?></div>
			<?php endif; ?>
		</div>
		<!-- ══════════════ END LOGO BLOCK ══════════════ -->

		<div class="sidebar-menu">
			<!-- MAIN Section (collapsible) -->
			<div class="menu-section" id="section-main">
				<div class="menu-section-header" onclick="toggleSection('main')">
					<span class="menu-section-title">MAIN</span>
					<i class="fas fa-chevron-down menu-section-arrow"></i>
				</div>
				<div class="menu-items">
					<?php if (has_permission('super_admin')): ?>
					<?php endif; ?>

					<?php if (in_array('dashboard', $allowedMenus)): ?>
						<a href="<?= base_url('dashboard') ?>"
							class="menu-item <?= ($activePage == 'dashboard') ? 'active' : '' ?>">
							<i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
						</a>
					<?php endif; ?>
					<?php if (in_array('manage_member_view', $allowedMenus)): ?>
						<a href="<?= base_url('manage_member') ?>"
							class="menu-item <?= ($activePage == 'manage_member') ? 'active' : '' ?>">
							<i class="fas fa-users"></i>
							<span>members</span>
						</a>
					<?php endif; ?>
					<!-- <?php if (in_array('manage_member', $allowedMenus)): ?>
						<?php $memberActive = ($activePage === 'manage_member'); ?>
						<div class="menu-item member-toggle <?= $memberActive ? 'active' : '' ?>"
							onclick="toggleMemberMenu()">
							<i class="fas fa-users"></i>
							<span><?= $menus['manage_member']['title'] ?></span>
							<i class="fas fa-chevron-down member-arrow"></i>
						</div>
						<div class="member-submenu" id="memberSubmenu">
							<?php if (in_array('manage_member_view', $allowedMenus)): ?>
								<a href="<?= base_url($menus['manage_member']['children']['manage_member_view']['url']) ?>"
									class="submenu-item <?= ($activeSubPage == 'view') ? 'active' : '' ?>">
									<?= $menus['manage_member']['children']['manage_member_view']['title'] ?>
								</a>
							<?php endif; ?>
							<?php if (in_array('manage_member_add', $allowedMenus)): ?>
								<a href="<?= base_url($menus['manage_member']['children']['manage_member_add']['url']) ?>"
									class="submenu-item <?= ($activeSubPage == 'add') ? 'active' : '' ?>">
									<?= $menus['manage_member']['children']['manage_member_add']['title'] ?>
								</a>
							<?php endif; ?>
							<?php if (in_array('manage_member_assign_role', $allowedMenus)): ?>
								<a href="<?= base_url($menus['manage_member']['children']['manage_member_assign_role']['url']) ?>"
									class="submenu-item <?= ($activeSubPage == 'role') ? 'active' : '' ?>">
									<?= $menus['manage_member']['children']['manage_member_assign_role']['title'] ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?> -->

					<?php if (in_array('staff', $allowedMenus)): ?>
						<a href="<?= base_url('staff') ?>"
							class="menu-item <?= ($activePage == 'staff') ? 'active' : '' ?>">
							<i class="fas fa-user-tie"></i><span>Staff</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('payments', $allowedMenus)): ?>
						<a href="<?= base_url('payments') ?>"
							class="menu-item <?= ($activePage == 'payments') ? 'active' : '' ?>">
							<i class="fas fa-credit-card"></i><span>Payments</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('complaints', $allowedMenus)): ?>
						<a href="<?= base_url('complaints') ?>"
							class="menu-item <?= ($activePage == 'complaints') ? 'active' : '' ?>">
							<i class="fas fa-exclamation-circle"></i><span>Complaints</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('notices', $allowedMenus)): ?>
						<a href="<?= base_url('notices') ?>"
							class="menu-item <?= ($activePage == 'notices') ? 'active' : '' ?>">
							<i class="fas fa-bell"></i><span>Notices</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('aminities', $allowedMenus)): ?>
						<a href="<?= base_url('aminities') ?>"
							class="menu-item <?= ($activePage == 'aminities') ? 'active' : '' ?>">
							<i class="fas fa-swimming-pool"></i><span>Amenities</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('visitors', $allowedMenus)): ?>
						<a href="<?= base_url('visitors') ?>"
							class="menu-item <?= ($activePage == 'visitors') ? 'active' : '' ?>">
							<i class="fas fa-door-open"></i><span>Visitors</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('reports', $allowedMenus)): ?>
						<a href="<?= base_url('reports') ?>"
							class="menu-item <?= ($activePage == 'reports') ? 'active' : '' ?>">
							<i class="fas fa-chart-line"></i><span>Reports</span>
						</a>
					<?php endif; ?>
					<?php if (in_array('society_setup', $allowedMenus)): ?>
						<a href="<?= base_url('society_setup') ?>"
							class="menu-item <?= ($activePage == 'society_setup') ? 'active' : '' ?>">
							<i class="fas fa-cogs"></i><span>society setup</span>
						</a>
					<?php endif; ?>
					<?php if (in_array('settings', $allowedMenus)): ?>
						<a href="<?= base_url('settings') ?>"
							class="menu-item <?= ($activePage == 'settings') ? 'active' : '' ?>">
							<i class="fas fa-cogs"></i><span>Settings</span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- MANAGEMENT Section (collapsible) -->
			<div class="menu-section" id="section-management">
				<div class="menu-section-header" onclick="toggleSection('management')">
					<span class="menu-section-title">MANAGEMENT</span>
					<i class="fas fa-chevron-down menu-section-arrow"></i>
				</div>
				<div class="menu-items">
					<?php if (in_array('flat_unit', $allowedMenus)): ?>
						<a href="<?= base_url('flat_unit') ?>"
							class="menu-item <?= ($activePage == 'flat_unit') ? 'active' : '' ?>">
							<i class="fas fa-building"></i><span>Flat / Unit Management</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('parking', $allowedMenus)): ?>
						<a href="<?= base_url('parking') ?>"
							class="menu-item <?= ($activePage == 'parking') ? 'active' : '' ?>">
							<i class="fas fa-parking"></i><span>Parking</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('documents', $allowedMenus)): ?>
						<a href="<?= base_url('documents') ?>"
							class="menu-item <?= ($activePage == 'documents') ? 'active' : '' ?>">
							<i class="fas fa-folder"></i><span>Documents</span>
						</a>
					<?php endif; ?>

					<?php if (in_array('events', $allowedMenus)): ?>
						<a href="<?= base_url('events') ?>"
							class="menu-item <?= ($activePage == 'events_booking') ? 'active' : '' ?>">
							<i class="fas fa-phone-alt"></i><span>Events &amp; Booking</span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- INSIGHTS Section (collapsible) -->
			<div class="menu-section" id="section-insights">
				<div class="menu-section-header" onclick="toggleSection('insights')">
					<span class="menu-section-title">INSIGHTS</span>
					<i class="fas fa-chevron-down menu-section-arrow"></i>
				</div>
				<div class="menu-items">
					<?php if (in_array('ai_insights', $allowedMenus)): ?>
						<a href="<?= base_url('ai_insights') ?>"
							class="menu-item <?= ($activePage == 'ai_insights') ? 'active' : '' ?>">
							<i class="fas fa-robot"></i><span>AI Insights</span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div style="padding: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
			<a href="<?= base_url('logout') ?>" class="menu-item logout-item">
				<i class="fas fa-sign-out-alt"></i><span>Logout</span>
			</a>
		</div>
	</div>

	<script>
		function toggleSection(sectionId) {
			const section = document.getElementById('section-' + sectionId);
			const isCollapsed = section.classList.contains('collapsed');
			if (isCollapsed) {
				section.classList.remove('collapsed');
				localStorage.setItem('sidebar_section_' + sectionId, 'expanded');
			} else {
				section.classList.add('collapsed');
				localStorage.setItem('sidebar_section_' + sectionId, 'collapsed');
			}
		}

		function restoreSectionStates() {
			['main', 'management', 'insights'].forEach(id => {
				const section = document.getElementById('section-' + id);
				if (!section) return;
				const state = localStorage.getItem('sidebar_section_' + id);
				if (state === 'collapsed') {
					section.classList.add('collapsed');
				} else {
					section.classList.remove('collapsed');
				}
			});
		}

		function toggleMemberMenu() {
			const submenu = document.getElementById('memberSubmenu');
			const arrow = document.querySelector('.member-arrow');
			if (submenu.classList.contains('open')) {
				submenu.classList.remove('open');
				arrow.style.transform = 'rotate(0deg)';
				localStorage.setItem('memberMenu', 'closed');
			} else {
				submenu.classList.add('open');
				arrow.style.transform = 'rotate(180deg)';
				localStorage.setItem('memberMenu', 'open');
			}
		}

		function toggleSubmenu(name) {
			const submenu = document.getElementById('submenu-' + name);
			if (!submenu) return;
			if (submenu.classList.contains('collapsed')) {
				submenu.classList.remove('collapsed');
				document.cookie = 'submenu_' + name + '=expanded; path=/';
			} else {
				submenu.classList.add('collapsed');
				document.cookie = 'submenu_' + name + '=collapsed; path=/';
			}
		}

		function expandSectionWithActiveItem() {
			document.querySelectorAll('.menu-section').forEach(section => {
				const activeItems = section.querySelectorAll('.menu-item.active, .submenu a.active');
				if (activeItems.length > 0) {
					section.classList.remove('collapsed');
					const id = section.id.replace('section-', '');
					localStorage.setItem('sidebar_section_' + id, 'expanded');
				}
			});
		}

		document.addEventListener('DOMContentLoaded', function () {
			restoreSectionStates();
			expandSectionWithActiveItem();

			// Auto-open member submenu if a child is active
			const activeSub = document.querySelector('.submenu-item.active');
			if (activeSub) {
				const submenu = document.getElementById('memberSubmenu');
				const arrow = document.querySelector('.member-arrow');
				const parent = document.querySelector('.member-toggle');
				submenu.classList.add('open');
				arrow.style.transform = 'rotate(180deg)';
				parent.classList.add('active');
			}
		});
	</script>
</body>

</html>
