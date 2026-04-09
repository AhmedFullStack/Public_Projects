<?php
use App\Core\Lang;
use App\Helpers\SeoHelper;
?>

<!-- ═══ HERO ══════════════════════════════════════════════ -->
<section class="hero" aria-labelledby="hero-title">
  <div class="hero-bg" aria-hidden="true"></div>
  <div class="hero-grid-pattern" aria-hidden="true"></div>

  <div class="container">
    <div class="hero-content">

      <div class="hero-badge">
        <span class="hero-badge-dot" aria-hidden="true"></span>
        <?= e(Lang::t('hero.badge')) ?>
      </div>

      <h1 class="hero-title" id="hero-title">
        <span class="highlight"><?= e(Lang::t('hero.title')) ?></span>
        <br>
        <span><?= e(Lang::t('hero.subtitle')) ?></span>
      </h1>

      <p class="hero-description">
        <?= e(Lang::t('hero.description')) ?>
      </p>

      <div class="hero-actions">
        <a href="<?= base_url('portfolio') ?>" class="btn btn-primary btn-lg">
          <?= e(Lang::t('hero.cta_portfolio')) ?>
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <?php if (Lang::isRtl()): ?>
            <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <?php else: ?>
            <path d="M6 12l4-4-4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <?php endif; ?>
          </svg>
        </a>

        <?php if ($cv): ?>
        <a href="<?= base_url('cv/download') ?>" class="btn btn-outline btn-lg">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M8 2v8M5 7l3 3 3-3M3 13h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <?= e(Lang::t('hero.cta_cv')) ?>
        </a>
        <?php endif; ?>
      </div>

      <div class="hero-stats" role="list" aria-label="<?= Lang::choose('إحصائيات', 'Statistics') ?>">
        <div class="stat-item" role="listitem">
          <div class="stat-number"><?= e($settings['projects_count'] ?? '30') ?>+</div>
          <div class="stat-label"><?= e(Lang::t('stats.projects')) ?></div>
        </div>
        <div class="stat-divider" aria-hidden="true"></div>
        <div class="stat-item" role="listitem">
          <div class="stat-number"><?= e($settings['years_experience'] ?? '8') ?></div>
          <div class="stat-label"><?= e(Lang::t('stats.experience')) ?></div>
        </div>
        <div class="stat-divider" aria-hidden="true"></div>
        <div class="stat-item" role="listitem">
          <div class="stat-number"><?= e($settings['clients_count'] ?? '15') ?>+</div>
          <div class="stat-label"><?= e(Lang::t('stats.clients')) ?></div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══ SKILLS ════════════════════════════════════════════ -->
<?php if (!empty($skills)): ?>
<section class="section skills-section" id="skills" aria-labelledby="skills-title">
  <div class="container">
    <div class="section-header">
      <span class="section-tag"><?= e(Lang::t('skills.title')) ?></span>
      <h2 class="section-title" id="skills-title"><?= e(Lang::t('skills.subtitle')) ?></h2>
    </div>

    <div class="skill-groups">
      <?php foreach ($skills as $group): ?>
      <?php if (empty($group['skills'])) continue; ?>
      <div class="skill-group">
        <h3 class="skill-group-title"><?= e($group['name'] ?? $group['slug']) ?></h3>
        <div class="skills-list grid-3">
          <?php foreach ($group['skills'] as $skill): ?>
          <div class="skill-item">
            <div class="skill-header">
              <span class="skill-name"><?= e($skill['name'] ?? $skill['slug']) ?></span>
              <span class="skill-pct"><?= (int)$skill['proficiency'] ?>%</span>
            </div>
            <div class="skill-bar-track" role="progressbar"
                 aria-valuenow="<?= (int)$skill['proficiency'] ?>"
                 aria-valuemin="0" aria-valuemax="100"
                 aria-label="<?= e($skill['name'] ?? '') ?>">
              <div class="skill-bar-fill"
                   data-width="<?= (int)$skill['proficiency'] ?>">
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══ FEATURED PROJECTS ════════════════════════════════ -->
<?php if (!empty($projects)): ?>
<section class="section" id="featured-projects" aria-labelledby="fp-title">
  <div class="container">
    <div class="section-header">
      <span class="section-tag"><?= e(Lang::t('portfolio.featured')) ?></span>
      <h2 class="section-title" id="fp-title"><?= e(Lang::t('portfolio.title')) ?></h2>
      <p class="section-subtitle"><?= e(Lang::t('portfolio.subtitle')) ?></p>
    </div>

    <div class="projects-grid">
      <?php foreach ($projects as $project): ?>
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
            <a href="<?= base_url('portfolio/' . e($project['slug'])) ?>"
               class="btn btn-primary btn-sm">
              <?= e(Lang::t('portfolio.view')) ?>
            </a>
          </div>
        </div>

        <div class="project-body">
          <?php if (!empty($project['category_name'])): ?>
          <span class="project-badge"><?= e($project['category_name']) ?></span>
          <?php endif; ?>

          <h3 class="project-title" id="proj-<?= (int)$project['id'] ?>">
            <a href="<?= base_url('portfolio/' . e($project['slug'])) ?>" style="color:inherit;">
              <?= e($project['title'] ?? '') ?>
            </a>
          </h3>

          <p class="project-summary"><?= e(mb_substr($project['summary'] ?? '', 0, 120)) ?><?= strlen($project['summary'] ?? '') > 120 ? '…' : '' ?></p>

          <div class="project-meta">
            <?php if (!empty($project['year'])): ?>
            <span><?= e($project['year']) ?></span>
            <?php endif; ?>
            <a href="<?= base_url('portfolio/' . e($project['slug'])) ?>" class="text-accent" style="font-size:.85rem; font-weight:500;">
              <?= e(Lang::t('common.read_more')) ?> →
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-lg">
      <a href="<?= base_url('portfolio') ?>" class="btn btn-outline">
        <?= e(Lang::t('portfolio.all')) ?>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══ CTA / CONTACT BANNER ═════════════════════════════ -->
<section class="section-sm" style="background: var(--bg-secondary); border-top: 1px solid var(--border);">
  <div class="container text-center">
    <h2 class="section-title" style="margin-bottom:.5rem;">
      <?= e(Lang::t('contact.subtitle')) ?>
    </h2>
    <p class="section-subtitle" style="margin-bottom: var(--space-xl);">
      <?= Lang::choose(
        'هل لديك مشروع أو تحتاج استشارة؟ أنا هنا للمساعدة.',
        'Have a project in mind? Let\'s talk and make it happen.'
      ) ?>
    </p>
    <a href="<?= base_url('contact') ?>" class="btn btn-primary btn-lg">
      <?= e(Lang::t('nav.contact')) ?>
    </a>
  </div>
</section>