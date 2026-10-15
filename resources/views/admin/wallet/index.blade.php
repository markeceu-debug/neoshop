@extends('layouts.admin')

@section('title', 'Carteira da Plataforma')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Carteira da Plataforma</h2>
      <p class="admin-page-sub">Comissões acumuladas de todas as vendas</p>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
    <div class="admin-card" style="padding:20px;">
      <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:8px;">Saldo disponível</div>
      <div style="font-size:24px; font-weight:900; color:var(--admin-green);">
        R$ {{ number_format($stats['balance'], 2, ',', '.') }}
      </div>
    </div>

    <div class="admin-card" style="padding:20px;">
      <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:8px;">Total arrecadado</div>
      <div style="font-size:24px; font-weight:900; color:var(--admin-accent-2);">
        R$ {{ number_format($stats['total_earned'], 2, ',', '.') }}
      </div>
    </div>

    <div class="admin-card" style="padding:20px;">
      <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:8px;">Total sacado</div>
      <div style="font-size:24px; font-weight:900; color:var(--admin-text);">
        R$ {{ number_format($stats['total_withdrawn'], 2, ',', '.') }}
      </div>
    </div>

    <div class="admin-card" style="padding:20px;">
      <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:8px;">Comissões recebidas</div>
      <div style="font-size:24px; font-weight:900; color:var(--admin-text);">
        {{ $stats['commissions'] }}
      </div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="bi bi-list-ul"></i> Movimentos</h3>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="background:var(--admin-sidebar); text-align:left;">
            <th style="padding:12px 16px;">Data</th>
            <th style="padding:12px 16px;">Tipo</th>
            <th style="padding:12px 16px;">Descrição</th>
            <th style="padding:12px 16px; text-align:right;">Valor</th>
            <th style="padding:12px 16px; text-align:right;">Saldo após</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($transactions as $t)
            <tr style="border-top:1px solid var(--admin-border-subtle);">
              <td style="padding:12px 16px; color:var(--admin-text-sec);">
                {{ $t->created_at->format('d/m/Y H:i') }}
              </td>
              <td style="padding:12px 16px;">
                <span class="admin-badge admin-badge-info">{{ $t->type }}</span>
              </td>
              <td style="padding:12px 16px; color:var(--admin-text-sec);">
                {{ $t->description }}
              </td>
              <td style="padding:12px 16px; text-align:right; font-weight:800; color:{{ $t->amount >= 0 ? 'var(--admin-green)' : 'var(--admin-red)' }};">
                {{ $t->amount >= 0 ? '+' : '' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
              </td>
              <td style="padding:12px 16px; text-align:right; color:var(--admin-text-mut);">
                R$ {{ number_format($t->balance_after, 2, ',', '.') }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="padding:40px; text-align:center; color:var(--admin-text-mut);">
                Nenhum movimento ainda.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <div style="padding:16px;">
        {{ $transactions->links() }}
      </div>
    </div>
  </div>

@endsection