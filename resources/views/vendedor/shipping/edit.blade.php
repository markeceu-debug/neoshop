@extends('layouts.vendor')

@section('title', 'Meu Frete')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Configuração de Frete</h2>
      <p class="admin-page-sub">
        Município: <strong>{{ $user->municipality ?? '—' }}, {{ $user->province ?? '—' }}</strong>
      </p>
    </div>
  </div>

  @if (! $zone)
    <div class="admin-card" style="padding:24px; border-left:3px solid var(--admin-red);">
      <h3 style="color:var(--admin-red); margin:0 0 8px; font-size:16px;">
        <i class="bi bi-exclamation-triangle-fill"></i> Ainda não entregamos no seu município
      </h3>
      <p style="color:var(--admin-text-sec); margin:0; font-size:14px;">
        Entre em contato com o admin pra criar a zona de frete do seu município.
        Você não pode vender até isso ser feito.
      </p>
    </div>
  @else
    @if ($errors->any())
      <div class="admin-card" style="padding:16px; border-left:3px solid var(--admin-red); margin-bottom:20px;">
        <ul style="margin:0; padding-left:18px; color:var(--admin-red);">
          @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- INFO DA ZONA --}}
    <div class="admin-card" style="padding:20px; margin-bottom:24px; border-left:3px solid var(--admin-accent);">
      <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
        <div>
          <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Frete sugerido</span>
          <div style="font-size:16px; color:var(--admin-text); font-weight:800; margin-top:4px;">
            R$ {{ number_format($zone->standard_fee, 2, ',', '.') }}
          </div>
        </div>
        <div>
          <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Expresso sugerido</span>
          <div style="font-size:16px; color:var(--admin-text); font-weight:800; margin-top:4px;">
            R$ {{ number_format($zone->express_fee, 2, ',', '.') }}
          </div>
        </div>
        <div>
          <span style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Teto máximo</span>
          <div style="font-size:16px; color:var(--admin-orange); font-weight:800; margin-top:4px;">
            R$ {{ number_format($zone->max_fee, 2, ',', '.') }}
          </div>
        </div>
      </div>
    </div>

    <form method="POST" action="{{ route('vendor.shipping.update') }}" class="admin-card" style="padding:24px;">
      @csrf
      @method('PUT')

      <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- ATIVAR CUSTOM --}}
        <label style="display:flex; align-items:center; gap:12px; cursor:pointer; padding:16px; background:var(--admin-bg); border:1px solid var(--admin-border-strong);">
          <input type="hidden" name="custom_shipping_enabled" value="0">
          <input type="checkbox" name="custom_shipping_enabled" value="1"
                 id="customEnabled"
                 {{ old('custom_shipping_enabled', $user->custom_shipping_enabled) ? 'checked' : '' }}
                 style="width:18px; height:18px;">
          <div>
            <strong style="color:var(--admin-text); display:block; font-size:13px;">Usar meu próprio frete</strong>
            <span style="font-size:12px; color:var(--admin-text-mut);">
              Se desativado, o sistema usa o frete sugerido pelo admin
            </span>
          </div>
        </label>

        {{-- CAMPOS --}}
        <div id="customFields" style="{{ $user->custom_shipping_enabled ? '' : 'opacity:.5; pointer-events:none;' }}">
          <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Valor do Frete (R$)
              </label>
              <input type="number" step="0.01" min="0" max="{{ $zone->max_fee }}"
                     name="custom_shipping_fee"
                     value="{{ old('custom_shipping_fee', $user->custom_shipping_fee ?? $zone->standard_fee) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
              <small style="color:var(--admin-text-mut); font-size:11px;">
                Máximo: R$ {{ number_format($zone->max_fee, 2, ',', '.') }}
              </small>
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Tempo estimado (horas)
              </label>
              <input type="number" min="1" max="72"
                     name="custom_shipping_hours"
                     value="{{ old('custom_shipping_hours', $user->custom_shipping_hours ?? $zone->standard_hours) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <div style="grid-column: 1 / -1;">
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Frete grátis acima de (R$)
              </label>
              <input type="number" step="0.01" min="0"
                     name="custom_free_above"
                     value="{{ old('custom_free_above', $user->custom_free_above) }}"
                     placeholder="Deixe vazio para não oferecer frete grátis"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
              <small style="color:var(--admin-text-mut); font-size:11px;">
                Se o cliente comprar acima desse valor, o frete é grátis.
              </small>
            </div>

          </div>
        </div>

        <button type="submit"
                class="admin-btn admin-btn-primary"
                style="justify-content:center; padding:14px 24px; font-size:14px;">
          <i class="bi bi-check-lg"></i> Salvar Configurações
        </button>

      </div>
    </form>
  @endif

@endsection

@push('scripts')
<script>
  const checkbox = document.getElementById('customEnabled');
  const fields   = document.getElementById('customFields');

  if (checkbox && fields) {
    checkbox.addEventListener('change', function () {
      fields.style.opacity = this.checked ? '1' : '.5';
      fields.style.pointerEvents = this.checked ? 'auto' : 'none';
    });
  }
</script>
@endpush