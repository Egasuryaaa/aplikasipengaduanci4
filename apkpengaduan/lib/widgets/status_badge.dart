import 'package:flutter/material.dart';

/// Enum for status badge sizes
enum StatusBadgeSize { small, medium, large }

class StatusBadge extends StatelessWidget {
  final String status;
  final StatusBadgeSize size;

  const StatusBadge({
    super.key,
    required this.status,
    this.size = StatusBadgeSize.medium,
  });

  @override
  Widget build(BuildContext context) {
    // Define status configurations
    final statusConfig = _getStatusConfig(status.toLowerCase());

    // Adjust padding and font size based on the badge size
    final padding = _getPadding();
    final iconSize = _getIconSize();
    final fontSize = _getFontSize();

    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: statusConfig.backgroundColor,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(statusConfig.icon, color: Colors.white, size: iconSize),
          const SizedBox(width: 4),
          Text(
            statusConfig.label,
            style: TextStyle(
              color: Colors.white,
              fontSize: fontSize,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  /// Get padding based on badge size
  EdgeInsets _getPadding() {
    switch (size) {
      case StatusBadgeSize.small:
        return const EdgeInsets.symmetric(horizontal: 8, vertical: 4);
      case StatusBadgeSize.medium:
        return const EdgeInsets.symmetric(horizontal: 12, vertical: 6);
      case StatusBadgeSize.large:
        return const EdgeInsets.symmetric(horizontal: 16, vertical: 8);
    }
  }

  /// Get icon size based on badge size
  double _getIconSize() {
    switch (size) {
      case StatusBadgeSize.small:
        return 12;
      case StatusBadgeSize.medium:
        return 16;
      case StatusBadgeSize.large:
        return 20;
    }
  }

  /// Get font size based on badge size
  double _getFontSize() {
    switch (size) {
      case StatusBadgeSize.small:
        return 10;
      case StatusBadgeSize.medium:
        return 12;
      case StatusBadgeSize.large:
        return 14;
    }
  }

  /// Get status configuration based on the status string
  StatusConfig _getStatusConfig(String status) {
    switch (status) {
      case 'pending':
        return StatusConfig(
          backgroundColor: Colors.orange,
          icon: Icons.hourglass_empty,
          label: 'Pending',
        );
      case 'diproses':
        return StatusConfig(
          backgroundColor: Colors.blue,
          icon: Icons.sync,
          label: 'Diproses',
        );
      case 'selesai':
        return StatusConfig(
          backgroundColor: Colors.green,
          icon: Icons.check_circle,
          label: 'Selesai',
        );
      case 'ditolak':
        return StatusConfig(
          backgroundColor: Colors.red,
          icon: Icons.cancel,
          label: 'Ditolak',
        );
      default:
        return StatusConfig(
          backgroundColor: Colors.grey,
          icon: Icons.help,
          label: status,
        );
    }
  }
}

/// Configuration class for different status types
class StatusConfig {
  final Color backgroundColor;
  final IconData icon;
  final String label;

  StatusConfig({
    required this.backgroundColor,
    required this.icon,
    required this.label,
  });
}
