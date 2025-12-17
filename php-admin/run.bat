@echo off
echo Starting PHP Admin Panel on http://127.0.0.1:8000
echo.
echo Press Ctrl+C to stop the server
echo.
php -S 127.0.0.1:8000 -t . router.php

