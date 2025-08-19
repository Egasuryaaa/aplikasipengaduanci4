import 'dart:developer';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  User? _user;
  String? _token;
  bool _isLoading = false;
  String? _error;
  bool _isLoggedIn = false;

  User? get user => _user;
  String? get token => _token;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isLoggedIn => _isLoggedIn;

  Future<bool> login({
    required String emailOrPhone,
    required String password,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.login(
        emailOrPhone: emailOrPhone,
        password: password,
      );
      if (response['status'] == true && response['data'] != null) {
        _user = User.fromJson(response['data']['user']);
        _token = response['data']['token'];
        _isLoggedIn = true;

        log(
          '[AuthProvider] Login successful - Token received: ${_token?.substring(0, 20)}...',
        );

        // Set token ke API service untuk request berikutnya
        await _apiService.setToken(_token!);

        // Simpan data user dan token ke SharedPreferences
        await _saveUserSession();

        log(
          '[AuthProvider] Login complete - User: ${_user?.name}, LoggedIn: $_isLoggedIn',
        );

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response['message'] ?? 'Login failed';
      }
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<bool> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final response = await _apiService.register(
        name: name,
        email: email,
        phone: phone,
        password: password,
      );
      if (response['status'] == true && response['data'] != null) {
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = response['message'] ?? 'Registrasi gagal';
      }
    } catch (e) {
      _error = e.toString();
    }
    _isLoading = false;
    notifyListeners();
    return false;
  }

  // Method untuk menyimpan session user
  Future<void> _saveUserSession() async {
    final prefs = await SharedPreferences.getInstance();
    if (_user != null && _token != null) {
      await prefs.setString('user_id', _user!.id);
      await prefs.setString('user_uuid', _user!.uuid);
      await prefs.setString('user_name', _user!.name);
      await prefs.setString('user_email', _user!.email);
      await prefs.setString('user_phone', _user!.phone);
      await prefs.setString('user_instansi_id', _user!.instansiId);
      await prefs.setString('user_role', _user!.role);
      await prefs.setString('user_is_active', _user!.isActive);
      await prefs.setString('user_created_at', _user!.createdAt);
      await prefs.setString('user_updated_at', _user!.updatedAt);

      // Simpan data opsional jika ada
      if (_user!.emailVerifiedAt != null) {
        await prefs.setString(
          'user_email_verified_at',
          _user!.emailVerifiedAt!,
        );
      }
      if (_user!.lastLogin != null) {
        await prefs.setString('user_last_login', _user!.lastLogin!);
      }

      await prefs.setString('auth_token', _token!);
      await prefs.setBool('is_logged_in', true);

      log('[AuthProvider] User session saved successfully');
    }
  }

  // Method untuk memuat session yang tersimpan
  Future<void> loadSavedSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final isLoggedIn = prefs.getBool('is_logged_in') ?? false;

      log('[AuthProvider] loadSavedSession - isLoggedIn flag: $isLoggedIn');

      if (isLoggedIn) {
        final token = prefs.getString('auth_token');
        final userId = prefs.getString('user_id');
        final userName = prefs.getString('user_name');
        final userEmail = prefs.getString('user_email');
        final userPhone = prefs.getString('user_phone');
        final userRole = prefs.getString('user_role');

        log(
          '[AuthProvider] Token from storage: ${token?.substring(0, 20)}...',
        );
        log(
          '[AuthProvider] User data from storage - ID: $userId, Name: $userName',
        );

        if (token != null &&
            userId != null &&
            userName != null &&
            userEmail != null &&
            userPhone != null &&
            userRole != null) {
          _token = token;
          _user = User(
            id: userId,
            uuid:
                prefs.getString('user_uuid') ??
                '', // UUID tidak disimpan, bisa diambil dari server jika diperlukan
            name: userName,
            email: userEmail,
            phone: userPhone,
            instansiId: prefs.getString('user_instansi_id') ?? '',
            role: userRole,
            isActive: 't', // String 't' untuk active
            emailVerifiedAt: prefs.getString('user_email_verified_at'),
            lastLogin: prefs.getString('user_last_login'),
            createdAt: prefs.getString('user_created_at') ?? '',
            updatedAt: prefs.getString('user_updated_at') ?? '',
          );
          _isLoggedIn = true;

          // Set token ke API service
          await _apiService.setToken(_token!);
          log(
            '[AuthProvider] Session restored successfully for user: $userName',
          );

          notifyListeners();
        } else {
          log('[AuthProvider] WARNING: Incomplete user data in storage');
        }
      } else {
        log('[AuthProvider] No saved session found');
      }
    } catch (e) {
      log('[AuthProvider] ERROR loading saved session: $e');
    }
  }

  // Method untuk logout
  Future<void> logout() async {
    try {
      final prefs = await SharedPreferences.getInstance();

      // Clear semua data session
      await prefs.remove('user_id');
      await prefs.remove('user_uuid');
      await prefs.remove('user_name');
      await prefs.remove('user_email');
      await prefs.remove('user_phone');
      await prefs.remove('user_instansi_id');
      await prefs.remove('user_role');
      await prefs.remove('user_is_active');
      await prefs.remove('user_email_verified_at');
      await prefs.remove('user_last_login');
      await prefs.remove('user_created_at');
      await prefs.remove('user_updated_at');
      await prefs.remove('auth_token');
      await prefs.setBool('is_logged_in', false);

      // Reset state
      _user = null;
      _token = null;
      _isLoggedIn = false;
      _error = null;

      // Clear authorization header dari API service
      _apiService.clearToken();

      notifyListeners();

      log('[AuthProvider] User logged out successfully');
    } catch (e) {
      log('Error during logout: $e');
    }
  }

  // Method untuk mengecek apakah user sudah login
  Future<bool> checkAuthStatus() async {
    if (_isLoading) return false; // Prevent multiple simultaneous calls

    _isLoading = true;
    notifyListeners();

    try {
      log('[AuthProvider] checkAuthStatus() - Loading saved session...');
      await loadSavedSession();

      // Jika ada session tersimpan, validasi token ke server
      if (_isLoggedIn && _user != null && _token != null) {
        log(
          '[AuthProvider] Session found - User: ${_user?.name}, validating token...',
        );
        final isValid = await _apiService.validateToken();
        if (!isValid) {
          log('[AuthProvider] Stored token is invalid, logging out');
          await logout();
          return false;
        }
        log('[AuthProvider] Token is valid, user authenticated');
      } else {
        log('[AuthProvider] No valid session found');
      }

      return _isLoggedIn && _user != null && _token != null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  } // Method untuk clear error

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
