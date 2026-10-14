{{-- resources/views/shop/partials/product-card.blade.php --}}
@php
  $available = $product->available_stock;
  $soldOut   = $product->is_sold_out;
@endphp

<div class="product-card {{ $soldOut ? 'is-sold-out' : '' }}">
  <div class="product-img-wrapper">
    <img src="{{ $product->cover_url }}" alt="{{ $product->name }}">

    @if ($soldOut)
      <div class="sold-out-overlay">
        <span>Esgotado</span>
      </div>
    @elseif ($available <= 5)
      <div class="discount-badge" style="background:var(--warning);color:#1a1000;">
        Últimas {{ $available }}!
      </div>
    @endif
  </div>

  <div class="product-info">
    <div class="product-title">{{ $product->name }}</div>
    <div class="product-price">R$ {{ number_format($product->price, 2, ',', '.') }}</div>

    <button @disabled($soldOut) class="btn-add-cart">
      {{ $soldOut ? 'Indisponível' : 'Adicionar' }}
    </button>
  </div>
</div>