<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parking_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_all_parking(int $society_id): array
	{
		$this->db->select('
            p.*,
            u.name        AS owner_name,
            u.flat_no     AS flat_no,
            u.phone       AS owner_phone,
            u.member_type AS owner_type,
            u.wing_id     AS owner_wing_id,
            w.wing_name   AS wing_name,
            a.name        AS allocated_by_name
        ');
		$this->db->from('parking p');
		$this->db->join('users u', 'u.id = p.owner_id', 'left');
		$this->db->join('wings w', 'w.id = u.wing_id', 'left');
		$this->db->join('users a', 'a.id = p.allocated_by', 'left');
		$this->db->where('p.society_id', $society_id);
		$this->db->order_by('p.allocated_at', 'DESC');
		return $this->db->get()->result();
	}

	public function get_owner_parking(int $owner_id, int $society_id): array
	{
		$this->db->select('
            p.*,
            u.flat_no   AS flat_no,
            w.wing_name AS wing_name,
            a.name      AS allocated_by_name
        ');
		$this->db->from('parking p');
		$this->db->join('users u', 'u.id = p.owner_id', 'left');
		$this->db->join('wings w', 'w.id = u.wing_id', 'left');
		$this->db->join('users a', 'a.id = p.allocated_by', 'left');
		$this->db->where('p.owner_id', $owner_id);
		$this->db->where('p.society_id', $society_id);
		$this->db->order_by('p.allocated_at', 'DESC');
		return $this->db->get()->result();
	}

	public function slot_exists(string $slot_number, int $society_id): ?object
	{
		return $this->db->get_where('parking', [
			'slot_number' => $slot_number,
			'society_id' => $society_id,
		])->row();
	}

	public function assign(array $data): bool
	{
		return $this->db->insert('parking', [
			'society_id' => $data['society_id'],
			'owner_id' => $data['owner_id'],
			'vehicle_number' => $data['vehicle_number'],
			'vehicle_type' => $data['vehicle_type'],
			'slot_number' => $data['slot_number'],
			'allocated_by' => $data['allocated_by'],
			'allocated_at' => date('Y-m-d H:i:s'),
		]);
	}

	public function revoke(int $id, int $society_id): bool
	{
		$this->db->where('id', $id);
		$this->db->where('society_id', $society_id);
		return $this->db->delete('parking');
	}

	public function get_members(int $society_id): array
	{
		$this->db->select('u.id, u.name, u.flat_no, u.member_type, w.wing_name');
		$this->db->from('users u');
		$this->db->join('wings w', 'w.id = u.wing_id', 'left');
		$this->db->where('u.society_id', $society_id);
		$this->db->where_in('u.member_type', ['owner', 'tenant']);
		$this->db->where('u.status', 1);
		$this->db->order_by('w.wing_name ASC, u.flat_no ASC');
		return $this->db->get()->result();
	}

	public function get_stats(int $society_id): array
	{
		$total = $this->db->where('society_id', $society_id)->count_all_results('parking');
		$four_wheel = $this->db->where('society_id', $society_id)->where('vehicle_type', '4-Wheeler')->count_all_results('parking');
		$two_wheel = $this->db->where('society_id', $society_id)->where('vehicle_type', '2-Wheeler')->count_all_results('parking');
		$members = $this->db->where('society_id', $society_id)->where_in('member_type', ['owner', 'tenant'])->count_all_results('users');

		return compact('total', 'four_wheel', 'two_wheel', 'members');
	}

	public function get_all_societies(): array
	{
		return $this->db->select('id, name')
			->from('societies')
			->order_by('name')
			->get()
			->result();
	}
}
