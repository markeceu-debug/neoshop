@extends('layouts.vendor')

@section('title', 'Saques')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Saques</h2>
      <p class="admin-page-sub">Solicite e acompanhe seus saques</p>
    </div>
    <div class="admin-page-actions">
      @if ($stats['available'] >= (float) \App\Models\Setting::get('min_withdrawal', 5000))
        <a href="{{ route('vendor.withdrawals.create') }}" class="admin-btn admin-btn-primary">
          <i class="bi bi-plus-lg"></i> Novo Saque
        </a>
      @endif
    </div>
  </div>

  {{-- STATS --}}
  <div class="admin-stats-grid">
    <div class="admin-stat stat-green">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Saldo Disponível</span>
        <div class="admin-stat-icon"><i class="bi bi-wallet2"></i></div>
      </div>
      <div class="admin-stat-value">R$ {{ number_format($stats['available'], 2, ',', '.') }}</div>
      <div class="admin-stat-trend"><span>Pronto para saque</span></div>
    </div>

    <div class="admin-stat stat-orange">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Em Garantia</span>
        <div class="admin-stat-icon"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="admin-stat-value">R$ {{ number_format($stats['pending'], 2, ',', '.') }}</div>
      <div class="admin-stat-trend"><span>Libera em {{ \App\Models\Setting::get('guarantee_days', 7) }} dias</span></div>
    </div>

    <div class="admin-stat stat-cyan">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Em Saque</span>
        <div class="admin-stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
      </div>
      <div class="admin-stat-value">R$ {{ number_format($stats['hold'], 2, ',', '.') }}</div>
      <div class="admin-stat-trend"><span>Aguardando pagamento</span></div>
    </div>

    <div class="admin-stat stat-purple">
      <div class="admin-stat-head">
        <span class="admin-stat-label">Total Sacado</span>
        <div class="admin-stat-icon"><i class="bi bi-trophy-fill"></i></div>
      </div>
      <div class="admin-stat-value">R$ {{ number_format($stats['total_paid'], 2, ',', '.') }}</div>
    </div>
  </div>

  {{-- LISTA --}}
  <div class="admin-table-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="bi bi-list-ul"></i> Histórico de Saques</h3>
    </div>
    <div class="admin-card-body">
      @if ($withdrawals->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:var(--admin-text-mut);">
          <i class="bi bi-arrow-down-circle" style="font-size:48px; display:block; margin-bottom:12px; opacity:.3;"></i>
          <p style="margin:0;">Você ainda não fez nenhuma solicitação de saque.</p>
        </div>
      @else
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr style="border-bottom:1px solid var(--admin-border-strong);">
                <th style="text-align:left; padding:10px 8px; font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Data</th>
                <th style="text-align:left; padding:10px 8px; font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Valor</th>
                <th style="text-align:left; padding:10px 8px; font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Método</th>
                <th style="text-align:left; padding:10px 8px; font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Status</th>
                <th style="text-align:left; padding:10px 8px; font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px;">Ref.</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($withdrawals as $w)
                <tr style="border-bottom:1px solid var(--admin-border-subtle);">
                  <td style="padding:12px 8px; font-size:13px;">{{ $w->created_at->format('d/m/Y H:i') }}</td>
                  <td style="padding:12px 8px; font-size:13px; font-weight:800; color:var(--admin-accent-2);">
                    R$ {{ number_format($w->amount, 2, ',', '.') }}
                  </td>
                  <td style="padding:12px 8px; font-size:13px;">
                    {{ $w->method === 'bank_transfer' ? 'Transferência' : 'Multicaixa Express' }}
                  </td>
                  <td style="padding:12px 8px;">
                    @php
                      $map = [
                        'pending'  => ['admin-badge-warning', 'Em análise'],
                        'approved' => ['admin-badge-info',    'Aprovado'],
                        'rejected' => ['admin-badge-danger',  'Rejeitado'],
                        'paid'     => ['admin-badge-success', 'Pago'],
                      ];
                      [$cls, $label] = $map[$w->status] ?? ['admin-badge-muted', $w->status];
                    @endphp
                    <span class="admin-badge {{ $cls }}">{{ $label }}</span>

                    @if ($w->status === 'rejected' && $w->rejection_reason)
                      <div style="font-size:11px; color:var(--admin-red); margin-top:4px;">
                        {{ $w->rejection_reason }}
                      </div>
                    @endif
                  </td>
                  <td style="padding:12px 8px; font-size:12px; color:var(--admin-text-mut);">
                    {{ $w->payment_reference ?: '—' }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div style="margin-top:20px;">{{ $withdrawals->links() }}</div>
      @endif
    </div>
  </div>

@endsection