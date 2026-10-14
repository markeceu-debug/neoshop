@extends('layouts.admin')

@section('title', 'Reclamações')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Reclamações</h2>
      <p class="admin-page-sub">Mediar disputas entre clientes e vendedores</p>
    </div>
  </div>

  {{-- STATS --}}
  <div class="admin-stats-grid">
    <div class="admin-stat stat-orange">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Abertas</span>
        <div class="admin-stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['open'] }}</div>
      <div class="admin-stat-trend"><span>Aguardando análise</span></div>
    </div>

    <div class="admin-stat stat-cyan">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Em Análise</span>
        <div class="admin-stat-icon"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['under_review'] }}</div>
    </div>

    <div class="admin-stat stat-green">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Resolvidas</span>
        <div class="admin-stat-icon"><i class="bi bi-check-circle"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['resolved'] }}</div>
    </div>

    <div class="admin-stat stat-purple">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Total</span>
        <div class="admin-stat-icon"><i class="bi bi-list-ul"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['total'] }}</div>
    </div>
  </div>

  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <div class="admin-table-search">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por cliente ou pedido...">
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
      window.LaravelDataTables['disputesTable'].search(this.value).draw();
    });
  </script>
@endpush