<?php
session_start();
include 'config.php';

 
if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

 
$delete_resource_id = isset($_GET['resource_id']) ? $_GET['resource_id'] : null;
$confirm = false;
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {
$sql_delete_resource = "DELETE FROM resources WHERE resource_id = ?";
$stmt_delete_resource = $conn->prepare($sql_delete_resource);
$stmt_delete_resource->bind_param("i", $delete_resource_id);

if ($stmt_delete_resource->execute()) {
 header("Location: manage_resources.php");
exit();
} else {
echo "Error deleting resource: " . $stmt_delete_resource->error;
}

$stmt_delete_resource->close();
$conn->close();
}
 
$sql_fetch_resource = "SELECT title FROM resources WHERE resource_id = ?";
$stmt_fetch_resource = $conn->prepare($sql_fetch_resource);
$stmt_fetch_resource->bind_param("i", $delete_resource_id);
$stmt_fetch_resource->execute();
$result_fetch_resource = $stmt_fetch_resource->get_result();

if ($result_fetch_resource->num_rows > 0) {
$resource = $result_fetch_resource->fetch_assoc();
} else {
echo "Resource not found.";
exit();
}

$stmt_fetch_resource->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delete Resource</title>

<style>
.confirmation-container {
max-width: 600px;
margin: 20px auto;
padding: 20px;
border: 1px solid #ccc;
background-color: #f9f9f9;
}
.confirmation-container h2 {
font-size: 24px;
margin-bottom: 20px;
}
.confirmation-container p {
margin-bottom: 10px;
}
.confirmation-container form {
display: inline-block;
}
.confirmation-container form input[type="submit"] {
padding: 10px 20px;
font-size: 16px;
background-color: #dc3545;
color: #fff;
border: none;
cursor: pointer;
border-radius: 4px;
}
.confirmation-container form input[type="submit"]:hover {
background-color: #c82333;
}
.confirmation-container form button {
padding: 10px 20px;
font-size: 16px;
background-color: #007bff;
color: #fff;
border: none;
cursor: pointer;
border-radius: 4px;
margin-left: 10px;
}
.confirmation-container form button:hover {
background-color: #0056b3;
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body><?php include 'navbar.php' ?> 
<div class="confirmation-container">
<h2>Delete Resource</h2>

<p>Are you sure you want to delete the resource "<?php echo htmlspecialchars($resource['title']); ?>"?</p>

<form method="POST">
<input type="hidden" name="resource_id" value="<?php echo $delete_resource_id; ?>">
<input type="submit" name="confirm" value="Delete">
<button type="button" onclick="location.href='manage_resources.php'">Cancel</button>
</form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
