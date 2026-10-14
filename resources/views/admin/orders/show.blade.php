@extends('layouts.admin')

@section('title', 'Pedido ' . $order->number)

@section('content')

<div class="admin-page-header">
  <div>
    <h2 class="admin-page-title">Pedido #{{ $order->number }}</h2>
    <p class="admin-page-sub">
      {{ $order->created_at->format('d/m/Y \à\s H:i') }}
      ·
      <span class="admin-badge
          @switch($order->status)
            @case('awaiting_payment')     admin-badge-warning @break
            @case('payment_under_review') admin-badge-info @break
            @case('payment_rejected')     admin-badge-danger @break
            @case('paid')                 admin-badge-success @break
            @case('shipped')              admin-badge-info @break
            @case('delivered')            admin-badge-success @break
            @case('completed')            admin-badge-success @break
            @default                      admin-badge-muted
          @endswitch
        " style="margin-left:10px;">{{ $order->status_label }}</span>
    </p>
  </div>
  <div class="admin-page-actions">
    <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn-secondary">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

  {{-- COLUNA ESQUERDA --}}
  <div style="display:flex; flex-direction:column; gap:20px;">

    {{-- COMPROVATIVOS --}}
    @if ($order->paymentProofs->isNotEmpty())
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title">
          <i class="bi bi-file-earmark-check"></i> Comprovativos ({{ $order->paymentProofs->count() }})
        </h3>
      </div>
      <div class="admin-card-body">

        @foreach ($order->paymentProofs as $proof)
        <div style="border:1px solid var(--admin-border-strong); margin-bottom:16px; background:var(--admin-bg);"
          id="proof-{{ $proof->id }}">

          <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:var(--admin-card); border-bottom:1px solid var(--admin-border-strong);">
            <div>
              <span class="admin-badge
                      @if ($proof->isApproved()) admin-badge-success
                      @elseif ($proof->isRejected()) admin-badge-danger
                      @else admin-badge-warning
                      @endif
                    ">
                @if ($proof->isApproved()) <i class="bi bi-check-circle-fill"></i> Aprovado
                @elseif ($proof->isRejected()) <i class="bi bi-x-circle-fill"></i> Rejeitado
                @else <i class="bi bi-hourglass-split"></i> Pendente
                @endif
              </span>
              <span style="font-size:11px; color:var(--admin-text-mut); margin-left:10px;">
                Enviado por {{ $proof->uploader?->name ?? 'Visitante' }} em {{ $proof->created_at->format('d/m/Y H:i') }}
              </span>
            </div>
            <span style="font-size:11px; color:var(--admin-text-mut);">
              {{ number_format($proof->file_size / 1024, 1) }} KB
            </span>
          </div>

          {{-- Preview --}}
          <div style="padding:16px;">
            @if (str_starts_with($proof->file_type, 'image/'))
            <a href="{{ $proof->url }}" target="_blank">
              <img src="{{ $proof->url }}" alt="Comprovativo"
                style="max-width:100%; max-height:400px; border:1px solid var(--admin-border-strong); display:block;">
            </a>
            @else
            <a href="{{ $proof->url }}" target="_blank"
              class="admin-btn admin-btn-secondary"
              style="width:100%; justify-content:center;">
              <i class="bi bi-file-pdf"></i> Abrir PDF
            </a>
            @endif

            @if ($proof->notes)
            <div style="margin-top:12px; padding:10px; background:var(--admin-card); border-left:3px solid var(--admin-accent); font-size:12px; color:var(--admin-text-sec);">
              <strong style="color:var(--admin-text);">Obs. do cliente:</strong> {{ $proof->notes }}
            </div>
            @endif

            @if ($proof->isRejected() && $proof->rejection_reason)
            <div style="margin-top:12px; padding:10px; background:rgba(239,68,68,0.08); border-left:3px solid var(--admin-red); font-size:12px; color:var(--admin-red);">
              <strong>Motivo da rejeição:</strong> {{ $proof->rejection_reason }}
            </div>
            @endif

            @if ($proof->isApproved() && $proof->reviewer)
            <div style="margin-top:12px; font-size:11px; color:var(--admin-text-mut);">
              Aprovado por <strong>{{ $proof->reviewer->name }}</strong> em {{ $proof->reviewed_at?->format('d/m/Y H:i') }}
            </div>
            @endif
          </div>

          {{-- Ações (só se pendente e pedido em análise) --}}
          @if ($proof->isPending() && $order->status === 'payment_under_review')
          <div style="display:flex; gap:8px; padding:0 16px 16px;">
            <button type="button"
              onclick="approveProof({{ $proof->id }})"
              class="admin-btn admin-btn-primary"
              style="flex:1; justify-content:center;">
              <i class="bi bi-check-lg"></i> Aprovar pagamento
            </button>
            <button type="button"
              onclick="rejectProof({{ $proof->id }})"
              class="admin-btn admin-btn-danger"
              style="flex:1; justify-content:center;">
              <i class="bi bi-x-lg"></i> Rejeitar
            </button>
          </div>
          @endif
        </div>
        @endforeach

      </div>
    </div>
    @endif

    {{-- ITENS DO PEDIDO --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title">
          <i class="bi bi-box-seam"></i> Itens ({{ $order->items->count() }})
        </h3>
      </div>
      <div class="admin-card-body">

        @foreach ($order->items as $item)
        <div style="display:grid; grid-template-columns:64px 1fr auto; gap:14px; align-items:center; padding:12px 0; border-bottom:1px solid var(--admin-border-subtle);">
          <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}"
            style="width:64px; height:64px; object-fit:cover; border:1px solid var(--admin-border-strong);">

          <div>
            <div style="font-size:13px; font-weight:700; color:var(--admin-text);">{{ $item->product_name }}</div>
            @if ($item->variant_value)
            <div style="font-size:11px; color:var(--admin-text-mut);">Variação: {{ $item->variant_value }}</div>
            @endif
            <div style="font-size:11px; color:var(--admin-text-mut);">Qtd: {{ $item->quantity }}</div>
          </div>

          <div style="text-align:right;">
            <div style="font-size:13px; font-weight:800; color:var(--admin-accent-2);">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</div>
            <div style="font-size:11px; color:var(--admin-text-mut);">× R$ {{ number_format($item->unit_price, 2, ',', '.') }}</div>
          </div>
        </div>
        @endforeach

      </div>
    </div>

    {{-- ENDEREÇO --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-geo-alt"></i> Endereço de Entrega</h3>
      </div>
      <div class="admin-card-body">
        <strong style="color:var(--admin-text);">{{ $order->customer_name }}</strong>

        @php
        $addr = is_array($order->shipping_address) ? $order->shipping_address : [];
        @endphp

        @if (!empty($addr))
        <p style="margin:8px 0 0; color:var(--admin-text-sec); font-size:13px; line-height:1.7;">
          {{ $addr['street'] ?? '—' }}, {{ $addr['number'] ?? '—' }}
          @if (!empty($addr['complement']))
          — {{ $addr['complement'] }}
          @endif
          <br>
          {{ $addr['neighborhood'] ?? '—' }}, {{ $addr['city'] ?? '—' }} — {{ $addr['state'] ?? '—' }}<br>
          CEP {{ $addr['cep'] ?? '—' }}
        </p>
        @else
        <p style="margin:8px 0 0; color:var(--admin-text-mut); font-size:13px; font-style:italic;">
          <i class="bi bi-exclamation-triangle"></i> Endereço não informado.
        </p>
        @endif

        @if ($order->customer_phone)
        <p style="margin:8px 0 0; font-size:12px; color:var(--admin-text-mut);">
          <i class="bi bi-telephone-fill"></i> {{ $order->customer_phone }}
        </p>
        @endif
      </div>
    </div>
  </div>

  {{-- COLUNA DIREITA --}}
  <div style="display:flex; flex-direction:column; gap:20px;">

    {{-- RESUMO FINANCEIRO --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-cash-coin"></i> Resumo Financeiro</h3>
      </div>
      <div class="admin-card-body">
        <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
          <div style="display:flex; justify-content:space-between;">
            <span style="color:var(--admin-text-sec);">Subtotal</span>
            <strong>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</strong>
          </div>
          @if ($order->discount > 0)
          <div style="display:flex; justify-content:space-between;">
            <span style="color:var(--admin-text-sec);">Desconto</span>
            <strong style="color:var(--admin-pink);">− R$ {{ number_format($order->discount, 2, ',', '.') }}</strong>
          </div>
          @endif
          <div style="display:flex; justify-content:space-between;">
            <span style="color:var(--admin-text-sec);">Frete</span>
            <strong>{{ $order->shipping > 0 ? 'R$ ' . number_format($order->shipping, 2, ',', '.') : 'Grátis' }}</strong>
          </div>
          <div style="display:flex; justify-content:space-between; padding-top:12px; border-top:1px dashed var(--admin-border-strong);">
            <span style="color:var(--admin-text-sec); font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Total pago</span>
            <strong style="font-size:18px; color:var(--admin-accent-2); font-weight:900;">R$ {{ number_format($order->total, 2, ',', '.') }}</strong>
          </div>
          @php
  $commissionMin = (float) \App\Models\Setting::get('commission_min', 200);
  $percentValue  = round($order->subtotal * ($order->commission_rate / 100), 2);
  $usedMin       = $order->commission_amount > $percentValue;
@endphp

<div style="display:flex; justify-content:space-between;">
  <span style="color:var(--admin-text-sec);">
    Comissão ({{ (int) $order->commission_rate }}%)
    @if ($usedMin)
      <span class="admin-badge admin-badge-warning" style="margin-left:6px; font-size:10px;">Mínima aplicada</span>
    @endif
  </span>
  <strong style="color:var(--admin-green);">R$ {{ number_format($order->commission_amount, 2, ',', '.') }}</strong>
</div>
          <div style="display:flex; justify-content:space-between;">
            <span style="color:var(--admin-text-sec);">Vendedor recebe</span>
            <strong style="color:var(--admin-accent);">R$ {{ number_format($order->seller_net_amount, 2, ',', '.') }}</strong>
          </div>
        </div>
      </div>
    </div>

    {{-- AÇÕES DE STATUS --}}
    @if ($order->status === 'paid')
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-truck"></i> Marcar como Enviado</h3>
      </div>
      <div class="admin-card-body">
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Código de Rastreio</label>
        <input type="text" id="trackingCode" placeholder="Ex: BR123456789"
          style="width:100%; margin-top:8px; padding:10px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
        <button type="button" onclick="markShipped()"
          class="admin-btn admin-btn-primary"
          style="width:100%; justify-content:center; margin-top:12px;">
          <i class="bi bi-truck"></i> Marcar como Enviado
        </button>
      </div>
    </div>
    @endif
    {{-- MARCAR COMO ENTREGUE (aparece quando já foi enviado ou pago) --}}
    @if (in_array($order->status, ['shipped', 'paid']))
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-check2-circle"></i> Marcar como Entregue</h3>
      </div>
      <div class="admin-card-body">
        <p style="font-size:12px; color:var(--admin-text-mut); margin:0 0 12px; line-height:1.5;">
          Após marcar como entregue, o valor ficará <strong style="color:var(--admin-accent);">
            {{ (int) \App\Models\Setting::get('guarantee_days', 7) }} dias</strong> em garantia antes de ser liberado ao vendedor.
        </p>
        <button type="button"
          onclick="markDelivered()"
          class="admin-btn admin-btn-primary"
          style="width:100%; justify-content:center; padding:14px 24px;">
          <i class="bi bi-house-door-fill"></i> Marcar como Entregue
        </button>
      </div>
    </div>
    @endif
    {{-- CLIENTE --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-person"></i> Cliente</h3>
      </div>
      <div class="admin-card-body" style="font-size:13px;">
        <strong style="color:var(--admin-text);">{{ $order->customer_name }}</strong>
        <div style="color:var(--admin-text-mut); margin-top:4px;">{{ $order->customer_email }}</div>
        @if ($order->customer_phone)
        <div style="color:var(--admin-text-mut);">{{ $order->customer_phone }}</div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  // ============================================================
  // HELPER — POST com JSON
  // ============================================================
  async function postAction(url, body) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body || {}),
      });

      const data = await res.json().catch(function () {
        return { status: 'error', message: 'Resposta inválida do servidor (HTTP ' + res.status + ')' };
      });

      return data;
    } catch (err) {
      return { status: 'error', message: 'Erro de conexão: ' + err.message };
    }
  }

  // ============================================================
  // TOAST (garante que existe)
  // ============================================================
  function showToast(html, type) {
    let container = document.getElementById('toastContainer');
    if (! container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      container.id = 'toastContainer';
      document.body.appendChild(container);
    }

    const el = document.createElement('div');
    el.className = 'neo-toast' + (type === 'error' ? ' toast-error' : '');
    const icon = type === 'error'
      ? '<i class="bi bi-exclamation-triangle-fill" style="color:var(--admin-red)"></i>'
      : '<i class="bi bi-check-circle-fill"></i>';
    el.innerHTML = icon + '<span>' + html + '</span>';
    container.appendChild(el);

    setTimeout(function () {
      el.style.transition = 'all 0.3s';
      el.style.opacity = '0';
      el.style.transform = 'translateX(400px)';
      setTimeout(function () { el.remove(); }, 300);
    }, 2600);
  }

  // ============================================================
  // APROVAR PAGAMENTO
  // ============================================================
  async function approveProof(proofId) {
    if (! confirm('Aprovar este comprovativo? O pedido será marcado como PAGO e o vendedor receberá o valor pendente.')) return;

    const data = await postAction('{{ route('admin.orders.approve', $order) }}', { proof_id: proofId });

    if (data.status === 'success') {
      showToast(data.message || 'Pagamento aprovado!');
      setTimeout(function () { window.location.reload(); }, 700);
    } else {
      showToast(data.message || 'Erro ao aprovar', 'error');
    }
  }

  // ============================================================
  // REJEITAR PAGAMENTO
  // ============================================================
  async function rejectProof(proofId) {
    const reason = prompt('Motivo da rejeição (o cliente verá esta mensagem):');
    if (! reason) return;

    const data = await postAction('{{ route('admin.orders.reject', $order) }}', {
      proof_id: proofId,
      reason: reason,
    });

    if (data.status === 'success') {
      showToast(data.message || 'Comprovativo rejeitado');
      setTimeout(function () { window.location.reload(); }, 700);
    } else {
      showToast(data.message || 'Erro ao rejeitar', 'error');
    }
  }

  // ============================================================
  // MARCAR COMO ENVIADO
  // ============================================================
  async function markShipped() {
    const input = document.getElementById('trackingCode');
    const tracking = input ? input.value : '';

    const data = await postAction('{{ route('admin.orders.shipped', $order) }}', {
      tracking_code: tracking,
    });

    if (data.status === 'success') {
      showToast(data.message || 'Pedido marcado como enviado!');
      setTimeout(function () { window.location.reload(); }, 700);
    } else {
      showToast(data.message || 'Erro ao marcar como enviado', 'error');
    }
  }

  // ============================================================
  // MARCAR COMO ENTREGUE
  // ============================================================
  async function markDelivered() {
    const note = prompt('Observação da entrega (opcional):\nEx: Entregue na portaria') || '';

    const data = await postAction('{{ route('admin.orders.delivered', $order) }}', {
      tracking_note: note,
    });

    if (data.status === 'success') {
      showToast(data.message || 'Pedido marcado como entregue!');
      setTimeout(function () { window.location.reload(); }, 700);
    } else {
      showToast(data.message || 'Erro ao marcar como entregue', 'error');
    }
  }
</script>
@endpush