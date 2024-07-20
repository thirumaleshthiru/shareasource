<?php
session_start();
include 'config.php';

 
if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

$current_user_id = $_SESSION['user_id'];

 
$sql = "SELECT r.resource_id, r.title, r.link, r.cover_image, u.username
FROM resources r
INNER JOIN users u ON r.user_id = u.user_id
WHERE r.user_id = ?
ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

  
$resources = [];
$message = '';

if ($result && $result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
$resources[] = $row;
}
} else {
$message = "No resources found.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
 
<title>Manage Resources</title>
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
max-width: 98%;
margin: 50px auto;
padding: 20px;
border-radius: 10px;
display:flex;
flex-wrap:wrap;
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
.resource-card {
width: 300px;
border: 1px solid #939185;
margin: 10px;
padding: 10px;
display: flex;
flex-direction:column;
gap:5px;
 border-radius: 10px;
 justify-content:space-around;

}
.resource-card img {
max-width: 100%;
height: 200px;
border-radius: 10px;
}
.resource-card h3 {
font-size: 18px;
margin-bottom: 10px;
}
.resource-card p {
font-size: 14px;
margin-bottom: 5px;
}
.resource-card .creator {
font-style: italic;
color: #666;
}
.button-group{
margin-top:10px;
}
.button-group a {
display: inline-block;
padding: 5px 10px;
margin-right: 5px;
text-decoration: none;
color: #EEEDEB;
border-radius: 4px;
cursor: pointer;
}
.button-group a.update {
background-color: #2F3645;  
text-decoration:none;
}
.button-group a.delete {
background-color: #939185; 
text-decoration:none;
 }
.button-group a.update:hover{
    color:#939185;
    background-color:white;
    border:1px solid #939185;
    transition:all 0.5s;
}
.button-group a.delete:hover{
    color:#2F3645;
    background-color:white;
    border:1px solid #2F3645;
    transition:all 0.5s;
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>   

<div class="containerr">
<?php if ($message): ?>
<div class="message error"><?= $message ?></div>
<?php endif; ?>

<?php foreach ($resources as $resource): ?>
<div class="resource-card">
<?php if (!empty($resource['cover_image'])): ?>
<img src="data:image/jpeg;base64,<?php echo base64_encode($resource['cover_image']); ?>" alt="<?php echo htmlspecialchars($resource['title']); ?>">
<?php else: ?>
<img src="placeholder.jpg" alt="Placeholder">
<?php endif; ?>
<h3><?php echo htmlspecialchars($resource['title']); ?></h3>
<p>Creator: <span class="creator"><?php echo htmlspecialchars($resource['username']); ?></span></p>
<div class="button-group">
<a href="update_resource.php?resource_id=<?php echo $resource['resource_id']; ?>" class="update">Update</a>
<a href="delete_resource.php?resource_id=<?php echo $resource['resource_id']; ?>" class="delete">Delete</a>
</div>
</div>
<?php endforeach; ?>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
