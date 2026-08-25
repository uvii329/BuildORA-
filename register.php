<?php

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (
        empty($username) ||
        empty($email) ||
        empty($password)
    ) {

        $message = "Please fill in all fields.";

    } else {

        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE username = ? OR email = ?"
        );

        $check->bind_param(
            "ss",
            $username,
            $email
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message =
                "Username or email already exists.";

        } else {

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $stmt = $conn->prepare(
                "INSERT INTO users
                (username, email, password)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $username,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {
                $newUserId = $stmt->insert_id;
                $stmt->close();
                $check->close();

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Automatically log in the newly registered user
                $_SESSION["user_id"] = $newUserId;
                $_SESSION["username"] = $username;
                $_SESSION["role"] = "user";

                // Redirect directly to homepage (Explore Posts)
                header("Location: index.php");
                exit();
            } else {
                $message = "Registration failed. Please try again.";
                $stmt->close();
                $check->close();
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

    <title>Register - BuildORA</title>

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

            <a href="login.php">
                Login
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

            <h1>Create Your Account</h1>

            <p>
                Join the community to share what you build and what you learn.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="form-message">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="register.php">

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Choose a username"
                    autocomplete="username"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    autocomplete="email"
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
                    placeholder="Create a password"
                    autocomplete="new-password"
                    required
                >

            </div>


            <button
                type="submit"
                class="form-button"
            >
                Create Account
            </button>

        </form>


        <div class="auth-footer">

            Already have an account?

            <a href="login.php">
                Sign in
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