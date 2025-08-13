<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\UserModel;
use App\Models\InstansiModel;
use App\Models\KategoriPengaduanModel;
use App\Models\StatusHistoryModel;
use App\Models\KomentarPengaduanModel;

class PengaduanController extends BaseController
{
    protected $pengaduanModel;
    protected $userModel;
    protected $instansiModel;
    protected $kategoriModel;
    protected $statusHistoryModel;
    protected $komentarModel;

    public function __construct()
    {
        $this->pengaduanModel = new PengaduanModel();
        $this->userModel = new UserModel();
        $this->instansiModel = new InstansiModel();
        $this->kategoriModel = new KategoriPengaduanModel();
        $this->statusHistoryModel = new StatusHistoryModel();
        $this->komentarModel = new KomentarPengaduanModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $perPage = 20;
        $currentPage = $this->request->getGet('page') ?? 1;
        
        // Get filters
        $filters = [
            'status' => $this->request->getGet('status'),
            'kategori_id' => $this->request->getGet('kategori_id'),
            'instansi_id' => $this->request->getGet('instansi_id'),
            'search' => $this->request->getGet('search'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to')
        ];

        // Build query with filters
        $builder = $this->pengaduanModel->getPengaduanWithRelations();

        // Apply filters
        if ($filters['status']) {
            $builder->where('pengaduan.status', $filters['status']);
        }

        if ($filters['kategori_id']) {
            $builder->where('pengaduan.kategori_id', $filters['kategori_id']);
        }

        if ($filters['instansi_id']) {
            $builder->where('pengaduan.instansi_id', $filters['instansi_id']);
        }

        if ($filters['search']) {
            $builder->groupStart()
                    ->like('pengaduan.nomor_pengaduan', $filters['search'])
                    ->orLike('pengaduan.deskripsi', $filters['search'])
                    ->orLike('users.name', $filters['search'])
                    ->orLike('users.email', $filters['search'])
                    ->groupEnd();
        }

        if ($filters['date_from']) {
            $builder->where('DATE(pengaduan.created_at) >=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $builder->where('DATE(pengaduan.created_at) <=', $filters['date_to']);
        }

        $pengaduan = $builder->orderBy('pengaduan.created_at', 'DESC')
                            ->paginate($perPage);

        $pager = $this->pengaduanModel->pager;

        // Get dropdown data for filters
        $kategoriList = $this->kategoriModel->getActiveKategori();
        $instansiList = $this->instansiModel->getActiveInstansi();

        $data = [
            'title' => 'Data Pengaduan - Sistem Pengaduan Kominfo',
            'pengaduan' => $pengaduan,
            'pager' => $pager,
            'filters' => $filters,
            'kategori_list' => $kategoriList,
            'instansi_list' => $instansiList,
            'current_page' => $currentPage,
            'per_page' => $perPage
        ];

        return view('admin/pengaduan/index', $data);
    }

    public function show($id)
    {
        $pengaduan = $this->pengaduanModel->getPengaduanWithRelations($id);

        if (!$pengaduan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengaduan tidak ditemukan');
        }

        // Get status history
        $statusHistory = $this->statusHistoryModel->getHistoryByPengaduan($id);

        // Get comments
        $comments = $this->komentarModel->select('komentar_pengaduan.*, users.name as user_name, users.role as user_role')
                                      ->join('users', 'users.id = komentar_pengaduan.user_id')
                                      ->where('pengaduan_id', $id)
                                      ->orderBy('komentar_pengaduan.created_at', 'ASC')
                                      ->findAll();

        // Parse foto_bukti JSON
        $fotoBukti = [];
        if ($pengaduan['foto_bukti']) {
            $fotoBukti = json_decode($pengaduan['foto_bukti'], true) ?? [];
        }

        $data = [
            'title' => 'Detail Pengaduan - ' . $pengaduan['nomor_pengaduan'],
            'pengaduan' => $pengaduan,
            'status_history' => $statusHistory,
            'comments' => $comments,
            'foto_bukti' => $fotoBukti,
            'user_role' => session('user_role'),
            'user_id' => session('user_id')
        ];

        return view('admin/pengaduan/detail', $data);
    }

    public function updateStatus($id)
    {
        $pengaduan = $this->pengaduanModel->find($id);
        $isAjax = $this->request->isAJAX();

        if (!$pengaduan) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'Pengaduan tidak ditemukan']);
            } else {
                return redirect()->to('/admin/pengaduan')->with('error', 'Pengaduan tidak ditemukan');
            }
        }

        // Check authorization
        if (session('user_role') === 'admin') {
            // Admin users can modify any pengaduan now
        }

        $validation = \Config\Services::validation();
        $rules = [
            'status' => 'required|in_list[pending,diproses,selesai,ditolak]',
            'keterangan_admin' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validation->getErrors()
                ]);
            } else {
                return redirect()->to('/admin/pengaduan')->with('error', 'Data tidak valid');
            }
        }

        $status = $this->request->getPost('status');
        $keterangan = $this->request->getPost('keterangan_admin');

        try {
            $updateData = [
                'status' => $status,
                'keterangan_admin' => $keterangan
            ];

            if ($status === 'selesai') {
                $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
            }

            $result = $this->pengaduanModel->update($id, $updateData);

            if ($result) {
                // Create notification for user
                $notificationModel = new \App\Models\NotificationModel();
                $statusText = [
                    'pending' => 'Menunggu',
                    'diproses' => 'Sedang Diproses',
                    'selesai' => 'Selesai',
                    'ditolak' => 'Ditolak'
                ];

                $notificationModel->insert([
                    'user_id' => $pengaduan['user_id'],
                    'pengaduan_id' => $id,
                    'title' => 'Status Pengaduan Diperbarui',
                    'message' => 'Status pengaduan ' . $pengaduan['nomor_pengaduan'] . ' telah diubah menjadi: ' . $statusText[$status],
                    'type' => $status === 'selesai' ? 'success' : ($status === 'ditolak' ? 'error' : 'info')
                ]);

                log_message('info', 'Pengaduan ' . $pengaduan['nomor_pengaduan'] . ' status updated to ' . $status . ' by ' . session('user_name'));

                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Status pengaduan berhasil diperbarui'
                    ]);
                } else {
                    return redirect()->to('/admin/pengaduan')->with('success', 'Status pengaduan berhasil diperbarui');
                }
            } else {
                if ($isAjax) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Gagal memperbarui status']);
                } else {
                    return redirect()->to('/admin/pengaduan')->with('error', 'Gagal memperbarui status');
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'Error updating pengaduan status: ' . $e->getMessage());
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
            } else {
                return redirect()->to('/admin/pengaduan')->with('error', 'Terjadi kesalahan sistem');
            }
        }
    }

    public function addComment($id)
    {
        $pengaduan = $this->pengaduanModel->find($id);

        if (!$pengaduan) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pengaduan tidak ditemukan']);
        }

        // Check authorization
        if (session('user_role') === 'admin') {
            // Admin users can add comments to any pengaduan now
        }

        $validation = \Config\Services::validation();
        $rules = [
            'komentar' => 'required|min_length[5]|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ]);
        }

        $komentar = $this->request->getPost('komentar');

        try {
            $commentData = [
                'pengaduan_id' => $id,
                'user_id' => session('user_id'),
                'komentar' => $komentar,
                'is_internal' => false
            ];

            $result = $this->komentarModel->insert($commentData);

            if ($result) {
                // Create notification for user (all comments are visible now)
                $notificationModel = new \App\Models\NotificationModel();
                $notificationModel->insert([
                    'user_id' => $pengaduan['user_id'],
                    'pengaduan_id' => $id,
                    'title' => 'Komentar Baru pada Pengaduan',
                    'message' => 'Ada komentar baru pada pengaduan ' . $pengaduan['nomor_pengaduan'],
                    'type' => 'info'
                ]);

                log_message('info', 'Comment added to pengaduan ' . $pengaduan['nomor_pengaduan'] . ' by ' . session('user_name'));

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Komentar berhasil ditambahkan'
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal menambahkan komentar']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error adding comment: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    /**
     * Update a comment
     * 
     * @param int $id Comment ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateComment($id = null)
    {
        // Check if user is logged in
        if (!session('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda belum login']);
        }
        
        // Check if user is admin or master
        if (!in_array(session('user_role'), ['admin', 'master'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak']);
        }

        // Find the comment
        $comment = $this->komentarModel->find($id);
        if (!$comment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komentar tidak ditemukan']);
        }

        // Check if user is owner of the comment or a master user
        if ($comment['user_id'] != session('user_id') && session('user_role') !== 'master') {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda hanya dapat mengedit komentar yang Anda buat']);
        }

        // Validate input
        $validation = \Config\Services::validation();
        $rules = [
            'komentar' => 'required|min_length[5]|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ]);
        }

        $komentar = $this->request->getPost('komentar');

        try {
            $result = $this->komentarModel->update($id, [
                'komentar' => $komentar
            ]);

            if ($result) {
                // Get the pengaduan related to this comment
                $pengaduan = $this->pengaduanModel->find($comment['pengaduan_id']);
                
                log_message('info', 'Comment updated for pengaduan ' . ($pengaduan ? $pengaduan['nomor_pengaduan'] : $comment['pengaduan_id']) . ' by ' . session('user_name'));

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Komentar berhasil diperbarui'
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal memperbarui komentar']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error updating comment: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    public function edit($id)
    {
        $pengaduan = $this->pengaduanModel->getPengaduanWithRelations($id);

        if (!$pengaduan) {
            return redirect()->to('/admin/pengaduan')->with('error', 'Pengaduan tidak ditemukan');
        }

        // Check authorization for admin role
        if (session('user_role') === 'admin') {
            // Admin users can edit any pengaduan now
        }

        // Get dropdown data
        $kategoriList = $this->kategoriModel->getActiveKategori();
        $instansiList = $this->instansiModel->getActiveInstansi();

        $data = [
            'title' => 'Edit Pengaduan - Sistem Pengaduan Kominfo',
            'pengaduan' => $pengaduan,
            'kategori_list' => $kategoriList,
            'instansi_list' => $instansiList,
            'validation' => session('validation') ?? \Config\Services::validation()
        ];

        return view('admin/pengaduan/edit', $data);
    }

    public function update($id)
    {
        $pengaduan = $this->pengaduanModel->find($id);

        if (!$pengaduan) {
            return redirect()->to('/admin/pengaduan')->with('error', 'Pengaduan tidak ditemukan');
        }

        // Check authorization for admin role
        if (session('user_role') === 'admin') {
            // Admin users can update any pengaduan now
        }

        // Validation rules
        $rules = [
            'deskripsi' => 'required|min_length[10]',
            'kategori_id' => 'required|numeric',
            'instansi_id' => 'required|numeric',
            'status' => 'required|in_list[pending,diproses,selesai,ditolak]',
            'keterangan_admin' => 'permit_empty|max_length[1000]',
            'foto_bukti' => 'permit_empty|is_image[foto_bukti]|max_size[foto_bukti,2048]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Get form data
        $oldStatus = $pengaduan['status'];
        $newStatus = $this->request->getPost('status');
        
        // Prepare update data
        $updateData = [
            'deskripsi' => $this->request->getPost('deskripsi'),
            'kategori_id' => $this->request->getPost('kategori_id'),
            'instansi_id' => $this->request->getPost('instansi_id'),
            'status' => $newStatus,
            'keterangan_admin' => $this->request->getPost('keterangan_admin')
        ];

        // Handle foto bukti if uploaded
        $file = $this->request->getFile('foto_bukti');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old photo if exists
            if (!empty($pengaduan['foto_bukti']) && file_exists('uploads/pengaduan/' . $pengaduan['foto_bukti'])) {
                unlink('uploads/pengaduan/' . $pengaduan['foto_bukti']);
            }
            
            // Generate new filename and move to uploads folder
            $newName = $pengaduan['nomor_pengaduan'] . '_' . time() . '.' . $file->getExtension();
            $file->move('uploads/pengaduan', $newName);
            $updateData['foto_bukti'] = $newName;
        }

        // If status changed to selesai, set completion date
        if ($newStatus === 'selesai' && $oldStatus !== 'selesai') {
            $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
        }

        try {
            // Update pengaduan
            $result = $this->pengaduanModel->update($id, $updateData);

            if ($result) {
                // Add status history if status changed
                if ($oldStatus !== $newStatus) {
                    $this->statusHistoryModel->insert([
                        'pengaduan_id' => $id,
                        'status_old' => $oldStatus,
                        'status_new' => $newStatus,
                        'keterangan' => 'Status diubah oleh ' . session('user_name'),
                        'updated_by' => session('user_id')
                    ]);

                    // Create notification for user
                    $notificationModel = new \App\Models\NotificationModel();
                    $statusText = [
                        'pending' => 'Menunggu',
                        'diproses' => 'Sedang Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak'
                    ];

                    $notificationModel->insert([
                        'user_id' => $pengaduan['user_id'],
                        'pengaduan_id' => $id,
                        'title' => 'Pengaduan Diperbarui',
                        'message' => 'Pengaduan ' . $pengaduan['nomor_pengaduan'] . ' telah diperbarui. Status: ' . $statusText[$newStatus],
                        'type' => $newStatus === 'selesai' ? 'success' : ($newStatus === 'ditolak' ? 'error' : 'info')
                    ]);
                }

                log_message('info', 'Pengaduan ' . $pengaduan['nomor_pengaduan'] . ' updated by ' . session('user_name'));

                return redirect()->to('/admin/pengaduan/' . $id)->with('success', 'Pengaduan berhasil diperbarui');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengaduan');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error updating pengaduan: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem');
        }
    }
}
