<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class amenity_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	// ---------- Amenities ----------
	public function get_amenities($user_id, $filters = [])
	{
		$this->db->where('user_id', $user_id);

		if (!empty($filters['search'])) {
			$this->db->group_start()
				->like('name', $filters['search'])
				->or_like('description', $filters['search'])
				->or_like('location', $filters['search'])
				->or_like('amenity_id', $filters['search'])
				->group_end();
		}
		if (!empty($filters['status'])) {
			$this->db->where('status', $filters['status']);
		}
		if (!empty($filters['category'])) {
			$this->db->where('category', $filters['category']);
		}
		// Price filter handled in controller because it's not a direct column
		$this->db->order_by('created_at', 'DESC');
		return $this->db->get('amenities')->result_array();
	}

	public function get_amenity($id, $user_id)
	{
		return $this->db->get_where('amenities', ['id' => $id, 'user_id' => $user_id])->row_array();
	}

	public function insert_amenity($data)
	{
		$this->db->insert('amenities', $data);
		return $this->db->insert_id();
	}

	public function update_amenity($id, $data, $user_id)
	{
		$this->db->where('id', $id);
		$this->db->where('user_id', $user_id);
		return $this->db->update('amenities', $data);
	}

	public function delete_amenity($id, $user_id)
	{
		return $this->db->delete('amenities', ['id' => $id, 'user_id' => $user_id]);
	}

	// ---------- Statistics ----------
	public function get_stats($user_id)
	{
		$today = date('Y-m-d');

		$this->db->where('user_id', $user_id);
		$total = $this->db->count_all_results('amenities');

		$this->db->where('user_id', $user_id);
		$this->db->where('status', 'available');
		$available = $this->db->count_all_results('amenities');

		$this->db->where('user_id', $user_id);
		$this->db->where('status', 'maintenance');
		$maintenance = $this->db->count_all_results('amenities');

		$this->db->where('user_id', $user_id);
		$this->db->where('status', 'closed');
		$closed = $this->db->count_all_results('amenities');

		// Bookings today
		$this->db->join('amenities', 'bookings.amenity_id = amenities.id');
		$this->db->where('amenities.user_id', $user_id);
		$this->db->where('booking_date', $today);
		$today_bookings = $this->db->count_all_results('bookings');

		return [
			'total' => $total,
			'available' => $available,
			'maintenance' => $maintenance,
			'closed' => $closed,
			'today_bookings' => $today_bookings,
		];
	}

	// ---------- Chart Data ----------
	public function get_chart_data($user_id)
	{
		$days = [];
		$counts = [];
		for ($i = 6; $i >= 0; $i--) {
			$date = date('Y-m-d', strtotime("-$i days"));
			$day_name = date('D', strtotime($date));

			$this->db->join('amenities', 'bookings.amenity_id = amenities.id');
			$this->db->where('amenities.user_id', $user_id);
			$this->db->where('booking_date', $date);
			$count = $this->db->count_all_results('bookings');

			$days[] = $day_name;
			$counts[] = $count;
		}
		return ['labels' => $days, 'data' => $counts];
	}

	// ---------- Bookings ----------
	public function get_todays_bookings($user_id)
	{
		$today = date('Y-m-d');
		$this->db->select('bookings.*, amenities.name as amenity_name, amenities.icon');
		$this->db->from('bookings');
		$this->db->join('amenities', 'bookings.amenity_id = amenities.id');
		$this->db->where('amenities.user_id', $user_id);
		$this->db->where('booking_date', $today);
		$this->db->order_by('start_time', 'ASC');
		return $this->db->get()->result_array();
	}

	public function insert_booking($data)
	{
		$this->db->insert('bookings', $data);
		return $this->db->insert_id();
	}

	public function get_booking($id, $user_id)
	{
		$this->db->select('bookings.*, amenities.name as amenity_name');
		$this->db->from('bookings');
		$this->db->join('amenities', 'bookings.amenity_id = amenities.id');
		$this->db->where('bookings.id', $id);
		$this->db->where('amenities.user_id', $user_id);
		return $this->db->get()->row_array();
	}

	public function cancel_booking($id, $user_id)
	{
		$this->db->where('id', $id);
		$this->db->where('amenity_id IN (SELECT id FROM amenities WHERE user_id = ' . $user_id . ')');
		return $this->db->update('bookings', ['status' => 'cancelled']);
	}

	// ---------- Utilities ----------
	public function generate_amenity_id()
	{
		$year = date('Y');
		$this->db->select('amenity_id');
		$this->db->like('amenity_id', "AMN-{$year}-", 'after');
		$this->db->order_by('amenity_id', 'DESC');
		$last = $this->db->get('amenities', 1)->row_array();

		if ($last) {
			$parts = explode('-', $last['amenity_id']);
			$num = intval(end($parts)) + 1;
		} else {
			$num = 1;
		}
		return 'AMN-' . $year . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
	}

	public function generate_booking_id()
	{
		$year = date('Y');
		$this->db->select('booking_id');
		$this->db->like('booking_id', "BKG-{$year}-", 'after');
		$this->db->order_by('booking_id', 'DESC');
		$last = $this->db->get('bookings', 1)->row_array();

		if ($last) {
			$parts = explode('-', $last['booking_id']);
			$num = intval(end($parts)) + 1;
		} else {
			$num = 1;
		}
		return 'BKG-' . $year . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
	}
}
