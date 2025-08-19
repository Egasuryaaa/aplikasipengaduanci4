import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../screens/auth/login_screen.dart';

class AuthHelper {
  /// Check if user is authenticated and redirect to login if not
  static Future<bool> requireAuth(BuildContext context) async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    if (!authProvider.isLoggedIn ||
        authProvider.user == null ||
        authProvider.token == null) {
      // Redirect to login screen
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan login terlebih dahulu'),
          backgroundColor: Colors.orange,
        ),
      );

      return false;
    }

    return true;
  }

  /// Get current user token
  static String? getCurrentToken(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    return authProvider.token;
  }

  /// Get current user data
  static dynamic getCurrentUser(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    return authProvider.user;
  }

  /// Check if user has specific role
  static bool hasRole(BuildContext context, String role) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    return authProvider.user?.role == role;
  }

  /// Show logout confirmation dialog
  static Future<bool?> showLogoutConfirmation(BuildContext context) {
    return showDialog<bool>(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Konfirmasi Logout'),
          content: const Text('Apakah Anda yakin ingin keluar dari aplikasi?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(context).pop(true),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red,
                foregroundColor: Colors.white,
              ),
              child: const Text('Logout'),
            ),
          ],
        );
      },
    );
  }

  /// Perform logout with navigation
  static Future<void> performLogout(BuildContext context) async {
    try {
      final confirmed = await showLogoutConfirmation(context);
      if (confirmed == true) {
        if (!context.mounted) return;
        final authProvider = Provider.of<AuthProvider>(context, listen: false);
        await authProvider.logout();

        if (context.mounted) {
          Navigator.of(context).pushAndRemoveUntil(
            MaterialPageRoute(builder: (_) => const LoginScreen()),
            (route) => false,
          );

          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Logout berhasil'),
              backgroundColor: Colors.green,
            ),
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error saat logout: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }
}
