<?php

namespace App\Controllers\Api;

use App\Models\PengaduanModel;
use App\Models\KategoriPengaduanModel;
use App\Models\StatusHistoryModel;

/**
 * API Pengaduan Controller
 * 
 * Handles CRUD operations for pengaduan (complaints) in mobile app
 */
class PengaduanController extends ApiController
{
    protected $pengaduanModel;
    protected $kategoriModel;
    protected $statusHistoryModel;
    
    public function __construct()
    {
        $this->pengaduanModel = new PengaduanModel();
        $this->kategoriModel = new KategoriPengaduanModel();
        $this->statusHistoryModel = new StatusHistoryModel();
    }

    /**
     * Get list of pengaduan for authenticated user
     * 
     * GET /api/pengaduan?page=1
     * Response 200: { "status": true, "message":"List pengaduan", "data": { "items": [...], "meta": {...} } }
     */
    public function index()
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10; // Default per page
        
        try {
            $userId = $this->getAuthUserId();
            
            // Check if user is authenticated
            if (!$userId) {
                return $this->respondError('Unauthorized', 401);
            }
            
            // Get pengaduan with relations
            $builder = $this->pengaduanModel->getPengaduanWithRelations()
                            ->where('pengaduan.user_id', $userId);
            
            // Apply search filters if provided
            $search = $this->request->getGet('search');
            if ($search) {
                $builder->groupStart()
                        ->like('pengaduan.nomor_pengaduan', $search)
                        ->orLike('pengaduan.deskripsi', $search)
                        ->groupEnd();
            }
            
            // Apply status filter if provided
            $status = $this->request->getGet('status');
            if ($status) {
                $builder->where('pengaduan.status', $status);
            }
            
            // Apply date range filters if provided
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');
            if ($dateFrom) {
                $builder->where('DATE(pengaduan.created_at) >=', $dateFrom);
            }
            if ($dateTo) {
                $builder->where('DATE(pengaduan.created_at) <=', $dateTo);
            }
            
            // Order by created_at descending (newest first)
            $builder->orderBy('pengaduan.created_at', 'DESC');
            
            // Paginate the results
            $pengaduan = $builder->paginate($perPage, 'default', $page);
            $pager = $this->pengaduanModel->pager;
            
            // Process pengaduan items to format foto_bukti as array
            $items = $pengaduan;
            foreach ($items as &$item) {
                if (!empty($item['foto_bukti'])) {
                    $filenames = json_decode($item['foto_bukti'], true) ?? [];
                    $item['foto_bukti'] = [];
                    foreach ($filenames as $filename) {
                        $item['foto_bukti'][] = base_url('uploads/pengaduan/' . $filename);
                    }
                } else {
                    $item['foto_bukti'] = [];
                }
            }
            
            // Return response
            return $this->respondSuccess([
                'items' => $items,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $pager->getTotal(),
                    'total_pages' => ceil($pager->getTotal() / $perPage)
                ]
            ], 'List pengaduan');
            
        } catch (\Exception $e) {
            log_message('error', 'Get pengaduan list error: ' . $e->getMessage());
            return $this->respondError('Failed to get pengaduan list: ' . $e->getMessage());
        }
    }

    /**
     * Get pengaduan detail by ID
     * 
     * GET /api/pengaduan/{id}
     * Response 200: { "status": true, "message":"Detail pengaduan", "data": { "pengaduan": {...}, "history": [...] } }
     * Response 404: { "status": false, "message":"Pengaduan not found", "data": null }
     */
    public function show($id = null)
    {
        // Set CORS headers
        $this->setCorsHeaders();
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        try {
            $userId = $this->getAuthUserId();
            
            // Check if user is authenticated
            if (!$userId) {
                return $this->respondError('Unauthorized', 401);
            }
            
            // Get pengaduan with relations
            $pengaduan = $this->pengaduanModel->getPengaduanWithRelations($id);
            
            // Check if pengaduan exists and belongs to the authenticated user
            if (!$pengaduan || $pengaduan['user_id'] != $userId) {
                return $this->respondNotFound('Pengaduan not found');
            }
            
            // Get status history
            $history = $this->statusHistoryModel->getHistoryByPengaduan($id);
            
            // Process foto_bukti - convert filenames to URLs
            if (!empty($pengaduan['foto_bukti'])) {
                $filenames = json_decode($pengaduan['foto_bukti'], true) ?? [];
                $pengaduan['foto_bukti'] = [];
                foreach ($filenames as $filename) {
                    $pengaduan['foto_bukti'][] = base_url('uploads/pengaduan/' . $filename);
                }
            } else {
                $pengaduan['foto_bukti'] = [];
            }
            
            // Return response
            return $this->respondSuccess([
                'pengaduan' => $pengaduan,
                'history' => $history
            ], 'Detail pengaduan');
            
        } catch (\Exception $e) {
            log_message('error', 'Get pengaduan detail error: ' . $e->getMessage());
            return $this->respondError('Failed to get pengaduan detail');
        }
    }

    /**
     * Create a new pengaduan
     * 
     * POST /api/pengaduan
     * FormData fields: judul, isi, kategori_id, lokasi, foto (file)
     * Response 201: { "status": true, "message":"Pengaduan dibuat", "data": { "pengaduan": {...} } }
     * Response 400: { "status": false, "message":"Validation Error", "data": { "errors": {...} } }
     */
    public function create()
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        // Validation rules
        $rules = [
            'deskripsi' => 'required|min_length[10]|max_length[2000]',
            'kategori_id' => 'required|integer|is_not_unique[kategori_pengaduan.id]',
        ];
        
        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }
        
        try {
            $userId = $this->getAuthUserId();
            
            // Check if user is authenticated
            if (!$userId) {
                return $this->respondError('Unauthorized - Please login first', 401);
            }
            
            // Get user details for instansi_id
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
            
            if (!$user) {
                return $this->respondError('User not found', 404);
            }
            
            $deskripsi = $this->request->getPost('deskripsi');
            $kategoriId = $this->request->getPost('kategori_id');
            $fotoPaths = [];
            
            // Handle file uploads - use public folder for direct access
            $uploadPath = FCPATH . 'uploads/pengaduan/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Handle file uploads - try both single and multiple file approaches
            $files = [];
            
            // First try to get as multiple files (foto_bukti[])
            $multipleFiles = $this->request->getFileMultiple('foto_bukti');
            
            if ($multipleFiles && is_array($multipleFiles)) {
                $files = $multipleFiles;
            } else {
                // Try to get as single file (foto_bukti)
                $singleFile = $this->request->getFile('foto_bukti');
                if ($singleFile && $singleFile->isValid()) {
                    $files = [$singleFile];
                }
            }
            
            // Debug uploaded files
            log_message('debug', '[PengaduanController::create] Files received: ' . count($files) . ' files found');
            log_message('debug', '[PengaduanController::create] POST data: ' . json_encode($this->request->getPost()));
            log_message('debug', '[PengaduanController::create] FILES array: ' . json_encode($_FILES));
            
            if (!empty($files)) {
                log_message('debug', '[PengaduanController::create] Processing ' . count($files) . ' files');
                foreach ($files as $index => $file) {
                    log_message('debug', '[PengaduanController::create] File ' . $index . ': Valid=' . ($file ? $file->isValid() : 'null') . ', Moved=' . ($file ? $file->hasMoved() : 'null'));
                    
                    if ($file && $file->isValid() && !$file->hasMoved()) {
                        // Check file type
                        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                        $fileExtension = strtolower($file->getExtension());
                        log_message('debug', '[PengaduanController::create] File extension: ' . $fileExtension);
                        
                        if (!in_array($fileExtension, $allowedTypes)) {
                            log_message('debug', '[PengaduanController::create] Skipping file - invalid type: ' . $fileExtension);
                            continue; // Skip invalid file types
                        }
                        
                        // Check file size (5MB max)
                        if ($file->getSize() > 5 * 1024 * 1024) {
                            log_message('debug', '[PengaduanController::create] Skipping file - too large: ' . $file->getSize());
                            continue; // Skip files larger than 5MB
                        }
                        
                        $newName = $file->getRandomName();
                        $file->move($uploadPath, $newName);
                        $fotoPaths[] = $newName; // Store filename only, not full URL
                        log_message('debug', '[PengaduanController::create] File saved: ' . $newName);
                    }
                }
            } else {
                log_message('debug', '[PengaduanController::create] No files to process');
            }

            // Generate nomor pengaduan
            $currentYear = date('Y');
            $currentMonth = date('m');
            $count = $this->pengaduanModel->where('EXTRACT(YEAR FROM created_at)', $currentYear)
                                         ->where('EXTRACT(MONTH FROM created_at)', $currentMonth)
                                         ->countAllResults();
            $nomorPengaduan = 'ADU' . $currentYear . $currentMonth . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            // Generate UUID manually
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            // Prepare data for insertion
            $data = [
                'uuid' => $uuid,
                'nomor_pengaduan' => $nomorPengaduan,
                'user_id' => $userId,
                'instansi_id' => $user['instansi_id'],
                'kategori_id' => $kategoriId,
                'deskripsi' => $deskripsi,
                'foto_bukti' => json_encode($fotoPaths),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Insert pengaduan
            $insertId = $this->pengaduanModel->insert($data);
            
            if (!$insertId) {
                return $this->respondError('Gagal menyimpan pengaduan', 500);
            }

            // Get the created pengaduan
            $pengaduan = $this->pengaduanModel->find($insertId);

            // Process foto_bukti for response - convert filenames to URLs
            if (!empty($pengaduan['foto_bukti'])) {
                $filenames = json_decode($pengaduan['foto_bukti'], true) ?? [];
                $pengaduan['foto_bukti'] = [];
                foreach ($filenames as $filename) {
                    $pengaduan['foto_bukti'][] = base_url('uploads/pengaduan/' . $filename);
                }
            } else {
                $pengaduan['foto_bukti'] = [];
            }

            return $this->respondCreated([
                'status' => true,
                'message' => 'Pengaduan berhasil dibuat',
                'data' => [
                    'pengaduan' => $pengaduan
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', '[PengaduanController::create] Error: ' . $e->getMessage());
            return $this->respondError('Terjadi kesalahan server: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing pengaduan
     * 
     * PUT /api/pengaduan/{id}
     * Request JSON or FormData: { "judul": "...", "isi": "...", "kategori_id": 1, "lokasi": "..." }
     * Response 200: { "status": true, "message":"Pengaduan diperbarui", "data": { "pengaduan": {...} } }
     * Response 404: { "status": false, "message":"Pengaduan not found", "data": null }
     */
    public function update($id = null)
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        // Define validation rules for all possible fields
        $rules = [
            'judul' => 'permit_empty|min_length[3]|max_length[255]',
            'isi' => 'permit_empty|min_length[10]|max_length[2000]',
            'kategori_id' => 'permit_empty|integer|is_not_unique[kategori_pengaduan.id]',
            'lokasi' => 'permit_empty|max_length[255]',
            'foto' => 'permit_empty|is_image[foto]|max_size[foto,5120]', // 5MB max
            'delete_photos' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }

        try {
            $userId = $this->getAuthUserId();
            
            // Check if user is authenticated
            if (!$userId) {
                return $this->respondError('Unauthorized', 401);
            }
            
            // Check if pengaduan exists and belongs to the authenticated user
            $pengaduan = $this->pengaduanModel->find($id);
            if (!$pengaduan) {
                return $this->respondNotFound('Pengaduan tidak ditemukan');
            }
            
            if ($pengaduan['user_id'] != $userId) {
                return $this->respondForbidden('Anda tidak memiliki akses untuk mengedit pengaduan ini');
            }
            
            // Check if pengaduan is still editable (only pending can be edited)
            if ($pengaduan['status'] !== 'pending') {
                return $this->respondError('Pengaduan tidak dapat diedit karena status sudah ' . $pengaduan['status'], 403);
            }
            
            // Prepare data for update
            $data = [];
            $contentType = $this->request->getHeaderLine('Content-Type');
            $isJsonRequest = strpos($contentType, 'application/json') !== false;
            
            // Handle judul field (mapped to a column in the database if needed)
            $judul = $isJsonRequest 
                ? $this->request->getJSON()->judul ?? null 
                : $this->request->getVar('judul');
            if ($judul !== null) {
                // Assuming there's no judul column and it's part of deskripsi or another field
                // If there's a specific judul column, just set $data['judul'] = $judul
                $data['judul'] = $judul;
            }
            
            // Handle description
            $isi = $isJsonRequest 
                ? $this->request->getJSON()->isi ?? null 
                : $this->request->getVar('isi');
            if ($isi !== null) {
                $data['deskripsi'] = $isi;
            }
            
            // Handle kategori
            $kategoriId = $isJsonRequest 
                ? $this->request->getJSON()->kategori_id ?? null 
                : $this->request->getVar('kategori_id');
            if ($kategoriId !== null) {
                $data['kategori_id'] = $kategoriId;
            }
            
            // Handle lokasi
            $lokasi = $isJsonRequest 
                ? $this->request->getJSON()->lokasi ?? null 
                : $this->request->getVar('lokasi');
            if ($lokasi !== null) {
                $data['lokasi'] = $lokasi;
            }
            
            // Handle photo deletion if requested
            $deletePhotos = $isJsonRequest 
                ? $this->request->getJSON()->delete_photos ?? null 
                : $this->request->getVar('delete_photos');
            
            $existingFoto = json_decode($pengaduan['foto_bukti'] ?? '[]', true) ?? [];
            
            if ($deletePhotos !== null) {
                // If delete_photos is an array, remove those specific photos
                if (is_array($deletePhotos)) {
                    // Get filenames from existing photos
                    $uploadPath = WRITEPATH . 'uploads/pengaduan/';
                    foreach ($deletePhotos as $photoToDelete) {
                        $index = array_search($photoToDelete, $existingFoto);
                        if ($index !== false) {
                            // Delete the file if it exists
                            if (file_exists($uploadPath . $photoToDelete)) {
                                unlink($uploadPath . $photoToDelete);
                            }
                            // Remove from array
                            unset($existingFoto[$index]);
                        }
                    }
                    // Reindex array
                    $existingFoto = array_values($existingFoto);
                } 
                // If delete_photos is 'all', clear all photos
                elseif ($deletePhotos === 'all') {
                    $uploadPath = WRITEPATH . 'uploads/pengaduan/';
                    foreach ($existingFoto as $photo) {
                        if (file_exists($uploadPath . $photo)) {
                            unlink($uploadPath . $photo);
                        }
                    }
                    $existingFoto = [];
                }
                
                // Update foto_bukti with the modified array
                $data['foto_bukti'] = json_encode($existingFoto);
            }
            
            // Handle file upload if provided
            $uploadPath = WRITEPATH . 'uploads/pengaduan/';
            
            // For JSON requests, no file uploads are expected
            if (!$isJsonRequest) {
                $files = $this->request->getFiles();
                
                if (isset($files['foto']) && !empty($files['foto'])) {
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }
                    
                    $fotoFiles = $files['foto'];
                    $fotoBukti = [];
                    
                    // Handle multiple files
                    if (is_array($fotoFiles)) {
                        foreach ($fotoFiles as $foto) {
                            if ($foto->isValid() && !$foto->hasMoved()) {
                                $newName = $foto->getRandomName();
                                $foto->move($uploadPath, $newName);
                                $fotoBukti[] = $newName;
                            }
                        }
                    } else {
                        // Handle single file
                        if ($fotoFiles->isValid() && !$fotoFiles->hasMoved()) {
                            $newName = $fotoFiles->getRandomName();
                            $fotoFiles->move($uploadPath, $newName);
                            $fotoBukti[] = $newName;
                        }
                    }
                    
                    if (!empty($fotoBukti)) {
                        // Merge with existing photos (after deletion if any)
                        $allFoto = array_merge($existingFoto, $fotoBukti);
                        $data['foto_bukti'] = json_encode($allFoto);
                    }
                }
            }
            
            // Update pengaduan if there are changes
            if (!empty($data)) {
                $this->pengaduanModel->update($id, $data);
                
                // Log the action
                log_message('info', "Pengaduan {$id} updated by user {$userId} with data: " . json_encode($data));
            }
            
            // Get the updated pengaduan with relations
            $updatedPengaduan = $this->pengaduanModel->getPengaduanWithRelations($id);
            
            // Get status history
            $history = $this->statusHistoryModel->getHistoryByPengaduan($id);
            
            // Process foto_bukti for response
            if (!empty($updatedPengaduan['foto_bukti'])) {
                $updatedPengaduan['foto_bukti'] = json_decode($updatedPengaduan['foto_bukti'], true) ?? [];
                // Convert to full URLs
                if (is_array($updatedPengaduan['foto_bukti'])) {
                    foreach ($updatedPengaduan['foto_bukti'] as &$foto) {
                        $foto = base_url('uploads/pengaduan/' . $foto);
                    }
                }
            } else {
                $updatedPengaduan['foto_bukti'] = [];
            }
            
            // Return response
            return $this->respondSuccess([
                'pengaduan' => $updatedPengaduan,
                'history' => $history
            ], 'Pengaduan berhasil diperbarui');
            
        } catch (\Exception $e) {
            log_message('error', 'Update pengaduan error: ' . $e->getMessage());
            return $this->respondError('Gagal memperbarui pengaduan: ' . $e->getMessage());
        }
    }

    /**
     * Add status/history to pengaduan
     * 
     * POST /api/pengaduan/{id}/status
     * Request JSON: { "status": "pending|diproses|selesai|ditolak", "keterangan": "..." }
     * Response 200: { "status": true, "message":"Status berhasil ditambahkan", "data": { "pengaduan": {...}, "history": {...} } }
     * Response 404: { "status": false, "message":"Pengaduan not found", "data": null }
     */
    public function addStatus($id)
    {
        // Set CORS headers
        $this->setCorsHeaders();
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        $rules = [
            'status' => 'required|in_list[pending,diproses,selesai,ditolak]',
            'keterangan' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }

        try {
            $userId = $this->getAuthUserId();
            
            // Check if user is authenticated
            if (!$userId) {
                return $this->respondError('Unauthorized', 401);
            }
            
            // Check if pengaduan exists and belongs to the authenticated user
            $pengaduan = $this->pengaduanModel->find($id);
            if (!$pengaduan || $pengaduan['user_id'] != $userId) {
                return $this->respondNotFound('Pengaduan not found');
            }
            
            $newStatus = $this->request->getVar('status');
            $keterangan = $this->request->getVar('keterangan') ?? 'Status diperbarui oleh pengguna';
            
            // Check if status is different
            if ($pengaduan['status'] === $newStatus) {
                return $this->respondError('Status is already ' . $newStatus, 400);
            }
            
            // Create status history record
            $historyData = [
                'pengaduan_id' => $id,
                'status_old' => $pengaduan['status'],
                'status_new' => $newStatus,
                'keterangan' => $keterangan,
                'updated_by' => $userId,
            ];
            
            // Insert history record
            $historyId = $this->statusHistoryModel->insert($historyData);
            
            // Update pengaduan status
            $updateData = ['status' => $newStatus];
            if ($newStatus === 'selesai') {
                $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
            }
            $this->pengaduanModel->update($id, $updateData);
            
            // Get updated pengaduan and history
            $updatedPengaduan = $this->pengaduanModel->getPengaduanWithRelations($id);
            $history = $this->statusHistoryModel->find($historyId);
            
            // Return response
            return $this->respondSuccess([
                'pengaduan' => $updatedPengaduan,
                'history' => $history
            ], 'Status berhasil ditambahkan');
            
        } catch (\Exception $e) {
            log_message('error', 'Add status error: ' . $e->getMessage());
            return $this->respondError('Failed to add status: ' . $e->getMessage());
        }
    }

    /**
     * Delete a pengaduan
     * 
     * DELETE /api/pengaduan/{id}
     * Response 200: { "status": true, "message":"Pengaduan berhasil dihapus", "data": null }
     * Response 400: { "status": false, "message":"Pengaduan dengan status ini tidak dapat dihapus", "data": null }
     * Response 404: { "status": false, "message":"Pengaduan not found", "data": null }
     */
    public function delete($id = null)
    {
        // Set headers explicitly for CORS and content type
        $this->setCorsHeaders();
        $this->response->setHeader('Content-Type', 'application/json');
        
        // Handle OPTIONS preflight requests
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }
        
        try {
            $userId = $this->getAuthUserId();
            
            // Check if user is authenticated
            if (!$userId) {
                return $this->respondError('Unauthorized', 401);
            }
            
            // Check if pengaduan exists and belongs to the authenticated user
            $pengaduan = $this->pengaduanModel->find($id);
            if (!$pengaduan) {
                return $this->respondNotFound('Pengaduan tidak ditemukan');
            }
            
            if ($pengaduan['user_id'] != $userId) {
                return $this->respondForbidden('Anda tidak memiliki akses untuk menghapus pengaduan ini');
            }
            
            // Only allow deletion if status is 'pending'
            if ($pengaduan['status'] !== 'pending') {
                return $this->respondError('Pengaduan dengan status ini tidak dapat dihapus', 400);
            }
            
            // Delete the pengaduan
            $this->pengaduanModel->delete($id);
            
            // Also delete any associated status history
            $this->statusHistoryModel->where('pengaduan_id', $id)->delete();
            
            return $this->respondSuccess(null, 'Pengaduan berhasil dihapus');
            
        } catch (\Exception $e) {
            log_message('error', 'Delete pengaduan error: ' . $e->getMessage());
            return $this->respondError('Gagal menghapus pengaduan: ' . $e->getMessage());
        }
    }

    /**
     * Test endpoint for debugging authentication
     * 
     * GET /api/pengaduan/test-auth
     * Headers: Authorization: Bearer {token}
     * Response 200: { "status": true, "message": "Authentication successful", "data": { "user": {...} } }
     * Response 401: { "status": false, "message": "Unauthorized" }
     */
}
