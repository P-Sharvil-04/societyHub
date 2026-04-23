<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Plan_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(['plan_model', 'Society_setup_model' => 'setup_model']);
		$this->load->helper(['url', 'form', 'html']);
		$this->load->library(['form_validation', 'session']);
		// 🔒 Check login
		if (!$this->session->userdata('user_id')) {
			show_error('Unauthorized access', 403);
		}

		// 🔒 Check Super Admin role
		$role = strtolower((string) $this->session->userdata('role_name'));

		if (!in_array($role, ['super_admin', 'superadmin', 'super admin'])) {
			show_error('Access denied. Super Admin only.', 403);
		}
	}

	/* ── Plan page ───────────────────────────────────────────── */
	public function plan()
	{
		$this->load->view('plan_view');
	}

	/* ══════════════════════════════════════════════════════════
	   STEP 1 — Register Society + Wings + Auto-generate Flats
	══════════════════════════════════════════════════════════ */
	public function register_society()
	{
		/* GET → show form */
		if ($this->input->method() !== 'post') {
			$this->load->view('register-society_view', ['errors' => '']);
			return;
		}

		/* ── Validation ── */
		$this->form_validation->set_rules('societyName', 'Society Name', 'required|trim|max_length[150]');
		$this->form_validation->set_rules('societyType', 'Society Type', 'required');
		$this->form_validation->set_rules('address', 'Address', 'required|trim');
		$this->form_validation->set_rules('city', 'City', 'required|trim');
		$this->form_validation->set_rules('state', 'State', 'required|trim');
		$this->form_validation->set_rules('pincode', 'Pincode', 'required|exact_length[6]|numeric');
		$this->form_validation->set_rules('lat', 'Location', 'required');
		$this->form_validation->set_rules('lng', 'Location', 'required');
		$this->form_validation->set_rules('totalFlats', 'Total Flats', 'required|integer|greater_than[0]');

		$type = $this->input->post('societyType', TRUE);

		if ($type === 'bungalow') {
			$this->form_validation->set_rules('number_of_bungalows', 'Number of Bungalows', 'required|integer|greater_than[0]');
		} else {
			$this->form_validation->set_rules('number_of_wings', 'Number of Wings', 'required|integer|greater_than[0]');
		}

		if ($this->form_validation->run() === FALSE) {
			$this->load->view('register-society_view', ['errors' => validation_errors()]);
			return;
		}

		/* ── Decode wing structure JSON ── */
		$wingStructure = [];
		$raw = $this->input->post('wing_structure', TRUE);
		if (!empty($raw)) {
			$decoded = json_decode($raw, true);
			if (is_array($decoded))
				$wingStructure = $decoded;
		}

		/* ── Insert society ── */
		$this->db->insert('societies', [
			'name' => $this->input->post('societyName', TRUE),
			'address' => $this->input->post('address', TRUE),
			'city' => $this->input->post('city', TRUE),
			'state' => $this->input->post('state', TRUE),
			'pincode' => $this->input->post('pincode', TRUE),
			'society_type' => $type,
			'total_units' => (int) $this->input->post('totalFlats', TRUE),
			'status' => 'pending',
			'setup_done' => 0,
			'created_at' => date('Y-m-d H:i:s'),
		]);

		$societyId = (int) $this->db->insert_id();

		if (!$societyId) {
			$this->load->view('register-society_view', ['errors' => 'Failed to register society. Please try again.']);
			return;
		}

		/* ── Generate Wings + Flats ── */
		$now = date('Y-m-d H:i:s');
		$flatsInserted = 0;

		if ($type === 'bungalow') {
			$num = (int) $this->input->post('number_of_bungalows', TRUE);
			$this->db->insert('wings', [
				'society_id' => $societyId,
				'wing_name' => 'Bungalow',
				'floors' => 1,
				'units_per_floor' => $num,
				'flat_type' => 'Bungalow',
				'wing_prefix' => 'B',
				'has_ground_floor' => 0,
				'naming_format' => 'B-{U}',
				'created_at' => $now,
			]);
			$wingId = (int) $this->db->insert_id();

			for ($u = 1; $u <= $num; $u++) {
				$this->db->insert('flats', [
					'society_id' => $societyId,
					'wing_id' => $wingId,
					'flat_no' => 'B-' . str_pad($u, 2, '0', STR_PAD_LEFT),
					'floor' => 1,
					'flat_type' => 'Bungalow',
					'status' => 1,
					'created_at' => $now,
				]);
				$flatsInserted++;
			}

		} else {
			foreach ($wingStructure as $w) {
				$wName = trim($w['wing_name'] ?? 'Wing');
				$wPrefix = strtoupper(trim($w['wing_prefix'] ?? substr($wName, 0, 1)));
				$floors = max(1, (int) ($w['floors'] ?? 4));
				$upf = max(1, (int) ($w['units_per_floor'] ?? 4));
				$fType = $w['flat_type'] ?? '2BHK';
				$gf = (int) ($w['has_ground_floor'] ?? 0);
				$fmt = trim($w['naming_format'] ?? '{W}-{F}{U}') ?: '{W}-{F}{U}';

				$this->db->insert('wings', [
					'society_id' => $societyId,
					'wing_name' => $wName,
					'floors' => $floors,
					'units_per_floor' => $upf,
					'flat_type' => $fType,
					'wing_prefix' => $wPrefix,
					'has_ground_floor' => $gf,
					'naming_format' => $fmt,
					'created_at' => $now,
				]);
				$wingId = (int) $this->db->insert_id();

				$startFloor = $gf ? 0 : 1;
				$endFloor = $startFloor + $floors - 1;

				for ($fl = $startFloor; $fl <= $endFloor; $fl++) {
					for ($u = 1; $u <= $upf; $u++) {
						$this->db->insert('flats', [
							'society_id' => $societyId,
							'wing_id' => $wingId,
							'flat_no' => $this->setup_model->make_flat_no($wPrefix, $fl, $u, $fmt),
							'floor' => $fl,
							'flat_type' => $fType,
							'status' => 1,
							'created_at' => $now,
						]);
						$flatsInserted++;
					}
				}
			}
		}

		/* ── Finalise ── */
		$this->db->where('id', $societyId)->update('societies', ['setup_done' => 1]);
		$this->session->set_userdata('reg_society_id', $societyId);
		$this->session->set_flashdata(
			'success',
			"Society registered! {$flatsInserted} flat(s) auto-generated. Now create the admin account."
		);

		redirect('plan_controller/admin_register');
	}

	/* ══════════════════════════════════════════════════════════
	   STEP 2 — Register Admin (Chairman)
	══════════════════════════════════════════════════════════ */
	public function admin_register()
	{
		/* Ensure Step 1 was completed */
		$societyId = (int) $this->session->userdata('reg_society_id');
		if (!$societyId) {
			$this->session->set_flashdata('error', 'Please register a society first.');
			redirect('plan_controller/register_society');
		}

		$wings = $this->plan_model->get_wings_by_society($societyId);
		$society = $this->db->get_where('societies', ['id' => $societyId])->row();

		/* GET → show form */
		if ($this->input->method() !== 'post') {
			$this->load->view('admin_register_view', [
				'wings' => $wings,
				'society' => $society,
				'vacantFlats' => [],   // loaded via AJAX on wing change
			]);
			return;
		}

		/* ── POST Validation ── */
		$this->form_validation->set_rules('adminName', 'Full Name', 'required|trim');
		$this->form_validation->set_rules('adminEmail', 'Email', 'required|valid_email|is_unique[users.email]');
		$this->form_validation->set_rules('adminPhone', 'Phone', 'required|numeric|exact_length[10]');
		$this->form_validation->set_rules('adminPassword', 'Password', 'required|min_length[6]');
		$this->form_validation->set_rules('confirmPassword', 'Confirm Password', 'required|matches[adminPassword]');
		$this->form_validation->set_rules('wing_id', 'Wing', 'required|integer');
		$this->form_validation->set_rules('flat_id', 'Flat', 'required|integer');

		if ($this->form_validation->run() === FALSE) {
			$this->load->view('admin_register_view', [
				'wings' => $wings,
				'society' => $society,
				'vacantFlats' => [],
			]);
			return;
		}

		$wingId = (int) $this->input->post('wing_id', TRUE);
		$flatId = (int) $this->input->post('flat_id', TRUE);

		/* Server-side: verify wing belongs to this society */
		$wing = $this->plan_model->get_wing($wingId);
		if (!$wing || (int) $wing->society_id !== $societyId) {
			$this->session->set_flashdata('error', 'Invalid wing selection.');
			redirect('plan_controller/admin_register');
		}

		/* Server-side: verify flat is vacant and belongs to this society */
		$flat = $this->setup_model->get_flat_by_id($flatId);
		if (!$flat || (int) $flat->society_id !== $societyId || (int) $flat->status !== 1) {
			$this->session->set_flashdata('error', 'Selected flat is no longer available. Please choose another.');
			redirect('plan_controller/admin_register');
		}

		/* ── Insert user + assign roles (transaction) ── */
		$this->db->trans_start();

		$userId = $this->plan_model->create_admin([
			'society_id' => $societyId,
			'name' => $this->input->post('adminName', TRUE),
			'email' => $this->input->post('adminEmail', TRUE),
			'phone' => $this->input->post('adminPhone', TRUE),
			'password' => password_hash($this->input->post('adminPassword'), PASSWORD_DEFAULT),
			'wing_id' => $wingId,
			'flat_no' => $flat->flat_no,
			'member_type' => 'owner',
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);

		if ($userId) {
			$this->plan_model->assign_role($userId, 2); // chairman
			$this->plan_model->assign_role($userId, 6); // owner
			/* Mark flat as occupied */
			$this->setup_model->occupy_flat($flatId);
		}

		$this->db->trans_complete();

		if (!$this->db->trans_status() || !$userId) {
			$this->session->set_flashdata('error', 'Registration failed. Please try again.');
			redirect('plan_controller/admin_register');
		}

		$this->session->unset_userdata('reg_society_id');
		$this->session->set_flashdata('success', 'Registration complete! .');
		redirect('society_setup');
	}

	/* ── AJAX: vacant flats for a wing (called from admin_register form) ── */
	public function get_wing_flats()
	{
		$this->output->set_header('Content-Type: application/json');

		$wingId = (int) $this->input->get('wing_id');
		$societyId = (int) $this->session->userdata('reg_society_id');

		if (!$wingId || !$societyId) {
			echo json_encode(['flats' => []]);
			return;
		}

		$flats = $this->setup_model->get_vacant_flats($societyId, $wingId);
		echo json_encode(['flats' => $flats]);
	}
}
