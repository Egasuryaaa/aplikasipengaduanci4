<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Tambah Instansi</h1>
        <p class="text-muted">Form untuk menambahkan instansi baru</p>
    </div>
</div>

<?php $errors = session('errors') ?? []; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-white">Form Instansi</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/instansi') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>" value="<?= old('nama') ?>" required>
                        <?php if(isset($errors['nama'])): ?><div class="invalid-feedback"><?= $errors['nama'] ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control <?= isset($errors['alamat']) ? 'is-invalid' : '' ?>" rows="3"><?= old('alamat') ?></textarea>
                        <?php if(isset($errors['alamat'])): ?><div class="invalid-feedback"><?= $errors['alamat'] ?></div><?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= old('email') ?>">
                            <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control <?= isset($errors['telepon']) ? 'is-invalid' : '' ?>" value="<?= old('telepon') ?>">
                            <?php if(isset($errors['telepon'])): ?><div class="invalid-feedback"><?= $errors['telepon'] ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active') ? 'checked' : 'checked' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/instansi') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
