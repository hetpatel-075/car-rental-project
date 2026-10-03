(function () {
  var grid = document.getElementById('carGrid');
  if (grid) {
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.premium-car-card'));
    var search = document.getElementById('carSearch'), sort = document.getElementById('carSort');
    var chips = document.querySelectorAll('#chipRow .chip, .category-card.cat'), none = document.getElementById('noResults');
    var type = 'all', activeLabel = '';
    function apply() {
      var q = search.value.trim().toLowerCase(), shown = 0;
      var list = cards.slice();
      if (sort.value === 'low') list.sort(function (a, b) { return a.dataset.price - b.dataset.price; });
      else if (sort.value === 'high') list.sort(function (a, b) { return b.dataset.price - a.dataset.price; });
      else list.sort(function (a, b) { return b.dataset.order - a.dataset.order; });
      list.forEach(function (c) {
        var ok = (type === 'all' || type.split(',').indexOf(c.dataset.type) > -1) && (!q || c.dataset.text.indexOf(q) > -1);
        c.style.display = ok ? '' : 'none';
        if (ok) { shown++; c.classList.add('visible'); }
        grid.appendChild(c);
      });
      if (none) { none.style.display = shown ? 'none' : 'block'; if (!shown) none.textContent = (activeLabel && !q) ? 'No ' + activeLabel + ' cars available right now. Please check other categories.' : 'No cars match your search. Try another category.'; }
    }
    chips.forEach(function (ch) {
      ch.addEventListener('click', function () {
        chips.forEach(function (x) { x.classList.remove('active'); });
        ch.classList.add('active'); type = ch.dataset.type; activeLabel = ch.dataset.label || ch.dataset.type; if (type === 'all') activeLabel = ''; apply();
        var sec = document.getElementById('cars'); if (sec) sec.scrollIntoView({ behavior: 'smooth' });
      });
    });
    search.addEventListener('input', apply); sort.addEventListener('change', apply);
  }

  // scroll reveal
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
  }, { threshold: .12 }) : null;
  document.querySelectorAll('.reveal').forEach(function (el) { io ? io.observe(el) : el.classList.add('visible'); });

  // animated counters
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var end = parseInt(el.dataset.count, 10), suf = el.dataset.suffix || (end === 24 ? '/7' : '+');
    if (el.dataset.count === '24') { suf = '/7'; }
    var run = function () {
      var t0 = null;
      (function step(t) { t0 = t0 || t; var p = Math.min((t - t0) / 1200, 1);
        el.textContent = Math.round(end * p) + (p === 1 ? suf : ''); if (p < 1) requestAnimationFrame(step); })(performance.now());
    };
    io ? new IntersectionObserver(function (es, o) { if (es[0].isIntersecting) { run(); o.disconnect(); } }).observe(el) : run();
  });

  // navbar shadow on scroll
  var nav = document.querySelector('.modern-nav');
  window.addEventListener('scroll', function () { if (nav) nav.classList.toggle('scrolled', window.scrollY > 10); });
})();
