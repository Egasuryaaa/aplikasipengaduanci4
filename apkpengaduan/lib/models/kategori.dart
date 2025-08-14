class Kategori {
  final String id;
  final String nama;
  final String deskripsi;
  final String isActive;
  final String createdAt;
  final String updatedAt;

  Kategori({
    required this.id,
    required this.nama,
    required this.deskripsi,
    required this.isActive,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Kategori.fromJson(Map<String, dynamic> json) {
    return Kategori(
      id: json['id'],
      nama: json['nama'],
      deskripsi: json['deskripsi'],
      isActive: json['is_active'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}
