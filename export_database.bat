@echo off
echo ProCook Database Export Tool
echo ===========================
echo.

echo Exporting database using mysqldump...
echo.

set /p dbname=Enter database name (default: procook): 
if "%dbname%"=="" set dbname=procook

echo.
echo Exporting %dbname% to backup file...

"C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe" -u root -p %dbname% > "%dbname%_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%.sql" 2>&1

if %errorlevel% == 0 (
    echo.
    echo ✅ Export successful! 
    echo File saved as: %dbname%_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%.sql
    echo.
    echo You can now use this file to import your data to the cloud database.
) else (
    echo.
    echo ❌ Export failed. Please check:
    echo 1. MySQL service is running in Laragon
    echo 2. Database name is correct
    echo 3. You have the correct password
)

echo.
pause