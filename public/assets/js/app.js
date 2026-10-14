/* ============================================================
   NEOSHOP — APP.JS
   ============================================================ */

/* ============================================================
   TOAST (global)
   ============================================================ */
function showToast(html, cls) {
  cls = cls || '';
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const el = document.createElement('div');
  el.className = 'neo-toast ' + cls;
  el.innerHTML = html;
  container.appendChild(el);
  setTimeout(function () {
    el.style.transition = 'all 0.3s';
    el.style.opacity = '0';
    el.style.transform = 'translateX(400px)';
    setTimeout(function () { el.remove(); }, 300);
  }, 2500);
}

/* ============================================================
   FAVORITOS
   ============================================================ */
function toggleFavorite(btn) {
  btn.classList.toggle('active');
  const icon = btn.querySelector('i');
  if (btn.classList.contains('active')) {
    icon.className = 'bi bi-heart-fill';
    showToast('<i class="bi bi-heart-fill"></i><span>Adicionado aos favoritos!</span>');
  } else {
    icon.className = 'bi bi-heart';
    showToast('<i class="bi bi-heartbreak-fill"></i><span>Removido dos favoritos</span>');
  }
}

/* ============================================================
   NEOSHOP — APP.JS (AJAX global)
   ============================================================ */

// ------------------------------------------------------------
// CSRF global para todas as requisições AJAX
// ------------------------------------------------------------
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    'Accept': 'application/json'
  }
});

/* ============================================================
   TOAST
   ============================================================ */
function showToast(html, cls) {
  cls = cls || '';
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const el = document.createElement('div');
  el.className = 'neo-toast ' + cls;
  el.innerHTML = html;
  container.appendChild(el);

  setTimeout(function () {
    el.style.transition = 'all 0.3s';
    el.style.opacity = '0';
    el.style.transform = 'translateX(400px)';
    setTimeout(function () { el.remove(); }, 300);
  }, 2500);
}




function openCart() {
  refreshMiniCart();
  const el = document.getElementById('miniCart');
  if (el) bootstrap.Offcanvas.getOrCreateInstance(el).show();
}

function openFavorites() {
  const el = document.getElementById('favoritesPanel');
  if (el) bootstrap.Offcanvas.getOrCreateInstance(el).show();
}

/* ============================================================
   TOTAIS DO CARRINHO
   ============================================================ */
function refreshCartTotals() {
  $.get('/cart/status', function (data) {
    $('#cartTotal').text('R$ ' + formatMoney(data.total));
    $('#cartSubtotal').text('R$ ' + formatMoney(data.total));
    $('#cartCount').text(data.count + ' itens');
    updateCartBadge(data.count);

    if (data.count === 0) {
      setTimeout(function () { location.reload(); }, 400);
    }
  });
}

/* ============================================================
   FAVORITOS
   ============================================================ */
function toggleFavorite(btn) {
  const $btn = $(btn);
  $btn.toggleClass('active');

  const active = $btn.hasClass('active');
  const icon = $btn.find('i');

  icon.attr('class', active ? 'bi bi-heart-fill' : 'bi bi-heart');

  showToast(
    active
      ? '<i class="bi bi-heart-fill"></i><span>Adicionado aos favoritos!</span>'
      : '<i class="bi bi-heartbreak-fill"></i><span>Removido dos favoritos</span>'
  );
}

/* ============================================================
   UTILITÁRIOS
   ============================================================ */
function formatMoney(v) {
  return parseFloat(v || 0).toFixed(2).replace('.', ',');
}

// ------------------------------------------------------------
// Aplica formatação de moeda em qualquer elemento com [data-money]
// ------------------------------------------------------------
$(document).ready(function () {
  $('[data-money]').each(function () {
    const v = parseFloat($(this).text());
    $(this).text('R$ ' + formatMoney(v));
  });
});

/* ============================================================
   COMPARTILHAR (página de produto)
   ============================================================ */
function toggleShareMenu(e) {
  e.stopPropagation();
  const menu = document.getElementById('shareMenu');
  if (menu) menu.classList.toggle('open');
}

document.addEventListener('click', function (e) {
  const menu = document.getElementById('shareMenu');
  const btn  = document.getElementById('shareBtn');
  if (menu && !menu.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
    menu.classList.remove('open');
  }
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    const menu = document.getElementById('shareMenu');
    if (menu) menu.classList.remove('open');
  }
});

function shareTo(network) {
  const url   = window.location.href;
  const title = document.title;
  let shareUrl = '';

  switch (network) {
    case 'whatsapp':
      shareUrl = 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url);
      break;
    case 'facebook':
      shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
      break;
    case 'twitter':
      shareUrl = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url);
      break;
    case 'telegram':
      shareUrl = 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title);
      break;
    case 'copy':
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          showToast('<i class="bi bi-check-circle-fill"></i><span>Link copiado!</span>');
        }).catch(function () { fallbackCopy(url); });
      } else {
        fallbackCopy(url);
      }
      const menu = document.getElementById('shareMenu');
      if (menu) menu.classList.remove('open');
      return;
  }

  if (shareUrl) window.open(shareUrl, '_blank', 'noopener,noreferrer,width=600,height=500');
  const m = document.getElementById('shareMenu');
  if (m) m.classList.remove('open');
}

function fallbackCopy(text) {
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'fixed';
  ta.style.left = '-9999px';
  document.body.appendChild(ta);
  ta.select();
  try {
    document.execCommand('copy');
    showToast('<i class="bi bi-check-circle-fill"></i><span>Link copiado!</span>');
  } catch (err) {
    showToast('<i class="bi bi-exclamation-triangle-fill" style="color:var(--danger)"></i><span>Não foi possível copiar</span>');
  }
  document.body.removeChild(ta);
}

/* ============================================================
   ZOOM ESTILO AMAZON (página de produto)
   ============================================================ */
(function () {
  const container = document.getElementById('galleryMain');
  const lens      = document.getElementById('zoomLens');
  const panel     = document.getElementById('zoomPanel');
  const panelImg  = document.getElementById('zoomPanelImg');

  if (!container || !lens || !panel) return;

  const LENS_W = 120;
  const LENS_H = 120;

  function move(e) {
    const rect = container.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    let lensX = x - LENS_W / 2;
    let lensY = y - LENS_H / 2;

    if (lensX < 0) lensX = 0;
    if (lensY < 0) lensY = 0;
    if (lensX > rect.width  - LENS_W) lensX = rect.width  - LENS_W;
    if (lensY > rect.height - LENS_H) lensY = rect.height - LENS_H;

    lens.style.left = lensX + 'px';
    lens.style.top  = lensY + 'px';

    const ratioX = lensX / (rect.width  - LENS_W);
    const ratioY = lensY / (rect.height - LENS_H);

    const panelRect = panel.getBoundingClientRect();
    const panelImgW = panelRect.width  * 2;
    const panelImgH = panelRect.height * 2;

    const maxMoveX = panelImgW - panelRect.width;
    const maxMoveY = panelImgH - panelRect.height;

    panelImg.style.left = -(ratioX * maxMoveX) + 'px';
    panelImg.style.top  = -(ratioY * maxMoveY) + 'px';
  }

  container.addEventListener('mouseenter', function () { panel.classList.add('active'); });
  container.addEventListener('mouseleave', function () { panel.classList.remove('active'); });
  container.addEventListener('mousemove', move);
})();



/* ============================================================
   CARROSSÉIS DO INDEX (produtos com paginação)
   ============================================================ */
jQuery(function ($) {
  if (!$('.hero-carousel').length && !$('#flashOwl').length) return;

  // HERO
  $('.hero-carousel').owlCarousel({
    items: 1,
    loop: true,
    autoplay: true,
    autoplayTimeout: 5000,
    autoplayHoverPause: true,
    nav: false,
    dots: true
  });

  // PRODUTOS
  const ids = ['flashOwl', 'electronicsOwl', 'homeOwl', 'gamesOwl'];
  const owls = {};

  ids.forEach(function (id) {
    const $el = $('#' + id);
    if (!$el.length) return;

    $el.owlCarousel({
      loop: true,
      nav: false,
      dots: false,
      margin: 12,
      smartSpeed: 300,
      responsive: {
        0:    { items: 2 },
        576:  { items: 3 },
        768:  { items: 3 },
        992:  { items: 5 },
        1200: { items: 6 }
      }
    });

    const owl = $el.data('owl.carousel');
    if (!owl) return;

    owls[id] = owl;

    $el.on('resized.owl.carousel refreshed.owl.carousel', function () {
      buildPagination(id);
    });
    $el.on('translated.owl.carousel changed.owl.carousel', function () {
      updatePagination(id);
    });

    buildPagination(id);
  });

  function getVisible(id) {
    const owl = owls[id];
    return (owl && owl.settings && owl.settings.items) || 1;
  }
  function getTotal(id) {
    return $('#' + id).find('.owl-item:not(.cloned)').length;
  }
  function getRealIndex(id) {
    const owl = owls[id];
    const total = getTotal(id);
    let rel = owl.relative(owl._current);
    if (rel < 0) rel += total;
    if (rel >= total) rel = rel % total;
    return rel;
  }
  function getCurrentPage(id) {
    const vis = getVisible(id);
    const rel = getRealIndex(id);
    return Math.floor(rel / vis) + 1;
  }

  function buildPagination(id) {
    const $nav = $('.carousel-pagination[data-owl="' + id + '"]');
    if (!$nav.length) return;

    const total      = getTotal(id);
    const visible    = getVisible(id);
    const totalPages = Math.max(1, Math.ceil(total / visible));

    const html =
      '<button class="pagination-btn" data-page="prev" type="button">' +
        '<i class="bi bi-chevron-left"></i>' +
      '</button>' +
      '<span class="pagination-text">Página <strong class="current-page">1</strong> de <strong>' + totalPages + '</strong></span>' +
      '<button class="pagination-btn" data-page="next" type="button">' +
        '<i class="bi bi-chevron-right"></i>' +
      '</button>';

    $nav.html(html);

    $nav.off('click.pg').on('click.pg', '.pagination-btn', function () {
      const dir = $(this).data('page');
      const $el = $('#' + id);
      if (dir === 'prev') $el.trigger('prev.owl.carousel', [300]);
      else                $el.trigger('next.owl.carousel', [300]);
    });

    updatePagination(id);
  }

  function updatePagination(id) {
    const $nav = $('.carousel-pagination[data-owl="' + id + '"]');
    if (!$nav.length) return;

    const total      = getTotal(id);
    const visible    = getVisible(id);
    const totalPages = Math.max(1, Math.ceil(total / visible));
    const current    = Math.min(getCurrentPage(id), totalPages);

    $nav.find('.current-page').text(current);
    $nav.find('[data-page="prev"]').prop('disabled', false);
    $nav.find('[data-page="next"]').prop('disabled', false);
  }
});

/* ============================================================
   TABS DE CATEGORIA (index)
   ============================================================ */
document.querySelectorAll('.section-tabs[data-target]').forEach(function (tabGroup) {
  const targetId = tabGroup.getAttribute('data-target');
  const grid = document.getElementById(targetId);
  if (!grid) return;

  tabGroup.querySelectorAll('.section-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabGroup.querySelectorAll('.section-tab').forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');

      const filter = tab.getAttribute('data-filter');
      grid.querySelectorAll('[data-cat]').forEach(function (card) {
        card.style.display = (filter === 'all' || card.getAttribute('data-cat') === filter) ? '' : 'none';
      });
    });
  });
});

/* ============================================================
   TABS DE CONTEÚDO (página de produto)
   ============================================================ */
document.querySelectorAll('.content-tab-btn').forEach(function (btn) {
  btn.addEventListener('click', function () {
    const tab = this.getAttribute('data-tab');
    document.querySelectorAll('.content-tab-btn').forEach(function (b) { b.classList.remove('active'); });
    this.classList.add('active');
    document.querySelectorAll('.content-tab-pane').forEach(function (pane) {
      pane.classList.toggle('active', pane.getAttribute('data-pane') === tab);
    });
  });
});

/* ============================================================
   FILTROS DE AVALIAÇÃO
   ============================================================ */
document.querySelectorAll('.review-filter').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.querySelectorAll('.review-filter').forEach(function (b) { b.classList.remove('active'); });
    this.classList.add('active');
  });
});

/* ============================================================
   VARIAÇÕES (página de produto)
   ============================================================ */
document.querySelectorAll('.variant-options').forEach(function (group) {
  group.querySelectorAll('.variant-option').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (this.classList.contains('disabled')) return;
      group.querySelectorAll('.variant-option').forEach(function (b) { b.classList.remove('selected'); });
      this.classList.add('selected');

      const groupName = group.getAttribute('data-group');
      const val = this.getAttribute('data-value');
      const labelId = groupName === 'cor' ? 'selectedColor' : 'selectedVersion';
      const label = document.getElementById(labelId);
      if (label) label.textContent = val;
    });
  });
});

/* ============================================================
   QUANTIDADE
   ============================================================ */
function changeQtyDetail(delta) {
  const input = document.getElementById('qtyDetail');
  if (!input) return;
  let val = parseInt(input.value) + delta;
  if (val < 1) val = 1;
  if (val > 127) val = 127;
  input.value = val;
}

/* ============================================================
   FAVORITO NA PÁGINA DE PRODUTO
   ============================================================ */
function toggleFavDetail(btn) {
  btn.classList.toggle('active');
  const icon = btn.querySelector('i');
  if (btn.classList.contains('active')) {
    icon.className = 'bi bi-heart-fill';
    showToast('<i class="bi bi-heart-fill"></i><span>Salvo nos favoritos!</span>');
  } else {
    icon.className = 'bi bi-heart';
    showToast('<i class="bi bi-heartbreak-fill"></i><span>Removido dos favoritos</span>');
  }
}



/* ============================================================
   VIEW TOGGLE GRID / LIST (listagem)
   ============================================================ */
const productsList = document.getElementById('productsList');
const viewBtns = document.querySelectorAll('.view-btn');

// Carrega view salva
const savedView = localStorage.getItem('neoView') || 'grid';
applyView(savedView);

viewBtns.forEach(function (btn) {
  btn.addEventListener('click', function () {
    applyView(this.getAttribute('data-view'));
  });
});

function applyView(view) {
  viewBtns.forEach(function (b) {
    b.classList.toggle('active', b.getAttribute('data-view') === view);
  });

  if (view === 'list') {
    productsList.classList.add('list-view');
  } else {
    productsList.classList.remove('list-view');
  }

  localStorage.setItem('neoView', view);
}

/* ============================================================
   FILTROS ACCORDION
   ============================================================ */
document.querySelectorAll('.filter-group-title').forEach(function (title) {
  title.addEventListener('click', function () {
    this.parentElement.classList.toggle('collapsed');
  });
});

/* ============================================================
   FILTROS MOBILE (offcanvas)
   ============================================================ */
function toggleMobileFilters() {
  const sidebar = document.getElementById('filtersSidebar');
  const offcanvasEl = document.getElementById('filtersOffcanvas');
  if (!sidebar || !offcanvasEl) return;

  const oc = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  const clone = sidebar.cloneNode(true);
  clone.style.display = 'block';
  clone.style.position = 'static';
  clone.id = 'filtersSidebarMobile';

  clone.querySelectorAll('.filter-group-title').forEach(function (title) {
    title.addEventListener('click', function () {
      this.parentElement.classList.toggle('collapsed');
    });
  });

  const body = offcanvasEl.querySelector('.offcanvas-body');
  body.innerHTML = '';
  body.appendChild(clone);

  oc.show();
}

/* ============================================================
   PAGINAÇÃO DE PÁGINA (listagem)
   ============================================================ */
let currentPage = 1;
const totalPages = 13;

function goToPage(page) {
  currentPage = Math.max(1, Math.min(totalPages, page));
  updatePaginationUI();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevPage() {
  if (currentPage > 1) goToPage(currentPage - 1);
}

function nextPage() {
  if (currentPage < totalPages) goToPage(currentPage + 1);
}

function updatePaginationUI() {
  document.querySelectorAll('.page-btn[data-page]').forEach(function (btn) {
    const p = btn.getAttribute('data-page');
    if (p === 'prev' || p === 'next') return;
    btn.classList.toggle('active', parseInt(p) === currentPage);
  });
  const cur = document.querySelector('.current-page');
  if (cur) cur.textContent = currentPage;
  const prev = document.querySelector('[data-page="prev"]');
  const next = document.querySelector('[data-page="next"]');
  if (prev) prev.disabled = currentPage === 1;
  if (next) next.disabled = currentPage === totalPages;
}

/* ============================================================
   COUNTDOWN (index)
   ============================================================ */
(function () {
  const hhEl = document.getElementById('hh');
  const mmEl = document.getElementById('mm');
  const ssEl = document.getElementById('ss');
  if (!hhEl || !mmEl || !ssEl) return;

  let hh = 2, mm = 45, ss = 30;
  setInterval(function () {
    ss--;
    if (ss < 0) { ss = 59; mm--; }
    if (mm < 0) { mm = 59; hh--; }
    if (hh < 0) { hh = 23; }
    hhEl.textContent = String(hh).padStart(2, '0');
    mmEl.textContent = String(mm).padStart(2, '0');
    ssEl.textContent = String(ss).padStart(2, '0');
  }, 1000);
})();

/* ============================================================
   INIT (listagem)
   ============================================================ */
if (document.querySelector('.pagination-bar')) {
  updatePaginationUI();
}

/* ============================================================
   TABS DA LOJA (perfil)
   ============================================================ */
(function () {
  const tabs = document.querySelectorAll('.store-nav-tab');
  const panes = document.querySelectorAll('.store-tab-pane');

  if (!tabs.length || !panes.length) return;

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function (e) {
      e.preventDefault();

      const targetPane = this.getAttribute('data-tab');
      if (!targetPane) return;

      // Ativa a tab clicada, desativa as outras
      tabs.forEach(function (t) {
        t.classList.remove('active');
      });
      this.classList.add('active');

      // Mostra somente o pane correspondente
      panes.forEach(function (pane) {
        const matches = pane.getAttribute('data-pane') === targetPane;
        pane.classList.toggle('active', matches);
      });

      // Rola até o início da aba (opcional, mas fica melhor)
      const container = document.querySelector('.store-nav');
      if (container) {
        const offset = container.getBoundingClientRect().top + window.scrollY - 80;
        window.scrollTo({ top: offset, behavior: 'smooth' });
      }
    });
  });

  // Ativa a primeira tab por padrão, se nenhuma estiver ativa
  const hasActive = document.querySelector('.store-nav-tab.active');
  if (!hasActive && tabs.length) {
    tabs[0].click();
  }
})();

/* ============================================================
   FILTROS DE CATEGORIA DA LOJA
   ============================================================ */
(function () {
  const filterBars = document.querySelectorAll('.stores-filter-bar');

  filterBars.forEach(function (bar) {
    const buttons = bar.querySelectorAll('.stores-filter-btn');
    if (!buttons.length) return;

    // Descobre o container de produtos mais próximo
    // (pode ser .products-list ou .stores-grid)
    let targetContainer = bar.parentElement.querySelector('.products-list');
    if (!targetContainer) {
      targetContainer = bar.parentElement.querySelector('.stores-grid');
    }
    if (!targetContainer) {
      // fallback global
      targetContainer = document.querySelector('.products-list') || document.querySelector('.stores-grid');
    }
    if (!targetContainer) return;

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        // Marca só esse como ativo
        buttons.forEach(function (b) { b.classList.remove('active'); });
        this.classList.add('active');

        const filter = this.textContent.trim().toLowerCase();

        const cards = targetContainer.querySelectorAll('.product-card');

        cards.forEach(function (card) {
          if (filter === 'tudo' || filter === 'todas') {
            card.style.display = '';
            return;
          }

          const title = (card.querySelector('.product-title') || card)
                          .textContent
                          .toLowerCase();

          // Mapeamento de filtros → palavras-chave
          const keywords = {
            'smartphones': ['galaxy', 'smartphone', 'celular', 'phone'],
            'tvs': ['tv', 'smart tv', 'televisão', 'televisao'],
            'notebooks': ['notebook', 'galaxy book', 'laptop'],
            'eletrodomésticos': ['geladeira', 'micro-ondas', 'microondas', 'fogão', 'fogao', 'máquina', 'maquina'],
            'eletrodomesticos': ['geladeira', 'micro-ondas', 'microondas', 'fogão', 'fogao', 'máquina', 'maquina'],
            'acessórios': ['carregador', 'cabo', 'fone', 'buds', 'watch', 'soundbar', 'monitor', 'mouse', 'teclado'],
            'acessorios': ['carregador', 'cabo', 'fone', 'buds', 'watch', 'soundbar', 'monitor', 'mouse', 'teclado']
          };

          const list = keywords[filter] || [filter];
          const match = list.some(function (kw) { return title.indexOf(kw) !== -1; });

          card.style.display = match ? '' : 'none';
        });

        // Conta quantos aparecem e atualiza o "X produtos encontrados"
        const visible = Array.from(cards).filter(function (c) { return c.style.display !== 'none'; }).length;

        const countEl = targetContainer.parentElement.querySelector('.listing-count strong');
        if (countEl) countEl.textContent = visible;
      });
    });
  });
})();

