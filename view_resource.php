<?php
session_start();
include 'config.php';

if (!isset($_GET['resource_id'])) {
echo "Resource ID not specified.";
exit();
}

$encoded_resource_id = $_GET['resource_id'];
$resource_id = base64_decode(urldecode($encoded_resource_id));

$update_views_sql = "UPDATE resources SET views = views + 1 WHERE resource_id = ?";
$update_views_stmt = $conn->prepare($update_views_sql);
$update_views_stmt->bind_param("i", $resource_id);
$update_views_stmt->execute();
$update_views_stmt->close();

$sql = "SELECT r.*, u.username, u.user_id AS creator_id
FROM resources r
INNER JOIN users u ON r.user_id = u.user_id
WHERE r.resource_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
$row = $result->fetch_assoc();
$title = $row['title'];
$link = $row['link'];
$cover_image = $row['cover_image'];
$creator_id = $row['creator_id'];
$creator = $row['username'];
$description = $row['resource_description'];
$views = $row['views'];
$likes = $row['likes'];

$tags_sql = "SELECT t.tag_name
FROM tags t
INNER JOIN resource_tags rt ON t.tag_id = rt.tag_id
WHERE rt.resource_id = ?";
$tags_stmt = $conn->prepare($tags_sql);
$tags_stmt->bind_param("i", $resource_id);
$tags_stmt->execute();
$tags_result = $tags_stmt->get_result();
$tags = [];
while ($tag_row = $tags_result->fetch_assoc()) {
$tags[] = $tag_row['tag_name'];
}
$tags_stmt->close();
} else {
echo "Resource not found.";
exit();
}

$stmt->close();

$current_user_id = null;
$follow_status = $follow_text = $like_status = $like_text = '';

if (isset($_SESSION['user_id'])) {
$current_user_id = $_SESSION['user_id'];

if ($current_user_id != $creator_id) {
$check_follow_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
$check_follow_stmt = $conn->prepare($check_follow_sql);
$check_follow_stmt->bind_param("ii", $current_user_id, $creator_id);
$check_follow_stmt->execute();
$check_follow_result = $check_follow_stmt->get_result();

if ($check_follow_result->num_rows > 0) {
$follow_status = "unfollow";
$follow_text = '<i class="fa-solid fa-minus"></i> Unfollow';
} else {
$follow_status = "follow";
$follow_text = '<i class="fa-solid fa-plus"></i> Follow';
}

$check_follow_stmt->close();
}

$check_like_sql = "SELECT * FROM likes WHERE user_id = ? AND resource_id = ?";
$check_like_stmt = $conn->prepare($check_like_sql);
$check_like_stmt->bind_param("ii", $current_user_id, $resource_id);
$check_like_stmt->execute();
$check_like_result = $check_like_stmt->get_result();

if ($check_like_result->num_rows > 0) {
$like_status = "unlike";
$like_text = '<i class="fa-solid fa-heart"></i> Unlike';
} else {
$like_status = "like";
$like_text = '<i class="fa-regular fa-heart"></i> Like';
}

$check_like_stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($description); ?>">
<link rel="stylesheet" href="style.css">  
<link rel="stylesheet" href="./styles/view.css"> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" /> <!-- Icon library -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >  
</head>
<body>
<?php include 'navbar.php' ?>

<main>
<div class="resource-details">
    
<?php if (!isset($_SESSION['user_id']) || $current_user_id != $creator_id): ?>
<?php if (isset($_SESSION['user_id'])): ?>
<?php if ($follow_status === 'follow'): ?>
<a href="follow.php?user_id=<?php echo $creator_id; ?>&action=follow&resource_id=<?php echo urlencode(base64_encode($resource_id)); ?>" class="follow-button"><?php echo $follow_text; ?></a>
<?php else: ?>
<a href="follow.php?user_id=<?php echo $creator_id; ?>&action=unfollow&resource_id=<?php echo urlencode(base64_encode($resource_id)); ?>" class="follow-button"><?php echo $follow_text; ?></a>
<?php endif; ?>
<?php if ($like_status === 'like'): ?>
<a href="like.php?user_id=<?php echo $current_user_id; ?>&resource_id=<?php echo urlencode(base64_encode($resource_id)); ?>&action=like" class="like-button"><?php echo $like_text; ?></a>
<?php else: ?>
<a href="like.php?user_id=<?php echo $current_user_id; ?>&resource_id=<?php echo urlencode(base64_encode($resource_id)); ?>&action=unlike" class="like-button"><?php echo $like_text; ?></a>
<?php endif; ?>
<?php else: ?>
<a href="login.php" class="follow-button">Follow</a>
<a href="login.php" class="like-button">Like</a>
<?php endif; ?>
<?php endif; ?>
<br>


<?php if (!empty($cover_image)): ?>
<img src="data:image/jpeg;base64,<?php echo base64_encode($cover_image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
<?php else: ?>
<img src="placeholder.jpg" alt="Placeholder">
<?php endif; ?>
<h2><?php echo htmlspecialchars($title); ?></h2>
<p class="creator">Creator: <span><a href="/shareasource/<?php echo htmlspecialchars($creator); ?>"><?php echo htmlspecialchars($creator); ?></a></span></p>
<p class="description">Description:</p>
<p><?php echo nl2br(htmlspecialchars($description)); ?></p>
<p class="link">Link: <span class="link-wrapper"><a href="<?php echo htmlspecialchars($link); ?>" target="_blank"><?php echo htmlspecialchars($link); ?></a></span></p>

<div class="resource-video" id="video-container">
</div>
<br>
<p class="tags">
  
    <?php foreach ($tags as $tag): ?>
        <a href="index.php?search=<?php echo urlencode($tag); ?>&filter=newest" class="tag-button"><?php echo htmlspecialchars($tag); ?></a>
    <?php endforeach; ?>
</p>

</div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
var link = "<?php echo htmlspecialchars($link); ?>";
var videoContainer = document.getElementById('video-container');

function extractVideoId(link) {
var videoId = '';
var regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
var match = link.match(regex);
if (match) {
videoId = match[1];
}
return videoId;
}

function embedYouTubeVideo(link) {
var videoId = extractVideoId(link);
if (videoId !== '') {
var iframe = document.createElement('iframe');
iframe.setAttribute('width', '560');
iframe.setAttribute('height', '315');
iframe.setAttribute('src', 'https://www.youtube.com/embed/' + videoId);
iframe.setAttribute('frameborder', '0');
iframe.setAttribute('allowfullscreen', '');
videoContainer.appendChild(iframe);
}
}

embedYouTubeVideo(link);
});
</script>

</body>
</html>