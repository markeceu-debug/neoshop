@extends('layouts.admin')

@section('title', 'Reclamação #' . $dispute->id)

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Reclamação #{{ $dispute->id }}</h2>
      <p class="admin-page-sub">
        Pedido <a href="{{ route('admin.orders.show', $dispute->order_id) }}" style="color:var(--admin-accent);">#{{ $dispute->order->number }}</a>
        · Aberta em {{ $dispute->created_at->format('d/m/Y \à\s H:i') }}
      </p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.disputes.index') }}" class="admin-btn admin-btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
    </div>
  </div>

  {{-- STATUS --}}
  @php [$statusCls, $statusLabel] = $dispute->status_badge; @endphp
  <div class="admin-card" style="padding:18px 24px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
      <span class="admin-badge {{ $statusCls }}" style="font-size:13px; padding:6px 14px;">
        {{ $statusLabel }}
      </span>
      <div style="flex:1; min-width:200px;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Motivo: {{ $dispute->reason_label }}
        </strong>
        @if ($dispute->resolver)
          <span style="color:var(--admin-text-sec); font-size:12px;">
            Resolvido por <strong>{{ $dispute->resolver->name }}</strong>
            em {{ $dispute->resolved_at?->format('d/m/Y H:i') }}
          </span>
        @endif
      </div>
      @if (! $dispute->isResolved())
        <span class="admin-badge admin-badge-warning">
          <i class="bi bi-hourglass-split"></i> Aguardando resolução
        </span>
      @endif
    </div>
  </div>

  <div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

    {{-- COLUNA ESQUERDA --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

      {{-- RECLAMAÇÃO --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-exclamation-triangle"></i> Reclamação do Cliente</h3>
        </div>
        <div class="admin-card-body">
          <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
            <div style="width:40px; height:40px; background:var(--admin-accent); border-radius:50% !important; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800;">
              {{ strtoupper(substr($dispute->opener->name, 0, 1)) }}
            </div>
            <div>
              <strong style="color:var(--admin-text); display:block;">{{ $dispute->opener->name }}</strong>
              <span style="font-size:12px; color:var(--admin-text-mut);">{{ $dispute->opener->email }}</span>
            </div>
          </div>

          <p style="color:var(--admin-text); font-size:14px; line-height:1.7; white-space:pre-line;">{{ $dispute->description }}</p>

          @if (!empty($dispute->attachments))
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px;">
              @foreach ($dispute->attachments as $att)
                <a href="{{ asset('storage/' . $att) }}" target="_blank">
                  <img src="{{ asset('storage/' . $att) }}"
                       style="width:100px; height:100px; object-fit:cover; border:1px solid var(--admin-border-strong);">
                </a>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      {{-- CHAT --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-chat-dots"></i> Conversa</h3>
        </div>
        <div class="admin-card-body">

          <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:24px;">
            @foreach ($dispute->messages as $msg)
              @php [$badgeCls, $badgeLabel] = $msg->sender_badge; @endphp
              <div style="display:flex; gap:12px;">
                <div style="width:36px; height:36px; background:var(--admin-accent); border-radius:50% !important; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; flex-shrink:0;">
                  {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                </div>
                <div style="flex:1;">
                  <div style="display:flex; gap:8px; align-items:center; margin-bottom:4px; flex-wrap:wrap;">
                    <strong style="font-size:12px; color:var(--admin-text);">{{ $msg->sender_name }}</strong>
                    <span class="admin-badge {{ $badgeCls }}" style="font-size:10px;">{{ $badgeLabel }}</span>
                    <span style="font-size:11px; color:var(--admin-text-mut);">{{ $msg->created_at->format('d/m H:i') }}</span>
                  </div>
                  <div style="background:var(--admin-bg); border:1px solid var(--admin-border-subtle); padding:12px 14px; font-size:13px; color:var(--admin-text-sec); line-height:1.6; white-space:pre-line;">
                    {{ $msg->message }}

                    @if (!empty($msg->attachments))
                      <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
                        @foreach ($msg->attachments as $att)
                          <a href="{{ asset('storage/' . $att) }}" target="_blank">
                            <img src="{{ asset('storage/' . $att) }}"
                                 style="width:80px; height:80px; object-fit:cover; border:1px solid var(--admin-border-strong);">
                          </a>
                        @endforeach
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          @if (! $dispute->isResolved())
            <form method="POST" action="{{ route('admin.disputes.message', $dispute) }}"
                  enctype="multipart/form-data"
                  style="border-top:1px solid var(--admin-border-subtle); padding-top:20px;">
              @csrf

              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Responder ao cliente
              </label>
              <textarea name="message" rows="3" required
                        placeholder="Ex: Olá, já estou analisando sua reclamação..."
                        style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;"></textarea>

              <div style="margin-top:12px;">
                <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                  Anexos (opcional)
                </label>
                <input type="file" name="attachments[]" multiple
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       style="width:100%; margin-top:8px; padding:10px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-size:12px;">
              </div>

              <button type="submit" class="admin-btn admin-btn-primary" style="margin-top:16px;">
                <i class="bi bi-send"></i> Enviar Mensagem
              </button>
            </form>
          @endif

        </div>
      </div>

    </div>

    {{-- COLUNA DIREITA --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

      {{-- RESUMO DO PEDIDO --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title"><i class="bi bi-receipt"></i> Pedido</h3>
        </div>
        <div class="admin-card-body" style="font-size:13px;">
          <div style="display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; justify-content:space-between;">
              <span style="color:var(--admin-text-sec);">Número</span>
              <a href="{{ route('admin.orders.show', $dispute->order_id) }}"
                 style="font-family:monospace; font-weight:800; color:var(--admin-accent-2); text-decoration:none;">
                {{ $dispute->order->number }}
              </a>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:var(--admin-text-sec);">Valor pago</span>
              <strong style="color:var(--admin-accent-2);">R$ {{ number_format($dispute->order->total, 2, ',', '.') }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:var(--admin-text-sec);">Vendedor recebe</span>
              <strong style="color:var(--admin-green);">R$ {{ number_format($dispute->order->seller_net_amount, 2, ',', '.') }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:var(--admin-text-sec);">Vendedor</span>
              <strong>{{ $dispute->order->seller?->name ?? '—' }}</strong>
            </div>
          </div>
        </div>
      </div>

      {{-- RESOLUÇÃO --}}
      @if (! $dispute->isResolved())
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="bi bi-check-circle"></i> Resolver</h3>
          </div>
          <div class="admin-card-body" style="display:flex; flex-direction:column; gap:12px;">

            {{-- Cliente --}}
            <button type="button" onclick="openResolveModal('buyer')"
                    class="admin-btn admin-btn-primary"
                    style="width:100%; justify-content:center; background:linear-gradient(135deg, var(--admin-green), #16a34a); box-shadow:0 0 20px rgba(34,197,94,0.3);">
              <i class="bi bi-arrow-counterclockwise"></i> Reembolsar Cliente
            </button>

            {{-- Vendedor --}}
            <button type="button" onclick="openResolveModal('seller')"
                    class="admin-btn admin-btn-secondary"
                    style="width:100%; justify-content:center;">
              <i class="bi bi-check-lg"></i> A favor do Vendedor
            </button>

            {{-- Parcial --}}
            <button type="button" onclick="openResolveModal('partial')"
                    class="admin-btn admin-btn-secondary"
                    style="width:100%; justify-content:center;">
              <i class="bi bi-sliders"></i> Reembolso Parcial
            </button>

          </div>
        </div>
      @else
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="bi bi-info-circle"></i> Resolução</h3>
          </div>
          <div class="admin-card-body">
            <div style="font-size:13px; color:var(--admin-text-sec); line-height:1.6;">
              <strong style="color:var(--admin-text); display:block; margin-bottom:8px;">
                {{ $dispute->status_label }}
              </strong>
              {{ $dispute->resolution_notes }}

              @if ($dispute->refund_amount > 0)
                <div style="margin-top:12px; padding-top:12px; border-top:1px solid var(--admin-border-subtle);">
                  <strong>Reembolso:</strong> R$ {{ number_format($dispute->refund_amount, 2, ',', '.') }}
                </div>
              @endif
            </div>
          </div>
        </div>
      @endif

    </div>
  </div>

  {{-- MODAIS DE RESOLUÇÃO --}}
  @if (! $dispute->isResolved())
    @foreach (['buyer' => ['Reembolsar Cliente', 'admin-green', 'O valor será devolvido ao cliente e o vendedor perde o pagamento.'],
              'seller' => ['A favor do Vendedor', 'admin-accent', 'O cliente não será reembolsado. O vendedor receberá o pagamento no prazo normal.'],
              'partial' => ['Reembolso Parcial', 'admin-cyan', 'Uma parte do valor será devolvida ao cliente e o restante liberado ao vendedor.']] as $key => $cfg)
      <div id="resolveModal-{{ $key }}" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(3px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
        <div style="background:var(--admin-card); border:1px solid var(--admin-border-strong); width:100%; max-width:520px;">

          <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid var(--admin-border); background:linear-gradient(135deg,var(--admin-sidebar) 0%,rgba(124,92,255,0.06) 100%);">
            <h3 style="margin:0; font-size:15px; font-weight:900; color:var(--admin-text); text-transform:uppercase; letter-spacing:1px;">
              {{ $cfg[0] }}
            </h3>
            <button type="button" onclick="closeResolveModal('{{ $key }}')" style="width:32px; height:32px; background:transparent; border:1px solid var(--admin-border-strong); color:var(--admin-text-sec); cursor:pointer; border-radius:50% !important;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <form method="POST"
                action="{{ route('admin.disputes.resolve-' . $key, $dispute) }}">
            @csrf

            <div style="padding:24px;">

              <div style="background:rgba(124,92,255,0.06); border-left:3px solid var(--{{ $cfg[1] }}); padding:12px 16px; margin-bottom:20px; font-size:13px; color:var(--admin-text-sec);">
                {{ $cfg[2] }}
              </div>

              @if ($key === 'partial')
                <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                  Valor a reembolsar (R$) *
                </label>
                <input type="number" name="refund_amount" step="0.01" required
                       min="0.01" max="{{ $dispute->order->total }}"
                       style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
                <small style="color:var(--admin-text-mut); font-size:11px;">
                  Máx: R$ {{ number_format($dispute->order->total, 2, ',', '.') }}
                </small>
              @endif

              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800; display:block; margin-top:16px;">
                Justificativa *
              </label>
              <textarea name="resolution_notes" rows="4" required
                        placeholder="Explique a decisão (visível ao cliente)..."
                        style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; padding:18px 24px; border-top:1px solid var(--admin-border); background:var(--admin-sidebar);">
              <button type="button" onclick="closeResolveModal('{{ $key }}')" class="admin-btn admin-btn-secondary">
                Cancelar
              </button>
              <button type="submit" class="admin-btn admin-btn-primary">
                <i class="bi bi-check-lg"></i> Confirmar
              </button>
            </div>
          </form>
        </div>
      </div>
    @endforeach
  @endif

@endsection

@push('scripts')
<script>
  function openResolveModal(key) {
    const el = document.getElementById('resolveModal-' + key);
    if (el) el.style.display = 'flex';
  }
  function closeResolveModal(key) {
    const el = document.getElementById('resolveModal-' + key);
    if (el) el.style.display = 'none';
  }

  // Fecha clicando no overlay
  document.querySelectorAll('[id^="resolveModal-"]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (e.target === this) this.style.display = 'none';
    });
  });

  // ESC fecha
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      ['buyer', 'seller', 'partial'].forEach(closeResolveModal);
    }
  });
</script>
@endpush