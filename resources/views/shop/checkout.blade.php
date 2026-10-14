<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Checkout - NeoShop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

  <style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .checkout-label {
      display: block;
      font-size: 11px;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .checkout-input {
      width: 100%;
      padding: 12px 14px;
      background: var(--bg-tertiary);
      border: 1px solid var(--border-strong);
      color: var(--text-primary);
      font-size: 14px;
      font-family: inherit;
      outline: none;
      transition: 0.15s;
    }
    .checkout-input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-light);
    }
    .checkout-input:disabled {
      opacity: .5;
      cursor: not-allowed;
    }
  </style>
</head>
<body>

  <header class="neo-header">
    <div class="container">
      <div class="row align-items-center g-3 py-2">
        <div class="col-md-4 col-6">
          <a href="{{ route('home') }}" class="text-decoration-none fs-3 fw-bold logo-text">
            <i class="bi bi-lightning-charge-fill"></i> NeoShop
          </a>
        </div>
        <div class="col-md-8 col-6">
          <div class="checkout-header-steps">
            <div class="checkout-step-mini active"><span class="step-num">1</span> Dados</div>
            <i class="bi bi-chevron-right step-arrow"></i>
            <div class="checkout-step-mini active"><span class="step-num">2</span> Entrega</div>
            <i class="bi bi-chevron-right step-arrow"></i>
            <div class="checkout-step-mini active"><span class="step-num">3</span> Pagamento</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="breadcrumb-bar">
    <div class="container">
      <a href="{{ route('home') }}">Home</a>
      <span class="sep">/</span>
      <a href="{{ route('cart.index') }}">Carrinho</a>
      <span class="sep">/</span>
      <span class="current">Checkout</span>
    </div>
  </div>

  <div class="container">
    <div class="checkout-wrap">

      <div class="checkout-page-title">
        <h1><i class="bi bi-bag-check"></i> Finalizar Compra</h1>
        <span class="checkout-secure">
          <i class="bi bi-shield-lock-fill"></i> Ambiente seguro
        </span>
      </div>

      {{-- AVISOS DE DIVERGÊNCIA --}}
      @if (!empty($issues))
        <div class="cart-alert" style="background: rgba(251,191,36,0.1); border-left: 3px solid var(--warning); padding: 16px 20px; margin-bottom: 24px;">
          <div style="font-weight:800; color:var(--warning); text-transform:uppercase; font-size:12px; letter-spacing:1px; margin-bottom:10px;">
            <i class="bi bi-exclamation-triangle-fill"></i> Atenção
          </div>
          <ul style="margin:0 0 12px; padding-left:20px; color:var(--text-secondary); font-size:13px;">
            @foreach ($issues as $issue)
              <li>{{ $issue['message'] }}</li>
            @endforeach
          </ul>
          <a href="{{ route('cart.index') }}" class="page-btn page-btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar ao carrinho
          </a>
        </div>
      @endif

      <form id="checkoutForm" onsubmit="submitCheckout(event)">
        @csrf

        <div class="checkout-layout">

          {{-- ============ COLUNA ESQUERDA ============ --}}
          <div class="checkout-steps-col">

            {{-- ============ ETAPA 1: DADOS + ENDEREÇO ============ --}}
            <div class="checkout-block active">
              <div class="checkout-block-header">
                <div class="checkout-block-num">1</div>
                <div class="checkout-block-title">
                  <h3>Dados de Entrega</h3>
                  <p>Quem vai receber o pedido?</p>
                </div>
              </div>
              <div class="checkout-block-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                  {{-- NOME --}}
                  <div>
                    <label class="checkout-label">Nome completo *</label>
                    <input type="text" name="name" required
                           value="{{ auth()->user()->name ?? old('name') }}"
                           class="checkout-input">
                  </div>

                  {{-- EMAIL --}}
                  <div>
                    <label class="checkout-label">E-mail *</label>
                    <input type="email" name="email" required
                           value="{{ auth()->user()->email ?? old('email') }}"
                           class="checkout-input">
                  </div>

                  {{-- TELEFONE --}}
                  <div>
                    <label class="checkout-label">Telefone *</label>
                    <input type="tel" name="phone" required
                           value="{{ auth()->user()->telefone ?? old('phone') }}"
                           placeholder="923 000 000"
                           class="checkout-input">
                  </div>

                  {{-- BAIRRO --}}
                  <div>
                    <label class="checkout-label">Bairro *</label>
                    <input type="text" name="shipping_address[neighborhood]" required
                           value="{{ old('shipping_address.neighborhood') }}"
                           placeholder="Ex: Talatona"
                           class="checkout-input">
                  </div>

                  {{-- PROVÍNCIA --}}
                  <div>
                    <label class="checkout-label">Província *</label>
                    <select name="shipping_address[province]" id="province" required
                            class="checkout-input">
                      <option value="">Selecione...</option>
                      @foreach ($provinces as $prov => $munis)
                        <option value="{{ $prov }}"
                          {{ old('shipping_address.province', $cart->customer_province) === $prov ? 'selected' : '' }}>
                          {{ $prov }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  {{-- MUNICÍPIO --}}
                  <div>
                    <label class="checkout-label">Município *</label>
                    <select name="shipping_address[municipality]" id="municipality" required
                            class="checkout-input">
                      <option value="">Selecione a província primeiro</option>
                    </select>
                  </div>

                  {{-- RUA --}}
                  <div style="grid-column: 1 / -1;">
                    <label class="checkout-label">Rua / Avenida *</label>
                    <input type="text" name="shipping_address[street]" required
                           value="{{ old('shipping_address.street') }}"
                           placeholder="Ex: Rua das Flores"
                           class="checkout-input">
                  </div>

                  {{-- NÚMERO --}}
                  <div>
                    <label class="checkout-label">Número / Casa *</label>
                    <input type="text" name="shipping_address[number]" required
                           value="{{ old('shipping_address.number') }}"
                           class="checkout-input">
                  </div>

                  {{-- COMPLEMENTO --}}
                  <div>
                    <label class="checkout-label">Complemento</label>
                    <input type="text" name="shipping_address[complement]"
                           value="{{ old('shipping_address.complement') }}"
                           placeholder="Apto, bloco..."
                           class="checkout-input">
                  </div>

                  {{-- REFERÊNCIA --}}
                  <div style="grid-column: 1 / -1;">
                    <label class="checkout-label">Ponto de Referência</label>
                    <input type="text" name="shipping_address[reference]"
                           value="{{ old('shipping_address.reference') }}"
                           placeholder="Ex: Perto do Kero, ao lado da farmácia..."
                           class="checkout-input">
                  </div>

                </div>
              </div>
            </div>

            {{-- ============ ETAPA 2: ENTREGA ============ --}}
            <div class="checkout-block active">
              <div class="checkout-block-header">
                <div class="checkout-block-num">2</div>
                <div class="checkout-block-title">
                  <h3>Opção de Entrega</h3>
                  <p id="shippingSubtitle">Preencha o endereço primeiro</p>
                </div>
              </div>
              <div class="checkout-block-body">
                <div class="shipping-list" id="shippingList">
                  <div style="text-align:center; padding:32px; color:var(--text-muted);">
                    <i class="bi bi-geo-alt" style="font-size:42px; display:block; margin-bottom:12px; opacity:.4;"></i>
                    Preencha o município para ver as opções de entrega
                  </div>
                </div>
              </div>
            </div>

            {{-- ============ ETAPA 3: PAGAMENTO ============ --}}
            <div class="checkout-block active">
              <div class="checkout-block-header">
                <div class="checkout-block-num">3</div>
                <div class="checkout-block-title">
                  <h3>Forma de Pagamento</h3>
                  <p>Como você prefere pagar?</p>
                </div>
              </div>
              <div class="checkout-block-body">
                <div class="payment-list">

                  <label class="payment-option selected">
                    <input type="radio" name="payment_method" value="transfer" checked>
                    <div class="payment-radio"></div>
                    <div class="payment-icon payment-pix"><i class="bi bi-bank"></i></div>
                    <div class="payment-info">
                      <div class="payment-name">Transferência Bancária</div>
                      <div class="payment-desc">IBAN · Multicaixa · Depósito</div>
                    </div>
                    <div class="payment-tag payment-tag-success">Aprovação manual</div>
                  </label>

                  <label class="payment-option">
                    <input type="radio" name="payment_method" value="multicaixa">
                    <div class="payment-radio"></div>
                    <div class="payment-icon payment-card"><i class="bi bi-qr-code"></i></div>
                    <div class="payment-info">
                      <div class="payment-name">Multicaixa Express</div>
                      <div class="payment-desc">Envio do comprovativo por imagem</div>
                    </div>
                  </label>

                </div>

                <div style="margin-top:20px; padding:16px; background:var(--bg-tertiary); border-left:3px solid var(--accent); font-size:13px; color:var(--text-secondary); line-height:1.6;">
                  <strong style="color:var(--text-primary); display:block; margin-bottom:6px;">
                    <i class="bi bi-info-circle-fill" style="color:var(--accent);"></i> Como funciona
                  </strong>
                  Após finalizar, você receberá os <strong>dados bancários</strong> para pagamento e poderá <strong>enviar o comprovativo</strong>. Seu pedido será liberado após o admin aprovar.
                </div>

                <div style="margin-top:16px;">
                  <label class="checkout-label">Observações (opcional)</label>
                  <textarea name="notes" rows="3" class="checkout-input"
                            placeholder="Alguma observação para o vendedor?"></textarea>
                </div>

              </div>
            </div>

            {{-- AÇÕES --}}
            <div class="checkout-actions">
              <a href="{{ route('cart.index') }}" class="checkout-back">
                <i class="bi bi-arrow-left"></i> Voltar ao Carrinho
              </a>
              <button type="submit" class="checkout-finish" id="checkoutBtn">
                <i class="bi bi-lock-fill"></i> Finalizar Pedido · R$ {{ number_format($cart->total, 2, ',', '.') }}
              </button>
            </div>

          </div>

          {{-- ============ RESUMO ============ --}}
          <aside class="checkout-summary-col">
            <div class="checkout-summary-card">
              <div class="checkout-summary-title">
                <i class="bi bi-receipt"></i> Resumo do Pedido
              </div>

              <div class="checkout-items">
                @foreach ($items as $item)
                  <div class="checkout-item-mini">
                    <img src="{{ $item->image_url }}" alt="{{ $item->display_name }}">
                    <div class="checkout-item-mini-info">
                      <div class="checkout-item-mini-name">{{ $item->display_name }}</div>
                      <div class="checkout-item-mini-qty">Qtd: {{ $item->quantity }}</div>
                    </div>
                    <div class="checkout-item-mini-price">
                      R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="checkout-summary-lines">
                <div class="checkout-line">
                  <span>Subtotal ({{ $cart->items_count }} itens)</span>
                  <span>R$ {{ number_format($cart->subtotal, 2, ',', '.') }}</span>
                </div>

                @if ($cart->discount > 0)
                  <div class="checkout-line">
                    <span>Cupom {{ $cart->coupon_code }}</span>
                    <span class="checkout-discount">− R$ {{ number_format($cart->discount, 2, ',', '.') }}</span>
                  </div>
                @endif

                <div class="checkout-line">
                  <span>Frete</span>
                  <span id="shippingLine" class="checkout-free">
                    {{ $cart->shipping > 0 ? 'R$ ' . number_format($cart->shipping, 2, ',', '.') : 'A calcular' }}
                  </span>
                </div>

                <div class="checkout-line checkout-line-total">
                  <span>Total</span>
                  <span class="checkout-total" id="totalLine">
                    R$ {{ number_format($cart->total, 2, ',', '.') }}
                  </span>
                </div>
              </div>

              <div class="checkout-summary-seals">
                <div class="checkout-seal"><i class="bi bi-shield-check"></i><span>Compra<br>Garantida</span></div>
                <div class="checkout-seal"><i class="bi bi-lock-fill"></i><span>Site<br>Seguro</span></div>
                <div class="checkout-seal"><i class="bi bi-arrow-repeat"></i><span>Devolução<br>Fácil</span></div>
              </div>
            </div>
          </aside>

        </div>
      </form>

    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>

  <script>
  // ============================================================
  // DADOS DO SERVIDOR
  // ============================================================
  const provinces         = @json($provinces);
  const cartProvince      = @json($cart->customer_province);
  const cartMunicipality  = @json($cart->customer_municipality);
  const cartSubtotal      = {{ (float) $cart->subtotal }};
  const cartDiscount      = {{ (float) $cart->discount }};

  const provinceSelect    = document.getElementById('province');
  const municipalitySelect = document.getElementById('municipality');
  const shippingList      = document.getElementById('shippingList');
  const shippingSubtitle  = document.getElementById('shippingSubtitle');
  const shippingLine      = document.getElementById('shippingLine');
  const totalLine         = document.getElementById('totalLine');

  // ============================================================
  // CARREGAR MUNICÍPIOS QUANDO MUDA PROVÍNCIA
  // ============================================================
  function loadMunicipalities() {
    const prov = provinceSelect.value;
    municipalitySelect.innerHTML = '<option value="">Selecione...</option>';

    if (prov && provinces[prov]) {
      provinces[prov].forEach(function (m) {
        const opt = document.createElement('option');
        opt.value = m;
        opt.textContent = m;
        if (m === cartMunicipality) opt.selected = true;
        municipalitySelect.appendChild(opt);
      });
    }
  }

  provinceSelect.addEventListener('change', function () {
    loadMunicipalities();
    // Se tem município selecionado automaticamente, calcula
    if (municipalitySelect.value) loadShipping();
  });

  municipalitySelect.addEventListener('change', loadShipping);

  // ============================================================
  // CARREGAR FRETE VIA AJAX
  // ============================================================
  async function loadShipping() {
    const province = provinceSelect.value;
    const municipality = municipalitySelect.value;

    if (! province || ! municipality) {
      shippingList.innerHTML = '<div style="text-align:center; padding:32px; color:var(--text-muted);"><i class="bi bi-geo-alt" style="font-size:42px; display:block; margin-bottom:12px; opacity:.4;"></i>Preencha o município</div>';
      shippingSubtitle.textContent = 'Preencha o endereço primeiro';
      return;
    }

    shippingList.innerHTML = '<div style="text-align:center; padding:32px; color:var(--text-muted);"><i class="bi bi-arrow-clockwise" style="font-size:28px; display:block; margin-bottom:12px; animation:spin 0.8s linear infinite;"></i>Calculando frete...</div>';

    try {
      const res = await fetch('{{ route('checkout.shipping') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ province, municipality }),
      });

      const data = await res.json();

      if (data.status === 'success' && data.options.length) {
        renderShippingOptions(data.options);
        updateTotals(data.total, data.fee);
        shippingSubtitle.textContent = 'Entrega em ' + municipality;
      } else {
        shippingList.innerHTML = '<div style="text-align:center; padding:32px; color:var(--danger);"><i class="bi bi-x-circle" style="font-size:42px; display:block; margin-bottom:12px;"></i>' + (data.message || 'Sem entrega disponível') + '</div>';
      }
    } catch (e) {
      shippingList.innerHTML = '<div style="text-align:center; padding:32px; color:var(--danger);"><i class="bi bi-x-circle" style="font-size:42px; display:block; margin-bottom:12px;"></i>Erro ao calcular frete</div>';
    }
  }

  // ============================================================
  // RENDERIZAR OPÇÕES DE ENTREGA
  // ============================================================
  function renderShippingOptions(options) {
    shippingList.innerHTML = '';

    options.forEach(function (opt, i) {
      const isChecked = i === 0;
      const priceHtml = opt.is_free
        ? '<span class="shipping-price-free">Grátis</span>'
        : '<span class="shipping-price">R$ ' + Number(opt.fee).toFixed(2).replace('.', ',') + '</span>';

      const icon = opt.code === 'express' ? 'lightning-charge-fill' : 'truck';

      const html = `
        <label class="shipping-option ${isChecked ? 'selected' : ''}">
          <input type="radio" name="shipping_method" value="${opt.code}" ${isChecked ? 'checked' : ''}>
          <div class="shipping-radio"></div>
          <div class="shipping-info">
            <div class="shipping-name">
              <i class="bi bi-${icon}"></i> ${opt.name}
            </div>
            <div class="shipping-time">Entrega em até ${opt.hours} horas</div>
          </div>
          ${priceHtml}
        </label>
      `;
      shippingList.insertAdjacentHTML('beforeend', html);
    });

    // Evento de troca de método
    document.querySelectorAll('.shipping-option input').forEach(function (input) {
      input.addEventListener('change', function () {
        document.querySelectorAll('.shipping-option').forEach(o => o.classList.remove('selected'));
        this.closest('.shipping-option').classList.add('selected');
        updateShippingMethod(this.value);
      });
    });
  }

  // ============================================================
  // TROCAR MÉTODO DE ENTREGA
  // ============================================================
  async function updateShippingMethod(method) {
    const province = provinceSelect.value;
    const municipality = municipalitySelect.value;

    const res = await fetch('{{ route('checkout.shipping') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      body: JSON.stringify({ province, municipality, method }),
    });

    const data = await res.json();
    if (data.status === 'success') {
      updateTotals(data.total, data.fee);
    }
  }

  // ============================================================
  // ATUALIZAR TOTAIS NA TELA
  // ============================================================
  function updateTotals(total, fee) {
    shippingLine.innerHTML = (fee > 0)
      ? 'R$ ' + fee
      : '<span class="checkout-free">Grátis</span>';

    shippingLine.className = (fee > 0) ? '' : 'checkout-free';

    totalLine.textContent = 'R$ ' + total;

    document.getElementById('checkoutBtn').innerHTML =
      '<i class="bi bi-lock-fill"></i> Finalizar Pedido · R$ ' + total;
  }

  // ============================================================
  // REALÇAR PAGAMENTO
  // ============================================================
  document.querySelectorAll('.payment-option input').forEach(function (input) {
    input.addEventListener('change', function () {
      document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
      this.closest('.payment-option').classList.add('selected');
    });
  });

  // ============================================================
  // SUBMETER CHECKOUT
  // ============================================================
  async function submitCheckout(e) {
    e.preventDefault();

    // Valida se o frete foi escolhido
    const shippingInput = document.querySelector('input[name="shipping_method"]:checked');
    if (! shippingInput) {
      showToast('Escolha uma opção de entrega.', 'error');
      return;
    }

    const btn = document.getElementById('checkoutBtn');
    if (btn.disabled) return;

    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-clockwise" style="animation:spin 0.8s linear infinite;"></i> Processando...';

    const form = document.getElementById('checkoutForm');
    const data = new FormData(form);

    const payload = {
      _token: '{{ csrf_token() }}',
      name: data.get('name'),
      email: data.get('email'),
      phone: data.get('phone'),
      shipping_method: data.get('shipping_method'),
      payment_method: data.get('payment_method'),
      notes: data.get('notes'),
      shipping_address: {
        province: data.get('shipping_address[province]'),
        municipality: data.get('shipping_address[municipality]'),
        neighborhood: data.get('shipping_address[neighborhood]'),
        street: data.get('shipping_address[street]'),
        number: data.get('shipping_address[number]'),
        complement: data.get('shipping_address[complement]'),
        reference: data.get('shipping_address[reference]'),
      },
    };

    try {
      const res = await fetch('{{ route('checkout.store') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(payload),
      });

      const json = await res.json();

      if (json.status === 'success') {
        showToast('<i class="bi bi-check-circle-fill"></i><span>' + json.message + '</span>');
        setTimeout(function () { window.location.href = json.redirect; }, 700);
      } else {
        showToast(json.message || 'Erro ao finalizar.', 'error');
        btn.disabled = false;
        btn.innerHTML = original;
      }
    } catch (err) {
      showToast('Erro de conexão.', 'error');
      btn.disabled = false;
      btn.innerHTML = original;
    }
  }

  // ============================================================
  // INICIALIZAÇÃO
  // ============================================================
  if (provinceSelect.value) {
    loadMunicipalities();
    if (municipalitySelect.value) loadShipping();
  }

  </script>
</body>
</html>