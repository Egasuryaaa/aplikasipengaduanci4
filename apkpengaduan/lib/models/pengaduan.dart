class Pengaduan {
  final int id;
  final String uuid;
  final String nomorPengaduan;
  final int userId;
  final int instansiId;
  final int kategoriId;
  final String deskripsi;
  final List<String>? fotoBukti;
  final String status;
  final String? tanggalSelesai;
  final String? keteranganAdmin;
  final String createdAt;
  final String updatedAt;

  // Relations
  final String? userName;
  final String? userEmail;
  final String? userPhone;
  final String? instansiNama;
  final String? kategoriNama;

  Pengaduan({
    required this.id,
    required this.uuid,
    required this.nomorPengaduan,
    required this.userId,
    required this.instansiId,
    required this.kategoriId,
    required this.deskripsi,
    this.fotoBukti,
    required this.status,
    this.tanggalSelesai,
    this.keteranganAdmin,
    required this.createdAt,
    required this.updatedAt,
    this.userName,
    this.userEmail,
    this.userPhone,
    this.instansiNama,
    this.kategoriNama,
  });

  factory Pengaduan.fromJson(Map<String, dynamic> json) {
    List<String>? fotoBukti;
    if (json['foto_bukti'] != null) {
      if (json['foto_bukti'] is List) {
        fotoBukti = List<String>.from(json['foto_bukti']);
      } else if (json['foto_bukti'] is String) {
        // If it's a string, use as a single item
        fotoBukti = [json['foto_bukti']];
      }
    }

    return Pengaduan(
      id: json['id'],
      uuid: json['uuid'],
      nomorPengaduan: json['nomor_pengaduan'],
      userId: json['user_id'],
      instansiId: json['instansi_id'],
      kategoriId: json['kategori_id'],
      deskripsi: json['deskripsi'],
      fotoBukti: fotoBukti,
      status: json['status'],
      tanggalSelesai: json['tanggal_selesai'],
      keteranganAdmin: json['keterangan_admin'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      userName: json['user_name'],
      userEmail: json['user_email'],
      userPhone: json['user_phone'],
      instansiNama: json['instansi_nama'],
      kategoriNama: json['kategori_nama'],
    );
  }

  // Helper method to get status text in Indonesian
  String get statusText {
    switch (status) {
      case 'pending':
        return 'Menunggu';
      case 'diproses':
        return 'Sedang Diproses';
      case 'selesai':
        return 'Selesai';
      case 'ditolak':
        return 'Ditolak';
      default:
        return status;
    }
  }

  // Helper method to get status color
  int get statusColor {
    switch (status) {
      case 'pending':
        return 0xFFFFA726; // Orange
      case 'diproses':
        return 0xFF42A5F5; // Blue
      case 'selesai':
        return 0xFF66BB6A; // Green
      case 'ditolak':
        return 0xFFEF5350; // Red
      default:
        return 0xFF9E9E9E; // Grey
    }
  }
}

class StatusHistory {
  final int id;
  final int pengaduanId;
  final String? statusOld;
  final String statusNew;
  final String? keterangan;
  final int updatedBy;
  final String createdAt;
  final String updatedAt;
  final String? updatedByName;

  StatusHistory({
    required this.id,
    required this.pengaduanId,
    this.statusOld,
    required this.statusNew,
    this.keterangan,
    required this.updatedBy,
    required this.createdAt,
    required this.updatedAt,
    this.updatedByName,
  });

  factory StatusHistory.fromJson(Map<String, dynamic> json) {
    return StatusHistory(
      id: json['id'],
      pengaduanId: json['pengaduan_id'],
      statusOld: json['status_old'],
      statusNew: json['status_new'],
      keterangan: json['keterangan'],
      updatedBy: json['updated_by'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      updatedByName: json['updated_by_name'],
    );
  }
}

class Kategori {
  final int id;
  final String nama;
  final String? deskripsi;
  final bool isActive;
  final String createdAt;
  final String updatedAt;

  Kategori({
    required this.id,
    required this.nama,
    this.deskripsi,
    required this.isActive,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Kategori.fromJson(Map<String, dynamic> json) {
    return Kategori(
      id: json['id'],
      nama: json['nama'],
      deskripsi: json['deskripsi'],
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}

// API response models
class PengaduanListResponse {
  final bool status;
  final String message;
  final List<Pengaduan> items;
  final Map<String, dynamic>? meta;

  PengaduanListResponse({
    required this.status,
    required this.message,
    required this.items,
    this.meta,
  });

  factory PengaduanListResponse.fromJson(Map<String, dynamic> json) {
    List<Pengaduan> items = [];

    if (json['data'] != null && json['data']['items'] != null) {
      items = List<Pengaduan>.from(
        json['data']['items'].map((item) => Pengaduan.fromJson(item)),
      );
    }

    return PengaduanListResponse(
      status: json['status'],
      message: json['message'],
      items: items,
      meta: json['data'] != null ? json['data']['meta'] : null,
    );
  }
}

class PengaduanDetailResponse {
  final bool status;
  final String message;
  final Pengaduan? pengaduan;
  final List<StatusHistory>? history;

  PengaduanDetailResponse({
    required this.status,
    required this.message,
    this.pengaduan,
    this.history,
  });

  factory PengaduanDetailResponse.fromJson(Map<String, dynamic> json) {
    List<StatusHistory>? history;

    if (json['data'] != null && json['data']['history'] != null) {
      history = List<StatusHistory>.from(
        json['data']['history'].map((item) => StatusHistory.fromJson(item)),
      );
    }

    return PengaduanDetailResponse(
      status: json['status'],
      message: json['message'],
      pengaduan:
          json['data'] != null && json['data']['pengaduan'] != null
              ? Pengaduan.fromJson(json['data']['pengaduan'])
              : null,
      history: history,
    );
  }
}

class KategoriListResponse {
  final bool status;
  final String message;
  final List<Kategori> kategori;

  KategoriListResponse({
    required this.status,
    required this.message,
    required this.kategori,
  });

  factory KategoriListResponse.fromJson(Map<String, dynamic> json) {
    List<Kategori> kategori = [];

    if (json['data'] != null && json['data']['kategori'] != null) {
      kategori = List<Kategori>.from(
        json['data']['kategori'].map((item) => Kategori.fromJson(item)),
      );
    }

    return KategoriListResponse(
      status: json['status'],
      message: json['message'],
      kategori: kategori,
    );
  }
}
