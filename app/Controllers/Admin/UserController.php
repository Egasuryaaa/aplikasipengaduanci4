<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\InstansiModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $instansiModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->instansiModel = new InstansiModel();
    }

    public function index()
    {
        $users = $this->userModel
            ->select('users.*, instansi.nama as instansi_nama')
            ->join('instansi', 'instansi.id = users.instansi_id', 'left')
            ->findAll();

        $data = [
            'title' => 'Manajemen Pengguna',
            'users' => $users
        ];

        return view('admin/users/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Pengguna',
            'instansi' => $this->instansiModel->where('is_active', true)->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/users/create', $data);
    }

    public function store()
    {
        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama harus diisi',
                    'min_length' => 'Nama minimal 3 karakter',
                    'max_length' => 'Nama maksimal 255 karakter'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah digunakan'
                ]
            ],
            'phone' => [
                'rules' => 'permit_empty|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'numeric' => 'Nomor telepon harus berupa angka',
                    'min_length' => 'Nomor telepon minimal 10 digit',
                    'max_length' => 'Nomor telepon maksimal 15 digit'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 8 karakter'
                ]
            ],
            'role' => [
                'rules' => 'required|in_list[master,admin,user]',
                'errors' => [
                    'required' => 'Role harus dipilih',
                    'in_list' => 'Role tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'password' => $this->request->getPost('password'),
            'role' => $this->request->getPost('role'),
            'instansi_id' => $this->request->getPost('instansi_id') ?: null,
            'is_active' => $this->request->getPost('is_active') ? true : false
        ];

        if ($this->userModel->insert($data)) {
            return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pengguna');
        }
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengguna tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Pengguna',
            'user' => $user,
            'instansi' => $this->instansiModel->where('is_active', true)->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/users/edit', $data);
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengguna tidak ditemukan');
        }

        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama harus diisi',
                    'min_length' => 'Nama minimal 3 karakter',
                    'max_length' => 'Nama maksimal 255 karakter'
                ]
            ],
            'email' => [
                'rules' => "required|valid_email|is_unique[users.email,id,{$id}]",
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah digunakan'
                ]
            ],
            'phone' => [
                'rules' => 'permit_empty|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'numeric' => 'Nomor telepon harus berupa angka',
                    'min_length' => 'Nomor telepon minimal 10 digit',
                    'max_length' => 'Nomor telepon maksimal 15 digit'
                ]
            ],
            'role' => [
                'rules' => 'required|in_list[master,admin,user]',
                'errors' => [
                    'required' => 'Role harus dipilih',
                    'in_list' => 'Role tidak valid'
                ]
            ]
        ];

        // Add password validation only if password is provided
        if ($this->request->getPost('password')) {
            $rules['password'] = [
                'rules' => 'min_length[8]',
                'errors' => [
                    'min_length' => 'Password minimal 8 karakter'
                ]
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'role' => $this->request->getPost('role'),
            'instansi_id' => $this->request->getPost('instansi_id') ?: null,
            'is_active' => $this->request->getPost('is_active') ? true : false
        ];

        // Only update password if provided
        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        // Skip validation untuk password karena itu opsional saat update
        $this->userModel->skipValidation(true);
        
        if ($this->userModel->update($id, $data)) {
            return redirect()->to('/admin/users')->with('success', 'Pengguna berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengguna');
        }
    }

    public function delete($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pengguna tidak ditemukan']);
        }

        // Prevent deleting current user
        if ($id == session('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri']);
        }

        // Check if user has pengaduan
        $pengaduanModel = new \App\Models\PengaduanModel();
        $pengaduanCount = $pengaduanModel->where('user_id', $id)->countAllResults();

        if ($pengaduanCount > 0) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Tidak dapat menghapus pengguna karena masih memiliki ' . $pengaduanCount . ' pengaduan'
            ]);
        }

        if ($this->userModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Pengguna berhasil dihapus']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus pengguna']);
        }
    }
}
