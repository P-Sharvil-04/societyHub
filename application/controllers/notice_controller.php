<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notice_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Notice_model');
		$this->load->helper(['form', 'url', 'download']);
		$this->load->library(['session', 'form_validation']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	/* ──────────────────────────────────────────────
	 *  INDEX — handles GET (list + filters) and POST (actions)
	 * ────────────────────────────────────────────── */
	public function index()
	{
		// Route POST actions (add/edit/delete)
		if ($this->input->post('action')) {
			switch ($this->input->post('action')) {
				case 'add':
					$this->_add();
					return;
				case 'edit':
					$this->_edit();
					return;
				case 'delete':
					$this->_delete();
					return;
			}
		}

		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		// ── Read GET filter params ──
		$raw_soc = $this->input->get('society_id', TRUE);
		$filters = [
			// text filters
			'search' => $this->input->get('search', TRUE) ?: '',
			'type' => $this->input->get('type', TRUE) ?: '',
			'status' => $this->input->get('status', TRUE) ?: '',
			// store society_id as null when omitted/empty => means "All"
			'society_id' => ($raw_soc !== null && $raw_soc !== '') ? (int) $raw_soc : null,
		];

		// Society scope:
		//   super admin → from GET param (null = all societies)
		//   others      → locked to session society
		$society_id = $isSuperAdmin ? $filters['society_id'] : (int) $session_society_id;

		// Prepare model-friendly filters (don't include society_id here; pass separately)
		$modelFilters = [
			'search' => $filters['search'],
			'type' => $filters['type'],
			'status' => $filters['status'],
		];

		// ── Fetch data (scoped to same society + filters) ──
		$notices = $this->Notice_model->get_notices($society_id, $modelFilters);
		$stats = $this->Notice_model->get_stats($society_id, $modelFilters);
		$recent = $this->Notice_model->get_recent_notices($society_id, 5);
		$monthly = $this->Notice_model->get_monthly_data($society_id);

		// Societies list for super admin filter bar + add form
		$societies = $isSuperAdmin ? $this->Notice_model->get_societies() : [];

		$data = [
			'title' => 'Notices',
			'activePage' => 'notices',
			'notices' => $notices,
			'stats' => $stats,
			'recent' => $recent,
			'monthly' => $monthly,
			'societies' => $societies,
			'filters' => $filters,
			'society_id' => $society_id,
			'isSuperAdmin' => $isSuperAdmin,
			'_modal' => $this->session->flashdata('modal_state') ?: [],
			'_old' => $this->session->flashdata('old') ?: [],
		];

		$this->load->view('header', $data);
		$this->load->view('notices_view', $data);
	}

	/* ──────────────────────────────────────────────
	 *  ADD
	 * ────────────────────────────────────────────── */
	private function _add()
	{
		$this->form_validation->set_rules('title', 'Title', 'trim|required');
		$this->form_validation->set_rules('description', 'Description', 'trim|required');
		$this->form_validation->set_rules('notice_type', 'Notice Type', 'required');
		$this->form_validation->set_rules('valid_until', 'Valid Until', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('modal_state', ['action' => 'add', 'open' => TRUE]);
			$this->session->set_flashdata('old', $this->input->post());
			redirect('notices');
			return;
		}

		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		$raw_soc = $this->input->post('society_id', TRUE);
		$society_id = $isSuperAdmin
			? (($raw_soc !== null && $raw_soc !== '') ? (int) $raw_soc : null)
			: (int) $session_society_id;

		$insert = [
			'society_id' => $society_id,
			'created_by' => $this->session->userdata('user_id') ?: null,
			'notice_id' => $this->Notice_model->generate_notice_id(),
			'title' => $this->input->post('title', TRUE),
			'description' => $this->input->post('description', TRUE),
			'notice_type' => $this->input->post('notice_type', TRUE),
			'valid_until' => $this->input->post('valid_until', TRUE),
			'status' => $this->input->post('status', TRUE) ?: 'active',
			'target_audience' => $this->input->post('target_audience', TRUE) ?: 'all',
		];

		$ok = $this->Notice_model->add_notice($insert);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Notice created successfully.' : 'Failed to save notice.');
		redirect('notices');
	}

	/* ──────────────────────────────────────────────
	 *  EDIT
	 * ────────────────────────────────────────────── */
	private function _edit()
	{
		$id = (int) $this->input->post('id');
		if (!$id) {
			$this->session->set_flashdata('error', 'Invalid notice ID.');
			redirect('notices');
			return;
		}

		$this->form_validation->set_rules('title', 'Title', 'trim|required');
		$this->form_validation->set_rules('description', 'Description', 'trim|required');
		$this->form_validation->set_rules('notice_type', 'Notice Type', 'required');
		$this->form_validation->set_rules('valid_until', 'Valid Until', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('modal_state', ['action' => 'edit', 'id' => $id, 'open' => TRUE]);
			$this->session->set_flashdata('old', $this->input->post());
			redirect('notices');
			return;
		}

		$upd = [
			'title' => $this->input->post('title', TRUE),
			'description' => $this->input->post('description', TRUE),
			'notice_type' => $this->input->post('notice_type', TRUE),
			'valid_until' => $this->input->post('valid_until', TRUE),
			'status' => $this->input->post('status', TRUE) ?: 'active',
			'target_audience' => $this->input->post('target_audience', TRUE) ?: 'all',
		];

		$ok = $this->Notice_model->edit_notice($id, $upd);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Notice updated successfully.' : 'Failed to update notice.');
		redirect('notices');
	}

	/* ──────────────────────────────────────────────
	 *  DELETE
	 * ────────────────────────────────────────────── */
	private function _delete()
	{
		$id = (int) $this->input->post('id');
		if (!$id) {
			$this->session->set_flashdata('error', 'Invalid notice ID.');
			redirect('notices');
			return;
		}
		if ($this->input->post('confirm') !== 'yes') {
			$this->session->set_flashdata('modal_state', ['action' => 'delete', 'id' => $id, 'open' => TRUE]);
			redirect('notices');
			return;
		}

		$ok = $this->Notice_model->remove_notice($id);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Notice deleted.' : 'Failed to delete.');
		redirect('notices');
	}

	/* ──────────────────────────────────────────────
	 *  EXPORT CSV
	 * ────────────────────────────────────────────── */
	public function export()
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		$raw_soc = $this->input->get('society_id', TRUE);
		$society_id = $isSuperAdmin ? (($raw_soc !== null && $raw_soc !== '') ? (int) $raw_soc : null) : (int) $session_society_id;

		$filters = [
			'search' => $this->input->get('search', TRUE) ?: '',
			'type' => $this->input->get('type', TRUE) ?: '',
			'status' => $this->input->get('status', TRUE) ?: '',
		];

		$notices = $this->Notice_model->get_notices($society_id, $filters);

		$csv = "Notice ID,Title,Type,Society,Valid Until,Status,Target Audience\n";
		foreach ($notices as $n) {
			$csv .= implode(',', [
				$n['notice_id'],
				'"' . str_replace('"', '""', $n['title']) . '"',
				$n['notice_type'],
				'"' . str_replace('"', '""', $n['society_name'] ?? '') . '"',
				$n['valid_until'] ?? '',
				$n['status'],
				$n['target_audience'],
			]) . "\n";
		}

		force_download('notices_' . date('Y-m-d') . '.csv', $csv);
	}
}
