import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../helpers/auth_helper.dart';

class CommonBottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int)? onTap;

  const CommonBottomNav({super.key, this.currentIndex = 0, this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withValues(alpha: 0.2),
            spreadRadius: 1,
            blurRadius: 5,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Navigation Items
              Expanded(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _buildNavItem(
                      context: context,
                      icon: Icons.home,
                      label: 'Home',
                      index: 0,
                      route: '/home',
                    ),
                    _buildNavItem(
                      context: context,
                      icon: Icons.list_alt,
                      label: 'Pengaduan',
                      index: 1,
                      route: '/pengaduan',
                    ),
                    Consumer<AuthProvider>(
                      builder: (context, authProvider, child) {
                        return _buildNavItem(
                          context: context,
                          icon: Icons.person,
                          label: 'Profile',
                          index: 2,
                          route: '/profile',
                        );
                      },
                    ),
                    GestureDetector(
                      onTap: () => AuthHelper.performLogout(context),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 8,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.red.shade50,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.logout,
                              color: Colors.red.shade600,
                              size: 24,
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Logout',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.red.shade600,
                                fontWeight: FontWeight.normal,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required BuildContext context,
    required IconData icon,
    required String label,
    required int index,
    required String route,
  }) {
    final isSelected = currentIndex == index;

    return GestureDetector(
      onTap: () {
        if (onTap != null) {
          onTap!(index);
        } else {
          // Default navigation behavior
          if (route == '/home') {
            Navigator.pushNamedAndRemoveUntil(context, route, (route) => false);
          } else {
            Navigator.pushNamed(context, route);
          }
        }
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? Colors.blue.shade50 : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              color: isSelected ? Colors.blue.shade600 : Colors.grey,
              size: 24,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: isSelected ? Colors.blue.shade600 : Colors.grey,
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
