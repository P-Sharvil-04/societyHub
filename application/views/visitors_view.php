<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport"
		content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes,viewport-fit=cover">
	<title>SocietyHub · Visitors</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<style>
		/* ── Visitor avatars & info ── */
		.visitor-avatar {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			flex-shrink: 0;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			font-weight: 700;
			font-size: 1rem;
		}

		.visitor-info {
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.visitor-details h4 {
			font-weight: 700;
			font-size: .92rem;
			color: var(--text-dark);
			margin-bottom: 3px;
		}

		.visitor-details span {
			font-size: .72rem;
			color: var(--text-light);
		}

		/* ── Flat tag ── */
		.flat-tag {
			background: var(--bg-light, #f5f5f5);
			padding: 3px 9px;
			border-radius: 20px;
			font-size: .75rem;
			font-weight: 600;
			color: var(--text-dark);
		}

		/* ── Status pills ── */
		.status-pill {
			padding: 3px 10px;
			border-radius: 30px;
			font-size: .68rem;
			font-weight: 700;
			letter-spacing: .4px;
			text-transform: uppercase;
			display: inline-block;
		}

		.status-pill.checked-in {
			background: rgba(46, 204, 113, .15);
			color: #27ae60;
		}

		.status-pill.checked-out {
			background: rgba(155, 89, 182, .15);
			color: #8e44ad;
		}

		.status-pill.pending {
			background: rgba(241, 196, 15, .15);
			color: #d68910;
		}

		/* ── Approval pills ── */
		.appr-pill {
			padding: 3px 9px;
			border-radius: 30px;
			font-size: .66rem;
			font-weight: 700;
			display: inline-block;
			margin-top: 4px;
		}

		.appr-pill.approved {
			background: rgba(46, 204, 113, .13);
			color: #27ae60;
		}

		.appr-pill.rejected {
			background: rgba(231, 76, 60, .13);
			color: #c0392b;
		}

		.appr-pill.pending {
			background: rgba(241, 196, 15, .13);
			color: #d68910;
		}

		/* ── Action-column helpers ── */
		.action-buttons {
			display: flex;
			align-items: center;
			gap: 5px;
			flex-wrap: wrap;
		}

		.awaiting-label {
			font-size: .7rem;
			color: var(--text-light);
			font-style: italic;
			white-space: nowrap;
		}

		.done-label {
			font-size: .7rem;
			color: #8e44ad;
			font-style: italic;
			white-space: nowrap;
		}

		/* coloured icon-buttons */
		.btn-icon.approve {
			color: #27ae60;
		}

		.btn-icon.approve:hover {
			background: rgba(46, 204, 113, .12);
		}

		.btn-icon.reject {
			color: var(--danger, #e74c3c);
		}

		.btn-icon.reject:hover {
			background: rgba(231, 76, 60, .10);
		}

		.btn-icon.checkout-btn {
			color: #8e44ad;
		}

		.btn-icon.checkout-btn:hover {
			background: rgba(155, 89, 182, .12);
		}

		/* ── Role banner ── */
		.role-banner {
			display: flex;
			align-items: flex-start;
			gap: 12px;
			background: linear-gradient(135deg, #eef2ff, #e0e7ff);
			border: 1px solid #c7d2fe;
			border-radius: 14px;
			padding: 13px 18px;
			margin-bottom: 20px;
			font-size: .85rem;
			color: #3730a3;
		}

		.role-banner i {
			font-size: 1.15rem;
			margin-top: 2px;
		}

		/* ── Active filter pills ── */
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

		/* ── Pagination ── */
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
			transition: all .2s;
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

		.pg-ellipsis {
			padding: 8px 6px;
			color: var(--text-light);
		}

		/* ── Modals ── */
		.modal {
			display: none;
			position: fixed;
			inset: 0;
			z-index: 1050;
			align-items: center;
			justify-content: center;
		}

		.modal.active {
			display: flex;
		}

		.modal-backdrop {
			position: absolute;
			inset: 0;
			background: rgba(0, 0, 0, .45);
		}

		.modal-content {
			position: relative;
			z-index: 1;
			background: var(--card-bg);
			border-radius: 20px;
			width: 100%;
			max-width: 540px;
			margin: 20px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, .22);
			animation: modalIn .18s ease;
		}

		.modal-content.sm {
			max-width: 420px;
		}

		@keyframes modalIn {
			from {
				opacity: 0;
				transform: scale(.95) translateY(10px);
			}

			to {
				opacity: 1;
				transform: scale(1) translateY(0);
			}
		}

		.modal-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 20px 24px 16px;
			border-bottom: 1px solid var(--border);
		}

		.modal-header h3 {
			font-size: 1rem;
			font-weight: 700;
			display: flex;
			align-items: center;
			gap: 8px;
			margin: 0;
		}

		.modal-close {
			width: 32px;
			height: 32px;
			border-radius: 50%;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.2rem;
			color: var(--text-light);
			background: var(--bg-light);
			border: none;
			transition: background .2s;
		}

		.modal-close:hover {
			background: var(--border);
		}

		.modal-body {
			padding: 20px 24px;
		}

		.modal-footer {
			display: flex;
			justify-content: flex-end;
			gap: 10px;
			padding: 16px 24px;
			border-top: 1px solid var(--border);
		}

		/* ── Info note inside add form ── */
		.info-note {
			background: #fef9e7;
			border: 1px solid #f9ca24;
			border-radius: 10px;
			padding: 10px 14px;
			font-size: .82rem;
			color: #7d6608;
			margin-top: 6px;
		}

		/* ── Locked row (Checked Out) ── */
		tr.locked-row {
			opacity: .7;
		}

		tr.locked-row td {
			color: var(--text-light);
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
	<?php $activePage = 'visitors';
	include('sidebar.php'); ?>

	<div class="main" id="main">

		<!-- Flash messages -->
		<?php if ($this->session->flashdata('success')): ?>
			<div class="notification success" id="flashMsg">
				<i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
			</div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="notification error" id="flashMsg">
				<i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
			</div>
		<?php endif; ?>

		<!-- Role guidance banner -->
		<?php if ($isSecurity): ?>
			<div class="role-banner">
				<i class="fas fa-shield-alt"></i>
				<div>
					<strong>Security View:</strong> Log visitor arrivals using <em>Log Check-In Request</em>.
					Once the flat owner approves, use the <strong>Check Out</strong>
					<i class="fas fa-sign-out-alt" style="font-size:.8rem"></i> button when the visitor leaves.
					Exit time is recorded automatically.
				</div>
			</div>
		<?php elseif ($isOwner): ?>
			<div class="role-banner">
				<i class="fas fa-home"></i>
				<div>
					<strong>Note:</strong> Approve or reject visitors coming to your flat.
					You can change your decision any time <em>until</em> the visitor has checked out.
					Once <strong>Checked Out</strong>, the record is locked.
				</div>
			</div>
		<?php endif; ?>

		<!-- Stats -->
		<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-users"></i></div>
				<div class="stat-info">
					<h4>Total Visitors</h4>
					<h2><?= (int) ($stats['total'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
				<div class="stat-info">
					<h4>Checked In</h4>
					<h2><?= (int) ($stats['checked_in'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
				<div class="stat-info">
					<h4>Checked Out</h4>
					<h2><?= (int) ($stats['checked_out'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-clock"></i></div>
				<div class="stat-info">
					<h4>Pending Approval</h4>
					<h2><?= (int) ($stats['pending_approval'] ?? 0) ?></h2>
				</div>
			</div>
		</div>

		<!-- ── Filter bar ── -->
		<form method="get" action="<?= site_url('visitors') ?>">
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
					<label><i class="fas fa-flag"></i> Status</label>
					<select name="status" class="filter-select">
						<option value="">All Status</option>
						<option value="Pending" <?= ($filters['status'] === 'Pending') ? 'selected' : '' ?>>Pending
						</option>
						<option value="Checked In" <?= ($filters['status'] === 'Checked In') ? 'selected' : '' ?>>Checked
							In
						</option>
						<option value="Checked Out" <?= ($filters['status'] === 'Checked Out') ? 'selected' : '' ?>>Checked
							Out</option>
					</select>
				</div>

				<div class="filter-group">
					<label><i class="fas fa-check-circle"></i> Approval</label>
					<select name="approval_status" class="filter-select">
						<option value="">All</option>
						<option value="pending" <?= ($filters['approval_status'] === 'pending') ? 'selected' : '' ?>>
							Pending
						</option>
						<option value="approved" <?= ($filters['approval_status'] === 'approved') ? 'selected' : '' ?>>
							Approved</option>
						<option value="rejected" <?= ($filters['approval_status'] === 'rejected') ? 'selected' : '' ?>>
							Rejected</option>
					</select>
				</div>

				<div class="search-box">
					<i class="fas fa-search"></i>
					<input type="text" name="search" placeholder="Search name, flat, purpose…"
						value="<?= html_escape($filters['search'] ?? '') ?>">
				</div>

				<div style="display:flex;gap:8px;align-items:flex-end;">
					<button type="submit" class="btn btn-outline"><i class="fas fa-search"></i> Search</button>
					<a href="<?= site_url('visitors') ?>" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
				</div>
			</div>
		</form>

		<!-- Active filter pills -->
		<?php
		$anyFilter = !empty($filters['society_id']) || !empty($filters['status'])
			|| !empty($filters['approval_status']) || !empty($filters['search']);
		if ($anyFilter): ?>
			<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;align-items:center;">
				<span style="font-size:.8rem;color:var(--text-light);font-weight:600;">Active filters:</span>

				<?php if (!empty($filters['society_id']) && $isSuperAdmin):
					$sLabel = '';
					foreach ($societies as $s) {
						if ((int) $s['id'] === (int) $filters['society_id']) {
							$sLabel = $s['name'];
							break;
						}
					}
					?>
					<span class="active-filter-pill">
						<i class="fas fa-building"></i> <?= html_escape($sLabel) ?>
						<a
							href="<?= site_url('visitors?' . http_build_query(array_merge($filters, ['society_id' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>
				<?php if (!empty($filters['status'])): ?>
					<span class="active-filter-pill">
						<i class="fas fa-flag"></i> <?= html_escape($filters['status']) ?>
						<a href="<?= site_url('visitors?' . http_build_query(array_merge($filters, ['status' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>
				<?php if (!empty($filters['approval_status'])): ?>
					<span class="active-filter-pill">
						<i class="fas fa-check-circle"></i> <?= ucfirst($filters['approval_status']) ?>
						<a
							href="<?= site_url('visitors?' . http_build_query(array_merge($filters, ['approval_status' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>
				<?php if (!empty($filters['search'])): ?>
					<span class="active-filter-pill">
						<i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>"
						<a href="<?= site_url('visitors?' . http_build_query(array_merge($filters, ['search' => '']))) ?>">×</a>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- ════════════════════════════════════════
			 VISITOR TABLE
		════════════════════════════════════════ -->
		<div class="table-section">
			<div class="table-header">
				<h3>
					<i class="fas fa-list"></i> Visitor Log
					<small style="font-weight:400;color:var(--text-light);font-size:.8rem;">
						(<?= (int) $total_visitors ?> records)
					</small>
				</h3>
				<div class="page-actions">
					<?php if (!$isOwner): ?>
						<button class="btn btn-outline" onclick="exportCSV()">
							<i class="fas fa-download"></i> Export CSV
						</button>
					<?php endif; ?>
					<?php if ($isSecurity || $isAdmin || $isSuperAdmin): ?>
						<button class="btn btn-primary" onclick="openModal('addModal')">
							<i class="fas fa-plus-circle"></i>
							<?= $isSecurity ? 'Log Check-In Request' : 'New Visitor' ?>
						</button>
					<?php endif; ?>
				</div>
			</div>

			<div class="table-wrapper">
				<table id="visitorTable">
					<thead>
						<tr>
							<th>Visitor</th>
							<th>Contact</th>
							<th>Flat</th>
							<th>Purpose</th>
							<?php if (!empty($isSuperAdmin)): ?>
								<th>Society</th><?php endif; ?>
							<th>Check-in Time</th>
							<th>Check-out Time</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($visitors)): ?>
							<?php foreach ($visitors as $v):
								$stRaw = trim($v->status ?? 'Pending'); // original value for display
								$st = strtolower($stRaw);              // normalized for logic
								$stKey = str_replace(' ', '-', $st);  // for CSS class
						
								$appr = strtolower(trim($v->approval_status ?? 'pending'));
								$isCheckedOut = ($st === 'checked out');	
								$apprIconMap = [
									'approved' => 'fa-check',
									'rejected' => 'fa-times',
									'pending' => 'fa-hourglass-half',
								];
								$apprIcon = $apprIconMap[$appr] ?? 'fa-hourglass-half';
								$visitorNameSafe = html_escape(addslashes($v->visitor_name ?? ''));
								?>
								<tr class="<?= $isCheckedOut ? 'locked-row' : '' ?>">

									<!-- Visitor -->
									<td>
										<div class="visitor-info">
											<div class="visitor-avatar">
												<?= strtoupper(substr($v->visitor_name ?? 'V', 0, 1)) ?>
											</div>
											<div class="visitor-details">
												<h4><?= html_escape($v->visitor_name) ?></h4>
												<span><?= html_escape($v->purpose ?? 'Visit') ?></span>
											</div>
										</div>
									</td>

									<!-- Contact -->
									<td><?= !empty($v->phone) ? html_escape($v->phone) : '—' ?></td>

									<!-- Flat -->
									<td>
										<?= !empty($v->flat)
											? '<span class="flat-tag">' . html_escape($v->flat) . '</span>'
											: '—' ?>
									</td>

									<!-- Purpose -->
									<td><?= !empty($v->purpose) ? html_escape($v->purpose) : '—' ?></td>

									<!-- Society (super-admin only) -->
									<?php if (!empty($isSuperAdmin)): ?>
										<td><?= html_escape($v->society_name ?? '—') ?></td>
									<?php endif; ?>

									<!-- Check-in time -->
									<td>
										<?= !empty($v->entry_time)
											? date('d M Y, h:i A', strtotime($v->entry_time))
											: '—' ?>
									</td>

									<!-- Check-out time -->
									<td>
										<?php if (!empty($v->exit_time)): ?>
											<?= date('d M Y, h:i A', strtotime($v->exit_time)) ?>
										<?php else: ?>
											<span style="color:var(--text-light);font-style:italic;font-size:.78rem;">
												Auto on checkout
											</span>
										<?php endif; ?>
									</td>

									<!-- Status + Approval -->
									<td>
										<div style="display:flex;flex-direction:column;gap:3px;align-items:flex-start;">
											<span class="status-pill <?= $stKey ?>"><?= $st ?></span>
											<span class="appr-pill <?= $appr ?>">
												<i class="fas <?= $apprIcon ?>"></i> <?= ucfirst($appr) ?>
											</span>
										</div>
									</td>

									<!-- Actions -->
									<td>
										<div class="action-buttons">

											<?php if ($isCheckedOut): ?>
												<!-- Checked Out: record is locked -->
												<?php if ($isAdmin || $isSuperAdmin): ?>
													<a href="<?= site_url('visitors?edit_id=' . $v->id) ?>" class="btn-icon"
														title="Edit visitor record">
														<i class="fas fa-edit"></i>
													</a>
													<a href="<?= site_url('visitors/delete/' . $v->id) ?>" class="btn-icon delete"
														title="Delete"
														onclick="return confirm('Permanently delete <?= $visitorNameSafe ?>?')">
														<i class="fas fa-trash"></i>
													</a>
												<?php else: ?>
													<span class="done-label">
														<i class="fas fa-check-double"></i> Visit complete
													</span>
												<?php endif; ?>

											<?php elseif ($isSecurity): ?>
												<!-- Security actions -->
												<?php if ($appr === 'approved' && $st === 'Checked In'): ?>
													<a href="<?= site_url('visitors/checkout/' . $v->id) ?>"
														class="btn-icon checkout-btn" title="Check Out – exit time auto-recorded"
														onclick="return confirm('Check out <?= $visitorNameSafe ?>?\nExit time will be recorded automatically.')">
														<i class="fas fa-sign-out-alt"></i>
													</a>
												<?php elseif ($appr === 'rejected'): ?>
													<span class="awaiting-label" style="color:var(--danger)">
														<i class="fas fa-ban"></i> Entry rejected
													</span>
												<?php else: ?>
													<span class="awaiting-label">
														<i class="fas fa-hourglass-half"></i> Awaiting owner
													</span>
												<?php endif; ?>

											<?php elseif ($isOwner): ?>
												<!-- Owner actions (non-checked-out) -->
												<?php if ($appr === 'pending'): ?>
													<a href="<?= site_url('visitors/approve/' . $v->id) ?>" class="btn-icon approve"
														title="Approve – allow entry"
														onclick="return confirm('Approve entry for <?= $visitorNameSafe ?>?')">
														<i class="fas fa-check-circle"></i>
													</a>
													<button class="btn-icon reject" title="Reject – deny entry"
														onclick="openRejectModal(<?= $v->id ?>, '<?= $visitorNameSafe ?>')">
														<i class="fas fa-times-circle"></i>
													</button>

												<?php elseif ($appr === 'approved'): ?>
													<button class="btn-icon reject" title="Revoke approval / reject entry"
														onclick="openRejectModal(<?= $v->id ?>, '<?= $visitorNameSafe ?>', true)">
														<i class="fas fa-user-times"></i>
													</button>
													<span class="awaiting-label" style="color:#27ae60;margin-left:2px;">
														Approved &nbsp;·&nbsp; tap icon to revoke
													</span>

												<?php else: /* rejected */ ?>
													<a href="<?= site_url('visitors/approve/' . $v->id) ?>" class="btn-icon approve"
														title="Re-approve – change decision"
														onclick="return confirm('Re-approve entry for <?= $visitorNameSafe ?>?')">
														<i class="fas fa-user-check"></i>
													</a>
													<span class="awaiting-label" style="color:var(--danger);margin-left:2px;">
														Rejected &nbsp;·&nbsp; tap icon to re-approve
													</span>
												<?php endif; ?>

											<?php else: /* Admin / SuperAdmin – non-checked-out */ ?>
												<!-- Admin actions -->
												<?php if ($appr === 'pending'): ?>
													<a href="<?= site_url('visitors/approve/' . $v->id) ?>" class="btn-icon approve"
														title="Approve"
														onclick="return confirm('Approve entry for <?= $visitorNameSafe ?>?')">
														<i class="fas fa-check-circle"></i>
													</a>
													<button class="btn-icon reject" title="Reject"
														onclick="openRejectModal(<?= $v->id ?>, '<?= $visitorNameSafe ?>')">
														<i class="fas fa-times-circle"></i>
													</button>

												<?php elseif ($appr === 'approved'): ?>
													<a href="<?= site_url('visitors/checkout/' . $v->id) ?>"
														class="btn-icon checkout-btn" title="Check Out – auto exit time"
														onclick="return confirm('Check out <?= $visitorNameSafe ?>?\nExit time recorded automatically.')">
														<i class="fas fa-sign-out-alt"></i>
													</a>
													<button class="btn-icon reject" title="Revoke approval"
														onclick="openRejectModal(<?= $v->id ?>, '<?= $visitorNameSafe ?>', true)">
														<i class="fas fa-user-times"></i>
													</button>

												<?php else: /* rejected */ ?>
													<a href="<?= site_url('visitors/approve/' . $v->id) ?>" class="btn-icon approve"
														title="Re-approve"
														onclick="return confirm('Re-approve entry for <?= $visitorNameSafe ?>?')">
														<i class="fas fa-user-check"></i>
													</a>
												<?php endif; ?>

												<a href="<?= site_url('visitors?edit_id=' . $v->id) ?>" class="btn-icon"
													title="Edit">
													<i class="fas fa-edit"></i>
												</a>
												<a href="<?= site_url('visitors/delete/' . $v->id) ?>" class="btn-icon delete"
													title="Delete"
													onclick="return confirm('Permanently delete <?= $visitorNameSafe ?>? This cannot be undone.')">
													<i class="fas fa-trash"></i>
												</a>
											<?php endif; ?>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="<?= !empty($isSuperAdmin) ? 9 : 8 ?>"
									style="text-align:center;padding:50px 20px;color:var(--text-light);">
									<i class="fas fa-users"
										style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:12px;"></i>
									No visitors found
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php if (!empty($pagination)): ?>
				<div class="pagination"><?= $pagination ?></div>
			<?php endif; ?>
		</div>

		<!-- Recent Visitors panel -->
		<?php if (!empty($recent)): ?>
			<div class="management-card" style="margin-bottom:30px;">
				<div class="section-header">
					<h3><i class="fas fa-clock"></i> Recent Visitors</h3>
				</div>
				<div class="member-list">
					<?php foreach ($recent as $rv): ?>
						<div class="member-item">
							<div class="member-info">
								<div class="member-avatar"></div>
								<div class="member-details">
									<h4><?= html_escape($rv->visitor_name ?? '') ?></h4>
									<span>
										<?= !empty($rv->flat) ? 'Flat ' . html_escape($rv->flat) . ' · ' : '' ?>
										<?= html_escape($rv->purpose ?? 'Visit') ?>
										<?php if (!empty($isSuperAdmin) && !empty($rv->society_name)): ?>
											&nbsp;·&nbsp;<em><?= html_escape($rv->society_name) ?></em>
										<?php endif; ?>
									</span>
								</div>
							</div>
							<span class="status-pill <?= strtolower(str_replace(' ', '-', $rv->status ?? 'pending')) ?>">
								<?= html_escape($rv->status ?? 'Pending') ?>
							</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	</div><!-- /.main -->

	<!-- Add Visitor Modal -->
	<?php if ($isSecurity || $isAdmin || $isSuperAdmin): ?>
		<div class="modal" id="addModal" role="dialog" aria-modal="true">
			<div class="modal-backdrop" onclick="closeModal('addModal')"></div>
			<div class="modal-content">
				<div class="modal-header">
					<h3>
						<i class="fas fa-user-plus" style="color:var(--primary)"></i>
						<?= $isSecurity ? 'Log Check-In Request' : 'Add New Visitor' ?>
					</h3>
					<button class="modal-close" onclick="closeModal('addModal')" aria-label="Close">&times;</button>
				</div>

				<form method="POST" action="<?= site_url('visitors/add') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
						value="<?= $this->security->get_csrf_hash() ?>">

					<?php if (!empty($isSuperAdmin)): ?>
						<div class="modal-body" style="padding-bottom:0;">
							<div class="form-group">
								<label><i class="fas fa-building"></i> Society <span
										style="color:var(--danger)">*</span></label>
								<select name="society_id" class="form-control" required>
									<option value="">— Select Society —</option>
									<?php foreach ($societies as $soc): ?>
										<option value="<?= (int) $soc['id'] ?>"><?= html_escape($soc['name']) ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					<?php else: ?>
						<input type="hidden" name="society_id" value="<?= (int) $this->session->userdata('society_id') ?>">
					<?php endif; ?>

					<div class="modal-body">
						<div class="form-row">
							<div class="form-group">
								<label>Full Name <span style="color:var(--danger)">*</span></label>
								<input type="text" name="visitor_name" class="form-control"
									placeholder="Visitor's full name" required maxlength="150">
							</div>
							<div class="form-group">
								<label>Phone Number</label>
								<input type="tel" name="phone" class="form-control" placeholder="+91a 9999 999999">
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label>Flat / Unit <span style="color:var(--danger)">*</span></label>
								<input type="text" name="flat" class="form-control" placeholder="e.g. A-302" required>
							</div>
							<div class="form-group">
								<label>Purpose of Visit</label>
								<input type="text" name="purpose" class="form-control" placeholder="e.g. Delivery, Guest…">
							</div>
						</div>

						<div class="form-group">
							<label>Entry Time <span style="color:var(--danger)">*</span></label>
							<input type="datetime-local" name="entry_time" id="addEntryTime" class="form-control" required>
						</div>

						<div class="info-note">
							<i class="fas fa-info-circle"></i>
							<?php if ($isSecurity): ?>
								This logs a <strong>pending check-in request</strong>. The flat owner must approve before the
								visitor can enter. <strong>Exit time is recorded automatically</strong> when you check them out.
							<?php else: ?>
								Visitor will be created as <strong>Pending</strong> for owner approval. Exit time is recorded
								automatically on checkout.
							<?php endif; ?>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-paper-plane"></i>
							<?= $isSecurity ? 'Submit Request' : 'Add Visitor' ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	<?php endif; ?>

	<!-- Edit Visitor Modal (Admin only) -->
	<?php if (!empty($edit_visitor) && !$isOwner && !$isSecurity): ?>
		<div class="modal" id="editModal" role="dialog" aria-modal="true">
			<div class="modal-backdrop"></div>
			<div class="modal-content">
				<div class="modal-header">
					<h3><i class="fas fa-edit" style="color:var(--primary)"></i> Edit Visitor</h3>
					<a href="<?= site_url('visitors') ?>" class="modal-close" aria-label="Close">&times;</a>
				</div>

				<form method="POST" action="<?= site_url('visitors/update') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
						value="<?= $this->security->get_csrf_hash() ?>">
					<input type="hidden" name="id" value="<?= (int) $edit_visitor['id'] ?>">

					<?php if (!empty($isSuperAdmin)): ?>
						<div class="modal-body" style="padding-bottom:0;">
							<div class="form-group">
								<label><i class="fas fa-building"></i> Society</label>
								<select name="society_id" class="form-control">
									<option value="">— Select —</option>
									<?php foreach ($societies as $soc): ?>
										<option value="<?= (int) $soc['id'] ?>" <?= ((int) $edit_visitor['society_id'] === (int) $soc['id']) ? 'selected' : '' ?>>
											<?= html_escape($soc['name']) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					<?php else: ?>
						<input type="hidden" name="society_id" value="<?= (int) $this->session->userdata('society_id') ?>">
					<?php endif; ?>

					<div class="modal-body">
						<div class="form-row">
							<div class="form-group">
								<label>Full Name <span style="color:var(--danger)">*</span></label>
								<input type="text" name="visitor_name" class="form-control"
									value="<?= html_escape($edit_visitor['visitor_name'] ?? '') ?>" required
									maxlength="150">
							</div>
							<div class="form-group">
								<label>Phone Number</label>
								<input type="tel" name="phone" class="form-control"
									value="<?= html_escape($edit_visitor['phone'] ?? '') ?>">
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label>Flat / Unit</label>
								<input type="text" name="flat" class="form-control"
									value="<?= html_escape($edit_visitor['flat'] ?? '') ?>">
							</div>
							<div class="form-group">
								<label>Purpose</label>
								<input type="text" name="purpose" class="form-control"
									value="<?= html_escape($edit_visitor['purpose'] ?? '') ?>">
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label>Entry Time <span style="color:var(--danger)">*</span></label>
								<input type="datetime-local" name="entry_time" class="form-control" value="<?= !empty($edit_visitor['entry_time'])
									? date('Y-m-d\TH:i', strtotime($edit_visitor['entry_time']))
									: '' ?>" required>
							</div>
							<div class="form-group">
								<label>
									Exit Time
									<small style="color:var(--text-light);font-weight:400;">
										(auto on checkout; correct only if needed)
									</small>
								</label>
								<input type="datetime-local" name="exit_time" class="form-control" value="<?= !empty($edit_visitor['exit_time'])
									? date('Y-m-d\TH:i', strtotime($edit_visitor['exit_time']))
									: '' ?>">
							</div>
						</div>

						<div class="form-group">
							<label>Status <span style="color:var(--danger)">*</span></label>
							<select name="status" class="form-control" required>
								<option value="Pending" <?= ($edit_visitor['status'] === 'Pending') ? 'selected' : '' ?>>
									Pending
								</option>
								<option value="Checked In" <?= ($edit_visitor['status'] === 'Checked In') ? 'selected' : '' ?>>
									Checked In</option>
								<option value="Checked Out" <?= ($edit_visitor['status'] === 'Checked Out') ? 'selected' : '' ?>>
									Checked Out</option>
							</select>
						</div>
					</div>

					<div class="modal-footer">
						<a href="<?= site_url('visitors') ?>" class="btn btn-outline">Cancel</a>
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save"></i> Save Changes
						</button>
					</div>
				</form>
			</div>
		</div>
	<?php endif; ?>

	<!-- Reject / Revoke Modal -->
	<?php if ($isOwner || $isAdmin || $isSuperAdmin): ?>
		<div class="modal" id="rejectModal" role="dialog" aria-modal="true">
			<div class="modal-backdrop" onclick="closeModal('rejectModal')"></div>
			<div class="modal-content sm">
				<div class="modal-header">
					<h3 id="rejectModalTitle">
						<i class="fas fa-times-circle" style="color:var(--danger)"></i>
						<span id="rejectModalHeading">Reject Visitor</span>
					</h3>
					<button class="modal-close" onclick="closeModal('rejectModal')" aria-label="Close">&times;</button>
				</div>

				<form method="POST" id="rejectForm" action="">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
						value="<?= $this->security->get_csrf_hash() ?>">
					<input type="hidden" name="id" id="rejectVisitorId">

					<div class="modal-body">
						<p id="rejectModalDesc" style="margin:0 0 14px;font-size:.9rem;"></p>
						<div class="form-group">
							<label>
								Reason
								<span style="color:var(--text-light);font-weight:400;">(optional)</span>
							</label>
							<textarea name="rejection_reason" class="form-control" rows="2"
								placeholder="e.g. Not expected, unverified identity…"></textarea>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-outline" onclick="closeModal('rejectModal')">Cancel</button>
						<button type="submit" class="btn btn-primary"
							style="background:var(--danger);border-color:var(--danger);">
							<i class="fas fa-ban"></i>
							<span id="rejectModalBtn">Reject Entry</span>
						</button>
					</div>
				</form>
			</div>
		</div>
	<?php endif; ?>

	<script>
		function openModal(id) {
			document.getElementById(id).classList.add('active');
		}
		function closeModal(id) {
			document.getElementById(id).classList.remove('active');
		}

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				document.querySelectorAll('.modal.active').forEach(function (m) {
					m.classList.remove('active');
				});
			}
		});

		function openRejectModal(id, name, isRevoke) {
			isRevoke = !!isRevoke;

			document.getElementById('rejectVisitorId').value = id;
			document.getElementById('rejectForm').action =
				'<?= site_url("visitors/reject/") ?>' + id;

			document.getElementById('rejectModalHeading').textContent =
				isRevoke ? 'Revoke Approval' : 'Reject Visitor';

			document.getElementById('rejectModalDesc').innerHTML = isRevoke
				? 'Revoke approval for <strong>' + name + '</strong>?<br>'
				+ '<small style="color:var(--danger)">This will mark the visitor as pending / not approved.</small>'
				: 'Reject entry for <strong>' + name + '</strong>?';

			document.getElementById('rejectModalBtn').textContent =
				isRevoke ? 'Revoke Approval' : 'Reject Entry';

			openModal('rejectModal');
		}

		document.addEventListener('DOMContentLoaded', function () {
			var addTrigger = document.querySelector('[onclick="openModal(\'addModal\')"]');
			if (addTrigger) {
				addTrigger.addEventListener('click', function () {
					var el = document.getElementById('addEntryTime');
					if (el && !el.value) {
						var now = new Date();
						var p = function (n) { return String(n).padStart(2, '0'); };
						el.value = now.getFullYear() + '-' + p(now.getMonth() + 1) + '-' +
							p(now.getDate()) + 'T' + p(now.getHours()) + ':' + p(now.getMinutes());
					}
				});
			}

			<?php if (!empty($edit_visitor)): ?>
				openModal('editModal');
			<?php endif; ?>

			var flash = document.getElementById('flashMsg');
			if (flash) {
				setTimeout(function () {
					flash.style.transition = 'opacity .5s';
					flash.style.opacity = '0';
					setTimeout(function () { flash.remove(); }, 500);
				}, 3500);
			}
		});

		function exportCSV() {
			var rows = [];
			var headers = [];

			document.querySelectorAll('#visitorTable thead th').forEach(function (th) {
				headers.push('"' + th.innerText.trim() + '"');
			});
			headers.pop();
			rows.push(headers.join(','));

			document.querySelectorAll('#visitorTable tbody tr').forEach(function (tr) {
				var cols = [];
				var tds = tr.querySelectorAll('td');
				for (var i = 0; i < tds.length - 1; i++) {
					cols.push('"' + tds[i].innerText.trim().replace(/"/g, '""') + '"');
				}
				if (cols.length) rows.push(cols.join(','));
			});

			var blob = new Blob([rows.join('\n')], { type: 'text/csv' });
			var a = document.createElement('a');
			a.href = URL.createObjectURL(blob);
			a.download = 'visitors_export.csv';
			a.click();
			URL.revokeObjectURL(a.href);
		}
	</script>
</body>

</html>
