class User {
  final String id;
  final String uuid;
  final String name;
  final String email;
  final String phone;
  final String instansiId;
  final String role;
  final String isActive;
  final String? emailVerifiedAt;
  final String? lastLogin;
  final String createdAt;
  final String updatedAt;

  User({
    required this.id,
    required this.uuid,
    required this.name,
    required this.email,
    required this.phone,
    required this.instansiId,
    required this.role,
    required this.isActive,
    this.emailVerifiedAt,
    this.lastLogin,
    required this.createdAt,
    required this.updatedAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      uuid: json['uuid'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      instansiId: json['instansi_id'],
      role: json['role'],
      isActive: json['is_active'],
      emailVerifiedAt: json['email_verified_at'],
      lastLogin: json['last_login'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}
