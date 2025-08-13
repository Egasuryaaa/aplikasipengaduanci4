<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PengaduanModel;
use App\Models\InstansiModel;
use App\Models\KategoriPengaduanModel;
use App\Models\KomentarPengaduanModel;

class PengaduanController extends ResourceController
{
    protected $pengaduanModel;
    protected $instansiModel;
    protected $kategoriModel;
    protected $komentarModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->pengaduanModel = new PengaduanModel();
        $this->instansiModel = new InstansiModel();
        $this->kategoriModel = new KategoriPengaduanModel();
        $this->komentarModel = new KomentarPengaduanModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $userId = $this->request->user_id;
        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 10;
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        // Build query
        $builder = $this->pengaduanModel->select('pengaduan.*, 
                                                 instansi.nama as instansi_nama,
                                                 kategori_pengaduan.nama as kategori_nama')
                                       ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                                       ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id')
                                       ->where('pengaduan.user_id', $userId);

        // Apply filters
        if ($status) {
            $builder->where('pengaduan.status', $status);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('pengaduan.nomor_pengaduan', $search)
                    ->orLike('pengaduan.deskripsi', $search)
                    ->groupEnd();
        }

        // Get total count for pagination
        $total = $builder->countAllResults(false);

        // Get paginated results
        $pengaduan = $builder->orderBy('pengaduan.created_at', 'DESC')
                            ->limit($limit, ($page - 1) * $limit)
                            ->find();

        // Process foto_bukti JSON for each pengaduan
        foreach ($pengaduan as &$p) {
            if ($p['foto_bukti']) {
                $fotoBukti = json_decode($p['foto_bukti'], true);
                if ($fotoBukti && is_array($fotoBukti)) {
                    // Convert relative paths to full URLs
                    $p['foto_bukti'] = array_map(function($foto) {
                        return base_url('uploads/pengaduan/' . $foto);
                    }, $fotoBukti);
                } else {
                    $p['foto_bukti'] = [];
                }
            } else {
                $p['foto_bukti'] = [];
            }
        }

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
            'message' => 'Data pengaduan berhasil diambil',
            'data' => $pengaduan,
            'meta' => [
                'pagination' => $pagination
            ]
        ]);
    }

    public function show($id = null)
    {
        $userId = $this->request->user_id;
        
        // Find pengaduan by UUID or ID
            $pengaduan = $this->pengaduanModel->select('pengaduan.*, 
                                   instansi.nama as instansi_nama,
                                   kategori_pengaduan.nama as kategori_nama')
                             ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                             ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id')
                             ->where('pengaduan.user_id', $userId)
                             ->groupStart()
                             ->where('pengaduan.uuid', $id)
                             ->orWhere('pengaduan.id', $id)
                             ->groupEnd()
                             ->first();

        if (!$pengaduan) {
            return $this->respond([
                'success' => false,
                'message' => 'Pengaduan tidak ditemukan'
            ], 404);
        }

        // Process foto_bukti JSON
        if ($pengaduan['foto_bukti']) {
            $fotoBukti = json_decode($pengaduan['foto_bukti'], true);
            if ($fotoBukti && is_array($fotoBukti)) {
                $pengaduan['foto_bukti'] = array_map(function($foto) {
                    return base_url('uploads/pengaduan/' . $foto);
                }, $fotoBukti);
            } else {
                $pengaduan['foto_bukti'] = [];
            }
        } else {
            $pengaduan['foto_bukti'] = [];
        }

        // Get status history
        $statusHistoryModel = new \App\Models\StatusHistoryModel();
        $statusHistory = $statusHistoryModel->getHistoryByPengaduan($pengaduan['id']);

        // Get comments (all comments are now visible to users)
        $comments = $this->komentarModel->getKomentarByPengaduan($pengaduan['id'], true);

        $data = [
            'pengaduan' => $pengaduan,
            'status_history' => $statusHistory,
            'comments' => $comments
        ];

        return $this->respond([
            'success' => true,
            'message' => 'Detail pengaduan berhasil diambil',
            'data' => $data
        ]);
    }

    public function create()
    {
        $userId = $this->request->user_id;
        
        $validation = \Config\Services::validation();
        $rules = [
            'instansi_id' => [
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Instansi harus dipilih',
                    'integer' => 'ID instansi tidak valid'
                ]
            ],
            'kategori_id' => [
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Kategori harus dipilih',
                    'integer' => 'ID kategori tidak valid'
                ]
            ],
            'deskripsi' => [
                'rules' => 'required|min_length[10]|max_length[2000]',
                'errors' => [
                    'required' => 'Deskripsi harus diisi',
                    'min_length' => 'Deskripsi minimal 10 karakter',
                    'max_length' => 'Deskripsi maksimal 2000 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

        // Verify instansi and kategori exist
        $instansi = $this->instansiModel->find($this->request->getPost('instansi_id'));
        $kategori = $this->kategoriModel->find($this->request->getPost('kategori_id'));

        if (!$instansi || !$instansi['is_active']) {
            return $this->respond([
                'success' => false,
                'message' => 'Instansi tidak valid atau tidak aktif'
            ], 400);
        }

        if (!$kategori || !$kategori['is_active']) {
            return $this->respond([
                'success' => false,
                'message' => 'Kategori tidak valid atau tidak aktif'
            ], 400);
        }

        $pengaduanData = [
            'user_id' => $userId,
            'instansi_id' => $this->request->getPost('instansi_id'),
            'kategori_id' => $this->request->getPost('kategori_id'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status' => 'pending'
        ];

        // Handle foto_bukti if provided (as JSON array of filenames)
        $fotoBukti = $this->request->getPost('foto_bukti');
        if ($fotoBukti) {
            if (is_string($fotoBukti)) {
                $fotoBukti = json_decode($fotoBukti, true);
            }
            if (is_array($fotoBukti)) {
                $pengaduanData['foto_bukti'] = json_encode($fotoBukti);
            }
        }

        try {
            $pengaduanId = $this->pengaduanModel->insert($pengaduanData);

            if ($pengaduanId) {
                // Get the created pengaduan
                $pengaduan = $this->pengaduanModel->getPengaduanWithRelations($pengaduanId);

                // Create notification for admins
                $notificationModel = new \App\Models\NotificationModel();
                $userModel = new \App\Models\UserModel();
                $admins = $userModel->getAdmins();

                foreach ($admins as $admin) {
                    $notificationModel->createNotification(
                        $admin['id'],
                        'Pengaduan Baru',
                        'Ada pengaduan baru dengan nomor ' . $pengaduan['nomor_pengaduan'],
                        'info',
                        $pengaduanId
                    );
                }

                log_message('info', 'New pengaduan created: ' . $pengaduan['nomor_pengaduan'] . ' by user ID: ' . $userId);

                return $this->respond([
                    'success' => true,
                    'message' => 'Pengaduan berhasil dibuat',
                    'data' => [
                        'id' => $pengaduan['id'],
                        'uuid' => $pengaduan['uuid'],
                        'nomor_pengaduan' => $pengaduan['nomor_pengaduan'],
                        'status' => $pengaduan['status'],
                        'created_at' => $pengaduan['created_at']
                    ]
                ], 201);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Gagal membuat pengaduan'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error creating pengaduan: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function addComment($uuid)
    {
        $userId = $this->request->user_id;
        
        // Find pengaduan by UUID and verify ownership
        $pengaduan = $this->pengaduanModel->where('uuid', $uuid)
                                         ->where('user_id', $userId)
                                         ->first();

        if (!$pengaduan) {
            return $this->respond([
                'success' => false,
                'message' => 'Pengaduan tidak ditemukan'
            ], 404);
        }

        $validation = \Config\Services::validation();
        $rules = [
            'komentar' => [
                'rules' => 'required|min_length[5]|max_length[1000]',
                'errors' => [
                    'required' => 'Komentar harus diisi',
                    'min_length' => 'Komentar minimal 5 karakter',
                    'max_length' => 'Komentar maksimal 1000 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

        $komentarData = [
            'pengaduan_id' => $pengaduan['id'],
            'user_id' => $userId,
            'komentar' => $this->request->getPost('komentar'),
            'is_internal' => false
        ];

        try {
            $komentarId = $this->komentarModel->insert($komentarData);

            if ($komentarId) {
                // Note: Since assigned_to field is removed, notifications will be handled differently
                // or you can create notifications for all admins if needed

                log_message('info', 'Comment added to pengaduan ' . $pengaduan['nomor_pengaduan'] . ' by user ID: ' . $userId);

                return $this->respond([
                    'success' => true,
                    'message' => 'Komentar berhasil ditambahkan'
                ], 201);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Gagal menambahkan komentar'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error adding comment: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function getStatistics()
    {
        $userId = $this->request->user_id;

        // Get user's pengaduan statistics
        $stats = [
            'total' => $this->pengaduanModel->where('user_id', $userId)->countAllResults(),
            'pending' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'pending')->countAllResults(),
            'diproses' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'diproses')->countAllResults(),
            'selesai' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'selesai')->countAllResults(),
            'ditolak' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'ditolak')->countAllResults()
        ];

        return $this->respond([
            'success' => true,
            'message' => 'Statistik pengaduan berhasil diambil',
            'data' => $stats
        ]);
    }

    public function update($id = null)
    {
        $userId = $this->request->user_id;
        $pengaduan = $this->pengaduanModel->find($id);

        if (!$pengaduan) {
            return $this->failNotFound('Pengaduan tidak ditemukan.');
        }

        // Verify ownership
        if ($pengaduan['user_id'] != $userId) {
            return $this->failForbidden('Anda tidak memiliki akses untuk mengubah pengaduan ini.');
        }

        // Can only update if status is still pending
        if ($pengaduan['status'] !== 'pending') {
            return $this->fail('Pengaduan yang sudah diproses tidak dapat diubah.', 400);
        }

        $rules = [
            'deskripsi' => 'required|min_length[10]',
            'kategori_id' => 'required|numeric',
            'instansi_id' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'deskripsi' => $this->request->getVar('deskripsi'),
            'kategori_id' => $this->request->getVar('kategori_id'),
            'instansi_id' => $this->request->getVar('instansi_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle file upload if exists
        $file = $this->request->getFile('foto_bukti');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old photo if exists
            if (!empty($pengaduan['foto_bukti']) && file_exists('uploads/pengaduan/' . $pengaduan['foto_bukti'])) {
                unlink('uploads/pengaduan/' . $pengaduan['foto_bukti']);
            }
            
            // Generate new filename and move to uploads folder
            $newName = $pengaduan['nomor_pengaduan'] . '_' . time() . '.' . $file->getExtension();
            $file->move('uploads/pengaduan', $newName);
            $data['foto_bukti'] = $newName;
        }

        if ($this->pengaduanModel->update($id, $data)) {
            // Get updated pengaduan with relations
            $updatedPengaduan = $this->pengaduanModel->select('pengaduan.*, 
                                                instansi.nama as instansi_nama,
                                                kategori_pengaduan.nama as kategori_nama')
                                              ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                                              ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id')
                                              ->find($id);

            return $this->respond([
                'success' => true,
                'message' => 'Pengaduan berhasil diperbarui',
                'data' => $updatedPengaduan
            ]);
        }

        return $this->fail('Gagal memperbarui pengaduan.', 500);
    }

    public function delete($id = null)
    {
        $userId = $this->request->user_id;
        $pengaduan = $this->pengaduanModel->find($id);

        if (!$pengaduan) {
            return $this->failNotFound('Pengaduan tidak ditemukan.');
        }

        // Verify ownership
        if ($pengaduan['user_id'] != $userId) {
            return $this->failForbidden('Anda tidak memiliki akses untuk menghapus pengaduan ini.');
        }

        // Can only delete if status is still pending
        if ($pengaduan['status'] !== 'pending') {
            return $this->fail('Pengaduan yang sudah diproses tidak dapat dihapus.', 400);
        }

        // Delete foto_bukti if exists
        if (!empty($pengaduan['foto_bukti']) && file_exists('uploads/pengaduan/' . $pengaduan['foto_bukti'])) {
            unlink('uploads/pengaduan/' . $pengaduan['foto_bukti']);
        }

        if ($this->pengaduanModel->delete($id)) {
            return $this->respondDeleted([
                'success' => true,
                'message' => 'Pengaduan berhasil dihapus'
            ]);
        }

        return $this->fail('Gagal menghapus pengaduan.', 500);
    }

    public function cancelPengaduan($id = null)
    {
        $userId = $this->request->user_id;
        $pengaduan = $this->pengaduanModel->find($id);

        if (!$pengaduan) {
            return $this->failNotFound('Pengaduan tidak ditemukan.');
        }

        // Verify ownership
        if ($pengaduan['user_id'] != $userId) {
            return $this->failForbidden('Anda tidak memiliki akses untuk membatalkan pengaduan ini.');
        }

        // Can only cancel if status is pending or diproses
        if (!in_array($pengaduan['status'], ['pending', 'diproses'])) {
            return $this->fail('Pengaduan yang sudah selesai atau ditolak tidak dapat dibatalkan.', 400);
        }

        $data = [
            'status' => 'ditolak',
            'keterangan_admin' => 'Dibatalkan oleh pengguna pada ' . date('d/m/Y H:i'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->pengaduanModel->update($id, $data)) {
            // Create status history
            $statusHistoryModel = new \App\Models\StatusHistoryModel();
            $statusHistoryModel->insert([
                'pengaduan_id' => $id,
                'status_lama' => $pengaduan['status'],
                'status_baru' => 'ditolak',
                'keterangan' => 'Dibatalkan oleh pengguna',
                'changed_by' => $userId
            ]);

            return $this->respond([
                'success' => true,
                'message' => 'Pengaduan berhasil dibatalkan'
            ]);
        }

        return $this->fail('Gagal membatalkan pengaduan.', 500);
    }
}
