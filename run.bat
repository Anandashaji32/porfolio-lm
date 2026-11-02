@echo off
title Anandha Shaji - Developer Portfolio ^& Library System
echo ==============================================================
echo       Anandha Shaji - Developer Portfolio Website
echo       Featuring: Athenaeum Library Management System
echo ==============================================================
echo.

set PHP_EXE=php
if exist "C:\xampp\php\php.exe" (
    set PHP_EXE="C:\xampp\php\php.exe"
)

echo [1] Starting local server at http://localhost:8000 ...
echo.
echo     - Portfolio Website: http://localhost:8000/
echo     - Portfolio Login:   http://localhost:8000/portfolio_login.php
echo     - Library System:    http://localhost:8000/library/
echo     - Library Login:     http://localhost:8000/library/login.php
echo.
echo [2] Opening your browser to your Portfolio Website...
echo.
echo Press Ctrl+C in this window when you want to stop the server.
echo ==============================================================
echo.

start "" "http://localhost:8000/"
%PHP_EXE% -S localhost:8000
pause
