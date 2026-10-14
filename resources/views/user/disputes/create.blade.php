@extends('layouts.user')

@section('title', 'Abrir Reclamação')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Abrir Reclamação</h2>
      <p class="admin-page-sub">Pedido #{{ $order->number }}</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('user.orders.show', $order) }}" class="admin-btn admin-btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
    </div>
  </div>

  @if ($errors->any())
    <div class="admin-card" style="padding:16px; border-left:3px solid var(--admin-red); margin-bottom:20px;">
      <ul style="margin:0; padding-left:18px; color:var(--admin-red);">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="admin-card" style="padding:20px; border-left:3px solid var(--admin-orange); margin-bottom:20px;">
    <div style="display:flex; align-items:center; gap:14px;">
      <i class="bi bi-info-circle-fill" style="font-size:24px; color:var(--admin-orange);"></i>
      <div style="font-size:13px; color:var(--admin-text-sec); line-height:1.6;">
        <strong style="color:var(--admin-text); display:block; margin-bottom:4px;">
          Antes de reclamar
        </strong>
        Tente primeiro entrar em contato com o vendedor pelo telefone. Muitos problemas são resolvidos rapidamente.
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('user.disputes.store', $order) }}"
        enctype="multipart/form-data" class="admin-card" style="padding:24px;">
    @csrf

    <div style="display:flex; flex-direction:column; gap:20px;">

      {{-- MOTIVO --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Motivo da Reclamação *
        </label>
        <select name="reason" id="reason" required
                onchange="toggleRequired()"
                style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
          <option value="">Selecione o motivo...</option>
          <option value="not_received"     {{ old('reason') === 'not_received'     ? 'selected' : '' }}>Produto não recebido</option>
          <option value="wrong_product"    {{ old('reason') === 'wrong_product'    ? 'selected' : '' }}>Produto errado</option>
          <option value="defective"        {{ old('reason') === 'defective'        ? 'selected' : '' }}>Produto com defeito</option>
          <option value="damaged"          {{ old('reason') === 'damaged'          ? 'selected' : '' }}>Produto danificado</option>
          <option value="not_as_described" {{ old('reason') === 'not_as_described' ? 'selected' : '' }}>Diferente do anunciado</option>
          <option value="other"            {{ old('reason') === 'other'            ? 'selected' : '' }}>Outro motivo</option>
        </select>
      </div>

      {{-- DESCRIÇÃO --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Descreva o problema *
        </label>
        <textarea name="description" rows="6" required minlength="20"
                  placeholder="Explique detalhadamente o que aconteceu..."
                  style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;">{{ old('description') }}</textarea>
        <small style="color:var(--admin-text-mut); font-size:11px;">
          Mínimo 20 caracteres.
        </small>
      </div>

      {{-- FOTOS --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Fotos <span id="photoHint" style="color:var(--admin-text-mut); text-transform:none; letter-spacing:0;">(opcional · máx 5)</span>
        </label>
        <input type="file" name="attachments[]" multiple
               accept="image/jpeg,image/jpg,image/png,image/webp"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
        <small style="color:var(--admin-text-mut); font-size:11px;">
          JPG, PNG ou WEBP · máx 5MB cada.
        </small>
      </div>

    </div>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <i class="bi bi-send"></i> Abrir Reclamação
      </button>
      <a href="{{ route('user.orders.show', $order) }}" class="admin-btn admin-btn-secondary">Cancelar</a>
    </div>

  </form>

@endsection

@push('scripts')
<script>
  function toggleRequired() {
    const reason = document.getElementById('reason').value;
    const hint   = document.getElementById('photoHint');
    const requires = ['defective', 'damaged', 'wrong_product', 'not_as_described'].includes(reason);

    if (requires) {
      hint.innerHTML = '<span style="color:var(--admin-orange); font-weight:700;">(obrigatório para este motivo)</span>';
      document.querySelector('input[name="attachments[]"]').required = true;
    } else {
      hint.textContent = '(opcional · máx 5)';
      document.querySelector('input[name="attachments[]"]').required = false;
    }
  }

  document.addEventListener('DOMContentLoaded', toggleRequired);
</script>
@endpush