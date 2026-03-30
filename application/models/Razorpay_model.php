<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '../vendor/autoload.php';
use Razorpay\Api\Api;

class Razorpay_model extends CI_Model
{
	private $api;

	public function __construct()
	{
		parent::__construct();
		$this->api = new Api('rzp_test_us_SHbyyHpXcssck6', '50lRK6hhtVNCQY8aFFGKOgn5');
	}

	public function create_contact($name, $email, $contact)
	{
		return $this->api->contact->create([
			'name' => $name,
			'email' => $email,
			'contact' => $contact,
			'type' => 'vendor'
		]);
	}

	public function link_bank_account($contact_id, $account_name, $ifsc, $account_number)
	{
		return $this->api->payoutAccount->create([
			'contact_id' => $contact_id,
			'account_type' => 'bank_account',
			'bank_account' => [
				'name' => $account_name,
				'ifsc' => $ifsc,
				'account_number' => $account_number
			]
		]);
	}

	public function get_payouts()
	{
		return $this->api->payout->all();
	}
}
