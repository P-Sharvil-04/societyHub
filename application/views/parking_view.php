<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0,user-scalable=yes">
    <title>Parking Management · Society</title>
    <link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <style>
        :root {
            --primary: #3498db; --primary-dark: #2980b9;
            --success: #059669; --warning: #d97706;
            --danger: #dc2626;  --amber: #f59e0b;
            --text: #1e293b;    --text-light: #64748b;
            --border: #e2e8f0;  --light: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }

        /* ── Stats ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }
        .stat-card {
            background: #fff; border-radius: 16px; padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06); border: 1px solid var(--border);
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
        .stat-icon {
            width: 46px; height: 46px; border-radius: 12px;
            background: rgba(52,152,219,.1); color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .stat-icon.green  { background: rgba(5,150,105,.1);  color: var(--success); }
        .stat-icon.amber  { background: rgba(215,119,6,.1);  color: var(--warning); }
        .stat-icon.purple { background: rgba(139,92,246,.1); color: #7c3aed; }
        .stat-info h4 { font-size: .75rem; color: var(--text-light); margin: 0 0 3px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .stat-info h2 { font-size: 1.7rem; font-weight: 800; margin: 0 0 2px; color: var(--text); line-height: 1; }
        .stat-trend { font-size: .72rem; color: var(--text-light); }

        /* ── Layout ── */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 992px) { .two-col { grid-template-columns: 1fr; } }

        /* ── Card ── */
        .card {
            background: #fff; border-radius: 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            border: 1px solid var(--border);
            margin-bottom: 20px; overflow: hidden;
        }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px;
            background: #fff; border-bottom: 1px solid var(--border);
        }
        .card-header h3 {
            font-size: .98rem; font-weight: 700; margin: 0;
            display: flex; align-items: center; gap: 8px; color: var(--text);
        }
        .card-header h3 i { color: var(--primary); }
        .card-body { padding: 20px; }

        /* ── Tabs ── */
        .park-tabs {
            display: flex; gap: 6px; margin-bottom: 18px;
            border-bottom: 1px solid var(--border); padding-bottom: 10px;
        }
        .park-tab {
            background: transparent; border: none;
            padding: 6px 14px; border-radius: 20px;
            font-weight: 600; font-size: .8rem; color: var(--text-light);
            cursor: pointer; font-family: inherit; transition: all .2s;
        }
        .park-tab i { margin-right: 5px; }
        .park-tab.on { background: var(--primary); color: #fff; }
        .park-tab:not(.on):hover { background: #e8f4fd; color: var(--primary); }

        .pane { display: none; }
        .pane.on { display: block; }

        /* ── Parking grid ── */
        .park-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 14px;
        }
        .park-tile {
            background: #fff; border-radius: 14px;
            padding: 14px 16px; border: 1.5px solid var(--border);
            transition: all .2s; position: relative;
        }
        .park-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.1); border-color: var(--primary); }
        .park-tile.t4w  { border-left: 4px solid var(--primary); }
        .park-tile.t2w  { border-left: 4px solid var(--success); }
        .park-tile.mine { border: 2px solid var(--amber); background: #fffbeb; }

        .you-tag {
            position: absolute; top: -6px; right: 10px;
            background: var(--amber); color: #1e293b;
            font-size: .67rem; font-weight: 700;
            padding: 2px 8px; border-radius: 20px;
        }
        .tile-slot { font-size: 1.3rem; font-weight: 800; color: var(--text); line-height: 1.2; margin-bottom: 4px; }
        /* ── FIX: flat info styling ── */
        .tile-flat {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f1f5f9; border-radius: 20px;
            padding: 2px 10px; font-size: .75rem; font-weight: 600;
            color: var(--text); margin-bottom: 8px;
        }
        .tile-flat i { color: var(--primary); font-size: .68rem; }
        .tile-flat.none { color: var(--text-light); font-weight: 400; }
        .tile-info { font-size: .78rem; color: var(--text-light); line-height: 1.5; }
        .tile-info strong { color: var(--text); }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left; padding: 10px 14px;
            background: var(--light); font-weight: 700;
            font-size: .74rem; color: var(--text-light);
            border-bottom: 2px solid var(--border);
            text-transform: uppercase; letter-spacing: .04em;
            white-space: nowrap;
        }
        td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: .84rem; color: var(--text); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8faff; }

        /* ── Flat cell ── */
        .flat-cell {
            display: flex; align-items: center; gap: 6px;
        }
        .flat-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #e0e7ff; color: #3730a3;
            border: 1px solid #c7d2fe;
            border-radius: 20px; padding: 2px 10px;
            font-size: .74rem; font-weight: 700;
        }
        .flat-chip i { font-size: .65rem; }
        .wing-chip {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f1f5f9; color: var(--text-light);
            border-radius: 20px; padding: 2px 8px;
            font-size: .68rem; font-weight: 500;
        }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600;
        }
        .b-4w    { background: rgba(52,152,219,.12); color: var(--primary); }
        .b-2w    { background: rgba(5,150,105,.12);  color: var(--success); }
        .b-owner { background: #e0f2fe; color: #0369a1; }
        .b-ten   { background: #f3e8ff; color: #7c3aed; }
        .b-cnt   { background: rgba(52,152,219,.1); color: var(--primary); }

        .you-inline {
            background: var(--amber); color: #1e293b;
            font-size: .63rem; font-weight: 700;
            padding: 1px 6px; border-radius: 10px; margin-left: 5px;
        }

        /* ── Form ── */
        .form-group { margin-bottom: 14px; }
        .form-label { font-size: .8rem; font-weight: 600; color: var(--text); display: block; margin-bottom: 5px; }
        .form-control, .form-select {
            width: 100%; padding: 9px 13px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-family: inherit; font-size: .84rem;
            color: var(--text); background: var(--light); outline: none;
            transition: border-color .2s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); background: #fff; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media(max-width:500px) { .form-grid-2 { grid-template-columns: 1fr; } }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: 20px;
            font-weight: 600; font-size: .84rem; cursor: pointer;
            border: none; font-family: inherit; transition: all .2s; text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 3px 10px rgba(52,152,219,.3); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-full { width: 100%; justify-content: center; }
        .btn-revoke {
            background: #fee2e2; color: var(--danger);
            border-radius: 8px; padding: 5px 12px; font-size: .75rem;
        }
        .btn-revoke:hover { background: var(--danger); color: #fff; }

        /* ── No parking list ── */
        .no-park-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px; border-bottom: 1px solid var(--border);
            font-size: .83rem;
        }
        .no-park-item:last-child { border-bottom: none; }

        /* ── Notification ── */
        .notification {
            display: flex; align-items: center; gap: 10px;
            padding: 13px 18px; border-radius: 12px; margin-bottom: 20px;
            font-weight: 600; font-size: .86rem;
        }
        .notification.success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .notification.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .empty-state { text-align: center; padding: 40px; color: var(--text-light); }
        .empty-state i { font-size: 2.5rem; opacity: .25; display: block; margin-bottom: 10px; }
    </style>
</head>

<?php include('header.php'); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">

<body>
<div class="overlay" id="overlay"></div>
<?php $activePage = 'parking'; include('sidebar.php'); ?>

<div class="main" id="main">

    <!-- Flash messages -->
    <?php if (!empty($flash['success'])): ?>
        <div class="notification success" id="flashMsg">
            <i class="fas fa-check-circle"></i> <?= $flash['success'] ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
        <div class="notification error" id="flashMsg">
            <i class="fas fa-exclamation-circle"></i> <?= $flash['error'] ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-square-parking"></i></div>
            <div class="stat-info"><h4>Total Allotted</h4><h2><?= $stats['total'] ?? 0 ?></h2><div class="stat-trend">society slots</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-car"></i></div>
            <div class="stat-info"><h4>4-Wheelers</h4><h2><?= $stats['four_wheel'] ?? 0 ?></h2><div class="stat-trend">vehicles</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-motorcycle"></i></div>
            <div class="stat-info"><h4>2-Wheelers</h4><h2><?= $stats['two_wheel'] ?? 0 ?></h2><div class="stat-trend">vehicles</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-id-card"></i></div>
            <div class="stat-info"><h4>Total Members</h4><h2><?= $stats['members'] ?? 0 ?></h2><div class="stat-trend">residents</div></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         CHAIRMAN VIEW
    ═══════════════════════════════════════════ -->
    <?php if ($section === 'chairman'): ?>
    <div class="two-col">

        <!-- LEFT: All parking records -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list-check"></i> All Parking Records</h3>
                    <span class="badge b-cnt"><?= count($parking_list ?? []) ?> records</span>
                </div>
                <div class="card-body">
                    <div class="park-tabs">
                        <button class="park-tab on" data-tab="ch-grid"><i class="fas fa-th"></i> Visual Grid</button>
                        <button class="park-tab"    data-tab="ch-table"><i class="fas fa-list"></i> Table View</button>
                    </div>

                    <!-- ── GRID VIEW ── -->
                    <div class="pane on" id="ch-grid">
                        <?php if (empty($parking_list)): ?>
                            <div class="empty-state"><i class="fas fa-square-parking"></i>No parking assigned yet.</div>
                        <?php else: ?>
                            <div class="park-grid">
                                <?php foreach ($parking_list as $p): ?>
                                <div class="park-tile <?= $p->vehicle_type === '4-Wheeler' ? 't4w' : 't2w' ?>">
                                    <div class="tile-slot"><?= html_escape($p->slot_number) ?></div>

                                    <!-- ★ FLAT + WING display ★ -->
                                    <?php if (!empty($p->flat_no)): ?>
                                        <div class="tile-flat">
                                            <i class="fas fa-door-closed"></i>
                                            <?= !empty($p->wing_name) ? html_escape($p->wing_name).' · ' : '' ?>
                                            <?= html_escape($p->flat_no) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="tile-flat none">No flat assigned</div>
                                    <?php endif; ?>

                                    <div class="tile-info">
                                        <strong><?= html_escape($p->owner_name ?? '—') ?></strong><br>
                                        <?= $p->vehicle_type === '4-Wheeler' ? '🚗' : '🛵' ?>
                                        <?= !empty($p->vehicle_number) ? html_escape($p->vehicle_number) : 'No vehicle no.' ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── TABLE VIEW ── -->
                    <div class="pane" id="ch-table">
                        <?php if (empty($parking_list)): ?>
                            <div class="empty-state"><i class="fas fa-list"></i>No records.</div>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Slot</th>
                                            <th>Flat / Wing</th>
                                            <th>Member</th>
                                            <th>Type</th>
                                            <th>Vehicle No.</th>
                                            <th>Allocated By</th>
                                            <th>Allocated On</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($parking_list as $i => $p): ?>
                                        <tr>
                                            <td style="color:var(--text-light);font-size:.75rem;"><?= $i+1 ?></td>
                                            <td><strong><?= html_escape($p->slot_number) ?></strong></td>
                                            <td>
                                                <!-- ★ FLAT + WING display ★ -->
                                                <?php if (!empty($p->flat_no)): ?>
                                                    <div class="flat-cell">
                                                        <span class="flat-chip">
                                                            <i class="fas fa-door-closed"></i>
                                                            <?= html_escape($p->flat_no) ?>
                                                        </span>
                                                        <?php if (!empty($p->wing_name)): ?>
                                                            <span class="wing-chip">
                                                                <i class="fas fa-layer-group"></i>
                                                                <?= html_escape($p->wing_name) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="color:var(--text-light);font-size:.78rem;">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= html_escape($p->owner_name ?? '—') ?>
                                                <?php if (!empty($p->owner_type)): ?>
                                                    <span class="badge <?= $p->owner_type === 'owner' ? 'b-owner' : 'b-ten' ?>" style="margin-left:4px;">
                                                        <?= ucfirst($p->owner_type) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $p->vehicle_type === '4-Wheeler' ? 'b-4w' : 'b-2w' ?>">
                                                    <?= $p->vehicle_type === '4-Wheeler' ? '🚗 4W' : '🛵 2W' ?>
                                                </span>
                                            </td>
                                            <td><?= html_escape($p->vehicle_number ?? '—') ?></td>
                                            <td style="font-size:.78rem;"><?= html_escape($p->allocated_by_name ?? '—') ?></td>
                                            <td style="font-size:.78rem;white-space:nowrap;"><?= date('d M Y', strtotime($p->allocated_at)) ?></td>
                                            <td>
                                                <a href="<?= site_url('parking/revoke/'.$p->id) ?>" class="btn btn-revoke revoke-btn">
                                                    <i class="fas fa-ban"></i> Revoke
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div><!-- /left -->

        <!-- RIGHT: Assign form + no-parking list -->
        <div>
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-circle-plus"></i> Assign Parking Slot</h3></div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('parking/assign') ?>">
                        <div class="form-group">
                            <label class="form-label">Owner / Tenant *</label>
                            <select name="owner_id" class="form-select" required>
                                <option value="">— Select Member —</option>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m->id ?>">
                                        <?php
                                        $label = '';
                                        if (!empty($m->wing_name)) $label .= '[' . $m->wing_name . '] ';
                                        if (!empty($m->flat_no))   $label .= $m->flat_no . ' — ';
                                        $label .= $m->name . ' (' . ucfirst($m->member_type) . ')';
                                        echo html_escape($label);
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Slot Number *</label>
                                <input type="text" name="slot_number" class="form-control" placeholder="e.g. P-01" maxlength="10" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Vehicle Type *</label>
                                <select name="vehicle_type" class="form-select" required>
                                    <option value="">— Select —</option>
                                    <option value="4-Wheeler">🚗 4-Wheeler</option>
                                    <option value="2-Wheeler">🛵 2-Wheeler</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Vehicle Number</label>
                            <input type="text" name="vehicle_number" class="form-control"
                                placeholder="MH 12 AB 1234" style="text-transform:uppercase"
                                oninput="this.value=this.value.toUpperCase()">
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-circle-check"></i> Assign Parking
                        </button>
                    </form>
                </div>
            </div>

            <!-- Members without parking -->
            <?php
                $assigned_ids = array_column($parking_list ?? [], 'owner_id');
                $no_park = array_filter($members ?? [], fn($m) => !in_array((int)$m->id, array_map('intval', $assigned_ids)));
            ?>
            <?php if (!empty($no_park)): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-triangle-exclamation" style="color:var(--danger);"></i> No Parking Assigned</h3>
                    <span class="badge" style="background:rgba(220,38,38,.1);color:var(--danger);"><?= count($no_park) ?></span>
                </div>
                <div style="max-height:280px;overflow-y:auto;">
                    <?php foreach ($no_park as $m): ?>
                    <div class="no-park-item">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php if (!empty($m->wing_name)): ?>
                                <span class="wing-chip"><i class="fas fa-layer-group"></i> <?= html_escape($m->wing_name) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($m->flat_no)): ?>
                                <span class="flat-chip"><i class="fas fa-door-closed"></i> <?= html_escape($m->flat_no) ?></span>
                            <?php endif; ?>
                            <span style="font-size:.83rem;"><?= html_escape($m->name) ?></span>
                            <span class="badge <?= $m->member_type === 'owner' ? 'b-owner' : 'b-ten' ?>"><?= ucfirst($m->member_type) ?></span>
                        </div>
                        <span style="color:var(--danger);font-size:.75rem;font-weight:600;">None</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div><!-- /right -->
    </div>

    <!-- ═══════════════════════════════════════════
         OWNER / TENANT VIEW
    ═══════════════════════════════════════════ -->
    <?php else: ?>

    <!-- My parking -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-id-card"></i> My Assigned Parking</h3>
            <span class="badge b-cnt"><?= count($my_parking ?? []) ?> slot<?= count($my_parking??[]) !== 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body">
            <?php if (empty($my_parking)): ?>
                <div class="empty-state">
                    <i class="fas fa-square-parking"></i>
                    <p>No parking slot assigned to you yet.<br>
                       Please contact your chairman.</p>
                </div>
            <?php else: ?>
                <div class="park-grid">
                    <?php foreach ($my_parking as $p): ?>
                    <div class="park-tile mine">
                        <div class="tile-slot"><?= html_escape($p->slot_number) ?></div>
                        <!-- ★ FLAT + WING ★ -->
                        <?php if (!empty($p->flat_no)): ?>
                            <div class="tile-flat">
                                <i class="fas fa-door-closed"></i>
                                <?= !empty($p->wing_name) ? html_escape($p->wing_name).' · ':'' ?>
                                <?= html_escape($p->flat_no) ?>
                            </div>
                        <?php endif; ?>
                        <div class="tile-info">
                            <span class="badge <?= $p->vehicle_type === '4-Wheeler' ? 'b-4w' : 'b-2w' ?>" style="margin-bottom:6px;">
                                <?= $p->vehicle_type === '4-Wheeler' ? '🚗 4-Wheeler' : '🛵 2-Wheeler' ?>
                            </span><br>
                            <?php if (!empty($p->vehicle_number)): ?>
                                <strong><?= html_escape($p->vehicle_number) ?></strong><br>
                            <?php endif; ?>
                            <i class="fas fa-calendar" style="margin-right:3px;opacity:.5;"></i>
                            <?= date('d M Y', strtotime($p->allocated_at)) ?>
                            <?php if (!empty($p->allocated_by_name)): ?>
                                <br><i class="fas fa-user-tie" style="margin-right:3px;opacity:.5;"></i>
                                <?= html_escape($p->allocated_by_name) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Society overview -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-map"></i> Society Parking Overview</h3>
            <div style="display:flex;gap:10px;font-size:.72rem;align-items:center;flex-wrap:wrap;">
                <span><span class="badge b-4w">🚗</span> 4-Wheeler</span>
                <span><span class="badge b-2w">🛵</span> 2-Wheeler</span>
                <span style="background:var(--amber);color:#1e293b;padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:700;">YOU</span>
            </div>
        </div>
        <div class="card-body">
            <div class="park-tabs">
                <button class="park-tab on" data-tab="ow-grid"><i class="fas fa-th"></i> Visual Grid</button>
                <button class="park-tab"    data-tab="ow-table"><i class="fas fa-list"></i> Table</button>
            </div>

            <!-- Grid -->
            <div class="pane on" id="ow-grid">
                <?php if (empty($all_parking)): ?>
                    <div class="empty-state"><i class="fas fa-square-parking"></i>No parking assigned.</div>
                <?php else: ?>
                    <div class="park-grid">
                        <?php foreach ($all_parking as $p):
                            $mine = ((int)$p->owner_id === (int)($sess['user_id'] ?? 0));
                        ?>
                        <div class="park-tile <?= $mine ? 'mine' : ($p->vehicle_type === '4-Wheeler' ? 't4w' : 't2w') ?>">
                            <?php if ($mine): ?><span class="you-tag">YOU</span><?php endif; ?>
                            <div class="tile-slot"><?= html_escape($p->slot_number) ?></div>
                            <!-- ★ FLAT + WING ★ -->
                            <?php if (!empty($p->flat_no)): ?>
                                <div class="tile-flat">
                                    <i class="fas fa-door-closed"></i>
                                    <?= !empty($p->wing_name) ? html_escape($p->wing_name).' · ':'' ?>
                                    <?= html_escape($p->flat_no) ?>
                                </div>
                            <?php else: ?>
                                <div class="tile-flat none">No flat</div>
                            <?php endif; ?>
                            <div class="tile-info">
                                <?= html_escape($p->owner_name ?? '—') ?><br>
                                <?= $p->vehicle_type === '4-Wheeler' ? '🚗' : '🛵' ?>
                                <?= !empty($p->vehicle_number) ? html_escape($p->vehicle_number) : '—' ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Table -->
            <div class="pane" id="ow-table">
                <?php if (empty($all_parking)): ?>
                    <div class="empty-state"><i class="fas fa-list"></i>No parking records.</div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th><th>Slot</th><th>Flat / Wing</th>
                                    <th>Member</th><th>Type</th>
                                    <th>Vehicle No.</th><th>Allocated On</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($all_parking as $i => $p):
                                $mine = ((int)$p->owner_id === (int)($sess['user_id'] ?? 0));
                            ?>
                                <tr style="<?= $mine ? 'background:#fffbeb;' : '' ?>">
                                    <td style="color:var(--text-light);font-size:.75rem;"><?= $i+1 ?></td>
                                    <td>
                                        <strong><?= html_escape($p->slot_number) ?></strong>
                                        <?php if ($mine): ?><span class="you-inline">YOU</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- ★ FLAT + WING ★ -->
                                        <?php if (!empty($p->flat_no)): ?>
                                            <div class="flat-cell">
                                                <span class="flat-chip">
                                                    <i class="fas fa-door-closed"></i>
                                                    <?= html_escape($p->flat_no) ?>
                                                </span>
                                                <?php if (!empty($p->wing_name)): ?>
                                                    <span class="wing-chip">
                                                        <i class="fas fa-layer-group"></i>
                                                        <?= html_escape($p->wing_name) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:var(--text-light);font-size:.78rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= html_escape($p->owner_name ?? '—') ?>
                                        <?php if (!empty($p->owner_type)): ?>
                                            <span class="badge <?= $p->owner_type === 'owner' ? 'b-owner' : 'b-ten' ?>"><?= ucfirst($p->owner_type) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $p->vehicle_type === '4-Wheeler' ? 'b-4w' : 'b-2w' ?>">
                                            <?= $p->vehicle_type === '4-Wheeler' ? '🚗 4W' : '🛵 2W' ?>
                                        </span>
                                    </td>
                                    <td><?= html_escape($p->vehicle_number ?? '—') ?></td>
                                    <td style="font-size:.78rem;white-space:nowrap;"><?= date('d M Y', strtotime($p->allocated_at)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.main -->

<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
/* Tab switcher */
document.querySelectorAll('.park-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        const id   = btn.dataset.tab;
        const wrap = btn.closest('.card-body');
        wrap.querySelectorAll('.park-tab').forEach(b => b.classList.remove('on'));
        wrap.querySelectorAll('.pane').forEach(p => p.classList.remove('on'));
        btn.classList.add('on');
        document.getElementById(id).classList.add('on');
    });
});

/* Revoke confirm */
document.querySelectorAll('.revoke-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm('Revoke this parking assignment? This cannot be undone.')) e.preventDefault();
    });
});

/* Flash dismiss */
document.addEventListener('DOMContentLoaded', () => {
    const f = document.getElementById('flashMsg');
    if (f) setTimeout(() => { f.style.transition='opacity .5s'; f.style.opacity='0'; setTimeout(()=>f.remove(),500); }, 3500);
});
</script>
</body>
</html>
