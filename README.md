# BuildORA - Developer Project Showcase & Story Hub

**BuildORA** is a full-featured blog and project-showcase web application designed for developers and creators to share the stories, challenges, and lessons learned behind what they build.

🌐 **Live Demo**: [http://buildora.great-site.net/](http://buildora.great-site.net/)  
💻 **Repository**: [https://github.com/uvii329/BuildORA-](https://github.com/uvii329/BuildORA-)

---

## ✨ Features

- **Story & Project Publishing (CRUD)**: Create, edit, preview, and delete project showcase stories with rich text, categories, and cover image uploads.
- **Categories System**: Categorize posts across Web Development, Software Development, Java, Spring Boot, Robotics, UI/UX Design, Team Projects, and more.
- **Interactive Likes System**: Real-time asynchronous (AJAX) heart liking and unliking with animated feedback and optimistic count updates.
- **Search & Category Filtering**: Instantly search stories by keyword and filter by specific categories.
- **Authentication & Security**: Secure user registration and login with bcrypt password hashing, prepared SQL statements, and strict author ownership protection.
- **Author Dashboard**: Dedicated creator dashboard displaying author metrics, total appreciations/likes, and post management tools.
- **Responsive & Dark/Light Themes**: Dynamic dark/light mode toggle with persistent local preferences and mobile/desktop responsive design.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.x
- **Database**: MySQL / MariaDB (InnoDB with foreign key cascades and constraints)
- **Frontend**: Semantic HTML5, Modern CSS3 (CSS Variables, Flexbox, CSS Grid), Vanilla JavaScript (ES6+)
- **Server Environment**: Apache with `.htaccess` rewrite rules and security hardening

---

## 🚀 Local Setup & Installation

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) (with PHP 8.x and MySQL / Apache)
- Git

### 2. Clone the Repository
```bash
git clone https://github.com/<YOUR_USERNAME>/buildora.git
```
Place the folder in your web root (e.g. `C:/xampp/htdocs/buildora-project`).

### 3. Database Setup
1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`).
3. Create a new database named `project_showcase`.
4. Import `database.sql` into the `project_showcase` database.

### 4. Configuration
1. Copy `config/database.example.php` to `config/database.local.php`:
   ```bash
   cp config/database.example.php config/database.local.php
   ```
2. Update `config/database.local.php` with your local database credentials if they differ from the defaults (`localhost`, `root`, `""`).

### 5. Run the Application
Open your browser and navigate to:
```
http://localhost/buildora-project/
```

---

## 📂 Project Structure

```text
buildora-project/
├── .htaccess                 # Production Apache routing & security
├── .gitignore                # Git ignore rules (secrets & temp files)
├── README.md                 # Project documentation
├── index.php                 # Main Explore Stories feed & hero banner
├── project.php               # Single story reading view & like bar
├── create-project.php        # Create new story with category selector
├── edit-project.php          # Edit story with ownership guard
├── dashboard.php             # Creator dashboard & management
├── login.php                 # User authentication
├── register.php              # User registration
├── logout.php                # Session termination
├── toggle-like.php           # Asynchronous AJAX like/unlike endpoint
├── delete-project.php        # Post deletion with cascading cleanup
├── database.sql              # Database schema and seed data
├── config/
│   ├── database.php          # Smart database connection loader
│   ├── database.example.php  # Example configuration template
│   └── database.local.php    # Private credentials (git-ignored)
├── css/
│   └── style.css             # Light/Dark mode themes & UI styles
├── js/
│   └── theme.js              # Theme switcher & AJAX interactions
└── uploads/
    ├── .htaccess             # Security rule preventing script execution
    └── .gitkeep              # Directory preservation for Git
```

---

## 🔒 Security Measures
- **Password Hashing**: `password_hash()` using `PASSWORD_DEFAULT` (bcrypt).
- **SQL Injection Prevention**: All queries use parameterized prepared statements (`$conn->prepare()`).
- **XSS Mitigation**: Contextual sanitization via `htmlspecialchars()`.
- **Upload Security**: File type verification (JPG, PNG, WEBP), file size caps, unique filename generation (`uniqid()`), and `.htaccess` script execution prevention.

---

## 📄 License
This project is open-source and available under the [MIT License](LICENSE).
