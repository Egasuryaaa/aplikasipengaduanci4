<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecurityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Validate CSRF for POST/PUT/DELETE requests
        if (in_array($request->getMethod(), ['post', 'put', 'delete'])) {
            $this->validateCSRF($request);
        }

        // Check for session hijacking - TEMPORARILY DISABLED
        // $this->validateSession($request);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add security headers
        $this->addSecurityHeaders($response, $request);

        return $response;
    }

    private function validateCSRF($request)
    {
        if (!csrf_check()) {
            log_message('security', 'CSRF token mismatch from IP: ' . $request->getIPAddress());
            throw new \CodeIgniter\Security\Exceptions\SecurityException('CSRF token mismatch');
        }

        // Additional form security checks
        $userAgent = $request->getUserAgent();
        $referer = $request->getHeaderLine('Referer');
        $sessionUA = session('user_agent');

        // Validate consistent user agent
        if ($sessionUA && $sessionUA !== $userAgent->getAgentString()) {
            log_message('security', 'User agent mismatch detected from IP: ' . $request->getIPAddress());
            session()->destroy();
            throw new \CodeIgniter\Security\Exceptions\SecurityException('Session hijacking detected');
        }

        // Validate referer for form submissions
        if (!$referer || !str_contains($referer, base_url())) {
            log_message('security', 'Invalid referer detected: ' . $referer);
            throw new \CodeIgniter\Security\Exceptions\SecurityException('Invalid referer');
        }
    }

    private function validateSession($request)
    {
        if (session()->has('user_id')) {
            // Update user agent in session
            if (!session()->has('user_agent')) {
                session()->set('user_agent', $request->getUserAgent()->getAgentString());
            }

            // Check session timeout (30 minutes)
            $lastActivity = session('last_activity');
            if ($lastActivity && (time() - $lastActivity) > 1800) {
                session()->destroy();
                return redirect()->to('/admin/login')->with('error', 'Session expired');
            }

            session()->set('last_activity', time());
        }
    }

    private function addSecurityHeaders($response, $request)
    {
        // CSP header for admin dashboard - DISABLED FOR TESTING
        // if (strpos($request->getUri()->getPath(), '/admin') === 0) {
        //     $nonce = $this->generateNonce();
        //     session()->set('csp_nonce', $nonce);

        //     $csp = "default-src 'self'; " .
        //            "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
        //            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; " .
        //            "img-src 'self' data: blob: https:; " .
        //            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
        //            "connect-src 'self'; " .
        //            "media-src 'self'; " .
        //            "object-src 'none'; " .
        //            "base-uri 'self'; " .
        //            "form-action 'self'; " .
        //            "frame-ancestors 'none'; " .
        //            "upgrade-insecure-requests; " .
        //            "report-uri /csp-violation-report";

        //     $response->setHeader('Content-Security-Policy', $csp);
        // }

        // Security headers for all responses
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Prevent caching of sensitive pages
        if (strpos($request->getUri()->getPath(), '/admin') === 0) {
            $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
        }
    }

    private function generateNonce()
    {
        return base64_encode(random_bytes(16));
    }
}
