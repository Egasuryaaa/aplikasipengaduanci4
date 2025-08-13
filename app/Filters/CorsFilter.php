<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    private $allowedOrigins = [
        'http://localhost:3000',      // Development
        'https://yourdomain.com',     // Production web
        'file://',                    // Mobile app (Capacitor)
        'ionic://',                   // Ionic app
        'capacitor://',               // Capacitor app
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');

        // Validate origin for security
        if ($origin && !in_array($origin, $this->allowedOrigins) && !$this->isValidMobileOrigin($origin)) {
            log_message('security', 'Blocked request from invalid origin: ' . $origin);
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Origin not allowed', 'origin' => $origin]);
        }

        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            $response = service('response');
            $response->setHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($origin));
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
            $response->setHeader('Access-Control-Allow-Credentials', 'false');
            $response->setHeader('Access-Control-Max-Age', '86400'); // 24 hours
            $response->setStatusCode(204);
            return $response;
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');

        if ($origin && ($this->isAllowedOrigin($origin) || $this->isValidMobileOrigin($origin))) {
            $response->setHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($origin));
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->setHeader('Access-Control-Allow-Credentials', 'false');
        }

        return $response;
    }

    private function isAllowedOrigin($origin): bool
    {
        return in_array($origin, $this->allowedOrigins);
    }

    private function isValidMobileOrigin($origin): bool
    {
        // Mobile app origins validation
        return preg_match('/^(http|https):\/\/localhost:\d+$/', $origin) ||
               preg_match('/^(file|ionic|capacitor):\/\//', $origin);
    }

    private function getAllowedOrigin($origin)
    {
        if ($this->isAllowedOrigin($origin) || $this->isValidMobileOrigin($origin)) {
            return $origin;
        }
        return $this->allowedOrigins[0]; // Default to first allowed origin
    }
}
