<?php

namespace App\Models;

use CodeIgniter\Model;

class KomentarPengaduanModel extends Model
{
    protected $table            = 'komentar_pengaduan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pengaduan_id', 'user_id', 'komentar', 'is_internal'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'pengaduan_id' => 'required|integer',
        'user_id'      => 'required|integer',
        'komentar'     => 'required|min_length[5]|max_length[1000]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getKomentarByPengaduan($pengaduanId, $includeInternal = true)
    {
        $builder = $this->select('komentar_pengaduan.*, users.name as user_name, users.role as user_role')
                       ->join('users', 'users.id = komentar_pengaduan.user_id')
                       ->where('pengaduan_id', $pengaduanId);

        // All comments are now visible to users, so we don't filter internal comments
        
        return $builder->orderBy('komentar_pengaduan.created_at', 'ASC')->findAll();
    }

    public function getKomentarWithPengaduan()
    {
        return $this->select('komentar_pengaduan.*, 
                             pengaduan.nomor_pengaduan, pengaduan.deskripsi,
                             users.name as user_name, users.role as user_role')
                   ->join('pengaduan', 'pengaduan.id = komentar_pengaduan.pengaduan_id')
                   ->join('users', 'users.id = komentar_pengaduan.user_id')
                   ->orderBy('komentar_pengaduan.created_at', 'DESC')
                   ->findAll();
    }
    
    /**
     * Get comment by ID with user details
     *
     * @param int $commentId Comment ID
     * @return array|null Comment data with user details or null if not found
     */
    public function getKomentarWithUser($commentId)
    {
        return $this->select('komentar_pengaduan.*, users.name as user_name, users.role as user_role')
                   ->join('users', 'users.id = komentar_pengaduan.user_id')
                   ->where('komentar_pengaduan.id', $commentId)
                   ->first();
    }
}
