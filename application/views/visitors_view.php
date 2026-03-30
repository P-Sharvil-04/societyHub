<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport"
		content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes,viewport-fit=cover,shrink-to-fit=no">
	<title>SocietyHub · Visitors</title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<style>
		.visitor-avatar {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			flex-shrink: 0;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			font-weight: 600;
			font-size: 1rem;
		}

		.visitor-info {
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.visitor-details h4 {
			font-weight: 700;
			font-size: .95rem;
			color: var(--text-dark);
			margin-bottom: 4px;
		}

		.visitor-details span {
			font-size: .75rem;
			color: var(--text-light);
		}

		.flat-tag {
			background: var(--bg-light, #f5f5f5);
			padding: 4px 8px;
			border-radius: 20px;
			font-size: .75rem;
			font-weight: 500;
			color: var(--text-dark);
		}

		.status-pill {
			padding: 4px 10px;
			border-radius: 30px;
			font-size: .7rem;
			font-weight: 600;
			letter-spacing: .3px;
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
			color: #f39c12;
		}

		.society-badge {
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
	</style>
</head>

<body>
	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'visitors';
	include('sidebar.php'); ?>

	<div class="main" id="main">

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

		<!-- Stats — scoped to active society + filters -->
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
					<h4>Pending</h4>
					<h2><?= (int) ($stats['pending'] ?? 0) ?></h2>
				</div>
			</div>
		</div>

		<!-- ── Filter bar — GET form, all filtering in controller (same as staff) ── -->
		<form method="GET" action="<?= site_url('visitors') ?>" id="filterForm">
			<div class="filter-section">

				<!-- Society filter: super admin only -->
				<?php if (!empty($isSuperAdmin) && !empty($societies)): ?>
					<div class="filter-group">
						<label><i class="fas fa-building"></i> Society</label>
						<select name="society_id" class="filter-select" onchange="this.form.submit()">
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
					<select name="status" class="filter-select" onchange="this.form.submit()">
						<option value="">All Status</option>
						<option value="Checked In" <?= ($filters['status'] === 'Checked In') ? 'selected' : '' ?>>Checked
							In</option>
						<option value="Checked Out" <?= ($filters['status'] === 'Checked Out') ? 'selected' : '' ?>>Checked
							Out</option>
						<option value="Pending" <?= ($filters['status'] === 'Pending') ? 'selected' : '' ?>>Pending
						</option>
					</select>
				</div>

				<!--
				Live search: typing submits the form after a short debounce.
				No JS filtering — the form posts to controller which queries the DB.
			-->
				<div class="search-box">
					<i class="fas fa-search"></i>
					<input type="text" name="search" id="searchInput" placeholder="Search name, flat, purpose..."
						value="<?= html_escape($filters['search'] ?? '') ?>" autocomplete="off">
				</div>

				<div style="display:flex;gap:8px;align-items:flex-end;">
					<button type="submit" class="btn btn-outline">
						<i class="fas fa-search"></i> Search
					</button>
					<?php
					$anyFilter = !empty($filters['search'])
						|| (!empty($filters['status']) && $filters['status'] !== 'all')
						|| !empty($filters['society_id']);
					if ($anyFilter): ?>
						<a href="<?= site_url('visitors') ?>" class="btn btn-outline">
							<i class="fas fa-times"></i> Clear
						</a>
					<?php endif; ?>
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

				<?php if (!empty($filters['status']) && $filters['status'] !== 'all'): ?>
					<span class="active-filter-pill">
						<i class="fas fa-flag"></i> <?= html_escape($filters['status']) ?>
						<a href="<?= site_url('visitors?' . http_build_query(array_merge($filters, ['status' => '']))) ?>">×</a>
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

		<!-- Visitor Table -->
		<div class="table-section">
			<div class="table-header">
				<h3>
					<i class="fas fa-list"></i> Visitor Log
					<small style="font-weight:400;color:var(--text-light);font-size:.8rem;">
						(<?= (int) ($total_visitors ?? 0) ?> records)
					</small>
				</h3>

				<!-- Refresh: clean URL, clears all filters -->
				<a href="<?= site_url('visitors') ?>" class="btn btn-outline" title="Clear filters &amp; refresh"
					style="display:flex;align-items:center;gap:6px;">
					<i class="fas fa-sync-alt"></i> Refresh
				</a>

				<div class="page-actions">
					<button class="btn btn-outline" onclick="exportCSV()">
						<i class="fas fa-download"></i> Export CSV
					</button>
					<button type="button" class="btn btn-primary" onclick="openAddModal()">
						<i class="fas fa-plus-circle"></i> New Visitor
					</button>
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
							<th>Check-in</th>
							<th>Check-out</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($visitors)): ?>
							<?php foreach ($visitors as $v):
								$st = $v->status ?? 'Pending';
								$stKey = strtolower(str_replace(' ', '-', $st));
								?>
								<tr>
									<td>
										<div class="visitor-info">
											<div class="visitor-avatar">
												<?= strtoupper(substr($v->visitor_name ?? 'V', 0, 1)) ?>
											</div>
											<div class="visitor-details">
												<h4><?= html_escape($v->visitor_name ?? '') ?></h4>
												<span><?= html_escape($v->purpose ?? 'Visit') ?></span>
											</div>
										</div>
									</td>
									<td><?= !empty($v->phone) ? html_escape($v->phone) : '—' ?></td>
									<td>
										<?php if (!empty($v->flat)): ?>
											<span class="flat-tag"><?= html_escape($v->flat) ?></span>
										<?php else: ?>—<?php endif; ?>
									</td>
									<td style="font-size:.83rem;color:var(--text-secondary);">
										<?= !empty($v->purpose) ? html_escape($v->purpose) : '—' ?>
									</td>
									<?php if (!empty($isSuperAdmin)): ?>
										<td>
											<?php if (!empty($v->society_name)): ?>
												<span class="society-badge">
													<i class="fas fa-city"></i> <?= html_escape($v->society_name) ?>
												</span>
											<?php else: ?>—<?php endif; ?>
										</td>
									<?php endif; ?>
									<td style="font-size:.82rem;white-space:nowrap;">
										<?= !empty($v->entry_time) ? date('d M, h:i A', strtotime($v->entry_time)) : '—' ?>
									</td>
									<td style="font-size:.82rem;white-space:nowrap;">
										<?= !empty($v->exit_time) ? date('d M, h:i A', strtotime($v->exit_time)) : '—' ?>
									</td>
									<td>
										<span class="status-pill <?= $stKey ?>"><?= html_escape($st) ?></span>
									</td>
									<td>
										<div class="action-buttons">
											<button type="button" class="btn-icon" title="Edit"
												onclick="editVisitor(<?= (int) $v->id ?>)">
												<i class="fas fa-edit"></i>
											</button>
											<button type="button" class="btn-icon delete" title="Delete"
												onclick="openDeleteModal(<?= (int) $v->id ?>, '<?= html_escape(addslashes($v->visitor_name ?? '')) ?>')">
												<i class="fas fa-trash"></i>
											</button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="<?= !empty($isSuperAdmin) ? 9 : 8 ?>"
									style="text-align:center;padding:40px;color:var(--text-light);">
									<i class="fas fa-users"
										style="font-size:3rem;margin-bottom:15px;opacity:.5;display:block;"></i>
									No visitors found<br>
									<button type="button" class="btn btn-primary" style="margin-top:15px;"
										onclick="openAddModal()">
										<i class="fas fa-plus"></i> Add Visitor
									</button>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			<div
				style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
				<span style="color:var(--text-light);font-size:.85rem;">
					Showing <strong><?= count($visitors ?? []) ?></strong>
					of <strong><?= (int) ($total_visitors ?? 0) ?></strong> entries
					<?php if (!empty($filters['search'])): ?>
						&nbsp;·&nbsp; Search: <strong>"<?= html_escape($filters['search']) ?>"</strong>
					<?php endif; ?>
					<?php if (!empty($filters['status']) && $filters['status'] !== 'all'): ?>
						&nbsp;·&nbsp; Status: <strong><?= html_escape($filters['status']) ?></strong>
					<?php endif; ?>
				</span>
				<?php if (!empty($pagination)): ?>
					<div class="pag-links"><?= $pagination ?></div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Recent Visitors -->
		<?php if (!empty($recent)): ?>
			<div class="management-card" style="margin-bottom:30px;">
				<div class="section-header">
					<h3><i class="fas fa-clock"></i> Recent Visitors</h3>
				</div>
				<div class="member-list">
					<?php foreach ($recent as $r): ?>
						<div class="member-item">
							<div class="member-info">
								<div class="member-avatar">
								</div>
								<div class="member-details">
									<h4><?= html_escape($r->visitor_name ?? '') ?></h4>
									<span>
										<?= !empty($r->flat) ? 'Flat ' . html_escape($r->flat) . ' · ' : '' ?>
										<?= html_escape($r->purpose ?? 'Visit') ?>
										<?php if (!empty($isSuperAdmin) && !empty($r->society_name)): ?>
											· <em><?= html_escape($r->society_name) ?></em>
										<?php endif; ?>
									</span>
								</div>
							</div>
							<span class="status-pill <?= strtolower(str_replace(' ', '-', $r->status ?? 'pending')) ?>">
								<?= html_escape($r->status ?? 'Pending') ?>
							</span>
						</div><br>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	</div><!-- /.main -->

	<!-- ══ ADD / EDIT MODAL ══ -->
	<div class="modal" id="visitorFormModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3><i class="fas fa-user-plus"></i> <span id="formModalTitle">Add New Visitor</span></h3>
				<span class="modal-close" onclick="closeModal('visitorFormModal')">&times;</span>
			</div>
			<div class="modal-body">
				<form id="visitorForm" method="POST" action="<?= site_url('visitors/add') ?>">
					<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
						value="<?= $this->security->get_csrf_hash() ?>">
					<input type="hidden" name="id" id="formVisitorId" value="">

					<!-- Society: super admin picks; others use session value -->
					<?php if (!empty($isSuperAdmin)): ?>
						<div class="form-group" style="margin-bottom:14px;">
							<label><i class="fas fa-building"></i> Society *</label>
							<select name="society_id" id="formSocietyId" class="form-control">
								<option value="">— Select Society —</option>
								<?php foreach ($societies as $soc): ?>
									<option value="<?= (int) $soc['id'] ?>" <?= ((int) ($filters['society_id'] ?? 0) === (int) $soc['id']) ? 'selected' : '' ?>>
										<?= html_escape($soc['name']) ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php else: ?>
						<input type="hidden" name="society_id" value="<?= (int) $this->session->userdata('society_id') ?>">
					<?php endif; ?>

					<div class="form-row">
						<div class="form-group">
							<label>Full Name *</label>
							<input type="text" name="visitor_name" id="formName" class="form-control"
								placeholder="Rahul Sharma" required>
						</div>
						<div class="form-group">
							<label>Phone Number</label>
							<input type="tel" name="phone" id="formPhone" class="form-control"
								placeholder="+91 98765 43210">
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label>Flat / Unit</label>
							<input type="text" name="flat" id="formFlat" class="form-control" placeholder="A-101">
						</div>
						<div class="form-group">
							<label>Purpose of Visit</label>
							<input type="text" name="purpose" id="formPurpose" class="form-control"
								placeholder="Delivery, Guest, Maintenance...">
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label>Entry Time *</label>
							<input type="datetime-local" name="entry_time" id="formEntry" class="form-control" required>
						</div>
						<div class="form-group">
							<label>Exit Time</label>
							<input type="datetime-local" name="exit_time" id="formExit" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label>Status *</label>
						<select name="status" id="formStatus" class="form-control" required>
							<option value="Pending">Pending</option>
							<option value="Checked In">Checked In</option>
							<option value="Checked Out">Checked Out</option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline" onclick="closeModal('visitorFormModal')">
					<i class="fas fa-times"></i> Cancel
				</button>
				<button type="submit" form="visitorForm" class="btn btn-primary">
					<i class="fas fa-save"></i> Save Visitor
				</button>
			</div>
		</div>
	</div>

	<!-- ══ DELETE CONFIRM MODAL ══ -->
	<div class="modal" id="deleteModal">
		<div class="modal-content" style="max-width:400px;">
			<div class="modal-header">
				<h3><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Confirm Delete</h3>
				<span class="modal-close" onclick="closeModal('deleteModal')">&times;</span>
			</div>
			<div class="modal-body" style="text-align:center;padding:24px;">
				<i class="fas fa-trash"
					style="font-size:3rem;color:var(--danger);margin-bottom:15px;display:block;"></i>
				Delete <strong id="delVisitorName">this visitor</strong>?<br>
				<span style="color:var(--danger);font-size:.9rem;display:block;margin-top:10px;">This cannot be
					undone.</span>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline" onclick="closeModal('deleteModal')">
					<i class="fas fa-times"></i> Cancel
				</button>
				<a href="#" id="delConfirmLink" class="btn btn-primary" style="background:var(--danger);">
					<i class="fas fa-trash"></i> Delete
				</a>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		/* ── Minimal JS:
		   1. Modal open/close
		   2. Add modal reset
		   3. Edit: one AJAX fetch to get visitor row, populate form
		   4. Delete modal
		   5. Live search: debounced form submit on search input (backend does the work)
		   6. Export CSV
		   No JS filtering, no data-attribute loops.
		── */

		/* Modal helpers */
		function openModal(id) { document.getElementById(id).classList.add('active'); document.getElementById('overlay').classList.add('active'); }
		function closeModal(id) { document.getElementById(id).classList.remove('active'); document.getElementById('overlay').classList.remove('active'); }
		document.getElementById('overlay').addEventListener('click', function () {
			document.querySelectorAll('.modal.active').forEach(function (m) { m.classList.remove('active'); });
			this.classList.remove('active');
		});
		window.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') return;
			document.querySelectorAll('.modal.active').forEach(function (m) { m.classList.remove('active'); });
			document.getElementById('overlay').classList.remove('active');
		});

		/* Flash auto-dismiss */
		document.addEventListener('DOMContentLoaded', function () {
			var f = document.getElementById('flashMsg');
			if (f) setTimeout(function () { f.style.transition = 'opacity .5s'; f.style.opacity = '0'; setTimeout(function () { f.remove(); }, 500); }, 3500);
		});

		/* Live search — debounce 500ms then submit the GET form (backend queries DB) */
		(function () {
			var input = document.getElementById('searchInput');
			if (!input) return;
			var timer;
			input.addEventListener('input', function () {
				clearTimeout(timer);
				timer = setTimeout(function () {
					document.getElementById('filterForm').submit();
				}, 500);
			});
		})();

		/* Add */
		function openAddModal() {
			document.getElementById('formModalTitle').innerText = 'Add New Visitor';
			document.getElementById('visitorForm').action = '<?= site_url("visitors/add") ?>';
			document.getElementById('formVisitorId').value = '';
			document.getElementById('formName').value = '';
			document.getElementById('formPhone').value = '';
			document.getElementById('formFlat').value = '';
			document.getElementById('formPurpose').value = '';
			document.getElementById('formExit').value = '';
			document.getElementById('formStatus').value = 'Pending';
			// Pre-fill entry time to now
			var now = new Date();
			var pad = function (n) { return String(n).padStart(2, '0'); };
			document.getElementById('formEntry').value = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
			var sc = document.getElementById('formSocietyId');
			if (sc) sc.value = '';
			openModal('visitorFormModal');
		}

		/* Edit — one AJAX fetch, populate form */
		function editVisitor(id) {
			$.get('<?= site_url("visitors/edit/") ?>' + id, function (d) {
				if (!d) { showToast('error', 'Failed to load.'); return; }
				document.getElementById('formModalTitle').innerText = 'Edit Visitor';
				document.getElementById('visitorForm').action = '<?= site_url("visitors/update") ?>';
				document.getElementById('formVisitorId').value = d.id || '';
				document.getElementById('formName').value = d.visitor_name || '';
				document.getElementById('formPhone').value = d.phone || '';
				document.getElementById('formFlat').value = d.flat || '';
				document.getElementById('formPurpose').value = d.purpose || '';
				document.getElementById('formEntry').value = fmtDT(d.entry_time);
				document.getElementById('formExit').value = fmtDT(d.exit_time);
				document.getElementById('formStatus').value = d.status || 'Pending';
				var sc = document.getElementById('formSocietyId');
				if (sc) sc.value = d.society_id || '';
				openModal('visitorFormModal');
			}, 'json').fail(function () { showToast('error', 'Failed to load visitor data.'); });
		}

		/* Delete */
		function openDeleteModal(id, name) {
			document.getElementById('delVisitorName').textContent = name || 'this visitor';
			document.getElementById('delConfirmLink').href = '<?= site_url("visitors/delete/") ?>' + id;
			openModal('deleteModal');
		}

		/* Export CSV — exports visible rows from current page */
		function exportCSV() {
			var table = document.getElementById('visitorTable');
			var rows = table.querySelectorAll('tr');
			var csv = [];
			rows.forEach(function (row) {
				var cols = row.querySelectorAll('td, th');
				var rowData = [];
				cols.forEach(function (col, j) {
					if (j === cols.length - 1) { rowData.push('""'); return; } // skip actions
					rowData.push('"' + col.innerText.replace(/\n/g, ' ').trim().replace(/"/g, '""') + '"');
				});
				csv.push(rowData.join(','));
			});
			var blob = new Blob([csv.join('\n')], { type: 'text/csv' });
			var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'visitors_export.csv'; a.click();
			URL.revokeObjectURL(a.href);
		}

		/* Helpers */
		function fmtDT(str) {
			if (!str) return '';
			var d = new Date(str);
			if (isNaN(d.getTime())) return (str + '').replace(' ', 'T').substring(0, 16);
			var p = function (n) { return String(n).padStart(2, '0'); };
			return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
		}
		function showToast(type, msg) {
			var n = document.createElement('div');
			n.className = 'notification ' + (type === 'success' ? 'success' : 'error');
			n.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + msg;
			document.body.appendChild(n);
			setTimeout(function () { n.style.animation = 'slideOut .3s ease'; setTimeout(function () { n.remove(); }, 300); }, 3000);
		}
	</script>
</body>

</html>
