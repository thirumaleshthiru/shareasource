<?php
session_start();
if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

include 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$link = $_POST['link'];
$type_id = $_POST['type_id'];
$category_id = $_POST['category_id'];
$description = $_POST['description'];

if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
$file_tmp_name = $_FILES['cover_image']['tmp_name'];
$cover_image = file_get_contents($file_tmp_name);

$stmt = $conn->prepare("INSERT INTO resources (user_id, title, link, cover_image, type_id, category_id, resource_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
if ($stmt === false) {
die('Error preparing statement: ' . $conn->error);
}

$stmt->bind_param("isssiss", $user_id, $title, $link, $cover_image, $type_id, $category_id, $description);

if ($stmt->execute()) {
$resource_id = $stmt->insert_id;

if (!empty($_POST['tags'])) {
$tags = explode(',', $_POST['tags']);
foreach ($tags as $tag) {
$tag = trim($tag);

$tag_id = insertOrGetTagId($tag, $conn);

$insert_tag_stmt = $conn->prepare("INSERT INTO resource_tags (resource_id, tag_id) VALUES (?, ?)");
if ($insert_tag_stmt === false) {
die('Error preparing tag statement: ' . $conn->error);
}
$insert_tag_stmt->bind_param("ii", $resource_id, $tag_id);
if (!$insert_tag_stmt->execute()) {
die('Error executing tag statement: ' . $insert_tag_stmt->error);
}
$insert_tag_stmt->close();
}
}

// Set success message
$message = "Resource Added Successfully!";
} else {
// Set error message
$message = "Error adding resource: " . $stmt->error;
}

$stmt->close();
} else {
$message = "Error uploading file.";
}

$conn->close();

// Alert message handling in JavaScript
echo "<script>alert('" . htmlspecialchars($message) . "');</script>";
// Redirect after showing the alert
echo "<script>window.location = 'add_resource.php';</script>";
exit();
}

function insertOrGetTagId($tag, $conn) {
$tag = trim($tag);
$tag_id = 0;

$check_tag_stmt = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
if ($check_tag_stmt === false) {
die('Error preparing check tag statement: ' . $conn->error);
}
$check_tag_stmt->bind_param("s", $tag);
if (!$check_tag_stmt->execute()) {
die('Error executing check tag statement: ' . $check_tag_stmt->error);
}
$check_tag_stmt->store_result();

if ($check_tag_stmt->num_rows > 0) {
$check_tag_stmt->bind_result($tag_id);
$check_tag_stmt->fetch();
} else {
$insert_tag_stmt = $conn->prepare("INSERT INTO tags (tag_name) VALUES (?)");
if ($insert_tag_stmt === false) {
die('Error preparing insert tag statement: ' . $conn->error);
}
$insert_tag_stmt->bind_param("s", $tag);
if ($insert_tag_stmt->execute()) {
$tag_id = $insert_tag_stmt->insert_id;
} else {
die('Error executing insert tag statement: ' . $insert_tag_stmt->error);
}
$insert_tag_stmt->close();
}

$check_tag_stmt->close();
return $tag_id;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Resource</title>
<link rel="stylesheet" href="./styles/add.css">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
<h2>Add New Resource</h2>
<?php if ($message): ?>
<div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<form action="add_resource.php" method="POST" enctype="multipart/form-data">
<label for="title">Title:</label>
<input type="text" id="title" name="title" required>

<label for="link">Link:</label>
<input type="text" id="link" name="link" required>

<label for="type_id">Resource Type:</label>
<select id="type_id" name="type_id" required>
<?php
$sql = "SELECT type_id, type_name FROM types";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
echo "<option value='" . $row['type_id'] . "'>" . $row['type_name'] . "</option>";
}
}
?>
</select>

<label for="category_id">Category:</label>
<select id="category_id" name="category_id" required>
<?php
$sql = "SELECT category_id, category_name FROM categories";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
}
}
?>
</select>

<label for="description">Description:</label>
<textarea id="description" name="description" rows="4" cols="50"></textarea>

<label for="tags">Tags (comma-separated):</label>
<input type="text" id="tags" name="tags">

<label for="cover_image">Cover Image:</label>
<input type="file" id="cover_image" name="cover_image" accept="image/*" required>
<br>
<button type="submit">Add Resource</button>
</form>


</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
