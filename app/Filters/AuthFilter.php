<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('/admin/login')->with('error', 'Please login to access this page');
        }

        // Check user role if specified
        if (!empty($arguments)) {
            $requiredRole = $arguments[0];
            $userRole = session('user_role');

            // Role hierarchy: master > admin > user
            $roleHierarchy = ['user' => 1, 'admin' => 2, 'master' => 3];

            if (!isset($roleHierarchy[$userRole]) || 
                !isset($roleHierarchy[$requiredRole]) ||
                $roleHierarchy[$userRole] < $roleHierarchy[$requiredRole]) {
                return redirect()->to('/admin/dashboard')->with('error', 'Access denied');
            }
        }

        // Additional security checks
        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find(session('user_id'));

            if (!$user || !$user['is_active']) {
                session()->destroy();
                return redirect()->to('/admin/login')->with('error', 'Account is inactive');
            }
        } catch (\Exception $e) {
            // If database error, allow access but log the error
            log_message('error', 'AuthFilter database error: ' . $e->getMessage());
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
