<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pedido {{ $order->number }} - NeoShop</title>
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
      <a href="{{ route('tracking.index') }}">Rastreio</a>
      <span class="sep">/</span>
      <span class="current">{{ $order->number }}</span>
    </div>
  </div>

  <div class="container">
    <div class="track-content">
      @include('shop.tracking.partials.timeline', ['order' => $order])
    </div>
  </div>

  @include('shop.partials.footer')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>