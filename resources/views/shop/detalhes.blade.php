<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $product->name }} - NeoShop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

  <style>
    /* ============================================================
       GALERIA — thumbs SEMPRE 92×92 com LOOP INFINITO (≥ 6 imgs)
       ============================================================ */
    .gallery-carousel-wrap {
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid var(--border-subtle);
      position: relative;
    }
    .gallery-carousel-wrap .owl-carousel .owl-stage {
      display: flex;
      align-items: center;
    }
    .gallery-carousel-wrap .owl-carousel .owl-item {
      width: 100px !important;
      flex: 0 0 100px !important;
      padding: 4px;
      box-sizing: border-box;
    }
    .gallery-carousel-wrap .gallery-carousel-thumb {
      width: 92px !important;
      height: 92px !important;
      max-width: 92px !important;
      max-height: 92px !important;
      border: 2px solid var(--border-subtle);
      background: var(--bg-tertiary);
      cursor: pointer;
      transition: 0.15s;
      overflow: hidden;
      display: block;
      margin: 0 auto;
      box-sizing: border-box;
    }
    .gallery-carousel-wrap .gallery-carousel-thumb img {
      width: 100% !important;
      height: 100% !important;
      object-fit: cover;
      display: block;
    }
    .gallery-carousel-wrap .gallery-carousel-thumb:hover { border-color: var(--border-strong); }
    .gallery-carousel-wrap .gallery-carousel-thumb.active {
      border-color: var(--accent);
      box-shadow: 0 0 12px var(--accent-glow);
    }
    .gallery-carousel-wrap .owl-nav button.owl-prev,
    .gallery-carousel-wrap .owl-nav button.owl-next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 28px; height: 28px;
      background: var(--bg-tertiary) !important;
      border: 1px solid var(--border-strong) !important;
      color: var(--text-secondary) !important;
      font-size: 12px !important;
      display: flex !important;
      align-items: center; justify-content: center;
      opacity: 0;
      transition: opacity 0.2s, background 0.15s, color 0.15s;
      border-radius: 0 !important;
      padding: 0 !important;
      z-index: 5;
    }
    .gallery-carousel-wrap:hover .owl-nav button.owl-prev,
    .gallery-carousel-wrap:hover .owl-nav button.owl-next { opacity: 1; }
    .gallery-carousel-wrap .owl-nav button.owl-prev { left: 4px; }
    .gallery-carousel-wrap .owl-nav button.owl-next { right: 4px; }
    .gallery-carousel-wrap .owl-nav button.owl-prev:hover,
    .gallery-carousel-wrap .owl-nav button.owl-next:hover {
      background: var(--accent) !important;
      color: #fff !important;
      border-color: var(--accent) !important;
    }
    .gallery-carousel-wrap.few-images .owl-carousel {
      display: flex; justify-content: center; gap: 8px;
    }
    .gallery-carousel-wrap.few-images .owl-item {
      width: 100px !important; flex: 0 0 100px !important;
    }
    .gallery-carousel-wrap.few-images .owl-nav { display: none !important; }

    /* ============================================================
       BADGE DE PROMOÇÃO NO TOPO
       ============================================================ */
    .promo-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 12px;
    }
    .promo-badge-top {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, var(--accent-3), #b01578);
      color: #fff;
      font-size: 11px;
      font-weight: 800;
      padding: 6px 14px;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 0 16px rgba(255,44,156,0.4);
      animation: pulseFlash 2s ease-in-out infinite;
    }
    .promo-timer {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255,44,156,0.12);
      border: 1px solid rgba(255,44,156,0.4);
      color: var(--accent-3);
      padding: 6px 14px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .promo-timer strong {
      font-family: 'Courier New', monospace;
      font-size: 14px;
      font-weight: 900;
      letter-spacing: 1px;
      font-variant-numeric: tabular-nums;
    }

    /* ============================================================
       BLOCO DE PREÇO COM ECONOMIA
       ============================================================ */
    .price-savings {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(34,197,94,0.12);
      border: 1px solid rgba(34,197,94,0.3);
      color: var(--success);
      font-size: 12px;
      font-weight: 800;
      padding: 4px 10px;
      margin-top: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .price-savings strong { font-size: 13px; }

    /* ============================================================
       CARD DE CUPONS
       ============================================================ */
    .coupon-list-card {
      background: linear-gradient(135deg, rgba(124,92,255,0.06) 0%, rgba(0,229,255,0.03) 100%);
      border: 1px solid rgba(124,92,255,0.25);
      padding: 14px 16px;
      margin-top: 12px;
      border-left: 3px solid var(--accent);
    }
    .coupon-list-title {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--accent);
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .coupon-list-items { display: flex; flex-direction: column; gap: 8px; }
    .coupon-list-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 10px;
      background: var(--bg-tertiary);
      border: 1px dashed var(--border-strong);
      cursor: pointer;
      transition: 0.15s;
    }
    .coupon-list-item:hover {
      border-color: var(--accent);
      background: var(--accent-light);
    }
    .coupon-list-item-icon {
      width: 32px; height: 32px;
      display: flex; align-items: center; justify-content: center;
      background: var(--accent-light);
      color: var(--accent);
      font-size: 15px;
      flex-shrink: 0;
    }
    .coupon-list-item-body { flex: 1; min-width: 0; }
    .coupon-list-item-body strong {
      display: block;
      color: var(--accent-2);
      font-size: 12px;
      font-weight: 800;
      font-family: monospace;
      letter-spacing: 0.5px;
      margin-bottom: 2px;
    }
    .coupon-list-item-body span {
      display: block;
      font-size: 11px;
      color: var(--text-muted);
      line-height: 1.35;
    }
    .coupon-list-copy {
      background: none;
      border: 1px solid var(--border-strong);
      color: var(--text-muted);
      width: 30px; height: 30px;
      display: flex; align-items: center; justify-content: center;
      font-size: 12px;
      cursor: pointer;
      transition: 0.15s;
      flex-shrink: 0;
    }
    .coupon-list-copy:hover {
      border-color: var(--accent);
      color: var(--accent);
      background: var(--accent-light);
    }

    .variant-swatch {
      display: inline-block;
      width: 14px; height: 14px;
      border: 1px solid var(--border-strong);
      vertical-align: middle;
      margin-right: 6px;
    }
  </style>
</head>
<body>

  @include('shop.partials.header')

  <div class="breadcrumb-bar">
    <div class="container">
      <a href="{{ route('home') }}">Home</a>
      <span class="sep">/</span>
      @if ($product->categoryRelation)
        <a href="{{ route('shop.category', $product->categoryRelation) }}">{{ $product->categoryRelation->name }}</a>
        <span class="sep">/</span>
      @endif
      <span class="current">{{ $product->name }}</span>
    </div>
  </div>

  @php
    $available = $product->available_stock;
    $soldOut   = $product->is_sold_out;
    $discount  = $product->old_price ? round((1 - $product->price / $product->old_price) * 100) : 0;

    // -------- Galeria --------
    $galleryImages = collect();

    if ($product->coverImage) {
        $galleryImages->push(['full' => $product->coverImage->url, 'thumb' => $product->coverImage->url]);
    } elseif ($product->image) {
        $galleryImages->push(['full' => asset('storage/' . $product->image), 'thumb' => asset('storage/' . $product->image)]);
    } else {
        $galleryImages->push([
            'full'  => 'https://picsum.photos/seed/p' . $product->id . '/1200/1200',
            'thumb' => 'https://picsum.photos/seed/p' . $product->id . '/300/300',
        ]);
    }

    foreach ($product->images as $img) {
        if ($img->is_cover) continue;
        $galleryImages->push(['full' => $img->url, 'thumb' => $img->url]);
    }

    $firstImage = $galleryImages->first();

    // -------- Variante inicial --------
    $initialVariantItem = null;
    foreach ($product->variants as $variant) {
        foreach ($variant->items as $it) {
            if (! $it->is_sold_out) {
                $initialVariantItem = $it;
                break 2;
            }
        }
    }

    $initialPrice     = $initialVariantItem
                        ? ($initialVariantItem->price > 0 ? $initialVariantItem->price : $product->price)
                        : $product->price;
    $initialAvailable = $initialVariantItem
                        ? $initialVariantItem->available_stock
                        : $available;

    // -------- Cupons ativos do produto --------
    $activeCoupons = \App\Models\Coupon::active()
        ->where(function ($q) use ($product) {
            $q->where('applies_to', 'all')
              ->orWhere(function ($q2) use ($product) {
                  $q2->where('applies_to', 'products')
                     ->whereJsonContains('product_ids', $product->id);
              })
              ->orWhere(function ($q2) use ($product) {
                  $q2->where('applies_to', 'categories')
                     ->whereJsonContains('category_ids', $product->category_id);
              });
        })
        ->orderByDesc('value')
        ->limit(3)
        ->get();

    // -------- Promoção ativa --------
    $activePromotion = \App\Models\Promotion::active()
        ->where(function ($q) use ($product) {
            $q->where('applies_to', 'all')
              ->orWhere(function ($q2) use ($product) {
                  $q2->where('applies_to', 'products')
                     ->whereJsonContains('product_ids', $product->id);
              })
              ->orWhere(function ($q2) use ($product) {
                  $q2->where('applies_to', 'categories')
                     ->whereJsonContains('category_ids', $product->category_id);
              });
        })
        ->orderByDesc('priority')
        ->first();

    // -------- Cálculo do preço "promocional" --------
    // Se houver promoção ativa, ela é aplicada visualmente ao preço
    $promoPrice = $initialPrice;
    $promoOldPrice = $product->old_price;

    if ($activePromotion) {
        if ($activePromotion->type === 'percent') {
            $promoPrice = round($initialPrice * (1 - $activePromotion->value / 100), 2);
            $promoOldPrice = $initialPrice;
        } elseif ($activePromotion->type === 'fixed') {
            $promoPrice = max(0, $initialPrice - $activePromotion->value);
            $promoOldPrice = $initialPrice;
        }
    }

    $promoDiscount = $promoOldPrice && $promoOldPrice > $promoPrice
        ? round((1 - $promoPrice / $promoOldPrice) * 100)
        : 0;

    $savings = $promoOldPrice && $promoOldPrice > $promoPrice
        ? ($promoOldPrice - $promoPrice)
        : 0;
  @endphp

  <div class="container">
    <div class="product-detail-wrap">
      <div class="row g-4">

        {{-- ============================================================ --}}
        {{-- GALERIA                                                      --}}
        {{-- ============================================================ --}}
        <div class="col-lg-5">
          <div class="gallery-zone">
            <div class="gallery-main-container">
              <div class="gallery-main" id="galleryMain">
                <img src="{{ $firstImage['full'] }}" alt="{{ $product->name }}" id="galleryMainImg">

                @if ($promoDiscount > 0)
                  <div class="gallery-badge">-{{ $promoDiscount }}% OFF</div>
                @endif

                <div class="gallery-hint">
                  <i class="bi bi-zoom-in"></i> Passe o mouse para ampliar
                </div>

                <div class="zoom-lens" id="zoomLens"></div>
              </div>

              <div class="zoom-panel" id="zoomPanel">
                <img src="{{ $firstImage['full'] }}" id="zoomPanelImg" alt="Zoom">
              </div>
            </div>
          </div>

          <div class="gallery-carousel-wrap">
            <div class="owl-carousel owl-theme" id="galleryCarousel">
              @foreach ($galleryImages as $idx => $img)
                <div class="gallery-carousel-thumb {{ $idx === 0 ? 'active' : '' }}"
                     data-img="{{ $img['full'] }}">
                  <img src="{{ $img['thumb'] }}" alt="Foto {{ $idx + 1 }}">
                </div>
              @endforeach
            </div>
          </div>
        </div>

        {{-- ============================================================ --}}
        {{-- INFO DO PRODUTO                                              --}}
        {{-- ============================================================ --}}
        <div class="col-lg-7">

          {{-- PROMOÇÃO: BADGE + RELÓGIO --}}
          @if ($activePromotion)
            <div class="promo-header">
              <div class="promo-badge-top">
                <i class="bi bi-fire"></i> {{ $activePromotion->name }}
              </div>

              @if ($activePromotion->expires_at)
                <div class="promo-timer" data-expires="{{ $activePromotion->expires_at->toIso8601String() }}">
                  <i class="bi bi-alarm"></i>
                  <span>Termina em</span>
                  <strong class="promo-timer-value">--:--:--</strong>
                </div>
              @endif
            </div>
          @endif

          <h1 class="product-title-main">{{ $product->name }}</h1>

          <div class="product-meta-row">
            <div class="rating">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-half"></i>
              <span class="rating-number">4.8</span>
            </div>
            <span class="rating-count">{{ $product->sold }} avaliações</span>
            <span class="divider"></span>
            <span>{{ $product->sold }} vendidos</span>
            @if ($product->categoryRelation)
              <span class="divider"></span>
              <span><i class="bi bi-tag"></i> {{ $product->categoryRelation->name }}</span>
            @endif
          </div>

          {{-- PREÇO COM RISCO + ECONOMIA --}}
          <div class="price-block">
            <div>
              <span class="price-old" id="priceOld"
                    style="{{ $promoOldPrice ? '' : 'display:none;' }}">
                R$ {{ $promoOldPrice ? number_format($promoOldPrice, 2, ',', '.') : '' }}
              </span>

              <span class="price-new">
                <small>R$</small>
                <span id="priceNew">{{ number_format($promoPrice, 2, ',', '.') }}</span>
              </span>

              <span class="price-discount" id="priceDiscount"
                    style="{{ $promoDiscount > 0 ? '' : 'display:none;' }}">
                -{{ $promoDiscount }}%
              </span>
            </div>

            <div class="price-installments">
              em até <strong>
                12x de R$ <span id="priceInstallment">{{ number_format($promoPrice / 12, 2, ',', '.') }}</span>
              </strong> sem juros no cartão
            </div>

            @if ($savings > 0)
              <div class="price-savings">
                <i class="bi bi-piggy-bank-fill"></i>
                Você economiza <strong>R$ {{ number_format($savings, 2, ',', '.') }}</strong>
              </div>
            @endif
          </div>

          {{-- CUPONS --}}
          @if ($activeCoupons->isNotEmpty())
            <div class="coupon-list-card">
              <div class="coupon-list-title">
                <i class="bi bi-ticket-perforated-fill"></i> Cupons disponíveis para este produto
              </div>

              <div class="coupon-list-items">
                @foreach ($activeCoupons as $coupon)
                  @php
                    $couponLabel = match ($coupon->type) {
                        'percent'       => rtrim(rtrim(number_format($coupon->value, 0, ',', '.'), '0'), ',') . '% OFF',
                        'fixed'         => 'R$ ' . number_format($coupon->value, 0, ',', '.') . ' OFF',
                        'free_shipping' => 'Frete Grátis',
                        default         => 'Desconto',
                    };
                  @endphp

                  <div class="coupon-list-item" onclick="copyCouponCode('{{ $coupon->code }}', this)">
                    <div class="coupon-list-item-icon">
                      <i class="bi bi-ticket-perforated"></i>
                    </div>

                    <div class="coupon-list-item-body">
                      <strong>{{ $coupon->code }}</strong>
                      <span>
                        {{ $couponLabel }}
                        @if ($coupon->min_purchase > 0)
                          · mín. R$ {{ number_format($coupon->min_purchase, 2, ',', '.') }}
                        @endif
                      </span>
                    </div>

                    <button type="button" class="coupon-list-copy"
                            onclick="event.stopPropagation(); copyCouponCode('{{ $coupon->code }}', this)"
                            title="Copiar código">
                      <i class="bi bi-clipboard"></i>
                    </button>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          {{-- VARIANTES --}}
          @foreach ($product->variants as $variant)
            <div class="variant-group" data-variant-id="{{ $variant->id }}">
              <div class="variant-label">
                {{ $variant->name }}:
                <span class="selected-value" id="selected-variant-{{ $variant->id }}">
                  {{ $initialVariantItem ? $initialVariantItem->value : '—' }}
                </span>
              </div>

              <div class="variant-options" data-variant-id="{{ $variant->id }}"
                   data-required="{{ $variant->required ? '1' : '0' }}">
                @foreach ($variant->items as $item)
                  @php
                    $itemSoldOut = $item->is_sold_out;
                    $isSelected  = $initialVariantItem && $initialVariantItem->id === $item->id;

                    // Preço efetivo desta variação com promoção
                    $itemBasePrice = $item->price > 0 ? $item->price : $product->price;
                    $itemFinalPrice = $itemBasePrice;

                    if ($activePromotion) {
                        if ($activePromotion->type === 'percent') {
                            $itemFinalPrice = round($itemBasePrice * (1 - $activePromotion->value / 100), 2);
                        } elseif ($activePromotion->type === 'fixed') {
                            $itemFinalPrice = max(0, $itemBasePrice - $activePromotion->value);
                        }
                    }
                  @endphp

                  <button type="button"
                          class="variant-option {{ $itemSoldOut ? 'disabled' : '' }} {{ $isSelected ? 'selected' : '' }}"
                          data-item-id="{{ $item->id }}"
                          data-value="{{ $item->value }}"
                          data-price="{{ $itemFinalPrice }}"
                          data-base-price="{{ $itemBasePrice }}"
                          data-available="{{ $item->available_stock }}"
                          data-image="{{ $item->image ? asset('storage/' . $item->image) : '' }}"
                          @disabled($itemSoldOut)>
                    @if ($item->color_hex)
                      <span class="variant-swatch"
                            style="background:{{ $item->color_hex }};"></span>
                    @endif
                    {{ $item->value }}
                    @if ($itemSoldOut)
                      <small style="opacity:.6;">(esgotado)</small>
                    @elseif ($item->is_low_stock)
                      <small style="color:var(--warning);">últimas {{ $item->available_stock }}</small>
                    @endif
                  </button>
                @endforeach
              </div>
            </div>
          @endforeach

          {{-- QUANTIDADE --}}
          <div class="qty-block">
            <div class="qty-control">
              <button type="button" onclick="changeQtyDetail(-1)">−</button>
              <input type="text" value="1" id="qtyDetail" readonly>
              <button type="button" onclick="changeQtyDetail(1)">+</button>
            </div>

            <div class="stock-info" id="stockInfo">
              @if ($soldOut)
                <span style="color:var(--danger);">
                  <i class="bi bi-x-circle"></i> Produto esgotado
                </span>
              @else
                Estoque: <strong id="stockValue">{{ $initialAvailable }} unidades</strong>
              @endif
            </div>
          </div>

          {{-- AÇÕES --}}
          <div class="action-buttons">
            @if ($soldOut)
              <button class="btn-action btn-add-cart-detail" disabled
                      style="opacity:.4;cursor:not-allowed;">
                <i class="bi bi-x-circle"></i> Esgotado
              </button>
            @else
              <button type="button"
                      class="btn-action btn-add-cart-detail js-detail-add"
                      onclick="detailAddToCart(this)">
                <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
              </button>

              <button type="button"
                      class="btn-action btn-buy-now js-detail-buy"
                      onclick="detailBuyNow(this)">
                <i class="bi bi-lightning-charge-fill"></i> Comprar Agora
              </button>
            @endif

            <button type="button" class="btn-icon-round" onclick="toggleFavorite(this)" title="Favoritar">
              <i class="bi bi-heart"></i>
            </button>

            <div class="share-wrapper">
              <button type="button" class="btn-icon-round" id="shareBtn" onclick="toggleShareMenu(event)" title="Compartilhar">
                <i class="bi bi-share"></i>
              </button>
              <div class="share-menu" id="shareMenu">
                <button class="share-item whatsapp" onclick="shareTo('whatsapp')" title="WhatsApp"><i class="bi bi-whatsapp"></i></button>
                <button class="share-item facebook" onclick="shareTo('facebook')" title="Facebook"><i class="bi bi-facebook"></i></button>
                <button class="share-item twitter"  onclick="shareTo('twitter')"  title="Twitter"><i class="bi bi-twitter-x"></i></button>
                <button class="share-item telegram" onclick="shareTo('telegram')" title="Telegram"><i class="bi bi-telegram"></i></button>
                <button class="share-item copy"     onclick="shareTo('copy')"     title="Copiar link"><i class="bi bi-link-45deg"></i></button>
              </div>
            </div>
          </div>

          {{-- GARANTIAS --}}
          <div class="guarantees">
            <div class="guarantee-item">
              <i class="bi bi-shield-check"></i>
              <div><strong>Compra Garantida</strong> Reembolso em 7 dias</div>
            </div>
            <div class="guarantee-item">
              <i class="bi bi-truck"></i>
              <div><strong>Frete Grátis</strong> Envio em 24h</div>
            </div>
            <div class="guarantee-item">
              <i class="bi bi-arrow-repeat"></i>
              <div><strong>Devolução Fácil</strong> Sem burocracia</div>
            </div>
          </div>

          {{-- VENDEDOR --}}
          @if ($product->user)
            <div class="seller-card">
              <div class="seller-avatar">{{ strtoupper(substr($product->user->name, 0, 1)) }}</div>
              <div class="seller-info">
                <h6>{{ $product->user->name }} <i class="bi bi-patch-check-fill verified"></i></h6>
                <p>
                  <strong>4.9</strong> de avaliação ·
                  <strong>{{ $product->sold }}</strong> vendas
                </p>
              </div>
              <div class="seller-actions">
                <button class="seller-btn">Ver loja</button>
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- DESCRIÇÃO --}}
      @if ($product->description)
        <div class="content-tabs" style="margin-top:24px;">
          <div class="content-tabs-nav">
            <button class="content-tab-btn active">
              <i class="bi bi-file-text"></i> Descrição
            </button>
          </div>
          <div class="content-tab-pane active" style="display:block;">
            <div class="description-content">
              {!! nl2br(e($product->description)) !!}
            </div>
          </div>
        </div>
      @endif

      {{-- RELACIONADOS --}}
      @if ($related->count())
        <div class="related-section">
          <div class="section-header">
            <h3><i class="bi bi-stars"></i> Você também pode gostar</h3>
            <a href="{{ route('shop.index') }}" class="btn-see-all">
              Ver tudo <i class="bi bi-chevron-right"></i>
            </a>
          </div>

          <div class="related-grid">
            @foreach ($related as $rel)
              @php
                $relDiscount = $rel->old_price ? round((1 - $rel->price / $rel->old_price) * 100) : 0;
                $relUrl = route('product.show', $rel);
              @endphp
              <div class="product-card">
                <div class="product-img-wrapper">
                  <a href="{{ $relUrl }}"><img src="{{ $rel->cover_url }}" alt="{{ $rel->name }}"></a>
                  @if ($relDiscount > 0)
                    <div class="discount-badge">-{{ $relDiscount }}%</div>
                  @endif
                </div>
                <div class="product-info">
                  <div class="product-title">
                    <a href="{{ $relUrl }}">{{ $rel->name }}</a>
                  </div>
                  <div>
                    <span class="product-price"><small>R$</small> {{ number_format($rel->price, 2, ',', '.') }}</span>
                  </div>
                  <div class="product-meta">
                    <span class="product-rating"><i class="bi bi-star-fill"></i> 4.8</span>
                    <span class="product-sold">{{ $rel->sold }} vendidos</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- MINI CART --}}
  <div class="offcanvas offcanvas-end offcanvas-cart" tabindex="-1" id="miniCart">
    <div id="miniCartContent">
      @include('shop.partials.mini-cart')
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  @include('shop.partials.footer')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/cart.js') }}"></script>

  <script>
  // ============================================================
  // GALERIA — LOOP INFINITO (≥ 6) ou ESTÁTICA (< 6)
  // ============================================================
  $(function () {
    const $carousel = $('#galleryCarousel');
    const $wrap     = $('.gallery-carousel-wrap');
    if (!$carousel.length) return;

    const totalImages = $carousel.find('.gallery-carousel-thumb').length;

    if (totalImages === 1) {
      $wrap.addClass('single-image');
      return;
    }

    if (totalImages < 6) {
      $wrap.addClass('few-images');
      $carousel.owlCarousel({
        loop: false, autoplay: false, smartSpeed: 300,
        nav: false, dots: false, margin: 8, autoWidth: false,
        responsive: {
          0:    { items: Math.min(totalImages, 3) },
          576:  { items: Math.min(totalImages, 4) },
          768:  { items: totalImages },
          992:  { items: totalImages },
          1200: { items: totalImages }
        }
      });
      $carousel.on('click', '.gallery-carousel-thumb', function () {
        const src = $(this).attr('data-img');
        if (!src) return;
        $('#galleryMainImg, #zoomPanelImg').attr('src', src);
        $('.gallery-carousel-thumb').removeClass('active');
        $(this).addClass('active');
      });
      return;
    }

    $carousel.owlCarousel({
      loop: true, autoplay: true, autoplayTimeout: 3500,
      autoplayHoverPause: true, autoplaySpeed: 400, smartSpeed: 400,
      nav: true, dots: false, margin: 8, navRewind: false, autoWidth: false,
      navText: ['<i class="bi bi-chevron-left"></i>', '<i class="bi bi-chevron-right"></i>'],
      responsive: {
        0:    { items: 3 },
        576:  { items: 4 },
        768:  { items: 5 },
        992:  { items: 6 },
        1200: { items: 6 }
      }
    });

    $carousel.on('click', '.gallery-carousel-thumb', function () {
      const src = $(this).attr('data-img');
      if (!src) return;
      $('#galleryMainImg, #zoomPanelImg').attr('src', src);
      $('.gallery-carousel-thumb').removeClass('active');
      $(this).addClass('active');
    });

    $carousel.on('translated.owl.carousel', function () {
      const $active = $carousel.find('.owl-item.active').first()
                              .find('.gallery-carousel-thumb').first();
      if (!$active.length) return;
      const src = $active.attr('data-img');
      if (!src) return;
      $('#galleryMainImg, #zoomPanelImg').attr('src', src);
      $('.gallery-carousel-thumb').removeClass('active');
      $active.addClass('active');
    });
  });

  // ============================================================
  // RELÓGIO DA PROMOÇÃO
  // ============================================================
  (function () {
    const timer = document.querySelector('.promo-timer');
    if (!timer) return;

    const expiresAt = new Date(timer.dataset.expires).getTime();
    const valueEl   = timer.querySelector('.promo-timer-value');

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
      const diff = Math.floor((expiresAt - Date.now()) / 1000);

      if (diff <= 0) {
        valueEl.textContent = '00:00:00';
        timer.style.opacity = '0.5';
        timer.style.borderColor = 'var(--border-strong)';
        timer.style.color = 'var(--text-muted)';
        clearInterval(interval);
        return;
      }

      const h = Math.floor(diff / 3600);
      const m = Math.floor((diff % 3600) / 60);
      const s = diff % 60;

      valueEl.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);

      // Cor por urgência
      if (diff < 60) {
        timer.style.color = 'var(--danger)';
        timer.style.borderColor = 'var(--danger)';
      } else if (diff < 3600) {
        timer.style.color = 'var(--warning)';
        timer.style.borderColor = 'var(--warning)';
      }
    }

    tick();
    const interval = setInterval(tick, 1000);
  })();

  // ============================================================
  // VARIANTES — preço atualiza com promoção
  // ============================================================
  function updatePriceDisplay(unitPrice, available, imageUrl) {
    $('#priceNew').text(unitPrice.toFixed(2).replace('.', ','));
    $('#priceInstallment').text((unitPrice / 12).toFixed(2).replace('.', ','));

    if (available > 0) {
      $('#stockInfo').html('Estoque: <strong id="stockValue">' + available + ' unidades</strong>');
    } else {
      $('#stockInfo').html('<span style="color:var(--danger);"><i class="bi bi-x-circle"></i> Esgotado nesta variação</span>');
    }

    if (imageUrl) {
      $('#galleryMainImg, #zoomPanelImg').attr('src', imageUrl);
    }
  }

  $('.variant-options').each(function () {
    const $group = $(this);

    $group.find('.variant-option').on('click', function () {
      if ($(this).hasClass('disabled')) return;

      $group.find('.variant-option').removeClass('selected');
      $(this).addClass('selected');

      const variantId = $group.data('variant-id');
      $('#selected-variant-' + variantId).text($(this).data('value'));

      const unitPrice = parseFloat($(this).data('price')) || 0;
      const available = parseInt($(this).data('available'), 10) || 0;
      const imageUrl  = $(this).data('image') || '';

      updatePriceDisplay(unitPrice, available, imageUrl);
    });
  });

  // ============================================================
  // QUANTIDADE
  // ============================================================
  function changeQtyDetail(delta) {
    const $input = $('#qtyDetail');
    let val = parseInt($input.val()) + delta;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    $input.val(val);
  }

  // ============================================================
  // HELPERS
  // ============================================================
  function getSelectedVariantItemId() {
    const $selected = $('.variant-option.selected').first();
    return $selected.length ? parseInt($selected.data('item-id'), 10) : null;
  }

  function validateVariants() {
    let ok = true;
    $('.variant-options').each(function () {
      const $group = $(this);
      const required = String($group.data('required')) === '1';
      if (!required) return;
      if (!$group.find('.variant-option.selected').length) ok = false;
    });
    return ok;
  }

  function isSelectedItemSoldOut() {
    const $selected = $('.variant-option.selected').first();
    if (!$selected.length) return false;
    return $selected.hasClass('disabled') || parseInt($selected.data('available'), 10) <= 0;
  }

  // ============================================================
  // ADICIONAR / COMPRAR
  // ============================================================
  function detailAddToCart(btn) {
    if (!validateVariants()) {
      showToast('Escolha uma variação para continuar.', 'error');
      return;
    }
    if (isSelectedItemSoldOut()) {
      showToast('Esta variação está esgotada.', 'error');
      return;
    }

    const variantItemId = getSelectedVariantItemId();
    const qty = parseInt($('#qtyDetail').val(), 10);

    cartAdd({{ $product->id }}, variantItemId, qty, btn);
  }

  function detailBuyNow(btn) {
    if (!validateVariants()) {
      showToast('Escolha uma variação para continuar.', 'error');
      return;
    }
    if (isSelectedItemSoldOut()) {
      showToast('Esta variação está esgotada.', 'error');
      return;
    }

    const variantItemId = getSelectedVariantItemId();
    const qty = parseInt($('#qtyDetail').val(), 10);

    if (btn) btn.disabled = true;

    cartAdd({{ $product->id }}, variantItemId, qty, btn);

    setTimeout(function () {
      window.location.href = '{{ route('checkout.index') }}';
    }, 900);
  }

  // ============================================================
  // COPIAR CUPOM
  // ============================================================
  function copyCouponCode(code, el) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code).then(function () {
        showToast('<i class="bi bi-check-circle-fill"></i><span>Cupom <strong>' + code + '</strong> copiado!</span>');
      }).catch(function () { fallbackCopyCoupon(code); });
    } else {
      fallbackCopyCoupon(code);
    }
  }

  function fallbackCopyCoupon(code) {
    const ta = document.createElement('textarea');
    ta.value = code;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
      showToast('<i class="bi bi-check-circle-fill"></i><span>Cupom <strong>' + code + '</strong> copiado!</span>');
    } catch (e) {
      showToast('Não foi possível copiar.', 'error');
    }
    document.body.removeChild(ta);
  }
  </script>
</body>
</html>