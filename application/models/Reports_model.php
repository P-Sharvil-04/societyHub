<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports_model extends CI_Model
{
    public function get_societies(): array
    {
        $this->db->select('id, name, city, status');
        $this->db->from('societies');
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_society_name(int $id): string
    {
        if ($id <= 0) return 'Unknown';
        $row = $this->db->get_where('societies', ['id' => $id])->row_array();
        return $row['name'] ?? 'Unknown';
    }

    public function get_report(
        string $type,
        string $start,
        string $end,
        ?int $sid,
        bool $globalView,
        bool $showSocietyColumn = true,
        int $page = 1,
        int $perPage = 10
    ): array {
        $offset = max(0, ($page - 1) * $perPage);

        switch ($type) {
            case 'complaints':
                return $this->_complaints($start, $end, $sid, $showSocietyColumn, $page, $perPage, $offset);
            case 'visitors':
                return $this->_visitors($start, $end, $sid, $showSocietyColumn, $page, $perPage, $offset);
            case 'maintenance':
                return $this->_maintenance($start, $end, $sid, $showSocietyColumn, $page, $perPage, $offset);
            default:
                return $this->_financial($start, $end, $sid, $globalView, $showSocietyColumn, $page, $perPage, $offset);
        }
    }

    // ---------- FINANCIAL ----------
    private function _financial(
        string $start,
        string $end,
        ?int $sid,
        bool $globalView,
        bool $showSocietyColumn,
        int $page,
        int $perPage,
        int $offset
    ): array {
        // Summary KPIs
        $this->db->select('
            SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) AS total_income,
            SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) AS pending_dues,
            COUNT(*) AS total_transactions,
            COUNT(CASE WHEN status = "paid" THEN 1 END) AS paid_count
        ');
        $this->db->from('payments');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $summary = $this->db->get()->row_array();

        // Primary chart: global => bar per society; single society => monthly trend
        if ($globalView) {
            $this->db->select("
                COALESCE(soc.name, 'Unknown Society') AS society_name,
                SUM(CASE WHEN p.status='paid' THEN p.amount ELSE 0 END) AS income,
                SUM(CASE WHEN p.status='pending' THEN p.amount ELSE 0 END) AS pending
            ");
            $this->db->from('payments p');
            $this->db->join('societies soc', 'soc.id = p.society_id', 'left');
            $this->db->where('DATE(p.created_at) >=', $start);
            $this->db->where('DATE(p.created_at) <=', $end);
            $this->db->group_by('p.society_id');
            $this->db->order_by('income', 'DESC');
            $byGroup = $this->db->get()->result_array();

            $primaryChart = [
                'type' => 'bar',
                'labels' => array_column($byGroup, 'society_name'),
                'datasets' => [
                    [
                        'label' => 'Collected',
                        'data' => array_map(fn($r) => (float)$r['income'], $byGroup),
                        'backgroundColor' => 'rgba(39,174,96,0.75)',
                        'borderColor' => '#27ae60',
                        'borderWidth' => 2,
                    ],
                    [
                        'label' => 'Pending',
                        'data' => array_map(fn($r) => (float)$r['pending'], $byGroup),
                        'backgroundColor' => 'rgba(231,76,60,0.75)',
                        'borderColor' => '#e74c3c',
                        'borderWidth' => 2,
                    ],
                ],
            ];
        } else {
            $this->db->select("
                DATE_FORMAT(created_at, '%b %Y') AS month_label,
                DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) AS pending
            ");
            $this->db->from('payments');
            $this->db->where('DATE(created_at) >=', $start);
            $this->db->where('DATE(created_at) <=', $end);
            if ($sid) $this->db->where('society_id', $sid);
            $this->db->group_by('month_key');
            $this->db->order_by('month_key', 'ASC');
            $monthly = $this->db->get()->result_array();

            $primaryChart = [
                'type' => 'bar',
                'labels' => array_column($monthly, 'month_label'),
                'datasets' => [
                    [
                        'label' => 'Collected (Paid)',
                        'data' => array_map(fn($r) => (float)$r['income'], $monthly),
                        'backgroundColor' => 'rgba(39,174,96,0.75)',
                        'borderColor' => '#27ae60',
                        'borderWidth' => 2,
                    ],
                    [
                        'label' => 'Pending',
                        'data' => array_map(fn($r) => (float)$r['pending'], $monthly),
                        'backgroundColor' => 'rgba(231,76,60,0.75)',
                        'borderColor' => '#e74c3c',
                        'borderWidth' => 2,
                    ],
                ],
            ];
        }

        // Secondary chart: payment type breakdown
        $this->db->select('payment_type, SUM(amount) AS total');
        $this->db->from('payments');
        $this->db->where('status', 'paid');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $this->db->group_by('payment_type');
        $byType = $this->db->get()->result_array();

        // Table data with pagination
        $sel = 'p.amount, p.payment_type, p.month, p.year, p.status, p.created_at, u.name AS user_name, u.flat_no';
        if ($showSocietyColumn) $sel .= ', COALESCE(soc.name, "Unknown Society") AS society_name';

        $this->db->select($sel);
        $this->db->from('payments p');
        $this->db->join('users u', 'u.id = p.user_id', 'left');
        if ($showSocietyColumn) $this->db->join('societies soc', 'soc.id = p.society_id', 'left');
        $this->db->where('DATE(p.created_at) >=', $start);
        $this->db->where('DATE(p.created_at) <=', $end);
        if ($sid) $this->db->where('p.society_id', $sid);
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($perPage, $offset);
        $rows = $this->db->get()->result_array();

        // Total rows for pagination
        $this->db->from('payments');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $totalRows = $this->db->count_all_results();

        $headers = ['Date', 'Resident', 'Flat', 'Type', 'Month/Year', 'Amount', 'Status'];
        if ($showSocietyColumn) array_splice($headers, 1, 0, ['Society']);

        $tableRows = [];
        foreach ($rows as $r) {
            $row = [
                date('d/m/Y', strtotime($r['created_at'])),
                $r['user_name'] ?? '—',
                $r['flat_no'] ?? '—',
                ucfirst($r['payment_type'] ?: '—'),
                $r['month'] ? $r['month'] . ' ' . $r['year'] : '—',
                '₹' . number_format((float)$r['amount'], 2),
                ucfirst($r['status'] ?? '—'),
            ];
            if ($showSocietyColumn) array_splice($row, 1, 0, [$r['society_name'] ?? 'Unknown Society']);
            $tableRows[] = $row;
        }

        return [
            'kpi' => [
                ['label' => 'Total Collected', 'value' => '₹' . number_format((float)($summary['total_income'] ?? 0), 0, '.', ','), 'trendDir' => 'up'],
                ['label' => 'Total Transactions', 'value' => (string)($summary['total_transactions'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Paid Transactions', 'value' => (string)($summary['paid_count'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Pending Dues', 'value' => '₹' . number_format((float)($summary['pending_dues'] ?? 0), 0, '.', ','), 'trendDir' => 'down'],
            ],
            'primaryChart' => $primaryChart,
            'secondaryChart' => [
                'type' => 'doughnut',
                'labels' => array_map(fn($r) => ucfirst($r['payment_type'] ?: 'Other'), $byType),
                'data' => array_map(fn($r) => (float)$r['total'], $byType),
                'colors' => ['#3498db', '#27ae60', '#f39c12', '#e74c3c', '#9b59b6'],
            ],
            'table' => [
                'headers' => $headers,
                'rows' => $tableRows,
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalRows' => (int)$totalRows,
                'totalPages' => (int)ceil($totalRows / $perPage),
            ],
        ];
    }

    // ---------- COMPLAINTS ----------
    private function _complaints(string $start, string $end, ?int $sid, bool $showSocietyColumn, int $page, int $perPage, int $offset): array
    {
        // Summary
        $this->db->select("
            COUNT(*) AS total,
            SUM(status='pending') AS open_count,
            SUM(status='resolved' OR status='closed') AS resolved_count,
            SUM(status='in-progress') AS inprogress_count
        ");
        $this->db->from('complaints');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $summary = $this->db->get()->row_array();

        // Primary chart: by category
        $this->db->select('COALESCE(category, "Uncategorised") AS label, COUNT(*) AS cnt');
        $this->db->from('complaints');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $this->db->group_by('category');
        $this->db->order_by('cnt', 'DESC');
        $byCat = $this->db->get()->result_array();

        // Avg resolution days
        $this->db->select('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)/24) AS avg_days');
        $this->db->from('complaints');
        $this->db->where('status', 'resolved');
        $this->db->where('resolved_at IS NOT NULL');
        if ($sid) $this->db->where('society_id', $sid);
        $avgRes = $this->db->get()->row_array();

        // Table data
        $sel = 'c.complaint_id, c.title, c.category, c.flat, c.user_name, c.status, c.expense_amount, c.created_at';
        if ($showSocietyColumn) $sel .= ', COALESCE(soc.name, "Unknown Society") AS society_name';

        $this->db->select($sel);
        $this->db->from('complaints c');
        if ($showSocietyColumn) $this->db->join('societies soc', 'soc.id = c.society_id', 'left');
        $this->db->where('DATE(c.created_at) >=', $start);
        $this->db->where('DATE(c.created_at) <=', $end);
        if ($sid) $this->db->where('c.society_id', $sid);
        $this->db->order_by('c.created_at', 'DESC');
        $this->db->limit($perPage, $offset);
        $rows = $this->db->get()->result_array();

        // Total rows for pagination
        $this->db->from('complaints');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $totalRows = $this->db->count_all_results();

        $headers = ['ID', 'Title', 'Category', 'Flat', 'Resident', 'Expense', 'Status', 'Date'];
        if ($showSocietyColumn) array_splice($headers, 1, 0, ['Society']);

        $tableRows = [];
        foreach ($rows as $r) {
            $row = [
                $r['complaint_id'] ?? '—',
                $r['title'] ?? '—',
                $r['category'] ?? '—',
                $r['flat'] ?? '—',
                $r['user_name'] ?? '—',
                $r['expense_amount'] ? '₹' . number_format((float)$r['expense_amount'], 0) : '—',
                ucfirst($r['status'] ?? '—'),
                date('d/m/Y', strtotime($r['created_at'])),
            ];
            if ($showSocietyColumn) array_splice($row, 1, 0, [$r['society_name'] ?? 'Unknown Society']);
            $tableRows[] = $row;
        }

        return [
            'kpi' => [
                ['label' => 'Total Complaints', 'value' => (string)($summary['total'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Open / Pending', 'value' => (string)($summary['open_count'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Resolved', 'value' => (string)($summary['resolved_count'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Avg Resolution', 'value' => round((float)($avgRes['avg_days'] ?? 0), 1) . ' days', 'trendDir' => 'down'],
            ],
            'primaryChart' => [
                'type' => 'bar',
                'labels' => array_column($byCat, 'label'),
                'datasets' => [[
                    'label' => 'Complaints by Category',
                    'data' => array_map(fn($r) => (int)$r['cnt'], $byCat),
                    'backgroundColor' => 'rgba(231,76,60,0.75)',
                    'borderColor' => '#c0392b',
                    'borderWidth' => 2,
                ]],
            ],
            'secondaryChart' => [
                'type' => 'pie',
                'labels' => ['Pending', 'In Progress', 'Resolved'],
                'data' => [
                    (int)($summary['open_count'] ?? 0),
                    (int)($summary['inprogress_count'] ?? 0),
                    (int)($summary['resolved_count'] ?? 0),
                ],
                'colors' => ['#e74c3c', '#f39c12', '#27ae60'],
            ],
            'table' => [
                'headers' => $headers,
                'rows' => $tableRows,
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalRows' => (int)$totalRows,
                'totalPages' => (int)ceil($totalRows / $perPage),
            ],
        ];
    }

    // ---------- VISITORS ----------
    private function _visitors(string $start, string $end, ?int $sid, bool $showSocietyColumn, int $page, int $perPage, int $offset): array
    {
        // Summary
        $this->db->select("
            COUNT(*) AS total,
            SUM(status='checked in') AS checked_in,
            SUM(status='checked out') AS checked_out,
            SUM(status='pending') AS pending_count
        ");
        $this->db->from('visitors');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $summary = $this->db->get()->row_array();

        // Avg visit duration
        $this->db->select('AVG(TIMESTAMPDIFF(MINUTE, entry_time, exit_time)) AS avg_min');
        $this->db->from('visitors');
        $this->db->where('exit_time IS NOT NULL');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $avgVisit = $this->db->get()->row_array();

        // Secondary chart: purpose
        $this->db->select('COALESCE(purpose, "Other") AS label, COUNT(*) AS cnt');
        $this->db->from('visitors');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $this->db->group_by('purpose');
        $byPurpose = $this->db->get()->result_array();

        // Primary chart: daily trend
        $this->db->select('DATE(created_at) AS day, COUNT(*) AS cnt');
        $this->db->from('visitors');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $this->db->group_by('day');
        $this->db->order_by('day', 'ASC');
        $daily = $this->db->get()->result_array();

        // Table data
        $sel = 'v.visitor_name, v.phone, v.flat, v.purpose, v.status, v.entry_time, v.exit_time';
        if ($showSocietyColumn) $sel .= ', COALESCE(soc.name, "Unknown Society") AS society_name';

        $this->db->select($sel);
        $this->db->from('visitors v');
        if ($showSocietyColumn) $this->db->join('societies soc', 'soc.id = v.society_id', 'left');
        $this->db->where('DATE(v.created_at) >=', $start);
        $this->db->where('DATE(v.created_at) <=', $end);
        if ($sid) $this->db->where('v.society_id', $sid);
        $this->db->order_by('v.created_at', 'DESC');
        $this->db->limit($perPage, $offset);
        $rows = $this->db->get()->result_array();

        $this->db->from('visitors');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $totalRows = $this->db->count_all_results();

        $headers = ['Visitor', 'Phone', 'Flat', 'Purpose', 'Entry', 'Exit', 'Status'];
        if ($showSocietyColumn) array_splice($headers, 1, 0, ['Society']);

        $tableRows = [];
        foreach ($rows as $r) {
            $row = [
                $r['visitor_name'] ?? '—',
                $r['phone'] ?? '—',
                $r['flat'] ?? '—',
                ucfirst($r['purpose'] ?? '—'),
                $r['entry_time'] ? date('d/m/Y H:i', strtotime($r['entry_time'])) : '—',
                $r['exit_time'] ? date('d/m/Y H:i', strtotime($r['exit_time'])) : '—',
                ucfirst($r['status'] ?? '—'),
            ];
            if ($showSocietyColumn) array_splice($row, 1, 0, [$r['society_name'] ?? 'Unknown Society']);
            $tableRows[] = $row;
        }

        return [
            'kpi' => [
                ['label' => 'Total Visitors', 'value' => (string)($summary['total'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Checked In', 'value' => (string)($summary['checked_in'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Pending', 'value' => (string)($summary['pending_count'] ?? 0), 'trendDir' => 'down'],
                ['label' => 'Avg Visit Time', 'value' => round((float)($avgVisit['avg_min'] ?? 0)) . ' min', 'trendDir' => 'up'],
            ],
            'primaryChart' => [
                'type' => 'line',
                'labels' => array_map(fn($r) => date('d M', strtotime($r['day'])), $daily),
                'datasets' => [[
                    'label' => 'Visitors per Day',
                    'data' => array_map(fn($r) => (int)$r['cnt'], $daily),
                    'borderColor' => '#3498db',
                    'backgroundColor' => 'rgba(52,152,219,0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ]],
            ],
            'secondaryChart' => [
                'type' => 'doughnut',
                'labels' => array_column($byPurpose, 'label'),
                'data' => array_map(fn($r) => (int)$r['cnt'], $byPurpose),
                'colors' => ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6'],
            ],
            'table' => [
                'headers' => $headers,
                'rows' => $tableRows,
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalRows' => (int)$totalRows,
                'totalPages' => (int)ceil($totalRows / $perPage),
            ],
        ];
    }

    // ---------- MAINTENANCE / STAFF ----------
    private function _maintenance(string $start, string $end, ?int $sid, bool $showSocietyColumn, int $page, int $perPage, int $offset): array
    {
        // Staff summary
        $this->db->select("
            COUNT(*) AS total_staff,
            SUM(status='active') AS active_staff,
            SUM(status='inactive') AS inactive_staff,
            SUM(salary) AS total_salary
        ");
        $this->db->from('staff');
        if ($sid) $this->db->where('society_id', $sid);
        $staffSummary = $this->db->get()->row_array();

        // Maintenance requests summary (from complaints where category = 'Maintenance')
        $this->db->select("
            COUNT(*) AS maint_requests,
            SUM(status='resolved' OR status='closed') AS completed,
            SUM(status='in-progress') AS inprogress,
            SUM(status='pending') AS pending_count,
            SUM(expense_amount) AS total_expense
        ");
        $this->db->from('complaints');
        $this->db->where('category', 'Maintenance');
        $this->db->where('DATE(created_at) >=', $start);
        $this->db->where('DATE(created_at) <=', $end);
        if ($sid) $this->db->where('society_id', $sid);
        $maintSummary = $this->db->get()->row_array();

        // Primary chart: staff by department
        $this->db->select('COALESCE(department, "General") AS label, COUNT(*) AS cnt');
        $this->db->from('staff');
        if ($sid) $this->db->where('society_id', $sid);
        $this->db->group_by('department');
        $byDept = $this->db->get()->result_array();

        // Table data (staff list)
        $sel = 's.first_name, s.last_name, s.designation, s.department, s.shift, s.salary, s.status, s.join_date';
        if ($showSocietyColumn) $sel .= ', COALESCE(soc.name, "Unknown Society") AS society_name';

        $this->db->select($sel);
        $this->db->from('staff s');
        if ($showSocietyColumn) $this->db->join('societies soc', 'soc.id = s.society_id', 'left');
        if ($sid) $this->db->where('s.society_id', $sid);
        $this->db->order_by('s.join_date', 'DESC');
        $this->db->order_by('s.id', 'DESC');
        $this->db->limit($perPage, $offset);
        $rows = $this->db->get()->result_array();

        $this->db->from('staff');
        if ($sid) $this->db->where('society_id', $sid);
        $totalRows = $this->db->count_all_results();

        $headers = ['Name', 'Designation', 'Department', 'Shift', 'Salary', 'Status', 'Join Date'];
        if ($showSocietyColumn) array_splice($headers, 1, 0, ['Society']);

        $avgSalary = ($staffSummary['total_staff'] ?? 0) ? (float)$staffSummary['total_salary'] / (float)$staffSummary['total_staff'] : 0;

        $tableRows = [];
        foreach ($rows as $r) {
            $row = [
                trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                $r['designation'] ?? '—',
                $r['department'] ?? '—',
                $r['shift'] ?? '—',
                '₹' . number_format((float)($r['salary'] ?? 0), 0),
                ucfirst($r['status'] ?? '—'),
                $r['join_date'] ? date('d/m/Y', strtotime($r['join_date'])) : '—',
            ];
            if ($showSocietyColumn) array_splice($row, 1, 0, [$r['society_name'] ?? 'Unknown Society']);
            $tableRows[] = $row;
        }

        $statuses = [
            'Completed'   => (int)($maintSummary['completed'] ?? 0),
            'In Progress' => (int)($maintSummary['inprogress'] ?? 0),
            'Pending'     => (int)($maintSummary['pending_count'] ?? 0),
        ];

        return [
            'kpi' => [
                ['label' => 'Total Staff', 'value' => (string)($staffSummary['total_staff'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Active Staff', 'value' => (string)($staffSummary['active_staff'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Maint. Requests', 'value' => (string)($maintSummary['maint_requests'] ?? 0), 'trendDir' => 'up'],
                ['label' => 'Avg Salary', 'value' => '₹' . number_format($avgSalary, 0), 'trendDir' => 'up'],
            ],
            'primaryChart' => [
                'type' => 'bar',
                'labels' => array_column($byDept, 'label'),
                'datasets' => [[
                    'label' => 'Staff by Department',
                    'data' => array_map(fn($r) => (int)$r['cnt'], $byDept),
                    'backgroundColor' => 'rgba(243,156,18,0.75)',
                    'borderColor' => '#e67e22',
                    'borderWidth' => 2,
                ]],
            ],
            'secondaryChart' => [
                'type' => 'pie',
                'labels' => array_keys($statuses),
                'data' => array_values($statuses),
                'colors' => ['#27ae60', '#f39c12', '#e74c3c'],
            ],
            'table' => [
                'headers' => $headers,
                'rows' => $tableRows,
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalRows' => (int)$totalRows,
                'totalPages' => (int)ceil($totalRows / $perPage),
            ],
        ];
    }
}
