<html>

<head>
	<style>
		/* === HEADER - FULLY RESPONSIVE === */
		.header {
			height: 70px;
			background: var(--header-bg);
			border-bottom: 1px solid var(--border);
			position: fixed;
			right: 0;
			top: 0;
			width: 100%;
			padding: 0 15px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			transition: all 0.3s;
			z-index: 100;
			left: 0;
		}

		@media (min-width: 769px) {
			.header {
				height: 80px;
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
			gap: 10px;
			flex: 1;
			min-width: 0;
		}

		@media (min-width: 769px) {
			.header-left {
				gap: 20px;
			}
		}

		.hamburger {
			font-size: 1.3rem;
			color: var(--text-light);
			cursor: pointer;
			padding: 8px;
			border-radius: 8px;
			transition: 0.2s;
			flex-shrink: 0;
		}

		.hamburger:hover {
			background: var(--light-bg);
		}

		.header-title {
			font-size: 1.1rem;
			font-weight: 700;
			color: var(--text-dark);
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			flex-shrink: 1;
			min-width: 0;
		}

		@media (min-width: 480px) {
			.header-title {
				font-size: 1.2rem;
			}
		}

		@media (min-width: 769px) {
			.header-title {
				font-size: 1.3rem;
			}
		}

		.search-bar {
			display: none;
		}

		@media (min-width: 900px) {
			.search-bar {
				display: flex;
				align-items: center;
				background: var(--light-bg);
				padding: 8px 16px;
				border-radius: 12px;
				width: 250px;
				border: 1px solid var(--border);
				transition: all 0.3s;
				flex-shrink: 1;
				min-width: 0;
			}
		}

		@media (min-width: 1200px) {
			.search-bar {
				width: 300px;
			}
		}

		.search-bar i {
			color: var(--text-light);
			margin-right: 8px;
			flex-shrink: 0;
		}

		.search-bar input {
			border: none;
			background: none;
			outline: none;
			width: 100%;
			color: var(--text-dark);
			font-size: 0.9rem;
			min-width: 0;
		}

		.header-right {
			display: flex;
			align-items: center;
			gap: 8px;
			flex-shrink: 0;
		}

		@media (min-width: 769px) {
			.header-right {
				gap: 15px;
			}
		}

		@media (min-width: 1024px) {
			.header-right {
				gap: 20px;
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
			position: relative;
			cursor: pointer;
			flex-shrink: 0;
		}

		@media (min-width: 769px) {
			.header-icon {
				width: 40px;
				height: 40px;
			}
		}

		.header-icon:hover {
			background: var(--light-bg);
		}

		.badge {
			position: absolute;
			top: 5px;
			right: 5px;
			width: 8px;
			height: 8px;
			background: var(--danger);
			border-radius: 50%;
			border: 2px solid var(--header-bg);
		}

		.theme-toggle {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0;
			padding: 0;
			width: 36px;
			height: 36px;
			border-radius: 10px;
			background: var(--light-bg);
			cursor: pointer;
			color: var(--text-dark);
			flex-shrink: 0;
		}

		@media (min-width: 769px) {
			.theme-toggle {
				width: 40px;
				height: 40px;
			}
		}

		@media (min-width: 1100px) {
			.theme-toggle {
				width: auto;
				height: auto;
				padding: 8px 16px;
				gap: 8px;
			}
		}

		.theme-toggle span {
			display: none;
		}

		@media (min-width: 1100px) {
			.theme-toggle span {
				display: inline;
				font-size: 0.85rem;
			}
		}

		.user-profile-header {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 4px 8px;
			border-radius: 10px;
			cursor: pointer;
			flex-shrink: 0;
		}

		@media (min-width: 769px) {
			.user-profile-header {
				padding: 5px 12px;
			}
		}

		.user-profile-header:hover {
			background: var(--light-bg);
		}

		.user-profile-header img {
			width: 32px;
			height: 32px;
			border-radius: 8px;
			border: 2px solid var(--primary);
			flex-shrink: 0;
		}

		@media (min-width: 769px) {
			.user-profile-header img {
				width: 38px;
				height: 38px;
				border-radius: 10px;
			}
		}

		.user-details {
			display: none;
		}

		@media (min-width: 1100px) {
			.user-details {
				display: block;
			}

			.user-details h4 {
				font-size: 0.9rem;
				font-weight: 700;
				color: var(--text-dark);
				white-space: nowrap;
			}

			.user-details span {
				font-size: 0.75rem;
				color: var(--text-light);
				white-space: nowrap;
			}
		}

		.user-profile-header {
			display: flex;
			align-items: center;
			gap: 15px;
		}

		.logout-btn {
			color: var(--text-light);
			font-size: 1.2rem;
			transition: color 0.2s;
		}

		.logout-btn:hover {
			color: var(--danger);
		}

		.user-profile-header {
			position: relative;
			/* for absolute positioning of dropdown */
			cursor: pointer;
		}

		.profile-dropdown {
			display: none;
			position: absolute;
			top: 100%;
			/* directly below the profile area */
			right: 0;
			background: var(--card-bg, #fff);
			border: 1px solid var(--border, #ddd);
			border-radius: 8px;
			box-shadow: var(--shadow-lg, 0 4px 20px rgba(0, 0, 0, 0.12));
			min-width: 180px;
			z-index: 1000;
			overflow: hidden;
			margin-top: 5px;
		}

		.profile-dropdown.show {
			display: block;
		}

		.dropdown-item {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 12px 16px;
			color: var(--text-dark, #2c3e50);
			text-decoration: none;
			transition: background 0.2s;
			border-bottom: 1px solid var(--border, #eee);
		}

		.dropdown-item:last-child {
			border-bottom: none;
		}

		.dropdown-item:hover {
			background: var(--light-bg, #f8f9fa);
		}

		.dropdown-item i {
			width: 20px;
			text-align: center;
			color: var(--primary, #3498db);
		}

		/* Existing styles ... */

		/* Make the trigger area a flex row on all screens */
		.user-profile-trigger {
			display: flex;
			align-items: center;
			gap: 10px;
			cursor: pointer;
			padding: 5px;
			border-radius: 8px;
			transition: background 0.2s;
		}

		.user-profile-trigger:hover {
			background: var(--light-bg, #f8f9fa);
		}

		.user-profile-trigger img {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			object-fit: cover;
			flex-shrink: 0;
		}

		.user-details {
			display: flex;
			flex-direction: column;
			line-height: 1.2;
			white-space: nowrap;
		}

		.user-details h4 {
			font-size: 0.9rem;
			font-weight: 600;
			margin: 0;
			color: var(--text-dark, #2c3e50);
		}

		.user-details span {
			font-size: 0.75rem;
			color: var(--text-light, #7f8c8d);
		}

		/* Dropdown base */
		.profile-dropdown {
			display: none;
			position: absolute;
			top: 100%;
			right: 0;
			background: var(--card-bg, #fff);
			border: 1px solid var(--border, #ddd);
			border-radius: 8px;
			box-shadow: var(--shadow-lg, 0 4px 20px rgba(0, 0, 0, 0.12));
			min-width: 180px;
			z-index: 1000;
			overflow: hidden;
			margin-top: 5px;
		}

		.profile-dropdown.show {
			display: block;
		}

		.dropdown-item {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 12px 16px;
			color: var(--text-dark, #2c3e50);
			text-decoration: none;
			transition: background 0.2s;
			border-bottom: 1px solid var(--border, #eee);
			font-size: 0.9rem;
		}

		.dropdown-item:last-child {
			border-bottom: none;
		}

		.dropdown-item:hover {
			background: var(--light-bg, #f8f9fa);
		}

		.dropdown-item i {
			width: 20px;
			text-align: center;
			color: var(--primary, #3498db);
		}

		/* Responsive adjustments for small screens */
		@media (max-width: 768px) {
			.user-profile-trigger .user-details {
				display: none;
				/* Hide name/email on tablets and phones */
			}

			.user-profile-trigger img {
				width: 36px;
				height: 36px;
			}

			.profile-dropdown {
				right: -10px;
				/* Align better with smaller trigger */
				min-width: 160px;
			}
		}

		@media (max-width: 480px) {
			.profile-dropdown {
				right: -5px;
				min-width: 150px;
			}

			.dropdown-item {
				padding: 10px 12px;
				font-size: 0.85rem;
			}
		}
	</style>
</head>
<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
<body>
	<div class="header" id="header">
		<div class="header-left">
			<i class="fas fa-bars hamburger" id="hamburger"></i>
			<div class="header-title"><?= $title ?></div>
			<div class="search-bar">
				<i class="fas fa-search"></i>
				<input type="text" placeholder="Search...">
			</div>
		</div>
		<div class="header-right">
			<div class="header-icon"><i class="fas fa-bell"></i><span class="badge"></span></div>
			<div class="header-icon"><i class="fas fa-envelope"></i><span class="badge"></span></div>
			<div class="theme-toggle" id="themeToggle">
				<i class="fas fa-moon"></i><span>Dark</span>
			</div>
			<div class="user-profile-header" id="userProfile">
				<?php
				// Determine logged-in user's name and email (admin or member)
				if ($this->session->userdata('user_name')) {
					$userName = $this->session->userdata('user_name');
					$userEmail = $this->session->userdata('user_email') ?: 'admin@society.com';
				} elseif ($this->session->userdata('member_name')) {
					$userName = $this->session->userdata('member_name');
					$userEmail = $this->session->userdata('member_email') ?: 'member@society.com';
				} else {
					$userName = 'Guest';
					$userEmail = 'guest@society.com';
				}
				?>
				<!-- Clickable area: avatar + name/email -->
				<div class="user-profile-trigger">
					<img src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=3498db&color=fff"
						alt="Profile">
					<div class="user-details">
						<h4><?= htmlspecialchars($userName) ?></h4>
						<span><?= htmlspecialchars($userEmail) ?></span>

					</div>

				</div>
				<a href="<?= site_url('logout') ?>" class="logout-btn" title="Logout">
					<i class="fas fa-sign-out-alt"></i>
				</a>
				<!-- Dropdown menu -->
				<div class="profile-dropdown" id="profileDropdown">
					<a href="<?= site_url('profile') ?>" class="dropdown-item">
						<i class="fas fa-user"></i> My Profile
					</a>
					<a href="<?= site_url('logout') ?>" class="dropdown-item">
						<i class="fas fa-sign-out-alt"></i> Logout
					</a>
				</div>
			</div>
		</div>
	</div>
</body>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const profileTrigger = document.querySelector('.user-profile-trigger');
		const dropdown = document.getElementById('profileDropdown');

		if (profileTrigger && dropdown) {
			profileTrigger.addEventListener('click', function (e) {
				e.stopPropagation();
				dropdown.classList.toggle('show');
			});

			document.addEventListener('click', function (event) {
				if (!profileTrigger.contains(event.target) && !dropdown.contains(event.target)) {
					dropdown.classList.remove('show');
				}
			});
		}
	});
</script>

</html>
