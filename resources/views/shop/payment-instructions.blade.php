<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pagamento do Pedido {{ $order->number }} - NeoShop</title>
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
      <a href="{{ route('cart.index') }}">Carrinho</a>
      <span class="sep">/</span>
      <span class="current">Pagamento</span>
    </div>
  </div>

  <div class="container" style="max-width: 900px; padding: 40px 0 60px;">

    {{-- CABEÇALHO --}}
    <div style="text-align:center; margin-bottom:32px;">
      <div style="display:inline-flex; align-items:center; justify-content:center; width:80px; height:80px; background:rgba(251,191,36,0.15); border:2px solid var(--warning); border-radius:50% !important; font-size:36px; color:var(--warning); margin-bottom:16px;">
        <i class="bi bi-hourglass-split"></i>
      </div>

      <h1 style="font-size:26px; font-weight:900; color:var(--text-primary); margin:0 0 8px; text-transform:uppercase; letter-spacing:1px;">
        Pedido aguardando pagamento
      </h1>

      <p style="font-size:15px; color:var(--text-secondary); max-width:520px; margin:0 auto;">
        Seu pedido <strong style="color:var(--accent-2);">#{{ $order->number }}</strong> foi criado.
        Faça o pagamento e envie o comprovativo abaixo.
      </p>
    </div>

    {{-- CARD DE DADOS BANCÁRIOS --}}
    <div class="admin-card" style="padding:28px; margin-bottom:24px;">
      <div style="display:flex; align-items:center; gap:10px; padding-bottom:16px; border-bottom:1px solid var(--border-subtle); margin-bottom:20px;">
        <i class="bi bi-bank2" style="color:var(--accent); font-size:22px;"></i>
        <h2 style="font-size:16px; font-weight:800; color:var(--text-primary); margin:0; text-transform:uppercase; letter-spacing:0.5px;">
          Dados para pagamento
        </h2>
      </div>

      <div style="display:grid; gap:16px;">

        <div style="display:grid; grid-template-columns:160px 1fr; gap:16px; align-items:center;">
          <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Titular</span>
          <strong style="font-size:14px; color:var(--text-primary);">{{ $bankInfo['holder'] }}</strong>
        </div>

        <div style="display:grid; grid-template-columns:160px 1fr; gap:16px; align-items:center;">
          <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Banco</span>
          <strong style="font-size:14px; color:var(--text-primary);">{{ $bankInfo['bank'] }}</strong>
        </div>

        <div style="display:grid; grid-template-columns:160px 1fr; gap:16px; align-items:center;">
          <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">IBAN</span>
          <div style="display:flex; align-items:center; gap:8px;">
            <strong style="font-size:14px; color:var(--text-primary); font-family:monospace; letter-spacing:0.5px;" id="ibanValue">
              {{ $bankInfo['iban'] }}
            </strong>
            <button type="button" onclick="copyText('{{ $bankInfo['iban'] }}')" style="background:none; border:1px solid var(--border-strong); color:var(--text-muted); width:32px; height:32px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
              <i class="bi bi-clipboard"></i>
            </button>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:160px 1fr; gap:16px; align-items:center;">
          <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Multicaixa Express</span>
          <div style="display:flex; align-items:center; gap:8px;">
            <strong style="font-size:14px; color:var(--text-primary);">{{ $bankInfo['multicaixa_express'] }}</strong>
            <button type="button" onclick="copyText('{{ $bankInfo['multicaixa_express'] }}')" style="background:none; border:1px solid var(--border-strong); color:var(--text-muted); width:32px; height:32px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
              <i class="bi bi-clipboard"></i>
            </button>
          </div>
        </div>
<div style="display:grid; grid-template-columns:160px 1fr; gap:16px; align-items:center;">
  <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Nº da Conta</span>
  <strong style="font-size:14px; color:var(--text-primary); font-family:monospace;">{{ $bankInfo['account'] ?? '—' }}</strong>
</div>
        <div style="display:grid; grid-template-columns:160px 1fr; gap:16px; align-items:center; padding-top:16px; border-top:1px solid var(--border-subtle);">
          <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Valor exato</span>
          <strong style="font-size:24px; color:var(--accent-2); font-weight:900; font-variant-numeric:tabular-nums;">
            R$ {{ number_format($order->total, 2, ',', '.') }}
          </strong>
        </div>

      </div>
    </div>

    {{-- UPLOAD DO COMPROVATIVO --}}
    <div class="admin-card" style="padding:28px;">
      <div style="display:flex; align-items:center; gap:10px; padding-bottom:16px; border-bottom:1px solid var(--border-subtle); margin-bottom:20px;">
        <i class="bi bi-cloud-upload-fill" style="color:var(--accent); font-size:22px;"></i>
        <h2 style="font-size:16px; font-weight:800; color:var(--text-primary); margin:0; text-transform:uppercase; letter-spacing:0.5px;">
          Enviar comprovativo
        </h2>
      </div>

      <form id="proofForm" enctype="multipart/form-data" onsubmit="submitProof(event)">
        @csrf

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700; margin-bottom:8px;">
            Arquivos (JPG, PNG ou PDF · máx 5MB cada · até 3)
          </label>

          <input type="file" name="files[]" multiple
                 accept="image/jpeg,image/png,image/jpg,application/pdf"
                 id="proofFiles" required
                 style="width:100%; padding:14px; background:var(--bg-tertiary); border:1px solid var(--border-strong); color:var(--text-primary); font-family:inherit;">
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700; margin-bottom:8px;">
            Observações (opcional)
          </label>
          <textarea name="notes" rows="3" placeholder="Ex: Paguei via Multicaixa Express às 14:30"
                    style="width:100%; padding:12px 14px; background:var(--bg-tertiary); border:1px solid var(--border-strong); color:var(--text-primary); font-family:inherit; resize:vertical;"></textarea>
        </div>

        <button type="submit" id="proofBtn"
                style="width:100%; padding:16px; background:linear-gradient(135deg, var(--accent), #5a3fff); border:none; color:#fff; font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:1.2px; cursor:pointer; box-shadow:0 0 24px var(--accent-glow); display:flex; align-items:center; justify-content:center; gap:10px;">
          <i class="bi bi-send-fill"></i> Enviar Comprovativo
        </button>
      </form>
    </div>

    {{-- RESUMO DO PEDIDO --}}
    <div class="admin-card" style="padding:24px; margin-top:24px;">
      <h3 style="font-size:14px; font-weight:800; color:var(--text-primary); margin:0 0 16px; text-transform:uppercase; letter-spacing:0.5px;">
        <i class="bi bi-receipt"></i> Itens do pedido
      </h3>

      @foreach ($order->items as $item)
        <div style="display:grid; grid-template-columns:56px 1fr auto; gap:12px; align-items:center; padding:10px 0; border-bottom:1px solid var(--border-subtle);">
          <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}"
               style="width:56px; height:56px; object-fit:cover; border:1px solid var(--border-strong);">

          <div>
            <div style="font-size:13px; color:var(--text-primary); font-weight:600;">{{ $item->product_name }}</div>
            @if ($item->variant_value)
              <div style="font-size:11px; color:var(--text-muted);">Variação: {{ $item->variant_value }}</div>
            @endif
            <div style="font-size:11px; color:var(--text-muted);">Qtd: {{ $item->quantity }}</div>
          </div>

          <div style="font-size:13px; font-weight:800; color:var(--accent-2); font-variant-numeric:tabular-nums;">
            R$ {{ number_format($item->subtotal, 2, ',', '.') }}
          </div>
        </div>
      @endforeach

      <div style="display:flex; justify-content:space-between; padding-top:16px; margin-top:16px; border-top:2px solid var(--border-strong);">
        <span style="font-size:14px; font-weight:800; color:var(--text-primary); text-transform:uppercase; letter-spacing:1px;">Total</span>
        <span style="font-size:20px; font-weight:900; color:var(--accent-2);">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
      </div>
    </div>

  </div>

  <div class="toast-container" id="toastContainer"></div>

  @include('shop.partials.footer')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>

  <script>
    function copyText(text) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () {
          showToast('<i class="bi bi-check-circle-fill"></i><span>Copiado!</span>');
        });
      }
    }

    async function submitProof(e) {
      e.preventDefault();

      const btn = document.getElementById('proofBtn');
      const input = document.getElementById('proofFiles');

      if (! input.files.length) {
        showToast('Escolha pelo menos 1 arquivo.', 'error');
        return;
      }

      if (input.files.length > 3) {
        showToast('Máximo de 3 arquivos.', 'error');
        return;
      }

      // Valida tamanho
      for (const f of input.files) {
        if (f.size > 5 * 1024 * 1024) {
          showToast('Arquivo muito grande: ' + f.name + ' (máx 5MB).', 'error');
          return;
        }
      }

      btn.disabled = true;
      const original = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-arrow-clockwise" style="animation:spin 0.8s linear infinite;"></i> Enviando...';

      const formData = new FormData(document.getElementById('proofForm'));

      try {
        const res = await fetch('{{ route('checkout.uploadProof', $order) }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: formData,
        });

        const json = await res.json();

        if (json.status === 'success') {
          showToast('<i class="bi bi-check-circle-fill"></i><span>' + json.message + '</span>');
          setTimeout(function () {
            window.location.href = json.redirect;
          }, 900);
        } else {
          showToast(json.message || 'Erro ao enviar.', 'error');
          btn.disabled = false;
          btn.innerHTML = original;
        }
      } catch (err) {
        showToast('Erro de conexão.', 'error');
        btn.disabled = false;
        btn.innerHTML = original;
      }
    }
  </script>

  <style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  </style>
</body>
</html>