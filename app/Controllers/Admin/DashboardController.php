<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaduanModel;
use App\Models\UserModel;
use App\Models\InstansiModel;
use App\Models\KategoriPengaduanModel;

class DashboardController extends BaseController
{
    protected $pengaduanModel;
    protected $userModel;
    protected $instansiModel;
    protected $kategoriModel;

    public function __construct()
    {
        $this->pengaduanModel = new PengaduanModel();
        $this->userModel = new UserModel();
        $this->instansiModel = new InstansiModel();
        $this->kategoriModel = new KategoriPengaduanModel();
    }

    public function index()
    {
        // Get statistics
        $stats = $this->pengaduanModel->getStatistics();
        
        // Get recent pengaduan
        $recentPengaduan = $this->pengaduanModel->getPengaduanWithRelations()
                               ->orderBy('pengaduan.created_at', 'DESC')
                               ->limit(10)
                               ->find();

        // Get pengaduan by status for chart
        $statusChart = [
            'labels' => ['Pending', 'Diproses', 'Selesai', 'Ditolak'],
            'data' => [
                $stats['pending'],
                $stats['diproses'], 
                $stats['selesai'],
                $stats['ditolak']
            ]
        ];

        // Get monthly statistics for line chart
        $monthlyChart = [
            'labels' => [],
            'data' => []
        ];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyChart['labels'][] = date('M', mktime(0, 0, 0, $i, 1));
            $found = false;
            
            foreach ($stats['monthly'] as $monthly) {
                if ($monthly['month'] == $i) {
                    $monthlyChart['data'][] = (int)$monthly['total'];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $monthlyChart['data'][] = 0;
            }
        }

        // Get user counts
        $userCounts = [
            'total_users' => $this->userModel->where('role', 'user')->countAllResults(),
            'total_admins' => $this->userModel->whereIn('role', ['admin', 'master'])->countAllResults(),
            'active_users' => $this->userModel->where('role', 'user')->where('is_active', true)->countAllResults()
        ];

        // Get master data counts
        $masterDataCounts = [
            'total_instansi' => $this->instansiModel->countAll(),
            'active_instansi' => $this->instansiModel->where('is_active', true)->countAllResults(),
            'total_kategori' => $this->kategoriModel->countAll(),
            'active_kategori' => $this->kategoriModel->where('is_active', true)->countAllResults()
        ];

        // Get recent pengaduan for all roles now (since assigned_to is removed)
        $myPengaduan = [];
        if (session('user_role') === 'admin') {
            $myPengaduan = $this->pengaduanModel->getPengaduanWithRelations()
                               ->where('pengaduan.status', 'diproses')
                               ->orderBy('pengaduan.created_at', 'DESC')
                               ->limit(5)
                               ->find();
        }

        $data = [
            'title' => 'Dashboard - Sistem Pengaduan Kominfo',
            'stats' => $stats,
            'recent_pengaduan' => $recentPengaduan,
            'status_chart' => $statusChart,
            'monthly_chart' => $monthlyChart,
            'user_counts' => $userCounts,
            'master_data_counts' => $masterDataCounts,
            'my_pengaduan' => $myPengaduan,
            'user_role' => session('user_role'),
            'user_name' => session('user_name')
        ];

        return view('admin/dashboard/index', $data);
    }

    public function getChartData()
    {
        // AJAX endpoint for chart data
        $type = $this->request->getGet('type');
        $period = $this->request->getGet('period') ?? 'month';

        switch ($type) {
            case 'status':
                $stats = $this->pengaduanModel->getStatistics();
                $data = [
                    'labels' => ['Pending', 'Diproses', 'Selesai', 'Ditolak'],
                    'datasets' => [[
                        'data' => [$stats['pending'], $stats['diproses'], $stats['selesai'], $stats['ditolak']],
                        'backgroundColor' => ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
                    ]]
                ];
                break;

            case 'monthly':
                $stats = $this->pengaduanModel->getStatistics();
                $labels = [];
                $data = [];

                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = date('M', mktime(0, 0, 0, $i, 1));
                    $found = false;
                    
                    foreach ($stats['monthly'] as $monthly) {
                        if ($monthly['month'] == $i) {
                            $data[] = (int)$monthly['total'];
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        $data[] = 0;
                    }
                }

                $chartData = [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Pengaduan per Bulan',
                        'data' => $data,
                        'borderColor' => '#007bff',
                        'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                        'fill' => true
                    ]]
                ];
                break;

            case 'kategori':
                $kategoriStats = $this->kategoriModel->getKategoriWithPengaduanCount();
                $labels = [];
                $data = [];

                foreach ($kategoriStats as $kategori) {
                    $labels[] = $kategori['nama'];
                    $data[] = (int)$kategori['pengaduan_count'];
                }

                $chartData = [
                    'labels' => $labels,
                    'datasets' => [[
                        'data' => $data,
                        'backgroundColor' => [
                            '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', 
                            '#9966ff', '#ff9f40', '#ff6384', '#c9cbcf'
                        ]
                    ]]
                ];
                break;

            default:
                $chartData = ['labels' => [], 'datasets' => []];
        }

        return $this->response->setJSON($chartData);
    }
}
