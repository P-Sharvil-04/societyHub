<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_model extends CI_Model
{
	/* ════════════════════════════════════════════════════════
	 *  FETCH — filtered list  (replaces get_all / get_by_society)
	 *
	 *  filters keys:
	 *    society_id   → int|''
	 *    designation  → string|''
	 *    status       → 'active'|'inactive'|'on-leave'|''
	 *    search       → LIKE on first_name, last_name, email, phone
	 * ════════════════════════════════════════════════════════ */

	public function get_filtered($filters = [])
	{
		$this->db
			->select('staff.*, users.name AS created_by_name, societies.name AS society_name')
			->from('staff')
			->join('users', 'users.id     = staff.created_by', 'left')
			->join('societies', 'societies.id = staff.society_id', 'left');

		if (!empty($filters['society_id'])) {
			$this->db->where('staff.society_id', (int) $filters['society_id']);
		}
		if (!empty($filters['designation'])) {
			$this->db->where('staff.designation', $filters['designation']);
		}
		if (isset($filters['status']) && $filters['status'] !== '') {
			$this->db->where('staff.status', $filters['status']);
		}
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$this->db->group_start()
				->like('staff.first_name', $s)
				->or_like('staff.last_name', $s)
				->or_like('staff.email', $s)
				->or_like('staff.phone', $s)
				->group_end();
		}

		return $this->db
			->order_by('societies.name', 'ASC')
			->order_by('staff.created_at', 'DESC')
			->get()->result_array();
	}

	/* ════════════════════════════════════════════════════════
	 *  STATS — scoped to same filters as list
	 *  Returns: total, active, inactive, on_leave,
	 *           security, housekeeping, maintenance, new_this_month
	 * ════════════════════════════════════════════════════════ */

	public function get_stats($filters = [])
	{
		$this->db->select('staff.status, staff.department, staff.created_at')
			->from('staff');

		if (!empty($filters['society_id'])) {
			$this->db->where('staff.society_id', (int) $filters['society_id']);
		}
		if (!empty($filters['designation'])) {
			$this->db->where('staff.designation', $filters['designation']);
		}
		if (isset($filters['status']) && $filters['status'] !== '') {
			$this->db->where('staff.status', $filters['status']);
		}
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$this->db->group_start()
				->like('staff.first_name', $s)
				->or_like('staff.last_name', $s)
				->or_like('staff.email', $s)
				->or_like('staff.phone', $s)
				->group_end();
		}

		$rows = $this->db->get()->result_array();

		$total = count($rows);
		$active = 0;
		$inactive = 0;
		$on_leave = 0;
		$security = 0;
		$housekeeping = 0;
		$maintenance = 0;
		$new_this_month = 0;
		$month = date('Y-m');

		foreach ($rows as $r) {
			$status = strtolower($r['status'] ?? '');
			$dept = strtolower($r['department'] ?? '');

			if ($status === 'active')
				$active++;
			if ($status === 'inactive')
				$inactive++;
			if ($status === 'on-leave')
				$on_leave++;

			if ($dept === 'security')
				$security++;
			if ($dept === 'housekeeping')
				$housekeeping++;
			if ($dept === 'maintenance')
				$maintenance++;

			if (!empty($r['created_at']) && substr($r['created_at'], 0, 7) === $month) {
				$new_this_month++;
			}
		}

		return compact('total', 'active', 'inactive', 'on_leave', 'security', 'housekeeping', 'maintenance', 'new_this_month');
	}

	/* ════════════════════════════════════════════════════════
	 *  SINGLE RECORD
	 * ════════════════════════════════════════════════════════ */

	public function get($id)
	{
		return $this->db
			->select('staff.*, users.name AS created_by_name, societies.name AS society_name')
			->from('staff')
			->join('users', 'users.id     = staff.created_by', 'left')
			->join('societies', 'societies.id = staff.society_id', 'left')
			->where('staff.id', $id)
			->get()->row_array();
	}

	public function get_by_id_and_society($id, $society_id)
	{
		return $this->db
			->select('staff.*, users.name AS created_by_name, societies.name AS society_name')
			->from('staff')
			->join('users', 'users.id     = staff.created_by', 'left')
			->join('societies', 'societies.id = staff.society_id', 'left')
			->where('staff.id', $id)
			->where('staff.society_id', $society_id)
			->get()->row_array();
	}

	/* ════════════════════════════════════════════════════════
	 *  RECENT STAFF (sidebar panel)
	 * ════════════════════════════════════════════════════════ */

	public function get_recent_staff($society_id, $limit = 5)
	{
		return $this->db
			->select('staff.*, societies.name AS society_name')
			->from('staff')
			->join('societies', 'societies.id = staff.society_id', 'left')
			->where('staff.society_id', $society_id)
			->order_by('staff.created_at', 'DESC')
			->limit($limit)
			->get()->result_array();
	}

	public function get_recent_staffs($limit = 5)
	{
		return $this->db
			->select('staff.*, societies.name AS society_name')
			->from('staff')
			->join('societies', 'societies.id = staff.society_id', 'left')
			->order_by('staff.created_at', 'DESC')
			->limit($limit)
			->get()->result_array();
	}

	/* ════════════════════════════════════════════════════════
	 *  SOCIETIES LIST
	 * ════════════════════════════════════════════════════════ */

	public function get_all_soc()
	{
		return $this->db
			->select('id, name')
			->from('societies')
			->order_by('name', 'ASC')
			->get()->result_array();
	}

	/* ════════════════════════════════════════════════════════
	 *  WRITE
	 * ════════════════════════════════════════════════════════ */

	public function insert_staff($data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('staff', $data);
		return $this->db->insert_id();
	}

	public function update_staff($id, $data, $society_id = null)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		$this->db->where('id', $id);
		if ($society_id !== null) {
			$this->db->where('society_id', $society_id);
		}
		return $this->db->update('staff', $data);
	}

	public function delete_staff($id, $society_id = null)
	{
		$this->db->where('id', $id);
		if ($society_id !== null) {
			$this->db->where('society_id', $society_id);
		}
		return $this->db->delete('staff');
	}

	/* ════════════════════════════════════════════════════════
	 *  ANALYTICS (kept for other use)
	 * ════════════════════════════════════════════════════════ */

	public function get_staff_count_per_society()
	{
		return $this->db
			->select("societies.id AS society_id, societies.name AS society_name,
				COUNT(staff.id) AS total,
				SUM(CASE WHEN staff.status = 'active'   THEN 1 ELSE 0 END) AS active,
				SUM(CASE WHEN staff.status = 'inactive' THEN 1 ELSE 0 END) AS inactive,
				SUM(CASE WHEN staff.status = 'on-leave' THEN 1 ELSE 0 END) AS on_leave")
			->from('societies')
			->join('staff', 'staff.society_id = societies.id', 'left')
			->group_by('societies.id')
			->order_by('societies.name', 'ASC')
			->get()->result_array();
	}
}
