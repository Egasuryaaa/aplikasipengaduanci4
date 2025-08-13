<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Tambah Kategori</h1>
        <p class="text-muted">Form untuk menambahkan kategori pengaduan</p>
    </div>
</div>
<?php $errors = session('errors') ?? []; ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-white">Form Kategori</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/kategori') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>" value="<?= old('nama') ?>" required>
                        <?php if(isset($errors['nama'])): ?><div class="invalid-feedback"><?= $errors['nama'] ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control <?= isset($errors['deskripsi']) ? 'is-invalid' : '' ?>" rows="3"><?= old('deskripsi') ?></textarea>
                        <?php if(isset($errors['deskripsi'])): ?><div class="invalid-feedback"><?= $errors['deskripsi'] ?></div><?php endif; ?>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active') ? 'checked' : 'checked' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/kategori') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
