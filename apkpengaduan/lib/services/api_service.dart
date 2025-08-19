import 'dart:developer';

import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:file_picker/file_picker.dart';
import 'package:http_parser/http_parser.dart';
import '../models/pengaduan.dart';
import '../models/kategori.dart';
import 'package:image_picker/image_picker.dart';

class ApiService {
  static const String baseUrl = 'http://localhost/serverpengaduan/api';
  final Dio _dio = Dio();

  // Singleton pattern
  static final ApiService _instance = ApiService._internal();
  factory ApiService() {
    log('[ApiService] Singleton instance requested - reusing same instance');
    return _instance;
  }

  ApiService._internal() {
    log('[ApiService] Creating new singleton instance');
    _dio.options.baseUrl = baseUrl;
    _dio.options.connectTimeout = const Duration(seconds: 15);
    _dio.options.receiveTimeout = const Duration(seconds: 15);
    _dio.options.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    // Add interceptor for debugging and error handling
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          log('[ApiService] ${options.method} ${options.uri}');
          log('[ApiService] Headers: ${options.headers}');
          handler.next(options);
        },
        onResponse: (response, handler) {
          log('[ApiService] Response: ${response.statusCode}');
          handler.next(response);
        },
        onError: (DioException e, handler) {
          log('[ApiService] Error: ${e.message}');
          log('[ApiService] Response: ${e.response?.data}');
          if (e.response?.statusCode == 401) {
            log(
              '[ApiService] Unauthorized - Token may be invalid or expired',
            );
          }
          handler.next(e);
        },
      ),
    );

    _initializeToken();
  }

  Future<void> _initializeToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');
      log('[ApiService] _initializeToken - Checking for stored token...');
      log('[ApiService] Token found in storage: ${token != null}');
      if (token != null && token.isNotEmpty) {
        _dio.options.headers['Authorization'] = 'Bearer $token';
        log('[ApiService] Token successfully set in headers');
        log(
          '[ApiService] Current Authorization header: ${_dio.options.headers['Authorization']}',
        );
      } else {
        log(
          '[ApiService] WARNING: No token found in storage - user may need to login',
        );
        _dio.options.headers.remove('Authorization');
      }
    } catch (e) {
      log('[ApiService] ERROR initializing token: $e');
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

  Future<void> setToken(String token) async {
    // Set header immediately
    _dio.options.headers['Authorization'] = 'Bearer $token';
    // Save token to SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    log('[ApiService] setToken() saved token and set Authorization header');
  }

  Future<void> clearToken() async {
    // Remove authorization header
    _dio.options.headers.remove('Authorization');
    // Remove token from SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    log('[ApiService] clearToken() removed token and Authorization header');
  }

  // Method untuk validasi token
  Future<bool> validateToken() async {
    try {
      final response = await _dio.get('/user');
      return response.statusCode == 200;
    } catch (e) {
      log('[ApiService] Token validation failed: $e');
      return false;
    }
  }

  Future<Map<String, dynamic>> getJumlahPengaduan() async {
    await _initializeToken();
    final response = await _dio.get(
      '/pengaduan/jumlah',
    ); // Pastikan endpoint sesuai
    return response.data;
  }

  // CRUD Pengaduan - Updated methods
  Future<Map<String, dynamic>> getPengaduanListWithMeta({
    int page = 1,
    String? search,
    String? status,
    String? dateFrom,
    String? dateTo,
  }) async {
    await _initializeToken();

    Map<String, dynamic> queryParams = {'page': page};
    if (search != null) queryParams['search'] = search;
    if (status != null) queryParams['status'] = status;
    if (dateFrom != null) queryParams['date_from'] = dateFrom;
    if (dateTo != null) queryParams['date_to'] = dateTo;

    final response = await _dio.get('/pengaduan', queryParameters: queryParams);
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

  Future<Map<String, dynamic>> getPengaduanDetailRaw(String id) async {
    await _initializeToken();
    final response = await _dio.get('/pengaduan/$id');
    return response.data;
  }

  Future<Pengaduan?> getPengaduanDetail(String id) async {
    await _initializeToken();
    final response = await _dio.get('/pengaduan/$id');
    if (response.data['status'] == true) {
      return Pengaduan.fromJson(response.data['data']['pengaduan']);
    }
    return null;
  }

  Future<Map<String, dynamic>> createPengaduanNew({
    required String instansiId,
    required String kategoriId,
    required String deskripsi,
    List<String>? fotoBukti,
  }) async {
    await _initializeToken();

    final data = <String, dynamic>{
      'instansi_id': instansiId,
      'kategori_id': kategoriId,
      'deskripsi': deskripsi,
    };

    if (fotoBukti != null && fotoBukti.isNotEmpty) {
      data['foto_bukti'] = fotoBukti;
    }

    final response = await _dio.post('/pengaduan', data: data);
    return response.data;
  }

  Future<Map<String, dynamic>> createPengaduan(
    Map<String, dynamic> data,
    List fotoBukti,
  ) async {
    log('[ApiService] createPengaduan() called with data: $data');
    log(
      '[ApiService] createPengaduan() called with ${fotoBukti.length} files',
    );
    await _initializeToken();
    log('[ApiService] createPengaduan() - Token initialization complete');
    log(
      '[ApiService] createPengaduan() - Current headers: ${_dio.options.headers}',
    );
    FormData formData = FormData.fromMap(data);

    // Tambah file ke form data
    if (fotoBukti.isNotEmpty) {
      log(
        '[ApiService] createPengaduan() - Processing ${fotoBukti.length} files',
      );
      for (var i = 0; i < fotoBukti.length; i++) {
        var file = fotoBukti[i];
        log(
          '[ApiService] createPengaduan() - Processing file $i: ${file.runtimeType}',
        );

        if (kIsWeb) {
          // Web: PlatformFile dari file_picker
          var platformFile = file as PlatformFile;
          log(
            '[ApiService] createPengaduan() - Web file: ${platformFile.name}, ${platformFile.size} bytes',
          );
          formData.files.add(
            MapEntry(
              'foto_bukti',
              MultipartFile.fromBytes(
                platformFile.bytes!,
                filename: platformFile.name,
                contentType: MediaType(
                  'image',
                  platformFile.extension == 'png' ? 'png' : 'jpeg',
                ),
              ),
            ),
          );
          log('[ApiService] createPengaduan() - Added web file to FormData');
        } else {
          // Mobile: XFile dari image_picker
          var xFile = file as XFile;
          log(
            '[ApiService] createPengaduan() - Mobile file: ${xFile.name}, path: ${xFile.path}',
          );
          formData.files.add(
            MapEntry(
              'foto_bukti',
              await MultipartFile.fromFile(xFile.path, filename: xFile.name),
            ),
          );
          log(
            '[ApiService] createPengaduan() - Added mobile file to FormData',
          );
        }
      }
    } else {
      log('[ApiService] createPengaduan() - No files to upload');
    }

    final response = await _dio.post('/pengaduan', data: formData);
    return response.data;
  }

  Future<Map<String, dynamic>> updatePengaduanNew({
    required String id,
    required String instansiId,
    required String kategoriId,
    required String deskripsi,
    List<String>? fotoBukti,
  }) async {
    await _initializeToken();

    final data = <String, dynamic>{
      'instansi_id': instansiId,
      'kategori_id': kategoriId,
      'deskripsi': deskripsi,
    };

    if (fotoBukti != null) {
      data['foto_bukti'] = fotoBukti;
    }

    final response = await _dio.put('/pengaduan/$id', data: data);
    return response.data;
  }

  Future<bool> updatePengaduan(
    String id,
    Map<String, dynamic> data, [
    List? fotoBukti,
  ]) async {
    await _initializeToken();

    if (fotoBukti == null || fotoBukti.isEmpty) {
      final response = await _dio.put('/pengaduan/$id', data: data);
      return response.data['status'] == true;
    } else {
      FormData formData = FormData.fromMap(data);

      // Handle foto bukti di web dan mobile
      for (var i = 0; i < fotoBukti.length; i++) {
        final file = fotoBukti[i];

        if (kIsWeb) {
          // Web: PlatformFile dari file_picker
          if (file is PlatformFile && file.bytes != null) {
            formData.files.add(
              MapEntry(
                'foto_bukti',
                MultipartFile.fromBytes(
                  file.bytes!,
                  filename: file.name,
                  contentType: MediaType(
                    'image',
                    file.extension == 'png' ? 'png' : 'jpeg',
                  ),
                ),
              ),
            );
          }
        } else {
          // Mobile: XFile dari image_picker
          if (file is XFile) {
            formData.files.add(
              MapEntry(
                'foto_bukti',
                await MultipartFile.fromFile(file.path, filename: file.name),
              ),
            );
          }
        }
      }

      final response = await _dio.put('/pengaduan/$id', data: formData);
      return response.data['status'] == true;
    }
  }

  Future<Map<String, dynamic>> deletePengaduanNew(String id) async {
    await _initializeToken();
    final response = await _dio.delete('/pengaduan/$id');
    return response.data;
  }

  Future<bool> deletePengaduan(String id) async {
    await _initializeToken();
    try {
      final response = await _dio.delete('/pengaduan/$id');
      return response.data['status'] == true;
    } on DioException catch (e) {
      if (e.response?.statusCode == 400) {
        // Extract error message from the response
        final message = e.response?.data['message'] ?? 'Bad request';
        throw Exception(message);
      }
      rethrow;
    }
  }

  // Kategori methods
  Future<List<Kategori>> getKategoriList() async {
    await _initializeToken();
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

  Future<Map<String, dynamic>> getUserDetail() async {
    await _initializeToken();
    final response = await _dio.get('/user');
    return response.data;
  }
}
