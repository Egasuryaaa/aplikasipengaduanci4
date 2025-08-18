# 🔧 Testing Guide untuk Flutter Create Pengaduan

## ✅ Backend Status

- **API Backend**: ✅ BERHASIL
- **Authentication**: ✅ Working dengan `john.doe@gmail.com` / `user123`
- **Create Pengaduan**: ✅ Response 201 berhasil

## 📱 Testing Flutter App

### 1. **Login ke Aplikasi Flutter**

- Buka: http://localhost:3000
- Kredensial login:
  - **Email**: `john.doe@gmail.com`
  - **Password**: `user123`

### 2. **Perhatikan Console Browser**

- Tekan F12 → Console tab
- Seharusnya melihat log seperti:
  ```
  [AuthProvider] Login successful - Token received: eyJ0eXAi...
  [ApiService] setToken() saved token and set Authorization header
  ```

### 3. **Test Create Pengaduan**

- Klik tombol **"+ Buat Pengaduan"**
- Isi form:
  - **Kategori**: Pilih salah satu (contoh: "Keamanan Informasi")
  - **Deskripsi**: Minimal 10 karakter (contoh: "Test pengaduan dari Flutter app")
- Klik **"Buat Pengaduan"**

### 4. **Debug Console Logs**

Cari log ini di browser console:

**✅ Success Patterns:**

```
[ApiService] createPengaduan() called with data: {deskripsi: ..., kategori_id: ...}
[ApiService] createPengaduan() - Token initialization complete
[ApiService] createPengaduan() - Current headers: {Authorization: Bearer eyJ0eXAi...}
[ApiService] POST http://localhost/serverpengaduan/api/pengaduan
[ApiService] Response: 201
```

**❌ Error Patterns:**

```
[ApiService] Unauthorized - Token may be invalid or expired
[ApiService] WARNING: No token found in storage - user may need to login
[ApiService] Error: ...
```

## 🚨 Jika Masih Error

### 1. **Clear Browser Storage**

```javascript
// Run di browser console:
localStorage.clear();
sessionStorage.clear();
location.reload();
```

### 2. **Logout & Login Ulang**

- Logout dari aplikasi
- Login kembali dengan kredensial di atas
- Test create pengaduan lagi

### 3. **Check XAMPP**

- Pastikan Apache & MySQL/PostgreSQL running
- Test backend: `php c:\xampp\htdocs\serverpengaduan\public\test_create_pengaduan.php`

## 📋 Expected Results

- **Login**: Berhasil masuk ke dashboard
- **Create**: Form terkirim tanpa error 401
- **Response**: Pengaduan berhasil dibuat dan muncul di list

## 🔍 What's Fixed

1. ✅ **Singleton ApiService**: Token shared antar provider
2. ✅ **Authentication Flow**: Proper session management
3. ✅ **Backend API**: Create pengaduan working (201 response)
4. ✅ **CORS Policy**: Headers configured correctly
5. ✅ **Token Management**: Persistent storage & validation
