<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pedido {{ $order->number }} - NeoShop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

  <style>
    /* Status do hero por estado */
    .order-status-awaiting_payment      { background: rgba(251,191,36,0.15); border-color: rgba(251,191,36,0.4); color: var(--warning); }
    .order-status-payment_under_review  { background: rgba(0,229,255,0.12);  border-color: rgba(0,229,255,0.4);  color: var(--accent-2); }
    .order-status-payment_rejected      { background: rgba(239,68,68,0.12);  border-color: rgba(239,68,68,0.4);  color: var(--danger); }
    .order-status-paid                  { background: rgba(34,197,94,0.12);  border-color: rgba(34,197,94,0.4);  color: var(--success); }
    .order-status-shipped               { background: rgba(124,92,255,0.12); border-color: rgba(124,92,255,0.4); color: var(--accent); }
    .order-status-delivered             { background: rgba(34,197,94,0.12);  border-color: rgba(34,197,94,0.4);  color: var(--success); }
    .order-status-completed             { background: rgba(34,197,94,0.15);  border-color: rgba(34,197,94,0.5);  color: var(--success); }
    .order-status-disputed              { background: rgba(239,68,68,0.12);  border-color: rgba(239,68,68,0.4);  color: var(--danger); }
    .order-status-refunded              { background: rgba(251,191,36,0.12); border-color: rgba(251,191,36,0.4); color: var(--warning); }
    .order-status-canceled              { background: rgba(107,114,128,0.12);border-color: rgba(107,114,128,0.4);color: var(--text-muted); }

    /* Ícone do hero por status */
    .order-hero-icon-awaiting           { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .order-hero-icon-under_review       { background: linear-gradient(135deg, #00e5ff, #0891b2); }
    .order-hero-icon-rejected           { background: linear-gradient(135deg, #ef4444, #b91c1c); }
    .order-hero-icon-paid               { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .order-hero-icon-shipped            { background: linear-gradient(135deg, #7c5cff, #5a3fff); }
    .order-hero-icon-delivered          { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .order-hero-icon-completed          { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .order-hero-icon-canceled           { background: linear-gradient(135deg, #6b7280, #4b5563); }

    .order-cta-big {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      padding: 16px 36px;
      background: linear-gradient(135deg, var(--accent), #5a3fff);
      color: #fff;
      text-decoration: none;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-size: 14px;
      box-shadow: 0 0 28px var(--accent-glow);
      margin-top: 20px;
      transition: 0.2s;
    }
    .order-cta-big:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 36px var(--accent-glow);
      color: #fff;
    }

    .order-cta-secondary {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 14px 28px;
      background: transparent;
      border: 2px solid var(--border-strong);
      color: var(--text-primary);
      text-decoration: none;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-size: 13px;
      margin-top: 20px;
      transition: 0.2s;
    }
    .order-cta-secondary:hover {
      border-color: var(--accent);
      color: var(--accent);
      background: var(--accent-light);
    }

    .order-proof-preview {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      background: var(--bg-tertiary);
      border: 1px solid var(--border-strong);
      color: var(--text-secondary);
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      text-decoration: none;
      margin: 2px;
      transition: 0.15s;
    }
    .order-proof-preview:hover {
      border-color: var(--accent);
      color: var(--accent);
      background: var(--accent-light);
    }

    .order-status-note {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 16px 20px;
      margin: 20px auto;
      max-width: 620px;
      border-left: 3px solid;
      text-align: left;
      font-size: 13px;
      line-height: 1.6;
    }
    .order-status-note i { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
  </style>
</head>
<body>

  @include('shop.partials.header')

  {{-- ============================================================ --}}
  {{-- HERO DINÂMICO                                                 --}}
  {{-- ============================================================ --}}
  @php
    $statusKey = $order->status;

    $statusMap = [
      'awaiting_payment'     => [
        'icon'   => 'hourglass-split',
        'title'  => 'Pedido criado!',
        'sub'    => 'Falta apenas o pagamento. Envie o comprovativo para liberar o pedido.',
        'badge'  => 'Aguardando Pagamento',
      ],
      'payment_under_review' => [
        'icon'   => 'hourglass-split',
        'title'  => 'Comprovativo em análise',
        'sub'    => 'Recebemos seu comprovativo. Nossa equipe vai analisar e liberar o pedido em breve.',
        'badge'  => 'Em Análise',
      ],
      'payment_rejected'     => [
        'icon'   => 'x-circle-fill',
        'title'  => 'Comprovativo rejeitado',
        'sub'    => 'O comprovativo enviado não foi aceito. Envie um novo para continuar.',
        'badge'  => 'Comprovativo Rejeitado',
      ],
      'paid'                 => [
        'icon'   => 'check-lg',
        'title'  => 'Pagamento aprovado!',
        'sub'    => 'Estamos preparando tudo. O vendedor já foi notificado.',
        'badge'  => 'Pagamento Aprovado',
      ],
      'shipped'              => [
        'icon'   => 'truck',
        'title'  => 'Pedido enviado!',
        'sub'    => 'Seu pedido saiu para entrega. Use o código de rastreio para acompanhar.',
        'badge'  => 'Enviado',
      ],
      'delivered'            => [
        'icon'   => 'box-seam-fill',
        'title'  => 'Pedido entregue!',
        'sub'    => 'Confirme o recebimento para liberar o pagamento ao vendedor.',
        'badge'  => 'Entregue',
      ],
      'completed'            => [
        'icon'   => 'trophy-fill',
        'title'  => 'Pedido concluído!',
        'sub'    => 'Obrigado pela sua compra. Esperamos que tenha gostado.',
        'badge'  => 'Concluído',
      ],
      'disputed'             => [
        'icon'   => 'exclamation-triangle-fill',
        'title'  => 'Reclamação aberta',
        'sub'    => 'Sua reclamação está em análise. Nossa equipe entrará em contato.',
        'badge'  => 'Em Disputa',
      ],
      'refunded'             => [
        'icon'   => 'arrow-counterclockwise',
        'title'  => 'Pedido reembolsado',
        'sub'    => 'O valor foi devolvido para você conforme combinado.',
        'badge'  => 'Reembolsado',
      ],
      'canceled'             => [
        'icon'   => 'x-circle',
        'title'  => 'Pedido cancelado',
        'sub'    => 'Este pedido foi cancelado. Se tiver dúvidas, fale com o suporte.',
        'badge'  => 'Cancelado',
      ],
    ];

    $st = $statusMap[$statusKey] ?? [
      'icon'  => 'info-circle',
      'title' => 'Pedido recebido',
      'sub'   => 'Estamos processando seu pedido.',
      'badge' => ucfirst($statusKey),
    ];

    $heroIconClass = 'order-hero-icon-' . str_replace('_', '', str_replace('awaiting_payment', 'awaiting', $statusKey));
    $heroIconClass = match ($statusKey) {
      'awaiting_payment'     => 'order-hero-icon-awaiting',
      'payment_under_review' => 'order-hero-icon-under_review',
      'payment_rejected'     => 'order-hero-icon-rejected',
      'paid'                 => 'order-hero-icon-paid',
      'shipped'              => 'order-hero-icon-shipped',
      'delivered'            => 'order-hero-icon-delivered',
      'completed'            => 'order-hero-icon-completed',
      'canceled'             => 'order-hero-icon-canceled',
      default                => 'order-hero-icon-awaiting',
    };

    $needsProof = in_array($statusKey, ['awaiting_payment', 'payment_rejected']);
  @endphp

  <section class="order-success-hero">
    <div class="order-success-hero-bg"></div>
    <div class="container">
      <div class="order-success-content">

        <div class="order-success-icon {{ $heroIconClass }}">
          <i class="bi bi-{{ $st['icon'] }}"></i>
          <div class="order-success-ring"></div>
        </div>

        <span class="order-success-badge order-status-{{ $statusKey }}">
          <i class="bi bi-{{ $st['icon'] }}"></i> {{ $st['badge'] }}
        </span>

        <h1 class="order-success-title">{{ $st['title'] }}</h1>
        <p class="order-success-sub">{{ $st['sub'] }}</p>

        <div class="order-number-box">
          <div class="order-number-label">Número do Pedido</div>
          <div class="order-number-value">
            {{ $order->number }}
            <button class="order-number-copy" onclick="copyOrderNumber()" title="Copiar">
              <i class="bi bi-clipboard"></i>
            </button>
          </div>
        </div>

        {{-- BOTÃO PRINCIPAL: enviar comprovativo --}}
        @if ($needsProof)
          <a href="{{ route('checkout.payment', $order) }}" class="order-cta-big">
            <i class="bi bi-cloud-upload-fill"></i>
            {{ $statusKey === 'payment_rejected' ? 'Enviar novo comprovativo' : 'Enviar comprovativo de pagamento' }}
          </a>
        @endif

        {{-- NOTA DE STATUS CONTEXTUAL --}}
        @if ($statusKey === 'awaiting_payment')
          <div class="order-status-note" style="background: rgba(251,191,36,0.08); border-color: var(--warning); color: var(--text-secondary);">
            <i class="bi bi-info-circle-fill" style="color: var(--warning);"></i>
            <div>
              <strong style="color: var(--warning);">Como pagar?</strong><br>
              Clique no botão acima para ver os dados bancários. Após transferir, envie o comprovativo.
            </div>
          </div>
        @endif

        @if ($statusKey === 'payment_under_review')
          <div class="order-status-note" style="background: rgba(0,229,255,0.08); border-color: var(--accent-2); color: var(--text-secondary);">
            <i class="bi bi-hourglass-split" style="color: var(--accent-2);"></i>
            <div>
              <strong style="color: var(--accent-2);">Aguardando aprovação</strong><br>
              Nossa equipe costuma analisar em até <strong>24 horas</strong>. Você receberá um e-mail assim que aprovarmos.
            </div>
          </div>
        @endif

        @if ($statusKey === 'payment_rejected' && $order->paymentProofs()->where('status', 'rejected')->exists())
          @php
            $rejected = $order->paymentProofs()->where('status', 'rejected')->latest()->first();
          @endphp
          <div class="order-status-note" style="background: rgba(239,68,68,0.08); border-color: var(--danger); color: var(--text-secondary);">
            <i class="bi bi-x-circle-fill" style="color: var(--danger);"></i>
            <div>
              <strong style="color: var(--danger);">Motivo da rejeição:</strong><br>
              {{ $rejected->rejection_reason ?? 'Comprovativo não pôde ser validado.' }}
            </div>
          </div>
        @endif

        {{-- TIMELINE DINÂMICA --}}
        @php
          $timelineDone = [
            'awaiting_payment'     => 0,
            'payment_under_review' => 1,
            'payment_rejected'     => 1,
            'paid'                 => 2,
            'shipped'              => 3,
            'delivered'            => 4,
            'completed'            => 5,
            'disputed'             => 4,
            'refunded'             => 5,
            'canceled'             => 0,
          ];
          $step = $timelineDone[$statusKey] ?? 0;
        @endphp

        <div class="order-timeline">
          <div class="order-timeline-step done">
            <div class="order-timeline-icon"><i class="bi bi-check-lg"></i></div>
            <div class="order-timeline-info">
              <strong>Pedido Criado</strong>
              <span>{{ $order->created_at->format('d/m H:i') }}</span>
            </div>
          </div>

          <div class="order-timeline-line {{ $step >= 1 ? 'done' : '' }}"></div>

          <div class="order-timeline-step {{ $step >= 2 ? 'done' : ($step === 1 ? 'active' : '') }}">
            <div class="order-timeline-icon"><i class="bi bi-check-lg"></i></div>
            <div class="order-timeline-info">
              <strong>Pagamento</strong>
              <span>
                @if ($order->paid_at)
                  {{ $order->paid_at->format('d/m H:i') }}
                @elseif ($statusKey === 'payment_under_review')
                  Em análise
                @elseif ($statusKey === 'payment_rejected')
                  Rejeitado
                @else
                  Aguardando
                @endif
              </span>
            </div>
          </div>

          <div class="order-timeline-line {{ $step >= 3 ? 'done' : '' }}"></div>

          <div class="order-timeline-step {{ $step >= 3 ? 'done' : '' }}">
            <div class="order-timeline-icon"><i class="bi bi-box-seam"></i></div>
            <div class="order-timeline-info">
              <strong>Enviado</strong>
              <span>{{ $order->shipped_at ? $order->shipped_at->format('d/m H:i') : 'Em breve' }}</span>
            </div>
          </div>

          <div class="order-timeline-line {{ $step >= 4 ? 'done' : '' }}"></div>

          <div class="order-timeline-step {{ $step >= 4 ? 'done' : '' }}">
            <div class="order-timeline-icon"><i class="bi bi-truck"></i></div>
            <div class="order-timeline-info">
              <strong>Em Transporte</strong>
              <span>{{ $order->shipped_at ? 'A caminho' : 'Em breve' }}</span>
            </div>
          </div>

          <div class="order-timeline-line {{ $step >= 5 ? 'done' : '' }}"></div>

          <div class="order-timeline-step {{ $step >= 5 ? 'done' : '' }}">
            <div class="order-timeline-icon"><i class="bi bi-house-door"></i></div>
            <div class="order-timeline-info">
              <strong>Entregue</strong>
              <span>{{ $order->delivered_at ? $order->delivered_at->format('d/m H:i') : 'Em breve' }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  {{-- ============================================================ --}}
  {{-- CONTEÚDO                                                      --}}
  {{-- ============================================================ --}}
  <div class="container">
    <div class="order-content">

      <div class="order-layout">

        {{-- COLUNA ESQUERDA --}}
        <div class="order-main">

          {{-- ITENS --}}
          <div class="order-card">
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

         <div class="order-card">
  <div class="order-card-header">
    <h2><i class="bi bi-geo-alt"></i> Endereço de Entrega</h2>
  </div>
  <div class="order-card-body">
    <div class="order-address">
      <div class="order-address-icon"><i class="bi bi-house-door-fill"></i></div>
      <div class="order-address-info">
        <strong>{{ $order->customer_name }}</strong>
        <p>
          {{-- Rua + número --}}
          {{ $order->shipping_address['street'] ?? '' }}, {{ $order->shipping_address['number'] ?? '' }}
          @if (!empty($order->shipping_address['complement']))
            — {{ $order->shipping_address['complement'] }}
          @endif
          <br>

          {{-- Bairro + município + província --}}
          {{ $order->shipping_address['neighborhood'] ?? '' }},
          {{ $order->shipping_address['municipality'] ?? '' }} —
          {{ $order->shipping_address['province'] ?? '' }}
        </p>

        @if (!empty($order->shipping_address['reference']))
          <p style="color:var(--text-muted);font-size:12px;margin:0;">
            <i class="bi bi-pin-map"></i> Ref: {{ $order->shipping_address['reference'] }}
          </p>
        @endif

        @if ($order->customer_phone)
          <span class="order-address-phone">
            <i class="bi bi-telephone-fill"></i> {{ $order->customer_phone }}
          </span>
        @endif
      </div>
    </div>
  </div>
</div>

          {{-- PAGAMENTO --}}
          <div class="order-card">
            <div class="order-card-header">
              <h2><i class="bi bi-credit-card"></i> Forma de Pagamento</h2>
            </div>
            <div class="order-card-body">
              <div class="order-payment">
                <div class="order-payment-icon">
                  <i class="bi bi-{{ $order->payment_method === 'pix' ? 'qr-code' : ($order->payment_method === 'boleto' ? 'barcode' : 'credit-card-2-front') }}"></i>
                </div>
                <div class="order-payment-info">
                  <strong>
                    {{ match ($order->payment_method) {
                        'pix'         => 'PIX',
                        'credit_card' => 'Cartão de Crédito',
                        'debit_card'  => 'Cartão de Débito',
                        'boleto'      => 'Boleto Bancário',
                        'transfer'    => 'Transferência Bancária',
                        'multicaixa'  => 'Multicaixa Express',
                        default       => ucfirst($order->payment_method),
                    } }}
                  </strong>
                  <p>
                    @if ($order->isPaid())
                      Pago em {{ $order->paid_at?->format('d/m/Y H:i') }}
                    @elseif ($statusKey === 'payment_under_review')
                      Comprovativo em análise
                    @elseif ($statusKey === 'payment_rejected')
                      Comprovativo rejeitado
                    @else
                      Aguardando pagamento
                    @endif
                  </p>
                </div>
                <span class="order-payment-badge">
                  <i class="bi bi-{{ $order->isPaid() ? 'check-circle-fill' : 'hourglass-split' }}"></i>
                  {{ $order->isPaid() ? 'Pago' : 'Pendente' }}
                </span>
              </div>

              {{-- COMPROVATIVOS ENVIADOS --}}
              @if ($order->paymentProofs()->count() > 0)
                <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border-subtle);">
                  <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700; margin-bottom:10px;">
                    Comprovativos enviados ({{ $order->paymentProofs()->count() }})
                  </div>
                  <div>
                    @foreach ($order->paymentProofs()->latest()->get() as $proof)
                      <a href="{{ $proof->url }}" target="_blank" class="order-proof-preview">
                        <i class="bi bi-{{ str_contains($proof->file_type, 'pdf') ? 'file-earmark-pdf' : 'file-earmark-image' }}"></i>
                        {{ $proof->created_at->format('d/m H:i') }}
                        <span style="color: {{ $proof->status === 'approved' ? 'var(--success)' : ($proof->status === 'rejected' ? 'var(--danger)' : 'var(--warning)') }};">
                          ({{ match($proof->status) { 'approved' => 'Aprovado', 'rejected' => 'Rejeitado', default => 'Em análise' } }})
                        </span>
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>
          </div>

          {{-- VENDEDOR --}}
          @if ($order->seller)
            <div class="order-card">
              <div class="order-card-header">
                <h2><i class="bi bi-shop"></i> Vendedor</h2>
              </div>
              <div class="order-card-body">
                <div style="display:flex; align-items:center; gap:14px;">
                  <div style="width:48px; height:48px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, var(--accent), var(--accent-2)); color:#fff; font-size:20px; font-weight:900; border-radius:50% !important;">
                    {{ strtoupper(substr($order->seller->name, 0, 1)) }}
                  </div>
                  <div>
                    <strong style="display:block; font-size:14px; color:var(--text-primary); margin-bottom:2px;">
                      {{ $order->seller->name }}
                      <i class="bi bi-patch-check-fill" style="color:var(--accent-2); font-size:12px;"></i>
                    </strong>
                    <span style="font-size:12px; color:var(--text-muted);">Vendedor verificado</span>
                  </div>
                </div>
              </div>
            </div>
          @endif

        </div>

        {{-- COLUNA DIREITA --}}
        <aside class="order-side">

          <div class="order-summary-card">
            <div class="order-summary-title">
              <i class="bi bi-receipt"></i> Resumo do Pedido
            </div>

            <div class="order-summary-lines">
              <div class="order-summary-line">
                <span>Subtotal</span>
                <span>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
              </div>

              @if ($order->discount > 0)
                <div class="order-summary-line">
                  <span>Desconto</span>
                  <span class="order-summary-discount">− R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                </div>
              @endif

              <div class="order-summary-line">
                <span>Frete</span>
                <span class="order-summary-free">
                  {{ $order->shipping > 0 ? 'R$ ' . number_format($order->shipping, 2, ',', '.') : 'Grátis' }}
                </span>
              </div>

              <div class="order-summary-line order-summary-line-total">
                <span>Total</span>
                <span class="order-summary-total">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
              </div>
            </div>

            <div class="order-actions">
              @if ($needsProof)
                <a href="{{ route('checkout.payment', $order) }}" class="order-btn order-btn-primary">
                  <i class="bi bi-cloud-upload-fill"></i> Enviar Comprovativo
                </a>
              @endif

              <a href="{{ route('home') }}" class="order-btn order-btn-secondary">
                <i class="bi bi-house-door"></i> Voltar à Loja
              </a>

              <a href="{{ route('shop.index') }}" class="order-btn order-btn-ghost">
                <i class="bi bi-grid-3x3-gap"></i> Continuar Comprando
              </a>
            </div>
          </div>

          {{-- AJUDA --}}
          <div class="order-help-card">
            <i class="bi bi-headset"></i>
            <h3>Precisa de ajuda?</h3>
            <p>Nossa equipe está pronta pra te ajudar com qualquer dúvida sobre seu pedido.</p>
            <a href="{{ route('shop.contact') ?? '/contato' }}" class="order-help-btn">
              <i class="bi bi-chat-dots"></i> Falar com Suporte
            </a>
          </div>

          {{-- SELOS --}}
          <div class="order-trust-card">
            <div class="order-trust-item">
              <i class="bi bi-shield-check"></i>
              <div>
                <strong>Compra Garantida</strong>
                <span>Reembolso em 7 dias</span>
              </div>
            </div>
            <div class="order-trust-item">
              <i class="bi bi-truck"></i>
              <div>
                <strong>Frete Grátis</strong>
                <span>Rastreamento completo</span>
              </div>
            </div>
            <div class="order-trust-item">
              <i class="bi bi-arrow-repeat"></i>
              <div>
                <strong>Devolução Fácil</strong>
                <span>Sem burocracia</span>
              </div>
            </div>
          </div>

        </aside>
      </div>

    </div>
  </div>

  @include('shop.partials.footer')

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>

  <script>
    function copyOrderNumber() {
      const num = '{{ $order->number }}';
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(num).then(function () {
          showToast('<i class="bi bi-check-circle-fill"></i><span>Número <strong>' + num + '</strong> copiado!</span>');
        });
      }
    }
  </script>
</body>
</html>