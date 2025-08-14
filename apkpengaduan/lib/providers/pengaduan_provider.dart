import 'package:flutter/material.dart';
import '../models/pengaduan.dart';
import '../services/api_service.dart';

class PengaduanProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<Pengaduan> pengaduanList = [];
  Pengaduan? pengaduanDetail;
  bool isLoading = false;
  String? error;

  Future<void> fetchPengaduanList() async {
    isLoading = true;
    notifyListeners();
    try {
      pengaduanList = await _apiService.getPengaduanList();
      error = null;
    } catch (e) {
      error = e.toString();
    }
    isLoading = false;
    notifyListeners();
  }

  Future<void> fetchPengaduanDetail(String id) async {
    isLoading = true;
    notifyListeners();
    try {
      pengaduanDetail = await _apiService.getPengaduanDetail(id);
      error = null;
    } catch (e) {
      error = e.toString();
    }
    isLoading = false;
    notifyListeners();
  }

  Future<bool> createPengaduan(Map<String, dynamic> data) async {
    isLoading = true;
    notifyListeners();
    try {
      final result = await _apiService.createPengaduan(data);
      await fetchPengaduanList();
      return result;
    } catch (e) {
      error = e.toString();
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updatePengaduan(String id, Map<String, dynamic> data) async {
    isLoading = true;
    notifyListeners();
    try {
      final result = await _apiService.updatePengaduan(id, data);
      await fetchPengaduanList();
      return result;
    } catch (e) {
      error = e.toString();
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> deletePengaduan(String id) async {
    isLoading = true;
    notifyListeners();
    try {
      final result = await _apiService.deletePengaduan(id);
      await fetchPengaduanList();
      return result;
    } catch (e) {
      error = e.toString();
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
