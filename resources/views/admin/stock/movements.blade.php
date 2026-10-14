@extends('layouts.admin')

@section('title', 'Movimentações de Estoque')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Histórico de Estoque</h2>
      <p class="admin-page-sub">Todas as entradas, saídas e ajustes registrados</p>
    </div>
  </div>

  <div class="admin-table-card">
    <div class="admin-table-wrap">
      <table class="table" style="width:100%;">
        <thead>
          <tr>
            <th style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Data</th>
            <th style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Item</th>
            <th style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Tipo</th>
            <th style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Qtd</th>
            <th style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Antes → Depois</th>
            <th style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Motivo</th>
            <th style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Usuário</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($movements as $m)
            <tr>
              <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ class_basename($m->stockable_type) }} #{{ $m->stockable_id }}</td>
              <td>
                @php
                  $map = ['in' => 'admin-badge-success', 'out' => 'admin-badge-danger', 'adjust' => 'admin-badge-warning'];
                  $label = ['in' => 'Entrada', 'out' => 'Saída', 'adjust' => 'Ajuste'];
                @endphp
                <span class="admin-badge {{ $map[$m->type] }}">{{ $label[$m->type] }}</span>
              </td>
              <td style="font-weight:800; color:{{ $m->quantity > 0 ? 'var(--admin-green)' : 'var(--admin-red)' }};">
                {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}
              </td>
              <td>{{ $m->stock_before }} → <strong>{{ $m->stock_after }}</strong></td>
              <td>{{ $m->reason }}</td>
              <td>{{ $m->user?->name ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--admin-text-mut);">Nenhuma movimentação.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin-top:16px;">{{ $movements->links() }}</div>

@endsection