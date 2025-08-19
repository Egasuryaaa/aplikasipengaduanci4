import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/common_bottom_nav.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _jumlahPengaduan = 0;
  bool _isLoading = true;

  // Statistik pengaduan
  int _pending = 0;
  int _diproses = 0;
  int _selesai = 0;
  int _ditolak = 0;

  @override
  void initState() {
    super.initState();
    _fetchStatistikPengaduan();
  }

  Future<void> _fetchStatistikPengaduan() async {
    setState(() => _isLoading = true);
    try {
      final api = ApiService();
      final response = await api.getPengaduanStatistik();
      if (response['status'] == true && response['data'] != null) {
        setState(() {
          _pending = response['data']['pending'] ?? 0;
          _diproses = response['data']['diproses'] ?? 0;
          _selesai = response['data']['selesai'] ?? 0;
          _ditolak = response['data']['ditolak'] ?? 0;
          _jumlahPengaduan = _pending + _diproses + _selesai + _ditolak;
        });
      } else {
        debugPrint('Statistik response error: $response');
      }
    } catch (e, stack) {
      debugPrint('Error fetch statistik: $e');
      debugPrint(stack.toString());
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Beranda Pengaduan'),
        elevation: 0,
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
        automaticallyImplyLeading: false, // Remove drawer button
      ),
      body:
          _isLoading
              ? const Center(child: CircularProgressIndicator())
              : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    // Welcome Card
                    Card(
                      elevation: 8,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(16),
                          gradient: LinearGradient(
                            colors: [
                              Colors.blue.shade600,
                              Colors.blue.shade400,
                            ],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                        ),
                        child: const Column(
                          children: [
                            Icon(
                              Icons.dashboard,
                              size: 60,
                              color: Colors.white,
                            ),
                            SizedBox(height: 16),
                            Text(
                              'Selamat Datang',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            Text(
                              'di Aplikasi Pengaduan',
                              style: TextStyle(
                                fontSize: 16,
                                color: Colors.white70,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Stats Card
                    Card(
                      elevation: 4,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: Colors.orange.shade100,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(
                                    Icons.list_alt,
                                    color: Colors.orange.shade600,
                                    size: 32,
                                  ),
                                ),
                                const SizedBox(width: 16),
                                const Expanded(
                                  child: Text(
                                    'Total Pengaduan',
                                    style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 16),
                            Text(
                              '$_jumlahPengaduan',
                              style: TextStyle(
                                fontSize: 48,
                                fontWeight: FontWeight.bold,
                                color: Colors.blue.shade600,
                              ),
                            ),
                            const Text(
                              'Pengaduan terdaftar',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.grey,
                              ),
                            ),
                            const SizedBox(height: 24),
                            // Statistik detail
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceAround,
                              children: [
                                _statItem('Pending', _pending, Colors.orange),
                                _statItem('Diproses', _diproses, Colors.blue),
                                _statItem('Selesai', _selesai, Colors.green),
                                _statItem('Ditolak', _ditolak, Colors.red),
                              ],
                            ),
                            const SizedBox(height: 24),
                            // Tombol Create Pengaduan
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton.icon(
                                onPressed: () {
                                  Navigator.pushNamed(
                                    context,
                                    '/create-pengaduan',
                                  );
                                },
                                icon: const Icon(Icons.add),
                                label: const Text('Buat Pengaduan Baru'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.blue.shade600,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 16,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  elevation: 2,
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
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          Navigator.pushNamed(context, '/create-pengaduan');
        },
        backgroundColor: Colors.blue.shade600,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Buat Pengaduan'),
      ),
      bottomNavigationBar: const CommonBottomNav(currentIndex: 0),
    );
  }

  Widget _statItem(String label, int value, Color color) {
    return Column(
      children: [
        Text(
          '$value',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
      ],
    );
  }
}
