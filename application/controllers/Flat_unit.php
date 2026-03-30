<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Flat_unit extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Flat_unit_model', 'flat_model');
        $this->load->helper(['url', 'form', 'html']);
        $this->load->library(['session', 'form_validation']);
        $this->_auth_check();
    }

    private function _auth_check(): void
    {
        if (!$this->session->userdata('user_id')) redirect('login');
    }

    private function _society_id(): int
    {
        return (int)$this->session->userdata('society_id');
    }

    private function _is_super_admin(): bool
    {
        $role = strtolower((string)($this->session->userdata('role_name') ?? ''));
        return in_array($role, ['super_admin', 'superadmin', 'super admin']);
    }

    /* ── ordinal() helper (available in view too) ── */
    private function _ensure_ordinal(): void
    {
        if (!function_exists('ordinal')) {
            function ordinal(int $n): string {
                $s = ['th','st','nd','rd']; $v = $n % 100;
                return $n . ($s[($v-20)%10] ?? $s[$v] ?? $s[0]);
            }
        }
    }

    /* ════════════════════════════════════════════════
       INDEX
    ════════════════════════════════════════════════ */
    public function index(): void
    {
        $isSuperAdmin = $this->_is_super_admin();
        $societyId    = $this->_society_id();

        /* Collect GET filters */
        $filters = [
            'society_id' => (int)($this->input->get('society_id') ?? 0),
            'wing_id'    => (int)($this->input->get('wing_id')    ?? 0),
            'floor'      => $this->input->get('floor')            ?? '',
            'flat_type'  => trim($this->input->get('flat_type')   ?? ''),
            'status'     => trim($this->input->get('status')      ?? ''),
            'search'     => trim($this->input->get('search')      ?? ''),
        ];

        if (!$isSuperAdmin) {
            $filters['society_id'] = $societyId;
        } elseif (empty($filters['society_id'])) {
            $filters['society_id'] = 0;
        }

        $flats             = $this->flat_model->get_flats($filters, $isSuperAdmin);
        $floorList         = $this->flat_model->get_floors($isSuperAdmin ? 0 : $societyId);
        $wings             = $this->flat_model->get_wings($isSuperAdmin  ? 0 : $societyId);
        $societies         = $isSuperAdmin ? $this->flat_model->get_societies() : [];
        $unassignedMembers = $this->flat_model->get_unassigned_members($isSuperAdmin ? 0 : $societyId);

        $statsFilters = $isSuperAdmin
            ? ['society_id' => $filters['society_id']]
            : ['society_id' => $societyId];

        $stats = $this->flat_model->get_stats($statsFilters);

        $totalFlats    = (int)$stats['total'];
        $occupied      = (int)$stats['occupied'];
        $vacant        = (int)$stats['vacant'];
        $blocked       = (int)$stats['blocked'];
        $ownerOccupied = (int)$stats['owner_occupied'];
        $newThisMonth  = (int)$stats['new_this_month'];

        $safe            = max($totalFlats, 1);
        $occupiedPercent = round(($occupied      / $safe) * 100);
        $vacantPercent   = round(($vacant        / $safe) * 100);
        $blockedPercent  = round(($blocked       / $safe) * 100);
        $ownerPercent    = round(($ownerOccupied / $safe) * 100);

        $this->_ensure_ordinal();

        $data = [
            'title'             => 'Flat / Unit Management',
            'flats'             => $flats,
            'floorList'         => $floorList,
            'wings'             => $wings,
            'societies'         => $societies,
            'unassignedMembers' => $unassignedMembers,
            'filters'           => $filters,
            'isSuperAdmin'      => $isSuperAdmin,
            'totalFlats'        => $totalFlats,
            'occupied'          => $occupied,
            'vacant'            => $vacant,
            'blocked'           => $blocked,
            'ownerOccupied'     => $ownerOccupied,
            'newThisMonth'      => $newThisMonth,
            'occupiedPercent'   => $occupiedPercent,
            'vacantPercent'     => $vacantPercent,
            'blockedPercent'    => $blockedPercent,
            'ownerPercent'      => $ownerPercent,
        ];

        $this->load->view('header', $data);
        $this->load->view('flat_unit', $data);
    }

    /* ════════════════════════════════════════════════
       SAVE (Add + Edit)
    ════════════════════════════════════════════════ */
    public function save(): void
    {
        if ($this->input->method() !== 'post') redirect('flat_unit');

        $flatId    = (int)$this->input->post('flatId');
        $societyId = $this->_society_id();
        $isSuper   = $this->_is_super_admin();

        $this->form_validation->set_rules('flat_no',   'Flat Number', 'required|trim|max_length[20]');
        $this->form_validation->set_rules('floor',     'Floor',       'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('flat_type', 'Flat Type',   'required|trim');
        $this->form_validation->set_rules('status',    'Status',      'required|in_list[occupied,vacant,blocked]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('flat_unit');
        }

        /* Status comes in as string from form → convert to tinyint for DB */
        $statusMap = ['occupied' => 0, 'vacant' => 1, 'blocked' => 2];
        $statusInt = $statusMap[$this->input->post('status', TRUE)] ?? 1;

        $data = [
            'flat_no'      => $this->input->post('flat_no',      TRUE),
            'wing_id'      => (int)$this->input->post('wing_id') ?: null,
            'floor'        => (int)$this->input->post('floor'),
            'flat_type'    => $this->input->post('flat_type',    TRUE),
            'area_sqft'    => (int)$this->input->post('area_sqft') ?: null,
            'status'       => $statusInt,
            'parking_slot' => $this->input->post('parking_slot', TRUE) ?: null,
            'remarks'      => $this->input->post('remarks',      TRUE) ?: null,
        ];

        if ($flatId > 0) {
            if (!$isSuper) {
                $existing = $this->flat_model->get_flat_by_id($flatId);
                if (!$existing || (int)$existing->society_id !== $societyId) {
                    $this->session->set_flashdata('error', 'Unauthorized action.');
                    redirect('flat_unit');
                }
            }
            $data['updated_at'] = date('Y-m-d H:i:s');
            $ok  = $this->flat_model->update_flat($flatId, $data);
            $msg = $ok ? 'Flat updated successfully.' : 'Failed to update flat.';
        } else {
            $data['society_id'] = $societyId;
            $data['created_at'] = date('Y-m-d H:i:s');
            if ($this->flat_model->flat_exists($data['flat_no'], $data['wing_id'], $societyId)) {
                $this->session->set_flashdata('error', 'Flat number already exists in this wing/society.');
                redirect('flat_unit');
            }
            $ok  = $this->flat_model->insert_flat($data);
            $msg = $ok ? 'Flat added successfully.' : 'Failed to add flat.';
        }

        $this->session->set_flashdata($ok ? 'success' : 'error', $msg);
        redirect('flat_unit');
    }

    /* ════════════════════════════════════════════════
       DELETE
    ════════════════════════════════════════════════ */
    public function delete(int $flatId = 0): void
    {
        $flatId    = (int)$flatId;
        $societyId = $this->_society_id();
        $isSuper   = $this->_is_super_admin();

        if (!$flatId) redirect('flat_unit');

        if (!$isSuper) {
            $flat = $this->flat_model->get_flat_by_id($flatId);
            if (!$flat || (int)$flat->society_id !== $societyId) {
                $this->session->set_flashdata('error', 'Unauthorized action.');
                redirect('flat_unit');
            }
        }

        $ok = $this->flat_model->delete_flat($flatId);
        $this->session->set_flashdata(
            $ok ? 'success' : 'error',
            $ok ? 'Flat deleted successfully.' : 'Failed to delete flat.'
        );
        redirect('flat_unit');
    }

    /* ════════════════════════════════════════════════
       ASSIGN RESIDENT
    ════════════════════════════════════════════════ */
    public function assign_resident(): void
    {
        $this->form_validation->set_rules('flat_id',   'Flat',   'required|integer');
        $this->form_validation->set_rules('member_id', 'Member', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('flat_unit');
        }

        $flatId     = (int)$this->input->post('flat_id');
        $memberId   = (int)$this->input->post('member_id');
        $moveInDate = $this->input->post('move_in_date', TRUE);
        $societyId  = $this->_society_id();

        if (!$this->_is_super_admin()) {
            $flat = $this->flat_model->get_flat_by_id($flatId);
            if (!$flat || (int)$flat->society_id !== $societyId) {
                $this->session->set_flashdata('error', 'Unauthorized action.');
                redirect('flat_unit');
            }
        }

        $ok = $this->flat_model->assign_resident($flatId, $memberId, $moveInDate);
        $this->session->set_flashdata(
            $ok ? 'success' : 'error',
            $ok ? 'Resident assigned successfully.' : 'Failed to assign resident.'
        );
        redirect('flat_unit');
    }

    /* ════════════════════════════════════════════════
       IMPORT CSV
    ════════════════════════════════════════════════ */
    public function import_csv(): void
    {
        $societyId = $this->_society_id();

        if (empty($_FILES['csv_file']['name'])) {
            $this->session->set_flashdata('error', 'Please select a CSV file.');
            redirect('flat_unit'); return;
        }
        if (strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION)) !== 'csv') {
            $this->session->set_flashdata('error', 'Only .csv files are allowed.');
            redirect('flat_unit'); return;
        }

        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) {
            $this->session->set_flashdata('error', 'Could not read the uploaded file.');
            redirect('flat_unit'); return;
        }

        $headers  = array_map('strtolower', array_map('trim', fgetcsv($handle)));
        $required = ['flat_no', 'floor', 'flat_type', 'status'];
        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                fclose($handle);
                $this->session->set_flashdata('error', "CSV missing required column: $req");
                redirect('flat_unit'); return;
            }
        }

        $statusMap   = ['occupied' => 0, 'vacant' => 1, 'blocked' => 2];
        $wingsCache  = [];
        $wingRows    = $this->flat_model->get_wings($societyId);
        foreach ($wingRows as $w) {
            $wingsCache[strtolower(trim($w->wing_name))] = (int)$w->id;
        }

        $inserted = $skipped = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($headers)) continue;
            $r = array_combine($headers, array_map('trim', $row));

            $wingId = null;
            if (!empty($r['wing_name'])) {
                $wingId = $wingsCache[strtolower($r['wing_name'])] ?? null;
            }

            $statusStr = strtolower($r['status'] ?? 'vacant');
            if (!array_key_exists($statusStr, $statusMap)) $statusStr = 'vacant';

            if ($this->flat_model->flat_exists($r['flat_no'], $wingId, $societyId)) {
                $skipped++; continue;
            }

            $this->flat_model->insert_flat([
                'society_id'   => $societyId,
                'wing_id'      => $wingId,
                'flat_no'      => substr($r['flat_no'], 0, 20),
                'floor'        => (int)($r['floor'] ?? 0),
                'flat_type'    => $r['flat_type'] ?? '2BHK',
                'area_sqft'    => !empty($r['area_sqft'])    ? (int)$r['area_sqft']    : null,
                'status'       => $statusMap[$statusStr],
                'parking_slot' => !empty($r['parking_slot']) ? $r['parking_slot']      : null,
                'remarks'      => !empty($r['remarks'])      ? $r['remarks']            : null,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $inserted++;
        }
        fclose($handle);

        $this->session->set_flashdata('success',
            "Import complete — $inserted flat(s) added, $skipped duplicate(s) skipped.");
        redirect('flat_unit');
    }
}
