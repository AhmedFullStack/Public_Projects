<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'لوحة التحكم') ?> — Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{--bg:#0a1628;--bg2:#0d1e35;--bg3:#0f2240;--accent:#4a9eff;--accent-d:#2e7dd9;--accent-glow:rgba(74,158,255,.12);--text:#e0e8f4;--text2:#8ba3bc;--text3:#5a7a96;--border:rgba(74,158,255,.12);--border2:rgba(74,158,255,.3);--danger:#ef4444;--success:#22c55e;--warning:#f59e0b;--sidebar:220px;--header:56px;--radius:10px}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Cairo',sans-serif;background:var(--bg);color:var(--text);direction:rtl;min-height:100vh;display:flex}
    a{color:var(--accent);text-decoration:none}
    button{cursor:pointer;font-family:inherit}
    ul{list-style:none}
    input,select,textarea{font-family:inherit}

    /* Sidebar */
    .admin-sidebar{width:var(--sidebar);flex-shrink:0;background:var(--bg2);border-left:1px solid var(--border);display:flex;flex-direction:column;position:fixed;inset-block:0;inset-inline-start:0;z-index:200;transition:transform .25s}
    .sidebar-logo{height:var(--header);display:flex;align-items:center;padding:0 20px;border-bottom:1px solid var(--border);gap:10px}
    .sidebar-logo-icon{width:32px;height:32px;background:var(--accent-glow);border:1px solid var(--border2);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:16px}
    .sidebar-logo-text{font-weight:700;font-size:.9rem;color:var(--text)}
    .sidebar-nav{flex:1;padding:16px 0;overflow-y:auto}
    .sidebar-section{font-size:.7rem;font-weight:600;letter-spacing:.08em;color:var(--text3);text-transform:uppercase;padding:8px 20px 4px}
    .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 20px;color:var(--text2);font-size:.875rem;font-weight:500;transition:all .15s;position:relative}
    .sidebar-link:hover{color:var(--accent);background:var(--accent-glow)}
    .sidebar-link.active{color:var(--accent);background:var(--accent-glow)}
    .sidebar-link.active::before{content:'';position:absolute;inset-block:0;inset-inline-end:0;width:3px;background:var(--accent);border-radius:2px 0 0 2px}
    .sidebar-link svg{width:18px;height:18px;flex-shrink:0}
    .badge-count{margin-inline-start:auto;background:var(--danger);color:#fff;font-size:.7rem;font-weight:700;border-radius:10px;padding:1px 6px;min-width:18px;text-align:center}

    /* Main */
    .admin-main{flex:1;margin-inline-start:var(--sidebar);display:flex;flex-direction:column;min-height:100vh}

    /* Topbar */
    .admin-topbar{height:var(--header);background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 24px;gap:16px;position:sticky;top:0;z-index:100}
    .topbar-title{font-size:1rem;font-weight:600;color:var(--text);flex:1}
    .topbar-admin{font-size:.8rem;color:var(--text2)}
    .topbar-logout{font-size:.8rem;color:var(--text3);border:1px solid var(--border);border-radius:6px;padding:5px 12px;transition:all .15s}
    .topbar-logout:hover{color:var(--danger);border-color:var(--danger)}

    /* Content */
    .admin-content{flex:1;padding:24px;max-width:1100px;width:100%}

    /* Cards */
    .adm-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:20px}
    .adm-card-title{font-size:.9rem;font-weight:600;color:var(--text);margin-bottom:16px;display:flex;align-items:center;gap:8px}
    .adm-card-title::before{content:'';width:3px;height:16px;background:var(--accent);border-radius:2px}

    /* Stat cards */
    .stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
    .stat-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:20px}
    .stat-card-label{font-size:.75rem;color:var(--text3);margin-bottom:6px}
    .stat-card-value{font-size:2rem;font-weight:700;color:var(--accent)}
    .stat-card-sub{font-size:.75rem;color:var(--text3);margin-top:4px}

    /* Tables */
    .adm-table{width:100%;border-collapse:collapse;font-size:.875rem}
    .adm-table th{text-align:right;padding:10px 12px;font-size:.75rem;font-weight:600;color:var(--text3);text-transform:uppercase;border-bottom:1px solid var(--border)}
    .adm-table td{padding:12px;border-bottom:1px solid rgba(74,158,255,.06);color:var(--text2);vertical-align:middle}
    .adm-table tr:last-child td{border-bottom:none}
    .adm-table tr:hover td{background:var(--accent-glow)}

    /* Buttons */
    .btn-adm{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:6px;font-size:.8rem;font-weight:500;border:1px solid transparent;transition:all .15s;font-family:inherit}
    .btn-adm-primary{background:var(--accent);color:#fff;border-color:var(--accent)}
    .btn-adm-primary:hover{background:var(--accent-d)}
    .btn-adm-outline{background:transparent;color:var(--text2);border-color:var(--border2)}
    .btn-adm-outline:hover{color:var(--accent);border-color:var(--accent)}
    .btn-adm-danger{background:transparent;color:var(--danger);border-color:rgba(239,68,68,.3)}
    .btn-adm-danger:hover{background:rgba(239,68,68,.1)}
    .btn-adm-sm{padding:4px 10px;font-size:.75rem}

    /* Forms */
    .form-group-adm{margin-bottom:16px}
    .form-label-adm{display:block;font-size:.8rem;color:var(--text2);margin-bottom:5px;font-weight:500}
    .form-ctrl-adm{width:100%;background:var(--bg2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:.875rem;padding:10px 12px;transition:border-color .15s;font-family:inherit;direction:rtl}
    .form-ctrl-adm:focus{outline:none;border-color:var(--accent)}
    .form-ctrl-adm::placeholder{color:var(--text3)}
    textarea.form-ctrl-adm{resize:vertical;min-height:100px}
    .form-row-adm{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .form-hint{font-size:.75rem;color:var(--text3);margin-top:3px}

    /* Alerts/Flash */
    .adm-flash{padding:10px 16px;border-radius:6px;font-size:.875rem;margin-bottom:16px;border:1px solid}
    .adm-flash-success{background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.3);color:#86efac}
    .adm-flash-error{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.3);color:#fca5a5}

    /* Status badges */
    .status-badge{display:inline-block;font-size:.7rem;font-weight:600;border-radius:4px;padding:2px 8px}
    .status-new{background:rgba(245,158,11,.15);color:#fcd34d}
    .status-read{background:rgba(74,158,255,.1);color:var(--accent)}
    .status-replied{background:rgba(34,197,94,.1);color:#86efac}

    /* Toggle */
    .toggle-wrap{display:flex;align-items:center;gap:8px}
    .toggle{appearance:none;width:36px;height:20px;background:var(--border2);border-radius:10px;position:relative;cursor:pointer;transition:background .2s;flex-shrink:0}
    .toggle:checked{background:var(--accent)}
    .toggle::after{content:'';position:absolute;width:14px;height:14px;background:#fff;border-radius:50%;top:3px;inset-inline-start:3px;transition:inset-inline-start .2s}
    .toggle:checked::after{inset-inline-start:19px}

    /* Responsive sidebar */
    @media(max-width:768px){
      .admin-sidebar{transform:translateX(100%)}
      .admin-sidebar.open{transform:none}
      .admin-main{margin-inline-start:0}
      .stat-grid{grid-template-columns:1fr 1fr}
    }
    @media(max-width:480px){.stat-grid{grid-template-columns:1fr}.form-row-adm{grid-template-columns:1fr}}

    /* Lang tabs */
    .lang-tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:20px;gap:4px}
    .lang-tab{padding:8px 20px;font-size:.875rem;color:var(--text2);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;transition:all .15s;font-family:inherit}
    .lang-tab.active{color:var(--accent);border-bottom-color:var(--accent)}
    .lang-panel{display:none}.lang-panel.active{display:block}
  </style>
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar" role="navigation" aria-label="Admin navigation">
  <div class="sidebar-logo">
    <div class="sidebar-logo-icon">⚙</div>
    <div class="sidebar-logo-text">لوحة التحكم</div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section">القائمة</div>
    <?php
      $adminPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
      $links = [
        'admin'           => ['icon' => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" fill="currentColor"/>', 'label' => 'الرئيسية'],
        'admin/projects'  => ['icon' => '<path d="M20 6h-2.18c.07-.44.18-.88.18-1.36C18 2.51 16.5 1 14.64 1 13.15 1 12.19 1.75 11.5 2.67L10 4.5 8.5 2.67C7.81 1.75 6.85 1 5.36 1 3.5 1 2 2.51 2 4.64c0 .48.11.92.18 1.36H0v2h20V6z" fill="currentColor"/>', 'label' => 'المشاريع'],
        'admin/skills'    => ['icon' => '<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm4.24 16L12 15.45 7.77 18l1.12-4.81-3.73-3.23 4.92-.42L12 5l1.92 4.53 4.92.42-3.73 3.23L16.23 18z" fill="currentColor"/>', 'label' => 'المهارات'],
        'admin/messages'  => ['icon' => '<path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="currentColor"/>', 'label' => 'الرسائل', 'badge' => true],
        'admin/cv'        => ['icon' => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="currentColor"/>', 'label' => 'السيرة الذاتية'],
        'admin/settings'  => ['icon' => '<path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96a7.04 7.04 0 0 0-1.62-.94l-.36-2.54a.484.484 0 0 0-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96a.48.48 0 0 0-.59.22L2.74 8.87a.48.48 0 0 0 .12.61l2.03 1.58c-.05.3-.07.63-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.37 1.04.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.57 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.49.49 0 0 0-.12-.61l-2.03-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" fill="currentColor"/>', 'label' => 'الإعدادات'],
        'admin/seo'       => ['icon' => '<path d="M9 3L5 6.99h3V14h2V6.99h3L9 3zm7 14.01V10h-2v7.01h-3L15 21l4-3.99h-3z" fill="currentColor"/>', 'label' => 'إعدادات SEO'],
      ];
      $unread = 0;
      try { $unread = (new \App\Models\MessageModel())->countUnread(); } catch(\Throwable $e) {}
      foreach ($links as $href => $link):
        $isActive = $adminPath === $href || str_starts_with($adminPath, $href . '/');
    ?>
    <a href="<?= base_url($href) ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?>" aria-current="<?= $isActive ? 'page' : 'false' ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true"><?= $link['icon'] ?></svg>
      <?= e($link['label']) ?>
      <?php if (!empty($link['badge']) && $unread > 0): ?>
      <span class="badge-count" aria-label="<?= $unread ?> رسالة جديدة"><?= $unread ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>

    <div class="sidebar-section" style="margin-top:16px;">الموقع</div>
    <a href="<?= base_url() ?>" target="_blank" class="sidebar-link">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z" fill="currentColor"/></svg>
      عرض الموقع
    </a>
  </nav>
</aside>

<!-- Main -->
<div class="admin-main">
  <header class="admin-topbar">
    <h1 class="topbar-title"><?= e($pageTitle ?? 'لوحة التحكم') ?></h1>
    <span class="topbar-admin">👤 <?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
    <a href="<?= base_url('admin/logout') ?>" class="topbar-logout">تسجيل الخروج</a>
  </header>

  <div class="admin-content">
    <?php
    // Admin flash messages
    $flash = \App\Core\Controller::getFlash();
    foreach ($flash as $type => $msgs) {
        foreach ($msgs as $msg) {
            $cls = $type === 'success' ? 'adm-flash-success' : 'adm-flash-error';
            echo '<div class="adm-flash ' . $cls . '" role="alert">' . e($msg) . '</div>';
        }
    }
    ?>
    <?= $content ?>
  </div>
</div>

<script>
// Lang tabs
document.querySelectorAll('.lang-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    const lang = tab.dataset.lang;
    const parent = tab.closest('.lang-tabs-wrapper');
    parent.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    parent.querySelectorAll('.lang-panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    parent.querySelector('.lang-panel[data-lang="' + lang + '"]').classList.add('active');
  });
});

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'هل أنت متأكد؟')) e.preventDefault();
  });
});
</script>
</body>
</html>