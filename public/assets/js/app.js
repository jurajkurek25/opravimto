/* ─── Opravimto.sk – Main JS ─── */

document.addEventListener('DOMContentLoaded', () => {

  // ── Scroll reveal ──
  const revealEls = document.querySelectorAll('.reveal-up, .reveal-right');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  revealEls.forEach(el => observer.observe(el));

  // ── Nav hide on scroll ──
  let lastY = 0;
  const nav = document.getElementById('main-nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      if (y > 80 && y > lastY) nav.classList.add('hidden-nav');
      else nav.classList.remove('hidden-nav');
      lastY = y;
    }, { passive: true });
  }

  // ── Mobile menu toggle ──
  const menuBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('open');
    });
  }

  // ── Hero canvas circuit particles ──
  const canvas = document.getElementById('hero-canvas');
  if (canvas) {
    const ctx = canvas.getContext('2d');
    let particles = [];
    let W, H;

    function resize() {
      W = canvas.width  = canvas.offsetWidth;
      H = canvas.height = canvas.offsetHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    class Particle {
      constructor() { this.reset(); }
      reset() {
        this.x = Math.random() * W;
        this.y = Math.random() * H;
        this.vx = (Math.random() - 0.5) * 0.4;
        this.vy = (Math.random() - 0.5) * 0.4;
        this.r  = Math.random() * 2 + 0.5;
        this.alpha = Math.random() * 0.6 + 0.2;
        this.life = 0;
        this.maxLife = Math.random() * 300 + 200;
      }
      update() {
        this.x += this.vx;
        this.y += this.vy;
        this.life++;
        if (this.life > this.maxLife || this.x < 0 || this.x > W || this.y < 0 || this.y > H) this.reset();
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(114,214,216,${this.alpha})`;
        ctx.fill();
      }
    }

    for (let i = 0; i < 60; i++) particles.push(new Particle());

    function drawLines() {
      for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
          const dx = particles[i].x - particles[j].x;
          const dy = particles[i].y - particles[j].y;
          const d  = Math.sqrt(dx * dx + dy * dy);
          if (d < 120) {
            ctx.beginPath();
            ctx.moveTo(particles[i].x, particles[i].y);
            ctx.lineTo(particles[j].x, particles[j].y);
            ctx.strokeStyle = `rgba(114,214,216,${0.15 * (1 - d / 120)})`;
            ctx.lineWidth = 0.5;
            ctx.stroke();
          }
        }
      }
    }

    function animate() {
      ctx.clearRect(0, 0, W, H);
      particles.forEach(p => { p.update(); p.draw(); });
      drawLines();
      requestAnimationFrame(animate);
    }
    animate();
  }

  // ── Counter animation ──
  const counters = document.querySelectorAll('.counter[data-target]');
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target;
      const target = parseInt(el.dataset.target, 10);
      const duration = 1500;
      const start = performance.now();
      const update = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(eased * target);
        if (progress < 1) requestAnimationFrame(update);
        else el.textContent = target;
      };
      requestAnimationFrame(update);
      counterObserver.unobserve(el);
    });
  }, { threshold: 0.5 });
  counters.forEach(c => counterObserver.observe(c));

  // ── Toast notification helper ──
  window.showToast = function(msg, type = 'success') {
    let toast = document.getElementById('toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'toast';
      document.body.appendChild(toast);
    }
    const icon = type === 'success' ? 'check_circle' : 'error';
    const color = type === 'success' ? '#72d6d8' : '#ffb4ab';
    toast.innerHTML = `<span class="material-symbols-outlined" style="color:${color};font-size:20px">${icon}</span>${msg}`;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
  };

  // ── Copy to clipboard with toast ──
  document.querySelectorAll('[onclick*="clipboard"]').forEach(btn => {
    btn.addEventListener('click', () => {
      window.showToast('Skopírované do schránky');
    });
  });

  // ── Reservation form step highlight ──
  const form = document.getElementById('rezervacia-form');
  if (form) {
    const sections = form.querySelectorAll('.form-section');
    const inputs = form.querySelectorAll('input, select, textarea');
    const progressBar = document.getElementById('progress-bar');

    function checkProgress() {
      const s1filled = form.querySelector('#zariadenie_typ').value && form.querySelector('#zariadenie_model').value;
      const s2filled = form.querySelector('#problem_nazov').value;
      const s3filled = form.querySelector('#email').value;

      const filled = [s1filled, s2filled, s3filled].filter(Boolean).length;
      if (progressBar) progressBar.style.width = (filled / 3 * 100) + '%';
    }

    inputs.forEach(inp => inp.addEventListener('input', checkProgress));
  }

  // ── Smooth anchor scroll ──
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const target = document.querySelector(a.getAttribute('href'));
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  // ── Admin: confirm destructive actions ──
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.dataset.confirm)) e.preventDefault();
    });
  });

});
