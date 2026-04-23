<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_controllerr extends CI_Controller
{
	private $society_id;
	private $razorpay_key;
	private $razorpay_secret;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Payment_model', 'pm');
		$this->load->library('form_validation');
		$this->load->helper('url');
		$this->load->helper('security');

		$this->society_id = $this->session->userdata('society_id');

		$this->razorpay_key = 'rzp_test_SQegebn7NHi2HZ';      // Replace with actual
		$this->razorpay_secret = '6DHNi6FGHUTYpnrq9Zfzm78p'; // Replace with actual

		if (file_exists(APPPATH . 'vendor/autoload.php')) {
			require_once APPPATH . 'vendor/autoload.php';
		} elseif (file_exists(APPPATH . 'third_party/razorpay/Razorpay.php')) {
			require_once APPPATH . 'third_party/razorpay/Razorpay.php';
		} else {
			log_message('error', 'Razorpay SDK not found');
		}
	}

	/**
	 * Main payments page – lists all unified payments
	 */
	public function payments()
	{
		$data['title'] = 'Payment History';
		$data['stats'] = $this->pm->get_summary_stats($this->society_id);
		$data['chart'] = $this->pm->get_chart_data($this->society_id);
		$data['payments'] = $this->pm->get_all_unified_payments(
			$this->society_id ? ['society_id' => $this->society_id] : []
		);
		$data['users'] = $this->pm->get_users_list($this->society_id);

		$this->load->view('header', $data);
		$this->load->view('payment_view', $data);
	}

	/**
	 * Maintenance payment page
	 */
	public function maintenance_pay()
	{
		$society_id = (int) $this->session->userdata('society_id');
		$user_id = (int) ($this->session->userdata('member_id') ?: $this->session->userdata('user_id'));
		$flat_no = $this->session->userdata('flat_no') ?: '-';

		if ($society_id <= 0 || $user_id <= 0) {
			show_error('Unauthorized', 403);
		}

		$current_month = date('F');
		$current_year = (int) date('Y');

		// Check if already paid
		$alreadyPaid = $this->db->select('id')
			->from('payments')
			->where('society_id', $society_id)
			->where('user_id', $user_id)
			->where('payment_type', 'maintenance')
			->where('month', $current_month)
			->where('year', $current_year)
			->where('status', 'paid')
			->limit(1)
			->get()
			->row_array();

		if ($alreadyPaid) {
			$this->session->set_flashdata('success', 'Maintenance already paid for this month.');
			redirect('payment_controllerr/payments');  // redirect to payment list
		}

		// Get maintenance amount
		$amount = $this->pm->get_maintenance_amount($society_id, $flat_no);
		if ($amount <= 0) {
			$this->session->set_flashdata('error', 'Maintenance amount not set. Please contact admin.');
			redirect('payment_controllerr/payments');
		}

		$dueDate = $this->pm->get_maintenance_due_date($society_id);

		$data = [
			'title' => 'Maintenance',
			'activePage' => 'payment',
			'amount' => $amount,
			'month' => $current_month,
			'year' => $current_year,
			'flatNo' => $flat_no,
			'userName' => $this->session->userdata('user_name') ?: 'Guest',
			'maintenance_due_date' => $dueDate,
			'razorpay_key' => $this->razorpay_key,
		];

		$this->load->view('header', $data);
		$this->load->view('maintenance_pay_view', $data);
	}

	/**
	 * AJAX: Create Razorpay order
	 */
	public function create_razorpay_order()
	{
		$this->output->set_content_type('application/json');

		if (!class_exists('Razorpay\Api\Api')) {
			$this->_json(['error' => 'Razorpay SDK not loaded. Contact admin.']);
			return;
		}

		$amount = (float) $this->input->post('amount');
		$month = $this->input->post('month');
		$year = (int) $this->input->post('year');

		if (!$amount || !$month || !$year) {
			$this->_json(['error' => 'Invalid parameters']);
			return;
		}

		if (empty($this->razorpay_key) || $this->razorpay_key === 'YOUR_RAZORPAY_KEY_ID') {
			$this->_json(['error' => 'Razorpay key not configured.']);
			return;
		}

		try {
			$api = new Razorpay\Api\Api($this->razorpay_key, $this->razorpay_secret);
			$order = $api->order->create([
				'receipt' => 'maint_' . uniqid(),
				'amount' => (int) ($amount * 100),
				'currency' => 'INR',
				'payment_capture' => 1,
			]);

			$this->session->set_userdata([
				'razorpay_order_id' => $order['id'],
				'payment_amount' => $amount,
				'payment_month' => $month,
				'payment_year' => $year,
				'payment_society_id' => $this->society_id,
				'payment_user_id' => $this->session->userdata('user_id'),
			]);

			$this->_json([
				'order_id' => $order['id'],
				'amount' => $amount,
				'key' => $this->razorpay_key
			]);
		} catch (Exception $e) {
			log_message('error', 'Razorpay order creation failed: ' . $e->getMessage());
			$this->_json(['error' => 'Unable to create order. Please try again later.']);
		}
	}

	/**
	 * AJAX: Payment success callback
	 */
	public function payment_success()
	{
		$this->output->set_content_type('application/json');

		$input = json_decode(file_get_contents('php://input'), true);
		$razorpay_payment_id = $input['razorpay_payment_id'] ?? null;
		$razorpay_order_id = $input['razorpay_order_id'] ?? null;
		$razorpay_signature = $input['razorpay_signature'] ?? null;

		if (!$razorpay_payment_id || !$razorpay_order_id || !$razorpay_signature) {
			$this->_json(['success' => false, 'message' => 'Missing payment details']);
			return;
		}

		$expected_order_id = $this->session->userdata('razorpay_order_id');
		$society_id = $this->session->userdata('payment_society_id');
		$user_id = $this->session->userdata('payment_user_id');
		$amount = $this->session->userdata('payment_amount');
		$month = $this->session->userdata('payment_month');
		$year = $this->session->userdata('payment_year');

		if ($razorpay_order_id !== $expected_order_id) {
			$this->_json(['success' => false, 'message' => 'Order ID mismatch']);
			return;
		}

		if (!class_exists('Razorpay\Api\Api')) {
			$this->_json(['success' => false, 'message' => 'Razorpay SDK missing']);
			return;
		}

		try {
			$api = new Razorpay\Api\Api($this->razorpay_key, $this->razorpay_secret);
			$attributes = [
				'razorpay_order_id' => $razorpay_order_id,
				'razorpay_payment_id' => $razorpay_payment_id,
				'razorpay_signature' => $razorpay_signature,
			];
			$api->utility->verifyPaymentSignature($attributes);

			$paymentData = [
				'society_id' => $society_id,
				'user_id' => $user_id,
				'amount' => $amount,
				'payment_type' => 'maintenance',
				'month' => $month,
				'year' => $year,
				'status' => 'paid',
				'payment_date' => date('Y-m-d H:i:s'),
				'payment_id' => $razorpay_payment_id,
				'order_id' => $razorpay_order_id,
				'created_by' => null,
			];

			$inserted = $this->pm->insert_payment($paymentData);

			$this->session->unset_userdata([
				'razorpay_order_id',
				'payment_amount',
				'payment_month',
				'payment_year',
				'payment_society_id',
				'payment_user_id'
			]);

			if ($inserted) {
				// Return redirect URL to the payment list page
				$this->_json([
					'success' => true,
					'message' => 'Payment recorded successfully.',
					'redirect_url' => site_url('payment_controllerr/payments')
				]);
			} else {
				$this->_json(['success' => false, 'message' => 'Payment verified but failed to save record.']);
			}
		} catch (Exception $e) {
			log_message('error', 'Razorpay verification failed: ' . $e->getMessage());
			$this->_json(['success' => false, 'message' => 'Payment verification failed: ' . $e->getMessage()]);
		}
	}

	private function _json($data)
	{
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}
	
}
