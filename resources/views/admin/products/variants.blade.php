@extends('layouts.admin')

@section('title', 'Variantes — ' . $product->name)

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">{{ $product->name }}</h2>
      <p class="admin-page-sub">Gerenciar variantes e galeria de fotos</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar aos Produtos
      </a>
    </div>
  </div>

  {{-- ===================== GALERIA ===================== --}}
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="bi bi-images"></i> Galeria de Fotos</h3>
      <span class="admin-badge admin-badge-muted">{{ $product->images->count() }} imagem(ns)</span>
    </div>
    <div class="admin-card-body">

      {{-- Upload múltiplo --}}
      <form method="POST" action="{{ route('admin.products.images.store', $product) }}"
            enctype="multipart/form-data"
            style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
        @csrf
        <input type="file" name="images[]" multiple accept="image/*"
               style="flex:1; min-width:240px; padding:10px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text);">
        <button type="submit" class="admin-btn admin-btn-primary">
          <i class="bi bi-cloud-upload"></i> Enviar Imagens
        </button>
      </form>

      @if ($product->images->count())
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:14px;">
          @foreach ($product->images as $img)
            <div style="position:relative; border:1px solid var(--admin-border-strong); background:var(--admin-bg);">
              <div style="padding-top:100%; position:relative; overflow:hidden;">
                <img src="{{ $img->url }}" alt="Imagem"
                     style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">

                @if ($img->is_cover)
                  <span class="admin-badge admin-badge-success" style="position:absolute; top:8px; left:8px;">
                    <i class="bi bi-star-fill"></i> Capa
                  </span>
                @endif
              </div>

              <div style="display:flex; gap:4px; padding:8px;">
                @unless ($img->is_cover)
                  <form method="POST" action="{{ route('admin.products.images.cover', $img) }}" style="flex:1;">
                    @csrf @method('PUT')
                    <button type="submit" class="admin-row-btn success" title="Definir como capa" style="width:100%;">
                      <i class="bi bi-star"></i>
                    </button>
                  </form>
                @endunless

                <form method="POST" action="{{ route('admin.products.images.destroy', $img) }}"
                      onsubmit="return confirm('Remover esta imagem?')" style="flex:1;">
                  @csrf @method('DELETE')
                  <button type="submit" class="admin-row-btn danger" title="Remover" style="width:100%;">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div style="text-align:center; padding:40px; color:var(--admin-text-mut);">
          <i class="bi bi-images" style="font-size:40px; display:block; margin-bottom:12px;"></i>
          Nenhuma imagem cadastrada.
        </div>
      @endif

    </div>
  </div>

  {{-- ===================== VARIANTES ===================== --}}
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="bi bi-diagram-3"></i> Variantes</h3>
      <span class="admin-badge admin-badge-purple">{{ $product->variants->count() }} variante(s)</span>
    </div>
    <div class="admin-card-body">

      {{-- Form: nova variante --}}
      <form method="POST" action="{{ route('admin.products.variants.store', $product) }}"
            style="display:grid; grid-template-columns:2fr 1.2fr auto auto; gap:10px; margin-bottom:24px; align-items:end;">
        @csrf

        <div>
          <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome da Variante *</label>
          <input type="text" name="name" required placeholder="Ex: Cor, Tamanho, Voltagem"
                 style="width:100%; margin-top:8px; padding:10px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
        </div>

        <div>
          <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Tipo *</label>
          <select name="type" required
                  style="width:100%; margin-top:8px; padding:10px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            <option value="padrao">Padrão</option>
            <option value="nao_padrao">Não Padrão</option>
          </select>
        </div>

        <label style="display:flex; align-items:center; gap:8px; padding-top:20px;">
          <input type="hidden" name="required" value="0">
          <input type="checkbox" name="required" value="1" checked style="width:18px;height:18px;">
          <span style="font-size:12px; font-weight:700; color:var(--admin-text);">Obrigatória</span>
        </label>

        <button type="submit" class="admin-btn admin-btn-primary" style="height:42px;">
          <i class="bi bi-plus-lg"></i> Adicionar
        </button>
      </form>

      {{-- Lista de variantes --}}
      @forelse ($product->variants as $variant)
        <div style="border:1px solid var(--admin-border-strong); margin-bottom:14px; background:var(--admin-bg);">

          {{-- Header da variante --}}
          <div style="display:grid; grid-template-columns:1fr auto auto; gap:12px; align-items:center; padding:14px 16px; background:var(--admin-card); border-bottom:1px solid var(--admin-border-strong);">
            <div>
              <strong style="color:var(--admin-text); font-size:14px;">{{ $variant->name }}</strong>
              <span class="admin-badge {{ $variant->type === 'padrao' ? 'admin-badge-info' : 'admin-badge-warning' }}" style="margin-left:10px;">
                {{ $variant->type_label }}
              </span>
              @if ($variant->required)
                <span class="admin-badge admin-badge-purple" style="margin-left:6px;">Obrigatória</span>
              @endif
              <span style="color:var(--admin-text-mut); font-size:12px; margin-left:10px;">
                {{ $variant->items->count() }} item(ns)
              </span>
            </div>

            <form method="POST" action="{{ route('admin.variants.destroy', $variant) }}"
                  onsubmit="return confirm('Remover esta variante e todos os itens?')">
              @csrf @method('DELETE')
              <button type="submit" class="admin-row-btn danger">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </div>

          {{-- Itens --}}
          <div style="padding:14px 16px;">

            @if ($variant->items->count())
              <table style="width:100%; border-collapse:collapse; margin-bottom:12px;">
                <thead>
                  <tr style="border-bottom:1px solid var(--admin-border);">
                    <th style="text-align:left; padding:8px 6px; font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Valor</th>
                    <th style="text-align:left; padding:8px 6px; font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Cor</th>
                    <th style="text-align:left; padding:8px 6px; font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Preço</th>
                    <th style="text-align:left; padding:8px 6px; font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Estoque</th>
                    <th style="text-align:left; padding:8px 6px; font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Img</th>
                    <th style="text-align:right; padding:8px 6px; font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($variant->items as $item)
                    <tr style="border-bottom:1px solid var(--admin-border);">
                      <td style="padding:8px 6px; color:var(--admin-text); font-weight:700;">{{ $item->value }}</td>
                      <td style="padding:8px 6px;">
                        @if ($item->color_hex)
                          <span style="display:inline-block; width:18px; height:18px; background:{{ $item->color_hex }}; border:1px solid var(--admin-border-strong); vertical-align:middle;"></span>
                        @else
                          <span style="color:var(--admin-text-mut);">—</span>
                        @endif
                      </td>
                      <td style="padding:8px 6px; color:var(--admin-accent-2); font-weight:700;">
                        R$ {{ number_format($item->price ?? 0, 2, ',', '.') }}
                      </td>
                      <td style="padding:8px 6px; color:var(--admin-text);">{{ $item->stock }}</td>
                      <td style="padding:8px 6px;">
                        @if ($item->image)
                          <img src="{{ $item->image_url }}" style="width:36px;height:36px;object-fit:cover;border:1px solid var(--admin-border-strong);">
                        @else
                          <span style="color:var(--admin-text-mut);">—</span>
                        @endif
                      </td>
                      <td style="padding:8px 6px; text-align:right;">
                        <form method="POST" action="{{ route('admin.variants.items.destroy', $item) }}"
                              onsubmit="return confirm('Remover este item?')" style="display:inline;">
                          @csrf @method('DELETE')
                          <button type="submit" class="admin-row-btn danger"><i class="bi bi-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <p style="color:var(--admin-text-mut); margin:0 0 12px; font-size:12px;">Nenhum item cadastrado para esta variante.</p>
            @endif

            {{-- Form: novo item --}}
            <form method="POST" action="{{ route('admin.variants.items.store', $variant) }}"
                  enctype="multipart/form-data"
                  style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1.5fr auto; gap:8px; align-items:end; padding-top:12px; border-top:1px solid var(--admin-border);">
              @csrf

              <div>
                <label style="font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Valor *</label>
                <input type="text" name="value" required placeholder="Ex: Verde"
                       style="width:100%; margin-top:6px; padding:8px; background:var(--admin-card); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; font-size:12px;">
              </div>

              <div>
                <label style="font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Cor (hex)</label>
                <input type="color" name="color_hex" value="#22c55e"
                       style="width:100%; margin-top:6px; height:36px; padding:2px; background:var(--admin-card); border:1px solid var(--admin-border-strong);">
              </div>

              <div>
                <label style="font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Preço (R$)</label>
                <input type="number" name="price" step="0.01" min="0" value="0"
                       style="width:100%; margin-top:6px; padding:8px; background:var(--admin-card); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; font-size:12px;">
              </div>

              <div>
                <label style="font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Estoque</label>
                <input type="number" name="stock" min="0" value="0"
                       style="width:100%; margin-top:6px; padding:8px; background:var(--admin-card); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit; font-size:12px;">
              </div>

              <div>
                <label style="font-size:10px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Imagem</label>
                <input type="file" name="image" accept="image/*"
                       style="width:100%; margin-top:6px; padding:6px; background:var(--admin-card); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-size:11px;">
              </div>

              <button type="submit" class="admin-btn admin-btn-primary" style="height:36px;">
                <i class="bi bi-plus-lg"></i>
              </button>
            </form>

          </div>
        </div>
      @empty
        <div style="text-align:center; padding:40px; color:var(--admin-text-mut);">
          <i class="bi bi-diagram-3" style="font-size:40px; display:block; margin-bottom:12px;"></i>
          Nenhuma variante cadastrada. Adicione a primeira acima.
        </div>
      @endforelse

    </div>
  </div>

@endsection