# Anandha Shaji - Developer Portfolio & Athenaeum Library System

A full-stack Developer Portfolio website with an integrated **Portfolio Admin Portal** and a featured **Athenaeum Library Management System**, built with a native **PHP backend**, **PDO SQLite & MySQL Database Architecture**, and modern responsive interface styled with **Tailwind CSS** and **FontAwesome**.

---

## 🌟 Architecture & Structure

```
anandhashaji/
├── index.php                        # Main Developer Portfolio Website
├── portfolio_login.php              # Portfolio Login Portal (with 1-Click Demo Fill)
├── portfolio_admin.php              # Portfolio Admin Dashboard (Inquiries Inbox & Bio Editor)
├── portfolio_action.php             # Portfolio Backend Actions (Auth, Contact Form, Profile)
├── portfolio_logout.php             # Portfolio Logout Handler
├── portfolio_db.php                 # Portfolio SQLite Database Initialization & Helpers
├── data/
│   └── portfolio.db                 # Auto-generated Portfolio SQLite DB
├── library/                         # Athenaeum Library Management System (Linked Project)
│   ├── index.php                    # Public Library Catalog
│   ├── login.php                    # Library System Login (Librarian & Student)
│   ├── register.php                 # Student Registration Page
│   ├── dashboard.php                # Adaptive Dashboard (Librarian vs Student)
│   ├── books.php                    # Catalog Search & Admin Inventory Manager
│   ├── issues.php                   # Circulation Desk (Issue & Return Books)
│   ├── my_books.php                 # Member Active Loans & History
│   ├── categories.php               # Book Genres / Categories
│   ├── members.php                  # Library Members Directory
│   ├── profile.php                  # User Profile & Password
│   ├── database.sql                 # MySQL Schema & Seed Data
│   ├── config/                      # Database configuration
│   ├── includes/                    # Auth, header, sidebar, footer components
│   └── actions/                     # Book, circulation, category, and member actions
├── run.bat                          # Double-click to run locally (http://localhost:8000)
├── share_url.bat                    # Double-click to generate a live public URL for spreadsheets
├── SPREADSHEET_SUBMISSION_GUIDE.md  # Step-by-step instructions for spreadsheet URLs
└── README.md                        # Documentation
```

---

## 🚀 How to Run the Website

### 1. Run Locally (Testing on Your PC)
- Double-click **`run.bat`**.
- It opens your browser to: **`http://localhost:8000/`**.

### 2. Generate Public URL for a Spreadsheet
- Double-click **`share_url.bat`**.
- It generates a live public HTTPS URL (e.g. `https://xxxx.loca.lt`) that anyone can open from any phone or computer worldwide.
- Copy that URL and paste it into your Google Sheet or Excel spreadsheet!

---

## 🔑 Login Credentials

### 1. Portfolio Admin Portal (`/portfolio_login.php`)
- **Email**: `anandha@portfolio.com`
- **Password**: `admin123`
*(Features a 1-click button to auto-fill)*

### 2. Library Management System (`/library/login.php`)
- **Librarian (Admin)**: `admin@library.com` / `admin123`
- **Student Member**: `student@library.com` / `student123`
*(Features 1-click demo buttons to auto-fill)*
