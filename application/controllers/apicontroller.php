<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class apicontroller extends CI_Controller
{
	public function member()
	{
		header('content-type:application/json');

		$this->load->model('manage_member_model');
		$this->load->library(array('session', 'form_validation'));
		$this->load->helper(['url', 'form']);

		$data['members'] = $this->manage_member_model->get();
		echo json_encode([
			'status' => true,
			'data' => $data
		]);
	}
}
?>
