<header class="admin-topbar">
  <button class="admin-topbar-toggle" id="adminToggle">
    <i class="bi bi-list"></i>
  </button>

  <h1 class="admin-topbar-title">@yield('title', 'Minha Conta')</h1>

  <nav class="admin-topbar-breadcrumb">
    <a href="{{ route('user.dashboard') }}">Minha Conta</a>
    <span class="sep">/</span>
    <span class="current">@yield('title', 'Dashboard')</span>
  </nav>

  <div class="admin-topbar-actions">
    <a href="{{ route('cart.index') }}" class="admin-topbar-btn" title="Carrinho">
      <i class="bi bi-cart3"></i>
    </a>
    <a href="{{ route('home') }}" class="admin-topbar-btn" title="Ver loja">
      <i class="bi bi-box-arrow-up-right"></i>
    </a>
  </div>
</header>