<!DOCTYPE html>
<html lang="<?= e(Lang::get()) ?>" dir="<?= Lang::isRtl() ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?= SeoHelper::renderHead() ?>

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
  <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>">

  <!-- Main CSS -->
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <?php if (Lang::isRtl()): ?>
  <link rel="stylesheet" href="<?= asset('css/rtl.css') ?>">
  <?php endif; ?>

  <!-- Google AdSense -->
  <?php if (!empty($settings['google_adsense_id'])): ?>
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e($settings['google_adsense_id']) ?>" crossorigin="anonymous"></script>
  <?php endif; ?>
</head>
<body class="<?= Lang::isRtl() ? 'rtl' : 'ltr' ?>" data-locale="<?= e(Lang::get()) ?>">

<!-- Skip navigation for accessibility -->
<a href="#main-content" class="skip-link"><?= Lang::choose('انتقل للمحتوى', 'Skip to content') ?></a>

<!-- ═══ HEADER ═══════════════════════════════════════════ -->
<header class="site-header" id="site-header" role="banner">
  <nav class="nav-container" aria-label="<?= Lang::choose('القائمة الرئيسية', 'Main navigation') ?>">

    <!-- Logo -->
    <a href="<?= base_url() ?>" class="nav-logo" aria-label="<?= e($settings['owner_name'] ?? 'Portfolio') ?>">
      <span class="logo-icon" aria-hidden="true">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
          <rect x="2" y="2" width="28" height="28" rx="6" stroke="var(--accent)" stroke-width="1.5"/>
          <rect x="8" y="8" width="16" height="16" rx="3" stroke="var(--accent)" stroke-width="1"/>
          <circle cx="16" cy="16" r="3" fill="var(--accent)"/>
          <line x1="16" y1="2" x2="16" y2="8" stroke="var(--accent)" stroke-width="1.5"/>
          <line x1="16" y1="24" x2="16" y2="30" stroke="var(--accent)" stroke-width="1.5"/>
          <line x1="2" y1="16" x2="8" y2="16" stroke="var(--accent)" stroke-width="1.5"/>
          <line x1="24" y1="16" x2="30" y2="16" stroke="var(--accent)" stroke-width="1.5"/>
        </svg>
      </span>
      <span class="logo-text"><?= e($settings['owner_name'] ?? 'Portfolio') ?></span>
    </a>

    <!-- Desktop nav links -->
    <ul class="nav-links" role="list">
      <?php
        $navItems = [
          ''          => Lang::t('nav.home'),
          'portfolio' => Lang::t('nav.portfolio'),
          'about'     => Lang::t('nav.about'),
          'contact'   => Lang::t('nav.contact'),
        ];
        $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $basePath    = trim(config('app.app.base_path', ''), '/');
        $currentPath = $basePath ? ltrim(substr($currentPath, strlen($basePath)), '/') : $currentPath;
        foreach ($navItems as $href => $label):
          $isActive = ($href === '' && $currentPath === '') || ($href !== '' && str_starts_with($currentPath, $href));
      ?>
      <li>
        <a href="<?= base_url($href) ?>"
           class="nav-link <?= $isActive ? 'active' : '' ?>"
           <?= $isActive ? 'aria-current="page"' : '' ?>>
          <?= e($label) ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <!-- Actions -->
    <div class="nav-actions">
      <!-- Language switcher -->
      <a href="?lang=<?= e(Lang::other()) ?>"
         class="lang-switch"
         aria-label="<?= Lang::choose('Switch to English', 'التبديل للعربية') ?>">
        <?= e(Lang::t('common.lang_switch')) ?>
      </a>

      <!-- Mobile menu toggle -->
      <button class="menu-toggle" id="menuToggle"
              aria-expanded="false"
              aria-controls="mobileMenu"
              aria-label="<?= Lang::choose('فتح القائمة', 'Open menu') ?>">
        <span class="burger-line"></span>
        <span class="burger-line"></span>
        <span class="burger-line"></span>
      </button>
    </div>
  </nav>

  <!-- Mobile menu -->
  <div class="mobile-menu" id="mobileMenu" aria-hidden="true" role="dialog" aria-label="<?= Lang::choose('القائمة', 'Menu') ?>">
    <ul role="list">
      <?php foreach ($navItems as $href => $label): ?>
      <li><a href="<?= base_url($href) ?>" class="mobile-nav-link"><?= e($label) ?></a></li>
      <?php endforeach; ?>
      <li>
        <a href="?lang=<?= e(Lang::other()) ?>" class="mobile-nav-link mobile-lang">
          🌐 <?= e(Lang::t('common.lang_switch')) ?>
        </a>
      </li>
    </ul>
  </div>
</header>

<!-- ═══ MAIN CONTENT ═════════════════════════════════════ -->
<main id="main-content" tabindex="-1">
  <?php
  // Flash messages
  $flash = \App\Core\Controller::getFlash();
  if (!empty($flash)):
  ?>
  <div class="flash-container" role="alert" aria-live="polite">
    <?php foreach ($flash as $type => $messages): ?>
      <?php foreach ($messages as $msg): ?>
      <div class="flash flash--<?= e($type) ?>">
        <span><?= $msg /* pre-sanitized in controller */ ?></span>
        <button class="flash-close" aria-label="<?= Lang::choose('إغلاق', 'Close') ?>">×</button>
      </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?= $content ?>
</main>

<!-- ═══ FOOTER ═══════════════════════════════════════════ -->
<footer class="site-footer" role="contentinfo">
  <div class="footer-inner">
    <div class="footer-brand">
      <p class="footer-name"><?= e($settings['owner_name'] ?? '') ?></p>
      <p class="footer-title"><?= e($settings['owner_title'] ?? '') ?></p>
    </div>

    <div class="footer-links">
      <a href="<?= base_url('privacy') ?>"><?= Lang::t('nav.privacy') ?></a>
      <a href="<?= base_url('contact') ?>"><?= Lang::t('nav.contact') ?></a>
      <?php if (!empty($settings['linkedin_url'])): ?>
      <a href="<?= e($settings['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
      <?php endif; ?>
    </div>

    <p class="footer-copy">
      &copy; <?= date('Y') ?> <?= e($settings['owner_name'] ?? '') ?>
      &mdash;
      <?= Lang::choose('جميع الحقوق محفوظة', 'All rights reserved') ?>
    </p>
  </div>
</footer>

<!-- Back to top -->
<button class="back-to-top" id="backToTop" aria-label="<?= Lang::choose('العودة للأعلى', 'Back to top') ?>">
  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
    <path d="M8 12V4M4 8l4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</button>

<!-- Google Analytics -->
<?php if (!empty($settings['google_analytics_id'])): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($settings['google_analytics_id']) ?>"></script>
<script nonce="<?= Security::nonce() ?>">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= e($settings['google_analytics_id']) ?>', { anonymize_ip: true });
</script>
<?php endif; ?>

<!-- Main JS -->
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>