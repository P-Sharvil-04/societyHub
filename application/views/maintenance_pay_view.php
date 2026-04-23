<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$CI =& get_instance();
$CI->load->database();

$society_id = (int) $CI->session->userdata('society_id');
$user_id = (int) ($CI->session->userdata('member_id') ?: $CI->session->userdata('user_id'));

$amount = isset($amount) ? (float) $amount : 0;
$month = isset($month) ? $month : date('F');
$year = isset($year) ? (int) $year : (int) date('Y');

$userName = $CI->session->userdata('user_name') ?: ($CI->session->userdata('member_name') ?: 'Guest');
$flatNo = $CI->session->userdata('flat_no') ?: ($CI->session->userdata('member_flat_no') ?: '-');

$maintenance_due_date = null;
if ($society_id > 0) {
	$dueRow = $CI->db->select('setting_value')
		->from('society_settings')
		->where('society_id', $society_id)
		->where('setting_key', 'maintenance_due_date')
		->limit(1)
		->get()
		->row_array();

	if ($dueRow && isset($dueRow['setting_value']) && is_numeric($dueRow['setting_value'])) {
		$maintenance_due_date = (int) $dueRow['setting_value'];
	}
}

function day_suffix_local($d)
{
	if (!in_array(($d % 100), [11, 12, 13])) {
		switch ($d % 10) {
			case 1:
				return 'st';
			case 2:
				return 'nd';
			case 3:
				return 'rd';
		}
	}
	return 'th';
}

$successMsg = $this->session->flashdata('success');
$errorMsg = $this->session->flashdata('error');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
	<title>Maintenance Payment - SocietyHub</title>

	<link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
	<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

	<style>
		:root {
			--sidebar-width: 260px;
			--page-bg: #f8fafc;
			--card-bg: #ffffff;
			--border: #e2e8f0;
			--muted: #64748b;
			--text: #1e293b;
			--primary: #6366f1;
			--primary-dark: #4f46e5;
			--success-bg: rgba(34, 197, 94, 0.10);
			--success-border: rgba(34, 197, 94, 0.22);
			--danger-bg: rgba(239, 68, 68, 0.10);
			--danger-border: rgba(239, 68, 68, 0.22);
		}

		* {
			box-sizing: border-box;
		}

		html,
		body {
			margin: 0;
			padding: 0;
			width: 100%;
			min-height: 100%;
			overflow-x: hidden;
			background: var(--page-bg);
			color: var(--text);
			font-family: inherit;
		}

		body {
			background: var(--page-bg);
		}

		/* Keep page away from sidebar on desktop */
		.page-shell {
			margin-left: var(--sidebar-width);
			padding: 24px 24px 32px;
			min-height: 100vh;
			margin-top: 80px;
		}

		@media (min-width: 769px) {
			.page-shell {
				margin-top: 90px;
				padding: 28px 32px 36px;
			}
		}

		@media (max-width: 991px) {
			.page-shell {
				margin-left: 0;
			}
		}

		.maint-page {
			max-width: 1200px;
			margin: 0 auto;
		}

		.maint-wrap {
			display: grid;
			grid-template-columns: minmax(0, 1fr);
			gap: 24px;
			align-items: start;
		}

		@media (min-width: 992px) {
			.maint-wrap {
				grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
			}
		}

		.card {
			background: var(--card-bg);
			border: 1px solid var(--border);
			border-radius: 24px;
			box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
		}

		.maint-hero,
		.maint-card,
		.maint-side {
			padding: 24px;
		}

		@media (max-width: 480px) {

			.maint-hero,
			.maint-card,
			.maint-side {
				padding: 18px;
				border-radius: 20px;
			}

			.page-shell {
				padding: 16px;
				margin-top: 72px;
			}
		}

		.maint-head {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 16px;
			margin-bottom: 18px;
		}

		.maint-title {
			margin: 0;
			font-size: clamp(1.25rem, 2.2vw, 1.75rem);
			font-weight: 800;
			line-height: 1.2;
			color: var(--text);
		}

		.maint-subtitle {
			margin: 8px 0 0;
			color: var(--muted);
			font-size: 0.95rem;
			line-height: 1.6;
		}

		.maint-badge {
			width: 54px;
			height: 54px;
			border-radius: 18px;
			display: grid;
			place-items: center;
			background: rgba(99, 102, 241, 0.10);
			color: var(--primary);
			font-size: 1.4rem;
			flex: 0 0 auto;
		}

		.maint-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
			gap: 14px;
			margin-top: 18px;
		}

		.info-box {
			background: #f8fafc;
			border: 1px solid var(--border);
			border-radius: 16px;
			padding: 14px 16px;
			min-width: 0;
		}

		.info-label {
			display: block;
			margin-bottom: 6px;
			font-size: 0.72rem;
			font-weight: 800;
			letter-spacing: 0.5px;
			text-transform: uppercase;
			color: var(--muted);
		}

		.info-value {
			font-size: 1rem;
			font-weight: 700;
			color: var(--text);
			word-break: break-word;
		}

		.info-value.amount {
			font-size: 1.4rem;
			color: #0f172a;
		}

		.maint-note {
			margin-top: 16px;
			padding: 14px 16px;
			border-left: 4px solid var(--primary);
			border-radius: 14px;
			background: rgba(99, 102, 241, 0.06);
			color: var(--text);
			line-height: 1.55;
			font-size: 0.92rem;
		}

		.pay-method-title,
		.side-title {
			margin: 0 0 18px;
			font-size: 1.05rem;
			font-weight: 800;
			color: var(--text);
		}

		.radio-list {
			display: grid;
			gap: 12px;
			margin-bottom: 20px;
		}

		.radio-item {
			display: flex;
			align-items: center;
			gap: 14px;
			padding: 14px 16px;
			background: #f8fafc;
			border: 1px solid var(--border);
			border-radius: 16px;
			cursor: pointer;
			transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease;
			user-select: none;
		}

		.radio-item:hover {
			transform: translateY(-1px);
			background: #eef2ff;
			border-color: rgba(99, 102, 241, 0.28);
		}

		.radio-item input[type="radio"] {
			margin: 0;
			width: 18px;
			height: 18px;
			accent-color: var(--primary);
			flex: 0 0 auto;
		}

		.radio-text {
			display: flex;
			flex-direction: column;
			gap: 2px;
			min-width: 0;
		}

		.radio-text strong {
			font-size: 0.95rem;
			color: var(--text);
		}

		.radio-text span {
			font-size: 0.8rem;
			color: var(--muted);
			line-height: 1.4;
		}

		.pay-btn {
			width: 100%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			border: none;
			border-radius: 16px;
			padding: 14px 16px;
			font-size: 1rem;
			font-weight: 800;
			color: #fff;
			cursor: pointer;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			box-shadow: 0 10px 22px rgba(99, 102, 241, 0.24);
			transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
		}

		.pay-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 14px 28px rgba(99, 102, 241, 0.28);
		}

		.pay-btn:active {
			transform: translateY(0);
		}

		.pay-btn:disabled {
			opacity: 0.75;
			cursor: not-allowed;
			transform: none;
			box-shadow: none;
		}

		.small-link {
			display: inline-block;
			margin-top: 16px;
			color: var(--primary);
			text-decoration: none;
			font-size: 0.9rem;
			font-weight: 700;
		}

		.small-link:hover {
			text-decoration: underline;
		}

		.maint-side {
			position: sticky;
			top: 100px;
		}

		@media (max-width: 991px) {
			.maint-side {
				position: static;
			}
		}

		.side-item {
			padding: 12px 0;
			border-bottom: 1px solid var(--border);
		}

		.side-item:last-child {
			border-bottom: none;
			padding-bottom: 0;
		}

		.side-item .k {
			display: block;
			margin-bottom: 4px;
			font-size: 0.72rem;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: var(--muted);
		}

		.side-item .v {
			font-size: 0.95rem;
			font-weight: 700;
			color: var(--text);
			line-height: 1.5;
			word-break: break-word;
		}

		.side-item .v code {
			display: inline-block;
			padding: 2px 7px;
			border-radius: 8px;
			background: #f1f5f9;
			color: var(--primary);
			font-size: 0.78rem;
			font-weight: 700;
		}

		.alert-success,
		.alert-danger {
			margin-bottom: 18px;
			padding: 12px 14px;
			border-radius: 14px;
			font-size: 0.92rem;
			font-weight: 700;
			line-height: 1.5;
		}

		.alert-success {
			background: var(--success-bg);
			border: 1px solid var(--success-border);
			color: #166534;
		}

		.alert-danger {
			background: var(--danger-bg);
			border: 1px solid var(--danger-border);
			color: #991b1b;
		}

		.section-spacer {
			margin-top: 24px;
		}

		@media (max-width: 480px) {
			.maint-head {
				gap: 12px;
			}

			.maint-badge {
				width: 48px;
				height: 48px;
				border-radius: 16px;
			}

			.maint-grid {
				grid-template-columns: 1fr;
			}

			.info-value.amount {
				font-size: 1.2rem;
			}

			.radio-item {
				padding: 12px 14px;
			}

			.radio-text strong {
				font-size: 0.92rem;
			}
		}
	</style>
</head>

<body>

	<?php include('sidebar.php'); ?>

	<div class="page-shell">
		<div class="maint-page">
			<div class="maint-wrap">
				<div>
					<div class="card maint-hero">
						<div class="maint-head">
							<div>
								<h1 class="maint-title">Maintenance Payment</h1>
								<p class="maint-subtitle">
									Pay your monthly maintenance for
									<strong><?= htmlspecialchars($month . ' ' . $year, ENT_QUOTES, 'UTF-8') ?></strong>.
									Flat: <strong><?= htmlspecialchars($flatNo, ENT_QUOTES, 'UTF-8') ?></strong>.
								</p>
							</div>
							<div class="maint-badge">₹</div>
						</div>

						<?php if (!empty($successMsg)): ?>
							<div class="alert-success">
								<?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?>
							</div>
						<?php endif; ?>

						<?php if (!empty($errorMsg)): ?>
							<div class="alert-danger">
								<?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
							</div>
						<?php endif; ?>

						<div class="maint-grid">
							<div class="info-box">
								<span class="info-label">Resident</span>
								<div class="info-value"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
							</div>

							<div class="info-box">
								<span class="info-label">Billing Month</span>
								<div class="info-value">
									<?= htmlspecialchars($month . ' ' . $year, ENT_QUOTES, 'UTF-8') ?></div>
							</div>

							<div class="info-box">
								<span class="info-label">Maintenance Amount</span>
								<div class="info-value amount">₹<?= number_format($amount, 2) ?></div>
							</div>

							<div class="info-box">
								<span class="info-label">Due Date</span>
								<div class="info-value">
									<?= $maintenance_due_date !== null
										? (int) $maintenance_due_date . day_suffix_local((int) $maintenance_due_date) . ' of every month'
										: 'Not set' ?>
								</div>
							</div>
						</div>

						<div class="maint-note">
							This payment is valid only for this month. Once the month changes, the payment status will
							automatically be treated as unpaid for the new month.
						</div>
					</div>

					<div class="card maint-card section-spacer">
						<h2 class="pay-method-title">Choose payment method</h2>

						<form action="<?= site_url('payment_controllerr/pay_now') ?>" method="post"
							id="maintenancePayForm">
							<div class="radio-list">
								<label class="radio-item">
									<input type="radio" name="payment_method" value="razorpay" checked>
									<div class="radio-text">
										<strong>Razorpay</strong>
										<span>Card, UPI, Net Banking, Wallet</span>
									</div>
								</label>

								<label class="radio-item">
									<input type="radio" name="payment_method" value="upi">
									<div class="radio-text">
										<strong>UPI</strong>
										<span>Pay directly using any UPI app</span>
									</div>
								</label>

								<label class="radio-item">
									<input type="radio" name="payment_method" value="cash">
									<div class="radio-text">
										<strong>Cash / Manual entry</strong>
										<span>Admin will record this payment manually</span>
									</div>
								</label>
							</div>

							<input type="hidden" name="month"
								value="<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>">
							<input type="hidden" name="year" value="<?= (int) $year ?>">
							<input type="hidden" name="amount"
								value="<?= htmlspecialchars((string) $amount, ENT_QUOTES, 'UTF-8') ?>">

							<button type="submit" class="pay-btn" id="payBtn">
								Pay ₹<?= number_format($amount, 2) ?> now
							</button>
						</form>

						<a href="<?= site_url('dashboard') ?>" class="small-link">← Back to dashboard</a>
					</div>
				</div>

				<div class="card maint-side">
					<h2 class="side-title">Payment summary</h2>

					<div class="side-item">
						<span class="k">Current month</span>
						<div class="v"><?= htmlspecialchars($month . ' ' . $year, ENT_QUOTES, 'UTF-8') ?></div>
					</div>

					<div class="side-item">
						<span class="k">Amount due</span>
						<div class="v">₹<?= number_format($amount, 2) ?></div>
					</div>

					<div class="side-item">
						<span class="k">Status</span>
						<div class="v" style="color:#e11d48;">Unpaid</div>
					</div>

					<div class="side-item">
						<span class="k">Note</span>
						<div class="v">
							After successful payment, record will be saved with:
							<code>payment_type = maintenance</code>,
							<code>month = <?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?></code>,
							<code>year = <?= (int) $year ?></code>,
							<code>status = paid</code>.
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		(function () {
			const form = document.getElementById('maintenancePayForm');
			const payBtn = document.getElementById('payBtn');
			if (!form || !payBtn) return;

			const defaultBtnText = payBtn.textContent.trim();

			function resetButton() {
				payBtn.disabled = false;
				payBtn.textContent = defaultBtnText;
			}

			function setLoading(text) {
				payBtn.disabled = true;
				payBtn.textContent = text;
			}

			form.addEventListener('submit', function (e) {
				const selected = document.querySelector('input[name="payment_method"]:checked');
				if (!selected) return;

				const selectedMethod = selected.value;

				if (selectedMethod !== 'razorpay') {
					return;
				}

				e.preventDefault();

				const amount = document.querySelector('input[name="amount"]').value;
				const month = document.querySelector('input[name="month"]').value;
				const year = document.querySelector('input[name="year"]').value;

				setLoading('Creating order...');

				fetch('<?= site_url('payment_controllerr/create_razorpay_order') ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
					},
					body: new URLSearchParams({
						amount: amount,
						month: month,
						year: year
					})
				})
					.then(async (response) => {
						const text = await response.text();
						try {
							return JSON.parse(text);
						} catch (err) {
							throw new Error('Invalid server response');
						}
					})
					.then((data) => {
						if (!data || data.error) {
							throw new Error(data && data.error ? data.error : 'Unable to create Razorpay order');
						}

						const options = {
							key: data.key,
							amount: Number(data.amount) * 100,
							currency: 'INR',
							name: 'SocietyHub',
							description: 'Maintenance - <?= htmlspecialchars($month . ' ' . $year, ENT_QUOTES, 'UTF-8') ?>',
							order_id: data.order_id,
							prefill: {
								name: '<?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>'
							},
							theme: {
								color: '#6366f1'
							},
							handler: function (response) {
								fetch('<?= site_url('payment_controllerr/payment_success') ?>', {
									method: 'POST',
									headers: {
										'Content-Type': 'application/json'
									},
									body: JSON.stringify({
										razorpay_payment_id: response.razorpay_payment_id,
										razorpay_order_id: response.razorpay_order_id,
										razorpay_signature: response.razorpay_signature,
										month: month,
										year: year,
										amount: amount
									})
								})
									.then(async (res) => {
										const text = await res.text();
										try {
											return JSON.parse(text);
										} catch (err) {
											throw new Error('Invalid verification response');
										}
									})
									.then((result) => {
										if (result && result.success) {
											alert(result.message || 'Payment completed successfully.');
											window.location.href = result.redirect_url || '<?= site_url('dashboard') ?>';
											return;
										}

										throw new Error((result && result.message) ? result.message : 'Payment verification failed');
									})
									.catch((err) => {
										alert('Error confirming payment: ' + err.message);
										resetButton();
									});
							}
						};

						const rzp = new Razorpay(options);

						rzp.on('payment.failed', function (response) {
							const msg = response && response.error && response.error.description
								? response.error.description
								: 'Payment failed.';
							alert(msg);
							resetButton();
						});

						rzp.open();
					})
					.catch((err) => {
						alert('Error: ' + err.message);
						resetButton();
					});
			});
		})();
	</script>
	<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>

</html>
