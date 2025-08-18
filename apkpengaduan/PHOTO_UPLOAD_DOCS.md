# 📸 Upload Foto Bukti - Dokumentasi Lengkap

## ✅ Status Implementation

### Backend (PHP CodeIgniter)

- ✅ **Upload Controller**: Support multiple file upload
- ✅ **File Storage**: Photos saved to `public/uploads/pengaduan/`
- ✅ **File Access**: Direct URL access via Apache
- ✅ **Security**: File type & size validation (JPG/PNG/GIF, max 5MB)
- ✅ **API Response**: Returns full photo URLs

### Flutter Client

- ✅ **File Picker**: Web (FilePicker) + Mobile (ImagePicker) support
- ✅ **Upload Form**: Multiple file selection in create pengaduan
- ✅ **Photo Display**: PhotoGalleryWidget in detail pengaduan
- ✅ **URL Handling**: Smart URL detection (full URL vs relative)

## 🔧 Testing Results

### 1. Upload Test

```bash
php public/test_upload_foto.php
# Result: HTTP 201 - Pengaduan berhasil dibuat dengan foto
```

### 2. Photo Access Test

```bash
php public/test_photo_access.php
# Result: HTTP 200 - ✅ Photo accessible!
```

### 3. Flutter Integration

```
[ApiService] createPengaduan() - Current headers with Authorization
[ApiService] Response: 201 - Create pengaduan successful
```

## 📱 How to Use in Flutter

### 1. **Create Pengaduan dengan Foto**

```dart
// In CreatePengaduanScreen
1. Pilih kategori pengaduan
2. Tulis deskripsi (min 10 karakter)
3. Klik icon camera untuk upload foto
4. Pilih multiple photos (optional)
5. Submit pengaduan
```

### 2. **View Foto in Detail**

```dart
// In DetailPengaduanScreen
1. Buka detail pengaduan
2. Scroll ke bagian "Foto Bukti"
3. Tap foto untuk fullscreen view
4. Swipe untuk multiple photos
```

## 🔄 API Endpoints

### Upload Pengaduan dengan Foto

```http
POST /api/pengaduan
Authorization: Bearer {token}
Content-Type: multipart/form-data

Fields:
- deskripsi: string (required, min 10 chars)
- kategori_id: integer (required)
- foto_bukti[]: file[] (optional, multiple files)
```

### Response Format

```json
{
  "status": true,
  "message": "Pengaduan berhasil dibuat",
  "data": {
    "pengaduan": {
      "id": 21,
      "deskripsi": "...",
      "foto_bukti": [
        "http://localhost/serverpengaduan/uploads/pengaduan/file1.jpg",
        "http://localhost/serverpengaduan/uploads/pengaduan/file2.png"
      ]
    }
  }
}
```

## 🎯 Next Steps

### Test in Flutter App:

1. **Open**: http://localhost:3000
2. **Login**: john.doe@gmail.com / user123
3. **Create**: Buat pengaduan baru dengan foto
4. **View**: Lihat detail pengaduan dan foto

### Photo Features:

- ✅ Multiple file upload
- ✅ File type validation
- ✅ File size limits
- ✅ Direct URL access
- ✅ Gallery display
- ✅ Fullscreen view

## 🔒 Security Features

- **File Type Check**: Only JPG, PNG, GIF allowed
- **Size Limit**: Maximum 5MB per file
- **Random Filenames**: Prevent file conflicts
- **Public Access**: Photos accessible via direct URL
- **Authentication**: Upload requires valid JWT token

## 📝 File Structure

```
serverpengaduan/
├── public/uploads/pengaduan/          # Photo storage
├── app/Controllers/Api/PengaduanController.php  # Upload logic
├── apkpengaduan/lib/screens/pengaduan/
│   ├── create_pengaduan.dart          # Upload form
│   └── detail_pengaduan.dart          # Photo display
└── apkpengaduan/lib/widgets/
    └── photo_gallery.dart             # Gallery component
```

All photo upload and display features are now **fully functional**! 🚀
