@php
    $isEdit       = isset($product) && $product?->exists;
    $hasVariants  = $isEdit && $product->has_variants;
@endphp

<div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

  {{-- NOME --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome do Produto *</label>
    <input type="text" name="name"
           value="{{ old('name', $product->name ?? '') }}"
           required
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- SKU --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">SKU</label>
    <input type="text" name="sku"
           value="{{ old('sku', $product->sku ?? '') }}"
           placeholder="Deixe em branco para gerar automaticamente"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- CATEGORIA --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Categoria *</label>
    <select name="category_id" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="">Selecione...</option>
      @foreach (\App\Models\Category::orderBy('position')->orderBy('name')->get() as $cat)
        <option value="{{ $cat->id }}"
          {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
          {{ $cat->name }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- PREÇO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Preço (R$) *</label>
    <input type="number" name="price" step="0.01" min="0"
           value="{{ old('price', $product->price ?? '') }}"
           required
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- PREÇO ANTIGO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Preço Antigo (R$)</label>
    <input type="number" name="old_price" step="0.01" min="0"
           value="{{ old('old_price', $product->old_price ?? '') }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- STATUS --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Status *</label>
    <select name="status" required
            style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      <option value="active"       {{ old('status', $product->status ?? 'active') === 'active'       ? 'selected' : '' }}>Ativo</option>
      <option value="inactive"     {{ old('status', $product->status ?? '')       === 'inactive'     ? 'selected' : '' }}>Inativo</option>
      <option value="out_of_stock" {{ old('status', $product->status ?? '')       === 'out_of_stock' ? 'selected' : '' }}>Esgotado</option>
    </select>
  </div>

  {{-- ================================================================ --}}
  {{-- ESTOQUE — só aparece quando NÃO há variantes                   --}}
  {{-- ================================================================ --}}
  @if ($hasVariants)
    {{-- Produto COM variantes → aviso + link --}}
    <div style="grid-column: 1 / -1; padding:16px; background:rgba(124,92,255,0.08); border-left:3px solid var(--admin-accent); display:flex; align-items:center; gap:12px;">
      <i class="bi bi-info-circle-fill" style="color:var(--admin-accent); font-size:22px;"></i>
      <div style="flex:1;">
        <strong style="color:var(--admin-text); display:block; margin-bottom:4px;">Este produto possui variantes</strong>
        <span style="color:var(--admin-text-sec); font-size:13px;">
          O estoque é gerenciado em cada item de variante (SKU real).
        </span>
      </div>
      <a href="{{ route('admin.products.variants.index', $product) }}"
         class="admin-btn admin-btn-primary">
        <i class="bi bi-diagram-3"></i> Gerenciar Variantes
      </a>
    </div>
  @else
    {{-- Produto SEM variantes → mostra campo de estoque --}}
    <div>
      <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
        {{ $isEdit ? 'Estoque Atual (somente leitura)' : 'Estoque Inicial' }}
      </label>

      @if ($isEdit)
        <input type="text" value="{{ $product->stock }} unidade(s)" readonly
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px dashed var(--admin-border-strong); color:var(--admin-text-mut); font-family:inherit; cursor:not-allowed;">

        <small style="display:block; margin-top:6px; color:var(--admin-text-mut); font-size:11px;">
          <i class="bi bi-info-circle"></i>
          Para alterar o estoque, use a tela de
          <a href="{{ route('admin.products.index') }}" style="color:var(--admin-accent);">Ajuste em Massa</a>.
        </small>
      @else
        <input type="number" name="stock_input" min="0" value="{{ old('stock_input', 0) }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">

        <small style="display:block; margin-top:6px; color:var(--admin-text-mut); font-size:11px;">
          <i class="bi bi-info-circle"></i>
          Defina o estoque inicial. Depois, ajustes podem ser feitos em massa.
        </small>
      @endif
    </div>

    {{-- Espaço para alinhar o grid --}}
    <div></div>
  @endif

  {{-- IMAGEM --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Imagem Principal</label>
    <input type="file" name="image" accept="image/*"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">

    @if ($isEdit && $product->image)
      <div style="margin-top:12px;">
        <img src="{{ asset('storage/' . $product->image) }}" alt="Imagem atual"
             style="max-width:120px; border:1px solid var(--admin-border-strong);">
      </div>
    @endif
  </div>

  {{-- DESCRIÇÃO --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Descrição</label>
    <textarea name="description" rows="5"
              style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;">{{ old('description', $product->description ?? '') }}</textarea>
  </div>

</div>