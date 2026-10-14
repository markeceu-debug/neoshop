{{-- ============================================================
     TOAST GLOBAL (lê flash messages da sessão)
     ============================================================ --}}
<div class="toast-container" id="toastContainer"></div>

<script>
(function () {
  // ---------- FLASH MESSAGES DO SERVIDOR ----------
  @if (session('success'))
    window.__flashToast = window.__flashToast || [];
    window.__flashToast.push({
      type: 'success',
      html: '<i class="bi bi-check-circle-fill"></i><span>{{ addslashes(session('success')) }}</span>'
    });
  @endif

  @if (session('error'))
    window.__flashToast = window.__flashToast || [];
    window.__flashToast.push({
      type: 'error',
      html: '<i class="bi bi-exclamation-triangle-fill" style="color:var(--danger)"></i><span>{{ addslashes(session('error')) }}</span>'
    });
  @endif

  @if (session('warning'))
    window.__flashToast = window.__flashToast || [];
    window.__flashToast.push({
      type: 'warning',
      html: '<i class="bi bi-exclamation-circle-fill" style="color:var(--warning)"></i><span>{{ addslashes(session('warning')) }}</span>'
    });
  @endif

  @if (session('info'))
    window.__flashToast = window.__flashToast || [];
    window.__flashToast.push({
      type: 'info',
      html: '<i class="bi bi-info-circle-fill" style="color:var(--accent-2)"></i><span>{{ addslashes(session('info')) }}</span>'
    });
  @endif

  // ---------- FUNÇÃO DE EXIBIR ----------
  function showToast(html, type) {
    const container = document.getElementById('toastContainer');
    if (! container) return;

    const el = document.createElement('div');
    el.className = 'neo-toast ' + (type === 'error' ? 'toast-error' : '') + (type === 'warning' ? ' toast-warning' : '');

    el.innerHTML = html;
    container.appendChild(el);

    setTimeout(function () {
      el.style.transition = 'all 0.3s';
      el.style.opacity = '0';
      el.style.transform = 'translateX(400px)';
      setTimeout(function () { el.remove(); }, 300);
    }, 4000);
  }

  // Expõe globalmente
  window.showToast = showToast;

  // Mostra os toasts do servidor quando o DOM estiver pronto
  document.addEventListener('DOMContentLoaded', function () {
    if (window.__flashToast && window.__flashToast.length) {
      window.__flashToast.forEach(function (t, i) {
        setTimeout(function () { showToast(t.html, t.type); }, i * 200);
      });
    }
  });
})();
</script>