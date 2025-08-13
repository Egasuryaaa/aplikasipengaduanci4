<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class CorsTestController extends ApiController
{
    /**
     * Test endpoint for CORS
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        return $this->respondSuccess([
            'cors_test' => true,
            'timestamp' => time(),
            'message' => 'CORS is properly configured'
        ]);
    }
}
