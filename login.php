<?php
include 'config.php';
session_start();


if (isset($_SESSION['user_id'])) {
if ($_SESSION['role'] == 'admin') {
header("Location: admin_dashboard.php");
} else {
header("Location: dashboard.php");
}
exit();
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$username = $_POST['username'];
$password = $_POST['password'];


$stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_id, $db_username, $db_password, $role);

if ($stmt->num_rows > 0) {
$stmt->fetch();
if (password_verify($password, $db_password)) {

$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $db_username;
$_SESSION['role'] = $role;
$stmt->close();
$conn->close();
if ($role == 'admin') {
header("Location: admin_dashboard.php");
} else {
header("Location: dashboard.php");
}
exit();
} else {

$error = "Invalid password.";
}
} else {

$error = "No user found with that username.";
}

$stmt->close();
$conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

* {
padding: 0;
box-sizing: border-box;
margin: 0;
}
.navbar {
background-color: #939185;
padding: 10px;
}
.containerr {
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
h2 {
text-align: center;
}
form {
display: flex;
flex-direction: column;
}
label {
margin-bottom: 5px;
}
input {
margin-bottom: 15px;
padding: 10px;
border: 1px solid #939185;
border-radius: 5px;
}
button {
padding: 10px;
border: none;
border-radius: 5px;
background-color: #2F3645;
color: #EEEDEB;
cursor: pointer;
}
.message {
text-align: center;
margin-top: 20px;
padding: 10px;
border-radius: 5px;
color: #EEEDEB;
}
.error {
background-color: #FF6B6B;
}
.success {
background-color: #6BFF6B;
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="containerr">
<h2>Login</h2>
<form action="login.php" method="POST">
<label for="username">Username:</label>
<input type="text" id="username" name="username" required>
<label for="password">Password:</label>
<input type="password" id="password" name="password" required>
<br>
<button type="submit">Login</button>
</form>
<?php if ($error): ?>
<div class="message error"><?= $error ?></div>
<?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
