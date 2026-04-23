<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Helper functions (unchanged)
if (!function_exists('ai_fmt_inr')) {
	function ai_fmt_inr($n)
	{
		return '₹' . number_format((float) $n, 0, '.', ',');
	}
}
if (!function_exists('ai_fmt_k')) {
	function ai_fmt_k($n)
	{
		$n = (float) $n;
		return $n >= 1000 ? '₹' . number_format($n / 1000, 1) . 'K' : ai_fmt_inr($n);
	}
}
if (!function_exists('ai_pct')) {
	function ai_pct($a, $b)
	{
		return $b > 0 ? round(($a / $b) * 100, 1) : 0;
	}
}

$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
	<title>AI Insights — <?= html_escape($society_name ?? 'Society') ?></title>
	<link rel="icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link
		href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Raleway:wght@700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap"
		rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
	<style>
		:root {
			--bg: #080c18;
			--surface: #0e1326;
			--card: #161e35;
			--card2: #121829;
			--border: rgba(66, 133, 244, .12);
			--border2: rgba(66, 133, 244, .30);
			--blue: #4285f4;
			--cyan: #00c8ff;
			--green: #00e676;
			--amber: #ffab00;
			--red: #ff5252;
			--violet: #7c4dff;
			--text1: #eaf0ff;
			--text2: #7b93c0;
			--text3: #3d5080;
			--radius: 16px;
			--shadow: 0 10px 40px rgba(0, 0, 0, .35);
			--sidebar-width: 260px;
			--sidebar-collapsed-width: 80px;
		}

		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		body {
			font-family: 'Outfit', sans-serif;
			background: var(--bg);
			color: var(--text1);
			overflow-x: hidden;
		}

		.sidebar {
			position: fixed;
			left: 0;
			top: 0;
			height: 100vh;
			width: var(--sidebar-width);
			background: var(--surface);
			border-right: 1px solid var(--border);
			z-index: 1000;
			transition: transform 0.3s ease, width 0.3s ease;
			overflow-y: auto;
		}

		.sidebar.collapsed {
			width: var(--sidebar-collapsed-width);
		}

		.main-content {
			margin-left: var(--sidebar-width);
			padding: 20px 30px;
			min-height: 100vh;
			transition: margin-left 0.3s ease;
			background: var(--bg);
		}

		.sidebar.collapsed+.main-content {
			margin-left: var(--sidebar-collapsed-width);
		}

		.overlay {
			display: none;
			position: fixed;
			inset: 0;
			background: rgba(8, 12, 24, 0.85);
			backdrop-filter: blur(4px);
			z-index: 999;
		}

		.overlay.on {
			display: block;
		}

		.menu-toggle {
			display: none;
			position: fixed;
			top: 16px;
			left: 16px;
			z-index: 1001;
			background: var(--card);
			border: 1px solid var(--border);
			border-radius: 12px;
			padding: 10px 16px;
			color: var(--text1);
			cursor: pointer;
			font-size: 1rem;
			font-weight: 600;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
		}

		.menu-toggle i {
			margin-right: 8px;
		}

		@media (max-width: 992px) {
			.sidebar {
				transform: translateX(-100%);
				width: 280px;
				box-shadow: 5px 0 30px rgba(0, 0, 0, 0.5);
			}

			.sidebar.mobile-open {
				transform: translateX(0);
			}

			.main-content {
				margin-left: 0 !important;
				padding: 16px;
			}

			.menu-toggle {
				display: block;
			}
		}

		.ai-page {
			max-width: 1600px;
			margin: 0 auto;
		}

		.pg-header {
			display: flex;
			justify-content: space-between;
			gap: 16px;
			align-items: flex-end;
			padding-bottom: 18px;
			border-bottom: 1px solid var(--border);
			margin-bottom: 22px;
			flex-wrap: wrap;
		}

		.pg-title {
			font-family: 'Raleway', sans-serif;
			font-size: 2rem;
			font-weight: 900;
			background: linear-gradient(120deg, #fff 0%, #a8d8ff 45%, #00c8ff 100%);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			line-height: 1.2;
		}

		.pg-sub {
			margin-top: 6px;
			color: var(--text2);
			font-size: 0.85rem;
		}

		.live-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 8px 14px;
			border-radius: 999px;
			background: rgba(0, 200, 255, .08);
			border: 1px solid rgba(0, 200, 255, .25);
			color: var(--cyan);
			font-size: 0.72rem;
			font-weight: 800;
			letter-spacing: .12em;
			text-transform: uppercase;
		}

		.live-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: var(--cyan);
			animation: blink 1.6s ease-in-out infinite;
		}

		@keyframes blink {
			50% {
				opacity: .25;
			}
		}

		.sec-label {
			display: flex;
			align-items: center;
			gap: 10px;
			color: var(--text3);
			font-size: 0.68rem;
			font-weight: 800;
			letter-spacing: .2em;
			text-transform: uppercase;
			margin: 28px 0 16px;
		}

		.sec-label::after {
			content: '';
			height: 1px;
			background: var(--border);
			flex: 1;
		}

		.grid-4,
		.grid-2,
		.grid-21 {
			display: grid;
			gap: 20px;
		}

		.grid-4 {
			grid-template-columns: repeat(4, 1fr);
		}

		.grid-2 {
			grid-template-columns: repeat(2, 1fr);
		}

		.grid-21 {
			grid-template-columns: 2fr 1fr;
		}

		@media (max-width: 1400px) {
			.grid-4 {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		@media (max-width: 768px) {

			.grid-4,
			.grid-2,
			.grid-21 {
				grid-template-columns: 1fr;
			}
		}

		.card {
			background: var(--card);
			border: 1px solid var(--border);
			border-radius: var(--radius);
			box-shadow: var(--shadow);
			transition: transform 0.2s, border-color 0.2s;
		}

		.card:hover {
			border-color: var(--border2);
			transform: translateY(-2px);
		}

		.kpi {
			padding: 20px;
			position: relative;
			overflow: hidden;
		}

		.kpi::after {
			content: '';
			position: absolute;
			top: 0;
			right: 0;
			width: 90px;
			height: 90px;
			border-radius: 0 0 0 90px;
			opacity: 0.06;
			transition: opacity 0.3s;
		}

		.kpi:hover::after {
			opacity: 0.12;
		}

		.kpi.blue::after {
			background: var(--blue);
		}

		.kpi.green::after {
			background: var(--green);
		}

		.kpi.amber::after {
			background: var(--amber);
		}

		.kpi.violet::after {
			background: var(--violet);
		}

		.kpi-top {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 16px;
		}

		.kpi-ico {
			width: 44px;
			height: 44px;
			border-radius: 14px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.2rem;
		}

		.blue .kpi-ico {
			color: var(--blue);
			background: rgba(66, 133, 244, 0.15);
		}

		.green .kpi-ico {
			color: var(--green);
			background: rgba(0, 230, 118, 0.15);
		}

		.amber .kpi-ico {
			color: var(--amber);
			background: rgba(255, 171, 0, 0.15);
		}

		.violet .kpi-ico {
			color: var(--violet);
			background: rgba(124, 77, 255, 0.15);
		}

		.pill {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			padding: 4px 10px;
			border-radius: 999px;
			font-size: 0.68rem;
			font-weight: 800;
			text-transform: uppercase;
		}

		.pill.up {
			background: rgba(0, 230, 118, 0.15);
			color: var(--green);
		}

		.pill.down {
			background: rgba(255, 82, 82, 0.15);
			color: var(--red);
		}

		.pill.flat {
			background: rgba(123, 147, 192, 0.15);
			color: var(--text2);
		}

		.kpi-val {
			font-family: 'Raleway', sans-serif;
			font-size: 2.2rem;
			font-weight: 900;
			line-height: 1;
			margin-bottom: 6px;
		}

		.kpi-lbl {
			font-size: 0.85rem;
			color: var(--text2);
		}

		.kpi-foot {
			margin-top: 14px;
			padding-top: 14px;
			border-top: 1px solid var(--border);
			font-size: 0.75rem;
			color: var(--text3);
			line-height: 1.6;
		}

		.kpi-foot span {
			color: var(--cyan);
			font-weight: 700;
		}

		.health {
			padding: 20px;
			display: flex;
			gap: 24px;
			align-items: center;
			flex-wrap: wrap;
		}

		.health-box {
			min-width: 220px;
		}

		.health-num {
			font-family: 'Raleway', sans-serif;
			font-size: 3rem;
			font-weight: 900;
			line-height: 1;
			color:
				<?= html_escape($health_color) ?>
			;
		}

		.health-bar {
			height: 10px;
			border-radius: 999px;
			background: rgba(255, 255, 255, 0.08);
			overflow: hidden;
			margin-top: 16px;
		}

		.health-bar>div {
			height: 100%;
			width:
				<?= (float) $health_score ?>
				%;
			background: linear-gradient(90deg,
					<?= html_escape($health_color) ?>
					, var(--cyan));
			transition: width 1s ease;
		}

		.chart-card {
			padding: 20px;
		}

		.chart-head {
			display: flex;
			justify-content: space-between;
			gap: 12px;
			align-items: flex-start;
			margin-bottom: 18px;
		}

		.chart-head h4 {
			margin: 0;
			font-size: 1rem;
			font-weight: 600;
		}

		.chart-head p {
			margin: 4px 0 0;
			color: var(--text2);
			font-size: 0.8rem;
		}

		.chart-badge {
			padding: 5px 12px;
			border-radius: 999px;
			font-size: 0.65rem;
			font-weight: 800;
			letter-spacing: .12em;
			text-transform: uppercase;
			border: 1px solid;
		}

		.b-ensemble {
			color: var(--amber);
			border-color: rgba(255, 171, 0, 0.3);
			background: rgba(255, 171, 0, 0.1);
		}

		.b-ai {
			color: var(--cyan);
			border-color: rgba(0, 200, 255, 0.3);
			background: rgba(0, 200, 255, 0.1);
		}

		.b-growth {
			color: var(--green);
			border-color: rgba(0, 230, 118, 0.3);
			background: rgba(0, 230, 118, 0.1);
		}

		.chart-wrap {
			height: 280px;
		}

		@media (max-width: 768px) {
			.chart-wrap {
				height: 220px;
			}
		}

		.stat-list,
		.event-list {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.list-item {
			padding: 14px 16px;
			border: 1px solid var(--border);
			background: rgba(255, 255, 255, 0.02);
			border-radius: 14px;
		}

		.list-title {
			font-weight: 700;
			font-size: 0.95rem;
		}

		.list-sub {
			font-size: 0.8rem;
			color: var(--text2);
			margin-top: 4px;
		}

		.risk-row {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 16px;
		}

		.risk-name {
			width: 130px;
			flex-shrink: 0;
			font-size: 0.8rem;
			color: var(--text2);
		}

		.risk-track {
			flex: 1;
			height: 8px;
			background: rgba(255, 255, 255, 0.06);
			border-radius: 999px;
			overflow: hidden;
		}

		.risk-bar {
			height: 100%;
			width: 0;
			border-radius: 999px;
			transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
		}

		.rb-low {
			background: linear-gradient(90deg, var(--green), #00b050);
		}

		.rb-mid {
			background: linear-gradient(90deg, var(--amber), #ff8f00);
		}

		.rb-high {
			background: linear-gradient(90deg, var(--red), #c62828);
		}

		.risk-pct {
			width: 52px;
			text-align: right;
			font-family: 'JetBrains Mono', monospace;
			font-size: 0.8rem;
			font-weight: 600;
		}

		.rp-low {
			color: var(--green);
		}

		.rp-mid {
			color: var(--amber);
		}

		.rp-high {
			color: var(--red);
		}

		.table {
			width: 100%;
			border-collapse: collapse;
		}

		.table th,
		.table td {
			padding: 12px 8px;
			border-bottom: 1px solid rgba(255, 255, 255, 0.04);
			text-align: left;
			font-size: 0.85rem;
		}

		.table th {
			font-size: 0.68rem;
			letter-spacing: .12em;
			text-transform: uppercase;
			color: var(--text3);
			font-weight: 700;
		}

		.table td {
			color: var(--text2);
			font-family: 'JetBrains Mono', monospace;
		}

		.anom-ok {
			color: var(--green);
			font-weight: 700;
		}

		.anom-flag {
			color: var(--red);
			font-weight: 700;
		}

		.dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			display: inline-block;
			margin-right: 8px;
		}

		.ai-panel {
			display: flex;
			flex-direction: column;
			min-height: 560px;
			overflow: hidden;
		}

		.ai-hdr {
			padding: 18px 20px;
			border-bottom: 1px solid var(--border);
			display: flex;
			gap: 14px;
			align-items: center;
			background: linear-gradient(90deg, rgba(66, 133, 244, 0.08), transparent);
		}

		.ai-orb {
			width: 42px;
			height: 42px;
			border-radius: 50%;
			background: conic-gradient(from 0deg, var(--blue), var(--cyan), var(--violet), var(--blue));
			position: relative;
			flex-shrink: 0;
			animation: spin 4s linear infinite;
		}

		.ai-orb:after {
			content: '';
			position: absolute;
			inset: 3px;
			border-radius: 50%;
			background: var(--card);
		}

		@keyframes spin {
			to {
				transform: rotate(360deg);
			}
		}

		.ai-hdr h4 {
			margin: 0;
			font-size: 1rem;
			font-weight: 600;
		}

		.ai-hdr p {
			margin: 4px 0 0;
			color: var(--text2);
			font-size: 0.75rem;
		}

		.ai-body {
			padding: 18px 20px;
			flex: 1;
			overflow-y: auto;
			display: flex;
			flex-direction: column;
			gap: 14px;
			max-height: 500px;
		}

		.chips {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			padding-bottom: 14px;
			border-bottom: 1px solid var(--border);
		}

		.chip {
			border: 1px solid rgba(66, 133, 244, 0.25);
			background: rgba(66, 133, 244, 0.08);
			color: var(--text2);
			padding: 7px 14px;
			border-radius: 999px;
			font-size: 0.75rem;
			cursor: pointer;
			transition: all 0.2s;
		}

		.chip:hover {
			background: rgba(66, 133, 244, 0.2);
			color: var(--text1);
			border-color: var(--blue);
		}

		.msg {
			display: flex;
			gap: 12px;
		}

		.msg-ico {
			width: 32px;
			height: 32px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
			margin-top: 2px;
			font-size: 0.8rem;
		}

		.ico-ai {
			background: rgba(66, 133, 244, 0.2);
			color: var(--blue);
		}

		.ico-user {
			background: rgba(0, 230, 118, 0.2);
			color: var(--green);
		}

		.bubble {
			flex: 1;
			padding: 12px 16px;
			border-radius: 8px 16px 16px 16px;
			background: var(--surface);
			border: 1px solid var(--border);
			font-size: 0.9rem;
			line-height: 1.6;
			color: var(--text1);
		}

		.bubble.user {
			background: rgba(66, 133, 244, 0.08);
			border-radius: 16px 8px 16px 16px;
			color: var(--text2);
		}

		.bubble strong {
			color: var(--cyan);
		}

		.bubble code {
			font-family: 'JetBrains Mono', monospace;
			background: rgba(66, 133, 244, 0.15);
			padding: 2px 6px;
			border-radius: 6px;
			color: var(--cyan);
		}

		.typing {
			display: flex;
			align-items: center;
			gap: 5px;
		}

		.typing span {
			width: 6px;
			height: 6px;
			border-radius: 50%;
			background: var(--blue);
			animation: dot 1.2s infinite;
		}

		.typing span:nth-child(2) {
			animation-delay: .2s;
		}

		.typing span:nth-child(3) {
			animation-delay: .4s;
		}

		@keyframes dot {
			50% {
				opacity: 1;
				transform: scale(1.2);
			}
		}

		.ai-foot {
			padding: 14px 18px;
			border-top: 1px solid var(--border);
			display: flex;
			gap: 10px;
		}

		.ai-input {
			flex: 1;
			resize: none;
			border: 1px solid var(--border);
			background: var(--surface);
			color: var(--text1);
			border-radius: 14px;
			padding: 12px 16px;
			font-family: 'Outfit', sans-serif;
			outline: none;
			font-size: 0.9rem;
			transition: border-color 0.2s;
		}

		.ai-input:focus {
			border-color: var(--blue);
		}

		.ai-send {
			width: 48px;
			height: 48px;
			border: none;
			border-radius: 14px;
			background: var(--blue);
			color: #fff;
			cursor: pointer;
			font-size: 1rem;
			transition: background 0.2s, transform 0.1s;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.ai-send:hover {
			background: #1a56c8;
		}

		.ai-send:active {
			transform: scale(0.95);
		}

		.ai-send:disabled {
			opacity: 0.6;
			pointer-events: none;
		}

		.spinner {
			width: 20px;
			height: 20px;
			border: 2px solid rgba(255, 255, 255, 0.3);
			border-top-color: #fff;
			border-radius: 50%;
			animation: spin 0.8s linear infinite;
		}

		.actions {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
			margin-bottom: 24px;
		}

		.btnx {
			border: none;
			border-radius: 14px;
			padding: 12px 20px;
			font-weight: 700;
			font-size: 0.85rem;
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			transition: all 0.2s;
		}

		.btn-blue {
			background: linear-gradient(135deg, var(--blue), #1a56c8);
			color: #fff;
			box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
		}

		.btn-green {
			background: linear-gradient(135deg, #00613a, #003d25);
			color: var(--green);
			border: 1px solid rgba(0, 230, 118, 0.35);
		}

		.btn-violet {
			background: linear-gradient(135deg, #3a1a6e, #260d4a);
			color: var(--violet);
			border: 1px solid rgba(124, 77, 255, 0.35);
		}

		.btn-amber {
			background: linear-gradient(135deg, #4a3000, #2e1d00);
			color: var(--amber);
			border: 1px solid rgba(255, 171, 0, 0.35);
		}

		.btnx i {
			font-size: 1rem;
		}

		.small-note {
			color: var(--text3);
			font-size: 0.8rem;
			margin-top: 8px;
		}

		.bubble.error {
			background: rgba(255, 82, 82, 0.15);
			border-color: var(--red);
			color: #ff8a8a;
		}

		@media print {

			.actions,
			.ai-panel,
			.overlay,
			.sidebar,
			.menu-toggle {
				display: none !important;
			}

			.main-content {
				margin: 0 !important;
				padding: 0;
			}

			.ai-page {
				padding: 0;
			}

			.card {
				box-shadow: none;
				border: 1px solid #ccc;
			}
		}

		@media (max-width: 576px) {
			.pg-title {
				font-size: 1.5rem;
			}

			.kpi-val {
				font-size: 1.8rem;
			}

			.health-num {
				font-size: 2.5rem;
			}

			.btnx {
				padding: 10px 14px;
				font-size: 0.75rem;
			}

			.risk-name {
				width: 100px;
				font-size: 0.7rem;
			}

			.risk-pct {
				width: 45px;
				font-size: 0.7rem;
			}

			.ai-body {
				max-height: 400px;
			}
		}
	</style>
</head>

<body>
	<button class="menu-toggle" id="menuToggle">
		<i class="fas fa-bars"></i> Menu
	</button>

	<div class="overlay" id="overlay"></div>
	<?php $activePage = 'ai_insights';
	include('sidebar.php'); ?>

	<div class="main-content">
		<div class="ai-page">
			<div class="pg-header">
				<div>
					<div class="pg-title"><i class="fas fa-brain" style="margin-right:10px"></i>AI Insights</div>
					<div class="pg-sub">
						<?= html_escape($society_name ?? 'Society') ?><?= !empty($society_tagline) ? ' · ' . html_escape($society_tagline) : '' ?>
						· Generated <?= date('d M Y, H:i') ?>
					</div>
				</div>
				<div class="live-badge"><span class="live-dot"></span> Live Intelligence</div>
			</div>

			<div class="actions">
				<button class="btnx btn-blue" type="button" onclick="generateReport(this)"><i class="fas fa-magic"></i>
					Generate AI Report</button>
				<button class="btnx btn-green" type="button" onclick="location.reload()"><i class="fas fa-sync-alt"></i>
					Refresh</button>
				<button class="btnx btn-violet" type="button" onclick="exportSummary()"><i
						class="fas fa-file-arrow-down"></i> Export Summary</button>
				<button class="btnx btn-amber" type="button" onclick="window.print()"><i class="fas fa-print"></i>
					Print</button>
			</div>

			<div class="sec-label"><i class="fas fa-chart-pie"></i> Key Metrics & Health Score</div>
			<div class="grid-4">
				<div class="card kpi blue">
					<div class="kpi-top">
						<div class="kpi-ico"><i class="fas fa-rupee-sign"></i></div>
						<span class="pill <?= ((float) ($pay_growth['avg_growth'] ?? 0)) >= 0 ? 'up' : 'down' ?>">
							<i
								class="fas fa-arrow-<?= ((float) ($pay_growth['avg_growth'] ?? 0)) >= 0 ? 'up' : 'down' ?>"></i>
							<?= abs((float) ($pay_growth['avg_growth'] ?? 0)) ?>%
						</span>
					</div>
					<div class="kpi-val"><?= ai_fmt_k($pay_kpis['total_collected'] ?? 0) ?></div>
					<div class="kpi-lbl">Total Collected</div>
					<div class="kpi-foot">
						Pending: <span><?= ai_fmt_k($pay_kpis['total_pending'] ?? 0) ?></span><br>
						Forecast: <span><?= ai_fmt_k($pay_ensemble[0] ?? 0) ?></span> next month
					</div>
				</div>
				<div class="card kpi green">
					<div class="kpi-top">
						<div class="kpi-ico"><i class="fas fa-users"></i></div>
						<span class="pill flat"><i class="fas fa-circle"></i>
							<?= (float) ($occupancy['rate'] ?? 0) ?>%</span>
					</div>
					<div class="kpi-val"><?= (int) ($member_kpis['active'] ?? 0) ?></div>
					<div class="kpi-lbl">Active Members</div>
					<div class="kpi-foot">
						Total: <span><?= (int) ($member_kpis['total'] ?? 0) ?></span><br>
						Occupancy:
						<span><?= (int) ($occupancy['occupied'] ?? 0) ?>/<?= (int) ($occupancy['total'] ?? 0) ?></span>
					</div>
				</div>
				<div class="card kpi amber">
					<div class="kpi-top">
						<div class="kpi-ico"><i class="fas fa-triangle-exclamation"></i></div>
						<span class="pill <?= ((float) ($comp_growth['avg_growth'] ?? 0)) <= 0 ? 'up' : 'down' ?>">
							<i
								class="fas fa-arrow-<?= ((float) ($comp_growth['avg_growth'] ?? 0)) <= 0 ? 'down' : 'up' ?>"></i>
							Trend
						</span>
					</div>
					<div class="kpi-val"><?= (int) ($comp_kpis['open_count'] ?? 0) ?></div>
					<div class="kpi-lbl">Open Complaints</div>
					<div class="kpi-foot">
						Resolved: <span><?= (int) ($comp_kpis['resolved_count'] ?? 0) ?></span><br>
						Rate: <span><?= (float) $resolution_rate ?>%</span>
					</div>
				</div>
				<div class="card kpi violet">
					<div class="kpi-top">
						<div class="kpi-ico"><i class="fas fa-door-open"></i></div>
						<span class="pill <?= ((float) ($visitor_growth['avg_growth'] ?? 0)) >= 0 ? 'up' : 'down' ?>">
							<i
								class="fas fa-arrow-<?= ((float) ($visitor_growth['avg_growth'] ?? 0)) >= 0 ? 'up' : 'down' ?>"></i>
							Visitors
						</span>
					</div>
					<div class="kpi-val"><?= (int) ($visitor_kpis['this_month'] ?? 0) ?></div>
					<div class="kpi-lbl">Visitors This Month</div>
					<div class="kpi-foot">
						Today: <span><?= (int) ($visitor_kpis['today'] ?? 0) ?></span><br>
						Forecast: <span><?= round((float) ($visitor_ensemble[0] ?? 0)) ?></span> next month
					</div>
				</div>
			</div>

			<div class="grid-2" style="margin-top:20px">
				<div class="card health">
					<div class="health-box">
						<div class="small-note" style="text-transform:uppercase;letter-spacing:.16em;font-weight:800">
							Society Health Score</div>
						<div class="health-num"><?= $health_score ?></div>
						<div style="color:<?= html_escape($health_color) ?>;font-weight:800;font-size:1.1rem">
							<?= html_escape($health_label) ?></div>
						<div class="health-bar">
							<div></div>
						</div>
						<div class="small-note">Based on payments, complaints, occupancy and staff activity</div>
					</div>
					<div style="flex:1">
						<div style="display:flex;flex-wrap:wrap;gap:8px">
							<span class="pill up"><i class="fas fa-robot"></i> ML Composite</span>
							<span class="pill <?= $health_score >= 65 ? 'up' : 'down' ?>"><i
									class="fas fa-chart-line"></i> <?= html_escape($health_label) ?></span>
						</div>
						<div style="margin-top:12px;color:var(--text2);font-size:.9rem;line-height:1.7">
							Financial risk is <strong style="color:var(--text1)"><?= (int) $payment_risk ?>%</strong>,
							complaint risk is <strong style="color:var(--text1)"><?= (int) $complaint_risk ?>%</strong>,
							and occupancy risk is <strong
								style="color:var(--text1)"><?= (int) $occupancy_risk ?>%</strong>.
						</div>
					</div>
				</div>
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Upcoming Events</h4>
							<p>Next events scheduled for the society</p>
						</div>
						<span class="chart-badge b-growth">Events</span>
					</div>
					<div class="event-list">
						<?php if (!empty($upcoming_events)): ?>
							<?php foreach ($upcoming_events as $ev): ?>
								<div class="list-item">
									<div class="list-title"><?= html_escape($ev['title'] ?? 'Event') ?></div>
									<div class="list-sub">
										<?= !empty($ev['event_date']) ? date('d M Y', strtotime($ev['event_date'])) : '—' ?>
										<?php if (!empty($ev['start_time'])): ?> ·
											<?= date('h:i A', strtotime($ev['start_time'])) ?>		<?php endif; ?>
										<?php if (!empty($ev['event_type'])): ?> ·
											<?= html_escape(ucfirst($ev['event_type'])) ?>		<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<div class="list-item">
								<div class="list-title">No upcoming events</div>
								<div class="list-sub">Create an event to see it here.</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="sec-label"><i class="fas fa-chart-line"></i> Trends & Forecasts</div>
			<div class="grid-2">
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Payments Forecast</h4>
							<p>Historical monthly collections and ensemble prediction</p>
						</div><span class="chart-badge b-ensemble">Ensemble AI</span>
					</div>
					<div class="chart-wrap"><canvas id="payChart"></canvas></div>
				</div>
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Complaints Forecast</h4>
							<p>Monthly complaint volume and AI prediction</p>
						</div><span class="chart-badge b-ai">AI Forecast</span>
					</div>
					<div class="chart-wrap"><canvas id="compChart"></canvas></div>
				</div>
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Visitor Forecast</h4>
							<p>Visitor traffic trends</p>
						</div><span class="chart-badge b-ai">AI Forecast</span>
					</div>
					<div class="chart-wrap"><canvas id="visChart"></canvas></div>
				</div>
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Member Growth</h4>
							<p>New member registrations and projection</p>
						</div><span class="chart-badge b-growth">Growth</span>
					</div>
					<div class="chart-wrap"><canvas id="memChart"></canvas></div>
				</div>
			</div>

			<div class="sec-label"><i class="fas fa-shield-halved"></i> Risk & Anomaly Detection</div>
			<div class="grid-21">
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Risk Assessment</h4>
							<p>Quick risk meter for core society metrics</p>
						</div><span class="chart-badge b-ensemble">Risk</span>
					</div>
					<?php
					$risks = [
						['Payment Default', $payment_risk],
						['Complaint Load', $complaint_risk],
						['Vacancy Risk', $occupancy_risk],
						['Collection Gap', max(0, 100 - ai_pct((float) ($pay_kpis['total_collected'] ?? 0), max(1, (float) ($pay_kpis['total_collected'] ?? 0) + (float) ($pay_kpis['total_pending'] ?? 0))))],
					];
					foreach ($risks as $r):
						$v = min(100, (float) $r[1]);
						$class = $v > 60 ? 'rb-high' : ($v > 30 ? 'rb-mid' : 'rb-low');
						$pctClass = $v > 60 ? 'rp-high' : ($v > 30 ? 'rp-mid' : 'rp-low');
						?>
						<div class="risk-row">
							<div class="risk-name"><?= html_escape($r[0]) ?></div>
							<div class="risk-track">
								<div class="risk-bar <?= $class ?>" data-w="<?= $v ?>%"></div>
							</div>
							<div class="risk-pct <?= $pctClass ?>"><?= (int) $v ?>%</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>3-Month Outlook</h4>
							<p>Summary of predicted metrics</p>
						</div><span class="chart-badge b-growth">Forecast</span>
					</div>
					<div class="stat-list">
						<?php foreach ($future_labels as $i => $label): ?>
							<div class="list-item">
								<div class="list-title"><?= html_escape($label) ?></div>
								<div class="list-sub">
									Payments <?= ai_fmt_k($pay_ensemble[$i] ?? 0) ?> · Complaints
									<?= round((float) ($comp_ensemble[$i] ?? 0)) ?> · Visitors
									<?= round((float) ($visitor_ensemble[$i] ?? 0)) ?>
								</div>
								<div class="list-sub">
									Payment CI:
									<?= ai_fmt_k($pay_ci[$i]['low'] ?? 0) ?>–<?= ai_fmt_k($pay_ci[$i]['high'] ?? 0) ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="grid-2" style="margin-top:20px">
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Payment Anomalies</h4>
							<p>Outliers in payment collection history</p>
						</div><span class="chart-badge b-ai">Z-Score</span>
					</div>
					<table class="table">
						<thead>
							<tr>
								<th>Month</th>
								<th>Amount</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($pay_rows)): ?>
								<?php foreach ($pay_rows as $i => $r): ?>
									<tr>
										<td><?= html_escape($r['label'] ?? '') ?></td>
										<td><?= ai_fmt_k($r['collected'] ?? 0) ?></td>
										<td>
											<?php if (!empty($pay_anomalies[$i])): ?>
												<span class="anom-flag"><span class="dot"
														style="background:var(--red)"></span>Anomaly</span>
											<?php else: ?>
												<span class="anom-ok"><span class="dot"
														style="background:var(--green)"></span>Normal</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="3">No payment data</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
				<div class="card chart-card">
					<div class="chart-head">
						<div>
							<h4>Complaint Anomalies</h4>
							<p>Outliers in complaint volume history</p>
						</div><span class="chart-badge b-ai">Z-Score</span>
					</div>
					<table class="table">
						<thead>
							<tr>
								<th>Month</th>
								<th>Count</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($comp_rows)): ?>
								<?php foreach ($comp_rows as $i => $r): ?>
									<tr>
										<td><?= html_escape($r['label'] ?? '') ?></td>
										<td><?= (int) ($r['total'] ?? 0) ?></td>
										<td>
											<?php if (!empty($comp_anomalies[$i])): ?>
												<span class="anom-flag"><span class="dot"
														style="background:var(--red)"></span>Spike</span>
											<?php else: ?>
												<span class="anom-ok"><span class="dot"
														style="background:var(--green)"></span>Normal</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="3">No complaint data</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="sec-label"><i class="fas fa-robot"></i> AI Assistant</div>
			<div class="card ai-panel">
				<div class="ai-hdr">
					<div class="ai-orb"></div>
					<div>
						<h4>Society AI Assistant</h4>
						<p>Context-aware · Data-grounded · Real-time analysis</p>
					</div>
				</div>
				<div class="ai-body" id="aiBody">
					<div class="chips">
						<?php foreach (['Summarize my society health', 'Biggest payment risks?', 'How to reduce complaints?', 'Predict next quarter revenue', 'Action plan for this month'] as $c): ?>
							<button class="chip" type="button"
								onclick="sendChip(this.textContent)"><?= html_escape($c) ?></button>
						<?php endforeach; ?>
					</div>
					<div class="msg">
						<div class="msg-ico ico-ai"><i class="fas fa-robot"></i></div>
						<div class="bubble">
							Hello! I reviewed <strong><?= html_escape($society_name ?? 'your society') ?></strong>.
							Health score is <code><?= $health_score ?></code>.
							Payments: <code><?= ai_fmt_k($pay_kpis['total_collected'] ?? 0) ?></code>, Pending:
							<code><?= ai_fmt_k($pay_kpis['total_pending'] ?? 0) ?></code>.
							Open complaints: <code><?= (int) ($comp_kpis['open_count'] ?? 0) ?></code>.
						</div>
					</div>
				</div>
				<div class="ai-foot">
					<textarea class="ai-input" id="aiInput" rows="1"
						placeholder="Ask about payments, predictions, complaints, risk…"
						onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg();}"></textarea>
					<button class="ai-send" id="sendBtn" type="button" onclick="sendMsg()"><i
							class="fas fa-paper-plane"></i></button>
				</div>
			</div>
			<div class="small-note" style="margin-top:12px">AI insights are based on historical data and may vary.
				Regular updates improve accuracy.</div>
		</div>
	</div>

	<input type="hidden" id="csrf_name" value="<?= $csrf_name ?>">
	<input type="hidden" id="csrf_hash" value="<?= $csrf_hash ?>">

	<script>
		(function () {
			const sidebar = document.querySelector('.sidebar');
			const overlay = document.getElementById('overlay');
			const menuToggle = document.getElementById('menuToggle');

			function openSidebar() {
				if (!sidebar) return;
				sidebar.classList.add('mobile-open');
				overlay.classList.add('on');
			}
			function closeSidebar() {
				if (!sidebar) return;
				sidebar.classList.remove('mobile-open');
				overlay.classList.remove('on');
			}
			if (menuToggle) menuToggle.addEventListener('click', openSidebar);
			if (overlay) overlay.addEventListener('click', closeSidebar);
			window.addEventListener('resize', function () {
				if (window.innerWidth > 992) closeSidebar();
			});
		})();
	</script>

	<script src="<?= base_url('assets/js/main.js') ?>"></script>
	<script>
		(function () {
			const D = {
				payLabels: <?= json_encode($pay_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				payVals: <?= json_encode($pay_vals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				payEnsemble: <?= json_encode($pay_ensemble, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				payLR: <?= json_encode($pay_lr ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				payES: <?= json_encode($pay_es ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,

				compLabels: <?= json_encode($comp_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				compVals: <?= json_encode($comp_vals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				compEnsemble: <?= json_encode($comp_ensemble, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				compLR: <?= json_encode($comp_lr ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,

				visLabels: <?= json_encode($visitor_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				visVals: <?= json_encode($visitor_vals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				visEnsemble: <?= json_encode($visitor_ensemble, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,

				memLabels: <?= json_encode($member_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				memVals: <?= json_encode($member_vals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				memEnsemble: <?= json_encode($member_ensemble, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,

				futureLabels: <?= json_encode($future_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				societyName: <?= json_encode($society_name ?? 'Society', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
				collected: <?= json_encode((float) ($pay_kpis['total_collected'] ?? 0)) ?>,
				pending: <?= json_encode((float) ($pay_kpis['total_pending'] ?? 0)) ?>,
				members: <?= json_encode((int) ($member_kpis['total'] ?? 0)) ?>,
				activeMembers: <?= json_encode((int) ($member_kpis['active'] ?? 0)) ?>,
				openComplaints: <?= json_encode((int) ($comp_kpis['open_count'] ?? 0)) ?>,
				occupancyRate: <?= json_encode((float) ($occupancy['rate'] ?? 0)) ?>,
				healthScore: <?= json_encode((float) $health_score) ?>
			};

			let CSRF = {
				name: document.getElementById('csrf_name').value,
				hash: document.getElementById('csrf_hash').value
			};

			async function refreshCsrf() {
				try {
					const res = await fetch('<?= site_url('ai_insights/refresh_csrf') ?>', {
						headers: { 'X-Requested-With': 'XMLHttpRequest' }
					});
					const data = await res.json();
					if (data.csrf_name) {
						CSRF.name = data.csrf_name;
						CSRF.hash = data.csrf_hash;
						document.getElementById('csrf_name').value = data.csrf_name;
						document.getElementById('csrf_hash').value = data.csrf_hash;
					}
				} catch (e) { console.warn('CSRF refresh failed'); }
			}

			function safeArr(v) { return Array.isArray(v) ? v : []; }
			function buildLabels(hist, future) { return safeArr(hist).concat(safeArr(future)); }
			function actualSeries(vals, futureLen) {
				vals = safeArr(vals); futureLen = Math.max(0, Number(futureLen) || 0);
				return vals.concat(Array(futureLen).fill(null));
			}
			function predSeries(vals, preds, futureLen) {
				vals = safeArr(vals); preds = safeArr(preds);
				futureLen = Math.max(0, Number(futureLen) || 0);
				const future = Array.from({ length: futureLen }, (_, i) => (typeof preds[i] === 'number' ? preds[i] : (preds[i] ?? null)));
				if (!vals.length) return future;
				return Array(Math.max(0, vals.length - 1)).fill(null).concat(vals[vals.length - 1], future);
			}
			function fmtMoney(v) {
				v = Number(v) || 0;
				return v >= 1000 ? '₹' + (v / 1000).toFixed(1) + 'K' : '₹' + Math.round(v).toLocaleString('en-IN');
			}
			function fmtNum(v) { return Math.round(Number(v) || 0).toLocaleString('en-IN'); }

			function chartOptions(yFmt) {
				return {
					responsive: true,
					maintainAspectRatio: false,
					interaction: { mode: 'index', intersect: false },
					plugins: {
						legend: { position: 'top', labels: { boxWidth: 10, padding: 14, font: { size: 11 } } },
						tooltip: {
							backgroundColor: '#161e35',
							borderColor: 'rgba(66,133,244,0.3)',
							borderWidth: 1,
							callbacks: { label: ctx => ' ' + yFmt(ctx.raw || 0) }
						}
					},
					scales: {
						x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { font: { size: 10 } } },
						y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { font: { size: 10 }, callback: v => yFmt(v) } }
					}
				};
			}

			function gradient(ctx, c1, c2) {
				const g = ctx.createLinearGradient(0, 0, 0, 240);
				g.addColorStop(0, c1);
				g.addColorStop(1, c2);
				return g;
			}

			function withAlpha(color, alpha) {
				if (typeof color !== 'string') return color;
				if (color.startsWith('rgba(')) {
					return color.replace(/rgba\(([^)]+?),\s*[0-9.]+\)/, 'rgba($1, ' + alpha + ')');
				}
				if (color.startsWith('rgb(')) {
					return color.replace(/rgb\(([^)]+)\)/, 'rgba($1, ' + alpha + ')');
				}
				return color;
			}

			function makeLineChart(canvasId, labels, actual, pred, yFmt, actualLabel, predLabel, actualColor, predColor) {
				const el = document.getElementById(canvasId);
				if (!el) return;
				const ctx = el.getContext('2d');
				new Chart(ctx, {
					type: 'line',
					data: {
						labels: labels,
						datasets: [
							{
								label: actualLabel,
								data: actual,
								borderColor: actualColor,
								backgroundColor: gradient(ctx, withAlpha(actualColor, 0.18), withAlpha(actualColor, 0.01)),
								fill: true,
								tension: .4,
								borderWidth: 2.4,
								pointRadius: 4
							},
							{
								label: predLabel,
								data: pred,
								borderColor: predColor,
								backgroundColor: 'transparent',
								borderDash: [6, 4],
								fill: false,
								tension: .4,
								borderWidth: 2,
								pointRadius: 5
							}
						]
					},
					options: chartOptions(yFmt)
				});
			}

			(function () {
				const futureLen = safeArr(D.futureLabels).length || 3;
				makeLineChart('payChart', buildLabels(D.payLabels, D.futureLabels), actualSeries(D.payVals, futureLen), predSeries(D.payVals, D.payEnsemble, futureLen), v => fmtMoney(v), 'Actual (₹)', 'Ensemble Prediction (₹)', 'rgba(66,133,244,1)', 'rgba(0,200,255,1)');
				makeLineChart('compChart', buildLabels(D.compLabels, D.futureLabels), actualSeries(D.compVals, futureLen), predSeries(D.compVals, D.compEnsemble, futureLen), v => fmtNum(v), 'Actual Complaints', 'AI Forecast', 'rgba(124,77,255,1)', 'rgba(0,200,255,1)');
				makeLineChart('visChart', buildLabels(D.visLabels, D.futureLabels), actualSeries(D.visVals, futureLen), predSeries(D.visVals, D.visEnsemble, futureLen), v => fmtNum(v), 'Actual Visitors', 'AI Forecast', 'rgba(0,200,255,1)', 'rgba(0,230,118,1)');
				makeLineChart('memChart', buildLabels(D.memLabels, D.futureLabels), actualSeries(D.memVals, futureLen), predSeries(D.memVals, D.memEnsemble, futureLen), v => '+' + fmtNum(v), 'New Members', 'Growth Forecast', 'rgba(0,230,118,1)', 'rgba(255,171,0,1)');

				document.querySelectorAll('.risk-bar').forEach(bar => {
					const w = bar.dataset.w || '0%';
					setTimeout(() => { bar.style.width = w; }, 300);
				});
			})();

			const aiBody = document.getElementById('aiBody');
			const aiInput = document.getElementById('aiInput');
			const sendBtn = document.getElementById('sendBtn');
			const chatHistory = [];

			function markdownToHtml(text) {
				return String(text || '')
					.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
					.replace(/`(.*?)`/g, '<code>$1</code>')
					.replace(/^- (.+)$/gm, '• $1')
					.replace(/\n/g, '<br>');
			}
			function addMessage(role, text, isError = false) {
				const row = document.createElement('div');
				row.className = 'msg';
				const bubbleClass = isError ? 'bubble error' : 'bubble' + (role === 'user' ? ' user' : '');
				row.innerHTML = `<div class="msg-ico ${role === 'assistant' ? 'ico-ai' : 'ico-user'}"><i class="fas fa-${role === 'assistant' ? 'robot' : 'user'}"></i></div><div class="${bubbleClass}">${markdownToHtml(text)}</div>`;
				aiBody.appendChild(row);
				aiBody.scrollTop = aiBody.scrollHeight;
			}
			function addTyping() {
				const id = 'typing_' + Date.now();
				const row = document.createElement('div');
				row.id = id; row.className = 'msg';
				row.innerHTML = `<div class="msg-ico ico-ai"><i class="fas fa-robot"></i></div><div class="bubble"><div class="typing"><span></span><span></span><span></span></div></div>`;
				aiBody.appendChild(row);
				aiBody.scrollTop = aiBody.scrollHeight;
				return id;
			}
			function removeTyping(id) { const el = document.getElementById(id); if (el) el.remove(); }

			async function sendMsg() {
				const txt = aiInput.value.trim();
				if (!txt) return;
				aiInput.value = '';
				const originalBtnHtml = sendBtn.innerHTML;
				sendBtn.disabled = true;
				sendBtn.innerHTML = '<span class="spinner"></span>';

				addMessage('user', txt);
				chatHistory.push({ role: 'user', content: txt });
				const typingId = addTyping();
				try {
					const res = await fetch('<?= site_url('ai_insights/chat') ?>', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
						body: new URLSearchParams({ message: txt, history: JSON.stringify(chatHistory.slice(-8)), [CSRF.name]: CSRF.hash })
					});
					const data = await res.json();
					removeTyping(typingId);
					if (data.csrf_hash) {
						CSRF.name = data.csrf_name;
						CSRF.hash = data.csrf_hash;
						document.getElementById('csrf_name').value = data.csrf_name;
						document.getElementById('csrf_hash').value = data.csrf_hash;
					}
					if (data.success) {
						addMessage('assistant', data.reply);
						chatHistory.push({ role: 'assistant', content: data.reply });
					} else {
						const errMsg = '⚠ ' + (data.error || 'AI unavailable');
						addMessage('assistant', errMsg, true);
					}
				} catch (e) {
					removeTyping(typingId);
					addMessage('assistant', '⚠ Network error. Please try again.', true);
				} finally {
					sendBtn.disabled = false;
					sendBtn.innerHTML = originalBtnHtml;
				}
			}

			function sendChip(text) { aiInput.value = text; sendMsg(); }

			async function generateReport(btn) {
				const button = btn || document.activeElement;
				const originalText = button ? button.innerHTML : '';
				if (button) {
					button.disabled = true;
					button.innerHTML = '<span class="spinner" style="width:16px;height:16px;"></span> Generating...';
				}
				try {
					const res = await fetch('<?= site_url('ai_insights/generate_report') ?>', {
						method: 'POST',
						headers: { 'X-Requested-With': 'XMLHttpRequest' },
						body: new URLSearchParams({ [CSRF.name]: CSRF.hash })
					});
					const data = await res.json();
					if (data.csrf_hash) {
						CSRF.name = data.csrf_name;
						CSRF.hash = data.csrf_hash;
						document.getElementById('csrf_name').value = data.csrf_name;
						document.getElementById('csrf_hash').value = data.csrf_hash;
					}
					if (data.success) {
						addMessage('assistant', data.report);
						chatHistory.push({ role: 'assistant', content: data.report });
					} else {
						addMessage('assistant', '⚠ ' + (data.error || 'Could not generate report.'), true);
					}
				} catch (e) {
					addMessage('assistant', '⚠ Network error while generating report.', true);
				} finally {
					if (button) {
						button.disabled = false;
						button.innerHTML = originalText;
					}
				}
			}

			function exportSummary() {
				const lines = [
					`AI INSIGHTS REPORT — ${D.societyName}`,
					`Generated: ${new Date().toLocaleString('en-IN')}`,
					'', `Health Score: ${D.healthScore}/100`,
					`Collected: ${fmtMoney(D.collected)}`, `Pending: ${fmtMoney(D.pending)}`,
					`Members: ${fmtNum(D.members)} (Active: ${fmtNum(D.activeMembers)})`,
					`Open Complaints: ${fmtNum(D.openComplaints)}`, `Occupancy: ${D.occupancyRate}%`,
					'', 'Forecasts:',
					...safeArr(D.futureLabels).map((label, i) => `${label}: Payments ${fmtMoney(D.payEnsemble[i] || 0)} | Complaints ${fmtNum(D.compEnsemble[i] || 0)} | Visitors ${fmtNum(D.visEnsemble[i] || 0)}`)
				];
				const blob = new Blob([lines.join('\n')], { type: 'text/plain' });
				const a = document.createElement('a');
				a.href = URL.createObjectURL(blob);
				a.download = `AI_Insights_${new Date().toISOString().slice(0, 10)}.txt`;
				a.click();
				URL.revokeObjectURL(a.href);
			}

			window.sendMsg = sendMsg;
			window.sendChip = sendChip;
			window.generateReport = generateReport;
			window.exportSummary = exportSummary;
			window.refreshCsrf = refreshCsrf;
		})();
	</script>
</body>

</html>
