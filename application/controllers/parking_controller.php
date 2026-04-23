<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parking_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Parking_model');
		$this->load->library(['session', 'form_validation']);
		$this->load->helper(['url', 'form']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	private function is_chairman()
	{
		return $this->session->userdata('role_name') === 'chairman';
	}

	private function is_super_admin()
	{
		return $this->session->userdata('role_name') === 'super_admin';
	}

	private function society_id()
	{
		return (int) $this->session->userdata('society_id');
	}

	private function user_id()
	{
		return (int) $this->session->userdata('user_id');
	}

	private function render($section, $data = [])
	{
		$data['section'] = $section;
		$data['sess'] = [
			'user_id' => $this->user_id(),
			'society_id' => $this->society_id(),
			'user_name' => $this->session->userdata('user_name'),
			'role_name' => $this->session->userdata('role_name'),
			'member_type' => $this->session->userdata('member_type'),
		];

		$data['title'] = 'Parking';
		$this->load->view('parking_view', $data);
	}

	public function index()
	{
		if ($this->is_super_admin()) {
			redirect('parking/super_admin_dashboard');
		} elseif ($this->is_chairman()) {
			redirect('parking/dashboard');
		} else {
			redirect('parking/my_parking');
		}
	}

	public function super_admin_dashboard()
	{
		if (!$this->is_super_admin()) {
			redirect('login');
		}

		$societies = $this->Parking_model->get_all_societies();
		if (empty($societies)) {
			show_error('No societies found');
		}

		// When form is submitted, save selected society in session and redirect
		if ($this->input->post('society_id')) {
			$posted_society_id = (int) $this->input->post('society_id', TRUE);
			$this->session->set_userdata('parking_selected_society_id', $posted_society_id);
			redirect('parking/super_admin_dashboard');
		}

		// Read selected society from session
		$selected_society_id = (int) $this->session->userdata('parking_selected_society_id');

		// Fallback to first society if nothing is selected or invalid
		$selected_society = null;
		foreach ($societies as $s) {
			if ((int) $s->id === $selected_society_id) {
				$selected_society = $s;
				break;
			}
		}

		if (!$selected_society) {
			$selected_society = $societies[0];
			$selected_society_id = (int) $selected_society->id;
			$this->session->set_userdata('parking_selected_society_id', $selected_society_id);
		}

		$selected_society_name = $selected_society->name;

		$parking_list = $this->Parking_model->get_all_parking($selected_society_id);
		$stats = $this->Parking_model->get_stats($selected_society_id);
		$members = $this->Parking_model->get_members($selected_society_id);

		$this->render('super_admin', [
			'societies' => $societies,
			'selected_society_id' => $selected_society_id,
			'selected_society_name' => $selected_society_name,
			'parking_list' => $parking_list,
			'stats' => $stats,
			'members' => $members,
			'flash' => [],
		]);
	}

	public function dashboard()
	{
		if (!$this->is_chairman()) {
			redirect('parking/my_parking');
		}

		$society_id = $this->society_id();

		$this->render('chairman', [
			'parking_list' => $this->Parking_model->get_all_parking($society_id),
			'members' => $this->Parking_model->get_members($society_id),
			'stats' => $this->Parking_model->get_stats($society_id),
			'flash' => [
				'success' => $this->session->flashdata('success'),
				'error' => $this->session->flashdata('error'),
			],
		]);
	}

	public function assign()
	{
		if (!$this->is_chairman()) {
			redirect('parking/my_parking');
		}

		$this->form_validation->set_rules('owner_id', 'Owner/Member', 'required|integer');
		$this->form_validation->set_rules('slot_number', 'Slot Number', 'required|trim|max_length[10]');
		$this->form_validation->set_rules('vehicle_type', 'Vehicle Type', 'required');
		$this->form_validation->set_rules('vehicle_number', 'Vehicle Number', 'trim|max_length[20]');

		if ($this->form_validation->run() === FALSE) {
			$this->session->set_flashdata('error', validation_errors(' ', ' '));
			redirect('parking/dashboard');
		}

		$society_id = $this->society_id();
		$slot_number = strtoupper(trim($this->input->post('slot_number', TRUE)));

		if ($this->Parking_model->slot_exists($slot_number, $society_id)) {
			$this->session->set_flashdata('error', "Slot <strong>{$slot_number}</strong> is already assigned in your society.");
			redirect('parking/dashboard');
		}

		$ok = $this->Parking_model->assign([
			'society_id' => $society_id,
			'owner_id' => (int) $this->input->post('owner_id'),
			'slot_number' => $slot_number,
			'vehicle_type' => $this->input->post('vehicle_type', TRUE),
			'vehicle_number' => strtoupper(trim($this->input->post('vehicle_number', TRUE))),
			'allocated_by' => $this->user_id(),
		]);

		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? "Parking slot <strong>{$slot_number}</strong> assigned successfully."
			: 'Something went wrong. Please try again.'
		);

		redirect('parking/dashboard');
	}

	public function revoke($id = 0)
	{
		if (!$this->is_chairman()) {
			redirect('parking/my_parking');
		}

		$ok = $this->Parking_model->revoke((int) $id, $this->society_id());

		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? 'Parking slot revoked successfully.' : 'Record not found.'
		);

		redirect('parking/dashboard');
	}

	public function my_parking()
	{
		if ($this->is_chairman()) {
			redirect('parking/dashboard');
		}

		$society_id = $this->society_id();
		$user_id = $this->user_id();

		$this->render('owner', [
			'my_parking' => $this->Parking_model->get_owner_parking($user_id, $society_id),
			'all_parking' => $this->Parking_model->get_all_parking($society_id),
			'stats' => $this->Parking_model->get_stats($society_id),
			'flash' => [
				'success' => $this->session->flashdata('success'),
				'error' => $this->session->flashdata('error'),
			],
		]);
	}
}
