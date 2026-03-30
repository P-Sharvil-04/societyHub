<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class visitor_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Visitor_model');
		$this->load->library(['form_validation', 'session', 'pagination']);
		$this->load->helper(['url', 'form', 'download']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	/* ──────────────────────────────────────────────
	 *  INDEX — list with backend filtering
	 * ────────────────────────────────────────────── */
	public function index()
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		// ── Read GET filter params ──
		$filters = [
			'society_id' => $isSuperAdmin
				? ((int) $this->input->get('society_id') ?: '')
				: $session_society_id,
			'status' => $this->input->get('status', TRUE) ?: '',
			'search' => $this->input->get('search', TRUE) ?: '',
		];

		// ── Pagination ──
		$per_page = 10;
		$page = max(1, (int) ($this->input->get('page') ?: 1));
		$offset = ($page - 1) * $per_page;
		$total = $this->Visitor_model->count_visitors($filters);

		$config = [
			'base_url' => site_url('visitors'),
			'total_rows' => $total,
			'per_page' => $per_page,
			'use_page_numbers' => TRUE,
			'page_query_string' => TRUE,
			'query_string_segment' => 'page',
			'reuse_query_string' => TRUE,
			'full_tag_open' => '<div class="pagination-wrapper">',
			'full_tag_close' => '</div>',
			'first_link' => 'First',
			'last_link' => 'Last',
			'next_link' => '&raquo;',
			'prev_link' => '&laquo;',
			'cur_tag_open' => '<span class="current-page">',
			'cur_tag_close' => '</span>',
		];
		$this->pagination->initialize($config);

		// ── Societies list for super admin filter ──
		$societies = $isSuperAdmin ? $this->Visitor_model->get_societies() : [];

		// ── Recent visitors (sidebar, unfiltered recent) ──
		$recent_society = $isSuperAdmin ? null : $session_society_id;

		$data = [
			'title' => 'Visitors',
			'activePage' => 'visitors',
			'visitors' => $this->Visitor_model->get_visitors($filters, $per_page, $offset),
			'stats' => $this->Visitor_model->get_stats($filters),
			'recent' => $this->Visitor_model->get_recent_visitors($recent_society, 5),
			'pagination' => $this->pagination->create_links(),
			'total_visitors' => $total,
			'current_page' => $page,
			'total_pages' => ceil($total / $per_page),
			'filters' => $filters,
			'societies' => $societies,
			'isSuperAdmin' => $isSuperAdmin,
		];

		$this->load->view('header', $data);
		$this->load->view('visitors_view', $data);
	}

	/* ──────────────────────────────────────────────
	 *  ADD
	 * ────────────────────────────────────────────── */
	public function add()
	{
		$this->form_validation->set_rules('visitor_name', 'Full Name', 'required|trim');
		$this->form_validation->set_rules('entry_time', 'Entry Time', 'required');
		$this->form_validation->set_rules('status', 'Status', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('visitors');
			return;
		}

		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		$entry = $this->input->post('entry_time');
		$exit = $this->input->post('exit_time');

		$insert = [
			'society_id' => $isSuperAdmin
				? ((int) $this->input->post('society_id') ?: null)
				: $session_society_id,
			'visitor_name' => $this->input->post('visitor_name', TRUE),
			'phone' => $this->input->post('phone', TRUE),
			'flat' => $this->input->post('flat', TRUE),
			'purpose' => $this->input->post('purpose', TRUE),
			'entry_time' => $entry ? str_replace('T', ' ', $entry) . ':00' : null,
			'exit_time' => $exit ? str_replace('T', ' ', $exit) . ':00' : null,
			'status' => $this->input->post('status', TRUE),
		];

		$ok = $this->Visitor_model->insert_visitor($insert);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Visitor added successfully!' : 'Failed to add visitor.');
		redirect('visitors');
	}

	/* ──────────────────────────────────────────────
	 *  EDIT (AJAX — returns visitor row as JSON)
	 * ────────────────────────────────────────────── */
	public function edit($id)
	{
		$v = $this->Visitor_model->get_visitor_by_id((int) $id);
		if (!$v) {
			show_404();
			return;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($v));
	}

	/* ──────────────────────────────────────────────
	 *  UPDATE
	 * ────────────────────────────────────────────── */
	public function update()
	{
		$id = (int) $this->input->post('id');

		$this->form_validation->set_rules('visitor_name', 'Full Name', 'required|trim');
		$this->form_validation->set_rules('entry_time', 'Entry Time', 'required');
		$this->form_validation->set_rules('status', 'Status', 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('visitors');
			return;
		}

		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		$entry = $this->input->post('entry_time');
		$exit = $this->input->post('exit_time');

		$upd = [
			'society_id' => $isSuperAdmin
				? ((int) $this->input->post('society_id') ?: null)
				: $session_society_id,
			'visitor_name' => $this->input->post('visitor_name', TRUE),
			'phone' => $this->input->post('phone', TRUE),
			'flat' => $this->input->post('flat', TRUE),
			'purpose' => $this->input->post('purpose', TRUE),
			'entry_time' => $entry ? str_replace('T', ' ', $entry) . ':00' : null,
			'exit_time' => $exit ? str_replace('T', ' ', $exit) . ':00' : null,
			'status' => $this->input->post('status', TRUE),
			'updated_at' => date('Y-m-d H:i:s'),
		];

		$ok = $this->Visitor_model->update_visitor($id, $upd);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Visitor updated successfully!' : 'Failed to update visitor.');
		redirect('visitors');
	}

	/* ──────────────────────────────────────────────
	 *  DELETE
	 * ────────────────────────────────────────────── */
	public function delete($id)
	{
		$ok = $this->Visitor_model->delete_visitor((int) $id);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Visitor deleted successfully!' : 'Failed to delete visitor.');
		redirect('visitors');
	}

	/* ──────────────────────────────────────────────
	 *  QUICK STATUS UPDATE (AJAX)
	 * ────────────────────────────────────────────── */
	public function update_status()
	{
		$id = (int) $this->input->post('id');
		$status = $this->input->post('status');

		if (!$id || !$status) {
			$this->output->set_content_type('application/json')
				->set_output(json_encode(['success' => false, 'message' => 'Invalid data']));
			return;
		}

		$upd = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];

		if (strtolower($status) === 'checked out') {
			$upd['exit_time'] = date('Y-m-d H:i:s');
		}

		$ok = $this->Visitor_model->update_visitor($id, $upd);
		$this->output->set_content_type('application/json')->set_output(json_encode(['success' => (bool) $ok]));
	}
}
