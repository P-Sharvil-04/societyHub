<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_controllerr extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Payment_model', 'payment_model');
		// Auth guard — adjust to your session key
		// if (!$this->session->userdata('admin_id')) redirect('auth/login');
	}

	// ─── MAIN VIEW ──────────────────────────────────────────────────────────

	public function payments()
	{
		$data['title'] = 'Payment Management';
		$data['stats'] = $this->payment_model->get_summary_stats();
		$data['chart'] = $this->payment_model->get_chart_data();
		$data['payments'] = $this->payment_model->get_all_unified_payments();
		$data['residents'] = $this->payment_model->get_residents_list();

		$this->load->view('payment_view', $data);
	}

	// ─── AJAX: Filtered / searched payments ─────────────────────────────────

	public function get_payments_ajax()
	{
		$filters = [
			'status' => $this->input->post('status'),
			'month' => $this->input->post('month'),
			'resident_id' => $this->input->post('resident_id'),
		];

		$payments = $this->payment_model->get_all_unified_payments(
			array_filter($filters)   // remove empty keys
		);

		// Client-side search is fine for small datasets;
		// pass raw array and let JS handle text search.
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['success' => true, 'data' => $payments]));
	}

	// ─── AJAX: Resident transaction history ─────────────────────────────────

	public function get_resident_history($resident_id = null)
	{
		if (!$resident_id) {
			return $this->_json(['success' => false, 'message' => 'Resident ID required']);
		}

		$history = $this->payment_model->get_resident_transaction_history($resident_id);
		$this->_json(['success' => true, 'data' => $history]);
	}

	// ─── AJAX: Add payment (manual / maintenance) ───────────────────────────

	public function add_payment()
	{
		$rules = [
			['field' => 'resident_id', 'label' => 'Resident', 'rules' => 'required|integer'],
			['field' => 'payment_type', 'label' => 'Type', 'rules' => 'required'],
			['field' => 'amount', 'label' => 'Amount', 'rules' => 'required|numeric'],
			['field' => 'payment_date', 'label' => 'Payment Date', 'rules' => 'required'],
			['field' => 'due_date', 'label' => 'Due Date', 'rules' => 'required'],
			['field' => 'status', 'label' => 'Status', 'rules' => 'required'],
		];

		$this->form_validation->set_rules($rules);

		if (!$this->form_validation->run()) {
			return $this->_json(['success' => false, 'message' => validation_errors()]);
		}

		$data = [
			'resident_id' => $this->input->post('resident_id', TRUE),
			'resident_name' => $this->input->post('resident_name', TRUE),
			'flat_number' => $this->input->post('flat_number', TRUE),
			'payment_type' => $this->input->post('payment_type', TRUE),
			'amount' => $this->input->post('amount', TRUE),
			'payment_date' => $this->input->post('payment_date', TRUE),
			'due_date' => $this->input->post('due_date', TRUE),
			'payment_method' => $this->input->post('payment_method', TRUE),
			'transaction_id' => $this->input->post('transaction_id', TRUE),
			'description' => $this->input->post('description', TRUE),
			'status' => $this->input->post('status', TRUE),
		];

		$id = $this->payment_model->insert_payment($data);

		$this->_json([
			'success' => (bool) $id,
			'message' => $id ? 'Payment recorded successfully.' : 'Failed to save payment.',
			'id' => $id,
		]);
	}

	// ─── AJAX: Edit payment ──────────────────────────────────────────────────

	public function edit_payment($id = null)
	{
		if (!$id)
			return $this->_json(['success' => false, 'message' => 'ID required']);

		$data = [
			'payment_type' => $this->input->post('payment_type', TRUE),
			'amount' => $this->input->post('amount', TRUE),
			'payment_date' => $this->input->post('payment_date', TRUE),
			'due_date' => $this->input->post('due_date', TRUE),
			'payment_method' => $this->input->post('payment_method', TRUE),
			'transaction_id' => $this->input->post('transaction_id', TRUE),
			'description' => $this->input->post('description', TRUE),
			'status' => $this->input->post('status', TRUE),
		];

		$ok = $this->payment_model->update_payment($id, $data);
		$this->_json(['success' => (bool) $ok, 'message' => $ok ? 'Payment updated.' : 'Update failed.']);
	}

	// ─── AJAX: Delete payment ───────────────────────────────────────────────

	public function delete_payment($id = null)
	{
		if (!$id)
			return $this->_json(['success' => false, 'message' => 'ID required']);

		$ok = $this->payment_model->delete_payment($id);
		$this->_json(['success' => (bool) $ok, 'message' => $ok ? 'Payment deleted.' : 'Delete failed.']);
	}

	// ─── AJAX: Single payment detail ────────────────────────────────────────

	public function get_payment($id = null)
	{
		if (!$id)
			return $this->_json(['success' => false, 'message' => 'ID required']);

		$payment = $this->payment_model->get_payment_by_id($id);
		$this->_json(
			$payment
			? ['success' => true, 'data' => $payment]
			: ['success' => false, 'message' => 'Not found']
		);
	}

	// ─── AJAX: Export CSV ───────────────────────────────────────────────────

	public function export_payments()
	{
		$payments = $this->payment_model->get_all_unified_payments();

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="payments_' . date('Y-m-d') . '.csv"');

		$out = fopen('php://output', 'w');
		fputcsv($out, [
			'Invoice ID',
			'Source',
			'Resident',
			'Flat',
			'Type',
			'Amount',
			'Payment Date',
			'Due Date',
			'Method',
			'Transaction ID',
			'Status',
		]);

		foreach ($payments as $p) {
			fputcsv($out, [
				$p['invoice_id'],
				ucfirst($p['source_type']),
				$p['resident_name'],
				$p['flat'],
				$p['payment_type'],
				$p['amount'],
				$p['payment_date'],
				$p['due_date'],
				$p['payment_method'],
				$p['transaction_id'],
				$p['status'],
			]);
		}

		fclose($out);
	}

	// ─── Helper ─────────────────────────────────────────────────────────────

	private function _json($data)
	{
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}
}
