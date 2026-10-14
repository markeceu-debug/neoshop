@extends('layouts.admin')

@section('title', 'Cupons')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Gerenciar Cupons</h2>
      <p class="admin-page-sub">Crie e gerencie cupons de desconto</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.coupons.create') }}" class="admin-btn admin-btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Cupom
      </a>
    </div>
  </div>

  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <div class="admin-table-search">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar por código ou nome...">
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
      window.LaravelDataTables['couponsTable'].search(this.value).draw();
    });

    document.addEventListener('change', function (e) {
      if (e.target && e.target.id === 'checkAll') {
        document.querySelectorAll('.row-check').forEach(c => c.checked = e.target.checked);
      }
    });
  </script>
@endpush