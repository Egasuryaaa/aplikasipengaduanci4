<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Files extends Controller
{
    public function pengaduan($filename = null)
    {
        // Debug logging
        log_message('debug', '[Files::pengaduan] Called with filename: ' . ($filename ?? 'null'));
        
        if (!$filename) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Filename required']);
        }
        
        $filepath = WRITEPATH . 'uploads/pengaduan/' . $filename;
        log_message('debug', '[Files::pengaduan] Looking for file: ' . $filepath);
        
        if (!file_exists($filepath)) {
            log_message('debug', '[Files::pengaduan] File not found: ' . $filepath);
            return $this->response->setStatusCode(404)->setJSON(['error' => 'File not found']);
        }
        
        // Get file info
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        log_message('debug', '[Files::pengaduan] Serving file: ' . $filepath . ', MIME: ' . $mimeType);
        
        // Set appropriate headers
        $this->response->setHeader('Content-Type', $mimeType);
        $this->response->setHeader('Content-Length', filesize($filepath));
        $this->response->setHeader('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
        
        // Output file
        $this->response->setBody(file_get_contents($filepath));
        
        return $this->response;
    }
}
