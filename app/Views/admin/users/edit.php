<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Edit Pengguna</h1>
        <p class="text-muted">Perbarui data pengguna</p>
    </div>
</div>
<?php $errors = session('errors') ?? []; ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-white">Form Pengguna</h6>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/users/' . $user['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= old('name', $user['name']) ?>" required>
                        <?php if(isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= old('email', $user['email']) ?>" required>
                            <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= old('phone', $user['phone']) ?>">
                            <?php if(isset($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone'] ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password (isi jika ingin mengganti)</label>
                            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
                            <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'] ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="master" <?= old('role', $user['role']) == 'master' ? 'selected' : '' ?>>Master</option>
                                <option value="admin" <?= old('role', $user['role']) == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="user" <?= old('role', $user['role']) == 'user' ? 'selected' : '' ?>>User</option>
                            </select>
                            <?php if(isset($errors['role'])): ?><div class="invalid-feedback"><?= $errors['role'] ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instansi</label>
                        <select name="instansi_id" class="form-select">
                            <option value="">-- Pilih Instansi --</option>
                            <?php if(isset($instansi)): foreach($instansi as $ins): ?>
                                <option value="<?= $ins['id'] ?>" <?= old('instansi_id', $user['instansi_id']) == $ins['id'] ? 'selected' : '' ?>><?= $ins['nama'] ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', $user['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
