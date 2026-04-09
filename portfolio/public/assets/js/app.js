/* ============================================================
   ElectroMech Portfolio — Main JavaScript
   Vanilla JS, no external dependencies
   ============================================================ */
   'use strict';

   /* ── DOM Ready ───────────────────────────────────────────── */
   document.addEventListener('DOMContentLoaded', () => {
     initNav();
     initSkillBars();
     initContactForm();
     initBackToTop();
     initFlashMessages();
     initProjectFilter();
     initLazyImages();
     initCopyLink();
   });
   
   /* ── Navigation ──────────────────────────────────────────── */
   function initNav() {
     const header = document.getElementById('site-header');
     const toggle = document.getElementById('menuToggle');
     const menu   = document.getElementById('mobileMenu');
   
     // Scroll → add class
     const onScroll = () => {
       if (header) header.classList.toggle('scrolled', window.scrollY > 20);
     };
     window.addEventListener('scroll', onScroll, { passive: true });
     onScroll();
   
     // Mobile menu toggle
     toggle?.addEventListener('click', () => {
       const isOpen = menu.classList.toggle('open');
       toggle.classList.toggle('open', isOpen);
       toggle.setAttribute('aria-expanded', isOpen);
       menu.setAttribute('aria-hidden', !isOpen);
       document.body.style.overflow = isOpen ? 'hidden' : '';
     });
   
     // Close on outside click
     document.addEventListener('click', (e) => {
       if (menu?.classList.contains('open') && !menu.contains(e.target) && e.target !== toggle) {
         menu.classList.remove('open');
         toggle?.classList.remove('open');
         toggle?.setAttribute('aria-expanded', 'false');
         menu.setAttribute('aria-hidden', 'true');
         document.body.style.overflow = '';
       }
     });
   
     // Close on nav link click
     menu?.querySelectorAll('.mobile-nav-link').forEach(link => {
       link.addEventListener('click', () => {
         menu.classList.remove('open');
         toggle?.classList.remove('open');
         document.body.style.overflow = '';
       });
     });
   }
   
   /* ── Skill Bars Intersection Animation ───────────────────── */
   function initSkillBars() {
     const bars = document.querySelectorAll('.skill-bar-fill');
     if (!bars.length) return;
   
     const observer = new IntersectionObserver((entries) => {
       entries.forEach(entry => {
         if (entry.isIntersecting) {
           const bar   = entry.target;
           const width = bar.dataset.width || '0';
           bar.style.setProperty('--target-width', width + '%');
           // Small delay for visual effect
           setTimeout(() => bar.classList.add('animated'), 100);
           observer.unobserve(bar);
         }
       });
     }, { threshold: 0.3 });
   
     bars.forEach(bar => observer.observe(bar));
   }
   
   /* ── Contact Form (AJAX) ─────────────────────────────────── */
   function initContactForm() {
     const form = document.getElementById('contactForm');
     if (!form) return;
   
     form.addEventListener('submit', async (e) => {
       e.preventDefault();
   
       const btn     = form.querySelector('[type="submit"]');
       const spinner = form.querySelector('.btn-spinner');
       const result  = document.getElementById('formResult');
   
       // Clear previous state
       clearFormErrors(form);
       if (result) result.textContent = '';
   
       // Client-side validation
       const errors = validateContactForm(form);
       if (errors.length) {
         errors.forEach(({ field, msg }) => showFieldError(form, field, msg));
         return;
       }
   
       // Loading state
       btn.disabled = true;
       btn.setAttribute('aria-busy', 'true');
       if (spinner) spinner.hidden = false;
   
       try {
         const resp = await fetch(form.action, {
           method: 'POST',
           body: new FormData(form),
           headers: { 'X-Requested-With': 'XMLHttpRequest' },
         });
   
         const data = await resp.json();
   
         if (data.success) {
           form.reset();
           showToast(data.message, 'success');
           if (result) {
             result.className = 'form-result form-result--success';
             result.textContent = data.message;
           }
         } else {
           showToast(data.message || 'Error', 'error');
           if (result) {
             result.className = 'form-result form-result--error';
             result.textContent = data.message;
           }
         }
       } catch {
         showToast('Connection error. Please try again.', 'error');
       } finally {
         btn.disabled = false;
         btn.removeAttribute('aria-busy');
         if (spinner) spinner.hidden = true;
       }
     });
   }
   
   function validateContactForm(form) {
     const errors = [];
     const locale = document.body.dataset.locale || 'ar';
     const msgs   = locale === 'ar'
       ? { required: 'هذا الحقل مطلوب', email: 'البريد الإلكتروني غير صحيح', min10: 'الرسالة قصيرة جداً (10 أحرف على الأقل)' }
       : { required: 'This field is required', email: 'Invalid email address', min10: 'Message too short (min 10 characters)' };
   
     const name    = form.querySelector('[name="name"]');
     const email   = form.querySelector('[name="email"]');
     const subject = form.querySelector('[name="subject"]');
     const message = form.querySelector('[name="message"]');
   
     if (!name?.value.trim())                           errors.push({ field: 'name',    msg: msgs.required });
     if (!email?.value.trim())                          errors.push({ field: 'email',   msg: msgs.required });
     else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) errors.push({ field: 'email', msg: msgs.email });
     if (!subject?.value.trim())                        errors.push({ field: 'subject', msg: msgs.required });
     if (!message?.value.trim())                        errors.push({ field: 'message', msg: msgs.required });
     else if (message.value.trim().length < 10)         errors.push({ field: 'message', msg: msgs.min10 });
   
     return errors;
   }
   
   function showFieldError(form, fieldName, msg) {
     const field = form.querySelector(`[name="${fieldName}"]`);
     if (!field) return;
     field.classList.add('form-control--error');
     const err = document.createElement('span');
     err.className = 'form-error-text';
     err.textContent = msg;
     field.parentNode.appendChild(err);
   }
   
   function clearFormErrors(form) {
     form.querySelectorAll('.form-control--error').forEach(el => el.classList.remove('form-control--error'));
     form.querySelectorAll('.form-error-text').forEach(el => el.remove());
   }
   
   /* ── Toast notifications ─────────────────────────────────── */
   function showToast(message, type = 'success', duration = 4000) {
     let container = document.querySelector('.flash-container');
     if (!container) {
       container = document.createElement('div');
       container.className = 'flash-container';
       document.body.appendChild(container);
     }
   
     const toast = document.createElement('div');
     toast.className = `flash flash--${type}`;
     toast.innerHTML = `<span>${escapeHtml(message)}</span><button class="flash-close" aria-label="Close">×</button>`;
   
     toast.querySelector('.flash-close').addEventListener('click', () => removeToast(toast));
     container.appendChild(toast);
   
     setTimeout(() => removeToast(toast), duration);
   }
   
   function removeToast(el) {
     el.style.opacity = '0';
     el.style.transform = 'translateX(20px)';
     el.style.transition = 'all .25s ease';
     setTimeout(() => el.remove(), 250);
   }
   
   /* ── Back to Top ─────────────────────────────────────────── */
   function initBackToTop() {
     const btn = document.getElementById('backToTop');
     if (!btn) return;
   
     window.addEventListener('scroll', () => {
       btn.classList.toggle('visible', window.scrollY > 400);
     }, { passive: true });
   
     btn.addEventListener('click', () => {
       window.scrollTo({ top: 0, behavior: 'smooth' });
     });
   }
   
   /* ── Flash message close ─────────────────────────────────── */
   function initFlashMessages() {
     document.addEventListener('click', (e) => {
       if (e.target.classList.contains('flash-close')) {
         removeToast(e.target.closest('.flash'));
       }
     });
   
     // Auto-dismiss after 5s
     document.querySelectorAll('.flash').forEach(f => {
       setTimeout(() => removeToast(f), 5000);
     });
   }
   
   /* ── Project filter (front-end AJAX load) ────────────────── */
   function initProjectFilter() {
     const tabs = document.querySelectorAll('.filter-tab[data-cat]');
     if (!tabs.length) return;
   
     tabs.forEach(tab => {
       tab.addEventListener('click', () => {
         tabs.forEach(t => { t.classList.remove('active'); t.setAttribute('aria-selected', 'false'); });
         tab.classList.add('active');
         tab.setAttribute('aria-selected', 'true');
   
         const catId = tab.dataset.cat;
         const url   = new URL(window.location.href);
         if (catId) url.searchParams.set('cat', catId);
         else       url.searchParams.delete('cat');
         url.searchParams.delete('page');
         window.history.pushState({}, '', url.toString());
         window.location.href = url.toString();
       });
     });
   }
   
   /* ── Lazy Images ─────────────────────────────────────────── */
   function initLazyImages() {
     if (!('IntersectionObserver' in window)) return;
   
     const imgs = document.querySelectorAll('img[data-src]');
     const observer = new IntersectionObserver((entries) => {
       entries.forEach(entry => {
         if (entry.isIntersecting) {
           const img = entry.target;
           img.src = img.dataset.src;
           if (img.dataset.srcset) img.srcset = img.dataset.srcset;
           img.removeAttribute('data-src');
           observer.unobserve(img);
         }
       });
     }, { rootMargin: '200px' });
   
     imgs.forEach(img => observer.observe(img));
   }
   
   /* ── Copy link ───────────────────────────────────────────── */
   function initCopyLink() {
     document.querySelectorAll('[data-copy-link]').forEach(btn => {
       btn.addEventListener('click', async () => {
         const url = btn.dataset.copyLink || window.location.href;
         try {
           await navigator.clipboard.writeText(url);
           const orig = btn.textContent;
           btn.textContent = btn.dataset.copied || '✓ Copied';
           setTimeout(() => btn.textContent = orig, 2000);
         } catch { /* clipboard not available */ }
       });
     });
   }
   
   /* ── Utilities ───────────────────────────────────────────── */
   function escapeHtml(str) {
     return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
   }