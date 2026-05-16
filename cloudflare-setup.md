# Ket noi VinFast local voi Cloudflare Tunnel

Domain dung cho project: `vinfasttpc.io.vn`

URL public sau khi chay tunnel:

```text
https://vinfasttpc.io.vn/vinfast/
```

## 1. Kiem tra Cloudflare

Trong Cloudflare, domain `vinfasttpc.io.vn` can o trang thai da active va proxy qua Cloudflare.

Vao `SSL/TLS > Overview` va de che do `Flexible` neu Laragon dang chay HTTP local. Sau do vao `SSL/TLS > Edge Certificates` va dam bao `Universal SSL` dang bat.

## 2. Tao tunnel

Vao Cloudflare Zero Trust:

```text
Networks > Tunnels > Create tunnel
```

Dat ten tunnel, vi du:

```text
vinfast-local
```

Chon connector Windows va copy token sau phan `--token`.

## 3. Gan public hostname

Trong public hostname cua tunnel, dat:

```text
Hostname: vinfasttpc.io.vn
Path: /vinfast/*
Service: http://localhost:80
```

Neu domain `vinfasttpc.io.vn` chi dung rieng cho website nay, co the bo trong `Path`; khi do moi duong dan tren domain se duoc dua ve Laragon.

## 4. Luu token local

Tao file nay trong project:

```text
D:\laragon\www\Vinfast\cloudflare-tunnel-token.txt
```

Dan duy nhat token Cloudflare vao file do. File token nay da duoc dua vao `.gitignore`, khong day len Git.

## 5. Chay tunnel

Mo file:

```text
D:\laragon\www\Vinfast\start-cloudflare-tunnel.bat
```

Khi tunnel dang chay, mo:

```text
https://vinfasttpc.io.vn/vinfast/
```
