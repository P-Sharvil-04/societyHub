<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class complaints_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('complaints_model');
		$this->load->helper(['url', 'form']);
		$this->load->library(['session', 'form_validation']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}
	
	public function index()
	{
		$role_name          = $this->session->userdata('role_name');
		$user_id            = $this->session->userdata('user_id');
		$session_society_id = $this->session->userdata('society_id');

		$isSuperAdmin = ($role_name === 'super_admin');
		$isOwner      = ($role_name === 'owner');

		// ── Read GET filter params ──
		$filters = [
			'status'     => $this->input->get('status',   TRUE) ?: '',
			'category'   => $this->input->get('category', TRUE) ?: '',
			'search'     => $this->input->get('search',   TRUE) ?: '',
			'society_id' => '',   // filled below
		];

		if ($isSuperAdmin) {
			// super admin can filter by any society
			$filters['society_id'] = (int) $this->input->get('society_id') ?: '';
		} else {
			// everyone else is locked to their own society
			$filters['society_id'] = $session_society_id;
		}

		// ── Fetch complaints ──
		if ($isOwner) {
			// Owner sees ONLY their own complaints
			$complaints = $this->complaints_model->get_by_user($user_id, $filters);
			$stats      = $this->complaints_model->get_stats_by_user($user_id);
		} else {
			// Admin / super admin / committee — society scoped or all
			$complaints = $this->complaints_model->get_filtered($filters);
			$stats      = $this->complaints_model->get_stats($filters['society_id'] ?: null);
		}

		// ── Societies for super admin filter dropdown ──
		$societies = $isSuperAdmin ? $this->complaints_model->get_societies() : [];

		// ── Members list for admin add/edit form ──
		// Owner does NOT need this — they file only for themselves
		$members = [];
		if (!$isOwner) {
			$members = $isSuperAdmin
				? $this->complaints_model->get_members()                        // all societies
				: $this->complaints_model->get_members($session_society_id);    // own society only
		}

		// ── Logged-in owner's profile (pre-fill add form) ──
		$logged_user = $isOwner ? $this->complaints_model->get_user_by_id($user_id) : null;

		$data = [
			'title'        => 'Complaints',
			'activePage'   => 'complaints',
			'complaints'   => $complaints,
			'stats'        => $stats,
			'societies'    => $societies,
			'members'      => $members,
			'filters'      => $filters,
			'isSuperAdmin' => $isSuperAdmin,
			'isOwner'      => $isOwner,
			'logged_user'  => $logged_user,
		];

		$this->load->view('header', $data);
		$this->load->view('complaints_view', $data);
	}

	public function add()
	{
		$role_name          = $this->session->userdata('role_name');
		$user_id            = $this->session->userdata('user_id');
		$session_society_id = $this->session->userdata('society_id');
		$isOwner            = ($role_name === 'owner');

		$this->form_validation->set_rules('title',       'Title',       'required|trim');
		$this->form_validation->set_rules('description', 'Description', 'required|trim');
		$this->form_validation->set_rules('category',    'Category',    'required');

		if (!$isOwner) {
			// Admin must pick a member
			$this->form_validation->set_rules('member_id', 'Member', 'required|integer');
		}

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('complaints');
		}

		// Resolve the member this complaint belongs to
		if ($isOwner) {
			$member = $this->complaints_model->get_user_by_id($user_id);
		} else {
			$member = $this->complaints_model->get_user_by_id((int) $this->input->post('member_id'));
		}

		if (!$member) {
			$this->session->set_flashdata('error', 'Member not found.');
			redirect('complaints');
		}

		$insert = [
			'society_id'   => $member['society_id'] ?? $session_society_id,
			'user_id'      => (int) $member['id'],
			'complaint_id' => $this->complaints_model->generate_complaint_id(),
			'user_name'    => $member['name']    ?? '',
			'flat'         => $member['flat_no'] ?? '',
			'title'        => $this->input->post('title',       TRUE),
			'description'  => $this->input->post('description', TRUE),
			'category'     => $this->input->post('category',    TRUE),
			'status'       => 'pending',
		];

		if ($this->complaints_model->insert_complaint($insert)) {
			$this->session->set_flashdata('success', 'Complaint registered successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to register complaint.');
		}
		redirect('complaints');
	}

	public function update()
	{
		$this->form_validation->set_rules('complaintId', 'Complaint ID', 'required|integer');
		$this->form_validation->set_rules('title',       'Title',        'required|trim');
		$this->form_validation->set_rules('description', 'Description',  'required|trim');
		$this->form_validation->set_rules('category',    'Category',     'required');
		$this->form_validation->set_rules('status',      'Status',       'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('complaints');
		}

		$id        = (int) $this->input->post('complaintId');
		$role_name = $this->session->userdata('role_name');
		$user_id   = $this->session->userdata('user_id');

		// Owner can only edit their own complaint
		if ($role_name === 'owner') {
			$existing = $this->complaints_model->get_by_id($id);
			if (!$existing || (int) $existing['user_id'] !== (int) $user_id) {
				$this->session->set_flashdata('error', 'You are not allowed to edit this complaint.');
				redirect('complaints');
			}
		}

		$upd = [
			'title'       => $this->input->post('title',       TRUE),
			'description' => $this->input->post('description', TRUE),
			'category'    => $this->input->post('category',    TRUE),
			'status'      => $this->input->post('status',      TRUE),
		];

		if ($this->complaints_model->update_complaint($id, $upd)) {
			$this->session->set_flashdata('success', 'Complaint updated successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to update complaint.');
		}
		redirect('complaints');
	}
	public function delete($id)
	{
		$role_name = $this->session->userdata('role_name');
		$user_id   = $this->session->userdata('user_id');
		$id        = (int) $id;

		if ($role_name === 'owner') {
			$existing = $this->complaints_model->get_by_id($id);
			if (!$existing || (int) $existing['user_id'] !== (int) $user_id) {
				echo json_encode(['success' => false, 'error' => 'Unauthorized']);
				return;
			}
		}

		echo json_encode(
			$this->complaints_model->delete_complaint($id)
				? ['success' => true]
				: ['success' => false, 'error' => 'Delete failed']
		);
	}
}
