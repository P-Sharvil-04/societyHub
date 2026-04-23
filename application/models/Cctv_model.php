<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cctv_model extends CI_Model
{
	public function get_user_access($user_id)
	{
		return $this->db
			->select('u.id as user_id, u.society_id, r.role_name')
			->from('users u')
			->join('user_roles ur', 'ur.user_id = u.id', 'left')
			->join('roles r', 'r.id = ur.role_id', 'left')
			->where('u.id', (int) $user_id)
			->get()
			->row();
	}

	public function get_all_cameras($society_id = null)
	{
		$this->db->from('cctv_cameras');

		if ($society_id !== null) {
			$this->db->where('society_id', (int) $society_id);
		}

		$this->db->order_by('sort_order', 'ASC');
		$this->db->order_by('id', 'ASC');

		return $this->db->get()->result();
	}

	public function insert_camera($data)
	{
		return $this->db->insert('cctv_cameras', $data);
	}

	public function delete_camera($id, $society_id = null)
	{
		$this->db->where('id', (int) $id);

		if ($society_id !== null) {
			$this->db->where('society_id', (int) $society_id);
		}

		return $this->db->delete('cctv_cameras');
	}

	public function get_camera_by_id($id)
	{
		return $this->db
			->where('id', (int) $id)
			->get('cctv_cameras')
			->row();
	}
}
