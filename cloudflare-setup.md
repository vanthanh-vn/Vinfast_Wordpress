# Ket noi VinFast local voi Cloudflare Tunnel

Domain dung cho project: `vinfasttpc.io.vn`

URL public sau khi chay tunnel:

```text
https://vinfasttpc.io.vn/vinfast/
```

## 1. Kiem tra Cloudflare

Trong Cloudflare, domain `vinfasttpc.io.vn` can o trang thai da active va proxy qua Cloudflare.

Vao `SSL/TLS > Overview` va de che do `Flexible` neu Laragon dang chay HTTP local. Sau do vao `SSL/TLS > Edge Certificates` va dam bao `Universal SSL` dang bat.

## Cach tu dong bang cloudflared login

Neu dang dang nhap Cloudflare tren trinh duyet, chay file:

```text
D:\laragon\www\Vinfast\setup-cloudflare-tunnel.bat
```

File nay se:

- mo trinh duyet de xac thuc Cloudflare neu may chua co `cert.pem`
- tao tunnel `vinfast-local`
- tao DNS route cho `vinfasttpc.io.vn` va `www.vinfasttpc.io.vn`
- tao config local trong `%USERPROFILE%\.cloudflared\vinfast-local.yml`
- chay tunnel toi Laragon `http://localhost:80`

Giu cua so tunnel dang mo de website public hoat dong.

## Cach thu cong bang Zero Trust token

### 1. Tao tunnel

Vao Cloudflare Zero Trust:

```text
Networks > Tunnels > Create tunnel
```

Dat ten tunnel, vi du:

```text
vinfast-local
```

Chon connector Windows va copy token sau phan `--token`.

### 2. Gan public hostname

Trong public hostname cua tunnel, dat:

```text
Hostname: vinfasttpc.io.vn
Path: bo trong
Service: http://localhost:80
```

De trong `Path` de toan bo domain di ve Laragon. Website van nam o duong dan `/vinfast/`.

### 3. Luu token local

Tao file nay trong project:

```text
D:\laragon\www\Vinfast\cloudflare-tunnel-token.txt
```

Dan duy nhat token Cloudflare vao file do. File token nay da duoc dua vao `.gitignore`, khong day len Git.

### 4. Chay tunnel

Mo file:

```text
D:\laragon\www\Vinfast\start-cloudflare-tunnel.bat
```

Khi tunnel dang chay, mo:

```text
https://vinfasttpc.io.vn/vinfast/
```
