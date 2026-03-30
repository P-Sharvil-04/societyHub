<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class staff_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Staff_model');
		$this->load->helper(['url', 'form']);
		$this->load->library(['session', 'form_validation']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	/* ──────────────────────────────────────────────
	 *  INDEX — backend filtering via GET params
	 * ────────────────────────────────────────────── */
	public function index()
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		$canManage = in_array($role_name, [
			'super_admin',
			'chairman',
			'secretary',
			'accountant',
			'committee_member'
		]);

		// ── Read GET filter params ──
		$filters = [
			'society_id' => $isSuperAdmin ? ((int) $this->input->get('society_id') ?: '') : $session_society_id,
			'designation' => $this->input->get('designation', TRUE) ?: '',
			'status' => $this->input->get('status', TRUE),      // keep as string; '' = all
			'search' => $this->input->get('search', TRUE) ?: '',
		];

		// Normalise status: null/false → ''
		if ($filters['status'] === null || $filters['status'] === false) {
			$filters['status'] = '';
		}

		// ── Fetch filtered staff ──
		$staff = $this->Staff_model->get_filtered($filters);

		// ── Stats scoped to same filters ──
		$stats = $this->Staff_model->get_stats($filters);

		// ── Societies list for super admin filter + add form ──
		$societies = $isSuperAdmin ? $this->Staff_model->get_all_soc() : [];

		// ── Recent staff panel (always unfiltered — shows latest additions) ──
		$recent_staff = $isSuperAdmin
			? $this->Staff_model->get_recent_staffs(5)
			: $this->Staff_model->get_recent_staff($session_society_id, 5);

		$data = [
			'title' => 'Staff Management',
			'staff' => $staff,
			'recent_staff' => $recent_staff,
			'stats' => $stats,
			'filters' => $filters,
			'societies' => $societies,
			'isSuperAdmin' => $isSuperAdmin,
			'canManage' => $canManage,
		];

		$this->load->view('header', $data);
		$this->load->view('staff_view', $data);
	}

	/* ──────────────────────────────────────────────
	 *  SAVE (add + edit)
	 * ────────────────────────────────────────────── */
	public function save()
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');

		$canManage = in_array($role_name, [
			'super_admin',
			'chairman',
			'secretary',
			'accountant',
			'committee_member'
		]);

		if (!$canManage) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(['status' => false, 'message' => 'Permission denied.']);
				return;
			}
			$this->session->set_flashdata('error', 'You do not have permission to manage staff.');
			redirect('staff');
		}

		$this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
		$this->form_validation->set_rules('phone', 'Phone', 'required|trim');
		$this->form_validation->set_rules('designation', 'Designation', 'required');
		$this->form_validation->set_rules('join_date', 'Join Date', 'required');

		if ($this->form_validation->run() == FALSE) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(['status' => false, 'message' => validation_errors()]);
				return;
			}
			$this->index();
			return;
		}

		$data = [
			'first_name' => $this->input->post('first_name', TRUE),
			'last_name' => $this->input->post('last_name', TRUE),
			'email' => $this->input->post('email', TRUE),
			'phone' => $this->input->post('phone', TRUE),
			'emergency_contact' => $this->input->post('emergency_contact', TRUE),
			'designation' => $this->input->post('designation', TRUE),
			'department' => $this->input->post('department', TRUE),
			'join_date' => $this->input->post('join_date', TRUE),
			'shift' => $this->input->post('shift', TRUE),
			'salary' => $this->input->post('salary') ?: null,
			'address' => $this->input->post('address', TRUE),
			'status' => $this->input->post('status', TRUE) ?: 'active',
		];

		$id = $this->input->post('id');

		if ($id) {
			$result = ($role_name === 'super_admin')
				? $this->Staff_model->update_staff($id, $data)
				: $this->Staff_model->update_staff($id, $data, $session_society_id);
			$message = 'Staff updated successfully.';
		} else {
			if ($role_name === 'super_admin') {
				$posted = $this->input->post('society_id');
				$data['society_id'] = ($posted !== '' && $posted !== null) ? (int) $posted : null;
			} else {
				$data['society_id'] = $session_society_id;
			}
			$data['created_by'] = $this->session->userdata('user_id');
			$result = $this->Staff_model->insert_staff($data);
			$message = 'Staff added successfully.';
		}

		if ($this->input->is_ajax_request()) {
			echo json_encode(['status' => (bool) $result, 'message' => $message]);
			return;
		}

		$this->session->set_flashdata($result ? 'success' : 'error', $result ? $message : 'Operation failed.');
		redirect('staff');
	}

	/* ──────────────────────────────────────────────
	 *  EDIT (AJAX — returns staff row as JSON)
	 * ────────────────────────────────────────────── */
	public function edit($id)
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');

		$canManage = in_array($role_name, [
			'super_admin',
			'chairman',
			'secretary',
			'accountant',
			'committee_member'
		]);

		if (!$canManage) {
			$this->output->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(['error' => 'Permission denied']));
			return;
		}

		$staff = ($role_name === 'super_admin')
			? $this->Staff_model->get($id)
			: $this->Staff_model->get_by_id_and_society($id, $session_society_id);

		if (!$staff) {
			$this->output->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode(['error' => 'Not found']));
			return;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($staff));
	}

	/* ──────────────────────────────────────────────
	 *  DELETE
	 * ────────────────────────────────────────────── */
	public function delete($id)
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');

		$canManage = in_array($role_name, [
			'super_admin',
			'chairman',
			'secretary',
			'accountant',
			'committee_member'
		]);

		if (!$canManage) {
			if ($this->input->is_ajax_request()) {
				echo json_encode(['status' => false, 'message' => 'Permission denied.']);
				return;
			}
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('staff');
		}

		$deleted = ($role_name === 'super_admin')
			? $this->Staff_model->delete_staff($id)
			: $this->Staff_model->delete_staff($id, $session_society_id);

		if ($this->input->is_ajax_request()) {
			echo json_encode(
				$deleted
				? ['status' => 'success', 'message' => 'Staff deleted successfully.']
				: ['status' => false, 'message' => 'Delete failed or record not found.']
			);
			return;
		}

		$this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'Staff deleted.' : 'Delete failed.');
		redirect('staff');
	}

	/* ──────────────────────────────────────────────
	 *  SOCIETY STATS (AJAX, super admin only)
	 * ────────────────────────────────────────────── */
	public function society_stats()
	{
		if ($this->session->userdata('role_name') !== 'super_admin') {
			$this->output->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(['error' => 'Permission denied']));
			return;
		}

		$stats = $this->Staff_model->get_staff_count_per_society();
		$this->output->set_content_type('application/json')->set_output(json_encode($stats));
	}
}
