$ErrorActionPreference = 'Stop'

$Cloudflared = 'D:\laragon\bin\cloudflared.exe'
$TunnelName = 'vinfast-local'
$Hostname = 'vinfasttpc.io.vn'
$Service = 'http://localhost:80'
$CloudflaredDir = Join-Path $env:USERPROFILE '.cloudflared'
$CertPath = Join-Path $CloudflaredDir 'cert.pem'
$ConfigPath = Join-Path $CloudflaredDir "$TunnelName.yml"

function Assert-LastCommand {
    param([string] $Message)

    if ($LASTEXITCODE -ne 0) {
        throw $Message
    }
}

function Get-VinFastTunnel {
    $raw = & $Cloudflared tunnel list --output json 2>$null
    if ($LASTEXITCODE -ne 0) {
        return $null
    }

    $text = ($raw -join "`n").Trim()
    if ([string]::IsNullOrWhiteSpace($text)) {
        return $null
    }

    $items = $text | ConvertFrom-Json
    return $items | Where-Object { $_.name -eq $TunnelName } | Select-Object -First 1
}

Write-Host ''
Write-Host '[VinFast Cloudflare Tunnel Setup]'
Write-Host "Tunnel:  $TunnelName"
Write-Host "Domain:  $Hostname"
Write-Host "Service: $Service"
Write-Host ''

if (-not (Test-Path $Cloudflared)) {
    throw "Khong tim thay cloudflared tai: $Cloudflared"
}

& $Cloudflared --version
Assert-LastCommand 'Khong chay duoc cloudflared.'

if (-not (Test-Path $CloudflaredDir)) {
    New-Item -ItemType Directory -Path $CloudflaredDir | Out-Null
}

if (-not (Test-Path $CertPath)) {
    Write-Host ''
    Write-Host 'Dang mo trinh duyet de dang nhap Cloudflare.'
    Write-Host 'Hay chon domain vinfasttpc.io.vn trong Cloudflare, sau do quay lai cua so nay.'
    Write-Host ''
    & $Cloudflared tunnel login
    Assert-LastCommand 'Cloudflare login chua hoan tat.'
}

if (-not (Test-Path $CertPath)) {
    throw "Chua tao duoc cert Cloudflare: $CertPath"
}

$tunnel = Get-VinFastTunnel

if ($null -eq $tunnel) {
    Write-Host ''
    Write-Host "Dang tao tunnel: $TunnelName"
    & $Cloudflared tunnel create $TunnelName
    Assert-LastCommand "Khong tao duoc tunnel $TunnelName."
    $tunnel = Get-VinFastTunnel
}

if ($null -eq $tunnel) {
    throw "Khong tim thay tunnel $TunnelName sau khi tao."
}

$TunnelId = $tunnel.id
$CredentialsPath = Join-Path $CloudflaredDir "$TunnelId.json"

if (-not (Test-Path $CredentialsPath)) {
    throw "Khong tim thay credentials file: $CredentialsPath"
}

$CredentialsForYaml = $CredentialsPath.Replace('\', '/')
$config = @(
    "tunnel: $TunnelId",
    "credentials-file: $CredentialsForYaml",
    '',
    'ingress:',
    "  - hostname: $Hostname",
    "    service: $Service",
    "  - hostname: www.$Hostname",
    "    service: $Service",
    '  - service: http_status:404'
)

Set-Content -Path $ConfigPath -Value $config -Encoding ASCII

Write-Host ''
Write-Host "Da tao config: $ConfigPath"
Write-Host ''
Write-Host "Dang route DNS cho $Hostname..."
& $Cloudflared tunnel route dns $TunnelName $Hostname
if ($LASTEXITCODE -ne 0) {
    Write-Host "DNS route cho $Hostname co the da ton tai. Tiep tuc."
}

Write-Host ''
Write-Host "Dang route DNS cho www.$Hostname..."
& $Cloudflared tunnel route dns $TunnelName "www.$Hostname"
if ($LASTEXITCODE -ne 0) {
    Write-Host "DNS route cho www.$Hostname co the da ton tai. Tiep tuc."
}

Write-Host ''
Write-Host 'Tunnel da cau hinh xong.'
Write-Host 'Giu cua so nay dang chay de website public hoat dong:'
Write-Host "https://$Hostname/vinfast/"
Write-Host ''

& $Cloudflared tunnel --config $ConfigPath run $TunnelName
