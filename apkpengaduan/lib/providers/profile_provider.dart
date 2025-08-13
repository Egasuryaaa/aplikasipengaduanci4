import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ProfileProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  Map<String, dynamic>? _profile;
  bool _isLoading = false;
  String? _error;

  // Getters
  Map<String, dynamic>? get profile => _profile;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Fetch user profile
  Future<void> fetchProfile() async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.getProfile();

      if (response['status']) {
        _profile = response['data']['user'];
      } else {
        _setError(response['message']);
      }
    } catch (e) {
      _setError(e.toString());
    } finally {
      _setLoading(false);
    }
  }

  // Update user profile
  Future<bool> updateProfile({
    String? name,
    String? email,
    String? phone,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.updateProfile(
        name: name,
        email: email,
        phone: phone,
      );

      if (response['status']) {
        _profile = response['data']['user'];
        return true;
      } else {
        _setError(response['message']);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Change password
  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        confirmPassword: confirmPassword,
      );

      if (response['status']) {
        return true;
      } else {
        _setError(response['message']);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
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
