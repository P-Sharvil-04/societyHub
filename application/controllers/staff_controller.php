<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class staff_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Staff_model');
		$this->load->helper(['url', 'form']);
		$this->load->library(['session', 'form_validation', 'pagination']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	/* ─────────────────────────────────────────────────────
	 *  Helper
	 * ───────────────────────────────────────────────────── */
	private function _json($data)
	{
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}

	/* ──────────────────────────────────────────────
	 *  INDEX 
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

		// Build filters from GET
		$filters = [
			'society_id' => $isSuperAdmin ? $this->input->get('society_id', TRUE) : $session_society_id,
			'designation' => $this->input->get('designation', TRUE) ?: '',
			'status' => $this->input->get('status', TRUE) ?: '',
			'search' => $this->input->get('search', TRUE) ?: '',
		];

		// Pagination settings
		$page = (int) $this->input->get('page') ?: 1;
		$per_page = 10;
		$offset = ($page - 1) * $per_page;

		$total_rows = $this->Staff_model->count_filtered($filters);
		$total_pages = ceil($total_rows / $per_page);

		// Fetch staff for current page
		$staff = $this->Staff_model->get_filtered($filters, $per_page, $offset);

		// Get stats, societies, recent staff
		$stats = $this->Staff_model->get_stats($filters);
		$societies = $isSuperAdmin ? $this->Staff_model->get_all_soc() : [];
		$recent_staff = $isSuperAdmin
			? $this->Staff_model->get_recent_staffs(5)
			: $this->Staff_model->get_recent_staff($session_society_id, 5);

		$anyFilter = (!empty($filters['society_id']) && $isSuperAdmin)
			|| !empty($filters['designation'])
			|| $filters['status'] !== ''
			|| !empty($filters['search']);

		// Generate pagination links manually (more reliable)
		$pagination = $this->_build_pagination($page, $total_pages, $filters);

		$data = [
			'title' => 'Staff',
			'staff' => $staff,
			'recent_staff' => $recent_staff,
			'stats' => $stats,
			'filters' => $filters,
			'societies' => $societies,
			'isSuperAdmin' => $isSuperAdmin,
			'canManage' => $canManage,
			'pagination' => $pagination,
			'total_count' => $total_rows,
			'anyFilter' => $anyFilter,
		];

		$this->load->view('header', $data);
		$this->load->view('staff_view', $data);
	}

	/**
	 * Build pagination HTML manually to preserve all filters
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
			$html .= '<a href="' . site_url('staff?' . http_build_query($prev_params)) . '"><i class="fas fa-chevron-left"></i> Previous</a>';
		}

		// Page numbers
		$start = max(1, $current_page - 2);
		$end = min($total_pages, $current_page + 2);

		if ($start > 1) {
			$first_params = array_merge($query_params, ['page' => 1]);
			$html .= '<a href="' . site_url('staff?' . http_build_query($first_params)) . '">1</a>';
			if ($start > 2) {
				$html .= '<span>...</span>';
			}
		}

		for ($i = $start; $i <= $end; $i++) {
			if ($i == $current_page) {
				$html .= '<strong>' . $i . '</strong>';
			} else {
				$params = array_merge($query_params, ['page' => $i]);
				$html .= '<a href="' . site_url('staff?' . http_build_query($params)) . '">' . $i . '</a>';
			}
		}

		if ($end < $total_pages) {
			if ($end < $total_pages - 1) {
				$html .= '<span>...</span>';
			}
			$last_params = array_merge($query_params, ['page' => $total_pages]);
			$html .= '<a href="' . site_url('staff?' . http_build_query($last_params)) . '">' . $total_pages . '</a>';
		}

		// Next
		if ($current_page < $total_pages) {
			$next_params = array_merge($query_params, ['page' => $current_page + 1]);
			$html .= '<a href="' . site_url('staff?' . http_build_query($next_params)) . '">Next <i class="fas fa-chevron-right"></i></a>';
		}

		$html .= '</div>';
		return $html;
	}
	/* ──────────────────────────────────────────────
	 *  SAVE (add + edit)
	 * ────────────────────────────────────────────── */
	public function save()
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isAjax = $this->input->is_ajax_request();

		$canManage = in_array($role_name, [
			'super_admin',
			'chairman',
			'secretary',
			'accountant',
			'committee_member'
		]);

		if (!$canManage) {
			if ($isAjax) {
				$this->_json(['status' => false, 'message' => 'Permission denied.']);
				return;
			}
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('staff');
		}

		$id = $this->input->post('id');

		// Validation
		$this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
		$this->form_validation->set_rules('phone', 'Phone', 'required|trim');
		$this->form_validation->set_rules('designation', 'Designation', 'required');
		$this->form_validation->set_rules('join_date', 'Join Date', 'required');

		if (!$id) {
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
		}

		if ($this->form_validation->run() === FALSE) {
			if ($isAjax) {
				$this->_json(['status' => false, 'message' => strip_tags(validation_errors())]);
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

		$plain_password = $this->input->post('password', TRUE);
		$new_password = $this->input->post('new_password', TRUE);

		// INSERT
		if (!$id) {
			if ($role_name === 'super_admin') {
				$posted = $this->input->post('society_id');
				$data['society_id'] = ($posted !== '' && $posted !== null) ? (int) $posted : null;
			} else {
				$data['society_id'] = $session_society_id;
			}
			$data['created_by'] = $this->session->userdata('user_id');

			if ($this->Staff_model->user_exists_by_email($data['email'])) {
				$msg = 'A login account already exists for this email. Use a different email.';
				if ($isAjax) {
					$this->_json(['status' => false, 'message' => $msg]);
					return;
				}
				$this->session->set_flashdata('error', $msg);
				redirect('staff');
			}

			$this->db->trans_start();
			$staff_id = $this->Staff_model->insert_staff($data);
			if ($staff_id) {
				$this->Staff_model->create_staff_user($data, $plain_password);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE || !$staff_id) {
				$msg = 'Failed to add staff. Please try again.';
				if ($isAjax) {
					$this->_json(['status' => false, 'message' => $msg]);
					return;
				}
				$this->session->set_flashdata('error', $msg);
				redirect('staff');
			}

			$role_label = $this->Staff_model->resolve_role_id($data['designation']) === 8 ? 'Security' : 'Staff';
			$message = "Staff added successfully. Login role: {$role_label}. Email: {$data['email']}";
			$result = true;

			// UPDATE
		} else {
			$existing = ($role_name === 'super_admin')
				? $this->Staff_model->get($id)
				: $this->Staff_model->get_by_id_and_society($id, $session_society_id);

			if (!$existing) {
				$msg = 'Staff record not found or access denied.';
				if ($isAjax) {
					$this->_json(['status' => false, 'message' => $msg]);
					return;
				}
				$this->session->set_flashdata('error', $msg);
				redirect('staff');
			}

			$this->db->trans_start();
			$result = ($role_name === 'super_admin')
				? $this->Staff_model->update_staff($id, $data)
				: $this->Staff_model->update_staff($id, $data, $session_society_id);

			$this->Staff_model->update_staff_user(
				$existing['email'],
				$data,
				!empty($new_password) ? $new_password : null
			);
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$result = false;
			}
			$message = 'Staff updated successfully.';
		}

		if ($isAjax) {
			$this->_json(['status' => (bool) $result, 'message' => $message]);
			return;
		}

		$this->session->set_flashdata($result ? 'success' : 'error', $result ? $message : 'Operation failed.');
		redirect('staff');
	}

	/* ──────────────────────────────────────────────
	 *  EDIT 
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
			$this->output->set_status_header(403);
			$this->_json(['error' => 'Permission denied']);
			return;
		}

		$staff = ($role_name === 'super_admin')
			? $this->Staff_model->get($id)
			: $this->Staff_model->get_by_id_and_society($id, $session_society_id);

		if (!$staff) {
			$this->output->set_status_header(404);
			$this->_json(['error' => 'Not found']);
			return;
		}

		$this->_json($staff);
	}

	/* ──────────────────────────────────────────────
	 *  DELETE — removes staff row + linked user account
	 * ────────────────────────────────────────────── */
	public function delete($id)
	{
		$role_name = $this->session->userdata('role_name');
		$session_society_id = $this->session->userdata('society_id');
		$isAjax = $this->input->is_ajax_request();

		$canManage = in_array($role_name, [
			'super_admin',
			'chairman',
			'secretary',
			'accountant',
			'committee_member'
		]);

		if (!$canManage) {
			if ($isAjax) {
				$this->_json(['status' => false, 'message' => 'Permission denied.']);
				return;
			}
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('staff');
		}

		$staff = ($role_name === 'super_admin')
			? $this->Staff_model->get($id)
			: $this->Staff_model->get_by_id_and_society($id, $session_society_id);

		$this->db->trans_start();

		if ($staff) {
			$this->Staff_model->delete_staff_user($staff['email']);
		}

		$deleted = ($role_name === 'super_admin')
			? $this->Staff_model->delete_staff($id)
			: $this->Staff_model->delete_staff($id, $session_society_id);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$deleted = false;
		}

		if ($isAjax) {
			$this->_json(
				$deleted
				? ['status' => 'success', 'message' => 'Staff and login account deleted.']
				: ['status' => false, 'message' => 'Delete failed or record not found.']
			);
			return;
		}

		$this->session->set_flashdata($deleted ? 'success' : 'error', $deleted ? 'Staff deleted.' : 'Delete failed.');
		redirect('staff');
	}

	/* ──────────────────────────────────────────────
	 *  SOCIETY STATS (super admin only)
	 * ────────────────────────────────────────────── */
	public function society_stats()
	{
		if ($this->session->userdata('role_name') !== 'super_admin') {
			$this->output->set_status_header(403);
			$this->_json(['error' => 'Permission denied']);
			return;
		}

		$this->_json($this->Staff_model->get_staff_count_per_society());
	}

	/* ──────────────────────────────────────────────
	 *  FILTER AJAX 
	 * ────────────────────────────────────────────── */
	public function filter_ajax()
	{
		show_404();
	}
}
