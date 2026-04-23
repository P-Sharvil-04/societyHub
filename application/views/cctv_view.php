<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
	<title>CCTV Dashboard · SocietyHub</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet">
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
	<style>
		/* extra refinements matching staff page aesthetics */
		.cctv-layout {
			display: grid;
			grid-template-columns: 380px 1fr;
			gap: 24px;
			margin-top: 10px;
		}

		.add-camera-card {
			background: var(--card-bg);
			border-radius: 24px;
			border: 1px solid var(--border);
			padding: 1.5rem;
			box-shadow: var(--shadow-sm);
			transition: all 0.2s ease;
			height: fit-content;
			position: sticky;
			top: 20px;
		}

		.add-camera-card h3 {
			font-size: 1.25rem;
			font-weight: 700;
			margin-bottom: 1.25rem;
			display: flex;
			align-items: center;
			gap: 10px;
			color: var(--text-dark);
		}

		.add-camera-card h3 i {
			color: var(--primary);
		}

		.cameras-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
			gap: 24px;
		}

		.camera-card {
			background: var(--card-bg);
			border-radius: 24px;
			border: 1px solid var(--border);
			overflow: hidden;
			transition: transform 0.2s, box-shadow 0.2s;
			box-shadow: var(--shadow-sm);
		}

		.camera-card:hover {
			transform: translateY(-3px);
			box-shadow: var(--shadow-md);
		}

		.camera-header {
			padding: 1rem 1.25rem;
			border-bottom: 1px solid var(--border);
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 12px;
			background: rgba(99, 102, 241, 0.02);
		}

		.camera-info h4 {
			font-weight: 700;
			font-size: 1rem;
			margin: 0 0 6px 0;
			color: var(--text-dark);
		}

		.camera-meta {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			font-size: 0.7rem;
			color: var(--text-light);
		}

		.camera-meta span i {
			width: 14px;
			margin-right: 4px;
		}

		.status-pill {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 5px 12px;
			border-radius: 40px;
			font-size: 0.7rem;
			font-weight: 600;
			text-transform: capitalize;
			background: rgba(34, 197, 94, 0.08);
			color: #15803d;
			border: 1px solid rgba(34, 197, 94, 0.2);
			flex-shrink: 0;
		}

		.status-pill.offline {
			background: rgba(239, 68, 68, 0.08);
			color: #b91c1c;
			border-color: rgba(239, 68, 68, 0.2);
		}

		.status-pill.pending {
			background: rgba(245, 158, 11, 0.08);
			color: #b45309;
			border-color: rgba(245, 158, 11, 0.2);
		}

		.status-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: currentColor;
		}

		.video-wrap {
			background: #0f1117;
			position: relative;
			min-height: 190px;
		}

		video.player {
			width: 100%;
			height: 190px;
			object-fit: cover;
			background: #0a0c12;
			display: block;
		}

		.camera-footer {
			padding: 12px 1.25rem;
			border-top: 1px solid var(--border);
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 10px;
			font-size: 0.75rem;
			color: var(--text-light);
			background: var(--card-bg);
		}

		.cam-key {
			font-family: monospace;
			background: var(--bg-light);
			padding: 4px 8px;
			border-radius: 20px;
			font-size: 0.7rem;
		}

		.icon-btn-group {
			display: flex;
			gap: 8px;
		}

		.icon-btn {
			border: 1px solid var(--border);
			background: transparent;
			padding: 6px 12px;
			border-radius: 30px;
			font-size: 0.7rem;
			font-weight: 500;
			cursor: pointer;
			transition: 0.2s;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			color: var(--text-dark);
		}

		.icon-btn:hover {
			background: rgba(99, 102, 241, 0.08);
			border-color: var(--primary);
		}

		.icon-btn.delete:hover {
			background: rgba(239, 68, 68, 0.1);
			border-color: var(--danger);
			color: var(--danger);
		}

		.error-message {
			padding: 10px 1rem;
			background: rgba(239, 68, 68, 0.05);
			border-top: 1px solid var(--border);
			font-size: 0.7rem;
			color: #dc2626;
			display: flex;
			align-items: center;
			gap: 6px;
		}

		.empty-state {
			text-align: center;
			padding: 50px 20px;
			background: var(--card-bg);
			border-radius: 28px;
			border: 1px dashed var(--border);
			color: var(--text-light);
		}

		.page-header-actions {
			display: flex;
			justify-content: space-between;
			align-items: center;
			flex-wrap: wrap;
			gap: 16px;
			margin-bottom: 20px;
		}

		@media (max-width: 1000px) {
			.cctv-layout {
				grid-template-columns: 1fr;
				gap: 20px;
			}

			.add-camera-card {
				position: relative;
				top: 0;
			}
		}

		@media (max-width: 640px) {
			.cameras-grid {
				grid-template-columns: 1fr;
			}
		}

		.notification {
			position: fixed;
			top: 20px;
			right: 20px;
			z-index: 1050;
			background: var(--card-bg);
			border-left: 4px solid var(--success);
			padding: 12px 20px;
			border-radius: 14px;
			box-shadow: var(--shadow-md);
			display: flex;
			align-items: center;
			gap: 12px;
			animation: slideIn 0.25s ease;
			font-weight: 500;
		}

		.notification.error {
			border-left-color: var(--danger);
		}

		@keyframes slideIn {
			from {
				transform: translateX(100%);
				opacity: 0;
			}

			to {
				transform: translateX(0);
				opacity: 1;
			}
		}

		@keyframes slideOut {
			from {
				transform: translateX(0);
				opacity: 1;
			}

			to {
				transform: translateX(100%);
				opacity: 0;
			}
		}

		.btn-primary {
			background: var(--primary);
			border: none;
			transition: 0.2s;
		}

		.btn-primary:hover {
			background: var(--primary-dark);
			transform: translateY(-1px);
		}

		.form-control,
		.form-select {
			background: var(--bg-light);
			border: 1px solid var(--border);
			transition: 0.2s;
		}

		.form-control:focus,
		.form-select:focus {
			border-color: var(--primary);
			box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
		}

		.stat-card {
			transition: transform 0.2s;
		}

		.stat-card:hover {
			transform: translateY(-2px);
		}

		body,
		.main {
			background: var(--bg);
		}
	</style>
</head>

<body>
	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'cctv';
	include('sidebar.php'); ?>

	<div class="main" id="main">
		<!-- Flash Messages -->
		<?php if ($this->session->flashdata('success')): ?>
			<div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i>
				<?= $this->session->flashdata('success') ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i>
				<?= $this->session->flashdata('error') ?></div>
		<?php endif; ?>

		<!-- Stats Cards -->
		<?php
		$totalCameras = count($cameras);
		$online = 0;
		$offline = 0;
		foreach ($cameras as $c) {
			$status = $c->last_status ?? 'pending';
			if ($status === 'online')
				$online++;
			else
				$offline++;
		}
		$serverHost = parse_url($hls_base_url, PHP_URL_HOST) ?: 'stream';
		?>
		<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-video"></i></div>
				<div class="stat-info">
					<h4>Total Cameras</h4>
					<h2><?= (int) $totalCameras ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-plug"></i></div>
				<div class="stat-info">
					<h4>Online</h4>
					<h2><?= (int) $online ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-power-off"></i></div>
				<div class="stat-info">
					<h4>Offline</h4>
					<h2><?= (int) $offline ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-server"></i></div>
				<div class="stat-info">
					<h4>HLS Server</h4>
					<h2><?= html_escape($serverHost) ?></h2>
				</div>
			</div>
		</div>

		<div class="cctv-layout">
			<!-- LEFT: Add Camera Form (only for chairman/superadmin) -->
			<?php if (in_array($user_role, ['chairman', 'super_admin'])): ?>
				<div class="add-camera-card">
					<h3><i class="fas fa-plus-circle"></i> Register New Camera</h3>
					<form method="post" action="<?= base_url('cctv/store') ?>" id="addCameraForm">
						<div class="form-group">
							<label><i class="fas fa-tag"></i> Camera Name *</label>
							<input type="text" name="name" class="form-control" placeholder="Main Gate, Parking, Lobby ..."
								required>
						</div>
						<div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
							<div class="form-group"><label>Brand *</label><select name="brand" class="form-control"
									required>
									<option value="hikvision">Hikvision</option>
									<option value="dahua">Dahua</option>
									<option value="cpplus">CP Plus</option>
									<option value="other">Other</option>
								</select></div>
							<div class="form-group"><label>IP Address *</label><input type="text" name="ip_address"
									class="form-control" placeholder="192.168.1.100" required></div>
						</div>
						<div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
							<div class="form-group"><label>Port *</label><input type="number" name="port"
									class="form-control" value="554" required></div>
							<div class="form-group"><label>Channel</label><input type="number" name="channel"
									class="form-control" value="1" min="1"></div>
						</div>
						<div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
							<div class="form-group"><label>Username</label><input type="text" name="username"
									class="form-control" placeholder="admin"></div>
							<div class="form-group"><label>Password</label><input type="password" name="password"
									class="form-control" placeholder="••••••"></div>
						</div>
						<div class="form-group"><label>Stream Type</label><select name="stream_type" class="form-control">
								<option value="sub">Sub Stream (lower bandwidth)</option>
								<option value="main">Main Stream (higher quality)</option>
							</select></div>

						<?php if ($user_role === 'super_admin' && isset($societies)): ?>
							<div class="form-group">
								<label><i class="fas fa-building"></i> Society *</label>
								<select name="society_id" class="form-control" required>
									<option value="">-- Select Society --</option>
									<?php foreach ($societies as $soc): ?>
										<option value="<?= (int) $soc['id'] ?>"><?= html_escape($soc['name']) ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<button type="submit" class="btn btn-primary"
							style="width:100%; justify-content:center; margin-top:8px;">
							<i class="fas fa-save"></i> Save Camera
						</button>
					</form>
				</div>
			<?php else: ?>
				<div class="add-camera-card" style="background: rgba(99,102,241,0.03); text-align:center;">
					<i class="fas fa-lock"
						style="font-size:2rem; color:var(--primary); margin-bottom:10px; display:block;"></i>
					<p>Only chairman can add new cameras.</p>
				</div>
			<?php endif; ?>

			<!-- RIGHT: Cameras Grid -->
			<div>
				<?php if (!empty($cameras)): ?>
					<div class="cameras-grid">
						<?php foreach ($cameras as $cam):
							$hlsUrl = rtrim($hls_base_url, '/') . '/' . $cam->cam_key . '/index.m3u8';
							$statusText = $cam->last_status ?? 'pending';
							$statusClass = ($statusText === 'online') ? 'online' : (($statusText === 'offline') ? 'offline' : 'pending');
							?>
							<div class="camera-card" data-cam-id="<?= (int) $cam->id ?>">
								<div class="camera-header">
									<div class="camera-info">
										<h4><?= html_escape($cam->name) ?></h4>
										<div class="camera-meta">
											<span><i class="fas fa-microchip"></i> <?= html_escape($cam->brand) ?></span>
											<span><i class="fas fa-network-wired"></i>
												<?= html_escape($cam->ip_address) ?>:<?= (int) $cam->port ?></span>
											<span><i class="fas fa-layer-group"></i> Ch.<?= (int) $cam->channel ?> /
												<?= html_escape($cam->stream_type) ?></span>
										</div>
									</div>
									<div class="status-pill <?= $statusClass ?>"><span class="status-dot"></span>
										<?= ucfirst($statusText) ?></div>
								</div>
								<div class="video-wrap">
									<video id="video-<?= (int) $cam->id ?>" class="player" controls muted playsinline></video>
								</div>
								<div class="camera-footer">
									<span class="cam-key"><i class="fas fa-key"></i> <?= html_escape($cam->cam_key) ?></span>
									<div class="icon-btn-group">
										<a class="icon-btn" href="<?= $hlsUrl ?>" target="_blank"><i
												class="fas fa-external-link-alt"></i> HLS</a>
										<?php if ($can_add || ($user_role === 'chairman' && $cam->society_id == $user_society_id)): ?>
											<button class="icon-btn delete"
												onclick="openDeleteCameraModal(<?= (int) $cam->id ?>, '<?= html_escape(addslashes($cam->name)) ?>')">
												<i class="fas fa-trash"></i> Delete
											</button>
										<?php endif; ?>
									</div>
								</div>
								<?php if (!empty($cam->last_error)): ?>
									<div class="error-message"><i class="fas fa-exclamation-triangle"></i>
										<?= html_escape($cam->last_error) ?></div>
								<?php endif; ?>
							</div>

							<script>
								(function () {
									var video = document.getElementById('video-<?= (int) $cam->id ?>');
									var src = <?= json_encode($hlsUrl) ?>;
									if (window.Hls && Hls.isSupported()) {
										var hls = new Hls({ lowLatencyMode: true, backBufferLength: 30, debug: false });
										hls.loadSource(src);
										hls.attachMedia(video);
										hls.on(Hls.Events.ERROR, function (evt, data) { if (data.fatal) console.warn('HLS error'); });
									} else if (video.canPlayType('application/vnd.apple.mpegurl')) {
										video.src = src;
									}
								})();
							</script>
						<?php endforeach; ?>
					</div>
				<?php else: ?>
					<div class="empty-state">
						<i class="fas fa-video-slash"
							style="font-size: 3rem; opacity: 0.5; margin-bottom: 16px; display: block;"></i>
						<p style="font-weight:500;">No cameras added yet</p>
						<p style="font-size:0.85rem;">
							<?= ($can_add) ? 'Use the form on the left to register your first IP camera.' : 'Please contact your society chairman to add cameras.' ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Delete Confirmation Modal -->
	<div class="modal" id="deleteCameraModal">
		<div class="modal-content" style="max-width:450px;">
			<div class="modal-header">
				<h3><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Confirm Deletion</h3>
				<span class="modal-close" onclick="closeModal('deleteCameraModal')">&times;</span>
			</div>
			<div class="modal-body" style="text-align:center; padding:20px;">
				<i class="fas fa-trash-alt"
					style="font-size: 2.5rem; color:var(--danger); margin-bottom: 12px; display:block;"></i>
				<p>Are you sure you want to delete camera <strong id="deleteCamName"></strong>?</p>
				<p class="text-muted" style="font-size:0.8rem;">This action is permanent.</p>
			</div>
			<div class="modal-footer" style="justify-content:center; gap:12px;">
				<button class="btn btn-outline" onclick="closeModal('deleteCameraModal')"><i class="fas fa-times"></i>
					Cancel</button>
				<button class="btn btn-primary" style="background:var(--danger); border-color:var(--danger);"
					onclick="confirmDeleteCamera()"><i class="fas fa-trash"></i> Delete Permanently</button>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		// Modal helpers
		function openModal(id) { $('#' + id).addClass('active'); $('#overlay').addClass('active'); }
		function closeModal(id) { $('#' + id).removeClass('active'); $('#overlay').removeClass('active'); }
		$('#overlay').on('click', function () { $('.modal.active').removeClass('active'); $(this).removeClass('active'); });
		$(document).on('keydown', function (e) { if (e.key === 'Escape') { $('.modal.active').removeClass('active'); $('#overlay').removeClass('active'); } });

		let deleteCamId = null;
		function openDeleteCameraModal(id, name) {
			deleteCamId = id;
			$('#deleteCameraId').val(id);
			$('#deleteCamName').text(name);
			openModal('deleteCameraModal');
		}
		function confirmDeleteCamera() {
			if (!deleteCamId) return;

			$.ajax({
				url: '<?= base_url('cctv/delete/') ?>' + deleteCamId,
				type: 'POST',
				dataType: 'json',
				data: { '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>' },
				success: function (res) {
					if (res.status === 'success') {
						showNotification(res.message || 'Camera deleted successfully', 'success');
						setTimeout(() => location.reload(), 800);
					} else {
						showNotification(res.message || 'Deletion failed', 'error');
						closeModal('deleteCameraModal');
					}
				},
				error: function () {
					showNotification('Server error', 'error');
					closeModal('deleteCameraModal');
				}
			});
		}

		// Auto-hide flash message
		document.addEventListener('DOMContentLoaded', function () {
			let flash = document.getElementById('flashMsg');
			if (flash) setTimeout(() => { flash.style.opacity = '0'; setTimeout(() => flash.remove(), 500); }, 3800);
		});

		function showNotification(message, type) {
			let n = document.createElement('div');
			n.className = 'notification ' + (type || 'success');
			n.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + message;
			document.body.appendChild(n);
			setTimeout(() => { n.style.animation = 'slideOut 0.3s ease'; setTimeout(() => n.remove(), 300); }, 3500);
		}

		document.getElementById('addCameraForm')?.addEventListener('submit', function () {
			let btn = this.querySelector('button[type="submit"]');
			if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
		});
	</script>
</body>

</html>
