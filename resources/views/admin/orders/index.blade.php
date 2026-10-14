@extends('layouts.admin')

@section('title', 'Pedidos')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Gerenciar Pedidos</h2>
      <p class="admin-page-sub">Aprovar pagamentos e acompanhar entregas</p>
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- FILTROS RÁPIDOS                                              --}}
  {{-- ============================================================ --}}
  <div class="admin-table-card" style="margin-bottom:20px;">
    <div class="admin-table-toolbar" style="gap:8px;">
      @php
        $filters = [
            ''                      => ['label' => 'Todos',         'icon' => 'list-ul'],
            'payment_under_review'  => ['label' => 'Em Análise',    'icon' => 'hourglass-split'],
            'paid'                  => ['label' => 'Pagos',         'icon' => 'check-circle-fill'],
            'shipped'               => ['label' => 'Enviados',      'icon' => 'truck'],
            'delivered'             => ['label' => 'Entregues',     'icon' => 'box-seam'],
            'completed'             => ['label' => 'Concluídos',    'icon' => 'trophy-fill'],
            'canceled'              => ['label' => 'Cancelados',    'icon' => 'x-circle'],
          ];
        $current = $currentStatus ?? '';
      @endphp

      @foreach ($filters as $value => $meta)
        <a href="{{ $value ? route('admin.orders.index', ['status' => $value]) : route('admin.orders.index') }}"
           class="admin-btn {{ $current === $value ? 'admin-btn-primary' : 'admin-btn-secondary' }}"
           style="padding:6px 14px; font-size:12px;">
          <i class="bi bi-{{ $meta['icon'] }}"></i> {{ $meta['label'] }}
        </a>
      @endforeach
    </div>
  </div>

  {{-- ============================================================ --}}
  {{-- TABELA                                                       --}}
  {{-- ============================================================ --}}
  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <div class="admin-table-search">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por nº, cliente ou vendedor...">
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
    // Busca local (DataTable)
    document.getElementById('searchInput').addEventListener('input', function () {
      window.LaravelDataTables['ordersTable'].search(this.value).draw();
    });

    // Check all
    document.addEventListener('change', function (e) {
      if (e.target && e.target.id === 'checkAll') {
        document.querySelectorAll('.row-check').forEach(c => c.checked = e.target.checked);
      }
    });
  </script>
@endpush