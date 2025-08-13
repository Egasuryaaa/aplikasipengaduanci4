import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

void main() {
  runApp(const TestApp());
}

class TestApp extends StatelessWidget {
  const TestApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'API Test',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: const ApiTestScreen(),
    );
  }
}

class ApiTestScreen extends StatefulWidget {
  const ApiTestScreen({super.key});

  @override
  State<ApiTestScreen> createState() => _ApiTestScreenState();
}

class _ApiTestScreenState extends State<ApiTestScreen> {
  final Dio _dio = Dio();
  String _response = 'No response yet';
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('API Test')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            ElevatedButton(
              onPressed: _testApi,
              child: const Text('Test Simple API'),
            ),
            const SizedBox(height: 10),
            ElevatedButton(
              onPressed: _testLogin,
              child: const Text('Test Login API'),
            ),
            const SizedBox(height: 20),
            if (_isLoading)
              const Center(child: CircularProgressIndicator())
            else
              Expanded(
                child: SingleChildScrollView(
                  child: Text(
                    _response,
                    style: const TextStyle(fontFamily: 'monospace'),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Future<void> _testApi() async {
    setState(() {
      _isLoading = true;
      _response = 'Testing API...';
    });

    try {
      final response = await _dio.get(
        'http://localhost/serverpengaduan/public/test_api.php',
      );

      setState(() {
        _response = 'Success!\n\n${response.data}';
      });
    } catch (e) {
      setState(() {
        _response = 'Error: $e';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _testLogin() async {
    setState(() {
      _isLoading = true;
      _response = 'Testing Login...';
    });

    try {
      final response = await _dio.post(
        'http://localhost/serverpengaduan/public/test_login.php',
        data: {'email_or_phone': 'user@example.com', 'password': 'password'},
        options: Options(headers: {'Content-Type': 'application/json'}),
      );

      setState(() {
        _response = 'Success!\n\n${response.data}';
      });
    } catch (e) {
      setState(() {
        _response = 'Error: $e';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
}
