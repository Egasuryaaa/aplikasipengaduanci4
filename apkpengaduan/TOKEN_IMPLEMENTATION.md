# Token Management Implementation for Flutter App

Implementasi ini menambahkan sistem token Bearer authentication ke aplikasi Flutter setelah login berhasil.

## Fitur yang Ditambahkan

### 1. AuthProvider Enhancement

- **Token Storage**: Menyimpan token JWT ke SharedPreferences
- **Auto Token Header**: Otomatis menambahkan header `Authorization: Bearer <token>` ke semua API request
- **Session Management**: Menyimpan dan memuat session user
- **Auto Login**: Cek status login saat aplikasi dimulai
- **Logout**: Membersihkan token dan session

### 2. ApiService Enhancement

- **Auto Token Initialization**: Memuat token dari storage saat startup
- **Token Management**: Method setToken() dan clearToken()
- **Auto Header**: Semua request API otomatis menggunakan token

### 3. UI Enhancements

- **AuthWrapper**: Screen wrapper untuk cek status login
- **Drawer Navigation**: Menu navigasi dengan informasi user
- **Logout Confirmation**: Dialog konfirmasi logout

## Response JSON dari Server

Setelah login berhasil, server mengirim response:

```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "4",
      "uuid": "3626e00a-cf2e-4b66-aca9-f8d54da211af",
      "name": "John Doe",
      "email": "john.doe@gmail.com",
      "phone": "081234567893",
      "instansi_id": "1",
      "role": "user",
      "is_active": "t",
      "email_verified_at": "2025-08-12 01:46:07",
      "last_login": "2025-08-18 13:10:40",
      "created_at": "2025-08-12 01:46:07",
      "updated_at": "2025-08-18 13:10:40"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjQiLCJlbWFpbCI6ImpvaG4uZG9lQGdtYWlsLmNvbSIsImV4cCI6MTc1NTYwOTA0MX0.PCisCX2obVCRXE55_LJiV8vExtyBova_khmhCsutick"
  }
}
```

## Cara Kerja Token

### 1. Login Process

1. User login dengan email/phone dan password
2. Server memverifikasi credentials
3. Server mengembalikan JWT token dan data user
4. AuthProvider menyimpan token dan user data
5. ApiService mengset Authorization header
6. User diarahkan ke HomeScreen

### 2. API Request Process

1. Setiap API request otomatis menggunakan token
2. ApiService menambahkan header: `Authorization: Bearer <token>`
3. Server memverifikasi token
4. Request diproses jika token valid

### 3. Token Persistence

- Token disimpan di SharedPreferences
- Saat app restart, token dimuat otomatis
- User tetap login sampai logout manual atau token expired

### 4. Logout Process

1. AuthProvider menghapus semua data session
2. ApiService menghapus Authorization header
3. User diarahkan kembali ke LoginScreen

## Files yang Dimodifikasi/Dibuat

### Modified Files:

1. `lib/providers/auth_provider.dart` - Enhanced token management
2. `lib/services/api_service.dart` - Auto token headers
3. `lib/screens/home_screen.dart` - Added drawer and logout
4. `lib/main.dart` - Updated to use AuthWrapper

### New Files:

1. `lib/screens/auth/auth_wrapper.dart` - Auth status checker

## Penggunaan

### Login

```dart
// Di LoginScreen
final authProvider = Provider.of<AuthProvider>(context, listen: false);
final success = await authProvider.login(
  emailOrPhone: emailController.text,
  password: passwordController.text,
);

if (success) {
  // Token otomatis tersimpan dan diset ke ApiService
  Navigator.pushReplacement(context, MaterialPageRoute(
    builder: (_) => const HomeScreen()
  ));
}
```

### API Call (Otomatis menggunakan token)

```dart
// Di provider atau service lainnya
final apiService = ApiService();
final response = await apiService.getPengaduanList(); // Token otomatis di header
```

### Logout

```dart
// Di HomeScreen atau screen lainnya
final authProvider = Provider.of<AuthProvider>(context, listen: false);
await authProvider.logout();
Navigator.pushAndRemoveUntil(
  context,
  MaterialPageRoute(builder: (_) => const LoginScreen()),
  (route) => false,
);
```

### Check Auth Status

```dart
// Saat app startup atau navigation
final authProvider = Provider.of<AuthProvider>(context, listen: false);
final isLoggedIn = await authProvider.checkAuthStatus();
if (isLoggedIn) {
  // User sudah login, arahkan ke HomeScreen
} else {
  // User belum login, arahkan ke LoginScreen
}
```

## Security Features

1. **Token Expiration**: JWT token memiliki expiry time
2. **Secure Storage**: Token disimpan di SharedPreferences (encrypted di production)
3. **Auto Cleanup**: Token dihapus saat logout
4. **Server Validation**: Setiap request divalidasi server-side

## Best Practices

1. **Error Handling**: Semua API call memiliki try-catch
2. **Loading States**: UI menunjukkan loading indicator
3. **User Feedback**: SnackBar untuk sukses/error messages
4. **Navigation**: Proper navigation flow untuk auth states
5. **Clean Architecture**: Provider pattern untuk state management

## Testing

Untuk menguji implementasi:

1. Login dengan credentials valid
2. Cek apakah token tersimpan di SharedPreferences
3. Lakukan API call dan verify Authorization header
4. Restart app dan cek auto-login
5. Test logout dan verify token cleanup
