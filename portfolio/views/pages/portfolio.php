<?php use App\Core\Lang; ?>

<div class="project-detail-header" style="padding: calc(var(--header-h) + 2rem) 0 2rem;">
  <div class="container">
    <span class="section-tag"><?= e(Lang::t('portfolio.title')) ?></span>
    <h1 class="section-title" style="text-align:<?= Lang::isRtl() ? 'right' : 'left' ?>; margin-top:.5rem;">
      <?= e(Lang::t('portfolio.subtitle')) ?>
    </h1>
  </div>
</div>

<section class="section">
  <div class="container">

    <!-- Category Filter -->
    <?php if (!empty($categories)): ?>
    <div class="filter-tabs" role="tablist" aria-label="<?= Lang::choose('تصفية حسب الفئة', 'Filter by category') ?>">
      <button class="filter-tab <?= !$categoryId ? 'active' : '' ?>"
              data-cat=""
              role="tab"
              aria-selected="<?= !$categoryId ? 'true' : 'false' ?>">
        <?= e(Lang::t('common.all')) ?>
      </button>
      <?php foreach ($categories as $cat): ?>
      <button class="filter-tab <?= $categoryId == $cat['id'] ? 'active' : '' ?>"
              data-cat="<?= (int)$cat['id'] ?>"
              role="tab"
              aria-selected="<?= $categoryId == $cat['id'] ? 'true' : 'false' ?>">
        <?= e($cat['name'] ?? $cat['slug']) ?>
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Projects Grid -->
    <?php if (!empty($items)): ?>
    <div class="projects-grid">
      <?php foreach ($items as $project): ?>
      <article class="card project-card" aria-labelledby="proj-<?= (int)$project['id'] ?>">
        <div class="project-thumb">
          <?php if (!empty($project['featured_image'])): ?>
          <img data-src="<?= base_url('public/uploads/projects/' . e($project['featured_image'])) ?>"
               src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3C/svg%3E"
               alt="<?= e($project['title'] ?? '') ?>"
               width="800" height="600"
               loading="lazy">
          <?php else: ?>
          <div class="project-thumb-placeholder" aria-hidden="true">⚡</div>
          <?php endif; ?>
          <div class="project-overlay">
            <a href="<?= base_url('portfolio/' . e($project['slug'])) ?>" class="btn btn-primary btn-sm">
              <?= e(Lang::t('portfolio.view')) ?>
            </a>
          </div>
        </div>

        <div class="project-body">
          <?php if (!empty($project['category_name'])): ?>
          <span class="project-badge"><?= e($project['category_name']) ?></span>
          <?php endif; ?>

          <h2 class="project-title" id="proj-<?= (int)$project['id'] ?>">
            <a href="<?= base_url('portfolio/' . e($project['slug'])) ?>" style="color:inherit;">
              <?= e($project['title'] ?? '') ?>
            </a>
          </h2>

          <p class="project-summary"><?= e(mb_substr($project['summary'] ?? '', 0, 130)) ?><?= mb_strlen($project['summary'] ?? '') > 130 ? '…' : '' ?></p>

          <div class="project-meta">
            <?php if ($project['year']): ?><span><?= e($project['year']) ?></span><?php endif; ?>
            <a href="<?= base_url('portfolio/' . e($project['slug'])) ?>" class="text-accent" style="font-size:.85rem;font-weight:500;">
              <?= e(Lang::t('common.read_more')) ?> →
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="<?= Lang::choose('تنقل الصفحات', 'Pagination') ?>">
      <?php if ($page > 1): ?>
      <a href="<?= base_url('portfolio?page=' . ($page-1) . ($categoryId ? '&cat='.$categoryId : '')) ?>"
         class="page-btn" aria-label="<?= e(Lang::t('common.prev')) ?>">←</a>
      <?php endif; ?>

      <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
      <a href="<?= base_url('portfolio?page=' . $i . ($categoryId ? '&cat='.$categoryId : '')) ?>"
         class="page-btn <?= $i === $page ? 'active' : '' ?>"
         <?= $i === $page ? 'aria-current="page"' : '' ?>>
        <?= $i ?>
      </a>
      <?php endfor; ?>

      <?php if ($page < $pages): ?>
      <a href="<?= base_url('portfolio?page=' . ($page+1) . ($categoryId ? '&cat='.$categoryId : '')) ?>"
         class="page-btn" aria-label="<?= e(Lang::t('common.next')) ?>">→</a>
      <?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <div class="text-center" style="padding: 4rem 0; color: var(--text-muted);">
      <p style="font-size:1.1rem;"><?= e(Lang::t('portfolio.empty')) ?></p>
    </div>
    <?php endif; ?>

  </div>
</section>