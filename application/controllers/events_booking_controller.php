<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class events_booking_controller extends CI_Controller
{
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

	/* ─── role helpers ──────────────────────────────────────────── */
	private function _role()
	{
		return (string) $this->session->userdata('role_name');
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
	private function _is_chairman()
	{
		return $this->_role() === 'chairman';
	}
	private function _is_resident()
	{
		return in_array($this->_role(), ['owner', 'tenant'], true);
	}
	private function _is_owner()
	{
		return in_array($this->_role(), ['chairman', 'secretary', 'committee_member', 'accountant', 'owner', 'tenant'], true);
	}
	private function _can_approve()
	{
		return in_array($this->_role(), ['super_admin', 'chairman', 'secretary'], true);
	}
	private function _can_manage()
	{
		return in_array($this->_role(), ['super_admin', 'chairman', 'secretary'], true);
	}

	private function _society_id_filter()
	{
		return $this->_is_super()
			? ((int) $this->input->get('society_id') ?: '')
			: $this->_society();
	}

	private function _json($payload, $status = 200)
	{
		return $this->output
			->set_status_header($status)
			->set_content_type('application/json')
			->set_output(json_encode($payload));
	}

	/* ═════════════════════════════════════════════════════════════
	 *  EVENTS TAB
	 * ═════════════════════════════════════════════════════════════ */
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

		// Ensure every fund event has a QR token
		$events = $this->Events_booking_model->get_events($filters);
		foreach ($events as $e) {
			if (!empty($e['fund_required']) && (float) ($e['fund_amount'] ?? 0) > 0 && empty($e['qr_token'])) {
				$this->Events_booking_model->rotate_event_qr_token((int) $e['id']);
			}
		}

		// Reload to get fresh tokens, then enrich per-user data
		$events = $this->Events_booking_model->get_events($filters);
		$user_id = $this->_user_id();

		foreach ($events as &$e) {
			$e['per_person_share'] = 0;
			$e['user_has_paid'] = false;
			$e['user_ticket_token'] = null;
			$e['ticket_scanned'] = false;

			if (!empty($e['fund_required']) && (float) $e['fund_amount'] > 0) {
				$e['per_person_share'] = $this->Events_booking_model->calculate_per_person_share($e['id']);

				if ($this->_is_resident()) {
					$e['user_has_paid'] = $this->Events_booking_model->has_contributed($e['id'], $user_id);

					if ($e['user_has_paid']) {
						// Get (or lazily create) the ticket token
						$e['user_ticket_token'] = $this->Events_booking_model->get_or_create_ticket_token($e['id'], $user_id);

						// Check whether the chairman has already scanned this ticket
						$contrib = $this->Events_booking_model->get_contribution($e['id'], $user_id);
						$e['ticket_scanned'] = !empty($contrib['ticket_scanned']);
					}
				}
			}
		}
		unset($e);

		$data = [
			'title' => 'Events & Bookings',
			'activePage' => 'events_booking',
			'tab' => 'events',
			'events' => $events,
			'event_stats' => $this->Events_booking_model->get_event_stats($filters),
			'recent_events' => $this->Events_booking_model->get_recent_events($recent_soc, 5),
			'societies' => $societies,
			'filters' => $filters,
			'isSuperAdmin' => $this->_is_super(),
			'isChairman' => $this->_is_chairman(),
			'isOwner' => $this->_is_owner(),
			'isResident' => $this->_is_resident(),
			'canManage' => $this->_can_manage(),
		];
		
		$this->load->view('header', $data);
		$this->load->view('events_booking_view', $data);
	}

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
		$fund_amount = $fund ? (float) $this->input->post('fund_amount') : 0;

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
			'fund_amount' => $fund_amount,
			'fund_status' => $fund ? ($this->input->post('fund_status') ?: 'open') : null,
			'created_by' => $this->_user_id(),
		];

		$id = (int) $this->input->post('event_id');
		$ok = $id
			? $this->Events_booking_model->update_event($id, $d)
			: $this->Events_booking_model->insert_event($d);

		if ($ok) {
			$event_id = $id ?: (int) $ok;
			if ($fund && $fund_amount > 0) {
				$this->Events_booking_model->rotate_event_qr_token($event_id);
			} else {
				$this->Events_booking_model->clear_event_qr_token($event_id);
			}
		}

		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? ($id ? 'Event updated.' : 'Event created.') : 'Operation failed.'
		);
		redirect('events_booking_controller/events');
	}

	public function delete_event($id)
	{
		$ok = $this->Events_booking_model->delete_event((int) $id);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Event deleted.' : 'Delete failed.');
		redirect('events_booking_controller/events');
	}

	/* ─── Chairman: scan the event-level QR ───────────────────────
	 *
	 *  Flow on each scan:
	 *    1. Validate token exists
	 *    2. Record scan (user + timestamp)
	 *    3. Immediately rotate token → chairman's QR button stays live
	 *    4. Show success flash
	 *
	 *  The chairman's QR button is ALWAYS shown in the view regardless of
	 *  previous scan history.
	 * ────────────────────────────────────────────────────────────── */
	public function scan_event_qr($token = '')
	{
		if (!$this->_is_chairman()) {
			$this->session->set_flashdata('error', 'Only the chairman can scan this QR.');
			redirect('events_booking_controller/events');
		}

		$token = trim((string) $token);
		if ($token === '') {
			$this->session->set_flashdata('error', 'Invalid QR token.');
			redirect('events_booking_controller/events');
		}

		$event = $this->Events_booking_model->get_event_by_qr_token($token);
		if (!$event) {
			$this->session->set_flashdata('error', 'QR code expired or invalid. Refresh the page to get a new QR.');
			redirect('events_booking_controller/events');
		}

		// Record scan + auto-rotate to a fresh token
		$this->Events_booking_model->mark_event_qr_scanned((int) $event['id'], $this->_user_id());

		$this->session->set_flashdata(
			'success',
			'✓ QR scanned for "' . $event['title'] . '". A new QR is ready for the next scan.'
		);
		redirect('events_booking_controller/events');
	}

	/* ─── Chairman: scan a resident's entry ticket ─────────────────
	 *
	 *  Chairman can scan multiple different residents' tickets.
	 *  If a ticket was already scanned, we warn but don't block
	 *  (re-verification at the gate is a valid use case).
	 * ────────────────────────────────────────────────────────────── */
	public function scan_ticket($token)
	{
		if (!$this->_is_chairman()) {
			$this->session->set_flashdata('error', 'Only the chairman can scan entry tickets.');
			redirect('events_booking_controller/events');
		}

		$result = $this->Events_booking_model->scan_ticket($token);

		if ($result === 'ok') {
			$this->session->set_flashdata('success', '✓ Ticket validated. Resident may enter.');
		} elseif ($result === 'already') {
			$this->session->set_flashdata('success', 'ℹ This ticket was already scanned earlier — entry was previously granted.');
		} else {
			$this->session->set_flashdata('error', 'Invalid ticket QR. Please try again.');
		}

		redirect('events_booking_controller/events');
	}

	/* ─── Chairman: AJAX list of scanned/paid tickets for an event ─ */
	public function get_scanned_tickets($event_id = 0)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}
		if (!$this->_is_chairman() && !$this->_can_manage()) {
			return $this->_json(['success' => false, 'message' => 'Permission denied.']);
		}

		$tickets = $this->Events_booking_model->get_event_contributions((int) $event_id);
		return $this->_json(['success' => true, 'tickets' => $tickets]);
	}

	/* ═════════════════════════════════════════════════════════════
	 *  BOOKINGS TAB
	 * ═════════════════════════════════════════════════════════════ */
	public function bookings()
	{
		$filters = [
			'society_id' => $this->_society_id_filter(),
			'status' => $this->input->get('status', TRUE) ?: '',
			'payment_status' => $this->input->get('payment_status', TRUE) ?: '',
			'search' => $this->input->get('search', TRUE) ?: '',
		];
		if ($this->_is_resident()) {
			$filters['user_id'] = $this->_user_id();
		}

		$logged_user = $this->_is_resident()
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
			'isChairman' => $this->_is_chairman(),
			'isOwner' => $this->_is_owner(),
			'isResident' => $this->_is_resident(),
			'canApprove' => $this->_can_approve(),
			'canManage' => $this->_can_manage(),
			'logged_user' => $logged_user,
			'logged_user_id' => $this->_user_id(),
		];
		$this->load->view('header', $data);
		$this->load->view('events_booking_view', $data);
	}

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

		if ($this->Events_booking_model->check_clash($society_id, $area_name, $booking_date, $start_time, $end_time, $booking_id ?: null)) {
			$this->session->set_flashdata('error', "'{$area_name}' is already booked for that date and time slot.");
			redirect('events_booking_controller/bookings');
		}

		if ($this->_is_resident()) {
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
			'status' => $this->_is_resident()
				? 'pending'
				: ($this->input->post('status', TRUE) ?: 'pending'),
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

	public function delete_booking($id)
	{
		$ok = $this->Events_booking_model->delete_booking((int) $id);
		$this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Booking deleted.' : 'Delete failed.');
		redirect('events_booking_controller/bookings');
	}

	public function approve_booking($id)
	{
		if (!$this->_can_approve()) {
			$this->session->set_flashdata('error', 'Permission denied.');
			redirect('events_booking_controller/bookings');
		}

		$status = $this->input->post('status', TRUE);
		if (!in_array($status, ['approved', 'rejected'], true)) {
			$this->session->set_flashdata('error', 'Invalid status.');
			redirect('events_booking_controller/bookings');
		}

		$ok = $this->Events_booking_model->approve_booking((int) $id, $status, $this->_user_id());
		$this->session->set_flashdata(
			$ok ? 'success' : 'error',
			$ok ? 'Booking ' . $status . '.' : 'Action failed.'
		);
		redirect('events_booking_controller/bookings');
	}

	/* ═════════════════════════════════════════════════════════════
	 *  RAZORPAY
	 * ═════════════════════════════════════════════════════════════ */
	public function razorpay_create_order()
	{
		if (!$this->input->is_ajax_request())
			show_404();

		$amount = (float) $this->input->post('amount');
		$type = $this->input->post('type', TRUE);
		$ref_id = (int) $this->input->post('ref_id');

		if ($amount <= 0 || !$type || !$ref_id) {
			return $this->_json(['success' => false, 'message' => 'Invalid data.']);
		}

		if ($type === 'booking') {
			$booking = $this->Events_booking_model->get_booking_by_id($ref_id);
			if (!$booking || (int) $booking['user_id'] !== $this->_user_id()) {
				return $this->_json(['success' => false, 'message' => 'Unauthorized.']);
			}
		}

		$this->config->load('razorpay', TRUE);
		$key_id = $this->config->item('razorpay_key_id', 'razorpay');
		$key_secret = $this->config->item('razorpay_key_secret', 'razorpay');

		if (!$key_id || !$key_secret) {
			return $this->_json(['success' => false, 'message' => 'Razorpay keys not configured.']);
		}

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
			return $this->_json(['success' => false, 'message' => 'cURL error: ' . $err]);
		}

		$order = json_decode($response, true);
		if (!isset($order['id'])) {
			return $this->_json(['success' => false, 'message' => $order['error']['description'] ?? 'Order creation failed.']);
		}

		return $this->_json(['success' => true, 'order_id' => $order['id'], 'amount' => $amount_paise, 'key_id' => $key_id]);
	}

	public function razorpay_verify()
	{
		$this->config->load('razorpay', TRUE);
		$key_secret = $this->config->item('razorpay_key_secret', 'razorpay');

		$order_id = $this->input->post('razorpay_order_id', TRUE);
		$payment_id = $this->input->post('razorpay_payment_id', TRUE);
		$signature = $this->input->post('razorpay_signature', TRUE);
		$type = $this->input->post('type', TRUE);
		$ref_id = (int) $this->input->post('ref_id');
		$amount = (float) $this->input->post('amount');

		if (!$order_id || !$payment_id || !$signature || !$type || !$ref_id || $amount <= 0) {
			$this->session->set_flashdata('error', 'Payment verification data missing.');
			redirect($type === 'booking' ? 'events_booking_controller/bookings' : 'events_booking_controller/events');
		}

		$expected = hash_hmac('sha256', $order_id . '|' . $payment_id, $key_secret);
		if (!hash_equals($expected, $signature)) {
			$this->session->set_flashdata('error', 'Payment verification failed. Please contact support.');
			redirect($type === 'booking' ? 'events_booking_controller/bookings' : 'events_booking_controller/events');
		}

		if ($this->Events_booking_model->payment_exists($payment_id)) {
			$this->session->set_flashdata('success', 'Payment already recorded.');
			redirect($type === 'booking' ? 'events_booking_controller/bookings' : 'events_booking_controller/events');
		}

		$user = $this->db->get_where('users', ['id' => $this->_user_id()])->row_array();

		if ($type === 'booking') {
			$booking = $this->Events_booking_model->get_booking_by_id($ref_id);
			if (!$booking || (int) $booking['user_id'] !== $this->_user_id()) {
				$this->session->set_flashdata('error', 'Unauthorized.');
				redirect('events_booking_controller/bookings');
			}
			$this->Events_booking_model->mark_booking_paid($ref_id);
			$this->Events_booking_model->insert_payment([
				'society_id' => $booking['society_id'] ?? $this->_society(),
				'user_id' => $this->_user_id(),
				'user_name' => $user['name'] ?? ($booking['user_name'] ?? ''),
				'flat_no' => $user['flat_no'] ?? ($booking['flat_no'] ?? ''),
				'reference_type' => 'booking',
				'reference_id' => $ref_id,
				'description' => 'Area Booking – ' . ($booking['area_name'] ?? ''),
				'amount' => $amount,
				'payment_method' => 'razorpay',
				'razorpay_order_id' => $order_id,
				'razorpay_payment_id' => $payment_id,
				'razorpay_signature' => $signature,
				'status' => 'paid',
				'paid_at' => date('Y-m-d H:i:s'),
			]);
			$this->session->set_flashdata('success', 'Payment of ₹' . number_format($amount, 0) . ' received. Booking confirmed!');
			redirect('events_booking_controller/bookings');

		} else {
			// contribution
			if ($this->Events_booking_model->has_contributed($ref_id, $this->_user_id())) {
				$this->session->set_flashdata('error', 'You have already contributed to this event.');
				redirect('events_booking_controller/events');
			}

			$event = $this->Events_booking_model->get_event_by_id($ref_id);
			if (!$event) {
				$this->session->set_flashdata('error', 'Event not found.');
				redirect('events_booking_controller/events');
			}

			$expected_share = $this->Events_booking_model->calculate_per_person_share($ref_id);
			if (abs($amount - $expected_share) > 0.01) {
				$this->session->set_flashdata('error', 'Contribution amount does not match the required share.');
				redirect('events_booking_controller/events');
			}

			$this->Events_booking_model->add_contribution([
				'event_id' => $ref_id,
				'society_id' => $user['society_id'] ?? null,
				'user_id' => $this->_user_id(),
				'user_name' => $user['name'] ?? '',
				'flat_no' => $user['flat_no'] ?? '',
				'amount' => $amount,
				'payment_status' => 'paid',
				'paid_at' => date('Y-m-d H:i:s'),
			]);

			// Generate ticket token immediately after contribution
			$this->Events_booking_model->get_or_create_ticket_token($ref_id, $this->_user_id());

			$this->Events_booking_model->insert_payment([
				'society_id' => $user['society_id'] ?? $this->_society(),
				'user_id' => $this->_user_id(),
				'user_name' => $user['name'] ?? '',
				'flat_no' => $user['flat_no'] ?? '',
				'reference_type' => 'contribution',
				'reference_id' => $ref_id,
				'description' => 'Fund Contribution – ' . ($event['title'] ?? ''),
				'amount' => $amount,
				'payment_method' => 'razorpay',
				'razorpay_order_id' => $order_id,
				'razorpay_payment_id' => $payment_id,
				'razorpay_signature' => $signature,
				'status' => 'paid',
				'paid_at' => date('Y-m-d H:i:s'),
			]);

			$this->session->set_flashdata('success', 'Contribution of ₹' . number_format($amount, 0) . ' received. Thank you! Check your ticket below.');
			redirect('events_booking_controller/events');
		}
	}
}
