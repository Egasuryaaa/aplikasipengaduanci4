<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class FileController extends ResourceController
{
    protected $format = 'json';

    public function upload()
    {
        $validation = \Config\Services::validation();
        
        // Validate file upload
        $rules = [
            'files' => [
                'rules' => 'uploaded[files]|max_size[files,5120]|is_image[files]|mime_in[files,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'File harus diupload',
                    'max_size' => 'Ukuran file maksimal 5MB',
                    'is_image' => 'File harus berupa gambar',
                    'mime_in' => 'Format file harus JPG, JPEG, atau PNG'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'success' => false,
                'message' => 'Validasi file gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

        $uploadedFiles = $this->request->getFiles();
        $files = $uploadedFiles['files'] ?? [];
        
        // Handle single file or multiple files
        if (!is_array($files)) {
            $files = [$files];
        }

        $uploadedFileNames = [];
        $uploadPath = WRITEPATH . 'uploads/pengaduan/';

        // Create upload directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        try {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    // Generate unique filename
                    $newName = $file->getRandomName();
                    
                    // Move file to upload directory
                    if ($file->move($uploadPath, $newName)) {
                        // Compress image if needed
                        $this->compressImage($uploadPath . $newName);
                        
                        $uploadedFileNames[] = $newName;
                        
                        log_message('info', 'File uploaded: ' . $newName . ' by user ID: ' . $this->request->user_id);
                    } else {
                        log_message('error', 'Failed to move uploaded file: ' . $file->getName());
                    }
                }
            }

            if (!empty($uploadedFileNames)) {
                // Return file URLs
                $fileUrls = array_map(function($filename) {
                    return [
                        'filename' => $filename,
                        'url' => base_url('uploads/pengaduan/' . $filename),
                        'thumbnail' => base_url('uploads/pengaduan/thumbs/' . $filename)
                    ];
                }, $uploadedFileNames);

                return $this->respond([
                    'success' => true,
                    'message' => 'File berhasil diupload',
                    'data' => [
                        'files' => $fileUrls,
                        'filenames' => $uploadedFileNames
                    ]
                ], 201);
            } else {
                return $this->respond([
                    'success' => false,
                    'message' => 'Gagal mengupload file'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupload file'
            ], 500);
        }
    }

    private function compressImage($filePath)
    {
        try {
            $imageInfo = getimagesize($filePath);
            $mimeType = $imageInfo['mime'];

            // Load image based on type
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($filePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($filePath);
                    break;
                default:
                    return; // Unsupported format
            }

            if (!$image) return;

            $width = imagesx($image);
            $height = imagesy($image);

            // Resize if image is too large
            $maxWidth = 1200;
            $maxHeight = 1200;

            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = intval($width * $ratio);
                $newHeight = intval($height * $ratio);

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG
                if ($mimeType === 'image/png') {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                }

                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                // Save compressed image
                switch ($mimeType) {
                    case 'image/jpeg':
                        imagejpeg($resizedImage, $filePath, 85); // 85% quality
                        break;
                    case 'image/png':
                        imagepng($resizedImage, $filePath, 6); // Compression level 6
                        break;
                }

                imagedestroy($resizedImage);
            }

            // Create thumbnail
            $this->createThumbnail($filePath, $mimeType);

            imagedestroy($image);

        } catch (\Exception $e) {
            log_message('error', 'Image compression error: ' . $e->getMessage());
        }
    }

    private function createThumbnail($filePath, $mimeType)
    {
        try {
            $thumbPath = dirname($filePath) . '/thumbs/';
            
            // Create thumbs directory if not exists
            if (!is_dir($thumbPath)) {
                mkdir($thumbPath, 0755, true);
            }

            $filename = basename($filePath);
            $thumbFile = $thumbPath . $filename;

            // Load original image
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($filePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($filePath);
                    break;
                default:
                    return;
            }

            if (!$image) return;

            $width = imagesx($image);
            $height = imagesy($image);

            // Calculate thumbnail dimensions (300x300 max)
            $thumbSize = 300;
            $ratio = min($thumbSize / $width, $thumbSize / $height);
            $thumbWidth = intval($width * $ratio);
            $thumbHeight = intval($height * $ratio);

            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);

            // Preserve transparency for PNG
            if ($mimeType === 'image/png') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }

            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

            // Save thumbnail
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($thumbnail, $thumbFile, 80);
                    break;
                case 'image/png':
                    imagepng($thumbnail, $thumbFile, 6);
                    break;
            }

            imagedestroy($thumbnail);
            imagedestroy($image);

        } catch (\Exception $e) {
            log_message('error', 'Thumbnail creation error: ' . $e->getMessage());
        }
    }
}
