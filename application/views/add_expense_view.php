<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Add Expense</title>

	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<style>
		body {
			font-family: Arial, sans-serif;
			background: #f5f5f5;
		}

		.container {
			width: 400px;
			margin: 50px auto;
			background: #fff;
			padding: 20px;
			border-radius: 6px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
		}

		h2 {
			text-align: center;
		}

		input,
		textarea,
		select,
		button {
			width: 100%;
			margin: 8px 0;
			padding: 10px;
		}

		button {
			background: #007bff;
			color: #fff;
			border: none;
			cursor: pointer;
		}

		button:hover {
			background: #0056b3;
		}

		.msg {
			margin-top: 10px;
			text-align: center;
		}
	</style>
</head>

<body>

	<div class="container">
		<h2>Add Expense</h2>

		<form id="expenseForm">
			<input type="text" name="title" id="title" placeholder="Expense Title" required>

			<input type="number" name="amount" id="amount" placeholder="Amount" required>

			<select name="category" id="category" required>
				<option value="">Select Category</option>
				<option value="Food">Food</option>
				<option value="Travel">Travel</option>
				<option value="Shopping">Shopping</option>
				<option value="Bills">Bills</option>
			</select>

			<textarea name="note" id="note" placeholder="Note"></textarea>

			<button type="submit">Save Expense</button>
		</form>

		<div class="msg" id="msg"></div>
	</div>

	<script>
		$(document).ready(function () {

			$('#expenseForm').submit(function (e) {
				e.preventDefault();

				$.ajax({
					url: "<?= base_url('save-expense') ?>",
					type: "POST",
					data: $(this).serialize(),
					dataType: "json",
					success: function (res) {
						$('#msg').html(res.msg);

						if (res.status) {
							$('#expenseForm')[0].reset();
						}
						setTimeout(function () {
							window.location.href = res.redirect;
						}, 1000);
					},

					error: function () {
						$('#msg').html('Something went wrong (500 error)');
					}
				});
			});

		});
	</script>

</body>

</html>
