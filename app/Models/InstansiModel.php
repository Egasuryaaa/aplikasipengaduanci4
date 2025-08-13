<?php

namespace App\Models;

use CodeIgniter\Model;

class InstansiModel extends Model
{
    protected $table            = 'instansi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama', 'alamat', 'telepon', 'email', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama'    => 'required|min_length[3]|max_length[255]',
        'alamat'  => 'permit_empty|max_length[500]',
        'telepon' => 'permit_empty|numeric|min_length[10]|max_length[15]',
        'email'   => 'permit_empty|valid_email',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getActiveInstansi()
    {
        return $this->where('is_active', true)->orderBy('nama', 'ASC')->findAll();
    }

    public function getInstansiWithUserCount()
    {
        return $this->select('instansi.*, COUNT(users.id) as user_count')
                   ->join('users', 'users.instansi_id = instansi.id', 'left')
                   ->groupBy('instansi.id')
                   ->orderBy('instansi.nama', 'ASC')
                   ->findAll();
    }

    public function getInstansiWithPengaduanCount()
    {
        return $this->select('instansi.*, COUNT(pengaduan.id) as pengaduan_count')
                   ->join('pengaduan', 'pengaduan.instansi_id = instansi.id', 'left')
                   ->groupBy('instansi.id')
                   ->orderBy('instansi.nama', 'ASC')
                   ->findAll();
    }
}
