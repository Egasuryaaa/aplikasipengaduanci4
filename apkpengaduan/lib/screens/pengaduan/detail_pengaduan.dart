import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/pengaduan_provider.dart';
import '../../providers/kategori_provider.dart';
import '../../models/pengaduan.dart';
import '../../widgets/photo_gallery.dart';

class DetailPengaduanScreen extends StatefulWidget {
  final String id;
  const DetailPengaduanScreen({super.key, required this.id});

  @override
  State<DetailPengaduanScreen> createState() => _DetailPengaduanScreenState();
}

class _DetailPengaduanScreenState extends State<DetailPengaduanScreen> {
  bool _isLoading = true;
  Pengaduan? _pengaduan;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      setState(() {
        _isLoading = true;
        _error = null;
      });

      final pengaduanProvider = Provider.of<PengaduanProvider>(
        context,
        listen: false,
      );
      final kategoriProvider = Provider.of<KategoriProvider>(
        context,
        listen: false,
      );

      // Load kategori dan detail pengaduan
      await Future.wait([
        kategoriProvider.fetchKategoriList(),
        pengaduanProvider.fetchPengaduanDetail(widget.id),
      ]);

      setState(() {
        _pengaduan = pengaduanProvider.pengaduanDetail;
        if (_pengaduan == null) {
          _error = 'Pengaduan tidak ditemukan';
        }
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _deletePengaduan() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder:
          (context) => AlertDialog(
            title: const Text('Konfirmasi Hapus'),
            content: const Text(
              'Apakah Anda yakin ingin menghapus pengaduan ini?',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Batal'),
              ),
              TextButton(
                onPressed: () => Navigator.pop(context, true),
                style: TextButton.styleFrom(foregroundColor: Colors.red),
                child: const Text('Hapus'),
              ),
            ],
          ),
    );

    if (confirm == true && mounted) {
      try {
        final pengaduanProvider = Provider.of<PengaduanProvider>(
          context,
          listen: false,
        );
        final success = await pengaduanProvider.deletePengaduan(widget.id);

        if (success && mounted) {
          Navigator.pop(context, true);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Pengaduan berhasil dihapus'),
              backgroundColor: Colors.green,
            ),
          );
        } else if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                pengaduanProvider.error ?? 'Gagal menghapus pengaduan',
              ),
              backgroundColor: Colors.red,
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Error: ${e.toString()}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'diproses':
        return Colors.blue;
      case 'selesai':
        return Colors.green;
      case 'ditolak':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getStatusText(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Menunggu';
      case 'diproses':
        return 'Diproses';
      case 'selesai':
        return 'Selesai';
      case 'ditolak':
        return 'Ditolak';
      default:
        return status;
    }
  }

  Widget _buildStatusBadge(String status) {
    final color = _getStatusColor(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 8),
          Text(
            _getStatusText(status),
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.bold,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontWeight: FontWeight.w500,
                      color: Colors.grey[600],
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    value,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.black87,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Detail Pengaduan'),
          backgroundColor: Colors.blue.shade600,
          foregroundColor: Colors.white,
        ),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_error != null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Detail Pengaduan'),
          backgroundColor: Colors.blue.shade600,
          foregroundColor: Colors.white,
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
                onPressed: _loadData,
                icon: const Icon(Icons.refresh),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_pengaduan == null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Detail Pengaduan'),
          backgroundColor: Colors.blue.shade600,
          foregroundColor: Colors.white,
        ),
        body: const Center(child: Text('Pengaduan tidak ditemukan')),
      );
    }

    final kategoriProvider = Provider.of<KategoriProvider>(context);
    final kategori = kategoriProvider.getKategoriById(_pengaduan!.kategoriId);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Pengaduan'),
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          if (_pengaduan?.status == 'pending')
            IconButton(
              onPressed: _deletePengaduan,
              icon: const Icon(Icons.delete),
              tooltip: 'Hapus Pengaduan',
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            children: [
              // Header dengan status
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Colors.blue.shade600, Colors.blue.shade400],
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _pengaduan!.nomorPengaduan,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    _buildStatusBadge(_pengaduan!.status),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Icon(
                          Icons.access_time,
                          color: Colors.white70,
                          size: 16,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'Dibuat: ${_pengaduan!.createdAt}',
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              // Content
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Deskripsi Pengaduan
                    _buildInfoCard(
                      'Deskripsi Pengaduan',
                      _pengaduan!.deskripsi,
                      Icons.description,
                      Colors.blue,
                    ),
                    const SizedBox(height: 16),

                    // Kategori
                    if (kategori != null) ...[
                      _buildInfoCard(
                        'Kategori',
                        '${kategori.nama}\n${kategori.deskripsi}',
                        Icons.category,
                        Colors.purple,
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Instansi
                    _buildInfoCard(
                      'Instansi Terkait',
                      _pengaduan!.instansiNama,
                      Icons.business,
                      Colors.green,
                    ),
                    const SizedBox(height: 16),

                    // Pelapor
                    _buildInfoCard(
                      'Pelapor',
                      '${_pengaduan!.userName}\n${_pengaduan!.userEmail}\n${_pengaduan!.userPhone}',
                      Icons.person,
                      Colors.orange,
                    ),
                    const SizedBox(height: 16),

                    // Tanggal Selesai
                    if (_pengaduan!.tanggalSelesai != null) ...[
                      _buildInfoCard(
                        'Tanggal Selesai',
                        _pengaduan!.tanggalSelesai!,
                        Icons.check_circle,
                        Colors.green,
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Foto Bukti
                    if (_pengaduan!.fotoBukti.isNotEmpty) ...[
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
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: Colors.amber.shade100,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Icon(
                                      Icons.photo_library,
                                      color: Colors.amber.shade800,
                                      size: 24,
                                    ),
                                  ),
                                  const SizedBox(width: 16),
                                  Text(
                                    'Foto Bukti',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.blue.shade100,
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Text(
                                      '${_pengaduan!.fotoBukti.length}',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: Colors.blue.shade800,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),
                              PhotoGalleryWidget(
                                photos: _pengaduan!.fotoBukti,
                                baseUrl: 'http://localhost/serverpengaduan',
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Keterangan Admin
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
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color:
                                        _pengaduan!.keteranganAdmin != null
                                            ? Colors.blue.shade100
                                            : Colors.grey.shade100,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Icon(
                                    _pengaduan!.keteranganAdmin != null
                                        ? Icons.message
                                        : Icons.message_outlined,
                                    color:
                                        _pengaduan!.keteranganAdmin != null
                                            ? Colors.blue.shade600
                                            : Colors.grey.shade600,
                                    size: 24,
                                  ),
                                ),
                                const SizedBox(width: 16),
                                const Text(
                                  'Keterangan Admin',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 16),
                            if (_pengaduan!.keteranganAdmin != null &&
                                _pengaduan!.keteranganAdmin!.isNotEmpty) ...[
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Colors.blue.shade50,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: Colors.blue.shade200,
                                  ),
                                ),
                                child: Text(
                                  _pengaduan!.keteranganAdmin!,
                                  style: const TextStyle(
                                    fontSize: 15,
                                    height: 1.5,
                                  ),
                                ),
                              ),
                            ] else ...[
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: Colors.grey.shade200,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Icon(
                                      Icons.schedule,
                                      color: Colors.grey.shade600,
                                      size: 20,
                                    ),
                                    const SizedBox(width: 12),
                                    Text(
                                      'Belum ada keterangan dari admin',
                                      style: TextStyle(
                                        color: Colors.grey.shade600,
                                        fontSize: 15,
                                        fontStyle: FontStyle.italic,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 32),

                    // Delete Button - Only show if status is pending
                    if (_pengaduan?.status == 'pending')
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton.icon(
                          onPressed: _deletePengaduan,
                          icon: const Icon(Icons.delete),
                          label: const Text(
                            'Hapus Pengaduan',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.red,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 2,
                          ),
                        ),
                      ),

                    // Info text when delete is not available
                    if (_pengaduan?.status != 'pending')
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.orange.shade50,
                          border: Border.all(color: Colors.orange.shade200),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.info_outline,
                              color: Colors.orange.shade600,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                'Pengaduan dengan status "${_pengaduan?.status}" tidak dapat dihapus',
                                style: TextStyle(
                                  color: Colors.orange.shade600,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                          ],
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
}
