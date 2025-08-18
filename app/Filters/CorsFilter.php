<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    /**
     * Handle CORS for all requests
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle OPTIONS preflight requests
        if ($request->getMethod() === 'options') {
            $response = service('response');
            $this->setCorsHeaders($response);
            return $response->setStatusCode(200);
        }
        
        // For other requests, just set the headers
        return null;
    }

    /**
     * Set CORS headers in response
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $this->setCorsHeaders($response);
        return $response;
    }

    /**
     * Set CORS headers
     */
    private function setCorsHeaders($response)
    {
        // Allow multiple origins for development and production
        $allowedOrigins = [
            'http://localhost:60405',  // Flutter web development
            'http://localhost:3000',   // React/Next.js development
            'http://localhost:8080',   // Vue/Vite development
            'https://yourdomain.com',  // Production domain
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins) || ENVIRONMENT === 'development') {
            $response->setHeader('Access-Control-Allow-Origin', $origin ?: '*');
        }
        
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-CSRF-TOKEN');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
        $response->setHeader('Access-Control-Allow-Credentials', 'false');
        $response->setHeader('Access-Control-Max-Age', '3600');
    }
}
