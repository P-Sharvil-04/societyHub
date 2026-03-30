<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	// ─── AGGREGATED: All payments from all sources ───────────────────────────

	/**
	 * Returns unified payment records from:
	 *  - payments (manual / maintenance)
	 *  - event_contributions
	 *  - amenity_bookings
	 *
	 * Each row is normalised so the view always gets the same keys.
	 */
	public function get_all_unified_payments($filters = [])
	{
		$rows = [];

		// 1. Core payments (maintenance, water, electricity, etc.)
		$rows = array_merge($rows, $this->_get_core_payments($filters));

		// 2. Event contributions
		$rows = array_merge($rows, $this->_get_event_contributions($filters));

		// 3. Amenity / facility bookings
		$rows = array_merge($rows, $this->_get_booking_payments($filters));

		// Sort by transaction date descending
		usort($rows, function ($a, $b) {
			return strtotime($b['payment_date'] ?: $b['due_date'])
				- strtotime($a['payment_date'] ?: $a['due_date']);
		});

		return $rows;
	}

	// ─── SUMMARY STATS ───────────────────────────────────────────────────────

	public function get_summary_stats()
	{
		$all = $this->get_all_unified_payments();

		$stats = [
			'total_collected' => 0,
			'pending_amount' => 0,
			'overdue_amount' => 0,
			'pending_count' => 0,
			'overdue_count' => 0,
			'paid_count' => 0,
			'total_count' => count($all),
		];

		foreach ($all as $p) {
			if ($p['status'] === 'paid') {
				$stats['total_collected'] += $p['amount'];
				$stats['paid_count']++;
			} elseif ($p['status'] === 'pending') {
				$stats['pending_amount'] += $p['amount'];
				$stats['pending_count']++;
			} elseif ($p['status'] === 'overdue') {
				$stats['overdue_amount'] += $p['amount'];
				$stats['overdue_count']++;
			}
		}

		$stats['collection_rate'] = $stats['total_count'] > 0
			? round(($stats['paid_count'] / $stats['total_count']) * 100)
			: 0;

		return $stats;
	}

	// ─── CHART DATA (last 6 months) ──────────────────────────────────────────

	public function get_chart_data()
	{
		$all = $this->get_all_unified_payments();
		$result = [];

		for ($i = 5; $i >= 0; $i--) {
			$ts = strtotime("-{$i} months");
			$month = (int) date('n', $ts);
			$year = (int) date('Y', $ts);
			$label = date('M y', $ts);

			$paid = 0;
			$pending = 0;

			foreach ($all as $p) {
				$dateStr = $p['status'] === 'paid' ? $p['payment_date'] : $p['due_date'];
				if (!$dateStr)
					continue;

				$d = new DateTime($dateStr);
				if ((int) $d->format('n') === $month && (int) $d->format('Y') === $year) {
					if ($p['status'] === 'paid') {
						$paid += $p['amount'];
					} elseif (in_array($p['status'], ['pending', 'overdue'])) {
						$pending += $p['amount'];
					}
				}
			}

			$result[] = [
				'label' => $label,
				'paid' => $paid,
				'pending' => $pending,
			];
		}

		return $result;
	}

	// ─── TRANSACTION HISTORY for a specific resident / user ─────────────────
	// event_contributions stores user_id — filter is mapped per-source internally.
	// A secondary PHP filter ensures cross-source accuracy.

	public function get_resident_transaction_history($user_id)
	{
		$all = $this->get_all_unified_payments(['resident_id' => $user_id]);

		return array_values(array_filter($all, function ($p) use ($user_id) {
			return (string) $p['resident_id'] === (string) $user_id;
		}));
	}

	// ─── SOURCE: Core payments table ─────────────────────────────────────────

	private function _get_core_payments($filters = [])
	{
		// Expected table: payments
		// Columns: id, invoice_id, resident_id, resident_name, flat_number,
		//          payment_type, amount, payment_date, due_date, payment_method,
		//          transaction_id, description, status, created_at
		$this->db->select(
			'id, invoice_id, resident_id, resident_name, flat_number AS flat,
             payment_type, amount, payment_date, due_date, payment_method,
             transaction_id, description, status, "maintenance" AS source_type'
		);
		$this->db->from('payments');

		$this->_apply_filters($filters, 'payment_date', 'resident_id', 'status');

		$query = $this->db->get();
		$rows = $query->result_array();

		return array_map([$this, '_normalise'], $rows);
	}

	// ─── SOURCE: Event contributions ─────────────────────────────────────────
	//
	// Actual schema (event_contributions):
	//   id, event_id, society_id, user_id, user_name, flat_no,
	//   amount, payment_status, paid_at, created_at
	//
	// Note: user_name & flat_no are stored directly — no residents join needed.
	// payment_status values: 'paid' (matches our normalised 'status').
	// paid_at is the payment datetime; we use created_at as fallback due_date.

	private function _get_event_contributions($filters = [])
	{
		$this->db->select(
			'ec.id,
             CONCAT("EVT-", LPAD(ec.id, 4, "0")) AS invoice_id,
             ec.user_id                           AS resident_id,
             ec.user_name                         AS resident_name,
             IFNULL(ec.flat_no, "-")              AS flat,
             CONCAT("Event: ", IFNULL(e.event_name, CONCAT("Event #", ec.event_id))) AS payment_type,
             ec.amount,
             DATE(ec.paid_at)                     AS payment_date,
             DATE(ec.created_at)                  AS due_date,
             ""                                   AS payment_method,
             ""                                   AS transaction_id,
             CONCAT(
                 "Contribution for: ",
                 IFNULL(e.event_name, CONCAT("Event #", ec.event_id))
             )                                    AS description,
             ec.payment_status                    AS status,
             "event"                              AS source_type'
		);
		$this->db->from('event_contributions ec');
		$this->db->join('events e', 'e.id = ec.event_id', 'left');

		// Filter by user_id (maps to resident_id in unified layer)
		if (!empty($filters['resident_id'])) {
			$this->db->where('ec.user_id', $filters['resident_id']);
		}
		// Filter by society if scoped login is active
		if (!empty($filters['society_id'])) {
			$this->db->where('ec.society_id', $filters['society_id']);
		}
		// payment_status filter — map generic 'paid'/'pending' to table column
		if (!empty($filters['status'])) {
			$this->db->where('ec.payment_status', $filters['status']);
		}
		// Month filter against paid_at (falls back to created_at)
		if (!empty($filters['month'])) {
			$this->db->where('MONTH(IFNULL(ec.paid_at, ec.created_at))', $filters['month']);
		}

		$query = $this->db->get();
		$rows = $query->result_array();

		return array_map([$this, '_normalise'], $rows);
	}

	// ─── SOURCE: Amenity / facility bookings ────────────────────────────────

	private function _get_booking_payments($filters = [])
	{
		// Expected tables: amenity_bookings, amenities, residents
		// Columns assumed: ab.id, a.name AS amenity_name, ab.resident_id,
		//   r.name AS resident_name, r.flat_number, ab.booking_fee AS amount,
		//   ab.paid_on AS payment_date, ab.booking_date AS due_date,
		//   ab.payment_method, ab.transaction_id, ab.status, ab.created_at
		$this->db->select(
			'ab.id,
             CONCAT("BKG-", LPAD(ab.id, 3, "0")) AS invoice_id,
             ab.resident_id,
             IFNULL(r.name, "Unknown") AS resident_name,
             IFNULL(r.flat_number, "-") AS flat,
             CONCAT("Booking: ", a.name) AS payment_type,
             ab.booking_fee AS amount,
             ab.paid_on AS payment_date,
             ab.booking_date AS due_date,
             ab.payment_method,
             ab.transaction_id,
             CONCAT("Amenity booking: ", a.name, " on ", DATE_FORMAT(ab.booking_date, "%d %b %Y")) AS description,
             ab.status,
             "booking" AS source_type'
		);
		$this->db->from('amenity_bookings ab');
		$this->db->join('amenities a', 'a.id = ab.amenity_id', 'left');
		$this->db->join('residents r', 'r.id = ab.resident_id', 'left');

		if (!empty($filters['resident_id'])) {
			$this->db->where('ab.resident_id', $filters['resident_id']);
		}
		if (!empty($filters['status'])) {
			$this->db->where('ab.status', $filters['status']);
		}
		if (!empty($filters['month'])) {
			$this->db->where('MONTH(IFNULL(ab.paid_on, ab.booking_date))', $filters['month']);
		}

		$query = $this->db->get();
		$rows = $query->result_array();

		return array_map([$this, '_normalise'], $rows);
	}

	// ─── NORMALISE row to a standard shape ───────────────────────────────────

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

	// ─── HELPER: apply common query filters ──────────────────────────────────

	private function _apply_filters($filters, $date_col, $resident_col, $status_col)
	{
		if (!empty($filters['resident_id'])) {
			$this->db->where($resident_col, $filters['resident_id']);
		}
		if (!empty($filters['status'])) {
			$this->db->where($status_col, $filters['status']);
		}
		if (!empty($filters['month'])) {
			$this->db->where("MONTH({$date_col})", $filters['month']);
		}
	}

	// ─── CRUD helpers for core payments table only ───────────────────────────

	public function insert_payment($data)
	{
		$data['invoice_id'] = $this->_generate_invoice_id();
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('payments', $data);
		return $this->db->insert_id();
	}

	public function update_payment($id, $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
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
		$this->db->where('id', $id);
		$row = $this->db->get('payments')->row_array();
		return $row ? $this->_normalise(array_merge($row, [
			'flat' => $row['flat_number'] ?? '-',
			'source_type' => 'maintenance',
			'invoice_id' => $row['invoice_id'] ?? '',
		])) : null;
	}

	private function _generate_invoice_id()
	{
		$year = date('Y');
		$count = $this->db->where('YEAR(created_at)', $year)->count_all_results('payments') + 1;
		return 'INV-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
	}

	// ─── Residents list (for dropdown) ───────────────────────────────────────

	public function get_residents_list()
	{
		return $this->db->select('id, name, flat_number')->get('residents')->result_array();
	}
}
