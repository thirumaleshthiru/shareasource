<?php
session_start();
if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

$username = isset($_SESSION['username']) ? strtoupper($_SESSION['username']) : 'USER';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($username); ?> Dashboard</title>
 
<style>
body {
font-family: Arial, sans-serif;
background-color: #EEEDEB;
color: #2F3645;
margin: 0;
padding: 0;
}
.navbar {
background-color: #939185;
padding: 10px;
}
.containerr {
max-width: 800px;
margin: 50px auto;
padding: 20px;
background-color: #E6B9A6;
border-radius: 10px;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.containerr h2{
    font-size:21px;
}
ul {
list-style-type: none;
padding: 0;
}
li {
margin-bottom: 10px;
}
.link {
display: block;
padding: 10px;
background-color: #2F3645;
color: #EEEDEB;
border-radius: 5px;
text-decoration: none;
}
.link:hover {
background-color: white;
color: #2F3645;
}
.link-logout{
background-color:#E4003A;
color:white;
border-radius: 5px;
text-decoration: none;
padding:10px;

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
<div class="containerr">
<h2><?php echo htmlspecialchars($username); ?> Dashboard</h2><br>
<ul>
<li><a href="manage_resources.php" class="link">Manage Resources</a></li>
<li><a href="followers.php" class="link">Followers</a></li>
<li><a href="following.php" class="link">Following</a></li>
<li><a href="add_resource.php" class="link">Add New Resource</a></li><br>
<li><a href="logout.php" class="link-logout">Logout</a></li>
</ul>
</div>
</main>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
