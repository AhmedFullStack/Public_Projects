<?php use App\Core\Lang; use App\Core\Security; ?>

<div class="project-detail-header" style="padding: calc(var(--header-h) + 2rem) 0 2rem;">
  <div class="container">
    <span class="section-tag"><?= e(Lang::t('nav.contact')) ?></span>
    <h1 class="section-title" style="text-align:<?= Lang::isRtl() ? 'right' : 'left' ?>; margin-top:.5rem;">
      <?= e(Lang::t('contact.subtitle')) ?>
    </h1>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="contact-layout">

      <!-- ── Contact Info ────────────────────────────────── -->
      <aside>
        <div class="contact-info-card">
          <?php if (!empty($settings['owner_email'])): ?>
          <div class="contact-info-item">
            <div class="contact-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </div>
            <div>
              <div class="contact-info-label"><?= e(Lang::t('contact.email_label')) ?></div>
              <a href="mailto:<?= e($settings['owner_email']) ?>" class="contact-info-value">
                <?= e($settings['owner_email']) ?>
              </a>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($settings['owner_phone'])): ?>
          <div class="contact-info-item">
            <div class="contact-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
            </div>
            <div>
              <div class="contact-info-label"><?= e(Lang::t('contact.phone_label')) ?></div>
              <a href="tel:<?= e($settings['owner_phone']) ?>" class="contact-info-value">
                <?= e($settings['owner_phone']) ?>
              </a>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($settings['linkedin_url'])): ?>
          <div class="contact-info-item">
            <div class="contact-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
            </div>
            <div>
              <div class="contact-info-label">LinkedIn</div>
              <a href="<?= e($settings['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" class="contact-info-value">
                <?= e(Lang::t('contact.linkedin')) ?>
              </a>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($settings['whatsapp_number'])): ?>
          <div class="contact-info-item">
            <div class="contact-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            </div>
            <div>
              <div class="contact-info-label">WhatsApp</div>
              <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $settings['whatsapp_number'])) ?>"
                 target="_blank" rel="noopener noreferrer" class="contact-info-value">
                <?= e(Lang::t('contact.whatsapp')) ?>
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </aside>

      <!-- ── Contact Form ────────────────────────────────── -->
      <div>
        <form id="contactForm"
              action="<?= base_url('contact/send') ?>"
              method="POST"
              novalidate
              aria-label="<?= e(Lang::t('contact.title')) ?>">

          <?= Security::csrfField() ?>
          <input type="hidden" name="_locale" value="<?= e(Lang::get()) ?>">

          <!-- Honeypot (anti-spam) -->
          <div style="display:none;" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="inp-name" class="form-label">
                <?= e(Lang::t('contact.name')) ?> <span aria-hidden="true" style="color:var(--danger)">*</span>
              </label>
              <input type="text" id="inp-name" name="name"
                     class="form-control"
                     autocomplete="name"
                     maxlength="100"
                     required
                     aria-required="true">
            </div>

            <div class="form-group">
              <label for="inp-email" class="form-label">
                <?= e(Lang::t('contact.email')) ?> <span aria-hidden="true" style="color:var(--danger)">*</span>
              </label>
              <input type="email" id="inp-email" name="email"
                     class="form-control"
                     autocomplete="email"
                     maxlength="191"
                     required
                     aria-required="true">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="inp-phone" class="form-label"><?= e(Lang::t('contact.phone')) ?></label>
              <input type="tel" id="inp-phone" name="phone"
                     class="form-control"
                     autocomplete="tel"
                     maxlength="30">
            </div>

            <div class="form-group">
              <label for="inp-subject" class="form-label">
                <?= e(Lang::t('contact.subject')) ?> <span aria-hidden="true" style="color:var(--danger)">*</span>
              </label>
              <input type="text" id="inp-subject" name="subject"
                     class="form-control"
                     maxlength="255"
                     required
                     aria-required="true">
            </div>
          </div>

          <div class="form-group">
            <label for="inp-message" class="form-label">
              <?= e(Lang::t('contact.message')) ?> <span aria-hidden="true" style="color:var(--danger)">*</span>
            </label>
            <textarea id="inp-message" name="message"
                      class="form-control"
                      rows="6"
                      maxlength="5000"
                      required
                      aria-required="true"></textarea>
          </div>

          <div id="formResult" role="status" aria-live="polite" style="margin-bottom:1rem;font-size:.9rem;"></div>

          <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center;">
            <span class="btn-spinner" hidden aria-hidden="true">⏳</span>
            <?= e(Lang::t('contact.send')) ?>
          </button>
        </form>
      </div>

    </div>
  </div>
</section>

<style>
.form-control--error  { border-color: var(--danger) !important; }
.form-error-text      { display:block; font-size:.8rem; color:var(--danger); margin-top:4px; }
.form-result--success { color: var(--success); font-weight:500; }
.form-result--error   { color: var(--danger); font-weight:500; }
</style>