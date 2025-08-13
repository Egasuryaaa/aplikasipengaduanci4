import 'package:flutter/material.dart';

/// Shows a delete confirmation dialog
///
/// [context] - The BuildContext
/// [onConfirm] - The callback function to execute when user confirms deletion
/// [title] - Optional custom title text
/// [message] - Optional custom message text
/// [confirmText] - Optional custom confirm button text
/// [cancelText] - Optional custom cancel button text
/// Returns a Future that resolves to true if user confirms, false otherwise
Future<bool> showDeleteConfirmationDialog({
  required BuildContext context,
  required VoidCallback onConfirm,
  String title = 'Hapus Pengaduan',
  String message = 'Apakah Anda yakin ingin menghapus pengaduan ini?',
  String confirmText = 'Hapus',
  String cancelText = 'Batal',
}) async {
  final result = await showDialog<bool>(
    context: context,
    barrierDismissible: false, // User must tap a button to close dialog
    builder: (BuildContext context) {
      return AlertDialog(
        title: Row(
          children: [
            const Icon(
              Icons.warning_amber_rounded,
              color: Colors.orange,
              size: 28,
            ),
            const SizedBox(width: 8),
            Text(title),
          ],
        ),
        content: Text(message),
        actions: <Widget>[
          // Cancel button
          TextButton(
            onPressed: () {
              Navigator.of(context).pop(false);
            },
            child: Text(cancelText),
          ),
          // Delete/confirm button
          TextButton(
            style: TextButton.styleFrom(
              foregroundColor: Colors.white,
              backgroundColor: Colors.red,
            ),
            onPressed: () {
              onConfirm();
              Navigator.of(context).pop(true);
            },
            child: Text(confirmText),
          ),
        ],
      );
    },
  );

  return result ?? false;
}

/// Shows a general confirmation dialog
///
/// Similar to delete confirmation but with customizable icon, colors and default text
/// Useful for other confirmations like logout, data discard, etc.
Future<bool> showConfirmationDialog({
  required BuildContext context,
  required VoidCallback onConfirm,
  required String title,
  required String message,
  String confirmText = 'Ya',
  String cancelText = 'Batal',
  IconData icon = Icons.help_outline,
  Color iconColor = Colors.blue,
  Color confirmButtonColor = Colors.blue,
}) async {
  final result = await showDialog<bool>(
    context: context,
    barrierDismissible: false,
    builder: (BuildContext context) {
      return AlertDialog(
        title: Row(
          children: [
            Icon(icon, color: iconColor, size: 28),
            const SizedBox(width: 8),
            Text(title),
          ],
        ),
        content: Text(message),
        actions: <Widget>[
          TextButton(
            onPressed: () {
              Navigator.of(context).pop(false);
            },
            child: Text(cancelText),
          ),
          TextButton(
            style: TextButton.styleFrom(
              foregroundColor: Colors.white,
              backgroundColor: confirmButtonColor,
            ),
            onPressed: () {
              onConfirm();
              Navigator.of(context).pop(true);
            },
            child: Text(confirmText),
          ),
        ],
      );
    },
  );

  return result ?? false;
}
