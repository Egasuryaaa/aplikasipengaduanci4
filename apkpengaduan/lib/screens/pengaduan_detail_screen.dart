import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../providers/pengaduan_provider.dart';
import '../models/pengaduan.dart';
import '../widgets/status_badge.dart';
import '../widgets/dialog_utils.dart';
import 'edit_pengaduan_screen.dart';

class PengaduanDetailScreen extends StatefulWidget {
  final int id;

  const PengaduanDetailScreen({super.key, required this.id});

  @override
  State<PengaduanDetailScreen> createState() => _PengaduanDetailScreenState();
}

class _PengaduanDetailScreenState extends State<PengaduanDetailScreen> {
  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    await pengaduanProvider.fetchDetail(widget.id);
  }

  @override
  Widget build(BuildContext context) {
    final pengaduanProvider = Provider.of<PengaduanProvider>(context);
    final pengaduan = pengaduanProvider.current;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Pengaduan'),
        actions: [
          // Edit button
          IconButton(
            icon: const Icon(Icons.edit),
            onPressed: () async {
              final result = await Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (context) => EditPengaduanScreen(id: widget.id),
                ),
              );

              // If returned with success, refresh the data
              if (result == true) {
                _loadData();
              }
            },
          ),
          // Delete button
          IconButton(
            icon: const Icon(Icons.delete),
            onPressed:
                () => showDeleteConfirmationDialog(
                  context,
                  title: 'Hapus Pengaduan',
                  content:
                      'Apakah Anda yakin ingin menghapus pengaduan ini? Tindakan ini tidak dapat dibatalkan.',
                  onConfirm: _deletePengaduan,
                ),
          ),
        ],
      ),
      body:
          pengaduanProvider.isLoading
              ? const Center(child: CircularProgressIndicator())
              : pengaduan == null
              ? Center(
                child: Text(pengaduanProvider.error ?? 'Pengaduan not found'),
              )
              : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildHeader(pengaduan),
                    const SizedBox(height: 16),
                    _buildStatusSection(pengaduan),
                    const SizedBox(height: 16),
                    _buildDescriptionSection(pengaduan),
                    const SizedBox(height: 16),
                    if (pengaduan.fotoBukti != null &&
                        pengaduan.fotoBukti!.isNotEmpty)
                      _buildPhotoSection(pengaduan),
                  ],
                ),
              ),
    );
  }

  Widget _buildHeader(Pengaduan pengaduan) {
    final formattedDate = DateFormat(
      'dd MMM yyyy, HH:mm',
    ).format(DateTime.parse(pengaduan.createdAt));

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.numbers),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    pengaduan.nomorPengaduan,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
                ),
              ],
            ),
            const Divider(),
            Row(
              children: [
                const Icon(Icons.category),
                const SizedBox(width: 8),
                Text(pengaduan.kategoriNama ?? 'Unknown Category'),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(Icons.access_time),
                const SizedBox(width: 8),
                Text('Created: $formattedDate'),
              ],
            ),
            if (pengaduan.tanggalSelesai != null) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.check_circle),
                  const SizedBox(width: 8),
                  Text(
                    'Completed: ${DateFormat('dd MMM yyyy, HH:mm').format(DateTime.parse(pengaduan.tanggalSelesai!))}',
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatusSection(Pengaduan pengaduan) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Status',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            const SizedBox(height: 8),
            StatusBadge(status: pengaduan.status, size: StatusBadgeSize.large),
            if (pengaduan.keteranganAdmin != null &&
                pengaduan.keteranganAdmin!.isNotEmpty) ...[
              const SizedBox(height: 16),
              const Text(
                'Admin Notes:',
                style: TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 4),
              Text(pengaduan.keteranganAdmin!),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildDescriptionSection(Pengaduan pengaduan) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Description',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            const SizedBox(height: 8),
            Text(pengaduan.deskripsi),
          ],
        ),
      ),
    );
  }

  Widget _buildPhotoSection(Pengaduan pengaduan) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Photos',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            const SizedBox(height: 8),
            SizedBox(
              height: 200,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: pengaduan.fotoBukti!.length,
                itemBuilder: (context, index) {
                  final foto = pengaduan.fotoBukti![index];
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: GestureDetector(
                      onTap: () {
                        _showFullScreenImage(context, foto);
                      },
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network(
                          foto,
                          width: 200,
                          height: 200,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return Container(
                              width: 200,
                              height: 200,
                              color: Colors.grey[300],
                              child: const Center(child: Icon(Icons.error)),
                            );
                          },
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showFullScreenImage(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder:
          (context) => Dialog(
            backgroundColor: Colors.transparent,
            insetPadding: EdgeInsets.zero,
            child: Stack(
              alignment: Alignment.center,
              children: [
                InteractiveViewer(
                  panEnabled: true,
                  boundaryMargin: const EdgeInsets.all(20),
                  minScale: 0.5,
                  maxScale: 4,
                  child: Image.network(
                    imageUrl,
                    fit: BoxFit.contain,
                    errorBuilder: (context, error, stackTrace) {
                      return Container(
                        color: Colors.grey[300],
                        child: const Center(child: Icon(Icons.error, size: 50)),
                      );
                    },
                  ),
                ),
                Positioned(
                  top: 20,
                  right: 20,
                  child: IconButton(
                    icon: const Icon(
                      Icons.close,
                      color: Colors.white,
                      size: 30,
                    ),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                ),
              ],
            ),
          ),
    );
  }

  // Delete the pengaduan
  Future<void> _deletePengaduan() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );

    try {
      final success = await pengaduanProvider.deletePengaduan(widget.id);

      if (success) {
  if (!mounted) return; // pastikan widget masih aktif

  ScaffoldMessenger.of(context).showSnackBar(
    const SnackBar(content: Text('Pengaduan deleted successfully')),
  );

        // Navigate back to the list screen with refresh indicator
        if (mounted) Navigator.of(context).pop(true);
      }
    } catch (e) {
      if (!mounted) return; // pastikan widget masih aktif

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error deleting pengaduan: $e')),
      );
    }
  }
}
