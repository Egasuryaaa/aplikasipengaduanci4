<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriPengaduanModel extends Model
{
    protected $table            = 'kategori_pengaduan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama', 'deskripsi', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama'      => 'required|min_length[3]|max_length[255]',
        'deskripsi' => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getActiveKategori()
    {
        return $this->where('is_active', true)->orderBy('nama', 'ASC')->findAll();
    }

    public function getKategoriWithPengaduanCount()
    {
        return $this->select('kategori_pengaduan.*, COUNT(pengaduan.id) as pengaduan_count')
                   ->join('pengaduan', 'pengaduan.kategori_id = kategori_pengaduan.id', 'left')
                   ->groupBy('kategori_pengaduan.id')
                   ->orderBy('kategori_pengaduan.nama', 'ASC')
                   ->findAll();
    }
}
