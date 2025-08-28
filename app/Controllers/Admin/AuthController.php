<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function login()
    {
        // Redirect if already logged in
        if (session()->has('user_id')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('admin/auth/login', [
            'title' => 'Admin Login - Sistem Pengaduan Kominfo',
            'csrf_token' => csrf_hash()
        ]);
    }

    public function doLogin()
    {
        // Debug: Check if method is reached
        log_message('debug', 'doLogin method called');
        
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
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 6 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            log_message('debug', 'Validation failed: ' . json_encode($validation->getErrors()));
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        log_message('debug', 'Login attempt for email: ' . $email);

        try {
            // Find user by email
            $user = $this->userModel->findByEmail($email);
            log_message('debug', 'User found: ' . ($user ? 'Yes' : 'No'));

            if (!$user) {
                return redirect()->back()->withInput()->with('error', 'Email atau password salah');
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                log_message('warning', 'Failed login attempt for email: ' . $email . ' from IP: ' . $this->request->getIPAddress());
                return redirect()->back()->withInput()->with('error', 'Email atau password salah');
            }

            // Check if user is active
            if (!$user['is_active']) {
                return redirect()->back()->withInput()->with('error', 'Akun Anda tidak aktif');
            }
            
            // pentinggg
            // pentinggg
            // pentinggg
            // pentinggg
            // Check if user has admin/master role
            if (!in_array($user['role'], ['admin', 'master'])) {
                return redirect()->back()->withInput()->with('error', 'Akses ditolak. Hanya admin dan master yang dapat login');
            }

            // Create session
            $sessionData = [
                'user_id' => $user['id'],
                'user_uuid' => $user['uuid'],
                'user_name' => $user['name'],
                'user_email' => $user['email'],
                'user_role' => $user['role'],
                'instansi_id' => $user['instansi_id'],
                'is_logged_in' => true,
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'last_activity' => time()
            ];

            session()->set($sessionData);
            log_message('debug', 'Session created for user: ' . $user['email']);

            // Update last login
            $this->userModel->updateLastLogin($user['id']);

            // Set remember me cookie if requested
            if ($remember) {
                $cookieValue = base64_encode($user['id'] . ':' . $user['email'] . ':' . hash('sha256', $user['password']));
                setcookie('remember_token', $cookieValue, time() + (86400 * 30), '/', '', true, true); // 30 days
            }

            log_message('info', 'User logged in: ' . $user['email'] . ' (' . $user['role'] . ')');

            return redirect()->to('/admin/dashboard')->with('success', 'Login berhasil');
            
        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        $userEmail = session('user_email');
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        // Destroy session
        session()->destroy();

        log_message('info', 'User logged out: ' . $userEmail);

        return redirect()->to('/admin/login')->with('success', 'Logout berhasil');
    }

    public function checkRememberToken()
    {
        if (isset($_COOKIE['remember_token']) && !session()->has('user_id')) {
            $token = base64_decode($_COOKIE['remember_token']);
            $parts = explode(':', $token);

            if (count($parts) === 3) {
                $userId = $parts[0];
                $email = $parts[1];
                $passwordHash = $parts[2];

                $user = $this->userModel->find($userId);

                if ($user && $user['email'] === $email && hash('sha256', $user['password']) === $passwordHash) {
                    // Auto login
                    $sessionData = [
                        'user_id' => $user['id'],
                        'user_uuid' => $user['uuid'],
                        'user_name' => $user['name'],
                        'user_email' => $user['email'],
                        'user_role' => $user['role'],
                        'instansi_id' => $user['instansi_id'],
                        'is_logged_in' => true,
                        'user_agent' => $this->request->getUserAgent()->getAgentString(),
                        'last_activity' => time()
                    ];

                    session()->set($sessionData);
                    $this->userModel->updateLastLogin($user['id']);
                }
            }
        }
    }
}
