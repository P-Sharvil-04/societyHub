<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Society_setup_model
 * Works with your real tables: societies, wings, flats, users
 *
 * flats.status (tinyint):  1 = vacant,  0 = occupied,  2 = blocked
 */
class Society_setup_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/* ── Societies ─────────────────────────────── */

	public function get_society(int $id): ?object
	{
		return $this->db->get_where('societies', ['id' => $id])->row() ?: null;
	}

	public function get_all_societies(): array
	{
		return $this->db->select('id, name, setup_done, status')
			->from('societies')->order_by('name')->get()->result();
	}

	public function is_setup_done(int $societyId): bool
	{
		$r = $this->db->select('setup_done')->from('societies')
			->where('id', $societyId)->get()->row();
		return $r ? (bool) $r->setup_done : false;
	}

	public function mark_setup_done(int $societyId): void
	{
		$this->db->where('id', $societyId)->update('societies', ['setup_done' => 1]);
	}

	/* ── Wings ─────────────────────────────────── */

	public function get_wings(int $societyId): array
	{
		return $this->db->select('*')->from('wings')
			->where('society_id', $societyId)
			->order_by('wing_name', 'ASC')->get()->result();
	}

	public function get_wing(int $wingId): ?object
	{
		return $this->db->get_where('wings', ['id' => $wingId])->row() ?: null;
	}

	/** Upsert wings: update if name exists, insert if new */
	public function upsert_wings(int $societyId, array $wings): bool
	{
		$this->db->trans_start();
		foreach ($wings as $w) {
			$name = trim($w['wing_name']);
			$prefix = strtoupper(trim($w['wing_prefix'] ?: substr($name, 0, 1)));
			$floors = max(1, (int) $w['floors']);
			$upf = max(1, (int) $w['units_per_floor']);
			$type = $w['flat_type'] ?? '2BHK';
			$gf = isset($w['has_ground_floor']) ? 1 : 0;
			$fmt = trim($w['naming_format'] ?: '{W}-{F}{U}');

			$existing = $this->db->get_where('wings', [
				'society_id' => $societyId,
				'wing_name' => $name
			])->row();

			if ($existing) {
				$this->db->where('id', $existing->id)->update('wings', [
					'floors' => $floors,
					'units_per_floor' => $upf,
					'flat_type' => $type,
					'wing_prefix' => $prefix,
					'has_ground_floor' => $gf,
					'naming_format' => $fmt,
				]);
			} else {
				$this->db->insert('wings', [
					'society_id' => $societyId,
					'wing_name' => $name,
					'floors' => $floors,
					'units_per_floor' => $upf,
					'flat_type' => $type,
					'wing_prefix' => $prefix,
					'has_ground_floor' => $gf,
					'naming_format' => $fmt,
					'created_at' => date('Y-m-d H:i:s'),
				]);
			}
		}
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function delete_wing(int $wingId): bool
	{
		$occupied = $this->db->where('wing_id', $wingId)
			->where('status', 0)->count_all_results('flats');
		if ($occupied > 0)
			return false;
		$this->db->where('wing_id', $wingId)->delete('flats');
		$this->db->where('id', $wingId)->delete('wings');
		return true;
	}

	/* ── Flat Generation ───────────────────────── */

	public function generate_flats(int $societyId): array
	{
		$wings = $this->get_wings($societyId);
		$inserted = $skipped = 0;
		$now = date('Y-m-d H:i:s');

		foreach ($wings as $w) {
			if (!$w->floors || !$w->units_per_floor)
				continue;
			$sf = $w->has_ground_floor ? 0 : 1;
			$ef = $sf + $w->floors - 1;
			for ($floor = $sf; $floor <= $ef; $floor++) {
				for ($unit = 1; $unit <= $w->units_per_floor; $unit++) {
					$no = $this->make_flat_no(
						$w->wing_prefix ?: $w->wing_name,
						$floor,
						$unit,
						$w->naming_format ?: '{W}-{F}{U}'
					);
					$exists = $this->db->where('flat_no', $no)
						->where('wing_id', $w->id)
						->where('society_id', $societyId)
						->count_all_results('flats');
					if ($exists) {
						$skipped++;
						continue;
					}
					$this->db->insert('flats', [
						'society_id' => $societyId,
						'wing_id' => $w->id,
						'flat_no' => $no,
						'floor' => $floor,
						'flat_type' => $w->flat_type ?: '2BHK',
						'status' => 1,
						'created_at' => $now,
					]);
					$inserted++;
				}
			}
		}
		return compact('inserted', 'skipped');
	}

	public function make_flat_no(string $prefix, int $floor, int $unit, string $fmt): string
	{
		$f = $floor === 0 ? 'G' : (string) $floor;
		$u = str_pad($unit, 2, '0', STR_PAD_LEFT);
		return strtoupper(str_replace(['{W}', '{F}', '{U}', '{FU}'], [$prefix, $f, $u, $f . $u], $fmt));
	}

	public function preview_flats(int $societyId): array
	{
		$wings = $this->get_wings($societyId);
		$total = 0;
		$result = [];
		foreach ($wings as $w) {
			if (!$w->floors || !$w->units_per_floor)
				continue;
			$sf = $w->has_ground_floor ? 0 : 1;
			$ef = $sf + $w->floors - 1;
			$fgs = [];
			for ($fl = $sf; $fl <= $ef; $fl++) {
				$flats = [];
				for ($u = 1; $u <= $w->units_per_floor; $u++) {
					$no = $this->make_flat_no($w->wing_prefix ?: $w->wing_name, $fl, $u, $w->naming_format ?: '{W}-{F}{U}');
					$ex = $this->db->where('flat_no', $no)->where('wing_id', $w->id)->where('society_id', $societyId)->count_all_results('flats') > 0;
					$flats[] = ['flat_no' => $no, 'exists' => $ex];
					$total++;
				}
				$fgs[] = ['floor' => $fl, 'floor_label' => $fl === 0 ? 'Ground Floor' : $this->_ord($fl) . ' Floor', 'flats' => $flats];
			}
			$result[] = ['wing_id' => $w->id, 'wing_name' => $w->wing_name, 'wing_prefix' => $w->wing_prefix ?: $w->wing_name, 'flat_type' => $w->flat_type, 'floor_groups' => $fgs];
		}
		return ['wings' => $result, 'total' => $total];
	}

	/* ── Vacant flats for member add picker ─────── */

	public function get_vacant_flats(int $societyId, int $wingId = 0, ?int $floor = null): array
	{
		$this->db->select('f.id, f.flat_no, f.floor, f.flat_type, f.wing_id, w.wing_name')
			->from('flats f')
			->join('wings w', 'w.id = f.wing_id', 'left')
			->where('f.society_id', $societyId)
			->where('f.status', 1);
		if ($wingId > 0)
			$this->db->where('f.wing_id', $wingId);
		if ($floor !== null)
			$this->db->where('f.floor', $floor);
		$this->db->order_by('w.wing_name ASC, f.floor ASC, f.flat_no ASC');
		$rows = $this->db->get()->result();
		foreach ($rows as $r) {
			$n = (int) $r->floor;
			$r->floor_label = $n === 0 ? 'Ground Floor' : $this->_ord($n) . ' Floor';
		}
		return $rows;
	}

	public function get_flat_by_id(int $id): ?object
	{
		return $this->db->get_where('flats', ['id' => $id])->row() ?: null;
	}

	public function occupy_flat(int $flatId): bool
	{
		return $this->db->where('id', $flatId)->update('flats', ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
	}

	public function vacate_flat_by_flat_no(string $flatNo, int $wingId, int $societyId): void
	{
		$this->db->where('flat_no', $flatNo)->where('wing_id', $wingId)
			->where('society_id', $societyId)
			->update('flats', ['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
	}

	/* ── Stats ─────────────────────────────────── */

	public function get_stats(int $societyId): array
	{
		$rows = $this->db->select('status, COUNT(*) AS cnt')
			->from('flats')->where('society_id', $societyId)
			->group_by('status')->get()->result();
		$s = ['total' => 0, 'vacant' => 0, 'occupied' => 0, 'blocked' => 0];
		foreach ($rows as $r) {
			$s['total'] += (int) $r->cnt;
			if ($r->status == 1)
				$s['vacant'] = (int) $r->cnt;
			if ($r->status == 0)
				$s['occupied'] = (int) $r->cnt;
			if ($r->status == 2)
				$s['blocked'] = (int) $r->cnt;
		}
		return $s;
	}

	public function get_wing_stats(int $wingId, int $societyId): array
	{
		$rows = $this->db->select('status, COUNT(*) AS cnt')
			->from('flats')->where('wing_id', $wingId)->where('society_id', $societyId)
			->group_by('status')->get()->result();
		$c = ['total' => 0, 'vacant' => 0, 'occupied' => 0];
		foreach ($rows as $r) {
			$c['total'] += (int) $r->cnt;
			if ($r->status == 1)
				$c['vacant'] = (int) $r->cnt;
			if ($r->status == 0)
				$c['occupied'] = (int) $r->cnt;
		}
		return $c;
	}

	private function _ord(int $n): string
	{
		$s = ['th', 'st', 'nd', 'rd'];
		$v = $n % 100;
		return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
	}
}
