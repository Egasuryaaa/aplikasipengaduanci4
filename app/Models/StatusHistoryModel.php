<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusHistoryModel extends Model
{
    protected $table            = 'status_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pengaduan_id', 'status_old', 'status_new', 'keterangan', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'pengaduan_id' => 'required|integer',
        'status_new'   => 'required|in_list[pending,diproses,selesai,ditolak]',
        'updated_by'   => 'required|integer',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getHistoryByPengaduan($pengaduanId)
    {
        return $this->select('status_history.*, users.name as updated_by_name')
                   ->join('users', 'users.id = status_history.updated_by')
                   ->where('pengaduan_id', $pengaduanId)
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    public function getHistoryWithPengaduan()
    {
        return $this->select('status_history.*, 
                             pengaduan.nomor_pengaduan, pengaduan.deskripsi,
                             users.name as updated_by_name')
                   ->join('pengaduan', 'pengaduan.id = status_history.pengaduan_id')
                   ->join('users', 'users.id = status_history.updated_by')
                   ->orderBy('status_history.created_at', 'DESC')
                   ->findAll();
    }
}
