@extends('layouts.admin')

@section('title', 'Produtos')

@section('content')

  <div class="admin-page-header">
<div>
  <h2 class="admin-page-title">Gerenciar Produtos</h2>
  <p class="admin-page-sub">Catálogo de produtos da loja</p>
</div>
<div class="admin-page-actions">
  <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary">
<i class="bi bi-plus-lg"></i> Novo Produto
  </a>
</div>
  </div>

  <div class="admin-table-card">
<div class="admin-table-toolbar">
  <div class="admin-table-search">
<i class="bi bi-search"></i>
<input type="text" id="searchInput" placeholder="Buscar produto por nome ou SKU...">
  </div>
</div>

<div class="admin-table-wrap">
  {{ $dataTable->table(['class' => 'display', 'style' => 'width:100%']) }}
</div>
  </div>


   {{-- ============================================================ --}}
{{-- BARRA FLUTUANTE DE AJUSTE EM MASSA                          --}}
{{-- ============================================================ --}}
<div class="bulk-bar" id="bulkBar">
  <div class="bulk-bar-info">
    <i class="bi bi-check2-square"></i>
    <strong id="bulkCount">0</strong>
    <span>produto(s) selecionado(s)</span>
  </div>

  <div class="bulk-bar-actions">
    <button type="button" class="bulk-btn" onclick="openBulkModal('increment')">
      <i class="bi bi-plus-lg"></i> Ajuste em massa
    </button>

    <button type="button" class="bulk-btn bulk-btn-ghost" onclick="clearBulk()">
      <i class="bi bi-x-lg"></i> Limpar
    </button>
  </div>
</div>

<div class="bulk-modal-overlay" id="bulkModal">
  <div class="bulk-modal">

    <div class="bulk-modal-header">
      <h3><i class="bi bi-boxes"></i> Ajuste em Massa</h3>
      <button type="button" class="bulk-modal-close" onclick="closeBulkModal()">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('admin.stock.bulk-adjust') }}" id="bulkForm">
      @csrf
      <div id="bulkIdsContainer"></div>

      <div class="bulk-modal-body">

        <div class="bulk-summary">
          <i class="bi bi-info-circle-fill"></i>
          <span id="bulkSummaryText">Você está ajustando <strong id="bulkModalCount">0</strong> produto(s).</span>
        </div>

        {{-- ============ MODO ============ --}}
        <label class="bulk-label">Operação</label>
        <div class="bulk-mode-grid">
          <label class="bulk-mode-option">
            <input type="radio" name="mode" value="increment" checked>
            <div class="bulk-mode-card">
              <i class="bi bi-plus-circle-fill"></i>
              <strong>Adicionar</strong>
              <span>+ N ao estoque</span>
            </div>
          </label>

          <label class="bulk-mode-option">
            <input type="radio" name="mode" value="decrement">
            <div class="bulk-mode-card">
              <i class="bi bi-dash-circle-fill"></i>
              <strong>Remover</strong>
              <span>− N do estoque</span>
            </div>
          </label>

          <label class="bulk-mode-option">
            <input type="radio" name="mode" value="set">
            <div class="bulk-mode-card">
              <i class="bi bi-sliders"></i>
              <strong>Definir valor</strong>
              <span>Substituir por N</span>
            </div>
          </label>
        </div>

        {{-- ============ ONDE APLICAR ============ --}}
        <label class="bulk-label">Aplicar em</label>
        <div class="bulk-target-grid">

          <label class="bulk-target-option">
            <input type="radio" name="target" value="auto" checked>
            <div class="bulk-target-card">
              <i class="bi bi-magic"></i>
              <strong>Automático</strong>
              <span>Ajusta cada produto onde o estoque vive</span>
            </div>
          </label>

          <label class="bulk-target-option">
            <input type="radio" name="target" value="products">
            <div class="bulk-target-card">
              <i class="bi bi-box-seam"></i>
              <strong>Apenas Produtos</strong>
              <span>Só produtos sem variantes</span>
            </div>
          </label>

          <label class="bulk-target-option">
            <input type="radio" name="target" value="variants">
            <div class="bulk-target-card">
              <i class="bi bi-diagram-3"></i>
              <strong>Apenas Variantes</strong>
              <span>Só itens de variante (SKUs reais)</span>
            </div>
          </label>

        </div>

        {{-- ============ VALOR ============ --}}
        <label class="bulk-label" for="bulkValue">Quantidade</label>
        <input type="number" name="value" id="bulkValue" min="0" value="10" required
               class="bulk-input">

        {{-- ============ MOTIVO ============ --}}
        <label class="bulk-label" for="bulkReason">Motivo (opcional)</label>
        <input type="text" name="reason" id="bulkReason" maxlength="120"
               placeholder="Ex: reposição de fornecedor, inventário..."
               class="bulk-input">

        {{-- ============ PREVIEW DINÂMICO ============ --}}
        <div id="bulkPreview" class="bulk-preview" style="display:none;">
          <div class="bulk-preview-title">
            <i class="bi bi-eye-fill"></i> Prévia
          </div>
          <div id="bulkPreviewBody"></div>
        </div>

      </div>

      <div class="bulk-modal-footer">
        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeBulkModal()">
          Cancelar
        </button>
        <button type="submit" class="admin-btn admin-btn-primary">
          <i class="bi bi-check-lg"></i> Aplicar Ajuste
        </button>
      </div>

    </form>
  </div>
</div>
@endsection

  @push('scripts')
  {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

  <script>
document.addEventListener('DOMContentLoaded', function () {

  // Busca customizada
  document.getElementById('searchInput').addEventListener('input', function () {
window.LaravelDataTables['productsTable'].search(this.value).draw();
  });

  // Check all
  document.addEventListener('change', function (e) {
if (e.target && e.target.id === 'checkAll') {
  document.querySelectorAll('.row-check').forEach(function (c) {
c.checked = e.target.checked;
  });
}
  });

  // ============ EXPANDIR LINHA DE VARIANTES ============
  document.addEventListener('click', async function (e) {
const btn = e.target.closest('.admin-expand-btn');
if (!btn) return;

const tr = btn.closest('tr');
const productId = btn.dataset.id;

// Se já está aberto → fecha
const next = tr.nextElementSibling;
if (next && next.classList.contains('admin-variants-row')) {
  next.remove();
  btn.classList.remove('open');
  return;
}

// Fecha qualquer outro aberto
document.querySelectorAll('.admin-variants-row').forEach(r => r.remove());
document.querySelectorAll('.admin-expand-btn.open').forEach(b => b.classList.remove('open'));

btn.classList.add('open');

// Cria linha com loading
const cols = tr.children.length;
const child = document.createElement('tr');
child.className = 'admin-variants-row';
child.innerHTML = `<td colspan="${cols}"><div class="admin-variants-panel">Carregando...</div></td>`;
tr.after(child);

try {
  const resp = await fetch(`/admin/products/${productId}/variants-json`, {
headers: { 'Accept': 'application/json' }
  });
  const data = await resp.json();

  if (!data.variants.length) {
child.querySelector('.admin-variants-panel').innerHTML =
  `<div class="admin-variants-empty"><i class="bi bi-diagram-3" style="font-size:24px;display:block;margin-bottom:8px;"></i> Nenhuma variante cadastrada.</div>`;
return;
  }

  let html = `<div class="admin-variants-title">Variantes de "${data.product.name}"</div><div class="admin-variants-grid">`;

  data.variants.forEach(v => {
html += `
  <div class="admin-variant-box">
<div class="admin-variant-head">
  <span class="admin-variant-name">${v.name}</span>
  <span class="admin-badge ${v.type === 'Padrão' ? 'admin-badge-info' : 'admin-badge-warning'}">${v.type}</span>
</div>
<div class="admin-variant-items">
`;

if (!v.items.length) {
  html += `<div class="admin-variant-item" style="grid-template-columns:1fr; justify-content:center; color:var(--admin-text-mut);">Sem itens</div>`;
} else {
  v.items.forEach(i => {
const swatch = i.color_hex
  ? `<span class="admin-variant-item-swatch" style="background:${i.color_hex};"></span>`
  : `<span class="admin-variant-item-swatch" style="background:transparent;"></span>`;

html += `
  <div class="admin-variant-item">
${swatch}
<span class="admin-variant-item-name">${i.value}</span>
<span class="admin-variant-item-price">R$ ${i.price}</span>
<span class="admin-variant-item-stock">${i.stock} un</span>
  </div>
`;
  });
}

html += `</div></div>`;
  });

  html += `</div>`;
  child.querySelector('.admin-variants-panel').innerHTML = html;

} catch (err) {
  child.querySelector('.admin-variants-panel').innerHTML =
`<div class="admin-variants-empty" style="color:var(--admin-red);">Erro ao carregar variantes.</div>`;
}
  });
});
  </script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const bulkBar     = document.getElementById('bulkBar');
  const bulkCount   = document.getElementById('bulkCount');
  const modal       = document.getElementById('bulkModal');
  const idsBox      = document.getElementById('bulkIdsContainer');
  const summaryText = document.getElementById('bulkSummaryText');
  const previewBox  = document.getElementById('bulkPreview');
  const previewBody = document.getElementById('bulkPreviewBody');

  // ---- Retorna os IDs marcados ----
  function getSelected() {
    return Array.from(document.querySelectorAll('.row-check:checked')).map(c => c.value);
  }

  // ---- Atualiza a barra flutuante ----
  function refreshBar() {
    const ids = getSelected();
    bulkCount.textContent = ids.length;

    document.querySelectorAll('.row-check').forEach(c => {
      const tr = c.closest('tr');
      if (tr) tr.classList.toggle('selected', c.checked);
    });

    bulkBar.classList.toggle('show', ids.length > 0);
  }

  // ---- Atualiza a prévia do modal (usa o endpoint variants-json) ----
  async function refreshPreview() {
    const ids = getSelected();
    if (!ids.length) {
      previewBox.style.display = 'none';
      return;
    }

    previewBox.style.display = 'block';
    previewBody.innerHTML = '<div style="color:var(--admin-text-mut);">Calculando...</div>';

    let totalProductos   = 0;
    let totalVariants    = 0;
    let productsWithVar  = 0;
    let productsNoVar    = 0;

    try {
      const promises = ids.map(id =>
        fetch(`/admin/products/${id}/variants-json`).then(r => r.json())
      );
      const results = await Promise.all(promises);

      results.forEach(r => {
        const variantCount = r.variants.reduce((acc, v) => acc + v.items.length, 0);
        if (variantCount > 0) {
          productsWithVar++;
          totalVariants += variantCount;
        } else {
          productsNoVar++;
          totalProductos++;
        }
      });

      previewBody.innerHTML = `
        <div class="bulk-preview-line">
          <i class="bi bi-box-seam"></i>
          <span><strong>${productsNoVar}</strong> produto(s) sem variantes → ajuste no Product</span>
        </div>
        <div class="bulk-preview-line">
          <i class="bi bi-diagram-3"></i>
          <span><strong>${productsWithVar}</strong> produto(s) com variantes → <strong>${totalVariants}</strong> item(ns) de variante</span>
        </div>
      `;

      // Atualiza o resumo
      summaryText.innerHTML = `Ajustando <strong>${ids.length}</strong> produto(s) — <strong>${productsNoVar + totalVariants}</strong> alvo(s) real(is).`;

    } catch (e) {
      previewBody.innerHTML = '<div style="color:var(--admin-red);">Erro ao calcular prévia.</div>';
    }
  }

  // ---- Escuta cliques em checkbox ----
  document.addEventListener('change', function (e) {
    if (e.target && (e.target.classList.contains('row-check') || e.target.id === 'checkAll')) {
      if (e.target.id === 'checkAll') {
        document.querySelectorAll('.row-check').forEach(c => c.checked = e.target.checked);
      }
      refreshBar();
    }
  });

  // ---- Redesenha após paginação ----
  if (window.LaravelDataTables && window.LaravelDataTables['productsTable']) {
    window.LaravelDataTables['productsTable'].on('draw', function () {
      setTimeout(refreshBar, 50);
    });
  }

  // ---- Abre modal ----
  window.openBulkModal = function () {
    const ids = getSelected();
    if (!ids.length) return;

    idsBox.innerHTML = ids.map(id =>
      `<input type="hidden" name="products[]" value="${id}">`
    ).join('');

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    refreshPreview();
  };

  // ---- Fecha modal ----
  window.closeBulkModal = function () {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  };

  // ---- Limpa seleção ----
  window.clearBulk = function () {
    document.querySelectorAll('.row-check').forEach(c => c.checked = false);
    const checkAll = document.getElementById('checkAll');
    if (checkAll) checkAll.checked = false;
    refreshBar();
  };

  // ---- ESC fecha ----
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) closeBulkModal();
  });

  // ---- Clique fora fecha ----
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeBulkModal();
  });

  refreshBar();
});
</script>
@endpush