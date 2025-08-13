<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\UserModel;

class AuthController extends ResourceController
{
    protected $userModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function register()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama harus diisi',
                    'min_length' => 'Nama minimal 3 karakter',
                    'max_length' => 'Nama maksimal 255 karakter'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah terdaftar'
                ]
            ],
            'phone' => [
                'rules' => 'permit_empty|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'numeric' => 'Nomor telepon harus berupa angka',
                    'min_length' => 'Nomor telepon minimal 10 digit',
                    'max_length' => 'Nomor telepon maksimal 15 digit'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 8 karakter'
                ]
            ],
            'password_confirm' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password harus diisi',
                    'matches' => 'Konfirmasi password tidak cocok'
                ]
            ],
            'instansi_id' => [
                'rules' => 'permit_empty|integer',
                'errors' => [
                    'integer' => 'ID instansi tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'password' => $this->request->getPost('password'),
            'instansi_id' => $this->request->getPost('instansi_id'),
            'role' => 'user',
            'is_active' => true
        ];

        try {
            $userId = $this->userModel->insert($userData);

            if ($userId) {
                // Get user data for response
                $user = $this->userModel->find($userId);
                unset($user['password']);

                // Generate JWT token
                $token = $this->generateJWT($user);

                // Create notification for welcome
                $notificationModel = new \App\Models\NotificationModel();
                $notificationModel->createNotification(
                    $userId,
                    'Selamat Datang!',
                    'Akun Anda telah berhasil dibuat. Sekarang Anda dapat mengajukan pengaduan.',
                    'success'
                );

                log_message('info', 'User registered: ' . $user['email']);

                return $this->respond([
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'data' => [
                        'user' => $user,
                        'token' => $token['access_token'],
                        'refresh_token' => $token['refresh_token'],
                        'expires_in' => $token['expires_in']
                    ]
                ], 201);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Gagal membuat akun'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function login()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid'
                ]
            ],
            'password' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Password harus diisi'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Find user by email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return $this->respond([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            log_message('security', 'Failed API login attempt for email: ' . $email . ' from IP: ' . $this->request->getIPAddress());
            return $this->respond([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Check if user is active
        if (!$user['is_active']) {
            return $this->respond([
                'success' => false,
                'message' => 'Akun Anda tidak aktif'
            ], 401);
        }

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Remove password from response
        unset($user['password']);

        // Generate JWT token
        $token = $this->generateJWT($user);

        log_message('info', 'API user logged in: ' . $user['email']);

        return $this->respond([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
                'expires_in' => $token['expires_in']
            ]
        ]);
    }

    public function refresh()
    {
        $refreshToken = $this->request->getPost('refresh_token');

        if (!$refreshToken) {
            return $this->respond([
                'success' => false,
                'message' => 'Refresh token required'
            ], 400);
        }

        try {
            $key = getenv('JWT_SECRET_KEY') ?: 'your-secret-key-change-in-production';
            $decoded = JWT::decode($refreshToken, new Key($key, 'HS256'));

            // Verify this is a refresh token
            if (!isset($decoded->type) || $decoded->type !== 'refresh') {
                return $this->respond([
                    'success' => false,
                    'message' => 'Invalid refresh token'
                ], 401);
            }

            // Get user data
            $user = $this->userModel->find($decoded->user_id);
            if (!$user || !$user['is_active']) {
                return $this->respond([
                    'success' => false,
                    'message' => 'User account is inactive'
                ], 401);
            }

            unset($user['password']);

            // Generate new tokens
            $token = $this->generateJWT($user);

            return $this->respond([
                'success' => true,
                'message' => 'Token refreshed',
                'data' => [
                    'token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'],
                    'expires_in' => $token['expires_in']
                ]
            ]);

        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Invalid refresh token'
            ], 401);
        }
    }

    public function profile()
    {
        $userId = $this->request->user_id;
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->respond([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        unset($user['password']);

        // Get instansi data if exists
        if ($user['instansi_id']) {
            $instansiModel = new \App\Models\InstansiModel();
            $instansi = $instansiModel->find($user['instansi_id']);
            $user['instansi'] = $instansi;
        }

        return $this->respond([
            'success' => true,
            'message' => 'Profile data retrieved',
            'data' => $user
        ]);
    }

    public function updateProfile()
    {
        $userId = $this->request->user_id;
        
        $validation = \Config\Services::validation();
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'phone' => 'permit_empty|numeric|min_length[10]|max_length[15]',
            'instansi_id' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

        $updateData = [
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'instansi_id' => $this->request->getPost('instansi_id')
        ];

        try {
            $result = $this->userModel->update($userId, $updateData);

            if ($result) {
                $user = $this->userModel->find($userId);
                unset($user['password']);

                return $this->respond([
                    'success' => true,
                    'message' => 'Profile berhasil diperbarui',
                    'data' => $user
                ]);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Gagal memperbarui profile'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function changePassword()
    {
        $userId = $this->request->user_id;
        
        $validation = \Config\Services::validation();
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

        $user = $this->userModel->find($userId);
        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return $this->respond([
                'success' => false,
                'message' => 'Password saat ini salah'
            ], 400);
        }

        try {
            $result = $this->userModel->update($userId, [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT)
            ]);

            if ($result) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Password berhasil diubah'
                ]);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Gagal mengubah password'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Password change error: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function logout()
    {
        // In a real implementation, you might want to blacklist the token
        // For now, we'll just return success
        return $this->respond([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    private function generateJWT($user)
    {
        $key = getenv('JWT_SECRET_KEY') ?: 'your-secret-key-change-in-production';
        $issuedAt = time();
        $expiresAt = $issuedAt + (60 * 60 * 24); // 24 hours
        $refreshExpiresAt = $issuedAt + (60 * 60 * 24 * 30); // 30 days

        // Access token payload
        $payload = [
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'user_id' => $user['id'],
            'user_uuid' => $user['uuid'],
            'user_role' => $user['role'],
            'type' => 'access'
        ];

        // Refresh token payload
        $refreshPayload = [
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => $issuedAt,
            'exp' => $refreshExpiresAt,
            'user_id' => $user['id'],
            'type' => 'refresh'
        ];

        $accessToken = JWT::encode($payload, $key, 'HS256');
        $refreshToken = JWT::encode($refreshPayload, $key, 'HS256');

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresAt - $issuedAt
        ];
    }
}
