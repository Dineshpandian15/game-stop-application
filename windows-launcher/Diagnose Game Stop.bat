@echo off
setlocal EnableExtensions

set "ROOT=%~dp0.."
set "PHP=%ROOT%\runtime\php.exe"
set "PORT=8765"
set "URL=http://127.0.0.1:%PORT%/login"

cd /d "%ROOT%"

echo ============================================
echo   Game Stop - Diagnostic Tool
echo ============================================
echo.
echo App folder: %CD%
echo.

if not exist "%PHP%" (
    echo [FAIL] runtime\php.exe is missing
    goto end
) else (
    echo [OK]   runtime\php.exe found
)

powershell -NoProfile -Command "Unblock-File -LiteralPath '%PHP%' -ErrorAction SilentlyContinue" 2>nul

echo.
echo --- PHP version ---
"%PHP%" -v
if errorlevel 1 (
    echo.
    echo [FAIL] PHP cannot run. Right-click runtime\php.exe - Properties - Unblock
    goto end
)

"%PHP%" -r "if (version_compare(PHP_VERSION, '8.4.1', '<')) { fwrite(STDERR, '[FAIL] PHP 8.4.1+ required. You have '.PHP_VERSION.'. Download GameStop-Windows-Shop-1.0.2.zip or newer.'.PHP_EOL); exit(1); } echo '[OK]   PHP version meets Laravel 13 requirement'.PHP_EOL;"
if errorlevel 1 goto end

echo.
echo --- Laravel status ---
if exist ".env" (
    echo [OK]   .env exists
) else (
    echo [WARN] .env missing - run Game Stop.bat first
)

"%PHP%" artisan about 2>nul
if errorlevel 1 echo [WARN] artisan about failed

echo.
echo --- Port %PORT% ---
netstat -an | find ":%PORT%" | find "LISTENING" >nul
if errorlevel 1 (
    echo [FAIL] Nothing listening on port %PORT%
    echo        Run "Game Stop.bat" to start the server.
) else (
    echo [OK]   Server is listening on port %PORT%
)

echo.
echo --- HTTP test ---
powershell -NoProfile -Command "try { $r = Invoke-WebRequest -Uri '%URL%' -UseBasicParsing -TimeoutSec 5; Write-Host '[OK]   HTTP' $r.StatusCode 'from' '%URL%' } catch { Write-Host '[FAIL]' $_.Exception.Message }"

echo.
echo --- Recent log lines ---
if exist "storage\logs\launcher.log" (
    echo launcher.log:
    powershell -NoProfile -Command "Get-Content 'storage\logs\launcher.log' -Tail 15"
) else (
    echo No launcher.log yet.
)

if exist "storage\logs\laravel.log" (
    echo.
    echo laravel.log:
    powershell -NoProfile -Command "Get-Content 'storage\logs\laravel.log' -Tail 15"
)

:end
echo.
echo ============================================
echo If HTTP test fails, try opening in browser:
echo   %URL%
echo.
echo Login: admin@gamestop.local / password
echo ============================================
echo.
pause
