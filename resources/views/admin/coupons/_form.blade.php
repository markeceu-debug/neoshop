<div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

  {{-- CÓDIGO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Código *</label>
    <input type="text" name="code" required
           value="{{ old('code', $coupon->code ?? '') }}"
           placeholder="Ex: BLACK50"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:monospace; text-transform:uppercase;">
  </div>

  {{-- NOME --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome Interno</label>
    <input type="text" name="name"
           value="{{ old('name', $coupon->name ?? '') }}"
           placeholder="Ex: Black Friday 50%"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- TIPO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Tipo *</label>
    <select name="type" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="percent"        {{ old('type', $coupon->type ?? '') === 'percent'       ? 'selected' : '' }}>Percentual (%)</option>
      <option value="fixed"          {{ old('type', $coupon->type ?? '') === 'fixed'         ? 'selected' : '' }}>Valor Fixo (R$)</option>
      <option value="free_shipping"  {{ old('type', $coupon->type ?? '') === 'free_shipping' ? 'selected' : '' }}>Frete Grátis</option>
    </select>
  </div>

  {{-- VALOR --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Valor *</label>
    <input type="number" name="value" step="0.01" min="0" required
           value="{{ old('value', $coupon->value ?? '') }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- MÍNIMO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Compra mínima (R$)</label>
    <input type="number" name="min_purchase" step="0.01" min="0"
           value="{{ old('min_purchase', $coupon->min_purchase ?? 0) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- TETO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Teto do desconto (R$)</label>
    <input type="number" name="max_discount" step="0.01" min="0"
           value="{{ old('max_discount', $coupon->max_discount ?? '') }}"
           placeholder="Só para percentual"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- USO TOTAL --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Limite total de usos</label>
    <input type="number" name="usage_limit" min="1"
           value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
           placeholder="Deixe vazio = ilimitado"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- USO POR USUÁRIO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Limite por usuário</label>
    <input type="number" name="per_user_limit" min="1"
           value="{{ old('per_user_limit', $coupon->per_user_limit ?? '') }}"
           placeholder="Deixe vazio = ilimitado"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- APLICA-SE A --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Aplica-se a *</label>
    <select name="applies_to" id="appliesTo" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="all"        {{ old('applies_to', $coupon->applies_to ?? '') === 'all'        ? 'selected' : '' }}>Todos os produtos</option>
      <option value="products"   {{ old('applies_to', $coupon->applies_to ?? '') === 'products'   ? 'selected' : '' }}>Produtos específicos</option>
      <option value="categories" {{ old('applies_to', $coupon->applies_to ?? '') === 'categories' ? 'selected' : '' }}>Categorias específicas</option>
    </select>
  </div>

  {{-- PRODUTOS --}}
  <div id="productsField" style="grid-column: 1 / -1; display:none;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Produtos</label>
    <select name="product_ids[]" multiple size="6"
            style="width:100%; margin-top:8px; padding:8px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      @foreach ($products as $p)
        <option value="{{ $p->id }}"
          {{ in_array($p->id, old('product_ids', $coupon->product_ids ?? [])) ? 'selected' : '' }}>
          {{ $p->name }}
        </option>
      @endforeach
    </select>
    <small style="color:var(--admin-text-mut); font-size:11px;">Segure Ctrl/Cmd para escolher vários</small>
  </div>

  {{-- CATEGORIAS --}}
  <div id="categoriesField" style="grid-column: 1 / -1; display:none;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Categorias</label>
    <select name="category_ids[]" multiple size="5"
            style="width:100%; margin-top:8px; padding:8px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      @foreach ($categories as $c)
        <option value="{{ $c->id }}"
          {{ in_array($c->id, old('category_ids', $coupon->category_ids ?? [])) ? 'selected' : '' }}>
          {{ $c->name }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- INÍCIO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Início</label>
    <input type="datetime-local" name="starts_at"
           value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- EXPIRA --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Expira em</label>
    <input type="datetime-local" name="expires_at"
           value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- DESCRIÇÃO --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Descrição</label>
    <textarea name="description" rows="3"
              style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;">{{ old('description', $coupon->description ?? '') }}</textarea>
  </div>

  {{-- ATIVO --}}
  <div style="grid-column: 1 / -1;">
    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
      <input type="hidden" name="active" value="0">
      <input type="checkbox" name="active" value="1"
             {{ old('active', $coupon->active ?? true) ? 'checked' : '' }}
             style="width:18px;height:18px;">
      <span style="font-size:13px; color:var(--admin-text); font-weight:600;">Cupom ativo</span>
    </label>
  </div>

</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('appliesTo');
    const pF  = document.getElementById('productsField');
    const cF  = document.getElementById('categoriesField');

    function toggle() {
      pF.style.display = sel.value === 'products'   ? 'block' : 'none';
      cF.style.display = sel.value === 'categories' ? 'block' : 'none';
    }
    toggle();
    sel.addEventListener('change', toggle);
  });
</script>