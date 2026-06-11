<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form method="post" action="<?= site_url('auth/signup') ?>" class="auth-card">
    <?= csrf_field() ?>
    <img src="<?= base_url('logo_astala.png') ?>" alt="ASTALA" class="auth-logo">
    <h1>Daftar ASTALA</h1>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif ?>

    <label>
        Nama
        <input type="text" name="nama" required>
    </label>

    <label>
        Email
        <input type="email" name="email" required>
    </label>

    <label>
        No HP
        <input type="text" name="no_hp">
    </label>

    <label>
        Password
        <input type="password" name="password" required>
    </label>

    <label>
        Konfirmasi Password
        <input type="password" name="confirm_password" required>
    </label>

    <button type="submit">Daftar</button>
    <a href="<?= site_url('auth/login') ?>">Sudah punya akun?</a>
</form>
<?= $this->endSection() ?>
