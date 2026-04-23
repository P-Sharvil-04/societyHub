<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Payment_model
 *
 * Aggregates 4 real payment sources into one unified shape:
 *   1. payments          – maintenance / Razorpay fees
 *   2. event_contributions – owner contributions to society events
 *   3. bookings          – area / facility booking fees
 *   4. penalties         – admin-raised penalty charges
 */
class Payment_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	// =========================================================================
	// PUBLIC API
	// =========================================================================

	/**
	 * Unified list from all 4 sources, sorted newest first.
	 *
	 * $filters (all optional):
	 *   society_id – scope to one society
	 *   user_id    – single resident history
	 *   status     – 'paid' | 'pending' | 'waived'
	 *   month      – '01'–'12'
	 */
	public function get_all_unified_payments($filters = [])
	{
		$rows = array_merge(
			$this->_get_maintenance_payments($filters),
			$this->_get_event_contributions($filters),
			$this->_get_booking_payments($filters)
		);

		usort($rows, function ($a, $b) {
			$da = $a['payment_date'] ?: ($a['due_date'] ?: '1970-01-01');
			$db = $b['payment_date'] ?: ($b['due_date'] ?: '1970-01-01');
			return strtotime($db) - strtotime($da);
		});

		return $rows;
	}

	private function _get_maintenance_payments($filters = [])
	{
		$this->db->select(
			'p.id,
		 CONCAT("PAY-", LPAD(p.id, 4, "0")) AS invoice_id,
		 p.user_id AS resident_id,
		 IFNULL(u.name, "Unknown") AS resident_name,
		 IFNULL(u.flat_no, "-") AS flat,
		 "Maintenance" AS payment_type,
		 p.amount,
		 DATE(p.payment_date) AS payment_date,
		 CONCAT(IFNULL(p.month, ""), " ", IFNULL(p.year, "")) AS due_date,
		 CASE WHEN p.payment_id IS NOT NULL THEN "Razorpay" ELSE "Manual" END AS payment_method,
		 IFNULL(p.payment_id, IFNULL(p.order_id, "")) AS transaction_id,
		 CONCAT("Maintenance – ", IFNULL(p.month, ""), " ", IFNULL(p.year, "")) AS description,
		 p.status,
		 "maintenance" AS source_type',
			false
		);
		$this->db->from('payments p');
		$this->db->join('users u', 'u.id = p.user_id', 'left');

		if (!empty($filters['society_id']))
			$this->db->where('p.society_id', $filters['society_id']);
		if (!empty($filters['user_id']))
			$this->db->where('p.user_id', $filters['user_id']);
		if (!empty($filters['status']))
			$this->db->where('p.status', $filters['status']);
		if (!empty($filters['month']))
			$this->db->where('p.month', $filters['month']);

		return array_map([$this, '_normalise'], $this->db->get()->result_array());
	}

	/** Stats for the four summary cards */
	public function get_summary_stats($society_id = null)
	{
		$filters = $society_id ? ['society_id' => $society_id] : [];
		$all = $this->get_all_unified_payments($filters);

		$s = [
			'total_collected' => 0,
			'pending_amount' => 0,
			'overdue_amount' => 0,
			'paid_count' => 0,
			'pending_count' => 0,
			'overdue_count' => 0,
			'total_count' => count($all),
		];

		foreach ($all as $p) {
			if (in_array($p['status'], ['paid', 'waived'])) {
				$s['total_collected'] += $p['amount'];
				$s['paid_count']++;
			} elseif ($p['status'] === 'pending') {
				$s['pending_amount'] += $p['amount'];
				$s['pending_count']++;
			} elseif ($p['status'] === 'overdue') {
				$s['overdue_amount'] += $p['amount'];
				$s['overdue_count']++;
			}
		}

		$s['collection_rate'] = $s['total_count'] > 0
			? round(($s['paid_count'] / $s['total_count']) * 100) : 0;

		return $s;
	}

	/** Bar chart data – last 6 months */
	public function get_chart_data($society_id = null)
	{
		$filters = $society_id ? ['society_id' => $society_id] : [];
		$all = $this->get_all_unified_payments($filters);
		$result = [];

		for ($i = 5; $i >= 0; $i--) {
			$ts = strtotime("-{$i} months");
			$month = (int) date('n', $ts);
			$year = (int) date('Y', $ts);
			$paid = 0;
			$pending = 0;

			foreach ($all as $p) {
				$ds = $p['payment_date'] ?: $p['due_date'];
				if (!$ds)
					continue;
				$d = new DateTime($ds);
				if ((int) $d->format('n') !== $month || (int) $d->format('Y') !== $year)
					continue;
				if (in_array($p['status'], ['paid', 'waived']))
					$paid += $p['amount'];
				else
					$pending += $p['amount'];
			}
			$result[] = ['label' => date('M y', $ts), 'paid' => $paid, 'pending' => $pending];
		}
		return $result;
	}

	/** All transactions for one user across every source */
	public function get_user_history($user_id, $society_id = null)
	{
		$f = ['user_id' => $user_id];
		if ($society_id)
			$f['society_id'] = $society_id;
		return $this->get_all_unified_payments($f);
	}

	/** Users list for "Record Payment" dropdown (owners + tenants only) */
	public function get_users_list($society_id = null)
	{
		$this->db->select('id, name, flat_no, member_type');
		$this->db->from('users');
		$this->db->where('status', 1);
		if ($society_id)
			$this->db->where('society_id', $society_id);
		$this->db->order_by('name', 'ASC');
		return $this->db->get()->result_array();
	}

	// ─── CRUD: payments table (admin manual maintenance entry) ───────────────

	public function insert_payment($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('payments', $data);
		return $this->db->insert_id();
	}

	public function update_payment($id, $data)
	{
		$this->db->where('id', $id);
		return $this->db->update('payments', $data);
	}

	public function delete_payment($id)
	{
		$this->db->where('id', $id);
		return $this->db->delete('payments');
	}

	public function get_payment_by_id($id)
	{
		$this->db->select('p.*, u.name AS uname, u.flat_no AS uflatno');
		$this->db->from('payments p');
		$this->db->join('users u', 'u.id = p.user_id', 'left');
		$this->db->where('p.id', $id);
		$row = $this->db->get()->row_array();
		if (!$row)
			return null;

		return $this->_normalise([
			'id' => $row['id'],
			'invoice_id' => 'PAY-' . str_pad($row['id'], 4, '0', STR_PAD_LEFT),
			'resident_id' => $row['user_id'],
			'resident_name' => $row['uname'] ?? 'Unknown',
			'flat' => $row['uflatno'] ?? '-',
			'payment_type' => ucfirst($row['payment_type'] ?? 'Maintenance'),
			'amount' => $row['amount'],
			'payment_date' => $row['payment_date'] ? date('Y-m-d', strtotime($row['payment_date'])) : '',
			'due_date' => $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : '',
			'payment_method' => $row['payment_id'] ? 'Razorpay' : 'Manual',
			'transaction_id' => $row['payment_id'] ?? ($row['order_id'] ?? ''),
			'description' => ucfirst($row['payment_type'] ?? '') . ' – ' . ($row['month'] ?? '') . ' ' . ($row['year'] ?? ''),
			'status' => $row['status'],
			'source_type' => 'maintenance',
		]);
	}

	// =========================================================================
	// PRIVATE SOURCES
	// =========================================================================

	/**
	 * SOURCE 1: payments table
	 *
	 * Real columns:
	 *   id, society_id, user_id, amount, payment_type ENUM(maintenance|penalty),
	 *   month VARCHAR(10), year INT, status ENUM(pending|paid),
	 *   created_by, payment_date DATETIME, order_id, payment_id, created_at, receipt
	 */
	// private function _get_maintenance_payments($filters = [])
	// {
	//     $this->db->select(
	//         'p.id,
	//          CONCAT("PAY-", LPAD(p.id, 4, "0"))   AS invoice_id,
	//          p.user_id                              AS resident_id,
	//          IFNULL(u.name, "Unknown")             AS resident_name,
	//          IFNULL(u.flat_no, "-")                AS flat,
	//          IFNULL(UPPER(LEFT(p.payment_type,1)),
	//              "M"), LOWER(SUBSTRING(p.payment_type,2)) AS _dummy,
	//          CASE
	//              WHEN p.payment_type = "maintenance" THEN "Maintenance"
	//              WHEN p.payment_type = "penalty"     THEN "Penalty"
	//              ELSE "Maintenance"
	//          END                                   AS payment_type,
	//          p.amount,
	//          DATE(p.payment_date)                  AS payment_date,
	//          DATE(p.created_at)                    AS due_date,
	//          CASE
	//              WHEN p.payment_id IS NOT NULL THEN "Razorpay"
	//              ELSE "Manual"
	//          END                                   AS payment_method,
	//          IFNULL(p.payment_id, IFNULL(p.order_id, "")) AS transaction_id,
	//          CONCAT(
	//              CASE WHEN p.payment_type="maintenance" THEN "Maintenance"
	//                   WHEN p.payment_type="penalty"     THEN "Penalty"
	//                   ELSE "Maintenance" END,
	//              IF(p.month IS NOT NULL AND p.month != "",
	//                 CONCAT(" – ", p.month, " ", IFNULL(p.year, "")), "")
	//          )                                     AS description,
	//          p.status,
	//          "maintenance"                         AS source_type'
	//     );
	//     $this->db->from('payments p');
	//     $this->db->join('users u', 'u.id = p.user_id', 'left');

	//     if (!empty($filters['society_id'])) $this->db->where('p.society_id', $filters['society_id']);
	//     if (!empty($filters['user_id']))    $this->db->where('p.user_id',    $filters['user_id']);
	//     if (!empty($filters['status']))     $this->db->where('p.status',     $filters['status']);
	//     if (!empty($filters['month']))      $this->db->where('p.month',      $filters['month']);

	//     return array_map([$this, '_normalise'], $this->db->get()->result_array());
	// }

	/**
	 * SOURCE 2: event_contributions
	 *
	 * Real columns:
	 *   id, event_id, society_id, user_id, user_name VARCHAR(255),
	 *   flat_no VARCHAR(50), amount, payment_status VARCHAR(50) DEFAULT 'paid',
	 *   paid_at DATETIME, created_at DATETIME
	 *
	 * Joined: events (id, title, event_date)
	 */
	private function _get_event_contributions($filters = [])
	{
		$this->db->select(
			'ec.id,
             CONCAT("EVT-", LPAD(ec.id, 4, "0"))  AS invoice_id,
             ec.user_id                             AS resident_id,
             ec.user_name                           AS resident_name,
             IFNULL(ec.flat_no, "-")               AS flat,
             CONCAT("Event: ", IFNULL(e.title,
                 CONCAT("Event #", ec.event_id)))  AS payment_type,
             ec.amount,
             DATE(ec.paid_at)                       AS payment_date,
             DATE(e.event_date)                     AS due_date,
             ""                                     AS payment_method,
             ""                                     AS transaction_id,
             CONCAT("Contribution – ",
                 IFNULL(e.title, CONCAT("Event #", ec.event_id))) AS description,
             ec.payment_status                      AS status,
             "event"                                AS source_type'
		);
		$this->db->from('event_contributions ec');
		$this->db->join('events e', 'e.id = ec.event_id', 'left');

		if (!empty($filters['society_id']))
			$this->db->where('ec.society_id', $filters['society_id']);
		if (!empty($filters['user_id']))
			$this->db->where('ec.user_id', $filters['user_id']);
		if (!empty($filters['status']))
			$this->db->where('ec.payment_status', $filters['status']);
		if (!empty($filters['month']))
			$this->db->where('MONTH(IFNULL(ec.paid_at, ec.created_at))', $filters['month']);

		return array_map([$this, '_normalise'], $this->db->get()->result_array());
	}

	/**
	 * SOURCE 3: bookings
	 *
	 * Real columns:
	 *   id, society_id, user_id, user_name VARCHAR(255), flat_no VARCHAR(50),
	 *   area_name VARCHAR(255), purpose VARCHAR(255), booking_date DATE,
	 *   start_time TIME, end_time TIME, amount DECIMAL(10,2),
	 *   payment_status ENUM(pending|paid|waived),
	 *   status ENUM(pending|approved|rejected),
	 *   approved_by INT, approved_at DATETIME, created_at, updated_at
	 *
	 * Note: payment_status and booking status are SEPARATE columns.
	 * We track payment_status only.
	 */
	private function _get_booking_payments($filters = [])
	{
		$this->db->select(
			'b.id,
             CONCAT("BKG-", LPAD(b.id, 4, "0"))   AS invoice_id,
             b.user_id                              AS resident_id,
             b.user_name                            AS resident_name,
             IFNULL(b.flat_no, "-")                AS flat,
             CONCAT("Booking: ", b.area_name)       AS payment_type,
             b.amount,
             DATE(IFNULL(b.approved_at, b.updated_at)) AS payment_date,
             b.booking_date                         AS due_date,
             ""                                     AS payment_method,
             ""                                     AS transaction_id,
             CONCAT(b.area_name,
                 IF(b.purpose IS NOT NULL,
                    CONCAT(" – ", b.purpose), ""),
                 " on ",
                 DATE_FORMAT(b.booking_date, "%d %b %Y")) AS description,
             b.payment_status                       AS status,
             "booking"                              AS source_type'
		);
		$this->db->from('bookings b');

		if (!empty($filters['society_id']))
			$this->db->where('b.society_id', $filters['society_id']);
		if (!empty($filters['user_id']))
			$this->db->where('b.user_id', $filters['user_id']);
		if (!empty($filters['status']))
			$this->db->where('b.payment_status', $filters['status']);
		if (!empty($filters['month']))
			$this->db->where('MONTH(b.booking_date)', $filters['month']);

		return array_map([$this, '_normalise'], $this->db->get()->result_array());
	}

	/**
	 * SOURCE 4: penalties
	 *
	 * Real columns:
	 *   id, society_id, user_id, reason VARCHAR(255), amount DECIMAL(10,2),
	 *   created_by INT, status ENUM(unpaid|paid), created_at TIMESTAMP
	 *
	 * We normalise: unpaid → pending  so the view has consistent statuses.
	 */
	// private function _get_penalties($filters = [])
	// {
	//     // Map incoming 'pending' filter → 'unpaid' for this table
	//     $statusFilter = null;
	//     if (!empty($filters['status'])) {
	//         $map = ['pending' => 'unpaid', 'paid' => 'paid'];
	//         $statusFilter = $map[$filters['status']] ?? null;
	//     }

	//     $this->db->select(
	//         'pen.id,
	//          CONCAT("PEN-", LPAD(pen.id, 4, "0"))  AS invoice_id,
	//          pen.user_id                             AS resident_id,
	//          IFNULL(u.name, "Unknown")              AS resident_name,
	//          IFNULL(u.flat_no, "-")                 AS flat,
	//          "Penalty"                               AS payment_type,
	//          pen.amount,
	//          CASE WHEN pen.status = "paid"
	//               THEN DATE(pen.created_at) ELSE "" END AS payment_date,
	//          DATE(pen.created_at)                    AS due_date,
	//          ""                                      AS payment_method,
	//          ""                                      AS transaction_id,
	//          IFNULL(pen.reason, "Penalty charge")    AS description,
	//          CASE WHEN pen.status = "unpaid"
	//               THEN "pending" ELSE "paid" END     AS status,
	//          "penalty"                               AS source_type'
	//     );
	//     $this->db->from('penalties pen');
	//     $this->db->join('users u', 'u.id = pen.user_id', 'left');

	//     if (!empty($filters['society_id'])) $this->db->where('pen.society_id', $filters['society_id']);
	//     if (!empty($filters['user_id']))    $this->db->where('pen.user_id',    $filters['user_id']);
	//     if ($statusFilter)                  $this->db->where('pen.status',     $statusFilter);
	//     if (!empty($filters['month']))      $this->db->where('MONTH(pen.created_at)', $filters['month']);

	//     return array_map([$this, '_normalise'], $this->db->get()->result_array());
	// }

	// ─── Normalise every row to the same shape ────────────────────────────────
	private function _normalise($row)
	{
		return [
			'id' => $row['id'] ?? null,
			'invoice_id' => $row['invoice_id'] ?? '',
			'resident_id' => $row['resident_id'] ?? null,
			'resident_name' => $row['resident_name'] ?? 'Unknown',
			'flat' => $row['flat'] ?? '-',
			'payment_type' => $row['payment_type'] ?? 'Other',
			'amount' => (float) ($row['amount'] ?? 0),
			'payment_date' => $row['payment_date'] ?? '',
			'due_date' => $row['due_date'] ?? '',
			'payment_method' => $row['payment_method'] ?? '',
			'transaction_id' => $row['transaction_id'] ?? '',
			'description' => $row['description'] ?? '',
			'status' => $row['status'] ?? 'pending',
			'source_type' => $row['source_type'] ?? 'maintenance',
		];
	}
	public function get_maintenance_amount(int $societyId, string $flatNo = null): float
	{
		// Option 1: from society_settings (global amount for society)
		$this->db->select('setting_value')
			->from('society_settings')
			->where('society_id', $societyId)
			->where('setting_key', 'maintenance_amount')
			->limit(1);
		$row = $this->db->get()->row_array();
		if ($row && is_numeric($row['setting_value'])) {
			return (float) $row['setting_value'];
		}

		// Option 2: from flats table (if each flat has its own amount)
		if ($flatNo) {
			$this->db->select('maintenance_amount')
				->from('flats')
				->where('society_id', $societyId)
				->where('flat_no', $flatNo)
				->limit(1);
			$row = $this->db->get()->row_array();
			if ($row && isset($row['maintenance_amount'])) {
				return (float) $row['maintenance_amount'];
			}
		}

		// Default if not set
		return 0;
	}

	/**
	 * Get maintenance due day (1-31) from society settings
	 * @param int $societyId
	 * @return int|null
	 */
	public function get_maintenance_due_date(int $societyId): ?int
	{
		$this->db->select('setting_value')
			->from('society_settings')
			->where('society_id', $societyId)
			->where('setting_key', 'maintenance_due_date')
			->limit(1);
		$row = $this->db->get()->row_array();
		if ($row && is_numeric($row['setting_value'])) {
			return (int) $row['setting_value'];
		}
		return null;
	}
}
