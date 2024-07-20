<?php
session_start();

include 'config.php';

 if (isset($_SESSION['user_id'])) {
 
if ($_SESSION['role'] == 'admin') {
header("Location: admin_dashboard.php"); 
} else {
header("Location: dashboard.php");  
}
exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$email = $_POST['email'];

 
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
echo "Username already exists. Please choose another username.";
} else {
 
$role = 'admin';
$stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $password, $email, $role);

if ($stmt->execute()) {
echo "Admin registration successful. <a href='login.php'>Login here</a>";
} else {
echo "Error: " . $stmt->error;
}
}

$stmt->close();
$conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Register</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body><?php include 'navbar.php'; ?>

<h2>Admin Register</h2>
<form action="admin_register.php" method="POST">
<label for="username">Username:</label>
<input type="text" id="username" name="username" required>
<br>
<label for="password">Password:</label>
<input type="password" id="password" name="password" required>
<br>
<label for="email">Email:</label>
<input type="email" id="email" name="email" required>
<br>
<button type="submit">Register as Admin</button>
</form>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 
</body>
</html>
