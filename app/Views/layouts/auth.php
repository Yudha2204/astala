<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'ASTALA') ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; margin: 0; background: #f5f7fb; font-family: Arial, sans-serif; }
        .auth-card { width: min(420px, calc(100vw - 32px)); display: grid; gap: 14px; padding: 28px; border-radius: 8px; background: #fff; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); }
        .auth-logo { width: 120px; justify-self: center; }
        h1 { margin: 0 0 8px; font-size: 24px; text-align: center; }
        label { display: grid; gap: 6px; font-size: 14px; color: #334155; }
        input { min-height: 42px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; }
        button { min-height: 44px; border: 0; border-radius: 6px; background: #0f63b6; color: #fff; font-weight: 700; cursor: pointer; }
        a { color: #0f63b6; text-align: center; text-decoration: none; }
        .alert { padding: 10px 12px; border-radius: 6px; font-size: 14px; }
        .alert.success { background: #dcfce7; color: #166534; }
        .alert.error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
