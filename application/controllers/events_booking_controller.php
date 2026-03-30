<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class events_booking_controller extends CI_Controller
{
	private $canManage = [];

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Events_booking_model');
		$this->load->helper(['url', 'form']);
		$this->load->library(['session', 'form_validation']);

		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
	}

	/* ─── shared session helpers ─── */
	private function _role()
	{
		return $this->session->userdata('role_name');
	}
	private function _society()
	{
		return (int) $this->session->userdata('society_id');
	}
	private function _user_id()
	{
		return (int) $this->session->userdata('user_id');
	}
	private function _is_super()
	{
		return $this->_role() === 'super_admin';
	}
	private function _is_owner()
	{
		return in_array($this->_role(), ['chairman', 'secretary', 'committee_member', 'accountant', 'secretary', 'owner', 'tenant']);
	}
	private function _can_approve()
	{
		return in_array($this->_role(), ['super_admin', 'chairman', 'secretary']);
	}
	private function _can_manage()
	{
		return in_array($this->_role(), ['super_admin', 'chairman', 'secretary']);
	}

	private function _society_id_filter()
	{
		return $this->_is_super()
			? ((int) $this->input->get('society_id') ?: '')
			: $this->_society();
	}

	/* ════════════════════════════════════════════════════════════
	 *  EVENTS — index
	 * ════════════════════════════════════════════════════════════ */
	public function events()
	{
		$filters = [
			'society_id' => $this->_society_id_filter(),
			'status' => $this->input->get('status', TRUE) ?: '',
			'event_type' => $this->input->get('event_type', TRUE) ?: '',
			'search' => $this->input->get('search', TRUE) ?: '',
		];

		$societies = $this->_is_super() ? $this->Events_booking_model->get_societies() : [];
		$recent_soc = $this->_is_super() ? null : $this->_society();

		$data = [
			'title' => 'Events & Bookings',
			'activePage' => 'events_booking',
			'tab' => 'events',
			'events' => $this->Events_booking_model->get_events($filters),
			'event_stats' => $this->Events_booking_model->get_event_stats($filters),
			'recent_events' => $this->Events_booking_model->get_recent_events($recent_soc, 5),
			'societies' => $societies,
			'filters' => $filters,
			'isSuperAdmin' => $this->_is_super(),
			'isOwner' => $this->_is_owner(),
			'canManage' => $this->_can_manage(),
		];

		$this->load->view('header', $data);
		$this->load->view('events_booking_view', $data);
	}

	/* ── save event ── */
	public function save_event()
	{
		$this->form_validation->set_rules('title', 'Title', 'required|trim');
		$this->form_validation->set_rules('event_date', 'Event Date', 'required');
		$this->form_validation->set_rules('event_type', 'Event Type', 'required');
		$this->form_validation->set_rules('status', 'Status', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('events_booking_controller/events');
		}

		$fund = (int) $this->input->post('fund_required');

		$d = [
			'society_id' => $this->_is_super()
				? ((int) $this->input->post('society_id') ?: null)
				: $this->_society(),
			'title' => $this->input->post('title', TRUE),
			'description' => $this->input->post('description', TRUE),
			'event_type' => $this->input->post('event_type', TRUE),
			'event_date' => $this->input->post('event_date', TRUE),
			'start_time' => $this->input->post('start_time', TRUE) ?: null,
			'end_time' => $this->input->post('end_time', TRUE) ?: null,
			'venue' => $this->input->post('venue', TRUE),
			'status' => $this->input->post('status', TRUE),
			'fund_required' => $fund,
			'fund_amount' => $fund ? (float) $this->input->post('fund_amount') : 0,
			'fund_status' => $fund ? ($this->input->post('fund_status') ?: 'open') : null,
			'created_by' => $this->_user_id(),
		];

		$id = (int) $this->input->post('event_id');
		$ok = $id
			? $this->Events_booking_model->update_event($id, $d)
			: $this->Events_booking_model->insert_event($d);

		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? ($id ? 'Event updated.' : 'Event created.') : 'Operation failed.'
		);
		redirect('events_booking_controller/events');
	}

	/* ── delete event ── */
	public function delete_event($id)
	{
		$ok = $this->Events_booking_model->delete_event((int) $id);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Event deleted.' : 'Delete failed.');
		redirect('events_booking_controller/events');
	}

	/* ── contribute (owner pays fund) ── */
	public function contribute()
	{
		if (!$this->_is_owner()) {
			$this->session->set_flashdata('error', 'Only owners can contribute.');
			redirect('events_booking_controller/events');
		}

		$event_id = (int) $this->input->post('event_id');
		$amount = (float) $this->input->post('amount');

		if (!$event_id || $amount <= 0) {
			$this->session->set_flashdata('error', 'Invalid amount.');
			redirect('events_booking_controller/events');
		}

		$user = $this->db->get_where('users', ['id' => $this->_user_id()])->row_array();
		if (!$user) {
			redirect('events_booking_controller/events');
		}

		$ok = $this->Events_booking_model->add_contribution([
			'event_id' => $event_id,
			'society_id' => $user['society_id'] ?? null,
			'user_id' => $this->_user_id(),
			'user_name' => $user['name'],
			'flat_no' => $user['flat_no'] ?? '',
			'amount' => $amount,
			'payment_status' => 'paid',
			'paid_at' => date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? 'Contribution recorded. Thank you!' : 'Failed to record contribution.'
		);
		redirect('events_booking_controller/events');
	}

	/* ════════════════════════════════════════════════════════════
	 *  BOOKINGS — index
	 * ════════════════════════════════════════════════════════════ */
	public function bookings()
	{
		$filters = [
			'society_id' => $this->_society_id_filter(),
			'status' => $this->input->get('status', TRUE) ?: '',
			'payment_status' => $this->input->get('payment_status', TRUE) ?: '',
			'search' => $this->input->get('search', TRUE) ?: '',
		];

		if ($this->_is_owner()) {
			$filters['user_id'] = $this->_user_id();
		}

		$logged_user = $this->_is_owner()
			? $this->db->get_where('users', ['id' => $this->_user_id()])->row_array()
			: null;

		$societies = $this->_is_super() ? $this->Events_booking_model->get_societies() : [];
		$recent_soc = $this->_is_super() ? null : $this->_society();

		$data = [
			'title' => 'Events & Bookings',
			'activePage' => 'events_booking',
			'tab' => 'bookings',
			'bookings' => $this->Events_booking_model->get_bookings($filters),
			'booking_stats' => $this->Events_booking_model->get_booking_stats($filters),
			'recent_bookings' => $this->Events_booking_model->get_recent_bookings($recent_soc, 5),
			'societies' => $societies,
			'filters' => $filters,
			'isSuperAdmin' => $this->_is_super(),
			'isOwner' => $this->_is_owner(),
			'canApprove' => $this->_can_approve(),
			'canManage' => $this->_can_manage(),
			'logged_user' => $logged_user,
		];

		$this->load->view('header', $data);
		$this->load->view('events_booking_view', $data);
	}

	/* ── save booking ── */
	public function save_booking()
	{
		$this->form_validation->set_rules('area_name', 'Area Name', 'required|trim');
		$this->form_validation->set_rules('booking_date', 'Booking Date', 'required');
		$this->form_validation->set_rules('start_time', 'Start Time', 'required');
		$this->form_validation->set_rules('end_time', 'End Time', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			redirect('events_booking_controller/bookings');
		}

		$booking_id = (int) $this->input->post('booking_id');
		$area_name = $this->input->post('area_name', TRUE);
		$booking_date = $this->input->post('booking_date', TRUE);
		$start_time = $this->input->post('start_time', TRUE);
		$end_time = $this->input->post('end_time', TRUE);
		$society_id = $this->_is_super()
			? ((int) $this->input->post('society_id') ?: $this->_society())
			: $this->_society();

		if (
			$this->Events_booking_model->check_clash(
				$society_id,
				$area_name,
				$booking_date,
				$start_time,
				$end_time,
				$booking_id ?: null
			)
		) {
			$this->session->set_flashdata('error', "'{$area_name}' is already booked for that date and time slot.");
			redirect('events_booking_controller/bookings');
		}

		if ($this->_is_owner()) {
			$user = $this->db->get_where('users', ['id' => $this->_user_id()])->row_array();
			$u_name = $user['name'] ?? '';
			$u_flat = $user['flat_no'] ?? '';
		} else {
			$u_name = $this->input->post('user_name', TRUE);
			$u_flat = $this->input->post('flat_no', TRUE);
		}

		$d = [
			'society_id' => $society_id,
			'user_id' => $this->_user_id(),
			'user_name' => $u_name,
			'flat_no' => $u_flat,
			'area_name' => $area_name,
			'purpose' => $this->input->post('purpose', TRUE),
			'booking_date' => $booking_date,
			'start_time' => $start_time,
			'end_time' => $end_time,
			'amount' => (float) $this->input->post('amount') ?: 0,
			'payment_status' => $this->input->post('payment_status', TRUE) ?: 'pending',
			'status' => $this->_is_owner() ? 'pending' : ($this->input->post('status', TRUE) ?: 'pending'),
		];

		$ok = $booking_id
			? $this->Events_booking_model->update_booking($booking_id, $d)
			: $this->Events_booking_model->insert_booking($d);

		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? ($booking_id ? 'Booking updated.' : 'Booking submitted.') : 'Operation failed.'
		);
		redirect('events_booking_controller/bookings');
	}

	/* ── delete booking ── */
	public function delete_booking($id)
	{
		$ok = $this->Events_booking_model->delete_booking((int) $id);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Booking deleted.' : 'Delete failed.');
		redirect('events_booking_controller/bookings');
	}

	/* ── approve / reject ── */
	public function approve_booking($id)
	{
		if (!$this->_can_approve()) {
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('events_booking_controller/bookings');
		}
		$status = $this->input->post('status');
		$ok = $this->Events_booking_model->approve_booking((int) $id, $status, $this->_user_id());
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Booking ' . $status . '.' : 'Action failed.');
		redirect('events_booking_controller/bookings');
	}

	/* ── mark paid ── */
	public function pay_booking($id)
	{
		$booking = $this->Events_booking_model->get_booking_by_id((int) $id);
		if ($this->_is_owner() && (!$booking || (int) $booking['user_id'] !== $this->_user_id())) {
			$this->session->set_flashdata('error', 'Unauthorized.');
			redirect('events_booking_controller/bookings');
		}
		$ok = $this->Events_booking_model->mark_booking_paid((int) $id);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Payment recorded.' : 'Failed to record payment.');
		redirect('events_booking_controller/bookings');
	}

	/* ════════════════════════════════════════════════════════════
	 *  RAZORPAY — create order (AJAX, returns JSON)
	 *
	 *  POST params:
	 *    amount  → amount in rupees (converted to paise internally)
	 *    type    → 'booking' | 'contribution'
	 *    ref_id  → booking_id or event_id
	 * ════════════════════════════════════════════════════════════ */
	public function razorpay_create_order()
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
			return;
		}

		$amount = (float) $this->input->post('amount');
		$type = $this->input->post('type', TRUE);
		$ref_id = (int) $this->input->post('ref_id');

		if ($amount <= 0 || !$type || !$ref_id) {
			echo json_encode(['success' => false, 'message' => 'Invalid data.']);
			return;
		}

		// ── Razorpay credentials — store in application/config/razorpay.php ──
		// $this->config->load('razorpay');
		// $key_id     = $this->config->item('razorpay_key_id');
		// $key_secret = $this->config->item('razorpay_key_secret');
		$key_id = 'rzp_test_SQegebn7NHi2HZ';   // ← replace with your key
		$key_secret = '6DHNi6FGHUTYpnrq9Zfzm78p';          // ← replace with your secret

		// Amount in paise (Razorpay works in smallest currency unit)
		$amount_paise = (int) round($amount * 100);

		$receipt = $type . '_' . $ref_id . '_' . time();

		$payload = json_encode([
			'amount' => $amount_paise,
			'currency' => 'INR',
			'receipt' => $receipt,
			'notes' => ['type' => $type, 'ref_id' => $ref_id],
		]);

		$ch = curl_init('https://api.razorpay.com/v1/orders');
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_USERPWD => $key_id . ':' . $key_secret,
			CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
			CURLOPT_SSL_VERIFYPEER => false,
		]);

		$response = curl_exec($ch);
		$err = curl_error($ch);
		curl_close($ch);

		if ($err) {
			echo json_encode(['success' => false, 'message' => 'cURL error: ' . $err]);
			return;
		}

		$order = json_decode($response, true);

		if (!isset($order['id'])) {
			echo json_encode(['success' => false, 'message' => $order['error']['description'] ?? 'Order creation failed.']);
			return;
		}

		echo json_encode([
			'success' => true,
			'order_id' => $order['id'],
			'amount' => $amount_paise,
			'key_id' => $key_id,
		]);
	}

	/* ════════════════════════════════════════════════════════════
	 *  RAZORPAY — verify payment signature + mark paid (POST)
	 *
	 *  POST params:
	 *    razorpay_order_id, razorpay_payment_id, razorpay_signature,
	 *    type   → 'booking' | 'contribution'
	 *    ref_id → booking_id or event_id
	 *    amount → original amount in rupees (for contribution insert)
	 * ════════════════════════════════════════════════════════════ */
	public function razorpay_verify()
	{
		$key_secret = '6DHNi6FGHUTYpnrq9Zfzm78p';  // ← same secret as above

		$order_id = $this->input->post('razorpay_order_id', TRUE);
		$payment_id = $this->input->post('razorpay_payment_id', TRUE);
		$signature = $this->input->post('razorpay_signature', TRUE);
		$type = $this->input->post('type', TRUE);
		$ref_id = (int) $this->input->post('ref_id');
		$amount = (float) $this->input->post('amount');

		// Verify signature: HMAC-SHA256 of "order_id|payment_id" using key_secret
		$expected = hash_hmac('sha256', $order_id . '|' . $payment_id, $key_secret);

		if (!hash_equals($expected, $signature)) {
			$this->session->set_flashdata('error', 'Payment verification failed. Please contact support.');
			redirect($type === 'booking'
				? 'events_booking_controller/bookings'
				: 'events_booking_controller/events');
		}

		// Signature valid — mark as paid
		if ($type === 'booking') {
			$booking = $this->Events_booking_model->get_booking_by_id($ref_id);
			if ($this->_is_owner() && (!$booking || (int) $booking['user_id'] !== $this->_user_id())) {
				$this->session->set_flashdata('error', 'Unauthorized.');
				redirect('events_booking_controller/bookings');
			}
			$this->Events_booking_model->mark_booking_paid($ref_id);
			$this->session->set_flashdata('success', 'Payment of ₹' . number_format($amount, 0) . ' received. Booking confirmed!');
			redirect('events_booking_controller/bookings');

		} else {
			// contribution
			$user = $this->db->get_where('users', ['id' => $this->_user_id()])->row_array();
			$this->Events_booking_model->add_contribution([
				'event_id' => $ref_id,
				'society_id' => $user['society_id'] ?? null,
				'user_id' => $this->_user_id(),
				'user_name' => $user['name'],
				'flat_no' => $user['flat_no'] ?? '',
				'amount' => $amount,
				'payment_status' => 'paid',
				'paid_at' => date('Y-m-d H:i:s'),
			]);
			$this->session->set_flashdata('success', 'Contribution of ₹' . number_format($amount, 0) . ' received. Thank you!');
			redirect('events_booking_controller/events');
		}
	}
}
