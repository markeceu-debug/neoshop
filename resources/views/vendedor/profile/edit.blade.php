@extends('layouts.vendor')

@section('title', 'Meu Perfil')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Meu Perfil</h2>
      <p class="admin-page-sub">Atualize seus dados e localização</p>
    </div>
  </div>

  @if ($errors->any())
    <div class="admin-card" style="padding:16px; border-left:3px solid var(--admin-red); margin-bottom:20px;">
      <ul style="margin:0; padding-left:18px; color:var(--admin-red);">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  @if (! $user->province || ! $user->municipality)
    <div class="admin-card" style="padding:16px; border-left:3px solid var(--admin-orange); margin-bottom:20px;">
      <strong style="color:var(--admin-orange); display:block; margin-bottom:4px;">
        <i class="bi bi-exclamation-triangle-fill"></i> Complete seu cadastro
      </strong>
      <span style="color:var(--admin-text-sec); font-size:13px;">
        Você precisa preencher <strong>Província</strong> e <strong>Município</strong> para começar a vender.
      </span>
    </div>
  @endif

  <form method="POST" action="{{ route('vendor.profile.update') }}" class="admin-card" style="padding:24px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

      {{-- NOME --}}
      <div style="grid-column: 1 / -1;">
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Nome / Nome da Loja *
        </label>
        <input type="text" name="name" required
               value="{{ old('name', $user->name) }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      {{-- TELEFONE --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Telefone
        </label>
        <input type="tel" name="telefone"
               value="{{ old('telefone', $user->telefone) }}"
               placeholder="(11) 99999-9999"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      {{-- E-MAIL (readonly) --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          E-mail
        </label>
        <input type="email" readonly
               value="{{ $user->email }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text-mut); font-family:inherit; cursor:not-allowed;">
      </div>

      {{-- PROVÍNCIA --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Província *
        </label>
        <select name="province" id="province" required
                style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
          <option value="">Selecione...</option>
          @foreach ($provinces as $prov => $munis)
            <option value="{{ $prov }}" {{ old('province', $user->province) === $prov ? 'selected' : '' }}>
              {{ $prov }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- MUNICÍPIO --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Município *
        </label>
        <select name="municipality" id="municipality" required
                style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
          <option value="">Selecione a província primeiro</option>
        </select>
      </div>

      {{-- BAIRRO --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Bairro
        </label>
        <input type="text" name="neighborhood"
               value="{{ old('neighborhood', $user->neighborhood) }}"
               placeholder="Ex: Talatona"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      {{-- REFERÊNCIA --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Ponto de Referência
        </label>
        <input type="text" name="address_reference"
               value="{{ old('address_reference', $user->address_reference) }}"
               placeholder="Ex: Perto do Kero"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

    </div>

    <div style="margin-top:24px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <i class="bi bi-check-lg"></i> Salvar Perfil
      </button>
    </div>

  </form>

@endsection

@push('scripts')
<script>
  const provinces = @json($provinces);
  const provinceSelect = document.getElementById('province');
  const municipalitySelect = document.getElementById('municipality');
  const currentMunicipality = @json(old('municipality', $user->municipality));

  function loadMunicipalities() {
    const prov = provinceSelect.value;
    municipalitySelect.innerHTML = '<option value="">Selecione...</option>';

    if (prov && provinces[prov]) {
      provinces[prov].forEach(function (m) {
        const opt = document.createElement('option');
        opt.value = m;
        opt.textContent = m;
        if (m === currentMunicipality) opt.selected = true;
        municipalitySelect.appendChild(opt);
      });
    }
  }

  provinceSelect.addEventListener('change', loadMunicipalities);
  if (provinceSelect.value) loadMunicipalities();
</script>
@endpush