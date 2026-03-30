<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * events table:
 *   id, society_id, title, description, event_type, event_date,
 *   start_time, end_time, venue, status, fund_required (tinyint 0/1),
 *   fund_amount, fund_raised, fund_status (open/closed),
 *   created_by, created_at, updated_at
 *
 * event_contributions table:
 *   id, event_id, society_id, user_id, user_name, flat_no,
 *   amount, payment_status, paid_at, created_at
 *
 * bookings table:
 *   id, society_id, user_id, user_name, flat_no,
 *   area_name, purpose, booking_date, start_time, end_time,
 *   amount, payment_status (pending/paid/waived),
 *   status (pending/approved/rejected),
 *   approved_by, approved_at, created_at, updated_at
 */
class Events_booking_model extends CI_Model
{
	/* ════════════════════════════════════════════════════════════
	 *  ── EVENTS ──
	 * ════════════════════════════════════════════════════════════ */

	public function get_events($filters = [])
	{
		$this->db
			->select('events.*, societies.name AS society_name, users.name AS created_by_name')
			->from('events')
			->join('societies', 'societies.id = events.society_id', 'left')
			->join('users',     'users.id = events.created_by',    'left');

		$this->_event_filters($filters);

		return $this->db->order_by('events.event_date', 'DESC')->get()->result_array();
	}

	public function get_event_stats($filters = [])
	{
		$count = function ($extra = []) use ($filters) {
			$this->db->from('events')
				->join('societies', 'societies.id = events.society_id', 'left');
			$f = $filters; unset($f['status'], $f['event_type']);
			$this->_event_filters($f);
			foreach ($extra as $c => $v) $this->db->where($c, $v);
			return (int) $this->db->count_all_results();
		};

		return [
			'total'     => $count(),
			'upcoming'  => $count(['events.status' => 'upcoming']),
			'ongoing'   => $count(['events.status' => 'ongoing']),
			'completed' => $count(['events.status' => 'completed']),
			'fund_open' => $count(['events.fund_required' => 1, 'events.fund_status' => 'open']),
		];
	}

	public function get_event_by_id($id)
	{
		return $this->db
			->select('events.*, societies.name AS society_name, users.name AS created_by_name')
			->from('events')
			->join('societies', 'societies.id = events.society_id', 'left')
			->join('users',     'users.id = events.created_by',    'left')
			->where('events.id', (int) $id)
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
			->join('societies', 'societies.id = events.society_id', 'left');
		if (!empty($society_id)) $this->db->where('events.society_id', (int) $society_id);
		return $this->db->order_by('events.created_at', 'DESC')->limit($limit)->get()->result_array();
	}

	/* ── Fund contributions ── */

	public function get_contributions($event_id)
	{
		return $this->db->where('event_id', (int) $event_id)
			->order_by('created_at', 'DESC')
			->get('event_contributions')->result_array();
	}

	public function add_contribution($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('event_contributions', $data);
		$id = $this->db->insert_id();
		if ($id && isset($data['amount']) && ($data['payment_status'] ?? '') === 'paid') {
			$this->db->query(
				'UPDATE events SET fund_raised = COALESCE(fund_raised,0) + ? WHERE id = ?',
				[(float) $data['amount'], (int) $data['event_id']]
			);
		}
		return $id;
	}

	private function _event_filters($f = [])
	{
		if (!empty($f['society_id'])) $this->db->where('events.society_id', (int) $f['society_id']);
		if (!empty($f['status']))     $this->db->where('events.status',     $f['status']);
		if (!empty($f['event_type'])) $this->db->where('events.event_type', $f['event_type']);
		if (!empty($f['search'])) {
			$this->db->group_start()
				->like('events.title',       $f['search'])
				->or_like('events.venue',    $f['search'])
				->or_like('events.description', $f['search'])
				->group_end();
		}
	}

	/* ════════════════════════════════════════════════════════════
	 *  ── BOOKINGS ──
	 * ════════════════════════════════════════════════════════════ */

	public function get_bookings($filters = [])
	{
		$this->db
			->select('bookings.*, societies.name AS society_name, approver.name AS approver_name')
			->from('bookings')
			->join('societies',        'societies.id = bookings.society_id', 'left')
			->join('users AS approver', 'approver.id = bookings.approved_by', 'left');

		$this->_booking_filters($filters);

		return $this->db->order_by('bookings.created_at', 'DESC')->get()->result_array();
	}

	public function get_booking_stats($filters = [])
	{
		$count = function ($extra = []) use ($filters) {
			$this->db->from('bookings')
				->join('societies', 'societies.id = bookings.society_id', 'left');
			$f = $filters; unset($f['status'], $f['payment_status']);
			$this->_booking_filters($f);
			foreach ($extra as $c => $v) $this->db->where($c, $v);
			return (int) $this->db->count_all_results();
		};

		return [
			'total'    => $count(),
			'pending'  => $count(['bookings.status' => 'pending']),
			'approved' => $count(['bookings.status' => 'approved']),
			'rejected' => $count(['bookings.status' => 'rejected']),
			'paid'     => $count(['bookings.payment_status' => 'paid']),
		];
	}

	public function get_booking_by_id($id)
	{
		return $this->db
			->select('bookings.*, societies.name AS society_name, approver.name AS approver_name')
			->from('bookings')
			->join('societies',        'societies.id = bookings.society_id', 'left')
			->join('users AS approver', 'approver.id = bookings.approved_by', 'left')
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
			'status'      => $status,
			'approved_by' => $approved_by,
			'approved_at' => date('Y-m-d H:i:s'),
			'updated_at'  => date('Y-m-d H:i:s'),
		]);
	}

	public function mark_booking_paid($id)
	{
		return $this->db->where('id', (int) $id)->update('bookings', [
			'payment_status' => 'paid',
			'updated_at'     => date('Y-m-d H:i:s'),
		]);
	}

	public function check_clash($society_id, $area_name, $booking_date, $start_time, $end_time, $exclude_id = null)
	{
		$this->db->where('society_id',  (int) $society_id)
			->where('area_name',        $area_name)
			->where('booking_date',     $booking_date)
			->where('status !=',        'rejected')
			->group_start()
				->where('start_time <', $end_time)
				->where('end_time >',   $start_time)
			->group_end();
		if ($exclude_id) $this->db->where('id !=', (int) $exclude_id);
		return $this->db->count_all_results('bookings') > 0;
	}

	public function get_recent_bookings($society_id = null, $limit = 5)
	{
		$this->db->select('bookings.*, societies.name AS society_name')
			->from('bookings')
			->join('societies', 'societies.id = bookings.society_id', 'left');
		if (!empty($society_id)) $this->db->where('bookings.society_id', (int) $society_id);
		return $this->db->order_by('bookings.created_at', 'DESC')->limit($limit)->get()->result_array();
	}

	private function _booking_filters($f = [])
	{
		if (!empty($f['society_id']))    $this->db->where('bookings.society_id',     (int) $f['society_id']);
		if (!empty($f['user_id']))       $this->db->where('bookings.user_id',        (int) $f['user_id']);
		if (!empty($f['status']))        $this->db->where('bookings.status',         $f['status']);
		if (!empty($f['payment_status']))$this->db->where('bookings.payment_status', $f['payment_status']);
		if (!empty($f['search'])) {
			$this->db->group_start()
				->like('bookings.user_name',  $f['search'])
				->or_like('bookings.area_name', $f['search'])
				->or_like('bookings.flat_no',   $f['search'])
				->or_like('bookings.purpose',   $f['search'])
				->group_end();
		}
	}

	/* ════════════════════════════════════════════════════════════
	 *  ── SHARED ──
	 * ════════════════════════════════════════════════════════════ */

	public function get_societies()
	{
		return $this->db->select('id, name')->order_by('name')->get('societies')->result_array();
	}
}
