<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['permissions'] = [

	'super_admin' => [
		'dashboard',
		'manage_member',
		'manage_member_view',
		'manage_member_add',
		'manage_member_assign_role',
		'staff',
		'payments',
		'complaints',
		'notices',
		'flat_unit',
		'parking',
		'documents',
		'events',
		'ai_insights',
		'visitors',
		'aminities',
		'reports',
		'settings',
		'society_setup',
		'cctv',
		// 'events'

	], // by default give all (we'll override below)

	// explicit lists (only keys that exist in menus.php)
	'chairman' => [
		'dashboard',
		'manage_member',
		'manage_member_view',
		'manage_member_add',
		'manage_member_assign_role',
		'staff',
		'payments',
		'complaints',
		'notices',
		'flat_unit',
		'parking',
		// 'documents',
		'events',
		'ai_insights',
		'visitors',
		'aminities',
		'reports',
		'settings',
		'cctv',
	],

	'secretary' => [
		'dashboard',
		'manage_member',
		'manage_member_view',
		'manage_member_assign_role',
		'staff',
		'complaints',
		'notices',
		// 'documents',
		'events',
		'parking'
	],

	'treasurer' => [
		'dashboard',
		'payments',
		'reports'
	],

	'committee' => [
		'dashboard',
		'manage_member',
		'manage_member_view',
		'events'
	],

	'owner' => [
		'dashboard',
		'manage_member',
		'manage_member_view',
		'staff',
		'complaints',
		'notices',
		'aminities',
		'parking',
		'events',
		'settings',
		'visitors',

	],

	'' => [
		'dashboard',
		'manage_member',
		'manage_member_view',
		'staff',
		'complaints',
		'notices',
		'aminities',
		'parking',
		'events'
	],

	'security' => [
		'visitors',
		'settings',
		'cctv',
	]
];
