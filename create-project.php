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

$message = "";
$success = false;

// Fetch all categories for the dropdown
$categoriesResult = $conn->query("SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $category_id = isset($_POST["category_id"]) ? (int)$_POST["category_id"] : 0;
    $imageName = "";

    if (empty($title) || empty($content)) {

        $message = "Post title and content are required.";

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
            // Handle cover image
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {

                $allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                $fileType = $_FILES["image"]["type"];

                if (!in_array($fileType, $allowedTypes)) {

                    $message = "Only JPG, PNG, and WEBP images are allowed.";

                } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {

                    $message = "Image size must be less than 5MB.";

                } else {

                    $extension = pathinfo(
                        $_FILES["image"]["name"],
                        PATHINFO_EXTENSION
                    );

                    $imageName =
                        uniqid("post_", true) . "." . $extension;

                    if (!is_dir("uploads")) {
                        mkdir("uploads", 0755, true);
                    }

                    $uploadPath =
                        "uploads/" . $imageName;

                    if (!move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $uploadPath
                    )) {

                        $message = "Failed to upload cover image.";
                        $imageName = "";
                    }
                }
            }

            if (empty($message)) {

                $user_id = $_SESSION["user_id"];

                $stmt = $conn->prepare(
                    "INSERT INTO posts (user_id, category_id, title, content, image)
                     VALUES (?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "iisss",
                    $user_id,
                    $category_id,
                    $title,
                    $content,
                    $imageName
                );

                if ($stmt->execute()) {

                    $success = true;
                    $message =
                        "Your post has been published successfully!";

                } else {

                    $message =
                        "Failed to publish post: " . $conn->error;
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

    <title>Write a Post - BuildORA</title>

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


//FORM

<section class="auth-page">

    <div class="auth-card project-form-card">

        <div class="auth-header">

            <span class="hero-badge">
                Share Your Experience
            </span>

            <h1>Write a Post</h1>

            <p>
                Share the story, challenges, architecture, and lessons learned behind your project.
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
                    value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                    placeholder="e.g. How We Built Our Robotic Dog, Building a Lost & Found System..."
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
                            $selected = (isset($_POST["category_id"]) && (int)$_POST["category_id"] === (int)$cat["id"]) ? "selected" : "";
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
                    Post Content & Story *
                </label>

                <textarea
                    id="content"
                    name="content"
                    rows="10"
                    placeholder="Describe how you built the project, challenges faced, technologies used, and key lessons learned..."
                    required
                ></textarea>

            </div>


            <div class="form-group">

                <label for="image">
                    Cover Image (Optional)
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <small>
                    Upload a JPG, PNG, or WEBP image. Maximum size: 5MB.
                </small>

            </div>

            <button
                type="submit"
                class="form-button"
            >
                Publish Post
            </button>

        </form>

        <?php else: ?>

            <a
                href="index.php"
                class="hero-button"
            >
                Explore Posts
            </a>

        <?php endif; ?>


        <div class="auth-footer">

            <a href="index.php">
                ← Back to Explore Posts
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