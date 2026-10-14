@extends('layouts.user')

@section('title', 'Minha Conta')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Olá, {{ auth()->user()->name }}!</h2>
      <p class="admin-page-sub">Bem-vindo à sua conta</p>
    </div>
  </div>

  {{-- STATS --}}
  <div class="admin-stats-grid">
    <div class="admin-stat stat-purple">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Total de Pedidos</span>
        <div class="admin-stat-icon"><i class="bi bi-receipt"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['total_orders'] }}</div>
    </div>

    <div class="admin-stat stat-orange">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Aguardando</span>
        <div class="admin-stat-icon"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['pending_payment'] }}</div>
    </div>

    <div class="admin-stat stat-cyan">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Enviados</span>
        <div class="admin-stat-icon"><i class="bi bi-truck"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['shipped'] }}</div>
    </div>

    <div class="admin-stat stat-green">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Concluídos</span>
        <div class="admin-stat-icon"><i class="bi bi-trophy-fill"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['completed'] }}</div>
    </div>
  </div>

  {{-- PEDIDOS RECENTES --}}
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="bi bi-clock-history"></i> Pedidos Recentes</h3>
      <a href="{{ route('user.orders.index') }}" class="admin-btn admin-btn-secondary" style="font-size:12px; padding:6px 14px;">
        Ver todos
      </a>
    </div>
    <div class="admin-card-body">
      @if ($recentOrders->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:var(--admin-text-mut);">
          <i class="bi bi-receipt" style="font-size:48px; display:block; margin-bottom:12px; opacity:.3;"></i>
          <p style="margin:0 0 16px;">Você ainda não fez nenhum pedido</p>
          <a href="{{ route('shop.index') }}" class="admin-btn admin-btn-primary">
            <i class="bi bi-grid-3x3-gap"></i> Explorar produtos
          </a>
        </div>
      @else
        @foreach ($recentOrders as $order)
          <div style="display:grid; grid-template-columns:1fr auto auto; gap:16px; align-items:center; padding:14px 0; border-bottom:1px solid var(--admin-border-subtle);">
            <div>
              <a href="{{ route('user.orders.show', $order) }}"
                 style="font-family:monospace; font-weight:800; color:var(--admin-accent-2); text-decoration:none; font-size:14px;">
                #{{ $order->number }}
              </a>
              <div style="font-size:11px; color:var(--admin-text-mut); margin-top:4px;">
                {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->items->count() }} itens
              </div>
            </div>

            <div style="font-weight:800; color:var(--admin-text);">
              R$ {{ number_format($order->total, 2, ',', '.') }}
            </div>

            <a href="{{ route('user.orders.show', $order) }}" class="admin-row-btn">
              <i class="bi bi-chevron-right"></i>
            </a>
          </div>
        @endforeach
      @endif
    </div>
  </div>

@endsection