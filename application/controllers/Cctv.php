<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cctv extends CI_Controller
{
	private $user_role = null;
	private $user_society_id = null;
	private $user_id = null;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(['session', 'form_validation']);
		$this->load->helper(['url', 'form']);
		$this->load->model('Cctv_model');

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}

		$this->user_id = (int) $this->session->userdata('user_id');

		// Get role + society from DB tables: user, user_role, role
		$access = $this->Cctv_model->get_user_access($this->user_id);

		if (!$access || empty($access->role_name)) {
			$this->user_role = null;
			$this->user_society_id = null;
		} else {
			$this->user_role = strtolower(trim($access->role_name));
			$this->user_society_id = $access->society_id ? (int) $access->society_id : null;
		}
	}

	public function index()
	{
		// Chairman and security can view
		$can_view = in_array($this->user_role, ['chairman', 'security', 'super_admin'], true);
		if (!$can_view) {
			$data['activePage'] = 'cctv';
			$data['activeSubPage'] = null;
			$data['user_role'] = $this->user_role;
			$data['can_add'] = false;
			$data['cameras'] = [];
			$data['hls_base_url'] = base_url('assets/hls');

			$this->load->view('header', $data);
			$this->load->view('cctv_view', $data);
			return;
		}

		if ($this->user_role === 'super_admin') {
			$data['cameras'] = $this->Cctv_model->get_all_cameras(); // ALL cameras
		} else {
			$data['cameras'] = [];
			if ($this->user_society_id) {
				$data['cameras'] = $this->Cctv_model->get_all_cameras($this->user_society_id);
			}
		}
		
		$data['hls_base_url'] = base_url('assets/hls');
		$data['activePage'] = 'cctv';
		$data['activeSubPage'] = null;
		$data['can_add'] = in_array($this->user_role, ['chairman', 'super_admin']);
		$data['user_role'] = $this->user_role;
		$data['user_society_id'] = $this->user_society_id;
		$data['can_view'] = $can_view;

		$data['title'] = 'CCTV';
		$this->load->view('header', $data);
		$this->load->view('cctv_view', $data);
	}

	public function store()
	{
		// Only chairman can add
		if (!in_array($this->user_role, ['chairman', 'super_admin'])) {
			$this->session->set_flashdata('error', 'You are not authorized to add cameras.');
			redirect('cctv');
		}

		if (empty($this->user_society_id)) {
			$this->session->set_flashdata('error', 'Your account is not linked to any society.');
			redirect('cctv');
		}

		$this->form_validation->set_rules('name', 'Camera Name', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('brand', 'Brand', 'required|trim');
		$this->form_validation->set_rules('ip_address', 'IP Address', 'required|trim');
		$this->form_validation->set_rules('port', 'Port', 'required|trim|integer');
		$this->form_validation->set_rules('username', 'Username', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');
		$this->form_validation->set_rules('channel', 'Channel', 'required|trim|integer');
		$this->form_validation->set_rules('stream_type', 'Stream Type', 'required|trim');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('cctv');
		}

		$name = $this->input->post('name', TRUE);
		$cam_key = url_title($name, '-', TRUE) . '-' . uniqid();

		$data = [
			'name' => $name,
			'cam_key' => $cam_key,
			'brand' => $this->input->post('brand', TRUE),
			'ip_address' => $this->input->post('ip_address', TRUE),
			'port' => (int) $this->input->post('port', TRUE),
			'username' => $this->input->post('username', TRUE),
			'password' => $this->input->post('password', TRUE),
			'channel' => (int) $this->input->post('channel', TRUE),
			'stream_type' => $this->input->post('stream_type', TRUE),
			'society_id' => ($this->user_role === 'super_admin')
				? (int) $this->input->post('society_id', TRUE)
				: $this->user_society_id,
			'created_by' => $this->user_id,
			'is_active' => 1,
			'sort_order' => 0,
			'last_status' => 'pending',
			'last_error' => null,
		];
		if ($this->user_role === 'super_admin' && empty($this->input->post('society_id'))) {
			$this->session->set_flashdata('error', 'Please select a society.');
			redirect('cctv');
		}
		$this->Cctv_model->insert_camera($data);

		$this->session->set_flashdata('success', 'Camera added successfully.');
		redirect('cctv');
	}

	public function delete($id)
	{
		$camera = $this->Cctv_model->get_camera_by_id($id);

		if (!$camera) {
			return $this->_json_response('error', 'Camera not found.');
		}

		// Only chairman can delete, and only from own society
		if (!in_array($this->user_role, ['chairman', 'super_admin'])) {
			return $this->_json_response('error', 'You are not authorized to delete cameras.');
		}

		if (
			$this->user_role !== 'super_admin' &&
			(int) $camera->society_id !== (int) $this->user_society_id
		) {
			return $this->_json_response('error', 'You are not authorized to delete this camera.');
		}

		$deleted = $this->Cctv_model->delete_camera(
			$id,
			($this->user_role !== 'super_admin') ? $this->user_society_id : null
		);
		if ($deleted) {
			return $this->_json_response('success', 'Camera deleted.');
		}

		return $this->_json_response('error', 'Deletion failed.');
	}

	private function _json_response($status, $message)
	{
		if ($this->input->is_ajax_request()) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status' => $status,
					'message' => $message
				]));
			return;
		}

		$this->session->set_flashdata($status === 'success' ? 'success' : 'error', $message);
		redirect('cctv');
	}
}
