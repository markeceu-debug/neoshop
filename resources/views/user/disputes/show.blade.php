@extends('layouts.user')

@section('title', 'Reclamação #' . $dispute->id)

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Reclamação #{{ $dispute->id }}</h2>
      <p class="admin-page-sub">
        Pedido #{{ $dispute->order->number }}
        · Aberta em {{ $dispute->created_at->format('d/m/Y \à\s H:i') }}
      </p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('user.orders.show', $dispute->order) }}" class="admin-btn admin-btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
    </div>
  </div>

  @php [$statusCls, $statusLabel] = $dispute->status_badge; @endphp

  {{-- STATUS --}}
  <div class="admin-card" style="padding:20px; margin-bottom:20px; border-left:3px solid
       @if ($dispute->status === 'open') var(--admin-orange)
       @elseif ($dispute->status === 'under_review') var(--admin-cyan)
       @elseif (in_array($dispute->status, ['resolved_buyer','resolved_partial'])) var(--admin-green)
       @else var(--admin-accent)
       @endif
  ;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
      <span class="admin-badge {{ $statusCls }}" style="font-size:13px; padding:6px 14px;">
        {{ $statusLabel }}
      </span>
      <div style="flex:1; min-width:200px;">
        <strong style="color:var(--admin-text); display:block; font-size:14px;">
          Motivo: {{ $dispute->reason_label }}
        </strong>
        <span style="color:var(--admin-text-sec); font-size:12px;">
          Aguardando análise do suporte
        </span>
      </div>
    </div>

    @if ($dispute->resolution_notes)
      <div style="margin-top:16px; padding:12px 16px; background:rgba(34,197,94,0.08); border-left:3px solid var(--admin-green); font-size:13px; color:var(--admin-text-sec);">
        <strong style="color:var(--admin-green); display:block; margin-bottom:4px;">
          <i class="bi bi-check-circle-fill"></i> Resolução
        </strong>
        {{ $dispute->resolution_notes }}

        @if ($dispute->refund_amount > 0)
          <div style="margin-top:8px; color:var(--admin-text);">
            <strong>Reembolso:</strong> R$ {{ number_format($dispute->refund_amount, 2, ',', '.') }}
          </div>
        @endif
      </div>
    @endif
  </div>

  {{-- DESCRIÇÃO ORIGINAL --}}
  <div class="admin-card" style="padding:20px; margin-bottom:20px;">
    <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:8px;">
      Descrição inicial
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

  {{-- CHAT --}}
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="bi bi-chat-dots"></i> Conversa com o Suporte</h3>
    </div>
    <div class="admin-card-body">

      <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:24px;">
        @foreach ($dispute->messages as $msg)
          @php [$badgeCls, $badgeLabel] = $msg->sender_badge; @endphp
          <div style="display:flex; gap:12px; {{ $msg->sender_role === 'buyer' ? 'flex-direction:row-reverse;' : '' }}">
            <div style="width:36px; height:36px; background:var(--admin-accent); border-radius:50% !important; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; flex-shrink:0;">
              {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
            </div>
            <div style="max-width:75%; {{ $msg->sender_role === 'buyer' ? 'text-align:right;' : '' }}">
              <div style="display:flex; gap:8px; align-items:center; margin-bottom:4px; {{ $msg->sender_role === 'buyer' ? 'justify-content:flex-end;' : '' }}">
                <strong style="font-size:12px; color:var(--admin-text);">{{ $msg->sender_name }}</strong>
                <span class="admin-badge {{ $badgeCls }}" style="font-size:10px;">{{ $badgeLabel }}</span>
                <span style="font-size:11px; color:var(--admin-text-mut);">{{ $msg->created_at->format('d/m H:i') }}</span>
              </div>
              <div style="background:{{ $msg->sender_role === 'buyer' ? 'rgba(124,92,255,0.12)' : 'var(--admin-bg)' }}; border:1px solid var(--admin-border-subtle); padding:12px 14px; font-size:13px; color:var(--admin-text-sec); line-height:1.6; white-space:pre-line; text-align:left;">
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

      {{-- FORM DE MENSAGEM --}}
      @if (! $dispute->isResolved())
        <form method="POST" action="{{ route('user.disputes.message', $dispute) }}"
              enctype="multipart/form-data"
              style="border-top:1px solid var(--admin-border-subtle); padding-top:20px;">
          @csrf

          <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
            Sua mensagem
          </label>
          <textarea name="message" rows="3" required
                    placeholder="Escreva aqui..."
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

@endsection