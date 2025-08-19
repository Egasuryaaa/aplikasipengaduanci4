import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/pengaduan_provider.dart';
import '../../providers/kategori_provider.dart';
import '../../models/pengaduan.dart';
import '../../widgets/common_bottom_nav.dart';
import 'detail_pengaduan.dart';
import 'create_pengaduan.dart';

class ListPengaduanScreen extends StatefulWidget {
  const ListPengaduanScreen({super.key});

  @override
  State<ListPengaduanScreen> createState() => _ListPengaduanScreenState();
}

class _ListPengaduanScreenState extends State<ListPengaduanScreen> {
  String _selectedStatusFilter = 'semua';
  final List<Map<String, dynamic>> _statusOptions = [
    {'value': 'semua', 'label': 'Semua Status', 'color': Colors.grey},
    {'value': 'pending', 'label': 'Menunggu', 'color': Colors.orange},
    {'value': 'diproses', 'label': 'Diproses', 'color': Colors.blue},
    {'value': 'selesai', 'label': 'Selesai', 'color': Colors.green},
    {'value': 'ditolak', 'label': 'Ditolak', 'color': Colors.red},
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  Future<void> _loadData() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    final kategoriProvider = Provider.of<KategoriProvider>(
      context,
      listen: false,
    );

    await Future.wait([
      pengaduanProvider.fetchPengaduanList(),
      kategoriProvider.fetchKategoriList(),
    ]);
  }

  List<Pengaduan> _getFilteredPengaduan(List<Pengaduan> allPengaduan) {
    if (_selectedStatusFilter == 'semua') {
      return allPengaduan;
    }
    return allPengaduan
        .where((p) => p.status.toLowerCase() == _selectedStatusFilter)
        .toList();
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

  Widget _buildStatusFilter() {
    return Container(
      height: 50,
      margin: const EdgeInsets.only(bottom: 16),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: _statusOptions.length,
        itemBuilder: (context, index) {
          final option = _statusOptions[index];
          final isSelected = _selectedStatusFilter == option['value'];

          return Container(
            margin: const EdgeInsets.only(right: 12),
            child: FilterChip(
              selected: isSelected,
              label: Text(option['label']),
              onSelected: (selected) {
                setState(() {
                  _selectedStatusFilter = option['value'];
                });
              },
              selectedColor: (option['color'] as Color).withValues(alpha: 0.2),
              checkmarkColor: option['color'],
              labelStyle: TextStyle(
                color: isSelected ? option['color'] : Colors.grey.shade700,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
              ),
              side: BorderSide(
                color: isSelected ? option['color'] : Colors.grey.shade300,
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildPengaduanCard(Pengaduan pengaduan, String? kategoriNama) {
    final statusColor = _getStatusColor(pengaduan.status);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => DetailPengaduanScreen(id: pengaduan.id),
            ),
          );
          // Refresh list jika ada perubahan
          if (result == true) {
            _loadData();
          }
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header dengan nomor dan status
              Row(
                children: [
                  Expanded(
                    child: Text(
                      pengaduan.nomorPengaduan,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                        color: Colors.black87,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: statusColor, width: 1),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 6,
                          height: 6,
                          decoration: BoxDecoration(
                            color: statusColor,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          _getStatusText(pengaduan.status),
                          style: TextStyle(
                            color: statusColor,
                            fontWeight: FontWeight.w600,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Deskripsi pengaduan
              Text(
                pengaduan.deskripsi,
                style: const TextStyle(
                  fontSize: 15,
                  color: Colors.black87,
                  height: 1.4,
                ),
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
              ),

              const SizedBox(height: 12),

              // Info kategori dan tanggal
              Row(
                children: [
                  if (kategoriNama != null) ...[
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.purple.shade50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.purple.shade200),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.category_outlined,
                            size: 14,
                            color: Colors.purple.shade600,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            kategoriNama,
                            style: TextStyle(
                              color: Colors.purple.shade600,
                              fontWeight: FontWeight.w500,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                  ],

                  Expanded(
                    child: Row(
                      children: [
                        Icon(
                          Icons.access_time,
                          size: 14,
                          color: Colors.grey.shade600,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          pengaduan.createdAt,
                          style: TextStyle(
                            color: Colors.grey.shade600,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              // Indikator keterangan admin
              if (pengaduan.keteranganAdmin != null &&
                  pengaduan.keteranganAdmin!.isNotEmpty) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.admin_panel_settings,
                        size: 14,
                        color: Colors.blue.shade600,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Ada balasan admin',
                        style: TextStyle(
                          color: Colors.blue.shade600,
                          fontWeight: FontWeight.w500,
                          fontSize: 11,
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
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Daftar Pengaduan'),
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
        elevation: 0,
        automaticallyImplyLeading: false,
      ),
      body: Consumer2<PengaduanProvider, KategoriProvider>(
        builder: (context, pengaduanProvider, kategoriProvider, child) {
          if (pengaduanProvider.isLoading &&
              pengaduanProvider.pengaduanList.isEmpty) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Memuat pengaduan...'),
                ],
              ),
            );
          }

          if (pengaduanProvider.error != null &&
              pengaduanProvider.pengaduanList.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.error_outline,
                    size: 64,
                    color: Colors.grey.shade400,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    pengaduanProvider.error!,
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
            );
          }

          final filteredPengaduan = _getFilteredPengaduan(
            pengaduanProvider.pengaduanList,
          );

          return RefreshIndicator(
            onRefresh: _loadData,
            child: Column(
              children: [
                // Filter Status
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Filter Status',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 8),
                      _buildStatusFilter(),
                    ],
                  ),
                ),

                // List Pengaduan
                Expanded(
                  child:
                      filteredPengaduan.isEmpty
                          ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.inbox_outlined,
                                  size: 64,
                                  color: Colors.grey.shade400,
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  _selectedStatusFilter == 'semua'
                                      ? 'Belum ada pengaduan'
                                      : 'Tidak ada pengaduan dengan status ${_getStatusText(_selectedStatusFilter).toLowerCase()}',
                                  style: TextStyle(
                                    fontSize: 16,
                                    color: Colors.grey.shade600,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                              ],
                            ),
                          )
                          : ListView.builder(
                            padding: const EdgeInsets.all(16),
                            itemCount: filteredPengaduan.length,
                            itemBuilder: (context, index) {
                              final pengaduan = filteredPengaduan[index];
                              final kategori = kategoriProvider.getKategoriById(
                                pengaduan.kategoriId,
                              );

                              return _buildPengaduanCard(
                                pengaduan,
                                kategori?.nama,
                              );
                            },
                          ),
                ),
              ],
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CreatePengaduanScreen()),
          );
          // Refresh list jika ada pengaduan baru
          if (result == true) {
            _loadData();
          }
        },
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Buat Pengaduan'),
      ),
      bottomNavigationBar: const CommonBottomNav(currentIndex: 1),
    );
  }
}
