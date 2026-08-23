<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

// Check post ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid post.");
}

$project_id = (int) $_GET["id"];
$current_user_id = isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : 0;

// Get post, author, category, and like metrics
$stmt = $conn->prepare("
    SELECT posts.*, users.username, categories.name AS category_name,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = posts.id) AS like_count,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = posts.id AND user_id = ?) AS user_liked
    FROM posts
    JOIN users ON posts.user_id = users.id
    LEFT JOIN categories ON posts.category_id = categories.id
    WHERE posts.id = ?
");

$stmt->bind_param("ii", $current_user_id, $project_id);
$stmt->execute();

$result = $stmt->get_result();

// Check whether post exists
if ($result->num_rows !== 1) {
    die("Post not found.");
}

$post = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($post["title"]); ?>
        - BuildORA
    </title>

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

            <?php if (isset($_SESSION["user_id"])): ?>

                <a href="dashboard.php">
                    My Dashboard
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

            <?php else: ?>

                <a href="login.php">
                    Login
                </a>

                <a
                    href="register.php"
                    class="nav-button"
                >
                    Get Started
                </a>

            <?php endif; ?>

            <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme">
                🌙
            </button>

        </nav>

    </div>

</header>


//POST DETAILS

<section class="project-detail-section">

    <div class="container project-detail-container">

        

        <div class="project-detail-header">

            <h1>
                <?php
                echo htmlspecialchars($post["title"]);
                ?>
            </h1>

            <div class="project-detail-meta">

                <?php if (!empty($post["category_name"])): ?>
                    <span class="detail-category-badge">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <?php echo htmlspecialchars($post["category_name"]); ?>
                    </span>
                <?php endif; ?>

                <span>
                    Written by
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $post["username"]
                        );
                        ?>
                    </strong>
                </span>

                <span>
                    Published on
                    <strong>
                        <?php
                        echo date(
                            "F j, Y",
                            strtotime(
                                $post["created_at"]
                            )
                        );
                        ?>
                    </strong>
                </span>

                <?php if (!empty($post["updated_at"]) && $post["updated_at"] !== $post["created_at"]): ?>
                    <span>
                        Updated on
                        <strong>
                            <?php
                            echo date(
                                "F j, Y",
                                strtotime(
                                    $post["updated_at"]
                                )
                            );
                            ?>
                        </strong>
                    </span>
                <?php endif; ?>

            </div>

        </div>


        

        <?php if (!empty($post["image"])): ?>

            <div class="project-detail-image">

                <img
                    src="uploads/<?php
                        echo htmlspecialchars(
                            $post["image"]
                        );
                    ?>"
                    alt="<?php
                        echo htmlspecialchars(
                            $post["title"]
                        );
                    ?>"
                >

            </div>

        <?php endif; ?>


       

        <div class="project-detail-content">

            <div class="detail-section">

                <h2>
                    The Story & Lessons Learned
                </h2>

                <div class="detail-description">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $post["content"]
                        )
                    );
                    ?>

                </div>

            </div>

         
            <div class="post-engagement-bar">
                <?php if (isset($_SESSION["user_id"])): ?>
                    <button
                        type="button"
                        class="like-button <?php echo ($post['user_liked'] > 0) ? 'liked' : ''; ?>"
                        data-post-id="<?php echo $post['id']; ?>"
                        id="like-btn-<?php echo $post['id']; ?>"
                        aria-label="Like this story"
                    >
                        <svg class="heart-icon" viewBox="0 0 24 24" fill="<?php echo ($post['user_liked'] > 0) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="like-text"><?php echo ($post['user_liked'] > 0) ? 'Liked' : 'Like Story'; ?></span>
                        <span class="like-count" id="like-count-<?php echo $post['id']; ?>" data-count="<?php echo (int)$post['like_count']; ?>">
                            <?php echo (int)$post['like_count']; ?>
                        </span>
                    </button>
                <?php else: ?>
                    <a
                        href="login.php"
                        class="like-button guest-like"
                        title="Log in to like this story"
                    >
                        <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="like-text">Like Story</span>
                        <span class="like-count"><?php echo (int)$post['like_count']; ?></span>
                    </a>
                <?php endif; ?>
            </div>


            

            <?php

            if (
                isset($_SESSION["user_id"]) &&
                $_SESSION["user_id"] == $post["user_id"]
            ):

            ?>

                <div class="owner-actions">

                    <a
                        href="edit-project.php?id=<?php
                            echo $post["id"];
                        ?>"
                        class="edit-button"
                    >
                        Edit Post
                    </a>


                    <a
                        href="delete-project.php?id=<?php
                            echo $post["id"];
                        ?>"
                        class="delete-button"
                        onclick="return confirm(
                            'Are you sure you want to delete this post?'
                        );"
                    >
                        Delete Post
                    </a>

                </div>

            <?php endif; ?>

        </div>


        <a
            href="index.php"
            class="back-link"
        >
            ← Back to Explore Posts
        </a>

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