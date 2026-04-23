<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Dashboard_model', 'dashboard_model');
        $this->load->helper(['url', 'html']);
        $this->load->library('session');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index(): void
    {
        $role = strtolower((string) $this->session->userdata('role_name'));
        $userId = (int) $this->session->userdata('user_id');
        $sessionSocietyId = (int) $this->session->userdata('society_id');

        $isSuperAdmin = in_array($role, ['super_admin', 'superadmin', 'super admin'], true);
        $isChairman = in_array($role, ['chairman', 'secretary', 'accountant', 'committee_member', 'staff', 'security'], true);
        $isOwner = ($role === 'owner');

        $selectedSocietyId = $isSuperAdmin
            ? ((int) $this->input->get('society_id') ?: $sessionSocietyId)
            : $sessionSocietyId;

        $societies = $isSuperAdmin ? $this->dashboard_model->get_societies() : [];

        // Gather all data
        $stats = $this->dashboard_model->get_stats($selectedSocietyId, $isSuperAdmin, $isOwner, $userId);
        $recentMembers = $this->dashboard_model->get_recent_members($selectedSocietyId, $isSuperAdmin, $isOwner, $userId, 3);
        $paymentSummary = $this->dashboard_model->get_payment_summary($selectedSocietyId, $isSuperAdmin, $isOwner, $userId);
        $series = $this->dashboard_model->get_income_expense_series($selectedSocietyId, $isSuperAdmin, $isOwner, $userId);
        $recentNotices = $this->dashboard_model->get_recent_notices($selectedSocietyId, $isSuperAdmin, $isOwner, $userId, 3);
        $recentComplaints = $this->dashboard_model->get_recent_complaints($selectedSocietyId, $isSuperAdmin, $isOwner, $userId, 3);
        $recentBookings = $this->dashboard_model->get_recent_bookings($selectedSocietyId, $isSuperAdmin, $isOwner, $userId, 3);
        $insights = $this->dashboard_model->get_insights($selectedSocietyId, $isSuperAdmin, $isOwner, $userId);

        $data = [
            'title' => 'Dashboard',
            'activePage' => 'dashboard',
            'role' => $role,
            'isSuperAdmin' => $isSuperAdmin,
            'isChairman' => $isChairman,
            'isOwner' => $isOwner,
            'selectedSocietyId' => $selectedSocietyId,
            'societies' => $societies,
            'stats' => $stats,
            'recentMembers' => $recentMembers,
            'paymentSummary' => $paymentSummary,
            'chartLabels' => $series['labels'],
            'incomeSeries' => $series['income'],
            'expenseSeries' => $series['expense'],
            'recentNotices' => $recentNotices,
            'recentComplaints' => $recentComplaints,
            'recentBookings' => $recentBookings,
            'insights' => $insights,
        ];

        $this->load->view('header', $data);
        $this->load->view('dashboard_view', $data);
    }
}
