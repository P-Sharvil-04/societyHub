<?php

function has_permission($menu)
{
	$CI =& get_instance();

	$role = $CI->session->userdata('role');

	$CI->config->load('permissions');

	$permissions = $CI->config->item('permissions');

	if (!isset($permissions[$role]))
		return false;

	if ($permissions[$role] === '*')
		return true;

	return in_array($menu, $permissions[$role]);
}
