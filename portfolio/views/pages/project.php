<?php use App\Core\Lang; ?>

<!-- Project Header -->
<div class="project-detail-header">
  <div class="container">
    <div style="margin-bottom:12px;">
      <?php if (!empty($project['category_name'])): ?>
      <span class="project-badge" style="font-size:.8rem;"><?= e($project['category_name']) ?></span>
      <?php endif; ?>
    </div>
    <h1 class="section-title" style="text-align:<?= Lang::isRtl() ? 'right' : 'left' ?>; max-width:700px;">
      <?= e($project['title'] ?? '') ?>
    </h1>
    <?php if (!empty($project['summary'])): ?>
    <p style="color:var(--text-secondary);margin-top:.75rem;max-width:600px;line-height:1.7;">
      <?= e($project['summary']) ?>
    </p>
    <?php endif; ?>

    <!-- Share / Copy -->
    <div style="margin-top:1.5rem;display:flex;gap:12px;flex-wrap:wrap;">
      <a href="<?= base_url('portfolio') ?>" class="btn btn-outline btn-sm">
        ← <?= e(Lang::t('portfolio.back')) ?>
      </a>
      <?php if (!empty($project['project_url'])): ?>
      <a href="<?= e($project['project_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
        🔗 <?= Lang::choose('رابط المشروع', 'Project URL') ?>
      </a>
      <?php endif; ?>
      <button class="btn btn-outline btn-sm"
              data-copy-link="<?= e(base_url('portfolio/' . $project['slug'])) ?>"
              data-copied="<?= e(Lang::t('common.copied')) ?>">
        <?= e(Lang::t('common.copy_link')) ?>
      </button>
    </div>
  </div>
</div>

<!-- Project Body -->
<div class="project-detail-body">
  <div class="container">
    <div class="project-detail-layout">

      <!-- Main content -->
      <div>
        <?php if (!empty($project['featured_image'])): ?>
        <div style="margin-bottom:var(--space-xl);border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--border);">
          <img src="<?= base_url('public/uploads/projects/' . e($project['featured_image'])) ?>"
               alt="<?= e($project['title'] ?? '') ?>"
               style="width:100%;max-height:480px;object-fit:cover;">
        </div>
        <?php endif; ?>

        <?php if (!empty($project['description'])): ?>
        <div class="prose" style="margin-bottom:var(--space-xl);">
          <h2 style="color:var(--text-primary);font-size:1.1rem;margin-bottom:.75rem;">
            <?= Lang::choose('تفاصيل المشروع', 'Project Details') ?>
          </h2>
          <?= nl2br(e($project['description'])) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($project['challenges'])): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:var(--space-lg);margin-bottom:var(--space-lg);">
          <h3 style="color:var(--accent);font-size:.9rem;font-weight:600;margin-bottom:.5rem;">
            <?= e(Lang::t('portfolio.challenges')) ?>
          </h3>
          <p class="prose"><?= nl2br(e($project['challenges'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($project['results'])): ?>
        <div style="background:rgba(34,197,94,.05);border:1px solid rgba(34,197,94,.2);border-radius:var(--radius-md);padding:var(--space-lg);margin-bottom:var(--space-lg);">
          <h3 style="color:var(--success);font-size:.9rem;font-weight:600;margin-bottom:.5rem;">
            <?= e(Lang::t('portfolio.results')) ?>
          </h3>
          <p class="prose"><?= nl2br(e($project['results'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($project['technologies']) && is_array($project['technologies'])): ?>
        <div>
          <h3 style="color:var(--text-primary);font-size:.9rem;font-weight:600;margin-bottom:.75rem;">
            <?= e(Lang::t('portfolio.technologies')) ?>
          </h3>
          <div class="tech-tags">
            <?php foreach ($project['technologies'] as $tech): ?>
            <span class="tech-tag"><?= e($tech) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar meta -->
      <aside>
        <div class="project-meta-card">
          <h3 style="font-size:.85rem;font-weight:600;color:var(--text-secondary);margin-bottom:12px;">
            <?= Lang::choose('معلومات المشروع', 'Project Info') ?>
          </h3>

          <?php if (!empty($project['client'])): ?>
          <div class="meta-row">
            <span class="meta-key"><?= e(Lang::t('portfolio.client')) ?></span>
            <span class="meta-val"><?= e($project['client']) ?></span>
          </div>
          <?php endif; ?>

          <?php if (!empty($project['year'])): ?>
          <div class="meta-row">
            <span class="meta-key"><?= e(Lang::t('portfolio.year')) ?></span>
            <span class="meta-val"><?= e($project['year']) ?></span>
          </div>
          <?php endif; ?>

          <?php if (!empty($project['duration'])): ?>
          <div class="meta-row">
            <span class="meta-key"><?= e(Lang::t('portfolio.duration')) ?></span>
            <span class="meta-val"><?= e($project['duration']) ?></span>
          </div>
          <?php endif; ?>

          <?php if (!empty($project['category_name'])): ?>
          <div class="meta-row">
            <span class="meta-key"><?= Lang::choose('الفئة', 'Category') ?></span>
            <span class="meta-val"><?= e($project['category_name']) ?></span>
          </div>
          <?php endif; ?>

          <div class="meta-row">
            <span class="meta-key"><?= Lang::choose('المشاهدات', 'Views') ?></span>
            <span class="meta-val"><?= number_format((int)($project['views_count'] ?? 0)) ?></span>
          </div>
        </div>
      </aside>

    </div>

    <!-- Related Projects -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:var(--space-2xl);">
      <h2 class="section-title" style="font-size:1.3rem;margin-bottom:var(--space-lg);">
        <?= e(Lang::t('portfolio.related')) ?>
      </h2>
      <div class="projects-grid">
        <?php foreach ($related as $rel): ?>
        <article class="card project-card">
          <div class="project-thumb">
            <?php if (!empty($rel['featured_image'])): ?>
            <img data-src="<?= base_url('public/uploads/projects/' . e($rel['featured_image'])) ?>"
                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3C/svg%3E"
                 alt="<?= e($rel['title'] ?? '') ?>" loading="lazy">
            <?php else: ?>
            <div class="project-thumb-placeholder" aria-hidden="true">⚡</div>
            <?php endif; ?>
            <div class="project-overlay">
              <a href="<?= base_url('portfolio/' . e($rel['slug'])) ?>" class="btn btn-primary btn-sm">
                <?= e(Lang::t('portfolio.view')) ?>
              </a>
            </div>
          </div>
          <div class="project-body">
            <h3 class="project-title">
              <a href="<?= base_url('portfolio/' . e($rel['slug'])) ?>" style="color:inherit;">
                <?= e($rel['title'] ?? '') ?>
              </a>
            </h3>
            <p class="project-summary"><?= e(mb_substr($rel['summary'] ?? '', 0, 100)) ?>…</p>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>