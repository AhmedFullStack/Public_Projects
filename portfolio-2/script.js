/**
 * ============================================================
 *  Ahmed AlaaEldin | Portfolio — Advanced Animations Engine
 *  GSAP 3.9.1 Core + Custom Effects
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {

    /* ══════════════════════════════════════════════════════════
       0. GSAP GLOBAL DEFAULTS & CUSTOM EASES
       ══════════════════════════════════════════════════════════ */
    gsap.defaults({ ease: "power3.out" });
    gsap.config({ nullTargetWarn: false });
  
    // Register a custom bounce ease
    CustomEase && CustomEase.create
      ? CustomEase.create("elasticPop", "M0,0 C0.14,0 0.242,0.438 0.272,0.561 0.313,0.728 0.354,0.963 0.362,1 0.37,0.985 0.414,0.985 0.455,1")
      : null;
  
  
    /* ══════════════════════════════════════════════════════════
       1. CUSTOM CURSOR
       ══════════════════════════════════════════════════════════ */
    const cursorDot    = document.createElement("div");
    const cursorRing   = document.createElement("div");
    cursorDot.className  = "cursor-dot";
    cursorRing.className = "cursor-ring";
    document.body.appendChild(cursorDot);
    document.body.appendChild(cursorRing);
  
    // Inject cursor styles
    const cursorStyles = document.createElement("style");
    cursorStyles.textContent = `
      body { cursor: none; }
      .cursor-dot {
        position: fixed; top: 0; left: 0; width: 8px; height: 8px;
        background: var(--accent-color, #7c3aed); border-radius: 50%;
        pointer-events: none; z-index: 99999; transform: translate(-50%,-50%);
        mix-blend-mode: exclusion; transition: transform 0.1s;
      }
      .cursor-ring {
        position: fixed; top: 0; left: 0; width: 40px; height: 40px;
        border: 2px solid var(--accent-color, #7c3aed); border-radius: 50%;
        pointer-events: none; z-index: 99998; transform: translate(-50%,-50%);
        opacity: 0.5; transition: width 0.3s, height 0.3s, opacity 0.3s;
        mix-blend-mode: exclusion;
      }
      .cursor-ring.hovered { width: 60px; height: 60px; opacity: 0.8; }
      a, button, .btn, .portfolio-card, .service-card, .skill-tag {
        cursor: none;
      }
    `;
    document.head.appendChild(cursorStyles);
  
    let mouseX = 0, mouseY = 0;
    let ringX  = 0, ringY  = 0;
  
    document.addEventListener("mousemove", e => {
      mouseX = e.clientX;
      mouseY = e.clientY;
      gsap.to(cursorDot, { x: mouseX, y: mouseY, duration: 0.1 });
    });
  
    // Smooth lag for ring
    (function ringLoop() {
      ringX += (mouseX - ringX) * 0.12;
      ringY += (mouseY - ringY) * 0.12;
      gsap.set(cursorRing, { x: ringX, y: ringY });
      requestAnimationFrame(ringLoop);
    })();
  
    // Cursor grow on hover
    document.querySelectorAll("a, button, .btn, .portfolio-card, .service-card, .skill-tag, .service-icon")
      .forEach(el => {
        el.addEventListener("mouseenter", () => cursorRing.classList.add("hovered"));
        el.addEventListener("mouseleave", () => cursorRing.classList.remove("hovered"));
      });
  
  
    /* ══════════════════════════════════════════════════════════
       2. TEXT SPLITTING — Word-by-word entrance for H1
       ══════════════════════════════════════════════════════════ */
    function splitTextToSpans(el) {
      if (!el) return;
      const words = el.textContent.trim().split(/\s+/);
      el.innerHTML = words.map(w => `<span class="word" style="display:inline-block; overflow:hidden; padding-bottom:0.05em;"><span class="word-inner" style="display:inline-block;">${w}</span></span>`).join(" ");
      return el.querySelectorAll(".word-inner");
    }
  
    const h1 = document.querySelector(".hero-text h1");
    const wordSpans = splitTextToSpans(h1);
  
    if (wordSpans) {
      gsap.from(wordSpans, {
        y: "110%",
        opacity: 0,
        duration: 1,
        stagger: 0.06,
        ease: "power4.out",
        delay: 0.2
      });
    }
  
  
    /* ══════════════════════════════════════════════════════════
       3. HERO SECTION — Orchestrated Timeline
       ══════════════════════════════════════════════════════════ */
    const heroTl = gsap.timeline({ defaults: { ease: "power3.out" } });
  
    heroTl
      .from(".hero-text p",          { opacity: 0, y: 30, duration: 0.9 }, 0.8)
      .from(".hero-buttons .btn",    { opacity: 0, y: 25, stagger: 0.15, duration: 0.8 }, 1.0)
      .from(".hero-image img",       {
        opacity: 0, scale: 0.85, rotateY: -15,
        duration: 1.3, ease: "back.out(1.7)"
      }, 0.4);
  
    // Floating animation on hero image (infinite)
    gsap.to(".hero-image img", {
      y: -15,
      duration: 3,
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut",
      delay: 1.5
    });
  
  
    /* ══════════════════════════════════════════════════════════
       4. PARALLAX — Hero elements on mouse move
       ══════════════════════════════════════════════════════════ */
    const heroSection = document.querySelector(".hero");
    if (heroSection) {
      heroSection.addEventListener("mousemove", e => {
        const rect = heroSection.getBoundingClientRect();
        const cx = (e.clientX - rect.left) / rect.width  - 0.5;  // -0.5 to 0.5
        const cy = (e.clientY - rect.top)  / rect.height - 0.5;
  
        gsap.to(".hero-image img", {
          rotateY: cx * 12,
          rotateX: -cy * 8,
          duration: 0.8,
          ease: "power2.out",
          transformPerspective: 800
        });
  
        gsap.to(".hero-text h1", {
          x: cx * 10,
          y: cy * 5,
          duration: 1,
          ease: "power2.out"
        });
      });
  
      heroSection.addEventListener("mouseleave", () => {
        gsap.to(".hero-image img", { rotateY: 0, rotateX: 0, duration: 1 });
        gsap.to(".hero-text h1",   { x: 0, y: 0, duration: 1 });
      });
    }
  
  
    /* ══════════════════════════════════════════════════════════
       5. MAGNETIC BUTTONS
       ══════════════════════════════════════════════════════════ */
    function makeMagnetic(selector, strength = 0.35) {
      document.querySelectorAll(selector).forEach(btn => {
        btn.addEventListener("mousemove", e => {
          const rect = btn.getBoundingClientRect();
          const dx = e.clientX - (rect.left + rect.width  / 2);
          const dy = e.clientY - (rect.top  + rect.height / 2);
          gsap.to(btn, { x: dx * strength, y: dy * strength, duration: 0.4, ease: "power2.out" });
        });
        btn.addEventListener("mouseleave", () => {
          gsap.to(btn, { x: 0, y: 0, duration: 0.7, ease: "elastic.out(1, 0.4)" });
        });
      });
    }
  
    makeMagnetic(".btn-primary", 0.4);
    makeMagnetic(".btn-secondary", 0.3);
  
  
    /* ══════════════════════════════════════════════════════════
       6. TILT EFFECT — Portfolio & Service Cards
       ══════════════════════════════════════════════════════════ */
    function addTilt(selector, maxTilt = 10) {
      document.querySelectorAll(selector).forEach(card => {
        card.style.transformStyle = "preserve-3d";
        card.style.transition = "box-shadow 0.3s";
  
        card.addEventListener("mousemove", e => {
          const rect = card.getBoundingClientRect();
          const cx = (e.clientX - rect.left) / rect.width  - 0.5;
          const cy = (e.clientY - rect.top)  / rect.height - 0.5;
  
          gsap.to(card, {
            rotateX: -cy * maxTilt,
            rotateY:  cx * maxTilt,
            transformPerspective: 600,
            duration: 0.4,
            ease: "power2.out",
            boxShadow: `${-cx*15}px ${-cy*15}px 40px rgba(0,0,0,0.15)`
          });
        });
  
        card.addEventListener("mouseleave", () => {
          gsap.to(card, {
            rotateX: 0, rotateY: 0,
            duration: 0.8,
            ease: "elastic.out(1, 0.5)",
            boxShadow: "0 4px 20px rgba(0,0,0,0.08)"
          });
        });
      });
    }
  
    addTilt(".portfolio-card", 8);
    addTilt(".service-card",   12);
  
  
    /* ══════════════════════════════════════════════════════════
       7. SKILL TAGS — Pop-in ripple with random delay
       ══════════════════════════════════════════════════════════ */
    function animateSkillTags(container) {
      const tags = container.querySelectorAll(".skill-tag");
      gsap.from(tags, {
        opacity: 0,
        scale: 0,
        duration: 0.5,
        stagger: { amount: 0.8, from: "random" },
        ease: "back.out(3)"
      });
    }
  
    // Hover shimmer on skill tags
    document.querySelectorAll(".skill-tag").forEach(tag => {
      tag.addEventListener("mouseenter", () => {
        gsap.to(tag, { scale: 1.15, duration: 0.2, ease: "power2.out" });
      });
      tag.addEventListener("mouseleave", () => {
        gsap.to(tag, { scale: 1, duration: 0.4, ease: "elastic.out(1.2, 0.5)" });
      });
    });
  
  
    /* ══════════════════════════════════════════════════════════
       8. ANIMATED COUNTER — Numbers count up when visible
       ══════════════════════════════════════════════════════════ */
    function animateCounter(el) {
      const target = parseFloat(el.dataset.target || el.textContent);
      const isPercent = el.textContent.includes("%");
      gsap.fromTo(el,
        { textContent: 0 },
        {
          textContent: target,
          duration: 2,
          ease: "power2.out",
          snap: { textContent: 1 },
          onUpdate() {
            el.textContent = Math.round(parseFloat(el.textContent)) + (isPercent ? "%" : "");
          }
        }
      );
    }
  
    // Attach data-target and mark skill percent spans for counter animation
    document.querySelectorAll(".skill-info span:last-child").forEach(span => {
      span.dataset.target = parseFloat(span.textContent);
      span.dataset.counter = "true";
    });
  
  
    /* ══════════════════════════════════════════════════════════
       9. PROGRESS BARS — Animated fill with shimmer overlay
       ══════════════════════════════════════════════════════════ */
    // Add shimmer pseudo-element style
    const shimmerStyle = document.createElement("style");
    shimmerStyle.textContent = `
      .progress { position: relative; overflow: hidden; }
      .progress::after {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: none;
      }
      .progress.animated::after {
        animation: shimmer 1.5s ease-in-out;
      }
      @keyframes shimmer {
        from { left: -100%; }
        to   { left: 150%; }
      }
    `;
    document.head.appendChild(shimmerStyle);
  
    function animateProgressBars(section) {
      section.querySelectorAll(".progress").forEach((bar, i) => {
        const target = bar.style.width;
        gsap.fromTo(bar,
          { width: "0%", opacity: 0.6 },
          {
            width: target,
            opacity: 1,
            duration: 1.6,
            delay: i * 0.12,
            ease: "power3.out",
            onComplete() {
              bar.classList.add("animated");
              // counter animation for the sibling span
              const infoSpan = bar.closest(".skill-item")?.querySelector(".skill-info span:last-child");
              if (infoSpan && infoSpan.dataset.counter) animateCounter(infoSpan);
            }
          }
        );
      });
    }
  
  
    /* ══════════════════════════════════════════════════════════
       10. INTERSECTION OBSERVER — Section entrance animations
       ══════════════════════════════════════════════════════════ */
    const observerOptions = { threshold: 0.12, rootMargin: "0px 0px -60px 0px" };
  
    const sectionObserver = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const sec = entry.target;
  
        // ---- Section header ----
        const header = sec.querySelector(".section-header");
        if (header) {
          gsap.from(header.querySelector("h2"), { opacity: 0, y: 40, duration: 0.9, delay: 0.1 });
          gsap.from(header.querySelector("p"),  { opacity: 0, y: 25, duration: 0.8, delay: 0.3 });
        }
  
        // ---- About ----
        if (sec.classList.contains("about")) {
          gsap.from(".about-text-box", { opacity: 0, x: -50, duration: 1, delay: 0.2 });
          gsap.from(".skills-box",     { opacity: 0, x:  50, duration: 1, delay: 0.3 });
  
          gsap.from(".credentials-list li", {
            opacity: 0, x: -40, stagger: 0.18, duration: 0.8, delay: 0.4
          });
  
          animateSkillTags(sec);
        }
  
        // ---- Skills (progress bars) ----
        if (sec.classList.contains("skills")) {
          gsap.from(".skills-column", {
            opacity: 0, y: 50, stagger: 0.25, duration: 0.9, delay: 0.15
          });
          animateProgressBars(sec);
        }
  
        // ---- Portfolio cards ----
        if (sec.classList.contains("portfolio")) {
          gsap.from(".portfolio-card", {
            opacity: 0,
            y: 70,
            rotateX: 10,
            stagger: 0.2,
            duration: 0.9,
            delay: 0.2,
            ease: "power3.out",
            transformPerspective: 800
          });
        }
  
        // ---- Services cards ----
        if (sec.classList.contains("services")) {
          gsap.from(".service-card", {
            opacity: 0, y: 60, scale: 0.92, stagger: 0.18, duration: 0.85, delay: 0.2
          });
          gsap.from(".service-icon", {
            opacity: 0, scale: 0, rotate: -30, stagger: 0.18, duration: 0.6, delay: 0.5, ease: "back.out(2)"
          });
        }
  
        // ---- Contact ----
        if (sec.classList.contains("contact") || sec.id === "contact") {
          gsap.from(".contact-info",          { opacity: 0, x: -50, duration: 1, delay: 0.2 });
          gsap.from(".contact-form-container", { opacity: 0, x:  50, duration: 1, delay: 0.35 });
          gsap.from(".form-group",            { opacity: 0, y: 25, stagger: 0.12, duration: 0.7, delay: 0.5 });
          gsap.from(".freelance-links .btn",  { opacity: 0, scale: 0.7, stagger: 0.1, duration: 0.6, delay: 0.7, ease: "back.out(2)" });
        }
  
        obs.unobserve(sec);
      });
    }, observerOptions);
  
    document.querySelectorAll("section, .contact").forEach(sec => sectionObserver.observe(sec));
  
  
    /* ══════════════════════════════════════════════════════════
       11. STICKY NAVBAR — Shrink + frosted glass on scroll
       ══════════════════════════════════════════════════════════ */
    const navbar = document.querySelector(".navbar");
  
    // Inject navbar scroll styles
    const navStyle = document.createElement("style");
    navStyle.textContent = `
      .navbar {
        transition: height 0.4s cubic-bezier(0.4,0,0.2,1),
                    background 0.4s ease,
                    backdrop-filter 0.4s ease,
                    box-shadow 0.4s ease !important;
        will-change: height, background;
      }
      .navbar.scrolled {
        background: rgba(255,255,255,0.85) !important;
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
        box-shadow: 0 4px 24px rgba(0,0,0,0.10) !important;
      }
    `;
    document.head.appendChild(navStyle);
  
    let lastScroll = 0;
    let navHideTimeout;
  
    window.addEventListener("scroll", () => {
      const scrollY = window.scrollY;
  
      // Scrolled class for frost
      scrollY > 50
        ? navbar.classList.add("scrolled")
        : navbar.classList.remove("scrolled");
  
      // Hide/show navbar on scroll direction
      if (scrollY > 300) {
        if (scrollY > lastScroll + 10) {
          // Scrolling down — hide
          gsap.to(navbar, { y: -100, duration: 0.4, ease: "power2.in" });
        } else if (scrollY < lastScroll - 5) {
          // Scrolling up — show
          gsap.to(navbar, { y: 0, duration: 0.5, ease: "power2.out" });
        }
      } else {
        gsap.to(navbar, { y: 0, duration: 0.4 });
      }
  
      lastScroll = scrollY;
    }, { passive: true });
  
  
    /* ══════════════════════════════════════════════════════════
       12. SMOOTH SCROLL with GSAP
       ══════════════════════════════════════════════════════════ */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener("click", function(e) {
        const href = this.getAttribute("href");
        if (href === "#") return;
        const target = document.querySelector(href);
        if (!target) return;
        e.preventDefault();
  
        const navHeight = navbar ? navbar.offsetHeight : 80;
        const targetY   = target.getBoundingClientRect().top + window.scrollY - navHeight;
  
        gsap.to(window, {
          scrollTo: targetY,
          duration: 1.2,
          ease: "power3.inOut",
          onStart() {
            // ScrollTo plugin fallback
            if (!gsap.plugins?.scrollTo) {
              window.scrollTo({ top: targetY, behavior: "smooth" });
            }
          }
        });
  
        // Fallback if ScrollTo plugin missing
        window.scrollTo({ top: targetY, behavior: "smooth" });
      });
    });
  
  
    /* ══════════════════════════════════════════════════════════
       13. CREDENTIAL LIST ITEMS — Hover lift
       ══════════════════════════════════════════════════════════ */
    document.querySelectorAll(".credentials-list li").forEach(li => {
      li.addEventListener("mouseenter", () => {
        gsap.to(li, { x: 8, duration: 0.25, ease: "power2.out" });
      });
      li.addEventListener("mouseleave", () => {
        gsap.to(li, { x: 0, duration: 0.4, ease: "elastic.out(1, 0.6)" });
      });
    });
  
  
    /* ══════════════════════════════════════════════════════════
       14. CONTACT FORM — Submit animation + micro-interaction
       ══════════════════════════════════════════════════════════ */
    const form   = document.querySelector(".contact-form");
    const submit = form?.querySelector("button[type=submit]");
  
    if (form && submit) {
      // Input focus animations
      form.querySelectorAll("input, textarea, select").forEach(input => {
        input.addEventListener("focus", () => {
          gsap.to(input, { scale: 1.015, duration: 0.2, ease: "power2.out" });
        });
        input.addEventListener("blur", () => {
          gsap.to(input, { scale: 1, duration: 0.3, ease: "power2.out" });
        });
      });
  
      // Submit button click feedback
      submit.addEventListener("click", () => {
        gsap.timeline()
          .to(submit, { scale: 0.95, duration: 0.1 })
          .to(submit, { scale: 1,    duration: 0.5, ease: "elastic.out(1, 0.4)" });
      });
    }
  
  
    /* ══════════════════════════════════════════════════════════
       15. FOOTER — Staggered entrance
       ══════════════════════════════════════════════════════════ */
    const footerObs = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        gsap.from(".footer-brand", { opacity: 0, y: 30, duration: 0.8 });
        gsap.from(".footer-social a", { opacity: 0, y: 20, stagger: 0.1, duration: 0.6, delay: 0.3 });
        gsap.from(".footer-bottom p",  { opacity: 0, y: 15, duration: 0.6, delay: 0.6 });
        footerObs.disconnect();
      });
    }, { threshold: 0.3 });
  
    const footer = document.querySelector("footer");
    if (footer) footerObs.observe(footer);
  
  
    /* ══════════════════════════════════════════════════════════
       16. SCROLL PROGRESS BAR
       ══════════════════════════════════════════════════════════ */
    const progressBar = document.createElement("div");
    progressBar.id = "scroll-progress";
    const progressBarStyle = document.createElement("style");
    progressBarStyle.textContent = `
      #scroll-progress {
        position: fixed;
        top: 0; left: 0;
        height: 3px;
        width: 0%;
        background: linear-gradient(90deg, var(--accent-color, #7c3aed), #a78bfa, #06b6d4);
        z-index: 99999;
        box-shadow: 0 0 8px var(--accent-color, #7c3aed);
        transition: width 0.1s linear;
        border-radius: 0 2px 2px 0;
      }
    `;
    document.head.appendChild(progressBarStyle);
    document.body.appendChild(progressBar);
  
    window.addEventListener("scroll", () => {
      const scrollTop  = document.documentElement.scrollTop;
      const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const pct = (scrollTop / scrollHeight) * 100;
      progressBar.style.width = pct + "%";
    }, { passive: true });
  
  
    /* ══════════════════════════════════════════════════════════
       17. NAVBAR LINK ACTIVE STATE on scroll
       ══════════════════════════════════════════════════════════ */
    const sections   = document.querySelectorAll("section[id], .contact[id]");
    const navLinks   = document.querySelectorAll(".nav-links a");
  
    const activeStyle = document.createElement("style");
    activeStyle.textContent = `
      .nav-links a.active {
        color: var(--accent-color, #7c3aed) !important;
        font-weight: 700;
      }
    `;
    document.head.appendChild(activeStyle);
  
    const linkObs = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const id   = entry.target.getAttribute("id");
        const link = document.querySelector(`.nav-links a[href="#${id}"]`);
        if (!link) return;
  
        if (entry.isIntersecting) {
          navLinks.forEach(l => l.classList.remove("active"));
          link.classList.add("active");
        }
      });
    }, { threshold: 0.4 });
  
    sections.forEach(sec => linkObs.observe(sec));
  
  
    /* ══════════════════════════════════════════════════════════
       18. GLITCH EFFECT on logo hover
       ══════════════════════════════════════════════════════════ */
    const logoEl = document.querySelector(".logo a, .footer-logo");
    if (logoEl) {
      const glitchStyle = document.createElement("style");
      glitchStyle.textContent = `
        .logo a, .footer-logo { position: relative; display: inline-block; }
        .logo a.glitch::before, .logo a.glitch::after,
        .footer-logo.glitch::before, .footer-logo.glitch::after {
          content: attr(data-text);
          position: absolute; top: 0; left: 0;
          width: 100%; height: 100%;
          color: inherit;
        }
        .logo a.glitch::before, .footer-logo.glitch::before {
          color: #f00; clip: rect(0,900px,2px,0); animation: glitch1 0.3s infinite;
        }
        .logo a.glitch::after, .footer-logo.glitch::after {
          color: #0ff; clip: rect(0,900px,4px,0); animation: glitch2 0.3s infinite;
        }
        @keyframes glitch1 {
          0%   { clip: rect(21px,900px,26px,0); transform: translate(-2px,0); }
          50%  { clip: rect(8px,900px,12px,0);  transform: translate(2px,0); }
          100% { clip: rect(21px,900px,26px,0); transform: translate(-2px,0); }
        }
        @keyframes glitch2 {
          0%   { clip: rect(14px,900px,18px,0); transform: translate(2px,0); }
          50%  { clip: rect(2px,900px,6px,0);   transform: translate(-2px,0); }
          100% { clip: rect(14px,900px,18px,0); transform: translate(2px,0); }
        }
      `;
      document.head.appendChild(glitchStyle);
      logoEl.dataset.text = logoEl.textContent;
  
      logoEl.addEventListener("mouseenter", () => logoEl.classList.add("glitch"));
      logoEl.addEventListener("mouseleave", () => logoEl.classList.remove("glitch"));
    }
  
  
    /* ══════════════════════════════════════════════════════════
       19. LOGO ENTRANCE (Navbar)
       ══════════════════════════════════════════════════════════ */
    gsap.from(".logo", { opacity: 0, x: -30, duration: 0.8, delay: 0.1 });
    gsap.from(".nav-links li", { opacity: 0, y: -20, stagger: 0.1, duration: 0.6, delay: 0.2 });
    gsap.from(".navbar .btn-primary", { opacity: 0, scale: 0.8, duration: 0.6, delay: 0.6, ease: "back.out(2)" });
  
  
    /* ══════════════════════════════════════════════════════════
       20. RIPPLE EFFECT on Buttons
       ══════════════════════════════════════════════════════════ */
    const rippleStyle = document.createElement("style");
    rippleStyle.textContent = `
      .btn { position: relative; overflow: hidden; }
      .ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        background: rgba(255,255,255,0.35);
        pointer-events: none;
        animation: ripple-anim 0.6s linear;
      }
      @keyframes ripple-anim {
        to { transform: scale(4); opacity: 0; }
      }
    `;
    document.head.appendChild(rippleStyle);
  
    document.querySelectorAll(".btn").forEach(btn => {
      btn.addEventListener("click", function(e) {
        const rect   = btn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height);
        const ripple = document.createElement("span");
        ripple.className = "ripple";
        ripple.style.cssText = `
          width: ${size}px; height: ${size}px;
          left: ${e.clientX - rect.left - size/2}px;
          top:  ${e.clientY - rect.top  - size/2}px;
        `;
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 700);
      });
    });
  
    console.log("✅ Advanced GSAP Animations Engine loaded — Ahmed AlaaEldin Portfolio");
  
  }); // END DOMContentLoaded