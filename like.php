<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to like or unlike resources.";
    exit();
}

if (!isset($_GET['resource_id']) || !isset($_GET['action'])) {
    echo "Invalid request.";
    exit();
}

$user_id = $_SESSION['user_id'];
$resource_id = base64_decode(urldecode($_GET['resource_id']));
$action = $_GET['action'];

if ($action == 'like') {
    $like_sql = "INSERT INTO likes (user_id, resource_id) VALUES (?, ?)";
    $update_likes_sql = "UPDATE resources SET likes = likes + 1 WHERE resource_id = ?";
} else if ($action == 'unlike') {
    $like_sql = "DELETE FROM likes WHERE user_id = ? AND resource_id = ?";
    $update_likes_sql = "UPDATE resources SET likes = likes - 1 WHERE resource_id = ?";
} else {
    echo "Invalid action.";
    exit();
}

$like_stmt = $conn->prepare($like_sql);
$like_stmt->bind_param("ii", $user_id, $resource_id);
$like_stmt->execute();
$like_stmt->close();

$update_likes_stmt = $conn->prepare($update_likes_sql);
$update_likes_stmt->bind_param("i", $resource_id);
$update_likes_stmt->execute();
$update_likes_stmt->close();

$conn->close();

header("Location: view_resource.php?resource_id=" . urlencode(base64_encode($resource_id)));
exit();
?>
