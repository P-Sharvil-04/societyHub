<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Map extends CI_Controller {

    public function index() {
        $data['google_api_key'] = 'AIzaSyARLVf0Pgw4xZuzHiLcTDwirZGzZYMlAbI'; // replace with your API key
        $this->load->view('map_view', $data);
    }
}
