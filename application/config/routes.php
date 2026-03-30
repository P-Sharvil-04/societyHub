<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'usercontroller/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['register'] = 'usercontroller/register';
$route['send-otp'] = 'usercontroller/send_otp';
$route['verify-otp'] = 'usercontroller/verify_otp';
$route['send_otp'] = 'usercontroller/send_otp';
$route['verify_otp'] = 'usercontroller/verify_otp';
$route['login'] = 'usercontroller/login';
$route['logout'] = 'usercontroller/logout';

$route['dashboard'] = 'usercontroller/dashboard';
$route['residents'] = 'feature_controller/residents';
$route['reports'] = 'feature_controller/reports';
$route['add'] = 'usercontroller/add_expense';
$route['save-expense'] = 'usercontroller/save_expense';

$route['plan'] = 'plan_controller/plan';
$route['register-society'] = 'plan_controller/register_society';

$route['manage_member'] = 'feature_controller/member';
$route['member'] = 'feature_controller/save';
$route['delete_member/(:num)'] = 'feature_controller/delete_member/$1';
$route['assign-role'] = 'feature_controller/assign_role';

$route['remove_role'] = 'feature_controller/remove_role';
$route['member/remove_role'] = 'feature_controller/remove_role';
$route['member/view/(:num)'] = 'feature_controller/view/$1';
$route['member/edit/(:num)'] = 'feature_controller/update_member/$1';
$route['member/update/(:num)'] = 'feature_controller/update/$1';

$route['admin_register'] = 'plan_controller/admin_register';
$route['register_society'] = 'plan_controller/register_society';


$route['complaints'] = 'complaints_controller/index';
$route['complaints/add'] = 'complaints_controller/add';
$route['complaints/update'] = 'complaints_controller/update';

$route['notices'] = 'notice_controller/index';
$route['notice'] = 'notice_controller/index';

$route['aminities'] = 'feature_controller/aminities';
// $route['aminities'] = 'aminity_controller/index';
$route['amenity'] = 'aminity_controller/index';
$route['amenity/export'] = 'aminity_controller/export';

// $route['visitors'] = 'visitor_controller/index';
// $route['visitor'] = 'visitor/index';
// $route['visitor/export'] = 'visitor/export';
// $route['visitor/modal_add'] = 'visitor/modal_add';
// $route['visitor/modal_edit/(:num)'] = 'visitor/modal_edit/$1';
// $route['visitor/modal_delete/(:num)'] = 'visitor/modal_delete/$1';

$route['visitors'] = 'visitor_controller/index';
$route['visitors/index'] = 'visitor_controller/index';
$route['visitors/add'] = 'visitor_controller/add';
$route['visitors/edit/(:num)'] = 'visitor_controller/edit/$1';
$route['visitors/update'] = 'visitor_controller/update';
$route['visitors/delete/(:num)'] = 'visitor_controller/delete/$1';
$route['visitors/update-status'] = 'visitor_controller/update_status';

// $route['documents'] = 'document_controller/index';
// // $route['documents'] = 'documents/index';
// $route['documents/read'] = 'document_controller/read';
$route['document/read'] = 'document_controller/read';
$route['documents'] = 'document_controller/index';

$route['staff'] = 'staff_controller/index';
$route['staff/save'] = 'staff_controller/save';
$route['staff/delete/(:num)'] = 'staff_controller/delete/$1';
$route['staff/edit/(:num)'] = 'staff_controller/edit/$1';
$route['staff/view/(:num)'] = 'staff_controller/view/$1';
$route['staff/(:num)'] = 'staff_controller/staff/$1';

$route['amenities'] = 'amenities';
$route['amenities/list'] = 'aminity_controller/list';
$route['amenities/save'] = 'aminity_controller/save';
$route['amenities/delete'] = 'aminity_controller/delete';

$route['payment'] = 'payment_controller/index';
$route['create-order'] = 'payment_controller/create_order';
$route['verify-payment'] = 'payment_controller/verify';
$route['chairman-balance'] = 'payment_controller/chairman_balance';

$route['bank'] = 'bank/index';
$route['razorpay/create_contact'] = 'razorpay/create_contact';
$route['razorpay/link_bank_account'] = 'razorpay/link_bank_account';
$route['razorpay/view_transactions'] = 'razorpay/view_transactions';


// Auth
$route['parking'] = 'parking_controller/index';         // redirects by role
$route['parking/dashboard'] = 'parking_controller/dashboard';     // chairman
$route['parking/assign'] = 'parking_controller/assign';        // POST: assign slot
$route['parking/revoke/(:num)'] = 'parking_controller/revoke/$1';     // revoke by assignment id
$route['parking/my_parking'] = 'parking_controller/my_parking';    // owner

// $route['manage_member'] = 'members_controller/index';
// $route['member'] = 'members_controller/save';
// $route['delete_member/(:num)'] = 'members_controller/delete_member/$1';
// $route['member/view/(:num)'] = 'members_controller/view/$1';
// $route['member/edit/(:num)'] = 'members_controller/update_member/$1';
// $route['member/update/(:num)'] = 'members_controller/update/$1';

$route['events'] = 'events_booking_controller/events';

$route['settings'] = 'settings_controller/index';

$route['profile'] = 'Profile/index';

$route['flat_unit'] = 'flat_unit/index';
$route['flat_unit/save'] = 'flat_unit/save';
$route['flat_unit/delete/(:num)'] = 'flat_unit/delete/$1';
$route['flat_unit/assign_resident'] = 'flat_unit/assign_resident';
$route['flat_unit/import_csv'] = 'flat_unit/import_csv';


$route['society_setup'] = 'society_setup/index';
$route['society_setup/save_wings'] = 'society_setup/save_wings';
$route['society_setup/preview'] = 'society_setup/preview';
$route['society_setup/generate'] = 'society_setup/generate';
$route['society_setup/delete_wing/(:num)'] = 'society_setup/delete_wing/$1';
$route['society_setup/vacant_flats'] = 'society_setup/vacant_flats';


$route['payments'] = 'payment_controllerr/payments';
