<header class="neo-header">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center top-bar mb-2 d-none d-md-flex">
      <div>
        <a href="#"><i class="bi bi-facebook"></i></a>
        <a href="#"><i class="bi bi-instagram"></i></a>
      </div>
      <div>
        <a href="#"><i class="bi bi-download"></i> Baixar App</a>
        <a href="#">Conectar</a>
        <a href="#"><i class="bi bi-bell"></i> Notificações</a>
        <a href="{{ route('help') }}"><i class="bi bi-question-circle"></i> Ajuda</a>

        @auth
          <a href="{{ route('profile') }}"><i class="bi bi-person"></i> {{ auth()->user()->name }}</a>
          <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:none;border:none;color:inherit;">
              <i class="bi bi-box-arrow-right"></i> Sair
            </button>
          </form>
        @else
          <a href="{{ route('register') }}"><i class="bi bi-person"></i> Registro</a>
          <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Login</a>
        @endauth
      </div>
    </div>

    <div class="row align-items-center g-3 pb-2">
      <div class="col-md-2 col-6">
        <a href="{{ route('home') }}" class="text-decoration-none fs-3 fw-bold logo-text">
          <i class="bi bi-lightning-charge-fill"></i> NeoShop
        </a>
      </div>
      <div class="col-md-8 col-12 order-md-2 order-3">
        <form action="{{ route('products.search') }}" method="GET" class="search-box">
          <input type="text" name="q" placeholder="O que você está procurando?" value="{{ request('q') }}">
          <button type="submit"><i class="bi bi-search"></i></button>
        </form>
      </div>
      <div class="col-md-2 col-6 order-md-3 order-2">
        <div class="header-actions">
          <a href="{{ route('favorites.index') }}" class="header-action-btn" title="Favoritos">
            <i class="bi bi-heart"></i>
            <span class="counter-badge">{{ $favoritesCount ?? 0 }}</span>
          </a>
          <a href="{{ route('cart.index') }}" class="header-action-btn" title="Carrinho">
            <i class="bi bi-cart3"></i>
            <span class="counter-badge">{{ $cartCount ?? 0 }}</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</header>