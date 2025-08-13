# Tutorial Testing API Pengaduan dengan Postman

## Persiapan

1. Download dan install [Postman](https://www.postman.com/downloads/)
2. Buka Postman dan buat collection baru bernama "API Pengaduan"

## Testing Endpoint Login

### 1. Setup Request Login

1. Klik tombol "New" dan pilih "HTTP Request"
2. Atur metode menjadi "POST"
3. Masukkan URL: `http://localhost/serverpengaduan/api/login`

### 2. Setup Headers

1. Klik tab "Headers"
2. Tambahkan header berikut:
   - Key: `Content-Type`, Value: `application/json`
   - Key: `Accept`, Value: `application/json`

### 3. Setup Body

1. Klik tab "Body"
2. Pilih opsi "raw" dan format "JSON"
3. Masukkan data login berikut:

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

### 4. Menjalankan Request

1. Klik tombol "Send"
2. Perhatikan hasil response di panel bawah
3. Jika berhasil, Anda akan melihat response JSON dengan status 200 dan data token

Contoh response sukses:

```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "User Name",
      "email": "user@example.com",
      "phone": "123456789",
      "role": "user"
    },
    "token": "64character_random_string_here"
  }
}
```

## Troubleshooting

### Penting: Penggunaan URL yang Benar

**Perhatikan**: URL API yang benar adalah `http://localhost/serverpengaduan/api/[endpoint]` (tanpa `/public/`). Pastikan Anda menggunakan URL yang tepat untuk semua API calls.

### Jika Response Berupa HTML, Bukan JSON

1. **Periksa URL**: Pastikan URL sudah benar dan mengarah ke endpoint API
2. **Periksa Headers**: Pastikan `Content-Type` dan `Accept` diatur ke `application/json`
3. **Periksa Apache & PHP Logs**: Di XAMPP, buka folder `C:\xampp\logs` untuk melihat error log
4. **Coba Restart Apache**: Kadang-kadang restarting server Apache dapat menyelesaikan masalah cache

### Jika Mendapat Error CORS

1. **Pastikan XAMPP Berjalan**: Restart Apache melalui XAMPP Control Panel
2. **Coba Endpoint Options**: Kirim request OPTIONS ke endpoint yang sama untuk memeriksa CORS headers

#### Testing dengan OPTIONS Request

1. Buat request baru dengan metode "OPTIONS"
2. URL: `http://localhost/serverpengaduan/api/login` (atau endpoint lainnya)
3. Headers:
   - `Origin`: `http://localhost:57236` (atau origin aplikasi Flutter Anda)
4. Klik tombol "Send"
5. Cek response headers - pastikan `Access-Control-Allow-Origin` ada dan diatur dengan benar

## Testing Endpoint Lainnya

### Register User Baru

1. Buat request baru dengan metode "POST"
2. URL: `http://localhost/serverpengaduan/api/register`
3. Body (JSON):

```json
{
  "name": "New User",
  "email": "newuser@example.com",
  "phone": "987654321",
  "password": "password"
}
```

### Mengambil Daftar Pengaduan

1. Buat request baru dengan metode "GET"
2. URL: `http://localhost/serverpengaduan/api/pengaduan`
3. Headers:
   - `Authorization`: `Bearer your_token_here` (Ganti dengan token dari login)

## Tips Memastikan API Bekerja Dengan Benar

1. **Gunakan Postman Collection Runner**: Buat urutan request untuk pengujian otomatis
2. **Tambahkan Test Scripts**: Di tab "Tests" pada Postman untuk memvalidasi response

### Endpoint Debug untuk Pengujian

Untuk memastikan API dan database berfungsi dengan baik, Anda dapat menggunakan endpoint debug:

1. Buat request baru dengan metode "GET"
2. URL: `http://localhost/serverpengaduan/api/debug/token`
3. Klik tombol "Send"

Endpoint ini akan menampilkan informasi tentang konfigurasi database dan struktur tabel `users` yang berguna untuk memverifikasi bahwa kolom `api_token` sudah ada di database.

Jika masih mengalami masalah dengan CORS atau format response, periksa kembali konfigurasi .htaccess dan pastikan:

1. Apache `mod_headers` dan `mod_rewrite` diaktifkan di XAMPP
2. Header CORS telah ditetapkan dengan benar
3. Format response JSON dikembalikan dengan header Content-Type yang tepat
