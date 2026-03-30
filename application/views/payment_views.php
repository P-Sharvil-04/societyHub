<!DOCTYPE html>
<html>

<head>
	<title>Payment Page</title>
	<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>

<body>

	<h2>Pay Amount: $<?= $amount ?></h2>
	<input type="number" id="amount" value="<?= $amount ?>" />
	<button id="payBtn">Pay Now</button>

	<script>
		document.getElementById('payBtn').onclick = function () {
			var amount = document.getElementById('amount').value;

			fetch('<?= base_url("payment_controller/create_order") ?>', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: 'amount=' + amount
			}).then(res => res.json())
				.then(order => {
					var options = {
						"key": "<?= $key_id ?>", // use passed key
						"amount": order.amount * 100, // Razorpay expects paise/cents
						"currency": "USD",
						"name": "Society Payment",
						"order_id": order.id,
						"handler": function (response) {
							fetch('<?= base_url("payment_controller/verify") ?>', {
								method: 'POST',
								headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
								body: 'razorpay_payment_id=' + response.razorpay_payment_id + '&razorpay_order_id=' + response.razorpay_order_id
							}).then(r => r.text()).then(alert);
						}
					};
					var rzp = new Razorpay(options);
					rzp.open();
				});
		};
	</script>

</body>

</html>
