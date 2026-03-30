<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Razorpay extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Razorpay_model');
	}

	public function test()
	{
		$contact = $this->Razorpay_model->create_contact();
		$contact_id = $contact->id;

		echo "<pre>Contact Created:\n";
		print_r($contact);
		echo "</pre>";

		$bank = $this->Razorpay_model->link_bank_account($contact_id);
		echo "<pre>Bank Linked:\n";
		print_r($bank);
		echo "</pre>";

		$payouts = $this->Razorpay_model->get_payouts();
		echo "<pre>Payouts:\n";
		print_r($payouts);
		echo "</pre>";
	}
}
