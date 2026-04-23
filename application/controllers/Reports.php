<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends CI_Controller
{
	private const SUPER_ROLES = ['super_admin'];

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library('session');
		$this->load->model('Reports_model');

		if (!$this->session->userdata('user_id')) {
			redirect('login');
		}
	}

	private function _get_role(): string
	{
		$role = $this->session->userdata('role');
		if (is_string($role) && $role !== '') {
			return $role;
		}

		$uid = (int) $this->session->userdata('user_id');

		$this->db->select('r.role_name');
		$this->db->from('user_roles ur');
		$this->db->join('roles r', 'r.id = ur.role_id');
		$this->db->where('ur.user_id', $uid);
		$this->db->order_by("FIELD(r.role_name,'super_admin','chairman','secretary','accountant','committee_member','owner','tenant','staff','security')", 'ASC', false);
		$this->db->limit(1);

		$row = $this->db->get()->row_array();
		return $row['role_name'] ?? 'owner';
	}

	private function _is_super(): bool
	{
		return in_array($this->_get_role(), self::SUPER_ROLES, true);
	}

	/**
	 * Get report context (society filter, global view, etc.)
	 * FIX: Explicitly set $sid = null for "All Societies" (super admin)
	 */
	private function _get_context(?string $requestedSociety = null): array
	{
		$isSuper = $this->_is_super();
		$sid = null;
		$globalView = false;
		$societyName = '';

		if ($isSuper) {
			// Super admin: if requestedSociety is 'all' or missing/null -> global view
			if ($requestedSociety && $requestedSociety !== 'all') {
				$sid = (int) $requestedSociety;
				$societyName = $sid > 0 ? $this->Reports_model->get_society_name($sid) : '';
			} else {
				$globalView = true;
				$societyName = 'All Societies';
			}
		} else {
			// Normal user: always bound to their own society
			$sid = (int) $this->session->userdata('society_id');
			if ($sid <= 0)
				$sid = null;
			$societyName = $sid ? $this->Reports_model->get_society_name($sid) : '';
		}

		return [
			'is_super' => $isSuper,
			'role' => $this->_get_role(),
			'sid' => $sid,
			'global_view' => $globalView,
			'society_name' => $societyName,
		];
	}

	public function index()
	{
		// Optional: clear stale society_id from session for super admins
		if ($this->_is_super()) {
			$this->session->unset_userdata('society_id');
		}

		$ctx = $this->_get_context(null);

		$mySocietyId = (int) $this->session->userdata('society_id');
		$mySocietyName = '';

		if (!$ctx['is_super'] && $mySocietyId > 0) {
			$row = $this->db
				->select('name')
				->from('societies')
				->where('id', $mySocietyId)
				->get()
				->row();
			$mySocietyName = $row ? $row->name : 'My Society';
		}

		$data = [
			'title' => 'Reports',
			'is_super' => $ctx['is_super'],
			'role' => $ctx['role'],
			'societies' => $ctx['is_super'] ? $this->Reports_model->get_societies() : [],
			'my_society_id' => $mySocietyId,
			'my_society_name' => $mySocietyName,
		];
		$this->load->view('header', $data);

		$this->load->view('report_view', $data);
	}

	public function get_data()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$type = $this->input->get('type', true) ?? 'financial';
		$start = $this->input->get('start_date', true) ?? date('Y-01-01');
		$end = $this->input->get('end_date', true) ?? date('Y-m-d');
		$requestedSociety = $this->input->get('society_id', true) ?: null;

		$page = max(1, (int) ($this->input->get('page', true) ?? 1));
		$perPage = max(5, min(50, (int) ($this->input->get('per_page', true) ?? 10)));

		$ctx = $this->_get_context($requestedSociety);

		$data = $this->Reports_model->get_report(
			$type,
			$start,
			$end,
			$ctx['sid'],
			$ctx['global_view'],
			true,       // always include society column in the table
			$page,
			$perPage
		);

		$data['meta'] = [
			'role' => $ctx['role'],
			'is_super' => $ctx['is_super'],
			'society_id' => $ctx['sid'],
			'society_name' => $ctx['society_name'],
			'global_view' => $ctx['global_view'],
		];

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
	}

	public function export_csv()
	{
		$type = $this->input->get('type', true) ?? 'financial';
		$start = $this->input->get('start_date', true) ?? date('Y-01-01');
		$end = $this->input->get('end_date', true) ?? date('Y-m-d');
		$requestedSociety = $this->input->get('society_id', true) ?: null;

		$ctx = $this->_get_context($requestedSociety);

		$data = $this->Reports_model->get_report(
			$type,
			$start,
			$end,
			$ctx['sid'],
			$ctx['global_view'],
			true,
			1,
			1000000
		);

		$socLabel = $ctx['global_view'] ? '_all' : ($ctx['sid'] ? '_soc' . $ctx['sid'] : '');
		$filename = 'report_' . $type . $socLabel . '_' . date('Ymd') . '.csv';

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		$out = fopen('php://output', 'w');
		fputcsv($out, $data['table']['headers']);
		foreach ($data['table']['rows'] as $row) {
			fputcsv($out, $row);
		}
		fclose($out);
		exit;
	}
}
