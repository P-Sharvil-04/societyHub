<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Society Registration</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

	<link rel="stylesheet" href="<?= base_url('assets/css/register.css') ?>">
</head>

<body>
	<div class="container">
		<h2>Register</h2>

		<div class="form-group">
			<label>Full Name</label>
			<input type="text" id="name" placeholder="Enter your name">
		</div>

		<div class="form-group">
			<label>Email Address</label>
			<input type="email" id="email" placeholder="Enter email">
		</div>

		<div class="form-group">
			<label>Password</label>
			<input type="password" id="password" placeholder="Create password">
		</div>

		<button class="btn btn-primary" onclick="sendOtp()">Send OTP</button>

		<!-- OTP SECTION -->
		<div class="otp-box" id="otpBox">
			<div class="form-group" style="margin-top:15px;">
				<label>Enter OTP</label>
				<input type="text" id="otp" placeholder="6-digit OTP">
			</div>
			<button class="btn btn-success" onclick="verifyOtp()">Verify OTP</button>
		</div>

		<button class="btn btn-primary" id="registerBtn" style="margin-top:15px;" onclick="registerUser()" disabled>
			Register
		</button>
		<div class="login-link">
			Already registered? <a href="<?= base_url('login') ?>">Login</a>
		</div>

		<div class="message" id="msg"></div>
	</div>
	<script>
		function sendOtp() {

			if (!$('#email').val()) {
				$('#msg').addClass('error').text('Please enter email');
				return;
			}

			$.post("<?= base_url('usercontroller/send_otp') ?>", {
				email: $('#email').val()
			}, function (res) {
				if (res.status) {
					$('#otpBox').slideDown();
					$('#msg').removeClass('error').addClass('success').text(res.msg);
				} else {
					$('#msg').removeClass('success').addClass('error').text(res.msg);
				}
			}, 'json');
		}

		function verifyOtp() {

			if (!$('#otp').val()) {
				$('#msg').addClass('error').text('Please enter OTP');
				return;
			}

			$.post("<?= base_url('usercontroller/verify_otp') ?>", {
				otp: $('#otp').val()
			}, function (res) {
				if (res.status) {
					$('#registerBtn').prop('disabled', false);
					$('#msg').removeClass('error').addClass('success').text(res.msg);
				} else {
					$('#msg').removeClass('success').addClass('error').text(res.msg);
				}
			}, 'json');
		}

		function registerUser() {

			if (!$('#name').val() || !$('#password').val()) {
				$('#msg').addClass('error').text('All fields are required');
				return;
			}

			$.post("<?= base_url('usercontroller/register') ?>", {
				name: $('#name').val(),
				password: $('#password').val()
			}, function (res) {
				if (res.status) {
					$('#msg').removeClass('error').addClass('success').text(res.msg);

					// ✅ REDIRECT TO LOGIN AFTER 1.5 SEC
					setTimeout(function () {
						window.location.href = res.redirect;
					}, 1500);

				} else {
					$('#msg').removeClass('success').addClass('error').text(res.msg);
				}
			}, 'json');
		}
	</script>


</body>

</html>
