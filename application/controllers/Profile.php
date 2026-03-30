<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Profile_model');
		$this->load->library(['session', 'form_validation', 'upload']);
		$this->load->helper(['url', 'form', 'security']);
		// Redirect to login if not logged in
		if (!$this->session->userdata('user_id')) {
			redirect('login');
		}
	}

	public function index()
	{
		$user_id = (int) $this->session->userdata('user_id');
		$data['user'] = $this->Profile_model->get_user($user_id);
		$this->load->view('profile_view', $data);
	}

	public function update_profile()
	{
		$user_id = (int) $this->session->userdata('user_id');

		// Basic validation
		$this->form_validation->set_rules('name', 'Full name', 'trim|required|max_length[150]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[150]');
		$this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]');
		$this->form_validation->set_rules('flat_no', 'Flat number', 'trim|max_length[50]');

		if ($this->form_validation->run() === FALSE) {
			// show form again with errors
			$this->index();
			return;
		}

		// Prepare update data (do not overwrite password here)
		$update = [
			'name' => $this->input->post('name', TRUE),
			'email' => $this->input->post('email', TRUE),
			'phone' => $this->input->post('phone', TRUE),
			'flat_no' => $this->input->post('flat_no', TRUE),
			'member_type' => $this->input->post('member_type', TRUE) ?: 'owner',
			'status' => $this->input->post('status') !== null ? (int) $this->input->post('status') : 1,
			'updated_at' => date('Y-m-d H:i:s')
		];

		// Handle profile image upload (only if profile_image column exists)
		if (!empty($_FILES['profile_image']['name'])) {
			$upload_path = './uploads/profile/';
			if (!is_dir($upload_path)) {
				mkdir($upload_path, 0755, true);
			}

			$config['upload_path'] = $upload_path;
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['max_size'] = 2048; // 2MB
			$config['encrypt_name'] = TRUE;

			$this->upload->initialize($config);

			if ($this->upload->do_upload('profile_image')) {
				$file = $this->upload->data();
				$update['profile_image'] = $file['file_name'];

				// Optionally remove old file (safe check)
				$old = $this->input->post('old_profile_image');
				if ($old && file_exists($upload_path . $old)) {
					// unlink($upload_path.$old); // remove only if you want auto-delete
				}
			} else {
				// upload failed: store error in flashdata and continue (or stop)
				$this->session->set_flashdata('upload_error', $this->upload->display_errors('', ''));
				// You may redirect back; we'll continue without image
			}
		}

		$this->Profile_model->update_user($user_id, $update);
		$this->session->set_flashdata('success', 'Profile updated successfully.');
		redirect('profile');
	}

	public function change_password()
	{
		$user_id = (int) $this->session->userdata('user_id');

		$this->form_validation->set_rules('current_password', 'Current password', 'required');
		$this->form_validation->set_rules('new_password', 'New password', 'required|min_length[8]');
		$this->form_validation->set_rules('confirm_password', 'Confirm password', 'required|matches[new_password]');

		if ($this->form_validation->run() === FALSE) {
			$this->index();
			return;
		}

		$current = $this->input->post('current_password', TRUE);
		$new = $this->input->post('new_password', TRUE);

		$user = $this->Profile_model->get_user($user_id);
		if (!$user) {
			$this->session->set_flashdata('error', 'User not found.');
			redirect('profile');
		}

		// password stored hashed; verify
		if (!password_verify($current, $user->password)) {
			$this->session->set_flashdata('error', 'Current password is incorrect.');
			redirect('profile');
		}

		$this->Profile_model->update_user($user_id, [
			'password' => password_hash($new, PASSWORD_DEFAULT),
			'updated_at' => date('Y-m-d H:i:s')
		]);

		$this->session->set_flashdata('success', 'Password changed successfully.');
		redirect('profile');
	}
}
