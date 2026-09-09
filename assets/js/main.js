/* SialSourcing — Main JavaScript */

document.addEventListener('DOMContentLoaded', () => {

  // ---- Navbar scroll effect ----
  const nav = document.querySelector('.nav');
  window.addEventListener('scroll', () => {
    nav?.classList.toggle('scrolled', window.scrollY > 60);
  });

  // ---- Hamburger menu ----
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', function() {
      const isOpen = navLinks.classList.contains('open');
      if (isOpen) {
        navLinks.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
      } else {
        navLinks.classList.add('open');
        hamburger.setAttribute('aria-expanded', 'true');
      }
    });

    navLinks.querySelectorAll('a:not(.nav-dropdown-toggle)').forEach(function(link) {
      link.addEventListener('click', function() {
        navLinks.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
      });
    });

    document.querySelectorAll('.nav-dropdown-toggle').forEach(function(toggle) {
      toggle.addEventListener('click', function(e) {
        if (window.innerWidth < 768) {
          e.preventDefault();
          const dropdown = toggle.closest('.nav-dropdown');
          dropdown.classList.toggle('open');
        }
      });
    });
  }
  // ↑ hamburger if block closes HERE — everything below is independent

  // ---- Scroll reveal (Intersection Observer) ----
  const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

  if (revealEls.length > 0) {
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting || entry.intersectionRatio > 0) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0,
        rootMargin: '0px'
      });
      revealEls.forEach(el => observer.observe(el));
    } else {
      revealEls.forEach(el => el.classList.add('visible'));
    }

    // Universal safety net — any element still hidden after 1.5s becomes visible
    setTimeout(() => {
      revealEls.forEach(el => el.classList.add('visible'));
    }, 1500);
  }

  // ---- Animated counters ----
  function animateCounter(el, target, duration = 2000) {
    const start = performance.now();
    const suffix = target.replace(/[\d,]/g, '');
    const num = parseInt(target.replace(/\D/g, ''));
    const update = (time) => {
      const elapsed = time - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(num * eased) + suffix;
      if (progress < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  }

  const counters = document.querySelectorAll('[data-count]');
  if (counters.length > 0) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target, entry.target.dataset.count);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(el => counterObserver.observe(el));
  }

  // ---- FAQ accordion ----
  document.querySelectorAll('.faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  // ---- Lab tabs filter ----
  const tabs = document.querySelectorAll('.lab-tab');
  const labCards = document.querySelectorAll('.lab-card[data-category]');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const cat = tab.dataset.filter;
      labCards.forEach(card => {
        const show = cat === 'all' || card.dataset.category === cat;
        card.style.display = show ? 'block' : 'none';
        if (show) card.style.animation = 'fadeIn 0.4s ease';
      });
    });
  });

  // ---- Smooth anchor links ----
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        navLinks?.classList.remove('open');
      }
    });
  });

  // ---- Copy-link buttons (blog share row) ----
  document.querySelectorAll('[data-copy-link]').forEach(btn => {
    btn.addEventListener('click', () => {
      const url = btn.dataset.copyLink;
      const done = () => {
        const label = btn.textContent;
        btn.textContent = 'Copied ✓';
        btn.classList.add('copied');
        setTimeout(() => { btn.textContent = label; btn.classList.remove('copied'); }, 2000);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done);
      } else {
        const ta = document.createElement('textarea');
        ta.value = url;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        done();
      }
    });
  });

});