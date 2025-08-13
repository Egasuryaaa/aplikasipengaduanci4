<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InstansiModel;
use App\Models\KategoriPengaduanModel;

class MasterDataController extends ResourceController
{
    protected $instansiModel;
    protected $kategoriModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->instansiModel = new InstansiModel();
        $this->kategoriModel = new KategoriPengaduanModel();
    }

    public function instansi()
    {
        try {
            $instansi = $this->instansiModel->getActiveInstansi();

            return $this->respond([
                'success' => true,
                'message' => 'Data instansi berhasil diambil',
                'data' => $instansi
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching instansi data: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function kategori()
    {
        try {
            $kategori = $this->kategoriModel->getActiveKategori();

            return $this->respond([
                'success' => true,
                'message' => 'Data kategori berhasil diambil',
                'data' => $kategori
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching kategori data: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
}
