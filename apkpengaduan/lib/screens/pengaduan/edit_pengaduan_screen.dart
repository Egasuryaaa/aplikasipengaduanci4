import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import '../../providers/pengaduan_provider.dart';

class EditPengaduanScreen extends StatefulWidget {
  final int id;

  const EditPengaduanScreen({super.key, required this.id});

  @override
  State<EditPengaduanScreen> createState() => _EditPengaduanScreenState();
}

class _EditPengaduanScreenState extends State<EditPengaduanScreen> {
  final _formKey = GlobalKey<FormState>();
  final _judulController = TextEditingController();
  final _isiController = TextEditingController();
  final _lokasiController = TextEditingController();
  int? _selectedKategoriId;
  File? _imageFile;
  bool _isLoading = false;
  bool _isLoadingData = true;
  bool _shouldDeletePhoto = false;
  String? _currentPhotoUrl;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _judulController.dispose();
    _isiController.dispose();
    _lokasiController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoadingData = true);

    try {
      // Load pengaduan details
      final pengaduanProvider = Provider.of<PengaduanProvider>(
        context,
        listen: false,
      );

      // Fetch categories if needed
      await pengaduanProvider.fetchKategori();

      // Fetch the complaint details
      await pengaduanProvider.fetchDetail(widget.id);
      final pengaduan = pengaduanProvider.current;

      if (pengaduan != null) {
        // Fill form with current data
        _judulController.text =
            pengaduan.nomorPengaduan; // Using nomorPengaduan as title
        _isiController.text = pengaduan.deskripsi;
        _lokasiController.text = ''; // No direct lokasi field in model
        _selectedKategoriId = pengaduan.kategoriId;
        _currentPhotoUrl =
            pengaduan.fotoBukti?.isNotEmpty == true
                ? pengaduan.fotoBukti![0]
                : null;
      }
    } catch (e) {
      if (!mounted) return; // pastikan widget masih aktif
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error loading data: $e')));
    } finally {
      setState(() => _isLoadingData = false);
    }
  }

  Future<void> _pickImage() async {
    final ImagePicker picker = ImagePicker();
    final XFile? pickedFile = await picker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 1200,
      maxHeight: 1200,
      imageQuality: 85,
    );

    if (pickedFile != null) {
      setState(() {
        _imageFile = File(pickedFile.path);
        _shouldDeletePhoto =
            false; // Reset delete flag if user picks a new image
      });
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final pengaduanProvider = Provider.of<PengaduanProvider>(
        context,
        listen: false,
      );

      final success = await pengaduanProvider.updatePengaduan(
        id: widget.id,
        judul: _judulController.text,
        isi: _isiController.text,
        kategoriId: _selectedKategoriId,
        lokasi:
            _lokasiController.text.isNotEmpty ? _lokasiController.text : null,
        foto: _imageFile,
        deleteFoto: _shouldDeletePhoto,
      );

      if (success) {
        if (!mounted) return; // pastikan widget masih aktif

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pengaduan updated successfully')),
        );

        Navigator.of(context).pop(true);
      }
    } catch (e) {
      if (!mounted) return; // pastikan widget masih aktif
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final pengaduanProvider = Provider.of<PengaduanProvider>(context);
    final kategoriList = pengaduanProvider.kategori;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Pengaduan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.save),
            onPressed: _isLoading ? null : _submitForm,
          ),
        ],
      ),
      body:
          _isLoadingData
              ? const Center(child: CircularProgressIndicator())
              : SingleChildScrollView(
                padding: const EdgeInsets.all(16.0),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Title field
                      TextFormField(
                        controller: _judulController,
                        decoration: const InputDecoration(
                          labelText: 'Judul',
                          border: OutlineInputBorder(),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter a title';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),

                      // Category dropdown
                      DropdownButtonFormField<int>(
                        value: _selectedKategoriId,
                        decoration: const InputDecoration(
                          labelText: 'Kategori',
                          border: OutlineInputBorder(),
                        ),
                        items:
                            kategoriList.map((kategori) {
                              return DropdownMenuItem<int>(
                                value: kategori.id,
                                child: Text(kategori.nama),
                              );
                            }).toList(),
                        onChanged: (value) {
                          setState(() => _selectedKategoriId = value);
                        },
                        validator: (value) {
                          if (value == null) {
                            return 'Please select a category';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),

                      // Location field
                      TextFormField(
                        controller: _lokasiController,
                        decoration: const InputDecoration(
                          labelText: 'Lokasi (optional)',
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Description field
                      TextFormField(
                        controller: _isiController,
                        decoration: const InputDecoration(
                          labelText: 'Deskripsi',
                          border: OutlineInputBorder(),
                          alignLabelWithHint: true,
                        ),
                        maxLines: 5,
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter a description';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),

                      // Photo section
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Foto Bukti',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 16),

                              // Show current photo or picked image
                              if (_imageFile != null) ...[
                                Image.file(
                                  _imageFile!,
                                  height: 200,
                                  width: double.infinity,
                                  fit: BoxFit.cover,
                                ),
                                const SizedBox(height: 8),
                                ElevatedButton.icon(
                                  icon: const Icon(Icons.delete),
                                  label: const Text('Remove Selected Photo'),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.red,
                                    foregroundColor: Colors.white,
                                  ),
                                  onPressed: () {
                                    setState(() {
                                      _imageFile = null;
                                      // Only set delete flag if there was a photo before
                                      _shouldDeletePhoto =
                                          _currentPhotoUrl != null;
                                    });
                                  },
                                ),
                              ] else if (_currentPhotoUrl != null &&
                                  !_shouldDeletePhoto) ...[
                                Image.network(
                                  _currentPhotoUrl!,
                                  height: 200,
                                  width: double.infinity,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      height: 200,
                                      width: double.infinity,
                                      color: Colors.grey[300],
                                      child: const Icon(Icons.error),
                                    );
                                  },
                                ),
                                const SizedBox(height: 8),
                                ElevatedButton.icon(
                                  icon: const Icon(Icons.delete),
                                  label: const Text('Delete Current Photo'),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.red,
                                    foregroundColor: Colors.white,
                                  ),
                                  onPressed: () {
                                    setState(() {
                                      _shouldDeletePhoto = true;
                                    });
                                  },
                                ),
                              ] else ...[
                                Container(
                                  height: 200,
                                  width: double.infinity,
                                  color: Colors.grey[200],
                                  child: const Center(
                                    child: Text('No Photo Selected'),
                                  ),
                                ),
                              ],

                              const SizedBox(height: 16),
                              ElevatedButton.icon(
                                icon: const Icon(Icons.photo_library),
                                label: const Text('Select Photo'),
                                onPressed: _pickImage,
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: 24),
                      // Submit button
                      ElevatedButton(
                        onPressed: _isLoading ? null : _submitForm,
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 16),
                        ),
                        child:
                            _isLoading
                                ? const CircularProgressIndicator()
                                : const Text(
                                  'Update Pengaduan',
                                  style: TextStyle(fontSize: 16),
                                ),
                      ),
                    ],
                  ),
                ),
              ),
    );
  }
}
