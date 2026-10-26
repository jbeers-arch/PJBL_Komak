/*! komak-fx.js: pustaka efek animasi PJBL Komak (global: KomakFX)
 * Pasang sebelum </body> setelah script halaman: <script src="../assets/js/komak-fx.js"></script>
 */
(() => {
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const SP = 'cubic-bezier(.34,1.56,.64,1)', OUT = 'cubic-bezier(.2,.8,.2,1)';
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const el = (t, c, h) => { const e = document.createElement(t); if (c) e.className = c; if (h != null) e.innerHTML = h; return e; };
  const NOOP = { finished: Promise.resolve(), cancel() {} };
  const A = (e, k, o) => (!e || reduce) ? NOOP : e.animate(k, { fill: 'backwards', ...o });
  const wait = ms => new Promise(r => setTimeout(r, reduce ? 0 : ms));
  const fmt = n => Math.round(n).toLocaleString('id-ID');
  const CHECK = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
  const SPIN = '<span class="kfx-spin"></span>';

  const st = el('style');
  st.textContent = `
@view-transition{navigation:auto}
::view-transition-old(root),::view-transition-new(root){animation-duration:.25s}
header{view-transition-name:kfx-head}#main-sidebar{view-transition-name:kfx-side}
.kfx-meter{display:grid;gap:6px;padding:12px;border-radius:16px;border:1.5px solid rgba(148,163,184,.35);font-size:12px;font-weight:700;transition:border-color .3s,background .3s}
.kfx-meter.ok{border-color:#10b981;background:rgba(16,185,129,.08)}
.kfx-mrow{display:grid;grid-template-columns:48px 1fr auto;gap:8px;align-items:center}
.kfx-mrow i{height:8px;border-radius:4px;background:rgba(148,163,184,.25);overflow:hidden}
.kfx-mrow b{display:block;height:100%;background:#6366f1;transform-origin:left;transition:transform .5s ${OUT},background .3s}
.kfx-meter.ok b{background:#10b981}
.kfx-mrow em{font-style:normal;font-variant-numeric:tabular-nums}
.kfx-mst{display:flex;align-items:center;gap:6px;color:#f43f5e}.kfx-meter.ok .kfx-mst{color:#10b981}
.kfx-stp{display:grid;grid-template-columns:repeat(var(--n,5),1fr);position:relative;text-align:center;font-size:11px;font-weight:700}
.kfx-line{position:absolute;top:14px;left:calc(50%/var(--n,5));right:calc(50%/var(--n,5));height:4px;border-radius:2px;background:rgba(148,163,184,.3)}
.kfx-line u{display:block;height:100%;border-radius:2px;background:#10b981;transform-origin:left;transform:scaleX(0);transition:transform .7s ${OUT}}
.kfx-st{position:relative;display:grid;justify-items:center;gap:4px}
.kfx-dot{width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:#e2e8f0;color:#475569;transition:background .3s,color .3s}
html.dark .kfx-dot{background:#27324a;color:#cbd5e1}
.kfx-dot.done{background:#10b981;color:#fff}
.kfx-dot.now{background:#6366f1;color:#fff;animation:kfxRing 1.4s infinite}
@keyframes kfxRing{0%{box-shadow:0 0 0 0 rgba(99,102,241,.5)}100%{box-shadow:0 0 0 10px rgba(99,102,241,0)}}
.kfx-q{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:12px;font-size:13px;font-weight:600}
.kfx-q.ok{color:#10b981}.kfx-q.err{color:#f43f5e}.kfx-qi{width:20px;height:20px;display:grid;place-items:center}
.kfx-spin{width:16px;height:16px;border:2.5px solid currentColor;border-right-color:transparent;border-radius:50%;animation:kfxSpin .7s linear infinite;display:inline-block}
@keyframes kfxSpin{to{transform:rotate(360deg)}}
.kfx-scan{position:relative;overflow:hidden}
.kfx-scanline{position:absolute;left:0;right:0;top:0;height:3px;background:#22d3ee;box-shadow:0 0 16px 4px rgba(34,211,238,.7);z-index:5}
.kfx-stamp{position:absolute;right:12px;top:50%;margin-top:-14px;padding:2px 10px;border:2.5px solid #10b981;color:#10b981;border-radius:8px;font-weight:800;letter-spacing:.08em;font-size:13px;transform:rotate(-8deg);pointer-events:none}
.kfx-fly{position:fixed;left:0;top:0;z-index:9999;font-size:40px;line-height:1;pointer-events:none}
.kfx-lockov{position:fixed;inset:0;z-index:9999;display:grid;place-content:center;justify-items:center;gap:8px;background:rgba(10,16,30,.7);color:#fff;font-weight:800}
@media (prefers-reduced-motion:reduce){.kfx-dot.now,.kfx-spin{animation:none}}
`;
  document.head.appendChild(st);

  /* 1. Gerak menggeleng (dipakai efek lain) */
  const shake = e => A(e, [{ translate: '0' }, { translate: '-6px' }, { translate: '6px' }, { translate: '-4px' }, { translate: '0' }], { duration: 380 });

  /* 2. Indikator jurnal seimbang. getD/getK mengembalikan angka total debit/kredit */
  function balanceMeter(root, getD, getK, submit) {
    const w = el('div', 'kfx-meter', '<div class="kfx-mrow"><span>Debit</span><i><b class="d"></b></i><em class="vd"></em></div><div class="kfx-mrow"><span>Kredit</span><i><b class="k"></b></i><em class="vk"></em></div><div class="kfx-mst"></div>');
    root.appendChild(w);
    let prev = null;
    const update = () => {
      const d = +getD() || 0, k = +getK() || 0, m = Math.max(d, k, 1), ok = d > 0 && d === k;
      $('.d', w).style.transform = `scaleX(${d / m})`; $('.k', w).style.transform = `scaleX(${k / m})`;
      $('.vd', w).textContent = fmt(d); $('.vk', w).textContent = fmt(k);
      w.classList.toggle('ok', ok);
      $('.kfx-mst', w).innerHTML = ok ? CHECK + ' Seimbang' : (d || k) ? 'Selisih Rp' + fmt(Math.abs(d - k)) : 'Belum ada nilai';
      if (ok && prev === false) A(w, [{ scale: 1 }, { scale: 1.04 }, { scale: 1 }], { duration: 400, easing: SP });
      prev = ok; return ok;
    };
    const guard = e => {
      if (update()) return true;
      if (e && e.preventDefault) e.preventDefault();
      shake(submit || w);
      A($('.kfx-mst', w), [{ opacity: 1 }, { opacity: .2 }, { opacity: 1 }, { opacity: .2 }, { opacity: 1 }], { duration: 500 });
      return false;
    };
    update();
    return { update, guard, el: w };
  }

  /* 3. Tahapan status cucian */
  function stepper(root, steps = ['Diterima', 'Dicuci', 'Disetrika', 'Siap', 'Diambil'], cur = 0) {
    root.classList.add('kfx-stp'); root.style.setProperty('--n', steps.length);
    root.innerHTML = '<div class="kfx-line"><u></u></div>' + steps.map((s, i) => `<div class="kfx-st"><span class="kfx-dot">${i + 1}</span><small>${s}</small></div>`).join('');
    const fill = $('u', root), dots = $$('.kfx-dot', root);
    const set = i => {
      cur = i; fill.style.transform = `scaleX(${steps.length > 1 ? i / (steps.length - 1) : 1})`;
      dots.forEach((d, j) => {
        d.classList.toggle('done', j < i || (j === i && i === steps.length - 1));
        d.classList.toggle('now', j === i && i < steps.length - 1);
        d.innerHTML = (j < i || (j === i && i === steps.length - 1)) ? CHECK : j + 1;
        if (j === i) A(d, [{ scale: 1 }, { scale: 1.3 }, { scale: 1 }], { duration: 450, easing: SP });
      });
    };
    requestAnimationFrame(() => set(cur));
    return { set, next: () => set(Math.min(cur + 1, steps.length - 1)) };
  }

  /* 4. Antrean offline tersinkron. send(item,i) harus mengembalikan Promise */
  async function syncQueue(list, items, send) {
    list.innerHTML = '';
    const rows = items.map(it => {
      const r = el('div', 'kfx-q', '<span class="kfx-qi"></span>'), s = el('span'); s.textContent = it.label;
      r.appendChild(s); list.appendChild(r);
      A(r, [{ opacity: 0, transform: 'translateX(-12px)' }, { opacity: 1, transform: 'none' }], { duration: 300 });
      return r;
    });
    let ok = 0;
    for (let i = 0; i < items.length; i++) {
      const ic = $('.kfx-qi', rows[i]); ic.innerHTML = SPIN;
      try {
        await send(items[i], i);
        ic.innerHTML = CHECK; rows[i].classList.add('ok'); ok++;
        A(ic, [{ scale: 0 }, { scale: 1.3 }, { scale: 1 }], { duration: 350, easing: SP });
      } catch (e) { ic.textContent = '!'; rows[i].classList.add('err'); shake(rows[i]); }
    }
    return ok;
  }

  /* 5. Angka menggulung + kilatan hijau (naik) / merah (turun) */
  function roll(e, to, o = {}) {
    const f = o.format || (v => fmt(v)), from = e._v || 0;
    cancelAnimationFrame(e._r); e._v = to;
    const put = v => { e._last = f(v); e.textContent = e._last; };
    if (reduce) return put(to);
    const t0 = performance.now(), d = o.duration || 600;
    (function s(t) { const k = Math.min(1, (t - t0) / d); put(from + (to - from) * (1 - Math.pow(1 - k, 3))); if (k < 1) e._r = requestAnimationFrame(s); })(t0);
    if (from !== to && o.flash !== false) A(e, [{ color: to > from ? '#10b981' : '#f43f5e', offset: 0 }, { offset: 1 }], { duration: 800, easing: 'ease-out' });
  }
  function watch(e) {   // animasikan otomatis setiap teks angka di elemen ini berubah
    e._v = +e.textContent.replace(/[^\d-]/g, '') || 0;
    new MutationObserver(() => {
      const t = e.textContent; if (t === e._last) return;
      const pre = /^\s*Rp/.test(t) ? 'Rp ' : '';
      roll(e, +t.replace(/[^\d-]/g, '') || 0, { format: v => pre + fmt(v) });
    }).observe(e, { childList: true, characterData: true, subtree: true });
  }

  /* 6. Terbang melengkung dari elemen A ke B (posting jurnal, transfer stok, kasir) */
  function arcFly(from, to, glyph = '📦', o = {}) {
    if (reduce || !from || !to) return Promise.resolve();
    const a = from.getBoundingClientRect(), b = to.getBoundingClientRect(), g = el('div', 'kfx-fly');
    if (typeof glyph === 'string') g.textContent = glyph; else g.appendChild(glyph);
    document.body.appendChild(g);
    const sx = a.left + a.width / 2 - 20, sy = a.top + a.height / 2 - 20, ex = b.left + b.width / 2 - 20, ey = b.top + b.height / 2 - 20;
    const mx = (sx + ex) / 2, my = Math.min(sy, ey) - (o.lift || 90);
    return g.animate([
      { transform: `translate(${sx}px,${sy}px) scale(1)`, offset: 0, easing: 'cubic-bezier(.3,.7,.4,1)' },
      { transform: `translate(${mx}px,${my}px) scale(1.25) rotate(12deg)`, offset: .45, easing: 'cubic-bezier(.6,0,.9,.6)' },
      { transform: `translate(${ex}px,${ey}px) scale(.3)`, opacity: .6, offset: 1 }
    ], { duration: o.duration || 700, fill: 'forwards' }).finished.then(() => g.remove());
  }

  /* 7. Scan faktur: garis pemindai lalu kolom terisi satu per satu. pairs = [[inputEl, nilai], ...] */
  async function scan(box, ms = 1600) {
    box.classList.add('kfx-scan');
    const l = el('div', 'kfx-scanline'); box.appendChild(l);
    await A(l, [{ top: '0%' }, { top: 'calc(100% - 3px)' }, { top: '0%' }], { duration: ms, easing: 'ease-in-out' }).finished;
    l.remove(); box.classList.remove('kfx-scan');
  }
  async function fill(pairs) {
    for (const [e, v] of pairs) {
      if (e.focus) e.focus({ preventScroll: true });
      e.value = v; e.dispatchEvent(new Event('input', { bubbles: true }));
      A(e, [{ backgroundColor: 'rgba(250,204,21,.55)', offset: 0 }, { offset: 1 }], { duration: 900 });
      await wait(180);
    }
  }

  /* 8. Cap POSTED pada baris jurnal */
  function stamp(row, text = 'POSTED') {
    if (getComputedStyle(row).position === 'static') row.style.position = 'relative';
    const s = el('span', 'kfx-stamp'); s.textContent = text; row.appendChild(s);
    A(s, [{ opacity: 0, transform: 'scale(2.4) rotate(-18deg)' }, { opacity: 1, transform: 'scale(1) rotate(-8deg)' }], { duration: 380, easing: SP });
    return s;
  }

  /* 9. Gembok menutup (Tutup Buku). Pakai: await KomakFX.lockClose(); lalu lanjutkan proses */
  async function lockClose(text = 'Periode ditutup') {
    const o = el('div', 'kfx-lockov', '<svg viewBox="0 0 64 64" width="96" height="96"><path class="sh" d="M20 28V20a12 12 0 0124 0v8" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"/><rect x="12" y="28" width="40" height="28" rx="6" fill="currentColor"/></svg><p></p>');
    $('p', o).textContent = text; document.body.appendChild(o);
    A(o, [{ opacity: 0 }, { opacity: 1 }], { duration: 200 });
    A($('svg', o), [{ transform: 'scale(.6)' }, { transform: 'scale(1)' }], { duration: 500, easing: SP });
    A($('.sh', o), [{ transform: 'translateY(-9px)' }, { transform: 'translateY(0)' }], { duration: 450, delay: 250, easing: SP });
    await wait(1100);
    await A(o, [{ opacity: 1 }, { opacity: 0 }], { duration: 250, fill: 'forwards' }).finished;
    o.remove();
  }

  /* 10. Laporan: batang tumbuh dari nol ([data-kfx-bar] atau [data-kfx-bar="x"]), denyut hijau, grafik Chart.js */
  function growBars(root = document) {
    $$('[data-kfx-bar]', root).forEach((b, i) => {
      const x = b.dataset.kfxBar === 'x'; b.style.transformOrigin = x ? 'left' : 'bottom';
      A(b, [{ transform: x ? 'scaleX(0)' : 'scaleY(0)' }, { transform: 'none' }], { duration: 700, delay: i * 60, easing: OUT });
    });
  }
  const pulse = e => A(e, [{ boxShadow: '0 0 0 0 rgba(16,185,129,.55)' }, { boxShadow: '0 0 0 14px rgba(16,185,129,0)' }], { duration: 900 });
  const chartDefaults = () => { if (window.Chart) Chart.defaults.animation = { duration: 900, easing: 'easeOutQuart' }; };

  /* 11. Tombol simpan: spinner lalu centang (sukses) atau geleng (gagal) */
  const toastOk = () => { const t = $('#toast-container') && $('#toast-container').lastElementChild; return !t || /emerald/.test(t.className); };
  async function busy(btn, fn) {
    if (!btn) return fn();
    const h = btn.innerHTML; btn.style.minWidth = btn.offsetWidth + 'px'; btn.disabled = true; btn.innerHTML = SPIN;
    let ok = true, r;
    try { r = await fn(); ok = r === undefined ? toastOk() : r !== false; } catch (e) { ok = false; }
    btn.innerHTML = ok ? CHECK : '!';
    if (ok) A(btn, [{ scale: .9 }, { scale: 1.08 }, { scale: 1 }], { duration: 350, easing: SP }); else shake(btn);
    await wait(700);
    btn.innerHTML = h; btn.disabled = false; btn.style.minWidth = '';
    return r;
  }

  /* 12. Geser baris ke kiri (HP) untuk memicu aksi, misalnya hapus */
  function swipeRows(sel, cb, root = document) {
    if (!matchMedia('(pointer:coarse)').matches) return;
    let S = null;
    root.addEventListener('touchstart', e => { const r = e.target.closest(sel); if (r) S = { r, x: e.touches[0].clientX, y: e.touches[0].clientY, dx: 0, lock: null }; }, { passive: true });
    root.addEventListener('touchmove', e => {
      if (!S) return;
      const t = e.touches[0], dx = t.clientX - S.x, dy = t.clientY - S.y;
      if (S.lock === null && (Math.abs(dx) > 8 || Math.abs(dy) > 8)) S.lock = Math.abs(dx) > Math.abs(dy) * 1.5 ? 'x' : 'y';
      if (S.lock !== 'x') return;
      S.dx = Math.min(0, dx);
      S.r.style.transform = `translateX(${S.dx * .6}px)`;
      S.r.style.background = `linear-gradient(to left,rgba(244,63,94,${.28 * Math.min(1, -S.dx / 90)}),transparent 60%)`;
    }, { passive: true });
    root.addEventListener('touchend', () => {
      if (!S) return;
      const { r, dx } = S; S = null;
      r.style.transition = `transform .3s ${SP},background .3s`; r.style.transform = ''; r.style.background = '';
      setTimeout(() => r.style.transition = '', 320);
      if (dx < -90) cb(r);
    });
  }

  /* 13. Ganti cabang: konten keluar ke kiri, halaman baru masuk dari kanan */
  function wireBranch() {
    const o = window.switchOperatingBranch;
    if (typeof o === 'function') window.switchOperatingBranch = id => {
      sessionStorage.setItem('kfx-branch', '1');
      const a = A($('main'), [{ opacity: 1, transform: 'none' }, { opacity: .25, transform: 'translateX(-24px)' }], { duration: 260, fill: 'forwards', easing: 'ease-in' });
      setTimeout(() => a.cancel(), 1500);   // jaga-jaga bila ganti cabang gagal dan halaman tidak dimuat ulang
      return o(id);
    };
    if (sessionStorage.getItem('kfx-branch')) {
      sessionStorage.removeItem('kfx-branch');
      A($('main'), [{ opacity: 0, transform: 'translateX(24px)' }, { opacity: 1, transform: 'none' }], { duration: 450, easing: OUT });
    }
  }

  /* Pemasangan otomatis yang aman (dilewati bila elemen tidak ada di halaman) */
  function autoWire() {
    chartDefaults(); wireBranch(); growBars();
    $$('[data-kfx-roll]').forEach(watch);
    const cont = $('#coa-container');
    if (cont) swipeRows('tbody tr', r => { const b = $('button[onclick^="deleteAccount"]', r); if (b) b.click(); }, cont);
    [['submitAddAccount', '#add-account-form'], ['submitEditAccount', '#edit-account-form']].forEach(([n, f]) => {
      const o = window[n];
      if (typeof o === 'function') window[n] = e => busy((e && e.submitter) || $(f + ' [type=submit]'), () => o(e));
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', autoWire); else autoWire();

  window.KomakFX = { balanceMeter, stepper, syncQueue, roll, watch, arcFly, scan, fill, stamp, lockClose, growBars, pulse, busy, shake, swipeRows };
})();
