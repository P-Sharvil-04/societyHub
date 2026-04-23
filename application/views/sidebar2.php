<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Sidebar</title>
	<!-- Fonts & Icons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet">
	<style>
		/* === SIDEBAR CSS (enhanced with submenu support) === */
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Inter', 'Poppins', sans-serif;
		}

		:root {
			--primary: #3498db;
			--primary-dark: #2980b9;
			--secondary: #2c3e50;
			--success: #27ae60;
			--warning: #f39c12;
			--danger: #e74c3c;
			--info: #4299e1;
			--light-bg: #f8f9fa;
			--border: #e1e8ed;
			--text-dark: #2c3e50;
			--text-light: #7f8c8d;
			--shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
			--shadow-lg: 0 4px 20px rgba(0, 0, 0, 0.12);
			--card-bg: #fff;
			--sidebar-bg: #2c3e50;
			--sidebar-text: #fff;
			--header-bg: #fff;
		}

		body.dark-mode {
			--light-bg: #1a202c;
			--border: #4a5568;
			--text-dark: #f7fafc;
			--text-light: #e2e8f0;
			--card-bg: #2d3748;
			--sidebar-bg: #1a202c;
			--sidebar-text: #f7fafc;
			--header-bg: #2d3748;
			--primary: #60a5fa;
			--primary-dark: #3b82f6;
			--success: #4ade80;
			--warning: #fbbf24;
			--danger: #f87171;
			--info: #60a5fa;
		}

		body {
			display: flex;
			min-height: 100vh;
			background: var(--light-bg);
			color: var(--text-dark);
			overflow-x: hidden;
			width: 100%;
			font-size: 14px;
		}

		.overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			z-index: 150;
			display: none;
			opacity: 0;
			transition: opacity 0.3s;
		}

		.overlay.active {
			display: block;
			opacity: 1;
		}

		.sidebar {
			width: 280px;
			background: var(--sidebar-bg);
			color: var(--sidebar-text);
			display: flex;
			flex-direction: column;
			transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s;
			height: 100vh;
			position: fixed;
			left: 0;
			top: 0;
			z-index: 200;
			box-shadow: var(--shadow-lg);
			overflow-y: auto;
			overflow-x: hidden;
			transform: translateX(-100%);
			/* mobile hidden by default */
		}

		.sidebar.active {
			transform: translateX(0);
		}

		@media (min-width: 769px) {
			.sidebar {
				transform: translateX(0);
				width: 280px;
			}

			.sidebar.collapsed {
				width: 80px;
			}

			.sidebar.collapsed .sidebar-header h2,
			.sidebar.collapsed .menu-item span,
			.sidebar.collapsed .menu-section-title,
			.sidebar.collapsed .user-info-sidebar,
			.sidebar.collapsed .upgrade-card {
				display: none;
			}

			.sidebar.collapsed .menu-item {
				justify-content: center;
				padding: 15px 0;
			}

			.sidebar.collapsed .menu-item i {
				margin: 0;
				font-size: 1.3rem;
			}
		}

		.sidebar-header {
			padding: 24px 20px;
			display: flex;
			align-items: center;
			gap: 12px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.1);
		}

		.logo-icon {
			width: 40px;
			height: 40px;
			background: var(--primary);
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			font-size: 1.2rem;
			flex-shrink: 0;
		}

		.sidebar-header h2 {
			font-size: 1.4rem;
			font-weight: 700;
			color: var(--sidebar-text);
			white-space: nowrap;
		}

		.user-profile-sidebar {
			padding: 24px 20px;
			display: flex;
			align-items: center;
			gap: 12px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.1);
		}

		.user-avatar {
			width: 100px;
			height: 100px;
			border-radius: 12px;
			background: var(--primary);
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			font-size: 1.2rem;
			font-weight: 600;
			flex-shrink: 0;
		}

		.user-info-sidebar h4 {
			font-weight: 700;
			font-size: 0.95rem;
			color: var(--sidebar-text);
			margin-bottom: 4px;
			white-space: nowrap;
		}

		.user-info-sidebar p {
			font-size: 0.75rem;
			color: rgba(255, 255, 255, 0.7);
			white-space: nowrap;
		}

		.sidebar-menu {
			padding: 20px;
			flex: 1;
		}

		/* === Submenu / collapsible section styles === */
		.menu-section {
			margin-bottom: 20px;
		}

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

		.menu-section-title {
			font-size: 0.7rem;
			font-weight: 700;
			color: rgba(255, 255, 255, 0.6);
			text-transform: uppercase;
			letter-spacing: 1px;
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

		.menu-item {
			padding: 12px 16px;
			margin: 4px 0;
			border-radius: 10px;
			display: flex;
			align-items: center;
			gap: 14px;
			color: rgba(255, 255, 255, 0.8);
			text-decoration: none;
			transition: all 0.2s;
			font-weight: 500;
			font-size: 0.95rem;
			white-space: nowrap;
		}

		.menu-item i {
			width: 20px;
			text-align: center;
			color: rgba(255, 255, 255, 0.6);
			flex-shrink: 0;
		}

		.menu-item:hover {
			background: rgba(255, 255, 255, 0.1);
			color: white;
			transform: translateX(3px);
		}

		.menu-item.active {
			background: var(--primary);
			color: white;
		}

		.logout-item {
			color: var(--danger) !important;
		}

		/* Submenu specifics */
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
		}

		.submenu a .mini {
			width: 18px;
			text-align: center;
			font-size: 0.9rem;
		}

		.submenu a:hover {
			background: rgba(255, 255, 255, 0.05);
			color: white;
			transform: none;
		}

		.submenu.collapsed {
			display: none;
		}

		/* === Members Submenu === */
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

		/* === Active Members Parent Highlight === */
		.member-toggle.active {
			background: var(--primary);
			color: #fff;
		}

		.member-toggle.active i {
			color: #fff;
		}
	</style>
</head>

<body>
	<?php
	// Use CI instance to access session inside a view (safe)
	$CI =& get_instance();

	// Ensure session library is loaded (usually autoloaded, but safe to call)
	if (!isset($CI->session)) {
		$CI->load->library('session');
	}

	// session keys set at login controller:
	// 'type' => 'user' | 'member' | 'super_admin'
	// 'role' => 'admin' | 'secretary' | 'treasurer' | 'committee' | ...
	$type = $CI->session->userdata('type') ?? null;
	$role = $CI->session->userdata('role') ?? null;

	// Active page / subpage (controller should set these)
	$activePage = $activePage ?? null;           // e.g. 'manage_member'
	$activeSubPage = $activeSubPage ?? null;     // e.g. 'add' | 'list' | 'assign_role' | 'documents'
	
	// Permission map (edit to change who can see what)
	$permissions = [
		'dashboard' => ['user' => true, 'super_admin' => true, 'member' => ['secretary', 'treasurer', 'vice-chairman']],
		'manage_member' => ['user' => true, 'super_admin' => true, 'member' => ['chairman']],
		'manage_member_view' => ['user' => true, 'super_admin' => true, 'member' => ['chairman', 'secretary', 'committee']],
		'manage_member_add' => ['user' => true, 'super_admin' => true, 'member' => ['chairman']],
		'manage_member_assign_role' => ['user' => true, 'super_admin' => true, 'member' => ['chairman', 'secretary']],
		'manage_member_documents' => ['user' => true, 'super_admin' => true, 'member' => ['chairman', 'secretary']],

		'staff' => ['user' => true, 'super_admin' => true],
		'payments' => ['super_admin' => true, 'user' => true, 'member' => ['treasurer']],
		'complaints' => ['super_admin' => true, 'user' => true, 'member' => ['secretary', '', 'committee member']],
		'notices' => ['super_admin' => true, 'user' => true, 'member' => ['secretary', '']],
		'amenities' => ['super_admin' => true, 'user' => true],
		'visitors' => ['super_admin' => true, 'user' => true],
		'reports' => ['super_admin' => true, 'user' => true],
		'settings' => ['super_admin' => true, 'user' => true],
		// management sublinks
		'flat_unit' => ['super_admin' => true, 'user' => true],
		'parking' => ['super_admin' => true, 'user' => true],
		'documents' => ['super_admin' => true, 'user' => true],
		'emergency_contact' => ['super_admin' => true, 'user' => true],
		'vendor' => ['super_admin' => true, 'user' => true],
		'events' => ['super_admin' => true, 'user' => true, 'member' => ['committee', 'secretary', 'vice-chairman']],
		'ai_insights' => ['super_admin' => true, 'user' => true],
	];

	// Helper function — wrapped in function_exists to avoid redeclaration
	if (!function_exists('can_view_sidebar')) {
		function can_view_sidebar($key, $type, $role, $permissions)
		{
			if (!isset($permissions[$key]))
				return false;
			$perm = $permissions[$key];

			// Admin (user) check
			if (isset($perm['user']) && $perm['user'] === true && $type === 'user') {
				return true;
			}
			if (isset($perm['super_admin']) && $perm['super_admin'] === true && $type === 'super_admin') {
				return true;
			}
			// Member check
			if (isset($perm['member'])) {
				if ($perm['member'] === true && $type === 'member') {
					return true;
				}
				if (is_array($perm['member']) && $type === 'member' && $role !== null) {
					// If session stores role as array or string, handle both
					if (is_array($role)) {
						foreach ($role as $r) {
							if (in_array($r, $perm['member']))
								return true;
						}
					} else {
						if (in_array($role, $perm['member']))
							return true;
					}
				}
			}
			return false;
		}
	}
	?>

	<!-- Overlay for mobile -->
	<div class="overlay" id="overlay"></div>

	<div class="sidebar" id="sidebar">
		<div class="user-profile-sidebar">
			<div>
				<img src="<?= base_url('assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png') ?>" class="user-avatar"
					alt="avatar">
				<center>
					<?php if ($type || $role): ?>
						<p style="font-size:.7rem; color:rgba(255,255,255,.7); margin-top:6px;">
							<?= $role ? htmlspecialchars(ucfirst((is_array($role) ? implode(',', $role) : $role))) : '' ?>
						</p>
					<?php endif; ?>
				</center>
			</div>
			<div class="user-info-sidebar">
				<h4>Society </h4>
				<h4>Management</h4>
				<h4>System</h4>

			</div>
		</div>

		<div class="sidebar-menu">
			<!-- MAIN Section (collapsible) -->
			<div class="menu-section" id="section-main">
				<div class="menu-section-header" onclick="toggleSection('main')">
					<span class="menu-section-title">MAIN</span>
					<i class="fas fa-chevron-down menu-section-arrow"></i>
				</div>
				<div class="menu-items">
					<?php if (can_view_sidebar('dashboard', $type, $role, $permissions)): ?>
						<a href="<?= base_url('dashboard') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'dashboard') ? 'active' : '' ?>">
							<i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('manage_member', $type, $role, $permissions)): ?>
						<!-- Parent menu item with submenu -->
						<?php
						$memberActive = (isset($activePage) && $activePage === 'manage_member');
						?>

						<div class="menu-item member-toggle <?= $memberActive ? 'active' : '' ?>"
							onclick="toggleMemberMenu()">
							<i class="fas fa-users"></i>
							<span>Members</span>
							<i class="fas fa-chevron-down member-arrow"></i>
						</div>

						<div class="member-submenu" id="memberSubmenu">
							<a href="<?= base_url('manage_member') ?>"
								class="submenu-item <?= (isset($activeSubPage) && $activeSubPage == 'view') ? 'active' : '' ?>">
								View Members
							</a>

							<?php if (can_view_sidebar('manage_member', $type, $role, $permissions)): ?>
								<a href="<?= base_url('manage_member/add') ?>"
									class="submenu-item <?= (isset($activeSubPage) && $activeSubPage == 'add') ? 'active' : '' ?>">
									Add Member
								</a>
							<?php endif; ?>

							<a href="<?= base_url('manage_member/assign_role') ?>"
								class="submenu-item <?= (isset($activeSubPage) && $activeSubPage == 'role') ? 'active' : '' ?>">
								Assign Committee
							</a>
						</div>

						<!-- Submenu (show/hide JS)
						<div class="submenu <?=
							(isset($activePage) && $activePage == 'manage_member' && (
								$activeSubPage === 'add' ||
								$activeSubPage === 'list' ||
								$activeSubPage === 'assign_role' ||
								$activeSubPage === 'documents'
							)) ? '' : ((isset($_COOKIE['submenu_members']) && $_COOKIE['submenu_members'] == 'collapsed') ? 'collapsed' : '')
							?>" id="submenu-members">
							<?php if (can_view_sidebar('manage_member_view', $type, $role, $permissions)): ?>
								<a href="<?= base_url('manage_member') ?>"
									class="<?= ($activePage == 'manage_member' && ($activeSubPage == '' || $activeSubPage == 'list')) ? 'active' : '' ?>">
									<span class="mini"><i class="fas fa-list"></i></span><span>View Members</span>
								</a>
							<?php endif; ?>

							<?php if (can_view_sidebar('manage_member_add', $type, $role, $permissions)): ?>
								<a href="<?= base_url('manage_member/add') ?>"
									class="<?= ($activePage == 'manage_member' && $activeSubPage == 'add') ? 'active' : '' ?>">
									<span class="mini"><i class="fas fa-user-plus"></i></span><span>Add Member</span>
								</a>
							<?php endif; ?>

							<?php if (can_view_sidebar('manage_member_assign_role', $type, $role, $permissions)): ?>
								<a href="<?= base_url('manage_member/assign_role') ?>"
									class="<?= ($activePage == 'manage_member' && $activeSubPage == 'assign_role') ? 'active' : '' ?>">
									<span class="mini"><i class="fas fa-user-shield"></i></span><span>Assign Role</span>
								</a>
							<?php endif; ?>
						</div>-->
					<?php endif; ?>

					<?php if (can_view_sidebar('staff', $type, $role, $permissions)): ?>
						<a href="<?= base_url('staff') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'staff') ? 'active' : '' ?>">
							<i class="fas fa-user-tie"></i><span>Staff</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('payments', $type, $role, $permissions)): ?>
						<a href="<?= base_url('payments') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'payments') ? 'active' : '' ?>">
							<i class="fas fa-credit-card"></i><span>Payments</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('complaints', $type, $role, $permissions)): ?>
						<a href="<?= base_url('complaints') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'complaints') ? 'active' : '' ?>">
							<i class="fas fa-exclamation-circle"></i><span>Complaints</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('notices', $type, $role, $permissions)): ?>
						<a href="<?= base_url('notices') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'notices') ? 'active' : '' ?>">
							<i class="fas fa-bell"></i><span>Notices</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('amenities', $type, $role, $permissions)): ?>
						<a href="<?= base_url('aminities') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'aminities') ? 'active' : '' ?>">
							<i class="fas fa-swimming-pool"></i><span>Amenities</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('visitors', $type, $role, $permissions)): ?>
						<a href="<?= base_url('visitors') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'visitors') ? 'active' : '' ?>">
							<i class="fas fa-door-open"></i><span>Visitors</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('reports', $type, $role, $permissions)): ?>
						<a href="<?= base_url('reports') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'reports') ? 'active' : '' ?>">
							<i class="fas fa-chart-line"></i><span>Reports</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('settings', $type, $role, $permissions)): ?>
						<a href="<?= base_url('settings') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'settings') ? 'active' : '' ?>">
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
					<?php if (can_view_sidebar('flat_unit', $type, $role, $permissions)): ?>
						<a href="#"
							class="menu-item <?= (isset($activePage) && $activePage == 'manage-unit') ? 'active' : '' ?>">
							<i class="fas fa-building"></i><span>Flat/Unit Management</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('parking', $type, $role, $permissions)): ?>
						<a href="#"
							class="menu-item <?= (isset($activePage) && $activePage == 'manage-parking') ? 'active' : '' ?>">
							<i class="fas fa-parking"></i><span>Parking Management</span>
						</a>
					<?php endif; ?>

					<?php if (can_view_sidebar('documents', $type, $role, $permissions)): ?>
						<a href="<?= base_url('documents') ?>"
							class="menu-item <?= (isset($activePage) && $activePage == 'manage-document') ? 'active' : '' ?>">
							<i class="fas fa-folder"></i><span>Documents</span>
						</a>
					<?php endif; ?>

					<!-- <?php if (can_view_sidebar('emergency_contact', $type, $role, $permissions)): ?>
						<a href="#"
							class="menu-item <?= (isset($activePage) && $activePage == 'manage-emergency_contact') ? 'active' : '' ?>">
							<i class="fas fa-phone-alt"></i><span>Emergency Contacts</span>
						</a>
					<?php endif; ?> -->

					<!-- <?php if (can_view_sidebar('vendor', $type, $role, $permissions)): ?>
						<a href="#"
							class="menu-item <?= (isset($activePage) && $activePage == 'manage-vendor') ? 'active' : '' ?>">
							<i class="fas fa-truck"></i><span>Vendor Management</span>
						</a>
					<?php endif; ?> -->

					<?php if (can_view_sidebar('events', $type, $role, $permissions)): ?>
						<a href="#"
							class="menu-item <?= (isset($activePage) && $activePage == 'manage-event') ? 'active' : '' ?>">
							<i class="fas fa-calendar-alt"></i><span>Events & Booking</span>
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
					<?php if (can_view_sidebar('ai_insights', $type, $role, $permissions)): ?>
						<a href="<?= base_url('ai_insights') ?>" class="menu-item"><i class="fas fa-robot"></i><span>AI
								Insights</span></a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
			<a href="<?= base_url('logout') ?>" class="menu-item logout-item"><i
					class="fas fa-sign-out-alt"></i><span>Logout</span></a>
		</div>
	</div>

	<!-- JavaScript for collapsible sections and submenus -->
	<script>
		// Toggle section collapse/expand and save state to localStorage
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

		// Restore section states from localStorage on page load
		function restoreSectionStates() {
			const sections = ['main', 'management', 'insights'];
			sections.forEach(id => {
				const section = document.getElementById('section-' + id);
				if (!section) return;
				const state = localStorage.getItem('sidebar_section_' + id);
				// Default to expanded if no state saved
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

		// Auto open Members if any subpage is active
		document.addEventListener('DOMContentLoaded', function () {
			const activeSub = document.querySelector('.submenu-item.active');
			const submenu = document.getElementById('memberSubmenu');
			const arrow = document.querySelector('.member-arrow');
			const parent = document.querySelector('.member-toggle');

			if (activeSub) {
				submenu.classList.add('open');
				arrow.style.transform = 'rotate(180deg)';
				parent.classList.add('active');
			}
		});
		// Toggle members submenu and persist choice to cookie (simple)
		function toggleSubmenu(name) {
			const submenu = document.getElementById('submenu-' + name);
			if (!submenu) return;
			const collapsed = submenu.classList.contains('collapsed');
			if (collapsed) {
				submenu.classList.remove('collapsed');
				document.cookie = 'submenu_' + name + '=expanded; path=/';
			} else {
				submenu.classList.add('collapsed');
				document.cookie = 'submenu_' + name + '=collapsed; path=/';
			}
		}

		// Automatically expand a section if any of its menu items is active
		function expandSectionWithActiveItem() {
			const sections = document.querySelectorAll('.menu-section');
			sections.forEach(section => {
				const activeItems = section.querySelectorAll('.menu-item.active, .submenu a.active');
				if (activeItems.length > 0) {
					section.classList.remove('collapsed');
					// Also update localStorage to expanded
					const id = section.id.replace('section-', '');
					localStorage.setItem('sidebar_section_' + id, 'expanded');
				}
			});
		}

		// Run on page load
		document.addEventListener('DOMContentLoaded', function () {
			restoreSectionStates();
			expandSectionWithActiveItem();
		});
	</script>
</body>

</html>


<?php if (in_array('dashboard', $allowedMenus)): ?>
	<a href="<?= base_url('dashboard') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'dashboard') ? 'active' : '' ?>">
		<i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
	</a>
<?php endif; ?>

<?php if (in_array('manage_member', $allowedMenus)): ?>
	<?php $memberActive = (isset($activePage) && $activePage === 'manage_member'); ?>
	<div class="menu-item member-toggle <?= $memberActive ? 'active' : '' ?>" onclick="toggleMemberMenu()">
		<i class="fas fa-users"></i>
		<span>
			<?= $menus['manage_member']['title'] ?>
		</span>
		<i class="fas fa-chevron-down member-arrow"></i>
	</div>

	<div class="member-submenu" id="memberSubmenu">
		<?php if (in_array('manage_member_view', $allowedMenus)): ?>
			<a href="<?= base_url($menus['manage_member']['children']['manage_member_view']['url']) ?>"
				class="submenu-item <?= (isset($activeSubPage) && $activeSubPage == 'view') ? 'active' : '' ?>">
				<?= $menus['manage_member']['children']['manage_member_view']['title'] ?>
			</a>
		<?php endif; ?>

		<?php if (in_array('manage_member_add', $allowedMenus)): ?>
			<a href="<?= base_url($menus['manage_member']['children']['manage_member_add']['url']) ?>"
				class="submenu-item <?= (isset($activeSubPage) && $activeSubPage == 'add') ? 'active' : '' ?>">
				<?= $menus['manage_member']['children']['manage_member_add']['title'] ?>
			</a>
		<?php endif; ?>

		<?php if (in_array('manage_member_assign_role', $allowedMenus)): ?>
			<a href="<?= base_url($menus['manage_member']['children']['manage_member_assign_role']['url']) ?>"
				class="submenu-item <?= (isset($activeSubPage) && $activeSubPage == 'role') ? 'active' : '' ?>">
				<?= $menus['manage_member']['children']['manage_member_assign_role']['title'] ?>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if (in_array('staff', $allowedMenus)): ?>
	<a href="<?= base_url('staff') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'staff') ? 'active' : '' ?>">
		<i class="fas fa-user-tie"></i><span>staff</span>
	</a>
<?php endif; ?>

<?php if (in_array('payments', $allowedMenus)): ?>
	<a href="<?= base_url('payments') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'payments') ? 'active' : '' ?>">
		<i class="fas fa-credit-card"></i><span>payments</span>
	</a>
<?php endif; ?>

<?php if (in_array('complaints', $allowedMenus)): ?>
	<a href="<?= base_url('complaints') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'complaints') ? 'active' : '' ?>">
		<i class="fas fa-exclamation-circle"></i><span>complaints</span>
	</a>
<?php endif; ?>
<?php if (in_array('notices', $allowedMenus)): ?>
	<a href="<?= base_url('notices') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'notices') ? 'active' : '' ?>">
		<i class="fas fa-bell"></i><span>notices</span>
	</a>
<?php endif; ?>

<?php if (in_array('aminities', $allowedMenus)): ?>
	<a href="<?= base_url('aminities') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'aminities') ? 'active' : '' ?>">
		<i class="fas fa-swimming-pool"></i><span>Amenities</span>
	</a>
<?php endif; ?>

<?php if (in_array('visitors', $allowedMenus)): ?>
	<a href="<?= base_url('visitors') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'visitors') ? 'active' : '' ?>">
		<i class="fas fa-door-open"></i><span>visitors</span>
	</a>
<?php endif; ?>

<?php if (in_array('reports', $allowedMenus)): ?>
	<a href="<?= base_url('reports') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'reports') ? 'active' : '' ?>">
		<i class="fas fa-chart-line"></i><span>reports</span>
	</a>
<?php endif; ?>

<?php if (in_array('settings', $allowedMenus)): ?>
	<a href="<?= base_url('settings') ?>"
		class="menu-item <?= (isset($activePage) && $activePage == 'settings') ? 'active' : '' ?>">
		<i class="fas fa-cogs"></i><span>settings</span>
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
			<a href="<?= base_url('dashboard') ?>"
				class="menu-item <?= (isset($activePage) && $activePage == 'manage-unit') ? 'active' : '' ?>">
				<i class="fas fa-building"></i><span>Flat/unit management</span>
			</a>
		<?php endif; ?>
		<?php if (in_array('parking', $allowedMenus)): ?>
			<a href="<?= base_url('parking') ?>"
				class="menu-item <?= (isset($activePage) && $activePage == 'manage-parking') ? 'active' : '' ?>">
				<i class="fas fa-parking"></i><span>Parking</span>
			</a>
		<?php endif; ?>

		<?php if (in_array('documents', $allowedMenus)): ?>
			<a href="<?= base_url('documents') ?>"
				class="menu-item <?= (isset($activePage) && $activePage == 'manage-document') ? 'active' : '' ?>">
				<i class="fas fa-folder"></i><span>Documents</span>
			</a>
		<?php endif; ?>
		<?php if (in_array('events', $allowedMenus)): ?>
			<a href="<?= base_url('emergency_contact') ?>"
				class="menu-item <?= (isset($activePage) && $activePage == 'manage-event') ? 'active' : '' ?>">
				<i class="fas fa-phone-alt"></i><span> Events & Booking</span>
			</a>
		<?php endif; ?>
