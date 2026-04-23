<?php defined('BASEPATH') OR exit('No direct script access allowed');
$role_name = $this->session->userdata('role_name');
$canManage = ($role_name !== 'owner'); // chairman, secretary, super_admin, etc. can manage
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>Society · Members Management</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/manage_member.css') ?>">
	<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<style>
		tr.society-group-row > td {
			background: #eef1fb !important;
			color: #3b5bdb;
			font-weight: 700;
			font-size: .83rem;
			padding: 8px 16px !important;
			border-top: 2px solid #c5d0f5;
			letter-spacing: .02em;
		}
		tr.chairman-row {
			background: var(--chairman-bg, #fffbeb) !important;
			border-left: 3px solid var(--chairman-border, #f59e0b);
		}
		.society-badge { display:inline-flex;align-items:center;gap:5px;background:var(--badge-bg, #eef1fb);color:var(--badge-text, #3b5bdb);border:1px solid var(--badge-border, #c5d0f5);border-radius:20px;padding:2px 10px;font-size:.78rem;font-weight:500; }
		.chairman-badge { display:inline-flex;align-items:center;gap:5px;background:var(--chairman-badge-bg, #fef3c7);color:var(--chairman-badge-text, #92400e);border:1px solid var(--chairman-badge-border, #fcd34d);border-radius:20px;padding:3px 10px;font-size:.78rem;font-weight:700; }
		.member-type-badge { display:inline-block;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:600;text-transform:capitalize; }
		.member-type-badge.owner { background:var(--owner-bg, #d1fae5);color:var(--owner-text, #065f46); }
		.member-type-badge.tenant { background:var(--tenant-bg, #dbeafe);color:var(--tenant-text, #1d4ed8); }
		.member-name-cell { display:flex;align-items:center;gap:10px; }
		.m-avatar { width:34px;height:34px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem; }
		.crown-icon { color:#d97706;font-size:.82rem;margin-left:4px; }
		.active-filter-pill { display:inline-flex;align-items:center;gap:6px;background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;border-radius:20px;padding:3px 10px 3px 12px;font-size:.78rem;font-weight:500; }
		.active-filter-pill a { color:#6366f1;text-decoration:none;font-weight:700;font-size:.85rem; }

		/* Flat picker */
		.flat-picker-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(82px,1fr));gap:7px;max-height:210px;overflow-y:auto;padding:8px;border:1.5px solid var(--border);border-radius:10px;background:var(--card-bg); }
		.fp-tile { padding:7px 5px;border-radius:8px;border:1.5px solid var(--border);background:var(--card-bg);cursor:pointer;transition:all .18s;font-family:'Inter',sans-serif;text-align:center; }
		.fp-tile:hover { border-color:var(--primary);background:var(--hover-bg, #e8f4fd); }
		.fp-tile.selected { border-color:var(--primary);background:var(--selected-bg, #dbeafe);color:var(--primary-dark, #1d4ed8); }
		.fp-tile .ft-no { font-weight:800;font-size:.82rem;color:var(--text-dark);line-height:1.2; }
		.fp-tile .ft-type { font-size:.63rem;color:var(--text-light);margin-top:1px; }
		.fp-tile.selected .ft-no,.fp-tile.selected .ft-type { color:var(--primary-dark, #1d4ed8); }
		.fp-floor-hdr { grid-column:1/-1;font-size:.67rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;padding:4px 0 2px;border-bottom:1px solid var(--border); }
		.selected-flat-bar { display:none;background:var(--info-bg, #e0f2fe);border:1.5px solid var(--info-border, #7dd3fc);border-radius:10px;padding:9px 14px;margin-bottom:10px;align-items:center;justify-content:space-between; }
		.sfb-icon { width:34px;height:34px;background:var(--primary);color:#fff;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0; }
		.sfb-clear { background:var(--danger-bg, #fee2e2);color:var(--danger);border:none;border-radius:7px;padding:4px 10px;cursor:pointer;font-size:.72rem;font-weight:600;font-family:'Inter',sans-serif; }
		@keyframes sp { to { transform:rotate(360deg) } }
		.spinner-sm { display:inline-block;width:14px;height:14px;border:2px solid rgba(52,152,219,.3);border-top-color:var(--primary);border-radius:50%;animation:sp .7s linear infinite; }
		.setup-prompt { background:var(--warning-bg, #fffbeb);border:1px solid var(--warning-border, #fcd34d);border-radius:10px;padding:11px 14px;font-size:.82rem;color:var(--warning-text, #92400e); }

		/* CSV import modal */
		.csv-format-table { width:100%;border-collapse:collapse;font-size:.78rem;margin:12px 0; }
		.csv-format-table th { background:var(--table-header-bg, #f1f5f9);padding:7px 10px;text-align:left;font-weight:700;color:var(--text-dark);border:1px solid var(--border); }
		.csv-format-table td { padding:7px 10px;border:1px solid var(--border);color:var(--text-dark);vertical-align:top; }
		.csv-format-table td:first-child { font-family:monospace;font-weight:700;color:var(--primary-dark, #0369a1); }
		.csv-format-table td.req { color:var(--danger);font-size:.7rem;font-weight:700; }
		.csv-format-table td.opt { color:var(--text-light);font-size:.7rem; }
		.import-drop-zone { border:2px dashed var(--border);border-radius:14px;padding:28px;text-align:center;background:var(--card-bg);transition:all .2s;cursor:pointer;position:relative; }
		.import-drop-zone:hover,.import-drop-zone.drag-over { border-color:var(--primary);background:var(--hover-bg, #eff6ff); }
		.import-drop-zone input[type="file"] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
		.import-drop-zone i { font-size:2rem;color:var(--border);display:block;margin-bottom:8px; }
		.import-drop-zone .dz-label { font-size:.88rem;color:var(--text-light);font-weight:500; }
		.import-drop-zone .dz-label strong { color:var(--primary); }
		.import-drop-zone .dz-sub { font-size:.74rem;color:var(--text-light);margin-top:4px; }
		.file-chosen { display:none;align-items:center;gap:10px;background:var(--success-bg, #d1fae5);border:1.5px solid var(--success-border, #a7f3d0);border-radius:10px;padding:10px 14px;margin-top:10px; }
		.file-chosen.show { display:flex; }
		.file-chosen i { color:var(--success); }
		.file-chosen .fc-name { font-size:.84rem;font-weight:700;color:var(--success-text, #065f46); }
		.file-chosen .fc-size { font-size:.72rem;color:var(--success); }
		.import-steps { display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap; }
		.import-step { display:flex;align-items:flex-start;gap:8px;background:var(--card-bg);border:1px solid var(--border);border-radius:10px;padding:10px 12px;flex:1;min-width:140px; }
		.is-num { width:22px;height:22px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0;margin-top:1px; }
		.is-text { font-size:.78rem;color:var(--text-light);font-weight:500; }
		.is-text strong { color:var(--text-dark);display:block;margin-bottom:2px; }
		.import-result-box { background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-top:12px; }
		.import-result-box h4 { font-size:.8rem;font-weight:700;color:var(--text-dark);margin:0 0 8px;text-transform:uppercase;letter-spacing:.05em; }
		.ir-item { font-size:.78rem;padding:4px 0;border-bottom:1px solid var(--border);color:var(--text-dark); }
		.ir-item:last-child { border-bottom:none; }
		.ir-item.err { color:var(--danger); }
		.ir-item.warn { color:var(--warning); }

		/* Super admin society selector in Add Member modal */
		.sa-society-select-wrap { background:var(--info-bg, #f0f4ff);border:1.5px solid var(--info-border, #c7d2fe);border-radius:10px;padding:12px;margin-bottom:14px; }
		.sa-society-select-wrap label { font-size:.8rem;font-weight:700;color:var(--primary-dark, #3730a3);margin-bottom:6px;display:block; }

		/* Pagination */
		.pagination { display: flex; gap: 5px; list-style: none; padding: 0; margin: 0; }
		.pagination .page-item { display: inline-block; }
		.pagination .page-link { display: block; padding: 6px 12px; border-radius: 6px; background: var(--card-bg); border: 1px solid var(--border); color: var(--text-dark); font-size: 0.85rem; font-weight: 500; text-decoration: none; transition: all 0.2s; }
		.pagination .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: #fff; }
		.pagination .page-link:hover { background: var(--hover-bg, #f1f5f9); border-color: var(--border); }
		.pagination .page-item.disabled .page-link { opacity: 0.5; pointer-events: none; }

		/* Dark mode adjustments */
		[data-theme="dark"] {
			--chairman-bg: #2a1f0a;
			--chairman-border: #d97706;
			--chairman-badge-bg: #3b2a0e;
			--chairman-badge-text: #fbbf24;
			--chairman-badge-border: #f59e0b;
		}
	</style>
</head>

<body>
<div class="overlay" id="overlay"></div>
<?php $activePage = 'manage_member'; include('sidebar.php'); ?>

<div class="main" id="main">

	<?php if ($this->session->flashdata('success')): ?>
		<div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?></div>
	<?php endif; ?>
	<?php if ($this->session->flashdata('error')): ?>
		<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?></div>
	<?php endif; ?>

	<!-- Stats -->
	<div class="stats-grid">
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-user-friends"></i></div>
			<div class="stat-info">
				<h4>Total Members</h4>
				<h2><?= $totalMembers ?></h2>
				<div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> +<?= $newThisMonth ?> this month</div>
			</div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-home"></i></div>
			<div class="stat-info"><h4>Owners</h4><h2><?= $owners ?></h2><div class="stat-trend"><?= $ownerPercent ?>%</div></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-user-tag"></i></div>
			<div class="stat-info"><h4>Tenants</h4><h2><?= $tenants ?></h2><div class="stat-trend"><?= $tenantPercent ?>%</div></div>
		</div>
		<div class="stat-card">
			<div class="stat-icon"><i class="fas fa-flag"></i></div>
			<div class="stat-info"><h4>Committee</h4><h2><?= $committee ?></h2><div class="stat-trend"><?= $active ?> active · <?= $inactive ?> inactive</div></div>
		</div>
	</div>

	<!-- Committee Strip -->
	<div class="committee-section">
		<div class="committee-header">
			<h3><i class="fas fa-users-cog"></i> Managing Committee</h3>
			<?php if ($canManage): ?>
				<button class="btn-sm btn-outline" onclick="openAssignRoleModal()"><i class="fas fa-plus-circle"></i> Add Committee</button>
			<?php endif; ?>
		</div>
		<div class="committee-grid">
			<?php
			$cMembers = array_filter($members, fn($m) => !empty($m->committee_role));
			?>
			<?php if (empty($cMembers)): ?>
				<p style="text-align:center;color:var(--text-light);padding:20px;">No committee members assigned yet.</p>
			<?php else: ?>
				<?php foreach ($cMembers as $m):
					$np = explode(' ', $m->name, 2);
					$in = strtoupper(substr($np[0], 0, 1) . (isset($np[1]) ? substr($np[1], 0, 1) : ''));
					$ic = strtolower($m->committee_role) === 'chairman'; ?>
					<div class="committee-card">
						<div class="committee-avatar" style="<?= $ic ? 'background:var(--chairman-bg, #fcd34d);color:var(--chairman-text, #92400e);' : '' ?>"><?= $in ?></div>
						<div class="committee-info">
							<h4><?= html_escape($m->name) ?><?= $ic ? ' <i class="fas fa-crown" style="color:#d97706;font-size:.8rem;"></i>' : '' ?></h4>
							<div class="committee-role"><?= ucwords(str_replace('_', ' ', $m->committee_role)) ?></div>
							<div class="committee-term"><?= html_escape($m->wing_name ?? '') ?> <?= html_escape($m->flat_no) ?><?php if ($isSuperAdmin && !empty($m->society_name)): ?> &nbsp;<span class="society-badge" style="font-size:.68rem;"><i class="fas fa-city"></i><?= html_escape($m->society_name) ?></span><?php endif; ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Filter Bar -->
	<form id="filterForm">
		<div class="filter-section" style="flex-wrap:wrap;gap:12px;">
			<?php if ($isSuperAdmin && !empty($societies)): ?>
				<div class="filter-group"><label><i class="fas fa-city"></i> Society</label>
					<select name="society_id" class="filter-select">
						<option value="">All Societies</option>
						<?php foreach ($societies as $soc): ?>
							<option value="<?= $soc->id ?>" <?= ((int)($filters['society_id'] ?? 0) === $soc->id) ? 'selected' : '' ?>><?= html_escape($soc->name) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<div class="filter-group"><label><i class="fas fa-building"></i> Wing</label>
				<select name="wing_id" class="filter-select">
					<option value="">All Wings</option>
					<?php foreach ($wings as $w): ?>
						<option value="<?= $w->id ?>" <?= ((int)($filters['wing_id'] ?? 0) === $w->id) ? 'selected' : '' ?>><?= html_escape($w->wing_name) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="filter-group"><label><i class="fas fa-user-tag"></i> Member Type</label>
				<select name="member_type" class="filter-select">
					<option value="">All Types</option>
					<option value="owner" <?= ($filters['member_type'] === 'owner') ? 'selected' : '' ?>>Owner</option>
					<option value="tenant" <?= ($filters['member_type'] === 'tenant') ? 'selected' : '' ?>>Tenant</option>
				</select>
			</div>
			<div class="filter-group"><label><i class="fas fa-users-cog"></i> Committee Role</label>
				<select name="role" class="filter-select">
					<option value="">All Roles</option>
					<?php foreach ($committee_roles as $role): ?>
						<option value="<?= strtolower($role->role_name) ?>" <?= ($filters['role'] === strtolower($role->role_name)) ? 'selected' : '' ?>>
							<?= ucwords(str_replace('_', ' ', $role->role_name)) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="filter-group"><label><i class="fas fa-circle"></i> Status</label>
				<select name="status" class="filter-select">
					<option value="">All Status</option>
					<option value="1" <?= ($filters['status'] === '1') ? 'selected' : '' ?>>Active</option>
					<option value="0" <?= ($filters['status'] === '0') ? 'selected' : '' ?>>Inactive</option>
				</select>
			</div>
			<div class="search-box"><i class="fas fa-search"></i>
				<input type="text" name="search" placeholder="Search name, flat, phone..." value="<?= html_escape($filters['search'] ?? '') ?>">
			</div>
			<div style="display:flex;gap:8px;align-items:flex-end;">
				<button type="button" class="btn btn-outline" onclick="loadData()"><i class="fas fa-search"></i> Search</button>
				<?php $anyFilter = !empty($filters['search']) || !empty($filters['wing_id']) || !empty($filters['member_type']) || !empty($filters['role']) || ($filters['status'] !== '') || !empty($filters['society_id']);
				if ($anyFilter): ?><a href="<?= site_url('manage_member') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a><?php endif; ?>
			</div>
		</div>
	</form>

	<!-- Active filter pills -->
	<?php if ($anyFilter): ?>
		<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">
			<span style="font-size:.8rem;color:var(--text-light);font-weight:600;">Active filters:</span>
			<?php if (!empty($filters['wing_id'])): ?>
				<?php $wLbl = ''; foreach ($wings as $w) { if ((int)$w->id === (int)$filters['wing_id']) { $wLbl = $w->wing_name; break; } } ?>
				<span class="active-filter-pill"><i class="fas fa-building"></i> <?= html_escape($wLbl) ?> <a href="<?= site_url('manage_member?' . http_build_query(array_merge($filters, ['wing_id' => '']))) ?>">×</a></span>
			<?php endif; ?>
			<?php if (!empty($filters['member_type'])): ?>
				<span class="active-filter-pill"><i class="fas fa-user-tag"></i> <?= ucfirst($filters['member_type']) ?> <a href="<?= site_url('manage_member?' . http_build_query(array_merge($filters, ['member_type' => '']))) ?>">×</a></span>
			<?php endif; ?>
			<?php if (!empty($filters['search'])): ?>
				<span class="active-filter-pill"><i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>" <a href="<?= site_url('manage_member?' . http_build_query(array_merge($filters, ['search' => '']))) ?>">×</a></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Members Table -->
	<div class="table-section">
		<div class="table-header">
			<h3>
				<i class="fas fa-list"></i>
				<?= $isSuperAdmin ? 'All Societies — Members Directory' : 'Members Directory' ?>
				<small style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= count($members) ?> records)</small>
			</h3>
			<a href="<?= site_url('manage_member') ?>" class="btn btn-outline" style="display:flex;align-items:center;gap:6px;">
				<i class="fas fa-sync-alt"></i> Refresh
			</a>
			<?php if ($canManage): ?>
				<div class="page-actions">
					<div class="export-wrapper">
						<button class="btn btn-outline" onclick="openImportModal()"><i class="fas fa-file-upload"></i> Import CSV</button>
						<button class="btn btn-primary" onclick="openAddMemberModal()"><i class="fas fa-plus-circle"></i> Add Member</button>
						<button type="button" class="btn btn-outline" onclick="toggleExportMenu(event)"><i class="fas fa-download"></i> Export</button>
						<div class="export-menu" id="exportMenu">
							<button onclick="exportExcel()">Excel</button>
							<button onclick="exportCSV()">CSV</button>
							<button onclick="printTable()">Print</button>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<!-- Import result detail (only if can manage) -->
		<?php if ($canManage): ?>
			<?php $importResult = $this->session->userdata('import_result'); ?>
			<?php if (!empty($importResult)): ?>
				<div style="padding:14px 20px;border-bottom:1px solid var(--border);" class="import-result-detail">
					<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
						<i class="fas fa-info-circle" style="color:var(--primary);"></i>
						<span style="font-weight:700;font-size:.88rem;">Import Result:
							<span style="color:var(--success);"><?= $importResult['inserted'] ?> inserted</span>,
							<span style="color:var(--danger);"><?= $importResult['skipped'] ?> skipped</span>
						</span>
						<button onclick="this.closest('.import-result-detail').remove();" style="margin-left:auto;background:none;border:none;cursor:pointer;color:var(--text-light);font-size:1rem;">×</button>
					</div>
					<?php if (!empty($importResult['errors'])): ?>
						<div class="import-result-box">
							<h4><i class="fas fa-times-circle" style="color:var(--danger);margin-right:4px;"></i>Errors (<?= count($importResult['errors']) ?>)</h4>
							<?php foreach ($importResult['errors'] as $e): ?>
								<div class="ir-item err"><i class="fas fa-times" style="margin-right:4px;"></i><?= html_escape($e) ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php if (!empty($importResult['warnings'])): ?>
						<div class="import-result-box" style="margin-top:8px;">
							<h4><i class="fas fa-exclamation-triangle" style="color:var(--warning);margin-right:4px;"></i>Warnings (<?= count($importResult['warnings']) ?>)</h4>
							<?php foreach ($importResult['warnings'] as $w): ?>
								<div class="ir-item warn"><i class="fas fa-exclamation" style="margin-right:4px;"></i><?= html_escape($w) ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php $this->session->unset_userdata('import_result'); ?>
			<?php endif; ?>
		<?php endif; ?>

		<div class="table-wrapper">
			<table id="membersTable">
				<thead>
					<tr>
						<th>Member</th>
						<th>Flat</th>
						<th>Wing</th>
						<th>Type</th>
						<th>Committee Role</th>
						<?php if ($isSuperAdmin): ?><th>Society</th><?php endif; ?>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody id="membersTableBody">
					<?php if (empty($members)): ?>
						<tr>
							<td colspan="<?= $isSuperAdmin ? 8 : 7 ?>" style="text-align:center;padding:32px;color:var(--text-light);">
								<i class="fas fa-users" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.4;"></i>No members found
							</td>
						</tr>
					<?php elseif ($isSuperAdmin): ?>
						<?php foreach ($societyGroups as $sName => $sMembers): ?>
							<tr class="society-group-row">
								<td colspan="<?= $canManage ? 8 : 7 ?>"><i class="fas fa-city"></i> <?= htmlspecialchars($sName) ?>
									<span style="font-weight:400;margin-left:8px;color:#5c7cfa;">— <?= count($sMembers) ?> members</span>
								</td>
							</tr>
							<?php foreach ($sMembers as $m):
								$isCh = strtolower($m->committee_role ?? '') === 'chairman';
								$sLbl = $m->status ? 'Active' : 'Inactive';
								$sCls = $m->status ? 'active' : 'inactive';
								$np   = explode(' ', $m->name, 2);
								$ini  = strtoupper(substr($np[0], 0, 1) . (isset($np[1]) ? substr($np[1], 0, 1) : ''));
							?>
							<tr class="<?= $isCh ? 'chairman-row' : '' ?>">
								<td>
									<div class="member-name-cell">
										<div class="m-avatar" style="background:<?= $isCh ? 'var(--chairman-bg, #fcd34d)' : 'var(--primary)' ?>;color:<?= $isCh ? 'var(--chairman-text, #92400e)' : '#fff' ?>;"><?= $ini ?></div>
										<div>
											<span style="font-weight:<?= $isCh ? '700' : '500' ?>;"><?= html_escape($m->name) ?></span>
											<?php if ($isCh): ?><i class="fas fa-crown crown-icon"></i><?php endif; ?>
										</div>
									</div>
								</td>
								<td><?= html_escape($m->flat_no) ?></td>
								<td><?= html_escape($m->wing_name ?? '—') ?></td>
								<td><span class="member-type-badge <?= $m->member_type ?? '' ?>"><?= ucfirst($m->member_type ?? '—') ?></span></td>
								<td>
									<?php if ($isCh): ?>
										<span class="chairman-badge"><i class="fas fa-crown"></i> Chairman</span>
									<?php elseif (!empty($m->committee_role)): ?>
										<span class="role-badge"><?= ucwords(str_replace('_', ' ', html_escape($m->committee_role))) ?></span>
									<?php else: ?><span style="color:var(--text-light);">—</span><?php endif; ?>
								</td>
								<td><span class="society-badge"><i class="fas fa-city"></i> <?= htmlspecialchars($sName) ?></span></td>
								<td><span class="status-badge <?= $sCls ?>"><?= $sLbl ?></span></td>
								<td>
									<div class="action-buttons">
										<!-- View (always allowed) -->
										<button class="btn-icon" title="View" onclick="openModal('vm_<?= $m->id ?>')"><i class="fas fa-eye"></i></button>
										<?php if ($canManage): ?>
											<button class="btn-icon" title="Assign Role" onclick="openAssignRoleModal(<?= $m->id ?>)"><i class="fas fa-users-cog"></i></button>
											<?php if (!empty($m->committee_role)): ?>
												<form method="post" action="<?= base_url('feature_controller/remove_role') ?>" style="display:inline;">
													<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
													<input type="hidden" name="id" value="<?= $m->id ?>">
													<button type="submit" class="btn-icon delete" title="Remove Role" onclick="return confirm('Remove role?')"><i class="fas fa-times-circle"></i></button>
												</form>
											<?php endif; ?>
											<button class="btn-icon" title="Edit"
												onclick='openEditMemberModal(<?= htmlspecialchars(json_encode([
													"id"          => $m->id,
													"name"        => $m->name,
													"flat_no"     => $m->flat_no,
													"wing_id"     => $m->wing_id,
													"member_type" => $m->member_type,
													"phone"       => $m->phone,
													"email"       => $m->email,
													"status"      => $m->status,
												]), ENT_QUOTES, "UTF-8") ?>)'>
												<i class="fas fa-edit"></i>
											</button>
											<a href="<?= base_url('feature_controller/delete_member/' . $m->id) ?>" onclick="return confirm('Delete <?= html_escape($m->name) ?>?')">
												<button class="btn-icon delete" title="Delete"><i class="fas fa-trash"></i></button>
											</a>
										<?php endif; ?>
									</div>
								</td>
							</tr>

							<!-- View Modal -->
							<div class="modal" id="vm_<?= $m->id ?>">
								<div class="modal-content">
									<div class="modal-header" style="<?= $isCh ? 'background:var(--chairman-bg, #fffbeb);' : '' ?>">
										<h3><?= $isCh ? '<i class="fas fa-crown" style="color:#d97706"></i> Chairman Details' : '<i class="fas fa-user"></i> Member Details' ?></h3>
										<span class="modal-close" onclick="closeModal('vm_<?= $m->id ?>')">&times;</span>
									</div>
									<div class="modal-body">
										<div style="display:flex;align-items:center;gap:18px;margin-bottom:18px;">
											<div class="staff-avatar" style="width:68px;height:68px;font-size:1.6rem;<?= $isCh ? 'background:var(--chairman-bg, #fcd34d);color:var(--chairman-text, #92400e);' : '' ?>"><?= $ini ?></div>
											<div>
												<h2 style="margin:0 0 5px;"><?= html_escape($m->name) ?></h2>
												<span class="member-type-badge <?= $m->member_type ?? '' ?>"><?= ucfirst($m->member_type ?? '—') ?></span>
												<span class="society-badge" style="margin-left:6px;"><i class="fas fa-city"></i> <?= htmlspecialchars($sName) ?></span>
											</div>
										</div>
										<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
											<div style="background:var(--card-bg);padding:14px;border-radius:10px;">
												<h4 style="margin-bottom:8px;"><i class="fas fa-home" style="color:var(--primary)"></i> Residence</h4>
												<p><i class="fas fa-door-open"></i> Flat: <strong><?= html_escape($m->flat_no) ?></strong></p>
												<p><i class="fas fa-layer-group"></i> Wing: <strong><?= html_escape($m->wing_name ?? '—') ?></strong></p>
											</div>
											<div style="background:var(--card-bg);padding:14px;border-radius:10px;">
												<h4 style="margin-bottom:8px;"><i class="fas fa-address-card" style="color:var(--primary)"></i> Contact</h4>
												<p><i class="fas fa-phone"></i> <?= html_escape($m->phone ?? '—') ?></p>
												<p><i class="fas fa-envelope"></i> <?= html_escape($m->email ?? '—') ?></p>
											</div>
										</div>
										<div style="margin-top:14px;background:var(--card-bg);padding:12px;border-radius:10px;">
											<?php if ($isCh): ?>
												<p><i class="fas fa-crown" style="color:#d97706;"></i> Role: <span class="chairman-badge"><i class="fas fa-crown"></i> Chairman</span></p>
											<?php elseif (!empty($m->committee_role)): ?>
												<p><i class="fas fa-users-cog" style="color:var(--primary);"></i> Role: <span class="role-badge"><?= ucwords(str_replace('_', ' ', html_escape($m->committee_role))) ?></span></p>
											<?php endif; ?>
											<p><i class="fas fa-circle" style="color:var(--primary);"></i> Status: <span class="status-badge <?= $sCls ?>"><?= $sLbl ?></span></p>
										</div>
									</div>
									<div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('vm_<?= $m->id ?>')"><i class="fas fa-times"></i> Close</button></div>
								</div>
							</div>
							<?php endforeach; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<?php foreach ($members as $m):
							$isCh = strtolower($m->committee_role ?? '') === 'chairman';
							$sLbl = $m->status ? 'Active' : 'Inactive';
							$sCls = $m->status ? 'active' : 'inactive';
							$np   = explode(' ', $m->name, 2);
							$ini  = strtoupper(substr($np[0], 0, 1) . (isset($np[1]) ? substr($np[1], 0, 1) : ''));
						?>
						<tr class="<?= $isCh ? 'chairman-row' : '' ?>">
							<td>
								<div class="member-name-cell">
									<div class="m-avatar" style="background:<?= $isCh ? 'var(--chairman-bg, #fcd34d)' : 'var(--primary)' ?>;color:<?= $isCh ? 'var(--chairman-text, #92400e)' : '#fff' ?>;"><?= $ini ?></div>
									<div>
										<span style="font-weight:<?= $isCh ? '700' : '500' ?>;"><?= html_escape($m->name) ?></span>
										<?php if ($isCh): ?><i class="fas fa-crown crown-icon"></i><?php endif; ?>
									</div>
								</div>
							</td>
							<td><?= html_escape($m->flat_no) ?></td>
							<td><?= html_escape($m->wing_name ?? '—') ?></td>
							<td><span class="member-type-badge <?= $m->member_type ?? '' ?>"><?= ucfirst($m->member_type ?? '—') ?></span></td>
							<td>
								<?php if ($isCh): ?>
									<span class="chairman-badge"><i class="fas fa-crown"></i> Chairman</span>
								<?php elseif (!empty($m->committee_role)): ?>
									<span class="role-badge"><?= ucwords(str_replace('_', ' ', html_escape($m->committee_role))) ?></span>
								<?php else: ?><span style="color:var(--text-light);">—</span><?php endif; ?>
							</td>
							<td><span class="status-badge <?= $sCls ?>"><?= $sLbl ?></span></td>
							<td>
								<div class="action-buttons">
									<!-- View (always allowed) -->
									<button class="btn-icon" title="View" onclick="openModal('vm_<?= $m->id ?>')"><i class="fas fa-eye"></i></button>
									<?php if ($canManage): ?>
										<button class="btn-icon" title="Assign Role" onclick="openAssignRoleModal(<?= $m->id ?>)"><i class="fas fa-users-cog"></i></button>
										<?php if (!empty($m->committee_role)): ?>
											<form method="post" action="<?= base_url('feature_controller/remove_role') ?>" style="display:inline;">
												<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
												<input type="hidden" name="id" value="<?= $m->id ?>">
												<button type="submit" class="btn-icon delete" title="Remove Role" onclick="return confirm('Remove role?')"><i class="fas fa-times-circle"></i></button>
											</form>
										<?php endif; ?>
										<button class="btn-icon" title="Edit"
											onclick='openEditMemberModal(<?= htmlspecialchars(json_encode([
												"id"          => $m->id,
												"name"        => $m->name,
												"flat_no"     => $m->flat_no,
												"wing_id"     => $m->wing_id,
												"member_type" => $m->member_type,
												"phone"       => $m->phone,
												"email"       => $m->email,
												"status"      => $m->status,
											]), ENT_QUOTES, "UTF-8") ?>)'>
											<i class="fas fa-edit"></i>
										</button>
										<a href="<?= base_url('feature_controller/delete_member/' . $m->id) ?>" onclick="return confirm('Delete <?= html_escape($m->name) ?>?')">
											<button class="btn-icon delete" title="Delete"><i class="fas fa-trash"></i></button>
										</a>
									<?php endif; ?>
								</div>
							</td>
						</tr>
						<!-- View Modal -->
						<div class="modal" id="vm_<?= $m->id ?>">
							<div class="modal-content">
								<div class="modal-header" style="<?= $isCh ? 'background:var(--chairman-bg, #fffbeb);' : '' ?>">
									<h3><?= $isCh ? '<i class="fas fa-crown" style="color:#d97706"></i> Chairman Details' : '<i class="fas fa-user"></i> Member Details' ?></h3>
									<span class="modal-close" onclick="closeModal('vm_<?= $m->id ?>')">&times;</span>
								</div>
								<div class="modal-body">
									<div style="display:flex;align-items:center;gap:18px;margin-bottom:20px;">
										<div class="staff-avatar" style="width:70px;height:70px;font-size:1.7rem;<?= $isCh ? 'background:var(--chairman-bg, #fcd34d);color:var(--chairman-text, #92400e);' : '' ?>"><?= $ini ?></div>
										<div>
											<h2 style="margin:0 0 4px;"><?= html_escape($m->name) ?><?= $isCh ? ' <i class="fas fa-crown" style="color:#d97706;"></i>' : '' ?></h2>
											<span class="member-type-badge <?= $m->member_type ?? '' ?>"><?= ucfirst($m->member_type ?? '—') ?></span>
										</div>
									</div>
									<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
										<div style="background:var(--card-bg);padding:16px;border-radius:10px;">
											<h4 style="margin-bottom:10px;"><i class="fas fa-home" style="color:var(--primary)"></i> Residence</h4>
											<p><i class="fas fa-door-open"></i> Flat: <strong><?= html_escape($m->flat_no) ?></strong></p>
											<p><i class="fas fa-layer-group"></i> Wing: <strong><?= html_escape($m->wing_name ?? '—') ?></strong></p>
										</div>
										<div style="background:var(--card-bg);padding:16px;border-radius:10px;">
											<h4 style="margin-bottom:10px;"><i class="fas fa-address-card" style="color:var(--primary)"></i> Contact</h4>
											<p><i class="fas fa-phone"></i> <?= html_escape($m->phone ?? '—') ?></p>
											<p><i class="fas fa-envelope"></i> <?= html_escape($m->email ?? '—') ?></p>
										</div>
									</div>
									<div style="margin-top:14px;background:var(--card-bg);padding:12px;border-radius:10px;">
										<?php if ($isCh): ?>
											<p><i class="fas fa-crown" style="color:#d97706;"></i> Role: <span class="chairman-badge"><i class="fas fa-crown"></i> Chairman</span></p>
										<?php elseif (!empty($m->committee_role)): ?>
											<p><i class="fas fa-users-cog" style="color:var(--primary);"></i> Role: <span class="role-badge"><?= ucwords(str_replace('_', ' ', html_escape($m->committee_role))) ?></span></p>
										<?php endif; ?>
										<p><i class="fas fa-circle" style="color:var(--primary);"></i> Status: <span class="status-badge <?= $sCls ?>"><?= $sLbl ?></span></p>
									</div>
								</div>
								<div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('vm_<?= $m->id ?>')"><i class="fas fa-times"></i> Close</button></div>
							</div>
						</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div><!-- /.table-section -->

	<!-- Pagination -->
	<?php if (!empty($pagination)): ?>
	<div class="pagination-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 0 20px;">
		<div class="pagination-info" style="font-size: 0.85rem; color: var(--text-light);">
			Showing <?= (($current_page - 1) * $per_page) + 1 ?> - 
			<?= min($current_page * $per_page, $total_records) ?> of <?= $total_records ?> members
		</div>
		<div class="pagination-links">
			<?= $pagination ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- ══════════════════════════════════════════════
	     MODALS (only included if canManage)
	══════════════════════════════════════════════ -->
	<?php if ($canManage): ?>
	<!-- CSV IMPORT MODAL -->
	<div class="modal" id="importModal">
		<div class="modal-content" style="max-width:700px;">
			<div class="modal-header">
				<h3><i class="fas fa-file-upload"></i> Import Members via CSV</h3>
				<span class="modal-close" onclick="closeModal('importModal')">&times;</span>
			</div>
			<div class="modal-body">
				<?php if ($isSuperAdmin && !empty($societies)): ?>
					<div class="sa-society-select-wrap">
						<label><i class="fas fa-city" style="margin-right:4px;"></i> Import into Society <span style="color:var(--danger);">*</span></label>
						<select name="import_society_id" id="importSocietyId" class="form-control" required>
							<option value="">— Select Society —</option>
							<?php foreach ($societies as $soc): ?>
								<option value="<?= $soc->id ?>"><?= html_escape($soc->name) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<div class="import-steps">
					<div class="import-step"><div class="is-num">1</div><div class="is-text"><strong>Download Sample</strong>Use the template to fill data correctly.</div></div>
					<div class="import-step"><div class="is-num">2</div><div class="is-text"><strong>Fill Data</strong>Enter member details in the correct columns.</div></div>
					<div class="import-step"><div class="is-num">3</div><div class="is-text"><strong>Upload &amp; Import</strong>Upload the CSV — members are auto-created.</div></div>
				</div>

				<div style="font-size:.78rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px;">
					<i class="fas fa-table" style="color:var(--primary);margin-right:4px;"></i> CSV Column Format
				</div>
				<table class="csv-format-table">
					<thead><tr><th>Column Name</th><th>Required</th><th>Format / Example</th><th>Notes</th></tr></thead>
					<tbody>
						<tr><td>first_name</td><td class="req">Required</td><td>Rajesh</td><td>Member's first name</td></tr>
						<tr><td>last_name</td><td class="req">Required</td><td>Kumar</td><td>Member's last name</td></tr>
						<tr><td>phone</td><td class="req">Required</td><td>9876543210</td><td>10-digit mobile, must be unique</td></tr>
						<tr><td>email</td><td class="opt">Optional</td><td>rajesh@email.com</td><td>Must be unique if provided</td></tr>
						<tr><td>wing_name</td><td class="opt">Optional</td><td>A</td><td>Must match existing wing name exactly</td></tr>
						<tr><td>flat_no</td><td class="req">Required</td><td>A-101</td><td>Must match an existing <strong>vacant</strong> flat</td></tr>
						<tr><td>member_type</td><td class="req">Required</td><td>owner / tenant</td><td>Lowercase only</td></tr>
						<tr><td>password</td><td class="opt">Optional</td><td>Pass@123</td><td>Default: <code>Society@123</code> if blank</td></tr>
						<tr><td>status</td><td class="opt">Optional</td><td>1 / 0</td><td>1 = Active, 0 = Inactive. Default: 1</td></tr>
					</tbody>
				</table>

				<div style="background:var(--warning-bg, #fef3c7);border:1px solid var(--warning-border, #fcd34d);border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:.79rem;color:var(--warning-text, #92400e);">
					<i class="fas fa-lightbulb" style="margin-right:4px;"></i>
					<strong>Tips:</strong> Flat must already exist and be vacant. Duplicate phone/email rows are skipped. One owner per flat is enforced.
				</div>

				<a href="<?= base_url('feature_controller/download_member_sample') ?>" class="btn btn-outline" style="margin-bottom:16px;">
					<i class="fas fa-download"></i> Download Sample CSV
				</a>

				<form id="importForm" method="post" action="<?= base_url('feature_controller/import_members') ?>" enctype="multipart/form-data">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
					<?php if ($isSuperAdmin): ?>
						<input type="hidden" name="import_society_id" id="importSocietyIdHidden" value="">
					<?php endif; ?>

					<div class="import-drop-zone" id="dropZone">
						<input type="file" name="csv_file" id="csvFileInput" accept=".csv" required onchange="handleFileChosen(this)">
						<i class="fas fa-cloud-upload-alt"></i>
						<div class="dz-label"><strong>Click to browse</strong> or drag &amp; drop your CSV here</div>
						<div class="dz-sub">Only .csv files · Maximum 2 MB</div>
					</div>

					<div class="file-chosen" id="fileChosen">
						<i class="fas fa-file-csv" style="font-size:1.4rem;"></i>
						<div>
							<div class="fc-name" id="fcName"></div>
							<div class="fc-size" id="fcSize"></div>
						</div>
						<button type="button" onclick="clearFileChosen()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:var(--success);font-size:1rem;"><i class="fas fa-times"></i></button>
					</div>

					<div class="modal-footer" style="padding:0;margin-top:14px;">
						<button type="button" class="btn btn-outline" onclick="closeModal('importModal')"><i class="fas fa-times"></i> Cancel</button>
						<button type="submit" class="btn btn-primary" id="importSubmitBtn"><i class="fas fa-upload"></i> Import Members</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- ADD / EDIT MEMBER MODAL -->
	<div class="modal" id="memberFormModal">
		<div class="modal-content" style="max-width:660px;">
			<div class="modal-header">
				<h3><i class="fas fa-user-plus"></i> <span id="formModalTitle">Add New Member</span></h3>
				<span class="modal-close" onclick="closeModal('memberFormModal')">&times;</span>
			</div>
			<div class="modal-body">
				<form id="memberForm" method="post" action="<?= base_url('feature_controller/save') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
					<input type="hidden" id="memberId" name="memberId" value="">
					<input type="hidden" id="hFlatNo"  name="flat_no"   value="">
					<input type="hidden" id="hWingId"  name="wing_id"   value="">
					<input type="hidden" id="hFlatId"  name="flat_id"   value="">

					<?php if ($isSuperAdmin && !empty($societies)): ?>
						<div class="sa-society-select-wrap" id="saSocietyWrap">
							<label><i class="fas fa-city" style="margin-right:4px;"></i> Society <span style="color:var(--danger);">*</span></label>
							<select name="society_id" id="saSocietySelect" class="form-control" onchange="saLoadFlats(this.value)">
								<option value="">— Select Society —</option>
								<?php foreach ($societies as $soc): ?>
									<option value="<?= $soc->id ?>"><?= html_escape($soc->name) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<div style="font-size:.7rem;font-weight:700;letter-spacing:.1em;color:var(--text-light);text-transform:uppercase;display:flex;align-items:center;gap:10px;margin-bottom:12px;">
						<span style="flex:1;height:1px;background:var(--border);"></span>Personal Info<span style="flex:1;height:1px;background:var(--border);"></span>
					</div>
					<div class="form-row">
						<div class="form-group"><label>First Name *</label><input type="text" name="first_name" class="form-control" id="firstName" required></div>
						<div class="form-group"><label>Last Name *</label><input type="text"  name="last_name"  class="form-control" id="lastName"  required></div>
						<div class="form-group"><label>Phone *</label><input type="tel" name="phone" class="form-control" id="phone" required placeholder="10-digit"></div>
						<div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" id="email"></div>
						<div class="form-group"><label>Member Type *</label>
							<select name="member_type" class="form-control" id="memberType" required>
								<option value="owner">Owner</option>
								<option value="tenant">Tenant</option>
							</select>
						</div>
						<div class="form-group"><label>Status</label>
							<select name="status" class="form-control" id="statusSel">
								<option value="1">Active</option>
								<option value="0">Inactive</option>
							</select>
						</div>
					</div>

					<div id="flatPickerSection">
						<div style="font-size:.7rem;font-weight:700;letter-spacing:.1em;color:var(--text-light);text-transform:uppercase;display:flex;align-items:center;gap:10px;margin:12px 0;">
							<span style="flex:1;height:1px;background:var(--border);"></span>Flat Assignment<span style="flex:1;height:1px;background:var(--border);"></span>
						</div>

						<?php if ($isSuperAdmin): ?>
							<div id="saFlatLoaderMsg" class="setup-prompt">
								<i class="fas fa-info-circle" style="color:#3b82f6;margin-right:4px;"></i>
								Select a society above to see available flats.
							</div>
							<div id="saFlatPickerInner" style="display:none;">
								<div class="form-row" style="margin-bottom:10px;">
									<div class="form-group">
										<label><i class="fas fa-layer-group" style="color:var(--primary);margin-right:3px;"></i> Filter by Wing</label>
										<select id="fpWing" class="form-control" onchange="fpFilter()">
											<option value="">All Wings</option>
										</select>
									</div>
									<div class="form-group">
										<label><i class="fas fa-stairs" style="color:var(--primary);margin-right:3px;"></i> Filter by Floor</label>
										<select id="fpFloor" class="form-control" onchange="fpFilter()" disabled>
											<option value="">All Floors</option>
										</select>
									</div>
								</div>
								<div class="selected-flat-bar" id="selFlatBar">
									<div style="display:flex;align-items:center;gap:10px;">
										<div class="sfb-icon"><i class="fas fa-door-closed"></i></div>
										<div>
											<div id="selFlatNo" style="font-weight:800;font-size:1rem;color:var(--text-dark);"></div>
											<div id="selFlatMeta" style="font-size:.73rem;color:var(--text-light);"></div>
										</div>
									</div>
									<button type="button" class="sfb-clear" onclick="fpClear()"><i class="fas fa-times"></i> Clear</button>
								</div>
								<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
									<label style="font-size:.8rem;font-weight:600;color:var(--text-dark);margin:0;"><i class="fas fa-th" style="color:var(--primary);margin-right:3px;"></i> Available Flats</label>
									<span id="fpCount" style="background:var(--success-bg, #d1fae5);color:var(--success-text, #065f46);border-radius:20px;padding:1px 9px;font-size:.7rem;font-weight:600;">0 vacant</span>
								</div>
								<div id="fpGrid" class="flat-picker-grid"></div>
								<div id="fpEmpty" style="display:none;text-align:center;padding:22px;color:var(--text-light);font-size:.82rem;">
									<i class="fas fa-door-open" style="display:block;font-size:1.5rem;opacity:.3;margin-bottom:5px;"></i>No vacant flats for this selection.
								</div>
							</div>

						<?php else: ?>
							<?php if (empty($vacantFlats)): ?>
								<div class="setup-prompt">
									<i class="fas fa-exclamation-triangle" style="color:var(--warning);margin-right:4px;"></i>
									<strong>No vacant flats available.</strong>
									<?php if (in_array(strtolower($role_name), ['chairman', 'super_admin'])): ?>
										<a href="<?= base_url('society_setup') ?>" style="color:var(--primary);font-weight:700;">Set up society structure →</a>
									<?php else: ?>Ask your chairman to configure the flat structure first.<?php endif; ?>
								</div>
							<?php else: ?>
								<div class="form-row" style="margin-bottom:10px;">
									<div class="form-group">
										<label><i class="fas fa-layer-group" style="color:var(--primary);margin-right:3px;"></i> Filter by Wing</label>
										<select id="fpWing" class="form-control" onchange="fpFilter()">
											<option value="">All Wings</option>
											<?php foreach ($wings as $w): ?>
												<option value="<?= $w->id ?>"><?= html_escape($w->wing_name) ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="form-group">
										<label><i class="fas fa-stairs" style="color:var(--primary);margin-right:3px;"></i> Filter by Floor</label>
										<select id="fpFloor" class="form-control" onchange="fpFilter()" disabled>
											<option value="">All Floors</option>
										</select>
									</div>
								</div>
								<div class="selected-flat-bar" id="selFlatBar">
									<div style="display:flex;align-items:center;gap:10px;">
										<div class="sfb-icon"><i class="fas fa-door-closed"></i></div>
										<div>
											<div id="selFlatNo" style="font-weight:800;font-size:1rem;color:var(--text-dark);"></div>
											<div id="selFlatMeta" style="font-size:.73rem;color:var(--text-light);"></div>
										</div>
									</div>
									<button type="button" class="sfb-clear" onclick="fpClear()"><i class="fas fa-times"></i> Clear</button>
								</div>
								<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
									<label style="font-size:.8rem;font-weight:600;color:var(--text-dark);margin:0;"><i class="fas fa-th" style="color:var(--primary);margin-right:3px;"></i> Available Flats</label>
									<span id="fpCount" style="background:var(--success-bg, #d1fae5);color:var(--success-text, #065f46);border-radius:20px;padding:1px 9px;font-size:.7rem;font-weight:600;"><?= count($vacantFlats) ?> vacant</span>
								</div>
								<div id="fpGrid" class="flat-picker-grid"></div>
								<div id="fpEmpty" style="display:none;text-align:center;padding:22px;color:var(--text-light);font-size:.82rem;">
									<i class="fas fa-door-open" style="display:block;font-size:1.5rem;opacity:.3;margin-bottom:5px;"></i>No vacant flats for this selection.
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<div style="font-size:.7rem;font-weight:700;letter-spacing:.1em;color:var(--text-light);text-transform:uppercase;display:flex;align-items:center;gap:10px;margin:12px 0 10px;">
						<span style="flex:1;height:1px;background:var(--border);"></span>Login Credentials<span style="flex:1;height:1px;background:var(--border);"></span>
					</div>
					<div class="form-group">
						<label>Password <span id="passwordHint" style="color:var(--text-light);font-size:.75rem;font-weight:400;">(required for new members)</span></label>
						<input type="password" name="password" class="form-control" id="password" placeholder="Min 6 characters">
					</div>
					<div class="modal-footer" style="padding:0;margin-top:10px;">
						<button type="button" class="btn btn-outline" onclick="closeModal('memberFormModal')"><i class="fas fa-times"></i> Cancel</button>
						<button type="submit" class="btn btn-primary" id="saveMemberBtn"><i class="fas fa-save"></i> Save Member</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- ASSIGN ROLE MODAL -->
	<div class="modal" id="committeeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3><i class="fas fa-users-cog"></i> Assign Committee Role</h3>
				<span class="modal-close" onclick="closeModal('committeeModal')">&times;</span>
			</div>
			<div class="modal-body">
				<form method="post" action="<?= base_url('feature_controller/assign_role') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
					<div class="form-group"><label>Select Member</label>
						<select class="form-control" name="committeeMemberId" id="committeeMemberSelect" required>
							<option value="">-- Select Member --</option>
							<?php foreach ($members as $m): ?>
								<option value="<?= $m->id ?>"><?= html_escape($m->name) ?> (<?= html_escape(($m->wing_name ?? '') . ' ' . $m->flat_no) ?>)</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group"><label>Committee Role</label>
						<select class="form-control" name="committeeRole" id="committeeRoleSelect" required>
							<option value="">-- Select Role --</option>
							<?php foreach ($committee_roles as $role): ?>
								<option value="<?= $role->id ?>"><?= ucwords(str_replace('_', ' ', $role->role_name)) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline" onclick="closeModal('committeeModal')">Cancel</button>
						<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Assign Role</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php endif; ?>

</div><!-- /.main -->

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
/* ── AJAX filter ── */
function loadData() {
	$.post("<?= site_url('feature_controller/filter_ajax') ?>", $("#filterForm").serialize(), function(res) {
		let data = JSON.parse(res);
		$("#membersTableBody").html(data.html);
	});
}
$("#filterForm").on("submit", function(e) { e.preventDefault(); loadData(); });
$(document).on("change", "#filterForm select", loadData);
let timer;
$(document).on("keyup", "input[name='search']", function() { clearTimeout(timer); timer = setTimeout(loadData, 300); });

/* ── Modal helpers ── */
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
window.addEventListener('click',   e => { if (e.target.classList.contains('modal')) e.target.classList.remove('active'); });
window.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active')); });

<?php if ($canManage): ?>
/* ════════════════════════════════
   CSV IMPORT
════════════════════════════════ */
function openImportModal() { clearFileChosen(); openModal('importModal'); }

function handleFileChosen(input) {
	const file = input.files[0]; if (!file) return;
	if (!file.name.toLowerCase().endsWith('.csv')) { alert('Only .csv files are allowed.'); input.value = ''; return; }
	if (file.size > 2 * 1024 * 1024) { alert('File too large. Maximum size is 2 MB.'); input.value = ''; return; }
	document.getElementById('fcName').textContent = file.name;
	document.getElementById('fcSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
	document.getElementById('fileChosen').classList.add('show');
}
function clearFileChosen() {
	const inp = document.getElementById('csvFileInput'); if (inp) inp.value = '';
	document.getElementById('fileChosen')?.classList.remove('show');
}

/* Drag & drop */
const dz = document.getElementById('dropZone');
if (dz) {
	['dragenter','dragover'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.add('drag-over'); }));
	['dragleave','drop'].forEach(e => dz.addEventListener(e, ev => { ev.preventDefault(); dz.classList.remove('drag-over'); }));
	dz.addEventListener('drop', ev => {
		const file = ev.dataTransfer.files[0]; if (!file) return;
		const inp = document.getElementById('csvFileInput');
		const dt = new DataTransfer(); dt.items.add(file); inp.files = dt.files;
		handleFileChosen(inp);
	});
}

document.getElementById('importForm')?.addEventListener('submit', function(e) {
	<?php if ($isSuperAdmin): ?>
	const selVal = document.getElementById('importSocietyId')?.value;
	if (!selVal) { e.preventDefault(); alert('Please select a society before importing.'); return; }
	document.getElementById('importSocietyIdHidden').value = selVal;
	<?php endif; ?>
	const inp = document.getElementById('csvFileInput');
	if (!inp || !inp.files.length) { e.preventDefault(); alert('Please select a CSV file.'); return; }
	const btn = document.getElementById('importSubmitBtn');
	btn.innerHTML = '<span class="spinner-sm"></span> Importing…'; btn.disabled = true;
});

/* ════════════════════════════════
   FLAT PICKER
════════════════════════════════ */
let allFlats     = <?= json_encode(array_map(fn($f) => [
	'id'          => (int)$f->id,
	'flat_no'     => $f->flat_no,
	'floor'       => (int)$f->floor,
	'flat_type'   => $f->flat_type,
	'wing_id'     => (int)($f->wing_id ?? 0),
	'wing_name'   => $f->wing_name ?? '',
	'floor_label' => $f->floor_label ?? '',
], $vacantFlats ?? [])) ?>;
let filteredFlats = [...allFlats];
let pickedFlat    = null;

function fpRender(flats) {
	const grid = document.getElementById('fpGrid');
	const empty = document.getElementById('fpEmpty');
	const cnt   = document.getElementById('fpCount');
	if (!grid) return;
	grid.innerHTML = '';
	if (!flats.length) {
		grid.style.display = 'none';
		if (empty) empty.style.display = 'block';
		if (cnt)   cnt.textContent = '0 vacant'; return;
	}
	grid.style.display = 'grid'; if (empty) empty.style.display = 'none';
	if (cnt) cnt.textContent = flats.length + ' vacant';
	const byFloor = {};
	flats.forEach(f => {
		const k = f.floor_label || (f.floor === 0 ? 'Ground Floor' : f.floor + ' Floor');
		if (!byFloor[k]) byFloor[k] = []; byFloor[k].push(f);
	});
	Object.entries(byFloor).forEach(([lbl, fls]) => {
		const hdr = document.createElement('div'); hdr.className = 'fp-floor-hdr';
		hdr.innerHTML = `<i class="fas fa-stairs" style="margin-right:3px;color:var(--primary);"></i>${lbl}`;
		grid.appendChild(hdr);
		fls.forEach(f => {
			const t = document.createElement('button'); t.type = 'button'; t.className = 'fp-tile'; t.dataset.id = f.id;
			t.innerHTML = `<div class="ft-no">${f.flat_no}</div><div class="ft-type">${f.flat_type}</div>`;
			t.addEventListener('click', () => fpPick(f, t)); grid.appendChild(t);
		});
	});
}
function fpPick(flat, tile) {
	document.querySelectorAll('.fp-tile.selected').forEach(t => t.classList.remove('selected'));
	tile.classList.add('selected'); pickedFlat = flat;
	document.getElementById('hFlatNo').value = flat.flat_no;
	document.getElementById('hWingId').value = flat.wing_id;
	document.getElementById('hFlatId').value = flat.id;
	const bar = document.getElementById('selFlatBar'); bar.style.display = 'flex';
	document.getElementById('selFlatNo').textContent = flat.flat_no;
	document.getElementById('selFlatMeta').textContent = [flat.flat_type, flat.wing_name ? 'Wing ' + flat.wing_name : '', flat.floor_label].filter(Boolean).join(' · ');
}
function fpClear() {
	pickedFlat = null;
	document.getElementById('hFlatNo').value = '';
	document.getElementById('hWingId').value = '';
	document.getElementById('hFlatId').value = '';
	const bar = document.getElementById('selFlatBar'); if (bar) bar.style.display = 'none';
	document.querySelectorAll('.fp-tile.selected').forEach(t => t.classList.remove('selected'));
}
function fpFilter() {
	const wingId   = parseInt(document.getElementById('fpWing')?.value || 0) || 0;
	const floorSel = document.getElementById('fpFloor');
	const prevFloor = floorSel?.value || '';
	const seen = new Set(); const floors = [];
	allFlats.filter(f => !wingId || f.wing_id === wingId).forEach(f => {
		if (!seen.has(f.floor)) { seen.add(f.floor); floors.push({ floor: f.floor, label: f.floor_label }); }
	});
	floors.sort((a, b) => a.floor - b.floor);
	if (floorSel) {
		floorSel.innerHTML = '<option value="">All Floors</option>';
		floors.forEach(fl => {
			const o = document.createElement('option'); o.value = fl.floor; o.textContent = fl.label;
			if (String(fl.floor) === prevFloor) o.selected = true; floorSel.appendChild(o);
		});
		floorSel.disabled = floors.length === 0;
	}
	filteredFlats = allFlats.filter(f => {
		const wm = !wingId || f.wing_id === wingId;
		const fm = (floorSel?.value ?? '') === '' || String(f.floor) === String(floorSel?.value ?? '');
		return wm && fm;
	});
	fpRender(filteredFlats); fpClear();
}

<?php if ($isSuperAdmin): ?>
function saLoadFlats(societyId) {
	const inner   = document.getElementById('saFlatPickerInner');
	const loaderMsg = document.getElementById('saFlatLoaderMsg');
	fpClear(); allFlats = []; filteredFlats = [];
	const grid = document.getElementById('fpGrid'); if (grid) grid.innerHTML = '';
	const wingEl  = document.getElementById('fpWing');
	const floorEl = document.getElementById('fpFloor');
	if (wingEl)  wingEl.innerHTML  = '<option value="">All Wings</option>';
	if (floorEl) { floorEl.innerHTML = '<option value="">All Floors</option>'; floorEl.disabled = true; }

	if (!societyId) { if (inner) inner.style.display = 'none'; if (loaderMsg) loaderMsg.style.display = 'block'; return; }

	if (loaderMsg) loaderMsg.innerHTML = '<span class="spinner-sm"></span> Loading flats…';

	$.getJSON("<?= site_url('feature_controller/get_vacant_flats_ajax') ?>", { society_id: societyId }, function(data) {
		allFlats = data || []; filteredFlats = [...allFlats];
		const wings = {}; allFlats.forEach(f => { if (f.wing_id && !wings[f.wing_id]) wings[f.wing_id] = f.wing_name; });
		if (wingEl) { Object.entries(wings).forEach(([id, name]) => { const o = document.createElement('option'); o.value = id; o.textContent = name; wingEl.appendChild(o); }); }
		if (inner)     inner.style.display     = 'block';
		if (loaderMsg) loaderMsg.style.display = 'none';
		fpRender(allFlats);
	}).fail(function() {
		if (loaderMsg) loaderMsg.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i> Failed to load flats. Please try again.';
	});
}
<?php endif; ?>

/* ── Add / Edit member modals ── */
function openAddMemberModal() {
	document.getElementById('memberForm').reset();
	document.getElementById('memberId').value = '';
	document.getElementById('formModalTitle').innerText = 'Add New Member';
	document.getElementById('passwordHint').innerText = '(required for new members)';
	document.getElementById('memberType').value = 'owner';
	document.getElementById('statusSel').value  = '1';
	const fps = document.getElementById('flatPickerSection'); if (fps) fps.style.display = 'block';
	<?php if ($isSuperAdmin): ?>
	const saSel = document.getElementById('saSocietySelect'); if (saSel) saSel.value = '';
	const inner = document.getElementById('saFlatPickerInner'); if (inner) inner.style.display = 'none';
	const lm    = document.getElementById('saFlatLoaderMsg');
	if (lm) { lm.style.display = 'block'; lm.innerHTML = '<i class="fas fa-info-circle" style="color:#3b82f6;margin-right:4px;"></i> Select a society above to see available flats.'; }
	allFlats = []; filteredFlats = [];
	<?php else: ?>
	fpClear(); filteredFlats = [...allFlats]; fpRender(filteredFlats);
	const fpw = document.getElementById('fpWing');   if (fpw) fpw.value = '';
	const fpf = document.getElementById('fpFloor');
	if (fpf) { fpf.innerHTML = '<option value="">All Floors</option>'; fpf.disabled = true; }
	<?php endif; ?>
	openModal('memberFormModal');
}
function openEditMemberModal(m) {
	document.getElementById('formModalTitle').innerText = 'Edit Member';
	document.getElementById('passwordHint').innerText   = '(leave blank to keep current)';
	document.getElementById('memberId').value  = m.id ?? '';
	const parts = (m.name ?? '').split(' ');
	document.getElementById('firstName').value = parts[0] ?? '';
	document.getElementById('lastName').value  = parts.slice(1).join(' ') || '';
	document.getElementById('memberType').value = m.member_type ?? 'owner';
	document.getElementById('phone').value      = m.phone  ?? '';
	document.getElementById('email').value      = m.email  ?? '';
	document.getElementById('statusSel').value  = String(m.status ?? '1');
	document.getElementById('password').value   = '';
	document.getElementById('hFlatNo').value    = m.flat_no  ?? '';
	document.getElementById('hWingId').value    = m.wing_id  ?? '';
	const fps = document.getElementById('flatPickerSection'); if (fps) fps.style.display = 'none';
	<?php if ($isSuperAdmin): ?>
	const ssw = document.getElementById('saSocietyWrap'); if (ssw) ssw.style.display = 'none';
	<?php endif; ?>
	openModal('memberFormModal');
}
function openAssignRoleModal(id) {
	openModal('committeeModal');
	document.getElementById('committeeMemberSelect').value = id || '';
	document.getElementById('committeeRoleSelect').value   = '';
}

document.getElementById('memberForm')?.addEventListener('submit', function(e) {
	const isEdit = !!document.getElementById('memberId').value;
	if (!isEdit && document.getElementById('flatPickerSection')?.style.display !== 'none') {
		<?php if ($isSuperAdmin): ?>
		if (!document.getElementById('saSocietySelect')?.value) {
			e.preventDefault(); alert('Please select a society.'); return;
		}
		<?php endif; ?>
		if (!document.getElementById('hFlatNo').value) {
			e.preventDefault();
			const g = document.getElementById('fpGrid');
			if (g) { g.style.borderColor = '#dc2626'; setTimeout(() => g.style.borderColor = 'var(--border)', 2000); }
			alert('Please select a flat.'); return;
		}
	}
	const btn = document.getElementById('saveMemberBtn');
	if (btn) { btn.innerHTML = '<span class="spinner-sm"></span> Saving…'; btn.disabled = true; }
});

/* ── Export ── */
function toggleExportMenu(e) { e.stopPropagation(); document.getElementById('exportMenu').classList.toggle('show'); }
document.addEventListener('click', () => { const m = document.getElementById('exportMenu'); if (m) m.classList.remove('show'); });
function exportExcel() { XLSX.writeFile(XLSX.utils.table_to_book(document.getElementById('membersTable'), { sheet: 'Members' }), 'member_list.xlsx'); }
function exportCSV()   { const csv = XLSX.utils.sheet_to_csv(XLSX.utils.table_to_sheet(document.getElementById('membersTable'))); const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' })); a.download = 'member_list.csv'; a.click(); }
function printTable()  { window.print(); }
<?php endif; ?>

/* ── Flash dismiss ── */
document.addEventListener('DOMContentLoaded', function() {
	const f = document.getElementById('flashMsg');
	if (f) setTimeout(() => { f.style.transition = 'opacity .5s'; f.style.opacity = '0'; setTimeout(() => f.remove(), 500); }, 3500);
	<?php if ($canManage && !$isSuperAdmin): ?>
	if (allFlats.length) fpRender(allFlats);
	<?php endif; ?>
});
</script>
</body>
</html>
