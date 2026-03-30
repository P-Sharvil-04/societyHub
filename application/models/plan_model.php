<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class plan_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	// Insert into `societies` table
	public function add_society($data)
	{
		$this->db->insert('societies', $data);
		return $this->db->insert_id();
	}

	// Insert a wing / bungalow record
	public function add_wing($data)
	{
		$this->db->insert('wings', $data);
		return $this->db->insert_id();
	}

	// Insert into `users` table (admin)
	public function create_admin($data)
	{
		$this->db->insert('users', $data);
		return $this->db->insert_id();
	}

	// Assign role to user via `user_roles` table
	public function assign_role($user_id, $role_id)
	{
		// avoid duplicate insert
		$exists = $this->db->get_where('user_roles', ['user_id' => (int) $user_id, 'role_id' => (int) $role_id])->row();
		if ($exists)
			return $exists->id;

		$this->db->insert('user_roles', [
			'user_id' => (int) $user_id,
			'role_id' => (int) $role_id,
			'created_at' => date('Y-m-d H:i:s')
		]);
		return $this->db->insert_id();
	}

	// --- NEW HELPERS ---

	/**
	 * Get all wings for a society (returns array of objects)
	 */
	public function get_wings_by_society($society_id)
	{
		return $this->db
			->where('society_id', (int) $society_id)
			->order_by('id', 'ASC')
			->get('wings')
			->result();
	}

	/**
	 * Get a single wing row by id
	 */
	public function get_wing($id)
	{
		return $this->db->where('id', (int) $id)->get('wings')->row();
	}
}
