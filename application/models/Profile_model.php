<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile_model extends CI_Model {
    public function __construct() {
        parent::__construct();
    }

    public function get_user($id) {
        return $this->db->where('id', (int)$id)->get('users')->row();
    }

    public function update_user($id, $data) {
        return $this->db->where('id', (int)$id)->update('users', $data);
    }
}
