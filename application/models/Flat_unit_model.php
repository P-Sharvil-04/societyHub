<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Flat_unit_model
 *
 * flats.status tinyint convention:
 *   0 = occupied
 *   1 = vacant
 *   2 = blocked
 */
class Flat_unit_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /* ── Status helpers ───────────────────────────────────────── */

    private function _status_to_int(string $s): int
    {
        return match(strtolower($s)) {
            'occupied' => 0,
            'blocked'  => 2,
            default    => 1,
        };
    }

    private function _status_case(string $alias = 'f'): string
    {
        return "CASE {$alias}.status WHEN 0 THEN 'occupied' WHEN 2 THEN 'blocked' ELSE 'vacant' END AS status";
    }

    /* ── Shared WHERE builder (used by get_flats + count_flats) ── */
    private function _apply_filters(array $filters, bool $isSuper): void
    {
        if (!$isSuper || !empty($filters['society_id'])) {
            $this->db->where('f.society_id', (int)($filters['society_id'] ?? 0));
        }
        if (!empty($filters['wing_id'])) {
            $this->db->where('f.wing_id', (int)$filters['wing_id']);
        }
        if ($filters['floor'] !== '' && $filters['floor'] !== null) {
            $this->db->where('f.floor', (int)$filters['floor']);
        }
        if (!empty($filters['flat_type'])) {
            $this->db->where('f.flat_type', $filters['flat_type']);
        }
        if (!empty($filters['status'])) {
            $statusInt = is_numeric($filters['status'])
                ? (int)$filters['status']
                : $this->_status_to_int($filters['status']);
            $this->db->where('f.status', $statusInt);
        }
        if (!empty($filters['search'])) {
            $q = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                ->like('f.flat_no',    $q)
                ->or_like('m.name',    $q)
                ->or_like('w.wing_name', $q)
                ->or_like('m.phone',   $q)
            ->group_end();
        }
    }

    /* ══════════════════════════════════════════════
       READ — All flats (paginated)
    ══════════════════════════════════════════════ */
    public function get_flats(array $filters = [], bool $isSuper = false, int $limit = 0, int $offset = 0): array
    {
        $this->db->select("
            f.id,
            f.society_id,
            f.wing_id,
            f.flat_no,
            f.floor,
            f.flat_type,
            f.area_sqft,
            f.parking_slot,
            f.remarks,
            f.created_at,
            w.wing_name,
            s.name          AS society_name,
            m.id            AS member_id,
            m.name          AS resident_name,
            m.member_type,
            m.phone         AS resident_phone,
            m.email         AS resident_email,
            " . $this->_status_case('f'), FALSE);

        $this->db->from('flats f');
        $this->db->join('wings    w', 'w.id = f.wing_id',    'left');
        $this->db->join('societies s', 's.id = f.society_id', 'left');
        $this->db->join(
            'users m',
            'm.flat_no    = f.flat_no
             AND (m.wing_id = f.wing_id OR (m.wing_id IS NULL AND f.wing_id IS NULL))
             AND m.society_id = f.society_id
             AND m.status = 1',
            'left'
        );

        $this->_apply_filters($filters, $isSuper);
        $this->db->order_by('f.floor ASC, f.flat_no ASC');

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    /* ══════════════════════════════════════════════
       COUNT — Total matching flats (for pagination)
    ══════════════════════════════════════════════ */
    public function count_flats(array $filters = [], bool $isSuper = false): int
    {
        $this->db->from('flats f');
        $this->db->join('wings    w', 'w.id = f.wing_id',    'left');
        $this->db->join('societies s', 's.id = f.society_id', 'left');
        $this->db->join(
            'users m',
            'm.flat_no    = f.flat_no
             AND (m.wing_id = f.wing_id OR (m.wing_id IS NULL AND f.wing_id IS NULL))
             AND m.society_id = f.society_id
             AND m.status = 1',
            'left'
        );
        $this->_apply_filters($filters, $isSuper);
        return (int)$this->db->count_all_results();
    }

    /* ══════════════════════════════════════════════
       READ — Single flat
    ══════════════════════════════════════════════ */
    public function get_flat_by_id(int $flatId): ?object
    {
        return $this->db->get_where('flats', ['id' => $flatId])->row() ?: null;
    }

    /* ══════════════════════════════════════════════
       READ — Distinct floor list
    ══════════════════════════════════════════════ */
    public function get_floors(int $societyId = 0): array
    {
        $this->db->select('floor')->from('flats')->order_by('floor ASC');
        if ($societyId > 0) $this->db->where('society_id', $societyId);
        return array_column($this->db->get()->result(), 'floor');
    }

    /* ══════════════════════════════════════════════
       READ — Wings
    ══════════════════════════════════════════════ */
    public function get_wings(int $societyId = 0): array
    {
        $this->db->select('id, wing_name')->from('wings')->order_by('wing_name ASC');
        if ($societyId > 0) $this->db->where('society_id', $societyId);
        return $this->db->get()->result();
    }

    /* ══════════════════════════════════════════════
       READ — Societies (super-admin)
    ══════════════════════════════════════════════ */
    public function get_societies(): array
    {
        return $this->db->select('id, name')->from('societies')->order_by('name ASC')->get()->result();
    }

    /* ══════════════════════════════════════════════
       READ — Members with no occupied flat
    ══════════════════════════════════════════════ */
    public function get_unassigned_members(int $societyId = 0): array
    {
        $this->db->select('m.id, m.name, m.flat_no, m.member_type');
        $this->db->from('users m');
        $this->db->where('m.status', 1);
        if ($societyId > 0) $this->db->where('m.society_id', $societyId);
        $this->db->where(
            "NOT EXISTS (
                SELECT 1 FROM flats f2
                WHERE f2.flat_no      = m.flat_no
                  AND (f2.wing_id     = m.wing_id OR (f2.wing_id IS NULL AND m.wing_id IS NULL))
                  AND f2.society_id   = m.society_id
                  AND f2.status       = 0
            )", NULL, FALSE
        );
        $this->db->order_by('m.name ASC');
        return $this->db->get()->result();
    }

    /* ══════════════════════════════════════════════
       STATS
    ══════════════════════════════════════════════ */
    public function get_stats(array $filters = []): array
    {
        $societyId = (int)($filters['society_id'] ?? 0);

        $this->db->select('COUNT(*) AS cnt')->from('flats f');
        if ($societyId > 0) $this->db->where('f.society_id', $societyId);
        $total = (int)$this->db->get()->row()->cnt;

        $this->db->select('status, COUNT(*) AS cnt')->from('flats f');
        if ($societyId > 0) $this->db->where('f.society_id', $societyId);
        $this->db->group_by('status');
        $counts = ['occupied' => 0, 'vacant' => 0, 'blocked' => 0];
        foreach ($this->db->get()->result() as $r) {
            if ((int)$r->status === 0) $counts['occupied'] = (int)$r->cnt;
            if ((int)$r->status === 1) $counts['vacant']   = (int)$r->cnt;
            if ((int)$r->status === 2) $counts['blocked']  = (int)$r->cnt;
        }

        $this->db->select('COUNT(*) AS cnt')->from('flats f');
        $this->db->join(
            'users m',
            "m.flat_no    = f.flat_no
             AND (m.wing_id = f.wing_id OR (m.wing_id IS NULL AND f.wing_id IS NULL))
             AND m.society_id = f.society_id
             AND m.status = 1
             AND m.member_type = 'owner'", 'inner'
        );
        $this->db->where('f.status', 0);
        if ($societyId > 0) $this->db->where('f.society_id', $societyId);
        $ownerOcc = (int)$this->db->get()->row()->cnt;

        $this->db->select('COUNT(*) AS cnt')->from('flats f');
        $this->db->where('MONTH(f.created_at)', date('n'));
        $this->db->where('YEAR(f.created_at)',  date('Y'));
        if ($societyId > 0) $this->db->where('f.society_id', $societyId);
        $newMonth = (int)$this->db->get()->row()->cnt;

        return [
            'total'          => $total,
            'occupied'       => $counts['occupied'],
            'vacant'         => $counts['vacant'],
            'blocked'        => $counts['blocked'],
            'owner_occupied' => $ownerOcc,
            'new_this_month' => $newMonth,
        ];
    }

    /* ══════════════════════════════════════════════
       WRITE — Insert flat
    ══════════════════════════════════════════════ */
    public function insert_flat(array $data): bool
    {
        if (isset($data['status']) && !is_numeric($data['status'])) {
            $data['status'] = $this->_status_to_int($data['status']);
        }
        return $this->db->insert('flats', $data);
    }

    /* ══════════════════════════════════════════════
       WRITE — Update flat
    ══════════════════════════════════════════════ */
    public function update_flat(int $id, array $data): bool
    {
        if (isset($data['status']) && !is_numeric($data['status'])) {
            $data['status'] = $this->_status_to_int($data['status']);
        }
        $this->db->where('id', $id);
        return $this->db->update('flats', $data);
    }

    /* ══════════════════════════════════════════════
       WRITE — Delete flat
    ══════════════════════════════════════════════ */
    public function delete_flat(int $id): bool
    {
        $this->db->where('id', $id);
        return $this->db->delete('flats');
    }

    /* ══════════════════════════════════════════════
       WRITE — Assign resident
    ══════════════════════════════════════════════ */
    public function assign_resident(int $flatId, int $memberId, string $moveInDate = ''): bool
    {
        $flat = $this->get_flat_by_id($flatId);
        if (!$flat) return false;

        $this->db->trans_start();

        $this->db->where('id', $flatId);
        $this->db->update('flats', ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

        $memberUpd = ['flat_no' => $flat->flat_no, 'wing_id' => $flat->wing_id];
        if (!empty($moveInDate)) $memberUpd['move_in_date'] = $moveInDate;
        $this->db->where('id', $memberId);
        $this->db->update('users', $memberUpd);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /* ══════════════════════════════════════════════
       UTIL — Duplicate check
    ══════════════════════════════════════════════ */
    public function flat_exists(string $flatNo, ?int $wingId, int $societyId, int $excludeId = 0): bool
    {
        $this->db->where('flat_no',    $flatNo);
        $this->db->where('society_id', $societyId);
        if ($wingId) {
            $this->db->where('wing_id', $wingId);
        } else {
            $this->db->where('wing_id IS NULL', NULL, FALSE);
        }
        if ($excludeId > 0) $this->db->where('id !=', $excludeId);
        return $this->db->count_all_results('flats') > 0;
    }
}
