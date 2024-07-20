<?php
session_start();
include 'config.php';

 if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

$current_user_id = $_SESSION['user_id'];

 if (isset($_GET['user_id']) && isset($_GET['action'])) {
$target_user_id = $_GET['user_id'];
$action = $_GET['action'];

if ($action === 'follow') {
$follow_sql = "INSERT INTO followers (follower_id, following_id) VALUES (?, ?)";
$follow_stmt = $conn->prepare($follow_sql);
$follow_stmt->bind_param("ii", $current_user_id, $target_user_id);
$follow_stmt->execute();
$follow_stmt->close();
} elseif ($action === 'unfollow') {
$unfollow_sql = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
$unfollow_stmt = $conn->prepare($unfollow_sql);
$unfollow_stmt->bind_param("ii", $current_user_id, $target_user_id);
$unfollow_stmt->execute();
$unfollow_stmt->close();
}
}
 
$user_sql = "SELECT username FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $current_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
$user_row = $user_result->fetch_assoc();
$username = $user_row['username'];

 $followers_sql = "SELECT u.user_id, u.username, 
CASE 
WHEN EXISTS (
SELECT * FROM followers 
WHERE follower_id = ? AND following_id = u.user_id
) THEN 'unfollow' 
ELSE 'follow' 
END AS follow_status
FROM users u
WHERE EXISTS (
SELECT * FROM followers 
WHERE follower_id = u.user_id AND following_id = ?
)";
$followers_stmt = $conn->prepare($followers_sql);
$followers_stmt->bind_param("ii", $current_user_id, $current_user_id);
$followers_stmt->execute();
$followers_result = $followers_stmt->get_result();

 
$followers_count_sql = "SELECT COUNT(*) AS followers_count FROM followers WHERE following_id = ?";
$followers_count_stmt = $conn->prepare($followers_count_sql);
$followers_count_stmt->bind_param("i", $current_user_id);
$followers_count_stmt->execute();
$followers_count_result = $followers_count_stmt->get_result();
$followers_count_row = $followers_count_result->fetch_assoc();
$followers_count = $followers_count_row['followers_count'];
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
<title><?php echo htmlspecialchars($username); ?>'s Followers</title>

<style>
.followers-container {
max-width: 800px;
margin: 20px auto;
padding: 20px;
border: 1px solid #ccc;
background-color: #f9f9f9;
}

.follower {
margin-bottom: 20px;
padding: 10px;
border: 1px solid #ddd;
background-color: #fff;
display: flex;
justify-content: space-between;
align-items: center;
}

.follower h2 {
font-size: 20px;
margin-bottom: 10px;
}

.follower p {
font-size: 16px;
margin-bottom: 10px;
}

.follow-button {
display: inline-block;
padding: 8px 16px;
background-color: #007bff;
color: #fff;
border: none;
cursor: pointer;
text-decoration: none;
border-radius: 4px;
}

.follow-button.unfollow {
background-color: #dc3545;  
}

.follow-button:hover {
background-color: #0056b3;
color: white;
text-decoration: none;
}
</style>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="followers-container">
<?php while ($follower_row = $followers_result->fetch_assoc()): ?>
<div class="follower">
<h2><a href="/shareasource/<?php echo htmlspecialchars($follower_row['username']); ?>"><?php echo htmlspecialchars($follower_row['username']); ?></a></h2>
<?php if ($follower_row['follow_status'] === 'unfollow'): ?>
<a href="?user_id=<?php echo $follower_row['user_id']; ?>&action=unfollow" class="follow-button unfollow">Unfollow</a>
<?php else: ?>
<a href="?user_id=<?php echo $follower_row['user_id']; ?>&action=follow" class="follow-button">Follow</a>
<?php endif; ?>
</div>
<?php endwhile; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
