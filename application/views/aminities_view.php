<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover, shrink-to-fit=no">
	<title>SocietyHub · Amenities Management</title>

	<!-- Icons & Fonts -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet" />

	<!-- Your external CSS files -->
	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/aminities.css') ?>">
</head>

<body>
	<div class="overlay" id="overlay"></div>

	<!-- SIDEBAR -->
	<?php $activePage = "aminities"; ?>
	<?php include('sidebar.php'); ?>

	<!-- HEADER -->
	<div class="header" id="header">
		<?php $this->load->view('header'); ?>
	</div>

	<!-- MAIN CONTENT -->
	<div class="main" id="main">
		<!-- Stats Cards (Today's Bookings removed) -->
		<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-swimming-pool"></i></div>
				<div class="stat-info">
					<h4>Total Amenities</h4>
					<h2 id="totalAmenities">0</h2>
					<div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> +2 this month</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-check-circle"></i></div>
				<div class="stat-info">
					<h4>Available</h4>
					<h2 id="availableCount">0</h2>
					<div class="stat-trend" style="color: var(--success);"><i class="fas fa-circle"></i> Ready to use
					</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon"><i class="fas fa-tools"></i></div>
				<div class="stat-info">
					<h4>Maintenance</h4>
					<h2 id="maintenanceCount">0</h2>
					<div class="stat-trend" style="color: var(--warning);"><i class="fas fa-clock"></i> Under repair
					</div>
				</div>
			</div>
		</div>

		<!-- Filter Section (price filter removed) -->
		<div class="filter-section">
			<div class="filter-group">
				<label><i class="fas fa-filter"></i> Status</label>
				<select id="statusFilter" class="filter-select" onchange="filterAmenities()">
					<option value="">All Status</option>
					<option value="available">Available</option>
					<option value="maintenance">Maintenance</option>
					<option value="closed">Closed</option>
				</select>
			</div>
			<div class="filter-group">
				<label><i class="fas fa-tag"></i> Category</label>
				<select id="categoryFilter" class="filter-select" onchange="filterAmenities()">
					<option value="">All Categories</option>
					<option value="sports">Sports</option>
					<option value="entertainment">Entertainment</option>
					<option value="wellness">Wellness</option>
					<option value="function">Function Hall</option>
					<option value="other">Other</option>
				</select>
			</div>
			<div class="search-box">
				<i class="fas fa-search"></i>
				<input type="text" id="amenitySearch" placeholder="Search amenities..." onkeyup="filterAmenities()">
			</div>
			<div class="page-actions">
				<button class="btn btn-outline" onclick="exportAmenities()">
					<i class="fas fa-download"></i> Export
				</button>
				<button class="btn btn-primary" onclick="openAddAmenityModal()">
					<i class="fas fa-plus-circle"></i> Add Amenity
				</button>
			</div>
		</div>

		<!-- View Toggle -->
		<div class="table-header" style="margin-bottom: 15px;">
			<h3><i class="fas fa-swimming-pool"></i> All Amenities</h3>
			<button class="btn-sm btn-outline" onclick="refreshTable()">
				<i class="fas fa-sync-alt"></i> Refresh
			</button>
			<div class="table-actions">

				<div class="view-toggle">
					<button class="view-btn active" onclick="toggleView('grid')" id="gridViewBtn">
						<i class="fas fa-th-large"></i> Grid
					</button>
					<button class="view-btn" onclick="toggleView('table')" id="tableViewBtn">
						<i class="fas fa-table"></i> Table
					</button>
				</div>

			</div>
		</div>

		<!-- Amenities Grid View -->
		<div id="amenitiesGridView" class="amenities-grid"></div>

		<!-- Amenities Table View -->
		<div id="amenitiesTableView" class="table-section" style="display: none;">
			<div class="table-wrapper">
				<table id="amenitiesTable">
					<thead>
						<tr>
							<th>ID</th>
							<th>Amenity</th>
							<th>Category</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="amenitiesTableBody"></tbody>
				</table>
			</div>
			<div class="pagination">
				<span class="page-item"><i class="fas fa-chevron-left"></i></span>
				<span class="page-item active">1</span>
				<span class="page-item">2</span>
				<span class="page-item">3</span>
				<span class="page-item">4</span>
				<span class="page-item"><i class="fas fa-chevron-right"></i></span>
			</div>
		</div>
	</div>

	<!-- Add/Edit Amenity Modal (price and capacity removed) -->
	<div class="modal" id="amenityFormModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3><i class="fas fa-swimming-pool"></i> <span id="formModalTitle">Add Amenity</span></h3>
				<span class="modal-close" onclick="closeModal('amenityFormModal')">&times;</span>
			</div>
			<div class="modal-body">
				<form id="amenityForm">
					<input type="hidden" id="amenityId">

					<div class="form-group">
						<label>Amenity Name *</label>
						<input type="text" class="form-control" id="name" placeholder="e.g., Swimming Pool" required>
					</div>

					<div class="form-group">
						<label>Description *</label>
						<textarea class="form-control" id="description" rows="3" placeholder="Detailed description"
							required></textarea>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label>Category *</label>
							<select class="form-control" id="category" required>
								<option value="">Select Category</option>
								<option value="sports">Sports</option>
								<option value="entertainment">Entertainment</option>
								<option value="wellness">Wellness</option>
								<option value="function">Function Hall</option>
								<option value="other">Other</option>
							</select>
						</div>
						<div class="form-group">
							<label>Icon</label>
							<select class="form-control" id="icon">
								<option value="fa-swimming-pool">Swimming Pool</option>
								<option value="fa-dumbbell">Gym</option>
								<option value="fa-tree">Park</option>
								<option value="fa-table-tennis">Table Tennis</option>
								<option value="fa-basketball-ball">Basketball</option>
								<option value="fa-volleyball-ball">Volleyball</option>
								<option value="fa-book">Library</option>
								<option value="fa-tv">TV Room</option>
								<option value="fa-music">Music Room</option>
								<option value="fa-gamepad">Gaming</option>
								<option value="fa-calendar-alt">Function Hall</option>
								<option value="fa-spa">Spa</option>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label>Location</label>
							<input type="text" class="form-control" id="location" placeholder="e.g., Ground Floor">
						</div>
						<div class="form-group">
							<label>Status *</label>
							<select class="form-control" id="status" required>
								<option value="available">Available</option>
								<option value="maintenance">Under Maintenance</option>
								<option value="closed">Closed</option>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label>Opening Time</label>
							<input type="time" class="form-control" id="openingTime" value="06:00">
						</div>
						<div class="form-group">
							<label>Closing Time</label>
							<input type="time" class="form-control" id="closingTime" value="22:00">
						</div>
					</div>

					<div class="form-group">
						<label>Rules & Regulations</label>
						<textarea class="form-control" id="rules" rows="2"
							placeholder="Enter rules and regulations"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button class="btn btn-outline" onclick="closeModal('amenityFormModal')"><i class="fas fa-times"></i>
					Cancel</button>
				<button class="btn btn-primary" onclick="saveAmenity()"><i class="fas fa-save"></i> Save
					Amenity</button>
			</div>
		</div>
	</div>

	<!-- View Amenity Modal (price and capacity removed) -->
	<div class="modal" id="viewAmenityModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3><i class="fas fa-info-circle"></i> Amenity Details</h3>
				<span class="modal-close" onclick="closeModal('viewAmenityModal')">&times;</span>
			</div>
			<div class="modal-body" id="viewAmenityBody"></div>
			<div class="modal-footer">
				<button class="btn btn-outline" onclick="closeModal('viewAmenityModal')"><i class="fas fa-times"></i>
					Close</button>
				<button class="btn btn-primary" onclick="editFromView()"><i class="fas fa-edit"></i> Edit</button>
			</div>
		</div>
	</div>

	<!-- Delete Confirmation Modal -->
	<div class="modal" id="deleteModal">
		<div class="modal-content" style="max-width: 400px;">
			<div class="modal-header">
				<h3><i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i> Confirm Delete</h3>
				<span class="modal-close" onclick="closeModal('deleteModal')">&times;</span>
			</div>
			<div class="modal-body">
				<p style="text-align: center; padding: 20px;">
					<i class="fas fa-trash" style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;"></i><br>
					Are you sure you want to delete this amenity?<br>
					<span style="color: var(--danger); font-size: 0.9rem; display: block; margin-top: 10px;">This action
						cannot be undone.</span>
				</p>
			</div>
			<div class="modal-footer">
				<button class="btn btn-outline" onclick="closeModal('deleteModal')"><i class="fas fa-times"></i>
					Cancel</button>
				<button class="btn btn-primary" style="background: var(--danger);" onclick="confirmDelete()"><i
						class="fas fa-trash"></i> Delete</button>
			</div>
		</div>
	</div>

	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		// ==================== API BASE URL ====================
		const apiBase = '<?= site_url('amenities') ?>';

		// ==================== GLOBAL DATA ====================
		let amenityData = [];
		let currentAmenityId = null;
		let deleteId = null;
		let currentView = 'grid';

		// ==================== HELPER FUNCTIONS ====================
		function getCurrentDate() { const d = new Date(); return d.toISOString().split('T')[0]; }

		// ==================== FETCH AMENITIES FROM API ====================
		function fetchAmenities() {
			fetch(apiBase + '/list')
				.then(r => r.json())
				.then(res => {
					console.log('API Response:', res); //  Check the actual field names
					if (res.success) {
						amenityData = res.data.map(a => ({
							...a,
							// Provide defaults for missing fields
							icon: a.icon || 'fa-swimming-pool',
							location: a.location || '',
							openingTime: a.openingTime || '06:00',
							closingTime: a.closingTime || '22:00',
							rules: a.rules || '',
							totalBookings: a.totalBookings || 0,
							rating: a.rating || 0,
							// Category: try common field names
							category: a.category || a.cat || a.amenity_category || 'other'
						}));
						loadAmenitiesGrid();
						loadAmenitiesTable();
						updateStats();
					} else {
						showNotification('Failed to load amenities', 'error');
					}
				})
				.catch(err => {
					console.error(err);
					showNotification('Error loading amenities', 'error');
				});
		}

		// ==================== VIEW TOGGLE ====================
		function toggleView(view) {
			currentView = view;
			const gridView = document.getElementById('amenitiesGridView');
			const tableView = document.getElementById('amenitiesTableView');
			const gridBtn = document.getElementById('gridViewBtn');
			const tableBtn = document.getElementById('tableViewBtn');

			if (view === 'grid') {
				gridView.style.display = 'grid';
				tableView.style.display = 'none';
				gridBtn.classList.add('active');
				tableBtn.classList.remove('active');
			} else {
				gridView.style.display = 'none';
				tableView.style.display = 'block';
				tableBtn.classList.add('active');
				gridBtn.classList.remove('active');
			}
		}

		// ==================== GRID VIEW (with category) ====================
		function loadAmenitiesGrid(filteredData = null) {
			const grid = document.getElementById('amenitiesGridView');
			const data = filteredData || amenityData;

			if (data.length === 0) {
				grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-light);">No amenities found</div>';
				return;
			}

			let html = '';
			data.forEach(a => {
				const categoryDisplay = a.category ? a.category.charAt(0).toUpperCase() + a.category.slice(1) : 'Other';
				html += `
				<div class="amenity-card">
					<div class="amenity-header">
						<div class="amenity-icon">
							<i class="fas ${a.icon}"></i>
						</div>
						<span class="amenity-status ${a.status}">${a.status.charAt(0).toUpperCase() + a.status.slice(1)}</span>
					</div>
					<div class="amenity-name">${escapeHtml(a.name)}</div>
					<div class="amenity-description">${escapeHtml(a.description.length > 80 ? a.description.substring(0, 80) + '...' : a.description)}</div>
					<div class="amenity-details">
						<div class="detail-item">
							<i class="fas fa-clock"></i>
							<span>${a.openingTime} - ${a.closingTime}</span>
						</div>
						<div class="detail-item">
							<i class="fas fa-map-marker-alt"></i>
							<span>${escapeHtml(a.location || 'N/A')}</span>
						</div>
						<div class="detail-item">
							<i class="fas fa-tag"></i>
							<span>${categoryDisplay}</span>
						</div>
					</div>
					<div class="amenity-footer">
						<div class="amenity-actions">
							<button class="btn-icon" onclick="viewAmenity(${a.id})" title="View"><i class="fas fa-eye"></i></button>
							<button class="btn-icon" onclick="editAmenity(${a.id})" title="Edit"><i class="fas fa-edit"></i></button>
							<button class="btn-icon delete" onclick="deleteAmenity(${a.id})" title="Delete"><i class="fas fa-trash"></i></button>
						</div>
					</div>
				</div>
			`;
			});

			grid.innerHTML = html;
		}

		// ==================== TABLE VIEW (with category) ====================
		function loadAmenitiesTable(filteredData = null) {
			const tbody = document.getElementById('amenitiesTableBody');
			const data = filteredData || amenityData;

			if (data.length === 0) {
				tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;">No amenities found</td></tr>';
				return;
			}

			let html = '';
			data.forEach(a => {
				const categoryDisplay = a.category ? a.category.charAt(0).toUpperCase() + a.category.slice(1) : 'Other';
				html += `<tr>
				<td><strong>${escapeHtml(a.amenityId || 'N/A')}</strong></td>
				<td>
					<div style="display:flex; align-items:center; gap:10px;">
						<div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg, var(--primary), var(--primary-dark)); display:flex; align-items:center; justify-content:center; color:white;">
							<i class="fas ${a.icon}"></i>
						</div>
						<div>
							<strong>${escapeHtml(a.name)}</strong><br>
							<span style="font-size:0.7rem; color:var(--text-light);">${categoryDisplay}</span>
						</div>
					</div>
				</td>
				<td><span class="badge" style="background:rgba(52,152,219,0.1); color:var(--primary);">${categoryDisplay}</span></td>
				<td><span class="amenity-status ${a.status}">${a.status.charAt(0).toUpperCase() + a.status.slice(1)}</span></td>
				<td>
					<div class="action-buttons">
						<button class="btn-icon" onclick="viewAmenity(${a.id})" title="View"><i class="fas fa-eye"></i></button>
						<button class="btn-icon" onclick="editAmenity(${a.id})" title="Edit"><i class="fas fa-edit"></i></button>
						<button class="btn-icon delete" onclick="deleteAmenity(${a.id})" title="Delete"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>`;
			});

			tbody.innerHTML = html;
		}

		// ==================== FILTER ====================
		function filterAmenities() {
			const search = document.getElementById('amenitySearch')?.value.toLowerCase() || '';
			const statusFilter = document.getElementById('statusFilter')?.value || '';
			const categoryFilter = document.getElementById('categoryFilter')?.value || '';

			const filtered = amenityData.filter(a => {
				const matchesSearch = search === '' ||
					a.name.toLowerCase().includes(search) ||
					a.description.toLowerCase().includes(search) ||
					(a.location || '').toLowerCase().includes(search) ||
					(a.amenityId || '').toLowerCase().includes(search);

				const matchesStatus = statusFilter === '' || a.status === statusFilter;
				const matchesCategory = categoryFilter === '' || a.category === categoryFilter;

				return matchesSearch && matchesStatus && matchesCategory;
			});

			loadAmenitiesGrid(filtered);
			loadAmenitiesTable(filtered);
			updateStats(filtered);
		}

		// ==================== UPDATE STATS ====================
		function updateStats(filteredData = null) {
			const d = filteredData || amenityData;
			document.getElementById('totalAmenities').textContent = d.length;
			document.getElementById('availableCount').textContent = d.filter(a => a.status === 'available').length;
			document.getElementById('maintenanceCount').textContent = d.filter(a => a.status === 'maintenance').length;
		}

		// ==================== CRUD OPERATIONS ====================
		function openAddAmenityModal() {
			document.getElementById('formModalTitle').textContent = 'Add Amenity';
			document.getElementById('amenityForm').reset();
			document.getElementById('amenityId').value = '';
			document.getElementById('openingTime').value = '06:00';
			document.getElementById('closingTime').value = '22:00';
			currentAmenityId = null;
			openModal('amenityFormModal');
		}

		function editAmenity(id) {
			const a = amenityData.find(a => a.id === id);
			if (!a) return;

			currentAmenityId = id;
			document.getElementById('formModalTitle').textContent = 'Edit Amenity';
			document.getElementById('amenityId').value = a.id;
			document.getElementById('name').value = a.name;
			document.getElementById('description').value = a.description;
			document.getElementById('category').value = a.category; // uses normalized field
			document.getElementById('icon').value = a.icon;
			document.getElementById('location').value = a.location || '';
			document.getElementById('status').value = a.status;
			document.getElementById('openingTime').value = a.openingTime || '06:00';
			document.getElementById('closingTime').value = a.closingTime || '22:00';
			document.getElementById('rules').value = a.rules || '';

			openModal('amenityFormModal');
		}

		function viewAmenity(id) {
			const a = amenityData.find(a => a.id === id);
			if (!a) return;

			currentAmenityId = id;
			const body = document.getElementById('viewAmenityBody');

			let statusColor = '';
			if (a.status === 'available') statusColor = 'var(--success)';
			else if (a.status === 'maintenance') statusColor = 'var(--warning)';
			else statusColor = 'var(--danger)';

			const categoryDisplay = a.category ? a.category.charAt(0).toUpperCase() + a.category.slice(1) : 'Other';

			body.innerHTML = `
			<div style="display:flex; align-items:center; gap:20px; margin-bottom:30px;">
				<div style="width:80px; height:80px; border-radius:20px; background:linear-gradient(135deg, var(--primary), var(--primary-dark)); display:flex; align-items:center; justify-content:center; color:white; font-size:2.2rem;">
					<i class="fas ${a.icon}"></i>
				</div>
				<div>
					<h2 style="color:var(--text-dark); margin-bottom:5px;">${escapeHtml(a.name)}</h2>
					<span style="background:var(--primary); color:white; padding:6px 14px; border-radius:30px; font-size:0.8rem;">${escapeHtml(a.amenityId || 'N/A')}</span>
					<span style="margin-left:10px; padding:6px 14px; border-radius:30px; font-size:0.8rem; background:${statusColor}20; color:${statusColor};">${a.status.charAt(0).toUpperCase() + a.status.slice(1)}</span>
				</div>
			</div>
			
			<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
				<div style="background:var(--light-bg); padding:20px; border-radius:12px;">
					<h4 style="margin-bottom:15px;"><i class="fas fa-info-circle" style="color:var(--primary);"></i> Basic Information</h4>
					<p><strong>Category:</strong> ${categoryDisplay}</p>
					<p><strong>Location:</strong> ${escapeHtml(a.location || 'Not specified')}</p>
				</div>
				<div style="background:var(--light-bg); padding:20px; border-radius:12px;">
					<h4 style="margin-bottom:15px;"><i class="fas fa-clock" style="color:var(--primary);"></i> Schedule</h4>
					<p><strong>Opening Time:</strong> ${a.openingTime || '06:00'}</p>
					<p><strong>Closing Time:</strong> ${a.closingTime || '22:00'}</p>
				</div>
			</div>
			
			<div style="background:var(--light-bg); padding:20px; border-radius:12px; margin-bottom:20px;">
				<h4 style="margin-bottom:15px;"><i class="fas fa-align-left" style="color:var(--primary);"></i> Description</h4>
				<p style="line-height:1.6;">${escapeHtml(a.description)}</p>
			</div>
			
			<div style="background:var(--light-bg); padding:20px; border-radius:12px;">
				<h4 style="margin-bottom:15px;"><i class="fas fa-gavel" style="color:var(--primary);"></i> Rules & Regulations</h4>
				<p style="line-height:1.6;">${escapeHtml(a.rules || 'No specific rules provided.')}</p>
			</div>
		`;

			openModal('viewAmenityModal');
		}

		function editFromView() {
			closeModal('viewAmenityModal');
			if (currentAmenityId) editAmenity(currentAmenityId);
		}

		function saveAmenity() {
			const name = document.getElementById('name').value;
			const description = document.getElementById('description').value;
			const category = document.getElementById('category').value;
			const icon = document.getElementById('icon').value;
			const status = document.getElementById('status').value;

			if (!name || !description || !category || !icon || !status) {
				showNotification('Please fill all required fields', 'error');
				return;
			}

			const data = {
				name,
				description,
				category,
				icon,
				location: document.getElementById('location').value,
				status,
				openingTime: document.getElementById('openingTime').value,
				closingTime: document.getElementById('closingTime').value,
				rules: document.getElementById('rules').value
			};

			const id = document.getElementById('amenityId').value;

			const formData = new FormData();
			for (let key in data) formData.append(key, data[key]);
			if (id) formData.append('id', id);

			const url = apiBase + '/save';

			fetch(url, { method: 'POST', body: formData })
				.then(r => r.json())
				.then(res => {
					if (res.success) {
						showNotification(res.message || 'Saved successfully', 'success');
						closeModal('amenityFormModal');
						fetchAmenities();
					} else {
						showNotification('Error saving amenity', 'error');
					}
				})
				.catch(err => {
					console.error(err);
					showNotification('Network error', 'error');
				});
		}

		function deleteAmenity(id) {
			deleteId = id;
			openModal('deleteModal');
		}

		function confirmDelete() {
			if (!deleteId) return;
			const formData = new FormData();
			formData.append('id', deleteId);

			fetch(apiBase + '/delete', { method: 'POST', body: formData })
				.then(r => r.json())
				.then(res => {
					if (res.success) {
						showNotification('Amenity deleted', 'success');
						fetchAmenities();
					} else {
						showNotification('Delete failed', 'error');
					}
				})
				.catch(err => {
					console.error(err);
					showNotification('Network error', 'error');
				})
				.finally(() => {
					closeModal('deleteModal');
					deleteId = null;
				});
		}

		// ==================== UTILITIES ====================
		function escapeHtml(s) {
			if (!s) return '';
			return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
		}

		function exportAmenities() {
			let csv = 'Amenity ID,Name,Category,Status,Location\n';
			amenityData.forEach(a => {
				csv += `${a.amenityId || ''},${a.name},${a.category || 'other'},${a.status},${a.location || ''}\n`;
			});
			const blob = new Blob([csv], { type: 'text/csv' });
			const url = window.URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `amenities_${new Date().toISOString().split('T')[0]}.csv`;
			a.click();
			window.URL.revokeObjectURL(url);
			showNotification('Exported successfully!', 'success');
		}

		function refreshTable() {
			fetchAmenities();
			showNotification('Refreshed!', 'success');
		}

		function showNotification(msg, type = 'success') {
			const n = document.createElement('div');
			n.className = `notification ${type}`;
			n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i><span>${msg}</span>`;
			document.body.appendChild(n);
			setTimeout(() => {
				n.style.animation = 'slideOut 0.3s ease';
				setTimeout(() => n.remove(), 300);
			}, 3000);
		}

		function openModal(id) { document.getElementById(id).classList.add('active'); }
		function closeModal(id) { document.getElementById(id).classList.remove('active'); }

		// ==================== INIT ====================
		document.addEventListener('DOMContentLoaded', function () {
			fetchAmenities();
		});

		window.addEventListener('click', function (e) {
			if (e.target.classList.contains('modal')) e.target.classList.remove('active');
		});
		window.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
		});
	</script>
</body>

</html>
