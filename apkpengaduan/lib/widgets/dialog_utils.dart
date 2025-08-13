import 'package:flutter/material.dart';

/// Shows a confirmation dialog with a delete warning
Future<void> showDeleteConfirmationDialog(
  BuildContext context, {
  required String title,
  required String content,
  required VoidCallback onConfirm,
  String confirmText = 'Delete',
  String cancelText = 'Cancel',
}) async {
  return showConfirmationDialog(
    context,
    title: title,
    content: content,
    onConfirm: onConfirm,
    confirmText: confirmText,
    cancelText: cancelText,
    confirmColor: Colors.red,
    icon: Icons.warning_amber_rounded,
    iconColor: Colors.red,
  );
}

/// Shows a generic confirmation dialog
Future<void> showConfirmationDialog(
  BuildContext context, {
  required String title,
  required String content,
  required VoidCallback onConfirm,
  String confirmText = 'Confirm',
  String cancelText = 'Cancel',
  Color? confirmColor,
  IconData? icon,
  Color? iconColor,
}) async {
  return showDialog(
    context: context,
    builder:
        (context) => AlertDialog(
          icon: icon != null ? Icon(icon, color: iconColor, size: 48) : null,
          title: Text(title),
          content: Text(content),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: Text(cancelText),
            ),
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                onConfirm();
              },
              style:
                  confirmColor != null
                      ? TextButton.styleFrom(foregroundColor: confirmColor)
                      : null,
              child: Text(confirmText),
            ),
          ],
        ),
  );
}
