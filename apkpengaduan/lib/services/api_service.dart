import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/pengaduan.dart';
import '../models/kategori.dart';

class ApiService {
  static const String baseUrl = 'http://localhost/serverpengaduan/api';
  final Dio _dio = Dio();

  ApiService() {
    _dio.options.baseUrl = baseUrl;
    _dio.options.connectTimeout = const Duration(seconds: 15);
    _dio.options.receiveTimeout = const Duration(seconds: 15);
    _dio.options.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    _initializeToken();
  }

  Future<void> _initializeToken() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    if (token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    }
  }

  Future<Map<String, dynamic>> login({
    required String emailOrPhone,
    required String password,
  }) async {
    final response = await _dio.post(
      '/login',
      data: {'email_or_phone': emailOrPhone, 'password': password},
    );
    return response.data;
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    final response = await _dio.post(
      '/register',
      data: {
        'name': name,
        'email': email,
        'phone': phone,
        'password': password,
      },
    );
    return response.data;
  }

  void setToken(String token) async {
    _dio.options.headers['Authorization'] = 'Bearer $token';
    // Simpan token ke SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  Future<Map<String, dynamic>> getJumlahPengaduan() async {
    await _initializeToken();
    final response = await _dio.get(
      '/pengaduan/jumlah',
    ); // Pastikan endpoint sesuai
    return response.data;
  }

  Future<List<Pengaduan>> getPengaduanList() async {
    await _initializeToken();
    final response = await _dio.get('/pengaduan');
    if (response.data['status'] == true) {
      final items = response.data['data']['items'] as List;
      return items.map((e) => Pengaduan.fromJson(e)).toList();
    }
    return [];
  }

  Future<Pengaduan?> getPengaduanDetail(String id) async {
    final response = await _dio.get('/pengaduan/$id');
    if (response.data['status'] == true) {
      return Pengaduan.fromJson(response.data['data']['pengaduan']);
    }
    return null;
  }

  Future<bool> createPengaduan(Map<String, dynamic> data) async {
    final response = await _dio.post('/pengaduan', data: data);
    return response.data['status'] == true;
  }

  Future<bool> updatePengaduan(String id, Map<String, dynamic> data) async {
    final response = await _dio.put('/pengaduan/$id', data: data);
    return response.data['status'] == true;
  }

  Future<bool> deletePengaduan(String id) async {
    final response = await _dio.delete('/pengaduan/$id');
    return response.data['status'] == true;
  }

  // Kategori methods
  Future<List<Kategori>> getKategoriList() async {
    final response = await _dio.get('/kategori');
    if (response.data['status'] == true) {
      final items = response.data['data']['kategori'] as List;
      return items.map((e) => Kategori.fromJson(e)).toList();
    }
    return [];
  }

  Future<Map<String, dynamic>> getPengaduanStatistik() async {
    // Pastikan token tersedia sebelum request
    await _initializeToken();
    final response = await _dio.get('/pengaduan/statistic');
    return response.data;
  }
}
