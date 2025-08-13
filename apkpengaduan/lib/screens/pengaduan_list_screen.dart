import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../providers/auth_provider.dart';
import '../providers/pengaduan_provider.dart';
import '../models/pengaduan.dart';
import 'login_screen.dart';
import 'pengaduan_detail_screen.dart';
import 'create_pengaduan_screen.dart';

class PengaduanListScreen extends StatefulWidget {
  const PengaduanListScreen({super.key});

  @override
  State<PengaduanListScreen> createState() => _PengaduanListScreenState();
}

class _PengaduanListScreenState extends State<PengaduanListScreen> {
  final _scrollController = ScrollController();
  String? _selectedStatus;
  final List<Map<String, dynamic>> _statusFilters = [
    {'value': null, 'label': 'All'},
    {'value': 'pending', 'label': 'Pending'},
    {'value': 'diproses', 'label': 'Processing'},
    {'value': 'selesai', 'label': 'Completed'},
    {'value': 'ditolak', 'label': 'Rejected'},
  ];

  @override
  void initState() {
    super.initState();
    // Fetch data when screen initializes
    _loadData();

    // Add scroll listener for pagination
    _scrollController.addListener(_scrollListener);
  }

  @override
  void dispose() {
    _scrollController.removeListener(_scrollListener);
    _scrollController.dispose();
    super.dispose();
  }

  // Load initial data
  Future<void> _loadData() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    await pengaduanProvider.fetchList(refresh: true, status: _selectedStatus);
  }

  // Handle scroll for pagination
  void _scrollListener() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      // Load more data when near the bottom
      final pengaduanProvider = Provider.of<PengaduanProvider>(
        context,
        listen: false,
      );
      if (!pengaduanProvider.isLoading && pengaduanProvider.hasMore) {
        pengaduanProvider.fetchList(status: _selectedStatus);
      }
    }
  }

  // Handle refresh
  Future<void> _handleRefresh() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    await pengaduanProvider.fetchList(refresh: true, status: _selectedStatus);
  }

  // Handle logout
  Future<void> _logout() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.logout();

    if (success && mounted) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (context) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final pengaduanProvider = Provider.of<PengaduanProvider>(context);
    final pengaduanList = pengaduanProvider.items;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengaduan List'),
        actions: [
          IconButton(icon: const Icon(Icons.exit_to_app), onPressed: _logout),
        ],
      ),
      body: Column(
        children: [
          // Status filter
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                const Text('Filter by status: '),
                const SizedBox(width: 8),
                Expanded(
                  child: DropdownButton<String?>(
                    isExpanded: true,
                    value: _selectedStatus,
                    onChanged: (value) {
                      setState(() {
                        _selectedStatus = value;
                      });
                      pengaduanProvider.resetPagination();
                      pengaduanProvider.fetchList(refresh: true, status: value);
                    },
                    items:
                        _statusFilters
                            .map(
                              (filter) => DropdownMenuItem<String?>(
                                value: filter['value'],
                                child: Text(filter['label']),
                              ),
                            )
                            .toList(),
                  ),
                ),
              ],
            ),
          ),

          // Pengaduan list
          Expanded(
            child:
                pengaduanProvider.isLoading && pengaduanList.isEmpty
                    ? const Center(child: CircularProgressIndicator())
                    : pengaduanList.isEmpty
                    ? const Center(child: Text('No pengaduan found'))
                    : RefreshIndicator(
                      onRefresh: _handleRefresh,
                      child: ListView.builder(
                        controller: _scrollController,
                        itemCount:
                            pengaduanList.length +
                            (pengaduanProvider.hasMore ? 1 : 0),
                        itemBuilder: (context, index) {
                          if (index == pengaduanList.length) {
                            return const Center(
                              child: Padding(
                                padding: EdgeInsets.all(8.0),
                                child: CircularProgressIndicator(),
                              ),
                            );
                          }

                          final pengaduan = pengaduanList[index];
                          return _buildPengaduanItem(pengaduan);
                        },
                      ),
                    ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (context) => const CreatePengaduanScreen(),
            ),
          );
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildPengaduanItem(Pengaduan pengaduan) {
    final formattedDate = DateFormat(
      'dd MMM yyyy, HH:mm',
    ).format(DateTime.parse(pengaduan.createdAt));

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: InkWell(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (context) => PengaduanDetailScreen(id: pengaduan.id),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      pengaduan.nomorPengaduan,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: Color(pengaduan.statusColor),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      pengaduan.statusText,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                pengaduan.deskripsi,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.category, size: 16),
                  const SizedBox(width: 4),
                  Text(pengaduan.kategoriNama ?? 'Unknown Category'),
                  const Spacer(),
                  const Icon(Icons.access_time, size: 16),
                  const SizedBox(width: 4),
                  Text(formattedDate, style: const TextStyle(fontSize: 12)),
                ],
              ),
              if (pengaduan.fotoBukti != null &&
                  pengaduan.fotoBukti!.isNotEmpty) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.photo, size: 16),
                    const SizedBox(width: 4),
                    Text('${pengaduan.fotoBukti!.length} Photos'),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
