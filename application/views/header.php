<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$CI =& get_instance();
$CI->load->database();

$society_id = (int) $CI->session->userdata('society_id');
$user_id = (int) ($CI->session->userdata('member_id') ?: $CI->session->userdata('user_id'));

$maintenance_amount = null;
$maintenance_due_date = null;
$maintenance_paid = false;

$current_month = date('F');   // IMPORTANT: keep same format when saving into payments.month
$current_year = (int) date('Y');

if ($society_id > 0) {
	$rows = $CI->db->select('setting_key, setting_value')
		->from('society_settings')
		->where('society_id', $society_id)
		->where_in('setting_key', ['maintenance_amount', 'maintenance_due_date'])
		->get()
		->result_array();

	foreach ($rows as $row) {
		if ($row['setting_key'] === 'maintenance_amount') {
			$maintenance_amount = is_numeric($row['setting_value']) ? (float) $row['setting_value'] : null;
		}
		if ($row['setting_key'] === 'maintenance_due_date') {
			$maintenance_due_date = is_numeric($row['setting_value']) ? (int) $row['setting_value'] : null;
		}
	}

	if ($user_id > 0) {
		$paid = $CI->db->select('id')
			->from('payments')
			->where('society_id', $society_id)
			->where('user_id', $user_id)
			->where('payment_type', 'maintenance')
			->where('month', $current_month)
			->where('year', $current_year)
			->where('status', 'paid')
			->limit(1)
			->get()
			->row_array();

		$maintenance_paid = !empty($paid);
	}
}

if (!function_exists('day_suffix')) {
	function day_suffix($d)
	{
		if (!in_array(($d % 100), [11, 12, 13])) {
			switch ($d % 10) {
				case 1:
					return 'st';
				case 2:
					return 'nd';
				case 3:
					return 'rd';
			}
		}
		return 'th';
	}
}

$userName = $CI->session->userdata('user_name') ?: ($CI->session->userdata('member_name') ?: 'Guest');
$userEmail = $CI->session->userdata('user_email') ?: ($CI->session->userdata('member_email') ?: 'guest@society.com');
$title = isset($title) ? $title : 'SocietyHub';

// --- Fetch profile image from database ---
$profile_image = null;
if ($user_id > 0) {
	// Try users table first
	$user = $CI->db->select('profile_image')
		->from('users')
		->where('id', $user_id)
		->get()
		->row_array();

	// If not found, try members table
	if (!$user) {
		$user = $CI->db->select('profile_image')
			->from('members')
			->where('id', $user_id)
			->get()
			->row_array();
	}

	if (!empty($user['profile_image'])) {
		$profile_image = base_url('uploads/profile/' . $user['profile_image']);
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<style>
		:root {
			--header-height: 70px;
		}

		@media(min-width: 769px) {
			:root {
				--header-height: 80px;
			}
		}

		/* Base responsive reset */
		* {
			box-sizing: border-box;
		}

		body {
			overflow-x: hidden;
		}

		.header {
			height: var(--header-height);
			background: var(--header-bg);
			border-bottom: 1px solid var(--border);
			position: fixed;
			right: 0;
			top: 0;
			width: 100%;
			padding: 0 12px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			transition: all .3s;
			z-index: 100;
			left: 0;
		}

		@media(min-width: 480px) {
			.header {
				padding: 0 15px;
			}
		}

		@media(min-width: 769px) {
			.header {
				width: calc(100% - 280px);
				left: auto;
				padding: 0 30px;
			}

			.header.collapsed {
				width: calc(100% - 80px);
			}
		}

		.header-left {
			display: flex;
			align-items: center;
			gap: 8px;
			flex: 1;
			min-width: 0;
		}

		@media(min-width: 769px) {
			.header-left {
				gap: 20px;
			}
		}

		.hamburger {
			font-size: 1.2rem;
			color: var(--text-light);
			cursor: pointer;
			padding: 8px;
			border-radius: 8px;
			transition: .2s;
			flex-shrink: 0;
			background: none;
			border: none;
		}

		@media(min-width: 480px) {
			.hamburger {
				font-size: 1.3rem;
				padding: 8px;
			}
		}

		.hamburger:hover {
			background: var(--light-bg);
		}

		.header-title {
			font-size: 1rem;
			font-weight: 700;
			color: var(--text-dark);
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			flex-shrink: 1;
		}

		@media(min-width: 480px) {
			.header-title {
				font-size: 1.1rem;
			}
		}

		@media(min-width: 769px) {
			.header-title {
				font-size: 1.3rem;
			}
		}

		.search-bar {
			display: none;
		}

		@media(min-width: 900px) {
			.search-bar {
				display: flex;
				align-items: center;
				background: var(--light-bg);
				padding: 8px 16px;
				border-radius: 12px;
				width: 250px;
				border: 1px solid var(--border);
			}

			.search-bar i {
				color: var(--text-light);
				margin-right: 8px;
			}

			.search-bar input {
				border: none;
				background: none;
				outline: none;
				width: 100%;
				color: var(--text-dark);
				font-size: .9rem;
			}
		}

		.header-right {
			display: flex;
			align-items: center;
			gap: 6px;
			flex-shrink: 0;
		}

		@media(min-width: 480px) {
			.header-right {
				gap: 10px;
			}
		}

		@media(min-width: 769px) {
			.header-right {
				gap: 15px;
			}
		}

		/* Responsive adjustments for very small screens */
		@media (max-width: 420px) {
			.header-right {
				gap: 4px;
			}
		}

		.header-icon {
			width: 36px;
			height: 36px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 10px;
			color: var(--text-light);
			cursor: pointer;
			position: relative;
		}

		.header-icon:hover {
			background: var(--light-bg);
		}

		.header-icon a {
			color: inherit;
			text-decoration: none;
			display: flex;
			width: 100%;
			height: 100%;
			align-items: center;
			justify-content: center;
		}

		.theme-toggle {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 36px;
			height: 36px;
			border-radius: 10px;
			background: var(--light-bg);
			cursor: pointer;
			color: var(--text-dark);
		}

		.theme-toggle span {
			display: none;
		}

		@media(min-width: 1100px) {
			.theme-toggle {
				width: auto;
				padding: 8px 16px;
				gap: 8px;
			}

			.theme-toggle span {
				display: inline;
				font-size: .85rem;
			}
		}

		/* Fix dropdown positioning - add relative container */
		.user-profile-header {
			display: flex;
			align-items: center;
			gap: 6px;
			padding: 4px 6px;
			border-radius: 10px;
			cursor: pointer;
			position: relative;
		}

		@media(min-width: 480px) {
			.user-profile-header {
				gap: 8px;
				padding: 4px 8px;
			}
		}

		.user-profile-header:hover {
			background: var(--light-bg);
		}

		.user-profile-trigger {
			display: flex;
			align-items: center;
			gap: 6px;
			cursor: pointer;
			padding: 5px;
			border-radius: 8px;
		}

		.user-profile-trigger img {
			width: 32px;
			height: 32px;
			border-radius: 50%;
			object-fit: cover;
			flex-shrink: 0;
		}

		@media(min-width: 480px) {
			.user-profile-trigger img {
				width: 36px;
				height: 36px;
			}
		}

		@media(min-width: 769px) {
			.user-profile-trigger img {
				width: 40px;
				height: 40px;
			}
		}

		.user-details {
			display: none;
		}

		@media(min-width: 1100px) {
			.user-details {
				display: flex;
				flex-direction: column;
				line-height: 1.2;
			}

			.user-details h4 {
				font-size: .9rem;
				font-weight: 600;
				margin: 0;
				color: var(--text-dark);
			}

			.user-details span {
				font-size: .75rem;
				color: var(--text-light);
			}
		}

		.logout-btn {
			color: var(--text-light);
			font-size: 1rem;
			transition: color .2s;
			text-decoration: none;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 32px;
			height: 32px;
			border-radius: 8px;
		}

		@media(min-width: 480px) {
			.logout-btn {
				font-size: 1.1rem;
				width: auto;
				height: auto;
				background: none;
				padding: 0;
			}
		}

		.logout-btn:hover {
			color: var(--danger);
			background: rgba(220, 38, 38, 0.1);
		}

		/* Profile dropdown - responsive & properly positioned */
		.profile-dropdown {
			display: none;
			position: absolute;
			top: calc(100% + 8px);
			right: 0;
			background: var(--card-bg, #fff);
			border: 1px solid var(--border);
			border-radius: 12px;
			box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
			min-width: 160px;
			max-width: 90vw;
			z-index: 9999;
			overflow: hidden;
		}

		@media(max-width: 480px) {
			.profile-dropdown {
				right: -5px;
				min-width: 150px;
			}
		}

		.profile-dropdown.open {
			display: block;
		}

		.dropdown-item {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 10px 14px;
			color: var(--text-dark);
			text-decoration: none;
			font-size: .85rem;
			border-bottom: 1px solid var(--border);
			white-space: nowrap;
		}

		@media (max-width: 480px) {
			.dropdown-item {
				white-space: normal;
				word-break: break-word;
			}
		}

		.dropdown-item:last-child {
			border-bottom: none;
		}

		.dropdown-item:hover {
			background: var(--light-bg);
		}

		.dropdown-item i {
			width: 16px;
			text-align: center;
			color: var(--primary);
		}

		/* Notification bell and panel - improved responsiveness */
		.notif-wrap {
			position: relative;
		}

		.bell-btn {
			position: relative;
			width: 36px;
			height: 36px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 10px;
			border: none;
			background: none;
			color: var(--text-light);
			cursor: pointer;
			flex-shrink: 0;
		}

		@media(min-width: 480px) {
			.bell-btn {
				width: 40px;
				height: 40px;
			}
		}

		.bell-btn:hover {
			background: var(--light-bg);
		}

		.bell-btn i {
			font-size: .95rem;
			pointer-events: none;
		}

		.bell-badge {
			position: absolute;
			top: 3px;
			right: 3px;
			min-width: 16px;
			height: 16px;
			background: #e74c3c;
			color: #fff;
			border-radius: 8px;
			font-size: .58rem;
			font-weight: 700;
			display: none;
			align-items: center;
			justify-content: center;
			border: 2px solid var(--header-bg);
			padding: 0 3px;
			pointer-events: none;
		}

		.bell-badge.show {
			display: flex;
		}

		@keyframes ring {

			0%,
			100% {
				transform: rotate(0)
			}

			20% {
				transform: rotate(18deg)
			}

			40% {
				transform: rotate(-14deg)
			}

			60% {
				transform: rotate(8deg)
			}

			80% {
				transform: rotate(-4deg)
			}
		}

		.bell-btn.ringing i {
			animation: ring .6s ease;
		}

		/* Notification panel - fully responsive */
		.notif-panel {
			position: absolute;
			top: calc(100% + 10px);
			right: -8px;
			width: 320px;
			max-width: calc(100vw - 24px);
			max-height: 440px;
			background: var(--card-bg, #fff);
			border: 1px solid var(--border);
			border-radius: 14px;
			box-shadow: 0 12px 40px rgba(0, 0, 0, .14);
			display: flex;
			flex-direction: column;
			overflow: hidden;
			opacity: 0;
			visibility: hidden;
			transform: translateY(-6px) scale(.98);
			transition: .2s ease;
			z-index: 9999;
		}

		@media(max-width: 480px) {
			.notif-panel {
				right: 0;
				width: calc(100vw - 24px);
				max-width: 340px;
			}
		}

		.notif-panel.open {
			opacity: 1;
			visibility: visible;
			transform: none;
		}

		.notif-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 12px 14px;
			border-bottom: 1px solid var(--border);
			flex-shrink: 0;
			flex-wrap: wrap;
			gap: 8px;
		}

		@media (max-width: 360px) {
			.notif-head {
				flex-direction: column;
				align-items: stretch;
			}
			.notif-mark-all {
				align-self: flex-end;
			}
		}

		.notif-head h4 {
			margin: 0;
			font-size: .9rem;
			font-weight: 700;
			color: var(--text-dark);
		}

		.notif-mark-all {
			background: none;
			border: none;
			cursor: pointer;
			font-size: .72rem;
			font-weight: 600;
			color: #6366f1;
			padding: 4px 8px;
			border-radius: 6px;
		}

		.notif-mark-all:hover {
			background: rgba(99, 102, 241, .1);
		}

		.notif-scroll {
			overflow-y: auto;
			flex: 1;
			-webkit-overflow-scrolling: touch;
		}

		.notif-scroll::-webkit-scrollbar {
			width: 3px;
		}

		.notif-scroll::-webkit-scrollbar-thumb {
			background: var(--border);
			border-radius: 2px;
		}

		.ni {
			display: flex;
			flex-direction: column;
			gap: 2px;
			padding: 10px 14px;
			border-bottom: 1px solid var(--border);
			cursor: pointer;
			transition: .15s;
		}

		.ni:last-child {
			border-bottom: none;
		}

		.ni:hover {
			background: var(--light-bg);
		}

		.ni.unread {
			background: rgba(99, 102, 241, .04);
			border-left: 3px solid #6366f1;
		}

		.ni-title {
			font-size: .82rem;
			font-weight: 600;
			color: var(--text-dark);
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.ni-msg {
			font-size: .74rem;
			color: var(--text-light);
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
			word-break: break-word;
		}

		.ni-time {
			font-size: .68rem;
			color: var(--text-light);
			margin-top: 2px;
		}

		.ni-empty {
			text-align: center;
			padding: 36px 20px;
			color: var(--text-light);
		}

		.ni-empty i {
			font-size: 1.8rem;
			display: block;
			margin-bottom: 8px;
			opacity: .35;
		}

		.ni-empty span {
			font-size: .8rem;
		}

		.notif-foot {
			padding: 9px 14px;
			border-top: 1px solid var(--border);
			text-align: center;
			flex-shrink: 0;
		}

		.notif-foot a {
			font-size: .76rem;
			color: #6366f1;
			text-decoration: none;
			font-weight: 600;
		}

		/* Toast notifications responsive */
		.notif-toast {
			position: fixed;
			top: calc(var(--header-height) + 10px);
			right: 12px;
			width: calc(100% - 24px);
			max-width: 300px;
			background: var(--card-bg, #fff);
			border: 1px solid var(--border);
			border-left: 4px solid #6366f1;
			border-radius: 12px;
			box-shadow: 0 8px 30px rgba(0, 0, 0, .15);
			padding: 12px 14px;
			display: flex;
			gap: 10px;
			align-items: flex-start;
			z-index: 99999;
			transform: translateX(330px);
			transition: transform .3s ease;
			pointer-events: auto;
		}

		@media(min-width: 480px) {
			.notif-toast {
				right: 20px;
				width: 300px;
			}
		}

		@media (max-width: 380px) {
			.notif-toast {
				left: 12px;
				right: 12px;
				max-width: none;
			}
		}

		.notif-toast.show {
			transform: translateX(0);
		}

		.notif-toast-icon {
			width: 32px;
			height: 32px;
			background: rgba(99, 102, 241, .12);
			border-radius: 8px;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}

		.notif-toast-icon i {
			color: #6366f1;
			font-size: .8rem;
		}

		.notif-toast-body {
			flex: 1;
			min-width: 0;
		}

		.notif-toast-title {
			font-size: .82rem;
			font-weight: 700;
			color: var(--text-dark);
			margin: 0 0 2px;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.notif-toast-msg {
			font-size: .75rem;
			color: var(--text-light);
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
			word-break: break-word;
		}

		.notif-toast-close {
			background: none;
			border: none;
			cursor: pointer;
			color: var(--text-light);
			font-size: 1.1rem;
			padding: 4px;
			line-height: 1;
			flex-shrink: 0;
			border-radius: 6px;
		}

		.notif-toast-close:hover {
			color: var(--danger);
			background: rgba(0,0,0,0.05);
		}

		/* Maintenance pill responsiveness */
		.maint-pill {
			display: none;
			align-items: center;
			gap: 8px;
			background: var(--light-bg);
			border: 1px solid var(--border);
			border-radius: 12px;
			padding: 5px 10px;
			color: var(--text-dark);
			flex-shrink: 0;
			text-decoration: none;
		}

		@media(min-width: 480px) {
			.maint-pill {
				display: flex;
			}
		}

		@media(min-width: 900px) {
			.maint-pill {
				gap: 10px;
				padding: 7px 12px;
			}
		}

		@media (max-width: 600px) {
			.maint-pill .mtext .label {
				font-size: 0.6rem;
			}
			.maint-pill .mtext .value {
				font-size: 0.7rem;
			}
		}

		.maint-pill i {
			color: var(--primary);
			font-size: .9rem;
		}

		.maint-pill .mtext {
			display: flex;
			flex-direction: column;
			line-height: 1.1;
		}

		.maint-pill .mtext .label {
			font-size: .65rem;
			color: var(--text-light);
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: .03em;
		}

		.maint-pill .mtext .value {
			font-size: .8rem;
			font-weight: 700;
			color: var(--text-dark);
			white-space: nowrap;
		}

		@media (max-width: 550px) {
			.maint-pill .mtext .value {
				white-space: normal;
				word-break: keep-all;
			}
		}

		@media(min-width: 900px) {
			.maint-pill .mtext .label {
				font-size: .68rem;
			}

			.maint-pill .mtext .value {
				font-size: .86rem;
			}
		}

		.maint-pill.due {
			cursor: pointer;
		}

		.maint-pill.paid {
			opacity: .9;
			cursor: default;
		}

		.maint-pill.due:hover {
			background: rgba(99, 102, 241, .08);
		}
	</style>
</head>

<body>
	<div class="header" id="header">
		<div class="header-left">
			<button class="hamburger" id="hamburger" aria-label="Menu">
				<i class="fas fa-bars"></i>
			</button>
			<div class="header-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
			<div class="search-bar">
				<i class="fas fa-search"></i>
				<input type="text" placeholder="Search...">
			</div>
		</div>

		<div class="header-right">
			<?php if (!empty($maintenance_amount) && $maintenance_amount > 0): ?>
				<?php if ($maintenance_paid): ?>
					<div class="maint-pill paid" title="Already paid for this month">
						<i class="fas fa-indian-rupee-sign"></i>
						<div class="mtext">
							<span class="label">Maintenance</span>
							<span class="value">
								₹<?= number_format($maintenance_amount, 0) ?>/month
								<?php if (!empty($maintenance_due_date)): ?>
									<small style="color:var(--text-light);font-weight:600;">
										· Due <?= (int) $maintenance_due_date ?><?= day_suffix((int) $maintenance_due_date) ?>
									</small>
								<?php endif; ?>
								<small style="color:green;font-weight:700;">· Paid for <?= date('F Y') ?></small>
							</span>
						</div>
					</div>
				<?php else: ?>
					<a href="<?= site_url('payment_controllerr/maintenance_pay') ?>" class="maint-pill due"
						title="Click to pay maintenance">
						<i class="fas fa-indian-rupee-sign"></i>
						<div class="mtext">
							<span class="label">Maintenance</span>
							<span class="value">
								₹<?= number_format($maintenance_amount, 0) ?>/month
								<?php if (!empty($maintenance_due_date)): ?>
									<small style="color:var(--text-light);font-weight:600;">
										· Due <?= (int) $maintenance_due_date ?><?= day_suffix((int) $maintenance_due_date) ?>
									</small>
								<?php endif; ?>
								<small style="color:#e74c3c;font-weight:700;">· Pay now</small>
							</span>
						</div>
					</a>
				<?php endif; ?>
			<?php endif; ?>

			<!-- Notification bell -->
			<div class="notif-wrap" id="notifWrap">
				<button class="bell-btn" id="bellBtn" onclick="N.togglePanel(event)" title="Notifications"
					type="button">
					<i class="fas fa-bell"></i>
					<span class="bell-badge" id="bellBadge">0</span>
				</button>
				<div class="notif-panel" id="notifPanel">
					<div class="notif-head">
						<h4><i class="fas fa-bell" style="color:#6366f1;margin-right:6px;"></i>Notifications</h4>
						<button class="notif-mark-all" onclick="N.markAll()" type="button">
							<i class="fas fa-check-double"></i> Mark all read
						</button>
					</div>
					<div class="notif-scroll" id="notifScroll">
						<div class="ni-empty" id="niEmpty">
							<i class="fas fa-bell-slash"></i>
							<span>No new notifications</span>
						</div>
					</div>
					<div class="notif-foot"><a href="<?= site_url('notifications') ?>">View all notification →</a></div>
				</div>
			</div>

			<!-- Settings icon -->
			<div class="header-icon">
				<a href="<?= base_url('settings') ?>" title="Settings">
					<i class="fas fa-cogs"></i>
				</a>
			</div>

			<!-- Theme toggle -->
			<div class="theme-toggle" id="themeToggle" title="Toggle theme">
				<i class="fas fa-moon"></i><span>Dark</span>
			</div>

			<!-- User profile -->
			<div class="user-profile-header" id="userProfileWrap">
				<div class="user-profile-trigger" id="profileTrigger">
					<img src="<?= $profile_image ?: 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=3498db&color=fff' ?>"
						alt="Profile">
					<div class="user-details">
						<h4><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h4>
						<span><?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></span>
					</div>
				</div>

				<a href="<?= site_url('logout') ?>" class="logout-btn" title="Logout">
					<i class="fas fa-sign-out-alt"></i>
				</a>

				<div class="profile-dropdown" id="profileDropdown">

					<a href="<?= site_url('profile') ?>" class="dropdown-item">
						<i class="fas fa-user"></i> My Profile
					</a>

					<a href="<?= site_url('payment_controllerr/maintenance_pay') ?>" class="dropdown-item">
						<i class="fas fa-indian-rupee-sign"></i>
						Maintenance

						<?php if ($maintenance_paid): ?>
							<span style="margin-left:auto;color:green;font-size:0.75rem;">Paid</span>
						<?php else: ?>
							<span style="margin-left:auto;color:#e74c3c;font-size:0.75rem;">Pay Now</span>
						<?php endif; ?>
					</a>

					<a href="<?= site_url('logout') ?>" class="dropdown-item">
						<i class="fas fa-sign-out-alt"></i> Logout
					</a>

				</div>
			</div>

		</div>
	</div>

	<audio id="notifSound" preload="auto">
		<source src="<?= base_url('assets/sounds/notify.mp3') ?>" type="audio/mpeg">
	</audio>

	<script>
		if ("Notification" in window) {
			if (Notification.permission !== "granted" && Notification.permission !== "denied") {
				Notification.requestPermission();
			}
		}

		window.N = (() => {
			const AJAX = '<?= site_url("notice_controller") ?>';
			const CSRF = '<?= $this->security->get_csrf_token_name() ?>';
			let csrfHash = '<?= $this->security->get_csrf_hash() ?>';

			let items = [];
			let open = false;
			let badge, scroll, empty, panel, bellBtn;

			const playSound = () => {
				try {
					const audio = document.getElementById('notifSound');
					if (!audio) return;
					audio.currentTime = 0;
					audio.play().catch(() => { });
				} catch (e) { }
			};

			const esc = s => String(s ?? '')
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;');

			const ago = d => {
				if (!d) return '';
				const s = Math.floor((Date.now() - new Date(d).getTime()) / 1000);
				if (s < 60) return 'Just now';
				if (s < 3600) return Math.floor(s / 60) + 'm ago';
				if (s < 86400) return Math.floor(s / 3600) + 'h ago';
				return Math.floor(s / 86400) + 'd ago';
			};

			const updateIconBadge = unread => {
				document.title = unread > 0 ? `(${unread}) SocietyHub` : 'SocietyHub';
				const favicon = document.querySelector("link[rel='icon']");
				if (favicon) {
					favicon.href = unread > 0
						? '<?= base_url("assets/img/favicon-red.png") ?>'
						: '<?= base_url("assets/img/favicon.png") ?>';
				}
			};

			const render = () => {
				const unread = items.filter(n => Number(n.is_read) === 0).length;
				updateIconBadge(unread);

				if (badge) {
					badge.textContent = unread > 0 ? unread : '';
					badge.classList.toggle('show', unread > 0);
				}

				if (!scroll || !empty) return;

				if (!items.length) {
					scroll.innerHTML = '';
					scroll.appendChild(empty);
					return;
				}

				scroll.innerHTML = items.map(n => {
					const id = String(n.id ?? '');
					return `
						<div class="ni ${Number(n.is_read) === 0 ? 'unread' : ''}" data-id="${esc(id)}">
							<div class="ni-title">${esc(n.title)}</div>
							<div class="ni-msg">${esc(n.message || '')}</div>
							<span class="ni-time">${ago(n.created_at)}</span>
						</div>
					`;
				}).join('');

				scroll.querySelectorAll('.ni').forEach(el => {
					const id = el.getAttribute('data-id') || '';
					if (/^\d+$/.test(id)) {
						el.addEventListener('click', () => N.read(id));
					}
				});
			};

			const post = (url, body = {}) => fetch(url, {
				method: 'POST',
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
				body: new URLSearchParams({ [CSRF]: csrfHash, ...body })
			}).then(r => r.json()).catch(() => null);

			const toast = (data) => {
				const t = document.createElement('div');
				t.className = 'notif-toast';
				t.innerHTML = `
					<div class="notif-toast-icon"><i class="fas fa-bullhorn"></i></div>
					<div class="notif-toast-body">
						<div class="notif-toast-title">${esc(data.title || 'New Notice')}</div>
						<div class="notif-toast-msg">${esc(data.message || '')}</div>
					</div>
					<button class="notif-toast-close" type="button">×</button>
				`;
				t.querySelector('.notif-toast-close').onclick = () => t.remove();
				document.body.appendChild(t);
				requestAnimationFrame(() => t.classList.add('show'));
				setTimeout(() => {
					t.classList.remove('show');
					setTimeout(() => t.remove(), 300);
				}, 4000);
			};

			return {
				init() {
					badge = document.getElementById('bellBadge');
					scroll = document.getElementById('notifScroll');
					empty = document.getElementById('niEmpty');
					panel = document.getElementById('notifPanel');
					bellBtn = document.getElementById('bellBtn');
					this.reload();
				},

				reload() {
					fetch(AJAX + '/unread_notifications', {
						headers: { 'X-Requested-With': 'XMLHttpRequest' }
					})
						.then(r => r.json())
						.then(d => {
							items = Array.isArray(d.data) ? d.data : [];
							render();
						})
						.catch(() => { });
				},

				add(data) {
					if (!data) return;
					const key = String(data.id ?? data.notification_id ?? data.notice_id ?? '');
					if (!key) return;

					const noticeKey = String(data.notice_id ?? data.notification_id ?? key);

					if (items.some(n => String(n.id) === key || String(n.notice_id ?? '') === noticeKey)) return;

					items.unshift({
						id: data.id ?? key,
						notice_id: data.notice_id ?? noticeKey,
						title: data.title || 'New Notice',
						message: data.message || '',
						is_read: 0,
						created_at: data.created_at || new Date().toISOString()
					});

					render();
					playSound();

					if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
						const n = new Notification(data.title || 'New Notice', {
							body: data.message || '',
							icon: '<?= base_url("assets/img/1000170405.png") ?>'
						});
						n.onclick = () => {
							window.focus();
							n.close();
						};
					} else if (!document.hidden) {
						toast(data);
					}

					if (bellBtn) {
						bellBtn.classList.remove('ringing');
						void bellBtn.offsetWidth;
						bellBtn.classList.add('ringing');
						setTimeout(() => bellBtn.classList.remove('ringing'), 700);
					}
				},

				read(id) {
					if (!/^\d+$/.test(String(id))) return;
					const n = items.find(x => String(x.id) === String(id));
					if (!n || Number(n.is_read) === 1) return;
					n.is_read = 1;
					render();
					post(AJAX + '/mark_read', { id }).catch(() => { });
				},

				markAll() {
					items.forEach(n => n.is_read = 1);
					render();
					post(AJAX + '/mark_all_read', {}).catch(() => { });
				},

				togglePanel(e) {
					if (e) e.stopPropagation();
					open = !open;
					if (panel) panel.classList.toggle('open', open);
					if (open) this.reload();
				},

				closePanel() {
					open = false;
					if (panel) panel.classList.remove('open');
				}
			};
		})();

		document.addEventListener('click', () => {
			const audio = document.getElementById('notifSound');
			if (audio) audio.play().then(() => {
				audio.pause();
				audio.currentTime = 0;
			}).catch(() => { });
		}, { once: true });

		document.addEventListener('DOMContentLoaded', () => {
			N.init();

			document.addEventListener('click', e => {
				const wrap = document.getElementById('notifWrap');
				if (wrap && !wrap.contains(e.target)) N.closePanel();

				const profWrap = document.getElementById('userProfileWrap');
				const dropdown = document.getElementById('profileDropdown');
				if (dropdown && profWrap && !profWrap.contains(e.target)) {
					dropdown.classList.remove('open');
				}
			});

			const trigger = document.getElementById('profileTrigger');
			const dropdown = document.getElementById('profileDropdown');
			if (trigger && dropdown) {
				trigger.addEventListener('click', e => {
					e.stopPropagation();
					dropdown.classList.toggle('open');
				});
			}
		});
	</script>
</body>

</html>
