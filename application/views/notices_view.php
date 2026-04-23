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
		/* Society badge */
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
		}

		/* Active filter pills */
		.filter-pill {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: #e0e7ff;
			color: #3730a3;
			border: 1px solid #c7d2fe;
			border-radius: 20px;
			padding: 3px 10px;
			font-size: .78rem;
			font-weight: 500;
		}

		.filter-pill a {
			color: #6366f1;
			text-decoration: none;
			font-weight: 700;
			margin-left: 6px;
		}

		/* Overlay */
		.overlay {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, .5);
			backdrop-filter: blur(4px);
			opacity: 0;
			visibility: hidden;
			transition: .25s;
			z-index: 999;
		}

		.overlay.active {
			opacity: 1;
			visibility: visible;
		}

		/* Modal */
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
			transition: .25s;
			z-index: 1000;
		}

		.modal.active {
			opacity: 1;
			visibility: visible;
			pointer-events: auto;
		}

		.modal-content {
			width: min(100%, 680px);
			max-height: 90vh;
			overflow: auto;
			background: #fff;
			border-radius: 20px;
			box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
			transform: translateY(10px) scale(.98);
			transition: .25s;
			border: 1px solid #e2e8f0;
		}

		.modal.active .modal-content {
			transform: none;
		}

		.modal-header {
			padding: 20px 24px 14px;
			border-bottom: 1px solid #e2e8f0;
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 12px;
		}

		.modal-title {
			margin: 0;
			font-size: 1.2rem;
			font-weight: 700;
			color: #0f172a;
		}

		.modal-subtitle {
			margin-top: 4px;
			font-size: .88rem;
			color: #64748b;
		}

		.modal-body {
			padding: 20px 24px;
		}

		.modal-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 14px;
		}

		.modal-grid-full {
			grid-column: 1/-1;
		}

		.modal label {
			display: block;
			margin: 0 0 6px;
			font-size: .83rem;
			font-weight: 600;
			color: #334155;
		}

		.modal input,
		.modal textarea,
		.modal select {
			width: 100%;
			border: 1px solid #cbd5e1;
			border-radius: 12px;
			padding: 11px 13px;
			font-size: .93rem;
			background: #fff;
			outline: none;
			transition: .2s;
			color: #0f172a;
		}

		.modal textarea {
			min-height: 130px;
			resize: vertical;
		}

		.modal input:focus,
		.modal textarea:focus,
		.modal select:focus {
			border-color: #6366f1;
			box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
		}

		.modal-footer {
			padding: 14px 24px 20px;
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			border-top: 1px solid #e2e8f0;
			background: #fafafa;
			border-radius: 0 0 20px 20px;
		}

		.modal-footer button,
		.modal-footer .btn {
			border: none;
			border-radius: 10px;
			padding: 10px 16px;
			font-weight: 600;
			cursor: pointer;
			transition: .2s;
		}

		.modal-footer .btn-primary {
			background: #3498db;
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

		/* View modal rows */
		.vrow {
			display: grid;
			grid-template-columns: 130px 1fr;
			gap: 8px;
			padding: 8px 0;
			border-bottom: 1px dashed #e2e8f0;
		}

		.vrow:last-child {
			border-bottom: none;
		}

		.vlabel {
			font-size: .8rem;
			font-weight: 700;
			color: #475569;
		}

		.vval {
			font-size: .9rem;
			color: #0f172a;
			word-break: break-word;
		}

		@media(max-width:600px) {
			.modal-grid {
				grid-template-columns: 1fr;
			}

			.vrow {
				grid-template-columns: 1fr;
			}

			.modal-header,
			.modal-body,
			.modal-footer {
				padding-left: 16px;
				padding-right: 16px;
			}
		}
		.management-card {
			background: var(--card-bg);
			border-radius: 20px;
			padding: 20px;
			border: 1px solid var(--border);
			width: 100%;
		}
	</style>
</head>

<body>
	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'notices';
	$this->load->view('sidebar'); ?>

	<div class="main" id="main">

		<!-- Flash -->
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

		<!-- Filter form -->
		<form id="filterForm" method="post" action="javascript:void(0);">
			<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
				value="<?= $this->security->get_csrf_hash() ?>">
			<div class="filter-section">
				<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
					<div class="filter-group">
						<label><i class="fas fa-building"></i> Society</label>
						<select name="society_id" class="filter-select">
							<option value="">All Societies</option>
							<?php foreach ($societies as $s): ?>
								<option value="<?= (int) $s['id'] ?>" <?= ($filters['society_id'] == $s['id']) ? 'selected' : '' ?>>
									<?= html_escape($s['name']) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
				<div class="filter-group">
					<label><i class="fas fa-filter"></i> Type</label>
					<select name="type" class="filter-select">
						<option value="">All Types</option>
						<?php foreach (['general', 'important', 'event', 'maintenance'] as $t): ?>
							<option value="<?= $t ?>" <?= ($filters['type'] === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="filter-group">
					<label><i class="fas fa-circle"></i> Status</label>
					<select name="status" class="filter-select">
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
					<?php $hasFilter = !empty($filters['search']) || !empty($filters['type']) || !empty($filters['status']) || !empty($filters['society_id']); ?>
					<?php if ($hasFilter): ?>
						<a href="<?= site_url('notices') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
					<?php endif; ?>
				</div>
			</div>
		</form>

		<!-- Active filter pills -->
		<?php if ($hasFilter): ?>
			<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">
				<span style="font-size:.78rem;color:var(--text-light);font-weight:600;">Active filters:</span>
				<?php if (!empty($filters['society_id']) && $isSuperAdmin):
					$sLabel = '';
					foreach ($societies as $s) {
						if ((int) $s['id'] === (int) $filters['society_id']) {
							$sLabel = $s['name'];
							break;
						}
					} ?>
					<span class="filter-pill"><i class="fas fa-building"></i> <?= html_escape($sLabel) ?><a
							href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['society_id' => '']))) ?>">×</a></span>
				<?php endif; ?>
				<?php if (!empty($filters['type'])): ?>
					<span class="filter-pill"><i class="fas fa-filter"></i> <?= ucfirst(html_escape($filters['type'])) ?><a
							href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['type' => '']))) ?>">×</a></span>
				<?php endif; ?>
				<?php if (!empty($filters['status'])): ?>
					<span class="filter-pill"><i class="fas fa-circle"></i> <?= ucfirst(html_escape($filters['status'])) ?><a
							href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['status' => '']))) ?>">×</a></span>
				<?php endif; ?>
				<?php if (!empty($filters['search'])): ?>
					<span class="filter-pill"><i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>"<a
							href="<?= site_url('notices?' . http_build_query(array_merge($filters, ['search' => '']))) ?>">×</a></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Toolbar -->
		<div class="table-header" style="margin-bottom:15px;">
			<h3><i class="fas fa-bell"></i> All Notices <small
					style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= count($notices) ?>
					records)</small></h3>
			<div class="view-toggle">
				<button class="view-btn active" id="gridViewBtn" onclick="toggleView('grid')"><i
						class="fas fa-th-large"></i> Grid</button>
				<button class="view-btn" id="tableViewBtn" onclick="toggleView('table')"><i class="fas fa-table"></i>
					Table</button>
			</div>
			<a href="<?= site_url('notices') ?>" class="btn btn-outline"><i class="fas fa-sync-alt"></i> Refresh</a>
			<div class="page-actions">
				<a href="<?= site_url('notice_controller/export?' . http_build_query(array_merge($filters ?? [], ['society_id' => $filters['society_id'] ?? '']))) ?>"
					class="btn btn-outline"><i class="fas fa-download"></i> Export</a>
				<button type="button" class="btn btn-primary" onclick="openAdd()"><i class="fas fa-plus-circle"></i>
					Create Notice</button>
			</div>
		</div>

		<!-- Grid view -->
		<div id="gridView" class="notices-grid">
			<?php if (empty($notices)): ?>
				<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-light);">
					<i class="fas fa-bell-slash" style="font-size:3rem;display:block;margin-bottom:12px;"></i>
					No notices found.<br><br>
					<button type="button" class="btn btn-primary" onclick="openAdd()"><i class="fas fa-plus-circle"></i>
						Create First Notice</button>
				</div>
			<?php else:
				foreach ($notices as $n):
					$icon = ['important' => 'fa-exclamation-circle', 'event' => 'fa-calendar-alt', 'maintenance' => 'fa-tools'][$n['notice_type']] ?? 'fa-bullhorn';
					$created = date('d M Y', strtotime($n['created_at']));
					$valid = !empty($n['valid_until']) ? date('d M Y', strtotime($n['valid_until'])) : '—';
					$json = htmlspecialchars(json_encode($n), ENT_QUOTES); ?>
					<div class="notice-card <?= html_escape($n['notice_type']) ?>">
						<div class="notice-header">
							<div class="notice-type"><i class="fas <?= $icon ?>"></i>
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
						<div class="notice-meta">
							<span><i class="fas fa-calendar-plus"></i> <?= $created ?></span>
							<span><i class="fas fa-hourglass-end"></i> <?= $valid ?></span>
							<span><i class="fas fa-users"></i> <?= ucfirst(html_escape($n['target_audience'])) ?></span>
						</div>
						<div class="notice-footer">
							<div class="notice-actions" style="margin-left:auto;display:flex;gap:6px;">
								<button class="btn-icon" title="View" onclick='viewNotice(<?= $json ?>)'><i
										class="fas fa-eye"></i></button>
								<button class="btn-icon" title="Edit"
									onclick='editNotice(<?= (int) $n['id'] ?>,<?= $json ?>)'><i
										class="fas fa-edit"></i></button>
								<button class="btn-icon delete" title="Delete" onclick="openDelete(<?= (int) $n['id'] ?>)"><i
										class="fas fa-trash"></i></button>
							</div>
						</div>
					</div>
				<?php endforeach; endif; ?>
		</div>

		<!-- Table view -->
		<div id="tableView" class="table-section" style="display:none;">
			<div class="table-wrapper">
				<table>
					<thead>
						<tr>
							<th>Notice ID</th>
							<th>Title</th>
							<th>Type</th>
							<?php if (!empty($isSuperAdmin)): ?>
								<th>Society</th><?php endif; ?>
							<th>Valid Until</th>
							<th>Audience</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="tableBody">
						<?php if (empty($notices)): ?>
							<tr>
								<td colspan="<?= !empty($isSuperAdmin) ? 8 : 7 ?>" style="text-align:center;padding:40px;">
									No
									notices found</td>
							</tr>
						<?php else:
							foreach ($notices as $n):
								$vu = !empty($n['valid_until']) ? date('d/m/Y', strtotime($n['valid_until'])) : '—';
								$json = htmlspecialchars(json_encode($n), ENT_QUOTES); ?>
								<tr>
									<td><strong><?= html_escape($n['notice_id']) ?></strong></td>
									<td><strong><?= html_escape($n['title']) ?></strong><br><span
											style="font-size:.72rem;color:var(--text-light);"><?= html_escape(substr($n['description'], 0, 60)) ?>...</span>
									</td>
									<td><span
											style="padding:3px 10px;font-size:12px;font-weight:600;text-transform:capitalize;background:rgba(52,152,219,.15);color:#3498db;border-radius:12px;"><?= html_escape($n['notice_type']) ?></span>
									</td>
									<?php if (!empty($isSuperAdmin)): ?>
										<td><?= !empty($n['society_name']) ? '<span class="notice-society-badge"><i class="fas fa-city"></i> ' . html_escape($n['society_name']) . '</span>' : '—' ?>
										</td>
									<?php endif; ?>
									<td><?= $vu ?></td>
									<td><?= ucfirst(html_escape($n['target_audience'])) ?></td>
									<td><span
											class="notice-status <?= html_escape($n['status']) ?>"><?= strtoupper($n['status']) ?></span>
									</td>
									<td>
										<div style="display:flex;gap:6px;">
											<button class="btn-icon" title="View" onclick='viewNotice(<?= $json ?>)'><i
													class="fas fa-eye"></i></button>
											<button class="btn-icon" title="Edit"
												onclick='editNotice(<?= (int) $n['id'] ?>,<?= $json ?>)'><i
													class="fas fa-edit"></i></button>
											<button class="btn-icon delete" title="Delete"
												onclick="openDelete(<?= (int) $n['id'] ?>)"><i
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
										<?php if (!empty($isSuperAdmin) && !empty($r['society_name'])): ?> ·
											<em><?= html_escape($r['society_name']) ?></em><?php endif; ?>
									</span>
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

	<!-- ═══ MODALS ═══ -->

	<!-- View -->
	<div id="viewModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<h2 class="modal-title">Notice Details</h2>
					<div class="modal-subtitle">Full notice information</div>
				</div>
				<button class="btn btn-outline" onclick="closeModal('viewModal')">Close</button>
			</div>
			<div class="modal-body">
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px;">
					<div class="vrow">
						<div class="vlabel">Notice ID</div>
						<div class="vval" id="v_id">—</div>
					</div>
					<div class="vrow">
						<div class="vlabel">Title</div>
						<div class="vval" id="v_title">—</div>
					</div>
					<div class="vrow">
						<div class="vlabel">Type</div>
						<div class="vval" id="v_type">—</div>
					</div>
					<div class="vrow">
						<div class="vlabel">Audience</div>
						<div class="vval" id="v_audience">—</div>
					</div>
					<div class="vrow">
						<div class="vlabel">Created</div>
						<div class="vval" id="v_created">—</div>
					</div>
					<div class="vrow">
						<div class="vlabel">Valid Until</div>
						<div class="vval" id="v_valid">—</div>
					</div>
					<div class="vrow">
						<div class="vlabel">Status</div>
						<div class="vval"><span id="v_status" class="notice-status">—</span></div>
					</div>
					<div class="vrow">
						<div class="vlabel">Society</div>
						<div class="vval" id="v_society">—</div>
					</div>
					<div class="vrow">
						<div class="vlabel">Description</div>
						<div class="vval" id="v_desc">—</div>
					</div>
				</div>
			</div>
			<div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('viewModal')">Close</button>
			</div>
		</div>
	</div>

	<!-- Add / Edit -->
	<div id="formModal" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<h2 class="modal-title" id="formTitle">Add Notice</h2>
					<div class="modal-subtitle">Create or update notice details</div>
				</div>
				<button class="btn btn-outline" onclick="closeModal('formModal')">x</button>
			</div>
			<form method="post" action="<?= site_url('notice_controller') ?>">
				<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
					value="<?= $this->security->get_csrf_hash() ?>">
				<input type="hidden" id="f_action" name="action" value="add">
				<input type="hidden" id="f_id" name="id" value="">
				<div class="modal-body">
					<div class="modal-grid">
						<div><label>Title</label><input type="text" id="f_title" name="title"
								placeholder="Notice title"></div>
						<div><label>Type</label>
							<select id="f_type" name="notice_type">
								<option value="general">General</option>
								<option value="important">Important</option>
								<option value="event">Event</option>
								<option value="maintenance">Maintenance</option>
							</select>
						</div>
						<div><label>Status</label>
							<select id="f_status" name="status">
								<option value="active">Active</option>
								<option value="scheduled">Scheduled</option>
								<option value="expired">Expired</option>
							</select>
						</div>
						<div><label>Audience</label>
							<select id="f_audience" name="target_audience">
								<option value="all">All</option>
								<option value="residents">Residents</option>
								<option value="staff">Staff</option>
								<option value="members">Members</option>
							</select>
						</div>
						<div><label>Valid Until</label><input type="date" id="f_valid" name="valid_until"></div>
						<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
							<div><label>Society</label>
								<select id="f_society" name="society_id">
									<option value="">Select Society</option>
									<?php foreach ($societies as $s): ?>
										<option value="<?= (int) $s['id'] ?>"><?= html_escape($s['name']) ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>
						<div class="modal-grid-full"><label>Description</label><textarea id="f_desc" name="description"
								placeholder="Write notice description"></textarea></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline" onclick="closeModal('formModal')">Cancel</button>
					<button type="submit" class="btn btn-primary">Save Notice</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Delete -->
	<div id="deleteModal" class="modal">
		<div class="modal-content" style="max-width:420px;">
			<div class="modal-header">
				<h2 class="modal-title">Confirm Delete</h2>
			</div>
			<div class="modal-body" style="text-align:center;padding:24px;">
				<p style="color:#475569;">Are you sure you want to delete this notice? This cannot be undone.</p>
			</div>
			<div class="modal-footer">
				<button class="btn btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
				<button class="btn btn-primary" onclick="submitDelete()" style="background:#e74c3c;">Delete</button>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>

	<script>
		(function () {
			const sid = '<?= (int) ($this->session->userdata('society_id') ?: 0) ?>';
			if (!sid || sid === '0') return;

			const SOCKET_URL = 'http://' + window.location.hostname + ':5000';

			const socket = io(SOCKET_URL, {
				transports: ['polling', 'websocket'],
				reconnection: true,
				query: { society_id: sid }
			});

			socket.on('connect', () => {
				console.log('Socket connected:', socket.id);
			});

			socket.on('connect_error', err => {
				console.log('Socket connect error:', err);
			});

			socket.on('new_notice', data => {
				if (window.N && typeof window.N.add === 'function') {
					N.add(data);
				}
				if (typeof loadNotices === 'function') {
					loadNotices();
				}
			});
		})();
	</script>

	<script>
		function loadNotices() {
			$.post('<?= site_url("notice_controller/filter_ajax") ?>', $('#filterForm').serialize(), function (res) {
				$('#gridView').html(res.gridHtml);
				$('#tableBody').html(res.tableHtml);
			}, 'json');
		}

		$('#filterForm').on('submit', e => {
			e.preventDefault();
			loadNotices();
		});

		$(document).on('change', '#filterForm select', loadNotices);

		let debounce;
		$(document).on('keyup', '#filterForm input[name=search]', () => {
			clearTimeout(debounce);
			debounce = setTimeout(loadNotices, 300);
		});
	</script>

	<script>
		let delId = null;

		function openModal(id) {
			document.getElementById(id).classList.add('active');
			document.getElementById('overlay').classList.add('active');
		}

		function closeModal(id) {
			document.getElementById(id).classList.remove('active');
			document.getElementById('overlay').classList.remove('active');
		}

		function toggleView(v) {
			document.getElementById('gridView').style.display = v === 'grid' ? 'grid' : 'none';
			document.getElementById('tableView').style.display = v === 'table' ? 'block' : 'none';
			document.getElementById('gridViewBtn').classList.toggle('active', v === 'grid');
			document.getElementById('tableViewBtn').classList.toggle('active', v === 'table');
		}

		function openAdd() {
			document.getElementById('formTitle').innerText = 'Add Notice';
			document.getElementById('f_action').value = 'add';
			document.getElementById('f_id').value = '';
			['f_title', 'f_desc', 'f_valid'].forEach(id => document.getElementById(id).value = '');
			['f_type', 'f_status', 'f_audience'].forEach(id => document.getElementById(id).selectedIndex = 0);
			openModal('formModal');
		}

		function viewNotice(n) {
			document.getElementById('v_id').textContent = n.notice_id || '—';
			document.getElementById('v_title').textContent = n.title || '—';
			document.getElementById('v_type').textContent = n.notice_type || '—';
			document.getElementById('v_audience').textContent = (n.target_audience || '').replace(/^\w/, c => c.toUpperCase());
			document.getElementById('v_created').textContent = (n.created_at || '').split(' ')[0];
			document.getElementById('v_valid').textContent = (n.valid_until || '—').split(' ')[0];
			document.getElementById('v_desc').textContent = n.description || '—';
			document.getElementById('v_society').textContent = n.society_name || '—';
			const s = document.getElementById('v_status');
			s.textContent = (n.status || '').toUpperCase();
			s.className = 'notice-status ' + (n.status || '');
			openModal('viewModal');
		}

		function editNotice(id, n) {
			document.getElementById('formTitle').innerText = 'Edit Notice';
			document.getElementById('f_action').value = 'edit';
			document.getElementById('f_id').value = id;
			document.getElementById('f_title').value = n.title || '';
			document.getElementById('f_desc').value = n.description || '';
			document.getElementById('f_type').value = n.notice_type || 'general';
			document.getElementById('f_status').value = n.status || 'active';
			document.getElementById('f_audience').value = n.target_audience || 'all';
			document.getElementById('f_valid').value = (n.valid_until || '').split(' ')[0];
			const sc = document.getElementById('f_society');
			if (sc) sc.value = n.society_id || '';
			openModal('formModal');
		}

		function openDelete(id) {
			delId = id;
			openModal('deleteModal');
		}

		function submitDelete() {
			if (!delId) return;
			const f = document.createElement('form');
			f.method = 'POST';
			f.action = '<?= site_url("notice_controller") ?>';
			f.innerHTML = `
			<input name="action" value="delete">
			<input name="id" value="${delId}">
			<input name="confirm" value="yes">
			<input name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
		`;
			document.body.appendChild(f);
			f.submit();
		}

		document.addEventListener('DOMContentLoaded', () => {
			const f = document.getElementById('flashMsg');
			if (f) {
				setTimeout(() => {
					f.style.transition = 'opacity .4s';
					f.style.opacity = '0';
					setTimeout(() => f.remove(), 400);
				}, 3500);
			}
		});
	</script>
</body>

</html>
