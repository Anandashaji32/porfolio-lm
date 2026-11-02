# 📋 Spreadsheet Submission & Public URL Guide

This guide explains how to get a **live public HTTPS URL** for your Portfolio & Library Management System to paste directly into your assignment, internship, or project evaluation spreadsheet.

---

## ⚡ Option 1: Instant Public URL (Recommended - 1 Click)

If you need a working live URL immediately without signing up for hosting:

### Steps:
1. Double-click the file named **`share_url.bat`** in your project folder:
   ```text
   C:\Users\ACER\OneDrive\Desktop\anandhashaji\share_url.bat
   ```
2. The black terminal window will start the server and display:
   ```text
   Tunnel Password: xxx.xxx.xxx.xxx
   your url is: https://great-adults-wish.loca.lt
   ```
3. **Copy that URL** (e.g., `https://xxxx.loca.lt`) and paste it into your spreadsheet column (e.g., "Project URL" or "Portfolio Link").
4. **Important**: Keep the terminal window open while your teacher, recruiter, or evaluator is checking your submission.

> [!NOTE]
> When the evaluator opens a `localtunnel` URL for the first time, it may ask for a **"Tunnel Password"**. The terminal window displays this password (it's your public IP). They just enter that number or click "Click to Continue" to view your full portfolio and working library system!

---

## 🌐 Option 2: 24/7 Permanent Free Hosting (Always Online)

If you want a permanent link that stays online forever even when your computer is shut down:

### Method A: InfinityFree (Free PHP + MySQL Hosting)
1. Go to [InfinityFree](https://www.infinityfree.com/) and register a free account.
2. Create a new account with a free subdomain (e.g. `anandhashaji.infinityfreeapp.com`).
3. Open the **Online File Manager** (`htdocs` folder).
4. Upload all the files from this folder (`index.php`, `portfolio_login.php`, `library/`, etc.).
5. In phpMyAdmin on InfinityFree, import `library/database.sql` and update `library/config/db.php` with the MySQL credentials provided in the InfinityFree control panel.
6. Copy your URL: `https://anandhashaji.infinityfreeapp.com` and paste it into your spreadsheet.

### Method B: Render.com (Free Web Service)
1. Push this folder to a GitHub repository:
   ```powershell
   git init
   git add .
   git commit -m "Anandha Shaji Portfolio and Library System"
   git remote add origin https://github.com/YOUR_USERNAME/anandhashaji.git
   git push -u origin main
   ```
2. Go to [Render](https://render.com/) -> Create **New Web Service** -> Connect your GitHub repo.
3. Select **Docker** or **PHP Native Environment**.
4. You will get a permanent public URL like `https://anandhashaji.onrender.com`!

---

## 🔑 Demo Credentials to Include in Your Spreadsheet

You can copy and paste this table into your spreadsheet notes or submission remarks:

| System | Role | URL / Route | Email | Password |
| :--- | :--- | :--- | :--- | :--- |
| **Portfolio Website** | Public Visitor | `/` (Home page) | *No login needed* | *Browse projects & contact* |
| **Portfolio Portal** | Owner / Admin | `/portfolio_login.php` | `anandha@portfolio.com` | `admin123` |
| **Library System** | Public Catalog | `/library/index.php` | *No login needed* | *Browse book catalog* |
| **Library System** | Librarian (Admin) | `/library/login.php` | `admin@library.com` | `admin123` |
| **Library System** | Student Member | `/library/login.php` | `student@library.com` | `student123` |

*(All login pages feature 1-Click Autofill buttons so evaluators can test with a single click!)*

---

## 🧪 Local Testing

To test everything on your own computer first:
1. Double-click **`run.bat`**.
2. Your browser will open directly to `http://localhost:8000/`.
3. You can click into:
   - **Portfolio Login** (`http://localhost:8000/portfolio_login.php`)
   - **Featured Project: Library System** (`http://localhost:8000/library/index.php`)
   - **Library Login** (`http://localhost:8000/library/login.php`)
