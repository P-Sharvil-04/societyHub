<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_controller extends CI_Controller
{
	private $key_id;
	private $key_secret;
	private $mode;

	public function __construct()
	{
		parent::__construct();
		$this->load->config('razorpay');
		$this->key_id = $this->config->item('razorpay_key_id');
		$this->key_secret = $this->config->item('razorpay_key_secret');
		$this->mode = $this->config->item('razorpay_mode');

		$this->load->model('Payment_model');
		$this->load->helper(['url', 'form', 'security']);
		$this->load->library('session');
	}

	private function curl_request($url, $method = 'GET', $data = null, $headers = [])
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->key_id . ":" . $this->key_secret);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		if (in_array($method, ['POST', 'PUT']) && !is_null($data)) {
			$is_json = false;
			foreach ($headers as $h) {
				if (stripos($h, 'application/json') !== false) {
					$is_json = true;
					break;
				}
			}
			if ($is_json) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
			}
		}

		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$response = curl_exec($ch);
		$curlErr = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$decoded = json_decode($response, true);

		return [
			'success' => ($curlErr === '' && $httpCode >= 200 && $httpCode < 300),
			'httpCode' => $httpCode,
			'raw' => $response,
			'json' => $decoded,
			'error' => $curlErr
		];
	}

	// Dashboard: show balance (live mode only) and orders
	public function chairman_balance()
	{
		$balance_in_inr = 0.00;
		$balance_msg = '';

		if ($this->mode === 'live') {
			$res = $this->curl_request("https://api.razorpay.com/v1/balance", 'GET');
			if ($res['success'] && isset($res['json']['items']) && is_array($res['json']['items'])) {
				foreach ($res['json']['items'] as $item) {
					if (isset($item['currency']) && strtoupper($item['currency']) === 'INR') {
						$balance_in_inr = floatval(($item['balance'] ?? 0) / 100.0);
						break;
					}
				}
			} else {
				// show raw message for debugging if needed
				$balance_msg = 'Failed to fetch balance: HTTP ' . $res['httpCode'];
				if (!empty($res['raw'])) {
					$balance_msg .= ' — ' . htmlspecialchars($res['raw']);
				}
			}
		} else {
			// Test mode: calculate a simulated balance from paid orders
			$sim_sum = $this->Payment_model->get_total_paid_amount();
			$balance_msg = 'Balance not available in test mode. Showing simulated balance (sum of paid orders).';
			$balance_in_inr = floatval($sim_sum);
		}

		$data = [
			'balance' => $balance_in_inr,
			'balance_msg' => $balance_msg,
			'orders' => $this->Payment_model->get_all_orders(),
			'key_id' => $this->key_id
		];

		$this->load->view('chairman_dashboard', $data);
	}

	// Create order (same as you had)
	public function create_order()
	{
		if ($this->input->method() !== 'post') {
			show_error('Method not allowed', 405);
		}

		$amount = floatval($this->input->post('amount', true));
		if ($amount <= 0) {
			$this->session->set_flashdata('order_error', 'Invalid amount');
			redirect(base_url('Payment_controller/chairman_balance'));
			return;
		}

		$url = "https://api.razorpay.com/v1/orders";
		$post_data = [
			'amount' => intval(round($amount * 100)),
			'currency' => 'INR',
			'receipt' => 'order_' . time(),
			'payment_capture' => 1
		];
		$headers = ['Content-Type: application/json'];

		$res = $this->curl_request($url, 'POST', $post_data, $headers);
		if (!$res['success'] || !isset($res['json']['id'])) {
			$this->session->set_flashdata('order_error', "Order creation failed: {$res['raw']}");
			redirect(base_url('Payment_controller/chairman_balance'));
			return;
		}

		$order = $res['json'];
		$this->Payment_model->insert_payment([
			'order_id' => $order['id'],
			'amount' => ($order['amount'] ?? 0) / 100,
			'status' => 'created',
			'receipt' => $order['receipt'] ?? null,
			'created_at' => date('Y-m-d H:i:s', $order['created_at'] ?? time())
		]);

		$data = ['order' => $order, 'key_id' => $this->key_id];
		$this->load->view('pay_view', $data);
	}

	// Payment verification (AJAX)
	public function payment_success()
	{
		$payment_id = $this->input->post('razorpay_payment_id', true);
		$order_id = $this->input->post('razorpay_order_id', true);
		$signature = $this->input->post('razorpay_signature', true);

		header('Content-Type: application/json');
		if (!$payment_id || !$order_id || !$signature) {
			echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
			return;
		}

		$expected_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, $this->key_secret);
		if (!hash_equals($expected_signature, $signature)) {
			echo json_encode(['status' => 'error', 'message' => 'Signature mismatch']);
			return;
		}

		$orderRow = $this->Payment_model->get_order_by_orderid($order_id);
		if (!$orderRow) {
			echo json_encode(['status' => 'error', 'message' => 'Order not found']);
			return;
		}

		$this->Payment_model->mark_order_paid($order_id, $payment_id);
		echo json_encode(['status' => 'success', 'message' => 'Payment verified']);
	}
}
