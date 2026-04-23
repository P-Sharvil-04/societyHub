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
		$role_name = $this->session->userdata('role_name');
		$user_id = $this->session->userdata('user_id');
		$session_society_id = $this->session->userdata('society_id');

		$isSuperAdmin = ($role_name === 'super_admin');
		$isOwner = ($role_name === 'owner');

		// Build filters from GET
		$filters = [
			'status' => $this->input->get('status', TRUE) ?: '',
			'category' => $this->input->get('category', TRUE) ?: '',
			'search' => $this->input->get('search', TRUE) ?: '',
			'society_id' => $isSuperAdmin ? $this->input->get('society_id', TRUE) : $session_society_id,
		];

		// Pagination settings
		$page = (int) $this->input->get('page') ?: 1;
		$per_page = 5;
		$offset = ($page - 1) * $per_page;

		if ($isOwner) {
			$total_rows = $this->complaints_model->count_by_user($user_id, $filters);
			$complaints = $this->complaints_model->get_by_user($user_id, $filters, $per_page, $offset);
			$stats = $this->complaints_model->get_stats_by_user($user_id);
		} else {
			$total_rows = $this->complaints_model->count_filtered($filters);
			$complaints = $this->complaints_model->get_filtered($filters, $per_page, $offset);
			$stats = $this->complaints_model->get_stats($filters['society_id'] ?: null);
		}

		$total_pages = ceil($total_rows / $per_page);
		$pagination = $this->_build_pagination($page, $total_pages, $filters);

		// Societies for super admin filter dropdown
		$societies = $isSuperAdmin ? $this->complaints_model->get_societies() : [];

		// Members list for admin add/edit form
		$members = [];
		if (!$isOwner) {
			$members = $isSuperAdmin
				? $this->complaints_model->get_members()
				: $this->complaints_model->get_members($session_society_id);
		}

		$logged_user = $isOwner ? $this->complaints_model->get_user_by_id($user_id) : null;

		$data = [
			'title' => 'Complaints',
			'activePage' => 'complaints',
			'complaints' => $complaints,
			'stats' => $stats,
			'societies' => $societies,
			'members' => $members,
			'filters' => $filters,
			'isSuperAdmin' => $isSuperAdmin,
			'isOwner' => $isOwner,
			'logged_user' => $logged_user,
			'pagination' => $pagination,
			'total_count' => $total_rows,
		];

		$this->load->view('header', $data);
		$this->load->view('complaints_view', $data);
	}

	/**
	 * Build pagination HTML manually to preserve all filters.
	 */
	private function _build_pagination($current_page, $total_pages, $filters)
	{
		if ($total_pages <= 1) {
			return '';
		}

		$query_params = [];
		foreach ($filters as $key => $value) {
			if ($value !== '' && $value !== null) {
				$query_params[$key] = $value;
			}
		}

		$html = '<div class="pagination">';

		// Previous
		if ($current_page > 1) {
			$prev_params = array_merge($query_params, ['page' => $current_page - 1]);
			$html .= '<a href="' . site_url('complaints?' . http_build_query($prev_params)) . '"><i class="fas fa-chevron-left"></i> Previous</a>';
		}

		// Page numbers
		$start = max(1, $current_page - 2);
		$end = min($total_pages, $current_page + 2);

		if ($start > 1) {
			$first_params = array_merge($query_params, ['page' => 1]);
			$html .= '<a href="' . site_url('complaints?' . http_build_query($first_params)) . '">1</a>';
			if ($start > 2) {
				$html .= '<span>...</span>';
			}
		}

		for ($i = $start; $i <= $end; $i++) {
			if ($i == $current_page) {
				$html .= '<strong>' . $i . '</strong>';
			} else {
				$params = array_merge($query_params, ['page' => $i]);
				$html .= '<a href="' . site_url('complaints?' . http_build_query($params)) . '">' . $i . '</a>';
			}
		}

		if ($end < $total_pages) {
			if ($end < $total_pages - 1) {
				$html .= '<span>...</span>';
			}
			$last_params = array_merge($query_params, ['page' => $total_pages]);
			$html .= '<a href="' . site_url('complaints?' . http_build_query($last_params)) . '">' . $total_pages . '</a>';
		}

		// Next
		if ($current_page < $total_pages) {
			$next_params = array_merge($query_params, ['page' => $current_page + 1]);
			$html .= '<a href="' . site_url('complaints?' . http_build_query($next_params)) . '">Next <i class="fas fa-chevron-right"></i></a>';
		}

		$html .= '</div>';
		return $html;
	}

	public function add()
	{
		$role_name = $this->session->userdata('role_name');
		$user_id = $this->session->userdata('user_id');
		$session_society_id = $this->session->userdata('society_id');
		$isOwner = ($role_name === 'owner');

		$this->form_validation->set_rules('title', 'Title', 'required|trim');
		$this->form_validation->set_rules('description', 'Description', 'required|trim');
		$this->form_validation->set_rules('category', 'Category', 'required');

		if (!$isOwner) {
			$this->form_validation->set_rules('member_id', 'Member', 'required|integer');
		}

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('complaints');
		}

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
			'society_id' => $member['society_id'] ?? $session_society_id,
			'user_id' => (int) $member['id'],
			'complaint_id' => $this->complaints_model->generate_complaint_id(),
			'user_name' => $member['name'] ?? '',
			'flat' => $member['flat_no'] ?? '',
			'title' => $this->input->post('title', TRUE),
			'description' => $this->input->post('description', TRUE),
			'category' => $this->input->post('category', TRUE),
			'status' => 'pending',
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
		$this->form_validation->set_rules('title', 'Title', 'required|trim');
		$this->form_validation->set_rules('description', 'Description', 'required|trim');
		$this->form_validation->set_rules('category', 'Category', 'required');
		$this->form_validation->set_rules('status', 'Status', 'required');

		$status = $this->input->post('status', TRUE);

		if ($status === 'resolved' || $status === 'closed') {
			$this->form_validation->set_rules('expense_amount', 'Expense Amount', 'required|numeric|greater_than_equal_to[0]');
		}

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('complaints');
		}

		$id = (int) $this->input->post('complaintId');
		$role_name = $this->session->userdata('role_name');
		$user_id = $this->session->userdata('user_id');

		if ($role_name === 'owner') {
			$existing = $this->complaints_model->get_by_id($id);
			if (!$existing || (int) $existing['user_id'] !== (int) $user_id) {
				$this->session->set_flashdata('error', 'You are not allowed to edit this complaint.');
				redirect('complaints');
			}
		}

		$expenseAmount = null;
		$imageName = null;

		if ($status === 'resolved' || $status === 'closed') {
			$expenseAmount = $this->input->post('expense_amount', TRUE);
			$existing = $this->complaints_model->get_by_id($id);

			if (empty($_FILES['resolution_image']['name']) && empty($existing['resolution_image'])) {
				$this->session->set_flashdata('error', 'Resolution image is required.');
				redirect('complaints');
			}

			$config['upload_path'] = './uploads/complaints/';
			$config['allowed_types'] = 'jpg|jpeg|png|webp';
			$config['max_size'] = 2048;
			$config['encrypt_name'] = TRUE;

			if (!is_dir($config['upload_path'])) {
				mkdir($config['upload_path'], 0777, true);
			}

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload('resolution_image')) {
				$this->session->set_flashdata('error', $this->upload->display_errors('', ''));
				redirect('complaints');
			}

			$uploadData = $this->upload->data();
			$imageName = $uploadData['file_name'];
		}

		$upd = [
			'title' => $this->input->post('title', TRUE),
			'description' => $this->input->post('description', TRUE),
			'category' => $this->input->post('category', TRUE),
			'status' => $status,
		];

		if ($status === 'resolved' || $status === 'closed') {
			$upd['expense_amount'] = $expenseAmount;
			$upd['resolution_image'] = $imageName;
		}

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
		$user_id = $this->session->userdata('user_id');
		$id = (int) $id;

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

	public function filter_ajax()
	{
		// No longer used – kept only to avoid 404 if called accidentally
		show_404();
	}
}
