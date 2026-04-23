<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,viewport-fit=cover">
	<title>My Profile · SocietyHub</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<!-- Additional profile‑specific styles (matching notices style) -->
	<style>
		/* Profile card adjustments */
		.profile-avatar-wrapper {
			position: relative;
			width: 140px;
			height: 140px;
			margin: 0 auto 1.2rem;
			cursor: pointer;
		}

		.profile-avatar-wrapper img {
			width: 100%;
			height: 100%;
			border-radius: 50%;
			object-fit: cover;
			border: 4px solid white;
			box-shadow: 0 12px 24px -8px rgba(59, 130, 246, 0.25);
			transition: all 0.2s;
		}

		.avatar-upload-badge {
			position: absolute;
			bottom: 6px;
			right: 6px;
			background: #6366f1;
			color: white;
			width: 40px;
			height: 40px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			border: 3px solid white;
			box-shadow: 0 6px 14px rgba(79, 70, 229, 0.3);
			transition: 0.2s;
		}

		.profile-avatar-wrapper:hover .avatar-upload-badge {
			background: #4f46e5;
			transform: scale(1.05);
		}

		.profile-meta-tag {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: #eef2ff;
			color: #4338ca;
			border-radius: 40px;
			padding: 0.3rem 1rem;
			font-size: 0.85rem;
			font-weight: 500;
		}

		.profile-form-card {
			background: var(--card-bg);
			border-radius: 20px;
			padding: 24px;
			border: 1px solid var(--border);
			margin-bottom: 24px;
		}

		.profile-form-title {
			font-size: 1.3rem;
			font-weight: 700;
			margin-bottom: 20px;
			display: flex;
			align-items: center;
			gap: 10px;
			color: #0f172a;
		}

		.profile-form-title i {
			color: #6366f1;
			background: #e0e7ff;
			padding: 8px;
			border-radius: 14px;
			font-size: 1rem;
		}

		.input-group {
			display: flex;
			align-items: center;
			background: white;
			border: 1px solid #e2e8f0;
			border-radius: 14px;
			padding: 0 0 0 14px;
			transition: 0.2s;
		}

		.input-group:focus-within {
			border-color: #6366f1;
			box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
		}

		.input-group i {
			color: #94a3b8;
			width: 20px;
		}

		.input-group input,
		.input-group select {
			width: 100%;
			padding: 12px 12px 12px 8px;
			border: none;
			background: transparent;
			font-size: 0.95rem;
			outline: none;
		}

		.file-upload-area {
			background: #f8fafc;
			border-radius: 16px;
			padding: 12px 16px;
			border: 1px dashed #cbd5e1;
		}

		.file-upload-area input {
			padding: 6px 0;
		}

		.form-hint {
			font-size: 0.8rem;
			color: #64748b;
			margin-top: 6px;
			display: flex;
			align-items: center;
			gap: 5px;
		}

		.action-bar {
			display: flex;
			align-items: center;
			gap: 20px;
			flex-wrap: wrap;
			margin-top: 8px;
		}

		.last-updated {
			font-size: 0.85rem;
			color: #64748b;
			display: flex;
			align-items: center;
			gap: 5px;
		}

		.two-column-layout {
			display: grid;
			grid-template-columns: 320px 1fr;
			gap: 24px;
		}

		@media (max-width: 800px) {
			.two-column-layout {
				grid-template-columns: 1fr;
			}
		}

		/* Override any missing classes from common.css */
		.btn-warning {
			background: #f59e0b;
			color: white;
			border: none;
		}

		.btn-warning:hover {
			background: #d97706;
		}
	</style>
</head>

<body>
	<?php $activePage = 'profile';
	$this->load->view('sidebar'); ?>

	<div class="main" id="main">

		<!-- Flash Messages (matching notices style) -->
		<?php if ($this->session->flashdata('success')): ?>
			<div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i>
				<?= $this->session->flashdata('success') ?></div>
		<?php elseif ($this->session->flashdata('error')): ?>
			<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i>
				<?= $this->session->flashdata('error') ?></div>
		<?php elseif ($this->session->flashdata('upload_error')): ?>
			<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i>
				<?= $this->session->flashdata('upload_error') ?></div>
		<?php endif; ?>

		<!-- Page Header -->
		<div class="table-header" style="margin-bottom: 20px;">
			<h3><i class="fas fa-user-circle"></i> My Profile</h3>
			<div class="page-actions">
				<a href="<?= base_url('dashboard') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i>
					Dashboard</a>
			</div>
		</div>

		<!-- Main two‑column layout -->
		<div class="two-column-layout">
			<!-- Left Column: Profile Card (summary) -->
			<div>
				<div class="profile-form-card" style="text-align: center;">
					<div class="profile-avatar-wrapper" onclick="document.getElementById('profile_image').click();">
						<?php
						$img = isset($user->profile_image) && $user->profile_image
							? base_url('uploads/profile/' . $user->profile_image)
							: base_url('uploads/profile/default.png');
						?>
						<img src="<?= $img ?>" alt="Profile" id="previewImage">
						<div class="avatar-upload-badge">
							<i class="fas fa-camera"></i>
						</div>
					</div>
					<h2 style="font-size: 1.8rem; font-weight: 700; margin: 0.2rem 0; color: #0f172a;">
						<?= html_escape($user->name) ?></h2>
					<div style="color: #64748b; margin-bottom: 12px;"><i class="far fa-envelope"
							style="margin-right: 6px;"></i><?= html_escape($user->email) ?></div>
					<div style="display: flex; flex-direction: column; gap: 8px; align-items: center;">
						<span class="profile-meta-tag"><i class="fas fa-door-open"></i> Flat:
							<?= html_escape($user->flat_no ?: '—') ?></span>
						<span class="profile-meta-tag"><i class="fas fa-tag"></i>
							<?= ucfirst(html_escape($user->member_type ?: 'owner')) ?></span>
					</div>
					<?php if (isset($user->updated_at)): ?>
						<div style="margin-top: 20px; font-size: 0.8rem; color: #94a3b8;">
							<i class="far fa-clock"></i> Last updated:
							<?= date('d M Y, H:i', strtotime($user->updated_at)) ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Right Column: Forms -->
			<div>
				<!-- Personal Information Form -->
				<div class="profile-form-card">
					<div class="profile-form-title">
						<i class="fas fa-user-edit"></i> Personal Information
					</div>
					<form action="<?= base_url('profile/update_profile') ?>" method="post"
						enctype="multipart/form-data">
						<input type="hidden" name="old_profile_image"
							value="<?= isset($user->profile_image) ? html_escape($user->profile_image) : '' ?>">

						<div style="margin-bottom: 20px;">
							<label
								style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Profile
								Image</label>
							<div class="file-upload-area">
								<input type="file" name="profile_image" id="profile_image" accept="image/*">
							</div>
							<div class="form-hint"><i class="fas fa-info-circle"></i> JPG, PNG up to 2MB</div>
						</div>

						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
							<div style="grid-column: span 2;">
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Full
									Name</label>
								<div class="input-group">
									<i class="fas fa-user"></i>
									<input type="text" name="name" value="<?= html_escape($user->name) ?>" required>
								</div>
							</div>
							<div style="grid-column: span 2;">
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Email
									Address</label>
								<div class="input-group">
									<i class="fas fa-envelope"></i>
									<input type="email" name="email" value="<?= html_escape($user->email) ?>" required>
								</div>
							</div>
							<div>
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Phone
									Number</label>
								<div class="input-group">
									<i class="fas fa-phone-alt"></i>
									<input type="text" name="phone" value="<?= html_escape($user->phone) ?>">
								</div>
							</div>
							<div>
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Flat
									No.</label>
								<div class="input-group">
									<i class="fas fa-home"></i>
									<input type="text" name="flat_no" value="<?= html_escape($user->flat_no) ?>">
								</div>
							</div>
							<div style="grid-column: span 2;">
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Member
									Type</label>
								<div class="input-group">
									<i class="fas fa-users"></i>
									<select name="member_type">
										<?php $types = ['owner' => 'Owner', 'tenant' => 'Tenant', 'family' => 'Family', 'staff' => 'Staff', 'other' => 'Other']; ?>
										<?php foreach ($types as $k => $v): ?>
											<option value="<?= $k ?>" <?= (isset($user->member_type) && $user->member_type == $k) ? 'selected' : '' ?>><?= $v ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>

						<div class="action-bar">
							<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update
								Profile</button>
							<span class="last-updated"><i class="far fa-clock"></i> Last updated:
								<?= isset($user->updated_at) ? date('d M Y, H:i', strtotime($user->updated_at)) : '—' ?></span>
						</div>
					</form>
				</div>

				<!-- Change Password Card -->
				<div class="profile-form-card">
					<div class="profile-form-title">
						<i class="fas fa-lock"></i> Change Password
					</div>
					<form action="<?= base_url('profile/change_password') ?>" method="post">
						<div style="display: grid; gap: 16px;">
							<div>
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Current
									Password</label>
								<div class="input-group">
									<i class="fas fa-key"></i>
									<input type="password" name="current_password" required>
								</div>
							</div>
							<div>
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">New
									Password</label>
								<div class="input-group">
									<i class="fas fa-lock"></i>
									<input type="password" name="new_password" required>
								</div>
							</div>
							<div>
								<label
									style="font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; display: block; color: #334155;">Confirm
									New Password</label>
								<div class="input-group">
									<i class="fas fa-check-circle"></i>
									<input type="password" name="confirm_password" required>
								</div>
							</div>
						</div>

						<div class="action-bar" style="margin-top: 20px;">
							<button type="submit" class="btn btn-warning"><i class="fas fa-key"></i> Change
								Password</button>
							<span class="form-hint"><i class="fas fa-shield-alt"></i> Minimum 8 characters</span>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Image preview script -->
	<script>
		document.getElementById('profile_image')?.addEventListener('change', function (e) {
			const file = this.files && this.files[0];
			if (!file) return;
			if (!file.type.startsWith('image/')) {
				alert('Please select an image file.');
				return;
			}

			const reader = new FileReader();
			reader.onload = function (evt) {
				document.getElementById('previewImage').src = evt.target.result;
			};

			reader.readAsDataURL(file);
		});

		// Auto‑hide flash messages (matching notices behaviour)
		document.addEventListener('DOMContentLoaded', () => {
			const flash = document.getElementById('flashMsg');
			if (flash) {
				setTimeout(() => {
					flash.style.transition = 'opacity 0.4s';
					flash.style.opacity = '0';
					setTimeout(() => flash.remove(), 400);
				}, 3500);
			}
		});
	</script>
</body>
</html>
