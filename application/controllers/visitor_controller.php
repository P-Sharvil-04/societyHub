<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class visitor_controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Visitor_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    private function _role()
    {
        $role = $this->session->userdata('role_name');
        return [
            'role_name'    => $role,
            'user_id'      => $this->session->userdata('user_id'),
            'society_id'   => $this->session->userdata('society_id'),
            'isSuperAdmin' => ($role === 'super_admin'),
            'isAdmin'      => ($role === 'admin'),
            'isOwner'      => ($role === 'owner'),
            'isSecurity'   => ($role === 'security'),
        ];
    }

    public function index()
    {
        $r = $this->_role();

        $filters = [
            'society_id'      => $r['isSuperAdmin'] ? $this->input->get('society_id', TRUE) : $r['society_id'],
            'status'          => $this->input->get('status', TRUE)          ?: '',
            'approval_status' => $this->input->get('approval_status', TRUE) ?: '',
            'search'          => $this->input->get('search', TRUE)          ?: '',
        ];

        if ($r['isOwner']) {
            $ownerFlat = $this->Visitor_model->get_owner_flats($r['user_id']);
            $filters['owner_flat'] = $ownerFlat['flat_no'] ?? '';
        }

        $page     = max(1, (int) $this->input->get('page'));
        $per_page = 10;
        $offset   = ($page - 1) * $per_page;

        $total_rows  = $this->Visitor_model->count_visitors($filters);
        $total_pages = (int) ceil($total_rows / $per_page);
        $pagination  = $this->_build_pagination($page, $total_pages, $filters);

        $visitors  = $this->Visitor_model->get_visitors($filters, $per_page, $offset);
        $stats     = $this->Visitor_model->get_stats($filters);
        $societies = $r['isSuperAdmin'] ? $this->Visitor_model->get_societies() : [];
        $recent    = $this->Visitor_model->get_recent_visitors($r['isSuperAdmin'] ? null : $r['society_id'], 5);

        $edit_visitor = null;
        $edit_id = (int) $this->input->get('edit_id');
        if ($edit_id && !$r['isOwner'] && !$r['isSecurity']) {
            $edit_visitor = $this->Visitor_model->get_visitor_by_id($edit_id);
        }

        $data = array_merge($r, [
            'title'          => 'Visitors',
            'activePage'     => 'visitors',
            'visitors'       => $visitors,
            'stats'          => $stats,
            'recent'         => $recent,
            'pagination'     => $pagination,
            'total_visitors' => $total_rows,
            'filters'        => $filters,
            'societies'      => $societies,
            'edit_visitor'   => $edit_visitor,
        ]);

        $this->load->view('header', $data);
        $this->load->view('visitors_view', $data);
    }

    public function add()
    {
        $r = $this->_role();

        if ($r['isOwner']) {
            $this->session->set_flashdata('error', 'Owners cannot add visitors directly.');
            redirect('visitors');
            return;
        }

        $this->form_validation->set_rules('visitor_name', 'Full Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('entry_time',   'Entry Time', 'required');
        $this->form_validation->set_rules('flat',         'Flat / Unit', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', strip_tags(validation_errors('', ' | ')));
            redirect('visitors');
            return;
        }

        $entry = $this->input->post('entry_time');

        $insert = [
            'society_id'      => $r['isSuperAdmin']
                                    ? ((int) $this->input->post('society_id') ?: null)
                                    : $r['society_id'],
            'visitor_name'    => $this->input->post('visitor_name', TRUE),
            'phone'           => $this->input->post('phone', TRUE),
            'flat'            => $this->input->post('flat', TRUE),
            'purpose'         => $this->input->post('purpose', TRUE),
            'entry_time'      => $entry ? str_replace('T', ' ', $entry) . ':00' : null,
            'exit_time'       => null,
            'status'          => 'Pending',
            'approval_status' => 'pending',
        ];

        $ok = $this->Visitor_model->insert_visitor($insert);
        $this->session->set_flashdata(
            $ok ? 'success' : 'error',
            $ok  ? 'Check-in request logged. Awaiting owner approval.'
                 : 'Failed to add visitor. Please try again.'
        );
        redirect('visitors');
    }

    public function edit($id)
    {
        $r = $this->_role();
        if ($r['isOwner'] || $r['isSecurity']) { redirect('visitors'); return; }
        redirect('visitors?edit_id=' . (int) $id);
    }

    public function update()
    {
        $r = $this->_role();
        if ($r['isOwner'] || $r['isSecurity']) {
            $this->session->set_flashdata('error', 'You do not have permission to edit visitors.');
            redirect('visitors');
            return;
        }

        $id = (int) $this->input->post('id');

        $this->form_validation->set_rules('visitor_name', 'Full Name', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('entry_time',   'Entry Time', 'required');
        $this->form_validation->set_rules('status',       'Status',     'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', strip_tags(validation_errors('', ' | ')));
            redirect('visitors?edit_id=' . $id);
            return;
        }

        $entry = $this->input->post('entry_time');
        $exit  = $this->input->post('exit_time');

        $upd = [
            'society_id'   => $r['isSuperAdmin']
                                ? ((int) $this->input->post('society_id') ?: null)
                                : $r['society_id'],
            'visitor_name' => $this->input->post('visitor_name', TRUE),
            'phone'        => $this->input->post('phone', TRUE),
            'flat'         => $this->input->post('flat', TRUE),
            'purpose'      => $this->input->post('purpose', TRUE),
            'entry_time'   => $entry ? str_replace('T', ' ', $entry) . ':00' : null,
            'exit_time'    => $exit  ? str_replace('T', ' ', $exit)  . ':00' : null,
            'status'       => $this->input->post('status', TRUE),
        ];

        $ok = $this->Visitor_model->update_visitor($id, $upd);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Visitor updated successfully.' : 'Failed to update visitor.');
        redirect('visitors');
    }

    public function delete($id)
    {
        $r = $this->_role();
        if ($r['isOwner'] || $r['isSecurity']) {
            $this->session->set_flashdata('error', 'You do not have permission to delete visitors.');
            redirect('visitors');
            return;
        }

        $ok = $this->Visitor_model->delete_visitor((int) $id);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Visitor deleted.' : 'Delete failed.');
        redirect('visitors');
    }

    public function approve($id)
    {
        $r = $this->_role();

        if ($r['isSecurity']) {
            $this->session->set_flashdata('error', 'Security staff cannot approve visitors.');
            redirect('visitors');
            return;
        }

        $visitor = $this->Visitor_model->get_visitor_by_id((int) $id);
        if (!$visitor) { show_404(); return; }

        if ($visitor['status'] === 'Checked Out') {
            $this->session->set_flashdata('error', 'This visitor has already checked out. Approval cannot be changed.');
            redirect('visitors');
            return;
        }

        $ok = $this->Visitor_model->approve_visitor((int) $id, $r['user_id']);
        $this->session->set_flashdata(
            $ok ? 'success' : 'error',
            $ok ? 'Visitor approved and marked as Checked In.' : 'Approval failed. Please try again.'
        );
        redirect('visitors');
    }

    public function reject($id)
    {
        $r = $this->_role();

        if ($r['isSecurity']) {
            $this->session->set_flashdata('error', 'Security staff cannot reject visitors.');
            redirect('visitors');
            return;
        }

        $visitor = $this->Visitor_model->get_visitor_by_id((int) $id);
        if (!$visitor) { show_404(); return; }

        if ($visitor['status'] === 'Checked Out') {
            $this->session->set_flashdata('error', 'This visitor has already checked out. Rejection cannot be applied.');
            redirect('visitors');
            return;
        }

        $reason = $this->input->post('rejection_reason', TRUE);
        $ok = $this->Visitor_model->reject_visitor((int) $id, $r['user_id'], $reason);
        $this->session->set_flashdata(
            $ok ? 'success' : 'error',
            $ok ? 'Visitor entry rejected.' : 'Rejection failed. Please try again.'
        );
        redirect('visitors');
    }

public function checkout($id)
{
    $r = $this->_role();

    if ($r['isOwner']) {
        $this->session->set_flashdata('error', 'Owners cannot check out visitors.');
        redirect('visitors');
        return;
    }

    $visitor = $this->Visitor_model->get_visitor_by_id((int) $id);
    if (!$visitor) {
        show_404();
        return;
    }

    $status  = strtolower(trim($visitor['status'] ?? ''));
    $approval = strtolower(trim($visitor['approval_status'] ?? ''));

    // already checked out
    if ($status === 'checked out') {
        $this->session->set_flashdata('error', 'This visitor has already checked out.');
        redirect('visitors');
        return;
    }

    // only approved + checked in visitors can be checked out
    if ($approval !== 'approved' || $status !== 'checked in') {
        $this->session->set_flashdata('error', 'Only approved / checked-in visitors can be checked out.');
        redirect('visitors');
        return;
    }

    $ok = $this->Visitor_model->checkout_visitor((int) $id);

    $this->session->set_flashdata(
        $ok ? 'success' : 'error',
        $ok ? 'Visitor checked out. Exit time recorded automatically.' : 'Checkout failed. Please try again.'
    );

    redirect('visitors');
}
    private function _build_pagination($current_page, $total_pages, $filters)
    {
        if ($total_pages <= 1) return '';

        $q   = array_filter($filters, fn($v) => $v !== '' && $v !== null);
        $url = fn($p) => site_url('visitors?' . http_build_query(array_merge($q, ['page' => $p])));

        $html  = '';

        if ($current_page > 1) {
            $html .= '<a href="' . $url($current_page - 1) . '"><i class="fas fa-chevron-left"></i> Prev</a>';
        }

        $start = max(1, $current_page - 2);
        $end   = min($total_pages, $current_page + 2);

        if ($start > 1) {
            $html .= '<a href="' . $url(1) . '">1</a>';
            if ($start > 2) $html .= '<span class="pg-ellipsis">…</span>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $html .= ($i === $current_page)
                ? '<strong>' . $i . '</strong>'
                : '<a href="' . $url($i) . '">' . $i . '</a>';
        }

        if ($end < $total_pages) {
            if ($end < $total_pages - 1) $html .= '<span class="pg-ellipsis">…</span>';
            $html .= '<a href="' . $url($total_pages) . '">' . $total_pages . '</a>';
        }

        if ($current_page < $total_pages) {
            $html .= '<a href="' . $url($current_page + 1) . '">Next <i class="fas fa-chevron-right"></i></a>';
        }

        return $html;
    }
}
