import 'dart:io';
import 'package:flutter/material.dart';
import '../models/pengaduan.dart';
import '../services/api_service.dart';

class PengaduanProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<Pengaduan> _items = [];
  Pengaduan? _current;
  bool _isLoading = false;
  String? _error;
  int _page = 1;
  bool _hasMore = true;
  Map<String, dynamic>? _meta;
  List<Kategori> _kategori = [];

  // Getters
  List<Pengaduan> get items => _items;
  Pengaduan? get current => _current;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get page => _page;
  bool get hasMore => _hasMore;
  Map<String, dynamic>? get meta => _meta;
  List<Kategori> get kategori => _kategori;

  // Fetch pengaduan list
  Future<void> fetchList({
    bool refresh = false,
    String? search,
    String? status,
    String? dateFrom,
    String? dateTo,
  }) async {
    if (refresh) {
      _page = 1;
      _hasMore = true;
    }

    if (_isLoading || (!_hasMore && !refresh)) return;

    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.getPengaduan(
        page: _page,
        search: search,
        status: status,
        dateFrom: dateFrom,
        dateTo: dateTo,
      );

      final pengaduanResponse = PengaduanListResponse.fromJson(response);

      if (pengaduanResponse.status) {
        if (refresh) {
          _items = pengaduanResponse.items;
        } else {
          _items = [..._items, ...pengaduanResponse.items];
        }

        _meta = pengaduanResponse.meta;

        // Check if there are more pages
        if (_meta != null && _meta!['current_page'] < _meta!['total_pages']) {
          _page++;
          _hasMore = true;
        } else {
          _hasMore = false;
        }
      } else {
        _setError(pengaduanResponse.message);
      }
    } catch (e) {
      _setError(e.toString());
    } finally {
      _setLoading(false);
    }
  }

  // Fetch pengaduan detail
  Future<void> fetchDetail(int id) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.getPengaduanDetail(id);

      final pengaduanResponse = PengaduanDetailResponse.fromJson(response);

      if (pengaduanResponse.status && pengaduanResponse.pengaduan != null) {
        _current = pengaduanResponse.pengaduan;
      } else {
        _setError(pengaduanResponse.message);
      }
    } catch (e) {
      _setError(e.toString());
    } finally {
      _setLoading(false);
    }
  }

  // Create new pengaduan
  Future<bool> createPengaduan({
    required String judul,
    required String isi,
    required int kategoriId,
    String? lokasi,
    File? foto,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.createPengaduan(
        judul: judul,
        isi: isi,
        kategoriId: kategoriId,
        lokasi: lokasi,
        foto: foto,
      );

      if (response['status']) {
        // Refresh list on success
        await fetchList(refresh: true);
        return true;
      } else {
        _setError(response['message']);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Update existing pengaduan
  Future<bool> updatePengaduan({
    required int id,
    String? judul,
    String? isi,
    int? kategoriId,
    String? lokasi,
    File? foto,
    bool deleteFoto = false,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.updatePengaduan(
        id: id,
        judul: judul,
        isi: isi,
        kategoriId: kategoriId,
        lokasi: lokasi,
        foto: foto,
        deleteFoto: deleteFoto,
      );

      if (response['status']) {
        // Refresh detail and list on success
        await fetchDetail(id);
        await fetchList(refresh: true);
        return true;
      } else {
        _setError(response['message']);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Delete a pengaduan
  Future<bool> deletePengaduan(int id) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.deletePengaduan(id);

      if (response['status']) {
        // Refresh list on successful deletion
        await fetchList(refresh: true);
        clearCurrent(); // Clear current selection since it was deleted
        return true;
      } else {
        _setError(response['message']);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Add status to pengaduan
  Future<bool> addStatus({
    required int id,
    required String status,
    String? keterangan,
  }) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.addPengaduanStatus(
        id: id,
        status: status,
        keterangan: keterangan,
      );

      if (response['status']) {
        // Refresh detail and list on success
        await fetchDetail(id);
        await fetchList(refresh: true);
        return true;
      } else {
        _setError(response['message']);
        return false;
      }
    } catch (e) {
      _setError(e.toString());
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Fetch kategori list
  Future<void> fetchKategori() async {
    if (_kategori.isNotEmpty) return;

    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.getKategori();

      final kategoriResponse = KategoriListResponse.fromJson(response);

      if (kategoriResponse.status) {
        _kategori = kategoriResponse.kategori;
      } else {
        _setError(kategoriResponse.message);
      }
    } catch (e) {
      _setError(e.toString());
    } finally {
      _setLoading(false);
    }
  }

  // Helper methods
  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String error) {
    _error = error;
    notifyListeners();
  }

  void _clearError() {
    _error = null;
    notifyListeners();
  }

  void clearCurrent() {
    _current = null;
    notifyListeners();
  }

  void resetPagination() {
    _page = 1;
    _hasMore = true;
    _items = [];
    notifyListeners();
  }
}
