# Vocaject Backend API

Vocaject adalah suatu aplikasi yang dapat membantu meningkatkan kesiapan kerja bagi mahasiswa vokasi di Indonesia. Aplikasi ini juga dapat membantu Perusahaan, Pelaku Usaha atau Lembaga untuk mencari pekerja lepas sementara.

## Postman Workspace

Vocaject Backend API merupakan Backend yang dibentuk untuk keperluan proyek Vocaject menggunakan Framework Laravel. Pengujian Backend ini dapat dilakukan menggunakan Postman, untuk melakukan pengujian dapat menekan tombol berikut:

[![Run in Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/9722425-b5e5609e-95a9-472b-899c-eac3f2b6f372?action=collection%2Ffork&collection-url=entityId%3D9722425-b5e5609e-95a9-472b-899c-eac3f2b6f372%26entityType%3Dcollection%26workspaceId%3D967752d3-bd7b-4af3-8bec-cddf45d5aaea)

## Note Deployment

Perintah untuk jalankan runtime
```
nohup php artisan serve --host=0.0.0.0 --port=80 > laravel.log 2>&1 &
```

Perintah untuk matikan runtime:
- Cari dulu pid-nya dengan perintah berikut:
  ```
  ps aux | grep "php artisan serve"
  ```
- Kemudian matikan dengan perintah sebagai contoh seperti berikut:
  ```
  kill 12345
  ```