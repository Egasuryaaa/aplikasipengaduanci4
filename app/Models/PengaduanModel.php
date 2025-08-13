<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaduanModel extends Model
{
    protected $table            = 'pengaduan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'uuid', 'nomor_pengaduan', 'user_id', 'instansi_id', 'kategori_id',
        'deskripsi', 'foto_bukti', 'status', 'tanggal_selesai', 'keterangan_admin'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'      => 'required|integer',
        'instansi_id'  => 'required|integer',
        'kategori_id'  => 'required|integer',
        'deskripsi'    => 'required|min_length[10]|max_length[2000]',
        'status'       => 'permit_empty|in_list[pending,diproses,selesai,ditolak]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateUuid', 'generateNomorPengaduan'];
    protected $afterInsert    = ['createStatusHistory'];
    protected $afterUpdate    = ['updateStatusHistory'];

    protected function generateUuid(array $data)
    {
        if (!isset($data['data']['uuid'])) {
            $data['data']['uuid'] = service('uuid')->uuid4()->toString();
        }
        return $data;
    }

    protected function generateNomorPengaduan(array $data)
    {
        if (!isset($data['data']['nomor_pengaduan'])) {
            $date = date('Ymd');
            $count = $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
            $number = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $data['data']['nomor_pengaduan'] = "PGD-{$date}-{$number}";
        }
        return $data;
    }

    protected function createStatusHistory(array $data)
    {
        if ($data['result']) {
            $statusHistoryModel = new \App\Models\StatusHistoryModel();
            $statusHistoryModel->insert([
                'pengaduan_id' => $data['id'],
                'status_old'   => null,
                'status_new'   => $data['data']['status'] ?? 'pending',
                'keterangan'   => 'Pengaduan dibuat',
                'updated_by'   => $data['data']['user_id'],
            ]);
        }
        return $data;
    }

    protected function updateStatusHistory(array $data)
    {
        if (isset($data['data']['status'])) {
            $oldData = $this->find($data['id'][0]);
            if ($oldData && $oldData['status'] !== $data['data']['status']) {
                $statusHistoryModel = new \App\Models\StatusHistoryModel();
                $statusHistoryModel->insert([
                    'pengaduan_id' => $data['id'][0],
                    'status_old'   => $oldData['status'],
                    'status_new'   => $data['data']['status'],
                    'keterangan'   => $data['data']['keterangan_admin'] ?? null,
                    'updated_by'   => session('user_id'),
                ]);
            }
        }
        return $data;
    }

    public function findByUuid($uuid)
    {
        return $this->where('uuid', $uuid)->first();
    }

    public function findByNomor($nomor)
    {
        return $this->where('nomor_pengaduan', $nomor)->first();
    }

    public function getPengaduanWithRelations($id = null)
    {
        $builder = $this->select('pengaduan.*, 
                                 users.name as user_name, users.email as user_email, users.phone as user_phone,
                                 instansi.nama as instansi_nama,
                                 kategori_pengaduan.nama as kategori_nama')
                       ->join('users', 'users.id = pengaduan.user_id')
                       ->join('instansi', 'instansi.id = pengaduan.instansi_id')
                       ->join('kategori_pengaduan', 'kategori_pengaduan.id = pengaduan.kategori_id');

        if ($id) {
            return $builder->where('pengaduan.id', $id)->first();
        }

        return $builder;
    }

    public function getStatistics()
    {
        $stats = [];
        $stats['total'] = $this->countAll();
        $stats['pending'] = $this->where('status', 'pending')->countAllResults();
        $stats['diproses'] = $this->where('status', 'diproses')->countAllResults();
        $stats['selesai'] = $this->where('status', 'selesai')->countAllResults();
        $stats['ditolak'] = $this->where('status', 'ditolak')->countAllResults();

        // Statistics by month
        $stats['monthly'] = $this->select('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as total')
                                ->where('EXTRACT(YEAR FROM created_at)', date('Y'))
                                ->groupBy('EXTRACT(MONTH FROM created_at)')
                                ->orderBy('month')
                                ->findAll();

        return $stats;
    }

    public function assignToAdmin($pengaduanId, $adminId, $keterangan = null)
    {
        $data = [
            'status' => 'diproses'
        ];

        if ($keterangan) {
            $data['keterangan_admin'] = $keterangan;
        }

        return $this->update($pengaduanId, $data);
    }

    public function markAsCompleted($pengaduanId, $keterangan = null)
    {
        $data = [
            'status' => 'selesai',
            'tanggal_selesai' => date('Y-m-d H:i:s')
        ];

        if ($keterangan) {
            $data['keterangan_admin'] = $keterangan;
        }

        return $this->update($pengaduanId, $data);
    }
}
