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
        
        // Check if header exists and has Bearer token
        if (empty($header) || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $this->failUnauthorized('No token provided');
        }
        
        $token = $matches[1];
        
        // Verify token
        $userModel = new UserModel();
        $user = $userModel->where('api_token', $token)->first();
        
        if (!$user) {
            return $this->failUnauthorized('Invalid token');
        }
        
        // Check if user is active
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
