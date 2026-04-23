<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport"
		content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes,viewport-fit=cover,shrink-to-fit=no">
	<title>Society – Staff</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
	<style>
		.staff-info {
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.staff-avatar {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			font-weight: 600;
			font-size: 1rem;
			flex-shrink: 0;
		}

		.staff-details h4 {
			font-weight: 700;
			font-size: .95rem;
			color: var(--text-dark);
			margin-bottom: 4px;
		}

		.staff-details span {
			font-size: .75rem;
			color: var(--text-light);
		}

		.status-badge.on-leave {
			background: rgba(243, 156, 18, .1);
			color: var(--warning);
		}

		.management-card {
			background: var(--card-bg);
			border-radius: 20px;
			padding: 20px;
			border: 1px solid var(--border);
			width: 100%;
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
		}

		.login-section {
			border: 1.5px dashed var(--border);
			border-radius: 12px;
			padding: 14px 16px 10px;
			margin-top: 6px;
			background: rgba(99, 102, 241, .03);
		}

		.login-section-title {
			font-size: .78rem;
			font-weight: 700;
			letter-spacing: .06em;
			text-transform: uppercase;
			color: var(--primary);
			margin-bottom: 10px;
			display: flex;
			align-items: center;
			gap: 6px;
		}

		.role-badge-preview {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			font-size: .75rem;
			font-weight: 600;
			padding: 3px 10px;
			border-radius: 20px;
			margin-left: 8px;
			transition: background .2s, color .2s;
		}

		.role-badge-preview.security {
			background: rgba(239, 68, 68, .12);
			color: #dc2626;
		}

		.role-badge-preview.staff {
			background: rgba(99, 102, 241, .12);
			color: #4f46e5;
		}

		.pass-toggle-wrap {
			position: relative;
		}

		.pass-toggle-wrap input {
			padding-right: 40px;
		}

		.pass-toggle-wrap .toggle-eye {
			position: absolute;
			right: 12px;
			top: 50%;
			transform: translateY(-50%);
			cursor: pointer;
			color: var(--text-light);
			font-size: .9rem;
			line-height: 1;
		}

		.pagination {
			display: flex;
			justify-content: flex-end;
			margin-top: 20px;
			gap: 6px;
			flex-wrap: wrap;
		}

		.pagination a,
		.pagination strong {
			display: inline-block;
			padding: 8px 14px;
			border-radius: 12px;
			background: var(--card-bg);
			border: 1px solid var(--border);
			color: var(--text-dark);
			font-weight: 500;
			text-decoration: none;
			transition: all 0.2s;
		}

		.pagination strong {
			background: var(--primary);
			color: #fff;
			border-color: var(--primary);
		}

		.pagination a:hover {
			background: var(--primary-light);
			border-color: var(--primary);
			color: var(--primary-dark);
		}
	</style>
</head>

<body>
	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'staff';
	include('sidebar.php'); ?>

	<div class="main" id="main">

		<?php if ($this->session->flashdata('success')): ?>
			<div class="notification success" id="flashMsg"><i class="fas fa-check-circle"></i>
				<?= $this->session->flashdata('success') ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="notification error" id="flashMsg"><i class="fas fa-exclamation-circle"></i>
				<?= $this->session->flashdata('error') ?></div>
		<?php endif; ?>

		<!-- Stats -->
		<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-users"></i></div>
				<div class="stat-info">
					<h4>Total Staff</h4>
					<h2><?= (int) ($stats['total'] ?? 0) ?></h2>
					<div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i>
						+<?= (int) ($stats['new_this_month'] ?? 0) ?> this month</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
				<div class="stat-info">
					<h4>Security</h4>
					<h2><?= (int) ($stats['security'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-broom"></i></div>
				<div class="stat-info">
					<h4>Housekeeping</h4>
					<h2><?= (int) ($stats['housekeeping'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-tools"></i></div>
				<div class="stat-info">
					<h4>Maintenance</h4>
					<h2><?= (int) ($stats['maintenance'] ?? 0) ?></h2>
				</div>
			</div>
		</div>

		<!-- Filter bar (GET, no AJAX) -->
		<form method="get" action="<?= site_url('staff') ?>" id="filterForm">
			<div class="filter-section">
				<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
					<div class="filter-group">
						<label><i class="fas fa-building"></i> Society</label>
						<select name="society_id" class="filter-select">
							<option value="">All Societies</option>
							<?php foreach ($societies as $soc): ?>
								<option value="<?= (int) $soc['id'] ?>" <?= ((int) ($filters['society_id'] ?? 0) === (int) $soc['id']) ? 'selected' : '' ?>>
									<?= html_escape($soc['name']) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<div class="filter-group">
					<label><i class="fas fa-filter"></i> Designation</label>
					<select name="designation" class="filter-select">
						<option value="">All Designations</option>
						<?php foreach (['Security Guard', 'Senior Security', 'Housekeeper', 'Senior Housekeeper', 'Maintenance', 'Electrician', 'Plumber', 'Manager', 'Supervisor', 'Accountant'] as $d): ?>
							<option value="<?= $d ?>" <?= (($filters['designation'] ?? '') === $d) ? 'selected' : '' ?>>
								<?= $d ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="filter-group">
					<label><i class="fas fa-flag"></i> Status</label>
					<select name="status" class="filter-select">
						<option value="">All Status</option>
						<option value="active" <?= (($filters['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active
						</option>
						<option value="inactive" <?= (($filters['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>
							Inactive</option>
						<option value="on-leave" <?= (($filters['status'] ?? '') === 'on-leave') ? 'selected' : '' ?>>On
							Leave</option>
					</select>
				</div>

				<div class="search-box">
					<i class="fas fa-search"></i>
					<input type="text" name="search" placeholder="Search name, email, phone..."
						value="<?= html_escape($filters['search'] ?? '') ?>">
				</div>

				<div style="display:flex;gap:8px;align-items:flex-end;">
					<button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
					<a href="<?= site_url('staff') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
				</div>
			</div>
		</form>

		<!-- Active filter pills -->
		<?php if ($anyFilter ?? false): ?>
			<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">
				<span style="font-size:.8rem;color:var(--text-light);font-weight:600;">Active filters:</span>
				<?php if (!empty($filters['society_id']) && $isSuperAdmin): ?>
					<?php
					$sLabel = '';
					foreach ($societies as $soc) {
						if ((int) $soc['id'] === (int) $filters['society_id']) {
							$sLabel = $soc['name'];
							break;
						}
					}
					$cu = site_url('staff?' . http_build_query(array_merge($filters, ['society_id' => ''])));
					?>
					<span class="active-filter-pill"><i class="fas fa-building"></i> <?= html_escape($sLabel) ?> <a
							href="<?= $cu ?>">×</a></span>
				<?php endif; ?>
				<?php if (!empty($filters['designation'])): ?>
					<?php $cu = site_url('staff?' . http_build_query(array_merge($filters, ['designation' => '']))); ?>
					<span class="active-filter-pill"><i class="fas fa-filter"></i> <?= html_escape($filters['designation']) ?>
						<a href="<?= $cu ?>">×</a></span>
				<?php endif; ?>
				<?php if ($filters['status'] !== ''): ?>
					<?php $cu = site_url('staff?' . http_build_query(array_merge($filters, ['status' => '']))); ?>
					<span class="active-filter-pill"><i class="fas fa-flag"></i> <?= ucfirst(html_escape($filters['status'])) ?>
						<a href="<?= $cu ?>">×</a></span>
				<?php endif; ?>
				<?php if (!empty($filters['search'])): ?>
					<?php $cu = site_url('staff?' . http_build_query(array_merge($filters, ['search' => '']))); ?>
					<span class="active-filter-pill"><i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>" <a
							href="<?= $cu ?>">×</a></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Staff Table -->
		<div class="table-section">
			<div class="table-header">
				<h3>
					<i class="fas fa-list"></i> Staff Directory
					<small style="font-weight:400;color:var(--text-light);font-size:.8rem;">(<?= $total_count ?>
						total)</small>
				</h3>
				<div class="page-actions">
					<button class="btn btn-outline" onclick="exportCSV()"><i class="fas fa-download"></i> Export
						CSV</button>
					<?php if (!empty($canManage)): ?>
						<button class="btn btn-primary" onclick="openAddStaffModal()"><i class="fas fa-plus-circle"></i> Add
							Staff</button>
					<?php endif; ?>
				</div>
			</div>

			<div class="table-wrapper">
				<table id="staffTable">
					<thead>
						<tr>
							<th>Staff Member</th>
							<th>Designation</th>
							<?php if (!empty($isSuperAdmin)): ?>
								<th>Society</th><?php endif; ?>
							<th>Contact</th>
							<th>Email</th>
							<th>Join Date</th>
							<th>Shift</th>
							<th>Status</th>
							<?php if (!empty($canManage)): ?>
								<th>Actions</th><?php endif; ?>
						</tr>
					</thead>
					<tbody id="staffTableBody">
						<?php if (!empty($staff)): ?>
							<?php foreach ($staff as $s): ?>
								<tr>
									<td>
										<div class="staff-info">
											<div class="staff-avatar">
												<?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'] ?? '', 0, 1)) ?>
											</div>
											<div class="staff-details">
												<h4><?= html_escape($s['first_name'] . ' ' . ($s['last_name'] ?? '')) ?></h4>
												<span><?= html_escape($s['department'] ?? '') ?></span>
											</div>
										</div>
									</td>
									<td><strong><?= html_escape($s['designation']) ?></strong></td>
									<?php if (!empty($isSuperAdmin)): ?>
										<td><?= html_escape($s['society_name'] ?? '—') ?></td>
									<?php endif; ?>
									<td><?= html_escape($s['phone']) ?></td>
									<td><?= html_escape($s['email']) ?></td>
									<td><?= !empty($s['join_date']) ? date('d/m/Y', strtotime($s['join_date'])) : '—' ?></td>
									<td><?= html_escape($s['shift'] ?? '') ?></td>
									<td><span
											class="status-badge <?= html_escape($s['status']) ?>"><?= ucfirst(html_escape($s['status'])) ?></span>
									</td>
									<?php if (!empty($canManage)): ?>
										<td>
											<div class="action-buttons">
												<button class="btn-icon" onclick="viewStaff(<?= (int) $s['id'] ?>)" title="View"><i
														class="fas fa-eye"></i></button>
												<button class="btn-icon" onclick="editStaff(<?= (int) $s['id'] ?>)" title="Edit"><i
														class="fas fa-edit"></i></button>
												<button class="btn-icon delete" onclick="deleteStaff(<?= (int) $s['id'] ?>)"
													title="Delete"><i class="fas fa-trash"></i></button>
											</div>
										</td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="<?= !empty($isSuperAdmin) ? (!empty($canManage) ? 9 : 8) : (!empty($canManage) ? 8 : 7) ?>"
									style="text-align:center;padding:30px;color:var(--text-light);">
									<i class="fas fa-users"
										style="font-size:2rem;margin-bottom:10px;display:block;opacity:.4"></i> No staff
									found
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			<?php if (!empty($pagination)): ?>
				<div class="pagination"><?= $pagination ?></div>
			<?php endif; ?>
		</div>

		<!-- Recent Staff Activity -->
		<div class="management-card" style="margin-bottom:30px;">
			<div class="section-header">
				<h3><i class="fas fa-clock"></i> Recent Staff Activity</h3>
			</div>
			<br>
			<div class="member-list">
				<?php if (!empty($recent_staff)): ?>
					<?php foreach ($recent_staff as $r): ?>
						<div class="member-item">
							<div class="member-info">
								<div class="member-avatar"></div>
								<div class="member-details">
									<h4><?= html_escape($r['first_name'] . ' ' . ($r['last_name'] ?? '')) ?></h4>
									<span>
										<?= html_escape($r['designation']) ?> · <?= html_escape($r['shift'] ?? '') ?> shift
										<?php if (!empty($isSuperAdmin) && !empty($r['society_name'])): ?> ·
											<em><?= html_escape($r['society_name']) ?></em><?php endif; ?>
									</span>
								</div>
							</div>
							<span
								class="member-status status-<?= html_escape($r['status']) ?>"><?= ucfirst(html_escape($r['status'])) ?></span>
						</div>
						<br>
					<?php endforeach; ?>
				<?php else: ?>
					<p style="text-align:center;color:var(--text-light);padding:20px;">No recent activity</p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Add / Edit Staff Modal -->
		<div class="modal" id="staffFormModal">
			<div class="modal-content">
				<div class="modal-header">
					<h3><i class="fas fa-user-plus"></i> <span id="formModalTitle">Add New Staff</span></h3>
					<span class="modal-close" onclick="closeModal('staffFormModal')">&times;</span>
				</div>
				<div class="modal-body">
					<form id="staffForm" method="post" action="<?= base_url('staff_controller/save') ?>">
						<input type="hidden" name="id" id="staffId">
						<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
							value="<?= $this->security->get_csrf_hash() ?>">

						<div class="form-row">
							<div class="form-group"><label>First Name *</label><input type="text" name="first_name"
									class="form-control" id="firstName" required></div>
							<div class="form-group"><label>Last Name</label><input type="text" name="last_name"
									class="form-control" id="lastName"></div>
						</div>
						<div class="form-row">
							<div class="form-group"><label>Email *</label><input type="email" name="email"
									class="form-control" id="email" required></div>
							<div class="form-group"><label>Phone *</label><input type="tel" name="phone"
									class="form-control" id="phone" required></div>
						</div>
						<div class="form-row">
							<div class="form-group">
								<label>Designation *</label>
								<select class="form-control" name="designation" id="designation" required>
									<option value="">Select</option>
									<?php foreach (['Security Guard', 'Senior Security', 'Housekeeper', 'Senior Housekeeper', 'Maintenance', 'Electrician', 'Plumber', 'Manager', 'Supervisor', 'Accountant'] as $d): ?>
										<option value="<?= $d ?>"><?= $d ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="form-group">
								<label>Department</label>
								<select class="form-control" name="department" id="department">
									<?php foreach (['Security', 'Housekeeping', 'Maintenance', 'Administration', 'Finance'] as $dep): ?>
										<option value="<?= $dep ?>"><?= $dep ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<?php if (!empty($isSuperAdmin)): ?>
							<div class="form-row">
								<div class="form-group">
									<label>Society *</label>
									<select class="form-control" name="society_id" id="societyInForm">
										<option value="">— Select Society —</option>
										<?php foreach ($societies as $soc): ?>
											<option value="<?= (int) $soc['id'] ?>" <?= ((int) ($filters['society_id'] ?? 0) === (int) $soc['id']) ? 'selected' : '' ?>>
												<?= html_escape($soc['name']) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						<?php endif; ?>

						<div class="form-row">
							<div class="form-group"><label>Join Date *</label><input type="date" name="join_date"
									class="form-control" id="joinDate" required></div>
							<div class="form-group">
								<label>Shift</label>
								<select class="form-control" name="shift" id="shift">
									<option value="Day">Day (6AM–2PM)</option>
									<option value="Evening">Evening (2PM–10PM)</option>
									<option value="Night">Night (10PM–6AM)</option>
									<option value="General">General (9AM–6PM)</option>
								</select>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group"><label>Salary (Monthly)</label><input type="number" name="salary"
									class="form-control" id="salary"></div>
							<div class="form-group"><label>Emergency Contact</label><input type="tel"
									name="emergency_contact" class="form-control" id="emergencyContact"></div>
						</div>
						<div class="form-group"><label>Address</label><textarea class="form-control" name="address"
								id="address" rows="2"></textarea></div>
						<div class="form-group">
							<label>Status</label>
							<select class="form-control" name="status" id="statusSel">
								<option value="active">Active</option>
								<option value="inactive">Inactive</option>
								<option value="on-leave">On Leave</option>
							</select>
						</div>

						<!-- LOGIN ACCOUNT SECTION -->
						<div class="login-section">
							<div class="login-section-title">
								<i class="fas fa-key"></i> Login Account
								<span class="role-badge-preview staff" id="roleBadgePreview">
									<i class="fas fa-user"></i> Staff
								</span>
							</div>

							<!-- ADD mode: password required -->
							<div id="addPasswordWrap">
								<div class="form-row">
									<div class="form-group">
										<label>Password * <small style="color:var(--text-light);font-weight:400">(min 6
												chars)</small></label>
										<div class="pass-toggle-wrap">
											<input type="password" name="password" id="password" class="form-control"
												placeholder="Set login password" autocomplete="new-password">
											<span class="toggle-eye" onclick="togglePass('password', this)"><i
													class="fas fa-eye"></i></span>
										</div>
									</div>
									<div class="form-group">
										<label>Confirm Password *</label>
										<div class="pass-toggle-wrap">
											<input type="password" id="passwordConfirm" class="form-control"
												placeholder="Re-enter password" autocomplete="new-password">
											<span class="toggle-eye" onclick="togglePass('passwordConfirm', this)"><i
													class="fas fa-eye"></i></span>
										</div>
									</div>
								</div>
								<p style="font-size:.75rem;color:var(--text-light);margin-top:-6px;">
									<i class="fas fa-info-circle"></i> Login email will be the staff's email address
									above. Role is set automatically based on designation.
								</p>
							</div>

							<!-- EDIT mode: optional password reset -->
							<div id="editPasswordWrap" style="display:none;">
								<div class="form-row">
									<div class="form-group">
										<label>New Password <small
												style="color:var(--text-light);font-weight:400">(leave blank to keep
												current)</small></label>
										<div class="pass-toggle-wrap">
											<input type="password" name="new_password" id="newPassword"
												class="form-control" placeholder="Enter new password to reset"
												autocomplete="new-password">
											<span class="toggle-eye" onclick="togglePass('newPassword', this)"><i
													class="fas fa-eye"></i></span>
										</div>
									</div>
								</div>
								<p style="font-size:.75rem;color:var(--text-light);margin-top:-6px;">
									<i class="fas fa-info-circle"></i> Role is updated automatically when designation
									changes. Leave password blank to keep the existing password.
								</p>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline" onclick="closeModal('staffFormModal')"><i class="fas fa-times"></i>
						Cancel</button>
					<button class="btn btn-primary" onclick="saveStaff()"><i class="fas fa-save"></i> Save
						Staff</button>
				</div>
			</div>
		</div>

		<!-- Delete Confirm Modal -->
		<div class="modal" id="deleteModal">
			<div class="modal-content" style="max-width:400px;">
				<div class="modal-header">
					<h3><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Confirm Delete</h3>
					<span class="modal-close" onclick="closeModal('deleteModal')">&times;</span>
				</div>
				<div class="modal-body" style="text-align:center;padding:24px;">
					<i class="fas fa-trash"
						style="font-size:3rem;color:var(--danger);margin-bottom:15px;display:block"></i>
					Are you sure you want to delete this staff member?<br>
					<span style="color:var(--danger);font-size:.9rem;display:block;margin-top:10px;">This will also
						remove their login account. This cannot be undone.</span>
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline" onclick="closeModal('deleteModal')"><i class="fas fa-times"></i>
						Cancel</button>
					<button class="btn btn-primary" style="background:var(--danger)" onclick="confirmDelete()"><i
							class="fas fa-trash"></i> Delete</button>
				</div>
			</div>
		</div>

		<!-- View Staff Modal -->
		<div class="modal" id="viewStaffModal">
			<div class="modal-content">
				<div class="modal-header">
					<h3><i class="fas fa-user"></i> Staff Details</h3>
					<span class="modal-close" onclick="closeModal('viewStaffModal')">&times;</span>
				</div>
				<div class="modal-body">
					<div class="staff-info" style="margin-bottom:20px;">
						<div class="staff-avatar" id="viewAvatar" style="width:56px;height:56px;font-size:1.2rem"></div>
						<div class="staff-details">
							<h4 id="viewName" style="font-size:1.1rem"></h4>
							<span id="viewDesignation"></span>
						</div>
					</div>
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;">
						<p><strong>Email:</strong><br><span id="viewEmail"></span></p>
						<p><strong>Phone:</strong><br><span id="viewPhone"></span></p>
						<p><strong>Department:</strong><br><span id="viewDepartment"></span></p>
						<p><strong>Shift:</strong><br><span id="viewShift"></span></p>
						<p><strong>Status:</strong><br><span id="viewStatus"></span></p>
						<p><strong>Join Date:</strong><br><span id="viewJoinDate"></span></p>
						<p><strong>Login Role:</strong><br><span id="viewRole"></span></p>
						<p style="grid-column:1/-1"><strong>Address:</strong><br><span id="viewAddress"></span></p>
						<?php if (!empty($isSuperAdmin)): ?>
							<p style="grid-column:1/-1"><strong>Society:</strong><br><span id="viewSociety"></span></p>
						<?php endif; ?>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline" onclick="closeModal('viewStaffModal')"><i class="fas fa-times"></i>
						Close</button>
					<?php if (!empty($canManage)): ?>
						<button class="btn btn-primary" onclick="editFromView()"><i class="fas fa-edit"></i> Edit
							Staff</button>
					<?php endif; ?>
				</div>
			</div>
		</div>

	</div><!-- /.main -->

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		var baseUrl = '<?= base_url() ?>';
		var currentStaffId = null;
		var deleteId = null;
		var securityDesignations = ['Security Guard', 'Senior Security'];

		document.addEventListener('DOMContentLoaded', function () {
			var f = document.getElementById('flashMsg');
			if (f) setTimeout(function () {
				f.style.transition = 'opacity .5s';
				f.style.opacity = '0';
				setTimeout(function () { f.remove(); }, 500);
			}, 3500);
		});

		document.getElementById('designation').addEventListener('change', function () {
			var badge = document.getElementById('roleBadgePreview');
			if (securityDesignations.indexOf(this.value) !== -1) {
				badge.className = 'role-badge-preview security';
				badge.innerHTML = '<i class="fas fa-shield-alt"></i> Security';
			} else {
				badge.className = 'role-badge-preview staff';
				badge.innerHTML = '<i class="fas fa-user"></i> Staff';
			}
		});

		function togglePass(fieldId, icon) {
			var inp = document.getElementById(fieldId);
			var isText = inp.type === 'text';
			inp.type = isText ? 'password' : 'text';
			icon.querySelector('i').className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
		}

		function exportCSV() {
			var rows = [], headers = [];
			$('#staffTable thead th').each(function () {
				var t = $(this).text().trim();
				if (t !== 'Actions') headers.push('"' + t + '"');
			});
			rows.push(headers.join(','));
			$('#staffTableBody tr').each(function () {
				var cols = [];
				$(this).find('td').each(function () {
					if ($(this).find('.action-buttons').length) return;
					var txt = $(this).text().replace(/\s+/g, ' ').trim();
					cols.push('"' + txt.replace(/"/g, '""') + '"');
				});
				if (cols.length) rows.push(cols.join(','));
			});
			var blob = new Blob([rows.join('\n')], { type: 'text/csv' });
			var a = document.createElement('a');
			a.href = URL.createObjectURL(blob);
			a.download = 'staff_export.csv';
			a.click();
			URL.revokeObjectURL(a.href);
		}

		function openAddStaffModal() {
			$('#staffForm')[0].reset();
			$('#staffId').val('');
			$('#formModalTitle').text('Add New Staff');
			$('#joinDate').val(new Date().toISOString().split('T')[0]);
			$('#addPasswordWrap').show();
			$('#editPasswordWrap').hide();
			$('#password').attr('required', true);
			var badge = document.getElementById('roleBadgePreview');
			badge.className = 'role-badge-preview staff';
			badge.innerHTML = '<i class="fas fa-user"></i> Staff';
			openModal('staffFormModal');
		}

		function editStaff(id) {
			$.get(baseUrl + 'staff/edit/' + id, function (data) {
				if (!data || data.error) { showNotification(data?.error || 'Not found', 'error'); return; }
				$('#staffId').val(data.id);
				$('#firstName').val(data.first_name || '');
				$('#lastName').val(data.last_name || '');
				$('#email').val(data.email || '');
				$('#phone').val(data.phone || '');
				$('#designation').val(data.designation || '').trigger('change');
				$('#department').val(data.department || '');
				$('#joinDate').val(data.join_date || '');
				$('#shift').val(data.shift || '');
				$('#salary').val(data.salary || '');
				$('#emergencyContact').val(data.emergency_contact || '');
				$('#address').val(data.address || '');
				$('#statusSel').val(data.status || 'active');
				if ($('#societyInForm').length) $('#societyInForm').val(data.society_id || '');
				$('#formModalTitle').text('Edit Staff');
				$('#addPasswordWrap').hide();
				$('#editPasswordWrap').show();
				$('#password').removeAttr('required').val('');
				$('#newPassword').val('');
				openModal('staffFormModal');
			}, 'json').fail(function () { showNotification('Failed to load staff data.', 'error'); });
		}

		function viewStaff(id) {
			currentStaffId = id;
			openModal('viewStaffModal');
			$.get(baseUrl + 'staff/edit/' + id, function (data) {
				if (!data || data.error) { showNotification('Staff not found', 'error'); closeModal('viewStaffModal'); return; }
				$('#viewName').text((data.first_name || '') + ' ' + (data.last_name || ''));
				$('#viewDesignation').text(data.designation || '—');
				$('#viewEmail').text(data.email || '—');
				$('#viewPhone').text(data.phone || '—');
				$('#viewDepartment').text(data.department || '—');
				$('#viewShift').text(data.shift || '—');
				$('#viewStatus').text(data.status || '—');
				$('#viewJoinDate').text(data.join_date || '—');
				$('#viewAddress').text(data.address || '—');
				var isSecDes = securityDesignations.indexOf(data.designation) !== -1;
				$('#viewRole').html(isSecDes ? '<span style="color:#dc2626;font-weight:600"><i class="fas fa-shield-alt"></i> Security</span>' : '<span style="color:#4f46e5;font-weight:600"><i class="fas fa-user"></i> Staff</span>');
				if ($('#viewSociety').length) $('#viewSociety').text(data.society_name || '—');
				$('#viewAvatar').text(((data.first_name || '').charAt(0) + (data.last_name || '').charAt(0)).toUpperCase());
			}, 'json');
		}

		function editFromView() { closeModal('viewStaffModal'); if (currentStaffId) editStaff(currentStaffId); }

		function saveStaff() {
			var isAdd = !$('#staffId').val();
			if (isAdd) {
				var pw = $('#password').val();
				var cpw = $('#passwordConfirm').val();
				if (!pw) { showNotification('Password is required.', 'error'); return; }
				if (pw.length < 6) { showNotification('Password must be at least 6 characters.', 'error'); return; }
				if (pw !== cpw) { showNotification('Passwords do not match.', 'error'); return; }
			}
			var form = document.getElementById('staffForm');
			fetch(form.action, {
				method: 'POST',
				body: new FormData(form),
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then(r => r.json())
				.then(d => {
					if (d.status) {
						showNotification(d.message || 'Saved successfully', 'success');
						setTimeout(() => location.reload(), 1200);
					} else {
						showNotification(d.message || 'Error saving staff', 'error');
					}
				})
				.catch(() => showNotification('Server error', 'error'));
		}

		function deleteStaff(id) { deleteId = id; openModal('deleteModal'); }
		function confirmDelete() {
			$.post(baseUrl + 'staff/delete/' + deleteId, function (res) {
				if (res.status === 'success' || res.status === true) {
					closeModal('deleteModal');
					showNotification('Staff and login account deleted successfully', 'success');
					setTimeout(() => location.reload(), 800);
				} else {
					showNotification(res.message || 'Delete failed', 'error');
				}
			}, 'json').fail(() => showNotification('Delete request failed', 'error'));
		}

		function openModal(id) { $('#' + id).addClass('active'); $('#overlay').addClass('active'); }
		function closeModal(id) { $('#' + id).removeClass('active'); $('#overlay').removeClass('active'); }
		$('#overlay').on('click', function () { $('.modal.active').removeClass('active'); $(this).removeClass('active'); });
		$(document).on('keydown', function (e) { if (e.key === 'Escape') { $('.modal.active').removeClass('active'); $('#overlay').removeClass('active'); } });

		function showNotification(message, type = 'success') {
			var n = document.createElement('div');
			n.className = 'notification ' + type;
			n.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + message;
			document.body.appendChild(n);
			setTimeout(() => { n.style.animation = 'slideOut 0.3s ease'; setTimeout(() => n.remove(), 300); }, 3500);
		}
	</script>
</body>

</html>
