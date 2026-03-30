<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class complaints_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* ══════════════════════════════════════
	 *  FETCH — MULTIPLE RECORDS
	 * ══════════════════════════════════════ */

	/**
	 * All complaints with backend filters.
	 * society_id  → scope to one society (pass '' or null for all)
	 * status      → filter by status
	 * category    → filter by category
	 * search      → LIKE on complaint_id, user_name, flat, title
	 */
	public function get_filtered($filters = [])
	{
		$this->db
			->select('complaints.*, societies.name AS society_name')
			->from('complaints')
			->join('societies', 'societies.id = complaints.society_id', 'left');

		if (!empty($filters['society_id'])) {
			$this->db->where('complaints.society_id', (int) $filters['society_id']);
		}
		if (!empty($filters['status'])) {
			$this->db->where('complaints.status', $filters['status']);
		}
		if (!empty($filters['category'])) {
			$this->db->where('complaints.category', $filters['category']);
		}
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$this->db->group_start()
				->like('complaints.complaint_id', $s)
				->or_like('complaints.user_name', $s)
				->or_like('complaints.flat', $s)
				->or_like('complaints.title', $s)
				->group_end();
		}

		return $this->db
			->order_by('complaints.created_at', 'DESC')
			->get()->result_array();
	}

	/**
	 * Owner's own complaints (+ optional status/category/search filter).
	 */
	public function get_by_user($user_id, $filters = [])
	{
		$this->db
			->select('complaints.*, societies.name AS society_name')
			->from('complaints')
			->join('societies', 'societies.id = complaints.society_id', 'left')
			->where('complaints.user_id', (int) $user_id);

		if (!empty($filters['status'])) {
			$this->db->where('complaints.status', $filters['status']);
		}
		if (!empty($filters['category'])) {
			$this->db->where('complaints.category', $filters['category']);
		}
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$this->db->group_start()
				->like('complaints.complaint_id', $s)
				->or_like('complaints.title', $s)
				->group_end();
		}

		return $this->db
			->order_by('complaints.created_at', 'DESC')
			->get()->result_array();
	}

	/* ══════════════════════════════════════
	 *  FETCH — SINGLE RECORD
	 * ══════════════════════════════════════ */

	public function get_by_id($id)
	{
		return $this->db
			->select('complaints.*, societies.name AS society_name')
			->from('complaints')
			->join('societies', 'societies.id = complaints.society_id', 'left')
			->where('complaints.id', (int) $id)
			->get()->row_array();
	}

	/* ══════════════════════════════════════
	 *  WRITE
	 * ══════════════════════════════════════ */

	public function insert_complaint($data)
	{
		$this->db->insert('complaints', $data);
		return $this->db->insert_id();
	}

	public function update_complaint($id, $data)
	{
		if (!empty($data['status']) && in_array($data['status'], ['resolved', 'closed'])) {
			$data['resolved_by'] = $this->session->userdata('user_id');
			$data['resolved_at'] = date('Y-m-d H:i:s');
		}
		return $this->db->where('id', (int) $id)->update('complaints', $data);
	}

	public function delete_complaint($id)
	{
		return $this->db->delete('complaints', ['id' => (int) $id]);
	}

	/* ══════════════════════════════════════
	 *  STATS
	 * ══════════════════════════════════════ */

	/**
	 * Stats for admins — pass society_id to scope, null for all.
	 */
	public function get_stats($society_id = null)
	{
		$w = function () use ($society_id) {
			if ($society_id)
				$this->db->where('society_id', (int) $society_id);
		};

		$w();
		$total = $this->db->count_all_results('complaints');
		$this->db->where('status', 'pending');
		$w();
		$pending = $this->db->count_all_results('complaints');
		$this->db->where('status', 'in-progress');
		$w();
		$in_progress = $this->db->count_all_results('complaints');
		$this->db->where('status', 'resolved');
		$w();
		$resolved = $this->db->count_all_results('complaints');
		$this->db->where('status', 'closed');
		$w();
		$closed = $this->db->count_all_results('complaints');

		return compact('total', 'pending', 'in_progress', 'resolved', 'closed');
	}

	/**
	 * Stats scoped to a single owner.
	 */
	public function get_stats_by_user($user_id)
	{
		$w = function () use ($user_id) {
			$this->db->where('user_id', (int) $user_id);
		};

		$w();
		$total = $this->db->count_all_results('complaints');
		$this->db->where('status', 'pending');
		$w();
		$pending = $this->db->count_all_results('complaints');
		$this->db->where('status', 'in-progress');
		$w();
		$in_progress = $this->db->count_all_results('complaints');
		$this->db->where('status', 'resolved');
		$w();
		$resolved = $this->db->count_all_results('complaints');
		$this->db->where('status', 'closed');
		$w();
		$closed = $this->db->count_all_results('complaints');

		return compact('total', 'pending', 'in_progress', 'resolved', 'closed');
	}

	/* ══════════════════════════════════════
	 *  USERS / MEMBERS
	 * ══════════════════════════════════════ */

	/**
	 * Get a single user by id (used to pre-fill owner info).
	 */
	public function get_user_by_id($id)
	{
		return $this->db->get_where('users', ['id' => (int) $id])->row_array();
	}

	/**
	 * Owner-role members list for admin add/edit dropdown.
	 * Pass society_id to restrict to one society; null = all societies.
	 */
	public function get_members($society_id = null)
	{
		$this->db
			->select('users.id, users.name, users.flat_no, users.society_id, societies.name AS society_name')
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id')
			->join('roles', 'roles.id = user_roles.role_id')
			->join('societies', 'societies.id = users.society_id', 'left')
			->where('roles.role_name', 'owner');

		if ($society_id) {
			$this->db->where('users.society_id', (int) $society_id);
		}

		return $this->db
			->order_by('societies.name, users.name', 'ASC')
			->get()->result_array();
	}

	/* ══════════════════════════════════════
	 *  SOCIETIES
	 * ══════════════════════════════════════ */

	public function get_societies()
	{
		return $this->db
			->select('id, name')
			->order_by('name', 'ASC')
			->get('societies')->result_array();
	}

	/* ══════════════════════════════════════
	 *  GENERATE COMPLAINT ID
	 * ══════════════════════════════════════ */

	public function generate_complaint_id()
	{
		$year = date('Y');
		$last = $this->db
			->select('complaint_id')
			->like('complaint_id', "CMP-$year-", 'after')
			->order_by('complaint_id', 'DESC')
			->get('complaints', 1)->row_array();

		$num = $last
			? str_pad((int) substr($last['complaint_id'], -3) + 1, 3, '0', STR_PAD_LEFT)
			: '001';

		return "CMP-$year-$num";
	}
}
