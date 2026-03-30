<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Society · Flat / Unit Management</title>
    <link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/flat_unit.css') ?>">
    <style>
        /* ── inline overrides only ── */
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --light-bg: #f8fafc;
        }
        body { font-family: 'Inter', sans-serif; background: #f4f7fe; }
    </style>
</head>
<body>

<div class="overlay" id="overlay"></div>
<?php $activePage = 'flat_unit'; include('sidebar.php'); ?>

<div class="main" id="main">

    <!-- ══════ Flash ══════ -->
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

    <!-- ══════ Page heading ══════ -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
     
        <div style="display:flex;gap:10px;align-items:center;">
            <!-- View toggle -->
        
           
        </div>
    </div>

    <!-- ══════ Stats ══════ -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <div class="stat-info">
                <h4>Total Units</h4>
                <h2><?= $totalFlats ?></h2>
                <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> +<?= $newThisMonth ?> this month</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
                <h4>Occupied</h4>
                <h2><?= $occupied ?></h2>
                <div class="stat-trend"><?= $occupiedPercent ?>% occupancy</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-door-open"></i></div>
            <div class="stat-info">
                <h4>Vacant</h4>
                <h2><?= $vacant ?></h2>
                <div class="stat-trend"><?= $vacantPercent ?>% of total</div>
            </div>
        </div>
        <!-- <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-ban"></i></div>
            <div class="stat-info">
                <h4>Blocked</h4>
                <h2><?= $blocked ?></h2>
                <div class="stat-trend"><?= $blockedPercent ?>% of total</div>
            </div>
        </div> -->
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-home"></i></div>
            <div class="stat-info">
                <h4>Owner Occupied</h4>
                <h2><?= $ownerOccupied ?></h2>
                <div class="stat-trend"><?= $ownerPercent ?>%</div>
            </div>
        </div>
    </div>

    <!-- ══════ Occupancy Overview Bar ══════ -->
    <div class="occ-overview">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2px;">
            <span style="font-size:.88rem;font-weight:700;color:var(--text-dark);">
                <i class="fas fa-chart-bar" style="color:var(--primary);margin-right:6px;"></i>Occupancy Overview
            </span>
            <span style="font-size:.78rem;color:var(--text-light);"><?= $totalFlats ?> total units</span>
        </div>
        <div class="occ-bar-track">
            <div class="occ-fill-occ"   id="barOcc"   style="width:0%"></div>
            <div class="occ-fill-vac"   id="barVac"   style="width:0%"></div>
            <div class="occ-fill-block" id="barBlock" style="width:0%"></div>
        </div>
        <div class="occ-legend">
            <div class="occ-leg"><div class="occ-dot" style="background:#059669"></div> Occupied (<?= $occupiedPercent ?>%)</div>
            <div class="occ-leg"><div class="occ-dot" style="background:#d97706"></div> Vacant (<?= $vacantPercent ?>%)</div>
            <div class="occ-leg"><div class="occ-dot" style="background:#dc2626"></div> Blocked (<?= $blockedPercent ?>%)</div>
        </div>
    </div>

    <!-- ══════ Filter Bar ══════ -->
    <form id="filterForm">
        <div class="filter-section">

            <?php if ($isSuperAdmin && !empty($societies)): ?>
            <div class="filter-group">
                <label><i class="fas fa-city"></i> Society</label>
                <select name="society_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Societies</option>
                    <?php foreach ($societies as $soc): ?>
                        <option value="<?= (int)$soc->id ?>"
                            <?= ((int)($filters['society_id'] ?? 0) === (int)$soc->id) ? 'selected' : '' ?>>
                            <?= html_escape($soc->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="filter-group">
                <label><i class="fas fa-layer-group"></i> Wing</label>
                <select name="wing_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Wings</option>
                    <?php foreach ($wings as $w): ?>
                        <option value="<?= (int)$w->id ?>"
                            <?= ((int)($filters['wing_id'] ?? 0) === (int)$w->id) ? 'selected' : '' ?>>
                            <?= html_escape($w->wing_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label><i class="fas fa-stairs"></i> Floor</label>
                <select name="floor" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Floors</option>
                    <?php foreach ($floorList as $fl): ?>
                        <option value="<?= (int)$fl ?>"
                            <?= ((string)($filters['floor'] ?? '') === (string)$fl) ? 'selected' : '' ?>>
                            <?= $fl == 0 ? 'Ground Floor' : ordinal($fl) . ' Floor' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label><i class="fas fa-home"></i> Flat Type</label>
                <select name="flat_type" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="1BHK"      <?= ($filters['flat_type'] === '1BHK')      ? 'selected':'' ?>>1 BHK</option>
                    <option value="2BHK"      <?= ($filters['flat_type'] === '2BHK')      ? 'selected':'' ?>>2 BHK</option>
                    <option value="3BHK"      <?= ($filters['flat_type'] === '3BHK')      ? 'selected':'' ?>>3 BHK</option>
                    <option value="4BHK"      <?= ($filters['flat_type'] === '4BHK')      ? 'selected':'' ?>>4 BHK</option>
                    <option value="Penthouse" <?= ($filters['flat_type'] === 'Penthouse') ? 'selected':'' ?>>Penthouse</option>
                    <option value="Shop"      <?= ($filters['flat_type'] === 'Shop')      ? 'selected':'' ?>>Shop</option>
                    <option value="Office"    <?= ($filters['flat_type'] === 'Office')    ? 'selected':'' ?>>Office</option>
                </select>
            </div>

            <div class="filter-group">
                <label><i class="fas fa-circle"></i> Status</label>
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="occupied" <?= ($filters['status'] === 'occupied') ? 'selected':'' ?>>Occupied</option>
                    <option value="vacant"   <?= ($filters['status'] === 'vacant')   ? 'selected':'' ?>>Vacant</option>
                    <option value="blocked"  <?= ($filters['status'] === 'blocked')  ? 'selected':'' ?>>Blocked</option>
                </select>
            </div>

            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search flat no, resident, wing…"
                    value="<?= html_escape($filters['search'] ?? '') ?>">
            </div>

            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i> Search</button>
                <?php
                $anyFilter = !empty($filters['search']) || !empty($filters['wing_id'])
                          || !empty($filters['flat_type']) || !empty($filters['status'])
                          || !empty($filters['floor']) || !empty($filters['society_id']);
                if ($anyFilter): ?>
                    <a href="<?= site_url('flat_unit') ?>" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
   			 <div class="view-toggle-group">
                <button class="vtbtn active" id="btnTableView" onclick="switchView('table')" title="Table View">
                    <i class="fas fa-list"></i>
                </button>
                <button class="vtbtn" id="btnGridView" onclick="switchView('grid')" title="Floor Grid View">
                    <i class="fas fa-th"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Active filter pills -->
    <?php if ($anyFilter ?? false): ?>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;align-items:center;">
        <span style="font-size:.8rem;color:var(--text-light);font-weight:600;">Active filters:</span>
        <?php if (!empty($filters['wing_id'])): ?>
            <?php
            $wLabel = ''; foreach ($wings as $w) { if ((int)$w->id === (int)$filters['wing_id']) { $wLabel = $w->wing_name; break; } }
            $clearUrl = site_url('flat_unit?' . http_build_query(array_merge($filters, ['wing_id' => ''])));
            ?>
            <span class="active-filter-pill"><i class="fas fa-layer-group"></i> <?= html_escape($wLabel) ?> <a href="<?= $clearUrl ?>">×</a></span>
        <?php endif; ?>
        <?php if (!empty($filters['floor'])): ?>
            <?php $clearUrl = site_url('flat_unit?' . http_build_query(array_merge($filters, ['floor' => '']))); ?>
            <span class="active-filter-pill"><i class="fas fa-stairs"></i>
                <?= $filters['floor'] == 0 ? 'Ground Floor' : ordinal($filters['floor']) . ' Floor' ?>
                <a href="<?= $clearUrl ?>">×</a>
            </span>
        <?php endif; ?>
        <?php if (!empty($filters['flat_type'])): ?>
            <?php $clearUrl = site_url('flat_unit?' . http_build_query(array_merge($filters, ['flat_type' => '']))); ?>
            <span class="active-filter-pill"><i class="fas fa-home"></i> <?= $filters['flat_type'] ?> <a href="<?= $clearUrl ?>">×</a></span>
        <?php endif; ?>
        <?php if (!empty($filters['status'])): ?>
            <?php $clearUrl = site_url('flat_unit?' . http_build_query(array_merge($filters, ['status' => '']))); ?>
            <span class="active-filter-pill"><i class="fas fa-circle"></i> <?= ucfirst($filters['status']) ?> <a href="<?= $clearUrl ?>">×</a></span>
        <?php endif; ?>
        <?php if (!empty($filters['search'])): ?>
            <?php $clearUrl = site_url('flat_unit?' . http_build_query(array_merge($filters, ['search' => '']))); ?>
            <span class="active-filter-pill"><i class="fas fa-search"></i> "<?= html_escape($filters['search']) ?>" <a href="<?= $clearUrl ?>">×</a></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════
         TABLE VIEW
    ══════════════════════════════════════════════ -->
    <div id="tableView">
        <div class="table-section">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list"></i>
                    <?= $isSuperAdmin ? 'All Societies — Flat Directory' : 'Flat Directory' ?>
                    <small style="font-weight:400;color:var(--text-light);font-size:.78rem;">
                        (<?= count($flats) ?> records)
                    </small>
                </h3>
                <a href="<?= site_url('flat_unit') ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-sync-alt"></i> Refresh
                </a>
                <div class="page-actions">
                    <div class="export-wrapper">
						 <button class="btn btn-outline" onclick="openImportModal()">
               				<i class="fas fa-file-import"></i> Import
            			</button>
    
                        <button type="button" class="btn btn-outline" onclick="toggleExportMenu(event)">
                            <i class="fas fa-download"></i> Export
                        </button>

						    <button class="btn btn-primary" onclick="openAddFlatModal()">
                				<i class="fas fa-plus-circle"></i> Add Flat
            				</button>
                        <div class="export-menu" id="exportMenu">
                            <button onclick="exportExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                            <button onclick="exportCSV()"><i class="fas fa-file-csv"></i> CSV</button>
                            <button onclick="printTable()"><i class="fas fa-print"></i> Print</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table id="flatsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Flat No.</th>
                            <th>Wing</th>
                            <th>Floor</th>
                            <th>Type</th>
                            <th>Area (sq ft)</th>
                            <th>Resident</th>
                            <th>Member Type</th>
                            <th>Parking</th>
                            <th>Status</th>
                            <?php if ($isSuperAdmin): ?><th>Society</th><?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($flats)): ?>
                        <tr>
                            <td colspan="<?= $isSuperAdmin ? 12 : 11 ?>"
                                style="text-align:center;padding:40px;color:var(--text-light);">
                                <i class="fas fa-building" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3;"></i>
                                No flats found. <a href="javascript:openAddFlatModal()" style="color:var(--primary);">Add your first flat →</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($flats as $i => $f): ?>
                        <?php
                            $ini = '—';
                            if (!empty($f->resident_name)) {
                                $np = explode(' ', $f->resident_name, 2);
                                $ini = strtoupper(substr($np[0],0,1) . (isset($np[1]) ? substr($np[1],0,1) : ''));
                            }
                            $floorLabel = $f->floor == 0 ? 'Ground' : ordinal($f->floor);
                            $typeClass  = strtolower(str_replace(['BHK',' '], ['bhk',''], $f->flat_type));
                            if ($f->flat_type === 'Penthouse') $typeClass = 'pent';
                            if (in_array($f->flat_type, ['Shop','Office'])) $typeClass = 'shop';
                        ?>
                        <tr>
                            <td style="color:var(--text-light);font-size:.78rem;"><?= $i+1 ?></td>
                            <td>
                                <span style="font-weight:700;color:var(--text-dark);font-size:.92rem;">
                                    <?= html_escape($f->flat_no) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($f->wing_name)): ?>
                                    <span class="wing-badge"><i class="fas fa-layer-group"></i> <?= html_escape($f->wing_name) ?></span>
                                <?php else: ?><span style="color:var(--text-light);">—</span><?php endif; ?>
                            </td>
                            <td><?= $floorLabel ?></td>
                            <td>
                                <span class="flat-type-badge <?= $typeClass ?>">
                                    <?= html_escape($f->flat_type) ?>
                                </span>
                            </td>
                            <td><?= $f->area_sqft ? number_format($f->area_sqft) : '—' ?></td>
                            <td>
                                <div class="member-name-cell">
                                    <div class="m-avatar <?= empty($f->resident_name) ? 'vacant-av' : '' ?>">
                                        <?= $ini ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:600;font-size:.84rem;">
                                            <?= !empty($f->resident_name) ? html_escape($f->resident_name) : '<span style="color:var(--text-light);font-weight:400;font-size:.8rem;">No resident</span>' ?>
                                        </div>
                                        <?php if (!empty($f->resident_phone)): ?>
                                            <div style="font-size:.73rem;color:var(--text-light);"><?= html_escape($f->resident_phone) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($f->member_type)): ?>
                                    <span class="flat-type-badge <?= $f->member_type === 'owner' ? 'bhk1' : 'bhk2' ?>">
                                        <?= ucfirst($f->member_type) ?>
                                    </span>
                                <?php else: ?><span style="color:var(--text-light);">—</span><?php endif; ?>
                            </td>
                            <td style="font-size:.83rem;"><?= html_escape($f->parking_slot ?: '—') ?></td>
                            <td>
                                <span class="status-badge <?= $f->status ?>">
                                    <?= ucfirst($f->status) ?>
                                </span>
                            </td>
                            <?php if ($isSuperAdmin): ?>
                            <td>
                                <span style="font-size:.78rem;color:var(--text-light);">
                                    <?= html_escape($f->society_name ?? '—') ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="View Details"
                                        onclick='openViewModal(<?= htmlspecialchars(json_encode([
                                            "id"=>$f->id,"flat_no"=>$f->flat_no,"wing_name"=>$f->wing_name??'',
                                            "floor"=>$f->floor,"flat_type"=>$f->flat_type,"area_sqft"=>$f->area_sqft??'',
                                            "status"=>$f->status,"parking_slot"=>$f->parking_slot??'',
                                            "remarks"=>$f->remarks??'',
                                            "resident_name"=>$f->resident_name??'','member_type'=>$f->member_type??'',
                                            "resident_phone"=>$f->resident_phone??'',"resident_email"=>$f->resident_email??'',
                                            "move_in_date"=>$f->move_in_date??'',"society_name"=>$f->society_name??''
                                        ]), ENT_QUOTES, 'UTF-8') ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit Flat"
                                        onclick='openEditFlatModal(<?= htmlspecialchars(json_encode([
                                            "id"=>$f->id,"flat_no"=>$f->flat_no,"wing_id"=>$f->wing_id??'',
                                            "floor"=>$f->floor,"flat_type"=>$f->flat_type,"area_sqft"=>$f->area_sqft??'',
                                            "status"=>$f->status,"parking_slot"=>$f->parking_slot??'',
                                            "remarks"=>$f->remarks??''
                                        ]), ENT_QUOTES, 'UTF-8') ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($f->status === 'vacant'): ?>
                                    <button class="btn-icon success" title="Assign Resident"
                                        onclick="openAssignModal(<?= $f->id ?>, '<?= html_escape($f->flat_no) ?>')">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?= base_url('flat_unit/delete/'.$f->id) ?>"
                                       onclick="return confirm('Delete flat <?= html_escape($f->flat_no) ?>? This action cannot be undone.')">
                                        <button class="btn-icon delete" title="Delete Flat">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         FLOOR / GRID VIEW
    ══════════════════════════════════════════════ -->
    <div id="gridView" style="display:none;">
        <?php if (empty($flats)): ?>
            <div style="text-align:center;padding:50px;color:var(--text-light);">
                <i class="fas fa-building" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3;"></i>
                No flats found.
            </div>
        <?php else: ?>
        <?php
        // Group flats by floor
        $floorGroups = [];
        foreach ($flats as $f) {
            $floorGroups[$f->floor][] = $f;
        }
        ksort($floorGroups);
        ?>
        <div class="floor-view">
        <?php foreach ($floorGroups as $fl => $flFlats): ?>
            <?php
            $flLabel = $fl == 0 ? 'Ground Floor' : ordinal($fl) . ' Floor';
            $flOcc   = count(array_filter($flFlats, fn($x) => $x->status === 'occupied'));
            $flVac   = count(array_filter($flFlats, fn($x) => $x->status === 'vacant'));
            ?>
            <div class="floor-card" id="fc-<?= $fl ?>">
                <div class="floor-card-header" onclick="toggleFloorCard(<?= $fl ?>)">
                    <div class="floor-num-badge">F<?= $fl == 0 ? 'G' : $fl ?></div>
                    <div class="floor-card-title"><?= $flLabel ?></div>
                    <div class="floor-meta">
                        <span class="fmini-badge tot"><?= count($flFlats) ?> units</span>
                        <span class="fmini-badge occ"><?= $flOcc ?> occ</span>
                        <span class="fmini-badge vac"><?= $flVac ?> vac</span>
                    </div>
                    <i class="fas fa-chevron-down floor-toggle-arrow"></i>
                </div>
                <div class="floor-card-body">
                    <div class="unit-grid">
                        <?php foreach ($flFlats as $f):
                            $typeClass = strtolower(str_replace(['BHK',' '], ['bhk',''], $f->flat_type));
                            if ($f->flat_type === 'Penthouse') $typeClass = 'pent';
                            if (in_array($f->flat_type, ['Shop','Office'])) $typeClass = 'shop';
                        ?>
                        <div class="unit-tile <?= $f->status ?>">
                            <div class="unit-tile-top">
                                <div class="unit-tile-no"><?= html_escape($f->flat_no) ?></div>
                                <span class="flat-type-badge <?= $typeClass ?>"><?= html_escape($f->flat_type) ?></span>
                            </div>
                            <div class="unit-tile-res">
                                <?= !empty($f->resident_name) ? html_escape($f->resident_name) : '<span style="color:var(--text-light);font-weight:400;">Vacant</span>' ?>
                            </div>
                            <div class="unit-tile-sub"><?= $f->area_sqft ? number_format($f->area_sqft).' sq ft' : '—' ?></div>
                            <span class="status-badge <?= $f->status ?>" style="margin-top:8px;display:inline-flex;">
                                <?= ucfirst($f->status) ?>
                            </span>
                            <div class="unit-tile-actions">
                                <button class="unit-tile-btn"
                                    onclick='openViewModal(<?= htmlspecialchars(json_encode([
                                        "id"=>$f->id,"flat_no"=>$f->flat_no,"wing_name"=>$f->wing_name??'',
                                        "floor"=>$f->floor,"flat_type"=>$f->flat_type,"area_sqft"=>$f->area_sqft??'',
                                        "status"=>$f->status,"parking_slot"=>$f->parking_slot??'',
                                        "remarks"=>$f->remarks??'',
                                        "resident_name"=>$f->resident_name??'','member_type'=>$f->member_type??'',
                                        "resident_phone"=>$f->resident_phone??'',"resident_email"=>$f->resident_email??'',
                                        "move_in_date"=>$f->move_in_date??'',"society_name"=>$f->society_name??''
                                    ]), ENT_QUOTES, 'UTF-8') ?>)'>
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="unit-tile-btn"
                                    onclick='openEditFlatModal(<?= htmlspecialchars(json_encode([
                                        "id"=>$f->id,"flat_no"=>$f->flat_no,"wing_id"=>$f->wing_id??'',
                                        "floor"=>$f->floor,"flat_type"=>$f->flat_type,"area_sqft"=>$f->area_sqft??'',
                                        "status"=>$f->status,"parking_slot"=>$f->parking_slot??'',
                                        "remarks"=>$f->remarks??''
                                    ]), ENT_QUOTES, 'UTF-8') ?>)'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <!-- Add tile -->
                        <div class="unit-tile add-tile" onclick="openAddFlatModal(<?= $fl ?>)">
                            <div class="add-tile-icon"><i class="fas fa-plus"></i></div>
                            <div class="add-tile-lbl">Add to <?= $flLabel ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /.main -->

<!-- ══════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════ -->

<!-- ── Add / Edit Flat Modal ── -->
<div class="modal" id="flatFormModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-building"></i> <span id="flatFormTitle">Add New Flat</span></h3>
            <span class="modal-close" onclick="closeModal('flatFormModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="flatForm" method="post" action="<?= base_url('flat_unit/save') ?>">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <input type="hidden" id="flatId" name="flatId" value="">

                <div class="section-divider">Unit Details</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Flat / Unit Number *</label>
                        <input type="text" name="flat_no" id="ff_flat_no" class="form-control" placeholder="e.g. A-101" required>
                    </div>
                    <div class="form-group">
                        <label>Wing / Block</label>
                        <select name="wing_id" id="ff_wing_id" class="form-control">
                            <option value="">-- Select Wing --</option>
                            <?php foreach ($wings as $w): ?>
                                <option value="<?= (int)$w->id ?>"><?= html_escape($w->wing_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Floor *</label>
                        <select name="floor" id="ff_floor" class="form-control" required>
                            <option value="0">Ground Floor</option>
                            <?php for ($fi = 1; $fi <= 20; $fi++): ?>
                                <option value="<?= $fi ?>"><?= ordinal($fi) ?> Floor</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Flat Type *</label>
                        <select name="flat_type" id="ff_flat_type" class="form-control" required>
                            <option value="1BHK">1 BHK</option>
                            <option value="2BHK" selected>2 BHK</option>
                            <option value="3BHK">3 BHK</option>
                            <option value="4BHK">4 BHK</option>
                            <option value="Penthouse">Penthouse</option>
                            <option value="Shop">Shop</option>
                            <option value="Office">Office</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Area (sq ft)</label>
                        <input type="number" name="area_sqft" id="ff_area" class="form-control" placeholder="e.g. 950" min="1">
                    </div>
                    <div class="form-group">
                        <label>Parking Slot</label>
                        <input type="text" name="parking_slot" id="ff_parking" class="form-control" placeholder="e.g. P-12">
                    </div>
                </div>

                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" id="ff_status" class="form-control" required>
                        <option value="vacant">Vacant</option>
                        <option value="occupied">Occupied</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Remarks</label>
                    <textarea name="remarks" id="ff_remarks" class="form-control" placeholder="Optional notes about the unit…"></textarea>
                </div>

                <div class="modal-footer" style="padding:0;margin-top:8px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('flatFormModal')"><i class="fas fa-times"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Flat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── View Flat Modal ── -->
<div class="modal" id="viewFlatModal">
    <div class="modal-content wide">
        <div class="modal-header">
            <h3><i class="fas fa-building"></i> Flat Details — <span id="vd_flat_no"></span></h3>
            <span class="modal-close" onclick="closeModal('viewFlatModal')">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Header row -->
            <div style="display:flex;align-items:center;gap:18px;margin-bottom:20px;padding:16px;background:#f8faff;border-radius:12px;border:1px solid #e8f0fe;">
                <div style="width:56px;height:56px;border-radius:14px;background:#e0e7ff;color:#3730a3;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">
                    <i class="fas fa-door-closed"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:1.3rem;font-weight:800;color:var(--text-dark);" id="vd_flat_no2"></div>
                    <div style="font-size:.82rem;color:var(--text-light);margin-top:3px;" id="vd_sub"></div>
                </div>
                <div id="vd_status_badge"></div>
            </div>

            <div class="detail-grid">
                <div class="detail-block">
                    <h4><i class="fas fa-building"></i> Unit Info</h4>
                    <div class="detail-row"><span class="detail-key">Wing</span><span class="detail-val" id="vd_wing">—</span></div>
                    <div class="detail-row"><span class="detail-key">Floor</span><span class="detail-val" id="vd_floor">—</span></div>
                    <div class="detail-row"><span class="detail-key">Type</span><span class="detail-val" id="vd_type">—</span></div>
                    <div class="detail-row"><span class="detail-key">Area</span><span class="detail-val" id="vd_area">—</span></div>
                    <div class="detail-row"><span class="detail-key">Parking</span><span class="detail-val" id="vd_parking">—</span></div>
                    <div class="detail-row"><span class="detail-key">Society</span><span class="detail-val" id="vd_society">—</span></div>
                </div>
                <div class="detail-block">
                    <h4><i class="fas fa-user"></i> Resident Info</h4>
                    <div class="detail-row"><span class="detail-key">Name</span><span class="detail-val" id="vd_resident">—</span></div>
                    <div class="detail-row"><span class="detail-key">Type</span><span class="detail-val" id="vd_mtype">—</span></div>
                    <div class="detail-row"><span class="detail-key">Phone</span><span class="detail-val" id="vd_phone">—</span></div>
                    <div class="detail-row"><span class="detail-key">Email</span><span class="detail-val" id="vd_email">—</span></div>
                    <div class="detail-row"><span class="detail-key">Move-in</span><span class="detail-val" id="vd_movein">—</span></div>
                </div>
            </div>

            <div id="vd_remarks_wrap" style="margin-top:14px;background:#f8faff;padding:12px 16px;border-radius:10px;border:1px solid #e8f0fe;display:none;">
                <span style="font-size:.78rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.05em;">Remarks</span>
                <p style="margin:6px 0 0;font-size:.85rem;color:var(--text-dark);" id="vd_remarks"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewFlatModal')"><i class="fas fa-times"></i> Close</button>
        </div>
    </div>
</div>

<!-- ── Assign Resident Modal ── -->
<div class="modal" id="assignModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Assign Resident — <span id="assignFlatLabel"></span></h3>
            <span class="modal-close" onclick="closeModal('assignModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="assignForm" method="post" action="<?= base_url('flat_unit/assign_resident') ?>">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <input type="hidden" name="flat_id" id="assignFlatId" value="">
                <div class="form-group">
                    <label>Select Member *</label>
                    <select name="member_id" class="form-control" required>
                        <option value="">-- Select Member --</option>
                        <?php foreach ($unassignedMembers as $m): ?>
                            <option value="<?= $m->id ?>">
                                <?= html_escape($m->name) ?> — Flat <?= html_escape($m->flat_no) ?> (<?= ucfirst($m->member_type) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Move-in Date</label>
                    <input type="date" name="move_in_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="modal-footer" style="padding:0;margin-top:8px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('assignModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Import Modal ── -->
<div class="modal" id="importModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-import"></i> Import Flats via CSV</h3>
            <span class="modal-close" onclick="closeModal('importModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="importForm" method="post" action="<?= base_url('flat_unit/import_csv') ?>" enctype="multipart/form-data">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="form-group">
                    <a href="<?= base_url('assets/sample/flats_sample.csv') ?>" class="btn btn-outline btn-sm" download>
                        <i class="fas fa-download"></i> Download Sample CSV
                    </a>
                </div>
                <div class="form-group">
                    <label>Upload CSV File *</label>
                    <input type="file" name="csv_file" id="importFile" class="form-control" accept=".csv" required>
                </div>
                <small style="color:var(--text-light);font-size:.78rem;">
                    • Only CSV files allowed &nbsp;• Required columns: flat_no, floor, flat_type, status &nbsp;• Duplicates skipped
                </small>
                <div class="modal-footer" style="padding:0;margin-top:14px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('importModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload &amp; Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
/* ──────── UTILS ──────── */
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
window.addEventListener('click', e => { if (e.target.classList.contains('modal')) e.target.classList.remove('active'); });
window.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active')); });

/* ──────── ADD FLAT ──────── */
function openAddFlatModal(floor) {
    document.getElementById('flatForm').reset();
    document.getElementById('flatId').value = '';
    document.getElementById('flatFormTitle').innerText = 'Add New Flat';
    if (floor !== undefined) document.getElementById('ff_floor').value = floor;
    openModal('flatFormModal');
}

/* ──────── EDIT FLAT ──────── */
function openEditFlatModal(f) {
    document.getElementById('flatFormTitle').innerText = 'Edit Flat';
    document.getElementById('flatId').value      = f.id      || '';
    document.getElementById('ff_flat_no').value  = f.flat_no || '';
    document.getElementById('ff_wing_id').value  = f.wing_id || '';
    document.getElementById('ff_floor').value    = f.floor   || '0';
    document.getElementById('ff_flat_type').value= f.flat_type|| '2BHK';
    document.getElementById('ff_area').value     = f.area_sqft|| '';
    document.getElementById('ff_parking').value  = f.parking_slot || '';
    document.getElementById('ff_status').value   = f.status  || 'vacant';
    document.getElementById('ff_remarks').value  = f.remarks || '';
    openModal('flatFormModal');
}

/* ──────── VIEW MODAL ──────── */
function openViewModal(f) {
    const floorLabel = f.floor == 0 ? 'Ground Floor' : ordinalJS(f.floor) + ' Floor';
    document.getElementById('vd_flat_no').innerText  = f.flat_no;
    document.getElementById('vd_flat_no2').innerText = f.flat_no;
    document.getElementById('vd_sub').innerText = [f.flat_type, floorLabel, f.wing_name || ''].filter(Boolean).join(' · ');
    document.getElementById('vd_wing').innerText    = f.wing_name || '—';
    document.getElementById('vd_floor').innerText   = floorLabel;
    document.getElementById('vd_type').innerText    = f.flat_type || '—';
    document.getElementById('vd_area').innerText    = f.area_sqft ? f.area_sqft + ' sq ft' : '—';
    document.getElementById('vd_parking').innerText = f.parking_slot || '—';
    document.getElementById('vd_society').innerText = f.society_name || '—';
    document.getElementById('vd_resident').innerText= f.resident_name || 'No resident';
    document.getElementById('vd_mtype').innerText   = f.member_type ? f.member_type.charAt(0).toUpperCase()+f.member_type.slice(1) : '—';
    document.getElementById('vd_phone').innerText   = f.resident_phone || '—';
    document.getElementById('vd_email').innerText   = f.resident_email || '—';
    document.getElementById('vd_movein').innerText  = f.move_in_date || '—';

    const statusColors = { occupied:'#d1fae5', vacant:'#fef3c7', blocked:'#fee2e2' };
    const textColors   = { occupied:'#065f46', vacant:'#92400e', blocked:'#991b1b' };
    const statusBadge  = document.getElementById('vd_status_badge');
    statusBadge.innerHTML = `<span class="status-badge ${f.status}" style="font-size:.82rem;padding:5px 14px;">${f.status.charAt(0).toUpperCase()+f.status.slice(1)}</span>`;

    const remarkWrap = document.getElementById('vd_remarks_wrap');
    if (f.remarks) { document.getElementById('vd_remarks').innerText = f.remarks; remarkWrap.style.display='block'; }
    else { remarkWrap.style.display='none'; }

    openModal('viewFlatModal');
}

function ordinalJS(n) {
    const s = ['th','st','nd','rd'], v = n % 100;
    return n + (s[(v-20)%10] || s[v] || s[0]);
}

/* ──────── ASSIGN ──────── */
function openAssignModal(flatId, flatNo) {
    document.getElementById('assignFlatId').value      = flatId;
    document.getElementById('assignFlatLabel').innerText = flatNo;
    openModal('assignModal');
}

/* ──────── IMPORT ──────── */
function openImportModal() { openModal('importModal'); }

/* ──────── FLOOR TOGGLE ──────── */
function toggleFloorCard(fl) {
    document.getElementById('fc-' + fl).classList.toggle('collapsed');
}

/* ──────── VIEW SWITCH ──────── */
function switchView(v) {
    const tv = document.getElementById('tableView');
    const gv = document.getElementById('gridView');
    const btnT = document.getElementById('btnTableView');
    const btnG = document.getElementById('btnGridView');
    if (v === 'table') {
        tv.style.display = 'block'; gv.style.display = 'none';
        btnT.classList.add('active'); btnG.classList.remove('active');
        localStorage.setItem('flatUnitView','table');
    } else {
        tv.style.display = 'none'; gv.style.display = 'block';
        btnG.classList.add('active'); btnT.classList.remove('active');
        localStorage.setItem('flatUnitView','grid');
    }
}

/* ──────── EXPORT ──────── */
function toggleExportMenu(e) { e.stopPropagation(); document.getElementById('exportMenu').classList.toggle('show'); }
document.addEventListener('click', () => { const m = document.getElementById('exportMenu'); if(m) m.classList.remove('show'); });
function exportExcel() { XLSX.writeFile(XLSX.utils.table_to_book(document.getElementById('flatsTable'),{sheet:'Flats'}),'flat_list.xlsx'); }
function exportCSV() {
    const csv = XLSX.utils.sheet_to_csv(XLSX.utils.table_to_sheet(document.getElementById('flatsTable')));
    const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'})); a.download='flat_list.csv'; a.click();
}
function printTable() { window.print(); }

/* ──────── INIT ──────── */
document.addEventListener('DOMContentLoaded', function () {
    // Flash auto-dismiss
    const f = document.getElementById('flashMsg');
    if (f) setTimeout(() => { f.style.transition='opacity .5s'; f.style.opacity='0'; setTimeout(()=>f.remove(),500); }, 3500);

    // Restore view preference
    const savedView = localStorage.getItem('flatUnitView') || 'table';
    switchView(savedView);

    // Animate occupancy bar
    setTimeout(() => {
        document.getElementById('barOcc').style.width   = '<?= $occupiedPercent ?>%';
        document.getElementById('barVac').style.width   = '<?= $vacantPercent ?>%';
        document.getElementById('barBlock').style.width = '<?= $blockedPercent ?>%';
    }, 300);
});
</script>
</body>
</html>
