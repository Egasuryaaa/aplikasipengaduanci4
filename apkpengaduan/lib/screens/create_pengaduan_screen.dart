import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import '../providers/pengaduan_provider.dart';

class CreatePengaduanScreen extends StatefulWidget {
  const CreatePengaduanScreen({super.key});

  @override
  State<CreatePengaduanScreen> createState() => _CreatePengaduanScreenState();
}

class _CreatePengaduanScreenState extends State<CreatePengaduanScreen> {
  final _formKey = GlobalKey<FormState>();
  final _judulController = TextEditingController();
  final _isiController = TextEditingController();
  final _lokasiController = TextEditingController();
  int? _selectedKategoriId;
  File? _imageFile;
  final _imagePicker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _loadKategori();
  }

  @override
  void dispose() {
    _judulController.dispose();
    _isiController.dispose();
    _lokasiController.dispose();
    super.dispose();
  }

  Future<void> _loadKategori() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    await pengaduanProvider.fetchKategori();
  }

  Future<void> _pickImage() async {
    try {
      final pickedFile = await _imagePicker.pickImage(
        source: ImageSource.gallery,
      );
      if (pickedFile != null) {
        setState(() {
          _imageFile = File(pickedFile.path);
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error picking image: $e')));
      }
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedKategoriId == null) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Please select a category')));
      return;
    }

    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );

    final success = await pengaduanProvider.createPengaduan(
      judul: _judulController.text.trim(),
      isi: _isiController.text.trim(),
      kategoriId: _selectedKategoriId!,
      lokasi: _lokasiController.text.trim(),
      foto: _imageFile,
    );

    if (success && mounted) {
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pengaduan created successfully')),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            pengaduanProvider.error ?? 'Failed to create pengaduan',
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final pengaduanProvider = Provider.of<PengaduanProvider>(context);
    final kategoriList = pengaduanProvider.kategori;

    return Scaffold(
      appBar: AppBar(title: const Text('Create Pengaduan')),
      body:
          pengaduanProvider.isLoading && kategoriList.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Kategori dropdown
                      DropdownButtonFormField<int>(
                        decoration: const InputDecoration(
                          labelText: 'Category',
                          border: OutlineInputBorder(),
                        ),
                        value: _selectedKategoriId,
                        items:
                            kategoriList
                                .map(
                                  (kategori) => DropdownMenuItem<int>(
                                    value: kategori.id,
                                    child: Text(kategori.nama),
                                  ),
                                )
                                .toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedKategoriId = value;
                          });
                        },
                        validator: (value) {
                          if (value == null) {
                            return 'Please select a category';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),

                      // Judul field
                      TextFormField(
                        controller: _judulController,
                        decoration: const InputDecoration(
                          labelText: 'Title',
                          border: OutlineInputBorder(),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter a title';
                          }
                          if (value.length < 3) {
                            return 'Title must be at least 3 characters';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),

                      // Isi field
                      TextFormField(
                        controller: _isiController,
                        decoration: const InputDecoration(
                          labelText: 'Description',
                          border: OutlineInputBorder(),
                          alignLabelWithHint: true,
                        ),
                        maxLines: 5,
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'Please enter a description';
                          }
                          if (value.length < 10) {
                            return 'Description must be at least 10 characters';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),

                      // Lokasi field
                      TextFormField(
                        controller: _lokasiController,
                        decoration: const InputDecoration(
                          labelText: 'Location',
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Photo section
                      const Text(
                        'Photo Evidence (Optional)',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          ElevatedButton.icon(
                            onPressed: _pickImage,
                            icon: const Icon(Icons.photo),
                            label: const Text('Pick Image'),
                          ),
                          const SizedBox(width: 16),
                          if (_imageFile != null) ...[
                            Expanded(
                              child: Text(
                                'Selected: ${_imageFile!.path.split('/').last}',
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.delete),
                              onPressed: () {
                                setState(() {
                                  _imageFile = null;
                                });
                              },
                            ),
                          ],
                        ],
                      ),
                      if (_imageFile != null) ...[
                        const SizedBox(height: 8),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Image.file(
                            _imageFile!,
                            height: 200,
                            width: double.infinity,
                            fit: BoxFit.cover,
                          ),
                        ),
                      ],
                      const SizedBox(height: 24),

                      // Submit button
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed:
                              pengaduanProvider.isLoading ? null : _submitForm,
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child:
                              pengaduanProvider.isLoading
                                  ? const CircularProgressIndicator()
                                  : const Text('Submit Pengaduan'),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
    );
  }
}
