<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Painel') - NeoShop Vendedor</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  {{-- Reaproveita o admin.css --}}
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

  <style>
    /* ============ TEMA VERDE DO VENDEDOR ============ */
    :root {
      --admin-accent: #22c55e;
      --admin-accent-hover: #16a34a;
      --admin-accent-glow: rgba(34,197,94,0.4);
    }
    .admin-sidebar-brand-icon {
      background: linear-gradient(135deg, #22c55e, #16a34a) !important;
    }
    .admin-nav-link.active {
      background: rgba(34,197,94,0.12) !important;
    }
    .admin-nav-link.active::before {
      background: var(--admin-accent) !important;
    }
  </style>

  @stack('styles')
</head>
<body class="admin-body">

  @include('vendedor.partials.sidebar')

  <div class="admin-overlay" id="adminOverlay"></div>

  @include('vendedor.partials.topbar', ['title' => trim($__env->yieldContent('title'))])

  <div class="admin-content">
    <div class="admin-main">
      @yield('content')
    </div>
  </div>

  {{-- TOASTS --}}
  @if (session('success'))
    <div class="admin-toast" id="adminToast">
      <i class="bi bi-check-circle-fill"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif
  @if (session('error'))
    <div class="admin-toast danger" id="adminToast">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  <script>
    // Remove toast após 3s
    setTimeout(function () {
      var t = document.getElementById('adminToast');
      if (t) {
        t.style.transition = 'all .3s';
        t.style.opacity = '0';
        setTimeout(function () { t.remove(); }, 300);
      }
    }, 3000);

    // Sidebar mobile
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('adminOverlay');
    var toggle  = document.getElementById('adminToggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
      });
      overlay.addEventListener('click', function () {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
      });
    }

    // Toast helper global (usado pelos JS da view)
    function showToast(html, type) {
      var container = document.getElementById('toastContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
      }
      var el = document.createElement('div');
      el.className = 'neo-toast' + (type === 'error' ? ' toast-error' : '');
      el.innerHTML = (type === 'error'
        ? '<i class="bi bi-exclamation-triangle-fill" style="color:var(--admin-red)"></i>'
        : '<i class="bi bi-check-circle-fill"></i>') + '<span>' + html + '</span>';
      container.appendChild(el);
      setTimeout(function () {
        el.style.transition = 'all 0.3s';
        el.style.opacity = '0';
        el.style.transform = 'translateX(400px)';
        setTimeout(function () { el.remove(); }, 300);
      }, 2600);
    }
  </script>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

  @stack('scripts')
</body>
</html>