<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'pengaduan_id', 'title', 'message', 'type', 'is_read'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'title'   => 'required|max_length[255]',
        'message' => 'required|max_length[1000]',
        'type'    => 'permit_empty|in_list[info,success,warning,error]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getNotificationsByUser($userId, $limit = null)
    {
        $builder = $this->select('notifications.*, pengaduan.nomor_pengaduan')
                       ->join('pengaduan', 'pengaduan.id = notifications.pengaduan_id', 'left')
                       ->where('user_id', $userId)
                       ->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                   ->where('is_read', false)
                   ->countAllResults();
    }

    public function markAsRead($notificationId, $userId = null)
    {
        $builder = $this->where('id', $notificationId);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->set('is_read', true)->update();
    }

    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                   ->set('is_read', true)
                   ->update();
    }

    public function createNotification($userId, $title, $message, $type = 'info', $pengaduanId = null)
    {
        return $this->insert([
            'user_id' => $userId,
            'pengaduan_id' => $pengaduanId,
            'title' => $title,
            'message' => $message,
            'type' => $type
        ]);
    }
}
