<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

$current_user_id = isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : 0;

// Get total number of blog posts
$postCountResult = $conn->query(
    "SELECT COUNT(*) AS total FROM posts"
);

$postCount = $postCountResult ? ($postCountResult->fetch_assoc()["total"] ?? 0) : 0;

// Get total number of student writers
$writerCountResult = $conn->query(
    "SELECT COUNT(DISTINCT user_id) AS total FROM posts"
);

$writerCount = $writerCountResult ? ($writerCountResult->fetch_assoc()["total"] ?? 0) : 0;

// Get all categories for filter dropdown
$categoriesList = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Get search and category filter values
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$categoryFilter = isset($_GET["category"]) ? (int) $_GET["category"] : 0;

// Selected category name for active badge
$selectedCategoryName = "";
if ($categoryFilter > 0) {
    $catStmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $catStmt->bind_param("i", $categoryFilter);
    $catStmt->execute();
    $catRes = $catStmt->get_result();
    if ($catRes && $catRow = $catRes->fetch_assoc()) {
        $selectedCategoryName = $catRow["name"];
    }
    $catStmt->close();
}

// Dynamic query building with prepared statements
$whereClauses = [];
$params = [$current_user_id];
$types = "i";

if (!empty($search)) {
    $whereClauses[] = "(posts.title LIKE ? OR posts.content LIKE ? OR users.username LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if ($categoryFilter > 0) {
    $whereClauses[] = "posts.category_id = ?";
    $params[] = $categoryFilter;
    $types .= "i";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$sql = "
    SELECT posts.*, users.username, categories.name AS category_name,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = posts.id) AS like_count,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = posts.id AND user_id = ?) AS user_liked
    FROM posts
    JOIN users ON posts.user_id = users.id
    LEFT JOIN categories ON posts.category_id = categories.id
    {$whereSql}
    ORDER BY posts.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuildORA — Share What You Build. Share What You Learn.</title>

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

<!-- NAVBAR -->
<header class="navbar">
    <div class="container navbar-container">
        <a href="index.php" class="logo">Build<span class="logo-accent">ORA</span></a>

        <nav class="nav-links">
            <a href="index.php" class="nav-link active">Explore Posts</a>

            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="dashboard.php" class="nav-link">My Dashboard</a>
                <a href="create-project.php" class="nav-button">
                    <span>+ Write a Post</span>
                </a>
                <a href="logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-button">
                    <span>Get Started &rarr;</span>
                </a>
            <?php endif; ?>

            <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Toggle theme" title="Toggle theme">
                🌙
            </button>
        </nav>
    </div>
</header>


<!-- EDITORIAL HERO SECTION -->
<section class="hero">
    <!-- Ambient Artistic Layered Background & Radiant Visuals -->
    <div class="hero-bg-art" aria-hidden="true">
        <!-- Multi-Orb Radiant Aurora (Dominant on the Right Side) -->
        <div class="art-orb orb-primary-right"></div>
        <div class="art-orb orb-secondary-blue"></div>
        <div class="art-orb orb-accent-pink"></div>
        <div class="art-orb orb-ambient-left"></div>

        <!-- Abstract Flowing Tech & Storytelling Geometric Visuals (Right Canvas) -->
        <div class="art-visual-right">
            <svg class="art-flow-svg" viewBox="0 0 600 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="heroArtGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#818cf8" stop-opacity="0.45"/>
                        <stop offset="50%" stop-color="#c084fc" stop-opacity="0.3"/>
                        <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.15"/>
                    </linearGradient>
                    <linearGradient id="heroArtGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#f472b6" stop-opacity="0.35"/>
                        <stop offset="60%" stop-color="#818cf8" stop-opacity="0.2"/>
                        <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.05"/>
                    </linearGradient>
                    <radialGradient id="heroCoreGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#a855f7" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
                    </radialGradient>
                </defs>

                
                <circle cx="340" cy="300" r="240" fill="url(#heroCoreGlow)" class="art-core-pulse"/>

                
                <circle cx="340" cy="300" r="230" stroke="url(#heroArtGrad1)" stroke-width="1.5" stroke-dasharray="10 14" class="art-orbit art-orbit-1"/>
                <circle cx="340" cy="300" r="170" stroke="url(#heroArtGrad2)" stroke-width="1.8" class="art-orbit art-orbit-2"/>
                <circle cx="340" cy="300" r="105" stroke="url(#heroArtGrad1)" stroke-width="1.2" stroke-dasharray="5 9" class="art-orbit art-orbit-3"/>

                
                <path d="M 100 200 C 220 90, 420 140, 520 280 C 600 400, 430 520, 300 480 C 180 440, 160 320, 340 300" stroke="url(#heroArtGrad1)" stroke-width="2.2" stroke-linecap="round" fill="none" opacity="0.65" class="art-flow-curve-1"/>
                <path d="M 160 360 C 260 480, 460 440, 540 320 C 580 220, 440 120, 320 180" stroke="url(#heroArtGrad2)" stroke-width="1.8" stroke-dasharray="6 10" stroke-linecap="round" fill="none" opacity="0.55" class="art-flow-curve-2"/>

                
                <circle cx="340" cy="300" r="5" fill="#a5b4fc" class="art-node art-node-center"/>
                <circle cx="170" cy="300" r="3.5" fill="#38bdf8" class="art-node art-node-1"/>
                <circle cx="475" cy="195" r="4" fill="#f472b6" class="art-node art-node-2"/>
                <circle cx="430" cy="425" r="3.5" fill="#c084fc" class="art-node art-node-3"/>
                <circle cx="230" cy="155" r="3" fill="#818cf8" class="art-node art-node-4"/>
                <circle cx="510" cy="300" r="3" fill="#34d399" class="art-node art-node-5"/>
            </svg>
        </div>

        
        <div class="hero-star-particles">
            <span class="h-star star-1"></span>
            <span class="h-star star-2"></span>
            <span class="h-star star-3"></span>
            <span class="h-star star-4"></span>
            <span class="h-star star-5"></span>
            <span class="h-star star-6"></span>
        </div>
    </div>

    <div class="container hero-container">
    
        <div class="hero-content">
            <h1 class="hero-title">
                Stories behind the code <em class="editorial-serif">and lessons from</em> <span class="gradient-text">what you build.</span>
            </h1>

            <p class="hero-lead">
                Every project has a story. Share how you built it, the challenges you faced, and what you learned along the way.
            </p>

            <div class="hero-actions">
                <a href="#projects" class="hero-button primary">
                    <span>Explore Stories</span>
                    <svg class="btn-arrow" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>

                <?php if (isset($_SESSION["user_id"])): ?>
                    <a href="create-project.php" class="hero-button secondary">
                        <span>+ Write a Post</span>
                    </a>
                <?php else: ?>
                    <a href="register.php" class="hero-button secondary">
                        <span>Start Writing</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>



<section class="editorial-stats-section">
    <div class="container">
        <div class="editorial-stats-container">
            <!-- Left: Mission Statement -->
            <div class="stats-narrative">
                <h2 class="stats-heading">
                    Where student projects become <span class="editorial-serif">insightful engineering</span> stories.
                </h2>
                <p class="stats-subtext">
                    Every post on BuildORA unpacks the journey—from initial idea and tech stack choices to the real challenges faced and the key lessons learned along the way.
                </p>
                <a href="create-project.php" class="stats-inline-cta">
                    <span>Share what you learned from your project</span>
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>

            
            <div class="stats-metrics-grid">
                <div class="metric-card">
                    <span class="metric-index">01</span>
                    <div class="metric-val-wrap">
                        <span class="metric-number" data-count="<?php echo (int)$postCount; ?>"><?php echo $postCount; ?></span>
                        <span class="metric-suffix">+</span>
                    </div>
                    <span class="metric-label">Stories Published</span>
                    <p class="metric-desc">Articles on how projects were built, challenges faced, and lessons learned.</p>
                </div>

                <div class="metric-card">
                    <span class="metric-index">02</span>
                    <div class="metric-val-wrap">
                        <span class="metric-number" data-count="<?php echo (int)$writerCount; ?>"><?php echo $writerCount; ?></span>
                        <span class="metric-suffix">+</span>
                    </div>
                    <span class="metric-label">Student Writers</span>
                    <p class="metric-desc">Developers sharing their personal growth and technical experiences.</p>
                </div>

                <div class="metric-card">
                    <span class="metric-index">03</span>
                    <div class="metric-val-wrap">
                        <span class="metric-number">100</span>
                        <span class="metric-suffix">%</span>
                    </div>
                    <span class="metric-label">Open Access</span>
                    <p class="metric-desc">Free forever for students, developers, and campus communities.</p>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="projects-section" id="projects">
    <div class="container">
        <div class="section-header-editorial">
            <h2 class="section-main-title">Explore Blog Posts & Stories</h2>
            <p class="section-main-desc">
                Read real-world project breakdowns, technical challenges, and learning journeys written by student developers and creators.
            </p>
        </div>

        
        <div class="curated-filter-suite">
            <form method="GET" action="index.php#projects" class="search-form-editorial">
                <div class="search-bar-unified">
                    <div class="search-field-editorial">
                        <svg class="search-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            placeholder="Search by topic, story title, content, or author..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>

                    <div class="select-field-editorial">
                        <select name="category" onchange="this.form.submit()" aria-label="Filter by Category">
                            <option value="">All Categories</option>
                            <?php 
                            if ($categoriesList && $categoriesList->num_rows > 0): 
                                $categoriesList->data_seek(0);
                                while ($cat = $categoriesList->fetch_assoc()):
                                    $selected = ($categoryFilter === (int)$cat["id"]) ? "selected" : "";
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

                    <button type="submit" class="search-btn-editorial">
                        <span>Filter Posts</span>
                    </button>
                </div>
            </form>

            
            <?php if (!empty($search) || $categoryFilter > 0): ?>
                <div class="active-filter-strip">
                    <span class="active-strip-label">Active filters:</span>
                    <?php if (!empty($search)): ?>
                        <span class="active-badge">Keyword: "<?php echo htmlspecialchars($search); ?>"</span>
                    <?php endif; ?>
                    <?php if (!empty($selectedCategoryName)): ?>
                        <span class="active-badge">Category: <?php echo htmlspecialchars($selectedCategoryName); ?></span>
                    <?php endif; ?>
                    <a href="index.php#projects" class="reset-filter-btn">&times; Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>


        <?php if ($result && $result->num_rows > 0): ?>
            <div class="editorial-project-grid">
                <?php 
                $cardIndex = 0;
                while ($post = $result->fetch_assoc()): 
                    $cardIndex++;
                ?>
                    <article 
                        class="editorial-card <?php echo ($cardIndex === 1 && empty($search) && $categoryFilter === 0) ? 'card-featured' : ''; ?>"
                        data-url="project.php?id=<?php echo $post["id"]; ?>"
                        tabindex="0"
                        role="link"
                        aria-label="Read story: <?php echo htmlspecialchars($post["title"]); ?>"
                    >
                        <div class="card-media-wrapper">
                            <a href="project.php?id=<?php echo $post["id"]; ?>" class="media-link" tabindex="-1" aria-hidden="true">
                                <?php if (!empty($post["image"])): ?>
                                    <img
                                        src="uploads/<?php echo htmlspecialchars($post["image"]); ?>"
                                        alt="<?php echo htmlspecialchars($post["title"]); ?>"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <div class="editorial-placeholder-art">
                                        <div class="placeholder-geometric-bg"></div>
                                        <span class="placeholder-emoji-art">🚀</span>
                                    </div>
                                <?php endif; ?>
                                <div class="card-media-gradient"></div>

                                <?php if (!empty($post["category_name"])): ?>
                                    <span class="card-category-overlay-badge"><?php echo htmlspecialchars($post["category_name"]); ?></span>
                                <?php endif; ?>

                                <span class="card-view-badge">Read Story</span>
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="card-meta-header">
                                <div class="author-attribution">
                                    <div class="author-avatar-circle">
                                        <?php echo strtoupper(substr($post["username"], 0, 1)); ?>
                                    </div>
                                    <span class="author-name-text"><?php echo htmlspecialchars($post["username"]); ?></span>
                                </div>

                                <div class="card-meta-right">
                                    <span class="card-date-text">
                                        <?php 
                                        if (!empty($post["created_at"])) {
                                            echo date("M d, Y", strtotime($post["created_at"]));
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <h3 class="card-title">
                                <a href="project.php?id=<?php echo $post["id"]; ?>">
                                    <?php echo htmlspecialchars($post["title"]); ?>
                                </a>
                            </h3>

                            <p class="card-description">
                                <?php
                                $contentSnippet = $post["content"] ?? "";
                                if (strlen($contentSnippet) > 130) {
                                    echo htmlspecialchars(substr($contentSnippet, 0, 130)) . "...";
                                } else {
                                    echo htmlspecialchars($contentSnippet);
                                }
                                ?>
                            </p>

                            <div class="card-footer-action">
                                
                                <?php if (isset($_SESSION["user_id"])): ?>
                                    <button
                                        type="button"
                                        class="card-like-btn <?php echo ($post['user_liked'] > 0) ? 'liked' : ''; ?>"
                                        data-post-id="<?php echo $post['id']; ?>"
                                        id="card-like-<?php echo $post['id']; ?>"
                                        title="<?php echo ($post['user_liked'] > 0) ? 'Unlike this story' : 'Like this story'; ?>"
                                        aria-label="Like story"
                                    >
                                        <svg class="heart-icon" viewBox="0 0 24 24" fill="<?php echo ($post['user_liked'] > 0) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        <span class="card-like-count" id="card-like-count-<?php echo $post['id']; ?>" data-count="<?php echo (int)$post['like_count']; ?>">
                                            <?php echo (int)$post['like_count']; ?>
                                        </span>
                                    </button>
                                <?php else: ?>
                                    <a
                                        href="login.php"
                                        class="card-like-btn guest-like"
                                        title="Log in to like this story"
                                        aria-label="Log in to like story"
                                    >
                                        <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        <span class="card-like-count"><?php echo (int)$post['like_count']; ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-editorial">
                <div class="empty-icon-art">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                </div>
                <h3>No stories matched your criteria</h3>
                <p>
                    Try searching for different keywords or explore our latest developer stories.
                </p>
                <div class="empty-cta-row">
                    <a href="index.php#projects" class="hero-button secondary">Clear Search</a>
                    <a href="create-project.php" class="hero-button primary">+ Write a Post</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>



<section class="editorial-pillars-section">
    <div class="container">
        <div class="pillars-header">
            <h2 class="pillars-title">Share What You Build. Share What You Learn.</h2>
            <p class="pillars-desc">
                A dedicated blogging platform for developers to document their craft and inspire peer creators.
            </p>
        </div>

        <div class="pillars-grid">
            <div class="pillar-card">
                <h3>How It Was Built</h3>
                <p>
                    Go beyond the final code. Write in-depth breakdowns explaining architecture decisions, tools chosen, and technical workflows.
                </p>
            </div>

            <div class="pillar-card">
                <h3>Challenges & Hurdles</h3>
                <p>
                    Be transparent about bugs encountered, team dynamics, and hurdles overcome during the development process.
                </p>
            </div>

            <div class="pillar-card">
                <h3>Lessons & Personal Growth</h3>
                <p>
                    Document what you learned from your first project, team assignments, and continuous exploration in technology.
                </p>
            </div>
        </div>
    </div>
</section>


<section class="cta-banner-section">
    <div class="container">
        <div class="cta-banner-box">
            <div class="cta-glow-bg"></div>
            <div class="cta-content">
                <h2 class="cta-heading">Ready to share your project story?</h2>
                <p class="cta-subheading">
                    Turn your latest build into an inspiring blog post. Share your challenges, codebase, and takeaways with fellow students and creators.
                </p>

                <div class="cta-btn-group">
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <a href="create-project.php" class="hero-button primary">
                            <span>+ Write a Post</span>
                            <svg class="btn-arrow" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="hero-button primary">
                            <span>Create Free Account</span>
                            <svg class="btn-arrow" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    <?php endif; ?>

                    <a href="#projects" class="hero-button secondary">
                        <span>Explore Posts</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- FOOTER -->
<footer class="footer">
    <div class="container footer-container">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="index.php" class="logo">Build<span class="logo-accent">ORA</span></a>
                <p class="footer-tagline">
                    Share What You Build. Share What You Learn. The developer blog platform for students and creators to share the stories, challenges, and lessons behind their projects.
                </p>
            </div>

            <div class="footer-nav-col">
                <span class="footer-col-title">Navigation</span>
                <a href="index.php">Explore Posts</a>
                <a href="create-project.php">Write a Post</a>
                <a href="login.php">Account Login</a>
            </div>

            <div class="footer-nav-col">
                <span class="footer-col-title">Community</span>
                <a href="#projects">Popular Stories</a>
                <a href="register.php">Join BuildORA</a>
                <a href="dashboard.php">Dashboard</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© 2026 <strong>BuildORA</strong>. Share What You Build. Share What You Learn.</p>
        </div>
    </div>
</footer>

<script src="js/theme.js?v=<?php echo time(); ?>"></script>
</body>
</html>