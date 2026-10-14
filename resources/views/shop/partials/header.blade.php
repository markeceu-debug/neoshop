<header class="neo-header">
  <div class="container">
    <div class="row align-items-center g-3 pb-2">
      <div class="col-md-2 col-6">
        <a href="{{ route('home') }}" class="text-decoration-none fs-3 fw-bold logo-text">
          <i class="bi bi-lightning-charge-fill"></i> NeoShop
        </a>
      </div>
      <div class="col-md-8 col-12 order-md-2 order-3">
        <div class="search-box">
          <input type="text" placeholder="O que você está procurando?">
          <button><i class="bi bi-search"></i></button>
        </div>
      </div>
      <div class="col-md-2 col-6 order-md-3 order-2">
        <div class="header-actions">
          <button class="header-action-btn" title="Favoritos">
            <i class="bi bi-heart"></i>
            <span class="counter-badge">0</span>
          </button>
          <button class="header-action-btn" title="Carrinho"
                  data-bs-toggle="offcanvas" data-bs-target="#miniCart">
            <i class="bi bi-cart3"></i>
            <span class="counter-badge" id="cartCountBadge">{{ app(\App\Services\CartService::class)->count() }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</header>