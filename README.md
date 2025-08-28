# Dokumentasi Sistem Pengaduan Kominfo Gunung Kidul

## Deskripsi Project

Sistem Pengaduan Kominfo Gunung Kidul adalah aplikasi web berbasis CodeIgniter 4 yang memungkinkan masyarakat untuk mengajukan pengaduan terkait layanan teknologi informasi dan komunikasi. Sistem ini dilengkapi dengan panel admin untuk mengelola pengaduan dan aplikasi mobile Flutter untuk akses yang lebih mudah.

## Teknologi Stack

- **Backend**: CodeIgniter 4 (PHP 8.1+)
- **Database**: PostgreSQL
- **Frontend Web**: Bootstrap 5, jQuery, DataTables, Chart.js
- **Mobile App**: Flutter (Dart)
- **Authentication**: JWT untuk API, Session untuk Web Admin

---

## 1. ROLE MANAGEMENT

### Role yang Tersedia

```
1. Master   - Super Admin (akses penuh)
2. Admin    - Administrator (akses terbatas)
3. User     - Pengguna biasa (hanya via mobile app)
```

### Implementasi Role

- **Location**: `app/Filters/AuthFilter.php`
- **Hierarchy**: `master > admin > user`
- **Middleware**: Filter `auth` dengan parameter role

### Hak Akses per Role

| Fitur                   | Master | Admin | User |
| ----------------------- | ------ | ----- | ---- |
| Dashboard               | ✅     | ✅    | ❌   |
| Kelola Pengaduan        | ✅     | ✅    | ❌   |
| Kelola User             | ✅     | ❌    | ❌   |
| Kelola Instansi         | ✅     | ✅    | ❌   |
| Kelola Kategori         | ✅     | ✅    | ❌   |
| Buat Pengaduan (Mobile) | ❌     | ❌    | ✅   |

---

## 2. CONTROLLERS

### Admin Controllers (Web)

```
app/Controllers/Admin/
├── AuthController.php      - Login/Logout admin
├── DashboardController.php - Dashboard & statistik
├── PengaduanController.php - CRUD pengaduan
├── UserController.php     - Manajemen user (Master only)
├── InstansiController.php - Manajemen instansi
└── KategoriController.php - Manajemen kategori
```

### API Controllers (Mobile)

```
app/Controllers/Api/
├── AuthController.php        - Register/Login mobile
├── PengaduanController.php   - CRUD pengaduan
├── UserController.php       - Profile user
├── KategoriController.php   - List kategori
└── PengaduanStatistic.php  - Statistik pengaduan
```

### Relasi Controller Utama

```
Login → Dashboard → Management Modules
  ↓         ↓              ↓
Auth    Statistics    Pengaduan/User/Instansi/Kategori
```

---

## 3. MODELS

| Model                    | Tabel              | Fungsi Utama                      |
| ------------------------ | ------------------ | --------------------------------- |
| `UserModel`              | users              | Autentikasi, manajemen user, role |
| `PengaduanModel`         | pengaduan          | CRUD pengaduan, statistik, relasi |
| `InstansiModel`          | instansi           | Master data instansi              |
| `KategoriPengaduanModel` | kategori_pengaduan | Master data kategori              |
| `KomentarPengaduanModel` | komentar_pengaduan | Komentar/tanggapan                |
| `StatusHistoryModel`     | status_history     | Log perubahan status              |

### Database Schema

```sql
-- Users Table
users (id, uuid, name, email, phone, password, role, instansi_id, is_active)

-- Pengaduan Table
pengaduan (id, uuid, nomor_pengaduan, user_id, instansi_id, kategori_id,
          deskripsi, foto_bukti, status, tanggal_selesai, keterangan_admin)

-- Supporting Tables
instansi (id, nama, alamat, telepon, email, is_active)
kategori_pengaduan (id, nama, deskripsi, is_active)
komentar_pengaduan (id, pengaduan_id, user_id, komentar, is_internal)
status_history (id, pengaduan_id, status_old, status_new, keterangan, updated_by)
```

---

## 4. VIEW STRUCTURE

### Admin Views

```
app/Views/admin/
├── layout/
│   └── main.php              - Layout utama dengan sidebar
├── auth/
│   └── login.php             - Halaman login admin
├── dashboard/
│   └── index.php             - Dashboard dengan chart & statistik
├── pengaduan/
│   ├── index.php             - List pengaduan dengan filter
│   ├── detail.php            - Detail pengaduan + komentar
│   └── edit.php              - Edit pengaduan
├── users/
│   ├── index.php             - List user (Master only)
│   ├── create.php            - Form tambah user
│   └── edit.php              - Form edit user
├── instansi/
│   ├── index.php             - List instansi
│   ├── create.php            - Form tambah instansi
│   └── edit.php              - Form edit instansi
└── kategori/
    ├── index.php             - List kategori
    ├── create.php            - Form tambah kategori
    └── edit.php              - Form edit kategori
```

### Render Flow

```
Controller → Load Data → View (extend layout/main.php) → Browser
```

---


### Route Configuration

```php
// app/Config/Routes.php

// Admin Routes (Session-based)
admin/
├── login (public)
├── dashboard (auth required)
├── pengaduan/* (auth required)
├── users/* (auth:master required)
├── instansi/* (auth required)
└── kategori/* (auth required)

// API Routes (Token-based)
api/
├── register, login (public)
├── user, pengaduan (apiauth required)
└── kategori (public)
```

### Filters & Middleware

```php
// app/Config/Filters.php
'auth'    => AuthFilter::class      - Session-based auth
'apiauth' => ApiAuthFilter::class   - JWT token auth
'cors'    => CorsFilter::class      - CORS headers
'csrf'    => CSRF::class            - CSRF protection
```

### Libraries & Helpers

- **JWT**: Firebase JWT untuk API authentication
- **Helper**: `pengaduan_helper.php` - UUID generation, response helpers
- **CORS**: Custom CORS filter untuk mobile API
- **File Upload**: Handling foto bukti pengaduan

---

## 6. ALUR APLIKASI

### Web Admin Flow

```
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│ Login Admin │ -> │  Dashboard   │ -> │ Management  │
│             │    │ (Statistics) │    │  Modules    │
└─────────────┘    └──────────────┘    └─────────────┘
       │                    │                   │
       v                    v                   v
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│ AuthFilter  │    │ ChartJS/     │    │ CRUD        │
│ Session     │    │ DataTables   │    │ Operations  │
└─────────────┘    └──────────────┘    └─────────────┘
```

### Mobile API Flow

```
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│Register/    │ -> │ JWT Token    │ -> │ API Calls   │
│Login Mobile │    │ Generation   │    │ (CRUD)      │
└─────────────┘    └──────────────┘    └─────────────┘
       │                    │                   │
       v                    v                   v
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│ ApiAuth     │    │ Bearer Token │    │ JSON        │
│ Filter      │    │ Header       │    │ Response    │
└─────────────┘    └──────────────┘    └─────────────┘
```

### Pengaduan Lifecycle

```
[User Submit] -> [Pending] -> [Admin Process] -> [Diproses] -> [Selesai/Ditolak]
                     │              │               │              │
                     v              v               v              v
             [Auto Number]   [Add Comments]  [Status Update]  [Final State]
```

### ASCII Flowchart - Complete System

```
                    ┌─────────────────┐
                    │   ENTRY POINT   │
                    └─────────────────┘
                             │
                  ┌──────────┴──────────┐
                  │                     │
           ┌─────────────┐      ┌─────────────┐
           │  WEB ADMIN  │      │ MOBILE APP  │
           └─────────────┘      └─────────────┘
                  │                     │
                  │                     │
        ┌─────────┴─────────┐          │
        │                   │          │
 ┌─────────────┐    ┌─────────────┐    │
 │   LOGIN     │    │   ROUTES    │    │
 │ (Session)   │    │   /admin/*  │    │
 └─────────────┘    └─────────────┘    │
        │                   │          │
        │                   │          │
 ┌─────────────┐    ┌─────────────┐    │
 │ AuthFilter  │    │ Controllers │    │
 │ (Role Check)│    │ Admin/*     │    │
 └─────────────┘    └─────────────┘    │
        │                   │          │
        │          ┌────────┴────────┐ │
        │          │                 │ │
        │   ┌─────────────┐ ┌─────────────┐
        │   │ Dashboard   │ │ Management  │
        │   │ (Charts)    │ │ (CRUD)      │
        │   └─────────────┘ └─────────────┘
        │          │                 │
        │          │                 │
        └──────────┼─────────────────┘
                   │
            ┌─────────────┐
            │   MODELS    │
            │ (Database)  │
            └─────────────┘
                   │
            ┌─────────────┐
            │ PostgreSQL  │
            │  Database   │
            └─────────────┘
                   │
                   │
         ┌─────────┴─────────┐
         │                   │
  ┌─────────────┐    ┌─────────────┐
  │  API CALLS  │    │   MOBILE    │
  │ (JWT Auth)  │    │    USER     │
  └─────────────┘    └─────────────┘
         │                   │
         │                   │
  ┌─────────────┐    ┌─────────────┐
  │ ApiAuth     │    │ Register/   │
  │ Filter      │    │ Login       │
  └─────────────┘    └─────────────┘
         │                   │
         │                   │
  ┌─────────────┐    ┌─────────────┐
  │ API         │    │ Pengaduan   │
  │ Controllers │    │ CRUD        │
  └─────────────┘    └─────────────┘
```

---

## 7. FITUR UTAMA

### Dashboard Admin

- **Statistik Real-time**: Total, pending, diproses, selesai, ditolak
- **Chart Visualisasi**: Doughnut chart status, line chart trend bulanan
- **Recent Data**: 10 pengaduan terbaru dengan aksi cepat
- **User Management**: Khusus role Master

### Pengaduan Management

- **Filter Advanced**: Status, kategori, instansi, tanggal, pencarian
- **Detail View**: Info lengkap + foto bukti + komentar + history
- **Status Update**: Workflow pending → diproses → selesai/ditolak
- **Comment System**: Internal/public comments dengan edit capability

### Mobile Integration

- **JWT Authentication**: Secure API access
- **CRUD Operations**: Create, read, update, delete pengaduan
- **File Upload**: Foto bukti dengan preview
- **Real-time Status**: Tracking status pengaduan

---

## 8. SECURITY FEATURES

- **CSRF Protection**: Semua form admin
- **JWT Expiration**: 24 jam untuk mobile
- **Role-based Access**: Hierarchy enforcement
- **SQL Injection Prevention**: Model-based queries
- **Password Hashing**: PHP password_hash()
- **CORS Configuration**: API mobile access

---

## 9. DEPLOYMENT NOTES

### Requirements

- PHP 8.1+
- PostgreSQL 12+
- Composer
- Node.js (untuk mobile development)

---

Dokumentasi ini memberikan overview lengkap tentang arsitektur, alur kerja, dan implementasi sistem pengaduan. Untuk pengembangan lebih lanjut, pastikan mengikuti pola yang sudah ada dan mempertahankan konsistensi role-based access control.
