@echo off
setlocal EnableExtensions EnableDelayedExpansion

rem Game Stop — Windows shop launcher
set "ROOT=%~dp0.."
set "PHP=%ROOT%\runtime\php.exe"
set "PORT=8765"
set "URL=http://127.0.0.1:%PORT%/login"
set "LOG=%ROOT%\storage\logs\launcher.log"

cd /d "%ROOT%"
if errorlevel 1 (
    echo ERROR: Cannot open app folder. Move Game Stop to C:\GameStop and try again.
    pause
    exit /b 1
)

echo [%date% %time%] Launcher started>> "%LOG%"

for %%D in (storage\logs storage\framework\sessions storage\framework\views storage\framework\cache\data bootstrap\cache database) do (
    if not exist "%%D" mkdir "%%D" 2>nul
)

powershell -NoProfile -Command "if (Test-Path '%PHP%') { Unblock-File -LiteralPath '%PHP%' -ErrorAction SilentlyContinue }" 2>nul

if not exist "%PHP%" (
    echo.
    echo  ERROR: PHP not found at runtime\php.exe
    echo  Re-extract the full zip package.
    echo.
    pause
    exit /b 1
)

"%PHP%" -v >nul 2>&1
if errorlevel 1 (
    echo.
    echo  ERROR: Windows blocked php.exe ^("Access is denied"^).
    echo.
    echo  FIX:
    echo    1. Right-click runtime\php.exe - Properties
    echo    2. At the bottom, tick "Unblock" - OK
    echo    3. Run this file again
    echo.
    echo  Also extract the app to C:\GameStop ^(not OneDrive/Desktop^).
    echo.
    pause
    exit /b 1
)

if not exist ".env" (
    if exist ".env.windows" (
        copy /Y ".env.windows" ".env" >nul
    ) else (
        copy /Y ".env.example" ".env" >nul
    )
    "%PHP%" artisan key:generate --force >> "%LOG%" 2>&1
)

"%PHP%" artisan config:clear >> "%LOG%" 2>&1
"%PHP%" artisan route:clear >> "%LOG%" 2>&1
"%PHP%" artisan view:clear >> "%LOG%" 2>&1

if not exist "database\game_stop.sqlite" (
    echo First run - creating database...
    type nul > "database\game_stop.sqlite"
    "%PHP%" artisan migrate --force >> "%LOG%" 2>&1
    if errorlevel 1 (
        echo ERROR: Database setup failed. See storage\logs\launcher.log
        pause
        exit /b 1
    )
    "%PHP%" artisan db:seed --force >> "%LOG%" 2>&1
)

netstat -an | find ":%PORT%" | find "LISTENING" >nul
if errorlevel 1 (
    echo Starting Game Stop server...
    start "Game Stop Server" /MIN cmd /c ""%PHP%" artisan serve --host=127.0.0.1 --port=%PORT% >> "%LOG%" 2>&1"

    set /a WAIT=0
    :waitloop
    if !WAIT! geq 45 (
        echo.
        echo  ERROR: Server did not start in time.
        echo  Open storage\logs\launcher.log for details.
        echo.
        pause
        exit /b 1
    )
    powershell -NoProfile -Command "try { (Invoke-WebRequest -Uri '%URL%' -UseBasicParsing -TimeoutSec 2).StatusCode | Out-Null; exit 0 } catch { exit 1 }" >nul 2>&1
    if not errorlevel 1 goto serverready
    timeout /t 1 /nobreak >nul
    set /a WAIT+=1
    goto waitloop
    :serverready
    echo Server ready.
) else (
    echo Server already running on port %PORT%.
)

echo Opening Game Stop login page...
start "" "%URL%"
timeout /t 3 >nul
exit /b 0
