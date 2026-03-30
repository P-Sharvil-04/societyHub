<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
	<title>SocietyHub · Residents Management</title>
	<link rel="icon" href="<?= base_url('assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png') ?>" type="image/png">

	<!-- Icons & Fonts (same premium set as dashboard) -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
		rel="stylesheet">

	<!-- External CSS -->
	<link rel="stylesheet" href="<?= base_url('assets/css/residents.css') ?>">
</head>

<body>
	<!-- Overlay for mobile -->
	<div class="overlay" id="overlay"></div>

	<!-- ============ SIDEBAR ============ -->
	<?php $activePage = 'residents'; ?>

	<?php include('includes/sidebar.php') ?>

	<!-- ============ HEADER ============ -->
	<div class="header" id="header">
		<div class="header-left">
			<i class="fas fa-bars hamburger" id="hamburger"></i>
			<div class="header-title">Residents</div>
			<div class="search-bar">
				<i class="fas fa-search"></i>
				<input type="text" id="globalSearchInput" placeholder="Search residents...">
			</div>
		</div>
		<?php include 'includes/header.php'; ?>
	</div>

	<!-- ============ MAIN CONTENT ============ -->
	<div class="main" id="main">
		<!-- ✅ COMPACT FILTER SECTION - TIGHT SPACING, NO WASTED SPACE -->
		<div class="filter-section">
			<div class="filter-group">
				<label><i class="fas fa-search"></i> Search</label>
				<input type="text" id="searchInput" placeholder="Name, flat, phone...">
			</div>
			<div class="filter-group">
				<label><i class="fas fa-building"></i> Wing</label>
				<select id="wingSelect">
					<option value="All">All Wings</option>
					<option value="A">Wing A</option>
					<option value="B">Wing B</option>
					<option value="C">Wing C</option>
				</select>
			</div>
			<div class="filter-group">
				<label><i class="fas fa-circle"></i> Status</label>
				<select id="statusSelect">
					<option value="All">All Status</option>
					<option value="Active">Active</option>
					<option value="Inactive">Inactive</option>
				</select>
			</div>
			<div class="filter-actions">
				<button class="" id="applyFilterBtn"></button>
				<button class="btn" id="addResidentBtn"><i class="fas fa-plus-circle"></i> Add</button>
			</div>
		</div>

		<!-- Quick Add Resident Form -->
		<div id="addResidentForm" class="add-resident-form">
			<h4><i class="fas fa-user-plus"></i> Add New Resident</h4>
			<div class="form-row">
				<div class="form-group">
					<label>Full Name *</label>
					<input type="text" id="newName" placeholder="Enter name">
				</div>
				<div class="form-group">
					<label>Flat No. *</label>
					<input type="text" id="newFlat" placeholder="e.g. 101">
				</div>
				<div class="form-group">
					<label>Wing</label>
					<select id="newWing">
						<option>A</option>
						<option>B</option>
						<option>C</option>
					</select>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label>Phone *</label>
					<input type="text" id="newPhone" placeholder="9876543210">
				</div>
				<div class="form-group">
					<label>Email</label>
					<input type="email" id="newEmail" placeholder="resident@email.com">
				</div>
				<div class="form-group">
					<label>Move-in Date</label>
					<input type="date" id="newMoveDate" value="2025-01-01">
				</div>
			</div>
			<div style="display: flex; gap: 12px; justify-content: flex-end;">
				<button class="btn btn-outline" id="cancelAddBtn">Cancel</button>
				<button class="btn" id="saveResidentBtn"><i class="fas fa-save"></i> Save</button>
			</div>
		</div>

		<!-- Residents Table -->
		<div class="table-section">
			<div class="section-header">
				<h3><i class="fas fa-list"></i> Residents Directory</h3>
				<span id="residentCount" style="color: var(--text-light); font-size: 0.9rem;">6 residents</span>
			</div>
			<div class="table-wrapper">
				<table id="residentTable">
					<thead>
						<tr>
							<th>Name</th>
							<th>Flat</th>
							<th>Wing</th>
							<th>Phone</th>
							<th>Email</th>
							<th>Move-in</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody id="residentTableBody"></tbody>
				</table>
			</div>
		</div>
	</div>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>

	<script>

		// ================ RESIDENTS FUNCTIONALITY ================
		let residentsData = [
			{
				name: 'Rajesh Kumar', flat: '101', wing: 'A', phone: '9876543210', email: 'rajesh@email.com', moveDate:
					'15-01-2024', status: 'Active'
			},
			{
				name: 'Priya Singh', flat: '102', wing: 'A', phone: '9876543211', email: 'priya@email.com', moveDate:
					'20-02-2024', status: 'Active'
			},
			{
				name: 'Amit Patel', flat: '203', wing: 'B', phone: '9876543212', email: 'amit@email.com', moveDate: '10-03-2024',
				status: 'Active'
			},
			{
				name: 'Neha Verma', flat: '301', wing: 'C', phone: '9876543213', email: 'neha@email.com', moveDate: '25-01-2024',
				status: 'Active'
			},
			{
				name: 'Vikas Sharma', flat: '401', wing: 'A', phone: '9876543214', email: 'vikas@email.com', moveDate:
					'05-04-2024', status: 'Active'
			},
			{
				name: 'Anjali Gupta', flat: '502', wing: 'B', phone: '9876543215', email: 'anjali@email.com', moveDate:
					'12-05-2024', status: 'Active'
			}
		];

		const tbody = document.getElementById('residentTableBody');
		const searchInput = document.getElementById('searchInput');
		const wingSelect = document.getElementById('wingSelect');
		const statusSelect = document.getElementById('statusSelect');
		const applyFilterBtn = document.getElementById('applyFilterBtn');
		const addResidentBtn = document.getElementById('addResidentBtn');
		const addForm = document.getElementById('addResidentForm');
		const cancelAddBtn = document.getElementById('cancelAddBtn');
		const saveResidentBtn = document.getElementById('saveResidentBtn');
		const globalSearchInput = document.getElementById('globalSearchInput');
		const residentCountSpan = document.getElementById('residentCount');

		function renderTable(data) {
			tbody.innerHTML = '';
			if (data.length === 0) {
				tbody.innerHTML = `<tr>
		<td colspan="8" style="text-align:center; padding:30px;">No residents found</td>
	</tr>`;
				residentCountSpan.innerText = '0 residents';
				return;
			}
			data.forEach((res, index) => {
				const row = document.createElement('tr');
				row.innerHTML = `
	<td>${res.name}</td>
	<td>${res.flat}</td>
	<td>${res.wing}</td>
	<td>${res.phone}</td>
	<td>${res.email}</td>
	<td>${res.moveDate}</td>
	<td><span class="status-badge ${res.status === 'Inactive' ? 'inactive' : ''}">${res.status}</span></td>
	<td>
		<button class="action-btn edit-btn" data-index="${index}" style="margin-right:6px;"><i
				class="fas fa-edit"></i></button>
		<button class="action-btn" style="background: var(--danger);" onclick="window.deleteResident(${index})"><i
				class="fas fa-trash"></i></button>
	</td>
	`;
				tbody.appendChild(row);
			});
			residentCountSpan.innerText = `${data.length} residents`;

			document.querySelectorAll('.edit-btn').forEach(btn => {
				btn.addEventListener('click', function (e) {
					const idx = this.getAttribute('data-index');
					editResidentPrompt(parseInt(idx));
				});
			});
		}

		function filterResidents() {
			const searchTerm = (searchInput.value + globalSearchInput.value).toLowerCase();
			const wing = wingSelect.value;
			const status = statusSelect.value;

			return residentsData.filter(res => {
				let match = true;
				if (searchTerm.trim() !== '') {
					match = res.name.toLowerCase().includes(searchTerm) ||
						res.flat.toLowerCase().includes(searchTerm) ||
						res.phone.includes(searchTerm) ||
						res.email.toLowerCase().includes(searchTerm);
				}
				if (wing !== 'All' && match) match = res.wing === wing;
				if (status !== 'All' && match) match = res.status === status;
				return match;
			});
		}

		function updateTable() {
			const filtered = filterResidents();
			renderTable(filtered);
		}


		addResidentBtn.addEventListener('click', () => {
			addForm.classList.toggle('active');
		});
		cancelAddBtn.addEventListener('click', () => {
			addForm.classList.remove('active');
		});

		saveResidentBtn.addEventListener('click', () => {
			const name = document.getElementById('newName').value.trim();
			const flat = document.getElementById('newFlat').value.trim();
			const wing = document.getElementById('newWing').value;
			const phone = document.getElementById('newPhone').value.trim();
			const email = document.getElementById('newEmail').value.trim();
			const moveDate = document.getElementById('newMoveDate').value;

			if (!name || !flat || !phone) {
				alert('Name, Flat and Phone are required');
				return;
			}
			const parts = moveDate.split('-');
			const formatted = `${parts[2]}-${parts[1]}-${parts[0]}`;

			const newResident = {
				name: name,
				flat: flat,
				wing: wing,
				phone: phone,
				email: email || '—',
				moveDate: formatted,
				status: 'Active'
			};
			residentsData.push(newResident);
			updateTable();
			addForm.classList.remove('active');
			document.getElementById('newName').value = '';
			document.getElementById('newFlat').value = '';
			document.getElementById('newPhone').value = '';
			document.getElementById('newEmail').value = '';
		});

		applyFilterBtn.addEventListener('click', updateTable);
		searchInput.addEventListener('keyup', updateTable);
		globalSearchInput.addEventListener('keyup', updateTable);
		wingSelect.addEventListener('change', updateTable);
		statusSelect.addEventListener('change', updateTable);

		renderTable(residentsData);
	</script>
</body>

</html>
