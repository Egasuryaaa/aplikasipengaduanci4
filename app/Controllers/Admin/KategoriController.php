<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KategoriPengaduanModel;

class KategoriController extends BaseController
{
    protected $kategoriModel;

    public function __construct()
    {
        $this->kategoriModel = new KategoriPengaduanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Kategori',
            'kategori' => $this->kategoriModel->findAll()
        ];

        return view('admin/kategori/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Kategori',
            'validation' => \Config\Services::validation()
        ];

        return view('admin/kategori/create', $data);
    }

    public function store()
    {
        $rules = [
            'nama' => [
                'rules' => 'required|min_length[3]|max_length[255]|is_unique[kategori_pengaduan.nama]',
                'errors' => [
                    'required' => 'Nama kategori harus diisi',
                    'min_length' => 'Nama kategori minimal 3 karakter',
                    'max_length' => 'Nama kategori maksimal 255 karakter',
                    'is_unique' => 'Nama kategori sudah ada'
                ]
            ],
            'deskripsi' => [
                'rules' => 'permit_empty|max_length[500]',
                'errors' => [
                    'max_length' => 'Deskripsi maksimal 500 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'is_active' => $this->request->getPost('is_active') ? true : false
        ];

        if ($this->kategoriModel->insert($data)) {
            return redirect()->to('/admin/kategori')->with('success', 'Kategori berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan kategori');
        }
    }

    public function edit($id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Kategori tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Kategori',
            'kategori' => $kategori,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/kategori/edit', $data);
    }

    public function update($id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Kategori tidak ditemukan');
        }

        $rules = [
            'nama' => [
                'rules' => "required|min_length[3]|max_length[255]|is_unique[kategori_pengaduan.nama,id,{$id}]",
                'errors' => [
                    'required' => 'Nama kategori harus diisi',
                    'min_length' => 'Nama kategori minimal 3 karakter',
                    'max_length' => 'Nama kategori maksimal 255 karakter',
                    'is_unique' => 'Nama kategori sudah ada'
                ]
            ],
            'deskripsi' => [
                'rules' => 'permit_empty|max_length[500]',
                'errors' => [
                    'max_length' => 'Deskripsi maksimal 500 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'is_active' => $this->request->getPost('is_active') ? true : false
        ];

        if ($this->kategoriModel->update($id, $data)) {
            return redirect()->to('/admin/kategori')->with('success', 'Kategori berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui kategori');
        }
    }

    public function delete($id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kategori tidak ditemukan']);
        }

        // Check if kategori is being used by pengaduan
        $pengaduanModel = new \App\Models\PengaduanModel();
        $pengaduanCount = $pengaduanModel->where('kategori_id', $id)->countAllResults();

        if ($pengaduanCount > 0) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Tidak dapat menghapus kategori karena masih digunakan oleh ' . $pengaduanCount . ' pengaduan'
            ]);
        }

        if ($this->kategoriModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Kategori berhasil dihapus']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus kategori']);
        }
    }
}
