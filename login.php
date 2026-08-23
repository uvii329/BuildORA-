<?php

session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                header("Location: index.php");
                exit();

            } else {

                $message = "Incorrect password.";
            }

        } else {

            $message = "No account found with that email.";
        }

        $stmt->close();
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

    <title>Login - BuildORA</title>

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

<header class="navbar">

    <div class="container navbar-container">

        <a href="index.php" class="logo">Build<span class="logo-accent">ORA</span></a>

        <nav class="nav-links">

            <a href="index.php">
                Explore Posts
            </a>

            <a href="register.php" class="nav-button">
                Get Started
            </a>

            <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme">
                🌙
            </button>

        </nav>

    </div>

</header>


<section class="auth-page">

    <div class="auth-card">

        <div class="auth-header">

            <h1>Welcome Back</h1>

            <p>
                Sign in to write, manage, and share your developer stories.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="form-message">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="form-button"
            >
                Sign In
            </button>

        </form>


        <div class="auth-footer">

            Don't have an account?

            <a href="register.php">
                Create one
            </a>

        </div>

    </div>

</section>


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