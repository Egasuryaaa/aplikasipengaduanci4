<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Models\InstansiModel;

/**
 * API User Controller
 * 
 * Handles user data operations for Flutter app
 */
class UserController extends ApiController
{
    protected $userModel;
    protected $instansiModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->instansiModel = new InstansiModel();
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
     * Get authenticated user details
     * 
     * GET /api/user
     * Headers: Authorization: Bearer {token}
     * Response 200: { "status": true, "message": "User details", "data": { "user": {...} } }
     * Response 401: { "status": false, "message": "Unauthorized" }
     */
    public function index()
    {
        // Set CORS headers
        $this->setCorsHeaders();
        
        // Get authenticated user
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->respondError('Unauthorized', 401);
        }
        
        // Get user with instansi details
        $userData = $this->userModel
            ->select('users.id, users.uuid, users.name, users.email, users.phone, users.instansi_id, users.role, users.is_active, users.created_at, users.updated_at, instansi.nama as instansi_nama, instansi.alamat as instansi_alamat, instansi.telepon as instansi_telepon, instansi.email as instansi_email')
            ->join('instansi', 'instansi.id = users.instansi_id', 'left')
            ->where('users.id', $user['id'])
            ->first();
        
        if (!$userData) {
            return $this->respondError('User not found', 404);
        }
        
        // Format response data
        $responseData = [
            'id' => $userData['id'],
            'uuid' => $userData['uuid'],
            'name' => $userData['name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            'instansi_id' => $userData['instansi_id'],
            'role' => $userData['role'],
            'is_active' => $userData['is_active'],
            'created_at' => $userData['created_at'],
            'updated_at' => $userData['updated_at'],
            'instansi' => null
        ];
        
        // Add instansi data if exists
        if ($userData['instansi_id'] && $userData['instansi_nama']) {
            $responseData['instansi'] = [
                'id' => $userData['instansi_id'],
                'nama' => $userData['instansi_nama'],
                'alamat' => $userData['instansi_alamat'],
                'telepon' => $userData['instansi_telepon'],
                'email' => $userData['instansi_email']
            ];
        }
        
        return $this->respondSuccess(['user' => $responseData], 'User details retrieved successfully');
    }

    /**
     * Update user profile
     * 
     * PUT /api/user
     * Headers: Authorization: Bearer {token}
     * Request JSON: { "name": "...", "email": "...", "phone": "...", "instansi_id": 1 }
     * Response 200: { "status": true, "message": "Profile updated", "data": { "user": {...} } }
     * Response 400: { "status": false, "message": "Validation Error", "data": { "errors": {...} } }
     * Response 401: { "status": false, "message": "Unauthorized" }
     */
    public function update($id = null)
    {
        // Set CORS headers
        $this->setCorsHeaders();
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        // Get authenticated user
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->respondError('Unauthorized', 401);
        }
        
        // Get request data
        $input = $this->request->getJSON(true);
        if (empty($input)) {
            $input = $this->request->getRawInput();
        }
        
        // Prepare data for update
        $updateData = [];
        $errors = [];
        
        // Validate and set name
        if (isset($input['name'])) {
            if (empty($input['name']) || strlen($input['name']) < 3) {
                $errors['name'] = 'Name must be at least 3 characters';
            } else {
                $updateData['name'] = $input['name'];
            }
        }
        
        // Validate and set email
        if (isset($input['email']) && $input['email'] !== $user['email']) {
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } else {
                // Check if email is already taken by another user
                $existingUser = $this->userModel->where('email', $input['email'])
                    ->where('id !=', $user['id'])
                    ->first();
                
                if ($existingUser) {
                    $errors['email'] = 'Email already taken';
                } else {
                    $updateData['email'] = $input['email'];
                }
            }
        }
        
        // Validate and set phone
        if (isset($input['phone'])) {
            if (!empty($input['phone'])) {
                if (!is_numeric($input['phone']) || strlen($input['phone']) < 10) {
                    $errors['phone'] = 'Phone number must be at least 10 digits';
                } else {
                    // Check if phone is already taken by another user
                    $existingUser = $this->userModel->where('phone', $input['phone'])
                        ->where('id !=', $user['id'])
                        ->first();
                    
                    if ($existingUser) {
                        $errors['phone'] = 'Phone number already taken';
                    } else {
                        $updateData['phone'] = $input['phone'];
                    }
                }
            } else {
                $updateData['phone'] = null;
            }
        }
        
        // Validate and set instansi_id
        if (isset($input['instansi_id'])) {
            if (!empty($input['instansi_id'])) {
                $instansi = $this->instansiModel->find($input['instansi_id']);
                if (!$instansi || !$instansi['is_active']) {
                    $errors['instansi_id'] = 'Invalid or inactive instansi';
                } else {
                    $updateData['instansi_id'] = $input['instansi_id'];
                }
            } else {
                $updateData['instansi_id'] = null;
            }
        }
        
        // Return errors if validation fails
        if (!empty($errors)) {
            return $this->respondError('Validation error', 400, ['errors' => $errors]);
        }
        
        // If there's nothing to update
        if (empty($updateData)) {
            return $this->respondError('No data provided for update', 400);
        }
        
        // Skip validation since we're doing custom validation
        $this->userModel->skipValidation(true);
        
        // Attempt to update user
        if (!$this->userModel->update($user['id'], $updateData)) {
            $modelErrors = $this->userModel->errors();
            return $this->respondError('Failed to update profile', 400, ['errors' => $modelErrors]);
        }
        
        // Get updated user data with instansi
        $updatedUser = $this->userModel
            ->select('users.id, users.uuid, users.name, users.email, users.phone, users.instansi_id, users.role, users.is_active, users.created_at, users.updated_at, instansi.nama as instansi_nama, instansi.alamat as instansi_alamat, instansi.telepon as instansi_telepon, instansi.email as instansi_email')
            ->join('instansi', 'instansi.id = users.instansi_id', 'left')
            ->where('users.id', $user['id'])
            ->first();
        
        // Format response data
        $responseData = [
            'id' => $updatedUser['id'],
            'uuid' => $updatedUser['uuid'],
            'name' => $updatedUser['name'],
            'email' => $updatedUser['email'],
            'phone' => $updatedUser['phone'],
            'instansi_id' => $updatedUser['instansi_id'],
            'role' => $updatedUser['role'],
            'is_active' => $updatedUser['is_active'],
            'created_at' => $updatedUser['created_at'],
            'updated_at' => $updatedUser['updated_at'],
            'instansi' => null
        ];
        
        // Add instansi data if exists
        if ($updatedUser['instansi_id'] && $updatedUser['instansi_nama']) {
            $responseData['instansi'] = [
                'id' => $updatedUser['instansi_id'],
                'nama' => $updatedUser['instansi_nama'],
                'alamat' => $updatedUser['instansi_alamat'],
                'telepon' => $updatedUser['instansi_telepon'],
                'email' => $updatedUser['instansi_email']
            ];
        }
        
        return $this->respondSuccess(['user' => $responseData], 'Profile updated successfully');
    }

    /**
     * Get authenticated user
     * 
     * @return array|null User data or null if not authenticated
     */
    protected function getAuthenticatedUser()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }
        
        $token = $matches[1];
        return $this->userModel->where('api_token', $token)->first();
    }
}
