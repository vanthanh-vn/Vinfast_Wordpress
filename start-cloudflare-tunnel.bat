@echo off
setlocal

cd /d "%~dp0"

set "CLOUDFLARED=D:\laragon\bin\cloudflared.exe"
set "TOKEN_FILE=%~dp0cloudflare-tunnel-token.txt"
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
  echo Thieu file token: %TOKEN_FILE%
  echo.
  echo Cach lay token:
  echo 1. Vao Cloudflare Zero Trust ^> Networks ^> Tunnels.
  echo 2. Create tunnel, dat ten: vinfast-local.
  echo 3. Chon Windows connector, copy phan token sau chu --token.
  echo 4. Public hostname nen dat:
  echo    Hostname: vinfasttpc.io.vn
  echo    Path: /vinfast/*  ^(co the bo trong neu domain chi dung cho web nay^)
  echo    Service: http://localhost:80
  echo 5. Tao file cloudflare-tunnel-token.txt trong thu muc project,
  echo    dan duy nhat token vao file do, roi chay lai file bat nay.
  echo.
  pause
  exit /b 1
)

set /p TUNNEL_TOKEN=<"%TOKEN_FILE%"

"%CLOUDFLARED%" tunnel --no-autoupdate run --token "%TUNNEL_TOKEN%"
