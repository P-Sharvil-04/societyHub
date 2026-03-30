<?php
class Expense extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Expense_model');
		$this->load->library(['session', 'email']);
		$this->load->helper(['url']);
	}

	public function add_expense()
	{
		header('Content-Type: application/json');
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}

		$this->load->view('add_expense_view');
		// ✅ Check login
		if (!$this->session->userdata('logged_in')) {
			echo json_encode([
				'status' => false,
				'msg' => 'Login required'
			]);
			return;
		}

		// ✅ Logged-in user data
		$user_id = $this->session->userdata('user_id');
		$user_email = $this->session->userdata('email');

		// ✅ Form data
		$data = [
			'user_id' => $user_id,
			'title' => $this->input->post('title', true),
			'amount' => $this->input->post('amount', true),
			'category' => $this->input->post('category', true),
			'note' => $this->input->post('note', true),
			'created_at' => date('Y-m-d H:i:s')
		];

		// ✅ Insert into DB
		if ($this->Expense_model->insert_expense($data)) {

			// ✅ EMAIL CONTENT (use same inserted data)
			$message = "
<h3>New Expense Added</h3>
<p><b>Title:</b> {$data['title']}</p>
<p><b>Amount:</b> ₹{$data['amount']}</p>
<p><b>Category:</b> {$data['category']}</p>
<p><b>Note:</b> {$data['note']}</p>
<p><b>Date:</b> {$data['created_at']}</p>
";

			// ✅ Send Email to logged-in user
			$this->email->from('yourgmail@gmail.com', 'Expense Tracker');
			$this->email->to($user_email);
			$this->email->subject('Expense Added Successfully');
			$this->email->message($message);

			if (!$this->email->send()) {
				echo json_encode([
					'status' => true,
					'msg' => 'Expense saved but email failed',
					'email_error' => $this->email->print_debugger()
				]);
				return;
			}

			echo json_encode([
				'status' => true,
				'msg' => 'Expense saved & emailed successfully'
			]);

		} else {
			echo json_encode([
				'status' => false,
				'msg' => 'Failed to save expense'
			]);
		}
	}
}
