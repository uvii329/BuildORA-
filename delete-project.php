<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

// User must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check post ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid post.");
}

$project_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

// Get post to clean up image
$stmt = $conn->prepare(
    "SELECT image FROM posts WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $post = $result->fetch_assoc();
    if (!empty($post["image"]) && file_exists("uploads/" . $post["image"])) {
        unlink("uploads/" . $post["image"]);
    }
}
$stmt->close();

// Delete only if the post belongs to the logged-in user
$stmt = $conn->prepare(
    "DELETE FROM posts WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $project_id, $user_id);

$stmt->execute();

$stmt->close();

header("Location: dashboard.php");
exit();

?>