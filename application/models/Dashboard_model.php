<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{
	public function get_societies(): array
	{
		return $this->db->select('id, name, status, setup_done')
			->from('societies')
			->order_by('name', 'ASC')
			->get()
			->result_array();
	}

	public function get_stats(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId): array
	{
		if ($isOwner) {
			$this->db->reset_query();
			$myComplaints = $this->db->from('complaints')
				->where('user_id', $userId)
				->count_all_results();

			$this->db->reset_query();
			$myPendingPayments = $this->db->from('payments')
				->where('user_id', $userId)
				->where('status', 'pending')
				->count_all_results();

			$this->db->reset_query();
			$myPaidPayments = $this->db->from('payments')
				->where('user_id', $userId)
				->where('status', 'paid')
				->count_all_results();

			$this->db->reset_query();
			$myBookings = $this->db->from('bookings')
				->where('user_id', $userId)
				->count_all_results();

			return [
				['label' => 'My Complaints', 'value' => $myComplaints, 'icon' => 'fa-exclamation-circle', 'trend' => 'Your issue log'],
				['label' => 'Pending Bills', 'value' => $myPendingPayments, 'icon' => 'fa-money-bill-wave', 'trend' => 'Unpaid requests'],
				['label' => 'Paid Bills', 'value' => $myPaidPayments, 'icon' => 'fa-receipt', 'trend' => 'Cleared payments'],
				['label' => 'My Bookings', 'value' => $myBookings, 'icon' => 'fa-calendar-alt', 'trend' => 'Requests made'],
			];
		}

		// For super admin or society admin
		$this->db->reset_query();
		$this->db->from('users')->where('status', 1);
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$totalResidents = (int) $this->db->count_all_results();

		$this->db->reset_query();
		$this->db->from('flats');
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$totalFlats = (int) $this->db->count_all_results();

		$this->db->reset_query();
		$this->db->from('flats')->where('status', 0); // 0 = occupied
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$occupiedFlats = (int) $this->db->count_all_results();

		$this->db->reset_query();
		$this->db->select_sum('amount', 'total_due')->from('payments')->where('status', 'pending');
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$pendingDues = (float) ($this->db->get()->row()->total_due ?? 0);

		$this->db->reset_query();
		$this->db->from('complaints')->where_in('status', ['pending', 'in-progress']);
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$openComplaints = (int) $this->db->count_all_results();

		$this->db->reset_query();
		$this->db->from('notices')->where('status', 'active');
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$activeNotices = (int) $this->db->count_all_results();

		return [
			['label' => 'Total Residents', 'value' => $totalResidents, 'icon' => 'fa-users', 'trend' => 'Active residents'],
			['label' => 'Total Flats', 'value' => $totalFlats, 'icon' => 'fa-building', 'trend' => $occupiedFlats . ' occupied'],
			['label' => 'Pending Dues', 'value' => $pendingDues, 'icon' => 'fa-money-bill-wave', 'trend' => 'Unpaid maintenance'],
			['label' => 'Open Complaints', 'value' => $openComplaints, 'icon' => 'fa-exclamation-circle', 'trend' => $activeNotices . ' active notices'],
		];
	}

	public function get_recent_members(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId, int $limit = 3): array
	{
		$this->db->reset_query();

		if ($isOwner) {
			return $this->db->select('id, name, flat_no, member_type, status')
				->from('users')
				->where('id', $userId)
				->limit(1)
				->get()
				->result_array();
		}

		$this->db->select('u.id, u.name, u.flat_no, u.member_type, u.status, s.name AS society_name');
		$this->db->from('users u');
		$this->db->join('societies s', 's.id = u.society_id', 'left');
		if (!$isSuperAdmin) {
			$this->db->where('u.society_id', $societyId);
		}
		$this->db->order_by('u.id', 'DESC')->limit($limit);
		return $this->db->get()->result_array();
	}

	public function get_payment_summary(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId): array
	{
		// Build base query
		$this->db->reset_query();
		$this->db->from('payments');
		if ($isOwner) {
			$this->db->where('user_id', $userId);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}

		$paid = (clone $this->db)->where('status', 'paid')->count_all_results();
		$pending = (clone $this->db)->where('status', 'pending')->count_all_results();
		$overdue = (clone $this->db)->where('status', 'overdue')->count_all_results();

		return compact('paid', 'pending', 'overdue');
	}

	public function get_income_expense_series(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId): array
	{
		$labels = $income = $expense = [];
		$months = [];
		for ($i = 5; $i >= 0; $i--) {
			$dt = new DateTime("first day of -{$i} month");
			$key = $dt->format('Y-m');
			$months[$key] = $dt->format('M');
			$income[$key] = 0;
			$expense[$key] = 0;
		}

		// Income from paid payments
		$this->db->reset_query();
		$this->db->select("DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total")
			->from('payments')
			->where('status', 'paid');
		if ($isOwner) {
			$this->db->where('user_id', $userId);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$incomeRows = $this->db->group_by('ym')->get()->result_array();

		// Expense from resolved complaints
		$this->db->reset_query();
		$this->db->select("DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(expense_amount),0) AS total")
			->from('complaints')
			->where_in('status', ['resolved', 'closed'])
			->where('expense_amount IS NOT NULL');
		if ($isOwner) {
			$this->db->where('user_id', $userId);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$expenseRows = $this->db->group_by('ym')->get()->result_array();

		foreach ($incomeRows as $r) {
			if (isset($income[$r['ym']])) {
				$income[$r['ym']] = (float) $r['total'];
			}
		}
		foreach ($expenseRows as $r) {
			if (isset($expense[$r['ym']])) {
				$expense[$r['ym']] = (float) $r['total'];
			}
		}

		return [
			'labels' => array_values($months),
			'income' => array_values($income),
			'expense' => array_values($expense),
		];
	}

	public function get_recent_notices(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId, int $limit = 3): array
	{
		$this->db->reset_query();
		$this->db->from('notices');
		if ($isOwner) {
			$this->db->where('society_id', $societyId)
				->where_in('target_audience', ['all', 'owners']);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$this->db->order_by('id', 'DESC')->limit($limit);
		return $this->db->get()->result_array();
	}

	public function get_recent_complaints(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId, int $limit = 3): array
	{
		$this->db->reset_query();
		$this->db->from('complaints');
		if ($isOwner) {
			$this->db->where('user_id', $userId);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$this->db->order_by('id', 'DESC')->limit($limit);
		return $this->db->get()->result_array();
	}

	public function get_recent_bookings(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId, int $limit = 3): array
	{
		$this->db->reset_query();
		$this->db->from('bookings');
		if ($isOwner) {
			$this->db->where('user_id', $userId);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$this->db->order_by('id', 'DESC')->limit($limit);
		return $this->db->get()->result_array();
	}

	public function get_insights(int $societyId, bool $isSuperAdmin, bool $isOwner, int $userId): array
	{
		// Average complaint resolution time
		$this->db->reset_query();
		$this->db->select('AVG(TIMESTAMPDIFF(DAY, created_at, resolved_at)) AS avg_days')
			->from('complaints')
			->where_in('status', ['resolved', 'closed'])
			->where('resolved_at IS NOT NULL');
		if ($isOwner) {
			$this->db->where('user_id', $userId);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$avgDays = (float) ($this->db->get()->row()->avg_days ?? 0);

		// Occupancy
		$this->db->reset_query();
		$this->db->from('flats');
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$totalFlats = (int) $this->db->count_all_results();

		$this->db->reset_query();
		$this->db->from('flats')->where('status', 0); // 0 = occupied
		if (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$occupiedFlats = (int) $this->db->count_all_results();
		$occupancy = $totalFlats ? round(($occupiedFlats / $totalFlats) * 100) : 0;

		// Pending dues for prediction
		$this->db->reset_query();
		$this->db->select_sum('amount', 'pending_total')
			->from('payments')
			->where('status', 'pending');
		if ($isOwner) {
			$this->db->where('user_id', $userId);
		} elseif (!$isSuperAdmin) {
			$this->db->where('society_id', $societyId);
		}
		$pendingDues = (float) ($this->db->get()->row()->pending_total ?? 0);
		$maintenancePrediction = $pendingDues > 0 ? '₹' . number_format($pendingDues * 1.12, 0) : '₹0';

		return [
			'maintenancePrediction' => $maintenancePrediction,
			'avgComplaintTime' => $avgDays > 0 ? number_format($avgDays, 1) . ' days' : '0 days',
			'occupancyForecast' => $occupancy . '%',
		];
	}
}
