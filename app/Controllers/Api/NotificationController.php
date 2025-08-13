<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\NotificationModel;

class NotificationController extends ResourceController
{
    protected $notificationModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        $userId = $this->request->user_id;
        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 20;
        $unreadOnly = $this->request->getGet('unread_only') === 'true';

        try {
            // Build query
            $builder = $this->notificationModel->select('notifications.*, pengaduan.nomor_pengaduan')
                                              ->join('pengaduan', 'pengaduan.id = notifications.pengaduan_id', 'left')
                                              ->where('notifications.user_id', $userId);

            if ($unreadOnly) {
                $builder->where('notifications.is_read', false);
            }

            // Get total count for pagination
            $total = $builder->countAllResults(false);

            // Get paginated results
            $notifications = $builder->orderBy('notifications.created_at', 'DESC')
                                   ->limit($limit, ($page - 1) * $limit)
                                   ->find();

            // Get unread count
            $unreadCount = $this->notificationModel->getUnreadCount($userId);

            $pagination = [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ];

            return $this->respond([
                'success' => true,
                'message' => 'Notifikasi berhasil diambil',
                'data' => $notifications,
                'meta' => [
                    'pagination' => $pagination,
                    'unread_count' => $unreadCount
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching notifications: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        $userId = $this->request->user_id;

        try {
            $result = $this->notificationModel->markAsRead($id, $userId);

            if ($result) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Notifikasi berhasil ditandai sebagai dibaca'
                ]);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan'
                ], 404);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error marking notification as read: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function markAllAsRead()
    {
        $userId = $this->request->user_id;

        try {
            $result = $this->notificationModel->markAllAsRead($userId);

            if ($result) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Semua notifikasi berhasil ditandai sebagai dibaca'
                ]);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Tidak ada notifikasi yang diperbarui'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error marking all notifications as read: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function getUnreadCount()
    {
        $userId = $this->request->user_id;

        try {
            $count = $this->notificationModel->getUnreadCount($userId);

            return $this->respond([
                'success' => true,
                'message' => 'Jumlah notifikasi belum dibaca',
                'data' => [
                    'unread_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting unread count: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
}
