import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/pengaduan_provider.dart';
import '../../providers/kategori_provider.dart';
import '../../models/kategori.dart';
import '../../widgets/common_bottom_nav.dart';
import 'dart:io';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:image_picker/image_picker.dart';

import 'package:file_picker/file_picker.dart'; // Tambahkan import ini

class CreatePengaduanScreen extends StatefulWidget {
  final String? editId;
  const CreatePengaduanScreen({super.key, this.editId});

  @override
  State<CreatePengaduanScreen> createState() => _CreatePengaduanScreenState();
}

class _CreatePengaduanScreenState extends State<CreatePengaduanScreen> {
  final _formKey = GlobalKey<FormState>();
  final _deskripsiController = TextEditingController();
  String? _selectedKategoriId;
  bool _isLoading = false;
  bool _isEditMode = false;

  // Ubah tipe untuk menampung jenis file berbeda di web vs mobile
  List<dynamic> _fotoBukti = [];
  bool get isWeb => kIsWeb;

  @override
  void initState() {
    super.initState();
    _isEditMode = widget.editId != null;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<KategoriProvider>(context, listen: false).fetchKategoriList();
      if (_isEditMode) {
        _loadPengaduanData();
      }
    });
  }

  Future<void> _loadPengaduanData() async {
    if (widget.editId == null) return;

    try {
      final pengaduanProvider = Provider.of<PengaduanProvider>(
        context,
        listen: false,
      );
      await pengaduanProvider.fetchPengaduanDetail(widget.editId!);

      final pengaduan = pengaduanProvider.pengaduanDetail;
      if (pengaduan != null) {
        setState(() {
          _deskripsiController.text = pengaduan.deskripsi;
          _selectedKategoriId = pengaduan.kategoriId;
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal memuat data: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _pickFotoBukti() async {
    if (kIsWeb) {
      // Khusus Web: gunakan FilePicker
      final result = await FilePicker.platform.pickFiles(
        allowMultiple: true,
        type: FileType.image,
        withData: true, // Penting! Memastikan data bytes tersedia di web
      );

      if (result != null && result.files.isNotEmpty) {
        setState(() {
          _fotoBukti = result.files;
        });
      }
    } else {
      // Mobile: gunakan ImagePicker
      final picker = ImagePicker();
      final picked = await picker.pickMultiImage();
      if (picked.isNotEmpty) {
        setState(() {
          _fotoBukti = picked;
        });
      }
    }
  }

  @override
  void dispose() {
    _deskripsiController.dispose();
    super.dispose();
  }

  Future<void> _submitPengaduan() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selectedKategoriId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih kategori pengaduan'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final pengaduanProvider = Provider.of<PengaduanProvider>(
        context,
        listen: false,
      );

      final data = {
        'deskripsi': _deskripsiController.text.trim(),
        'kategori_id': _selectedKategoriId,
      };

      bool success;
      if (_isEditMode && widget.editId != null) {
        success = await pengaduanProvider.updatePengaduan(
          widget.editId!,
          data,
          _fotoBukti,
        );
      } else {
        success = await pengaduanProvider.createPengaduan(data, _fotoBukti);
      }

      if (success && mounted) {
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              _isEditMode
                  ? 'Pengaduan berhasil diperbarui'
                  : 'Pengaduan berhasil dibuat',
            ),
            backgroundColor: Colors.green,
          ),
        );
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              pengaduanProvider.error ??
                  'Gagal ${_isEditMode ? 'memperbarui' : 'membuat'} pengaduan',
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
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final kategoriProvider = Provider.of<KategoriProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditMode ? 'Edit Pengaduan' : 'Buat Pengaduan Baru'),
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
        elevation: 0,
        automaticallyImplyLeading: true, // Keep back button for create screen
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header Card
              Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    gradient: LinearGradient(
                      colors: [Colors.blue.shade600, Colors.blue.shade400],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: const Column(
                    children: [
                      Icon(Icons.report_problem, size: 48, color: Colors.white),
                      SizedBox(height: 12),
                      Text(
                        'Sampaikan Keluhan Anda',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                      Text(
                        'Isi formulir di bawah untuk membuat pengaduan',
                        style: TextStyle(fontSize: 14, color: Colors.white70),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Form Card
              Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Kategori Pengaduan',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Dropdown Kategori
                      kategoriProvider.isLoading
                          ? Container(
                            height: 56,
                            decoration: BoxDecoration(
                              border: Border.all(color: Colors.grey.shade300),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Center(
                              child: CircularProgressIndicator(),
                            ),
                          )
                          : DropdownButtonFormField<String>(
                            value: _selectedKategoriId,
                            decoration: InputDecoration(
                              hintText: 'Pilih kategori pengaduan',
                              prefixIcon: Icon(
                                Icons.category,
                                color: Colors.blue.shade600,
                              ),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: Colors.grey.shade300,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: Colors.grey.shade300,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: Colors.blue.shade600,
                                  width: 2,
                                ),
                              ),
                              filled: true,
                              fillColor: Colors.grey.shade50,
                            ),
                            items:
                                kategoriProvider.kategoriList.map((
                                  Kategori kategori,
                                ) {
                                  return DropdownMenuItem<String>(
                                    value: kategori.id,
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Text(
                                          kategori.nama,
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w600,
                                            fontSize: 14,
                                          ),
                                        ),
                                        Text(
                                          kategori.deskripsi,
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Colors.grey.shade600,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ],
                                    ),
                                  );
                                }).toList(),
                            onChanged: (String? newValue) {
                              setState(() {
                                _selectedKategoriId = newValue;
                              });
                            },
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Kategori wajib dipilih';
                              }
                              return null;
                            },
                          ),

                      const SizedBox(height: 24),

                      const Text(
                        'Deskripsi Pengaduan',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Text Area Deskripsi
                      TextFormField(
                        controller: _deskripsiController,
                        maxLines: 6,
                        decoration: InputDecoration(
                          hintText: 'Jelaskan detail pengaduan Anda...',
                          prefixIcon: Padding(
                            padding: const EdgeInsets.only(bottom: 80),
                            child: Icon(
                              Icons.description,
                              color: Colors.blue.shade600,
                            ),
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: Colors.grey.shade300),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(color: Colors.grey.shade300),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: Colors.blue.shade600,
                              width: 2,
                            ),
                          ),
                          filled: true,
                          fillColor: Colors.grey.shade50,
                        ),
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Deskripsi pengaduan wajib diisi';
                          }
                          if (value.trim().length < 10) {
                            return 'Deskripsi minimal 10 karakter';
                          }
                          return null;
                        },
                      ),

                      const SizedBox(height: 24),
                      // Upload Foto Bukti
                      Text(
                        'Foto Bukti (opsional)',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Wrap(
                        spacing: 8,
                        children: [
                          ..._fotoBukti.map(
                            (file) => Stack(
                              alignment: Alignment.topRight,
                              children: [
                                Container(
                                  width: 80,
                                  height: 80,
                                  margin: EdgeInsets.only(top: 8, right: 8),
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: Colors.grey.shade300,
                                    ),
                                  ),
                                  child: ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child:
                                        kIsWeb
                                            ? Image.memory(
                                              (file as PlatformFile).bytes!,
                                              fit: BoxFit.cover,
                                            )
                                            : Image.file(
                                              File((file as XFile).path),
                                              fit: BoxFit.cover,
                                            ),
                                  ),
                                ),
                                IconButton(
                                  icon: Icon(
                                    Icons.close,
                                    color: Colors.red,
                                    size: 20,
                                  ),
                                  onPressed: () {
                                    setState(() {
                                      _fotoBukti.remove(file);
                                    });
                                  },
                                ),
                              ],
                            ),
                          ),
                          InkWell(
                            onTap: _pickFotoBukti,
                            child: Container(
                              width: 80,
                              height: 80,
                              decoration: BoxDecoration(
                                color: Colors.grey.shade200,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: Colors.grey.shade400),
                              ),
                              child: Icon(
                                Icons.add_a_photo,
                                color: Colors.blue.shade600,
                              ),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 32),

                      // Submit Button
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton(
                          onPressed: _isLoading ? null : _submitPengaduan,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.blue.shade600,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 2,
                          ),
                          child:
                              _isLoading
                                  ? Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: Colors.white,
                                        ),
                                      ),
                                      SizedBox(width: 12),
                                      Text(
                                        _isEditMode
                                            ? 'Memperbarui...'
                                            : 'Membuat Pengaduan...',
                                      ),
                                    ],
                                  )
                                  : Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.send),
                                      SizedBox(width: 8),
                                      Text(
                                        _isEditMode
                                            ? 'Perbarui Pengaduan'
                                            : 'Buat Pengaduan',
                                        style: TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
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
      ),
      bottomNavigationBar: const CommonBottomNav(currentIndex: -1),
    );
  }
}
