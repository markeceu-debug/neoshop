@extends('layouts.user')

@section('title', 'Pedido ' . $order->number)

@section('content')

<div class="admin-page-header">
  <div>
    <h2 class="admin-page-title">Pedido #{{ $order->number }}</h2>
    <p class="admin-page-sub">{{ $order->created_at->format('d/m/Y \à\s H:i') }}</p>
  </div>
  <div class="admin-page-actions">
    <a href="{{ route('user.orders.index') }}" class="admin-btn admin-btn-secondary">
      <i class="bi bi-arrow-left"></i> Voltar
    </a>
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

  <div style="display:flex; flex-direction:column; gap:20px;">

    {{-- ITENS --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-box-seam"></i> Itens ({{ $order->items->count() }})</h3>
      </div>
      <div class="admin-card-body">
        @foreach ($order->items as $item)
        <div style="display:grid; grid-template-columns:64px 1fr auto; gap:14px; align-items:center; padding:12px 0; border-bottom:1px solid var(--admin-border-subtle);">
          <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}"
            style="width:64px; height:64px; object-fit:cover; border:1px solid var(--admin-border-strong);">
          <div>
            <div style="font-size:13px; font-weight:700; color:var(--admin-text);">{{ $item->product_name }}</div>
            @if ($item->variant_value)
            <div style="font-size:11px; color:var(--admin-text-mut);">{{ $item->variant_value }}</div>
            @endif
            <div style="font-size:11px; color:var(--admin-text-mut);">Qtd: {{ $item->quantity }}</div>
          </div>
          <div style="font-weight:800; color:var(--admin-accent-2);">
            R$ {{ number_format($item->subtotal, 2, ',', '.') }}
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
          {{ $order->shipping_address['street'] }}, {{ $order->shipping_address['number'] }}
          @if (!empty($order->shipping_address['complement']))
          — {{ $order->shipping_address['complement'] }}
          @endif
          <br>
          {{ $order->shipping_address['neighborhood'] }},
          {{ $order->shipping_address['municipality'] }} — {{ $order->shipping_address['province'] }}
        </p>
        @if ($order->customer_phone)
        <p style="margin:12px 0 0; font-size:13px; color:var(--admin-text);">
          <i class="bi bi-telephone-fill"></i> {{ $order->customer_phone }}
        </p>
        @endif
      </div>
    </div>

  </div>

  <div style="display:flex; flex-direction:column; gap:20px;">

    {{-- RESUMO --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-receipt"></i> Resumo</h3>
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
            <span style="color:var(--admin-text-sec); font-weight:800; text-transform:uppercase;">Total</span>
            <strong style="font-size:18px; color:var(--admin-accent-2); font-weight:900;">R$ {{ number_format($order->total, 2, ',', '.') }}</strong>
          </div>
        </div>
      </div>
    </div>

    {{-- VENDEDOR --}}
    @if ($order->seller)
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-shop"></i> Vendedor</h3>
      </div>
      <div class="admin-card-body" style="font-size:13px;">
        <strong style="color:var(--admin-text);">{{ $order->seller->name }}</strong>
        @if ($order->seller->municipality)
        <div style="color:var(--admin-text-mut); margin-top:4px;">
          <i class="bi bi-geo-alt-fill"></i> {{ $order->seller->municipality }}
        </div>
        @endif
      </div>
    </div>
    @endif


    {{-- AÇÕES DO CLIENTE --}}
    @if ($order->status === 'shipped')
    <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-cyan); margin-top:20px;">
      <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <i class="bi bi-box-seam" style="font-size:32px; color:var(--admin-cyan);"></i>
        <div style="flex:1; min-width:200px;">
          <strong style="color:var(--admin-text); display:block; font-size:14px;">
            Seu pedido está a caminho!
          </strong>
          <span style="color:var(--admin-text-sec); font-size:13px;">
            Assim que receber, confirme o recebimento para finalizar.
          </span>
        </div>
        <form method="POST" action="{{ route('user.orders.delivered', $order) }}"
          onsubmit="return confirm('Confirmar que você recebeu o produto?');">
          @csrf
          <button type="submit" class="admin-btn admin-btn-primary" style="padding:12px 24px;">
            <i class="bi bi-check-circle-fill"></i> Confirmar Recebimento
          </button>
        </form>
      </div>
    </div>
    @endif

{{-- ============ AÇÕES DO CLIENTE ============ --}}

{{-- ENVIADO: pode confirmar recebimento --}}
@if ($order->status === 'shipped')
  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-cyan); margin-top:20px;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
      <i class="bi bi-box-seam" style="font-size:32px; color:var(--admin-cyan);"></i>
      <div style="flex:1; min-width:200px;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Seu pedido está a caminho!
        </strong>
        <span style="color:var(--admin-text-sec); font-size:13px;">
          Assim que receber, confirme o recebimento para finalizar.
        </span>
      </div>
      <form method="POST" action="{{ route('user.orders.delivered', $order) }}"
            onsubmit="return confirm('Confirmar que você recebeu o produto?');">
        @csrf
        <button type="submit" class="admin-btn admin-btn-primary" style="padding:12px 24px;">
          <i class="bi bi-check-circle-fill"></i> Confirmar Recebimento
        </button>
      </form>
    </div>
  </div>
@endif

{{-- ENTREGUE: pode abrir reclamação (dentro de 7 dias) --}}
@if ($order->status === 'delivered')
  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-green); margin-top:20px;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
      <i class="bi bi-check-circle-fill" style="font-size:32px; color:var(--admin-green);"></i>
      <div style="flex:1; min-width:200px;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Recebimento confirmado!
        </strong>
        <span style="color:var(--admin-text-sec); font-size:13px;">
          @if ($order->isDisputable())
            Você tem até <strong>{{ $order->auto_release_at?->format('d/m/Y') }}</strong> para abrir uma reclamação.
          @else
            O prazo para reclamação expirou.
          @endif
        </span>
      </div>

      @if ($order->isDisputable())
        <a href="{{ route('user.disputes.create', $order) }}"
           class="admin-btn admin-btn-secondary"
           style="padding:12px 24px; border-color:var(--admin-orange); color:var(--admin-orange);">
          <i class="bi bi-exclamation-triangle"></i> Abrir Reclamação
        </a>
      @endif
    </div>
  </div>
@endif

{{-- COM DISPUTA: mostra link pra reclamação --}}
@if ($order->status === 'disputed')
  @php $dispute = $order->openDispute(); @endphp
  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-orange); margin-top:20px;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
      <i class="bi bi-exclamation-triangle-fill" style="font-size:32px; color:var(--admin-orange);"></i>
      <div style="flex:1; min-width:200px;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Reclamação em andamento
        </strong>
        <span style="color:var(--admin-text-sec); font-size:13px;">
          Aguardando análise do administrador.
        </span>
      </div>
      @if ($dispute)
        <a href="{{ route('user.disputes.show', $dispute) }}"
           class="admin-btn admin-btn-primary"
           style="padding:12px 24px;">
          <i class="bi bi-chat-dots"></i> Ver Reclamação
        </a>
      @endif
    </div>
  </div>
@endif

{{-- CONCLUÍDO --}}
@if ($order->status === 'completed')
  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-green); margin-top:20px;">
    <div style="display:flex; align-items:center; gap:16px;">
      <i class="bi bi-trophy-fill" style="font-size:32px; color:var(--admin-green);"></i>
      <div style="flex:1;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Pedido concluído!
        </strong>
        <span style="color:var(--admin-text-sec); font-size:13px;">
          Obrigado por comprar na NeoShop.
        </span>
      </div>
    </div>
  </div>
@endif

{{-- REEMBOLSADO --}}
@if ($order->status === 'refunded')
  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-accent); margin-top:20px;">
    <div style="display:flex; align-items:center; gap:16px;">
      <i class="bi bi-arrow-counterclockwise" style="font-size:32px; color:var(--admin-accent);"></i>
      <div style="flex:1;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Pedido reembolsado
        </strong>
        <span style="color:var(--admin-text-sec); font-size:13px;">
          @if ($order->refund_amount > 0)
            Valor reembolsado: <strong>R$ {{ number_format($order->refund_amount, 2, ',', '.') }}</strong>
            @if ($order->refunded_at)
              · {{ $order->refunded_at->format('d/m/Y') }}
            @endif
          @endif
        </span>
      </div>
    </div>
  </div>
@endif

    @if ($order->status === 'completed')
    <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-green); margin-top:20px;">
      <div style="display:flex; align-items:center; gap:16px;">
        <i class="bi bi-trophy-fill" style="font-size:32px; color:var(--admin-green);"></i>
        <div style="flex:1;">
          <strong style="color:var(--admin-text); display:block; font-size:14px;">
            Pedido concluído!
          </strong>
          <span style="color:var(--admin-text-sec); font-size:13px;">
            Obrigado por comprar na NeoShop. Esperamos te ver em breve!
          </span>
        </div>
      </div>
    </div>
    @endif

    {{-- SE HOUVER COMPROVATIVO --}}
    @if ($order->paymentProofs->isNotEmpty())
    <div class="admin-card" style="margin-top:20px;">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-file-earmark-check"></i> Comprovativo</h3>
      </div>
      <div class="admin-card-body">
        @php $proof = $order->paymentProofs->first(); @endphp
        @if ($proof)
        <div style="display:flex; align-items:center; gap:16px;">
          @if (str_starts_with($proof->file_type, 'image/'))
          <a href="{{ $proof->url }}" target="_blank">
            <img src="{{ $proof->url }}" style="width:80px; height:80px; object-fit:cover; border:1px solid var(--admin-border-strong);">
          </a>
          @endif
          <div>
            <div style="font-size:13px; color:var(--admin-text);">
              <i class="bi bi-check-circle-fill" style="color:var(--admin-green);"></i>
              Enviado em {{ $proof->created_at->format('d/m/Y H:i') }}
            </div>
            @if ($proof->isApproved())
            <div style="font-size:12px; color:var(--admin-green); margin-top:4px;">
              <i class="bi bi-patch-check-fill"></i> Aprovado
            </div>
            @elseif ($proof->isRejected())
            <div style="font-size:12px; color:var(--admin-red); margin-top:4px;">
              <i class="bi bi-x-circle-fill"></i> Rejeitado: {{ $proof->rejection_reason }}
            </div>
            @else
            <div style="font-size:12px; color:var(--admin-orange); margin-top:4px;">
              <i class="bi bi-hourglass-split"></i> Em análise
            </div>
            @endif
          </div>
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>

@endsection