<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Meu Carrinho - NeoShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>

  @include('shop.partials.header')

  <div class="breadcrumb-bar">
    <div class="container">
      <a href="{{ route('home') }}">Home</a>
      <span class="sep">/</span>
      <span class="current">Meu Carrinho</span>
    </div>
  </div>
@if (!empty($outOfZone))
  <div style="background: rgba(239,68,68,0.08); border-left: 3px solid var(--danger); padding: 16px 20px; margin-bottom: 20px;">
    <div style="font-weight:800; color:var(--danger); text-transform:uppercase; font-size:12px; letter-spacing:1px; margin-bottom:10px;">
      <i class="bi bi-x-circle-fill"></i> Produtos fora do seu município
    </div>
    <ul style="margin:0 0 14px; padding-left:20px; color:var(--text-secondary); font-size:13px;">
      @foreach ($outOfZone as $ooz)
        <li>
          <strong style="color:var(--text-primary);">{{ $ooz['name'] }}</strong> — 
          vendedor <strong>{{ $ooz['seller'] }}</strong> entrega apenas em <strong>{{ $ooz['seller_municipality'] }}</strong>, 
          mas você está em <strong>{{ $ooz['customer_municipality'] }}</strong>.
        </li>
      @endforeach
    </ul>
    <p style="color:var(--text-secondary); font-size:13px; margin:0;">
      Remova esses itens ou altere o município de entrega para continuar.
    </p>
  </div>
@endif
  <div class="container">
    <div class="cart-page-wrap">

      <div class="cart-page-title">
        <h1><i class="bi bi-cart3"></i> Meu Carrinho</h1>
        <span class="cart-page-count" id="cartPageCount">
          {{ $cart->items_count }} {{ $cart->items_count === 1 ? 'item' : 'itens' }}
        </span>
      </div>

      {{-- ============================================================
           AVISO DE DIVERGÊNCIAS (preço mudou / estoque insuficiente)
           ============================================================ --}}
      @if (!empty($issues))
        <div class="cart-alert" style="
            background: rgba(251,191,36,0.08);
            border-left: 3px solid var(--warning);
            padding: 16px 20px;
            margin-bottom: 20px;
        ">
          <div style="
              font-weight: 800;
              color: var(--warning);
              text-transform: uppercase;
              font-size: 12px;
              letter-spacing: 1px;
              margin-bottom: 10px;
              display: flex;
              align-items: center;
              gap: 8px;
          ">
            <i class="bi bi-exclamation-triangle-fill"></i> Atenção
          </div>

          <ul style="
              margin: 0 0 14px;
              padding-left: 20px;
              color: var(--text-secondary);
              font-size: 13px;
              line-height: 1.6;
          ">
            @foreach ($issues as $issue)
              <li>{{ $issue['message'] }}</li>
            @endforeach
          </ul>

          <button type="button" onclick="cartSync()" style="
              background: transparent;
              border: 1px solid var(--warning);
              color: var(--warning);
              font-size: 12px;
              font-weight: 700;
              padding: 8px 16px;
              text-transform: uppercase;
              letter-spacing: 0.5px;
              cursor: pointer;
              display: inline-flex;
              align-items: center;
              gap: 8px;
          ">
            <i class="bi bi-arrow-clockwise"></i> Atualizar carrinho
          </button>
        </div>
      @endif

      @if ($items->isEmpty())

        {{-- ============ CARRINHO VAZIO ============ --}}
        <div style="
            text-align: center;
            padding: 80px 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-subtle);
        ">
          <i class="bi bi-cart-x" style="
              font-size: 80px;
              color: var(--border-strong);
              display: block;
              margin-bottom: 20px;
          "></i>
          <h2 style="
              font-size: 22px;
              color: var(--text-primary);
              margin-bottom: 10px;
              font-weight: 800;
          ">Seu carrinho está vazio</h2>
          <p style="color: var(--text-muted); margin-bottom: 24px;">
            Explore nossos produtos e adicione o que você gosta.
          </p>
          <a href="{{ route('shop.index') }}" style="
              display: inline-flex;
              align-items: center;
              gap: 8px;
              padding: 14px 28px;
              background: linear-gradient(135deg, var(--accent), #5a3fff);
              color: #fff;
              text-decoration: none;
              font-size: 13px;
              font-weight: 800;
              text-transform: uppercase;
              letter-spacing: 1px;
              box-shadow: 0 0 20px var(--accent-glow);
          ">
            <i class="bi bi-grid-3x3-gap"></i> Explorar Produtos
          </a>
        </div>

      @else

        <div class="cart-layout">

          {{-- ============================================================
               LISTA DE ITENS
               ============================================================ --}}
          <div class="cart-items-col" id="cartItemsCol">

            {{-- TOOLBAR --}}
            <div class="cart-toolbar">
              <label class="cart-check-all">
                <input type="checkbox" id="checkAll" checked>
                <span>Selecionar todos ({{ $cart->items_count }} itens)</span>
              </label>

              <button type="button" class="cart-toolbar-btn" onclick="cartClear()">
                <i class="bi bi-trash"></i> Remover todos
              </button>
            </div>

            {{-- ITENS --}}
            @foreach ($items as $item)
              @php
                $variantLabel = $item->variantItem ? $item->variantItem->value : null;
                $hasReservation = $item->reservation && ! $item->reservation->committed_at;
              @endphp

              <div class="cart-item-row" data-item-id="{{ $item->id }}">

                <label class="cart-item-check">
                  <input type="checkbox" checked>
                </label>

                <a href="{{ $item->product ? route('product.show', $item->product) : '#' }}" class="cart-item-img">
                  <img src="{{ $item->image_url }}" alt="{{ $item->display_name }}">
                </a>

                <div class="cart-item-info">
                  <a href="{{ $item->product ? route('product.show', $item->product) : '#' }}" class="cart-item-title">
                    {{ $item->product?->name ?? 'Produto removido' }}
                  </a>

                  @if ($variantLabel)
                    <div class="cart-item-variant">
                      <span>Variação: <strong>{{ $variantLabel }}</strong></span>
                    </div>
                  @endif

                  @if ($hasReservation)
                    <div class="cart-item-reservation" data-expires="{{ $item->reservation->expires_at->toIso8601String() }}">
                      <i class="bi bi-clock-history"></i>
                      <span>Reservado por <strong class="reservation-timer">--:--</strong></span>
                    </div>
                  @endif

                  <div class="cart-item-tags">
                    <span class="cart-tag cart-tag-free">
                      <i class="bi bi-truck"></i> Frete Grátis
                    </span>
                  </div>
                </div>

                <div class="cart-item-price-block">
                  <div class="cart-item-price">
                    R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                  </div>
                </div>

                <div class="cart-item-qty-control">
                  <button type="button" onclick="cartQty({{ $item->id }}, -1)">−</button>
                  <input type="text" value="{{ $item->quantity }}" readonly data-qty-input>
                  <button type="button" onclick="cartQty({{ $item->id }}, 1)">+</button>
                </div>

                <div class="cart-item-subtotal">
                  <div class="subtotal-label">Subtotal</div>
                  <div class="subtotal-value" data-subtotal>
                    R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                  </div>
                </div>

                <button type="button" class="cart-item-remove" title="Remover"
                        onclick="cartRemove({{ $item->id }})">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            @endforeach

            {{-- CONTINUAR COMPRANDO --}}
            <div class="cart-continue">
              <a href="{{ route('shop.index') }}">
                <i class="bi bi-arrow-left"></i> Continuar Comprando
              </a>
            </div>
          </div>

          {{-- ============================================================
               RESUMO DO PEDIDO
               ============================================================ --}}
          <aside class="cart-summary-col">
            <div class="cart-summary-card">

              <div class="cart-summary-title">
                <i class="bi bi-receipt"></i> Resumo do Pedido
              </div>

       {{-- CUPOM --}}
<div class="cart-coupon-block">

  @if ($cart->coupon_code)
    {{-- CUPOM APLICADO --}}
    <div class="cart-coupon-applied" id="couponApplied">
      <span>
        <i class="bi bi-check-circle-fill"></i>
        <strong id="couponAppliedCode">{{ $cart->coupon_code }}</strong> aplicado
      </span>
      <button type="button" onclick="removeCoupon()">Remover</button>
    </div>

    {{-- Input escondido, aparece se remover --}}
    <div class="cart-coupon-input" id="couponInputWrap" style="display:none;">
      <input type="text" id="couponCode" placeholder="Digite o cupom" style="text-transform:uppercase;">
      <button type="button" onclick="applyCoupon()">Aplicar</button>
    </div>
  @else
    {{-- SEM CUPOM --}}
    <div class="cart-coupon-label">
      <i class="bi bi-ticket-perforated"></i> Cupom de desconto
    </div>

    <div class="cart-coupon-input" id="couponInputWrap">
      <input type="text" id="couponCode" placeholder="Digite o cupom" style="text-transform:uppercase;">
      <button type="button" onclick="applyCoupon()">Aplicar</button>
    </div>

    <div class="cart-coupon-applied" id="couponApplied" style="display:none;">
      <span>
        <i class="bi bi-check-circle-fill"></i>
        <strong id="couponAppliedCode"></strong> aplicado
      </span>
      <button type="button" onclick="removeCoupon()">Remover</button>
    </div>
  @endif
</div>

{{-- VALORES --}}
<div class="cart-summary-lines">
  <div class="summary-line">
    <span>
      Subtotal (<span data-cart-items-count>{{ $cart->items_count }}</span> itens)
    </span>
    <span data-cart-subtotal>
      R$ {{ number_format($cart->subtotal, 2, ',', '.') }}
    </span>
  </div>

  <div class="summary-line">
    <span>Frete</span>
    <span class="summary-free">Grátis</span>
  </div>

  {{-- LINHA DO DESCONTO — só aparece se houver cupom --}}
  <div class="summary-line" id="summaryDiscountRow"
       style="{{ $cart->coupon_code && $cart->discount > 0 ? '' : 'display:none;' }}">
    <span>Cupom <strong id="summaryCouponCode">{{ $cart->coupon_code }}</strong></span>
    <span class="summary-discount" data-cart-discount>
      − R$ {{ number_format($cart->discount, 2, ',', '.') }}
    </span>
  </div>

  <div class="summary-line summary-line-total">
    <span>Total</span>
    <span class="summary-total" data-cart-total>
      R$ {{ number_format($cart->total, 2, ',', '.') }}
    </span>
  </div>

  <div class="summary-installments">
    em até <strong>12x de R$ {{ number_format($cart->total / 12, 2, ',', '.') }}</strong> sem juros
  </div>
</div>

              {{-- BOTÃO CHECKOUT --}}
              <button type="button" class="cart-checkout-btn" onclick="cartCheckout()">
                <i class="bi bi-bag-check"></i> Finalizar Compra
              </button>

              {{-- SELOS --}}
              <div class="cart-summary-seals">
                <div class="cart-seal">
                  <i class="bi bi-shield-check"></i>
                  <span>Compra<br>Garantida</span>
                </div>
                <div class="cart-seal">
                  <i class="bi bi-truck"></i>
                  <span>Frete<br>Grátis</span>
                </div>
                <div class="cart-seal">
                  <i class="bi bi-arrow-repeat"></i>
                  <span>Devolução<br>Fácil</span>
                </div>
              </div>
            </div>
          </aside>
        </div>
      @endif
    </div>
  </div>

  {{-- ==================== MINI CART (offcanvas) ==================== --}}
  <div class="offcanvas offcanvas-end offcanvas-cart" tabindex="-1" id="miniCart"> 
    <div id="miniCartContent">
      @include('shop.partials.mini-cart')
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  @include('shop.partials.footer')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/cart.js') }}"></script>

</body>
</html>