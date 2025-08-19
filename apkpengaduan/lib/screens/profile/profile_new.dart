import 'package:flutter/material.dart';
import '../../widgets/common_bottom_nav.dart';
import '../../services/api_service.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ApiService _apiService = ApiService();
  Map<String, dynamic>? _userData;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchUserData();
  }

  Future<void> _fetchUserData() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final response = await _apiService.getUserDetail();

      if (response['status'] == true && response['data'] != null) {
        setState(() {
          _userData = response['data']['user'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response['message'] ?? 'Failed to load user data';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Error: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Profil User'),
          elevation: 0,
          backgroundColor: Colors.blue.shade600,
          foregroundColor: Colors.white,
          automaticallyImplyLeading: false,
        ),
        body: const Center(child: CircularProgressIndicator()),
        bottomNavigationBar: const CommonBottomNav(currentIndex: 2),
      );
    }

    if (_error != null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Profil User'),
          elevation: 0,
          backgroundColor: Colors.blue.shade600,
          foregroundColor: Colors.white,
          automaticallyImplyLeading: false,
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, size: 64, color: Colors.grey.shade400),
              const SizedBox(height: 16),
              Text(
                _error!,
                style: TextStyle(fontSize: 16, color: Colors.grey.shade600),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _fetchUserData,
                icon: const Icon(Icons.refresh),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
        bottomNavigationBar: const CommonBottomNav(currentIndex: 2),
      );
    }

    final user = _userData ?? {};
    final instansi = user['instansi'] ?? {};

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil User'),
        elevation: 0,
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            onPressed: _fetchUserData,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh Data',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchUserData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              // Profile Header Card
              Card(
                elevation: 8,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    gradient: LinearGradient(
                      colors: [Colors.blue.shade600, Colors.blue.shade400],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: Column(
                    children: [
                      CircleAvatar(
                        radius: 50,
                        backgroundColor: Colors.white,
                        child: Text(
                          (user['name']?.toString().isNotEmpty == true)
                              ? user['name']
                                  .toString()
                                  .substring(0, 1)
                                  .toUpperCase()
                              : 'U',
                          style: TextStyle(
                            fontSize: 36,
                            fontWeight: FontWeight.bold,
                            color: Colors.blue.shade600,
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        user['name']?.toString() ?? 'User Name',
                        style: const TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        user['email']?.toString() ?? 'email@example.com',
                        style: const TextStyle(
                          fontSize: 16,
                          color: Colors.white70,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          user['role']?.toString().toUpperCase() ?? 'USER',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // User Information Card
              Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Informasi Personal',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildInfoTile(
                        Icons.person_outline,
                        'Nama Lengkap',
                        user['name']?.toString() ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.email_outlined,
                        'Email',
                        user['email']?.toString() ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.phone_outlined,
                        'Telepon',
                        user['phone']?.toString() ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.badge_outlined,
                        'Role',
                        user['role']?.toString().toUpperCase() ?? 'USER',
                      ),
                      _buildInfoTile(
                        Icons.toggle_on,
                        'Status',
                        (user['is_active'] == 't' || user['is_active'] == true)
                            ? 'Aktif'
                            : 'Tidak Aktif',
                        statusColor:
                            (user['is_active'] == 't' ||
                                    user['is_active'] == true)
                                ? Colors.green
                                : Colors.red,
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 16),

              // Institution Information Card
              Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Informasi Instansi',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildInfoTile(
                        Icons.business,
                        'Instansi',
                        instansi['nama']?.toString() ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.location_on,
                        'Alamat',
                        instansi['alamat']?.toString() ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.phone_outlined,
                        'Telepon',
                        instansi['telepon']?.toString() ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.email_outlined,
                        'Email Instansi',
                        instansi['email']?.toString() ?? '-',
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 16),

              // Account Information Card
              Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Informasi Akun',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildInfoTile(
                        Icons.fingerprint,
                        'User ID',
                        user['uuid']?.toString() ??
                            user['id']?.toString() ??
                            '-',
                      ),
                      _buildInfoTile(
                        Icons.access_time,
                        'Bergabung Sejak',
                        user['created_at']?.toString() ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.update,
                        'Terakhir Diperbarui',
                        user['updated_at']?.toString() ?? '-',
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: const CommonBottomNav(currentIndex: 2),
    );
  }

  Widget _buildInfoTile(
    IconData icon,
    String label,
    String value, {
    Color? statusColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        children: [
          Icon(icon, color: Colors.blue.shade600, size: 24),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey.shade600,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: statusColor ?? Colors.black87,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
