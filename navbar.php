 

<?php
 if (session_status() === PHP_SESSION_NONE) {
session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark">
<a class="navbar-brand" href="index.php">ShareASource</a>
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse justify-content-around" id="navbarNav">
<ul class="navbar-nav">
<li class="nav-item">
<a class="nav-link" href="index.php">Home</a>
</li>
<?php
 if (isset($_SESSION['user_id'])) {
 $role = $_SESSION['role'];
if ($role === 'admin') {
echo '<li class="nav-item">
<a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a>
</li>';
} else {
echo '<li class="nav-item">
<a class="nav-link" href="dashboard.php">Dashboard</a>
</li>';
}
echo '<li class="nav-item">
<a class="nav-link" href="logout.php">Logout</a>
</li>';
} else {
echo '<li class="nav-item">
<a class="nav-link" href="register.php">Register</a>
</li>
<li class="nav-item">
<a class="nav-link" href="login.php">Login</a>
</li>';
}
?>
</ul>
</div>
</nav>
 
