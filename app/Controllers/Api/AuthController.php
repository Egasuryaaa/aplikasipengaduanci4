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
        try {
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
                    'rules' => 'required|min_length[6]',
                    'errors' => [
                        'required' => 'Password harus diisi',
                        'min_length' => 'Password minimal 6 karakter'
                    ]
                ],
                'password_confirmation' => [
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
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'data' => [
                        'errors' => $validation->getErrors()
                    ]
                ], 400);
            }

            // Verify instansi exists if provided
            if ($this->request->getPost('instansi_id')) {
                $instansiModel = new \App\Models\InstansiModel();
                $instansi = $instansiModel->find($this->request->getPost('instansi_id'));
                if (!$instansi) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Instansi tidak ditemukan',
                        'data' => null
                    ], 400);
                }
            }

            $userData = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role' => 'user',
                'instansi_id' => $this->request->getPost('instansi_id'),
                'is_active' => true
            ];

            $userId = $this->userModel->insert($userData);

            if ($userId) {
                // Get created user
                $user = $this->userModel->find($userId);
                
                // Remove password from response
                unset($user['password']);

                log_message('info', 'New user registered: ' . $user['email']);

                return response()->json([
                    'status' => true,
                    'message' => 'Registrasi berhasil',
                    'data' => [
                        'user' => $user
                    ]
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendaftarkan pengguna',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in AuthController@register: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function login()
    {
        try {
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
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'data' => [
                        'errors' => $validation->getErrors()
                    ]
                ], 400);
            }

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            // Find user by email
            $user = $this->userModel->where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau password salah',
                    'data' => null
                ], 401);
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau password salah',
                    'data' => null
                ], 401);
            }

            // Check if user is active
            if (!$user['is_active']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator',
                    'data' => null
                ], 403);
            }

            // Generate JWT token
            $token = $this->generateJWT($user);

            // Remove password from response
            unset($user['password']);

            // Update last login
            $this->userModel->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);

            log_message('info', 'User logged in: ' . $user['email']);

            return response()->json([
                'status' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token['access_token'],
                    'token_type' => 'Bearer',
                    'expires_in' => $token['expires_in']
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in AuthController@login: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function profile()
    {
        try {
            $userId = $this->request->user_id;
            $user = $this->userModel->find($userId);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ], 404);
            }

            // Remove password from response
            unset($user['password']);

            return response()->json([
                'status' => true,
                'message' => 'Data profil berhasil diambil',
                'data' => [
                    'user' => $user
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in AuthController@profile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function updateProfile()
    {
        try {
            $userId = $this->request->user_id;
            $validation = \Config\Services::validation();
            
            $rules = [
                'name' => [
                    'rules' => 'required|min_length[3]|max_length[100]',
                    'errors' => [
                        'required' => 'Nama harus diisi',
                        'min_length' => 'Nama minimal 3 karakter',
                        'max_length' => 'Nama maksimal 100 karakter'
                    ]
                ],
                'phone' => [
                    'rules' => 'permit_empty|min_length[10]|max_length[15]|regex_match[/^[0-9]+$/]',
                    'errors' => [
                        'min_length' => 'Nomor telepon minimal 10 digit',
                        'max_length' => 'Nomor telepon maksimal 15 digit',
                        'regex_match' => 'Nomor telepon hanya boleh berisi angka'
                    ]
                ]
            ];

            if (!$this->validate($rules)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'data' => [
                        'errors' => $validation->getErrors()
                    ]
                ], 400);
            }

            $updateData = [
                'name' => $this->request->getPost('name'),
                'phone' => $this->request->getPost('phone')
            ];

            if ($this->userModel->update($userId, $updateData)) {
                $user = $this->userModel->find($userId);
                unset($user['password']);

                log_message('info', 'User profile updated: ' . $user['email']);

                return response()->json([
                    'status' => true,
                    'message' => 'Profil berhasil diperbarui',
                    'data' => [
                        'user' => $user
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal memperbarui profil',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in AuthController@updateProfile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function changePassword()
    {
        try {
            $userId = $this->request->user_id;
            $validation = \Config\Services::validation();
            
            $rules = [
                'current_password' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Password saat ini harus diisi'
                    ]
                ],
                'new_password' => [
                    'rules' => 'required|min_length[6]',
                    'errors' => [
                        'required' => 'Password baru harus diisi',
                        'min_length' => 'Password baru minimal 6 karakter'
                    ]
                ],
                'confirm_password' => [
                    'rules' => 'required|matches[new_password]',
                    'errors' => [
                        'required' => 'Konfirmasi password harus diisi',
                        'matches' => 'Konfirmasi password tidak cocok'
                    ]
                ]
            ];

            if (!$this->validate($rules)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'data' => [
                        'errors' => $validation->getErrors()
                    ]
                ], 400);
            }

            $user = $this->userModel->find($userId);
            
            if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password saat ini salah',
                    'data' => null
                ], 400);
            }

            $updateData = [
                'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)
            ];

            if ($this->userModel->update($userId, $updateData)) {
                log_message('info', 'User password changed: ' . $user['email']);

                return response()->json([
                    'status' => true,
                    'message' => 'Password berhasil diubah',
                    'data' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mengubah password',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in AuthController@changePassword: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function logout()
    {
        try {
            // In a stateless JWT system, logout is typically handled on the client side
            // by simply removing the token. However, we can log the action.
            $userId = $this->request->user_id ?? null;
            
            if ($userId) {
                log_message('info', 'User logged out: ' . $userId);
            }

            return response()->json([
                'status' => true,
                'message' => 'Logout berhasil',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in AuthController@logout: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
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
