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
  document.querySelectorAll('.dash-main .table-card:not([data-no-filter])').forEach(function (card) {
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

  /* ---------- Multi-step application form ---------- */
  function formatMB(bytes) {
    if (bytes < 1048576) return Math.max(1, Math.round(bytes / 1024)) + 'KB';
    var mb = bytes / 1048576;
    return (mb >= 10 ? Math.round(mb) : Math.round(mb * 10) / 10) + 'MB';
  }

  function initStepper(form) {
    var steps = Array.prototype.slice.call(form.querySelectorAll('.form-step'));
    if (steps.length < 2) return;
    var markers = Array.prototype.slice.call(form.querySelectorAll('.stepper [data-goto]'));
    var back = form.querySelector('[data-step-back]');
    var next = form.querySelector('[data-step-next]');
    var submit = form.querySelector('[data-step-submit]');
    var counter = form.querySelector('[data-step-count]');
    var totalNote = form.querySelector('[data-upload-total]');
    var maxFile = parseInt(form.getAttribute('data-max-file'), 10) || 0;
    var maxTotal = parseInt(form.getAttribute('data-max-total'), 10) || 0;
    var maxCount = parseInt(form.getAttribute('data-max-count'), 10) || 0;
    var submitLabel = submit ? submit.innerHTML : '';
    var current = 0;
    var furthest = 0;
    var dirty = false;
    var submitting = false;

    function show(index, move) {
      current = Math.max(0, Math.min(index, steps.length - 1));
      furthest = Math.max(furthest, current);
      steps.forEach(function (step, n) { step.classList.toggle('is-active', n === current); });
      markers.forEach(function (marker, n) {
        var reachable = n !== current && n <= furthest;
        marker.classList.toggle('is-current', n === current);
        marker.classList.toggle('is-done', reachable);
        marker.tabIndex = reachable ? 0 : -1;
        if (n === current) marker.setAttribute('aria-current', 'step'); else marker.removeAttribute('aria-current');
      });
      var last = current === steps.length - 1;
      if (back) back.hidden = current === 0;
      if (next) next.hidden = last;
      if (submit) submit.hidden = !last;
      if (counter) counter.textContent = 'Step ' + (current + 1) + ' of ' + steps.length;
      if (last) fillSignature();
      if (move) {
        var offset = header ? header.offsetHeight + 16 : 16;
        window.scrollTo({ top: form.getBoundingClientRect().top + window.pageYOffset - offset, behavior: reduceMotion ? 'auto' : 'smooth' });
        steps[current].setAttribute('tabindex', '-1');
        steps[current].focus({ preventScroll: true });
      }
    }

    // Checks one step; when `report` is set, shows that step and the browser's message for the first problem.
    function validateStep(index, report) {
      var fields = steps[index].querySelectorAll('input, select, textarea');
      for (var i = 0; i < fields.length; i++) {
        if (fields[i].disabled || fields[i].checkValidity()) continue;
        if (report) {
          show(index, false);
          fields[i].reportValidity();
        }
        return false;
      }
      if (totalNote && steps[index].contains(totalNote) && !updateTotal()) {
        if (report) {
          show(index, false);
          totalNote.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
        }
        return false;
      }
      return true;
    }

    function goTo(target) {
      if (target === current || target > furthest) return;
      for (var i = current; i < target; i++) {
        if (!validateStep(i, true)) return;
      }
      show(target, true);
    }

    function fillSignature() {
      var sig = form.querySelector('#sig');
      if (!sig || sig.value.trim()) return;
      var parts = ['#p_other', '#p_surname'].map(function (sel) {
        var el = form.querySelector(sel);
        return el ? el.value.trim() : '';
      }).filter(Boolean);
      if (parts.length) sig.value = parts.join(' ');
    }

    /* Attachments: type, per-file size, per-field count and whole-form limits */
    function checkFiles(input) {
      var files = input.files || [];
      var accept = (input.getAttribute('accept') || '').toLowerCase().split(',').filter(Boolean);
      var perField = parseInt(input.getAttribute('data-max-files'), 10) || 0;
      var msg = '';
      for (var i = 0; i < files.length && !msg; i++) {
        var ext = '.' + files[i].name.split('.').pop().toLowerCase();
        if (accept.length && accept.indexOf(ext) === -1) {
          msg = '"' + files[i].name + '" is not an accepted file type (' + accept.join(', ').replace(/\./g, '').toUpperCase() + ').';
        } else if (maxFile && files[i].size > maxFile) {
          msg = '"' + files[i].name + '" is ' + formatMB(files[i].size) + '. The maximum is ' + formatMB(maxFile) + ' per file.';
        }
      }
      if (!msg && perField && files.length > perField) msg = 'Please choose at most ' + perField + ' files here.';
      input.setCustomValidity(msg);

      var box = input.closest('.upload-box');
      var label = box && box.querySelector('[data-file-label]');
      if (label) {
        if (!label.hasAttribute('data-default')) label.setAttribute('data-default', label.textContent);
        label.textContent = msg || (files.length === 1 ? files[0].name + ' · ' + formatMB(files[0].size)
          : files.length ? files.length + ' files selected' : label.getAttribute('data-default'));
        box.classList.toggle('has-file', files.length > 0 && !msg);
        box.classList.toggle('has-error', !!msg);
      }
    }

    function updateTotal() {
      if (!totalNote) return true;
      var bytes = 0;
      var count = 0;
      form.querySelectorAll('input[type=file]').forEach(function (input) {
        var files = input.files || [];
        for (var i = 0; i < files.length; i++) { bytes += files[i].size; count++; }
      });
      // Leave headroom for the text fields and multipart overhead that share the same limit.
      var tooBig = maxTotal > 0 && bytes + 65536 > maxTotal;
      var tooMany = maxCount > 0 && count > maxCount;
      totalNote.classList.toggle('is-over', tooBig || tooMany);
      if (!count) {
        totalNote.textContent = '';
      } else {
        totalNote.textContent = count + (count === 1 ? ' file' : ' files') + ' attached · ' + formatMB(bytes)
          + (maxTotal ? ' of ' + formatMB(maxTotal) + ' allowed' : '')
          + (tooBig ? ' — too large to send together. Please attach smaller scans or fewer files.' : '')
          + (!tooBig && tooMany ? ' — the maximum is ' + maxCount + ' files per application.' : '');
      }
      return !(tooBig || tooMany);
    }

    form.addEventListener('change', function (e) {
      dirty = true;
      if (e.target.type === 'file') {
        checkFiles(e.target);
        updateTotal();
      }
    });
    form.addEventListener('input', function () { dirty = true; });

    form.querySelectorAll('.upload-box input[type=file]').forEach(function (input) {
      var box = input.closest('.upload-box');
      input.addEventListener('dragenter', function () { box.classList.add('is-drag'); });
      ['dragleave', 'drop'].forEach(function (type) {
        input.addEventListener(type, function () { box.classList.remove('is-drag'); });
      });
    });

    /* Extra rows (e.g. other qualifications) */
    form.querySelectorAll('[data-add-row]').forEach(function (btn) {
      var table = document.getElementById(btn.getAttribute('data-add-row'));
      var max = parseInt(btn.getAttribute('data-max-rows'), 10) || 10;
      if (!table || !table.tBodies.length) return;
      var body = table.tBodies[0];
      function sync() { btn.hidden = body.rows.length >= max; }
      btn.addEventListener('click', function () {
        if (body.rows.length >= max) return;
        var index = body.rows.length;
        var row = body.rows[index - 1].cloneNode(true);
        row.querySelectorAll('input, select, textarea').forEach(function (el) {
          el.name = el.name.replace(/\[\d+\]/, '[' + index + ']');
          el.value = '';
          el.setCustomValidity('');
        });
        body.appendChild(row);
        row.querySelector('input').focus();
        sync();
        updateTotal();
      });
      sync();
    });

    /* Navigation */
    if (next) next.addEventListener('click', function () {
      if (validateStep(current, true)) show(current + 1, true);
    });
    if (back) back.addEventListener('click', function () { show(current - 1, true); });
    markers.forEach(function (marker, n) {
      marker.addEventListener('click', function () { goTo(n); });
      marker.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); goTo(n); }
      });
    });

    form.addEventListener('submit', function (e) {
      if (submitting) { e.preventDefault(); return; }
      if (current < steps.length - 1) {
        // Enter in a field moves to the next step instead of submitting early.
        e.preventDefault();
        if (validateStep(current, true)) show(current + 1, true);
        return;
      }
      for (var i = 0; i < steps.length; i++) {
        if (!validateStep(i, true)) { e.preventDefault(); return; }
      }
      submitting = true;
      if (submit) {
        submit.disabled = true;
        submit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting…';
      }
    });

    window.addEventListener('beforeunload', function (e) {
      if (!dirty || submitting) return;
      e.preventDefault();
      e.returnValue = '';
    });
    window.addEventListener('pageshow', function (e) {
      if (!e.persisted || !submit) return;
      submitting = false;
      submit.disabled = false;
      submit.innerHTML = submitLabel;
    });

    // Returning with errors (or after re-selecting attachments): open the step that needs attention.
    var start = 0;
    var firstError = form.querySelector('.field-error');
    if (firstError || form.querySelector('input[name="declaration"]:checked')) {
      furthest = steps.length - 1;
      var target = firstError ? firstError.closest('.form-step') : (totalNote && totalNote.closest('.form-step'));
      start = Math.max(0, steps.indexOf(target));
    }
    show(start, false);
  }
  document.querySelectorAll('form[data-stepper]').forEach(initStepper);

  /* ---------- Copy-to-clipboard buttons ---------- */
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    var label = btn.innerHTML;
    function copied() {
      btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
      setTimeout(function () { btn.innerHTML = label; }, 2000);
    }
    function fallback(text) {
      var area = document.createElement('textarea');
      area.value = text;
      area.setAttribute('readonly', '');
      area.style.position = 'fixed';
      area.style.opacity = '0';
      document.body.appendChild(area);
      area.select();
      try { if (document.execCommand('copy')) copied(); } catch (e) {}
      document.body.removeChild(area);
    }
    btn.addEventListener('click', function () {
      var text = btn.getAttribute('data-copy');
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(copied, function () { fallback(text); });
      } else {
        fallback(text);
      }
    });
  });

  /* ---------- Confirm consequential choices (e.g. admission decisions) ---------- */
  document.querySelectorAll('form[data-decision-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var picked = form.querySelector('input[type=radio]:checked');
      var message = picked && picked.getAttribute('data-confirm');
      if (message && !window.confirm(message)) e.preventDefault();
    });
  });

  /* ---------- Confirm deletions ---------- */
  document.querySelectorAll('form[data-confirm-submit]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm-submit'))) e.preventDefault();
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
