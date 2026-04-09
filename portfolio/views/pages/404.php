<?php /* views/pages/404.php */ use App\Core\Lang; ?>
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:2rem;">
  <div>
    <div style="font-size:6rem;font-weight:700;color:var(--border-hover);line-height:1;margin-bottom:1rem;">404</div>
    <h1 style="font-size:1.5rem;font-weight:600;color:var(--text-primary);margin-bottom:.5rem;">
      <?= e(Lang::t('common.error_404')) ?>
    </h1>
    <p style="color:var(--text-muted);margin-bottom:2rem;">
      <?= Lang::choose('الصفحة التي تبحث عنها غير موجودة أو تم نقلها.', 'The page you\'re looking for doesn\'t exist or has been moved.') ?>
    </p>
    <a href="<?= base_url() ?>" class="btn btn-primary"><?= e(Lang::t('common.go_home')) ?></a>
  </div>
</div>