<header class="admin-topbar">
  <button class="admin-topbar-toggle" id="adminToggle">
    <i class="bi bi-list"></i>
  </button>

  <h1 class="admin-topbar-title">@yield('title', 'Painel')</h1>

  <nav class="admin-topbar-breadcrumb">
    <a href="{{ route('vendor.dashboard') }}">Vendedor</a>
    <span class="sep">/</span>
    <span class="current">@yield('title', 'Dashboard')</span>
  </nav>

  <div class="admin-topbar-actions">
    <button class="admin-topbar-btn" title="Notificações">
      <i class="bi bi-bell"></i>
    </button>
    <a href="{{ route('home') }}" target="_blank" class="admin-topbar-btn" title="Ver loja">
      <i class="bi bi-box-arrow-up-right"></i>
    </a>
  </div>
</header>