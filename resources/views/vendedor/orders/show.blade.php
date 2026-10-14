@extends('layouts.vendor')

@section('title', 'Pedido ' . $order->number)

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Pedido #{{ $order->number }}</h2>
      <p class="admin-page-sub">
        {{ $order->created_at->format('d/m/Y \à\s H:i') }}
      </p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('vendor.orders.index') }}" class="admin-btn admin-btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
    </div>
  </div>

@if ($order->status === 'paid')
  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-green); margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
      <i class="bi bi-box-seam" style="font-size:32px; color:var(--admin-green);"></i>
      <div style="flex:1; min-width:200px;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Pagamento aprovado! Prepare o envio.
        </strong>
        <span style="color:var(--admin-text-sec); font-size:13px;">
          Combine com o motoboy e marque o pedido como enviado quando despachar.
        </span>
      </div>
      <button type="button"
              onclick="openShippedModal()"
              class="admin-btn admin-btn-primary"
              style="padding:12px 24px;">
        <i class="bi bi-truck"></i> Marcar como Enviado
      </button>
    </div>
  </div>
@endif

{{-- CARD DE ENVIO (se já foi enviado) --}}
@if ($order->status === 'shipped' && $order->courier_name)
  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-cyan); margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:16px;">
      <i class="bi bi-truck" style="font-size:32px; color:var(--admin-cyan);"></i>
      <div style="flex:1;">
        <strong style="color:var(--admin-text); display:block; font-size:14px; margin-bottom:4px;">
          Enviado em {{ $order->shipped_at->format('d/m/Y \à\s H:i') }}
        </strong>
        <div style="font-size:13px; color:var(--admin-text-sec);">
          <strong style="color:var(--admin-text);">Motoboy:</strong> {{ $order->courier_name }}
          @if ($order->courier_phone)
            · <i class="bi bi-telephone-fill"></i> {{ $order->courier_phone }}
          @endif
        </div>
        @if ($order->shipping_note)
          <div style="font-size:12px; color:var(--admin-text-mut); margin-top:6px;">
            <strong>Obs:</strong> {{ $order->shipping_note }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endif

  <div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

    {{-- COLUNA ESQUERDA --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

      {{-- ITENS --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title">
            <i class="bi bi-box-seam"></i> Itens do Pedido ({{ $order->items->count() }})
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
         <p style="margin:8px 0 0; color:var(--admin-text-sec); font-size:13px; line-height:1.7;">
  {{ $order->shipping_address['street'] ?? '—' }},
  {{ $order->shipping_address['number'] ?? '—' }}
  @if (!empty($order->shipping_address['complement']))
    — {{ $order->shipping_address['complement'] }}
  @endif
  <br>
  {{ $order->shipping_address['neighborhood'] ?? '—' }},
  {{ $order->shipping_address['municipality'] ?? '—' }} —
  {{ $order->shipping_address['province'] ?? '—' }}
  @if (!empty($order->shipping_address['reference']))
    <br>
    <strong style="color:var(--admin-text);">Referência:</strong>
    {{ $order->shipping_address['reference'] }}
  @endif
</p>
          @if ($order->customer_phone)
            <p style="margin:12px 0 0; font-size:13px; color:var(--admin-text);">
              <i class="bi bi-telephone-fill"></i> <strong>{{ $order->customer_phone }}</strong>
            </p>
          @endif
        </div>
      </div>

    </div>

    {{-- COLUNA DIREITA --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

      {{-- RESUMO --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-cash-coin"></i> Resumo</h3>
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
              <span style="color:var(--admin-text-sec); font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Total</span>
              <strong style="font-size:18px; color:var(--admin-accent-2); font-weight:900;">R$ {{ number_format($order->total, 2, ',', '.') }}</strong>
            </div>

            <div style="display:flex; justify-content:space-between; padding-top:12px; border-top:1px solid var(--admin-border-subtle);">
              <span style="color:var(--admin-text-sec);">Comissão ({{ (int) $order->commission_rate }}%)</span>
              <strong style="color:var(--admin-orange);">− R$ {{ number_format($order->commission_amount, 2, ',', '.') }}</strong>
            </div>

            <div style="display:flex; justify-content:space-between;">
              <span style="color:var(--admin-text-sec); font-weight:800;">Você recebe</span>
              <strong style="color:var(--admin-green); font-size:16px;">R$ {{ number_format($order->seller_net_amount, 2, ',', '.') }}</strong>
            </div>

          </div>
        </div>
      </div>

      {{-- CLIENTE --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-person"></i> Cliente</h3>
        </div>
        <div class="admin-card-body" style="font-size:13px;">
          <strong style="color:var(--admin-text);">{{ $order->customer_name }}</strong>
          <div style="color:var(--admin-text-mut); margin-top:4px;">{{ $order->customer_email }}</div>
          @if ($order->customer_phone)
            <div style="color:var(--admin-text); margin-top:8px;">
              <i class="bi bi-whatsapp" style="color:var(--admin-green);"></i>
              <a href="https://wa.me/244{{ preg_replace('/\D/', '', $order->customer_phone) }}"
                 target="_blank"
                 style="color:var(--admin-green); text-decoration:none; font-weight:700;">
                Abrir WhatsApp
              </a>
            </div>
          @endif
        </div>
      </div>

    </div>
  </div>
{{-- MODAL: MARCAR COMO ENVIADO --}}
<div id="shippedModal" class="admin-modal-overlay" style="display:none;">
  <div class="admin-modal" style="max-width:480px;">

    <div class="admin-modal-header">
      <h3><i class="bi bi-truck"></i> Marcar como Enviado</h3>
      <button type="button" onclick="closeShippedModal()" class="admin-modal-close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('vendor.orders.shipped', $order) }}">
      @csrf

      <div class="admin-modal-body">

        <div style="background:rgba(124,92,255,0.08); border-left:3px solid var(--admin-accent); padding:12px 16px; margin-bottom:20px; font-size:12px; color:var(--admin-text-sec);">
          Confirme os dados do motoboy que está fazendo a entrega.
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">

          <div>
            <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
              Nome do Motoboy *
            </label>
            <input type="text" name="courier_name" required
                   placeholder="Ex: João Pedro"
                   style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
          </div>

          <div>
            <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
              Telefone do Motoboy
            </label>
            <input type="tel" name="courier_phone"
                   placeholder="923 000 000"
                   style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
          </div>

          <div>
            <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
              Observação (opcional)
            </label>
            <textarea name="shipping_note" rows="3"
                      placeholder="Ex: Saiu às 14h, previsão 30min"
                      style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;"></textarea>
          </div>

        </div>
      </div>

      <div class="admin-modal-footer">
        <button type="button" onclick="closeShippedModal()" class="admin-btn admin-btn-secondary">
          Cancelar
        </button>
        <button type="submit" class="admin-btn admin-btn-primary">
          <i class="bi bi-check-lg"></i> Confirmar Envio
        </button>
      </div>

    </form>
  </div>
</div>
@push('scripts')
<script>
  function openShippedModal() {
    document.getElementById('shippedModal').style.display = 'flex';
  }
  function closeShippedModal() {
    document.getElementById('shippedModal').style.display = 'none';
  }
  // Fecha clicando no overlay
  document.getElementById('shippedModal')?.addEventListener('click', function (e) {
    if (e.target === this) closeShippedModal();
  });
  // ESC fecha
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeShippedModal();
  });
</script>
@endpush
@endsection