@extends('layouts.admin')

@section('title', 'Zonas de Frete')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Zonas de Frete</h2>
      <p class="admin-page-sub">Motoboy local por município</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.shipping-zones.create') }}" class="admin-btn admin-btn-primary">
        <i class="bi bi-plus-lg"></i> Nova Zona
      </a>
    </div>
  </div>

  {{-- STATS --}}
  <div class="admin-stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:20px;">
    <div class="admin-stat stat-cyan">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Total de Zonas</span>
        <div class="admin-stat-icon"><i class="bi bi-truck"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="admin-stat stat-green">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Ativas</span>
        <div class="admin-stat-icon"><i class="bi bi-check-circle"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['active'] }}</div>
    </div>
    <div class="admin-stat stat-red">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Inativas</span>
        <div class="admin-stat-icon"><i class="bi bi-x-circle"></i></div>
      </div>
      <div class="admin-stat-value">{{ $stats['inactive'] }}</div>
    </div>
  </div>

  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <div class="admin-table-search">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por província ou município...">
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
      window.LaravelDataTables['shippingZonesTable'].search(this.value).draw();
    });

    document.addEventListener('change', function (e) {
      if (e.target && e.target.id === 'checkAll') {
        document.querySelectorAll('.row-check').forEach(c => c.checked = e.target.checked);
      }
    });

    function toggleZone(id) {
      fetch(`/admin/shipping-zones/${id}/toggle`, {
        method: 'PUT',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
      })
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success') {
          showToast('<i class="bi bi-check-circle-fill"></i><span>' + data.message + '</span>');
          window.LaravelDataTables['shippingZonesTable'].ajax.reload(null, false);
        }
      });
    }
  </script>
@endpush