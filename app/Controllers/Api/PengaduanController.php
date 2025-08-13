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

    /**
     * Helper method to process foto_bukti
     */
    private function processFotoBukti($fotoBukti)
    {
        if (!$fotoBukti) {
            return [];
        }

        $decoded = json_decode($fotoBukti, true);
        if ($decoded && is_array($decoded)) {
            return array_map(function($foto) {
                return base_url('uploads/pengaduan/' . $foto);
            }, $decoded);
        }

        return [];
    }

    /**
     * Helper method to create admin notifications
     */
    private function createAdminNotifications($pengaduanId, $nomorPengaduan)
    {
        try {
            $notificationModel = new \App\Models\NotificationModel();
            $userModel = new \App\Models\UserModel();
            $admins = $userModel->where('role', 'admin')->orWhere('role', 'master')->findAll();

            foreach ($admins as $admin) {
                $notificationModel->insert([
                    'user_id' => $admin['id'],
                    'title' => 'Pengaduan Baru',
                    'message' => 'Ada pengaduan baru dengan nomor ' . $nomorPengaduan,
                    'type' => 'info',
                    'data' => json_encode(['pengaduan_id' => $pengaduanId]),
                    'is_read' => false
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating admin notifications: ' . $e->getMessage());
        }
    }

    public function index()
    {
        try {
            $userId = $this->request->user_id;
            $page = max(1, (int)($this->request->getGet('page') ?? 1));
            $limit = max(1, min(50, (int)($this->request->getGet('limit') ?? 10)));
            $status = $this->request->getGet('status');
            $search = $this->request->getGet('search');
            $kategoriId = $this->request->getGet('kategori_id');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');

            // Build query
            $builder = $this->pengaduanModel->select('pengaduan.*, 
                                                     instansi.nama as instansi_nama,
                                                     kategori_pengaduan.nama as kategori_nama,
                                                     users.name as user_name,
                                                     users.email as user_email')
                                           ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                                           ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id')
                                           ->join('users', 'users.id = pengaduan.user_id')
                                           ->where('pengaduan.user_id', $userId);

            // Apply filters
            if ($status && in_array($status, ['pending', 'diproses', 'selesai', 'ditolak'])) {
                $builder->where('pengaduan.status', $status);
            }

            if ($search) {
                $builder->groupStart()
                        ->like('pengaduan.nomor_pengaduan', $search)
                        ->orLike('pengaduan.deskripsi', $search)
                        ->groupEnd();
            }

            if ($kategoriId) {
                $builder->where('pengaduan.kategori_id', $kategoriId);
            }

            if ($dateFrom) {
                $builder->where('pengaduan.created_at >=', $dateFrom);
            }

            if ($dateTo) {
                $builder->where('pengaduan.created_at <=', $dateTo);
            }

            // Get total count for pagination
            $total = $builder->countAllResults(false);

            // Get paginated results
            $pengaduan = $builder->orderBy('pengaduan.created_at', 'DESC')
                                ->limit($limit, ($page - 1) * $limit)
                                ->find();

            // Process foto_bukti for each pengaduan
            foreach ($pengaduan as &$p) {
                $p['foto_bukti'] = $this->processFotoBukti($p['foto_bukti']);
                $p['created_at_formatted'] = date('d/m/Y H:i', strtotime($p['created_at']));
                $p['updated_at_formatted'] = date('d/m/Y H:i', strtotime($p['updated_at']));
            }

            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ];

            return response()->json([
                'status' => true,
                'message' => 'Data pengaduan berhasil diambil',
                'data' => [
                    'pengaduan' => $pengaduan,
                    'pagination' => $pagination
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in PengaduanController@index: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengaduan',
                'data' => null
            ], 500);
        }
    }

    public function show($id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID pengaduan tidak valid',
                    'data' => null
                ], 400);
            }

            $userId = $this->request->user_id;
            
            // Find pengaduan by UUID or ID
            $pengaduan = $this->pengaduanModel->select('pengaduan.*, 
                                       instansi.nama as instansi_nama,
                                       kategori_pengaduan.nama as kategori_nama,
                                       users.name as user_name,
                                       users.email as user_email')
                                 ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                                 ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id')
                                 ->join('users', 'users.id = pengaduan.user_id')
                                 ->where('pengaduan.user_id', $userId)
                                 ->groupStart()
                                 ->where('pengaduan.uuid', $id)
                                 ->orWhere('pengaduan.id', $id)
                                 ->groupEnd()
                                 ->first();

            if (!$pengaduan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            // Process foto_bukti
            $pengaduan['foto_bukti'] = $this->processFotoBukti($pengaduan['foto_bukti']);
            $pengaduan['created_at_formatted'] = date('d/m/Y H:i', strtotime($pengaduan['created_at']));
            $pengaduan['updated_at_formatted'] = date('d/m/Y H:i', strtotime($pengaduan['updated_at']));

            // Get status history
            $statusHistoryModel = new \App\Models\StatusHistoryModel();
            $statusHistory = $statusHistoryModel->getHistoryByPengaduan($pengaduan['id']);

            // Get comments
            $comments = $this->komentarModel->getKomentarByPengaduan($pengaduan['id'], true);

            return response()->json([
                'status' => true,
                'message' => 'Detail pengaduan berhasil diambil',
                'data' => [
                    'pengaduan' => $pengaduan,
                    'status_history' => $statusHistory ?? [],
                    'comments' => $comments ?? []
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in PengaduanController@show: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail pengaduan',
                'data' => null
            ], 500);
        }
    }

    public function create()
    {
        try {
            $userId = $this->request->user_id;
            
            // Validation rules
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
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'data' => [
                        'errors' => $validation->getErrors()
                    ]
                ], 400);
            }

            // Verify instansi and kategori exist
            $instansi = $this->instansiModel->find($this->request->getPost('instansi_id'));
            $kategori = $this->kategoriModel->find($this->request->getPost('kategori_id'));

            if (!$instansi || !$instansi['is_active']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Instansi tidak valid atau tidak aktif',
                    'data' => null
                ], 400);
            }

            if (!$kategori || !$kategori['is_active']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kategori tidak valid atau tidak aktif',
                    'data' => null
                ], 400);
            }

            $pengaduanData = [
                'user_id' => $userId,
                'instansi_id' => $this->request->getPost('instansi_id'),
                'kategori_id' => $this->request->getPost('kategori_id'),
                'deskripsi' => $this->request->getPost('deskripsi'),
                'status' => 'pending'
            ];

            // Handle file uploads
            $fotoBukti = [];
            $files = $this->request->getFiles();
            
            if (isset($files['foto_bukti'])) {
                $uploadedFiles = $files['foto_bukti'];
                
                // Handle single file or multiple files
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($uploadedFiles as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        // Validate file type
                        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!in_array($file->getMimeType(), $allowedTypes)) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Hanya file gambar (JPG, JPEG, PNG, GIF) yang diperbolehkan',
                                'data' => null
                            ], 400);
                        }

                        // Validate file size (max 5MB)
                        if ($file->getSize() > 5 * 1024 * 1024) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Ukuran file maksimal 5MB',
                                'data' => null
                            ], 400);
                        }

                        // Generate unique filename
                        $fileName = uniqid() . '_' . time() . '.' . $file->getExtension();
                        
                        // Move file
                        if ($file->move(ROOTPATH . 'public/uploads/pengaduan', $fileName)) {
                            $fotoBukti[] = $fileName;
                        }
                    }
                }
            }

            // Handle foto_bukti from JSON string (for compatibility)
            $fotoBuktiJson = $this->request->getPost('foto_bukti');
            if ($fotoBuktiJson && is_string($fotoBuktiJson)) {
                $decoded = json_decode($fotoBuktiJson, true);
                if ($decoded && is_array($decoded)) {
                    $fotoBukti = array_merge($fotoBukti, $decoded);
                }
            }

            if (!empty($fotoBukti)) {
                $pengaduanData['foto_bukti'] = json_encode($fotoBukti);
            }

            $pengaduanId = $this->pengaduanModel->insert($pengaduanData);

            if ($pengaduanId) {
                // Get the created pengaduan
                $pengaduan = $this->pengaduanModel->select('pengaduan.*, 
                                           instansi.nama as instansi_nama,
                                           kategori_pengaduan.nama as kategori_nama')
                                   ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                                   ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id')
                                   ->find($pengaduanId);

                // Create notification for admins
                $this->createAdminNotifications($pengaduanId, $pengaduan['nomor_pengaduan']);

                log_message('info', 'New pengaduan created: ' . $pengaduan['nomor_pengaduan'] . ' by user ID: ' . $userId);

                return response()->json([
                    'status' => true,
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
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal membuat pengaduan',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error creating pengaduan: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function addComment($uuid)
    {
        try {
            if (!$uuid) {
                return response()->json([
                    'status' => false,
                    'message' => 'UUID pengaduan tidak valid',
                    'data' => null
                ], 400);
            }

            $userId = $this->request->user_id;
            
            // Find pengaduan by UUID and verify ownership
            $pengaduan = $this->pengaduanModel->where('uuid', $uuid)
                                             ->where('user_id', $userId)
                                             ->first();

            if (!$pengaduan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
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
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'data' => [
                        'errors' => $validation->getErrors()
                    ]
                ], 400);
            }

            $komentarData = [
                'pengaduan_id' => $pengaduan['id'],
                'user_id' => $userId,
                'komentar' => $this->request->getPost('komentar'),
                'is_internal' => false
            ];

            $komentarId = $this->komentarModel->insert($komentarData);

            if ($komentarId) {
                log_message('info', 'Comment added to pengaduan ' . $pengaduan['nomor_pengaduan'] . ' by user ID: ' . $userId);

                return response()->json([
                    'status' => true,
                    'message' => 'Komentar berhasil ditambahkan',
                    'data' => [
                        'komentar_id' => $komentarId
                    ]
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menambahkan komentar',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error adding comment: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function getStatistics()
    {
        try {
            $userId = $this->request->user_id;

            // Get user's pengaduan statistics
            $stats = [
                'total' => $this->pengaduanModel->where('user_id', $userId)->countAllResults(),
                'pending' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'pending')->countAllResults(),
                'diproses' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'diproses')->countAllResults(),
                'selesai' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'selesai')->countAllResults(),
                'ditolak' => $this->pengaduanModel->where('user_id', $userId)->where('status', 'ditolak')->countAllResults()
            ];

            return response()->json([
                'status' => true,
                'message' => 'Statistik pengaduan berhasil diambil',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in PengaduanController@getStatistics: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik',
                'data' => null
            ], 500);
        }
    }

    public function update($id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID pengaduan tidak valid',
                    'data' => null
                ], 400);
            }

            $userId = $this->request->user_id;
            $pengaduan = $this->pengaduanModel->find($id);

            if (!$pengaduan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            // Verify ownership
            if ($pengaduan['user_id'] != $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengubah pengaduan ini',
                    'data' => null
                ], 403);
            }

            // Can only update if status is still pending
            if ($pengaduan['status'] !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan yang sudah diproses tidak dapat diubah',
                    'data' => null
                ], 400);
            }

            // Validation rules
            $validation = \Config\Services::validation();
            $rules = [
                'deskripsi' => [
                    'rules' => 'required|min_length[10]|max_length[2000]',
                    'errors' => [
                        'required' => 'Deskripsi harus diisi',
                        'min_length' => 'Deskripsi minimal 10 karakter',
                        'max_length' => 'Deskripsi maksimal 2000 karakter'
                    ]
                ],
                'kategori_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Kategori harus dipilih',
                        'integer' => 'ID kategori tidak valid'
                    ]
                ],
                'instansi_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Instansi harus dipilih',
                        'integer' => 'ID instansi tidak valid'
                    ]
                ]
            ];

            if (!$this->validate($rules)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'data' => [
                        'errors' => $validation->getErrors()
                    ]
                ], 400);
            }

            $data = [
                'deskripsi' => $this->request->getVar('deskripsi'),
                'kategori_id' => $this->request->getVar('kategori_id'),
                'instansi_id' => $this->request->getVar('instansi_id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle file uploads
            $files = $this->request->getFiles();
            if (isset($files['foto_bukti'])) {
                $fotoBukti = [];
                $uploadedFiles = $files['foto_bukti'];
                
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($uploadedFiles as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $fileName = uniqid() . '_' . time() . '.' . $file->getExtension();
                        if ($file->move(ROOTPATH . 'public/uploads/pengaduan', $fileName)) {
                            $fotoBukti[] = $fileName;
                        }
                    }
                }

                if (!empty($fotoBukti)) {
                    // Delete old photos
                    $oldFotoBukti = json_decode($pengaduan['foto_bukti'], true);
                    if ($oldFotoBukti && is_array($oldFotoBukti)) {
                        foreach ($oldFotoBukti as $oldFoto) {
                            $filePath = ROOTPATH . 'public/uploads/pengaduan/' . $oldFoto;
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }
                    }
                    
                    $data['foto_bukti'] = json_encode($fotoBukti);
                }
            }

            if ($this->pengaduanModel->update($id, $data)) {
                // Get updated pengaduan with relations
                $updatedPengaduan = $this->pengaduanModel->select('pengaduan.*, 
                                                    instansi.nama as instansi_nama,
                                                    kategori_pengaduan.nama as kategori_nama')
                                                  ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                                                  ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id')
                                                  ->find($id);

                $updatedPengaduan['foto_bukti'] = $this->processFotoBukti($updatedPengaduan['foto_bukti']);

                return response()->json([
                    'status' => true,
                    'message' => 'Pengaduan berhasil diperbarui',
                    'data' => $updatedPengaduan
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui pengaduan',
                'data' => null
            ], 500);

        } catch (\Exception $e) {
            log_message('error', 'Error in PengaduanController@update: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function delete($id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID pengaduan tidak valid',
                    'data' => null
                ], 400);
            }

            $userId = $this->request->user_id;
            $pengaduan = $this->pengaduanModel->find($id);

            if (!$pengaduan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            // Verify ownership
            if ($pengaduan['user_id'] != $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus pengaduan ini',
                    'data' => null
                ], 403);
            }

            // Can only delete if status is still pending
            if ($pengaduan['status'] !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan yang sudah diproses tidak dapat dihapus',
                    'data' => null
                ], 400);
            }

            // Delete foto_bukti files
            if (!empty($pengaduan['foto_bukti'])) {
                $fotoBukti = json_decode($pengaduan['foto_bukti'], true);
                if ($fotoBukti && is_array($fotoBukti)) {
                    foreach ($fotoBukti as $foto) {
                        $filePath = ROOTPATH . 'public/uploads/pengaduan/' . $foto;
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            }

            if ($this->pengaduanModel->delete($id)) {
                return response()->json([
                    'status' => true,
                    'message' => 'Pengaduan berhasil dihapus',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus pengaduan',
                'data' => null
            ], 500);

        } catch (\Exception $e) {
            log_message('error', 'Error in PengaduanController@delete: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function cancelPengaduan($id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID pengaduan tidak valid',
                    'data' => null
                ], 400);
            }

            $userId = $this->request->user_id;
            $pengaduan = $this->pengaduanModel->find($id);

            if (!$pengaduan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            // Verify ownership
            if ($pengaduan['user_id'] != $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak memiliki akses untuk membatalkan pengaduan ini',
                    'data' => null
                ], 403);
            }

            // Can only cancel if status is pending or diproses
            if (!in_array($pengaduan['status'], ['pending', 'diproses'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengaduan yang sudah selesai atau ditolak tidak dapat dibatalkan',
                    'data' => null
                ], 400);
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

                return response()->json([
                    'status' => true,
                    'message' => 'Pengaduan berhasil dibatalkan',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Gagal membatalkan pengaduan',
                'data' => null
            ], 500);

        } catch (\Exception $e) {
            log_message('error', 'Error in PengaduanController@cancelPengaduan: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }
}
