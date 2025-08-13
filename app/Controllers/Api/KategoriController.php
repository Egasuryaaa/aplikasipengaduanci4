<?php

namespace App\Controllers\Api;

use App\Models\KategoriPengaduanModel;

/**
 * API Kategori Controller
 * 
 * Handles operations related to categories in mobile app
 */
class KategoriController extends ApiController
{
    protected $kategoriModel;
    
    public function __construct()
    {
        $this->kategoriModel = new KategoriPengaduanModel();
    }
    
    /**
     * Get list of active categories
     * 
     * GET /api/kategori
     * Response 200: { "status": true, "message":"List kategori", "data": { "kategori": [...] } }
     */
    public function index()
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        try {
            $kategori = $this->kategoriModel->getActiveKategori();
            
            return $this->respondSuccess([
                'kategori' => $kategori
            ], 'List kategori');
            
        } catch (\Exception $e) {
            log_message('error', 'Get kategori error: ' . $e->getMessage());
            return $this->respondError('Failed to get kategori list');
        }
    }
}
