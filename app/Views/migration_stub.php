<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'ASTALA CI4 Migration') ?></title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f8fafc; color: #0f172a; font-family: Arial, sans-serif; }
        main { width: min(720px, calc(100vw - 32px)); padding: 28px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; }
        code { padding: 2px 6px; border-radius: 4px; background: #e2e8f0; }
    </style>
</head>
<body>
    <main>
        <h1>ASTALA CI4 Migration</h1>
        <p>Route <code><?= esc($path ?? '/') ?></code> sudah terdaftar di CodeIgniter 4.</p>
        <p>Controller dan view detail untuk modul ini masih tahap porting dari aplikasi Node/EJS.</p>
    </main>
</body>
</html>
