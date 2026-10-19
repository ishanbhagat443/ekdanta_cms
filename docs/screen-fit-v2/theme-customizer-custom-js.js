/* Ekdanta screen fit, v2 (Theme Customizer > Custom JS). One copy, for every screen size. */
(function () {
  // Parts the automatic sort gets wrong. L left, C centre, R right, S fills the screen,
  // SX fills the width, W widens a row so its two ends meet the left and right groups,
  // Z zooms to cover the screen, T is a sideways track of full-screen panels.
  // A part listed here never switches its section to centred.
  var OVERRIDES = {
    '.ek-home-hero [data-m="beam"]': 'S',
    '.ek-obstacles [data-m="walker"]': 'C',
    '.ek-reels-carousel [data-m="reel-wall"]': 'Z',
    '.ek-services-panels [data-m="svc-panels"]': 'T',
    '.ek-finale [data-m="end-foot"]': 'W',
    // Added while rolling out (desktop layout only), to match the Laptop boards:
    '.ek-work-hero .ek-comp--d [data-m="wk-text"]': 'C',
    '.ek-work-hero .ek-comp--d [data-m="wk-hint"]': 'C',
    '.ek-work-hero .ek-comp--d .ek-art > div:nth-child(2)': 'C',
    '.ek-case-studies-scroller .ek-comp--d [data-m="wc-texts"]': 'R',
    '.ek-work-showcase .ek-comp--d [data-m="work-panels"]': 'R',
    '.ek-reels-carousel .ek-comp--d [data-m="reel-late"]': 'C',
    '.ek-expertise-cards .ek-comp--d [data-m^="card-"]': 'L',
    '.ek-service-deep-dive .ek-comp--d [data-m="deep-groups"]': 'S',
    '.ek-process-path .ek-comp--d .ek-art > div:nth-child(5)': 'L',
    '.ek-proof-carousel .ek-comp--d [data-m="proof-head"]': 'L',
    '.ek-proof-carousel .ek-comp--d [data-m="proof-ui"]:has(a)': 'R',
    '.ek-proof-carousel .ek-comp--d [data-m="proof-ui"]:not(:has(a))': 'L',
    '.ek-contact-hero .ek-comp--d [data-m="bubble"]': 'C',
    '.ek-contact-channels .ek-comp--d [data-m="ch-cards"]': 'R',
    '.ek-brief-builder .ek-comp--d [data-m="brief-word"]': 'L',
    '.ek-team-roll-call .ek-comp--d [data-m="tm-head"]': 'C',
    '.ek-team-roll-call .ek-comp--d [data-m="tm-text"]': 'C',
    '.ek-not-found-hero .ek-comp--d [data-m="nf-trail"]': 'S'
  };
  var SPLIT = 0.66; // a desktop part wider than 66% of the frame keeps its whole section centred
  var MEDIA = /^(svg|img|video|canvas|picture)$/i;
  var timer = 0, watched = typeof WeakSet === 'function' ? new WeakSet() : null;

  function px(v) { return v.toFixed(2) + 'px'; }
  function unit(comp) {
    var cs = getComputedStyle(comp);
    return parseFloat(cs.getPropertyValue('--u')) || parseFloat(cs.getPropertyValue('--u-n')) || 1; // --u is the size in use
  }
  function override(el) {
    for (var sel in OVERRIDES) if (el.matches(sel)) return OVERRIDES[sel];
    return '';
  }
  // T: a sideways track of full-frame panels. Each panel widens to the screen. Inside a desktop
  // panel, parts that start on the left stay at the screen's left and the others go to the right;
  // inside a phone panel, the column stays centred.
  function track(el, U, Dw, isD) {
    Array.prototype.forEach.call(el.children, function (panel) {
      Array.prototype.forEach.call(panel.children, function (k) {
        var a = !isD ? 'C' : k.offsetLeft / U < Dw * 0.42 ? 'L' : 'R';
        if (k.getAttribute('data-ek-in') !== a) k.setAttribute('data-ek-in', a);
      });
    });
  }
  function tag(comp, stage, art) {
    var isD = comp.classList.contains('ek-comp--d');
    var U = unit(comp), Dw = isD ? 1440 : 390, Dh = isD ? 900 : 844;
    // The gaps between the design box and the stage (the screen), in screen pixels.
    var sw = stage.clientWidth, sh = stage.clientHeight, aw = art.offsetWidth, ah = art.offsetHeight;
    var l = art.offsetLeft, t = art.offsetTop, r = sw - l - aw, b = sh - t - ah;
    var s = art.style;
    s.setProperty('--ek-l', px(l)); s.setProperty('--ek-r', px(r));
    s.setProperty('--ek-t', px(t)); s.setProperty('--ek-b', px(b));
    s.setProperty('--ek-dy', px((sh - ah) / 2 - t)); // 0 when the box is already centred
    s.setProperty('--ek-cover', Math.max(sw / aw, sh / ah).toFixed(4));
    var parts = [], centred = !isD;
    Array.prototype.forEach.call(art.children, function (el) {
      if (/^(SCRIPT|STYLE|TEMPLATE)$/.test(el.tagName)) return;
      var a = override(el), fixed = !!a;
      var x0 = el.offsetLeft / U, x1 = x0 + el.offsetWidth / U;
      var y0 = el.offsetTop / U, y1 = y0 + el.offsetHeight / U;
      // a part that sits on the frame's bottom edge sits on the screen's bottom edge
      var v = y1 >= Dh - 6 && y0 > Dh * 0.3 ? 'B' : '';
      if (v !== (el.getAttribute('data-ek-v') || '')) { if (v) el.setAttribute('data-ek-v', v); else el.removeAttribute('data-ek-v'); }
      if (!a) {
        var wide = x0 <= 2 && x1 >= Dw - 2, tall = y0 <= 2 && y1 >= Dh - 2;
        var fill = !el.textContent.trim() && Array.prototype.every.call(el.children, function (k) { return MEDIA.test(k.tagName); });
        if (wide && fill) a = tall ? 'S' : 'SX';
        else if (wide) a = 'C';
        else {
          if (x1 - x0 > Dw * SPLIT) centred = true;
          var cx = (x0 + x1) / 2;
          a = cx < Dw * 0.42 ? 'L' : cx > Dw * 0.58 ? 'R' : 'C';
        }
      }
      // design widths, measured once before the CSS widens anything
      if (a === 'W' && !el.style.getPropertyValue('--ek-w')) el.style.setProperty('--ek-w', Math.round(el.offsetWidth / U));
      if (a === 'T' && !el.style.getPropertyValue('--ek-pw') && el.firstElementChild) el.style.setProperty('--ek-pw', Math.round(el.firstElementChild.offsetWidth / U));
      parts.push([el, a, fixed, v]);
    });
    var dy = (sh - ah) / 2 - t;
    function moved(p) { // how far the CSS moves a part, in screen pixels
      var a = p[1];
      return [a === 'L' || a === 'W' ? -l : a === 'R' ? r : a === 'S' || a === 'SX' ? 0 : (r - l) / 2, p[3] === 'B' ? b : dy];
    }
    var beam = null, rig = null, copy = null;
    parts.forEach(function (p) {
      if (centred && !p[2] && (p[1] === 'L' || p[1] === 'R')) p[1] = 'C';
      if (p[0].getAttribute('data-ek-fit') !== p[1]) p[0].setAttribute('data-ek-fit', p[1]);
      if (p[1] === 'T') track(p[0], U, Dw, isD);
      if (p[0].getAttribute('data-m') === 'beam') beam = p;
      else if (p[0].querySelector('[data-m="rig"]')) rig = p;
      else if (!copy && p[0].textContent.trim()) copy = p;
    });
    // Home hero: the beam layers fill the screen. Its tip follows the torch rig, and the lit
    // copy of the headline inside it stays over the headline.
    if (beam) {
      var h = rig ? moved(rig) : [0, 0], c = copy ? moved(copy) : [0, 0];
      s.setProperty('--ek-hx', px(l + h[0])); s.setProperty('--ek-hy', px(t + h[1]));
      s.setProperty('--ek-cx', px(l + c[0])); s.setProperty('--ek-cy', px(t + c[1]));
    }
  }
  function run() {
    timer = 0;
    document.querySelectorAll('.ek-comp').forEach(function (comp) {
      var art = comp.querySelector('.ek-stage > .ek-art');
      if (!art) return;
      if (watched && !watched.has(art)) { // re-sort if a section adds or replaces its parts
        watched.add(art);
        new MutationObserver(later).observe(art, { childList: true });
      }
      if (art.offsetWidth) tag(comp, art.parentElement, art); // hidden stages are sorted once they show
    });
  }
  function later() { if (!timer) timer = setTimeout(run, 60); }
  ['resize', 'orientationchange', 'load', 'pageshow'].forEach(function (e) { window.addEventListener(e, later); });
  if (window.visualViewport) window.visualViewport.addEventListener('resize', later);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(later);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', later);
  else later();
  window.EK_FIT_RUN = run; // lets you re-run it by hand while testing
})();