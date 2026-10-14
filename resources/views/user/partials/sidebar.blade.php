<aside class="admin-sidebar" id="adminSidebar">
  <a href="{{ route('user.dashboard') }}" class="admin-sidebar-brand">
    <div class="admin-sidebar-brand-icon" style="background:linear-gradient(135deg,var(--admin-accent),var(--admin-cyan));">
      <i class="bi bi-person-circle"></i>
    </div>
    <div class="admin-sidebar-brand-text">
      <span class="admin-sidebar-brand-name">NeoShop</span>
      <span class="admin-sidebar-brand-sub">Minha Conta</span>
    </div>
  </a>

  <nav class="admin-nav">

    <div class="admin-nav-section">
      <div class="admin-nav-title">Principal</div>
      <ul class="admin-nav-list">
        <li>
          <a href="{{ route('user.dashboard') }}"
             class="admin-nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
          </a>
        </li>
      </ul>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-title">Minhas Compras</div>
      <ul class="admin-nav-list">
        <li>
          <a href="{{ route('user.orders.index') }}"
             class="admin-nav-link {{ request()->routeIs('user.orders.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Meus Pedidos
          </a>
        </li>
        <li>
          <li>
  <a href="#" class="admin-nav-link">
    <i class="bi bi-exclamation-triangle"></i> Reclamações
    @php
      $openDisputes = \App\Models\Dispute::where('opened_by', auth()->id())
          ->whereIn('status', ['open', 'under_review'])
          ->count();
    @endphp
    @if ($openDisputes > 0)
      <span class="admin-nav-badge warning">{{ $openDisputes }}</span>
    @endif
  </a>
</li>
          <a href="#" class="admin-nav-link">
            <i class="bi bi-heart"></i> Favoritos
          </a>
        </li>
      </ul>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-title">Conta</div>
      <ul class="admin-nav-list">
        <li>
          <a href="{{ route('user.profile.edit') }}"
             class="admin-nav-link {{ request()->routeIs('user.profile.*') ? 'active' : '' }}">
            <i class="bi bi-person"></i> Meu Perfil
          </a>
        </li>
        <li>
          <a href="#" class="admin-nav-link">
            <i class="bi bi-geo-alt"></i> Endereços
          </a>
        </li>
      </ul>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-title">Sistema</div>
      <ul class="admin-nav-list">
        <li>
          <a href="{{ route('home') }}" class="admin-nav-link">
            <i class="bi bi-box-arrow-up-right"></i> Ir à Loja
          </a>
        </li>
      </ul>
    </div>

  </nav>

  <div class="admin-sidebar-footer">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit"
              class="admin-sidebar-user"
              style="width:100%; border:none; background:inherit; cursor:pointer; text-align:left;">
        <div class="admin-sidebar-user-avatar">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="admin-sidebar-user-info">
          <span class="admin-sidebar-user-name">{{ auth()->user()->name }}</span>
          <span class="admin-sidebar-user-role">Cliente</span>
        </div>
        <i class="bi bi-box-arrow-right" style="color:var(--admin-text-mut);"></i>
      </button>
    </form>
  </div>
</aside>