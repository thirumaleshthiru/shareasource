<?php
include 'config.php';
 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
header("Location: login.php");
exit();
}

 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
$category_name = $_POST['category_name'];

 
$stmt = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ?");
$stmt->bind_param("s", $category_name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
echo "Category already exists.";
} else {
 
$stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
$stmt->bind_param("s", $category_name);

if ($stmt->execute()) {
echo "Category added successfully.";
} else {
echo "Error adding category: " . $stmt->error;
}
}

$stmt->close();
}

 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_type'])) {
$type_name = $_POST['type_name'];

 
$stmt = $conn->prepare("SELECT type_id FROM types WHERE type_name = ?");
$stmt->bind_param("s", $type_name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
echo "Resource type already exists.";
} else {
 
$stmt = $conn->prepare("INSERT INTO types (type_name) VALUES (?)");
$stmt->bind_param("s", $type_name);

if ($stmt->execute()) {
echo "Resource type added successfully.";
} else {
echo "Error adding resource type: " . $stmt->error;
}
}

$stmt->close();
}

 if (isset($_POST['delete_category'])) {
$category_id = $_POST['category_id'];

 
$stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
$delete_category_message = "Category deleted successfully.";
} else {
$delete_category_message = "Error deleting category: " . $stmt->error;
}

$stmt->close();
}

 if (isset($_POST['delete_type'])) {
$type_id = $_POST['type_id'];

 
$stmt = $conn->prepare("DELETE FROM types WHERE type_id = ?");
$stmt->bind_param("i", $type_id);

if ($stmt->execute()) {
$delete_type_message = "Resource type deleted successfully.";
} else {
$delete_type_message = "Error deleting resource type: " . $stmt->error;
}

$stmt->close();
}

 if (isset($_POST['delete_resource'])) {
$resource_id = $_POST['resource_id'];

 
$stmt = $conn->prepare("DELETE FROM resources WHERE resource_id = ?");
$stmt->bind_param("i", $resource_id);

if ($stmt->execute()) {
$delete_message = "Resource deleted successfully.";
} else {
$delete_message = "Error deleting resource: " . $stmt->error;
}

$stmt->close();
}

 $sql_resources = "SELECT r.resource_id, r.title, u.username
FROM resources r
INNER JOIN users u ON r.user_id = u.user_id
ORDER BY r.created_at DESC";
$result_resources = $conn->query($sql_resources);

if (!$result_resources) {
$fetch_error = "Error fetching resources: " . $conn->error;
}

 $sql_categories = "SELECT category_id, category_name FROM categories";
$result_categories = $conn->query($sql_categories);

if (!$result_categories) {
$fetch_categories_error = "Error fetching categories: " . $conn->error;
}

 $sql_types = "SELECT type_id, type_name FROM types";
$result_types = $conn->query($sql_types);

if (!$result_types) {
$fetch_types_error = "Error fetching resource types: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
 
<style>
body {
font-family: Arial, sans-serif;
background-color: #f0f0f0;
 
}
.container {
max-width: 800px;
margin: 0 auto;
padding:10px;
}
.alert {
margin-top: 20px;
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
 
 
<?php if (isset($delete_message)): ?>
<div class="alert alert-info"><?php echo $delete_message; ?></div>
<?php endif; ?>

 
<?php if (isset($delete_category_message)): ?>
<div class="alert alert-info"><?php echo $delete_category_message; ?></div>
<?php endif; ?>

 
<?php if (isset($delete_type_message)): ?>
<div class="alert alert-info"><?php echo $delete_type_message; ?></div>
<?php endif; ?>

 <?php if (isset($fetch_error)): ?>
<div class="alert alert-danger"><?php echo $fetch_error; ?></div>
<?php endif; ?>

 <?php if (isset($fetch_categories_error)): ?>
<div class="alert alert-danger"><?php echo $fetch_categories_error; ?></div>
<?php endif; ?>

 <?php if (isset($fetch_types_error)): ?>
<div class="alert alert-danger"><?php echo $fetch_types_error; ?></div>
<?php endif; ?>

 <h3>Add Category</h3>
<form action="admin_dashboard.php" method="POST">
<div class="input-group mb-3">
<input type="text" id="category_name" name="category_name" class="form-control" placeholder="Enter Category Name" required>
<div class="input-group-append">
<button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
</div>
</div>
</form>

 <h3>Existing Categories</h3>
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
<th>Category Name</th>
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php
if ($result_categories->num_rows > 0) {
while ($row = $result_categories->fetch_assoc()) {
$category_id = $row['category_id'];
$category_name = htmlspecialchars($row['category_name']);
?>
<tr>
<td><?php echo $category_name; ?></td>
<td>
<form action="admin_dashboard.php" method="POST">
<input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
<button type="submit" name="delete_category" class="btn btn-danger btn-sm">Delete</button>
</form>
</td>
</tr>
<?php
}
} else {
echo '<tr><td colspan="2">No categories found.</td></tr>';
}
?>
</tbody>
</table>
</div>

 <h3>Add Resource Type</h3>
<form action="admin_dashboard.php" method="POST">
<div class="input-group mb-3">
<input type="text" id="type_name" name="type_name" class="form-control" placeholder="Enter Resource Type Name" required>
<div class="input-group-append">
<button type="submit" name="add_type" class="btn btn-primary">Add Resource Type</button>
</div>
</div>
</form>

 <h3>Existing Resource Types</h3>
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
<th>Resource Type Name</th>
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php
if ($result_types->num_rows > 0) {
while ($row = $result_types->fetch_assoc()) {
$type_id = $row['type_id'];
$type_name = htmlspecialchars($row['type_name']);
?>
<tr>
<td><?php echo $type_name; ?></td>
<td>
<form action="admin_dashboard.php" method="POST">
<input type="hidden" name="type_id" value="<?php echo $type_id; ?>">
<button type="submit" name="delete_type" class="btn btn-danger btn-sm">Delete</button>
</form>
</td>
</tr>
<?php
}
} else {
echo '<tr><td colspan="2">No resource types found.</td></tr>';
}
?>
</tbody>
</table>
</div>

 <h3>All Resources</h3>
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
<th>Title</th>
<th>Creator</th>
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php
if ($result_resources->num_rows > 0) {
while ($row = $result_resources->fetch_assoc()) {
$resource_id = $row['resource_id'];
$title = htmlspecialchars($row['title']);
$creator = htmlspecialchars($row['username']);
?>
<tr>
<td><?php echo $title; ?></td>
<td><?php echo $creator; ?></td>
<td>
<form action="admin_dashboard.php" method="POST">
<input type="hidden" name="resource_id" value="<?php echo $resource_id; ?>">
<button type="submit" name="delete_resource" class="btn btn-danger btn-sm">Delete</button>
</form>
</td>
</tr>
<?php
}
} else {
echo '<tr><td colspan="3">No resources found.</td></tr>';
}
?>
</tbody>
</table>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 
</body>
</html>
