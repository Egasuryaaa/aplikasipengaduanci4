<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FileController extends BaseController
{
    public function uploadImage()
    {
        try {
            // Check if file was uploaded
            $file = $this->request->getFile('image');
            
            if (!$file) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada file yang diupload',
                    'data' => null
                ], 400);
            }

            // Validate file
            if (!$file->isValid()) {
                return response()->json([
                    'status' => false,
                    'message' => 'File tidak valid: ' . $file->getErrorString(),
                    'data' => null
                ], 400);
            }

            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tipe file tidak didukung. Hanya JPEG, JPG, dan PNG yang diizinkan',
                    'data' => null
                ], 400);
            }

            // Check file size (max 5MB)
            $maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if ($file->getSize() > $maxSize) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ukuran file terlalu besar. Maksimal 5MB',
                    'data' => null
                ], 400);
            }

            // Generate unique filename
            $extension = $file->getClientExtension();
            $filename = uniqid() . '_' . time() . '.' . $extension;

            // Set upload path
            $uploadPath = FCPATH . 'uploads/pengaduan/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file to upload directory
            if ($file->move($uploadPath, $filename)) {
                $fileUrl = base_url('uploads/pengaduan/' . $filename);
                
                log_message('info', 'File uploaded successfully: ' . $filename);
                
                return response()->json([
                    'status' => true,
                    'message' => 'File berhasil diupload',
                    'data' => [
                        'filename' => $filename,
                        'url' => $fileUrl,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mengupload file',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in FileController@uploadImage: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function uploadMultiple()
    {
        try {
            $files = $this->request->getFiles();
            
            if (empty($files['images'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada file yang diupload',
                    'data' => null
                ], 400);
            }

            $uploadedFiles = [];
            $errors = [];
            $uploadPath = FCPATH . 'uploads/pengaduan/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            foreach ($files['images'] as $file) {
                if (!$file->isValid()) {
                    $errors[] = 'File tidak valid: ' . $file->getErrorString();
                    continue;
                }

                // Check file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($file->getMimeType(), $allowedTypes)) {
                    $errors[] = 'Tipe file tidak didukung: ' . $file->getClientName();
                    continue;
                }

                // Check file size (max 5MB)
                $maxSize = 5 * 1024 * 1024; // 5MB in bytes
                if ($file->getSize() > $maxSize) {
                    $errors[] = 'File terlalu besar: ' . $file->getClientName();
                    continue;
                }

                // Generate unique filename
                $extension = $file->getClientExtension();
                $filename = uniqid() . '_' . time() . '.' . $extension;

                // Move file to upload directory
                if ($file->move($uploadPath, $filename)) {
                    $uploadedFiles[] = [
                        'filename' => $filename,
                        'url' => base_url('uploads/pengaduan/' . $filename),
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                        'original_name' => $file->getClientName()
                    ];
                } else {
                    $errors[] = 'Gagal mengupload: ' . $file->getClientName();
                }
            }

            if (!empty($uploadedFiles)) {
                $response = [
                    'status' => true,
                    'message' => count($uploadedFiles) . ' file berhasil diupload',
                    'data' => [
                        'uploaded_files' => $uploadedFiles,
                        'total_uploaded' => count($uploadedFiles)
                    ]
                ];

                if (!empty($errors)) {
                    $response['data']['errors'] = $errors;
                    $response['data']['total_errors'] = count($errors);
                }

                return response()->json($response, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada file yang berhasil diupload',
                    'data' => [
                        'errors' => $errors
                    ]
                ], 400);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in FileController@uploadMultiple: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function deleteFile($filename = null)
    {
        try {
            if (!$filename) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nama file harus disediakan',
                    'data' => null
                ], 400);
            }

            // Sanitize filename to prevent directory traversal
            $filename = basename($filename);
            $filePath = FCPATH . 'uploads/pengaduan/' . $filename;

            if (!file_exists($filePath)) {
                return response()->json([
                    'status' => false,
                    'message' => 'File tidak ditemukan',
                    'data' => null
                ], 404);
            }

            if (unlink($filePath)) {
                log_message('info', 'File deleted successfully: ' . $filename);
                
                return response()->json([
                    'status' => true,
                    'message' => 'File berhasil dihapus',
                    'data' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menghapus file',
                    'data' => null
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in FileController@deleteFile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }

    public function getFileInfo($filename = null)
    {
        try {
            if (!$filename) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nama file harus disediakan',
                    'data' => null
                ], 400);
            }

            // Sanitize filename to prevent directory traversal
            $filename = basename($filename);
            $filePath = FCPATH . 'uploads/pengaduan/' . $filename;

            if (!file_exists($filePath)) {
                return response()->json([
                    'status' => false,
                    'message' => 'File tidak ditemukan',
                    'data' => null
                ], 404);
            }

            $fileInfo = [
                'filename' => $filename,
                'url' => base_url('uploads/pengaduan/' . $filename),
                'size' => filesize($filePath),
                'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                'type' => mime_content_type($filePath)
            ];

            return response()->json([
                'status' => true,
                'message' => 'Informasi file berhasil diambil',
                'data' => $fileInfo
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error in FileController@getFileInfo: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
                'data' => null
            ], 500);
        }
    }
}
