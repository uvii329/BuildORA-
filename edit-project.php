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

$message = "";
$success = false;


// Get post belonging to logged-in user
$stmt = $conn->prepare(
    "SELECT * FROM posts WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("You are not allowed to edit this post.");
}

$project = $result->fetch_assoc();

$stmt->close();


// Fetch all categories for the dropdown
$categoriesResult = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $category_id = isset($_POST["category_id"]) ? (int)$_POST["category_id"] : 0;

    // Keep existing image by default
    $imageName = $project["image"];

    if (empty($title) || empty($content)) {

        $message =
            "Post title and content are required.";

    } elseif ($category_id <= 0) {

        $message = "Please select a category for your post.";

    } else {

        // Validate that category exists
        $catCheck = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $catCheck->bind_param("i", $category_id);
        $catCheck->execute();
        $catCheckRes = $catCheck->get_result();
        if ($catCheckRes->num_rows !== 1) {
            $message = "Selected category is invalid.";
        }
        $catCheck->close();

        if (empty($message)) {
            // Check whether a new image was uploaded
            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] === 0
            ) {

                $allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                $fileType = $_FILES["image"]["type"];

                if (!in_array($fileType, $allowedTypes)) {

                    $message =
                        "Only JPG, PNG, and WEBP images are allowed.";

                } elseif (
                    $_FILES["image"]["size"] >
                    5 * 1024 * 1024
                ) {

                    $message =
                        "Image size must be less than 5MB.";

                } else {

                    $extension = pathinfo(
                        $_FILES["image"]["name"],
                        PATHINFO_EXTENSION
                    );

                    $newImageName =
                        uniqid("post_", true)
                        . "."
                        . $extension;

                    if (!is_dir("uploads")) {
                        mkdir("uploads", 0755, true);
                    }

                    $uploadPath =
                        "uploads/" . $newImageName;

                    if (
                        move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $uploadPath
                        )
                    ) {

                        // Delete old image
                        if (
                            !empty($project["image"]) &&
                            file_exists(
                                "uploads/" . $project["image"]
                            )
                        ) {

                            unlink(
                                "uploads/" . $project["image"]
                            );
                        }

                        $imageName = $newImageName;

                    } else {

                        $message =
                            "Failed to upload new image.";
                    }
                }
            }


            // Update post with category
            if (empty($message)) {

                $stmt = $conn->prepare("
                    UPDATE posts
                    SET
                        category_id = ?,
                        title = ?,
                        content = ?,
                        image = ?,
                        updated_at = NOW()
                    WHERE id = ? AND user_id = ?
                ");

                $stmt->bind_param(
                    "isssii",
                    $category_id,
                    $title,
                    $content,
                    $imageName,
                    $project_id,
                    $user_id
                );

                if ($stmt->execute()) {

                    $success = true;
                    $project["title"] = $title;
                    $project["content"] = $content;
                    $project["category_id"] = $category_id;
                    $project["image"] = $imageName;

                    $message =
                        "Post updated successfully!";

                } else {

                    $message =
                        "Failed to update post: " . $conn->error;
                }

                $stmt->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Post - BuildORA</title>

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

            <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme">
                🌙
            </button>

        </nav>

    </div>

</header>


//EDIT FORM 

<section class="auth-page">

    <div class="auth-card project-form-card">

        <div class="auth-header">

            <span class="hero-badge">
                Edit Your Story
            </span>

            <h1>
                Edit Post
            </h1>

            <p>
                Update your story, architecture details, and project learnings.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div
                class="<?php
                    echo $success
                        ? 'form-success'
                        : 'form-message';
                ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <?php if (!$success): ?>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label for="title">
                    Post Title *
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?php
                        echo htmlspecialchars(
                            $project["title"]
                        );
                    ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="category_id">
                    Category *
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    required
                >
                    <option value="">-- Select a Category --</option>
                    <?php 
                    if ($categoriesResult && $categoriesResult->num_rows > 0): 
                        $categoriesResult->data_seek(0);
                        while ($cat = $categoriesResult->fetch_assoc()):
                            $selected = ((int)$project["category_id"] === (int)$cat["id"]) ? "selected" : "";
                    ?>
                        <option value="<?php echo $cat["id"]; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($cat["name"]); ?>
                        </option>
                    <?php 
                        endwhile;
                    endif; 
                    ?>
                </select>

            </div>


            <div class="form-group">

                <label for="content">
                    Story, Challenges & Lessons Learned *
                </label>

                <textarea
                    id="content"
                    name="content"
                    rows="10"
                    required
                ><?php
                    echo htmlspecialchars(
                        $project["content"] ?? ""
                    );
                ?></textarea>

            </div>


            <?php if (!empty($project["image"])): ?>

                <div class="current-image">

                    <p>
                        <strong>Current Cover Image</strong>
                    </p>

                    <img
                        src="uploads/<?php
                            echo htmlspecialchars(
                                $project["image"]
                            );
                        ?>"
                        alt="Current cover image"
                    >

                </div>

            <?php endif; ?>


            <div class="form-group">

                <label for="image">
                    Replace Cover Image
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <small>
                    Leave empty to keep the current image.
                    JPG, PNG, or WEBP. Maximum 5MB.
                </small>

            </div>


            <button
                type="submit"
                class="form-button"
            >
                Update Post
            </button>

        </form>

        <?php else: ?>

            <a
                href="project.php?id=<?php
                    echo $project_id;
                ?>"
                class="hero-button"
            >
                View Updated Post
            </a>

        <?php endif; ?>


        <div class="auth-footer">

            <a
                href="project.php?id=<?php
                    echo $project_id;
                ?>"
            >
                ← Back to Post
            </a>

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