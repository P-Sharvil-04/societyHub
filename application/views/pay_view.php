<!DOCTYPE html>
<html>

<head>
	<title>Pay Maintenance</title>
	<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>

<body>

	<h3>Pay Order: <?= $order['id'] ?></h3>
	<p>Amount: ₹<?= $order['amount'] / 100 ?></p>

	<button id="rzp-button">Pay Now</button>

	<script>

		var options = {
			key: "<?= $key_id ?>",
			order_id: "<?= $order['id'] ?>",
			currency: "INR",
			name: "Society Maintenance",
			description: "Maintenance Payment",

			handler: function (response) {

				var formData = new FormData();

				formData.append("razorpay_payment_id", response.razorpay_payment_id);
				formData.append("razorpay_order_id", response.razorpay_order_id);
				formData.append("razorpay_signature", response.razorpay_signature);

				fetch("<?= base_url('Payment_controller/payment_success') ?>", {
					method: "POST",
					body: formData
				})
					.then(res => res.json())
					.then(data => {
						alert(data.message);
						window.location = "<?= base_url('Payment_controller/chairman_balance') ?>";
					});
			}
		};

		var rzp = new Razorpay(options);

		document.getElementById('rzp-button').onclick = function (e) {
			rzp.open();
			e.preventDefault();
		}

	</script>

</body>

</html>
