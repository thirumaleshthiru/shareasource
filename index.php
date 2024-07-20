<?php
include 'config.php';

 $search = '';
$filter = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
$search = trim($_GET['search'] ?? '');
$filter = trim($_GET['filter'] ?? '');
$search_param = '%' . $search . '%';

 
$sql = "SELECT r.resource_id, r.title, r.link, r.cover_image, u.username, r.views, r.likes, r.resource_description
FROM resources r
INNER JOIN users u ON r.user_id = u.user_id
LEFT JOIN resource_tags rt ON r.resource_id = rt.resource_id
LEFT JOIN tags t ON rt.tag_id = t.tag_id
WHERE r.title LIKE ? OR t.tag_name LIKE ? OR u.username LIKE ?
GROUP BY r.resource_id";

 switch ($filter) {
case 'likes':
$sql .= " ORDER BY r.likes DESC";
break;
case 'views':
$sql .= " ORDER BY r.views DESC";
break;
default:
$sql .= " ORDER BY r.created_at DESC";
break;
}

$stmt = $conn->prepare($sql);

if ($stmt) {
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
$results = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
} else {
echo "Error preparing the statement: " . $conn->error;
}
} else {
 $sql = "SELECT r.resource_id, r.title, r.link, r.cover_image, u.username, r.views, r.likes, r.resource_description
FROM resources r
INNER JOIN users u ON r.user_id = u.user_id
ORDER BY r.created_at DESC";
$result = $conn->query($sql);

if ($result) {
$results = $result->fetch_all(MYSQLI_ASSOC);
} else {
echo "Error executing the query: " . $conn->error;
}
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ShareASource</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
main{
width:80%;
margin:auto;
}
.resource-cards {
display: flex;
flex-wrap: wrap;
gap: 50px;
padding:50px;
 
 
}
.resource-card {
width: 300px;
border: 1px solid #ccc;
padding: 5px;
display: flex;
flex-direction: column;
gap: 10px;
background-color: #fff;
height: 500px;
justify-content: space-around;
overflow:auto;
}
.resource-card img {
max-width: 100%;
height: 200px;

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
.resource-card .description {
display: -webkit-box;
-webkit-box-orient: vertical;
-webkit-line-clamp: 3;
overflow: hidden;
text-overflow: ellipsis;
}
 
.search-bar input[type="text"] {
border-radius: 3px;
padding: 9px;
width: 90% ;
border: 1px solid #ccc;
}
.search-bar input[type="submit"] {
border-radius: 10px;
padding: 5px;
border: none;
width: 100px;
text-align: center;
background-color: #DCA47C;
color: white;
}
.filter-options {
display: flex;
gap: 10px;
padding: 10px;
align-items: center;
} 
@media screen and (max-width: 800px) {
.resource-card {
width: 100%;
}
.resource-cards {
display: flex;
flex-wrap: wrap;
 
justify-content: center;
gap: 50px;
padding:10px;
}
.search-bar{
    padding: 10px;

}
.search-bar input[type="text"] {
border: 1px solid #ccc;
width: 99%;
}
.search-bar input[type="submit"] {
margin-top: 10px;
}
main{
width:100%;
 }
}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
<div class="search-bar">
<form method="GET" action="index.php">
<input type="text" name="search" placeholder="Search by title, tags, or creator" value="<?php echo htmlspecialchars($search); ?>">
<div class="filter-options">
<label for="filter">Sort by:</label>
<select name="filter" id="filter">
<option value="newest" <?php echo $filter == 'newest' ? 'selected' : ''; ?>>Newest</option>
<option value="likes" <?php echo $filter == 'likes' ? 'selected' : ''; ?>>Most Likes</option>
<option value="views" <?php echo $filter == 'views' ? 'selected' : ''; ?>>Most Views</option>
</select>
</div>
<input type="submit" value="Search">
</form>
</div>

<div class="resource-cards">
<?php if (count($results) > 0): ?>
<?php foreach ($results as $row): ?>
<div class="resource-card">
<?php if (!empty($row['cover_image'])): ?>
<img src="data:image/jpeg;base64,<?php echo base64_encode($row['cover_image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
<?php else: ?>
<img src="placeholder.jpg" alt="Placeholder">
<?php endif; ?>
<h3><?php echo htmlspecialchars($row['title']); ?></h3>
<p class="description"><?php echo htmlspecialchars($row['resource_description']); ?></p>
<p>Creator: <span class="creator"><?php echo htmlspecialchars($row['username']); ?></span></p>
<p>Views: <?php echo $row['views']; ?></p>
<p>Likes: <?php echo $row['likes']; ?></p>
<p><a href="view_resource.php?resource_id=<?php echo urlencode(base64_encode($row['resource_id'])); ?>">View Resource</a></p>
</div>
<?php endforeach; ?>
<?php else: ?>
<p>No resources found.</p>
<?php endif; ?>
</div>
</main>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
