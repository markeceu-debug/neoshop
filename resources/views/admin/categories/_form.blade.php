<div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

  {{-- NOME --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome da Categoria *</label>
    <input type="text" name="name" required
           value="{{ old('name', $category->name ?? '') }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- ÍCONE --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Ícone (Bootstrap Icons)</label>
    <input type="text" name="icon"
           value="{{ old('icon', $category->icon ?? '') }}"
           placeholder="bi-phone, bi-laptop, bi-house..."
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
    <small style="color:var(--admin-text-mut); font-size:11px;">Consulte em: icons.getbootstrap.com</small>
  </div>

  {{-- POSIÇÃO --}}
  <div>
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Ordem de Exibição</label>
    <input type="number" name="position" min="0"
           value="{{ old('position', $category->position ?? 0) }}"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
  </div>

  {{-- STATUS --}}
  <div style="grid-column: 1 / -1;">
    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
      <input type="hidden" name="active" value="0">
      <input type="checkbox" name="active" value="1"
             {{ old('active', $category->active ?? true) ? 'checked' : '' }}
             style="width:18px;height:18px;">
      <span style="font-size:13px; color:var(--admin-text); font-weight:600;">Categoria ativa</span>
    </label>
  </div>

  {{-- IMAGEM --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Imagem da Categoria</label>
    <input type="file" name="image" accept="image/*"
           style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">

    @if (!empty($category?->image))
      <div style="margin-top:12px;">
        <img src="{{ asset('storage/' . $category->image) }}" alt="Imagem atual" style="max-width:120px; border:1px solid var(--admin-border-strong);">
      </div>
    @endif
  </div>

  {{-- DESCRIÇÃO --}}
  <div style="grid-column: 1 / -1;">
    <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Descrição</label>
    <textarea name="description" rows="4"
              style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; resize:vertical;">{{ old('description', $category->description ?? '') }}</textarea>
  </div>

</div>