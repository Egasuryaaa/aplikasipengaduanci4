class Pengaduan {
  final String id;
  final String uuid;
  final String nomorPengaduan;
  final String userId;
  final String instansiId;
  final String kategoriId;
  final String deskripsi;
  final List<dynamic> fotoBukti;
  final String status;
  final String? tanggalSelesai;
  final String? keteranganAdmin;
  final String createdAt;
  final String updatedAt;
  final String userName;
  final String userEmail;
  final String userPhone;
  final String instansiNama;
  final String kategoriNama;

  Pengaduan({
    required this.id,
    required this.uuid,
    required this.nomorPengaduan,
    required this.userId,
    required this.instansiId,
    required this.kategoriId,
    required this.deskripsi,
    required this.fotoBukti,
    required this.status,
    this.tanggalSelesai,
    this.keteranganAdmin,
    required this.createdAt,
    required this.updatedAt,
    required this.userName,
    required this.userEmail,
    required this.userPhone,
    required this.instansiNama,
    required this.kategoriNama,
  });

  factory Pengaduan.fromJson(Map<String, dynamic> json) {
    return Pengaduan(
      id: json['id'],
      uuid: json['uuid'],
      nomorPengaduan: json['nomor_pengaduan'],
      userId: json['user_id'],
      instansiId: json['instansi_id'],
      kategoriId: json['kategori_id'],
      deskripsi: json['deskripsi'],
      fotoBukti: json['foto_bukti'] ?? [],
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
}
