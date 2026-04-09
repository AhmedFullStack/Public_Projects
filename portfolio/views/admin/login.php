<!-- ══ ADMIN LOGIN ════════════════════════════════════════ -->
<?php /* views/admin/login.php */ ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>تسجيل الدخول — Admin</title>
  <meta name="robots" content="noindex,nofollow">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Cairo',sans-serif;background:#0a1628;color:#e0e8f4;min-height:100vh;display:flex;align-items:center;justify-content:center;background-image:linear-gradient(rgba(74,158,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(74,158,255,.03) 1px,transparent 1px);background-size:48px 48px}
    .login-box{width:100%;max-width:400px;padding:0 20px}
    .login-logo{text-align:center;margin-bottom:32px}
    .login-logo-icon{width:56px;height:56px;background:rgba(74,158,255,.1);border:1px solid rgba(74,158,255,.3);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:12px}
    .login-logo h1{font-size:1.1rem;font-weight:700;color:#e0e8f4}
    .login-logo p{font-size:.8rem;color:#8ba3bc;margin-top:4px}
    .login-card{background:#0d1e35;border:1px solid rgba(74,158,255,.12);border-radius:14px;padding:32px}
    .field{margin-bottom:18px}
    .field label{display:block;font-size:.8rem;color:#8ba3bc;margin-bottom:6px;font-weight:500}
    .field input{width:100%;background:#0a1628;border:1px solid rgba(74,158,255,.2);border-radius:8px;color:#e0e8f4;padding:11px 14px;font-size:.9rem;font-family:inherit;transition:border-color .15s;direction:ltr}
    .field input:focus{outline:none;border-color:#4a9eff}
    .remember{display:flex;align-items:center;gap:8px;font-size:.8rem;color:#8ba3bc;margin-bottom:20px}
    .remember input{width:auto}
    .btn-login{width:100%;background:#4a9eff;color:#fff;border:none;border-radius:8px;padding:12px;font-size:.9rem;font-weight:600;font-family:inherit;cursor:pointer;transition:background .15s}
    .btn-login:hover{background:#2e7dd9}
    .alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);color:#fca5a5;border-radius:8px;padding:10px 14px;font-size:.85rem;margin-bottom:16px}
    .back-link{text-align:center;margin-top:20px;font-size:.8rem;color:#5a7a96}
    .back-link a{color:#4a9eff}
  </style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">
    <div class="login-logo-icon">⚙</div>
    <h1>لوحة التحكم</h1>
    <p>Portfolio Admin Panel</p>
  </div>

  <div class="login-card">
    <?php if (!empty($flash['error'])): ?>
    <div class="alert-error" role="alert"><?= e(implode(' ', $flash['error'])) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('admin/login') ?>" novalidate>
      <?= \App\Core\Security::csrfField() ?>

      <div class="field">
        <label for="login-email">البريد الإلكتروني</label>
        <input type="email" id="login-email" name="email" autocomplete="email" required autofocus>
      </div>

      <div class="field">
        <label for="login-pass">كلمة المرور</label>
        <input type="password" id="login-pass" name="password" autocomplete="current-password" required>
      </div>

      <label class="remember">
        <input type="checkbox" name="remember" value="1">
        تذكرني لمدة 30 يوم
      </label>

      <button type="submit" class="btn-login">تسجيل الدخول</button>
    </form>
  </div>

  <div class="back-link"><a href="<?= base_url() ?>">← العودة للموقع</a></div>
</div>
</body>
</html>