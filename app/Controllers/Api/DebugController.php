<?php

namespace App\Controllers\Api;

/**
 * Debug Controller for testing API functionality
 */
class DebugController extends ApiController
{
    /**
     * Test API endpoint for token generation
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function testToken()
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        // Get database information
        $db = \Config\Database::connect();
        
        // Get information about users table
        $tableInfo = [];
        try {
            $query = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
            $tableInfo = $query->getResultArray();
        } catch (\Exception $e) {
            return $this->respondError('Database error: ' . $e->getMessage());
        }
        
        return $this->respondSuccess([
            'database' => [
                'driver' => $db->DBDriver,
                'version' => $db->getVersion(),
                'database' => $db->database,
            ],
            'users_table' => $tableInfo,
            'timestamp' => date('Y-m-d H:i:s')
        ], 'Debug info retrieved successfully');
    }
}
