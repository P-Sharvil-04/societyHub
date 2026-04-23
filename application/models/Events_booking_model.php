<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Events_booking_model extends CI_Model
{
	/* ═════════════════════════════════════════════════════════════
	 *  EVENTS
	 * ═════════════════════════════════════════════════════════════ */
	public function get_events($filters = [])
	{
		$this->db->select('events.*, societies.name AS society_name, users.name AS created_by_name')
			->from('events')
			->join('societies', 'societies.id = events.society_id', 'left')
			->join('users', 'users.id = events.created_by', 'left');
		$this->_event_filters($filters);
		return $this->db->order_by('events.event_date', 'DESC')->get()->result_array();
	}

	public function get_event_stats($filters = [])
	{
		$count = function ($extra = []) use ($filters) {
			$this->db->from('events')->join('societies', 'societies.id=events.society_id', 'left');
			$f = $filters;
			unset($f['status'], $f['event_type']);
			$this->_event_filters($f);
			foreach ($extra as $c => $v) {
				$this->db->where($c, $v);
			}
			return (int) $this->db->count_all_results();
		};
		return [
			'total' => $count(),
			'upcoming' => $count(['events.status' => 'upcoming']),
			'ongoing' => $count(['events.status' => 'ongoing']),
			'completed' => $count(['events.status' => 'completed']),
			'fund_open' => $count(['events.fund_required' => 1, 'events.fund_status' => 'open']),
		];
	}

	public function get_event_by_id($id)
	{
		return $this->db
			->select('events.*, societies.name AS society_name, users.name AS created_by_name')
			->from('events')
			->join('societies', 'societies.id=events.society_id', 'left')
			->join('users', 'users.id=events.created_by', 'left')
			->where('events.id', (int) $id)
			->get()->row_array();
	}

	public function get_event_by_qr_token($token)
	{
		return $this->db
			->select('events.*, societies.name AS society_name, users.name AS created_by_name')
			->from('events')
			->join('societies', 'societies.id=events.society_id', 'left')
			->join('users', 'users.id=events.created_by', 'left')
			->where('events.qr_token', $token)
			->get()->row_array();
	}

	public function insert_event($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('events', $data);
		return $this->db->insert_id();
	}

	public function update_event($id, $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update('events', $data);
	}

	public function delete_event($id)
	{
		$this->db->where('event_id', (int) $id)->delete('event_contributions');
		return $this->db->where('id', (int) $id)->delete('events');
	}

	public function get_recent_events($society_id = null, $limit = 5)
	{
		$this->db->select('events.*, societies.name AS society_name')
			->from('events')
			->join('societies', 'societies.id=events.society_id', 'left');
		if ($society_id) {
			$this->db->where('events.society_id', (int) $society_id);
		}
		return $this->db->order_by('events.created_at', 'DESC')->limit($limit)->get()->result_array();
	}

	private function _event_filters($f = [])
	{
		if (!empty($f['society_id'])) {
			$this->db->where('events.society_id', (int) $f['society_id']);
		}
		if (!empty($f['status'])) {
			$this->db->where('events.status', $f['status']);
		}
		if (!empty($f['event_type'])) {
			$this->db->where('events.event_type', $f['event_type']);
		}
		if (!empty($f['search'])) {
			$this->db->group_start()
				->like('events.title', $f['search'])
				->or_like('events.venue', $f['search'])
				->or_like('events.description', $f['search'])
				->group_end();
		}
	}

	/* ─── QR token management ──────────────────────────────────── */

	/**
	 * Generate (or rotate) the event-level QR token.
	 * Also clears the previous scan record so the new token is fresh.
	 */
	public function rotate_event_qr_token($event_id)
	{
		$token = bin2hex(random_bytes(32));
		$this->db->where('id', (int) $event_id)->update('events', [
			'qr_token' => $token,
			'qr_scanned_by' => null,
			'qr_scanned_at' => null,
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		return $token;
	}

	public function clear_event_qr_token($event_id)
	{
		return $this->db->where('id', (int) $event_id)->update('events', [
			'qr_token' => null,
			'qr_scanned_by' => null,
			'qr_scanned_at' => null,
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}

	/**
	 * Mark the current event QR as scanned, then immediately rotate it
	 * so the chairman's QR button always has a fresh, scannable token.
	 *
	 * Returns the new token so the controller can persist it if needed.
	 */
	public function mark_event_qr_scanned($event_id, $user_id)
	{
		// 1. Record the scan timestamp & who scanned
		$this->db->where('id', (int) $event_id)->update('events', [
			'qr_scanned_by' => (int) $user_id,
			'qr_scanned_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);

		// 2. Immediately generate a new token so the button remains usable
		return $this->rotate_event_qr_token((int) $event_id);
	}

	/* ─────────────────────────────────────────────────────────────
	 *  CONTRIBUTIONS & TICKETS
	 * ───────────────────────────────────────────────────────────── */

	public function has_contributed($event_id, $user_id)
	{
		return $this->db
			->where('event_id', (int) $event_id)
			->where('user_id', (int) $user_id)
			->count_all_results('event_contributions') > 0;
	}

	public function get_contribution($event_id, $user_id)
	{
		return $this->db
			->where('event_id', (int) $event_id)
			->where('user_id', (int) $user_id)
			->get('event_contributions')->row_array();
	}

	public function get_or_create_ticket_token($event_id, $user_id)
	{
		$c = $this->get_contribution($event_id, $user_id);
		if (!$c) {
			return null;
		}
		if (!empty($c['ticket_token'])) {
			return $c['ticket_token'];
		}
		$token = bin2hex(random_bytes(24));
		$this->db->where('id', $c['id'])->update('event_contributions', [
			'ticket_token' => $token,
			'ticket_scanned' => 0,
		]);
		return $token;
	}

	/**
	 * Scan a resident's entry ticket.
	 *
	 * Returns:
	 *   'ok'          — newly scanned successfully
	 *   'already'     — token valid but was already scanned before
	 *   false         — token not found
	 */
	public function scan_ticket($token)
	{
		$c = $this->db->where('ticket_token', $token)->get('event_contributions')->row_array();
		if (!$c) {
			return false;  // invalid token
		}
		if ($c['ticket_scanned']) {
			return 'already';  // already scanned — chairman can still confirm
		}
		$this->db->where('id', $c['id'])->update('event_contributions', [
			'ticket_scanned' => 1,
			'ticket_scanned_at' => date('Y-m-d H:i:s'),
		]);
		return 'ok';
	}

	/**
	 * Return all contributions (paid residents) for an event,
	 * including scan status — used by the chairman's Ticket List modal.
	 */
	public function get_event_contributions($event_id)
	{
		return $this->db
			->select('event_contributions.*, users.flat_no')
			->from('event_contributions')
			->join('users', 'users.id = event_contributions.user_id', 'left')
			->where('event_contributions.event_id', (int) $event_id)
			->where('event_contributions.payment_status', 'paid')
			->order_by('event_contributions.user_name', 'ASC')
			->get()->result_array();
	}

	public function get_resident_count($society_id, $roles = ['owner', 'tenant'])
	{
		// Count all users in this society (role filter can be added if roles column exists)
		return $this->db->where('society_id', $society_id)->count_all_results('users');
	}

	public function calculate_per_person_share($event_id)
	{
		$e = $this->get_event_by_id($event_id);
		if (!$e || !$e['fund_required'] || $e['fund_amount'] <= 0) {
			return 0;
		}
		$cnt = $this->get_resident_count($e['society_id']);
		return $cnt > 0 ? $e['fund_amount'] / $cnt : 0;
	}

	public function add_contribution($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('event_contributions', $data);
		$id = $this->db->insert_id();
		if ($id && isset($data['amount']) && ($data['payment_status'] ?? '') === 'paid') {
			$this->db->query(
				'UPDATE events SET fund_raised = COALESCE(fund_raised, 0) + ? WHERE id = ?',
				[(float) $data['amount'], (int) $data['event_id']]
			);
		}
		return $id;
	}

	/* ═════════════════════════════════════════════════════════════
	 *  BOOKINGS
	 * ═════════════════════════════════════════════════════════════ */

	public function get_bookings($filters = [])
	{
		$this->db->select('bookings.*, societies.name AS society_name, approver.name AS approver_name')
			->from('bookings')
			->join('societies', 'societies.id=bookings.society_id', 'left')
			->join('users AS approver', 'approver.id=bookings.approved_by', 'left');
		$this->_booking_filters($filters);
		return $this->db->order_by('bookings.created_at', 'DESC')->get()->result_array();
	}

	public function get_booking_stats($filters = [])
	{
		$count = function ($extra = []) use ($filters) {
			$this->db->from('bookings')->join('societies', 'societies.id=bookings.society_id', 'left');
			$f = $filters;
			unset($f['status'], $f['payment_status']);
			$this->_booking_filters($f);
			foreach ($extra as $c => $v) {
				$this->db->where($c, $v);
			}
			return (int) $this->db->count_all_results();
		};
		return [
			'total' => $count(),
			'pending' => $count(['bookings.status' => 'pending']),
			'approved' => $count(['bookings.status' => 'approved']),
			'rejected' => $count(['bookings.status' => 'rejected']),
			'paid' => $count(['bookings.payment_status' => 'paid']),
		];
	}

	public function get_booking_by_id($id)
	{
		return $this->db
			->select('bookings.*, societies.name AS society_name, approver.name AS approver_name')
			->from('bookings')
			->join('societies', 'societies.id=bookings.society_id', 'left')
			->join('users AS approver', 'approver.id=bookings.approved_by', 'left')
			->where('bookings.id', (int) $id)
			->get()->row_array();
	}

	public function insert_booking($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('bookings', $data);
		return $this->db->insert_id();
	}

	public function update_booking($id, $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update('bookings', $data);
	}

	public function delete_booking($id)
	{
		return $this->db->where('id', (int) $id)->delete('bookings');
	}

	public function approve_booking($id, $status, $approved_by)
	{
		return $this->db->where('id', (int) $id)->update('bookings', [
			'status' => $status,
			'approved_by' => $approved_by,
			'approved_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}

	public function mark_booking_paid($id)
	{
		return $this->db->where('id', (int) $id)->update('bookings', [
			'payment_status' => 'paid',
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}

	public function check_clash($society_id, $area_name, $booking_date, $start_time, $end_time, $exclude_id = null)
	{
		$this->db
			->where('society_id', (int) $society_id)
			->where('area_name', $area_name)
			->where('booking_date', $booking_date)
			->where('status !=', 'rejected')
			->group_start()
			->where('start_time <', $end_time)
			->where('end_time >', $start_time)
			->group_end();
		if ($exclude_id) {
			$this->db->where('id !=', (int) $exclude_id);
		}
		return $this->db->count_all_results('bookings') > 0;
	}

	public function get_recent_bookings($society_id = null, $limit = 5)
	{
		$this->db->select('bookings.*, societies.name AS society_name')
			->from('bookings')
			->join('societies', 'societies.id=bookings.society_id', 'left');
		if ($society_id) {
			$this->db->where('bookings.society_id', (int) $society_id);
		}
		return $this->db->order_by('bookings.created_at', 'DESC')->limit($limit)->get()->result_array();
	}

	private function _booking_filters($f = [])
	{
		if (!empty($f['society_id'])) {
			$this->db->where('bookings.society_id', (int) $f['society_id']);
		}
		if (!empty($f['user_id'])) {
			$this->db->where('bookings.user_id', (int) $f['user_id']);
		}
		if (!empty($f['status'])) {
			$this->db->where('bookings.status', $f['status']);
		}
		if (!empty($f['payment_status'])) {
			$this->db->where('bookings.payment_status', $f['payment_status']);
		}
		if (!empty($f['search'])) {
			$this->db->group_start()
				->like('bookings.user_name', $f['search'])
				->or_like('bookings.area_name', $f['search'])
				->or_like('bookings.flat_no', $f['search'])
				->or_like('bookings.purpose', $f['search'])
				->group_end();
		}
	}

	/* ═════════════════════════════════════════════════════════════
	 *  PAYMENTS
	 * ═════════════════════════════════════════════════════════════ */

	public function insert_payment($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('payments', $data);
		return $this->db->insert_id();
	}

	public function payment_exists($razorpay_payment_id)
	{
		return $this->db
			->where('razorpay_payment_id', $razorpay_payment_id)
			->count_all_results('payments') > 0;
	}

	public function get_payments_by_user($user_id, $limit = 50)
	{
		return $this->db
			->where('user_id', (int) $user_id)
			->order_by('created_at', 'DESC')
			->limit($limit)
			->get('payments')->result_array();
	}

	public function get_payments_by_society($society_id, $filters = [], $limit = 100)
	{
		$this->db->where('society_id', (int) $society_id);
		if (!empty($filters['reference_type'])) {
			$this->db->where('reference_type', $filters['reference_type']);
		}
		if (!empty($filters['user_id'])) {
			$this->db->where('user_id', (int) $filters['user_id']);
		}
		return $this->db->order_by('created_at', 'DESC')->limit($limit)->get('payments')->result_array();
	}

	public function get_all_payments($limit = 200)
	{
		return $this->db->order_by('created_at', 'DESC')->limit($limit)->get('payments')->result_array();
	}

	/* ═════════════════════════════════════════════════════════════
	 *  SHARED
	 * ═════════════════════════════════════════════════════════════ */

	public function get_societies()
	{
		return $this->db->select('id,name')->order_by('name')->get('societies')->result_array();
	}
}
