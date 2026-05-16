@echo off
setlocal

cd /d "%~dp0"

set "CLOUDFLARED=D:\laragon\bin\cloudflared.exe"
set "TOKEN_FILE=%~dp0cloudflare-tunnel-token.txt"
set "CONFIG_FILE=%USERPROFILE%\.cloudflared\vinfast-local.yml"
set "TUNNEL_NAME=vinfast-local"
set "PUBLIC_URL=https://vinfasttpc.io.vn/vinfast/"

echo.
echo [VinFast Cloudflare Tunnel]
echo Local service: http://localhost:80
echo Public URL:    %PUBLIC_URL%
echo.

if not exist "%CLOUDFLARED%" (
  echo Khong tim thay cloudflared tai: %CLOUDFLARED%
  echo Hay tai cloudflared.exe vao D:\laragon\bin roi chay lai.
  pause
  exit /b 1
)

if not exist "%TOKEN_FILE%" (
  if exist "%CONFIG_FILE%" (
    echo Khong co token local, dang chay tunnel bang named tunnel config:
    echo %CONFIG_FILE%
    echo.
    "%CLOUDFLARED%" tunnel --config "%CONFIG_FILE%" run "%TUNNEL_NAME%"
    exit /b %ERRORLEVEL%
  )

  echo Thieu file token hoac named tunnel config.
  echo.
  echo Cach nhanh nhat:
  echo Chay file setup-cloudflare-tunnel.bat de dang nhap Cloudflare,
  echo tao tunnel, route DNS va chay tunnel tu dong.
  echo.
  echo Cach dung token Zero Trust:
  echo 1. Vao Cloudflare Zero Trust ^> Networks ^> Tunnels.
  echo 2. Create tunnel, dat ten: vinfast-local.
  echo 3. Chon Windows connector, copy phan token sau chu --token.
  echo 4. Public hostname nen dat:
  echo    Hostname: vinfasttpc.io.vn
  echo    Path: bo trong
  echo    Service: http://localhost:80
  echo 5. Tao file cloudflare-tunnel-token.txt trong thu muc project,
  echo    dan duy nhat token vao file do, roi chay lai file bat nay.
  echo.
  pause
  exit /b 1
)

set /p TUNNEL_TOKEN=<"%TOKEN_FILE%"

"%CLOUDFLARED%" tunnel --no-autoupdate run --token "%TUNNEL_TOKEN%"
