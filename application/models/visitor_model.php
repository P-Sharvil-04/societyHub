<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visitor_model extends CI_Model
{
	private $table = 'visitors';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* ══════════════════════════════════════════════
	 *  FETCH – filtered list with pagination
	 * ══════════════════════════════════════════════ */
	public function get_visitors($filters = [], $limit = 10, $offset = 0)
	{
		$this->db
			->select('visitors.*, societies.name AS society_name, u.name AS approver_name')
			->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left')
			->join('users u', 'u.id = visitors.approved_by', 'left');

		$this->_apply_filters($filters);

		return $this->db
			->order_by('visitors.entry_time', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get()->result();
	}

	public function count_visitors($filters = [])
	{
		$this->db->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left');

		$this->_apply_filters($filters);

		return (int) $this->db->count_all_results();
	}

	/* ══════════════════════════════════════════════
	 *  STATS
	 * ══════════════════════════════════════════════ */
	public function get_stats($filters = [])
	{
		$count = function ($status = null, $approval = null) use ($filters) {
			$this->db->from($this->table)
				->join('societies', 'societies.id = visitors.society_id', 'left');

			$scopeFilters = $filters;
			unset($scopeFilters['status'], $scopeFilters['approval_status']);
			$this->_apply_filters($scopeFilters);

			if ($status !== null)
				$this->db->where('visitors.status', $status);
			if ($approval !== null)
				$this->db->where('visitors.approval_status', $approval);

			return (int) $this->db->count_all_results();
		};

		return [
			'total' => $count(null, null),
			'checked_in' => $count('Checked In', null),
			'checked_out' => $count('Checked Out', null),
			'pending_approval' => $count('Pending', 'pending'),
		];
	}

	/* ══════════════════════════════════════════════
	 *  SINGLE RECORD
	 * ══════════════════════════════════════════════ */
	public function get_visitor_by_id($id)
	{
		return $this->db
			->select('visitors.*, societies.name AS society_name, u.name AS approver_name')
			->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left')
			->join('users u', 'u.id = visitors.approved_by', 'left')
			->where('visitors.id', (int) $id)
			->get()->row_array();
	}

	/* ══════════════════════════════════════════════
	 *  WRITE
	 * ══════════════════════════════════════════════ */
	public function insert_visitor($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$ok = $this->db->insert($this->table, $data);
		return $ok ? $this->db->insert_id() : false;
	}

	public function update_visitor($id, $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		return (bool) $this->db->where('id', (int) $id)->update($this->table, $data);
	}

	public function delete_visitor($id)
	{
		return (bool) $this->db->where('id', (int) $id)->delete($this->table);
	}

	/* ══════════════════════════════════════════════
	 *  APPROVAL ACTIONS
	 * ══════════════════════════════════════════════ */
	public function approve_visitor($id, $approved_by)
	{
		return (bool) $this->db->where('id', (int) $id)->update($this->table, [
			'approval_status' => 'approved',
			'status' => 'Checked In',
			'approved_by' => (int) $approved_by,
			'approved_at' => date('Y-m-d H:i:s'),
			'rejection_reason' => null,
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}

	public function reject_visitor($id, $approved_by, $reason = null)
	{
		return (bool) $this->db->where('id', (int) $id)->update($this->table, [
			'approval_status' => 'rejected',
			'status' => 'Pending',
			'approved_by' => (int) $approved_by,
			'approved_at' => date('Y-m-d H:i:s'),
			'rejection_reason' => $reason,
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}

	/* ══════════════════════════════════════════════
	 *  CHECKOUT
	 * ══════════════════════════════════════════════ */
	public function checkout_visitor($id)
	{
		return (bool) $this->db->where('id', (int) $id)->update($this->table, [
			'status' => 'Checked Out',
			'exit_time' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}

	/* ══════════════════════════════════════════════
	 *  RECENT
	 * ══════════════════════════════════════════════ */
	public function get_recent_visitors($society_id = null, $limit = 5)
	{
		$this->db
			->select('visitors.*, societies.name AS society_name')
			->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left');

		if (!empty($society_id)) {
			$this->db->where('visitors.society_id', (int) $society_id);
		}

		return $this->db
			->order_by('visitors.entry_time', 'DESC')
			->limit((int) $limit)
			->get()->result();
	}

	/* ══════════════════════════════════════════════
	 *  SOCIETIES LIST
	 * ══════════════════════════════════════════════ */
	public function get_societies()
	{
		return $this->db
			->select('id, name')
			->order_by('name', 'ASC')
			->get('societies')->result_array();
	}

	/* ══════════════════════════════════════════════
	 *  OWNER FLAT
	 * ══════════════════════════════════════════════ */
	public function get_owner_flats($user_id)
	{
		return $this->db
			->select('flat_no')
			->from('users')
			->where('id', (int) $user_id)
			->get()->row_array();
	}

	/* ══════════════════════════════════════════════
	 *  PRIVATE – WHERE builder
	 * ══════════════════════════════════════════════ */
	private function _apply_filters($filters = [])
	{
		if (!empty($filters['society_id'])) {
			$this->db->where('visitors.society_id', (int) $filters['society_id']);
		}
		if (!empty($filters['status']) && $filters['status'] !== 'all') {
			$this->db->where('visitors.status', $filters['status']);
		}
		if (!empty($filters['approval_status']) && $filters['approval_status'] !== 'all') {
			$this->db->where('visitors.approval_status', $filters['approval_status']);
		}
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$this->db->group_start()
				->like('visitors.visitor_name', $s)
				->or_like('visitors.flat', $s)
				->or_like('visitors.purpose', $s)
				->or_like('visitors.phone', $s)
				->group_end();
		}
		if (!empty($filters['owner_flat'])) {
			$this->db->where('visitors.flat', $filters['owner_flat']);
		}
	}
}
