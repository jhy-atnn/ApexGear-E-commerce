@echo off
setlocal

set "MYSQLDUMP=%USERPROFILE%\Pictures\xampp\mysql\bin\mysqldump.exe"
set "OUTPUT=%~dp0db_apexgear.sql"

if not exist "%MYSQLDUMP%" (
    echo mysqldump.exe was not found at:
    echo %MYSQLDUMP%
    exit /b 1
)

"%MYSQLDUMP%" -uroot --default-character-set=utf8mb4 --databases db_apexgear --add-drop-database --add-drop-table --routines --events --result-file="%OUTPUT%"

if errorlevel 1 (
    echo Database export failed.
    exit /b 1
)

echo Exported current db_apexgear database to:
echo %OUTPUT%
