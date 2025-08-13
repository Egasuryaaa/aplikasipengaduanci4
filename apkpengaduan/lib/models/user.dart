class User {
  final int id;
  final String uuid;
  final String name;
  final String email;
  final String? phone;
  final int? instansiId;
  final String role;
  final bool isActive;
  final String? emailVerifiedAt;
  final String? lastLogin;
  final String createdAt;
  final String updatedAt;

  User({
    required this.id,
    required this.uuid,
    required this.name,
    required this.email,
    this.phone,
    this.instansiId,
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
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      emailVerifiedAt: json['email_verified_at'],
      lastLogin: json['last_login'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'uuid': uuid,
      'name': name,
      'email': email,
      'phone': phone,
      'instansi_id': instansiId,
      'role': role,
      'is_active': isActive,
      'email_verified_at': emailVerifiedAt,
      'last_login': lastLogin,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}

// Auth response model
class AuthResponse {
  final bool status;
  final String message;
  final User? user;
  final String? token;

  AuthResponse({
    required this.status,
    required this.message,
    this.user,
    this.token,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      status: json['status'],
      message: json['message'],
      user:
          json['data'] != null && json['data']['user'] != null
              ? User.fromJson(json['data']['user'])
              : null,
      token: json['data'] != null ? json['data']['token'] : null,
    );
  }
}
