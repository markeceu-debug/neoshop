<div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

  {{-- PROVÍNCIA --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Província *</label>
    <select name="province" id="province" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="">Selecione...</option>
      @foreach ($provinces as $prov => $munis)
        <option value="{{ $prov }}" {{ old('province', $zone->province ?? '') === $prov ? 'selected' : '' }}>
          {{ $prov }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- MUNICÍPIO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Município *</label>
    <select name="municipality" id="municipality" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="">Selecione a província primeiro</option>
    </select>
  </div>

  {{-- FRETE PADRÃO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Motoboy Padrão (R$) *</label>
    <input type="number" step="0.01" min="0" name="standard_fee" required
           value="{{ old('standard_fee', $zone->standard_fee ?? 500) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- HORAS PADRÃO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Tempo Padrão (horas) *</label>
    <input type="number" min="1" max="72" name="standard_hours" required
           value="{{ old('standard_hours', $zone->standard_hours ?? 6) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- FRETE EXPRESSO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Motoboy Expresso (R$)</label>
    <input type="number" step="0.01" min="0" name="express_fee"
           value="{{ old('express_fee', $zone->express_fee ?? 800) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
    <small style="color:var(--admin-text-mut); font-size:11px;">Deixe 0 para não oferecer expresso</small>
  </div>

  {{-- HORAS EXPRESSO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Tempo Expresso (horas)</label>
    <input type="number" min="1" max="72" name="express_hours"
           value="{{ old('express_hours', $zone->express_hours ?? 2) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- GRÁTIS ACIMA DE --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Frete Grátis acima de (R$)</label>
    <input type="number" step="0.01" min="0" name="free_above"
           value="{{ old('free_above', $zone->free_above ?? '') }}"
           placeholder="Deixe vazio para nunca"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- RETIRADA --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Endereço de Retirada</label>
    <input type="text" name="pickup_address"
           value="{{ old('pickup_address', $zone->pickup_address ?? '') }}"
           placeholder="Ex: Av. X, Bairro Y"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- ATIVO --}}
  <div>
    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding-top:24px;">
      <input type="hidden" name="pickup_enabled" value="0">
      <input type="checkbox" name="pickup_enabled" value="1"
             {{ old('pickup_enabled', $zone->pickup_enabled ?? true) ? 'checked' : '' }}
             style="width:18px;height:18px;">
      <span style="font-size:13px; color:var(--admin-text); font-weight:600;">Permitir retirada</span>
    </label>
  </div>

  <div>
    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; padding-top:24px;">
      <input type="hidden" name="active" value="0">
      <input type="checkbox" name="active" value="1"
             {{ old('active', $zone->active ?? true) ? 'checked' : '' }}
             style="width:18px;height:18px;">
      <span style="font-size:13px; color:var(--admin-text); font-weight:600;">Zona ativa</span>
    </label>
  </div>

  {{-- NOTAS --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Observações</label>
    <textarea name="notes" rows="3"
              style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;">{{ old('notes', $zone->notes ?? '') }}</textarea>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const provinces = @json($provinces);
  const provinceSelect = document.getElementById('province');
  const municipalitySelect = document.getElementById('municipality');
  const currentMunicipality = @json(old('municipality', $zone->municipality ?? ''));

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
});
</script>