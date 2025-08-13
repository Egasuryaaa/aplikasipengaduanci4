<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ApiController extends ResourceController
{
    protected $format = 'json';

    /**
     * Return success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return mixed
     */
    protected function respondSuccess($data = null, string $message = 'Success', int $code = 200)
    {
        // Add CORS headers directly in the controller
        $this->setCorsHeaders();
        
        return $this->respond([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return error response
     *
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @return mixed
     */
    protected function respondError(string $message = 'Error', int $code = 400, $data = null)
    {
        // Add CORS headers directly in the controller
        $this->setCorsHeaders();
        
        return $this->respond([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return unauthorized response
     *
     * @param string $message
     * @return mixed
     */
    protected function respondUnauthorized(string $message = 'Unauthorized')
    {
        return $this->respondError($message, 401);
    }

    /**
     * Return forbidden response
     *
     * @param string $message
     * @return mixed
     */
    protected function respondForbidden(string $message = 'Forbidden')
    {
        return $this->respondError($message, 403);
    }

    /**
     * Return not found response
     *
     * @param string $message
     * @return mixed
     */
    protected function respondNotFound(string $message = 'Not Found')
    {
        return $this->respondError($message, 404);
    }

    /**
     * Return validation error response
     *
     * @param array $errors
     * @param string $message
     * @return mixed
     */
    protected function respondValidationError(array $errors, string $message = 'Validation Error')
    {
        return $this->respondError($message, 400, ['errors' => $errors]);
    }

    /**
     * Get authenticated user from request
     *
     * @return array|null
     */
    protected function getAuthUser()
    {
        return $this->request->user ?? null;
    }

    /**
     * Get authenticated user ID from request
     *
     * @return int|null
     */
    protected function getAuthUserId()
    {
        return $this->getAuthUser()['id'] ?? null;
    }
    
    /**
     * Set CORS headers for API responses
     */
    protected function setCorsHeaders()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Credentials', 'false');
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
}
