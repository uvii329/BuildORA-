<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

$isAjax = (!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") 
          || (isset($_SERVER["HTTP_ACCEPT"]) && strpos($_SERVER["HTTP_ACCEPT"], "application/json") !== false);

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    if ($isAjax) {
        header("Content-Type: application/json");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "unauthorized",
            "message" => "Please login to like posts.",
            "redirect" => "login.php"
        ]);
        exit();
    }
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];
$post_id = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $post_id = isset($_POST["post_id"]) ? (int) $_POST["post_id"] : 0;
} elseif ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["post_id"])) {
    $post_id = (int) $_GET["post_id"];
}

if ($post_id <= 0) {
    if ($isAjax) {
        header("Content-Type: application/json");
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid post ID."]);
        exit();
    }
    header("Location: index.php");
    exit();
}

// Verify that the post exists
$checkPostStmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
$checkPostStmt->bind_param("i", $post_id);
$checkPostStmt->execute();
$postResult = $checkPostStmt->get_result();

if ($postResult->num_rows !== 1) {
    $checkPostStmt->close();
    if ($isAjax) {
        header("Content-Type: application/json");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Post not found."]);
        exit();
    }
    header("Location: index.php");
    exit();
}
$checkPostStmt->close();

// Check if the user has already liked this post
$checkLikeStmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
$checkLikeStmt->bind_param("ii", $post_id, $user_id);
$checkLikeStmt->execute();
$likeResult = $checkLikeStmt->get_result();
$alreadyLiked = ($likeResult->num_rows > 0);
$checkLikeStmt->close();

$isLikedNow = false;

if ($alreadyLiked) {
    // Unlike: delete from post_likes
    $deleteStmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $post_id, $user_id);
    $deleteStmt->execute();
    $deleteStmt->close();
    $isLikedNow = false;
} else {
    // Like: insert into post_likes
    $insertStmt = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $post_id, $user_id);
    $insertStmt->execute();
    $insertStmt->close();
    $isLikedNow = true;
}

// Get updated total like count for this post
$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM post_likes WHERE post_id = ?");
$countStmt->bind_param("i", $post_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$likeCount = ($countResult && $row = $countResult->fetch_assoc()) ? (int) $row["total"] : 0;
$countStmt->close();

if ($isAjax) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => true,
        "liked" => $isLikedNow,
        "like_count" => $likeCount,
        "post_id" => $post_id
    ]);
    exit();
}

// Fallback safe internal redirect
$returnUrl = "project.php?id=" . $post_id;
if (!empty($_SERVER["HTTP_REFERER"])) {
    $ref = $_SERVER["HTTP_REFERER"];
    $host = $_SERVER["HTTP_HOST"] ?? "";
    if (empty($host) || strpos($ref, $host) !== false) {
        $parsed = parse_url($ref);
        $path = $parsed["path"] ?? "";
        $query = isset($parsed["query"]) ? "?" . $parsed["query"] : "";
        if (!empty($path) && !preg_match('/^https?:\/\//i', $path)) {
            $returnUrl = basename($path) . $query;
        }
    }
}
header("Location: " . $returnUrl);
exit();
