<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class ApiAuthFilter implements FilterInterface
{
    /**
     * Filter for API authentication using Bearer token
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Get the Authorization header
        $header = $request->getHeaderLine('Authorization');
        log_message('debug', 'Authorization header: ' . $header);
        if (empty($header) || !preg_match('/Bearer\\s(\\S+)/', $header, $matches)) {
            return $this->failUnauthorized('No token provided');
        }

        $token = $matches[1];

        // Verify JWT token
        try {
            $key = getenv('JWT_SECRET_KEY') ?: 'your_default_secret_key';
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
        } catch (\Exception $e) {
            return $this->failUnauthorized('Invalid or expired token');
        }

        // Find user by id from JWT payload
        $userModel = new UserModel();
        $user = $userModel->find((int)($decoded->id ?? 0));
        if (!$user) {
            return $this->failUnauthorized('User not found');
        }
        if (!$user['is_active']) {
            return $this->failUnauthorized('User account is inactive');
        }

        // Set user data in request for controllers to access
        $request->user = $user;
    }
    
    /**
     * We don't need to do anything here.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
    
    /**
     * Return unauthorized response
     */
    protected function failUnauthorized(string $message = 'Unauthorized')
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'status' => false,
                'message' => $message,
                'data' => null
            ])
            ->setHeader('Content-Type', 'application/json');
    }
}
