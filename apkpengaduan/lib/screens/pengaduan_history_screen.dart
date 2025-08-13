import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../providers/pengaduan_provider.dart';
import '../widgets/status_badge.dart';
import 'pengaduan_detail_screen.dart';

class PengaduanHistoryScreen extends StatefulWidget {
  const PengaduanHistoryScreen({super.key});

  @override
  State<PengaduanHistoryScreen> createState() => _PengaduanHistoryScreenState();
}

class _PengaduanHistoryScreenState extends State<PengaduanHistoryScreen> {
  final _scrollController = ScrollController();
  final _searchController = TextEditingController();
  String? _selectedStatus;
  bool _isSearching = false;

  // Status filter options
  final List<Map<String, dynamic>> _statusFilters = [
    {'label': 'Semua Status', 'value': null},
    {'label': 'Pending', 'value': 'pending'},
    {'label': 'Diproses', 'value': 'diproses'},
    {'label': 'Selesai', 'value': 'selesai'},
    {'label': 'Ditolak', 'value': 'ditolak'},
  ];

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_scrollListener);
    _loadData();
  }

  @override
  void dispose() {
    _scrollController.removeListener(_scrollListener);
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  // Load initial data
  Future<void> _loadData() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    await pengaduanProvider.fetchList(
      refresh: true,
      status: _selectedStatus,
      search: _searchController.text.isNotEmpty ? _searchController.text : null,
    );
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
        pengaduanProvider.fetchList(
          status: _selectedStatus,
          search:
              _searchController.text.isNotEmpty ? _searchController.text : null,
        );
      }
    }
  }

  // Handle refresh
  Future<void> _handleRefresh() async {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    await pengaduanProvider.fetchList(
      refresh: true,
      status: _selectedStatus,
      search: _searchController.text.isNotEmpty ? _searchController.text : null,
    );
  }

  // Handle search
  void _performSearch() {
    final pengaduanProvider = Provider.of<PengaduanProvider>(
      context,
      listen: false,
    );
    pengaduanProvider.resetPagination();
    pengaduanProvider.fetchList(
      refresh: true,
      status: _selectedStatus,
      search: _searchController.text.isNotEmpty ? _searchController.text : null,
    );
  }

  @override
  Widget build(BuildContext context) {
    final pengaduanProvider = Provider.of<PengaduanProvider>(context);
    final pengaduanList = pengaduanProvider.items;

    return Scaffold(
      appBar: AppBar(
        title:
            _isSearching
                ? TextField(
                  controller: _searchController,
                  decoration: const InputDecoration(
                    hintText: 'Cari nomor atau deskripsi...',
                    border: InputBorder.none,
                  ),
                  onSubmitted: (_) => _performSearch(),
                  autofocus: true,
                )
                : const Text('Riwayat Pengaduan'),
        actions: [
          // Search toggle button
          IconButton(
            icon: Icon(_isSearching ? Icons.close : Icons.search),
            onPressed: () {
              setState(() {
                _isSearching = !_isSearching;
                if (!_isSearching) {
                  _searchController.clear();
                  _performSearch();
                }
              });
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Status filter
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                const Text('Filter status: '),
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
                      pengaduanProvider.fetchList(
                        refresh: true,
                        status: value,
                        search:
                            _searchController.text.isNotEmpty
                                ? _searchController.text
                                : null,
                      );
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
                    ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(
                            Icons.history,
                            size: 80,
                            color: Colors.grey,
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'Tidak ada pengaduan ditemukan',
                            style: TextStyle(
                              fontSize: 16,
                              color: Colors.grey[600],
                            ),
                          ),
                          if (_searchController.text.isNotEmpty ||
                              _selectedStatus != null) ...[
                            const SizedBox(height: 8),
                            ElevatedButton.icon(
                              icon: const Icon(Icons.refresh),
                              label: const Text('Reset Filter'),
                              onPressed: () {
                                setState(() {
                                  _searchController.clear();
                                  _selectedStatus = null;
                                  _isSearching = false;
                                });
                                pengaduanProvider.resetPagination();
                                pengaduanProvider.fetchList(refresh: true);
                              },
                            ),
                          ],
                        ],
                      ),
                    )
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
    );
  }

  Widget _buildPengaduanItem(dynamic pengaduan) {
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
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
                  StatusBadge(status: pengaduan.status),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                pengaduan.kategoriNama ?? 'Tanpa Kategori',
                style: TextStyle(color: Colors.grey[600]),
              ),
              const SizedBox(height: 8),
              Text(
                pengaduan.deskripsi,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Icon(
                        Icons.access_time,
                        size: 16,
                        color: Colors.grey,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        formattedDate,
                        style: TextStyle(color: Colors.grey[600], fontSize: 12),
                      ),
                    ],
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder:
                              (context) =>
                                  PengaduanDetailScreen(id: pengaduan.id),
                        ),
                      );
                    },
                    child: const Text('Detail'),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
