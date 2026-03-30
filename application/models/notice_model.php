<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notice_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* FETCH — filtered list
	 * filters keys: search, type, status
	 * $society_id === null => no society filter (all societies)
	 */
	public function get_notices($society_id = null, $filters = [])
	{
		$this->db->reset_query();
		$this->db
			->select('notices.*, societies.name AS society_name')
			->from('notices')
			->join('societies', 'societies.id = notices.society_id', 'left');

		// apply society scope only when provided (null means "all")
		if ($society_id !== null) {
			$this->db->where('notices.society_id', (int) $society_id);
		}

		if (!empty($filters['search'])) {
			$this->db->group_start()
				->like('notices.title', $filters['search'])
				->or_like('notices.description', $filters['search'])
				->or_like('notices.notice_id', $filters['search'])
				->group_end();
		}
		if (!empty($filters['type'])) {
			$this->db->where('notices.notice_type', $filters['type']);
		}
		if (!empty($filters['status'])) {
			$this->db->where('notices.status', $filters['status']);
		}

		return $this->db
			->order_by('notices.created_at', 'DESC')
			->get()->result_array();
	}

	/* STATS — build fresh query each time to avoid builder leakage */
	public function get_stats($society_id = null, $filters = [])
	{
		$build_count = function ($status = null) use ($society_id, $filters) {
			$this->db->reset_query();
			$this->db->from('notices');

			if ($society_id !== null) {
				$this->db->where('notices.society_id', (int) $society_id);
			}
			if ($status !== null) {
				$this->db->where('notices.status', $status);
			}
			if (!empty($filters['search'])) {
				$this->db->group_start()
					->like('notices.title', $filters['search'])
					->or_like('notices.description', $filters['search'])
					->or_like('notices.notice_id', $filters['search'])
					->group_end();
			}
			if (!empty($filters['type'])) {
				$this->db->where('notices.notice_type', $filters['type']);
			}

			return (int) $this->db->count_all_results();
		};

		$total = $build_count(null);
		$active = $build_count('active');
		$scheduled = $build_count('scheduled');
		$expired = $build_count('expired');

		$this->db->reset_query();
		return [
			'total' => $total,
			'active' => $active,
			'scheduled' => $scheduled,
			'expired' => $expired,
		];
	}

	/* SINGLE */
	public function get_notice($id)
	{
		return $this->db
			->select('notices.*, societies.name AS society_name')
			->from('notices')
			->join('societies', 'societies.id = notices.society_id', 'left')
			->where('notices.id', (int) $id)
			->get()->row_array();
	}

	/* WRITE */
	public function add_notice($data)
	{
		$this->db->insert('notices', $data);
		return $this->db->insert_id();
	}

	public function edit_notice($id, $data)
	{
		return $this->db->where('id', (int) $id)->update('notices', $data);
	}

	public function remove_notice($id)
	{
		return $this->db->where('id', (int) $id)->delete('notices');
	}

	/* RECENT */
	public function get_recent_notices($society_id = null, $limit = 5)
	{
		$this->db->reset_query();
		$this->db
			->select('notices.*, societies.name AS society_name')
			->from('notices')
			->join('societies', 'societies.id = notices.society_id', 'left');

		if ($society_id !== null) {
			$this->db->where('notices.society_id', (int) $society_id);
		}

		return $this->db
			->order_by('notices.created_at', 'DESC')
			->limit((int) $limit)
			->get()->result_array();
	}

	/* MONTHLY DATA */
	public function get_monthly_data($society_id = null)
	{
		$labels = [];
		$data = [];

		for ($i = 5; $i >= 0; $i--) {
			$dt = new DateTime();
			$dt->modify("-{$i} months");

			$this->db->reset_query();
			$this->db->from('notices');

			if ($society_id !== null) {
				$this->db->where('society_id', (int) $society_id);
			}

			$this->db
				->where('MONTH(created_at)', $dt->format('m'))
				->where('YEAR(created_at)', $dt->format('Y'));

			$data[] = (int) $this->db->count_all_results();
			$labels[] = $dt->format('M Y');
		}

		$this->db->reset_query();
		return ['labels' => $labels, 'data' => $data];
	}

	/* SOCIETIES */
	public function get_societies()
	{
		return $this->db
			->select('id, name')
			->order_by('name', 'ASC')
			->get('societies')->result_array();
	}

	/* ID generator */
	public function generate_notice_id()
	{
		$this->db->reset_query();
		$year = date('Y');
		$count = $this->db
			->like('notice_id', "NOT-{$year}-", 'after')
			->count_all_results('notices');
		return 'NOT-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
	}
}
