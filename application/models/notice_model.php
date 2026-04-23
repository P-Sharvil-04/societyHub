<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notice_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* ── Notices ── */

	public function get_notices($society_id = null, $filters = [])
	{
		$this->db->reset_query()
			->select('notices.*, societies.name AS society_name')
			->from('notices')
			->join('societies', 'societies.id = notices.society_id', 'left');

		$this->_applyScope($society_id);
		$this->_applyFilters($filters);

		return $this->db->order_by('notices.created_at', 'DESC')->get()->result_array();
	}

	public function get_notice($id)
	{
		return $this->db
			->select('notices.*, societies.name AS society_name')
			->from('notices')
			->join('societies', 'societies.id = notices.society_id', 'left')
			->where('notices.id', (int) $id)
			->get()->row_array();
	}

	public function add_notice(array $data)
	{
		$this->db->insert('notices', $data);
		return $this->db->insert_id();
	}

	public function edit_notice($id, array $data)
	{
		return $this->db->where('id', (int) $id)->update('notices', $data);
	}

	public function remove_notice($id)
	{
		return $this->db->where('id', (int) $id)->delete('notices');
	}

	/* ── Stats ───────────────────────────────────────── */

	public function get_stats($society_id = null, $filters = [])
	{
		$count = function ($status = null) use ($society_id, $filters) {
			$this->db->reset_query()->from('notices');
			$this->_applyScope($society_id);
			if ($status)
				$this->db->where('notices.status', $status);
			$this->_applyFilters($filters, false); // search + type only, skip status
			return (int) $this->db->count_all_results();
		};

		return [
			'total' => $count(),
			'active' => $count('active'),
			'scheduled' => $count('scheduled'),
			'expired' => $count('expired'),
		];
	}

	/* ── Recent ──────────────────────────────────────── */

	public function get_recent_notices($society_id = null, $limit = 5)
	{
		$this->db->reset_query()
			->select('notices.*, societies.name AS society_name')
			->from('notices')
			->join('societies', 'societies.id = notices.society_id', 'left');

		$this->_applyScope($society_id);

		return $this->db->order_by('notices.created_at', 'DESC')->limit((int) $limit)->get()->result_array();
	}

	/* ── Monthly chart data (last 6 months) ─────────── */

	public function get_monthly_data($society_id = null)
	{
		$labels = [];
		$data = [];
		for ($i = 5; $i >= 0; $i--) {
			$dt = (new DateTime())->modify("-{$i} months");
			$this->db->reset_query()->from('notices');
			$this->_applyScope($society_id);
			$this->db->where('MONTH(created_at)', $dt->format('m'))->where('YEAR(created_at)', $dt->format('Y'));
			$data[] = (int) $this->db->count_all_results();
			$labels[] = $dt->format('M Y');
		}
		return ['labels' => $labels, 'data' => $data];
	}

	/* ── Societies list ──────────────────────────────── */

	public function get_societies()
	{
		return $this->db->select('id, name')->order_by('name', 'ASC')->get('societies')->result_array();
	}

	/* ── Notice ID generator ─────────────────────────── */

	public function generate_notice_id()
	{
		$year = date('Y');
		$count = $this->db->reset_query()->like('notice_id', "NOT-{$year}-", 'after')->count_all_results('notices');
		return 'NOT-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
	}

	/* ── Notifications ───────────────────────────────── */

	public function insert_notification(array $data)
	{
		return $this->db->insert('notifications', $data);
	}

	public function get_unread_notifications($society_id)
	{
		return $this->db
			->select('id, notice_id, title, message, is_read, created_at')
			->where('society_id', (int) $society_id)
			->where('is_read', 0)
			->order_by('created_at', 'DESC')
			->limit(50)
			->get('notifications')->result_array();
	}

	public function mark_notification_read($id)
	{
		return $this->db->where('id', (int) $id)->update('notifications', ['is_read' => 1]);
	}

	public function mark_all_notifications_read($society_id)
	{
		return $this->db->where('society_id', (int) $society_id)->where('is_read', 0)->update('notifications', ['is_read' => 1]);
	}

	/* ════════════════════════════════════════════════════
	   PRIVATE QUERY HELPERS
	════════════════════════════════════════════════════ */

	private function _applyScope($society_id): void
	{
		if ($society_id !== null) {
			$this->db->where('notices.society_id', (int) $society_id);
		}
	}

	private function _applyFilters(array $f, bool $includeStatus = true): void
	{
		if (!empty($f['search'])) {
			$this->db->group_start()
				->like('notices.title', $f['search'])
				->or_like('notices.description', $f['search'])
				->or_like('notices.notice_id', $f['search'])
				->group_end();
		}
		if (!empty($f['type']))
			$this->db->where('notices.notice_type', $f['type']);
		if ($includeStatus && !empty($f['status']))
			$this->db->where('notices.status', $f['status']);
	}
}
