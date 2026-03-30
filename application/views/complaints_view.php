<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>SocietyHub · Complaints</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/complaints.css') ?>">
	<style>
		.modal.active {
			display: block;
		}
	</style>
</head>

<body>

	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'complaints';
	$this->load->view('sidebar'); ?>

	<div class="main" id="main">

		<?php if ($this->session->flashdata('success')): ?>
			<div class="notification success">
				<i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
			</div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="notification error">
				<i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
			</div>
		<?php endif; ?>

		<!-- ── Stats ── -->
		<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
				<div class="stat-info">
					<h4>Total</h4>
					<h2><?= (int) ($stats['total'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-clock"></i></div>
				<div class="stat-info">
					<h4>Pending</h4>
					<h2><?= (int) ($stats['pending'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-spinner"></i></div>
				<div class="stat-info">
					<h4>In Progress</h4>
					<h2><?= (int) ($stats['in_progress'] ?? 0) ?></h2>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-check-circle"></i></div>
				<div class="stat-info">
					<h4>Resolved</h4>
					<h2><?= (int) ($stats['resolved'] ?? 0) ?></h2>
				</div>
			</div>
		</div>

		<!-- ── Filter bar — GET form, all filtering done in controller ── -->
		<form method="GET" action="<?= site_url('complaints') ?>">

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

				<!-- Status filter -->
				<div class="filter-group">
					<label><i class="fas fa-filter"></i> Status</label>
					<select name="status" class="filter-select" onchange="this.form.submit()">
						<option value="">All Status</option>
						<?php foreach (['pending' => 'Pending', 'in-progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $val => $label): ?>
							<option value="<?= $val ?>" <?= ($filters['status'] === $val) ? 'selected' : '' ?>>
								<?= $label ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Category filter -->
				<div class="filter-group">
					<label><i class="fas fa-tag"></i> Category</label>
					<select name="category" class="filter-select" onchange="this.form.submit()">
						<option value="">All Categories</option>
						<?php foreach (['Plumbing', 'Electrical', 'Maintenance', 'Cleanliness', 'Noise', 'Security', 'Parking', 'Other'] as $cat): ?>
							<option value="<?= $cat ?>" <?= ($filters['category'] === $cat) ? 'selected' : '' ?>>
								<?= $cat ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Search -->
				<div class="search-box">
					<i class="fas fa-search"></i>
					<input type="text" name="search" placeholder="Search ID, member, title..."
						value="<?= html_escape($filters['search'] ?? '') ?>">
				</div>

				<!-- Submit search + clear button -->
				<div style="display:flex;gap:8px;align-items:flex-end;">
					<button type="submit" class="btn btn-outline">
						<i class="fas fa-search"></i> Search
					</button>
					<?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['category']) || !empty($filters['society_id'])): ?>
						<a href="<?= site_url('complaints') ?>" class="btn btn-outline">
							<i class="fas fa-times"></i> Clear
						</a>
					<?php endif; ?>
				</div>

			</div>
		</form>

		<!-- ── Table ── -->
		<div class="table-section">
			<div class="table-header">
				<h3>
					<i class="fas fa-list"></i> Complaints List
					<small style="font-weight:400;color:var(--text-light);font-size:.82rem;">
						(<?= count($complaints) ?> records)
					</small>
				</h3>
				<div class="page-actions">
					<button class="btn btn-primary" onclick="openAddModal()">
						<i class="fas fa-plus-circle"></i> Register Complaint
					</button>
				</div>
			</div>

			<div class="table-wrapper">
				<table>
					<thead>
						<tr>
							<th>Comp. ID</th>
							<th>Member</th>
							<th>Flat</th>
							<th>Title</th>
							<th>Category</th>
							<?php if (!empty($isSuperAdmin)): ?>
								<th>Society</th><?php endif; ?>
							<th>Date</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($complaints)): ?>
							<?php foreach ($complaints as $c): ?>
								<tr data-row-id="<?= (int) $c['id'] ?>">
									<td><?= html_escape($c['complaint_id']) ?></td>
									<td><strong><?= html_escape($c['user_name']) ?></strong></td>
									<td><?= html_escape($c['flat'] ?? '—') ?></td>
									<td><?= html_escape($c['title']) ?></td>
									<td><?= html_escape($c['category']) ?></td>
									<?php if (!empty($isSuperAdmin)): ?>
										<td><?= html_escape($c['society_name'] ?? '—') ?></td>
									<?php endif; ?>
									<td>
										<?= !empty($c['created_at']) ? date('d/m/Y', strtotime($c['created_at'])) : '—' ?>
									</td>
									<td>
										<span class="status-badge <?= html_escape($c['status']) ?>">
											<?= ucwords(str_replace('-', ' ', html_escape($c['status']))) ?>
										</span>
									</td>
									<td>
										<div class="action-buttons">
											<button class="btn-icon" title="View"
												onclick='viewComplaint(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)'>
												<i class="fas fa-eye"></i>
											</button>
											<button class="btn-icon" title="Edit"
												onclick='editComplaint(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)'>
												<i class="fas fa-edit"></i>
											</button>
											<button class="btn-icon delete" title="Delete"
												onclick="deleteComplaint(<?= (int) $c['id'] ?>)">
												<i class="fas fa-trash"></i>
											</button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="<?= !empty($isSuperAdmin) ? 9 : 8 ?>"
									style="text-align:center;padding:32px;color:var(--text-light);">
									<i class="fas fa-inbox"
										style="font-size:2rem;opacity:.35;display:block;margin-bottom:8px"></i>
									No complaints found
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

	</div><!-- /.main -->

	<!-- ══════════════════════════════════════════
	 ADD / EDIT MODAL
════════════════════════════════════════════ -->
	<div class="modal" id="complaintFormModal">
		<div class="modal-content" style="max-width:640px;">
			<div class="modal-header">
				<h3>
					<i class="fas fa-exclamation-circle" style="color:var(--primary)"></i>
					<span id="formModalTitle">Register Complaint</span>
				</h3>
				<span class="modal-close" onclick="closeModal('complaintFormModal')">&times;</span>
			</div>

			<form id="complaintForm" method="POST" action="<?= base_url('complaints/add') ?>">
				<input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
					value="<?= $this->security->get_csrf_hash() ?>">
				<input type="hidden" name="complaintId" id="formComplaintId">

				<div class="modal-body" style="padding:16px 20px;">

					<!-- Member section -->
					<?php if (!empty($isOwner) && !empty($logged_user)): ?>
						<!-- Owner: locked to their own profile -->
						<input type="hidden" name="member_id" value="<?= (int) $logged_user['id'] ?>">
						<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;">
							<div class="form-group">
								<label><i class="fas fa-user"></i> Member</label>
								<input type="text" class="form-control"
									value="<?= html_escape($logged_user['name'] ?? '') ?>" readonly
									style="background:var(--bg,#f5f5f5);cursor:not-allowed;">
							</div>
							<div class="form-group">
								<label><i class="fas fa-door-open"></i> Flat</label>
								<input type="text" class="form-control"
									value="<?= html_escape($logged_user['flat_no'] ?? '') ?>" readonly
									style="background:var(--bg,#f5f5f5);cursor:not-allowed;">
							</div>
						</div>

					<?php else: ?>
						<!-- Admin / super admin: dropdown to pick member -->
						<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;">
							<div class="form-group">
								<label><i class="fas fa-user"></i> Member *</label>
								<select name="member_id" id="memberSelect" class="form-control" required
									onchange="document.getElementById('flatDisplay').value = this.options[this.selectedIndex].dataset.flat || ''">
									<option value="">— Select Member —</option>
									<?php foreach ($members as $m): ?>
										<option value="<?= (int) $m['id'] ?>"
											data-flat="<?= html_escape($m['flat_no'] ?? '') ?>">
											<?= html_escape($m['name'] ?? '') ?>
											(<?= html_escape($m['flat_no'] ?? '') ?>)
											<?php if (!empty($isSuperAdmin) && !empty($m['society_name'])): ?>
												— <?= html_escape($m['society_name']) ?>
											<?php endif; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="form-group">
								<label><i class="fas fa-door-open"></i> Flat</label>
								<input type="text" class="form-control" id="flatDisplay" readonly placeholder="Auto-filled"
									style="background:var(--bg,#f5f5f5);cursor:not-allowed;">
							</div>
						</div>
					<?php endif; ?>

					<!-- Title -->
					<div class="form-group" style="margin-bottom:14px;">
						<label><i class="fas fa-heading"></i> Title *</label>
						<input type="text" name="title" id="formTitle" class="form-control" required>
					</div>

					<!-- Description -->
					<div class="form-group" style="margin-bottom:14px;">
						<label><i class="fas fa-align-left"></i> Description *</label>
						<textarea name="description" id="formDesc" rows="4" class="form-control" required></textarea>
					</div>

					<!-- Category + Status -->
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
						<div class="form-group">
							<label><i class="fas fa-tag"></i> Category *</label>
							<select name="category" id="formCategory" class="form-control" required>
								<option value="">Select Category</option>
								<?php foreach (['Plumbing', 'Electrical', 'Maintenance', 'Cleanliness', 'Noise', 'Security', 'Parking', 'Other'] as $cat): ?>
									<option value="<?= $cat ?>"><?= $cat ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label><i class="fas fa-info-circle"></i> Status</label>
							<select name="status" id="formStatus" class="form-control"
								onchange="document.getElementById('resolutionGroup').style.display = (this.value==='resolved'||this.value==='closed') ? 'block':'none'">
								<option value="pending">Pending</option>
								<option value="in-progress">In Progress</option>
								<option value="resolved">Resolved</option>
								<option value="closed">Closed</option>
							</select>
						</div>
					</div>

					<!-- Resolution notes (shown for resolved/closed) -->
					<div class="form-group" id="resolutionGroup" style="display:none;">
						<label><i class="fas fa-clipboard-check"></i> Resolution Notes</label>
						<textarea name="resolution" id="formResolution" class="form-control" rows="2"></textarea>
					</div>

				</div>

				<div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:14px 20px;">
					<button type="button" class="btn btn-outline" onclick="closeModal('complaintFormModal')">
						<i class="fas fa-times"></i> Cancel
					</button>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-save"></i> Save Complaint
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- ══ VIEW MODAL ══ -->
	<div class="modal" id="viewModal">
		<div class="modal-content" style="max-width:540px;">
			<div class="modal-header">
				<h3><i class="fas fa-file-alt"></i> Complaint Details</h3>
				<span class="modal-close" onclick="closeModal('viewModal')">&times;</span>
			</div>
			<div class="modal-body" style="padding:20px;">
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 24px;">
					<p><strong>Complaint ID:</strong><br><span id="v_cid"></span></p>
					<p><strong>Status:</strong><br><span id="v_status"></span></p>
					<p><strong>Member:</strong><br><span id="v_member"></span></p>
					<p><strong>Flat:</strong><br><span id="v_flat"></span></p>
					<?php if (!empty($isSuperAdmin)): ?>
						<p style="grid-column:1/-1"><strong>Society:</strong><br><span id="v_society"></span></p>
					<?php endif; ?>
					<p style="grid-column:1/-1"><strong>Title:</strong><br><span id="v_title"></span></p>
					<p style="grid-column:1/-1"><strong>Description:</strong><br><span id="v_desc"></span></p>
					<p><strong>Category:</strong><br><span id="v_category"></span></p>
					<p><strong>Date:</strong><br><span id="v_date"></span></p>
				</div>
			</div>
			<div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;">
				<button class="btn btn-outline" onclick="closeModal('viewModal')">Close</button>
				<button class="btn btn-primary" onclick="editFromView()">
					<i class="fas fa-edit"></i> Edit
				</button>
			</div>
		</div>
	</div>

	<!-- ══ DELETE CONFIRM MODAL ══ -->
	<div class="modal" id="deleteModal">
		<div class="modal-content" style="max-width:400px;">
			<div class="modal-header">
				<h3 style="color:var(--danger)">
					<i class="fas fa-exclamation-triangle"></i> Confirm Delete
				</h3>
				<span class="modal-close" onclick="closeModal('deleteModal')">&times;</span>
			</div>
			<div class="modal-body" style="text-align:center;padding:24px;">
				<i class="fas fa-trash" style="font-size:3rem;color:var(--danger);display:block;margin-bottom:12px"></i>
				Are you sure you want to delete this complaint?<br>
				<span style="color:var(--danger);font-size:.88rem;display:block;margin-top:8px;">
					This cannot be undone.
				</span>
			</div>
			<div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;">
				<button class="btn btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
				<button class="btn btn-primary" style="background:var(--danger)" onclick="confirmDelete()">
					<i class="fas fa-trash"></i> Delete
				</button>
			</div>
		</div>
	</div>

	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		/* ── Only 4 things handled in JS:
		   1. Modal open/close
		   2. View modal population (from inline JSON — no AJAX)
		   3. Edit modal population (from inline JSON — no AJAX)
		   4. Delete via one fetch call
		   Everything else (filtering, stats, table) is PHP/server-side.
		── */

		const baseUrl = '<?= base_url() ?>';
		let _deleteId = null;
		let _currentRow = null;   // for editFromView

		/* ── Modal ── */
		function openModal(id) {
			document.getElementById(id).classList.add('active');
			document.getElementById('overlay').classList.add('active');
		}
		function closeModal(id) {
			document.getElementById(id).classList.remove('active');
			document.getElementById('overlay').classList.remove('active');
		}
		document.getElementById('overlay').addEventListener('click', () => {
			document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
			document.getElementById('overlay').classList.remove('active');
		});
		document.addEventListener('keydown', e => {
			if (e.key !== 'Escape') return;
			document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
			document.getElementById('overlay').classList.remove('active');
		});

		/* ── Add ── */
		function openAddModal() {
			document.getElementById('complaintForm').reset();
			document.getElementById('formModalTitle').textContent = 'Register Complaint';
			document.getElementById('formComplaintId').value = '';
			document.getElementById('complaintForm').action = baseUrl + 'complaints/add';
			document.getElementById('resolutionGroup').style.display = 'none';
			const flat = document.getElementById('flatDisplay');
			if (flat) flat.value = '';
			openModal('complaintFormModal');
		}

		/* ── View ── */
		function viewComplaint(c) {
			_currentRow = c;
			document.getElementById('v_cid').textContent = c.complaint_id || '—';
			document.getElementById('v_member').textContent = c.user_name || '—';
			document.getElementById('v_flat').textContent = c.flat || '—';
			document.getElementById('v_title').textContent = c.title || '—';
			document.getElementById('v_desc').textContent = c.description || '—';
			document.getElementById('v_category').textContent = c.category || '—';
			document.getElementById('v_status').textContent = c.status || '—';
			document.getElementById('v_date').textContent = c.created_at ? fmtDate(c.created_at) : '—';
			const vs = document.getElementById('v_society');
			if (vs) vs.textContent = c.society_name || '—';
			openModal('viewModal');
		}

		function editFromView() {
			closeModal('viewModal');
			if (_currentRow) editComplaint(_currentRow);
		}

		/* ── Edit ── */
		function editComplaint(c) {
			document.getElementById('formModalTitle').textContent = 'Edit Complaint';
			document.getElementById('formComplaintId').value = c.id;
			document.getElementById('formTitle').value = c.title || '';
			document.getElementById('formDesc').value = c.description || '';
			document.getElementById('formCategory').value = c.category || '';
			document.getElementById('formStatus').value = c.status || 'pending';
			document.getElementById('formResolution').value = c.resolution || '';
			document.getElementById('complaintForm').action = baseUrl + 'complaints/update';

			const showRes = c.status === 'resolved' || c.status === 'closed';
			document.getElementById('resolutionGroup').style.display = showRes ? 'block' : 'none';

			// If admin dropdown exists, match the member
			const sel = document.getElementById('memberSelect');
			if (sel) {
				for (let i = 0; i < sel.options.length; i++) {
					if (sel.options[i].value == c.user_id) {
						sel.selectedIndex = i;
						const flat = document.getElementById('flatDisplay');
						if (flat) flat.value = sel.options[i].dataset.flat || '';
						break;
					}
				}
			}

			openModal('complaintFormModal');
		}

		/* ── Delete ── */
		function deleteComplaint(id) { _deleteId = id; openModal('deleteModal'); }

		function confirmDelete() {
			if (!_deleteId) return;
			closeModal('deleteModal');
			fetch(baseUrl + 'complaints/delete/' + _deleteId, { credentials: 'same-origin' })
				.then(r => r.json())
				.then(j => {
					if (j && j.success) {
						// Remove row from DOM — no reload needed
						document.querySelectorAll('[data-row-id="' + _deleteId + '"]').forEach(tr => tr.remove());
						showToast('Complaint deleted successfully.');
					} else {
						showToast(j?.error || 'Delete failed.', 'error');
					}
					_deleteId = null;
				})
				.catch(() => showToast('Request failed.', 'error'));
		}

		/* ── Toast ── */
		function showToast(msg, type) {
			type = type || 'success';
			const n = document.createElement('div');
			n.className = 'notification ' + type;
			n.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + msg;
			document.body.appendChild(n);
			setTimeout(() => { n.style.animation = 'slideOut .3s ease'; setTimeout(() => n.remove(), 300); }, 3000);
		}

		/* ── Date format ── */
		function fmtDate(dt) {
			if (!dt) return '';
			const p = dt.split(' ')[0].split('-');
			return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : dt;
		}
	</script>
</body>

</html>
