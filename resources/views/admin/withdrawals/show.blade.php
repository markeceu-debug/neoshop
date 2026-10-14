@extends('layouts.admin')

@section('title', 'Saque #' . $withdrawal->id)

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Saque #{{ $withdrawal->id }}</h2>
      <p class="admin-page-sub">
        Solicitado em {{ $withdrawal->created_at->format('d/m/Y \à\s H:i') }}
      </p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.withdrawals.index') }}" class="admin-btn admin-btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:1fr 400px; gap:24px; align-items:start;">

    {{-- COLUNA ESQUERDA --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

      {{-- VALOR --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-cash-coin"></i> Valor do Saque</h3>
        </div>
        <div class="admin-card-body">
          <div style="text-align:center; padding:20px 0;">
            <div style="font-size:42px; font-weight:900; color:var(--admin-accent-2); font-variant-numeric:tabular-nums; letter-spacing:-1px;">
              R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}
            </div>
            <div style="font-size:13px; color:var(--admin-text-mut); margin-top:8px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">
              {{ $withdrawal->method === 'bank_transfer' ? 'Transferência Bancária' : 'Multicaixa Express' }}
            </div>
          </div>
        </div>
      </div>

      {{-- DADOS BANCÁRIOS --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-bank"></i> Dados Bancários</h3>
        </div>
        <div class="admin-card-body">
          @php $bank = $withdrawal->bank_data; @endphp

          <div style="display:flex; flex-direction:column; gap:14px;">
            <div>
              <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Titular</span>
              <div style="font-size:14px; color:var(--admin-text); font-weight:700; margin-top:4px;">
                {{ $bank['holder'] ?? '—' }}
              </div>
            </div>

            <div>
              <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Banco</span>
              <div style="font-size:14px; color:var(--admin-text); font-weight:700; margin-top:4px;">
                {{ $bank['bank'] ?? '—' }}
              </div>
            </div>

            <div>
              <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Número da Conta</span>
              <div style="font-size:14px; color:var(--admin-text); font-weight:700; margin-top:4px; font-family:monospace;">
                {{ $bank['account'] ?? '—' }}
              </div>
            </div>

            <div>
              <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">IBAN</span>
              <div style="font-size:14px; color:var(--admin-text); font-weight:700; margin-top:4px; font-family:monospace; word-break:break-all;">
                {{ $bank['iban'] ?? '—' }}
              </div>
            </div>

            @if (!empty($bank['express']))
              <div>
                <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Multicaixa Express</span>
                <div style="font-size:14px; color:var(--admin-text); font-weight:700; margin-top:4px;">
                  {{ $bank['express'] }}
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- VENDEDOR --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-person"></i> Vendedor</h3>
        </div>
        <div class="admin-card-body">
          <strong style="color:var(--admin-text); font-size:14px;">{{ $withdrawal->user->name }}</strong>
          <div style="color:var(--admin-text-mut); font-size:13px; margin-top:4px;">
            {{ $withdrawal->user->email }}
          </div>
          @if ($withdrawal->user->telefone)
            <div style="color:var(--admin-text-mut); font-size:13px; margin-top:4px;">
              <i class="bi bi-telephone-fill"></i> {{ $withdrawal->user->telefone }}
            </div>
          @endif
        </div>
      </div>

    </div>

    {{-- COLUNA DIREITA --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

      {{-- STATUS --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-info-circle"></i> Status</h3>
        </div>
        <div class="admin-card-body">
          @php
            $map = [
              'pending'  => ['admin-badge-warning', 'hourglass-split',   'Pendente'],
              'approved' => ['admin-badge-info',    'check-circle-fill', 'Aprovado'],
              'rejected' => ['admin-badge-danger',  'x-circle-fill',     'Rejeitado'],
              'paid'     => ['admin-badge-success', 'trophy-fill',       'Pago'],
            ];
            [$cls, $icon, $label] = $map[$withdrawal->status] ?? ['admin-badge-muted', 'info-circle', $withdrawal->status];
          @endphp

          <div style="text-align:center; margin-bottom:20px;">
            <span class="admin-badge {{ $cls }}" style="font-size:14px; padding:8px 18px;">
              <i class="bi bi-{{ $icon }}"></i> {{ $label }}
            </span>
          </div>

          @if ($withdrawal->reviewer)
            <div style="font-size:12px; color:var(--admin-text-mut); text-align:center;">
              Revisado por <strong>{{ $withdrawal->reviewer->name }}</strong><br>
              {{ $withdrawal->reviewed_at?->format('d/m/Y H:i') }}
            </div>
          @endif

          @if ($withdrawal->rejection_reason)
            <div style="margin-top:16px; padding:12px; background:rgba(239,68,68,0.08); border-left:3px solid var(--admin-red); font-size:12px; color:var(--admin-red);">
              <strong>Motivo da rejeição:</strong><br>
              {{ $withdrawal->rejection_reason }}
            </div>
          @endif

          @if ($withdrawal->payment_reference)
            <div style="margin-top:16px; padding:12px; background:rgba(34,197,94,0.08); border-left:3px solid var(--admin-green); font-size:12px; color:var(--admin-green);">
              <strong>Referência do pagamento:</strong><br>
              {{ $withdrawal->payment_reference }}
              @if ($withdrawal->paid_at)
                <br><small>{{ $withdrawal->paid_at->format('d/m/Y H:i') }}</small>
              @endif
            </div>
          @endif
        </div>
      </div>

      {{-- AÇÕES --}}
      @if (in_array($withdrawal->status, ['pending', 'approved']))
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="bi bi-lightning-charge-fill"></i> Ações</h3>
          </div>
          <div class="admin-card-body" style="display:flex; flex-direction:column; gap:12px;">

            {{-- APROVAR (só se pending) --}}
            @if ($withdrawal->status === 'pending')
              <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                @csrf
                <button type="submit" class="admin-btn admin-btn-primary"
                        style="width:100%; justify-content:center;">
                  <i class="bi bi-check-lg"></i> Aprovar Saque
                </button>
              </form>
            @endif

            {{-- MARCAR COMO PAGO --}}
            <button type="button" onclick="openPaidModal()"
                    class="admin-btn admin-btn-primary"
                    style="width:100%; justify-content:center;">
              <i class="bi bi-trophy-fill"></i> Marcar como Pago
            </button>

            {{-- REJEITAR --}}
            <button type="button" onclick="openRejectModal()"
                    class="admin-btn admin-btn-danger"
                    style="width:100%; justify-content:center;">
              <i class="bi bi-x-lg"></i> Rejeitar
            </button>

          </div>
        </div>
      @endif

    </div>
  </div>

  {{-- MODAL: MARCAR COMO PAGO --}}
  <div id="paidModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(3px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--admin-card); border:1px solid var(--admin-border-strong); width:100%; max-width:480px; box-shadow:0 24px 60px rgba(0,0,0,0.7);">

      <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid var(--admin-border); background:linear-gradient(135deg,var(--admin-sidebar) 0%,rgba(124,92,255,0.06) 100%);">
        <h3 style="margin:0; font-size:15px; font-weight:900; color:var(--admin-text); text-transform:uppercase; letter-spacing:1px;">
          <i class="bi bi-trophy-fill" style="color:var(--admin-green);"></i> Confirmar Pagamento
        </h3>
        <button type="button" onclick="closePaidModal()" style="width:32px; height:32px; background:transparent; border:1px solid var(--admin-border-strong); color:var(--admin-text-sec); cursor:pointer; border-radius:50% !important; font-size:14px;">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.withdrawals.paid', $withdrawal) }}">
        @csrf

        <div style="padding:24px;">
          <div style="background:rgba(34,197,94,0.08); border-left:3px solid var(--admin-green); padding:14px 16px; margin-bottom:20px; font-size:13px; color:var(--admin-text-sec);">
            <strong style="color:var(--admin-green); display:block; margin-bottom:4px;">
              <i class="bi bi-check-circle-fill"></i> Confirme que já transferiu
            </strong>
            Só marque como pago depois de <strong>efetivamente</strong> transferir o dinheiro para o vendedor.
          </div>

          <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
            Referência do Pagamento *
          </label>
          <input type="text" name="payment_reference" required
                 placeholder="Ex: TRF-20251013-001"
                 style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; padding:18px 24px; border-top:1px solid var(--admin-border); background:var(--admin-sidebar);">
          <button type="button" onclick="closePaidModal()" class="admin-btn admin-btn-secondary">
            Cancelar
          </button>
          <button type="submit" class="admin-btn admin-btn-primary">
            <i class="bi bi-check-lg"></i> Confirmar Pagamento
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- MODAL: REJEITAR --}}
  <div id="rejectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(3px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--admin-card); border:1px solid var(--admin-border-strong); width:100%; max-width:480px; box-shadow:0 24px 60px rgba(0,0,0,0.7);">

      <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid var(--admin-border); background:linear-gradient(135deg,var(--admin-sidebar) 0%,rgba(239,68,68,0.06) 100%);">
        <h3 style="margin:0; font-size:15px; font-weight:900; color:var(--admin-text); text-transform:uppercase; letter-spacing:1px;">
          <i class="bi bi-x-circle-fill" style="color:var(--admin-red);"></i> Rejeitar Saque
        </h3>
        <button type="button" onclick="closeRejectModal()" style="width:32px; height:32px; background:transparent; border:1px solid var(--admin-border-strong); color:var(--admin-text-sec); cursor:pointer; border-radius:50% !important; font-size:14px;">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
        @csrf

        <div style="padding:24px;">
          <div style="background:rgba(251,191,36,0.08); border-left:3px solid var(--admin-orange); padding:14px 16px; margin-bottom:20px; font-size:13px; color:var(--admin-text-sec);">
            <strong style="color:var(--admin-orange); display:block; margin-bottom:4px;">
              <i class="bi bi-info-circle-fill"></i> O valor será devolvido
            </strong>
            O vendedor receberá o valor de volta no saldo disponível.
          </div>

          <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
            Motivo da Rejeição *
          </label>
          <textarea name="reason" required rows="4"
                    placeholder="Ex: Dados bancários incorretos"
                    style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;"></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; padding:18px 24px; border-top:1px solid var(--admin-border); background:var(--admin-sidebar);">
          <button type="button" onclick="closeRejectModal()" class="admin-btn admin-btn-secondary">
            Cancelar
          </button>
          <button type="submit" class="admin-btn admin-btn-danger">
            <i class="bi bi-x-lg"></i> Confirmar Rejeição
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  function openPaidModal() {
    document.getElementById('paidModal').style.display = 'flex';
  }
  function closePaidModal() {
    document.getElementById('paidModal').style.display = 'none';
  }
  function openRejectModal() {
    document.getElementById('rejectModal').style.display = 'flex';
  }
  function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
  }
  // Fecha clicando no overlay
  document.querySelectorAll('#paidModal, #rejectModal').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
  });
  // ESC fecha
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closePaidModal();
      closeRejectModal();
    }
  });
</script>
@endpush