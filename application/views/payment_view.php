<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title>SocietyHub · Payment Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">

<style>
/* ═══════════════════════════════════════════════════════════════
   PAYMENT PAGE – extended styles
   ═══════════════════════════════════════════════════════════════ */

/* ── Source-type chips ── */
.source-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.source-badge.maintenance { background: rgba(52,152,219,0.12); color: #2980b9; }
.source-badge.event       { background: rgba(155,89,182,0.12); color: #8e44ad; }
.source-badge.booking     { background: rgba(230,126,34,0.12); color: #d35400; }

/* ── Status badges ── */
.status-badge        { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.status-badge.paid   { background: rgba(39,174,96,0.12);  color: #27ae60; }
.status-badge.pending{ background: rgba(243,156,18,0.12); color: #f39c12; }
.status-badge.overdue{ background: rgba(231,76,60,0.12);  color: #e74c3c; }
.status-badge.refunded{background: rgba(52,152,219,0.12); color: #2980b9; }

/* ── Payment avatar ── */
.payment-avatar {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 0.9rem; flex-shrink: 0;
}
.payment-info { display: flex; align-items: center; gap: 10px; }
.payment-details h4 { font-weight: 600; font-size: 0.9rem; margin-bottom: 2px; }
.payment-details span { font-size: 0.72rem; color: var(--text-light); }

/* ── Tabs ── */
.source-tabs {
    display: flex; gap: 8px; flex-wrap: wrap;
    margin-bottom: 4px;
}
.tab-btn {
    padding: 7px 18px; border-radius: 30px; border: 1.5px solid transparent;
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    transition: all .2s;
    background: var(--light-bg); color: var(--text-light);
}
.tab-btn.active, .tab-btn:hover { border-color: var(--primary); color: var(--primary); background: rgba(var(--primary-rgb),.08); }
.tab-btn .tab-count {
    display: inline-block; margin-left: 6px; background: var(--primary);
    color: #fff; border-radius: 10px; padding: 1px 7px; font-size: 0.68rem;
}

/* ── History modal ── */
.history-timeline { display: flex; flex-direction: column; gap: 0; }
.history-item {
    display: flex; gap: 16px; align-items: flex-start;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
    position: relative;
}
.history-item:last-child { border-bottom: none; }
.history-dot {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; color: #fff; margin-top: 2px;
}
.history-dot.paid    { background: #27ae60; }
.history-dot.pending { background: #f39c12; }
.history-dot.overdue { background: #e74c3c; }
.history-dot.booking { background: #d35400; }
.history-dot.event   { background: #8e44ad; }
.history-body h4 { font-size: 0.9rem; font-weight: 700; margin-bottom: 3px; }
.history-body p  { font-size: 0.78rem; color: var(--text-light); margin: 0; }
.history-amount  { margin-left: auto; font-weight: 800; font-size: 1rem; white-space: nowrap; }
.history-amount.positive { color: var(--success); }

/* ── Empty state ── */
.empty-state { text-align: center; padding: 50px 20px; color: var(--text-light); }
.empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: .4; }

/* ── Resident search ── */
.resident-lookup-wrap { position: relative; }
.resident-lookup-wrap .lookup-dropdown {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 200;
    background: var(--card-bg); border: 1px solid var(--border);
    border-radius: 8px; max-height: 200px; overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
}
.lookup-option {
    padding: 10px 14px; cursor: pointer; font-size: 0.88rem;
    border-bottom: 1px solid var(--border);
    transition: background .15s;
}
.lookup-option:hover { background: var(--light-bg); }
</style>

<body>
<div class="overlay" id="overlay"></div>

<!-- SIDEBAR -->
<?php $activePage = 'payments'; ?>
<?php include('sidebar.php') ?>

<!-- HEADER -->
<div class="header" id="header">
    <div class="header-left">
        <i class="fas fa-bars hamburger" id="hamburger"></i>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="globalSearch" placeholder="Search payments...">
        </div>
    </div>
    <?php $this->load->view('header'); ?>
</div>

<!-- MAIN -->
<div class="main" id="main">

    <!-- ── STATS ── -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
                <h4>Total Collected</h4>
                <h2 id="totalCollected">₹<?= number_format($stats['total_collected']) ?></h2>
                <div class="stat-trend trend-up"><i class="fas fa-check-circle"></i> <?= $stats['paid_count'] ?> transactions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h4>Pending</h4>
                <h2 id="pendingAmount">₹<?= number_format($stats['pending_amount']) ?></h2>
                <div class="stat-trend" style="color:var(--warning)"><i class="fas fa-exclamation-circle"></i> <?= $stats['pending_count'] ?> invoices</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <h4>Overdue</h4>
                <h2 id="overdueAmount">₹<?= number_format($stats['overdue_amount']) ?></h2>
                <div class="stat-trend" style="color:var(--danger)"><i class="fas fa-arrow-down"></i> <?= $stats['overdue_count'] ?> invoices</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-percent"></i></div>
            <div class="stat-info">
                <h4>Collection Rate</h4>
                <h2 id="collectionRate"><?= $stats['collection_rate'] ?>%</h2>
                <div class="stat-trend"><i class="fas fa-chart-line"></i> All sources combined</div>
            </div>
        </div>
    </div>

    <!-- ── CHART ── -->
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-bar"></i> Payment Overview</h3>
            <span class="chart-period">Last 6 Months · All Sources</span>
        </div>
        <div class="chart-container">
            <canvas id="paymentChart"></canvas>
        </div>
    </div>

    <!-- ── SOURCE TABS + FILTERS ── -->
    <div class="filter-section" style="flex-direction:column; gap:14px;">
        <!-- Source Tabs -->
        <div class="source-tabs">
            <button class="tab-btn active" data-source="">
                <i class="fas fa-layer-group"></i> All Sources
                <span class="tab-count" id="countAll"><?= count($payments) ?></span>
            </button>
            <button class="tab-btn" data-source="maintenance">
                <i class="fas fa-home"></i> Maintenance
                <span class="tab-count" id="countMaintenance"><?= count(array_filter($payments, fn($p) => $p['source_type']==='maintenance')) ?></span>
            </button>
            <button class="tab-btn" data-source="event">
                <i class="fas fa-calendar-star"></i> Events
                <span class="tab-count" id="countEvent"><?= count(array_filter($payments, fn($p) => $p['source_type']==='event')) ?></span>
            </button>
            <button class="tab-btn" data-source="booking">
                <i class="fas fa-bookmark"></i> Bookings
                <span class="tab-count" id="countBooking"><?= count(array_filter($payments, fn($p) => $p['source_type']==='booking')) ?></span>
            </button>
        </div>
        <!-- Filters row -->
        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Status</label>
                <select id="statusFilter" class="filter-select" onchange="filterPayments()">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="overdue">Overdue</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> Month</label>
                <select id="monthFilter" class="filter-select" onchange="filterPayments()">
                    <option value="">All Months</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="search-box" style="flex:1; min-width:220px;">
                <i class="fas fa-search"></i>
                <input type="text" id="paymentSearch" placeholder="Search invoice, resident, flat…" onkeyup="filterPayments()">
            </div>
        </div>
    </div>

    <!-- ── PAYMENTS TABLE ── -->
    <div class="table-section">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Payment Transactions</h3>
            <div class="table-actions">
                <button class="btn-sm btn-outline" onclick="refreshTable()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="page-actions">
                <button class="btn btn-outline" onclick="exportPayments()">
                    <i class="fas fa-download"></i> Export
                </button>
                <button class="btn btn-primary" onclick="openAddPaymentModal()">
                    <i class="fas fa-plus-circle"></i> Record Payment
                </button>
            </div>
        </div>
        <div class="table-wrapper">
            <table id="paymentsTable">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Source</th>
                        <th>Resident</th>
                        <th>Flat</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody"></tbody>
            </table>
        </div>
        <div class="pagination" id="paginationWrap"></div>
    </div>

    <!-- ── RECENT TRANSACTIONS ── -->
    <div class="management-card">
        <div class="section-header">
            <h3><i class="fas fa-clock"></i> Recent Transactions</h3>
            <a href="#" class="view-all" onclick="viewAllTransactions(); return false;">View All →</a>
        </div>
        <div class="member-list" id="recentTransactionsList"></div>
    </div>

</div><!-- /main -->

<!-- ══════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════ -->

<!-- Add / Edit Payment -->
<div class="modal" id="paymentFormModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-credit-card"></i> <span id="formModalTitle">Record Payment</span></h3>
            <span class="modal-close" onclick="closeModal('paymentFormModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="paymentForm" onsubmit="return false;">
                <input type="hidden" id="paymentId">
                <div class="form-row">
                    <div class="form-group">
                        <label>Resident *</label>
                        <select class="form-control" id="residentId" required onchange="onResidentChange(this)">
                            <option value="">Select Resident</option>
                            <?php foreach ($residents as $r): ?>
                            <option value="<?= $r['id'] ?>" data-flat="<?= htmlspecialchars($r['flat_number']) ?>">
                                <?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['flat_number']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Flat Number</label>
                        <input type="text" class="form-control" id="flatNumber" placeholder="Auto-filled" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Payment Type *</label>
                        <select class="form-control" id="paymentType" required>
                            <option value="">Select Type</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Water Bill">Water Bill</option>
                            <option value="Electricity">Electricity</option>
                            <option value="Parking">Parking</option>
                            <option value="Amenity Booking">Amenity Booking</option>
                            <option value="Late Fee">Late Fee</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (₹) *</label>
                        <input type="number" class="form-control" id="amount" placeholder="Enter amount" required min="1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Payment Date *</label>
                        <input type="date" class="form-control" id="paymentDate" required>
                    </div>
                    <div class="form-group">
                        <label>Due Date *</label>
                        <input type="date" class="form-control" id="dueDate" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control" id="paymentMethod">
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="UPI">UPI</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction ID</label>
                        <input type="text" class="form-control" id="transactionId" placeholder="UTR / Cheque / Ref No.">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" id="description" rows="2" placeholder="Optional note"></textarea>
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select class="form-control" id="status" required>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('paymentFormModal')"><i class="fas fa-times"></i> Cancel</button>
            <button class="btn btn-primary" onclick="savePayment()"><i class="fas fa-save"></i> Save Payment</button>
        </div>
    </div>
</div>

<!-- View Payment -->
<div class="modal" id="viewPaymentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-invoice"></i> Payment Details</h3>
            <span class="modal-close" onclick="closeModal('viewPaymentModal')">&times;</span>
        </div>
        <div class="modal-body" id="viewPaymentBody"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewPaymentModal')"><i class="fas fa-times"></i> Close</button>
            <button class="btn btn-outline" id="editFromViewBtn" onclick="editFromView()"><i class="fas fa-edit"></i> Edit</button>
            <button class="btn btn-outline" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <button class="btn btn-primary" id="historyFromViewBtn" onclick="viewResidentHistory()">
                <i class="fas fa-history"></i> Full History
            </button>
        </div>
    </div>
</div>

<!-- Transaction History Modal -->
<div class="modal" id="historyModal">
    <div class="modal-content" style="max-width:640px;">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Transaction History – <span id="historyResidentName"></span></h3>
            <span class="modal-close" onclick="closeModal('historyModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
                <span id="historyBadgeMaintenance" class="source-badge maintenance" style="cursor:pointer" onclick="filterHistory('maintenance')">
                    <i class="fas fa-home"></i> Maintenance
                </span>
                <span id="historyBadgeEvent" class="source-badge event" style="cursor:pointer" onclick="filterHistory('event')">
                    <i class="fas fa-calendar-star"></i> Events
                </span>
                <span id="historyBadgeBooking" class="source-badge booking" style="cursor:pointer" onclick="filterHistory('booking')">
                    <i class="fas fa-bookmark"></i> Bookings
                </span>
                <span class="source-badge" style="cursor:pointer; background:var(--light-bg); color:var(--text-dark)" onclick="filterHistory('')">
                    <i class="fas fa-layer-group"></i> All
                </span>
            </div>
            <div class="history-timeline" id="historyList"></div>
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
            <h3><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Confirm Delete</h3>
            <span class="modal-close" onclick="closeModal('deleteModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p style="text-align:center; padding:20px;">
                <i class="fas fa-trash" style="font-size:3rem; color:var(--danger); display:block; margin-bottom:12px;"></i>
                Are you sure you want to delete this payment record?<br>
                <span style="color:var(--danger); font-size:0.85rem;">This action cannot be undone.</span>
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('deleteModal')"><i class="fas fa-times"></i> Cancel</button>
            <button class="btn btn-primary" style="background:var(--danger)" onclick="confirmDelete()"><i class="fas fa-trash"></i> Delete</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════════ -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
<script>
// ── PHP → JS data bridge ─────────────────────────────────────────────────────
const BASE_URL   = '<?= base_url() ?>';
let paymentData  = <?= json_encode($payments)  ?>;
let chartData    = <?= json_encode($chart)     ?>;

// ── State ────────────────────────────────────────────────────────────────────
let activeSource     = '';       // '' | 'maintenance' | 'event' | 'booking'
let currentPaymentId = null;
let deleteId         = null;
let historyAll       = [];       // full history for current resident
let currentPage      = 1;
const PAGE_SIZE      = 15;

// ── Helpers ──────────────────────────────────────────────────────────────────
const fmt = (n) => '₹' + Number(n).toLocaleString('en-IN');
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-IN', {day:'2-digit', month:'2-digit', year:'numeric'}) : '–';
const fmtDateLong = (d) => d ? new Date(d).toLocaleDateString('en-IN', {day:'numeric', month:'long', year:'numeric'}) : 'Not paid';
const initials = (name) => name.split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2);
const cap = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
const sourceIcon = { maintenance:'home', event:'calendar-alt', booking:'bookmark' };

// ── CHART ────────────────────────────────────────────────────────────────────
function initPaymentChart() {
    const canvas = document.getElementById('paymentChart');
    if (!canvas) return;
    if (window._payChart instanceof Chart) window._payChart.destroy();

    const labels  = chartData.map(r => r.label);
    const paid    = chartData.map(r => r.paid);
    const pending = chartData.map(r => r.pending);

    window._payChart = new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label:'Paid',            data:paid,    backgroundColor:'rgba(39,174,96,.85)',  borderColor:'#27ae60', borderWidth:1, borderRadius:8, barPercentage:.6 },
                { label:'Pending/Overdue', data:pending, backgroundColor:'rgba(243,156,18,.85)', borderColor:'#f39c12', borderWidth:1, borderRadius:8, barPercentage:.6 }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{
                legend:{ position:'top', labels:{ usePointStyle:true, boxWidth:8, padding:20 } },
                tooltip:{
                    mode:'index', intersect:false,
                    callbacks:{ label: ctx => `${ctx.dataset.label}: ${fmt(ctx.parsed.y)}` }
                }
            },
            scales:{
                y:{ beginAtZero:true, ticks:{ callback: v => '₹'+(v/1000).toFixed(0)+'k' }, title:{ display:true, text:'Amount (₹)' } },
                x:{ grid:{ display:false } }
            }
        }
    });
}

// ── TABLE ────────────────────────────────────────────────────────────────────
function getFilteredData() {
    const search = document.getElementById('paymentSearch')?.value.toLowerCase() || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const month  = document.getElementById('monthFilter')?.value || '';

    return paymentData.filter(p => {
        const matchSource = !activeSource || p.source_type === activeSource;
        const matchStatus = !status || p.status === status;
        const matchSearch = !search ||
            p.invoice_id.toLowerCase().includes(search) ||
            p.resident_name.toLowerCase().includes(search) ||
            p.flat.toLowerCase().includes(search) ||
            p.payment_type.toLowerCase().includes(search);
        let matchMonth = true;
        if (month) {
            const d = p.payment_date ? new Date(p.payment_date) : new Date(p.due_date);
            matchMonth = String(d.getMonth() + 1).padStart(2,'0') === month;
        }
        return matchSource && matchStatus && matchSearch && matchMonth;
    });
}

function renderTable(filtered) {
    const tbody = document.getElementById('paymentsTableBody');
    const start = (currentPage - 1) * PAGE_SIZE;
    const page  = filtered.slice(start, start + PAGE_SIZE);

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10"><div class="empty-state"><i class="fas fa-search"></i>No matching payments found.</div></td></tr>`;
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(p => `
        <tr>
            <td><strong>${p.invoice_id}</strong></td>
            <td>
                <span class="source-badge ${p.source_type}">
                    <i class="fas fa-${sourceIcon[p.source_type] || 'circle'}"></i>
                    ${cap(p.source_type)}
                </span>
            </td>
            <td>
                <div class="payment-info">
                    <div class="payment-avatar">${initials(p.resident_name)}</div>
                    <div class="payment-details">
                        <h4>${p.resident_name}</h4>
                        <span>ID: RES-${String(p.resident_id).padStart(3,'0')}</span>
                    </div>
                </div>
            </td>
            <td>${p.flat}</td>
            <td style="max-width:160px; font-size:.82rem;">${p.payment_type}</td>
            <td><strong>${fmt(p.amount)}</strong></td>
            <td>${fmtDate(p.payment_date)}</td>
            <td>${fmtDate(p.due_date)}</td>
            <td><span class="status-badge ${p.status}">${cap(p.status)}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" onclick="viewPayment('${p.invoice_id}')" title="View"><i class="fas fa-eye"></i></button>
                    ${p.source_type==='maintenance' ? `<button class="btn-icon" onclick="editPayment('${p.invoice_id}')" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon delete" onclick="deletePayment('${p.id}')" title="Delete"><i class="fas fa-trash"></i></button>` : ''}
                    <button class="btn-icon" onclick="viewResidentHistoryById(${p.resident_id}, '${p.resident_name}')" title="History"><i class="fas fa-history"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination(filtered.length);
}

function renderPagination(total) {
    const wrap  = document.getElementById('paginationWrap');
    const pages = Math.ceil(total / PAGE_SIZE);
    if (pages <= 1) { wrap.innerHTML = ''; return; }

    let html = `<span class="page-item" onclick="goPage(${currentPage-1})"><i class="fas fa-chevron-left"></i></span>`;
    for (let i = 1; i <= pages; i++) {
        html += `<span class="page-item ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</span>`;
    }
    html += `<span class="page-item" onclick="goPage(${currentPage+1})"><i class="fas fa-chevron-right"></i></span>`;
    wrap.innerHTML = html;
}

function goPage(n) {
    const total = Math.ceil(getFilteredData().length / PAGE_SIZE);
    if (n < 1 || n > total) return;
    currentPage = n;
    renderTable(getFilteredData());
}

function filterPayments() {
    currentPage = 1;
    renderTable(getFilteredData());
}

// ── SOURCE TABS ──────────────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeSource = this.dataset.source;
        filterPayments();
    });
});

// ── RECENT TRANSACTIONS ──────────────────────────────────────────────────────
function loadRecentTransactions() {
    const list = document.getElementById('recentTransactionsList');
    const recent = [...paymentData]
        .filter(p => p.status === 'paid' && p.payment_date)
        .sort((a,b) => new Date(b.payment_date) - new Date(a.payment_date))
        .slice(0, 5);

    if (!recent.length) {
        list.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light);">No recent transactions</div>';
        return;
    }

    list.innerHTML = recent.map(p => `
        <div class="member-item">
            <div class="member-info">
                <div class="member-avatar">${initials(p.resident_name)}</div>
                <div class="member-details">
                    <h4>${p.resident_name}
                        <span class="source-badge ${p.source_type}" style="margin-left:8px; font-size:.65rem;">
                            ${cap(p.source_type)}
                        </span>
                    </h4>
                    <span>${p.payment_type} · ${p.flat}</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:15px;">
                <span style="font-weight:700; color:var(--success);">${fmt(p.amount)}</span>
                <span style="font-size:.75rem; color:var(--text-light);">${fmtDate(p.payment_date)}</span>
                <button class="btn-icon" onclick="viewResidentHistoryById(${p.resident_id},'${p.resident_name}')" title="History">
                    <i class="fas fa-history"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// ── VIEW PAYMENT ─────────────────────────────────────────────────────────────
function viewPayment(invoiceId) {
    const p = paymentData.find(x => x.invoice_id === invoiceId);
    if (!p) return;
    currentPaymentId = p.id;

    const isEditable = p.source_type === 'maintenance';
    document.getElementById('editFromViewBtn').style.display = isEditable ? '' : 'none';

    const sourceColors = { maintenance:'var(--primary)', event:'#8e44ad', booking:'#d35400' };

    document.getElementById('viewPaymentBody').innerHTML = `
        <div style="display:flex; align-items:center; gap:20px; margin-bottom:24px;">
            <div style="width:72px; height:72px; border-radius:16px; background:${sourceColors[p.source_type]}; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.4rem; font-weight:700;">
                <i class="fas fa-${sourceIcon[p.source_type]}"></i>
            </div>
            <div>
                <h2 style="color:var(--text-dark); margin-bottom:6px;">${p.invoice_id}</h2>
                <span class="source-badge ${p.source_type}" style="margin-right:8px;">${cap(p.source_type)}</span>
                <span class="status-badge ${p.status}">${cap(p.status)}</span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div style="background:var(--light-bg); padding:18px; border-radius:12px;">
                <h4 style="margin-bottom:12px;"><i class="fas fa-user" style="color:var(--primary)"></i> Resident</h4>
                <p><strong>Name:</strong> ${p.resident_name}</p>
                <p><strong>Flat:</strong> ${p.flat}</p>
            </div>
            <div style="background:var(--light-bg); padding:18px; border-radius:12px;">
                <h4 style="margin-bottom:12px;"><i class="fas fa-receipt" style="color:var(--primary)"></i> Payment</h4>
                <p><strong>Type:</strong> ${p.payment_type}</p>
                <p><strong>Amount:</strong> <span style="font-size:1.15rem; font-weight:800; color:var(--success);">${fmt(p.amount)}</span></p>
                <p><strong>Date:</strong> ${fmtDateLong(p.payment_date)}</p>
                <p><strong>Due:</strong> ${fmtDateLong(p.due_date)}</p>
            </div>
            <div style="background:var(--light-bg); padding:18px; border-radius:12px;">
                <h4 style="margin-bottom:12px;"><i class="fas fa-credit-card" style="color:var(--primary)"></i> Transaction</h4>
                <p><strong>Method:</strong> ${p.payment_method || '–'}</p>
                <p><strong>Ref ID:</strong> ${p.transaction_id || '–'}</p>
            </div>
            <div style="background:var(--light-bg); padding:18px; border-radius:12px;">
                <h4 style="margin-bottom:12px;"><i class="fas fa-file-alt" style="color:var(--primary)"></i> Description</h4>
                <p style="color:var(--text-light);">${p.description || 'No description'}</p>
            </div>
        </div>
    `;

    // stash resident info for history
    document.getElementById('historyFromViewBtn').setAttribute('data-rid', p.resident_id);
    document.getElementById('historyFromViewBtn').setAttribute('data-rname', p.resident_name);

    openModal('viewPaymentModal');
}

function viewResidentHistory() {
    const btn = document.getElementById('historyFromViewBtn');
    viewResidentHistoryById(btn.getAttribute('data-rid'), btn.getAttribute('data-rname'));
}

// ── RESIDENT HISTORY ─────────────────────────────────────────────────────────
function viewResidentHistoryById(residentId, residentName) {
    closeModal('viewPaymentModal');
    document.getElementById('historyResidentName').textContent = residentName || '';

    historyAll = paymentData.filter(p => String(p.resident_id) === String(residentId));
    renderHistoryList('');
    openModal('historyModal');
}

function filterHistory(source) {
    renderHistoryList(source);
}

function renderHistoryList(source) {
    const list   = document.getElementById('historyList');
    const items  = source ? historyAll.filter(p => p.source_type === source) : historyAll;

    if (!items.length) {
        list.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i>No transactions found for this filter.</div>';
        return;
    }

    const dotClass = { maintenance:'paid', event:'event', booking:'booking' };

    list.innerHTML = items.map(p => `
        <div class="history-item">
            <div class="history-dot ${p.source_type === 'event' ? 'event' : p.source_type === 'booking' ? 'booking' : p.status}">
                <i class="fas fa-${sourceIcon[p.source_type] || 'circle'}"></i>
            </div>
            <div class="history-body">
                <h4>${p.payment_type}</h4>
                <p>
                    ${p.invoice_id} &nbsp;·&nbsp;
                    <span class="status-badge ${p.status}" style="font-size:.68rem; padding:2px 8px;">${cap(p.status)}</span>
                    &nbsp;·&nbsp; ${fmtDate(p.payment_date || p.due_date)}
                </p>
                ${p.description ? `<p style="margin-top:3px; font-style:italic;">${p.description}</p>` : ''}
            </div>
            <span class="history-amount positive">${fmt(p.amount)}</span>
        </div>
    `).join('');
}

function viewAllTransactions() {
    // reset filters & show all
    activeSource = '';
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-source=""]').classList.add('active');
    document.getElementById('statusFilter').value = '';
    document.getElementById('monthFilter').value  = '';
    document.getElementById('paymentSearch').value = '';
    filterPayments();
    window.scrollTo({ top: 0, behavior:'smooth' });
}

// ── CRUD ─────────────────────────────────────────────────────────────────────
function openAddPaymentModal() {
    document.getElementById('formModalTitle').textContent = 'Record Payment';
    document.getElementById('paymentForm').reset();
    document.getElementById('paymentId').value = '';
    document.getElementById('paymentDate').value = new Date().toISOString().split('T')[0];
    const nd = new Date(); nd.setMonth(nd.getMonth()+1); nd.setDate(10);
    document.getElementById('dueDate').value = nd.toISOString().split('T')[0];
    currentPaymentId = null;
    openModal('paymentFormModal');
}

function onResidentChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('flatNumber').value = opt.dataset.flat || '';
}

function editPayment(invoiceId) {
    const p = paymentData.find(x => x.invoice_id === invoiceId && x.source_type === 'maintenance');
    if (!p) { showNotification('Only manual payments can be edited here.', 'info'); return; }
    currentPaymentId = p.id;
    document.getElementById('formModalTitle').textContent = 'Edit Payment';
    document.getElementById('paymentId').value   = p.id;
    document.getElementById('residentId').value  = p.resident_id;
    document.getElementById('flatNumber').value  = p.flat;
    document.getElementById('paymentType').value = p.payment_type;
    document.getElementById('amount').value      = p.amount;
    document.getElementById('paymentDate').value = p.payment_date;
    document.getElementById('dueDate').value     = p.due_date;
    document.getElementById('paymentMethod').value = p.payment_method;
    document.getElementById('transactionId').value = p.transaction_id;
    document.getElementById('description').value   = p.description;
    document.getElementById('status').value        = p.status;
    openModal('paymentFormModal');
}

function editFromView() {
    closeModal('viewPaymentModal');
    const p = paymentData.find(x => String(x.id) === String(currentPaymentId));
    if (p) editPayment(p.invoice_id);
}

function savePayment() {
    const residentSel  = document.getElementById('residentId');
    const residentId   = residentSel.value;
    const residentName = residentSel.options[residentSel.selectedIndex]?.text.split('(')[0].trim() || '';
    const flatNumber   = document.getElementById('flatNumber').value;
    const paymentType  = document.getElementById('paymentType').value;
    const amount       = document.getElementById('amount').value;
    const paymentDate  = document.getElementById('paymentDate').value;
    const dueDate      = document.getElementById('dueDate').value;
    const status       = document.getElementById('status').value;

    if (!residentId || !paymentType || !amount || !paymentDate || !dueDate) {
        showNotification('Please fill in all required fields.', 'error');
        return;
    }

    const payload = new FormData();
    payload.append('resident_id',   residentId);
    payload.append('resident_name', residentName);
    payload.append('flat_number',   flatNumber);
    payload.append('payment_type',  paymentType);
    payload.append('amount',        amount);
    payload.append('payment_date',  paymentDate);
    payload.append('due_date',      dueDate);
    payload.append('payment_method',document.getElementById('paymentMethod').value);
    payload.append('transaction_id',document.getElementById('transactionId').value);
    payload.append('description',   document.getElementById('description').value);
    payload.append('status',        status);

    const paymentId = document.getElementById('paymentId').value;
    const url = paymentId
        ? BASE_URL + 'Payment_controllerr/edit_payment/' + paymentId
        : BASE_URL + 'Payment_controllerr/add_payment';

    fetch(url, { method:'POST', body:payload })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showNotification(res.message, 'success');
                closeModal('paymentFormModal');
                refreshTable();
            } else {
                showNotification(res.message || 'Failed to save.', 'error');
            }
        })
        .catch(() => showNotification('Network error.', 'error'));
}

function deletePayment(id) {
    deleteId = id;
    openModal('deleteModal');
}

function confirmDelete() {
    if (!deleteId) return;
    fetch(BASE_URL + 'Payment_controllerr/delete_payment/' + deleteId, { method:'POST' })
        .then(r => r.json())
        .then(res => {
            showNotification(res.success ? 'Payment deleted.' : 'Failed to delete.', res.success ? 'info' : 'error');
            closeModal('deleteModal');
            if (res.success) refreshTable();
        })
        .catch(() => showNotification('Network error.', 'error'));
}

// ── UTILITIES ────────────────────────────────────────────────────────────────
function refreshTable() {
    fetch(BASE_URL + 'Payment_controllerr/get_payments_ajax', { method:'POST' })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                paymentData = res.data;
                filterPayments();
                loadRecentTransactions();
                initPaymentChart();
                showNotification('Data refreshed!', 'success');
            }
        })
        .catch(() => {
            // fallback: just re-render existing data
            filterPayments();
            loadRecentTransactions();
        });
}

function exportPayments() {
    window.location.href = BASE_URL + 'Payment_controllerr/export_payments';
}

function showNotification(message, type = 'success') {
    const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle', warning:'exclamation-triangle' };
    const n = document.createElement('div');
    n.className = `notification ${type}`;
    n.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i><span>${message}</span>`;
    document.body.appendChild(n);
    setTimeout(() => { n.style.animation = 'slideOut .3s ease'; setTimeout(() => n.remove(), 300); }, 3000);
}

function openModal(id)  { document.getElementById(id)?.classList.add('active'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('active'); }

// ── INIT ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    filterPayments();
    loadRecentTransactions();
    setTimeout(initPaymentChart, 200);

    // Close modals on outside click
    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.modal.active').forEach(m => closeModal(m.id));
    });

    // Sidebar collapse
    const collapseBtn = document.getElementById('collapseBtn');
    if (collapseBtn) {
        collapseBtn.addEventListener('click', () => {
            ['sidebar','header','main'].forEach(id => {
                document.getElementById(id)?.classList.toggle('collapsed');
            });
        });
    }
});

window.addEventListener('resize', () => {
    clearTimeout(window._chartTimer);
    window._chartTimer = setTimeout(() => window._payChart?.resize(), 250);
});
</script>
</body>
</html>
