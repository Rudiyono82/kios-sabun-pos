@echo off
title KIOS SABUN & PARFUM POS
echo ========================================================
echo   KIOS SABUN & PARFUM - WEB POINT OF SALES (CI4)
echo ========================================================
echo.
echo Server lokal sedang berjalan di:
echo http://localhost:8080/kios-sabun-parfum-pos
echo.
echo Membuka browser otomatis...
echo.
start http://localhost:8080/kios-sabun-parfum-pos
cd /d "C:\xampp\htdocs"
C:\xampp\php\php.exe -S 0.0.0.0:8080 router.php
pause
