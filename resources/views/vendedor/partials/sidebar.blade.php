<aside class="admin-sidebar" id="adminSidebar">
  <a href="{{ route('vendor.dashboard') }}" class="admin-sidebar-brand">
    <div class="admin-sidebar-brand-icon" style="background:linear-gradient(135deg,var(--admin-green),var(--admin-cyan));">
      <i class="bi bi-shop"></i>
    </div>
    <div class="admin-sidebar-brand-text">
      <span class="admin-sidebar-brand-name">NeoShop</span>
      <span class="admin-sidebar-brand-sub">Painel Vendedor</span>
    </div>
  </a>

  <nav class="admin-nav">

    <div class="admin-nav-section">
      <div class="admin-nav-title">Principal</div>
      <ul class="admin-nav-list">
        <li>
          <a href="{{ route('vendor.dashboard') }}"
            class="admin-nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
          </a>
        </li>
      </ul>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-title">Minha Loja</div>
      <ul class="admin-nav-list">
        <li>
          <a href="#" class="admin-nav-link">
            <i class="bi bi-box-seam"></i> Meus Produtos
          </a>
        </li>
        <li>
          <a href="{{ route('vendor.orders.index') }}"
            class="admin-nav-link {{ request()->routeIs('vendor.orders.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Meus Pedidos
            @php
            $pendingOrders = \App\Models\Order::where('seller_id', auth()->id())
            ->where('status', 'paid')
            ->count();
            @endphp
            @if ($pendingOrders > 0)
            <span class="admin-nav-badge warning">{{ $pendingOrders }}</span>
            @endif
          </a>
        </li>
        <li>
          <a href="{{ route('vendor.shipping.edit') }}"
            class="admin-nav-link {{ request()->routeIs('vendor.shipping.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Meu Frete
          </a>
        </li>
      </ul>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-title">Financeiro</div>
      <ul class="admin-nav-list">
        <li>
          <a href="#" class="admin-nav-link">
            <i class="bi bi-wallet2"></i> Minha Carteira
          </a>
        </li>
        <li>
          <a href="{{ route('vendor.withdrawals.index') }}"
            class="admin-nav-link {{ request()->routeIs('vendor.withdrawals.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-down-circle"></i> Saques
            @php
            $pendingWithdrawals = \App\Models\Withdrawal::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->count();
            @endphp
            @if ($pendingWithdrawals > 0)
            <span class="admin-nav-badge warning">{{ $pendingWithdrawals }}</span>
            @endif
          </a>
        </li>
      </ul>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-title">Sistema</div>
      <ul class="admin-nav-list">
        <li>
          <a href="{{ route('vendor.profile.edit') }}"
            class="admin-nav-link {{ request()->routeIs('vendor.profile.*') ? 'active' : '' }}">
            <i class="bi bi-person"></i> Meu Perfil
          </a>
        </li>
        <li>
          <a href="{{ route('home') }}" class="admin-nav-link" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> Ver Loja
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
        <div class="admin-sidebar-user-avatar"
          style="background:linear-gradient(135deg,var(--admin-green),var(--admin-cyan));">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="admin-sidebar-user-info">
          <span class="admin-sidebar-user-name">{{ auth()->user()->name }}</span>
          <span class="admin-sidebar-user-role">Vendedor</span>
        </div>
        <i class="bi bi-box-arrow-right" style="color:var(--admin-text-mut);"></i>
      </button>
    </form>
  </div>
</aside>