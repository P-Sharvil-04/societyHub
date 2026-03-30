<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_member_model extends CI_Model
{
    const COMMITTEE_ROLE_IDS = [2, 3, 4, 5];

    /* ════════════════════════════════════════════════
     *  ROLES
     * ════════════════════════════════════════════════ */
    public function get_roles()
    {
        return $this->db->where_in('id', self::COMMITTEE_ROLE_IDS)->get('roles')->result();
    }

    /* ════════════════════════════════════════════════
     *  FILTERED MEMBERS LIST
     * ════════════════════════════════════════════════ */
    public function get_filtered($filters = [])
    {
        $role_ids = implode(',', self::COMMITTEE_ROLE_IDS);

        $sql = "
            SELECT
                u.id, u.name, u.email, u.phone, u.flat_no,
                u.member_type, u.status, u.created_at,
                u.society_id, u.wing_id,
                s.name           AS society_name,
                w.wing_name      AS wing_name,
                r_comm.role_name AS committee_role,
                r_comm.id        AS committee_role_id
            FROM users u
            LEFT JOIN societies  s       ON s.id            = u.society_id
            LEFT JOIN wings      w       ON w.id            = u.wing_id
            LEFT JOIN user_roles ur_comm ON ur_comm.user_id = u.id
                                        AND ur_comm.role_id IN ({$role_ids})
            LEFT JOIN roles      r_comm  ON r_comm.id       = ur_comm.role_id
            WHERE (u.member_type IN ('owner','tenant') OR ur_comm.role_id IS NOT NULL)
        ";

        $binds = [];
        if (!empty($filters['society_id']))       { $sql .= " AND u.society_id = ?";    $binds[] = (int)$filters['society_id']; }
        if (!empty($filters['wing_id']))           { $sql .= " AND u.wing_id = ?";       $binds[] = (int)$filters['wing_id']; }
        if (!empty($filters['member_type']))       { $sql .= " AND u.member_type = ?";   $binds[] = $filters['member_type']; }
        if (!empty($filters['role']))              { $sql .= " AND r_comm.role_name = ?";$binds[] = $filters['role']; }
        if (isset($filters['status']) && $filters['status'] !== '') { $sql .= " AND u.status = ?"; $binds[] = (int)$filters['status']; }
        if (!empty($filters['search'])) {
            $like = '%' . $this->db->escape_like_str($filters['search']) . '%';
            $sql .= " AND (u.name LIKE ? OR u.flat_no LIKE ? OR u.phone LIKE ?)";
            $binds[] = $like; $binds[] = $like; $binds[] = $like;
        }
        $sql .= " ORDER BY s.name ASC, CASE WHEN LOWER(r_comm.role_name)='chairman' THEN 0 ELSE 1 END ASC, u.name ASC";

        return $this->db->query($sql, $binds)->result();
    }

    /* ════════════════════════════════════════════════
     *  STATS
     * ════════════════════════════════════════════════ */
    public function get_stats($filters = [])
    {
        $role_ids = implode(',', self::COMMITTEE_ROLE_IDS);
        $base = "
            SELECT u.member_type, u.status, u.created_at, r_comm.role_name AS committee_role
            FROM users u
            LEFT JOIN user_roles ur_comm ON ur_comm.user_id = u.id AND ur_comm.role_id IN ({$role_ids})
            LEFT JOIN roles      r_comm  ON r_comm.id = ur_comm.role_id
            WHERE (u.member_type IN ('owner','tenant') OR ur_comm.role_id IS NOT NULL)
        ";
        $binds = [];
        if (!empty($filters['society_id']))  { $base .= " AND u.society_id = ?"; $binds[] = (int)$filters['society_id']; }
        if (!empty($filters['wing_id']))     { $base .= " AND u.wing_id = ?";    $binds[] = (int)$filters['wing_id']; }
        if (!empty($filters['member_type'])) { $base .= " AND u.member_type = ?";$binds[] = $filters['member_type']; }
        if (!empty($filters['role']))        { $base .= " AND r_comm.role_name = ?"; $binds[] = $filters['role']; }
        if (isset($filters['status']) && $filters['status'] !== '') { $base .= " AND u.status = ?"; $binds[] = (int)$filters['status']; }
        if (!empty($filters['search'])) {
            $like = '%' . $this->db->escape_like_str($filters['search']) . '%';
            $base .= " AND (u.name LIKE ? OR u.flat_no LIKE ? OR u.phone LIKE ?)";
            $binds[] = $like; $binds[] = $like; $binds[] = $like;
        }

        $rows           = $this->db->query($base, $binds)->result();
        $total          = count($rows);
        $owners         = $tenants = $committee = $active = $inactive = $new_this_month = 0;
        $month          = date('Y-m');
        foreach ($rows as $r) {
            if ($r->member_type === 'owner')  $owners++;
            if ($r->member_type === 'tenant') $tenants++;
            if (!empty($r->committee_role))   $committee++;
            if ($r->status == 1) $active++;   else $inactive++;
            if (!empty($r->created_at) && substr($r->created_at, 0, 7) === $month) $new_this_month++;
        }
        return compact('total','owners','tenants','committee','active','inactive','new_this_month');
    }

    /* ════════════════════════════════════════════════
     *  WINGS / SOCIETIES DROPDOWNS
     * ════════════════════════════════════════════════ */
    public function get_wings($society_id = null)
    {
        if ($society_id) $this->db->where('society_id', (int)$society_id);
        return $this->db->get('wings')->result();
    }

    public function get_societies()
    {
        return $this->db->select('id, name')->order_by('name','ASC')->get('societies')->result();
    }

    /* ════════════════════════════════════════════════
     *  CRUD
     * ════════════════════════════════════════════════ */
    public function create($data)
    {
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function update_member($id, $data)
    {
        return $this->db->where('id', $id)->update('users', $data);
    }

    public function delete($id)
    {
        $this->db->where('user_id', $id)->delete('user_roles');
        return $this->db->where('id', $id)->delete('users');
    }

    /* ════════════════════════════════════════════════
     *  COMMITTEE ROLE ASSIGNMENT
     * ════════════════════════════════════════════════ */
    public function assign_committee_role($user_id, $role_id)
    {
        if (empty($user_id) || empty($role_id)) return false;
        if (!in_array((int)$role_id, self::COMMITTEE_ROLE_IDS)) return false;
        $this->db->trans_start();
        $existing = $this->db->select('id')->from('user_roles')
            ->where('user_id', $user_id)->where_in('role_id', self::COMMITTEE_ROLE_IDS)->get()->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update('user_roles', ['role_id' => (int)$role_id]);
        } else {
            $this->db->insert('user_roles', ['user_id' => (int)$user_id, 'role_id' => (int)$role_id]);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function remove_committee_role($user_id)
    {
        if (empty($user_id)) return false;
        return $this->db->where('user_id', $user_id)->where_in('role_id', self::COMMITTEE_ROLE_IDS)->delete('user_roles');
    }

    /* ════════════════════════════════════════════════
     *  VALIDATION HELPERS
     * ════════════════════════════════════════════════ */
    public function memberExists($phone, $email, $society_id, $excludeId = null)
    {
        $this->db->where('society_id', $society_id)->group_start()->where('phone', $phone)->or_where('email', $email)->group_end();
        if (!empty($excludeId)) $this->db->where('id !=', (int)$excludeId);
        return $this->db->get('users')->num_rows() > 0;
    }

    public function phoneOrEmailExists($phone, $email, $society_id): bool
    {
        $q = $this->db->query(
            "SELECT id FROM users WHERE society_id = ? AND (phone = ? OR (email IS NOT NULL AND email != '' AND email = ?)) LIMIT 1",
            [$society_id, $phone, $email]
        );
        return $q->num_rows() > 0;
    }

    public function ownerExistsForFlat($flat_no, $society_id, $excludeId = null)
    {
        $this->db->where('flat_no', $flat_no)->where('society_id', $society_id)->where('member_type', 'owner');
        if (!empty($excludeId)) $this->db->where('id !=', (int)$excludeId);
        return $this->db->get('users')->num_rows() > 0;
    }

    public function assign_default_role($user_id, $member_type)
    {
        $role = $this->db->get_where('roles', ['role_name' => $member_type === 'owner' ? 'owner' : 'tenant'])->row();
        if ($role) {
            $exists = $this->db->where('user_id', $user_id)->where('role_id', $role->id)->get('user_roles')->row();
            if (!$exists) $this->db->insert('user_roles', ['user_id' => $user_id, 'role_id' => $role->id]);
        }
    }

    /* ════════════════════════════════════════════════
     *  CSV IMPORT — Core logic
     *
     *  Expected CSV columns (case-insensitive):
     *    first_name*  last_name*  phone*  email
     *    wing_name    flat_no*    member_type*  (owner|tenant)
     *    password     status      (1=active, 0=inactive, default 1)
     *
     *  Returns array:
     *    inserted  int   — rows successfully created
     *    skipped   int   — duplicate phone/email or flat owner conflict
     *    errors    array — row-level error messages
     *    warnings  array — non-fatal notices
     * ════════════════════════════════════════════════ */
    public function import_csv(string $tmpPath, int $societyId): array
    {
        $handle = fopen($tmpPath, 'r');
        if (!$handle) {
            return ['inserted' => 0, 'skipped' => 0, 'errors' => ['Could not read uploaded file.'], 'warnings' => []];
        }

        /* ── Read + normalise headers ── */
        $rawHeaders = fgetcsv($handle);
        if (!$rawHeaders) {
            fclose($handle);
            return ['inserted' => 0, 'skipped' => 0, 'errors' => ['CSV file is empty or unreadable.'], 'warnings' => []];
        }
        $headers = array_map(fn($h) => strtolower(trim($h)), $rawHeaders);

        /* ── Validate required columns ── */
        $required = ['first_name', 'last_name', 'phone', 'flat_no', 'member_type'];
        $missing  = array_diff($required, $headers);
        if (!empty($missing)) {
            fclose($handle);
            return [
                'inserted' => 0, 'skipped' => 0,
                'errors'   => ['Missing required column(s): ' . implode(', ', $missing)],
                'warnings' => [],
            ];
        }

        /* ── Build wing name → id lookup (cached) ── */
        $wingCache = [];
        foreach ($this->db->where('society_id', $societyId)->get('wings')->result() as $w) {
            $wingCache[strtolower(trim($w->wing_name))] = (int)$w->id;
        }

        /* ── Build role lookup ── */
        $roleCache = [];
        foreach ($this->db->get('roles')->result() as $r) {
            $roleCache[strtolower($r->role_name)] = (int)$r->id;
        }

        /* ── Process rows ── */
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];
        $warnings = [];
        $rowNum   = 1; // header was row 0
        $now      = date('Y-m-d H:i:s');

        /* Default password to use when CSV column is blank */
        $defaultPassword = 'Society@123';

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            /* Skip completely blank rows */
            if (empty(array_filter($row))) continue;

            /* Map row to associative array */
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }
            $r = array_combine($headers, array_map('trim', $row));

            /* ── Field extraction ── */
            $firstName  = $r['first_name']   ?? '';
            $lastName   = $r['last_name']     ?? '';
            $phone      = $r['phone']         ?? '';
            $email      = !empty($r['email']) ? strtolower($r['email']) : null;
            $wingName   = $r['wing_name']     ?? '';
            $flatNo     = $r['flat_no']       ?? '';
            $memberType = strtolower($r['member_type'] ?? 'owner');
            $password   = !empty($r['password']) ? $r['password'] : $defaultPassword;
            $status     = isset($r['status']) && $r['status'] !== '' ? (int)$r['status'] : 1;

            /* ── Row-level validation ── */
            $rowErrors = [];
            if (empty($firstName))       $rowErrors[] = 'first_name is required';
            if (empty($lastName))        $rowErrors[] = 'last_name is required';
            if (!preg_match('/^[0-9]{10}$/', $phone)) $rowErrors[] = 'phone must be 10 digits';
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $rowErrors[] = 'invalid email address';
            if (empty($flatNo))          $rowErrors[] = 'flat_no is required';
            if (!in_array($memberType, ['owner','tenant'])) $rowErrors[] = "member_type must be 'owner' or 'tenant'";

            if (!empty($rowErrors)) {
                $errors[] = "Row $rowNum: " . implode('; ', $rowErrors) . " — skipped.";
                $skipped++;
                continue;
            }

            /* ── Resolve wing_id ── */
            $wingId = null;
            if (!empty($wingName)) {
                $wingId = $wingCache[strtolower($wingName)] ?? null;
                if (!$wingId) {
                    $warnings[] = "Row $rowNum ({$firstName} {$lastName}): Wing '{$wingName}' not found — flat assigned without wing.";
                }
            }

            /* ── Check duplicate phone / email ── */
            if ($this->phoneOrEmailExists($phone, $email ?? '', $societyId)) {
                $errors[] = "Row $rowNum ({$firstName} {$lastName}): Phone or email already registered — skipped.";
                $skipped++;
                continue;
            }

            /* ── Owner uniqueness per flat ── */
            if ($memberType === 'owner' && $this->ownerExistsForFlat($flatNo, $societyId)) {
                $errors[] = "Row $rowNum ({$firstName} {$lastName}): An owner already exists for flat {$flatNo} — skipped.";
                $skipped++;
                continue;
            }

            /* ── Find the flat record ── */
            $this->db->where('flat_no',    $flatNo);
            $this->db->where('society_id', $societyId);
            if ($wingId) {
                $this->db->where('wing_id', $wingId);
            } else {
                /* If no wing given, try to find the flat by flat_no alone */
            }
            $flatRow = $this->db->get('flats')->row();

            if (!$flatRow) {
                $errors[] = "Row $rowNum ({$firstName} {$lastName}): Flat '{$flatNo}'" . ($wingName ? " in wing '{$wingName}'" : '') . " not found — skipped.";
                $skipped++;
                continue;
            }

            if ((int)$flatRow->status === 0) {
                $errors[] = "Row $rowNum ({$firstName} {$lastName}): Flat '{$flatNo}' is already occupied — skipped.";
                $skipped++;
                continue;
            }

            /* ── Insert user (transaction) ── */
            $this->db->trans_start();

            $userId = $this->create([
                'society_id'  => $societyId,
                'wing_id'     => $flatRow->wing_id,   // authoritative from flat record
                'name'        => trim($firstName . ' ' . $lastName),
                'email'       => $email,
                'phone'       => $phone,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'flat_no'     => $flatRow->flat_no,
                'member_type' => $memberType,
                'status'      => $status,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            if ($userId) {
                /* Assign role */
                $roleName = $memberType === 'owner' ? 'owner' : 'tenant';
                if (!empty($roleCache[$roleName])) {
                    $this->db->insert('user_roles', ['user_id' => $userId, 'role_id' => $roleCache[$roleName]]);
                }

                /* Mark flat as occupied (status = 0) */
                $this->db->where('id', $flatRow->id)->update('flats', [
                    'status'     => 0,
                    'updated_at' => $now,
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() && $userId) {
                $inserted++;
            } else {
                $errors[] = "Row $rowNum ({$firstName} {$lastName}): Database error — skipped.";
                $skipped++;
            }
        }

        fclose($handle);
        return compact('inserted', 'skipped', 'errors', 'warnings');
    }
}
