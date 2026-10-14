/* ============================================================
   NEOSHOP — CARRINHO (AJAX) — v3
   Inclui: add, update, remove, clear, sync, cupom, timer
   ============================================================ */

(function () {
  'use strict';

  // ---------- CSRF ----------
  const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.content ||
    document.querySelector('input[name="_token"]')?.value;

  // ---------- HTTP helper ----------
  function post(url, method, body) {
    return fetch(url, {
      method: method || 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: body ? JSON.stringify(body) : null,
      credentials: 'same-origin',
    }).then(function (r) {
      return r.json().then(function (data) {
        if (!r.ok) {
          throw { status: r.status, data: data };
        }
        return data;
      });
    });
  }

  // ---------- Toast ----------
  function toast(msg, type) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const el = document.createElement('div');
    el.className = 'neo-toast ' + (type === 'error' ? 'toast-error' : '');
    const icon =
      type === 'error'
        ? '<i class="bi bi-exclamation-triangle-fill" style="color:var(--danger)"></i>'
        : '<i class="bi bi-check-circle-fill"></i>';
    el.innerHTML = icon + '<span>' + msg + '</span>';
    container.appendChild(el);

    setTimeout(function () {
      el.style.transition = 'all 0.3s';
      el.style.opacity = '0';
      el.style.transform = 'translateX(400px)';
      setTimeout(function () { el.remove(); }, 300);
    }, 2600);
  }
  window.showToast = toast;

  // ---------- Badge global ----------
  function updateBadge(count) {
    const badge = document.getElementById('cartCountBadge');
    if (badge) badge.textContent = count;

    const miniCount = document.querySelector('#miniCart #cartCount');
    if (miniCount) miniCount.textContent = count;
  }

  // ---------- Totais na página /cart ----------
  function updateTotals(total, count) {
    document.querySelectorAll('[data-cart-total]').forEach(function (el) {
      el.textContent = 'R$ ' + total;
    });
    document.querySelectorAll('[data-cart-subtotal]').forEach(function (el) {
      el.textContent = 'R$ ' + total;
    });
    if (count !== undefined && count !== null) {
      document.querySelectorAll('[data-cart-items-count]').forEach(function (el) {
        el.textContent = count;
      });
      const pageCount = document.getElementById('cartPageCount');
      if (pageCount) pageCount.textContent = count + (count === 1 ? ' item' : ' itens');
    }
  }

  // ---------- Refresh do mini-cart ----------
  async function refreshMiniCart() {
    const wrap = document.getElementById('miniCartContent');
    if (!wrap) return;

    try {
      const res = await fetch('/cart/mini', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      wrap.innerHTML = await res.text();
    } catch (e) { /* silencioso */ }
  }
  window.refreshMiniCart = refreshMiniCart;

  /* ============================================================
     ADICIONAR AO CARRINHO
     ============================================================ */
  window.cartAdd = function (productId, variantItemId, qty, buttonEl) {
    if (buttonEl && buttonEl.dataset.loading === '1') return;

    if (buttonEl) {
      buttonEl.dataset.loading = '1';
      buttonEl.dataset.originalHtml = buttonEl.innerHTML;
      buttonEl.disabled = true;
      buttonEl.innerHTML =
        '<i class="bi bi-arrow-clockwise" style="animation:spin 0.8s linear infinite;"></i> Adicionando...';
    }

    post('/cart/add', 'POST', {
      product_id: productId,
      variant_item_id: variantItemId || null,
      quantity: qty || 1,
    })
      .then(function (data) {
        if (data.status === 'success') {
          toast(data.message || 'Adicionado!', 'success');
          updateBadge(data.cart_count);
          updateTotals(data.cart_total, data.cart_count);
          refreshMiniCart();

          const el = document.getElementById('miniCart');
          if (el) bootstrap.Offcanvas.getOrCreateInstance(el).show();
        } else {
          toast(data.message || 'Erro ao adicionar', 'error');
        }
      })
      .catch(function (err) {
        const msg = err?.data?.message || 'Erro de conexão.';
        toast(msg, 'error');
      })
      .finally(function () {
        if (buttonEl) {
          buttonEl.disabled = false;
          buttonEl.dataset.loading = '0';
          buttonEl.innerHTML = buttonEl.dataset.originalHtml || '<i class="bi bi-cart-plus"></i> Adicionar';
        }
      });
  };

  /* ============================================================
     ALTERAR QUANTIDADE
     ============================================================ */
  window.cartQty = function (itemId, delta) {
    const rows = document.querySelectorAll('[data-item-id="' + itemId + '"]');
    if (!rows.length) return;

    let currentQty = 0;
    rows.forEach(function (row) {
      const input = row.querySelector('[data-qty-input]');
      if (input && !isNaN(parseInt(input.value, 10))) {
        currentQty = parseInt(input.value, 10);
        return;
      }
      const span = row.querySelector('.cart-item-qty span');
      if (span && !isNaN(parseInt(span.textContent, 10))) {
        currentQty = parseInt(span.textContent, 10);
      }
    });

    const newQty = Math.max(0, currentQty + delta);

    post('/cart/' + itemId, 'PUT', { quantity: newQty })
      .then(function (data) {
        if (data.status !== 'success') {
          toast(data.message || 'Erro', 'error');
          return;
        }

        if (data.removed) {
          rows.forEach(function (row) {
            row.style.transition = 'all 0.3s';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-20px)';
            setTimeout(function () {
              row.remove();
              if (document.querySelectorAll('#cartItemsCol [data-item-id]').length === 0) {
                window.location.reload();
              }
            }, 300);
          });
        } else {
          rows.forEach(function (row) {
            const input = row.querySelector('[data-qty-input]');
            const span = row.querySelector('.cart-item-qty span');
            if (input) input.value = data.item.quantity;
            if (span) span.textContent = data.item.quantity;

            const sub = row.querySelector('[data-subtotal]');
            if (sub) sub.textContent = 'R$ ' + data.item.subtotal;
          });
        }

        updateBadge(data.cart_count);
        updateTotals(data.cart_total, data.cart_count);
        refreshMiniCart();
      })
      .catch(function (err) {
        toast(err?.data?.message || 'Erro de conexão.', 'error');
      });
  };

  /* ============================================================
     REMOVER ITEM
     ============================================================ */
  window.cartRemove = function (itemId) {
    post('/cart/' + itemId, 'DELETE')
      .then(function (data) {
        if (data.status !== 'success') {
          toast(data.message || 'Erro', 'error');
          return;
        }

        toast('Item removido.', 'success');
        updateBadge(data.cart_count);
        updateTotals(data.cart_total, data.cart_count);
        refreshMiniCart();

        const rows = document.querySelectorAll('[data-item-id="' + itemId + '"]');
        rows.forEach(function (row) {
          row.style.transition = 'all 0.3s';
          row.style.opacity = '0';
          row.style.transform = 'translateX(-20px)';
          setTimeout(function () {
            row.remove();
            if (document.querySelectorAll('#cartItemsCol [data-item-id]').length === 0) {
              window.location.reload();
            }
          }, 300);
        });
      })
      .catch(function (err) {
        toast(err?.data?.message || 'Erro de conexão.', 'error');
      });
  };

  /* ============================================================
     LIMPAR CARRINHO
     ============================================================ */
  window.cartClear = function () {
    if (!confirm('Remover todos os itens do carrinho?')) return;

    post('/cart', 'DELETE')
      .then(function (data) {
        if (data.status === 'success') {
          toast('Carrinho esvaziado.', 'success');
          updateBadge(0);
          updateTotals('0,00', 0);
          setTimeout(function () { window.location.reload(); }, 400);
        }
      })
      .catch(function (err) {
        toast(err?.data?.message || 'Erro de conexão.', 'error');
      });
  };

  /* ============================================================
     SYNC
     ============================================================ */
  window.cartSync = function () {
    post('/cart/sync', 'POST', {})
      .then(function (data) {
        if (data.status === 'success') {
          toast('Carrinho atualizado!', 'success');
          setTimeout(function () { window.location.reload(); }, 600);
        } else {
          toast(data.message || 'Erro ao atualizar', 'error');
        }
      })
      .catch(function (err) {
        toast(err?.data?.message || 'Erro de conexão.', 'error');
      });
  };

  /* ============================================================
     CUPOM — APLICAR
     ============================================================ */
  window.applyCoupon = function () {
    const input = document.getElementById('couponCode');
    if (!input) return;

    const code = input.value.trim().toUpperCase();
    if (!code) {
      toast('Digite um cupom.', 'error');
      return;
    }

    post('/coupon/apply', 'POST', { code: code })
      .then(function (data) {
        if (data.status === 'success') {
          toast(data.message || 'Cupom aplicado!', 'success');

          // Esconde input, mostra aplicado
          const inputWrap = document.getElementById('couponInputWrap');
          const applied = document.getElementById('couponApplied');
          if (inputWrap) inputWrap.style.display = 'none';
          if (applied) applied.style.display = 'flex';

          const codeEl = document.getElementById('couponAppliedCode');
          if (codeEl) codeEl.textContent = data.coupon.code;

          updateTotals(data.total, data.cart_count);
          refreshMiniCart();
        } else {
          toast(data.message || 'Cupom inválido', 'error');
        }
      })
      .catch(function (err) {
        toast(err?.data?.message || 'Cupom inválido', 'error');
      });
  };

  /* ============================================================
     CUPOM — REMOVER
     ============================================================ */
  window.removeCoupon = function () {
    post('/coupon/remove', 'DELETE')
      .then(function (data) {
        if (data.status === 'success') {
          toast('Cupom removido.', 'success');

          const inputWrap = document.getElementById('couponInputWrap');
          const applied = document.getElementById('couponApplied');
          const input = document.getElementById('couponCode');

          if (inputWrap) inputWrap.style.display = 'flex';
          if (applied) applied.style.display = 'none';
          if (input) input.value = '';

          updateTotals(data.total, data.cart_count);
          refreshMiniCart();
        }
      })
      .catch(function (err) {
        toast(err?.data?.message || 'Erro de conexão.', 'error');
      });
  };

  /* ============================================================
     CHECKOUT
     ============================================================ */
  window.cartCheckout = function () {
    window.location.href = '/checkout';
  };

  /* ============================================================
     CRONÔMETRO DE RESERVAS
     ============================================================ */
  function startReservationTimers() {
    document.querySelectorAll('[data-expires]').forEach(function (el) {
      const expires = new Date(el.dataset.expires).getTime();
      const timer = el.querySelector('.reservation-timer');
      if (!timer) return;

      function tick() {
        const diff = Math.floor((expires - Date.now()) / 1000);
        if (diff <= 0) {
          timer.textContent = 'expirado';
          el.style.color = 'var(--danger)';
          clearInterval(interval);
          return;
        }
        const m = Math.floor(diff / 60);
        const s = diff % 60;
        timer.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');

        if (diff < 60) el.style.color = 'var(--danger)';
        else if (diff < 300) el.style.color = 'var(--warning)';
        else el.style.color = 'var(--accent-2)';
      }

      tick();
      const interval = setInterval(tick, 1000);
    });
  }

  /* ============================================================
     SELECIONAR TODOS
     ============================================================ */
  function bindCheckAll() {
    const checkAll = document.getElementById('checkAll');
    const col = document.getElementById('cartItemsCol');
    if (!checkAll || !col) return;

    checkAll.addEventListener('change', function () {
      col.querySelectorAll('.cart-item-check input').forEach(function (cb) {
        cb.checked = checkAll.checked;
      });
    });

    col.querySelectorAll('.cart-item-check input').forEach(function (cb) {
      cb.addEventListener('change', function () {
        const all = col.querySelectorAll('.cart-item-check input');
        const checked = col.querySelectorAll('.cart-item-check input:checked');
        checkAll.checked = all.length > 0 && all.length === checked.length;
      });
    });
  }

  /* ============================================================
     INIT
     ============================================================ */
  document.addEventListener('DOMContentLoaded', function () {
    startReservationTimers();
    bindCheckAll();

    const miniCart = document.getElementById('miniCart');
    if (miniCart) {
      miniCart.addEventListener('show.bs.offcanvas', function () {
        refreshMiniCart();
      });
    }

    // Enter no input do cupom aplica direto
    const couponInput = document.getElementById('couponCode');
    if (couponInput) {
      couponInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          window.applyCoupon();
        }
      });
    }
  });
})();