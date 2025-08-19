import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../helpers/auth_helper.dart';

/// Widget yang memerlukan authentication
/// Otomatis redirect ke login jika user belum authenticate
class AuthRequiredWidget extends StatefulWidget {
  final Widget child;
  final String? requiredRole;
  final String? redirectMessage;

  const AuthRequiredWidget({
    super.key,
    required this.child,
    this.requiredRole,
    this.redirectMessage,
  });

  @override
  State<AuthRequiredWidget> createState() => _AuthRequiredWidgetState();
}

class _AuthRequiredWidgetState extends State<AuthRequiredWidget> {
  bool _isChecking = true;
  bool _isAuthorized = false;

  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    // Check basic authentication
    final isAuth = await AuthHelper.requireAuth(context);

    if (isAuth && widget.requiredRole != null) {
      // Check role if specified
      if (!mounted) return;
      final hasRole = AuthHelper.hasRole(context, widget.requiredRole!);
      if (!hasRole) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                widget.redirectMessage ??
                    'Anda tidak memiliki akses untuk halaman ini',
              ),
              backgroundColor: Colors.red,
            ),
          );
          Navigator.of(context).pop();
        }
        setState(() {
          _isChecking = false;
          _isAuthorized = false;
        });
        return;
      }
    }

    setState(() {
      _isChecking = false;
      _isAuthorized = isAuth;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isChecking) {
      return const Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 16),
              Text('Memverifikasi akses...'),
            ],
          ),
        ),
      );
    }

    if (!_isAuthorized) {
      return const Scaffold(body: Center(child: Text('Tidak memiliki akses')));
    }

    return widget.child;
  }
}

/// AppBar dengan informasi user dan tombol logout
class AuthAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final List<Widget>? actions;
  final bool showLogout;

  const AuthAppBar({
    super.key,
    required this.title,
    this.actions,
    this.showLogout = true,
  });

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        return AppBar(
          title: Text(title),
          backgroundColor: Colors.blue.shade600,
          foregroundColor: Colors.white,
          actions: [
            if (actions != null) ...actions!,
            if (showLogout)
              PopupMenuButton<String>(
                icon: const Icon(Icons.account_circle),
                onSelected: (value) {
                  switch (value) {
                    case 'profile':
                      Navigator.pushNamed(context, '/profile');
                      break;
                    case 'logout':
                      AuthHelper.performLogout(context);
                      break;
                  }
                },
                itemBuilder:
                    (BuildContext context) => [
                      PopupMenuItem<String>(
                        enabled: false,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              authProvider.user?.name ?? 'User',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.black,
                              ),
                            ),
                            Text(
                              authProvider.user?.email ?? '',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade600,
                              ),
                            ),
                            const Divider(),
                          ],
                        ),
                      ),
                      const PopupMenuItem<String>(
                        value: 'profile',
                        child: ListTile(
                          leading: Icon(Icons.person),
                          title: Text('Profil'),
                          contentPadding: EdgeInsets.zero,
                        ),
                      ),
                      const PopupMenuItem<String>(
                        value: 'logout',
                        child: ListTile(
                          leading: Icon(Icons.logout, color: Colors.red),
                          title: Text(
                            'Logout',
                            style: TextStyle(color: Colors.red),
                          ),
                          contentPadding: EdgeInsets.zero,
                        ),
                      ),
                    ],
              ),
          ],
        );
      },
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}

/// Drawer dengan informasi user dan menu navigasi
class AuthDrawer extends StatelessWidget {
  const AuthDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        return Drawer(
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              UserAccountsDrawerHeader(
                decoration: BoxDecoration(color: Colors.blue.shade600),
                accountName: Text(
                  authProvider.user?.name ?? 'User',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 18,
                  ),
                ),
                accountEmail: Text(
                  authProvider.user?.email ?? '',
                  style: const TextStyle(fontSize: 14),
                ),
                currentAccountPicture: CircleAvatar(
                  backgroundColor: Colors.white,
                  child: Text(
                    (authProvider.user?.name != null &&
                            authProvider.user!.name.isNotEmpty)
                        ? authProvider.user!.name.substring(0, 1).toUpperCase()
                        : 'U',
                    style: TextStyle(
                      fontSize: 24,
                      color: Colors.blue.shade600,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
              _buildDrawerItem(
                icon: Icons.home,
                title: 'Beranda',
                onTap: () {
                  Navigator.pop(context);
                  Navigator.pushNamedAndRemoveUntil(
                    context,
                    '/home',
                    (route) => false,
                  );
                },
              ),
              _buildDrawerItem(
                icon: Icons.report_problem,
                title: 'Pengaduan Saya',
                onTap: () {
                  Navigator.pop(context);
                  Navigator.pushNamed(context, '/pengaduan');
                },
              ),
              _buildDrawerItem(
                icon: Icons.add_circle,
                title: 'Buat Pengaduan',
                onTap: () {
                  Navigator.pop(context);
                  Navigator.pushNamed(context, '/create-pengaduan');
                },
              ),
              _buildDrawerItem(
                icon: Icons.person,
                title: 'Profil',
                onTap: () {
                  Navigator.pop(context);
                  Navigator.pushNamed(context, '/profile');
                },
              ),
              const Divider(),
              _buildDrawerItem(
                icon: Icons.logout,
                title: 'Logout',
                color: Colors.red,
                onTap: () {
                  Navigator.pop(context);
                  AuthHelper.performLogout(context);
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDrawerItem({
    required IconData icon,
    required String title,
    required VoidCallback onTap,
    Color? color,
  }) {
    return ListTile(
      leading: Icon(icon, color: color),
      title: Text(title, style: TextStyle(color: color)),
      onTap: onTap,
    );
  }
}
