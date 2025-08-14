import 'package:flutter/material.dart';
import '../models/kategori.dart';
import '../services/api_service.dart';

class KategoriProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<Kategori> kategoriList = [];
  bool isLoading = false;
  String? error;

  Future<void> fetchKategoriList() async {
    isLoading = true;
    notifyListeners();
    try {
      kategoriList = await _apiService.getKategoriList();
      error = null;
    } catch (e) {
      error = e.toString();
    }
    isLoading = false;
    notifyListeners();
  }

  Kategori? getKategoriById(String id) {
    try {
      return kategoriList.firstWhere((kategori) => kategori.id == id);
    } catch (e) {
      return null;
    }
  }
}
