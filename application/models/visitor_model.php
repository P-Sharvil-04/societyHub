<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visitor_model extends CI_Model
{
	private $table = 'visitors';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* ════════════════════════════════════════════════
	 *  FETCH — filtered list
	 *  filters keys:
	 *    society_id → int|''
	 *    status     → 'Checked In'|'Checked Out'|'Pending'|''
	 *    search     → LIKE on visitor_name, flat, purpose, phone
	 * ════════════════════════════════════════════════ */

	public function get_visitors($filters = [], $limit = 10, $offset = 0)
	{
		$this->db
			->select('visitors.*, societies.name AS society_name')
			->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left');

		$this->_apply_filters($filters);

		return $this->db
			->order_by('visitors.entry_time', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get()->result();
	}

	public function count_visitors($filters = [])
	{
		$this->db->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left');

		$this->_apply_filters($filters);

		return (int) $this->db->count_all_results();
	}

	/* ════════════════════════════════════════════════
	 *  STATS — scoped to same filters (society + search)
	 * ════════════════════════════════════════════════ */

	public function get_stats($filters = [])
	{
		// Run count for each status using same society/search scope
		$count = function ($status = null) use ($filters) {
			$this->db->from($this->table)
				->join('societies', 'societies.id = visitors.society_id', 'left');

			// Only apply society + search for stats (not status — we count per status)
			$scopeFilters = $filters;
			unset($scopeFilters['status']);
			$this->_apply_filters($scopeFilters);

			if ($status !== null) {
				$this->db->where('visitors.status', $status);
			}
			return (int) $this->db->count_all_results();
		};

		return [
			'total' => $count(null),
			'checked_in' => $count('Checked In'),
			'checked_out' => $count('Checked Out'),
			'pending' => $count('Pending'),
		];
	}

	/* ════════════════════════════════════════════════
	 *  SINGLE RECORD
	 * ════════════════════════════════════════════════ */

	public function get_visitor_by_id($id)
	{
		return $this->db
			->select('visitors.*, societies.name AS society_name')
			->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left')
			->where('visitors.id', (int) $id)
			->get()->row_array();
	}

	/* ════════════════════════════════════════════════
	 *  WRITE
	 * ════════════════════════════════════════════════ */

	public function insert_visitor($data)
	{
		$ok = $this->db->insert($this->table, $data);
		return $ok ? $this->db->insert_id() : false;
	}

	public function update_visitor($id, $data)
	{
		return (bool) $this->db->where('id', (int) $id)->update($this->table, $data);
	}

	public function delete_visitor($id)
	{
		return (bool) $this->db->where('id', (int) $id)->delete($this->table);
	}

	/* ════════════════════════════════════════════════
	 *  RECENT (sidebar panel)
	 * ════════════════════════════════════════════════ */

	public function get_recent_visitors($society_id = null, $limit = 5)
	{
		$this->db
			->select('visitors.*, societies.name AS society_name')
			->from($this->table)
			->join('societies', 'societies.id = visitors.society_id', 'left');

		if (!empty($society_id)) {
			$this->db->where('visitors.society_id', (int) $society_id);
		}

		return $this->db
			->order_by('visitors.entry_time', 'DESC')
			->limit((int) $limit)
			->get()->result();
	}

	/* ════════════════════════════════════════════════
	 *  SOCIETIES LIST
	 * ════════════════════════════════════════════════ */

	public function get_societies()
	{
		return $this->db
			->select('id, name')
			->order_by('name', 'ASC')
			->get('societies')->result_array();
	}

	/* ════════════════════════════════════════════════
	 *  PRIVATE HELPER — shared WHERE clauses
	 * ════════════════════════════════════════════════ */

	private function _apply_filters($filters = [])
	{
		if (!empty($filters['society_id'])) {
			$this->db->where('visitors.society_id', (int) $filters['society_id']);
		}
		if (!empty($filters['status']) && $filters['status'] !== 'all') {
			$this->db->where('visitors.status', $filters['status']);
		}
		if (!empty($filters['search'])) {
			$s = $filters['search'];
			$this->db->group_start()
				->like('visitors.visitor_name', $s)
				->or_like('visitors.flat', $s)
				->or_like('visitors.purpose', $s)
				->or_like('visitors.phone', $s)
				->group_end();
		}
	}
}
