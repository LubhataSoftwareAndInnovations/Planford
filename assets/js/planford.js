/* ============================================================
   Planford JS v0.1 — by Lubhata
   ============================================================ */
'use strict';

// ── Theme ───────────────────────────────────────────────────
const Theme = {
  init() {
    const saved = localStorage.getItem('pf_theme') || 'light';
    this.apply(saved);
  },
  apply(t) {
    document.documentElement.setAttribute('data-theme', t);
    localStorage.setItem('pf_theme', t);
    const btn = document.getElementById('themeToggle');
    if (btn) btn.innerHTML = t === 'dark'
      ? '<i class="bi bi-sun-fill"></i>'
      : '<i class="bi bi-moon-fill"></i>';
  },
  toggle() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    this.apply(current === 'dark' ? 'light' : 'dark');
  }
};
Theme.init();

// ── Dropdowns ───────────────────────────────────────────────
document.addEventListener('click', e => {
  const toggle = e.target.closest('[data-dropdown]');
  // Close all
  document.querySelectorAll('.pf-dropdown.open').forEach(d => {
    if (!d.contains(toggle)) d.classList.remove('open');
  });
  if (toggle) {
    const dd = toggle.closest('.pf-dropdown');
    if (dd) dd.classList.toggle('open');
  }
});

// ── Modals ──────────────────────────────────────────────────
const Modal = {
  open(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
  },
  close(id) {
    const el = id ? document.getElementById(id) : document.querySelector('.pf-overlay.open');
    if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
  }
};

document.addEventListener('click', e => {
  if (e.target.matches('[data-modal-open]')) Modal.open(e.target.dataset.modalOpen);
  if (e.target.matches('[data-modal-close]') || e.target.matches('.pf-overlay')) Modal.close();
  if (e.target.matches('.pf-modal-close')) Modal.close();
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') Modal.close();
});

// ── Sidebar mobile toggle ───────────────────────────────────
document.addEventListener('click', e => {
  if (e.target.matches('#sidebarToggle') || e.target.closest('#sidebarToggle')) {
    document.querySelector('.pf-sidebar')?.classList.toggle('open');
  }
});

// ── Tabs ────────────────────────────────────────────────────
document.querySelectorAll('[data-tab]').forEach(tab => {
  tab.addEventListener('click', e => {
    e.preventDefault();
    const target = tab.dataset.tab;
    const container = tab.closest('[data-tabs-container]') || document;
    container.querySelectorAll('[data-tab]').forEach(t => t.classList.remove('active'));
    container.querySelectorAll('[data-tab-panel]').forEach(p => p.style.display = 'none');
    tab.classList.add('active');
    const panel = container.querySelector(`[data-tab-panel="${target}"]`);
    if (panel) panel.style.display = '';
  });
});

// ── Toast notifications ─────────────────────────────────────
const Toast = {
  container: null,
  init() {
    this.container = document.createElement('div');
    this.container.style.cssText = `
      position:fixed;bottom:24px;right:24px;z-index:9999;
      display:flex;flex-direction:column;gap:10px;pointer-events:none;
    `;
    document.body.appendChild(this.container);
  },
  show(msg, type = 'info', duration = 4000) {
    const colors = {
      success: '#22c55e', danger: '#ef4444',
      warning: '#f59e0b', info: '#6366f1'
    };
    const icons = {
      success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill',
      warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill'
    };
    const el = document.createElement('div');
    el.style.cssText = `
      background:var(--pf-surface);border:1px solid var(--pf-border);
      border-left:4px solid ${colors[type]};
      border-radius:10px;padding:14px 18px;
      display:flex;align-items:center;gap:10px;
      box-shadow:0 8px 24px rgba(0,0,0,.15);
      pointer-events:all;min-width:260px;max-width:360px;
      animation:fadeIn .3s ease;
      font-size:13.5px;color:var(--pf-text);font-family:var(--pf-font);
    `;
    el.innerHTML = `
      <i class="bi ${icons[type]}" style="color:${colors[type]};font-size:18px;flex-shrink:0"></i>
      <span style="flex:1">${msg}</span>
      <button onclick="this.parentElement.remove()" style="
        border:none;background:none;cursor:pointer;color:var(--pf-text-3);font-size:16px;
        padding:0;line-height:1;
      ">×</button>
    `;
    this.container.appendChild(el);
    if (duration) setTimeout(() => el.style.opacity === '' && el.remove(), duration);
    return el;
  }
};
Toast.init();

// ── Progress slider with live preview ──────────────────────
document.querySelectorAll('[data-progress-slider]').forEach(slider => {
  const display = document.getElementById(slider.dataset.progressDisplay);
  slider.addEventListener('input', () => {
    if (display) display.textContent = slider.value + '%';
  });
});

// ── AJAX helper ─────────────────────────────────────────────
const Api = {
  async post(url, data = {}, isForm = false) {
    const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
    const opts = {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrf }
    };
    if (isForm) {
      opts.body = data; // FormData
    } else {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify({ ...data, _csrf: csrf });
    }
    const res = await fetch(url, opts);
    return res.json();
  },

  async get(url) {
    const res = await fetch(url);
    return res.json();
  }
};

// ── Confirm delete ───────────────────────────────────────────
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-confirm]');
  if (btn) {
    e.preventDefault();
    const msg = btn.dataset.confirm || 'Are you sure?';
    if (confirm(msg)) {
      const form = btn.closest('form');
      if (form) form.submit();
      else if (btn.href) window.location = btn.href;
    }
  }
});

// ── Mark notification read ───────────────────────────────────
document.querySelectorAll('[data-notif-id]').forEach(el => {
  el.addEventListener('click', () => {
    Api.post(PF.url + '/api/notifications/read', { id: el.dataset.notifId });
  });
});

// ── Chart helpers (using Chart.js if loaded) ─────────────────
const Charts = {
  donut(canvasId, labels, data, colors) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || typeof Chart === 'undefined') return;
    return new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor: 'var(--pf-surface)' }]
      },
      options: {
        cutout: '70%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, color: 'var(--pf-text-2)', font: { size: 12 } } } },
        responsive: true,
        maintainAspectRatio: true,
      }
    });
  },

  bar(canvasId, labels, datasets) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || typeof Chart === 'undefined') return;
    return new Chart(ctx, {
      type: 'bar',
      data: { labels, datasets },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: 'var(--pf-text-2)' } } },
        scales: {
          x: { ticks: { color: 'var(--pf-text-3)' }, grid: { color: 'var(--pf-border)' } },
          y: { ticks: { color: 'var(--pf-text-3)' }, grid: { color: 'var(--pf-border)' } }
        }
      }
    });
  },

  line(canvasId, labels, datasets) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || typeof Chart === 'undefined') return;
    return new Chart(ctx, {
      type: 'line',
      data: { labels, datasets },
      options: {
        responsive: true,
        tension: 0.4,
        plugins: { legend: { labels: { color: 'var(--pf-text-2)' } } },
        scales: {
          x: { ticks: { color: 'var(--pf-text-3)' }, grid: { color: 'var(--pf-border)' } },
          y: { ticks: { color: 'var(--pf-text-3)' }, grid: { color: 'var(--pf-border)' } }
        }
      }
    });
  }
};

// ── Gantt renderer ───────────────────────────────────────────
function renderGantt(containerId, tasks, startDate, endDate) {
  const container = document.getElementById(containerId);
  if (!container) return;

  const start  = new Date(startDate);
  const end    = new Date(endDate);
  const totalDays = (end - start) / 86400000;

  container.innerHTML = '';

  tasks.forEach(t => {
    const row  = document.createElement('div');
    row.className = 'pf-gantt-row';

    const label = document.createElement('div');
    label.className = 'pf-gantt-label truncate';
    label.title = t.title;
    label.textContent = t.title;

    const track = document.createElement('div');
    track.className = 'pf-gantt-track';

    if (t.start && t.end) {
      const tStart = new Date(t.start);
      const tEnd   = new Date(t.end);
      const left   = Math.max(0, (tStart - start) / 86400000 / totalDays * 100);
      const width  = Math.max(1, (tEnd - tStart) / 86400000 / totalDays * 100);

      const bar = document.createElement('div');
      bar.className = 'pf-gantt-bar';
      bar.style.left  = left + '%';
      bar.style.width = width + '%';
      bar.style.background = t.color || 'var(--pf-indigo)';
      bar.title = `${t.title}: ${t.start} → ${t.end}`;
      bar.textContent = t.title;
      track.appendChild(bar);
    }

    row.appendChild(label);
    row.appendChild(track);
    container.appendChild(row);
  });
}

// ── Search (live, debounced) ─────────────────────────────────
let searchTimeout;
const searchInput = document.getElementById('globalSearch');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      const q = searchInput.value.trim();
      if (q.length >= 2) window.location = PF.url + '/search?q=' + encodeURIComponent(q);
    }, 400);
  });
}

// ── Global PF config (set per-page) ─────────────────────────
window.PF = window.PF || { url: '' };

// ── Auto-dismiss flash messages ──────────────────────────────
document.querySelectorAll('.pf-flash-auto').forEach(el => {
  setTimeout(() => {
    el.style.opacity = '0';
    el.style.transition = 'opacity .4s';
    setTimeout(() => el.remove(), 400);
  }, 4000);
});

// ── Copy to clipboard ────────────────────────────────────────
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-copy]');
  if (btn) {
    navigator.clipboard.writeText(btn.dataset.copy).then(() => {
      Toast.show('Copied to clipboard', 'success', 2000);
    });
  }
});

// Expose globals
window.Modal  = Modal;
window.Toast  = Toast;
window.Charts = Charts;
window.Theme  = Theme;
window.renderGantt = renderGantt;
