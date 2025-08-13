import 'dart:io';

import 'package:dio/dio.dart';

class ApiService {
  // Configure baseUrl to point to the backend
  static const String baseUrl = 'http://localhost/serverpengaduan/public/api';
  final Dio _dio = Dio();

  ApiService() {
    _dio.options.baseUrl = baseUrl;
    _dio.options.connectTimeout = const Duration(seconds: 10);
    _dio.options.receiveTimeout = const Duration(seconds: 10);

    // Set default content type
    _dio.options.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    // Add interceptor for logging
    _dio.interceptors.add(LogInterceptor(responseBody: true));
  }

  // Add auth token to headers
  void setToken(String token) {
    _dio.options.headers['Authorization'] = 'Bearer $token';
  }

  // Clear auth token
  void clearToken() {
    _dio.options.headers.remove('Authorization');
  }

  // Register a new user
  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    try {
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
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Login user
  Future<Map<String, dynamic>> login({
    required String emailOrPhone,
    required String password,
  }) async {
    try {
      final response = await _dio.post(
        '/login',
        data: {'email_or_phone': emailOrPhone, 'password': password},
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Logout user
  Future<Map<String, dynamic>> logout() async {
    try {
      final response = await _dio.post('/logout');
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get current user details
  Future<Map<String, dynamic>> getUser() async {
    try {
      final response = await _dio.get('/user');
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get list of pengaduan with pagination
  Future<Map<String, dynamic>> getPengaduan({
    int page = 1,
    String? search,
    String? status,
    String? dateFrom,
    String? dateTo,
  }) async {
    try {
      Map<String, dynamic> queryParameters = {'page': page};

      if (search != null && search.isNotEmpty) {
        queryParameters['search'] = search;
      }

      if (status != null && status.isNotEmpty) {
        queryParameters['status'] = status;
      }

      if (dateFrom != null && dateFrom.isNotEmpty) {
        queryParameters['date_from'] = dateFrom;
      }

      if (dateTo != null && dateTo.isNotEmpty) {
        queryParameters['date_to'] = dateTo;
      }

      final response = await _dio.get(
        '/pengaduan',
        queryParameters: queryParameters,
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get pengaduan detail by ID
  Future<Map<String, dynamic>> getPengaduanDetail(int id) async {
    try {
      final response = await _dio.get('/pengaduan/$id');
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Create new pengaduan
  Future<Map<String, dynamic>> createPengaduan({
    required String judul,
    required String isi,
    required int kategoriId,
    String? lokasi,
    File? foto,
  }) async {
    try {
      FormData formData = FormData.fromMap({
        'judul': judul,
        'isi': isi,
        'kategori_id': kategoriId,
        'lokasi': lokasi,
      });

      // Add foto if provided
      if (foto != null) {
        formData.files.add(
          MapEntry(
            'foto',
            await MultipartFile.fromFile(
              foto.path,
              filename: foto.path.split('/').last,
            ),
          ),
        );
      }

      final response = await _dio.post('/pengaduan', data: formData);
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Update existing pengaduan
  Future<Map<String, dynamic>> updatePengaduan({
    required int id,
    String? judul,
    String? isi,
    int? kategoriId,
    String? lokasi,
    File? foto,
  }) async {
    try {
      FormData formData = FormData.fromMap({
        if (judul != null) 'judul': judul,
        if (isi != null) 'isi': isi,
        if (kategoriId != null) 'kategori_id': kategoriId,
        if (lokasi != null) 'lokasi': lokasi,
      });

      // Add foto if provided
      if (foto != null) {
        formData.files.add(
          MapEntry(
            'foto',
            await MultipartFile.fromFile(
              foto.path,
              filename: foto.path.split('/').last,
            ),
          ),
        );
      }

      final response = await _dio.put('/pengaduan/$id', data: formData);
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Add status/history to pengaduan
  Future<Map<String, dynamic>> addPengaduanStatus({
    required int id,
    required String status,
    String? keterangan,
  }) async {
    try {
      final response = await _dio.post(
        '/pengaduan/$id/status',
        data: {'status': status, 'keterangan': keterangan},
      );
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Get list of categories
  Future<Map<String, dynamic>> getKategori() async {
    try {
      final response = await _dio.get('/kategori');
      return response.data;
    } catch (e) {
      throw _handleError(e);
    }
  }

  // Handle error responses
  Exception _handleError(dynamic error) {
    if (error is DioException) {
      if (error.response != null) {
        // Try to parse error message from response
        try {
          final data = error.response!.data;
          if (data is Map && data.containsKey('message')) {
            return Exception(data['message']);
          }
        } catch (_) {}

        // If we can't parse the error, return the status code
        return Exception(
          'Error ${error.response!.statusCode}: ${error.response!.statusMessage}',
        );
      } else if (error.type == DioExceptionType.connectionError) {
        // This might be a CORS error
        return Exception(
          'Connection error (possible CORS issue): ${error.message}',
        );
      } else if (error.error is SocketException) {
        return Exception('Network error: Check your internet connection');
      } else {
        return Exception('Request failed: ${error.message} (${error.type})');
      }
    }
    return Exception('Unknown error occurred: $error');
  }
}
