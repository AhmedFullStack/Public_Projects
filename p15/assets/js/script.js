
gsap.registerPlugin(ScrollTrigger);

/* CURSOR */
const dot = document.getElementById('dot');
const ring = document.getElementById('ring');

/* تمركز العنصرين من مركزهما بدلاً من الزاوية */
gsap.set([dot, ring], { xPercent: -50, yPercent: -50 });

window.addEventListener('mousemove', e => {
  gsap.to(dot,  { x: e.clientX, y: e.clientY, duration: .08, overwrite: 'auto' });
  gsap.to(ring, { x: e.clientX, y: e.clientY, duration: .3, ease: 'power2.out', overwrite: 'auto' });
});

document.querySelectorAll('button, .tag').forEach(el => {
  el.addEventListener('mouseenter', () => ring.classList.add('on'));
  el.addEventListener('mouseleave', () => ring.classList.remove('on'));
});

/* CARDS ENTRANCE */
gsap.utils.toArray('.card').forEach((card, i) => {
  gsap.to(card, {
    opacity: 1, y: 0, duration: .65, ease: 'power2.out',
    delay: i === 0 ? .15 : 0,
    scrollTrigger: { trigger: card, start: 'top 88%' }
  });
});

/* IMG HOVER SCALE */
document.querySelectorAll('.prod-card').forEach(card => {
  const img = card.querySelector('img');
  card.addEventListener('mouseenter', () =>
    gsap.to(img, { scale: 1.04, duration: .4, ease: 'power2.out' })
  );
  card.addEventListener('mouseleave', () =>
    gsap.to(img, { scale: 1, duration: .4, ease: 'power2.out' })
  );
});
 