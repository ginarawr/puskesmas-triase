/* Sistem Triase Puskesmas – JavaScript ringan tanpa dependensi. */
(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
  const csrf = () => $('meta[name="csrf-token"]')?.content ?? '';

  /* Tampilkan / sembunyikan kata sandi */
  document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-toggle-password]');
    if (toggle) {
      const input = toggle.parentElement.querySelector('input');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggle.classList.toggle('is-on', show);
      toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    }

    if (e.target.closest('[data-forgot]')) {
      e.preventDefault();
      alert('Hubungi administrator puskesmas untuk mengatur ulang kata sandi.');
    }

    const close = e.target.closest('[data-toast-close]');
    if (close) close.closest('[data-toast]')?.remove();

    const speak = e.target.closest('[data-speak]');
    if (speak) announce(speak.closest('[data-toast]')?.dataset.announce);
  });

  /* Suara panggilan pasien (Web Speech API, bahasa Indonesia bila tersedia) */
  function announce(text) {
    if (!text || !('speechSynthesis' in window)) return;
    window.speechSynthesis.cancel();
    const u = new SpeechSynthesisUtterance(text);
    u.lang = 'id-ID';
    u.rate = 0.9;
    window.speechSynthesis.speak(u);
  }
  const toast = $('[data-toast][data-announce]');
  if (toast) announce(toast.dataset.announce);

  /* Pesan sukses hilang sendiri setelah 8 detik (pesan error tetap) */
  $$('[data-toast].toast-ok:not([data-announce])').forEach((t) => setTimeout(() => t.remove(), 8000));

  /* Pembaruan otomatis dashboard: ambil halaman yang sama, tukar area [data-live] */
  if ($('[data-live]')) {
    const seconds = Number($('meta[name="refresh-seconds"]')?.content) || 10;
    const setOnline = (ok) => $$('[data-live-indicator]').forEach((el) => {
      el.classList.toggle('is-offline', !ok);
      const label = $('[data-live-text]', el);
      if (label && !ok) label.textContent = 'Koneksi terputus, mencoba lagi…';
    });

    setInterval(async () => {
      if (document.hidden) return;
      try {
        const res = await fetch(location.href, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        if (!res.ok) throw new Error(res.status);
        const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
        if (!doc.querySelector('[data-live]')) { location.reload(); return; } // sesi berakhir
        $$('[data-live]').forEach((el) => {
          const next = doc.querySelector(`[data-live="${el.dataset.live}"]`);
          if (next && next.innerHTML !== el.innerHTML) el.innerHTML = next.innerHTML;
        });
        setOnline(true);
      } catch (_) {
        setOnline(false);
      }
    }, seconds * 1000);
  }

  /* Form pasien baru: evaluasi prioritas langsung dari server (satu sumber aturan) */
  const form = $('[data-evaluate-url]');
  const box = $('[data-eval]');
  if (form && box) {
    let timer;
    const outcomeBadge = $('[data-eval-outcome]', box);
    const render = (r) => {
      box.dataset.tone = r.priority;
      $('[data-eval-title]', box).textContent = r.headline;
      $('[data-eval-route]', box).textContent = r.route;
      $('[data-eval-triggers]', box).textContent = r.triggers.length
        ? 'Nilai pemicu utama: ' + r.triggers.join(' • ') : 'Tidak ada nilai pemicu.';
      outcomeBadge.textContent = 'Outcome = ' + r.outcome;
      outcomeBadge.className = 'badge ' + (r.outcome ? 'badge-danger' : 'badge-ok');
    };
    const hasInput = () => ['glucose', 'diastolic_bp', 'bmi', 'dpf', 'age', 'systolic_bp']
      .some((n) => form.elements[n]?.value !== '');

    const run = () => {
      clearTimeout(timer);
      timer = setTimeout(async () => {
        if (!hasInput()) return;
        try {
          const res = await fetch(form.dataset.evaluateUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: new FormData(form),
          });
          if (res.ok) render(await res.json());
        } catch (_) { /* abaikan; evaluasi final tetap dihitung saat disimpan */ }
      }, 300);
    };
    form.addEventListener('input', run);
    run();
  }

  /* Resep & tindakan: tambah / hapus baris */
  const rx = $('[data-rx]');
  if (rx) {
    const list = $('[data-rx-list]', rx);
    const tpl = $('[data-rx-template]', rx);
    const count = $('[data-rx-count]', rx);
    const update = () => {
      const filled = $$('input[type="text"]', list).filter((i) => i.value.trim() !== '').length;
      count.textContent = `${filled} item tercatat`;
    };
    rx.addEventListener('click', (e) => {
      if (e.target.closest('[data-rx-add]')) {
        const html = tpl.innerHTML.replaceAll('__i__', Date.now());
        list.insertAdjacentHTML('beforeend', html);
        list.lastElementChild.querySelector('input')?.focus();
      }
      const remove = e.target.closest('[data-rx-remove]');
      if (remove) { remove.closest('.rx-row').remove(); update(); }
    });
    rx.addEventListener('input', update);
  }
})();
