<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	public $role;
	public $menus;
	public $allowedMenus;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');

		// load configs
		$this->config->load('menus', TRUE);
		$this->config->load('permissions', TRUE);

		$this->menus = $this->config->item('menus', 'menus');
		$permissions = $this->config->item('permissions', 'permissions');

		// get role from session (must be set at login)
		$this->role = $this->session->userdata('role') ?? 'member';

		// allowed menu keys for this role
		$this->allowedMenus = $permissions[$this->role] ?? [];

		// make available to all views
		$this->load->vars([
			'menus' => $this->menus,
			'allowedMenus' => $this->allowedMenus,
			'currentRole' => $this->role,
		]);
	}

	protected function check_permission($menu)
	{
		if (!has_permission($menu)) {
			show_error('Access Denied', 403);
		}
	}
}
