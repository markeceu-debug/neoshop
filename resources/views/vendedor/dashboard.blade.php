@extends('layouts.vendor')

@section('title', 'Dashboard')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Olá, {{ auth()->user()->name }}!</h2>
      <p class="admin-page-sub">Bem-vindo ao seu painel de vendedor</p>
    </div>
  </div>

  {{-- STATS --}}
  <div class="admin-stats-grid">
    <div class="admin-stat stat-green">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Vendas Hoje</span>
        <div class="admin-stat-icon"><i class="bi bi-cart-check"></i></div>
      </div>
      <div class="admin-stat-value">0</div>
      <div class="admin-stat-trend">
        <i class="bi bi-arrow-up-right"></i> Comece a vender!
      </div>
    </div>

    <div class="admin-stat stat-cyan">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Saldo Disponível</span>
        <div class="admin-stat-icon"><i class="bi bi-wallet2"></i></div>
      </div>
      <div class="admin-stat-value">R$ 0,00</div>
      <div class="admin-stat-trend">
        <span>Pronto para saque</span>
      </div>
    </div>

    <div class="admin-stat stat-orange">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Em Garantia</span>
        <div class="admin-stat-icon"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="admin-stat-value">R$ 0,00</div>
      <div class="admin-stat-trend">
        <span>Libera em 7 dias</span>
      </div>
    </div>

    <div class="admin-stat stat-purple">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Produtos Ativos</span>
        <div class="admin-stat-icon"><i class="bi bi-box-seam"></i></div>
      </div>
      <div class="admin-stat-value">0</div>
      <div class="admin-stat-trend">
        <span>Cadastre seu primeiro</span>
      </div>
    </div>
  </div>

  {{-- AÇÕES RÁPIDAS --}}
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="bi bi-lightning-charge-fill"></i> Ações Rápidas</h3>
    </div>
    <div class="admin-card-body">
      <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">

        <a href="{{ route('vendor.shipping.edit') }}"
           style="display:flex; flex-direction:column; align-items:center; gap:12px; padding:24px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); text-decoration:none; transition:.15s;">
          <i class="bi bi-truck" style="font-size:32px; color:var(--admin-accent);"></i>
          <strong style="color:var(--admin-text); font-size:13px;">Configurar Frete</strong>
          <span style="color:var(--admin-text-mut); font-size:11px; text-align:center;">Defina o valor da entrega por município</span>
        </a>

        <a href="#"
           style="display:flex; flex-direction:column; align-items:center; gap:12px; padding:24px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); text-decoration:none; transition:.15s;">
          <i class="bi bi-plus-circle" style="font-size:32px; color:var(--admin-green);"></i>
          <strong style="color:var(--admin-text); font-size:13px;">Novo Produto</strong>
          <span style="color:var(--admin-text-mut); font-size:11px; text-align:center;">Adicione um produto ao catálogo</span>
        </a>

        <a href="#"
           style="display:flex; flex-direction:column; align-items:center; gap:12px; padding:24px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); text-decoration:none; transition:.15s;">
          <i class="bi bi-wallet2" style="font-size:32px; color:var(--admin-cyan);"></i>
          <strong style="color:var(--admin-text); font-size:13px;">Ver Carteira</strong>
          <span style="color:var(--admin-text-mut); font-size:11px; text-align:center;">Saldo e histórico de vendas</span>
        </a>

      </div>
    </div>
  </div>

@endsection