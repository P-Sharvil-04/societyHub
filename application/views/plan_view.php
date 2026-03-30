<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
	<title>SocietyHub · Choose Your Plan</title>
	<link rel="icon" href="<?= base_url('assets/img/Gemini_Generated_Image_vudhc1vudhc1vudh.png') ?>" type="image/png">

	<!-- Icons & Fonts -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet" />

	<link rel="stylesheet" href="<?= base_url('assets/css/plan.css') ?>">
</head>

<body>
	<div class="pricing-container">
		<!-- Header -->
		<div class="pricing-header">
			<h1>Choose Your Perfect Plan</h1>
			<p>Start with our Free Plan or unlock premium features for your growing community</p>
		</div>

		<!-- Billing Toggle (only for Basic & Pro) -->
		<div class="billing-toggle">
			<span>Monthly</span>
			<label class="toggle-switch">
				<input type="checkbox" id="billingToggle">
				<span class="toggle-slider"></span>
			</label>
			<span>Yearly <span class="save-badge">Save 20%</span></span>
		</div>

		<!-- Pricing Cards -->
		<div class="pricing-grid">
			<!-- Free Plan -->
			<div class="pricing-card">
				<div class="popular-badge free-badge">Free Forever</div>
				<div class="card-header">
					<div class="plan-icon"><i class="fas fa-rocket"></i></div>
					<h3 class="plan-name">Free</h3>
					<div class="plan-price">₹0</div>
					<div class="plan-period">forever free</div>
				</div>
				<ul class="plan-features">
					<li><i class="fas fa-check-circle"></i> Up to 25 Residents</li>
					<li><i class="fas fa-check-circle"></i> Basic Dashboard</li>
					<li><i class="fas fa-check-circle"></i> Complaint Registration</li>
					<li><i class="fas fa-check-circle"></i> Notice Board</li>
					<li><i class="fas fa-check-circle"></i> Email Support</li>
				</ul>
				<a href="<?= base_url('register-society') ?>" class="btn-select btn-free">Get
					Started Free</a>
			</div>

			<!-- Basic Plan -->
			<div class="pricing-card">
				<div class="card-header">
					<div class="plan-icon"><i class="fas fa-home"></i></div>
					<h3 class="plan-name">Basic</h3>
					<div class="plan-price" id="basicPrice">₹999</div>
					<div class="plan-period" id="basicPeriod">per month</div>
				</div>
				<ul class="plan-features">
					<li><i class="fas fa-check-circle"></i> Up to 100 Residents</li>
					<li><i class="fas fa-check-circle"></i> Basic Reports</li>
					<li><i class="fas fa-check-circle"></i> Staff Management</li>
					<li><i class="fas fa-check-circle"></i> Payment Tracking</li>
					<li><i class="fas fa-check-circle"></i> Visitor Management</li>
					<li><i class="fas fa-check-circle"></i> Email Support</li>
				</ul>
				<a href="<?= base_url('register-society') ?>?plan=basic&billing=monthly" class="btn-select"
					id="basicLink">Choose
					Basic</a>
			</div>

			<!-- Professional Plan (Popular) -->
			<div class="pricing-card popular">
				<div class="popular-badge">Most Popular</div>
				<div class="card-header">
					<div class="plan-icon"><i class="fas fa-building"></i></div>
					<h3 class="plan-name">Professional</h3>
					<div class="plan-price" id="proPrice">₹2,499</div>
					<div class="plan-period" id="proPeriod">per month</div>
				</div>
				<ul class="plan-features">
					<li><i class="fas fa-check-circle"></i> Up to 500 Residents</li>
					<li><i class="fas fa-check-circle"></i> Advanced Analytics</li>
					<li><i class="fas fa-check-circle"></i> AI Insights & Predictions</li>
					<li><i class="fas fa-check-circle"></i> Vendor Management</li>
					<li><i class="fas fa-check-circle"></i> Parking Management</li>
					<li><i class="fas fa-check-circle"></i> Document Management</li>
					<li><i class="fas fa-check-circle"></i> Priority Support</li>
				</ul>
				<a href="<?= base_url('register-society') ?>?plan=pro&billing=monthly" class="btn-select"
					id="proLink">Choose Professional</a>
			</div>
		</div>

		<!-- Features Comparison (Free, Basic, Pro) -->
		<div class="features-comparison">
			<h2>Compare All Features</h2>
			<div class="comparison-table">
				<table>
					<thead>
						<tr>
							<th>Feature</th>
							<th>Free</th>
							<th>Basic</th>
							<th>Professional</th>
						</tr>
					</thead>
					<tbody>
						<!-- <tr>
							<td>Max Residents</td>
							<td>25</td>
							<td>100</td>
							<td>500</td>
						</tr> -->
						<tr>
							<td>Dashboard Access</td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<!-- <tr>
							<td>Complaint Management</td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr> -->
						<tr>
							<td>Notice Board</td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<tr>
							<td>Payment Tracking</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<!-- <tr>
							<td>Staff Management</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr> -->
						<tr>
							<td>Visitor Management</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<tr>
							<td>Advanced Reports</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<tr>
							<td>AI Insights</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<!-- <tr>
							<td>Vendor Management</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<tr>
							<td>Parking Management</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr> -->
						<tr>
							<td>Document Management</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-check available"></i></td>
							<td><i class="fas fa-check available"></i></td>
						</tr>
						<!-- <tr>
							<td>API Access</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
						</tr> -->
						<tr>
							<td>24/7 Support</td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
							<td><i class="fas fa-times unavailable"></i></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- FAQ Section -->
		<div class="faq-section">
			<h2>Frequently Asked Questions</h2>
			<div class="faq-grid">
				<div class="faq-item">
					<h3><i class="fas fa-question-circle"></i> Can I change plans later?</h3>
					<p>Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next
						billing cycle.</p>
				</div>
				<div class="faq-item">
					<h3><i class="fas fa-question-circle"></i> Is the Free Plan really free?</h3>
					<p>Absolutely! No credit card required. You get full access to basic features forever.</p>
				</div>
				<div class="faq-item">
					<h3><i class="fas fa-question-circle"></i> What payment methods do you accept?</h3>
					<p>We accept all major credit cards, UPI, net banking, and PayPal.</p>
				</div>
				<div class="faq-item">
					<h3><i class="fas fa-question-circle"></i> Is there a setup fee?</h3>
					<p>No setup fees! You only pay for your subscription plan.</p>
				</div>
			</div>
		</div>
	</div>

	<script>
		// Billing Toggle Logic – update links and prices for Basic & Pro
		const toggle = document.getElementById('billingToggle');
		const basicPrice = document.getElementById('basicPrice');
		const proPrice = document.getElementById('proPrice');
		const basicPeriod = document.getElementById('basicPeriod');
		const proPeriod = document.getElementById('proPeriod');

		const basicLink = document.getElementById('basicLink');
		const proLink = document.getElementById('proLink');

		function updateBilling(isYearly) {
			const billing = isYearly ? 'yearly' : 'monthly';
			basicLink.href = `<?= base_url('register-society') ?>?plan=basic&billing=${billing}`;
			proLink.href = `<?= base_url('register-society') ?>?plan=pro&billing=${billing}`;

			if (isYearly) {
				basicPrice.textContent = '₹9,590';
				proPrice.textContent = '₹23,990';
				basicPeriod.textContent = 'per year';
				proPeriod.textContent = 'per year';
			} else {
				basicPrice.textContent = '₹999';
				proPrice.textContent = '₹2,499';
				basicPeriod.textContent = 'per month';
				proPeriod.textContent = 'per month';
			}
		}

		toggle.addEventListener('change', function () {
			updateBilling(this.checked);
		});
	</script>
</body>

</html>
