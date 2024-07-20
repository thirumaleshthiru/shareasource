<?php
session_start();
include 'config.php';

if (!isset($_GET['username'])) {
echo "Username not specified.";
exit();
}

$username = $_GET['username'];

$user_sql = "SELECT user_id, username FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
$user_row = $user_result->fetch_assoc();
$user_id = $user_row['user_id'];

$resources_sql = "SELECT r.*, GROUP_CONCAT(t.tag_name SEPARATOR ', ') AS tags
FROM resources r
LEFT JOIN resource_tags rt ON r.resource_id = rt.resource_id
LEFT JOIN tags t ON rt.tag_id = t.tag_id
WHERE r.user_id = ?
GROUP BY r.resource_id";
$resources_stmt = $conn->prepare($resources_sql);
$resources_stmt->bind_param("i", $user_id);
$resources_stmt->execute();
$resources_result = $resources_stmt->get_result();
} else {
echo "User not found.";
exit();
}

$user_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($username); ?>'s Resources</title>
<style>
.resource-cards {
display: flex;
flex-wrap: wrap;
gap: 30px;
max-width: 90%;
margin: 20px auto;
padding: 20px;
}
.resource-card {
width: 300px;
border: 1px solid #ccc;
padding: 10px;
display: flex;
flex-direction: column;
border-radius: 18px;
gap: 10px;
background-color: #fff;
}
.resource-card img {
max-width: 100%;
height: 200px;
border-radius: 18px;
}
.resource-card h3 {
font-size: 18px;
margin-bottom: 10px;
}
.resource-card p {
font-size: 14px;
margin-bottom: 5px;
}
.resource-card .description {
display: -webkit-box;
-webkit-line-clamp: 2;
-webkit-box-orient: vertical;
overflow: hidden;
text-overflow: ellipsis;
}
.resource-card a.view-button {
text-align: center;
padding: 10px;
margin-top: auto;
background-color: #007bff;
color: #fff;
text-decoration: none;
border-radius: 5px;
}
.resource-card a.view-button:hover {
background-color: #0056b3;
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="resource-cards">
<?php while ($resource_row = $resources_result->fetch_assoc()): ?>
<div class="resource-card">
<?php if (!empty($resource_row['cover_image'])): ?>
<img src="data:image/jpeg;base64,<?php echo base64_encode($resource_row['cover_image']); ?>" alt="<?php echo htmlspecialchars($resource_row['title']); ?>">
<?php else: ?>
<img src="placeholder.jpg" alt="Placeholder">
<?php endif; ?>
<h3><?php echo htmlspecialchars($resource_row['title']); ?></h3>
<p class="description"><?php echo htmlspecialchars(substr($resource_row['resource_description'], 0, 100)); if (strlen($resource_row['resource_description']) > 100) echo "..."; ?></p>
<a href="view_resource.php?resource_id=<?php echo base64_encode($resource_row['resource_id']); ?>" class="view-button">View Resource</a>
</div>
<?php endwhile; ?>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
