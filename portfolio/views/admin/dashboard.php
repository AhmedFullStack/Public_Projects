<?php $pageTitle = 'لوحة التحكم'; ?>

<!-- Stat Cards -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-card-label">المشاريع النشطة</div>
    <div class="stat-card-value"><?= (int)($stats['active_projects'] ?? 0) ?></div>
    <div class="stat-card-sub">من إجمالي <?= (int)($stats['total_projects'] ?? 0) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">الرسائل غير المقروءة</div>
    <div class="stat-card-value" style="color:var(--warning)"><?= (int)($stats['unread_messages'] ?? 0) ?></div>
    <div class="stat-card-sub">من إجمالي <?= (int)($stats['total_messages'] ?? 0) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">مشاهدات المشاريع</div>
    <div class="stat-card-value" style="color:var(--success)"><?= number_format((int)($stats['total_views'] ?? 0)) ?></div>
    <div class="stat-card-sub">تحميلات CV: <?= (int)($stats['cv_downloads'] ?? 0) ?></div>
  </div>
</div>

<!-- Quick Actions -->
<div class="adm-card" style="margin-bottom:20px;">
  <div class="adm-card-title">إجراءات سريعة</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px;">
    <a href="<?= base_url('admin/projects/create') ?>" class="btn-adm btn-adm-primary">+ مشروع جديد</a>
    <a href="<?= base_url('admin/skills') ?>" class="btn-adm btn-adm-outline">تعديل المهارات</a>
    <a href="<?= base_url('admin/cv') ?>" class="btn-adm btn-adm-outline">رفع السيرة الذاتية</a>
    <a href="<?= base_url('admin/settings') ?>" class="btn-adm btn-adm-outline">الإعدادات العامة</a>
    <a href="<?= base_url('admin/seo') ?>" class="btn-adm btn-adm-outline">إعدادات SEO</a>
  </div>
</div>

<!-- Recent Messages -->
<?php if (!empty($recentMessages)): ?>
<div class="adm-card">
  <div class="adm-card-title" style="justify-content:space-between;display:flex;align-items:center;">
    <span>آخر الرسائل</span>
    <a href="<?= base_url('admin/messages') ?>" style="font-size:.75rem;font-weight:400;">عرض الكل →</a>
  </div>
  <table class="adm-table">
    <thead>
      <tr><th>الاسم</th><th>الموضوع</th><th>الحالة</th><th>التاريخ</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($recentMessages as $msg): ?>
      <tr>
        <td><?= e($msg['name']) ?></td>
        <td><?= e(mb_substr($msg['subject'], 0, 40)) ?></td>
        <td><span class="status-badge status-<?= e($msg['status']) ?>"><?= e($msg['status']) ?></span></td>
        <td style="color:var(--text3);font-size:.8rem;"><?= date('d/m/Y', strtotime($msg['created_at'])) ?></td>
        <td><a href="<?= base_url('admin/messages/' . $msg['id']) ?>" class="btn-adm btn-adm-outline btn-adm-sm">عرض</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Recent Projects -->
<?php if (!empty($recentProjects)): ?>
<div class="adm-card">
  <div class="adm-card-title" style="justify-content:space-between;display:flex;align-items:center;">
    <span>آخر المشاريع</span>
    <a href="<?= base_url('admin/projects') ?>" style="font-size:.75rem;font-weight:400;">عرض الكل →</a>
  </div>
  <table class="adm-table">
    <thead>
      <tr><th>العنوان (عربي)</th><th>السنة</th><th>الحالة</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($recentProjects as $proj): ?>
      <tr>
        <td><?= e($proj['title_ar'] ?? '—') ?></td>
        <td><?= e($proj['year'] ?? '—') ?></td>
        <td>
          <?php if ($proj['is_active']): ?>
          <span class="status-badge status-replied">نشط</span>
          <?php else: ?>
          <span class="status-badge" style="background:rgba(90,122,150,.15);color:var(--text3)">مخفي</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="<?= base_url('admin/projects/' . $proj['id'] . '/edit') ?>" class="btn-adm btn-adm-outline btn-adm-sm">تعديل</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>