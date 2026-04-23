<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5,viewport-fit=cover">
    <title>SocietyHub </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">

<style>
/* ═══ SOURCE BADGES ══════════════════════════════════════════════════════════ */
.src-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 9px; border-radius: 20px; font-size: .68rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .4px;
    white-space: nowrap;
}
.src-badge.maintenance { background: rgba(41,128,185,.12); color: #2980b9; }
.src-badge.event       { background: rgba(142,68,173,.12); color: #8e44ad; }
.src-badge.booking     { background: rgba(211,84,0,.12);   color: #d35400; }
.src-badge.penalty     { background: rgba(231,76,60,.12);  color: #c0392b; }

/* ═══ STATUS BADGES ══════════════════════════════════════════════════════════ */
.status-badge          { display: inline-block; padding: 3px 11px; border-radius: 20px; font-size: .73rem; font-weight: 600; }
.status-badge.paid     { background: rgba(39,174,96,.12); color: #27ae60; }
.status-badge.pending  { background: rgba(243,156,18,.12); color: #e67e22; }
.status-badge.overdue  { background: rgba(231,76,60,.12); color: #e74c3c; }
.status-badge.waived   { background: rgba(52,152,219,.12); color: #2980b9; }

/* ═══ SOURCE FILTER TABS ═════════════════════════════════════════════════════ */
.source-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
.tab-btn {
    padding: 6px 16px; border-radius: 30px; border: 1.5px solid var(--border, #ddd);
    font-size: .8rem; font-weight: 600; cursor: pointer; background: transparent;
    color: var(--text-light, #888); transition: all .18s;
}
.tab-btn.active,
.tab-btn:hover { border-color: var(--primary, #3498db); color: var(--primary, #3498db); background: rgba(52,152,219,.08); }
.tab-count {
    display: inline-block; margin-left: 5px; background: var(--primary, #3498db);
    color: #fff; border-radius: 10px; padding: 0 7px; font-size: .67rem;
}

/* ═══ PAYMENT AVATAR ═════════════════════════════════════════════════════════ */
.pay-avatar {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary, #3498db), #2980b9);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: .85rem;
}
.pay-info         { display: flex; align-items: center; gap: 10px; }
.pay-details h4   { font-size: .88rem; font-weight: 600; margin: 0 0 2px; }
.pay-details span { font-size: .72rem; color: var(--text-light, #888); }

/* ═══ HISTORY MODAL TIMELINE ════════════════════════════════════════════════ */
.history-item {
    display: flex; gap: 14px; padding: 12px 0;
    border-bottom: 1px solid var(--border, #eee);
    align-items: flex-start;
}
.history-item:last-child { border-bottom: none; }
.h-dot {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; color: #fff;
}
.h-dot.maintenance { background: #2980b9; }
.h-dot.event       { background: #8e44ad; }
.h-dot.booking     { background: #d35400; }
.h-dot.penalty     { background: #c0392b; }
.h-body h4  { font-size: .88rem; font-weight: 700; margin: 0 0 3px; }
.h-body p   { font-size: .76rem; color: var(--text-light, #888); margin: 0; }
.h-amount   { margin-left: auto; font-weight: 800; font-size: .98rem; white-space: nowrap; color: #27ae60; }

/* ═══ EMPTY STATE ════════════════════════════════════════════════════════════ */
.empty-state { text-align: center; padding: 40px 20px; color: var(--text-light, #888); }
.empty-state i { display: block; font-size: 2.2rem; margin-bottom: 10px; opacity: .35; }
.management-card {
			background: var(--card-bg);
			border-radius: 20px;
			padding: 20px;
			border: 1px solid var(--border);
			width: 100%;
		}
</style>

<body>
<div class="overlay" id="overlay"></div>
<?php $activePage = 'payments'; ?>
<?php include('sidebar.php') ?>

<div class="main" id="main">

    <!-- ── STATS ──────────────────────────────────────────────────────────── -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
                <h4>Total Collected</h4>
                <h2>₹<?= number_format($stats['total_collected'], 0, '.', ',') ?></h2>
                <div class="stat-trend trend-up">
                    <i class="fas fa-check-circle"></i>
                    <?= $stats['paid_count'] ?> transactions
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h4>Pending</h4>
                <h2>₹<?= number_format($stats['pending_amount'], 0, '.', ',') ?></h2>
                <div class="stat-trend" style="color:var(--warning,#e67e22)">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $stats['pending_count'] ?> invoices
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <h4>Overdue / Penalties</h4>
                <h2>₹<?= number_format($stats['overdue_amount'], 0, '.', ',') ?></h2>
                <div class="stat-trend" style="color:var(--danger,#e74c3c)">
                    <i class="fas fa-arrow-down"></i>
                    <?= $stats['overdue_count'] ?> records
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-percent"></i></div>
            <div class="stat-info">
                <h4>Collection Rate</h4>
                <h2><?= $stats['collection_rate'] ?>%</h2>
                <div class="stat-trend">
                    <i class="fas fa-chart-line"></i> All sources combined
                </div>
            </div>
        </div>
    </div>

    <!-- ── CHART ──────────────────────────────────────────────────────────── -->
    <!-- <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-bar"></i> Payment Overview</h3>
            <span class="chart-period">Last 6 Months · All Sources</span>
        </div>
        <div class="chart-container">
            <canvas id="paymentChart"></canvas>
        </div>
    </div> -->

    <!-- ── FILTERS + TABS ─────────────────────────────────────────────────── -->
    <div class="filter-section" style="flex-direction:column; gap:12px;">
        <!-- Source filter tabs -->
        <div class="source-tabs">
            <button class="tab-btn active" data-src="">
                <i class="fas fa-layer-group"></i> All
                <span class="tab-count"><?= count($payments) ?></span>
            </button>
            <button class="tab-btn" data-src="maintenance">
                <i class="fas fa-home"></i> Maintenance
                <span class="tab-count"><?= count(array_filter($payments, fn($p) => $p['source_type']==='maintenance')) ?></span>
            </button>
            <button class="tab-btn" data-src="event">
                <i class="fas fa-calendar-alt"></i> Events
                <span class="tab-count"><?= count(array_filter($payments, fn($p) => $p['source_type']==='event')) ?></span>
            </button>
            <button class="tab-btn" data-src="booking">
                <i class="fas fa-bookmark"></i> Bookings
                <span class="tab-count"><?= count(array_filter($payments, fn($p) => $p['source_type']==='booking')) ?></span>
            </button>
            <button class="tab-btn" data-src="penalty">
                <i class="fas fa-gavel"></i> Penalties
                <span class="tab-count"><?= count(array_filter($payments, fn($p) => $p['source_type']==='penalty')) ?></span>
            </button>
        </div>
        <!-- Filter row -->
        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Status</label>
                <select id="statusFilter" class="filter-select" onchange="applyFilters()">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="waived">Waived</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> Month</label>
                <select id="monthFilter" class="filter-select" onchange="applyFilters()">
                    <option value="">All Months</option>
                    <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"><?= date('F',mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="search-box" style="flex:1;min-width:220px;">
                <i class="fas fa-search"></i>
                <input type="text" id="srchBox" placeholder="Invoice, resident, flat…" oninput="applyFilters()">
            </div>
        </div>
    </div>

    <!-- ── TABLE ──────────────────────────────────────────────────────────── -->
    <div class="table-section">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Payment Transactions</h3>
            <div class="table-actions">
                <button class="btn-sm btn-outline" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="page-actions">
                <button class="btn btn-outline" onclick="exportCSV()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus-circle"></i> Record Payment
                </button>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="paymentsTable">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Source</th>
                        <th>Resident</th>
                        <th>Flat</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Paid On</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tBody"></tbody>
            </table>
        </div>
        <div class="pagination" id="pgWrap"></div>
    </div>

    <!-- ── RECENT TRANSACTIONS ────────────────────────────────────────────── -->
		<div class="management-card" style="margin-bottom:30px;">
        <div class="section-header">
			<div class="member-list">
            <h3><i class="fas fa-clock"></i> Recent Transactions</h3>
            <a href="#" class="view-all" onclick="showAll();return false;">View All →</a>
        </div>

        <div class="member-list" id="recentList"></div>
		
		</div>
    </div>
</div><!-- /main -->

<!-- ═══════════════════════════════════════ MODALS ═══════════════════════════ -->

<!-- Add / Edit maintenance payment -->
<div class="modal" id="payFormModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-credit-card"></i> <span id="fmTitle">Record Payment</span></h3>
            <span class="modal-close" onclick="closeModal('payFormModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="payForm" onsubmit="return false;">
                <!-- <input type="hidden" id="editId"> -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Resident *</label>
                        <select class="form-control" id="fUser" required onchange="fillFlat(this)">
                            <option value="">Select Resident</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"
                                    data-flat="<?= htmlspecialchars($u['flat_no'] ?? '') ?>"
                                    data-type="<?= htmlspecialchars($u['member_type'] ?? '') ?>">
                                <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['flat_no'] ?? '-') ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Flat</label>
                        <input type="text" class="form-control" id="fFlat" placeholder="Auto-filled" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Payment Type *</label>
                        <select class="form-control" id="fType" required>
                            <option value="maintenance">Maintenance</option>
                            <option value="penalty">Penalty</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (₹) *</label>
                        <input type="number" class="form-control" id="fAmount" placeholder="e.g. 2500" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Month *</label>
                        <select class="form-control" id="fMonth" required>
                            <?php for ($m=1;$m<=12;$m++): ?>
                            <option value="<?= date('F',mktime(0,0,0,$m,1)) ?>"
                                    <?= (int)date('n') === $m ? 'selected' : '' ?>>
                                <?= date('F',mktime(0,0,0,$m,1)) ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year *</label>
                        <input type="number" class="form-control" id="fYear"
                               value="<?= date('Y') ?>" min="2020" max="2099" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" class="form-control" id="fDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select class="form-control" id="fStatus" required>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Razorpay Order ID</label>
                        <input type="text" class="form-control" id="fOrderId" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label>Razorpay Payment ID</label>
                        <input type="text" class="form-control" id="fPaymentId" placeholder="Optional">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('payFormModal')"><i class="fas fa-times"></i> Cancel</button>
            <button class="btn btn-primary" onclick="savePayment()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<!-- View Payment Detail -->
<div class="modal" id="viewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-invoice"></i> Payment Details</h3>
            <span class="modal-close" onclick="closeModal('viewModal')">&times;</span>
        </div>
        <div class="modal-body" id="viewBody"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewModal')"><i class="fas fa-times"></i> Close</button>
            <!-- <button id="editFromViewBtn"></button> -->
            <button class="btn btn-primary" id="historyBtn" onclick="showUserHistory()">
                <i class="fas fa-history"></i> Full History
            </button>
        </div>
    </div>
</div>

<!-- Transaction History -->
<div class="modal" id="historyModal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> History – <span id="hName"></span></h3>
            <span class="modal-close" onclick="closeModal('historyModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;" id="hFilterBtns">
                <button class="tab-btn active" data-hsrc="" onclick="filterHistory(this,'')">All</button>
                <button class="tab-btn" data-hsrc="maintenance" onclick="filterHistory(this,'maintenance')"><i class="fas fa-home"></i> Maintenance</button>
                <button class="tab-btn" data-hsrc="event"       onclick="filterHistory(this,'event')"><i class="fas fa-calendar-alt"></i> Events</button>
                <button class="tab-btn" data-hsrc="booking"     onclick="filterHistory(this,'booking')"><i class="fas fa-bookmark"></i> Bookings</button>
                <button class="tab-btn" data-hsrc="penalty"     onclick="filterHistory(this,'penalty')"><i class="fas fa-gavel"></i> Penalties</button>
            </div>
            <div id="hList"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('historyModal')"><i class="fas fa-times"></i> Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirm -->
<div class="modal" id="deleteModal">
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle" style="color:var(--danger,#e74c3c)"></i> Confirm Delete</h3>
            <span class="modal-close" onclick="closeModal('deleteModal')">&times;</span>
        </div>
        <div class="modal-body" style="text-align:center; padding:24px;">
            <i class="fas fa-trash" style="font-size:2.8rem; color:var(--danger,#e74c3c); display:block; margin-bottom:12px;"></i>
            Delete this payment record? <br>
            <small style="color:var(--danger,#e74c3c)">This cannot be undone.</small>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn btn-primary" style="background:var(--danger,#e74c3c)" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════ SCRIPTS ══════════════════════════ -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
// ── CONFIG & DATA BRIDGE ──────────────────────────────────────────────────────
const BASE    = '<?= base_url() ?>';
let allData   = <?= json_encode($payments)  ?>;  // PHP → JS
let chartData = <?= json_encode($chart)     ?>;
let histData  = [];            // current user's history

// ── STATE ─────────────────────────────────────────────────────────────────────
let activeSrc   = '';
let curViewId   = null;        // payments.id of viewed record
let curUserId   = null;        // user_id for history
let curUserName = '';
let deleteId    = null;
let curPage     = 1;
const PER_PAGE  = 15;

// ── ICON MAP ──────────────────────────────────────────────────────────────────
const SRC_ICON  = { maintenance:'home', event:'calendar-alt', booking:'bookmark', penalty:'gavel' };

// ── HELPERS ───────────────────────────────────────────────────────────────────
const fmt     = n  => '₹' + Number(n).toLocaleString('en-IN');
const fmtDate = ds => ds ? new Date(ds).toLocaleDateString('en-IN',{day:'2-digit',month:'2-digit',year:'numeric'}) : '–';
const fmtLong = ds => ds ? new Date(ds).toLocaleDateString('en-IN',{day:'numeric',month:'long',year:'numeric'})   : 'Not paid';
const initials= n  => n.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);
const cap     = s  => s ? s.charAt(0).toUpperCase()+s.slice(1) : '';

// ── CHART ─────────────────────────────────────────────────────────────────────
function initChart() {
    const cv = document.getElementById('paymentChart');
    if (!cv) return;
    if (window._chart instanceof Chart) window._chart.destroy();
    window._chart = new Chart(cv.getContext('2d'), {
        type: 'bar',
        data: {
            labels: chartData.map(r=>r.label),
            datasets: [
                { label:'Paid',    data:chartData.map(r=>r.paid),
                  backgroundColor:'rgba(39,174,96,.85)', borderColor:'#27ae60', borderWidth:1, borderRadius:8, barPercentage:.6 },
                { label:'Pending', data:chartData.map(r=>r.pending),
                  backgroundColor:'rgba(243,156,18,.85)', borderColor:'#f39c12', borderWidth:1, borderRadius:8, barPercentage:.6 }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{
                legend:{ position:'top', labels:{ usePointStyle:true, boxWidth:8, padding:18 } },
                tooltip:{ mode:'index', intersect:false, callbacks:{ label:ctx=>`${ctx.dataset.label}: ${fmt(ctx.parsed.y)}` } }
            },
            scales:{
                y:{ beginAtZero:true, ticks:{ callback:v=>'₹'+(v/1000).toFixed(0)+'k' }, title:{ display:true, text:'Amount (₹)' } },
                x:{ grid:{ display:false } }
            }
        }
    });
}

// ── FILTER ────────────────────────────────────────────────────────────────────
function filtered() {
    const srch   = document.getElementById('srchBox').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const month  = document.getElementById('monthFilter').value;

    return allData.filter(p => {
        if (activeSrc && p.source_type !== activeSrc) return false;
        if (status && p.status !== status)             return false;
        if (srch && ![p.invoice_id,p.resident_name,p.flat,p.payment_type,p.description]
                       .some(f=>(f||'').toLowerCase().includes(srch))) return false;
        if (month) {
            const ds = p.payment_date || p.due_date;
            if (!ds) return false;
            const m = String(new Date(ds).getMonth()+1).padStart(2,'0');
            if (m !== month) return false;
        }
        return true;
    });
}

function applyFilters() { curPage = 1; renderTable(); }

// ── TABLE ─────────────────────────────────────────────────────────────────────
function renderTable() {
    const data  = filtered();
    const tbody = document.getElementById('tBody');
    const start = (curPage-1)*PER_PAGE;
    const page  = data.slice(start, start+PER_PAGE);

    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state"><i class="fas fa-search"></i>No matching records.</div></td></tr>`;
        renderPagination(0); return;
    }

    tbody.innerHTML = page.map(p => `
        <tr>
            <td><strong>${p.invoice_id}</strong></td>
            <td><span class="src-badge ${p.source_type}"><i class="fas fa-${SRC_ICON[p.source_type]||'circle'}"></i> ${cap(p.source_type)}</span></td>
            <td>
                <div class="pay-info">
                    <div class="pay-avatar">${initials(p.resident_name)}</div>
                    <div class="pay-details">
                        <h4>${p.resident_name}</h4>
                        <span>UID: ${p.resident_id}</span>
                    </div>
                </div>
            </td>
            <td>${p.flat}</td>
            <td style="font-size:.82rem; max-width:150px;">${p.payment_type}</td>
            <td><strong>${fmt(p.amount)}</strong></td>
            <td>${fmtDate(p.payment_date)}</td>
            <td>${fmtDate(p.due_date)}</td>
            <td><span class="status-badge ${p.status}">${cap(p.status)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" title="View"    onclick="viewPayment('${p.invoice_id}')"><i class="fas fa-eye"></i></button>
               
                    <button class="btn-icon" title="History" onclick="openHistoryFor(${p.resident_id},'${p.resident_name.replace(/'/g,"\\'")}')"><i class="fas fa-history"></i></button>
                </div>
            </td>
        </tr>`).join('');

    renderPagination(data.length);
}

function renderPagination(total) {
    const wrap  = document.getElementById('pgWrap');
    const pages = Math.ceil(total/PER_PAGE);
    if (pages <= 1) { wrap.innerHTML = ''; return; }
    let html = `<span class="page-item" onclick="goPage(${curPage-1})"><i class="fas fa-chevron-left"></i></span>`;
    for (let i=1;i<=pages;i++) html += `<span class="page-item ${i===curPage?'active':''}" onclick="goPage(${i})">${i}</span>`;
    html += `<span class="page-item" onclick="goPage(${curPage+1})"><i class="fas fa-chevron-right"></i></span>`;
    wrap.innerHTML = html;
}

function goPage(n) {
    const max = Math.ceil(filtered().length/PER_PAGE);
    if (n<1||n>max) return;
    curPage=n; renderTable();
}

// ── SOURCE TABS ───────────────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn[data-src]').forEach(btn=>btn.addEventListener('click',function(){
    document.querySelectorAll('.tab-btn[data-src]').forEach(b=>b.classList.remove('active'));
    this.classList.add('active');
    activeSrc = this.dataset.src;
    applyFilters();
}));

// ── RECENT TRANSACTIONS ───────────────────────────────────────────────────────
function renderRecent() {
    const list = document.getElementById('recentList');
    const rec  = [...allData].filter(p=>p.payment_date)
                             .sort((a,b)=>new Date(b.payment_date)-new Date(a.payment_date))
                             .slice(0,5);
    if (!rec.length) { list.innerHTML='<div style="padding:20px;text-align:center;color:var(--text-light)">No transactions</div>'; return; }
    list.innerHTML = rec.map(p=>`
        <div class="member-item">
            <div class="member-info">
                <div class="member-avatar">${initials(p.resident_name)}</div>
                <div class="member-details">
                    <h4>${p.resident_name} <span class="src-badge ${p.source_type}" style="font-size:.62rem">${cap(p.source_type)}</span></h4>
                    <span>${p.payment_type} · ${p.flat}</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-weight:800;color:var(--success,#27ae60)">${fmt(p.amount)}</span>
                <span style="font-size:.73rem;color:var(--text-light)">${fmtDate(p.payment_date)}</span>
                <button class="btn-icon" onclick="openHistoryFor(${p.resident_id},'${p.resident_name.replace(/'/g,"\\'")}')">
                    <i class="fas fa-history"></i>
                </button>
            </div>
        </div>`).join('');
}

function showAll() {
    activeSrc='';
    document.querySelectorAll('.tab-btn[data-src]').forEach(b=>b.classList.remove('active'));
    document.querySelector('[data-src=""]').classList.add('active');
    document.getElementById('statusFilter').value='';
    document.getElementById('monthFilter').value='';
    document.getElementById('srchBox').value='';
    applyFilters();
    window.scrollTo({top:0,behavior:'smooth'});
}

// ── VIEW PAYMENT ──────────────────────────────────────────────────────────────
const SRC_COLOR = { maintenance:'var(--primary,#3498db)', event:'#8e44ad', booking:'#d35400', penalty:'#c0392b' };

function viewPayment(invId) {
    const p = allData.find(x=>x.invoice_id===invId);
    if (!p) return;
    curViewId   = p.id;
    curUserId   = p.resident_id;
    curUserName = p.resident_name;

    // document.getElementById('editFromViewBtn').style.display = p.source_type==='maintenance' ? '' : 'none';

    document.getElementById('viewBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:18px;margin-bottom:22px;">
            <div style="width:66px;height:66px;border-radius:14px;background:${SRC_COLOR[p.source_type]};
                 display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;">
                <i class="fas fa-${SRC_ICON[p.source_type]}"></i>
            </div>
            <div>
                <h2 style="margin-bottom:6px;">${p.invoice_id}</h2>
                <span class="src-badge ${p.source_type}" style="margin-right:8px;">${cap(p.source_type)}</span>
                <span class="status-badge ${p.status}">${cap(p.status)}</span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div style="background:var(--light-bg,#f5f7fa);padding:16px;border-radius:12px;">
                <h4 style="margin-bottom:10px;"><i class="fas fa-user" style="color:var(--primary)"></i> Resident</h4>
                <p><b>Name:</b> ${p.resident_name}</p>
                <p><b>Flat:</b> ${p.flat}</p>
                <p><b>UID:</b> ${p.resident_id}</p>
            </div>
            <div style="background:var(--light-bg,#f5f7fa);padding:16px;border-radius:12px;">
                <h4 style="margin-bottom:10px;"><i class="fas fa-receipt" style="color:var(--primary)"></i> Payment</h4>
                <p><b>Type:</b> ${p.payment_type}</p>
                <p><b>Amount:</b> <span style="font-size:1.1rem;font-weight:800;color:#27ae60;">${fmt(p.amount)}</span></p>
                <p><b>Paid on:</b> ${fmtLong(p.payment_date)}</p>
                <p><b>Due date:</b> ${fmtLong(p.due_date)}</p>
            </div>
            <div style="background:var(--light-bg,#f5f7fa);padding:16px;border-radius:12px;">
                <h4 style="margin-bottom:10px;"><i class="fas fa-credit-card" style="color:var(--primary)"></i> Transaction</h4>
                <p><b>Method:</b> ${p.payment_method||'–'}</p>
                <p><b>Ref ID:</b> ${p.transaction_id||'–'}</p>
            </div>
            <div style="background:var(--light-bg,#f5f7fa);padding:16px;border-radius:12px;">
                <h4 style="margin-bottom:10px;"><i class="fas fa-file-alt" style="color:var(--primary)"></i> Description</h4>
                <p style="color:var(--text-light,#888);">${p.description||'–'}</p>
            </div>
        </div>`;

    openModal('viewModal');
}

// function editFromView() {
//     closeModal('viewModal');
//     const p = allData.find(x=>x.id==curViewId && x.source_type==='maintenance');
//     if (p) editPayment(p.invoice_id);
// }

function showUserHistory() {
    closeModal('viewModal');
    openHistoryFor(curUserId, curUserName);
}

// ── HISTORY ───────────────────────────────────────────────────────────────────
function openHistoryFor(uid, uname) {
    curUserId   = uid;
    curUserName = uname;
    document.getElementById('hName').textContent = uname;
    histData = allData.filter(p=>String(p.resident_id)===String(uid));
    renderHistory('');
    // reset tab
    document.querySelectorAll('#hFilterBtns .tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelector('#hFilterBtns [data-hsrc=""]').classList.add('active');
    openModal('historyModal');
}

function filterHistory(btn, src) {
    document.querySelectorAll('#hFilterBtns .tab-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    renderHistory(src);
}

function renderHistory(src) {
    const list  = document.getElementById('hList');
    const items = src ? histData.filter(p=>p.source_type===src) : histData;
    if (!items.length) {
        list.innerHTML='<div class="empty-state"><i class="fas fa-inbox"></i>No transactions for this filter.</div>';
        return;
    }
    list.innerHTML = items.map(p=>`
        <div class="history-item">
            <div class="h-dot ${p.source_type}"><i class="fas fa-${SRC_ICON[p.source_type]||'circle'}"></i></div>
            <div class="h-body">
                <h4>${p.payment_type}</h4>
                <p>${p.invoice_id}
                   &nbsp;·&nbsp; <span class="status-badge ${p.status}" style="font-size:.65rem;padding:1px 7px;">${cap(p.status)}</span>
                   &nbsp;·&nbsp; ${fmtDate(p.payment_date||p.due_date)}
                </p>
                ${p.description ? `<p style="margin-top:3px;font-style:italic;font-size:.74rem;">${p.description}</p>` : ''}
            </div>
            <span class="h-amount">${fmt(p.amount)}</span>
        </div>`).join('');
}

// ── ADD / EDIT ─────────────────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('fmTitle').textContent='Record Payment';
    document.getElementById('payForm').reset();
    // document.getElementById('editId').value='';
    document.getElementById('fDate').value = new Date().toISOString().split('T')[0];
    openModal('payFormModal');
}

function fillFlat(sel) {
    document.getElementById('fFlat').value = sel.options[sel.selectedIndex]?.dataset.flat||'';
}

// function editPayment(invId) {
//     const p = allData.find(x=>x.invoice_id===invId && x.source_type==='maintenance');
//     if (!p) { toast('Only maintenance payments can be edited here.','info'); return; }
//     document.getElementById('fmTitle').textContent = 'Edit Payment';
//     document.getElementById('editId').value  = p.id;
//     document.getElementById('fUser').value   = p.resident_id;
//     document.getElementById('fFlat').value   = p.flat;
//     document.getElementById('fType').value   = 'maintenance';
//     document.getElementById('fAmount').value = p.amount;
//     document.getElementById('fDate').value   = p.payment_date;
//     document.getElementById('fStatus').value = p.status;
//     openModal('payFormModal');
// }

function savePayment() {
    const uid    = document.getElementById('fUser').value;
    const amount = document.getElementById('fAmount').value;
    const month  = document.getElementById('fMonth').value;
    const year   = document.getElementById('fYear').value;
    const status = document.getElementById('fStatus').value;
    const type   = document.getElementById('fType').value;

    if (!uid||!amount||!month||!year) { toast('Please fill all required fields.','error'); return; }

    const form = new FormData();
    form.append('user_id',      uid);
    form.append('amount',       amount);
    form.append('payment_type', type);
    form.append('month',        month);
    form.append('year',         year);
    form.append('status',       status);
    form.append('payment_date', document.getElementById('fDate').value);
    form.append('order_id',     document.getElementById('fOrderId').value);
    form.append('payment_id',   document.getElementById('fPaymentId').value);

    // const id  = document.getElementById('editId').value;
    const url = id
        ? BASE+'payment_controllerr/edit_payment/'+id
        : BASE+'payment_controllerr/add_payment';

    fetch(url,{method:'POST',body:form})
        .then(r=>r.json())
        .then(res=>{
            toast(res.message, res.success?'success':'error');
            if (res.success){ closeModal('payFormModal'); refreshData(); }
        })
        .catch(()=>toast('Network error','error'));
}

// ── DELETE ────────────────────────────────────────────────────────────────────
function deletePayment(id) { deleteId=id; openModal('deleteModal'); }

function confirmDelete() {
    if (!deleteId) return;
    fetch(BASE+'payment_controllerr/delete_payment/'+deleteId,{method:'POST'})
        .then(r=>r.json())
        .then(res=>{
            toast(res.message, res.success?'info':'error');
            closeModal('deleteModal');
            if (res.success) refreshData();
        })
        .catch(()=>toast('Network error','error'));
}

// ── REFRESH via AJAX ──────────────────────────────────────────────────────────
function refreshData() {
    fetch(BASE+'payment_controllerr/get_payments_ajax',{method:'POST'})
        .then(r=>r.json())
        .then(res=>{
            if (res.success){ allData=res.data; applyFilters(); renderRecent(); toast('Refreshed!','success'); }
        })
        .catch(()=>{ applyFilters(); renderRecent(); });
}

function exportCSV() { window.location.href=BASE+'payment_controllerr/export_payments'; }

// ── TOAST ─────────────────────────────────────────────────────────────────────
function toast(msg, type='success') {
    const icons={success:'check-circle',error:'exclamation-circle',info:'info-circle'};
    const el=document.createElement('div');
    el.className=`notification ${type}`;
    el.innerHTML=`<i class="fas fa-${icons[type]||'info-circle'}"></i><span>${msg}</span>`;
    document.body.appendChild(el);
    setTimeout(()=>{ el.style.animation='slideOut .3s ease'; setTimeout(()=>el.remove(),300); },3000);
}

function openModal(id)  { document.getElementById(id)?.classList.add('active'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('active'); }

// ── INIT ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded',()=>{
    renderTable();
    renderRecent();
    setTimeout(initChart, 150);

    document.querySelectorAll('.modal').forEach(m=>{
        m.addEventListener('click',e=>{ if(e.target===m) closeModal(m.id); });
    });
    document.addEventListener('keydown',e=>{
        if(e.key==='Escape') document.querySelectorAll('.modal.active').forEach(m=>closeModal(m.id));
    });

    const cb = document.getElementById('collapseBtn');
    if (cb) cb.addEventListener('click',()=>{
        ['sidebar','header','main'].forEach(id=>document.getElementById(id)?.classList.toggle('collapsed'));
    });
});

window.addEventListener('resize',()=>{
    clearTimeout(window._cr);
    window._cr=setTimeout(()=>window._chart?.resize(),250);
});
</script>
</body>
</html>
