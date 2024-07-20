<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}

$current_user_id = $_SESSION['user_id'];

if (!isset($_GET['user_id']) || !isset($_GET['action']) || !isset($_GET['resource_id'])) {
header("Location: view_resource.php");
exit();
}

$creator_id = $_GET['user_id'];
$action = $_GET['action'];
$encoded_resource_id = $_GET['resource_id'];

$resource_id = base64_decode(urldecode($encoded_resource_id));

if ($action !== 'follow' && $action !== 'unfollow') {
header("Location: view_resource.php?resource_id=" . urlencode(base64_encode($resource_id)));
exit();
}

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

header("Location: view_resource.php?resource_id=" . urlencode(base64_encode($resource_id)));
exit();
?>
