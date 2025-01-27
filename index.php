<?php
include 'config.php';

$search = '';
$filter = 'newest'; // Default filter
$results = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $search = trim($_GET['search'] ?? '');
    $filter = trim($_GET['filter'] ?? 'newest'); // Set default filter to 'newest'
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="./styles/home.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
<div class="search-bar">
<form method="GET" action="index.php">
    <input type="text" name="search" placeholder="Search by title, tags, or creator" value="<?php echo htmlspecialchars($search); ?>">
    <input type="submit" value="Search">
    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
</form>
</div>
<br>
<div class="filter-buttons">
    <a href="index.php?search=<?php echo urlencode($search); ?>&filter=newest" class="btn <?php echo $filter == 'newest' ? 'btn-primary' : 'btn-secondary'; ?>">Newest</a>
    <a href="index.php?search=<?php echo urlencode($search); ?>&filter=likes" class="btn <?php echo $filter == 'likes' ? 'btn-primary' : 'btn-secondary'; ?>">Most Likes</a>
    <a href="index.php?search=<?php echo urlencode($search); ?>&filter=views" class="btn <?php echo $filter == 'views' ? 'btn-primary' : 'btn-secondary'; ?>">Most Views</a>
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
<div class="views-likes">
<p><i class="fa-regular fa-eye"></i> &nbsp;&nbsp;<?php echo $row['views']; ?></p>
<p><i class="fa-regular fa-heart"></i>&nbsp;&nbsp;<?php echo $row['likes']; ?></p>
</div>
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
