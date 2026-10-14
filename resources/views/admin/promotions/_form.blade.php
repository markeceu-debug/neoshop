<div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

  {{-- NOME --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome da Promoção *</label>
    <input type="text" name="name" required
           value="{{ old('name', $promotion->name ?? '') }}"
           placeholder="Ex: Black Friday 50% OFF"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- TIPO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Tipo *</label>
    <select name="type" id="promoType" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="percent"     {{ old('type', $promotion->type ?? '') === 'percent'     ? 'selected' : '' }}>Percentual (%)</option>
      <option value="fixed"       {{ old('type', $promotion->type ?? '') === 'fixed'       ? 'selected' : '' }}>Valor Fixo (R$)</option>
      <option value="buy_x_get_y" {{ old('type', $promotion->type ?? '') === 'buy_x_get_y' ? 'selected' : '' }}>Compre X Leve Y</option>
    </select>
  </div>

  {{-- VALOR --}}
  <div id="valueField">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Valor *</label>
    <input type="number" name="value" step="0.01" min="0"
           value="{{ old('value', $promotion->value ?? 0) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- BUY X GET Y --}}
  <div id="buyXGetYFields" style="grid-column: 1 / -1; display:none;">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Compre quantos?</label>
        <input type="number" name="buy_qty" min="1"
               value="{{ old('buy_qty', $promotion->buy_qty ?? '') }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Leve quantos grátis?</label>
        <input type="number" name="get_qty" min="1"
               value="{{ old('get_qty', $promotion->get_qty ?? '') }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>
    </div>
  </div>

  {{-- APLICA-SE A --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Aplica-se a *</label>
    <select name="applies_to" id="promoAppliesTo" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="all"        {{ old('applies_to', $promotion->applies_to ?? '') === 'all'        ? 'selected' : '' }}>Todos os produtos</option>
      <option value="products"   {{ old('applies_to', $promotion->applies_to ?? '') === 'products'   ? 'selected' : '' }}>Produtos específicos</option>
      <option value="categories" {{ old('applies_to', $promotion->applies_to ?? '') === 'categories' ? 'selected' : '' }}>Categorias específicas</option>
    </select>
  </div>

  {{-- PRODUTOS --}}
  <div id="promoProductsField" style="grid-column: 1 / -1; display:none;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Produtos</label>
    <select name="product_ids[]" multiple size="6"
            style="width:100%; margin-top:8px; padding:8px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      @foreach ($products as $p)
        <option value="{{ $p->id }}"
          {{ in_array($p->id, old('product_ids', $promotion->product_ids ?? [])) ? 'selected' : '' }}>
          {{ $p->name }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- CATEGORIAS --}}
  <div id="promoCategoriesField" style="grid-column: 1 / -1; display:none;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Categorias</label>
    <select name="category_ids[]" multiple size="5"
            style="width:100%; margin-top:8px; padding:8px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      @foreach ($categories as $c)
        <option value="{{ $c->id }}"
          {{ in_array($c->id, old('category_ids', $promotion->category_ids ?? [])) ? 'selected' : '' }}>
          {{ $c->name }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- PRIORIDADE --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Prioridade</label>
    <input type="number" name="priority" min="0"
           value="{{ old('priority', $promotion->priority ?? 0) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
    <small style="color:var(--admin-text-mut); font-size:11px;">Maior = aplicada primeiro</small>
  </div>

  {{-- INÍCIO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Início</label>
    <input type="datetime-local" name="starts_at"
           value="{{ old('starts_at', isset($promotion->starts_at) ? $promotion->starts_at->format('Y-m-d\TH:i') : '') }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- EXPIRA --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Expira em</label>
    <input type="datetime-local" name="expires_at"
           value="{{ old('expires_at', isset($promotion->expires_at) ? $promotion->expires_at->format('Y-m-d\TH:i') : '') }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- DESCRIÇÃO --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Descrição</label>
    <textarea name="description" rows="3"
              style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;">{{ old('description', $promotion->description ?? '') }}</textarea>
  </div>

  {{-- ATIVA --}}
  <div style="grid-column: 1 / -1;">
    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
      <input type="hidden" name="active" value="0">
      <input type="checkbox" name="active" value="1"
             {{ old('active', $promotion->active ?? true) ? 'checked' : '' }}
             style="width:18px;height:18px;">
      <span style="font-size:13px; color:var(--admin-text); font-weight:600;">Promoção ativa</span>
    </label>
  </div>

</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('promoType');
    const val  = document.getElementById('valueField');
    const bxy  = document.getElementById('buyXGetYFields');
    const sel  = document.getElementById('promoAppliesTo');
    const pF   = document.getElementById('promoProductsField');
    const cF   = document.getElementById('promoCategoriesField');

    function toggleType() {
      val.style.display = type.value === 'buy_x_get_y' ? 'none'  : 'block';
      bxy.style.display = type.value === 'buy_x_get_y' ? 'block' : 'none';
    }
    function toggleApplies() {
      pF.style.display = sel.value === 'products'   ? 'block' : 'none';
      cF.style.display = sel.value === 'categories' ? 'block' : 'none';
    }

    toggleType(); toggleApplies();
    type.addEventListener('change', toggleType);
    sel.addEventListener('change', toggleApplies);
  });
</script>