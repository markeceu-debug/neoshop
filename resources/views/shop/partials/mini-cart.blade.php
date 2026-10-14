@php
    $cart  = $cart  ?? app(\App\Services\CartService::class)->resolveCart(false);
    $items = $items ?? ($cart ? $cart->items()->with(['product', 'variantItem', 'reservation'])->get() : collect());
@endphp

<div class="cart-header d-flex justify-content-between align-items-center">
  <h5>
    <i class="bi bi-cart3"></i> Carrinho
    (<span id="cartCount">{{ $cart ? $cart->items_count : 0 }}</span>)
  </h5>
  <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
</div>

<div class="offcanvas-body p-0" style="flex:1; overflow-y:auto;">
  <div id="cartItems">
    @if ($items->isEmpty())
      <div class="cart-empty" id="cartEmpty">
        <i class="bi bi-cart-x"></i>
        <p>Seu carrinho está vazio</p>
      </div>
    @else
      @foreach ($items as $item)
        @php
          $unitPrice = (float) $item->unit_price;
          $originalPrice = null;

          if ($item->product && $item->product->active_promotion) {
              $originalPrice = (float) $item->product->price;
          } elseif ($item->product && $item->product->old_price && (float) $item->product->old_price > $unitPrice) {
              $originalPrice = (float) $item->product->old_price;
          }
        @endphp

        <div class="cart-item" data-item-id="{{ $item->id }}">
          <img src="{{ $item->image_url }}" alt="{{ $item->display_name }}">

          <div class="cart-item-info">
            <div class="cart-item-title">{{ $item->display_name }}</div>

            <div class="cart-item-price">
              R$ {{ number_format($unitPrice, 2, ',', '.') }}

              @if ($originalPrice)
                <small style="text-decoration:line-through; color:var(--text-muted); font-weight:400; margin-left:6px; font-size:11px;">
                  R$ {{ number_format($originalPrice, 2, ',', '.') }}
                </small>
              @endif
            </div>

            <div class="cart-item-qty">
              <button type="button" onclick="cartQty({{ $item->id }}, -1)">−</button>
              <span>{{ $item->quantity }}</span>
              <button type="button" onclick="cartQty({{ $item->id }}, 1)">+</button>
            </div>
          </div>

          <button class="btn-remove" type="button" onclick="cartRemove({{ $item->id }})">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      @endforeach
    @endif
  </div>
</div>

@if ($cart && $items->isNotEmpty())
  <div class="cart-footer" id="cartFooter">
    <div class="cart-total">
      <span class="label">Total:</span>
      <span class="cart-total-price" id="cartTotal">
        R$ {{ number_format($cart->total, 2, ',', '.') }}
      </span>
    </div>
    <a href="{{ route('cart.index') }}"
       class="btn-checkout"
       style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
      <i class="bi bi-bag-check"></i> Ver Carrinho
    </a>
  </div>
@else
  <div class="cart-footer" id="cartFooter">
    <a href="{{ route('shop.index') }}"
       class="btn-checkout"
       style="text-decoration:none; display:flex; align-items:center; justify-content:center; background:transparent; color:var(--accent); border:2px solid var(--accent);">
      <i class="bi bi-grid-3x3-gap"></i> Explorar Produtos
    </a>
  </div>
@endif