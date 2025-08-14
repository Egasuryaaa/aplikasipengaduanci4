import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/pengaduan_provider.dart';
import '../../providers/kategori_provider.dart';
import '../../models/pengaduan.dart';
import 'detailpengaduan.dart';
import 'createpengaduan.dart';

class PengaduanListScreen extends StatefulWidget {
  const PengaduanListScreen({super.key});

  @override
  State<PengaduanListScreen> createState() => _PengaduanListScreenState();
}

class _PengaduanListScreenState extends State<PengaduanListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<KategoriProvider>(context, listen: false).fetchKategoriList();
    });
  }

  @override
  Widget build(BuildContext context) {
    final provider = Provider.of<PengaduanProvider>(context);
    final kategoriProvider = Provider.of<KategoriProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: Text('Daftar Pengaduan'),
        backgroundColor: Colors.indigo,
        foregroundColor: Colors.white,
      ),
      body:
          provider.isLoading
              ? Center(
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.indigo),
                ),
              )
              : RefreshIndicator(
                onRefresh: () => provider.fetchPengaduanList(),
                child: ListView.builder(
                  padding: EdgeInsets.all(16),
                  itemCount: provider.pengaduanList.length,
                  itemBuilder: (context, index) {
                    Pengaduan p = provider.pengaduanList[index];
                    final kategori =
                        kategoriProvider.kategoriList
                                .where((k) => k.id == p.kategoriId)
                                .isNotEmpty
                            ? kategoriProvider.kategoriList.firstWhere(
                              (k) => k.id == p.kategoriId,
                            )
                            : null;

                    return Card(
                      margin: EdgeInsets.only(bottom: 16),
                      elevation: 3,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(12),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => PengaduanDetailScreen(id: p.id),
                            ),
                          );
                        },
                        child: Padding(
                          padding: EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    padding: EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      color: _getStatusColor(
                                        p.status,
                                      ).withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                        color: _getStatusColor(p.status),
                                        width: 1,
                                      ),
                                    ),
                                    child: Text(
                                      p.status,
                                      style: TextStyle(
                                        color: _getStatusColor(p.status),
                                        fontWeight: FontWeight.w600,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ),
                                  Spacer(),
                                  Text(
                                    p.nomorPengaduan,
                                    style: TextStyle(
                                      color: Colors.grey[600],
                                      fontWeight: FontWeight.w500,
                                      fontSize: 14,
                                    ),
                                  ),
                                ],
                              ),
                              SizedBox(height: 12),
                              Text(
                                p.deskripsi,
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                  color: Colors.grey[800],
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              SizedBox(height: 8),
                              if (kategori != null)
                                Row(
                                  children: [
                                    Icon(
                                      Icons.category_outlined,
                                      size: 16,
                                      color: Colors.indigo,
                                    ),
                                    SizedBox(width: 4),
                                    Text(
                                      kategori.nama,
                                      style: TextStyle(
                                        color: Colors.indigo,
                                        fontWeight: FontWeight.w500,
                                        fontSize: 14,
                                      ),
                                    ),
                                  ],
                                ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => PengaduanFormScreen()),
          );
        },
        backgroundColor: Colors.indigo,
        child: Icon(Icons.add),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'proses':
        return Colors.blue;
      case 'selesai':
        return Colors.green;
      case 'ditolak':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
