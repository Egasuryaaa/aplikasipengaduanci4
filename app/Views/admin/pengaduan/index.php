<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Manajemen Pengaduan</h1>
        <p class="text-muted">Kelola semua pengaduan yang masuk ke sistem</p>
    </div>
</div>

<!-- Filter Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-white">Filter Pengaduan</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('admin/pengaduan') ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="pending" <?= (request()->getGet('status') == 'pending') ? 'selected' : '' ?>>Menunggu</option>
                                <option value="diproses" <?= (request()->getGet('status') == 'diproses') ? 'selected' : '' ?>>Diproses</option>
                                <option value="selesai" <?= (request()->getGet('status') == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                                <option value="ditolak" <?= (request()->getGet('status') == 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="kategori_id" class="form-label">Kategori</label>
                            <select name="kategori_id" id="kategori_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php if (isset($kategori_list)): ?>
                                    <?php foreach ($kategori_list as $kategori): ?>
                                        <option value="<?= $kategori['id'] ?>" 
                                                <?= (request()->getGet('kategori_id') == $kategori['id']) ? 'selected' : '' ?>>
                                            <?= $kategori['nama'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Dari Tanggal</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="<?= request()->getGet('date_from') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Sampai Tanggal</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="<?= request()->getGet('date_to') ?>">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Cari deskripsi, nomor pengaduan, atau nama pelapor..." 
                                   value="<?= request()->getGet('search') ?>">
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="<?= base_url('admin/pengaduan') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Pengaduan Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">Daftar Pengaduan</h6>
                <div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="pengaduanTable">
                        <thead>
                            <tr>
                                <th width="12%">No. Pengaduan</th>
                                <th width="35%">Deskripsi & Pelapor</th>
                                <th width="8%">Foto</th>
                                <th width="15%">Kategori</th>
                                <th width="12%">Status</th>
                                <th width="10%">Tanggal</th>
                                <th width="8%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($pengaduan) && !empty($pengaduan)): ?>
                                <?php foreach ($pengaduan as $item): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?= $item['nomor_pengaduan'] ?></strong>
                                    </td>
                                    <td>
                                        <div class="mb-2">
                                            <strong><?= character_limiter($item['deskripsi'], 80) ?></strong>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fas fa-user"></i> <?= $item['user_name'] ?><br>
                                            <i class="fas fa-envelope"></i> <?= $item['user_email'] ?><br>
                                            <i class="fas fa-building"></i> <?= $item['instansi_nama'] ?? 'Tidak ada' ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        // Parse foto_bukti as JSON array
                                        $fotoBukti = [];
                                        if (!empty($item['foto_bukti'])) {
                                            if (is_string($item['foto_bukti'])) {
                                                $fotoBukti = json_decode($item['foto_bukti'], true) ?? [];
                                            } elseif (is_array($item['foto_bukti'])) {
                                                $fotoBukti = $item['foto_bukti'];
                                            }
                                        }
                                        ?>
                                        <?php if (!empty($fotoBukti)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="showMultipleImages(<?= htmlspecialchars(json_encode(array_map(function($foto) {
                                                        return (strpos($foto, 'http') === 0) ? $foto : base_url('uploads/pengaduan/' . $foto);
                                                    }, $fotoBukti))) ?>)">
                                                <i class="fas fa-image"></i> <?= count($fotoBukti) ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $item['kategori_nama'] ?? '-' ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = '';
                                        $statusText = '';
                                        switch($item['status']) {
                                            case 'pending':
                                                $badgeClass = 'bg-warning text-dark';
                                                $statusText = 'Menunggu';
                                                break;
                                            case 'diproses':
                                                $badgeClass = 'bg-info';
                                                $statusText = 'Diproses';
                                                break;
                                            case 'selesai':
                                                $badgeClass = 'bg-success';
                                                $statusText = 'Selesai';
                                                break;
                                            case 'ditolak':
                                                $badgeClass = 'bg-danger';
                                                $statusText = 'Ditolak';
                                                break;
                                            default:
                                                $badgeClass = 'bg-secondary';
                                                $statusText = ucfirst($item['status']);
                                        }
                                        ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm badge <?= $badgeClass ?> dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <?= $statusText ?>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $item['id'] ?>, 'pending')">
                                                    <span class="badge bg-warning text-dark">Menunggu</span></a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $item['id'] ?>, 'diproses')">
                                                    <span class="badge bg-info">Diproses</span></a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $item['id'] ?>, 'selesai')">
                                                    <span class="badge bg-success">Selesai</span></a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $item['id'] ?>, 'ditolak')">
                                                    <span class="badge bg-danger">Ditolak</span></a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <?= date('d/m/Y', strtotime($item['created_at'])) ?><br>
                                            <small class="text-muted"><?= date('H:i', strtotime($item['created_at'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical" role="group">
                                            <a href="<?= base_url('admin/pengaduan/' . $item['id']) ?>" 
                                               class="btn btn-sm btn-primary mb-1" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- <button type="button" class="btn btn-sm btn-warning mb-1" 
                                                    onclick="window.location.href='<?= base_url('admin/pengaduan/' . $item['id'] . '/edit') ?>'" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button> -->
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data pengaduan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <div class="mt-3">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
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
                <p>Apakah Anda yakin ingin menghapus pengaduan ini?</p>
                <p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Bukti Pengaduan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Foto Bukti">
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#pengaduanTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "order": [[5, "desc"]], // Order by date desc (6th column, index 5)
            "columnDefs": [
                { "orderable": false, "targets": [2, 6] } // Disable ordering on foto and action columns
            ],
            "language": {
                "processing": "Sedang memproses...",
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "paginate": {
                    "first": "Pertama",
                    "previous": "Sebelumnya",
                    "next": "Selanjutnya",
                    "last": "Terakhir"
                }
            }
        });
    });

    // Show image function
    function showImage(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        imageModal.show();
    }

    // Show multiple images function
    function showMultipleImages(imageUrls) {
        if (imageUrls.length === 1) {
            showImage(imageUrls[0]);
        } else if (imageUrls.length > 1) {
            // Create a carousel or gallery for multiple images
            let carousel = '<div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">';
            carousel += '<div class="carousel-inner">';
            
            imageUrls.forEach((url, index) => {
                carousel += `<div class="carousel-item ${index === 0 ? 'active' : ''}">`;
                carousel += `<img src="${url}" class="d-block w-100" style="max-height: 500px; object-fit: contain;">`;
                carousel += '</div>';
            });
            
            carousel += '</div>';
            
            if (imageUrls.length > 1) {
                carousel += '<button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">';
                carousel += '<span class="carousel-control-prev-icon"></span>';
                carousel += '</button>';
                carousel += '<button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">';
                carousel += '<span class="carousel-control-next-icon"></span>';
                carousel += '</button>';
            }
            
            carousel += '</div>';
            
            // Replace modal content
            document.querySelector('#imageModal .modal-body').innerHTML = carousel;
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }
    }

    // Update status function
    function updateStatus(id, status) {
        if (confirm('Apakah Anda yakin ingin mengubah status pengaduan ini?')) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('admin/pengaduan/') ?>' + id + '/status';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '<?= csrf_token() ?>';
            csrfInput.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfInput);
            
            // Add status
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            form.appendChild(statusInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Edit pengaduan function
    function editPengaduan(id) {
        window.location.href = '<?= base_url('admin/pengaduan/edit/') ?>' + id;
    }

    // Delete function
    function deletePengaduan(id) {
        document.getElementById('deleteForm').action = '<?= base_url('admin/pengaduan/delete/') ?>' + id;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>
<?= $this->endSection() ?>
