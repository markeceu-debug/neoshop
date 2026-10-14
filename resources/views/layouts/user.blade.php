<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Minha Conta') - NeoShop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
  @stack('styles')
</head>
<body class="admin-body">

  @include('user.partials.sidebar')
  <div class="admin-overlay" id="adminOverlay"></div>
  @include('user.partials.topbar', ['title' => trim($__env->yieldContent('title'))])

  <div class="admin-content">
    <div class="admin-main">
      @yield('content')
    </div>
  </div>

  {{-- TOAST via SESSION --}}
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
    setTimeout(function () {
      var t = document.getElementById('adminToast');
      if (t) { t.style.transition = 'all .3s'; t.style.opacity = '0'; setTimeout(function(){ t.remove(); }, 300); }
    }, 3000);

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
  </script>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>
</html>