<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Society_setup extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Society_setup_model', 'setup_model');
		$this->load->helper(['url', 'form', 'html']);
		$this->load->library(['session', 'form_validation']);
		$this->_guard();
	}

	private function _guard(): void
	{
		if (!$this->session->userdata('user_id'))
			redirect('login');
		$role = strtolower((string) ($this->session->userdata('role_name') ?? ''));
		if (!in_array($role, ['super_admin', 'superadmin', 'super admin', 'chairman'])) {
			$this->session->set_flashdata('error', 'Access denied.');
			redirect('dashboard');
		}
	}

	private function _is_super(): bool
	{
		return in_array(
			strtolower((string) $this->session->userdata('role_name')),
			['super_admin', 'superadmin', 'super admin']
		);
	}

	private function _society_id(): int
	{
		return (int) $this->session->userdata('society_id');
	}

	/* ── Index ── */
	public function index(): void
	{
		$isSuper = $this->_is_super();
		$societyId = $isSuper
			? ((int) $this->input->get('society_id') ?: $this->_society_id())
			: $this->_society_id();

		$wings = $this->setup_model->get_wings($societyId);
		$stats = $this->setup_model->get_stats($societyId);
		$setupDone = $this->setup_model->is_setup_done($societyId);
		$society = $this->setup_model->get_society($societyId);

		// Per-wing flat counts
		$wingStats = [];
		foreach ($wings as $w) {
			$wingStats[$w->id] = $this->setup_model->get_wing_stats($w->id, $societyId);
		}

		$data = [
			'isSuper' => $isSuper,
			'societies' => $isSuper ? $this->setup_model->get_all_societies() : [],
			'selectedSoc' => $societyId,
			'society' => $society,
			'wings' => $wings,
			'wingStats' => $wingStats,
			'stats' => $stats,
			'setupDone' => $setupDone,
		];
		$data['title'] = 'setup';
		$this->load->view('header', $data);
		$this->load->view('society_setup', $data);
	}

	/* ── Save wing configuration ─────────────── */
	public function save_wings(): void
	{
		if ($this->input->method() !== 'post')
			redirect('society_setup');

		$societyId = (int) $this->input->post('society_id') ?: $this->_society_id();
		if (!$this->_is_super())
			$societyId = $this->_society_id();

		$wings = $this->input->post('wings');
		if (empty($wings) || !is_array($wings)) {
			$this->session->set_flashdata('error', 'Please add at least one wing.');
			redirect($this->_redirect($societyId));
		}

		// Validate
		$errors = [];
		foreach ($wings as $i => $w) {
			$n = $i + 1;
			if (empty(trim($w['wing_name'] ?? '')))
				$errors[] = "Wing $n: Name required.";
			if ((int) ($w['floors'] ?? 0) < 1)
				$errors[] = "Wing $n: Floors must be ≥ 1.";
			if ((int) ($w['units_per_floor'] ?? 0) < 1)
				$errors[] = "Wing $n: Units/floor must be ≥ 1.";
		}
		if ($errors) {
			$this->session->set_flashdata('error', implode('<br>', $errors));
			redirect($this->_redirect($societyId));
		}

		$ok = $this->setup_model->upsert_wings($societyId, $wings);
		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? 'Wing structure saved! Preview below then click Generate.' : 'Failed to save.'
		);
		redirect($this->_redirect($societyId));
	}

	/* ── Preview (AJAX JSON) ─────────────────── */
	public function preview(): void
	{
		$this->output->set_header('Content-Type: application/json');
		$societyId = (int) $this->input->post('society_id') ?: $this->_society_id();
		if (!$this->_is_super())
			$societyId = $this->_society_id();

		$data = $this->setup_model->preview_flats($societyId);
		echo json_encode(['success' => true] + $data);
	}

	/* ── Generate all flats ──────────────────── */
	public function generate(): void
	{
		if ($this->input->method() !== 'post')
			redirect('society_setup');

		$societyId = (int) $this->input->post('society_id') ?: $this->_society_id();
		if (!$this->_is_super())
			$societyId = $this->_society_id();

		$result = $this->setup_model->generate_flats($societyId);
		$this->setup_model->mark_setup_done($societyId);

		$this->session->set_flashdata(
			'success',
			"✅ Done! <strong>{$result['inserted']} flat(s) created</strong>, {$result['skipped']} already existed (skipped)."
		);
		redirect($this->_redirect($societyId));
	}

	/* ── Delete wing (only if no occupied flats) */
	public function delete_wing(int $wingId = 0): void
	{
		$societyId = $this->_society_id();
		$wing = $this->setup_model->get_wing($wingId);

		if (!$wing || (!$this->_is_super() && (int) $wing->society_id !== $societyId)) {
			$this->session->set_flashdata('error', 'Unauthorized.');
			redirect('society_setup');
		}

		$ok = $this->setup_model->delete_wing($wingId);
		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? 'Wing and its vacant flats removed.' : 'Cannot delete — wing has occupied flats.'
		);
		redirect($this->_redirect((int) $wing->society_id));
	}

	/* ── Available vacant flats (AJAX for member add) */
	public function vacant_flats(): void
	{
		$this->output->set_header('Content-Type: application/json');
		$societyId = $this->_society_id();
		$wingId = (int) $this->input->get('wing_id');
		$floor = $this->input->get('floor');
		$floorVal = ($floor !== null && $floor !== '') ? (int) $floor : null;

		$flats = $this->setup_model->get_vacant_flats($societyId, $wingId, $floorVal);
		echo json_encode(['success' => true, 'flats' => $flats]);
	}

	private function _redirect(int $societyId): string
	{
		return 'society_setup' . ($this->_is_super() ? '?society_id=' . $societyId : '');
	}
}
