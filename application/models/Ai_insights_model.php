<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AI Insights Model
 * Provides database queries and forecasting algorithms.
 */
class Ai_insights_model extends CI_Model
{
	private $tables = [
		'payments' => 'payments',
		'complaints' => 'complaints',
		'users' => 'users',
		'flats' => 'flats',
		'visitors' => 'visitors',
		'staff' => 'staff',
		'events' => 'events',
	];

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	private function society_id(): int
	{
		return (int) $this->session->userdata('society_id');
	}

	public function get_monthly_payments(int $months = 12): array
	{
		$result = $this->db
			->select("
				DATE_FORMAT(created_at, '%Y-%m') AS period,
				DATE_FORMAT(created_at, '%b %Y') AS label,
				SUM(amount) AS total,
				SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS collected,
				SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) AS pending,
				COUNT(*) AS transactions,
				COUNT(DISTINCT user_id) AS unique_payers
			", false)
			->from($this->tables['payments'])
			->where('society_id', $this->society_id())
			->where('created_at >=', date('Y-m-d', strtotime("-{$months} months")))
			->group_by(['period', 'label'])
			->order_by('period', 'ASC')
			->get()->result_array();

		return is_array($result) ? $result : [];
	}

	public function get_payment_kpis(): array
	{
		$row = $this->db
			->select("
				SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS total_collected,
				SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) AS total_pending,
				SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) AS total_overdue,
				COUNT(*) AS total_transactions,
				COUNT(CASE WHEN status = 'paid' THEN 1 END) AS paid_count,
				COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count
			", false)
			->where('society_id', $this->society_id())
			->get($this->tables['payments'])
			->row_array();

		return $row ?: [
			'total_collected' => 0,
			'total_pending' => 0,
			'total_overdue' => 0,
			'total_transactions' => 0,
			'paid_count' => 0,
			'pending_count' => 0,
		];
	}

	public function get_payment_by_category(): array
	{
		$result = $this->db
			->select("payment_type AS category, SUM(amount) AS total, COUNT(*) AS count", false)
			->where('society_id', $this->society_id())
			->where('status', 'paid')
			->group_by('payment_type')
			->order_by('total', 'DESC')
			->get($this->tables['payments'])
			->result_array();

		return is_array($result) ? $result : [];
	}

	public function get_monthly_complaints(int $months = 12): array
	{
		$result = $this->db
			->select("
				DATE_FORMAT(created_at, '%Y-%m') AS period,
				DATE_FORMAT(created_at, '%b %Y') AS label,
				COUNT(*) AS total,
				COUNT(CASE WHEN status = 'open' THEN 1 END) AS open_count,
				COUNT(CASE WHEN status = 'resolved' THEN 1 END) AS resolved_count,
				COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count
			", false)
			->from($this->tables['complaints'])
			->where('society_id', $this->society_id())
			->where('created_at >=', date('Y-m-d', strtotime("-{$months} months")))
			->group_by(['period', 'label'])
			->order_by('period', 'ASC')
			->get()->result_array();

		return is_array($result) ? $result : [];
	}

	public function get_complaint_kpis(): array
	{
		$row = $this->db
			->select("
				COUNT(*) AS total,
				COUNT(CASE WHEN status = 'open' THEN 1 END) AS open_count,
				COUNT(CASE WHEN status = 'resolved' THEN 1 END) AS resolved_count,
				COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count,
				AVG(CASE WHEN status = 'resolved' THEN DATEDIFF(IFNULL(resolved_at, created_at), created_at) END) AS avg_resolution_days
			", false)
			->where('society_id', $this->society_id())
			->get($this->tables['complaints'])
			->row_array();

		return $row ?: [
			'total' => 0,
			'open_count' => 0,
			'resolved_count' => 0,
			'pending_count' => 0,
			'avg_resolution_days' => 0,
		];
	}

	public function get_complaints_by_category(): array
	{
		$result = $this->db
			->select('category, COUNT(*) AS total', false)
			->where('society_id', $this->society_id())
			->group_by('category')
			->order_by('total', 'DESC')
			->limit(6)
			->get($this->tables['complaints'])
			->result_array();

		return is_array($result) ? $result : [];
	}

	public function get_member_kpis(): array
	{
		$row = $this->db
			->select("
				COUNT(*) AS total,
				COUNT(CASE WHEN status = 'active' THEN 1 END) AS active,
				COUNT(CASE WHEN status = 'inactive' THEN 1 END) AS inactive
			", false)
			->where('society_id', $this->society_id())
			->get($this->tables['users'])
			->row_array();

		return $row ?: ['total' => 0, 'active' => 0, 'inactive' => 0];
	}

	public function get_monthly_member_growth(int $months = 12): array
	{
		$result = $this->db
			->select("
				DATE_FORMAT(created_at, '%Y-%m') AS period,
				DATE_FORMAT(created_at, '%b %Y') AS label,
				COUNT(*) AS new_members
			", false)
			->from($this->tables['users'])
			->where('society_id', $this->society_id())
			->where('created_at >=', date('Y-m-d', strtotime("-{$months} months")))
			->group_by(['period', 'label'])
			->order_by('period', 'ASC')
			->get()->result_array();

		return is_array($result) ? $result : [];
	}

	public function get_flat_occupancy(): array
	{
		if (!$this->db->table_exists($this->tables['flats'])) {
			return ['total' => 0, 'occupied' => 0, 'vacant' => 0, 'rate' => 0];
		}

		$total = (int) $this->db
			->where('society_id', $this->society_id())
			->count_all_results($this->tables['flats']);

		$occupied = (int) $this->db
			->where('society_id', $this->society_id())
			->where('status', 'occupied')
			->count_all_results($this->tables['flats']);

		return [
			'total' => $total,
			'occupied' => $occupied,
			'vacant' => max(0, $total - $occupied),
			'rate' => $total > 0 ? round(($occupied / $total) * 100, 1) : 0,
		];
	}

	public function get_monthly_visitors(int $months = 12): array
	{
		$result = $this->db
			->select("
				DATE_FORMAT(created_at, '%Y-%m') AS period,
				DATE_FORMAT(created_at, '%b %Y') AS label,
				COUNT(*) AS total
			", false)
			->from($this->tables['visitors'])
			->where('society_id', $this->society_id())
			->where('created_at >=', date('Y-m-d', strtotime("-{$months} months")))
			->group_by(['period', 'label'])
			->order_by('period', 'ASC')
			->get()->result_array();

		return is_array($result) ? $result : [];
	}

	public function get_visitor_kpis(): array
	{
		$row = $this->db
			->select("
				COUNT(*) AS total_all_time,
				COUNT(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 END) AS this_month,
				COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) AS today
			", false)
			->where('society_id', $this->society_id())
			->get($this->tables['visitors'])
			->row_array();

		return $row ?: ['total_all_time' => 0, 'this_month' => 0, 'today' => 0];
	}

	public function get_staff_kpis(): array
	{
		$row = $this->db
			->select("
				COUNT(*) AS total,
				COUNT(CASE WHEN status = 'active' THEN 1 END) AS active,
				COUNT(CASE WHEN status = 'inactive' THEN 1 END) AS inactive
			", false)
			->where('society_id', $this->society_id())
			->get($this->tables['staff'])
			->row_array();

		return $row ?: ['total' => 0, 'active' => 0, 'inactive' => 0];
	}

	public function get_upcoming_events(int $limit = 5): array
	{
		$result = $this->db
			->where('society_id', $this->society_id())
			->where('event_date >=', date('Y-m-d'))
			->order_by('event_date', 'ASC')
			->limit($limit)
			->get($this->tables['events'])
			->result_array();

		return is_array($result) ? $result : [];
	}

	public function linear_regression(array $y, int $future = 3): array
	{
		$n = count($y);
		if ($n < 2) {
			$last = !empty($y) ? (float) end($y) : 0;
			return array_fill(0, $future, max(0, $last));
		}

		$x = range(1, $n);
		$xMean = array_sum($x) / $n;
		$yMean = array_sum($y) / $n;

		$num = 0.0;
		$den = 0.0;
		for ($i = 0; $i < $n; $i++) {
			$num += ($x[$i] - $xMean) * ($y[$i] - $yMean);
			$den += pow($x[$i] - $xMean, 2);
		}

		$slope = $den != 0 ? $num / $den : 0.0;
		$intercept = $yMean - ($slope * $xMean);

		$preds = [];
		for ($i = $n + 1; $i <= $n + $future; $i++) {
			$preds[] = max(0, round($intercept + ($slope * $i), 2));
		}
		return $preds;
	}

	public function exponential_smoothing(array $y, int $future = 3, float $alpha = 0.4, float $beta = 0.3): array
	{
		$n = count($y);
		if ($n < 2) {
			$last = !empty($y) ? (float) end($y) : 0;
			return array_fill(0, $future, max(0, $last));
		}

		$level = $y[0];
		$trend = $y[1] - $y[0];

		for ($i = 1; $i < $n; $i++) {
			$prevLevel = $level;
			$level = ($alpha * $y[$i]) + ((1 - $alpha) * ($level + $trend));
			$trend = ($beta * ($level - $prevLevel)) + ((1 - $beta) * $trend);
		}

		$preds = [];
		for ($h = 1; $h <= $future; $h++) {
			$preds[] = max(0, round($level + ($h * $trend), 2));
		}
		return $preds;
	}

	public function weighted_moving_average(array $y, int $future = 3, int $window = 4): array
	{
		$n = count($y);
		if ($n === 0) {
			return array_fill(0, $future, 0);
		}

		$window = min($window, $n);
		$slice = array_slice($y, -$window);
		$weights = range(1, $window);
		$wSum = array_sum($weights);

		$wma = 0.0;
		foreach ($slice as $i => $v) {
			$wma += $v * $weights[$i];
		}
		$wma /= $wSum;

		$drift = 0.0;
		if ($n >= 2) {
			$drift = (($y[$n - 1] - $y[0]) / ($n - 1)) * 0.2;
		}

		$preds = [];
		$current = $wma;
		for ($h = 1; $h <= $future; $h++) {
			$current = max(0, $current + $drift);
			$preds[] = round($current, 2);
		}
		return $preds;
	}

	public function seasonal_forecast(array $y, array $periods, int $future = 3): array
	{
		$n = count($y);
		if ($n < 6) {
			return $this->linear_regression($y, $future);
		}

		$monthTotals = array_fill(1, 12, ['sum' => 0, 'count' => 0]);
		foreach ($periods as $i => $p) {
			if (!isset($y[$i])) {
				continue;
			}
			$m = (int) date('n', strtotime($p . '-01'));
			$monthTotals[$m]['sum'] += $y[$i];
			$monthTotals[$m]['count'] += 1;
		}

		$grandMean = array_sum($y) / $n ?: 1;

		$seasonIndex = [];
		foreach ($monthTotals as $m => $d) {
			$monthAvg = $d['count'] > 0 ? ($d['sum'] / $d['count']) : $grandMean;
			$seasonIndex[$m] = $monthAvg / $grandMean;
		}

		$deseason = [];
		foreach ($periods as $i => $p) {
			if (!isset($y[$i])) {
				continue;
			}
			$m = (int) date('n', strtotime($p . '-01'));
			$si = $seasonIndex[$m] ?? 1;
			$deseason[] = $si > 0 ? ($y[$i] / $si) : $y[$i];
		}

		$baseForecasts = $this->linear_regression($deseason, $future);
		$preds = [];
		for ($h = 0; $h < $future; $h++) {
			$futureMonth = (int) date('n', strtotime('+' . ($h + 1) . ' month'));
			$si = $seasonIndex[$futureMonth] ?? 1;
			$preds[] = max(0, round(($baseForecasts[$h] ?? 0) * $si, 2));
		}
		return $preds;
	}

	public function ensemble_forecast(array $y, array $periods = [], int $future = 3): array
	{
		if (count($y) < 2) {
			$last = !empty($y) ? (float) end($y) : 0;
			return array_fill(0, $future, max(0, $last));
		}

		$lr = $this->linear_regression($y, $future);
		$es = $this->exponential_smoothing($y, $future);
		$wma = $this->weighted_moving_average($y, $future);
		$sea = !empty($periods) ? $this->seasonal_forecast($y, $periods, $future) : $lr;

		$weights = ['es' => 0.35, 'lr' => 0.30, 'wma' => 0.20, 'sea' => 0.15];
		$preds = [];
		for ($i = 0; $i < $future; $i++) {
			$val = ($es[$i] ?? 0) * $weights['es']
				+ ($lr[$i] ?? 0) * $weights['lr']
				+ ($wma[$i] ?? 0) * $weights['wma']
				+ ($sea[$i] ?? 0) * $weights['sea'];
			$preds[] = max(0, round($val, 2));
		}
		return $preds;
	}

	public function detect_anomalies(array $y, float $threshold = 2.0): array
	{
		$n = count($y);
		if ($n < 3) {
			return array_fill(0, $n, false);
		}

		$mean = array_sum($y) / $n;
		$variance = 0.0;
		foreach ($y as $v) {
			$variance += pow($v - $mean, 2);
		}
		$std = sqrt($variance / $n) ?: 1;

		$flags = [];
		foreach ($y as $v) {
			$z = abs(($v - $mean) / $std);
			$flags[] = $z > $threshold;
		}
		return $flags;
	}

	public function growth_analysis(array $y): array
	{
		$n = count($y);
		if ($n < 2) {
			return ['mom_rates' => [], 'avg_growth' => 0, 'cagr' => 0];
		}

		$momRates = [];
		for ($i = 1; $i < $n; $i++) {
			$prev = $y[$i - 1] ?: 1;
			$momRates[] = round((($y[$i] - $y[$i - 1]) / $prev) * 100, 2);
		}

		$avgGrowth = count($momRates) ? array_sum($momRates) / count($momRates) : 0;

		$start = $y[0] ?: 1;
		$end = $y[$n - 1];
		$cagr = ($n > 1 && $start > 0) ? (pow($end / $start, 1 / ($n - 1)) - 1) * 100 : 0;

		return [
			'mom_rates' => $momRates,
			'avg_growth' => round($avgGrowth, 2),
			'cagr' => round($cagr, 2),
		];
	}

	public function forecast_confidence(array $y, array $predictions): array
	{
		$n = count($y);
		if ($n < 3) {
			$intervals = [];
			foreach ($predictions as $p) {
				$intervals[] = [
					'low' => round($p * 0.85, 2),
					'high' => round($p * 1.15, 2),
					'margin' => round($p * 0.15, 2),
				];
			}
			return $intervals;
		}

		$x = range(1, $n);
		$xMean = array_sum($x) / $n;
		$yMean = array_sum($y) / $n;

		$num = 0.0;
		$den = 0.0;
		for ($i = 0; $i < $n; $i++) {
			$num += ($x[$i] - $xMean) * ($y[$i] - $yMean);
			$den += pow($x[$i] - $xMean, 2);
		}

		$slope = $den != 0 ? $num / $den : 0.0;
		$intercept = $yMean - ($slope * $xMean);

		$residuals = [];
		for ($i = 0; $i < $n; $i++) {
			$fitted = $intercept + ($slope * $x[$i]);
			$residuals[] = pow($y[$i] - $fitted, 2);
		}

		$rmse = sqrt(array_sum($residuals) / $n);
		$ci_multiplier = 1.96;

		$intervals = [];
		foreach ($predictions as $h => $pred) {
			$margin = $ci_multiplier * $rmse * sqrt(1 + (($h + 1) / $n));
			$intervals[] = [
				'low' => max(0, round($pred - $margin, 2)),
				'high' => round($pred + $margin, 2),
				'margin' => round($margin, 2),
			];
		}
		return $intervals;
	}

	public function compute_health_score(array $kpis): float
	{
		$collected = (float) ($kpis['payment']['total_collected'] ?? 0);
		$pending = (float) ($kpis['payment']['total_pending'] ?? 0);
		$payTotal = $collected + $pending;
		$paymentScore = $payTotal > 0 ? (($collected / $payTotal) * 100) : 50;

		$occupancyScore = (float) ($kpis['occupancy']['rate'] ?? 50);

		$compTotal = (int) ($kpis['complaint']['total'] ?? 0);
		$resolved = (int) ($kpis['complaint']['resolved_count'] ?? 0);
		$complaintScore = $compTotal > 0 ? (($resolved / $compTotal) * 100) : 80;

		$staffActive = (int) ($kpis['staff']['active'] ?? 0);
		$staffScore = min(100, $staffActive * 10);

		$weights = [
			'payment' => 0.40,
			'occupancy' => 0.30,
			'complaint' => 0.20,
			'staff' => 0.10,
		];

		$health = ($paymentScore * $weights['payment'])
			+ ($occupancyScore * $weights['occupancy'])
			+ ($complaintScore * $weights['complaint'])
			+ ($staffScore * $weights['staff']);

		return round(min(100, max(0, $health)), 1);
	}

	public function extract_column(array $rows, string $col): array
	{
		return array_map(static function ($r) use ($col) {
			return (float) ($r[$col] ?? 0);
		}, $rows);
	}

	public function extract_periods(array $rows): array
	{
		return array_map(static function ($r) {
			return $r['period'] ?? '';
		}, $rows);
	}

	public function extract_labels(array $rows): array
	{
		return array_map(static function ($r) {
			return $r['label'] ?? '';
		}, $rows);
	}

	public function future_labels(int $count = 3): array
	{
		$labels = [];
		for ($i = 1; $i <= $count; $i++) {
			$labels[] = date('M Y', strtotime("+{$i} month"));
		}
		return $labels;
	}

	public function future_periods(int $count = 3): array
	{
		$periods = [];
		for ($i = 1; $i <= $count; $i++) {
			$periods[] = date('Y-m', strtotime("+{$i} month"));
		}
		return $periods;
	}
}
