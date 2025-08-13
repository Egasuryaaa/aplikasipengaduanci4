# Sistem Pengaduan Kominfo Gunung Kidul

## Overview

Sistem pengaduan online untuk Dinas Komunikasi dan Informatika Kabupaten Gunung Kidul dengan arsitektur terpisah:

- **Web Admin Dashboard**: CodeIgniter 4 + PostgreSQL untuk pengelolaan admin
- **Mobile App API**: RESTful API dengan JWT authentication untuk aplikasi mobile Flutter

## Technology Stack

### Backend

- **Framework**: CodeIgniter 4
- **Database**: PostgreSQL
- **Authentication**:
  - Session-based untuk web admin
  - JWT untuk mobile API
- **Security**: CSRF protection, CSP headers, CORS configuration
- **File Storage**: Local storage dengan kompresi gambar

### Frontend

- **Web Admin**: Server-side rendering dengan Bootstrap
- **Mobile App**: Flutter (repository terpisah)

## Features

### Web Admin Dashboard

- ✅ Login/logout dengan session management
- ✅ Dashboard dengan statistik dan chart
- ✅ Manajemen pengaduan (view, assign, update status)
- ✅ Manajemen user (admin/master only)
- ✅ Master data (instansi, kategori)
- ✅ Sistem komentar
- ✅ History status pengaduan
- ✅ Notifikasi real-time
- ✅ Export/report data

### Mobile API

- ✅ User registration/login dengan JWT
- ✅ Profile management
- ✅ Submit pengaduan dengan foto
- ✅ Tracking status pengaduan
- ✅ Notifikasi push
- ✅ File upload dengan compression
- ✅ Offline-first data structure

### Security Features

- ✅ CSRF protection untuk semua form
- ✅ Content Security Policy (CSP) headers
- ✅ CORS configuration untuk mobile
- ✅ JWT dengan refresh token
- ✅ Rate limiting
- ✅ Input sanitization & validation
- ✅ Secure file upload
- ✅ Session hijacking protection
- ✅ SQL injection prevention

## Installation

### Prerequisites

- PHP 8.1+
- PostgreSQL 12+
- Composer
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone Repository**

   ```bash
   git clone <repository-url>
   cd serverpengaduan
   ```

2. **Install Dependencies**

   ```bash
   composer install
   ```

3. **Environment Configuration**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` file dengan konfigurasi database dan aplikasi Anda.

4. **Database Setup**

   ```bash
   # Buat database PostgreSQL
   createdb pengaduan_kominfo

   # Jalankan migration
   php spark migrate

   # Jalankan seeder (optional)
   php spark db:seed UserSeeder
   php spark db:seed InstansiSeeder
   php spark db:seed KategoriSeeder
   ```

5. **Set Permissions**

   ```bash
   chmod -R 755 writable/
   chmod -R 755 public/uploads/
   ```

6. **Generate JWT Secret**
   ```bash
   # Generate secure JWT secret key (32+ characters)
   openssl rand -base64 32
   ```
   Update `JWT_SECRET_KEY` di file `.env`

## API Documentation

### Authentication Endpoints

#### POST /api/auth/register

Registrasi user baru

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "081234567890",
  "password": "password123",
  "password_confirm": "password123",
  "instansi_id": 1
}
```

#### POST /api/auth/login

Login user

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Pengaduan Endpoints

#### GET /api/pengaduan

Get list pengaduan user
**Headers**: `Authorization: Bearer <token>`
**Query params**: `page`, `limit`, `status`, `search`

#### POST /api/pengaduan

Submit pengaduan baru
**Headers**: `Authorization: Bearer <token>`

```json
{
  "instansi_id": 1,
  "kategori_id": 1,
  "deskripsi": "Deskripsi pengaduan",
  "foto_bukti": ["filename1.jpg", "filename2.jpg"]
}
```

### File Upload

#### POST /api/upload

Upload file gambar
**Headers**: `Authorization: Bearer <token>`
**Content-Type**: `multipart/form-data`
**Files**: `files[]` (max 5MB per file, format: jpg,jpeg,png)

## Security Configuration

### CSP Headers

```php
Content-Security-Policy: default-src 'self';
script-src 'self' 'nonce-{random}' https://cdn.jsdelivr.net;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
img-src 'self' data: https:;
font-src 'self' https://fonts.gstatic.com;
```

### Rate Limiting

- API endpoints: 60 requests/minute per IP
- Login endpoints: 5 attempts/minute per IP
- File upload: 10 uploads/minute per user

## License

Copyright © 2025 Dinas Kominfo Gunung Kidul. All rights reserved.
