<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Society Login – Split Screen</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<style>
		/* ========== GLOBAL LAYOUT ========== */
		.split-layout {
			display: flex;
			min-height: 100vh;
			background: var(--light-bg);
		}

		/* ========== LEFT SIDE (60%) ========== */
		.split-left {
			flex: 1.5;
			/* 60% of the width */
			background-image: url('<?= base_url('assets/img/GG-Gate-1210x617.jpg') ?>');
			background-size: cover;
			background-position: center;
			position: relative;
			display: none;
			/* hidden on mobile */
			overflow: hidden;
		}

		/* Animated gradient overlay */
		.split-left .overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: linear-gradient(135deg,
					rgba(52, 152, 219, 0.85) 0%,
					rgba(41, 128, 185, 0.75) 50%,
					rgba(52, 152, 219, 0.85) 100%);
			background-size: 200% 200%;
			animation: gradientShift 12s ease infinite;
			z-index: 1;
		}

		@keyframes gradientShift {
			0% {
				background-position: 0% 50%;
			}

			50% {
				background-position: 100% 50%;
			}

			100% {
				background-position: 0% 50%;
			}
		}

		.split-content {
			position: relative;
			z-index: 2;
			color: white;
			padding: 3rem;
			display: flex;
			flex-direction: column;
			justify-content: center;
			height: 100%;
			text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
		}

		.split-content .logo-wrapper {
			width: 80px;
			height: 80px;
			background: rgba(255, 255, 255, 0.2);
			backdrop-filter: blur(8px);
			border-radius: 24px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
			font-size: 2.5rem;
			margin-bottom: 1.8rem;
			box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
			border: 1px solid rgba(255, 255, 255, 0.3);
		}

		.split-content h2 {
			font-size: 3rem;
			font-weight: 700;
			margin-bottom: 0.25rem;
			line-height: 1;
		}

		.split-content p {
			font-size: 1.3rem;
			opacity: 0.95;
			max-width: 80%;
			font-weight: 300;
			letter-spacing: 0.5px;
		}

		/* ========== RIGHT SIDE (40%) ========== */
		.split-right {
			flex: 1;
			/* 40% */
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 2rem;
			background: var(--light-bg);
			position: relative;
			overflow: hidden;
		}

		/* Subtle background pattern */
		.split-right::before {
			content: '';
			position: absolute;
			top: -50%;
			left: -50%;
			width: 200%;
			height: 200%;
			background: radial-gradient(circle at 30% 40%, rgba(52, 152, 219, 0.03) 0%, transparent 30%);
			z-index: 0;
		}

		.split-right .login-card {
			width: 100%;
			max-width: 420px;
			background: var(--card-bg);
			border-radius: 28px;
			border: 1px solid var(--border);
			box-shadow: var(--shadow-lg);
			padding: 2.2rem 2rem;
			position: relative;
			z-index: 1;
			transition: transform 0.3s ease, box-shadow 0.3s ease;
		}

		.split-right .login-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15);
		}

		/* ========== ALERTS (refined) ========== */
		.alert {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 14px 18px;
			border-radius: 16px;
			font-size: 0.9rem;
			font-weight: 500;
			margin-bottom: 1.5rem;
			animation: slideDown 0.3s ease;
			border-left: 4px solid transparent;
			backdrop-filter: blur(4px);
		}

		.alert i {
			font-size: 1.2rem;
		}

		.alert-success {
			background: rgba(39, 174, 96, 0.15);
			color: var(--success);
			border-left-color: var(--success);
		}

		.alert-info {
			background: rgba(52, 152, 219, 0.15);
			color: var(--primary);
			border-left-color: var(--primary);
		}

		.alert-danger {
			background: rgba(231, 76, 60, 0.15);
			color: var(--danger);
			border-left-color: var(--danger);
		}

		@keyframes slideDown {
			from {
				opacity: 0;
				transform: translateY(-10px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* ========== LOGIN HEADER ========== */
		.login-header {
			text-align: center;
			margin-bottom: 2rem;
		}

		.login-header h2 {
			font-size: 2rem;
			font-weight: 700;
			color: var(--text-dark);
			margin-bottom: 0.25rem;
		}

		.login-header p {
			color: var(--text-light);
			font-size: 0.95rem;
		}

		/* ========== FORM ========== */
		.form-group {
			margin-bottom: 1.5rem;
		}

		.form-group label {
			display: flex;
			align-items: center;
			gap: 6px;
			margin-bottom: 8px;
			color: var(--text-dark);
			font-weight: 600;
			font-size: 0.9rem;
		}

		.form-group label i {
			color: var(--primary);
			width: 18px;
		}

		.form-control {
			width: 100%;
			padding: 14px 16px;
			border: 1px solid var(--border);
			border-radius: 14px;
			background: var(--light-bg);
			color: var(--text-dark);
			font-size: 0.95rem;
			transition: all 0.2s;
		}

		.form-control:focus {
			border-color: var(--primary);
			outline: none;
			box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
		}

		/* ========== BUTTON ========== */
		.btn-login {
			width: 100%;
			padding: 15px;
			font-size: 1rem;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			border: none;
			color: white;
			font-weight: 600;
			border-radius: 16px;
			cursor: pointer;
			transition: all 0.3s;
			box-shadow: 0 8px 18px rgba(52, 152, 219, 0.3);
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		.btn-login:hover {
			transform: translateY(-3px);
			box-shadow: 0 15px 25px rgba(52, 152, 219, 0.4);
		}

		.btn-login i {
			font-size: 1.1rem;
		}

		/* ========== MESSAGE AREA ========== */
		#msg {
			margin-top: 1.2rem;
			text-align: center;
			font-weight: 500;
			padding: 10px;
			border-radius: 12px;
		}

		#msg.success {
			background: rgba(39, 174, 96, 0.1);
			color: var(--success);
		}

		#msg.error {
			background: rgba(231, 76, 60, 0.1);
			color: var(--danger);
		}

		/* ========== FOOTER LINK ========== */
		.login-footer {
			text-align: center;
			margin-top: 2rem;
			color: var(--text-light);
			font-size: 0.9rem;
		}

		.login-footer a {
			color: var(--primary);
			font-weight: 600;
			text-decoration: none;
			margin-left: 5px;
			transition: color 0.2s;
		}

		.login-footer a:hover {
			color: var(--primary-dark);
			text-decoration: underline;
		}

		/* ========== THEME TOGGLE ========== */
		.theme-toggle-login {
			position: fixed;
			top: 20px;
			right: 20px;
			width: 48px;
			height: 48px;
			background: var(--card-bg);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			box-shadow: var(--shadow-lg);
			z-index: 1000;
			border: 1px solid var(--border);
			color: var(--text-dark);
			transition: 0.2s;
			font-size: 1.2rem;
		}

		.theme-toggle-login:hover {
			transform: scale(1.1);
			background: var(--primary);
			color: white;
		}

		/* ========== RESPONSIVE ========== */
		@media (min-width: 769px) {
			.split-left {
				display: flex;
			}
		}

		@media (max-width: 768px) {
			.split-layout {
				flex-direction: column;
			}

			.split-left {
				min-height: 250px;
				display: flex;
			}

			.split-content {
				padding: 2rem;
				text-align: center;
				align-items: center;
			}

			.split-content .logo-wrapper {
				margin-left: auto;
				margin-right: auto;
			}

			.split-content p {
				max-width: 100%;
			}

			.split-right {
				padding: 1.5rem;
			}

			.split-right .login-card {
				padding: 1.8rem 1.5rem;
			}
		}
	</style>
</head>

<body class="split-layout">
	<!-- LEFT SIDE: Image with overlay and welcome content -->
	<div class="split-left">
		<div class="overlay"></div>
		<div class="split-content">
			<div class="logo-wrapper">
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
			<h2>Welcome to Society </h2>
			<p>Manage your community seamlessly with our integrated portal.</p>
		</div>
	</div>
	
	<!-- RIGHT SIDE: Login card -->
	<div class="split-right">
		<div class="login-card">
			<div class="login-header">
				<h2>Sign In</h2>
				<p>Access your dashboard</p>
			</div>

			<!-- Flash messages (PHP) -->
			<?php if ($this->session->flashdata('success')): ?>
				<div class="alert alert-success" id="flashMsg">
					<i class="fas fa-check-circle"></i>
					<?= $this->session->flashdata('success') ?>
				</div>
			<?php endif; ?>
			<?php if ($this->session->flashdata('info')): ?>
				<div class="alert alert-info" id="flashMsg">
					<i class="fas fa-info-circle"></i>
					<?= $this->session->flashdata('info') ?>
				</div>
			<?php endif; ?>
			<?php if ($this->session->flashdata('error')): ?>
				<div class="alert alert-danger" id="flashMsg">
					<i class="fas fa-exclamation-circle"></i>
					<?= $this->session->flashdata('error') ?>
				</div>
			<?php endif; ?>

			<div class="form-group">
				<label for="email"><i class="fas fa-envelope"></i>Email Address</label>
				<input type="email" class="form-control" id="email" placeholder="Enter your email" autofocus>
			</div>
			<div class="form-group">
				<label for="password"><i class="fas fa-lock"></i>Password</label>
				<input type="password" class="form-control" id="password" placeholder="Enter your password">
			</div>

			<button class="btn-login" onclick="loginUser()">
				<i class="fas fa-sign-in-alt"></i> Login
			</button>

			<div id="msg"></div>

			<div class="login-footer">
				Don't have an account?
				<a href="<?= base_url('register-society') ?>">Create account</a>
			</div>
		</div>
	</div>

	<!-- Theme Toggle Button -->
	<div class="theme-toggle-login" id="themeToggle">
		<i class="fas fa-sun" id="themeIcon"></i>
	</div>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script>
		function loginUser() {
			const email = $('#email').val();
			const password = $('#password').val();
			const msgBox = $('#msg');

			if (!email || !password) {
				msgBox.removeClass('success').addClass('error').text('Email and password are required');
				return;
			}

			$.post("<?= base_url('login') ?>", {
				email: email,
				password: password
			}, function (res) {
				if (res.status) {
					msgBox.removeClass('error').addClass('success').text(res.msg);
					setTimeout(() => {
						window.location.href = res.redirect;
					}, 1000);
				} else {
					msgBox.removeClass('success').addClass('error').text(res.msg);
				}
			}, 'json').fail(() => {
				msgBox.removeClass('success').addClass('error').text('Server error (500). Check backend.');
			});
		}

		// Auto-dismiss flash messages
		const flash = document.getElementById('flashMsg');
		if (flash) {
			setTimeout(() => {
				flash.style.transition = 'opacity 0.4s ease';
				flash.style.opacity = '0';
				setTimeout(() => flash.remove(), 400);
			}, 4000);
		}

		// Dark mode toggle with localStorage
		(function () {
			const savedTheme = localStorage.getItem('theme');
			if (savedTheme === 'dark') {
				document.body.classList.add('dark-mode');
				$('#themeIcon').removeClass('fa-sun').addClass('fa-moon');
			}

			$('#themeToggle').click(function () {
				document.body.classList.toggle('dark-mode');
				const isDark = document.body.classList.contains('dark-mode');
				$('#themeIcon').toggleClass('fa-sun fa-moon');
				localStorage.setItem('theme', isDark ? 'dark' : 'light');
			});
		})();
	</script>
</body>

</html>
