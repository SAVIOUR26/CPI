(function () {
  var doc = document.documentElement;
  doc.classList.add('js');
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Public header ---------- */
  var header = document.getElementById('site-header');
  var toggle = document.getElementById('nav-toggle');
  var collapse = document.getElementById('nav-collapse');
  var backToTop = document.getElementById('back-to-top');

  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    if (header) header.classList.toggle('is-scrolled', y > 12);
    if (backToTop) backToTop.classList.toggle('is-visible', y > 600);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  if (backToTop) {
    backToTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }

  function setNavOpen(open) {
    if (!toggle || !collapse) return;
    collapse.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    toggle.innerHTML = open ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
  }

  if (toggle && collapse) {
    toggle.addEventListener('click', function () {
      setNavOpen(!collapse.classList.contains('open'));
    });
    collapse.addEventListener('click', function (e) {
      if (e.target.closest('a')) setNavOpen(false);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth > 1180) setNavOpen(false);
    });
  }

  // Dropdowns: hover/focus handle mouse & keyboard in CSS; this adds tap support.
  var dropdownButtons = document.querySelectorAll('[data-dropdown]');
  dropdownButtons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (window.innerWidth <= 1180) return;
      e.preventDefault();
      var item = btn.closest('.nav-item');
      var open = !item.classList.contains('is-open');
      closeDropdowns();
      item.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  function closeDropdowns() {
    document.querySelectorAll('.nav-item.is-open').forEach(function (item) {
      item.classList.remove('is-open');
      var b = item.querySelector('[data-dropdown]');
      if (b) b.setAttribute('aria-expanded', 'false');
    });
  }
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.nav-item')) closeDropdowns();
  });

  /* ---------- Dashboard sidebar ---------- */
  var dashBtn = document.getElementById('dash-menu-btn');
  function setDashOpen(open) {
    document.body.classList.toggle('dash-open', open);
    if (dashBtn) dashBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  if (dashBtn) {
    dashBtn.addEventListener('click', function () {
      setDashOpen(!document.body.classList.contains('dash-open'));
    });
    document.querySelectorAll('[data-dash-close]').forEach(function (el) {
      el.addEventListener('click', function () { setDashOpen(false); });
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024) setDashOpen(false);
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    setNavOpen(false);
    closeDropdowns();
    setDashOpen(false);
  });


  /* ---------- Instant filter for long portal tables ---------- */
  document.querySelectorAll('.dash-main .table-card').forEach(function (card) {
    var rows = card.querySelectorAll('tbody tr');
    if (rows.length <= 8) return;
    var wrap = document.createElement('div');
    wrap.className = 'table-tools';
    wrap.innerHTML = '<div class="input-icon"><i class="fa-solid fa-magnifying-glass"></i>' +
      '<input type="search" placeholder="Filter ' + rows.length + ' rows…" aria-label="Filter table"></div>' +
      '<span class="table-count">' + rows.length + ' rows</span>';
    card.parentNode.insertBefore(wrap, card);
    var input = wrap.querySelector('input');
    var count = wrap.querySelector('.table-count');
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      var shown = 0;
      rows.forEach(function (tr) {
        var match = !q || tr.textContent.toLowerCase().indexOf(q) !== -1;
        tr.style.display = match ? '' : 'none';
        if (match) shown++;
      });
      count.textContent = q ? shown + ' of ' + rows.length + ' rows' : rows.length + ' rows';
    });
  });

  /* ---------- Count-up numbers ---------- */
  function countUp(el) {
    var target = parseInt(el.getAttribute('data-count'), 10);
    if (isNaN(target)) return;
    if (reduceMotion || target === 0) { el.textContent = target.toLocaleString(); return; }
    var duration = 1400;
    var start = null;
    function frame(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased).toLocaleString();
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  /* ---------- Scroll reveal ---------- */
  var revealEls = document.querySelectorAll('[data-reveal]');
  var counters = document.querySelectorAll('[data-count]');

  if ('IntersectionObserver' in window && !reduceMotion) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        if (el.hasAttribute('data-reveal')) el.classList.add('is-visible');
        if (el.hasAttribute('data-count')) countUp(el);
        io.unobserve(el);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
    counters.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    counters.forEach(countUp);
  }
})();
