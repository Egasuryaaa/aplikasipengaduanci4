<?php

if (!function_exists('uuid4')) {
    /**
     * Generate a UUID v4 string
     */
    function uuid4()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('success_response')) {
    /**
     * Generate standardized success response for API
     */
    function success_response($data = null, $message = 'Success', $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return service('response')->setJSON($response)->setStatusCode($code);
    }
}

if (!function_exists('error_response')) {
    /**
     * Generate standardized error response for API
     */
    function error_response($message = 'Error', $code = 500, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return service('response')->setJSON($response)->setStatusCode($code);
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename for safe storage
     */
    function sanitize_filename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        $filename = preg_replace('/\.+/', '.', $filename);
        return trim($filename, '.');
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format file size in human readable format
     */
    function format_file_size($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('is_valid_image')) {
    /**
     * Check if file is a valid image
     */
    function is_valid_image($file_path)
    {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file_path);
        finfo_close($file_info);
        
        return in_array($mime_type, $allowed_types);
    }
}

if (!function_exists('generate_pengaduan_number')) {
    /**
     * Generate unique pengaduan number
     */
    function generate_pengaduan_number()
    {
        $date = date('Ymd');
        $pengaduanModel = new \App\Models\PengaduanModel();
        $count = $pengaduanModel->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
        $number = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return "PGD-{$date}-{$number}";
    }
}

if (!function_exists('status_badge_class')) {
    /**
     * Get CSS class for status badge
     */
    function status_badge_class($status)
    {
        $classes = [
            'pending' => 'badge-warning',
            'diproses' => 'badge-info',
            'selesai' => 'badge-success',
            'ditolak' => 'badge-danger'
        ];
        
        return $classes[$status] ?? 'badge-secondary';
    }
}

if (!function_exists('status_text')) {
    /**
     * Get readable status text
     */
    function status_text($status)
    {
        $texts = [
            'pending' => 'Menunggu',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak'
        ];
        
        return $texts[$status] ?? ucfirst($status);
    }
}

if (!function_exists('time_ago')) {
    /**
     * Get human readable time difference
     */
    function time_ago($datetime)
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) {
            return 'Baru saja';
        } elseif ($time < 3600) {
            $minutes = floor($time / 60);
            return $minutes . ' menit yang lalu';
        } elseif ($time < 86400) {
            $hours = floor($time / 3600);
            return $hours . ' jam yang lalu';
        } elseif ($time < 2592000) {
            $days = floor($time / 86400);
            return $days . ' hari yang lalu';
        } elseif ($time < 31104000) {
            $months = floor($time / 2592000);
            return $months . ' bulan yang lalu';
        } else {
            $years = floor($time / 31104000);
            return $years . ' tahun yang lalu';
        }
    }
}

if (!function_exists('mask_email')) {
    /**
     * Mask email for privacy
     */
    function mask_email($email)
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        
        $masked_name = substr($name, 0, 2) . str_repeat('*', strlen($name) - 4) . substr($name, -2);
        
        return $masked_name . '@' . $domain;
    }
}

if (!function_exists('mask_phone')) {
    /**
     * Mask phone number for privacy
     */
    function mask_phone($phone)
    {
        if (strlen($phone) < 4) {
            return $phone;
        }
        
        return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 6) . substr($phone, -3);
    }
}

if (!function_exists('create_breadcrumb')) {
    /**
     * Create breadcrumb array
     */
    function create_breadcrumb($items)
    {
        $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        
        foreach ($items as $key => $item) {
            if ($key === array_key_last($items)) {
                $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . esc($item['title']) . '</li>';
            } else {
                $breadcrumb .= '<li class="breadcrumb-item"><a href="' . esc($item['url']) . '">' . esc($item['title']) . '</a></li>';
            }
        }
        
        $breadcrumb .= '</ol></nav>';
        
        return $breadcrumb;
    }
}

if (!function_exists('get_avatar_url')) {
    /**
     * Get user avatar URL
     */
    function get_avatar_url($email, $size = 80)
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }
}

if (!function_exists('log_user_activity')) {
    /**
     * Log user activity
     */
    function log_user_activity($action, $details = null)
    {
        $user_id = session('user_id');
        $user_name = session('user_name');
        
        if ($user_id) {
            $message = "User {$user_name} (ID: {$user_id}) {$action}";
            if ($details) {
                $message .= " - Details: {$details}";
            }
            log_message('info', $message);
        }
    }
}

if (!function_exists('validate_indonesian_phone')) {
    /**
     * Validate Indonesian phone number format
     */
    function validate_indonesian_phone($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if starts with 08, +628, or 628
        if (preg_match('/^(08|628|\+628)/', $phone)) {
            // Normalize to 08 format
            $phone = preg_replace('/^(\+628|628)/', '08', $phone);
            
            // Check length (10-15 digits)
            if (strlen($phone) >= 10 && strlen($phone) <= 15) {
                return $phone;
            }
        }
        
        return false;
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * Get real client IP address
     */
    function get_client_ip()
    {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
