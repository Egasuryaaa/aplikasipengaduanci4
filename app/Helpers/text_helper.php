<?php

if (! function_exists('character_limiter')) {
    /**
     * Character Limiter
     *
     * Limits the string based on the character count.  Preserves complete words
     * so the character count may not be exactly as specified.
     *
     * @param string $str
     * @param int    $n
     * @param string $end_char the end character. Usually an ellipsis
     *
     * @return string
     */
    function character_limiter(string $str, int $n = 500, string $end_char = '&#8230;'): string
    {
        if (mb_strlen($str) < $n) {
            return $str;
        }

        // a bit complicated, but faster than preg_replace with \s+
        $str = preg_replace('/ {2,}/', ' ', str_replace(["\r", "\n", "\t", "\v", "\f"], ' ', $str));

        if (mb_strlen($str) <= $n) {
            return $str;
        }

        $out = '';
        foreach (explode(' ', trim($str)) as $val) {
            $out .= $val . ' ';

            if (mb_strlen($out) >= $n) {
                $out = trim($out);

                return (mb_strlen($out) === mb_strlen($str)) ? $out : $out . $end_char;
            }
        }

        return $out;
    }
}

if (! function_exists('word_limiter')) {
    /**
     * Word Limiter
     *
     * Limits a string to X number of words.
     *
     * @param string $str
     * @param int    $limit
     * @param string $end_char the end character. Usually an ellipsis
     *
     * @return string
     */
    function word_limiter(string $str, int $limit = 100, string $end_char = '&#8230;'): string
    {
        if (trim($str) === '') {
            return $str;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,' . (int) $limit . '}/', $str, $matches);

        if (strlen($str) === strlen($matches[0])) {
            $end_char = '';
        }

        return rtrim($matches[0]) . $end_char;
    }
}

if (! function_exists('format_bytes')) {
    /**
     * Format file size
     *
     * @param int $size Size in bytes
     * @param int $precision Decimal precision
     * @return string
     */
    function format_bytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}

if (! function_exists('time_ago')) {
    /**
     * Convert timestamp to human readable format
     *
     * @param string $datetime
     * @return string
     */
    function time_ago(string $datetime): string
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) {
            return 'baru saja';
        } elseif ($time < 3600) {
            return floor($time / 60) . ' menit yang lalu';
        } elseif ($time < 86400) {
            return floor($time / 3600) . ' jam yang lalu';
        } elseif ($time < 2592000) {
            return floor($time / 86400) . ' hari yang lalu';
        } elseif ($time < 31536000) {
            return floor($time / 2592000) . ' bulan yang lalu';
        } else {
            return floor($time / 31536000) . ' tahun yang lalu';
        }
    }
}

if (! function_exists('status_badge')) {
    /**
     * Generate status badge HTML
     *
     * @param string $status
     * @return string
     */
    function status_badge(string $status): string
    {
        $badges = [
            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
            'menunggu' => '<span class="badge bg-warning text-dark">Menunggu</span>',
            'diproses' => '<span class="badge bg-info">Diproses</span>',
            'selesai' => '<span class="badge bg-success">Selesai</span>',
            'ditolak' => '<span class="badge bg-danger">Ditolak</span>',
            'aktif' => '<span class="badge bg-success">Aktif</span>',
            'tidak_aktif' => '<span class="badge bg-secondary">Tidak Aktif</span>',
        ];
        
        return $badges[strtolower($status)] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }
}

if (! function_exists('priority_badge')) {
    /**
     * Generate priority badge HTML
     *
     * @param string $priority
     * @return string
     */
    function priority_badge(string $priority): string
    {
        $badges = [
            'rendah' => '<span class="text-success"><i class="fas fa-circle"></i> Rendah</span>',
            'sedang' => '<span class="text-warning"><i class="fas fa-circle"></i> Sedang</span>',
            'tinggi' => '<span class="text-danger"><i class="fas fa-circle"></i> Tinggi</span>',
        ];
        
        return $badges[strtolower($priority)] ?? '<span class="text-secondary"><i class="fas fa-circle"></i> ' . ucfirst($priority) . '</span>';
    }
}
