<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class settings_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Settings_model');
		$this->load->helper(['url', 'form']);
		$this->load->library(['session', 'form_validation']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	/* ── session helpers ── */
	private function _role()
	{
		return $this->session->userdata('role_name');
	}
	private function _uid()
	{
		return (int) $this->session->userdata('user_id');
	}
	private function _society()
	{
		return (int) $this->session->userdata('society_id');
	}
	private function _is_super()
	{
		return $this->_role() === 'super_admin';
	}
	private function _is_owner()
	{
		return $this->_role() === 'owner';
	}

	/* ════════════════════════════════════════════════════════════
	 *  INDEX
	 * ════════════════════════════════════════════════════════════ */
	public function index()
	{
		$role = $this->_role();

		// Load settings appropriate for this role
		if ($this->_is_super()) {
			$settings = $this->Settings_model->get_system_settings();
		} else {
			// Society settings + fall back to system defaults for unset keys
			$sys_defaults = $this->Settings_model->get_system_settings();
			$soc_settings = $this->Settings_model->get_society_settings($this->_society());
			$settings = array_merge($sys_defaults, $soc_settings);
		}

		$user = $this->Settings_model->get_user($this->_uid());
		$all_societies = $this->_is_super() ? $this->Settings_model->get_all_societies() : [];
		$system_info = $this->_is_super() ? $this->Settings_model->get_system_info() : null;

		// Load the society row for chairman (to edit society name/address etc.)
		$society_row = null;
		if (!$this->_is_super() && !$this->_is_owner()) {
			$society_row = $this->db->get_where('societies', ['id' => $this->_society()])->row_array();
		}

		$data = [
			'title' => 'Settings',
			'activePage' => 'settings',
			'settings' => $settings,
			'user' => $user,
			'society_row' => $society_row,
			'all_societies' => $all_societies,
			'system_info' => $system_info,
			'isSuperAdmin' => $this->_is_super(),
			'isOwner' => $this->_is_owner(),
			'isChairman' => ($role === 'chairman'),
			'role' => $role,
		];

		$this->load->view('header', $data);
		$this->load->view('settings_view', $data);
	}

	/* ════════════════════════════════════════════════════════════
	 *  SAVE PROFILE  (all roles)
	 * ════════════════════════════════════════════════════════════ */
	public function save_profile()
	{
		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('settings_controller');
		}

		$email = $this->input->post('email', TRUE);
		if ($this->Settings_model->email_taken($email, $this->_uid())) {
			$this->session->set_flashdata('error', 'That email is already in use by another account.');
			redirect('settings_controller');
		}

		$upd = [
			'name' => $this->input->post('name', TRUE),
			'email' => $email,
			'phone' => $this->input->post('phone', TRUE),
		];

		// Optional password change
		$curr_pw = $this->input->post('current_password', FALSE);
		$new_pw = $this->input->post('new_password', FALSE);
		$conf_pw = $this->input->post('confirm_password', FALSE);

		if (!empty($new_pw)) {
			if (strlen($new_pw) < 6) {
				$this->session->set_flashdata('error', 'New password must be at least 6 characters.');
				redirect('settings_controller');
			}
			if ($new_pw !== $conf_pw) {
				$this->session->set_flashdata('error', 'New password and confirmation do not match.');
				redirect('settings_controller');
			}
			$user = $this->Settings_model->get_user($this->_uid());
			if (!password_verify($curr_pw, $user['password'] ?? '')) {
				$this->session->set_flashdata('error', 'Current password is incorrect.');
				redirect('settings_controller');
			}
			$upd['password'] = password_hash($new_pw, PASSWORD_DEFAULT);
		}

		$this->Settings_model->update_profile($this->_uid(), $upd);
		$this->session->set_userdata('name', $upd['name']);
		$this->session->set_flashdata('success', 'Profile updated successfully.');
		redirect('settings_controller');
	}

	/* ════════════════════════════════════════════════════════════
	 *  SAVE SYSTEM SETTINGS  (super admin only)
	 * ════════════════════════════════════════════════════════════ */
	public function save_system()
	{
		if (!$this->_is_super()) {
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('settings_controller');
		}

		$checkboxes = ['global_test_mode', 'allow_guests', 'committee_access', 'auto_reminders_global'];
		$keys = [
			'global_razorpay_key_id',
			'global_razorpay_key_secret',
			'global_test_mode',
			'allow_guests',
			'committee_access',
			'default_role',
			'system_timezone',
			'system_env',
			'system_version',
			'auto_reminders_global',
			'session_timeout_global',
			'password_expiry_global',
		];

		$batch = [];
		foreach ($keys as $k) {
			$batch[$k] = in_array($k, $checkboxes)
				? ($this->input->post($k) ? '1' : '0')
				: $this->input->post($k, TRUE);
		}

		$this->Settings_model->save_system_settings($batch);
		$this->session->set_flashdata('success', 'System settings saved.');
		redirect('settings_controller');
	}

	/* ════════════════════════════════════════════════════════════
	 *  SAVE SOCIETY SETTINGS  (chairman)
	 * ════════════════════════════════════════════════════════════ */
	public function save_society()
	{
		if ($this->_is_super() || $this->_is_owner()) {
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('settings_controller');
		}

		$soc_id = $this->_society();

		// Update societies table (name, address, phone, email)
		$soc_data = array_filter([
			'name' => $this->input->post('society_name', TRUE),
			'address' => $this->input->post('society_address', TRUE),
			'phone' => $this->input->post('society_phone', TRUE),
			'email' => $this->input->post('society_email', TRUE),
		]);
		if (!empty($soc_data)) {
			$this->Settings_model->update_society($soc_id, $soc_data);
		}

		// Persist configurable settings
		$checkboxes = [
			'auto_reminders',
			'email_notif',
			'sms_notif',
			'push_notif',
			'two_factor',
			'razorpay_test_mode'
		];
		$keys = array_merge($checkboxes, [
			'maintenance_due_date',
			'maintenance_late_fee',
			'interest_rate',
			'notif_email',
			'session_timeout',
			'password_expiry',
			'razorpay_key_id',
			'razorpay_key_secret',
		]);

		$batch = [];
		foreach ($keys as $k) {
			$batch[$k] = in_array($k, $checkboxes)
				? ($this->input->post($k) ? '1' : '0')
				: $this->input->post($k, TRUE);
		}

		$this->Settings_model->save_society_settings($soc_id, $batch);
		$this->session->set_flashdata('success', 'Settings saved.');
		redirect('settings_controller');
	}

	/* ════════════════════════════════════════════════════════════
	 *  SAVE OWNER PREFERENCES  (owner — notifications + security only)
	 * ════════════════════════════════════════════════════════════ */
	public function save_owner()
	{
		if (!$this->_is_owner()) {
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('settings_controller');
		}

		$checkboxes = ['email_notif', 'sms_notif', 'push_notif', 'two_factor'];
		$keys = array_merge($checkboxes, ['session_timeout', 'password_expiry']);

		$batch = [];
		foreach ($keys as $k) {
			$batch[$k] = in_array($k, $checkboxes)
				? ($this->input->post($k) ? '1' : '0')
				: $this->input->post($k, TRUE);
		}

		$this->Settings_model->save_society_settings($this->_society(), $batch);
		$this->session->set_flashdata('success', 'Preferences saved.');
		redirect('settings_controller');
	}

	/* ════════════════════════════════════════════════════════════
	 *  TOGGLE SOCIETY STATUS  (super admin)
	 * ════════════════════════════════════════════════════════════ */
	public function toggle_society($id)
	{
		if (!$this->_is_super()) {
			redirect('settings_controller');
		}
		$soc = $this->db->get_where('societies', ['id' => (int) $id])->row_array();
		if ($soc) {
			$new = ($soc['status'] ?? 'active') === 'active' ? 'diactivated' : 'active';
			$this->Settings_model->update_society((int) $id, ['status' => $new]);
			$this->session->set_flashdata('success', 'Society status toggled to ' . $new . '.');
		}
		redirect('settings_controller');
	}
}
