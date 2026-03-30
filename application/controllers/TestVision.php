<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class TestVision extends CI_Controller
{
	public function index()
	{
		$client = new ImageAnnotatorClient([
			'credentials' => APPPATH . 'config/google_vision.json'
		]);

		echo "✅ Google Vision Loaded";
		$client->close();
	}
}
