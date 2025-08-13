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

  // Getters
  User? get user => _user;
  String? get token => _token;
  bool get isLoggedIn => _token != null;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Constructor
  AuthProvider() {
    _loadFromPrefs();
  }

  // Load auth state from SharedPreferences
  Future<void> _loadFromPrefs() async {
    _setLoading(true);
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token != null && token.isNotEmpty) {
        _token = token;
        _apiService.setToken(token);

        // Get user info
        await _fetchUserInfo();
      }
    } catch (e) {
      _setError(e.toString());
    } finally {
      _setLoading(false);
    }
  }

  // Save auth state to SharedPreferences
  Future<void> _saveToPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    if (_token != null) {
      await prefs.setString('token', _token!);
    } else {
      await prefs.remove('token');
    }
  }

  // Register a new user
  Future<bool> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.register(
        name: name,
        email: email,
        phone: phone,
        password: password,
      );

      final authResponse = AuthResponse.fromJson(response);

      if (authResponse.status &&
          authResponse.user != null &&
          authResponse.token != null) {
        _user = authResponse.user;
        _token = authResponse.token;
        _apiService.setToken(_token!);
        await _saveToPrefs();
        return true;
      } else {
        _setError(authResponse.message);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Login user
  Future<bool> login({
    required String emailOrPhone,
    required String password,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.login(
        emailOrPhone: emailOrPhone,
        password: password,
      );

      final authResponse = AuthResponse.fromJson(response);

      if (authResponse.status &&
          authResponse.user != null &&
          authResponse.token != null) {
        _user = authResponse.user;
        _token = authResponse.token;
        _apiService.setToken(_token!);
        await _saveToPrefs();
        return true;
      } else {
        _setError(authResponse.message);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Logout user
  Future<bool> logout() async {
    _setLoading(true);
    _clearError();

    try {
      if (_token != null) {
        await _apiService.logout();
      }

      _user = null;
      _token = null;
      _apiService.clearToken();
      await _saveToPrefs();
      return true;
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Change user password
  Future<bool> changePassword(String oldPassword, String newPassword) async {
    _setLoading(true);
    _clearError();
    
    try {
      final response = await _apiService.changePassword(
        currentPassword: oldPassword,
        newPassword: newPassword,
        confirmPassword: newPassword,
      );
      
      if (response['status'] == true) {
        return true;
      } else {
        _setError(response['message'] ?? 'Failed to change password');
        return false;
      }
    } catch (e) {
      _setError('Error: ${e.toString()}');
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Get current user info
  Future<void> _fetchUserInfo() async {
    try {
      final response = await _apiService.getUser();

      if (response['status'] &&
          response['data'] != null &&
          response['data']['user'] != null) {
        _user = User.fromJson(response['data']['user']);
        notifyListeners();
      }
    } catch (e) {
      // If we can't get user info, logout
      await logout();
    }
  }

  // Helper methods
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String error) {
    _error = error;
    notifyListeners();
  }

  void _clearError() {
    _error = null;
    notifyListeners();
  }
}
