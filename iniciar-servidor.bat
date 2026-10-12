@echo off 
cd /d "%%~dp0" 
echo. 
echo ================================================= 
echo   SERVIDOR PHP: http://localhost:8001 
echo   Para detener: Ctrl + C 
echo ================================================= 
echo. 
php -S localhost:8001 
pause 
