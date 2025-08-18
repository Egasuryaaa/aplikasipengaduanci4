import 'package:flutter/material.dart';

class ProfileScreen extends StatelessWidget {
  final Map<String, dynamic> user;

  const ProfileScreen({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    final instansi = user['instansi'] ?? {};
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil User'),
        elevation: 0,
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
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
                      child: Icon(
                        Icons.person,
                        size: 60,
                        color: Colors.blue.shade600,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      user['name'] ?? 'No Name',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    Text(
                      user['email'] ?? 'No Email',
                      style: const TextStyle(
                        fontSize: 16,
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // User Info Card
            Card(
              elevation: 4,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Informasi Personal',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    _buildInfoTile(Icons.phone, 'No. HP', user['phone'] ?? '-'),
                    _buildInfoTile(
                      Icons.verified_user,
                      'Role',
                      user['role'] ?? '-',
                    ),
                    _buildInfoTile(
                      Icons.check_circle,
                      'Status',
                      user['is_active'] == 't' ? 'Aktif' : 'Tidak Aktif',
                      statusColor:
                          user['is_active'] == 't' ? Colors.green : Colors.red,
                    ),
                    _buildInfoTile(
                      Icons.calendar_today,
                      'Dibuat',
                      user['created_at'] ?? '-',
                    ),
                    _buildInfoTile(
                      Icons.update,
                      'Terakhir Update',
                      user['updated_at'] ?? '-',
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Instansi Info Card
            if (instansi.isNotEmpty)
              Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Informasi Instansi',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildInfoTile(
                        Icons.business,
                        'Instansi',
                        instansi['nama'] ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.location_on,
                        'Alamat',
                        instansi['alamat'] ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.phone_outlined,
                        'Telepon',
                        instansi['telepon'] ?? '-',
                      ),
                      _buildInfoTile(
                        Icons.email_outlined,
                        'Email',
                        instansi['email'] ?? '-',
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoTile(
    IconData icon,
    String label,
    String value, {
    Color? statusColor,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.blue.shade50,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: Colors.blue.shade600, size: 24),
          ),
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
