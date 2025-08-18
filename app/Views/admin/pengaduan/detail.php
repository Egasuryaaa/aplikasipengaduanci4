<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Detail Pengaduan - <?= $pengaduan['nomor_pengaduan'] ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .timeline-comment {
        position: relative;
        margin-bottom: 1.5rem;
        padding-left: 60px;
    }
    .timeline-comment:before {
        content: '';
        position: absolute;
        top: 0;
        left: 30px;
        height: 100%;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-comment:last-child:before {
        height: 20px;
    }
    .timeline-comment-badge {
        position: absolute;
        top: 0;
        left: 0;
        width: 60px;
        display: flex;
        justify-content: center;
    }
    .timeline-comment-badge .badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        z-index: 1;
    }
    .timeline-comment-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    .timeline-comment-body {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        position: relative;
    }
    .timeline-comment-body:before {
        content: '';
        position: absolute;
        left: -8px;
        top: 15px;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid #f8f9fa;
    }
    .internal-comment {
        background-color: #fff3cd;
    }
    .internal-comment:before {
        border-right-color: #fff3cd;
    }
    .status-badge {
        min-width: 100px;
    }
    .foto-bukti-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .foto-bukti-item {
        width: 150px;
        height: 150px;
        object-fit: cover;
        cursor: pointer;
        border-radius: 5px;
        transition: transform 0.2s;
    }
    .foto-bukti-item:hover {
        transform: scale(1.05);
    }
    .comment-toggle {
        cursor: pointer;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pengaduan</h1>
        <a href="<?= base_url('admin/pengaduan') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Kembali
        </a>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Main Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-white">Informasi Pengaduan #<?= $pengaduan['nomor_pengaduan'] ?></h6>
            <div>
                <?php
                $badgeClass = '';
                $statusText = '';
                switch($pengaduan['status']) {
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
                        $statusText = ucfirst($pengaduan['status']);
                }
                ?>
                <span class="badge <?= $badgeClass ?> status-badge"><?= $statusText ?></span>
                
                <div class="dropdown d-inline-block ml-2">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Aksi
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#statusModal">
                            <i class="fas fa-edit fa-sm fa-fw me-2"></i> Update Status
                        </a></li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/pengaduan/' . $pengaduan['id'] . '/edit') ?>">
                            <i class="fas fa-pen fa-sm fa-fw me-2"></i> Edit Pengaduan
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash fa-sm fa-fw me-2"></i> Hapus Pengaduan
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">No. Pengaduan</th>
                            <td width="70%"><strong><?= $pengaduan['nomor_pengaduan'] ?></strong></td>
                        </tr>
                        <tr>
                            <th>Pelapor</th>
                            <td>
                                <strong><?= $pengaduan['user_name'] ?></strong><br>
                                <small class="text-muted">
                                    <i class="fas fa-envelope fa-sm"></i> <?= $pengaduan['user_email'] ?? '-' ?><br>
                                    <i class="fas fa-phone fa-sm"></i> <?= $pengaduan['user_phone'] ?? '-' ?>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td><?= $pengaduan['kategori_nama'] ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th>Instansi</th>
                            <td><?= $pengaduan['instansi_nama'] ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Laporan</th>
                            <td><?= date('d/m/Y H:i', strtotime($pengaduan['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Status</th>
                            <td width="70%">
                                <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                <?php if ($pengaduan['status'] === 'selesai' && !empty($pengaduan['tanggal_selesai'])): ?>
                                    <small class="d-block text-muted mt-1">
                                        Selesai pada: <?= date('d/m/Y H:i', strtotime($pengaduan['tanggal_selesai'])) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Terakhir Diperbarui</th>
                            <td><?= date('d/m/Y H:i', strtotime($pengaduan['updated_at'])) ?></td>
                        </tr>
                        <?php if (!empty($pengaduan['keterangan_admin'])): ?>
                        <tr>
                            <th>Keterangan Admin</th>
                            <td><em><?= $pengaduan['keterangan_admin'] ?></em></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="font-weight-bold">Deskripsi Pengaduan:</h6>
                    <p class="mb-3"><?= nl2br($pengaduan['deskripsi']) ?></p>
                </div>
            </div>

            <?php 
            // Parse foto_bukti as JSON array
            $fotoBukti = [];
            if (!empty($pengaduan['foto_bukti'])) {
                if (is_string($pengaduan['foto_bukti'])) {
                    $fotoBukti = json_decode($pengaduan['foto_bukti'], true) ?? [];
                } elseif (is_array($pengaduan['foto_bukti'])) {
                    $fotoBukti = $pengaduan['foto_bukti'];
                }
            }
            ?>
            <?php if (!empty($fotoBukti)): ?>
            <div class="row mt-2">
                <div class="col-12">
                    <h6 class="font-weight-bold">Foto Bukti (<?= count($fotoBukti) ?> foto):</h6>
                    <div class="foto-bukti-container">
                        <?php foreach ($fotoBukti as $foto): ?>
                            <?php 
                            // Handle both filename and full URL
                            $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : base_url('uploads/pengaduan/' . $foto);
                            ?>
                            <img src="<?= $fotoUrl ?>" 
                                 class="foto-bukti-item" 
                                 onclick="showImage('<?= $fotoUrl ?>')" 
                                 alt="Foto Bukti" 
                                 loading="lazy">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs for Comments and History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="pengaduanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="comments-tab" data-bs-toggle="tab" 
                            data-bs-target="#comments" type="button" role="tab" aria-selected="true">
                        <i class="fas fa-comments"></i> Komentar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" 
                            data-bs-target="#history" type="button" role="tab" aria-selected="false">
                        <i class="fas fa-history"></i> Riwayat Status
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="pengaduanTabsContent">
                <!-- Comments Tab -->
                <div class="tab-pane fade show active" id="comments" role="tabpanel">
                    <!-- Comment Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form id="commentForm" method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="komentar" class="form-label">Tambahkan Komentar</label>
                                    <textarea class="form-control" id="komentar" name="komentar" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Kirim Komentar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Comments List -->
                    <h6 class="font-weight-bold mb-3">Riwayat Komentar</h6>
                    
                    <?php if (empty($comments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada komentar untuk pengaduan ini.
                        </div>
                    <?php else: ?>
                        <div class="comments-timeline">
                            <?php foreach ($comments as $comment): ?>
                                <?php 
                                    $isAdmin = $comment['user_role'] === 'admin' || $comment['user_role'] === 'master';
                                    $badgeColor = $isAdmin ? 'bg-primary' : 'bg-success';
                                ?>
                                <div class="timeline-comment">
                                    <div class="timeline-comment-badge">
                                        <span class="badge <?= $badgeColor ?>">
                                            <?= substr($comment['user_name'], 0, 1) ?>
                                        </span>
                                    </div>
                                    <div class="timeline-comment-content">
                                        <div class="timeline-comment-header">
                                            <div>
                                                <strong><?= $comment['user_name'] ?></strong>
                                                <?php if ($isAdmin): ?>
                                                    <span class="badge bg-secondary">Admin</span>
                                                <?php endif; ?>
                                                <?php if (($user_role === 'admin' || $user_role === 'master') && 
                                                         ($comment['user_id'] == session('user_id') || $user_role === 'master')): ?>
                                                    <button class="btn btn-sm btn-link edit-comment-btn" 
                                                            data-id="<?= $comment['id'] ?>" 
                                                            data-content="<?= htmlspecialchars($comment['komentar']) ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                                        </div>
                                        <div class="timeline-comment-body" id="comment-body-<?= $comment['id'] ?>">
                                            <?= nl2br($comment['komentar']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- History Tab -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <?php if (empty($status_history)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada perubahan status untuk pengaduan ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Status Lama</th>
                                        <th>Status Baru</th>
                                        <th>Diubah Oleh</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($status_history as $history): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($history['created_at'])) ?></td>
                                            <td>
                                                <?php 
                                                    $oldStatusText = $history['status_old'] === 'pending' ? 'Menunggu' : ucfirst($history['status_old']);
                                                    $oldStatusClass = '';
                                                    switch($history['status_old']) {
                                                        case 'pending': $oldStatusClass = 'bg-warning text-dark'; break;
                                                        case 'diproses': $oldStatusClass = 'bg-info'; break;
                                                        case 'selesai': $oldStatusClass = 'bg-success'; break;
                                                        case 'ditolak': $oldStatusClass = 'bg-danger'; break;
                                                        default: $oldStatusClass = 'bg-secondary';
                                                    }
                                                ?>
                                                <span class="badge <?= $oldStatusClass ?>"><?= $oldStatusText ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $newStatusText = $history['status_new'] === 'pending' ? 'Menunggu' : ucfirst($history['status_new']);
                                                    $newStatusClass = '';
                                                    switch($history['status_new']) {
                                                        case 'pending': $newStatusClass = 'bg-warning text-dark'; break;
                                                        case 'diproses': $newStatusClass = 'bg-info'; break;
                                                        case 'selesai': $newStatusClass = 'bg-success'; break;
                                                        case 'ditolak': $newStatusClass = 'bg-danger'; break;
                                                        default: $newStatusClass = 'bg-secondary';
                                                    }
                                                ?>
                                                <span class="badge <?= $newStatusClass ?>"><?= $newStatusText ?></span>
                                            </td>
                                            <td><?= $history['updated_by_name'] ?? 'System' ?></td>
                                            <td><?= $history['keterangan'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Pengaduan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/pengaduan/' . $pengaduan['id'] . '/status') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" <?= $pengaduan['status'] == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="diproses" <?= $pengaduan['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="selesai" <?= $pengaduan['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="ditolak" <?= $pengaduan['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan_admin" class="form-label">Keterangan Admin</label>
                        <textarea class="form-control" id="keterangan_admin" name="keterangan_admin" rows="3"><?= $pengaduan['keterangan_admin'] ?></textarea>
                        <small class="form-text text-muted">Catatan internal yang akan tampil di detail pengaduan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
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
                <form id="deleteForm" action="<?= base_url('admin/pengaduan/delete/' . $pengaduan['id']) ?>" method="POST">
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
    $(document).ready(function() {
        // Handle comment form submission
        $('#commentForm').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                type: "POST",
                url: "<?= base_url('admin/pengaduan/' . $pengaduan['id'] . '/comment') ?>",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Show success alert
                        alert('Komentar berhasil ditambahkan!');
                        
                        // Reload the page to show the new comment
                        location.reload();
                    } else {
                        // Show error message
                        alert('Error: ' + (response.message || 'Gagal menambahkan komentar'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('Terjadi kesalahan sistem. Silakan coba lagi.');
                    console.error(error);
                }
            });
        });
    });

    // Show image in modal
    function showImage(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        imageModal.show();
    }

    // Edit Comment Functionality
    $(document).ready(function() {
        // Handle edit comment button click
        $('.edit-comment-btn').on('click', function() {
            const commentId = $(this).data('id');
            const commentContent = $(this).data('content');
            
            // Set the comment content in the modal
            $('#edit-comment-id').val(commentId);
            $('#edit-comment-content').val(commentContent);
            
            // Show the modal
            const editCommentModal = new bootstrap.Modal(document.getElementById('editCommentModal'));
            editCommentModal.show();
        });
        
        // Handle comment update form submission
        $('#edit-comment-form').on('submit', function(e) {
            e.preventDefault();
            
            const commentId = $('#edit-comment-id').val();
            const newContent = $('#edit-comment-content').val();
            
            $.ajax({
                url: '<?= site_url('admin/pengaduan/update-comment') ?>/' + commentId,
                type: 'POST',
                data: {
                    komentar: newContent,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update the comment content on the page
                        $('#comment-body-' + commentId).html(newContent.replace(/\n/g, '<br>'));
                        
                        // Hide the modal
                        const editCommentModal = bootstrap.Modal.getInstance(document.getElementById('editCommentModal'));
                        editCommentModal.hide();
                        
                        // Show success message
                        alert('Komentar berhasil diperbarui');
                    } else {
                        // Show error message
                        alert('Error: ' + (response.message || 'Gagal memperbarui komentar'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('Terjadi kesalahan sistem. Silakan coba lagi.');
                    console.error(error);
                }
            });
        });
    });
</script>

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCommentModalLabel">Edit Komentar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-comment-form">
                <div class="modal-body">
                    <input type="hidden" id="edit-comment-id">
                    <div class="mb-3">
                        <label for="edit-comment-content" class="form-label">Komentar</label>
                        <textarea class="form-control" id="edit-comment-content" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
