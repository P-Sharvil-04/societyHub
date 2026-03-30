<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usercontroller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library(['session', 'form_validation', 'email']);
		$this->load->helper(['url', 'form']);
		$this->load->model('Usermodel');
		$this->load->library(['session', 'email']);
	}

	// ================= OTP SEND =================
	public function send_otp()
	{
		header('Content-Type: application/json');

		$email = $this->input->post('email', true);

		if (!$email) {
			echo json_encode(['status' => false, 'msg' => 'Email required']);
			return;
		}

		$otp = rand(100000, 999999);

		// store otp in session
		$this->session->set_userdata([
			'reg_otp' => $otp,
			'reg_email' => $email
		]);

		$this->email->from('sharvil.tmbs25@gmail.com', 'Society Management');
		$this->email->to($email);
		$this->email->subject('Your Registration OTP');
		$this->email->message("<h3>Your OTP is: $otp</h3>");

		if ($this->email->send()) {
			echo json_encode(['status' => true, 'msg' => 'OTP sent to email']);
		} else {
			echo json_encode([
				'status' => false,
				'msg' => 'Email sending failed',
				'error' => $this->email->print_debugger()
			]);
		}
	}

	// ================= OTP VERIFY =================
	public function verify_otp()
	{
		header('Content-Type: application/json');

		$otp = $this->input->post('otp');

		if ($otp == $this->session->userdata('reg_otp')) {
			$this->session->set_userdata('otp_verified', true);
			echo json_encode(['status' => true, 'msg' => 'OTP verified']);
		} else {
			echo json_encode(['status' => false, 'msg' => 'Invalid OTP']);
		}
	}

	// ================= REGISTER =================
	public function register()
	{
		// If POST request → process registration
		if ($this->input->post()) {

			header('Content-Type: application/json');

			if (!$this->session->userdata('otp_verified')) {
				echo json_encode(['status' => false, 'msg' => 'OTP verification required']);
				return;
			}

			$this->form_validation->set_rules('name', 'Name', 'required');
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

			if ($this->form_validation->run() == false) {
				echo json_encode(['status' => false, 'msg' => validation_errors()]);
				return;
			}

			$data = [
				'name' => $this->input->post('name', true),
				'email' => $this->session->userdata('reg_email'),
				'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT)
			];

			$this->usermodel->register($data);

			// clear registration session
			$this->session->unset_userdata(['reg_otp', 'otp_verified', 'reg_email']);

			echo json_encode([
				'status' => true,
				'msg' => 'Registration successful',
				'redirect' => base_url('login')
			]);
			return;
		}
		$this->load->view('register_view');
	}

	// ================= LOGIN =================

	public function login()
	{
		if ($this->session->userdata('logged_in')) {
			redirect('dashboard');
		}

		if ($this->input->post()) {
			header('Content-Type: application/json');

			$email = $this->input->post('email', TRUE);
			$password = $this->input->post('password', FALSE);

			if (empty($email) || empty($password)) {
				echo json_encode(['status' => false, 'msg' => 'Email and password are required.']);
				return;
			}

			$user = $this->Usermodel->get_user_by_email($email);

			if (!$user) {
				echo json_encode(['status' => false, 'msg' => 'No account found with that email address.']);
				return;
			}

			if (!password_verify($password, $user->password)) {
				echo json_encode(['status' => false, 'msg' => 'Incorrect password. Please try again.']);
				return;
			}

			// --- New: enforce society status / role login rules ---
			// Super admin bypasses the checks and can always log in.
			$userRole = !empty($user->role_name) ? $user->role_name : null;

			if ($userRole !== 'super_admin') {

				$societyStatus = null;

				if (!empty($user->society_id)) {
					$socRow = $this->db
						->select('status')
						->from('societies')
						->where('id', $user->society_id)
						->limit(1)
						->get()
						->row();

					$societyStatus = $socRow->status ?? null;
				}

				//  BLOCK: Deactivated
				if ($societyStatus === 'deactivated') {
					echo json_encode([
						'status' => false,
						'msg' => 'The society is Deactivated. Contact Super Admin for activation.'
					]);
					return;
				}

				//  BLOCK: Pending
				if ($societyStatus === 'pending') {
					echo json_encode([
						'status' => false,
						'msg' => 'The society is under review. Please wait or contact admin.'
					]);
					return;
				}

				//  BLOCK: Unknown / missing status
				if ($societyStatus !== 'active') {
					echo json_encode([
						'status' => false,
						'msg' => 'Invalid society status. Contact support.'
					]);
					return;
				}
			}
			// --- End new checks ---

			$societyName = 'Your Society';        // fallback defaults
			$societyTagline = 'Residential Society';

			if (!empty($user->society_id)) {
				$societyRow = $this->db
					->select('name as society_name, society_tagline')
					->from('societies')
					->where('id', $user->society_id)
					->limit(1)
					->get()
					->row();

				if ($societyRow) {
					$societyName = $societyRow->society_name ?? $societyName;
					$societyTagline = $societyRow->society_tagline ?? $societyTagline;
				}
			}
			$roleName = !empty($user->role_name) ? $user->role_name : null;
			$memberType = !empty($user->member_type) ? $user->member_type : null;

			$this->session->set_userdata([
				'logged_in' => TRUE,
				'user_id' => $user->id,
				'society_id' => $user->society_id,
				'society_name' => $societyName,
				'society_tagline' => $societyTagline,
				'role_name' => $roleName,
				'member_type' => $memberType,
				'user_name' => $user->name,
				'user_email' => $user->email,
			]);

			$roleLabels = [
				'super_admin' => 'Super Admin',
				'chairman' => 'Chairman',
				'committee_member' => 'Committee Member',
				'secretary' => 'Secretary',
				'accountant' => 'Accountant',
				'security' => 'Security',
				'staff' => 'Staff',
				'owner' => 'Owner',
				'tenant' => 'Tenant',
			];
			$displayKey = $roleName ?? $memberType ?? 'user';
			$label = $roleLabels[$displayKey] ?? ucfirst($displayKey);

			$this->session->set_flashdata(
				'success',
				"Welcome back, {$user->name}! Logged in as {$label}."
			);

			echo json_encode([
				'status' => true,
				'msg' => "Login successful. Welcome, {$user->name}!",
				'role' => $roleName,
				'redirect' => base_url('dashboard'),
			]);
			return;
		}

		$this->load->view('login_view');
	}
	// ================= DASHBOARD =================
	public function dashboard()
	{
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
		// inside controller constructor or specific method

		$data['title'] = "dashboard";

		$this->load->view('header', $data);
		$this->load->view('dashboard_view');
	}

	// ================= LOGOUT =================
	public function logout()
	{
		$this->session->sess_destroy();
		$this->session->set_flashdata('success', 'You have been logged out successfully.');
		redirect('login');
	}
	// ============ profile ================
	// public function profile()
	// {
	// 	$this->load->view('profile_view');
	// }

	// =========== settings ==================
	public function settings()
	{
		$data['title'] = 'settings';
		$this->load->view('settings_view', $data);
	}

	// ==========- add expense ===============
	public function add_expense()
	{
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}

		$this->load->view('add_expense_view');
	}
	// ================= Save Expense =================
	public function save_expense()
	{
		header('Content-Type: application/json');

		if (!$this->session->userdata('logged_in')) {
			echo json_encode(['status' => false, 'msg' => 'Login required']);
			return;
		}

		$user_id = $this->session->userdata('user_id');
		$user_email = $this->session->userdata('user_email');

		if (!$user_email) {
			echo json_encode(['status' => false, 'msg' => 'Email not found']);
			return;
		}

		$data = [
			'user_id' => $user_id,
			'title' => $this->input->post('title', true),
			'amount' => $this->input->post('amount', true),
			'category' => $this->input->post('category', true),
			'note' => $this->input->post('note', true),
			'created_at' => date('Y-m-d H:i:s')
		];

		if ($this->usermodel->insert_expense($data)) {

			$message = "
                <h3>New Expense Added</h3>
                <p><b>Title:</b> {$data['title']}</p>
                <p><b>Amount:</b> ₹{$data['amount']}</p>
                <p><b>Category:</b> {$data['category']}</p>
                <p><b>Note:</b> {$data['note']}</p>
                <p><b>Date:</b> {$data['created_at']}</p>
            ";

			$this->email->from('sharvil.tmbs25@gmail.com', 'Expense Tracker');
			$this->email->to('dhyeytmbs25@gmail.com');
			$this->email->subject('Expense Added Successfully');
			$this->email->message($message);

			$this->email->send(); // even if fails, expense is saved

			echo json_encode([
				'status' => true,
				'msg' => 'Expense saved & email sent successfully',
				'redirect' => base_url('usercontroller/dashboard')
			]);
		} else {
			echo json_encode(['status' => false, 'msg' => 'Failed to save expense']);
		}
	}

	// ============= get role ================ 
	public function get_role()
	{
		$this->load->model('Usermodel');
		$roles = $this->Usermodel->get_roles();

		echo "<option>select role</option>";
		foreach ($roles as $r) {
			echo "<option value='" . $r->id . "'>" . $r->role_name . "</option>";
		}
	}

	// ============ get wings ===============
	public function get_wings()
	{
		$this->load->model('/Usermodel');
		$wings = $this->Usermodel->get_wings();
		// echo "<option>select wing</option>";
		foreach ($wings as $w) {
			echo "<option value='" . $w->id . "'>" . $w->wing_name . "</option>";
		}
	}

}
