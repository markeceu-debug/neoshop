 <!-- SIDEBAR -->
 <aside class="admin-sidebar" id="adminSidebar">
   <a href="dashboard.html" class="admin-sidebar-brand">
     <div class="admin-sidebar-brand-icon"><i class="bi bi-lightning-charge-fill"></i></div>
     <div class="admin-sidebar-brand-text">
       <span class="admin-sidebar-brand-name">NeoShop</span>
       <span class="admin-sidebar-brand-sub">Admin Panel</span>
     </div>
   </a>

   <nav class="admin-nav">
     <div class="admin-nav-section">
       <div class="admin-nav-title">Principal</div>
       <ul class="admin-nav-list">
         <li><a href="dashboard.html" class="admin-nav-link active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
         <li><a href="#" class="admin-nav-link"><i class="bi bi-graph-up-arrow"></i> Relatórios</a></li>
         <li><a href="#" class="admin-nav-link"><i class="bi bi-bar-chart-line"></i> Analytics <span class="admin-nav-badge">New</span></a></li>
       </ul>
     </div>

     <div class="admin-nav-section">
       <div class="admin-nav-title">Gestão</div>
       <ul class="admin-nav-list">
         <li>
           <a href="{{ route('admin.categories.index') }}"
             class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
             <i class="bi bi-tags"></i> Categorias
           </a>
         </li>
         <li><a href="{{ route('admin.products.index') }}" class="admin-nav-link"><i class="bi bi-box-seam"></i> Produtos <span class="admin-nav-badge">2.4k</span></a></li>
         <li>
           <a href="{{ route('admin.orders.index') }}"
             class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
             <i class="bi bi-receipt"></i> Pedidos
             @php
             $pending = \App\Models\Order::whereIn('status', ['payment_under_review', 'awaiting_payment'])->count();
             @endphp
             @if ($pending > 0)
             <span class="admin-nav-badge danger">{{ $pending }}</span>
             @endif
           </a>
         </li>
         <li>
           <a href="{{ route('admin.shipping-zones.index') }}"
             class="admin-nav-link {{ request()->routeIs('admin.shipping-zones.*') ? 'active' : '' }}">
             <i class="bi bi-truck"></i> Zonas de Frete
           </a>
         </li>
         <li><a href="clientes.html" class="admin-nav-link"><i class="bi bi-people"></i> Clientes <span class="admin-nav-badge success">15M</span></a></li>
         <li><a href="vendedores.html" class="admin-nav-link"><i class="bi bi-shop"></i> Vendedores <span class="admin-nav-badge">280k</span></a></li>
       </ul>
       <li>
         <a href="{{ route('admin.stock.movements.index') }}"
           class="admin-nav-link {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">
           <i class="bi bi-box-seam"></i> Estoque
         </a>
       </li>
       <li>
         <a href="{{ route('admin.coupons.index') }}"
           class="admin-nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
           <i class="bi bi-ticket-perforated"></i> Cupons
         </a>
       </li>
       <li>
         <a href="{{ route('admin.promotions.index') }}"
           class="admin-nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
           <i class="bi bi-megaphone"></i> Promoções
         </a>
       </li>
       <li>
  <a href="{{ route('admin.disputes.index') }}"
     class="admin-nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
    <i class="bi bi-exclamation-triangle"></i> Reclamações
    @php
      $openDisputes = \App\Models\Dispute::whereIn('status', ['open', 'under_review'])->count();
    @endphp
    @if ($openDisputes > 0)
      <span class="admin-nav-badge danger">{{ $openDisputes }}</span>
    @endif
  </a>
</li>
     </div>

     <div class="admin-nav-section">
       <div class="admin-nav-title">Financeiro</div>
       <ul class="admin-nav-list">
         <li><a href="#" class="admin-nav-link"><i class="bi bi-cash-coin"></i> Transações</a></li>
         <li>
           <a href="{{ route('admin.withdrawals.index') }}"
             class="admin-nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
             <i class="bi bi-wallet2"></i> Saques
             @php
             $pendingWithdrawals = \App\Models\Withdrawal::where('status', 'pending')->count();
             @endphp
             @if ($pendingWithdrawals > 0)
             <span class="admin-nav-badge danger">{{ $pendingWithdrawals }}</span>
             @endif
           </a>
         </li>
         <li><a href="#" class="admin-nav-link"><i class="bi bi-credit-card"></i> Pagamentos</a></li>
       </ul>
     </div>

     <div class="admin-nav-section">
       <div class="admin-nav-title">Sistema</div>
       <ul class="admin-nav-list">
        <li>
  <a href="{{ route('admin.settings.edit') }}"
     class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <i class="bi bi-gear"></i> Configurações
    <span class="admin-nav-badge success" style="margin-left:auto; font-size:10px;">
      {{ (int) \App\Models\Setting::get('commission_rate', 10) }}%
    </span>
  </a>
</li>
         <li><a href="#" class="admin-nav-link"><i class="bi bi-shield-lock"></i> Segurança</a></li>
         <li><a href="../index.html" class="admin-nav-link"><i class="bi bi-box-arrow-up-right"></i> Ver Loja</a></li>
         <li>
           <a href="{{ route('admin.profile.show') }}"
             class="admin-nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
             <i class="bi bi-person-circle"></i> Meu Perfil
           </a>
         </li>
       </ul>
     </div>
   </nav>

   <div class="admin-sidebar-footer">
     <div style="display:flex; gap:6px;">
       <a href="{{ route('admin.profile.show') }}"
         class="admin-sidebar-user"
         style="flex:1; min-width:0; {{ request()->routeIs('admin.profile.*') ? 'border-color:var(--admin-accent);' : '' }}">
         <div class="admin-sidebar-user-avatar" style="background-image:url('{{ auth()->user()->avatar_url }}'); background-size:cover; background-position:center;">
           @if (!auth()->user()->avatar)
           {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
           @endif
         </div>
         <div class="admin-sidebar-user-info">
           <span class="admin-sidebar-user-name">{{ auth()->user()->name }}</span>
           <span class="admin-sidebar-user-role">{{ ucfirst(auth()->user()->role) }}</span>
         </div>
       </a>

       <form method="POST" action="{{ route('logout') }}" style="display:flex;">
         @csrf
         <button type="submit" class="admin-row-btn" title="Sair" style="width:44px; height:auto;">
           <i class="bi bi-box-arrow-right"></i>
         </button>
       </form>
     </div>
   </div>
 </aside>