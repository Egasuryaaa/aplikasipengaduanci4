<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InstansiModel;

class InstansiController extends BaseController
{
    protected $instansiModel;

    public function __construct()
    {
        $this->instansiModel = new InstansiModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Instansi',
            'instansi' => $this->instansiModel->findAll()
        ];

        return view('admin/instansi/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Instansi',
            'validation' => \Config\Services::validation()
        ];

        return view('admin/instansi/create', $data);
    }

    public function store()
    {
        $rules = [
            'nama' => [
                'rules' => 'required|min_length[3]|max_length[255]|is_unique[instansi.nama]',
                'errors' => [
                    'required' => 'Nama instansi harus diisi',
                    'min_length' => 'Nama instansi minimal 3 karakter',
                    'max_length' => 'Nama instansi maksimal 255 karakter',
                    'is_unique' => 'Nama instansi sudah ada'
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty|max_length[500]',
                'errors' => [
                    'max_length' => 'Alamat maksimal 500 karakter'
                ]
            ],
            'email' => [
                'rules' => 'permit_empty|valid_email|is_unique[instansi.email]',
                'errors' => [
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah digunakan'
                ]
            ],
            'telepon' => [
                'rules' => 'permit_empty|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'numeric' => 'Nomor telepon harus berupa angka',
                    'min_length' => 'Nomor telepon minimal 10 digit',
                    'max_length' => 'Nomor telepon maksimal 15 digit'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'alamat' => $this->request->getPost('alamat'),
            'email' => $this->request->getPost('email'),
            'telepon' => $this->request->getPost('telepon'),
            'is_active' => $this->request->getPost('is_active') ? true : false
        ];

        if ($this->instansiModel->insert($data)) {
            return redirect()->to('/admin/instansi')->with('success', 'Instansi berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan instansi');
        }
    }

    public function edit($id)
    {
        $instansi = $this->instansiModel->find($id);

        if (!$instansi) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Instansi tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Instansi',
            'instansi' => $instansi,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/instansi/edit', $data);
    }

    public function update($id)
    {
        $instansi = $this->instansiModel->find($id);

        if (!$instansi) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Instansi tidak ditemukan');
        }

        $rules = [
            'nama' => [
                'rules' => "required|min_length[3]|max_length[255]|is_unique[instansi.nama,id,{$id}]",
                'errors' => [
                    'required' => 'Nama instansi harus diisi',
                    'min_length' => 'Nama instansi minimal 3 karakter',
                    'max_length' => 'Nama instansi maksimal 255 karakter',
                    'is_unique' => 'Nama instansi sudah ada'
                ]
            ],
            'alamat' => [
                'rules' => 'permit_empty|max_length[500]',
                'errors' => [
                    'max_length' => 'Alamat maksimal 500 karakter'
                ]
            ],
            'email' => [
                'rules' => "permit_empty|valid_email|is_unique[instansi.email,id,{$id}]",
                'errors' => [
                    'valid_email' => 'Format email tidak valid',
                    'is_unique' => 'Email sudah digunakan'
                ]
            ],
            'telepon' => [
                'rules' => 'permit_empty|numeric|min_length[10]|max_length[15]',
                'errors' => [
                    'numeric' => 'Nomor telepon harus berupa angka',
                    'min_length' => 'Nomor telepon minimal 10 digit',
                    'max_length' => 'Nomor telepon maksimal 15 digit'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'alamat' => $this->request->getPost('alamat'),
            'email' => $this->request->getPost('email'),
            'telepon' => $this->request->getPost('telepon'),
            'is_active' => $this->request->getPost('is_active') ? true : false
        ];

        if ($this->instansiModel->update($id, $data)) {
            return redirect()->to('/admin/instansi')->with('success', 'Instansi berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui instansi');
        }
    }

    public function delete($id)
    {
        $instansi = $this->instansiModel->find($id);

        if (!$instansi) {
            return $this->response->setJSON(['success' => false, 'message' => 'Instansi tidak ditemukan']);
        }

        // Check if instansi is being used by users
        $userModel = new \App\Models\UserModel();
        $usersCount = $userModel->where('instansi_id', $id)->countAllResults();

        if ($usersCount > 0) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Tidak dapat menghapus instansi karena masih digunakan oleh ' . $usersCount . ' pengguna'
            ]);
        }

        if ($this->instansiModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Instansi berhasil dihapus']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus instansi']);
        }
    }
}
