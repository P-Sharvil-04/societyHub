<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_model extends CI_Model
{
	private $security_designations = ['Security Guard', 'Senior Security'];

	/* ════════════════════════════════════════════════════════
	 *  FETCH — filtered list (now with pagination support)
	 * ════════════════════════════════════════════════════════ */

	public function get_filtered($filters = [], $limit = null, $offset = 0)
	{
		$this->db
			->select('staff.*, u.name AS created_by_name, societies.name AS society_name')
			->from('staff')
			->join('users u', 'u.id = staff.created_by', 'left')
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

		$this->db->order_by('societies.name', 'ASC')
			->order_by('staff.created_at', 'DESC');

		if ($limit !== null) {
			$this->db->limit($limit, $offset);
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Count total records matching filters (for pagination)
	 */
	public function count_filtered($filters = [])
	{
		$this->db->from('staff');

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

		return $this->db->count_all_results();
	}

	/* ════════════════════════════════════════════════════════
	 *  STATS — scoped to same filters as list
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
			->select('staff.*, u.name AS created_by_name, societies.name AS society_name')
			->from('staff')
			->join('users u', 'u.id = staff.created_by', 'left')
			->join('societies', 'societies.id = staff.society_id', 'left')
			->where('staff.id', $id)
			->get()->row_array();
	}

	public function get_by_id_and_society($id, $society_id)
	{
		return $this->db
			->select('staff.*, u.name AS created_by_name, societies.name AS society_name')
			->from('staff')
			->join('users u', 'u.id = staff.created_by', 'left')
			->join('societies', 'societies.id = staff.society_id', 'left')
			->where('staff.id', $id)
			->where('staff.society_id', $society_id)
			->get()->row_array();
	}

	/* ════════════════════════════════════════════════════════
	 *  RECENT STAFF 
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
	 *  WRITE — staff table
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
	 *  USER ACCOUNT — login management for staff
	 * ════════════════════════════════════════════════════════ */

	public function resolve_role_id($designation)
	{
		return in_array($designation, $this->security_designations) ? 8 : 9;
	}

	public function user_exists_by_email($email)
	{
		return (bool) $this->db
			->where('email', $email)
			->count_all_results('users');
	}

	private function _get_user_id_by_email($email)
	{
		$row = $this->db
			->select('id')
			->from('users')
			->where('email', $email)
			->get()->row_array();

		return $row ? (int) $row['id'] : null;
	}

	public function create_staff_user($staff, $plain_pass)
	{
		if ($this->user_exists_by_email($staff['email'])) {
			return false;
		}

		$name = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));

		$this->db->insert('users', [
			'name' => $name,
			'email' => $staff['email'],
			'password' => password_hash($plain_pass, PASSWORD_BCRYPT),
			'society_id' => $staff['society_id'] ?? null,
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
		]);

		$user_id = $this->db->insert_id();
		if (!$user_id) {
			return false;
		}

		$this->db->insert('user_roles', [
			'user_id' => $user_id,
			'role_id' => $this->resolve_role_id($staff['designation']),
		]);

		return $user_id;
	}

	public function update_staff_user($old_email, $staff, $new_pass = null)
	{
		$user_id = $this->_get_user_id_by_email($old_email);
		if (!$user_id) {
			return false;
		}

		$name = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));

		$update = [
			'name' => $name,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		$new_email = $staff['email'] ?? $old_email;
		if ($new_email !== $old_email) {
			$update['email'] = $new_email;
		}

		if (!empty($new_pass)) {
			$update['password'] = password_hash($new_pass, PASSWORD_BCRYPT);
		}

		$this->db->where('id', $user_id)->update('users', $update);

		$new_role_id = $this->resolve_role_id($staff['designation']);
		$existing_role = $this->db
			->select('role_id')
			->where('user_id', $user_id)
			->get('user_roles')->row_array();

		if ($existing_role) {
			if ((int) $existing_role['role_id'] !== $new_role_id) {
				$this->db->where('user_id', $user_id)
					->update('user_roles', ['role_id' => $new_role_id]);
			}
		} else {
			$this->db->insert('user_roles', [
				'user_id' => $user_id,
				'role_id' => $new_role_id,
			]);
		}

		return true;
	}

	public function delete_staff_user($email)
	{
		$user_id = $this->_get_user_id_by_email($email);
		if (!$user_id) {
			return false;
		}

		$role_row = $this->db
			->select('role_id')
			->where('user_id', $user_id)
			->get('user_roles')->row_array();

		if (!$role_row || !in_array((int) $role_row['role_id'], [8, 9])) {
			return false;
		}

		$this->db->where('user_id', $user_id)->delete('user_roles');
		$this->db->where('id', $user_id)->delete('users');

		return true;
	}

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
