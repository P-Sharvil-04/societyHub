<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notice_controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notice_model');
        $this->load->helper(['form', 'url', 'download']);
        $this->load->library(['session', 'form_validation', 'email']);

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    public function index()
    {
        if ($this->input->post('action')) {
            switch ($this->input->post('action')) {
                case 'add':    $this->_add();    return;
                case 'edit':   $this->_edit();   return;
                case 'delete': $this->_delete(); return;
            }
        }

        $role_name         = $this->session->userdata('role_name');
        $session_society_id = $this->session->userdata('society_id');
        $isSuperAdmin      = ($role_name === 'super_admin');

        $raw_soc = $this->input->get('society_id', TRUE);
        $filters = [
            'search'     => $this->input->get('search', TRUE) ?: '',
            'type'       => $this->input->get('type',   TRUE) ?: '',
            'status'     => $this->input->get('status', TRUE) ?: '',
            'society_id' => ($raw_soc !== null && $raw_soc !== '') ? (int) $raw_soc : null,
        ];

        $society_id   = $isSuperAdmin ? $filters['society_id'] : (int) $session_society_id;
        $modelFilters = ['search' => $filters['search'], 'type' => $filters['type'], 'status' => $filters['status']];

        $notices  = $this->Notice_model->get_notices($society_id, $modelFilters);
        $stats    = $this->Notice_model->get_stats($society_id, $modelFilters);
        $recent   = $this->Notice_model->get_recent_notices($society_id, 5);
        $monthly  = $this->Notice_model->get_monthly_data($society_id);
        $societies = $isSuperAdmin ? $this->Notice_model->get_societies() : [];

        $data = [
            'title'       => 'Notices',
            'activePage'  => 'notices',
            'notices'     => $notices,
            'stats'       => $stats,
            'recent'      => $recent,
            'monthly'     => $monthly,
            'societies'   => $societies,
            'filters'     => $filters,
            'society_id'  => $society_id,
            'isSuperAdmin'=> $isSuperAdmin,
            '_modal'      => $this->session->flashdata('modal_state') ?: [],
            '_old'        => $this->session->flashdata('old') ?: [],
        ];

        $this->load->view('header', $data);
        $this->load->view('notices_view', $data);
    }

    private function _add()
    {
        $this->form_validation->set_rules('title',       'Title',       'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');
        $this->form_validation->set_rules('notice_type', 'Notice Type', 'required');
        $this->form_validation->set_rules('valid_until', 'Valid Until', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('modal_state', ['action' => 'add', 'open' => TRUE]);
            $this->session->set_flashdata('old', $this->input->post());
            redirect('notices');
            return;
        }

        $role_name          = $this->session->userdata('role_name');
        $session_society_id = $this->session->userdata('society_id');
        $isSuperAdmin       = ($role_name === 'super_admin');

        $raw_soc    = $this->input->post('society_id', TRUE);
        $society_id = $isSuperAdmin
            ? (($raw_soc !== null && $raw_soc !== '') ? (int) $raw_soc : null)
            : (int) $session_society_id;

        if ($isSuperAdmin && empty($society_id)) {
            $this->session->set_flashdata('error', 'Please select a society.');
            redirect('notices');
            return;
        }

        $insert = [
            'society_id'      => $society_id,
            'created_by'      => $this->session->userdata('user_id') ?: null,
            'notice_id'       => $this->Notice_model->generate_notice_id(),
            'title'           => $this->input->post('title',           TRUE),
            'description'     => $this->input->post('description',     TRUE),
            'notice_type'     => $this->input->post('notice_type',     TRUE),
            'valid_until'     => $this->input->post('valid_until',     TRUE),
            'status'          => $this->input->post('status',          TRUE) ?: 'active',
            'target_audience' => $this->input->post('target_audience', TRUE) ?: 'all',
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $notice_id = $this->Notice_model->add_notice($insert);

        if ($notice_id) {
            $notice = $this->Notice_model->get_notice($notice_id);

            $this->Notice_model->insert_notification([
                'society_id' => $notice['society_id'],
                'notice_id'  => $notice['id'],
                'title'      => $notice['title'],
                'message'    => $notice['description'],
                'is_read'    => 0,
            ]);

            $this->_push_to_python($notice);
            $this->_send_notice_email($notice);
        }

        $this->session->set_flashdata(
            $notice_id ? 'success' : 'error',
            $notice_id ? 'Notice created successfully.' : 'Failed to save notice.'
        );
        redirect('notices');
    }

    private function _edit()
    {
        $id = (int) $this->input->post('id');
        if (!$id) { $this->session->set_flashdata('error', 'Invalid notice ID.'); redirect('notices'); return; }

        $this->form_validation->set_rules('title',       'Title',       'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');
        $this->form_validation->set_rules('notice_type', 'Notice Type', 'required');
        $this->form_validation->set_rules('valid_until', 'Valid Until', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('modal_state', ['action' => 'edit', 'id' => $id, 'open' => TRUE]);
            $this->session->set_flashdata('old', $this->input->post());
            redirect('notices');
            return;
        }

        $upd = [
            'title'           => $this->input->post('title',           TRUE),
            'description'     => $this->input->post('description',     TRUE),
            'notice_type'     => $this->input->post('notice_type',     TRUE),
            'valid_until'     => $this->input->post('valid_until',     TRUE),
            'status'          => $this->input->post('status',          TRUE) ?: 'active',
            'target_audience' => $this->input->post('target_audience', TRUE) ?: 'all',
        ];

        $ok = $this->Notice_model->edit_notice($id, $upd);

        if ($ok) {
            $notice = $this->Notice_model->get_notice($id);
            $this->_push_to_python($notice);
        }

        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Notice updated.' : 'Failed to update notice.');
        redirect('notices');
    }

    private function _delete()
    {
        $id = (int) $this->input->post('id');
        if (!$id) { $this->session->set_flashdata('error', 'Invalid notice ID.'); redirect('notices'); return; }

        if ($this->input->post('confirm') !== 'yes') {
            $this->session->set_flashdata('modal_state', ['action' => 'delete', 'id' => $id, 'open' => TRUE]);
            redirect('notices');
            return;
        }

        $ok = $this->Notice_model->remove_notice($id);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Notice deleted.' : 'Failed to delete.');
        redirect('notices');
    }

    public function unread_notifications()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $society_id = (int) $this->session->userdata('society_id');
        $rows       = $this->Notice_model->get_unread_notifications($society_id);

        $this->_json(['success' => true, 'count' => count($rows), 'data' => $rows]);
    }

    public function mark_read()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $id = (int) $this->input->post('id');
        if (!$id) { $this->_json(['success' => false, 'message' => 'Invalid ID']); return; }

        $ok = $this->Notice_model->mark_notification_read($id);
        $this->_json(['success' => (bool) $ok]);
    }

    public function mark_all_read()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $society_id = (int) $this->session->userdata('society_id');
        $ok = $this->Notice_model->mark_all_notifications_read($society_id);
        $this->_json(['success' => (bool) $ok]);
    }
    public function filter_ajax()
    {
        if (!$this->input->is_ajax_request()) { show_404(); }

        $role_name          = $this->session->userdata('role_name');
        $session_society_id = $this->session->userdata('society_id');
        $isSuperAdmin       = ($role_name === 'super_admin');

        $society_id = $isSuperAdmin
            ? ($this->input->post('society_id', true) ?: null)
            : (int) $session_society_id;

        $filters = [
            'search' => $this->input->post('search', true) ?: '',
            'type'   => $this->input->post('type',   true) ?: '',
            'status' => $this->input->post('status', true) ?: '',
        ];

        $notices  = $this->Notice_model->get_notices($society_id, $filters);
        $gridHtml = '';
        $tableHtml = '';

        if (empty($notices)) {
            $gridHtml  = '<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-light);"><i class="fas fa-bell-slash" style="font-size:3rem;display:block;margin-bottom:12px;"></i>No notices found.</div>';
            $tableHtml = '<tr><td colspan="' . ($isSuperAdmin ? 8 : 7) . '" style="text-align:center;padding:40px;">No notices found</td></tr>';
        } else {
            foreach ($notices as $n) {
                $typeIcon    = $this->_typeIcon($n['notice_type']);
                $createdDate = date('d M Y', strtotime($n['created_at']));
                $validUntil  = !empty($n['valid_until']) ? date('d M Y', strtotime($n['valid_until'])) : '—';
                $json        = htmlspecialchars(json_encode($n), ENT_QUOTES, 'UTF-8');

                $gridHtml .= '<div class="notice-card ' . html_escape($n['notice_type']) . '">
                    <div class="notice-header">
                        <div class="notice-type"><i class="fas ' . $typeIcon . '"></i><h4>' . ucfirst(html_escape($n['notice_type'])) . '</h4></div>
                        <span class="notice-status ' . html_escape($n['status']) . '">' . strtoupper($n['status']) . '</span>
                    </div>
                    <div class="notice-title">' . html_escape($n['title']) . '</div>
                    <div class="notice-description">' . html_escape(substr($n['description'], 0, 120)) . '...</div>';

                if ($isSuperAdmin && !empty($n['society_name'])) {
                    $gridHtml .= '<div style="margin:6px 0;"><span class="notice-society-badge"><i class="fas fa-city"></i> ' . html_escape($n['society_name']) . '</span></div>';
                }

                $gridHtml .= '<div class="notice-meta">
                        <span><i class="fas fa-calendar-plus"></i> ' . $createdDate . '</span>
                        <span><i class="fas fa-hourglass-end"></i> ' . $validUntil . '</span>
                        <span><i class="fas fa-users"></i> ' . ucfirst(html_escape($n['target_audience'])) . '</span>
                    </div>
                    <div class="notice-footer">
                        <div class="notice-actions" style="margin-left:auto;display:flex;gap:6px;">
                            <button type="button" class="btn-icon" title="View"   onclick="viewNotice(' . $json . ')"><i class="fas fa-eye"></i></button>
                            <button type="button" class="btn-icon" title="Edit"   onclick="editNotice(' . (int) $n['id'] . ', ' . $json . ')"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn-icon delete" title="Delete" onclick="openDeleteModal(' . (int) $n['id'] . ')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>';

                $tableHtml .= '<tr>
                    <td><strong>' . html_escape($n['notice_id']) . '</strong></td>
                    <td><strong>' . html_escape($n['title']) . '</strong><br><span style="font-size:.72rem;color:var(--text-light);">' . html_escape(substr($n['description'], 0, 60)) . '...</span></td>
                    <td><span style="padding:3px 10px;font-size:12px;font-weight:600;text-transform:capitalize;background:rgba(52,152,219,.15);color:#3498db;border-radius:12px;display:inline-block;">' . html_escape($n['notice_type']) . '</span></td>';

                if ($isSuperAdmin) {
                    $tableHtml .= '<td>' . (!empty($n['society_name']) ? '<span class="notice-society-badge"><i class="fas fa-city"></i> ' . html_escape($n['society_name']) . '</span>' : '<span style="color:var(--text-light);">—</span>') . '</td>';
                }

                $tableHtml .= '<td>' . $validUntil . '</td>
                    <td>' . ucfirst(html_escape($n['target_audience'])) . '</td>
                    <td><span class="notice-status ' . html_escape($n['status']) . '">' . strtoupper($n['status']) . '</span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button type="button" class="btn-icon" title="View"   onclick="viewNotice(' . $json . ')"><i class="fas fa-eye"></i></button>
                            <button type="button" class="btn-icon" title="Edit"   onclick="editNotice(' . (int) $n['id'] . ', ' . $json . ')"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn-icon delete" title="Delete" onclick="openDeleteModal(' . (int) $n['id'] . ')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>';
            }
        }

        $this->_json(['gridHtml' => $gridHtml, 'tableHtml' => $tableHtml, 'count' => count($notices)]);
    }
    public function export()
    {
        $role_name          = $this->session->userdata('role_name');
        $session_society_id = $this->session->userdata('society_id');
        $isSuperAdmin       = ($role_name === 'super_admin');

        $raw_soc    = $this->input->get('society_id', TRUE);
        $society_id = $isSuperAdmin ? (($raw_soc !== null && $raw_soc !== '') ? (int) $raw_soc : null) : (int) $session_society_id;

        $filters = [
            'search' => $this->input->get('search', TRUE) ?: '',
            'type'   => $this->input->get('type',   TRUE) ?: '',
            'status' => $this->input->get('status', TRUE) ?: '',
        ];

        $notices = $this->Notice_model->get_notices($society_id, $filters);
        $csv = "Notice ID,Title,Type,Society,Valid Until,Status,Target Audience\n";
        foreach ($notices as $n) {
            $csv .= implode(',', [
                $n['notice_id'],
                '"' . str_replace('"', '""', $n['title']) . '"',
                $n['notice_type'],
                '"' . str_replace('"', '""', $n['society_name'] ?? '') . '"',
                $n['valid_until'] ?? '',
                $n['status'],
                $n['target_audience'],
            ]) . "\n";
        }
        force_download('notices_' . date('Y-m-d') . '.csv', $csv);
    }

    private function _typeIcon($type)
    {
        $map = ['important' => 'fa-exclamation-circle', 'event' => 'fa-calendar-alt', 'maintenance' => 'fa-tools'];
        return $map[$type] ?? 'fa-bullhorn';
    }

    private function _json($data)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    private function _push_to_python($notice)
    {
        if (empty($notice['society_id'])) { log_message('error', 'Push skipped: society_id missing'); return; }

        $payload = [
            'society_id' => (string) $notice['society_id'],
            'notice_id'  => $notice['notice_id'],
            'title'      => $notice['title'],
            'message'    => $notice['description'],
            'created_at' => $notice['created_at'],
        ];

        $ch = curl_init('http://192.168.1.164:5000/api/notice/push');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 5,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);
        log_message('info', 'Push response: ' . $response . ' | error: ' . $error);
    }
    private function _send_notice_email($notice)
    {
        $users = $this->db->where('society_id', $notice['society_id'])->get('users')->result_array();
        foreach ($users as $u) {
            if (empty($u['email'])) continue;
            $this->email->clear(TRUE);
            $this->email->from('sharvil.tmbs25@gmail.com', 'Society');
            $this->email->to($u['email']);
            $this->email->subject($notice['title']);
            $this->email->message("
                <h3>{$notice['title']}</h3>
                <p>{$notice['description']}</p>
                <br><small>This is an automated notice from Society Management System</small>
            ");
			
            if (!$this->email->send()) {
                log_message('error', $this->email->print_debugger());
            }
        }
    }
}
