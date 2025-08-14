<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'uuid', 'name', 'email', 'phone', 'password', 'instansi_id', 
        'role', 'is_active', 'email_verified_at', 'last_login'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name'     => 'required|min_length[3]|max_length[255]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'phone'    => 'permit_empty|numeric|min_length[10]|max_length[15]',
        'password' => 'required|min_length[8]',
        'role'     => 'required|in_list[master,admin,user]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword', 'generateUuid'];
    protected $beforeUpdate   = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    protected function generateUuid(array $data)
    {
        if (!isset($data['data']['uuid'])) {
            $data['data']['uuid'] = service('uuid')->uuid4()->toString();
        }
        return $data;
    }

    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function findByUuid($uuid)
    {
        return $this->where('uuid', $uuid)->first();
    }

    public function getAdmins()
    {
        return $this->whereIn('role', ['admin', 'master'])
                   ->where('is_active', true)
                   ->findAll();
    }

    public function getUsersWithInstansi()
    {
        return $this->select('users.*, instansi.nama as instansi_nama')
                   ->join('instansi', 'instansi.id = users.instansi_id', 'left')
                   ->where('users.is_active', true)
                   ->findAll();
    }

    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Find user by phone number
     *
     * @param string $phone
     * @return array|null
     */
    public function findByPhone($phone)
    {
        return $this->where('phone', $phone)->first();
    }

    /**
     * Find user by either email or phone
     *
     * @param string $identity Email or phone number
     * @return array|null
     */
    public function findByIdentity($identity)
    {
        return $this->where('email', $identity)
                    ->orWhere('phone', $identity)
                    ->first();
    }
}
