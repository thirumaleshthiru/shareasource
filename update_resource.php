<?php
session_start();
include 'config.php';

 
if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

 
$update_resource_id = isset($_GET['resource_id']) ? $_GET['resource_id'] : null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
$title = $_POST['title'];
$link = $_POST['link'];
$description = $_POST['description'];
$type_id = $_POST['type_id'];
$category_id = $_POST['category_id'];

 
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
$cover_image = file_get_contents($_FILES['cover_image']['tmp_name']);
} else {
 
$sql_select_image = "SELECT cover_image FROM resources WHERE resource_id = ?";
$stmt_select_image = $conn->prepare($sql_select_image);
$stmt_select_image->bind_param("i", $update_resource_id);
$stmt_select_image->execute();
$result_select_image = $stmt_select_image->get_result();

if ($result_select_image->num_rows > 0) {
$row = $result_select_image->fetch_assoc();
$cover_image = $row['cover_image'];
}

$stmt_select_image->close();
}
 
$sql_update_resource = "UPDATE resources SET title = ?, link = ?, cover_image = ?, type_id = ?, category_id = ?, resource_description = ? WHERE resource_id = ?";
$stmt_update_resource = $conn->prepare($sql_update_resource);
$stmt_update_resource->bind_param("sssiiii", $title, $link, $cover_image, $type_id, $category_id, $description, $update_resource_id);

if ($stmt_update_resource->execute()) {
$success = true;
} else {
echo "Error updating resource: " . $stmt_update_resource->error;
}

$stmt_update_resource->close();
$conn->close();
} else {
 
$sql_fetch_resource = "SELECT resource_id, title, link, cover_image, type_id, category_id, resource_description FROM resources WHERE resource_id = ?";
$stmt_fetch_resource = $conn->prepare($sql_fetch_resource);
$stmt_fetch_resource->bind_param("i", $update_resource_id);
$stmt_fetch_resource->execute();
$result_fetch_resource = $stmt_fetch_resource->get_result();

if ($result_fetch_resource->num_rows > 0) {
$resource = $result_fetch_resource->fetch_assoc();
 $description = $resource['resource_description'];
} else {
echo "Resource not found.";
exit();
}

$stmt_fetch_resource->close();
$conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Resource</title>
<style>
.form-container {
max-width: 600px;
margin: 20px auto;
padding: 20px;
border: 1px solid #ccc;
 
}
.form-container h2 {
font-size: 24px;
margin-bottom: 20px;
}
.form-group {
margin-bottom: 10px;
}
.form-group label {
display: block;
font-weight: bold;
}
.form-group input[type="text"], 
.form-group input[type="url"],
.form-group select,
.form-group textarea {
width: 100%;
padding: 8px;
font-size: 16px;
}
.form-group textarea {
height: 100px;
}
.form-group input[type="submit"] {
padding: 10px 20px;
font-size: 16px;
background-color: #007bff;
color: #fff;
border: none;
cursor: pointer;
border-radius: 4px;
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php' ?> 
<div class="form-container">
<h2>Update Resource</h2>

<?php if ($success): ?>
<p>Resource updated successfully.</p>
<?php else: ?>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="resource_id" value="<?php echo $resource['resource_id']; ?>">

<div class="form-group">
<label for="title">Title:</label>
<input type="text" id="title" name="title" value="<?php echo htmlspecialchars($resource['title']); ?>" required>
</div>

<div class="form-group">
<label for="link">Link:</label>
<input type="url" id="link" name="link" value="<?php echo htmlspecialchars($resource['link']); ?>" required>
</div>

<div class="form-group">
<label for="cover_image">Cover Image:</label>
<input type="file" id="cover_image" name="cover_image">
<p>Current Cover Image:</p>
<?php if (!empty($resource['cover_image'])): ?>
<img src="data:image/jpeg;base64,<?php echo base64_encode($resource['cover_image']); ?>" alt="<?php echo htmlspecialchars($resource['title']); ?>" style="max-width: 100%;">
<?php else: ?>
<img src="placeholder.jpg" alt="Placeholder" style="max-width: 100%;">
<?php endif; ?>
</div>

<div class="form-group">
<label for="type_id">Type:</label>
<select id="type_id" name="type_id" required>
<?php
include 'config.php';
$sql_types = "SELECT type_id, type_name FROM types";
$result_types = $conn->query($sql_types);

if ($result_types->num_rows > 0) {
while ($row = $result_types->fetch_assoc()) {
$selected = ($row['type_id'] == $resource['type_id']) ? "selected" : "";
echo '<option value="' . $row['type_id'] . '" ' . $selected . '>' . htmlspecialchars($row['type_name']) . '</option>';
}
}
$conn->close();
?>
</select>
</div>

<div class="form-group">
<label for="category_id">Category:</label>
<select id="category_id" name="category_id" required>
<?php
include 'config.php';
$sql_categories = "SELECT category_id, category_name FROM categories";
$result_categories = $conn->query($sql_categories);

if ($result_categories->num_rows > 0) {
while ($row = $result_categories->fetch_assoc()) {
$selected = ($row['category_id'] == $resource['category_id']) ? "selected" : "";
echo '<option value="' . $row['category_id'] . '" ' . $selected . '>' . htmlspecialchars($row['category_name']) . '</option>';
}
}
$conn->close();
?>
</select>
</div>

<div class="form-group">
<label for="description">Description:</label>
<textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea>
</div>

<div class="form-group">
<input type="submit" value="Update Resource">
</div>
</form>
<?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
