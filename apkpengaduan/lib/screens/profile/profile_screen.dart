import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/profile_provider.dart';
import '../../providers/auth_provider.dart';
import 'edit_profile_screen.dart';
import 'change_password_screen.dart';
import '../auth/login_screen.dart';
import '../../widgets/dialog_utils.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    final profileProvider = Provider.of<ProfileProvider>(
      context,
      listen: false,
    );
    await profileProvider.fetchProfile();
  }

  @override
  Widget build(BuildContext context) {
    final profileProvider = Provider.of<ProfileProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit),
            onPressed: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const EditProfileScreen(),
                ),
              );

              if (result == true) {
                _loadProfile();
              }
            },
          ),
        ],
      ),
      body:
          profileProvider.isLoading
              ? const Center(child: CircularProgressIndicator())
              : profileProvider.error != null
              ? Center(child: Text(profileProvider.error!))
              : profileProvider.profile == null
              ? const Center(child: Text('No profile data available'))
              : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildProfileHeader(profileProvider.profile!),
                    const SizedBox(height: 24),
                    _buildProfileDetails(profileProvider.profile!),
                    const SizedBox(height: 24),
                    _buildActions(),
                  ],
                ),
              ),
    );
  }

  Widget _buildProfileHeader(Map<String, dynamic> profile) {
    return Center(
      child: Column(
        children: [
          const CircleAvatar(
            radius: 50,
            backgroundColor: Colors.blue,
            child: Icon(Icons.person, size: 50, color: Colors.white),
          ),
          const SizedBox(height: 16),
          Text(
            profile['name'] ?? 'User',
            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
          ),
          Text(
            profile['role'] ?? 'User',
            style: TextStyle(fontSize: 16, color: Colors.grey[600]),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileDetails(Map<String, dynamic> profile) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Contact Information',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const Divider(),
            _buildInfoItem(
              Icons.email,
              'Email',
              profile['email'] ?? 'No email provided',
            ),
            const SizedBox(height: 8),
            _buildInfoItem(
              Icons.phone,
              'Phone',
              profile['phone'] ?? 'No phone provided',
            ),
            if (profile['instansi_id'] != null) ...[
              const SizedBox(height: 8),
              _buildInfoItem(
                Icons.business,
                'Institution',
                profile['instansi_nama'] ??
                    'Institution #${profile['instansi_id']}',
              ),
            ],
            const SizedBox(height: 8),
            _buildInfoItem(
              Icons.access_time,
              'Last Login',
              profile['last_login'] ?? 'N/A',
            ),
            const SizedBox(height: 8),
            _buildInfoItem(
              Icons.calendar_today,
              'Registered On',
              profile['created_at'] ?? 'N/A',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoItem(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, color: Colors.blue, size: 20),
        const SizedBox(width: 8),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: TextStyle(fontSize: 14, color: Colors.grey[600]),
            ),
            Text(value, style: const TextStyle(fontSize: 16)),
          ],
        ),
      ],
    );
  }

  Widget _buildActions() {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Account Settings',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.lock, color: Colors.blue),
              title: const Text('Change Password'),
              trailing: const Icon(Icons.arrow_forward_ios, size: 16),
              onTap: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const ChangePasswordScreen(),
                  ),
                );

                if (result == true) {
                  if (!mounted) return; // pastikan widget masih aktif

                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Password changed successfully'),
                    ),
                  );
                }
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout, color: Colors.red),
              title: const Text('Logout'),
              trailing: const Icon(Icons.arrow_forward_ios, size: 16),
              onTap: () => _handleLogout(),
            ),
          ],
        ),
      ),
    );
  }

  // Handle logout
  Future<void> _handleLogout() async {
    DialogUtils.showConfirmationDialog(
      context,
      title: 'Logout',
      content: 'Are you sure you want to logout?',
      confirmText: 'Logout',
      cancelText: 'Cancel',
      confirmColor: Colors.red,
      icon: Icons.logout,
      iconColor: Colors.red,
      onConfirm: () async {
        final authProvider = Provider.of<AuthProvider>(context, listen: false);
        final success = await authProvider.logout();

        if (success && mounted) {
          Navigator.of(context).pushAndRemoveUntil(
            MaterialPageRoute(builder: (context) => const LoginScreen()),
            (route) => false,
          );
        }
      },
    );
  }
}
