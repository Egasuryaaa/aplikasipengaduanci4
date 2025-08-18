# Debug Authentication Issues

## Problem

- Getting 401 Unauthorized errors when creating pengaduan
- Error: `GET http://localhost/serverpengaduan/api/pengaduan/14 401 (Unauthorized)`

## Root Cause Analysis

1. **ApiService Singleton Issue**: Each provider was creating new ApiService instances, causing token to not be shared between instances
2. **Token Persistence**: Token might not be properly saved or loaded from SharedPreferences
3. **Race Condition**: API calls might be made before token is properly initialized

## Solutions Applied

### 1. Fixed ApiService Singleton Pattern

```dart
// Before: Multiple instances
class ApiService {
  ApiService() { ... }
}

// After: Proper singleton
class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal() { ... }
}
```

### 2. Enhanced Token Debugging

- Added extensive logging in `_initializeToken()`
- Added debugging in `setToken()` and `clearToken()`
- Added logging in AuthProvider login and session management

### 3. Improved Session Management

- Enhanced `loadSavedSession()` with detailed logging
- Better error handling in `checkAuthStatus()`
- Clearer token validation flow

## How to Test

1. Open Flutter app at http://localhost:3000
2. Login with credentials (john.doe@gmail.com / password123)
3. Check browser console for authentication logs
4. Try creating a new pengaduan
5. Look for these log patterns:

**Expected Success Logs:**

```
[AuthProvider] Login successful - Token received: eyJ0eXAiOiJKV1QiLCJhbGc...
[ApiService] setToken() saved token and set Authorization header
[AuthProvider] Session restored successfully for user: John Doe
[ApiService] Token successfully set in headers
[ApiService] Current Authorization header: Bearer eyJ0eXAi...
```

**Error Indicators:**

```
[ApiService] WARNING: No token found in storage - user may need to login
[ApiService] Unauthorized - Token may be invalid or expired
```

## Next Steps if Still Failing

1. Clear browser cache and SharedPreferences
2. Check if XAMPP server is running
3. Verify JWT token expiration (currently 24 hours)
4. Check CORS configuration

## Backend Status

✅ PHP API endpoints tested and working (200 OK responses)
✅ JWT authentication working on server side
✅ CORS headers properly configured
