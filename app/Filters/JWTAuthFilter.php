<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $key = getenv('JWT_SECRET_KEY') ?: 'your-secret-key-change-in-production';
        
        // Get token from Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Authorization header missing']);
        }

        // Extract token from "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid authorization header format']);
        }

        $token = $matches[1];

        try {
            // Verify and decode the token
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            // Check token expiration
            if ($decoded->exp < time()) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['error' => 'Token expired']);
            }

            // Verify user exists and is active
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($decoded->user_id);

            if (!$user || !$user['is_active']) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['error' => 'User account is inactive']);
            }

            // Store user info in request for use in controllers
            $request->user_id = $decoded->user_id;
            $request->user_role = $user['role'];
            $request->user_data = $user;

        } catch (\Exception $e) {
            log_message('error', 'JWT validation failed: ' . $e->getMessage());
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid token']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
