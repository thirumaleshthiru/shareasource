<?php
session_start();
include 'config.php';
require 'vendor/autoload.php'; // Include PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$email = $_POST['email'];

// Check if username or email already exists
$check_existing_query = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($check_existing_query);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
// Username or email already exists
$message = "Username or email already registered. Please use a different username or email.";
} else {
// Generate OTP
$otp = rand(100000, 999999); // Generate 6-digit OTP
$_SESSION['otp'] = $otp; // Store OTP in session for verification
$_SESSION['username'] = $username;
$_SESSION['password'] = $password;
$_SESSION['email'] = $email;

// Send OTP via email
$mail = new PHPMailer(true);
try {
// Server settings
$mail->SMTPDebug = 0; // Disable verbose debug output
$mail->isSMTP(); // Set mailer to use SMTP
$mail->Host       = 'smtp.office365.com'; // Specify Outlook SMTP servers
$mail->SMTPAuth   = true; // Enable SMTP authentication
$mail->Username   = 'methirumaleshgandam@outlook.com'; // SMTP username
$mail->Password   = 'Thiruout@79'; // SMTP password
$mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
$mail->Port       = 587; // TCP port to connect to

// Recipients
$mail->setFrom('methirumaleshgandam@outlook.com', 'Thirumalesh');
$mail->addAddress($email); // Add a recipient
$mail->addReplyTo('methirumaleshgandam@outlook.com', 'Thirumalesh');

// Content
$mail->isHTML(true); // Set email format to HTML
$mail->Subject = 'OTP Verification for Registration';
$mail->Body    = 'Your OTP for registration is: <b>' . $otp . '</b>';
$mail->AltBody = 'Your OTP for registration is: ' . $otp;

// Send email
$mail->send();
$message = "Registration successful. Check your email for OTP verification.";
header("Location: verify_otp.php");
exit();
} catch (Exception $e) {
$message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register</title>
<style>
* {
padding: 0;
box-sizing: border-box;
margin: 0;
}
.register-container {
max-width: 400px;
width: 100%;
padding: 20px;
background-color: #E6B9A6;
border-radius: 10px;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
 
}
.register-container h2 {
text-align: center;
margin-bottom: 20px;
}
.register-container label {
display: block;
margin-bottom: 5px;
}
.register-container input {
width: 100%;
padding: 8px;
border: 1px solid #939185;
border-radius: 5px;
margin-bottom: 15px;
}
.register-container button {
width: 100%;
padding: 10px;
background-color: #2F3645;
color: #EEEDEB;
border: none;
border-radius: 5px;
cursor: pointer;
}
.message {
text-align: center;
margin-bottom: 20px;
color: #2F3645;
}
main{
width:80%;
padding:30px;
}
@media screen and (max-width:800px){
main{
width:100%;
padding:30px;
}
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<main>
<div class="register-container">
<h2>Register</h2>
<?php if (!empty($message)): ?>
<div class="message"><?php echo $message; ?></div>
<?php endif; ?>
<form action="register.php" method="POST">
<div>
<label for="username">Username:</label>
<input type="text" id="username" name="username" required>
</div>
<div>
<label for="password">Password:</label>
<input type="password" id="password" name="password" required>
</div>
<div>
<label for="email">Email:</label>
<input type="email" id="email" name="email" required>
</div>
<br>
<button type="submit">Register</button>
</form>
</div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
