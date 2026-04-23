<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['menus'] = [

	// MAIN
	'dashboard' => [
		'title' => 'Dashboard',
		'url' => 'dashboard',
		'icon' => 'fas fa-tachometer-alt',
		'section' => 'main',
	],

	'manage_member' => [
		'title' => 'Members',
		'url' => 'manage_member',
		'icon' => 'fas fa-users',
		'section' => 'main',
		'children' => [
			'manage_member_view' => [
				'title' => 'View Members',
				'url' => 'manage_member',
			],
			'manage_member_add' => [
				'title' => 'Add Member',
				'url' => 'manage_member/add',
			],
			'manage_member_assign_role' => [
				'title' => 'Assign Committee',
				'url' => 'manage_member/assign_role',
			],
		],
	],

	'staff' => [
		'title' => 'Staff',
		'url' => 'staff',
		'icon' => 'fas fa-user-tie',
		'section' => 'main',
	],

	'payments' => [
		'title' => 'Payments',
		'url' => 'payments',
		'icon' => 'fas fa-credit-card',
		'section' => 'main',
	],

	'complaints' => [
		'title' => 'Complaints',
		'url' => 'complaints',
		'icon' => 'fas fa-exclamation-circle',
		'section' => 'main',
	],

	'notices' => [
		'title' => 'Notices',
		'url' => 'notices',
		'icon' => 'fas fa-bell',
		'section' => 'main',
	],

	// MANAGEMENT
	'flat_unit' => [
		'title' => 'Flat/Unit Management',
		'url' => 'flat_unit',
		'icon' => 'fas fa-building',
		'section' => 'management',
	],

	'parking' => [
		'title' => 'Parking Management',
		'url' => 'parking',
		'icon' => 'fas fa-parking',
		'section' => 'management',
	],

	'documents' => [
		'title' => 'Documents',
		'url' => 'documents',
		'icon' => 'fas fa-folder',
		'section' => 'management',
	],

	'events' => [
		'title' => 'Events & Booking',
		'url' => 'events',
		'icon' => 'fas fa-calendar-alt',
		'section' => 'management',
	],

	// INSIGHTS
	'ai_insights' => [
		'title' => 'AI Insights',
		'url' => 'ai_insights',
		'icon' => 'fas fa-robot',
		'section' => 'insights',
	],
	
	'cctv' => [
		'title' => 'CCTV',
		'url' => 'cctv',
		'icon' => 'fas fa-video',
	],

];
