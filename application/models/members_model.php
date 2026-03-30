<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class members_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* ══════════════════════════════════════════
	 *  FETCH — MULTIPLE RECORDS
	 * ══════════════════════════════════════════ */

	/**
	 * Filtered member list.
	 *
	 * filters:
	 *   society_id  → scope to one society  ('' = all)
	 *   role        → 'owner' | 'chairman'  ('' = both)
	 *   status      → 'active'|'inactive'   ('' = all)
	 *   search      → LIKE on name, email, phone, flat_no
	 */
	public function get_filtered($filters = [])
	{
		$this->db
			->select('
				users.id,
				users.name,
				users.email,
				users.phone,
				users.flat_no,
				users.status,
				users.society_id,
				users.created_at,
				societies.name   AS society_name,
				roles.role_name  AS role_name
			')
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id',      'left')
			->join('roles',      'roles.id = user_roles.role_id',      'left')
			->join('societies',  'societies.id = users.society_id',    'left')
			// Only owner and chairman roles
			->where_in('roles.role_name', ['owner', 'chairman']);

		if (!empty($filters['society_id'])) {
			$this->db->where('users.society_id', (int) $filters['society_id']);
		}
		if (!empty($filters['role'])) {
			$this->db->where('roles.role_name', $filters['role']);
		}
		if (!empty($filters['status'])) {
			$this->db->where('users.status', $filters['status']);
		}
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$this->db->group_start()
				->like('users.name',    $s)
				->or_like('users.email',   $s)
				->or_like('users.phone',   $s)
				->or_like('users.flat_no', $s)
				->group_end();
		}

		return $this->db
			->order_by('societies.name, roles.role_name DESC, users.name', 'ASC')
			->get()->result_array();
	}

	/* ══════════════════════════════════════════
	 *  FETCH — SINGLE RECORD
	 * ══════════════════════════════════════════ */

	public function get_by_id($id)
	{
		return $this->db
			->select('users.*, societies.name AS society_name, roles.role_name AS role_name')
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id',   'left')
			->join('roles',      'roles.id = user_roles.role_id',   'left')
			->join('societies',  'societies.id = users.society_id', 'left')
			->where('users.id', (int) $id)
			->get()->row_array();
	}

	/* ══════════════════════════════════════════
	 *  WRITE
	 * ══════════════════════════════════════════ */

	/**
	 * Insert a new user + assign role.
	 * Returns new user id on success, false on failure.
	 */
	public function insert_member($user_data, $role_name)
	{
		$user_data['created_at'] = date('Y-m-d H:i:s');

		$this->db->insert('users', $user_data);
		$new_id = $this->db->insert_id();

		if (!$new_id) return false;

		// Assign role
		$role = $this->db->get_where('roles', ['role_name' => $role_name])->row_array();
		if ($role) {
			$this->db->insert('user_roles', [
				'user_id' => $new_id,
				'role_id' => $role['id'],
			]);
		}

		return $new_id;
	}

	/**
	 * Update user details + update role assignment.
	 */
	public function update_member($id, $user_data, $role_name)
	{
		$user_data['updated_at'] = date('Y-m-d H:i:s');

		$this->db->where('id', (int) $id)->update('users', $user_data);

		// Update role
		$role = $this->db->get_where('roles', ['role_name' => $role_name])->row_array();
		if ($role) {
			// Check if user_roles row exists
			$existing = $this->db->get_where('user_roles', ['user_id' => (int) $id])->row_array();
			if ($existing) {
				$this->db->where('user_id', (int) $id)->update('user_roles', ['role_id' => $role['id']]);
			} else {
				$this->db->insert('user_roles', ['user_id' => (int) $id, 'role_id' => $role['id']]);
			}
		}

		return $this->db->affected_rows() >= 0;
	}

	/**
	 * Delete user + their role assignment.
	 */
	public function delete_member($id)
	{
		$this->db->delete('user_roles', ['user_id' => (int) $id]);
		return $this->db->delete('users', ['id' => (int) $id]);
	}

	/* ══════════════════════════════════════════
	 *  VALIDATION HELPERS
	 * ══════════════════════════════════════════ */

	/**
	 * Check if email already used by another user.
	 * exclude_id = 0 means new user (no exclusion needed).
	 */
	public function email_exists($email, $exclude_id = 0)
	{
		$this->db->where('email', $email);
		if ($exclude_id) {
			$this->db->where('id !=', (int) $exclude_id);
		}
		return $this->db->count_all_results('users') > 0;
	}

	/* ══════════════════════════════════════════
	 *  STATS
	 * ══════════════════════════════════════════ */

	/**
	 * Count totals for stat cards.
	 * Pass society_id to scope; null = all societies.
	 */
	public function get_stats($society_id = null)
	{
		$scope = function () use ($society_id) {
			$this->db
				->from('users')
				->join('user_roles', 'user_roles.user_id = users.id', 'left')
				->join('roles',      'roles.id = user_roles.role_id', 'left')
				->where_in('roles.role_name', ['owner', 'chairman']);
			if ($society_id) $this->db->where('users.society_id', (int) $society_id);
		};

		$scope(); $total    = $this->db->count_all_results();
		$scope(); $this->db->where('roles.role_name', 'owner');    $members   = $this->db->count_all_results();
		$scope(); $this->db->where('roles.role_name', 'chairman'); $chairmen  = $this->db->count_all_results();
		$scope(); $this->db->where('users.status', 'active');      $active    = $this->db->count_all_results();
		$scope(); $this->db->where('users.status', 'inactive');    $inactive  = $this->db->count_all_results();

		return compact('total', 'members', 'chairmen', 'active', 'inactive');
	}

	/* ══════════════════════════════════════════
	 *  SOCIETIES
	 * ══════════════════════════════════════════ */

	public function get_societies()
	{
		return $this->db
			->select('id, name')
			->order_by('name', 'ASC')
			->get('societies')->result_array();
	}

	public function get_society_by_id($id)
	{
		return $this->db->get_where('societies', ['id' => (int) $id])->row_array();
	}
}
