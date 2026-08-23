// ============================================================
// BuildORA - Theme Controller & Interactive UI Enhancements
// ============================================================

(function () {
    // 1. Theme State Initialization (Immediate Anti-Flash)
    function updateDOMTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add("dark-mode");
            if (document.body) {
                document.body.classList.add("dark-mode");
            }
        } else {
            document.documentElement.classList.remove("dark-mode");
            if (document.body) {
                document.body.classList.remove("dark-mode");
            }
        }
    }

    const savedTheme = localStorage.getItem("theme");
    updateDOMTheme(savedTheme === "dark");

    // 2. Main interactive features when DOM is loaded
    function initApp() {
        const isReducedMotion = window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches;

        // Synchronize theme toggle buttons
        const isDark = localStorage.getItem("theme") === "dark";
        updateDOMTheme(isDark);

        const toggleButtons = document.querySelectorAll(
            "#theme-toggle, .theme-toggle"
        );

        toggleButtons.forEach(function (btn) {
            btn.textContent = isDark ? "☀️" : "🌙";
            btn.setAttribute(
                "aria-label",
                isDark ? "Switch to light mode" : "Switch to dark mode"
            );
            btn.setAttribute(
                "title",
                isDark ? "Switch to light mode" : "Switch to dark mode"
            );

            btn.onclick = function (e) {
                e.preventDefault();
                const wasDark = localStorage.getItem("theme") === "dark";
                const nowDark = !wasDark;
                localStorage.setItem("theme", nowDark ? "dark" : "light");
                updateDOMTheme(nowDark);

                toggleButtons.forEach(function (b) {
                    b.textContent = nowDark ? "☀️" : "🌙";
                    b.setAttribute(
                        "aria-label",
                        nowDark ? "Switch to light mode" : "Switch to dark mode"
                    );
                    b.setAttribute(
                        "title",
                        nowDark ? "Switch to light mode" : "Switch to dark mode"
                    );
                });
            };
        });

        // 3. Number Counter Animation for Statistics
        initCounters(isReducedMotion);

        // 4. Scroll Reveal Animations
        initScrollReveal(isReducedMotion);

        // 5. Asynchronous Like Button Handler
        initLikeButtons();

        // 6. Clickable Editorial Cards
        initClickableCards();
    }

    // Make entire blog post card clickable while preserving inner interactive elements
    function initClickableCards() {
        document.addEventListener("click", function (e) {
            const card = e.target.closest(".editorial-card[data-url]");
            if (!card) return;

            // If clicked on like button or any interactive button, do not navigate
            if (e.target.closest(".card-like-btn, button, input, select, textarea")) {
                return;
            }

            const url = card.getAttribute("data-url");
            if (url) {
                window.location.href = url;
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                const card = document.activeElement;
                if (card && card.matches && card.matches(".editorial-card[data-url]")) {
                    const url = card.getAttribute("data-url");
                    if (url) {
                        window.location.href = url;
                    }
                }
            }
        });
    }

    // Interactive AJAX Like / Unlike Controller
    function initLikeButtons() {
        document.addEventListener("click", function (e) {
            const likeBtn = e.target.closest(".like-button:not(.guest-like), .card-like-btn:not(.guest-like)");
            if (!likeBtn) return;

            e.preventDefault();
            const postId = likeBtn.getAttribute("data-post-id");
            if (!postId || likeBtn.disabled) return;

            likeBtn.disabled = true;

            const formData = new FormData();
            formData.append("post_id", postId);

            fetch("toggle-like.php", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(function (res) {
                if (res.status === 401) {
                    window.location.href = "login.php";
                    return null;
                }
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.success) return;

                const isLiked = data.liked;
                const newCount = data.like_count;

                // Update all like buttons for this post on the page
                const allMatchingBtns = document.querySelectorAll(
                    '[data-post-id="' + postId + '"]'
                );

                allMatchingBtns.forEach(function (btn) {
                    if (isLiked) {
                        btn.classList.add("liked");
                    } else {
                        btn.classList.remove("liked");
                    }

                    // Heart SVG fill
                    const svgHeart = btn.querySelector("svg");
                    if (svgHeart) {
                        svgHeart.setAttribute("fill", isLiked ? "currentColor" : "none");
                    }

                    // Text label if present (single post page)
                    const labelSpan = btn.querySelector(".like-text, .like-label");
                    if (labelSpan) {
                        labelSpan.textContent = isLiked ? "Liked" : "Like Story";
                    }

                    // Title tooltip
                    btn.setAttribute("title", isLiked ? "Unlike this story" : "Like this story");

                    // Counter
                    const counter = btn.querySelector(".like-count, .card-like-count, .like-counter");
                    if (counter) {
                        counter.textContent = newCount;
                        counter.setAttribute("data-count", newCount);
                    }
                });
            })
            .catch(function (err) {
                console.error("Error toggling like:", err);
            })
            .finally(function () {
                likeBtn.disabled = false;
            });
        });
    }

    // Number counting animation
    function initCounters(isReducedMotion) {
        if (isReducedMotion || !("IntersectionObserver" in window)) return;

        const countElements = document.querySelectorAll("[data-count]");
        if (!countElements.length) return;

        const observer = new IntersectionObserver(
            function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animateCount(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.2 }
        );

        countElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    function animateCount(el) {
        const target = parseInt(el.getAttribute("data-count"), 10);
        if (isNaN(target) || target <= 0) return;

        const duration = 1200; // 1.2s smooth count
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(easeProgress * target);

            el.textContent = current;

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.textContent = target;
            }
        }

        requestAnimationFrame(update);
    }

    // Scroll reveal observer
    function initScrollReveal(isReducedMotion) {
        const revealTargets = document.querySelectorAll(
            ".hero-content, .art-visual-right, .stats-narrative, .metric-card, .curated-filter-suite, .editorial-card, .pillar-card, .cta-banner-box, .empty-state-editorial, .project-card, .auth-card, .project-detail-content, .empty-dashboard"
        );

        if (isReducedMotion || !("IntersectionObserver" in window)) {
            revealTargets.forEach(function (el) {
                el.classList.add("is-revealed");
            });
            return;
        }

        revealTargets.forEach(function (el) {
            el.classList.add("reveal-on-scroll");
        });

        const revealObserver = new IntersectionObserver(
            function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-revealed");
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.08, rootMargin: "0px 0px -25px 0px" }
        );

        revealTargets.forEach(function (el) {
            revealObserver.observe(el);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initApp);
    } else {
        initApp();
    }
})();
