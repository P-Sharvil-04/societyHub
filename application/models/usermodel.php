<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usermodel extends CI_Model
{
	public function register($data)
	{
		$this->db->insert('users', $data);
		return $this->db->insert_id();
	}

	// ── Fetch user + their role in one query ─────────────────────────
	// Returns: user row with extra columns `role_id` and `role_name`
	public function get_user_by_email($email)
	{
		return $this->db
			->select('users.*, roles.id as role_id, roles.role_name as role_name')
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id', 'left')
			->join('roles', 'roles.id = user_roles.role_id', 'left')
			->where('users.email', $email)
			->where('users.status', 1)
			->get()
			->row();
	}
	public function get_user_roles($user_id)
	{
		$roles = $this->db
			->select('roles.role_name')
			->from('user_roles')
			->join('roles', 'roles.id = user_roles.role_id')
			->where('user_roles.user_id', $user_id)
			->get()
			->result();

		$roleArray = [];

		foreach ($roles as $role) {
			$roleArray[] = $role->role_name;
		}

		return $roleArray;
	}
	// ── Misc helpers (unchanged logic, fixed table names) ────────────
	public function insert_expense($data)
	{
		return $this->db->insert('expenses', $data);
	}

	public function get_roles()
	{
		return $this->db->get('roles')->result();
	}

	public function get_wings()
	{
		return $this->db->get('wings')->result();
	}
	public function login($email, $password)
	{
		$admin = $this->db
			->where('email', $email)
			->get('super_admin')
			->row();

		if ($admin && $password) {
			return $admin;
		}

		return false;
	}

}

?>
