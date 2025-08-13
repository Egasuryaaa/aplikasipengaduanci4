<?= $this->extend('admin/layout/main') ?>

<?= $this->section('title') ?>
Edit Pengaduan - Sistem Pengaduan K                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="keterangan_admin" class="form-label">Keterangan Admin</label>
                        <textarea class="form-control <?= (isset($validation) && $validation->hasError('keterangan_admin')) ? 'is-invalid' : '' ?>" 
                                  id="keterangan_admin" name="keterangan_admin" rows="3" placeholder="Keterangan untuk pengaduan ini..."><?= old('keterangan_admin', $pengaduan['keterangan_admin']) ?></textarea>
                        <?php if (isset($validation) && $validation->hasError('keterangan_admin')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('keterangan_admin') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>$this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Pengaduan</h1>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-white">Form Edit Pengaduan #<?= $pengaduan['nomor_pengaduan'] ?></h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/pengaduan/update/' . $pengaduan['id']) ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nomor_pengaduan" class="form-label">No. Pengaduan</label>
                        <input type="text" class="form-control" id="nomor_pengaduan" value="<?= $pengaduan['nomor_pengaduan'] ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="user_name" class="form-label">Pelapor</label>
                        <input type="text" class="form-control" id="user_name" value="<?= $pengaduan['user_name'] ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi Pengaduan</label>
                    <textarea class="form-control <?= (isset($validation) && $validation->hasError('deskripsi')) ? 'is-invalid' : '' ?>" 
                              id="deskripsi" name="deskripsi" rows="4"><?= old('deskripsi', $pengaduan['deskripsi']) ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('deskripsi')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('deskripsi') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select <?= (isset($validation) && $validation->hasError('kategori_id')) ? 'is-invalid' : '' ?>" 
                                id="kategori_id" name="kategori_id">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori_list as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>" <?= old('kategori_id', $pengaduan['kategori_id']) == $kategori['id'] ? 'selected' : '' ?>>
                                    <?= $kategori['nama'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($validation) && $validation->hasError('kategori_id')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('kategori_id') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="instansi_id" class="form-label">Instansi</label>
                        <select class="form-select <?= (isset($validation) && $validation->hasError('instansi_id')) ? 'is-invalid' : '' ?>" 
                                id="instansi_id" name="instansi_id">
                            <option value="">Pilih Instansi</option>
                            <?php foreach ($instansi_list as $instansi): ?>
                                <option value="<?= $instansi['id'] ?>" <?= old('instansi_id', $pengaduan['instansi_id']) == $instansi['id'] ? 'selected' : '' ?>>
                                    <?= $instansi['nama'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($validation) && $validation->hasError('instansi_id')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('instansi_id') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select <?= (isset($validation) && $validation->hasError('status')) ? 'is-invalid' : '' ?>" 
                                id="status" name="status">
                            <option value="pending" <?= old('status', $pengaduan['status']) == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="diproses" <?= old('status', $pengaduan['status']) == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="selesai" <?= old('status', $pengaduan['status']) == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="ditolak" <?= old('status', $pengaduan['status']) == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                        <?php if (isset($validation) && $validation->hasError('status')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('status') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="keterangan_admin" class="form-label">Keterangan Admin</label>
                        <textarea class="form-control <?= (isset($validation) && $validation->hasError('keterangan_admin')) ? 'is-invalid' : '' ?>" 
                                  id="keterangan_admin" name="keterangan_admin" rows="3" placeholder="Keterangan untuk pengaduan ini..."><?= old('keterangan_admin', $pengaduan['keterangan_admin']) ?></textarea>
                        <?php if (isset($validation) && $validation->hasError('keterangan_admin')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('keterangan_admin') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="current_foto" class="form-label">Foto Bukti Saat Ini</label>
                        <?php if (!empty($pengaduan['foto_bukti'])): ?>
                            <div class="mb-2">
                                <img src="<?= base_url('uploads/pengaduan/' . $pengaduan['foto_bukti']) ?>" 
                                     class="img-thumbnail" style="max-height: 200px;" alt="Foto Bukti">
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada foto bukti</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="foto_bukti" class="form-label">Upload Foto Bukti Baru (Opsional)</label>
                        <input type="file" class="form-control <?= (isset($validation) && $validation->hasError('foto_bukti')) ? 'is-invalid' : '' ?>" 
                               id="foto_bukti" name="foto_bukti">
                        <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maks 2MB</small>
                        <?php if (isset($validation) && $validation->hasError('foto_bukti')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('foto_bukti') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan_admin" class="form-label">Keterangan Admin</label>
                    <textarea class="form-control <?= (isset($validation) && $validation->hasError('keterangan_admin')) ? 'is-invalid' : '' ?>" 
                              id="keterangan_admin" name="keterangan_admin" rows="3"><?= old('keterangan_admin', $pengaduan['keterangan_admin']) ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('keterangan_admin')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('keterangan_admin') ?>
                        </div>
                    <?php endif; ?>
                    <small class="form-text text-muted">Catatan internal yang akan tampil di detail pengaduan.</small>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= base_url('admin/pengaduan') ?>" class="btn btn-secondary me-md-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
