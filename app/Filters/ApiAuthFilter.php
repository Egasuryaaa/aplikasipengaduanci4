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
        // Handle OPTIONS preflight requests - allow without authentication
        if ($request->getMethod() === 'options') {
            $response = service('response');
            $response->setHeader('Access-Control-Allow-Origin', '*');
            $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Credentials', 'false');
            $response->setHeader('Access-Control-Max-Age', '3600');
            return $response->setStatusCode(200);
        }

        // Get the Authorization header
        $header = $request->getHeaderLine('Authorization');
        // Mask token value in logs to avoid leaking sensitive data
        $maskedHeader = preg_replace('/(Bearer\s+)\S+/i', '$1[REDACTED]', $header);
        log_message('debug', 'Authorization header: ' . $maskedHeader);
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

    // Set user data for downstream use.
    // Note: CodeIgniter\HTTP\IncomingRequest does not support setAttribute();
    // avoid attaching arbitrary data to the request. Use session or
    // have controllers decode JWT again via ApiController::getAuthUser().

    // Jika ingin menyimpan user di session:
        session()->set('user', $user);

    // Atau jika ingin meneruskan data ke controller, gunakan session (di atas)
    // atau panggil ApiController::getAuthUser() di controller.
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
        $response = service('response');
        
        // Set CORS headers
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Credentials', 'false');
        $response->setHeader('Access-Control-Max-Age', '3600');
        
        return $response
            ->setStatusCode(401)
            ->setJSON([
                'status' => false,
                'message' => $message,
                'data' => null
            ])
            ->setHeader('Content-Type', 'application/json');
    }
}
