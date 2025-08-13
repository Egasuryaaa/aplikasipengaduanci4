<?php

namespace App\Controllers\Api;

use App\Models\UserModel;

/**
 * API Authentication Controller
 * 
 * Handles user registration, login, and logout for mobile app
 */
class AuthController extends ApiController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    /**
     * Handle OPTIONS requests for CORS preflight
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function options()
    {
        $this->setCorsHeaders();
        return $this->response->setStatusCode(200);
    }

    /**
     * Register a new user
     * 
     * POST /api/register
     * Request JSON: { "name": "...", "email": "...", "phone": "...", "password": "..." }
     * Response 201: { "status": true, "message": "User registered", "data": { "user": {...}, "token": "..." } }
     * Response 400: { "status": false, "message": "Validation Error", "data": { "errors": {...} } }
     */
    public function register()
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'phone' => 'required|numeric|min_length[10]|max_length[15]|is_unique[users.phone]',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'email' => $this->request->getVar('email'),
            'phone' => $this->request->getVar('phone'),
            'password' => $this->request->getVar('password'),
            'role' => 'user', // Default role for mobile app users
            'is_active' => true,
        ];

        try {
            // Insert user
            $userId = $this->userModel->insert($data);
            
            // Generate token
            $token = $this->userModel->generateApiToken($userId);
            
            // Get user data
            $user = $this->userModel->find($userId);
            
            // Remove sensitive data
            unset($user['password']);
            unset($user['api_token']);
            
            return $this->respondSuccess([
                'user' => $user,
                'token' => $token
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            return $this->respondError('Failed to register user. Please try again.');
        }
    }

    /**
     * User login
     * 
     * POST /api/login
     * Request JSON: { "email_or_phone": "...", "password": "..." }
     * Response 200: { "status": true, "message":"Login berhasil", "data": { "user": {...}, "token": "..." } }
     * Response 401: { "status": false, "message": "Invalid credentials", "data": null }
     */
    public function login()
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        // Check for either email or email_or_phone field
        $email = $this->request->getVar('email');
        $emailOrPhone = $this->request->getVar('email_or_phone');
        
        if (empty($emailOrPhone) && !empty($email)) {
            // If using 'email' field instead of 'email_or_phone'
            $emailOrPhone = $email;
        }
        
        $rules = [
            'password' => 'required'
        ];
        
        // Validate based on available input
        if (empty($emailOrPhone)) {
            return $this->respondValidationError(['email_or_phone' => 'The email_or_phone field is required.']);
        }

        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }

        $password = $this->request->getVar('password');
        
        try {
            // Try to find user by identity (email or phone)
            $user = $this->userModel->findByIdentity($emailOrPhone);
            
            // Check if user exists and password is correct
            if (!$user || !password_verify($password, $user['password'])) {
                return $this->respondUnauthorized('Invalid email/phone or password');
            }
            
            // Check if user is active
            if (!$user['is_active']) {
                return $this->respondForbidden('Account is inactive');
            }
            
            try {
                // Generate token
                $token = $this->userModel->generateApiToken($user['id']);
                
                // Update last login
                $this->userModel->updateLastLogin($user['id']);
            } catch (\Exception $e) {
                log_message('error', 'Login error: ' . $e->getMessage());
                return $this->respondError('Authentication error: ' . $e->getMessage(), 500);
            }
            
            // Remove sensitive data
            unset($user['password']);
            unset($user['api_token']);
            
            return $this->respondSuccess([
                'user' => $user,
                'token' => $token
            ], 'Login successful');
        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return $this->respondError('Login failed. Please try again.');
        }
    }

    /**
     * User logout
     * 
     * POST /api/logout
     * Response 200: { "status": true, "message": "Logout successful", "data": null }
     */
    public function logout()
    {
        try {
            $user = $this->getAuthUser();
            $this->userModel->clearApiToken($user['id']);
            
            return $this->respondSuccess(null, 'Logout successful');
        } catch (\Exception $e) {
            log_message('error', 'Logout error: ' . $e->getMessage());
            return $this->respondError('Logout failed. Please try again.');
        }
    }

    /**
     * Get authenticated user details
     * 
     * GET /api/user
     * Response 200: { "status": true, "message": "User details", "data": { "user": {...} } }
     */
    public function user()
    {
        try {
            $user = $this->getAuthUser();
            
            // Remove sensitive data
            unset($user['password']);
            unset($user['api_token']);
            
            return $this->respondSuccess(['user' => $user], 'User details');
        } catch (\Exception $e) {
            log_message('error', 'Get user error: ' . $e->getMessage());
            return $this->respondError('Failed to get user details.');
        }
    }
}
