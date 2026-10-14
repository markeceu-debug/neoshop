@extends('layouts.user')

@section('title', 'Meus Pedidos')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Meus Pedidos</h2>
      <p class="admin-page-sub">Acompanhe suas compras</p>
    </div>
  </div>

  <div class="admin-stats-grid">
    <div class="admin-stat stat-purple">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Total</span>
        <div class="admin-stat-icon"><i class="bi bi-receipt"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['total'] }}</div>
    </div>

    <div class="admin-stat stat-orange">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Aguardando</span>
        <div class="admin-stat-icon"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['pending'] }}</div>
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

  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <div class="admin-table-search">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por nº do pedido...">
      </div>
    </div>

    <div class="admin-table-wrap">
      {{ $dataTable->table(['class' => 'display', 'style' => 'width:100%']) }}
    </div>
  </div>

@endsection

@push('scripts')
  {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

  <script>
    document.getElementById('searchInput').addEventListener('input', function () {
      window.LaravelDataTables['userOrdersTable'].search(this.value).draw();
    });
  </script>
@endpush