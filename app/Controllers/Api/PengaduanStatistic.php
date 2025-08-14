<?php


namespace App\Controllers\Api;

use App\Models\PengaduanModel;

class PengaduanStatistic extends ApiController
{
    protected $pengaduanModel;

    public function __construct()
    {
        $this->pengaduanModel = new PengaduanModel();
    }

    /**
     * Get statistik jumlah pengaduan per status untuk user yang sedang login
     * 
     * GET /api/pengaduan/statistic
     * Response 200: { "status": true, "message":"Statistik pengaduan", "data": { "pending": 2, "diproses": 1, "selesai": 3, "ditolak": 0 } }
     * Response 401: { "status": false, "message":"Unauthorized", "data": null }
     */
    public function index()
    {
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');

        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }

        try {
            $userId = $this->getAuthUserId();
            if (!$userId) {
                return $this->respondError('Unauthorized', 401);
            }

            $statuses = ['pending', 'diproses', 'selesai', 'ditolak'];
            $statistic = [];

            foreach ($statuses as $status) {
                $statistic[$status] = $this->pengaduanModel
                    ->where('user_id', $userId)
                    ->where('status', $status)
                    ->countAllResults();
            }

            return $this->respondSuccess($statistic, 'Statistik pengaduan');
        } catch (\Exception $e) {
            log_message('error', 'Get statistik pengaduan error: ' . $e->getMessage());
            return $this->respondError('Failed to get statistik pengaduan');
        }
    }
}