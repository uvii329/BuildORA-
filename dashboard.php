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

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

// Get user's blog posts with category and likes
$stmt = $conn->prepare("
    SELECT posts.*, categories.name AS category_name,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = posts.id) AS like_count
    FROM posts
    LEFT JOIN categories ON posts.category_id = categories.id
    WHERE posts.user_id = ?
    ORDER BY posts.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();


// Count posts
$postCount = $result ? $result->num_rows : 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Dashboard - BuildORA</title>

    <script>
        (function () {
            const savedTheme = localStorage.getItem("theme");
            if (savedTheme === "dark") {
                document.documentElement.classList.add("dark-mode");
            }
        })();
    </script>

    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">

</head>

<body>

//NAVBAR

<header class="navbar">

    <div class="container navbar-container">

        <a href="index.php" class="logo">Build<span class="logo-accent">ORA</span></a>

        <nav class="nav-links">

            <a href="index.php">
                Explore Posts
            </a>

            <a
                href="create-project.php"
                class="nav-button"
            >
                + Write a Post
            </a>

            <a href="logout.php">
                Logout
            </a>

            <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme">
                🌙
            </button>

        </nav>

    </div>

</header>


//DASHBOARD 
<section class="dashboard-section">
    <div class="container dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <span class="hero-badge">Creator Dashboard</span>
                <h1>
                    Welcome back, <?php echo htmlspecialchars($username); ?> 👋
                </h1>
                <p>
                    Manage and publish your stories, challenges, and project lessons.
                </p>
            </div>

            <a href="create-project.php" class="hero-button primary">
                <span>+ Write a Post</span>
            </a>
        </div>

        <!-- Posts -->
        <div class="dashboard-projects">
            <div class="dashboard-section-heading">
                <h2>Your Posts</h2>
                <p>Stories and project breakdowns you've published on BuildORA.</p>
            </div>

            <?php if ($postCount > 0): ?>
                <div class="project-grid">
                    <?php while ($post = $result->fetch_assoc()): ?>
                        <article class="project-card">
                            <!-- Image -->
                            <div class="project-image">
                                <?php if (!empty($post["image"])): ?>
                                    <img
                                        src="uploads/<?php echo htmlspecialchars($post["image"]); ?>"
                                        alt="<?php echo htmlspecialchars($post["title"]); ?>"
                                    >
                                <?php else: ?>
                                    <span class="placeholder-emoji-art">🚀</span>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="project-content">
                                <div class="dashboard-card-top-meta">
                                    <?php if (!empty($post["category_name"])): ?>
                                        <span class="post-category-badge">
                                            <?php echo htmlspecialchars($post["category_name"]); ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="dashboard-like-indicator" title="<?php echo (int)$post['like_count']; ?> appreciation likes">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        <span><?php echo (int)$post['like_count']; ?></span>
                                    </span>
                                </div>

                                <h3>
                                    <a href="project.php?id=<?php echo $post["id"]; ?>">
                                        <?php echo htmlspecialchars($post["title"]); ?>
                                    </a>
                                </h3>

                                <p>
                                    <?php
                                    $contentSnippet = $post["content"] ?? "";
                                    if (strlen($contentSnippet) > 120) {
                                        echo htmlspecialchars(substr($contentSnippet, 0, 120)) . "...";
                                    } else {
                                        echo htmlspecialchars($contentSnippet);
                                    }
                                    ?>
                                </p>

                                //Actions
                                <div class="dashboard-actions">
                                    <a href="project.php?id=<?php echo $post["id"]; ?>" class="view-button">
                                        <span>Read</span>
                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>

                                    <a href="edit-project.php?id=<?php echo $post["id"]; ?>" class="edit-button">
                                        Edit
                                    </a>

                                    <a
                                        href="delete-project.php?id=<?php echo $post["id"]; ?>"
                                        class="delete-button"
                                        onclick="return confirm('Are you sure you want to delete this post?');"
                                    >
                                        Delete
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-dashboard">
                    <div class="empty-icon">🚀</div>
                    <h2>No stories published yet</h2>
                    <p>
                        You haven't written any project stories or lessons learned yet. Start by sharing what you built and what you learned from the process.
                    </p>
                    <a href="create-project.php" class="hero-button primary">
                        <span>+ Write Your First Post</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>


//FOOTER

<footer class="footer">

    <div class="container">

        <p>
            © 2026
            <strong>BuildORA</strong>.
            Share What You Build. Share What You Learn.
        </p>

    </div>

</footer>

<script src="js/theme.js?v=<?php echo time(); ?>"></script>
</body>

</html>