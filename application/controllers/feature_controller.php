<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class feature_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('manage_member_model');
		$this->load->library(['session', 'form_validation']);
		$this->load->helper(['url', 'form']);

		// Guard: must be logged in
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}
	// public function residents()
	// {
	// 	$this->load->view('residents_view');
	// }
	// public function staff()
	// {
	// 	$this->load->model('staff_model');
	// 	$this->load->helper(['url', 'form']);
	// 	$this->load->library(['session', 'form_validation']);

	// 	$this->form_validation->set_rules('first_name', 'First Name', 'required');
	// 	$this->form_validation->set_rules('last_name', 'Last Name', 'required');
	// 	$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
	// 	$this->form_validation->set_rules('phone', 'Phone', 'required');
	// 	$this->form_validation->set_rules('designation', 'Designation', 'required');
	// 	$this->form_validation->set_rules('join_date', 'Join Date', 'required');

	// 	$user_id = $this->session->userdata('user_id');
	// 	//  validation failed → load page
	// 	if ($this->form_validation->run() == FALSE) {
	// 		if (!$this->session->userdata('logged_in')) {
	// 			redirect('login');
	// 		}

	// 		$type = $this->session->userdata('type');
	// 		$role = $this->session->userdata('role');
	// 		$user_id = $this->session->userdata('user_id');


	// 		// if ($type !== 'user' && $type !== 'super_admin') {
	// 		// 	redirect('no_permission');
	// 		// }
	// 		// if ($role === 'super_admin') {
	// 		// 	$data['staff'] = $this->staff_model->get_all();
	// 		// 	$data['recent_staff'] = $this->staff_model->get_recent_staffs(5);
	// 		// } else {
	// 		// 	$data['staff'] = $this->staff_model->gett($user_id);
	// 		// 	$data['recent_staff'] = $this->staff_model->get_recent_staff($user_id, 5);

	// 		// }
	// 		$data['title'] = "staff";
	// 		$this->load->view('header', $data);

	// 		// Get recent staff (latest 5)
	// 		$this->load->view('staff_view', $data);
	// 		return;
	// 	}
	// 	$data = [
	// 		'user_id' => $user_id,
	// 		'first_name' => $this->input->post('first_name', true),
	// 		'last_name' => $this->input->post('last_name', true),
	// 		'email' => $this->input->post('email', true),
	// 		'phone' => $this->input->post('phone', true),
	// 		'emergency_contact' => $this->input->post('emergency_contact', true),
	// 		'designation' => $this->input->post('designation', true),
	// 		'department' => $this->input->post('department', true),
	// 		'join_date' => $this->input->post('join_date', true),
	// 		'shift' => $this->input->post('shift', true),
	// 		'salary' => $this->input->post('salary') ?: null,
	// 		'address' => $this->input->post('address', true),
	// 		'status' => $this->input->post('status', true)
	// 	];

	// 	$id = $this->input->post('id');  // hidden field for edit
	// 	if ($id) {
	// 		// Update existing staff (model must check ownership via user_id)
	// 		$this->staff_model->update_staff($id, $data, $user_id);
	// 		$message = 'Staff updated successfully';
	// 	} else {
	// 		// Insert new staff
	// 		$this->staff_model->insert_staff($data);
	// 		$message = 'Staff added successfully';
	// 	}

	// 	// Handle AJAX request
	// 	if ($this->input->is_ajax_request()) {
	// 		echo json_encode([
	// 			'status' => true,
	// 			'message' => $message
	// 		]);
	// 		return;
	// 	}

	// 	// Non-AJAX form submit – set flash and redirect
	// 	$this->session->set_flashdata('success', $message);
	// 	redirect('manage_member');
	// }

	// public function view($id)
	// {
	// 	$this->load->library('session');
	// 	$this->load->model('Staff_model');

	// 	$staff = $this->Staff_model->get($id);

	// 	if (!$staff) {
	// 		show_404();
	// 		return;
	// 	}

	// 	$data['staff'] = $staff;
	// 	$data['title'] = 'aminities';

	// 	//  IMPORTANT: load partial view only
	// 	$this->load->view('staff', $data);
	// }

	// public function delete_staff($id)
	// {
	// 	// Load necessary resources (if not already autoloaded)
	// 	$this->load->model('staff_model');
	// 	$this->load->library('session');

	// 	// $user_id = $this->session->userdata('user_id');
	// 	// if (!$user_id) {
	// 	// 	// Not logged in – handle according to your app
	// 	// 	if ($this->input->is_ajax_request()) {
	// 	// 		echo json_encode(['status' => false, 'message' => 'You must be logged in']);
	// 	// 		return;
	// 	// 	}
	// 	// 	redirect('login');
	// 	// }

	// 	// Attempt to delete (model checks ownership)
	// 	$deleted = $this->staff_model->delete_staff($id);

	// 	// AJAX request → return JSON
	// 	if ($this->input->is_ajax_request()) {
	// 		if ($deleted) {
	// 			echo json_encode([
	// 				'status' => 'success',
	// 				'message' => 'Staff deleted successfully'
	// 			]);
	// 		} else {
	// 			echo json_encode([
	// 				'status' => false,
	// 				'message' => 'Delete failed or record not found'
	// 			]);
	// 		}
	// 		return;
	// 	}

	// 	// Normal form submit (non‑AJAX) → set flash and redirect
	// 	if ($deleted) {
	// 		$this->session->set_flashdata('success', 'Staff deleted successfully');
	// 	} else {
	// 		$this->session->set_flashdata('error', 'Delete failed');
	// 	}
	// 	redirect('staff'); // or wherever your staff list page is
	// }

	// public function edit($id)
	// {
	// 	$this->load->model('staff_model');

	// 	$staff = $this->staff_model->get($id);

	// 	if (!$staff) {
	// 		show_404();
	// 		return;
	// 	}

	// 	$this->output
	// 		->set_content_type('application/json')
	// 		->set_output(json_encode($staff));
	// }


	// public function notices()
	// {
	// 	$this->load->view('notices_view');
	// }

	public function aminities()
	{
		$data['title'] = 'aminities';

		$this->load->view('aminities_view', $data);
	}
	// public function reports()
	// {
	// 	$data['title'] = 'reports';
	// 	$this->load->view('report_view', $data);
	// }
	// public function visitors()
	// {
	// 	$this->load->view('visitors_view');
	// }
	// ========== get member ==============

	public function member()
	{
		$role_name = $this->session->userdata('role_name');
		$society_id = $this->session->userdata('society_id');
		$isSuperAdmin = ($role_name === 'super_admin');

		// Pagination settings
		$per_page = 10; // Items per page
		$page = (int) $this->input->get('page') ?: 1;
		if ($page < 1)
			$page = 1;
		$offset = ($page - 1) * $per_page;

		$filters = [
			'society_id' => $isSuperAdmin ? ((int) $this->input->get('society_id') ?: '') : $society_id,
			'wing_id' => (int) $this->input->get('wing_id') ?: '',
			'member_type' => $this->input->get('member_type', TRUE) ?: '',
			'role' => $this->input->get('role', TRUE) ?: '',
			'status' => $this->input->get('status', TRUE) ?? '',
			'search' => $this->input->get('search', TRUE) ?: '',
		];
		if ($filters['status'] === null || $filters['status'] === false)
			$filters['status'] = '';

		// Get total count for pagination
		$total_rows = $this->manage_member_model->count_filtered($filters);

		// Get paginated members
		$members = $this->manage_member_model->get_filtered($filters, $per_page, $offset);

		// Pagination config
		$this->load->library('pagination');
		$config['base_url'] = site_url('manage_member') . '?' . http_build_query(array_filter($filters));
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $per_page;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'page';
		$config['use_page_numbers'] = TRUE;
		$config['reuse_query_string'] = TRUE;
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['first_link'] = '«';
		$config['last_link'] = '»';
		$config['next_link'] = '›';
		$config['prev_link'] = '‹';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
		$config['cur_tag_close'] = '</span></li>';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['attributes'] = ['class' => 'page-link'];

		$this->pagination->initialize($config);
		$pagination_links = $this->pagination->create_links();

		$stats = $this->manage_member_model->get_stats($filters);

		$societyGroups = [];
		if ($isSuperAdmin) {
			foreach ($members as $m) {
				$societyGroups[$m->society_name ?? 'Unknown'][] = $m;
			}
			ksort($societyGroups);
		}

		$societies = $isSuperAdmin ? $this->manage_member_model->get_societies() : [];
		$wings = $isSuperAdmin
			? $this->manage_member_model->get_wings($filters['society_id'] ?: null)
			: $this->manage_member_model->get_wings($society_id);

		// Vacant flats for the flat picker
		$vacantFlats = [];
		if (!$isSuperAdmin && $society_id) {
			$this->load->model('Society_setup_model', 'setup_model');
			$vacantFlats = $this->setup_model->get_vacant_flats((int) $society_id);
		}

		$data = [
			'title' => 'Members',
			'members' => $members,
			'isSuperAdmin' => $isSuperAdmin,
			'societyGroups' => $societyGroups,
			'societies' => $societies,
			'wings' => $wings,
			'committee_roles' => $this->manage_member_model->get_roles(),
			'filters' => $filters,
			'vacantFlats' => $vacantFlats,
			'totalMembers' => $stats['total'],
			'owners' => $stats['owners'],
			'tenants' => $stats['tenants'],
			'committee' => $stats['committee'],
			'active' => $stats['active'],
			'inactive' => $stats['inactive'],
			'newThisMonth' => $stats['new_this_month'],
			'ownerPercent' => $stats['total'] > 0 ? round(($stats['owners'] / $stats['total']) * 100) : 0,
			'tenantPercent' => $stats['total'] > 0 ? round(($stats['tenants'] / $stats['total']) * 100) : 0,
			'pagination' => $pagination_links, // Added
			'total_records' => $total_rows,     // Added
			'per_page' => $per_page,
			'current_page' => $page,
		];

		$this->load->view('header', $data);
		$this->load->view('manage_member_view', $data);
	}

	/* 
	  SAVE — Add or Edit member
	 */
	public function save()
	{
		$this->load->library('email');
		$this->load->model('Society_setup_model', 'setup_model');

		$society_id = (int) $this->session->userdata('society_id');
		$memberId = (int) $this->input->post('memberId');
		$isEdit = $memberId > 0;

		// Validation rules
		$this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
		$this->form_validation->set_rules('phone', 'Phone', 'required|numeric|exact_length[10]');
		$this->form_validation->set_rules('member_type', 'Member Type', 'required|in_list[owner,tenant]');
		if (!$isEdit) {
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
		}

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('manage_member');
		}

		// ── Resolve flat fields ──
		$flatId = (int) $this->input->post('flat_id');
		$flatNo = $this->input->post('flat_no', TRUE);
		$wingId = $this->input->post('wing_id', TRUE) ?: null;

		// On Add: flat_id must be provided (from picker)
		if (!$isEdit) {
			if (!$flatId) {
				$this->session->set_flashdata('error', 'Please select a flat for the new member.');
				redirect('manage_member');
			}

			// Load flat from DB — authoritative source
			$flat = $this->setup_model->get_flat_by_id($flatId);
			if (!$flat || (int) $flat->society_id !== $society_id) {
				$this->session->set_flashdata('error', 'Invalid flat selection.');
				redirect('manage_member');
			}
			if ((int) $flat->status !== 1) {
				$this->session->set_flashdata('error', 'This flat is no longer vacant. Please choose another.');
				redirect('manage_member');
			}

			$flatNo = $flat->flat_no;
			$wingId = $flat->wing_id;
		}

		$memberData = [
			'name' => trim($this->input->post('first_name', TRUE) . ' ' . $this->input->post('last_name', TRUE)),
			'flat_no' => $flatNo,
			'wing_id' => $wingId ?: null,
			'phone' => $this->input->post('phone', TRUE),
			'email' => $this->input->post('email', TRUE) ?: null,
			'member_type' => $this->input->post('member_type', TRUE),
			'status' => in_array($this->input->post('status', TRUE), ['1', 'Active']) ? 1 : 0,
			'society_id' => $society_id,
		];

		$password = $this->input->post('password', FALSE);
		if (!empty($password)) {
			$memberData['password'] = password_hash($password, PASSWORD_DEFAULT);
		}

		/* ── EDIT ── */
		if ($isEdit) {
			if ($this->manage_member_model->memberExists($memberData['phone'], $memberData['email'], $society_id, $memberId)) {
				$this->session->set_flashdata('error', 'Phone or email already in use by another member.');
				redirect('manage_member');
			}
			$this->manage_member_model->update_member($memberId, $memberData);
			$this->session->set_flashdata('success', 'Member updated successfully.');
			redirect('manage_member');
		}

		/* ── ADD ── */
		if ($this->manage_member_model->memberExists($memberData['phone'], $memberData['email'], $society_id)) {
			$this->session->set_flashdata('error', 'A member with this phone or email already exists.');
			redirect('manage_member');
		}
		if (
			$memberData['member_type'] === 'owner' &&
			$this->manage_member_model->ownerExistsForFlat($memberData['flat_no'], $society_id)
		) {
			$this->session->set_flashdata('error', 'An owner already exists for this flat.');
			redirect('manage_member');
		}

		$this->db->trans_start();

		$insertId = $this->manage_member_model->create($memberData);

		if ($insertId) {
			// Mark flat as occupied (status = 0)
			$this->setup_model->occupy_flat($flatId);

			// Assign owner/tenant role
			$roleName = $memberData['member_type'] === 'owner' ? 'owner' : 'tenant';
			$roleRow = $this->db->get_where('roles', ['role_name' => $roleName])->row();
			if ($roleRow) {
				$this->db->insert('user_roles', ['user_id' => $insertId, 'role_id' => $roleRow->id]);
			}
		}

		$this->db->trans_complete();
		$txOk = $this->db->trans_status();

		if (!$txOk) {
			$this->session->set_flashdata('error', 'Failed to save member. Please try again.');
			redirect('manage_member');
		}

		// Send welcome email
		if ($insertId && !empty($memberData['email'])) {
			$this->_sendWelcomeEmail([
				'name' => $memberData['name'],
				'email' => $memberData['email'],
				'flat_no' => $memberData['flat_no'],
			], $password);
		}

		$this->session->set_flashdata(
			'success',
			'Member added successfully! Flat ' . $memberData['flat_no'] . ' is now marked as occupied.'
			. (!empty($memberData['email']) ? ' Welcome email sent.' : '')
		);
		redirect('manage_member');
	}

	/* ──────────────────────────────────────────────────────────────
	 *  DELETE MEMBER — also vacates the flat
	 * ────────────────────────────────────────────────────────────── */
	public function delete_member($id)
	{
		$this->load->model('Society_setup_model', 'setup_model');

		if (empty($id)) {
			$this->session->set_flashdata('error', 'Invalid member ID.');
			redirect('manage_member');
		}

		$society_id = (int) $this->session->userdata('society_id');

		// Get member's flat info before deletion
		$user = $this->db->get_where('users', ['id' => $id, 'society_id' => $society_id])->row();

		if ($this->manage_member_model->delete($id)) {
			// Vacate the flat they were in
			if ($user && $user->flat_no && $user->wing_id) {
				$this->setup_model->vacate_flat_by_flat_no(
					$user->flat_no,
					(int) $user->wing_id,
					$society_id
				);
			}
			$this->session->set_flashdata('success', 'Member deleted. Flat ' . $user->flat_no . ' is now vacant.');
		} else {
			$this->session->set_flashdata('error', 'Failed to delete member.');
		}
		redirect('manage_member');
	}
	/* ──────────────────────────────────────────────────────────────
	 *  ASSIGN COMMITTEE ROLE
	 * ────────────────────────────────────────────────────────────── */
	public function assign_role()
	{
		$user_id = $this->input->post('committeeMemberId', TRUE);
		$role_id = $this->input->post('committeeRole', TRUE);

		if (!$user_id || !$role_id) {
			$this->session->set_flashdata('error', 'Please select both a member and a role.');
			redirect('manage_member');
		}
		if (!in_array((int) $role_id, $this->manage_member_model::COMMITTEE_ROLE_IDS)) {
			$this->session->set_flashdata('error', 'Invalid role selected.');
			redirect('manage_member');
		}
		if ($this->manage_member_model->assign_committee_role($user_id, (int) $role_id)) {
			$this->session->set_flashdata('success', 'Committee role assigned successfully.');
		} else {
			$this->session->set_flashdata('error', 'Failed to assign committee role.');
		}
		redirect('manage_member');
	}

	/* ──────────────────────────────────────────────────────────────
	 *  REMOVE COMMITTEE ROLE
	 * ────────────────────────────────────────────────────────────── */
	public function remove_role()
	{
		$user_id = $this->input->post('id', TRUE);
		if (!$user_id) {
			$this->session->set_flashdata('error', 'Invalid user.');
			redirect('manage_member');
		}
		if ($this->manage_member_model->remove_committee_role($user_id)) {
			$this->session->set_flashdata('success', 'Committee role removed.');
		} else {
			$this->session->set_flashdata('error', 'Failed to remove committee role.');
		}
		redirect('manage_member');
	}
	public function filter_ajax()
	{
		$filters = $this->input->post();

		$members = $this->manage_member_model->get_filtered($filters);

		$html = "";
		foreach ($members as $m) {

			$status = ($m->status == 1) ? 'Active' : 'Inactive';
			$modalId = 'vm_' . $m->id; // generate properly

			$memberData = htmlspecialchars(json_encode([
				"id" => $m->id,
				"name" => $m->name,
				"flat_no" => $m->flat_no,
				"wing_id" => $m->wing_id,
				"member_type" => $m->member_type,
				"phone" => $m->phone,
				"email" => $m->email,
				"status" => $m->status
			]), ENT_QUOTES, 'UTF-8');

			$html .= "<tr>
        <td>{$m->name}</td>
        <td>{$m->flat_no}</td>
        <td>{$m->wing_name}</td>
        <td>{$m->member_type}</td>
        <td>" . ($m->committee_role ?? '-') . "</td>
			<td>{$m->society_name}</td>
        <td>{$status}</td>
        <td>
              <button class='btn-icon' 
                        onclick=\"openModal('{$modalId}')\">
                    <i class='fas fa-eye'></i>
                </button>
        </td>
    </tr>";
		}

		echo json_encode(['html' => $html]);
	}
	// ── WELCOME EMAIL ──
	private function _sendWelcomeEmail($member, $plainPassword)
	{
		$this->email->from('sharvil.tmbs25@gmail.com', 'Society Management');
		$this->email->to($member['email']);
		$this->email->subject('Welcome to Society Management');
		$this->email->attach(FCPATH . 'assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png', 'inline');
		$cid = $this->email->attachment_cid(FCPATH . 'assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png');

		$message = "
				<center>
				<img src='cid:$cid' width='120'>
				</center>

				<h3>Welcome {$member['name']}!</h3>
				<p>You have been successfully added as a society member.</p>

				<p><strong>Your Login Details:</strong></p>
				<p>
				Email: {$member['email']}<br>
				Password: {$plainPassword}
				</p>

				<p>Flat No: {$member['flat_no']}</p>

				<br>
				<p>Regards,<br>Society Management Team</p>
				";

		$this->email->set_mailtype('html');
		$this->email->message($message);

		if (!$this->email->send()) {
			log_message('error', $this->email->print_debugger());
		}
	}
	public function import_members()
	{
		/* Only chairman / admin can import */
		$role = strtolower((string) $this->session->userdata('role_name'));
		if (!in_array($role, ['chairman', 'super_admin', 'superadmin'])) {
			$this->session->set_flashdata('error', 'You do not have permission to import members.');
			redirect('manage_member');
		}

		$societyId = (int) $this->session->userdata('society_id');

		/* ── Validate file upload ── */
		if (empty($_FILES['csv_file']['name'])) {
			$this->session->set_flashdata('error', 'Please select a CSV file to upload.');
			redirect('manage_member');
		}

		$file = $_FILES['csv_file'];

		/* Check extension */
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if ($ext !== 'csv') {
			$this->session->set_flashdata('error', 'Only .csv files are allowed.');
			redirect('manage_member');
		}

		/* Check upload error */
		if ($file['error'] !== UPLOAD_ERR_OK) {
			$this->session->set_flashdata('error', 'File upload failed. Please try again.');
			redirect('manage_member');
		}

		/* Check file size (max 2 MB) */
		if ($file['size'] > 2 * 1024 * 1024) {
			$this->session->set_flashdata('error', 'File too large. Maximum size is 2 MB.');
			redirect('manage_member');
		}

		/* ── Run import ── */
		$result = $this->manage_member_model->import_csv($file['tmp_name'], $societyId);

		/* ── Build flash message ── */
		$inserted = $result['inserted'];
		$skipped = $result['skipped'];
		$errors = $result['errors'] ?? [];
		$warnings = $result['warnings'] ?? [];

		if ($inserted > 0) {
			$msg = "<strong>{$inserted} member(s) imported successfully.</strong>";
			if ($skipped > 0)
				$msg .= " {$skipped} row(s) skipped.";
			$this->session->set_flashdata('success', $msg);
		} else {
			$msg = "No members were imported.";
			if ($skipped > 0)
				$msg .= " {$skipped} row(s) skipped.";
			$this->session->set_flashdata('error', $msg);
		}

		/* Store detailed errors/warnings in session for the result modal */
		if (!empty($errors) || !empty($warnings)) {
			$this->session->set_userdata('import_result', [
				'inserted' => $inserted,
				'skipped' => $skipped,
				'errors' => $errors,
				'warnings' => $warnings,
			]);
		} else {
			$this->session->unset_userdata('import_result');
		}

		redirect('manage_member');
	}

	/* ──────────────────────────────────────────────────────────────
	 *  DOWNLOAD SAMPLE CSV
	 * ────────────────────────────────────────────────────────────── */
	public function download_member_sample()
	{
		$filename = 'member_import_sample.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		header('Expires: 0');

		$out = fopen('php://output', 'w');

		/* BOM for Excel UTF-8 compatibility */
		fputs($out, "\xEF\xBB\xBF");

		/* Header row */
		fputcsv($out, [
			'first_name',
			'last_name',
			'phone',
			'email',
			'wing_name',
			'flat_no',
			'member_type',
			'password',
			'status'
		]);

		/* Sample rows */
		$samples = [
			['Rajesh', 'Kumar', '9876543210', 'rajesh@email.com', 'A', 'A-101', 'owner', 'Pass@123', '1'],
			['Priya', 'Sharma', '9876543211', 'priya@email.com', 'A', 'A-102', 'tenant', 'Pass@123', '1'],
			['Amit', 'Desai', '9876543212', 'amit@email.com', 'B', 'B-201', 'owner', 'Pass@123', '1'],
			['Neha', 'Joshi', '9876543213', 'neha@email.com', 'B', 'B-202', 'tenant', '', '1'],
			['Vikram', 'Singh', '9876543214', 'vikram@email.com', 'A', 'A-103', 'owner', '', '1'],
		];
		foreach ($samples as $row)
			fputcsv($out, $row);

		fclose($out);
		exit;
	}

	// =========== import member / csv ===========
	public function import_csv()
	{
		$this->load->model('manage_member_model');
		$this->load->library(['session']);

		if (empty($_FILES['csv_file']['name'])) {
			$this->session->set_flashdata('error', 'Please upload a CSV file');
			redirect('manage_member');
		}

		$file = $_FILES['csv_file']['tmp_name'];
		$handle = fopen($file, "r");

		if ($handle === FALSE) {
			$this->session->set_flashdata('error', 'Unable to read CSV file');
			redirect('manage_member');
		}

		$header = fgetcsv($handle); // skip header

		$success = 0;
		$skipped = 0;

		while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

			$data = array_combine($header, $row);

			// Required fields
			if (
				empty($data['first_name']) ||
				empty($data['last_name']) ||
				empty($data['flat_number']) ||
				empty($data['phone'])
			) {
				$skipped++;
				continue;
			}

			if ($this->manage_member_model->memberExists($data['phone'], $data['email'])) {
				$skipped++;
				continue;
			}

			// One owner per flat
			if (
				strtolower($data['member_type']) === 'owner' &&
				$this->manage_member_model->ownerExistsForFlat($data['flat_number'])
			) {
				$skipped++;
				continue;
			}

			// Prepare insert data
			$insertData = [
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'flat_number' => $data['flat_number'],
				'wing' => $data['wing'] ?? '',
				'member_type' => $data['member_type'] ?? 'tenant',
				'phone' => $data['phone'],
				'email' => $data['email'] ?? '',
				'moveindate' => $data['moveindate'] ?? null,
				'status' => $data['status'] ?? 'Active',
			];

			if (!empty($data['password'])) {
				$insertData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
			}

			$this->manage_member_model->add($insertData);
			$success++;
		}

		fclose($handle);

		$this->session->set_flashdata(
			'success',
			"CSV Import Completed. Added: {$success}, Skipped: {$skipped}"
		);

		redirect('manage_member');
	}


}
