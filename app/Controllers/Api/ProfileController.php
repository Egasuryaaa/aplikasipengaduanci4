<?php

namespace App\Controllers\Api;

use App\Models\UserModel;

/**
 * API Profile Controller
 * 
 * Handles user profile operations like viewing, updating, and changing password
 */
class ProfileController extends ApiController
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
     * Get user profile
     * 
     * GET /api/profile
     * Headers: Authorization: Bearer {token}
     * Response 200: { "status": true, "message": "User profile", "data": { "user": {...} } }
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
        
        // Remove sensitive data
        unset($user['password']);
        unset($user['api_token']);
        
        return $this->respondSuccess(['user' => $user], 'User profile retrieved successfully');
    }

    /**
     * Update user profile
     * 
     * PUT /api/profile
     * Headers: Authorization: Bearer {token}
     * Request JSON: { "name": "...", "email": "...", "phone": "..." }
     * Response 200: { "status": true, "message": "Profile updated", "data": { "user": {...} } }
     * Response 400: { "status": false, "message": "Validation Error", "data": { "errors": {...} } }
     * Response 401: { "status": false, "message": "Unauthorized" }
     */
    public function update($id = null)
    {
        // Set CORS headers
        $this->setCorsHeaders();
        
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
        
        // Validate and set name
        if (isset($input['name'])) {
            $updateData['name'] = $input['name'];
        }
        
        // Validate and set email
        if (isset($input['email']) && $input['email'] !== $user['email']) {
            // Check if email is already taken by another user
            $existingUser = $this->userModel->where('email', $input['email'])
                ->where('id !=', $user['id'])
                ->first();
            
            if ($existingUser) {
                return $this->respondError('Email already taken', 400);
            }
            
            $updateData['email'] = $input['email'];
        }
        
        // Validate and set phone
        if (isset($input['phone'])) {
            // Check if phone is already taken by another user
            if (!empty($input['phone'])) {
                $existingUser = $this->userModel->where('phone', $input['phone'])
                    ->where('id !=', $user['id'])
                    ->first();
                
                if ($existingUser) {
                    return $this->respondError('Phone number already taken', 400);
                }
            }
            
            $updateData['phone'] = $input['phone'];
        }
        
        // Skip validation since we're doing custom validation
        $this->userModel->skipValidation(true);
        
        // If there's nothing to update
        if (empty($updateData)) {
            return $this->respondError('No data provided for update', 400);
        }
        
        // Attempt to update user
        if (!$this->userModel->update($user['id'], $updateData)) {
            $errors = $this->userModel->errors();
            return $this->respondError('Failed to update profile', 400, ['errors' => $errors]);
        }
        
        // Get updated user data
        $updatedUser = $this->userModel->find($user['id']);
        unset($updatedUser['password']);
        unset($updatedUser['api_token']);
        
        return $this->respondSuccess(['user' => $updatedUser], 'Profile updated successfully');
    }

    /**
     * Change user password
     * 
     * POST /api/profile/change-password
     * Headers: Authorization: Bearer {token}
     * Request JSON: { "current_password": "...", "new_password": "...", "confirm_password": "..." }
     * Response 200: { "status": true, "message": "Password changed successfully" }
     * Response 400: { "status": false, "message": "Validation Error", "data": { "errors": {...} } }
     * Response 401: { "status": false, "message": "Unauthorized" }
     */
    public function changePassword()
    {
        // Set CORS headers
        $this->setCorsHeaders();
        
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
        
        // Validate input
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];
        
        $errors = [];
        
        // Check if current password is provided
        if (!isset($input['current_password']) || empty($input['current_password'])) {
            $errors['current_password'] = 'Current password is required';
        }
        
        // Check if new password is provided and meets minimum length
        if (!isset($input['new_password']) || empty($input['new_password'])) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($input['new_password']) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters long';
        }
        
        // Check if confirmation password matches new password
        if (!isset($input['confirm_password']) || empty($input['confirm_password'])) {
            $errors['confirm_password'] = 'Confirm password is required';
        } elseif (isset($input['new_password']) && $input['confirm_password'] !== $input['new_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        // Return errors if validation fails
        if (!empty($errors)) {
            return $this->respondError('Validation error', 400, ['errors' => $errors]);
        }
        
        // Verify current password
        if (!password_verify($input['current_password'], $user['password'])) {
            return $this->respondError('Current password is incorrect', 400);
        }
        
        // Update password
        $this->userModel->update($user['id'], [
            'password' => $input['new_password'] // Model's beforeUpdate will hash this
        ]);
        
        return $this->respondSuccess(null, 'Password changed successfully');
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
