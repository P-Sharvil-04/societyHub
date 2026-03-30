<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,viewport-fit=cover">
	<title>SocietyHub · Notices</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/notices.css') ?>">
	<style>
		.notice-society-badge {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: #eef1fb;
			color: #3b5bdb;
			border: 1px solid #c5d0f5;
			border-radius: 12px;
			padding: 2px 8px;
			font-size: .72rem;
			font-weight: 500;
			margin-top: 4px;
		}

		.active-filter-pill {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: #e0e7ff;
			color: #3730a3;
			border: 1px solid #c7d2fe;
			border-radius: 20px;
			padding: 3px 10px 3px 12px;
			font-size: .78rem;
			font-weight: 500;
		}

		.active-filter-pill a {
			color: #6366f1;
			text-decoration: none;
			font-weight: 700;
			font-size: .85rem;
			margin-left: 8px;
		}

		/* Overlay */
		.overlay {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, .55);
			backdrop-filter: blur(6px);
			opacity: 0;
			visibility: hidden;
			transition: .25s ease;
			z-index: 999;
		}

		.overlay.active {
			opacity: 1;
			visibility: visible;
		}

		/* Modal wrapper */
		.modal {
			position: fixed;
			inset: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
			opacity: 0;
			visibility: hidden;
			pointer-events: none;
			transition: .25s ease;
			z-index: 1000;
		}

		.modal.active {
			opacity: 1;
			visibility: visible;
			pointer-events: auto;
		}

		/* Card */
		.modal-content {
			width: min(100%, 680px);
			max-height: 90vh;
			overflow: auto;
			background: #fff;
			border-radius: 22px;
			box-shadow: 0 24px 80px rgba(15, 23, 42, .25);
			transform: translateY(12px) scale(.98);
			transition: .25s ease;
			border: 1px solid rgba(226, 232, 240, .9);
		}

		.modal.active .modal-content {
			transform: translateY(0) scale(1);
		}

		/* Header */
		.modal-header {
			padding: 20px 24px 16px;
			border-bottom: 1px solid #e2e8f0;
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 16px;
		}

		.modal-title {
			margin: 0;
			font-size: 1.25rem;
			font-weight: 700;
			color: #0f172a;
			line-height: 1.2;
		}

		.modal-subtitle {
			margin-top: 6px;
			font-size: .9rem;
			color: #64748b;
		}

		/* Body */
		.modal-body {
			padding: 22px 24px;
		}

		.modal-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 16px;
		}

		.modal-grid-full {
			grid-column: 1 / -1;
		}

		/* Form controls */
		.modal label {
			display: block;
			margin: 0 0 7px;
			font-size: .84rem;
			font-weight: 600;
			color: #334155;
		}

		.modal input,
		.modal textarea,
		.modal select {
			width: 100%;
			border: 1px solid #cbd5e1;
			border-radius: 14px;
			padding: 12px 14px;
			font-size: .95rem;
			background: #fff;
			outline: none;
			transition: .2s ease;
			color: #0f172a;
		}

		.modal textarea {
			min-height: 140px;
			resize: vertical;
		}

		.modal input:focus,
		.modal textarea:focus,
		.modal select:focus {
			border-color: #6366f1;
			box-shadow: 0 0 0 4px rgba(99, 102, 241, .12);
		}

		/* Footer */
		.modal-footer {
			padding: 18px 24px 24px;
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			border-top: 1px solid #e2e8f0;
			background: #fafafa;
			border-bottom-left-radius: 22px;
			border-bottom-right-radius: 22px;
		}

		/* Buttons inside modal */
		.modal-footer .btn,
		.modal-footer button {
			border: none;
			border-radius: 12px;
			padding: 10px 16px;
			font-weight: 600;
			cursor: pointer;
			transition: .2s ease;
		}

		.modal-footer .btn-primary {
			background: #6366f1;
			color: #fff;
		}

		.modal-footer .btn-primary:hover {
			background: #4f46e5;
		}

		.modal-footer .btn-outline {
			background: #fff;
			color: #334155;
			border: 1px solid #cbd5e1;
		}

		.modal-footer .btn-outline:hover {
			background: #f8fafc;
		}

		/* View mode */
		.notice-view-card {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 16px;
		}

		.notice-view-row {
			display: grid;
			grid-template-columns: 150px 1fr;
			gap: 10px;
			padding: 8px 0;
			border-bottom: 1px dashed #e2e8f0;
		}

		.notice-view-row:last-child {
			border-bottom: none;
		}

		.notice-view-label {
			font-size: .82rem;
			font-weight: 700;
			color: #475569;
		}

		.notice-view-value {
			font-size: .92rem;
			color: #0f172a;
			word-break: break-word;
		}

		/* Responsive */
		@media (max-width: 768px) {
			.modal-content {
				width: 100%;
				border-radius: 18px;
			}

			.modal-grid {
				grid-template-columns: 1fr;
			}

			.notice-view-row {
				grid-template-columns: 1fr;
			}

			.modal-header,
			.modal-body,
			.modal-footer {
				padding-left: 16px;
				padding-right: 16px;
			}
		}
	</style>
</head>

<body>
	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'notices';
	$this->load->view('sidebar'); ?>

	<div class="main" id="main">

		<?php if ($this->session->flashdata('success')): ?>
			<div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i>
				<?= $this->session->flashdata('success') ?></div>
		<?php elseif ($this->session->flashdata('error')): ?>
			<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i>
				<?= $this->session->flashdata('error') ?></div>
		<?php endif; ?>

		<!-- Stats -->
		<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-bullhorn"></i></div>
				<div class="stat-info">
					<h4>Total Notices</h4>
					<h2><?= (int) ($stats['total'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-check-circle"></i></div>
				<div class="stat-info">
					<h4>Active</h4>
					<h2><?= (int) ($stats['active'] ?? 0) ?></h2>
					<div class="stat-trend" style="color:var(--success)"><i class="fas fa-circle"></i> Live</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-clock"></i></div>
				<div class="stat-info">
					<h4>Scheduled</h4>
					<h2><?= (int) ($stats['scheduled'] ?? 0) ?></h2>
					<div class="stat-trend" style="color:var(--warning)"><i class="fas fa-calendar"></i> Upcoming</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-archive"></i></div>
				<div class="stat-info">
					<h4>Expired</h4>
					<h2><?= (int) ($stats['expired'] ?? 0) ?></h2>
					<div class="stat-trend" style="color:var(--danger)"><i class="fas fa-arrow-down"></i> Archived</div>
				</div>
			</div>
		</div>

		<!-- Filter bar — GET -->
		<form method="GET" action="<?= site_url('notices') ?>">
			<div class="filter-section">
				<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
					<div class="filter-group">
						<label><i class="fas fa-building"></i> Society</label>
						<select name="society_id" class="filter-select" onchange="this.form.submit()">
							<option value="">All Societies</option>
							<?php $curSoc = array_key_exists('society_id', $filters) ? $filters['society_id'] : null; ?>
							<?php foreach ($societies as $soc): ?>
								<?php $sel = ($curSoc !== null && (int) $curSoc === (int) $soc['id']) ? 'selected' : ''; ?>
								<option value="<?= (int) $soc['id'] ?>" <?= $sel ?>><?= html_escape($soc['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<div class="filter-group">
					<label><i class="fas fa-filter"></i> Type</label>
					<select name="type" class="filter-select" onchange="this.form.submit()">
						<option value="">All Types</option>
						<?php foreach (['general', 'important', 'event', 'maintenance'] as $t): ?>
							<option value="<?= $t ?>" <?= ($filters['type'] === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="filter-group">
					<label><i class="fas fa-circle"></i> Status</label>
					<select name="status" class="filter-select" onchange="this.form.submit()">
						<option value="">All Status</option>
						<?php foreach (['active', 'scheduled', 'expired'] as $st): ?>
							<option value="<?= $st ?>" <?= ($filters['status'] === $st) ? 'selected' : '' ?>>
								<?= ucfirst($st) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="search-box">
					<i class="fas fa-search"></i>
					<input type="text" name="search" placeholder="Search notices..."
						value="<?= html_escape($filters['search'] ?? '') ?>">
				</div>

				<div style="display:flex;gap:8px;align-items:flex-end;">
					<button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
					<?php
					$anyFilter = (!empty($filters['search'])) || (!empty($filters['type'])) || (!empty($filters['status'])) || (isset($filters['society_id']) && $filters['society_id'] !== null);
					?>
					<?php if ($anyFilter): ?>
						<a href="<?= site_url('notices') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
					<?php endif; ?>
				</div>
			</div>
		</form>

		<!-- Active filter pills -->
		<?php if ($anyFilter): ?>
			<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">
				<span style="font-size:.8rem;color:var(--text-light);font-weight:600;">Active filters:</span>

				<?php if (!empty($filters['society_id']) && !empty($isSuperAdmin)): ?>
					<?php
					$sLabel = '';
					foreach ($societies as $s) {
						if ((int) $s['id'] === (int) $filters['society_id']) {
							$sLabel = $s['name'];
							break;
						}
					}
					?>
					<span class="active-filter-pill"><i class="fas fa-building"></i> <?= html_escape($sLabel) ?>
						<a
							href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['society_id' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>

				<?php if (!empty($filters['type'])): ?>
					<span class="active-filter-pill"><i class="fas fa-filter"></i> <?= ucfirst(html_escape($filters['type'])) ?>
						<a href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['type' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>

				<?php if (!empty($filters['status'])): ?>
					<span class="active-filter-pill"><i class="fas fa-circle"></i>
						<?= ucfirst(html_escape($filters['status'])) ?>
						<a href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['status' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>

				<?php if (!empty($filters['search'])): ?>
					<span class="active-filter-pill"><i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>"
						<a href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['search' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Toolbar -->
		<div class="table-header" style="margin-bottom:15px;">
			<h3><i class="fas fa-bell"></i> All Notices <small
					style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= count($notices) ?>
					records)</small></h3>
			<div class="table-actions">
				<div class="view-toggle">
					<button class="view-btn active" onclick="toggleView('grid')" id="gridViewBtn"><i
							class="fas fa-th-large"></i> Grid</button>
					<button class="view-btn" onclick="toggleView('table')" id="tableViewBtn"><i
							class="fas fa-table"></i> Table</button>
				</div>
			</div>

			<a href="<?= site_url('notices') ?>" class="btn btn-outline" title="Clear all filters and refresh"
				style="display:flex;align-items:center;gap:6px;"><i class="fas fa-sync-alt"></i> Refresh</a>

			<div class="page-actions">
				<?php
				$exportQuery = http_build_query(array_merge($filters ?? [], ['society_id' => $filters['society_id'] ?? '']));
				?>
				<a href="<?= site_url('notice_controller/export?' . $exportQuery) ?>" class="btn btn-outline"><i
						class="fas fa-download"></i> Export</a>
				<button type="button" class="btn btn-primary" onclick="openAddModal()"><i
						class="fas fa-plus-circle"></i> Create Notice</button>
			</div>
		</div>

		<!-- GRID VIEW -->
		<div id="noticesGridView" class="notices-grid">
			<?php if (empty($notices)): ?>
				<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-light);">
					<i class="fas fa-bell-slash" style="font-size:3rem;display:block;margin-bottom:12px;"></i>
					No notices found.<br><br>
					<button type="button" class="btn btn-primary" onclick="openAddModal()"><i
							class="fas fa-plus-circle"></i> Create First Notice</button>
				</div>
			<?php else: ?>
				<?php foreach ($notices as $n):
					switch ($n['notice_type']) {
						case 'important':
							$typeIcon = 'fa-exclamation-circle';
							break;
						case 'event':
							$typeIcon = 'fa-calendar-alt';
							break;
						case 'maintenance':
							$typeIcon = 'fa-tools';
							break;
						default:
							$typeIcon = 'fa-bullhorn';
					}
					$createdDate = date('d M Y', strtotime($n['created_at']));
					$validUntil = !empty($n['valid_until']) ? date('d M Y', strtotime($n['valid_until'])) : '—';
					?>
					<div class="notice-card <?= html_escape($n['notice_type']) ?>">
						<div class="notice-header">
							<div class="notice-type"><i class="fas <?= $typeIcon ?>"></i>
								<h4><?= ucfirst(html_escape($n['notice_type'])) ?></h4>
							</div>
							<span class="notice-status <?= html_escape($n['status']) ?>"><?= strtoupper($n['status']) ?></span>
						</div>
						<div class="notice-title"><?= html_escape($n['title']) ?></div>
						<div class="notice-description"><?= html_escape(substr($n['description'], 0, 120)) ?>...</div>
						<?php if (!empty($isSuperAdmin) && !empty($n['society_name'])): ?>
							<div style="margin:6px 0;"><span class="notice-society-badge"><i class="fas fa-city"></i>
									<?= html_escape($n['society_name']) ?></span></div>
						<?php endif; ?>
						<div class="notice-meta"><span><i class="fas fa-calendar-plus"></i> <?= $createdDate ?></span><span><i
									class="fas fa-hourglass-end"></i> <?= $validUntil ?></span><span><i
									class="fas fa-users"></i> <?= ucfirst(html_escape($n['target_audience'])) ?></span></div>
						<div class="notice-footer">
							<div class="notice-actions" style="margin-left:auto;display:flex;gap:6px;">
								<button type="button" class="btn-icon" title="View"
									onclick='viewNotice(<?= htmlspecialchars(json_encode($n), ENT_QUOTES) ?>)'><i
										class="fas fa-eye"></i></button>
								<button type="button" class="btn-icon" title="Edit"
									onclick='editNotice(<?= (int) $n['id'] ?>, <?= htmlspecialchars(json_encode($n), ENT_QUOTES) ?>)'><i
										class="fas fa-edit"></i></button>
								<button type="button" class="btn-icon delete" title="Delete"
									onclick="openDeleteModal(<?= (int) $n['id'] ?>)"><i class="fas fa-trash"></i></button>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<!-- TABLE VIEW (hidden by default) -->
		<div id="noticesTableView" class="table-section" style="display:none;">
			<div class="table-wrapper">
				<table id="noticesTable">
					<thead>
						<tr>
							<th>Notice ID</th>
							<th>Title</th>
							<th>Type</th><?php if (!empty($isSuperAdmin)): ?>
								<th>Society</th><?php endif; ?>
							<th>Valid Until</th>
							<th>Audience</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($notices)): ?>
							<tr>
								<td colspan="<?= !empty($isSuperAdmin) ? 8 : 7 ?>" style="text-align:center;padding:40px;">
									No notices found</td>
							</tr>
						<?php else:
							foreach ($notices as $n):
								$vu = !empty($n['valid_until']) ? date('d/m/Y', strtotime($n['valid_until'])) : '—'; ?>
								<tr>
									<td><strong><?= html_escape($n['notice_id']) ?></strong></td>
									<td><strong><?= html_escape($n['title']) ?></strong><br><span
											style="font-size:.72rem;color:var(--text-light);"><?= html_escape(substr($n['description'], 0, 60)) ?>...</span>
									</td>
									<td><span
											style="padding:3px 10px;font-size:12px;font-weight:600;text-transform:capitalize;background:rgba(52,152,219,.15);color:#3498db;border-radius:12px;display:inline-block;"><?= html_escape($n['notice_type']) ?></span>
									</td>
									<?php if (!empty($isSuperAdmin)): ?>
										<td><?php if (!empty($n['society_name'])): ?><span class="notice-society-badge"><i
														class="fas fa-city"></i>
													<?= html_escape($n['society_name']) ?></span><?php else: ?><span
													style="color:var(--text-light);">—</span><?php endif; ?></td><?php endif; ?>
									<td><?= $vu ?></td>
									<td><?= ucfirst(html_escape($n['target_audience'])) ?></td>
									<td><span
											class="notice-status <?= html_escape($n['status']) ?>"><?= strtoupper($n['status']) ?></span>
									</td>
									<td>
										<div style="display:flex;gap:6px;">
											<button type="button" class="btn-icon" title="View"
												onclick='viewNotice(<?= htmlspecialchars(json_encode($n), ENT_QUOTES) ?>)'><i
													class="fas fa-eye"></i></button>
											<button type="button" class="btn-icon" title="Edit"
												onclick='editNotice(<?= (int) $n['id'] ?>, <?= htmlspecialchars(json_encode($n), ENT_QUOTES) ?>)'><i
													class="fas fa-edit"></i></button>
											<button type="button" class="btn-icon delete" title="Delete"
												onclick="openDeleteModal(<?= (int) $n['id'] ?>)"><i
													class="fas fa-trash"></i></button>
										</div>
									</td>
								</tr>
							<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Recent Notices -->
		<div class="management-card" style="margin-top:20px;">
			<div class="section-header">
				<h3><i class="fas fa-clock"></i> Recent Notices</h3>
			</div>
			<div class="member-list">
				<?php if (empty($recent)): ?>
					<div style="padding:20px;text-align:center;color:var(--text-light);">No recent notices</div>
				<?php else:
					foreach ($recent as $r): ?>
						<div class="member-item">
							<div class="member-info">
								<div class="member-avatar"><i class="fas fa-bullhorn" style="color:var(--primary);"></i></div>
								<div class="member-details">
									<h4><?= html_escape($r['title']) ?></h4>
									<span><?= ucfirst(html_escape($r['notice_type'])) ?> ·
										<?= ucfirst(html_escape($r['target_audience'])) ?>
										<?php if (!empty($isSuperAdmin) && !empty($r['society_name'])): ?>·
											<em><?= html_escape($r['society_name']) ?></em><?php endif; ?></span>
								</div>
							</div>
							<div style="display:flex;align-items:center;gap:10px;">
								<span class="notice-status <?= html_escape($r['status']) ?>"
									style="font-size:.7rem;"><?= strtoupper($r['status']) ?></span>
								<span
									style="font-size:.7rem;color:var(--text-light);"><?= date('d M', strtotime($r['created_at'])) ?></span>
							</div>
						</div>
					<?php endforeach; endif; ?>
			</div>
		</div>

	</div><!-- /main -->

	<!-- VIEW / ADD / EDIT / DELETE modals (unchanged content & JS population) -->
	<div id="viewNoticeModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<h2 class="modal-title">Notice Details</h2>
					<div class="modal-subtitle">View notice information in a clean card layout</div>
				</div>
				<button type="button" class="btn btn-outline" onclick="closeModal('viewNoticeModal')">Close</button>
			</div>

			<div class="modal-body">
				<div class="notice-view-card">
					<div class="notice-view-row">
						<div class="notice-view-label">Notice ID</div>
						<div class="notice-view-value" id="vn_id">—</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Title</div>
						<div class="notice-view-value" id="vn_title">—</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Type</div>
						<div class="notice-view-value" id="vn_type_badge">—</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Audience</div>
						<div class="notice-view-value" id="vn_audience">—</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Created</div>
						<div class="notice-view-value" id="vn_created">—</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Valid Till</div>
						<div class="notice-view-value" id="vn_valid">—</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Status</div>
						<div class="notice-view-value">
							<span id="vn_status_badge" class="notice-status">—</span>
						</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Society</div>
						<div class="notice-view-value" id="vn_society">—</div>
					</div>
					<div class="notice-view-row">
						<div class="notice-view-label">Description</div>
						<div class="notice-view-value" id="vn_desc">—</div>
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-outline" onclick="closeModal('viewNoticeModal')">Close</button>
			</div>
		</div>
	</div>
	<!-- ... keep your existing modal HTML here (same as before) ... -->
	<div id="noticeFormModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<h2 class="modal-title" id="formModalTitle">Add Notice</h2>
					<div class="modal-subtitle">Create or update notice details</div>
				</div>
				<button type="button" class="btn btn-outline" onclick="closeModal('noticeFormModal')">Close</button>
			</div>

			<form method="post" action="<?= site_url('notice_controller/save') ?>">
				<div class="modal-body">
					<input type="hidden" id="formAction" name="action" value="add">
					<input type="hidden" id="formNoticeId" name="id" value="">

					<div class="modal-grid">
						<div>
							<label for="formTitle">Title</label>
							<input type="text" id="formTitle" name="title" placeholder="Enter notice title">
						</div>

						<div>
							<label for="formType">Type</label>
							<select id="formType" name="notice_type">
								<option value="general">General</option>
								<option value="important">Important</option>
								<option value="event">Event</option>
								<option value="maintenance">Maintenance</option>
							</select>
						</div>

						<div>
							<label for="formStatus">Status</label>
							<select id="formStatus" name="status">
								<option value="active">Active</option>
								<option value="scheduled">Scheduled</option>
								<option value="expired">Expired</option>
							</select>
						</div>

						<div>
							<label for="formAudience">Audience</label>
							<select id="formAudience" name="target_audience">
								<option value="all">All</option>
								<option value="residents">Residents</option>
								<option value="staff">Staff</option>
								<option value="members">Members</option>
							</select>
						</div>

						<div>
							<label for="formValidUntil">Valid Until</label>
							<input type="date" id="formValidUntil" name="valid_until">
						</div>

						<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
							<div>
								<label for="formSocietyId">Society</label>
								<select id="formSocietyId" name="society_id">
									<option value="">Select Society</option>
									<?php foreach ($societies as $soc): ?>
										<option value="<?= (int) $soc['id'] ?>">
											<?= html_escape($soc['name']) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<div class="modal-grid-full">
							<label for="formDesc">Description</label>
							<textarea id="formDesc" name="description"
								placeholder="Write notice description"></textarea>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline"
						onclick="closeModal('noticeFormModal')">Cancel</button>
					<button type="submit" class="btn btn-primary">Save Notice</button>
				</div>
			</form>
		</div>
	</div>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		/* Minimal page JS: modal helpers, grid/table toggle, view/edit population */
		function openModal(id) {
			const modal = document.getElementById(id);
			const overlay = document.getElementById('overlay');

			if (!modal) {
				console.error('Modal not found:', id);
				return;
			}

			modal.classList.add('active');
			if (overlay) overlay.classList.add('active');
		}

		function closeModal(id) {
			const modal = document.getElementById(id);
			const overlay = document.getElementById('overlay');

			if (modal) modal.classList.remove('active');
			if (overlay) overlay.classList.remove('active');
		}
		function toggleView(view) { document.getElementById('noticesGridView').style.display = view === 'grid' ? 'grid' : 'none'; document.getElementById('noticesTableView').style.display = view === 'table' ? 'block' : 'none'; document.getElementById('gridViewBtn').classList.toggle('active', view === 'grid'); document.getElementById('tableViewBtn').classList.toggle('active', view === 'table'); }

		function openAddModal() {
			/* same as your original openAddModal */
			var el = document.getElementById('noticeFormModal');
			if (el) openModal('noticeFormModal');
		}
		var _viewingNotice = null;
		function viewNotice(n) {
			_viewingNotice = n;
			try {
				document.getElementById('vn_id').textContent = n.notice_id || '—'; document.getElementById('vn_title').textContent = n.title || '—'; document.getElementById('vn_type_badge').textContent = n.notice_type || '—'; document.getElementById('vn_audience').textContent = n.target_audience ? n.target_audience.charAt(0).toUpperCase() + n.target_audience.slice(1) : '—'; document.getElementById('vn_created').textContent = n.created_at ? n.created_at.split(' ')[0] : '—'; document.getElementById('vn_valid').textContent = n.valid_until ? n.valid_until.split(' ')[0] : '—'; document.getElementById('vn_desc').textContent = n.description || '—'; var sb = document.getElementById('vn_status_badge');
				if (sb) {
					sb.textContent = n.status ? n.status.toUpperCase() : ''; sb.className = 'notice-status ' + (n.status || '');
				}
				var vs = document.getElementById('vn_society');
				if (vs) vs.textContent = n.society_name || '—';
			}
			catch (e) { }
			openModal('viewNoticeModal');
		}
		function editNotice(id, n) { try { document.getElementById('formModalTitle').innerText = 'Edit Notice'; document.getElementById('formAction').value = 'edit'; document.getElementById('formNoticeId').value = id; document.getElementById('formTitle').value = n.title || ''; document.getElementById('formDesc').value = n.description || ''; document.getElementById('formType').value = n.notice_type || 'general'; document.getElementById('formStatus').value = n.status || 'active'; document.getElementById('formAudience').value = n.target_audience || 'all'; document.getElementById('formValidUntil').value = (n.valid_until || '').split(' ')[0]; var sc = document.getElementById('formSocietyId'); if (sc) sc.value = n.society_id || ''; } catch (e) { } openModal('noticeFormModal'); }

		// auto-hide flash
		document.addEventListener('DOMContentLoaded', function () { var f = document.getElementById('flashMsg'); if (f) setTimeout(function () { f.style.transition = 'opacity .5s'; f.style.opacity = '0'; setTimeout(function () { if (f) f.remove(); }, 500); }, 3500); });
	</script>
</body>

</html>
