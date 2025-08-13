<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Manajemen Pengguna</h1>
        <p class="text-muted">Kelola pengguna dalam sistem</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">Daftar Pengguna</h6>
                <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Pengguna
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Role</th>
                                <th>Instansi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($users) && !empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <strong><?= $user['name'] ?></strong>
                                    </td>
                                    <td><?= $user['email'] ?></td>
                                    <td><?= $user['phone'] ?: '-' ?></td>
                                    <td>
                                        <?php
                                        $roleClass = '';
                                        switch($user['role']) {
                                            case 'master':
                                                $roleClass = 'bg-danger';
                                                break;
                                            case 'admin':
                                                $roleClass = 'bg-warning text-dark';
                                                break;
                                            case 'user':
                                                $roleClass = 'bg-info';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $roleClass ?>"><?= ucfirst($user['role']) ?></span>
                                    </td>
                                    <td><?= $user['instansi_nama'] ?: '-' ?></td>
                                    <td>
                                        <?= $user['is_active'] ? 
                                            '<span class="badge bg-success">Aktif</span>' : 
                                            '<span class="badge bg-secondary">Tidak Aktif</span>' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= base_url('admin/users/' . $user['id'] . '/edit') ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != session('user_id')): ?>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteUser('<?= $user['id'] ?>')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pengguna</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengguna ini?</p>
                <p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#usersTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "columnDefs": [
                { "orderable": false, "targets": [7] } // Disable ordering on action column
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            }
        });
    });

    // Delete function
    let deleteId = null;
    
    function deleteUser(id) {
        deleteId = id;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
    
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteId) {
            fetch(`<?= base_url('admin/users/') ?>${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus pengguna');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem');
            });
        }
    });
</script>
<?= $this->endSection() ?>
