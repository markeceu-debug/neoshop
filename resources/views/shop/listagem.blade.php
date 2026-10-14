<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Produtos - NeoShop</title>

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
      <span class="current">Produtos</span>
    </div>
  </div>
<div class="container" style="margin-top:16px;">

  {{-- AVISO 1: cliente SEM município --}}
  @if (empty($customerMunicipality))
    <div style="background: linear-gradient(135deg, rgba(124,92,255,0.12), rgba(0,229,255,0.06)); border: 1px solid rgba(124,92,255,0.3); border-left: 4px solid var(--accent); padding: 16px 20px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 12px;">
      <i class="bi bi-geo-alt-fill" style="font-size: 24px; color: var(--accent);"></i>
      <div style="flex:1; min-width: 200px;">
        <strong style="display: block; color: var(--text-primary); margin-bottom: 4px;">
          Escolha o seu município
        </strong>
        <span style="font-size: 13px; color: var(--text-secondary);">
          Mostramos apenas produtos que entregamos na sua região.
        </span>
      </div>

      <form method="GET" action="{{ url()->current() }}" style="display: flex; gap: 8px; align-items: center;">
        <input type="text" name="municipality" placeholder="Ex: Belas" required
               value="{{ request('municipality') }}"
               style="padding: 10px 14px; background: var(--bg-tertiary); border: 1px solid var(--border-strong); color: var(--text-primary); font-family: inherit; min-width: 180px;">
        <button type="submit" style="padding: 10px 20px; background: var(--accent); border: none; color: #fff; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; cursor: pointer;">
          Confirmar
        </button>
      </form>
    </div>
  @endif

  {{-- AVISO 2: cliente COM município --}}
  @if (! empty($customerMunicipality))
    <div style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.3); border-left: 4px solid var(--success); padding: 12px 20px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
      <i class="bi bi-check-circle-fill" style="font-size: 20px; color: var(--success);"></i>
      <div style="flex:1; min-width: 200px;">
        <span style="font-size: 13px; color: var(--text-secondary);">
          Entregando em <strong style="color: var(--text-primary);">{{ $customerMunicipality }}</strong>
        </span>
      </div>
      <a href="{{ route('shop.clear-municipality') }}"
         style="font-size: 12px; color: var(--accent); text-decoration: none; font-weight: 700;">
        <i class="bi bi-pencil"></i> Alterar
      </a>
    </div>
  @endif

  {{-- AVISO 3: cliente COM município mas SEM produtos locais --}}
  @if (! empty($noProductsInMunicipality))
    <div style="background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.3); border-left: 4px solid var(--warning); padding: 16px 20px; margin-bottom: 12px;">
      <strong style="color: var(--warning); display: block; margin-bottom: 4px;">
        <i class="bi bi-exclamation-triangle-fill"></i> Ainda não temos vendedores em {{ $customerMunicipality }}
      </strong>
      <span style="font-size: 13px; color: var(--text-secondary);">
        Mostrando produtos de outras regiões. Volte em breve!
      </span>
    </div>
  @endif

</div>
  <div class="container">
    <div class="listing-wrap">

      <aside class="filters-sidebar">
        <div class="filters-title">
          <span><i class="bi bi-funnel"></i> Filtros</span>
          <a href="{{ route('shop.index') }}" class="filter-clear">Limpar</a>
        </div>

        <form method="GET" action="{{ route('shop.index') }}">
          <div class="filter-group">
            <div class="filter-group-title">
              <span>Categoria</span>
              <i class="bi bi-chevron-down"></i>
            </div>
            <div class="filter-group-body">
              @foreach ($categories as $cat)
                <label class="filter-option">
                  <input type="radio" name="categoria" value="{{ $cat->slug }}"
                         {{ ($filters['categoria'] ?? '') === $cat->slug ? 'checked' : '' }}>
                  <span>{{ $cat->name }}</span>
                  <span class="count">({{ $cat->products()->count() }})</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="filter-group">
            <div class="filter-group-title">
              <span>Preço</span>
              <i class="bi bi-chevron-down"></i>
            </div>
            <div class="filter-group-body">
              <div class="price-inputs">
                <input type="number" name="min" placeholder="Min" value="{{ $filters['min'] ?? '' }}">
                <span>até</span>
                <input type="number" name="max" placeholder="Max" value="{{ $filters['max'] ?? '' }}">
              </div>
              <button type="submit" class="btn-apply-price">Aplicar</button>
            </div>
          </div>

          @if ($filters['q'] ?? null)
            <input type="hidden" name="q" value="{{ $filters['q'] }}">
          @endif
        </form>
      </aside>

      <main class="listing-main">

        <div class="listing-toolbar">
          <div class="listing-count">
            <strong>{{ $total }}</strong> produtos encontrados
          </div>

          <div class="listing-toolbar-right">
            <div class="view-toggle">
              <button class="view-btn" data-view="grid" title="Grade">
                <i class="bi bi-grid-3x3-gap-fill"></i>
              </button>
              <button class="view-btn active" data-view="list" title="Lista">
                <i class="bi bi-list-ul"></i>
              </button>
            </div>

            <select class="sort-select" onchange="window.location.href='?sort='+this.value">
              <option value="relevance"  {{ ($filters['sort'] ?? '') === 'relevance'  ? 'selected' : '' }}>Mais Relevantes</option>
              <option value="price_asc"  {{ ($filters['sort'] ?? '') === 'price_asc'  ? 'selected' : '' }}>Menor Preço</option>
              <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>Maior Preço</option>
              <option value="rating"     {{ ($filters['sort'] ?? '') === 'rating'     ? 'selected' : '' }}>Mais Vendidos</option>
              <option value="newest"     {{ ($filters['sort'] ?? '') === 'newest'     ? 'selected' : '' }}>Novidades</option>
            </select>
          </div>
        </div>

        <div class="products-list list-view" id="productsList">

          @forelse ($products as $product)
            @php
              $available     = $product->available_stock;
              $soldOut       = $product->is_sold_out;
              $lowStock      = $product->is_low_stock;
              $hasVariants   = (bool) $product->has_variants;
              $finalPrice    = $product->final_price;
              $originalPrice = $product->original_price;
              $discount      = $product->discount_percent;
              $detailUrl     = route('product.show', $product);
            @endphp

            <div class="product-card" data-product-id="{{ $product->id }}">

              <a href="{{ $detailUrl }}" class="product-img-wrapper d-block">
                <img src="{{ $product->cover_url }}" alt="{{ $product->name }}">

                @if ($discount > 0 && ! $soldOut)
                  <div class="discount-badge">-{{ $discount }}%</div>
                @endif

                @if ($soldOut)
                  <div class="discount-badge" style="background:var(--danger);">Esgotado</div>
                @elseif ($lowStock)
                  <div class="discount-badge" style="background:var(--warning); color:#1a1000;">
                    Últimas {{ $available }}
                  </div>
                @endif
              </a>

              <div class="product-info">
                <div class="product-title">
                  <a href="{{ $detailUrl }}">{{ $product->name }}</a>
                </div>

                <div class="product-desc">
                  {{ \Illuminate\Support\Str::limit($product->description ?: 'Produto em destaque na NeoShop com ótimo custo-benefício e envio rápido.', 140) }}
                </div>

                <div>
                  <span class="product-price">
                    <small>R$</small> {{ number_format($finalPrice, 2, ',', '.') }}
                  </span>

                  @if ($originalPrice)
                    <span class="product-old-price">
                      R$ {{ number_format($originalPrice, 2, ',', '.') }}
                    </span>
                  @endif
                </div>

                <div class="product-meta">
                  <span class="product-rating"><i class="bi bi-star-fill"></i> 4.8</span>
                  <span class="product-sold">{{ $product->sold }} vendidos</span>
                  @if ($product->categoryRelation)
                    <span class="product-sold">
                      <i class="bi bi-tag"></i> {{ $product->categoryRelation->name }}
                    </span>
                  @endif
                </div>
              </div>

              <div class="product-actions-list">

                <a href="{{ $detailUrl }}" class="btn-list-action btn-list-view">
                  <i class="bi bi-eye"></i> Ver detalhes
                </a>

                @if ($soldOut)
                  <button class="btn-list-action" disabled
                          style="opacity:.4; cursor:not-allowed; background:var(--bg-tertiary); color:var(--text-muted);">
                    <i class="bi bi-x-circle"></i> Esgotado
                  </button>

                @elseif ($hasVariants)
                  <a href="{{ $detailUrl }}"
                     class="btn-list-action btn-list-cart"
                     title="Escolha uma variação">
                    <i class="bi bi-diagram-3"></i> Escolher
                  </a>

                @else
                  <button type="button"
                          class="btn-list-action btn-list-cart js-add-to-cart"
                          data-product-id="{{ $product->id }}"
                          data-variant-item-id=""
                          onclick="cartAdd({{ $product->id }}, null, 1, this)">
                    <i class="bi bi-cart-plus"></i> Adicionar
                  </button>
                @endif

                @unless ($soldOut)
                  <a href="{{ $detailUrl }}#comprar"
                     class="btn-list-action btn-list-buy">
                    <i class="bi bi-lightning-charge-fill"></i> Comprar
                  </a>
                @endunless

              </div>
            </div>
          @empty
            <div style="text-align:center; padding:80px 20px; color:var(--text-muted);">
              <i class="bi bi-search" style="font-size:64px; display:block; margin-bottom:12px;"></i>
              Nenhum produto encontrado
            </div>
          @endforelse

        </div>

        @if ($products->hasPages())
          <div class="pagination-bar">
            {{ $products->links('pagination::bootstrap-5') }}
          </div>
        @endif

      </main>
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
@include('shop.partials.toast')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/cart.js') }}"></script>

  <script>
  function applyView(view) {
    const $list = $('#productsList');
    $('.view-btn').removeClass('active');
    $('.view-btn[data-view="' + view + '"]').addClass('active');
    $list.toggleClass('list-view', view === 'list');
    localStorage.setItem('neoView', view);
  }

  $(function () {
    applyView(localStorage.getItem('neoView') || 'list');

    $('.view-btn').on('click', function () {
      applyView($(this).data('view'));
    });
  });
  </script>
</body>
</html>