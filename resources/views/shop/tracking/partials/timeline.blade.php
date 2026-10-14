@php
  $statusMap = [
    'awaiting_payment'     => ['label' => 'Aguardando Pagamento',  'class' => 'warning'],
    'payment_under_review' => ['label' => 'Comprovativo em Análise','class' => 'info'],
    'payment_rejected'     => ['label' => 'Comprovativo Rejeitado', 'class' => 'danger'],
    'paid'                 => ['label' => 'Pago',                  'class' => 'success'],
    'shipped'              => ['label' => 'Em Transporte',         'class' => 'info'],
    'delivered'            => ['label' => 'Entregue',              'class' => 'success'],
    'completed'            => ['label' => 'Concluído',             'class' => 'success'],
    'canceled'             => ['label' => 'Cancelado',             'class' => 'danger'],
    'refunded'             => ['label' => 'Reembolsado',           'class' => 'warning'],
    'disputed'             => ['label' => 'Em Disputa',            'class' => 'danger'],
  ];

  $currentStatus = $statusMap[$order->status] ?? ['label' => 'Processando', 'class' => 'info'];

  // Progresso (5 etapas)
  $steps = [
    'awaiting_payment'     => 1,
    'payment_under_review' => 2,
    'payment_rejected'     => 2,
    'paid'                 => 3,
    'preparing'            => 3,
    'shipped'              => 4,
    'delivered'            => 5,
    'completed'            => 5,
  ];
  $currentStep = $steps[$order->status] ?? 1;
@endphp

{{-- HEADER DO PEDIDO --}}
<div class="track-header">
  <div class="track-header-info">
    <span class="track-header-label">Pedido</span>
    <strong class="track-header-code">#{{ $order->number }}</strong>
    <span class="track-header-date">
      Realizado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}
    </span>
  </div>

  <div class="track-header-status">
    <span class="track-status-badge track-status-{{ $currentStatus['class'] }}">
      <i class="bi bi-info-circle"></i> {{ $currentStatus['label'] }}
    </span>

    @if ($order->status === 'delivered' && $order->auto_release_at)
      <span class="track-header-eta">
        Libera em <strong>{{ $order->auto_release_at->format('d/m/Y') }}</strong>
      </span>
    @endif
  </div>
</div>

{{-- BARRA DE PROGRESSO --}}
<div class="track-progress-card">
  <div class="track-progress-title">
    <i class="bi bi-graph-up-arrow"></i> Progresso do Pedido
  </div>

  <div class="track-progress-steps">
    @php
      $progressSteps = [
        1 => ['icon' => 'cart-check',   'label' => 'Recebido'],
        2 => ['icon' => 'file-check',   'label' => 'Análise'],
        3 => ['icon' => 'box-seam',     'label' => 'Preparando'],
        4 => ['icon' => 'truck',        'label' => 'Em Transporte'],
        5 => ['icon' => 'house-door',   'label' => 'Entregue'],
      ];
    @endphp

    @foreach ($progressSteps as $num => $step)
      @php
        $isDone   = $num < $currentStep;
        $isActive = $num === $currentStep;
      @endphp

      <div class="track-progress-step {{ $isDone ? 'done' : '' }} {{ $isActive ? 'active' : '' }}">
        <div class="track-progress-icon">
          <i class="bi bi-{{ $isDone ? 'check-lg' : $step['icon'] }}"></i>
        </div>
        <span>{{ $step['label'] }}</span>
      </div>

      @if ($num < 5)
        <div class="track-progress-line {{ $isDone ? 'done' : '' }}"></div>
      @endif
    @endforeach
  </div>
</div>

{{-- TIMELINE DE EVENTOS --}}
<div class="track-timeline-card">
  <div class="track-timeline-header">
    <h3><i class="bi bi-clock-history"></i> Histórico de Rastreamento</h3>
  </div>

  <div class="track-timeline">
    @forelse ($order->trackingEvents as $idx => $event)
      <div class="track-event {{ $idx === 0 ? 'track-event-latest' : '' }}">
        <div class="track-event-icon {{ $idx === 0 ? 'track-event-icon-active' : '' }}">
          <i class="bi bi-{{ $event->icon }}"></i>
        </div>

        <div class="track-event-body">
          <div class="track-event-head">
            <strong>{{ $event->title }}</strong>
            @if ($idx === 0)
              <span class="track-event-badge">Último</span>
            @endif
          </div>

          @if ($event->description)
            <p style="margin:6px 0; color:var(--text-secondary); font-size:13px;">
              {{ $event->description }}
            </p>
          @endif

          @if ($event->location)
            <div class="track-event-location">
              <i class="bi bi-geo-alt-fill"></i> {{ $event->location }}
            </div>
          @endif

          <div class="track-event-date">
            <i class="bi bi-clock"></i> {{ $event->created_at->format('d/m/Y \à\s H:i') }}
          </div>
        </div>
      </div>
    @empty
      <div style="text-align:center; padding:40px; color:var(--text-muted);">
        <i class="bi bi-clock-history" style="font-size:48px; display:block; margin-bottom:12px; opacity:.4;"></i>
        Sem eventos ainda
      </div>
    @endforelse
  </div>
</div>

{{-- ITENS DO PEDIDO --}}
<div class="order-card" style="margin-top:24px;">
  <div class="order-card-header">
    <h2><i class="bi bi-box-seam"></i> Itens do Pedido ({{ $order->items->count() }})</h2>
  </div>
  <div class="order-card-body">
    @foreach ($order->items as $item)
      <div class="order-item">
        <div class="order-item-img">
          <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}">
        </div>
        <div class="order-item-info">
          <a href="#" class="order-item-title">{{ $item->product_name }}</a>
          @if ($item->variant_value)
            <div class="order-item-variant">Variação: <strong>{{ $item->variant_value }}</strong></div>
          @endif
          <div class="order-item-qty">Quantidade: <strong>{{ $item->quantity }}</strong></div>
        </div>
        <div class="order-item-price">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</div>
      </div>
    @endforeach
  </div>
</div>