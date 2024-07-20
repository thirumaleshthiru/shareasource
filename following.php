<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

$current_user_id = $_SESSION['user_id'];

// Fetch user details from the database
$user_sql = "SELECT username FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $current_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
$user_row = $user_result->fetch_assoc();
$username = $user_row['username'];

// Fetch the users that the current user is following
$following_sql = "SELECT u.user_id, u.username,
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
WHERE follower_id = ? AND following_id = u.user_id
)";
$following_stmt = $conn->prepare($following_sql);
$following_stmt->bind_param("ii", $current_user_id, $current_user_id);
$following_stmt->execute();
$following_result = $following_stmt->get_result();

$following_stmt->close();
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
<title><?php echo htmlspecialchars($username); ?>'s Following</title>

<style>
.following-container {
max-width: 800px;
margin: 20px auto;
padding: 20px;
}

.following {
margin-bottom: 20px;
padding: 10px;
border: 1px solid #ddd;
background-color: #fff;
display: flex;
justify-content: space-between;
align-items: center;
}

.following h2 {
font-size: 20px;
margin-bottom: 10px;
}

.follow-button {
display: inline-block;
padding: 5px;
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
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="following-container">
<?php while ($following_row = $following_result->fetch_assoc()): ?>
<div class="following">
<h2><a href="/shareasource/<?php echo htmlspecialchars($following_row['username']); ?>"><?php echo htmlspecialchars($following_row['username']); ?></a></h2>
<?php if ($following_row['follow_status'] === 'unfollow'): ?>
<button class="follow-button unfollow" data-user-id="<?php echo $following_row['user_id']; ?>" data-action="unfollow">Unfollow</button>
<?php else: ?>
<button class="follow-button" data-user-id="<?php echo $following_row['user_id']; ?>" data-action="follow">Follow</button>
<?php endif; ?>
</div>
<?php endwhile; ?>
</div>

<script>
$(document).ready(function() {
$('.follow-button').click(function() {
var button = $(this);
var userId = button.data('user-id');
var action = button.data('action');

$.ajax({
url: 'following.php',
type: 'GET',
data: {
    user_id: userId,
    action: action
},
success: function(response) {
    if (action === 'follow') {
        button.removeClass('follow').addClass('unfollow').text('Unfollow').data('action', 'unfollow');
        button.css('background-color', '#dc3545');
    } else {
        button.removeClass('unfollow').addClass('follow').text('Follow').data('action', 'follow');
        button.css('background-color', '#007bff');
    }
}
});
});
});
</script>
</body>
</html>

<?php
include 'config.php';

if (isset($_GET['user_id']) && isset($_GET['action'])) {
$creator_id = $_GET['user_id'];
$action = $_GET['action'];

if ($action === 'follow') {
$check_follow_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
$check_follow_stmt = $conn->prepare($check_follow_sql);
$check_follow_stmt->bind_param("ii", $current_user_id, $creator_id);
$check_follow_stmt->execute();
$check_follow_result = $check_follow_stmt->get_result();

if ($check_follow_result->num_rows === 0) {
$follow_sql = "INSERT INTO followers (follower_id, following_id) VALUES (?, ?)";
$follow_stmt = $conn->prepare($follow_sql);
$follow_stmt->bind_param("ii", $current_user_id, $creator_id);
$follow_stmt->execute();
$follow_stmt->close();
}

$check_follow_stmt->close();
} elseif ($action === 'unfollow') {
$unfollow_sql = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
$unfollow_stmt = $conn->prepare($unfollow_sql);
$unfollow_stmt->bind_param("ii", $current_user_id, $creator_id);
$unfollow_stmt->execute();
$unfollow_stmt->close();
}

$conn->close();
exit();
}
?>
