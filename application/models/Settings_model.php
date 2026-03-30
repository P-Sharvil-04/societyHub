<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * SQL to create the two tables:
 *
 * CREATE TABLE `system_settings` (
 *   `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
 *   `setting_key` VARCHAR(100) NOT NULL UNIQUE,
 *   `setting_val` TEXT DEFAULT NULL,
 *   `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 *   PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 *
 * CREATE TABLE `society_settings` (
 *   `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
 *   `society_id`  INT UNSIGNED NOT NULL,
 *   `setting_key` VARCHAR(100) NOT NULL,
 *   `setting_val` TEXT DEFAULT NULL,
 *   `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 *   PRIMARY KEY (`id`),
 *   UNIQUE KEY `uq_soc_key` (`society_id`, `setting_key`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
class Settings_model extends CI_Model
{
	/* ════════════════════════════════════════════════
	 *  SYSTEM SETTINGS  (super admin only)
	 * ════════════════════════════════════════════════ */

	/**
	 * Get all system settings as a flat key=>value array.
	 */
	public function get_system_settings()
	{
		$rows = $this->db->get('system_settings')->result_array();
		$out = [];
		foreach ($rows as $r) {
			$out[$r['setting_key']] = $r['setting_val'];
		}
		return $out;
	}

	/**
	 * Upsert multiple system settings at once.
	 * $data = ['key' => 'value', ...]
	 */
	public function save_system_settings(array $data)
	{
		foreach ($data as $key => $val) {
			$this->_upsert_system($key, $val);
		}
		return true;
	}

	private function _upsert_system($key, $val)
	{
		$exists = $this->db->get_where('system_settings', ['setting_key' => $key])->row_array();
		if ($exists) {
			$this->db->where('setting_key', $key)
				->update('system_settings', ['setting_val' => $val, 'updated_at' => date('Y-m-d H:i:s')]);
		} else {
			$this->db->insert('system_settings', [
				'setting_key' => $key,
				'setting_val' => $val,
				'updated_at' => date('Y-m-d H:i:s'),
			]);
		}
	}

	/* ════════════════════════════════════════════════
	 *  SOCIETY SETTINGS  (chairman / admin)
	 * ════════════════════════════════════════════════ */

	/**
	 * Get all settings for one society as a flat key=>value array.
	 */
	public function get_society_settings($society_id)
	{
		$rows = $this->db->where('society_id', (int) $society_id)->get('society_settings')->result_array();
		$out = [];
		foreach ($rows as $r) {
			$out[$r['setting_key']] = $r['setting_val'];
		}
		return $out;
	}

	/**
	 * Upsert multiple society settings at once.
	 */
	public function save_society_settings($society_id, array $data)
	{
		foreach ($data as $key => $val) {
			$this->_upsert_society($society_id, $key, $val);
		}
		return true;
	}

	private function _upsert_society($society_id, $key, $val)
	{
		$exists = $this->db
			->where('society_id', (int) $society_id)
			->where('setting_key', $key)
			->get('society_settings')->row_array();

		if ($exists) {
			$this->db
				->where('society_id', (int) $society_id)
				->where('setting_key', $key)
				->update('society_settings', ['setting_val' => $val, 'updated_at' => date('Y-m-d H:i:s')]);
		} else {
			$this->db->insert('society_settings', [
				'society_id' => (int) $society_id,
				'setting_key' => $key,
				'setting_val' => $val,
				'updated_at' => date('Y-m-d H:i:s'),
			]);
		}
	}

	/* ════════════════════════════════════════════════
	 *  USER PROFILE  (all roles — update own record)
	 * ════════════════════════════════════════════════ */

	public function get_user($id)
	{
		return $this->db->get_where('users', ['id' => (int) $id])->row_array();
	}

	public function update_profile($id, array $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update('users', $data);
	}

	public function email_taken($email, $exclude_id)
	{
		return $this->db
			->where('email', $email)
			->where('id !=', (int) $exclude_id)
			->count_all_results('users') > 0;
	}

	/* ════════════════════════════════════════════════
	 *  SOCIETIES LIST  (super admin dropdown)
	 * ════════════════════════════════════════════════ */

	public function get_all_societies()
	{
		return $this->db->select('id, name, address, status')
			->order_by('name')
			->get('societies')->result_array();
	}

	public function update_society($id, array $data)
	{
		return $this->db->where('id', (int) $id)->update('societies', $data);
	}

	/* ════════════════════════════════════════════════
	 *  SYSTEM INFO  (read-only counts for super admin)
	 * ════════════════════════════════════════════════ */

	public function get_system_info()
	{
		return [
			'total_societies' => (int) $this->db->count_all('societies'),
			'total_users' => (int) $this->db->count_all('users'),
			'total_staff' => (int) $this->db->count_all('staff'),
			'total_bookings' => (int) $this->db->count_all('bookings'),
			'total_complaints' => (int) $this->db->count_all('complaints'),
		];
	}
}
