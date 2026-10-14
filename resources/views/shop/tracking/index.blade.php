<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rastrear Pedido - NeoShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>

  @include('shop.partials.header')

  <section class="page-hero">
    <div class="container">
      <span class="page-hero-badge"><i class="bi bi-truck"></i> Rastreamento</span>
      <h1 class="page-hero-title">Rastrear meu <span>Pedido</span></h1>
      <p class="page-hero-sub">Acompanhe em tempo real onde está a sua encomenda</p>

      <form method="GET" action="{{ route('tracking.index') }}" class="track-search">
        <i class="bi bi-search"></i>
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Digite o código de rastreio ou nº do pedido" required>
        <button type="submit">
          <i class="bi bi-arrow-right"></i> Rastrear
        </button>
      </form>
    </div>
  </section>

  <div class="container">
    <div class="track-content">

      @if ($searched && ! $order)
        <div style="text-align:center; padding:60px 20px; background:var(--bg-secondary); border:1px solid var(--border-subtle);">
          <i class="bi bi-search" style="font-size:64px; display:block; margin-bottom:16px; color:var(--border-strong);"></i>
          <h3 style="color:var(--text-primary); margin-bottom:8px;">Nenhum pedido encontrado</h3>
          <p style="color:var(--text-secondary);">Verifique o número digitado ou entre em contato com o suporte.</p>
        </div>
      @endif

      @if ($order)
        @include('shop.tracking.partials.timeline', ['order' => $order])
      @endif

    </div>
  </div>

  @include('shop.partials.footer')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>