@echo off
title Anandha Shaji Portfolio - Public URL Generator
echo =========================================================================
echo       Anandha Shaji Portfolio ^& Library Management System
echo            LIVE PUBLIC URL GENERATOR FOR SPREADSHEET
echo =========================================================================
echo.

set PHP_EXE=php
if exist "C:\xampp\php\php.exe" (
    set PHP_EXE="C:\xampp\php\php.exe"
)

echo [1/2] Ensuring local PHP server is running on port 8000...
start /b "" %PHP_EXE% -S localhost:8000 >nul 2>&1

echo [2/2] Launching Cloudflare Tunnel for direct, instant access...
echo -------------------------------------------------------------------------
echo   INSTRUCTIONS FOR YOUR SPREADSHEET:
echo   1. Look for the line below that says:
echo      "https://xxxx.trycloudflare.com"
echo   2. Copy that link and paste it directly into your spreadsheet!
echo   3. This URL works directly with NO password prompt when clicked.
echo   4. Keep this black window open while people test your link.
echo -------------------------------------------------------------------------
echo.

if exist "%~dp0cloudflared.exe" (
    "%~dp0cloudflared.exe" tunnel --url http://localhost:8000
) else (
    npx localtunnel --port 8000
)

pause
