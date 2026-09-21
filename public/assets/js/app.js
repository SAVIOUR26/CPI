(function () {
  var toggle = document.getElementById('nav-toggle');
  var collapse = document.getElementById('nav-collapse');
  if (!toggle || !collapse) return;

  function setOpen(open) {
    collapse.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.innerHTML = open ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
  }

  toggle.addEventListener('click', function () {
    setOpen(!collapse.classList.contains('open'));
  });

  collapse.addEventListener('click', function (e) {
    if (e.target.tagName === 'A') setOpen(false);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') setOpen(false);
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 900) setOpen(false);
  });
})();
