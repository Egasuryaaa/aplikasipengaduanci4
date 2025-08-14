import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/pengaduan_provider.dart';
import '../../providers/kategori_provider.dart';
import '../../models/kategori.dart';

class PengaduanFormScreen extends StatefulWidget {
  final String? id;
  const PengaduanFormScreen({super.key, this.id});

  @override
  State<PengaduanFormScreen> createState() => _PengaduanFormScreenState();
}

class _PengaduanFormScreenState extends State<PengaduanFormScreen> {
  final _deskripsiController = TextEditingController();
  final _judulController = TextEditingController();
  final _lokasiController = TextEditingController();
  String? _selectedKategoriId;

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
        title: Text(widget.id == null ? 'Tambah Pengaduan' : 'Edit Pengaduan'),
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              elevation: 4,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Form Pengaduan',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 20),
                    TextFormField(
                      controller: _judulController,
                      decoration: InputDecoration(
                        labelText: 'Judul Pengaduan',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                        prefixIcon: const Icon(Icons.title),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _deskripsiController,
                      decoration: InputDecoration(
                        labelText: 'Deskripsi Pengaduan',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                        prefixIcon: const Icon(Icons.description),
                      ),
                      maxLines: 4,
                    ),
                    const SizedBox(height: 16),
                    DropdownButtonFormField<String>(
                      value: _selectedKategoriId,
                      decoration: InputDecoration(
                        labelText: 'Kategori',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                        prefixIcon: const Icon(Icons.category),
                      ),
                      items:
                          kategoriProvider.kategoriList.map((
                            Kategori kategori,
                          ) {
                            return DropdownMenuItem<String>(
                              value: kategori.id,
                              child: Text(kategori.nama),
                            );
                          }).toList(),
                      onChanged: (String? newValue) {
                        setState(() {
                          _selectedKategoriId = newValue;
                        });
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _lokasiController,
                      decoration: InputDecoration(
                        labelText: 'Lokasi (Opsional)',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                        prefixIcon: const Icon(Icons.location_on),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed:
                            provider.isLoading
                                ? null
                                : () async {
                                  if (_deskripsiController.text.isEmpty ||
                                      _selectedKategoriId == null) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(
                                        content: Text(
                                          'Harap isi semua field yang wajib',
                                        ),
                                      ),
                                    );
                                    return;
                                  }

                                  final data = {
                                    'judul': _judulController.text,
                                    'isi': _deskripsiController.text,
                                    'kategori_id': _selectedKategoriId,
                                    'lokasi': _lokasiController.text,
                                  };

                                  bool result;
                                  if (widget.id == null) {
                                    result = await provider.createPengaduan(
                                      data,
                                    );
                                  } else {
                                    result = await provider.updatePengaduan(
                                      widget.id!,
                                      data,
                                    );
                                  }

                                  if (result && mounted) {
                                    Navigator.pop(context);
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(
                                          widget.id == null
                                              ? 'Pengaduan berhasil dibuat'
                                              : 'Pengaduan berhasil diupdate',
                                        ),
                                      ),
                                    );
                                  } else if (mounted) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(
                                          provider.error ?? 'Terjadi kesalahan',
                                        ),
                                      ),
                                    );
                                  }
                                },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blue.shade600,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        child:
                            provider.isLoading
                                ? const CircularProgressIndicator(
                                  color: Colors.white,
                                )
                                : Text(
                                  widget.id == null
                                      ? 'Buat Pengaduan'
                                      : 'Update Pengaduan',
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                      ),
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
}
