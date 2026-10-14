@extends('layouts.admin')

@section('title', 'Saques')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Saques</h2>
      <p class="admin-page-sub">Aprovar e pagar solicitações de saque</p>
    </div>
  </div>

  {{-- STATS --}}
  <div class="admin-stats-grid">
    <div class="admin-stat stat-orange">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Pendentes</span>
        <div class="admin-stat-icon"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['pending'] }}</div>
      <div class="admin-stat-trend">
        <span>Aguardando análise</span>
      </div>
    </div>

    <div class="admin-stat stat-cyan">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Aprovados</span>
        <div class="admin-stat-icon"><i class="bi bi-check-circle"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['approved'] }}</div>
      <div class="admin-stat-trend">
        <span>Aguardando pagamento</span>
      </div>
    </div>

    <div class="admin-stat stat-green">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Pagos</span>
        <div class="admin-stat-icon"><i class="bi bi-trophy-fill"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['paid'] }}</div>
      <div class="admin-stat-trend">
        <span>Total de saques</span>
      </div>
    </div>

    <div class="admin-stat stat-purple">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Total Pago</span>
        <div class="admin-stat-icon"><i class="bi bi-cash-coin"></i></div>
      </div>
      <div class="admin-stat-value">
        R$ {{ number_format($stats['total_paid'], 2, ',', '.') }}
      </div>
    </div>
  </div>

  {{-- TABELA --}}
  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <div class="admin-table-search">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por vendedor ou valor...">
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
      window.LaravelDataTables['withdrawalsTable'].search(this.value).draw();
    });
  </script>
@endpush