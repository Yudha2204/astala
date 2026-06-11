<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form method="post" action="<?= site_url('auth/login') ?>" class="auth-card">
    <?= csrf_field() ?>
    <img src="<?= base_url('logo_astala.png') ?>" alt="ASTALA" class="auth-logo">
    <h1>Login ASTALA</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif ?>

    <label>
        Email
        <input type="email" name="email" required autofocus>
    </label>

    <label>
        Password
        <input type="password" name="password" required>
    </label>

    <button type="submit">Masuk</button>
    <a href="<?= site_url('auth/signup') ?>">Daftar akun mitra</a>
</form>
<?= $this->endSection() ?>
