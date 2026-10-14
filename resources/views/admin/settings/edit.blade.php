@extends('layouts.admin')

@section('title', 'Configurações')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Configurações do Sistema</h2>
      <p class="admin-page-sub">Comissão, dados bancários e configurações da loja</p>
    </div>
  </div>

  @if ($errors->any())
    <div class="admin-card" style="padding:16px; border-left:3px solid var(--admin-red); margin-bottom:20px;">
      <ul style="margin:0; padding-left:18px; color:var(--admin-red);">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

      {{-- ============================================================ --}}
      {{-- COMISSÃO E GARANTIA                                          --}}
      {{-- ============================================================ --}}
      <div class="admin-card" style="grid-column: 1 / -1;">
        <div class="admin-card-header">
          <h3 class="admin-card-title">
            <i class="bi bi-percent"></i> Comissão e Garantia
          </h3>
        </div>
        <div class="admin-card-body">
          <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px;">

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Comissão da Plataforma (%) *
              </label>
              <div style="display:flex; align-items:center; margin-top:8px;">
                <input type="number" name="commission_rate" step="0.01" min="0" max="100" required
                       value="{{ old('commission_rate', $settings['commission_rate']) }}"
                       style="width:100%; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
                <span style="padding:12px 16px; background:var(--admin-card); border:1px solid var(--admin-border-strong); border-left:none; color:var(--admin-text-mut); font-weight:800;">%</span>
              </div>
              <small style="color:var(--admin-text-mut); font-size:11px;">
                Quanto a plataforma recebe de cada venda
              </small>
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Comissão Mínima por Pedido (R$)
              </label>
              <input type="number" name="commission_min" step="0.01" min="0" required
                     value="{{ old('commission_min', $settings['commission_min']) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
              <small style="color:var(--admin-text-mut); font-size:11px;">
                Cobrada mesmo se o % for menor
              </small>
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Dias de Garantia *
              </label>
              <div style="display:flex; align-items:center; margin-top:8px;">
                <input type="number" name="guarantee_days" min="1" max="60" required
                       value="{{ old('guarantee_days', $settings['guarantee_days']) }}"
                       style="width:100%; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
                <span style="padding:12px 16px; background:var(--admin-card); border:1px solid var(--admin-border-strong); border-left:none; color:var(--admin-text-mut); font-weight:800;">dias</span>
              </div>
              <small style="color:var(--admin-text-mut); font-size:11px;">
                Tempo até liberar o dinheiro pro vendedor
              </small>
            </div>

          </div>

          {{-- SIMULADOR --}}
          <div style="margin-top:24px; padding:20px; background:linear-gradient(135deg, rgba(124,92,255,0.08), rgba(0,229,255,0.04)); border-left:3px solid var(--admin-accent);">
            <div style="font-size:11px; color:var(--admin-accent); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:12px;">
              <i class="bi bi-calculator"></i> Simulação de comissão
            </div>
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; font-size:12px;">
              <div>
                <span style="color:var(--admin-text-mut);">Pedido de R$ 1.000</span>
                <div style="font-size:14px; color:var(--admin-text); font-weight:800; margin-top:4px;">
                  <span id="simCommission1">—</span> comissão
                </div>
                <div style="font-size:11px; color:var(--admin-green);">
                  <span id="simSeller1">—</span> pro vendedor
                </div>
              </div>
              <div>
                <span style="color:var(--admin-text-mut);">Pedido de R$ 5.000</span>
                <div style="font-size:14px; color:var(--admin-text); font-weight:800; margin-top:4px;">
                  <span id="simCommission2">—</span> comissão
                </div>
                <div style="font-size:11px; color:var(--admin-green);">
                  <span id="simSeller2">—</span> pro vendedor
                </div>
              </div>
              <div>
                <span style="color:var(--admin-text-mut);">Pedido de R$ 10.000</span>
                <div style="font-size:14px; color:var(--admin-text); font-weight:800; margin-top:4px;">
                  <span id="simCommission3">—</span> comissão
                </div>
                <div style="font-size:11px; color:var(--admin-green);">
                  <span id="simSeller3">—</span> pro vendedor
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- ============================================================ --}}
      {{-- DADOS BANCÁRIOS                                              --}}
      {{-- ============================================================ --}}
      <div class="admin-card" style="grid-column: 1 / -1;">
        <div class="admin-card-header">
          <h3 class="admin-card-title">
            <i class="bi bi-bank2"></i> Dados Bancários para Recebimento
          </h3>
        </div>
        <div class="admin-card-body">
          <div style="background:rgba(0,229,255,0.06); border-left:3px solid var(--admin-cyan); padding:12px 16px; margin-bottom:20px; font-size:12px; color:var(--admin-text-sec);">
            <i class="bi bi-info-circle-fill" style="color:var(--admin-cyan);"></i>
            Esses dados aparecem para o cliente no checkout. O cliente paga aqui e envia o comprovativo.
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

            <div style="grid-column: 1 / -1;">
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Nome do Titular *
              </label>
              <input type="text" name="bank_holder" required
                     value="{{ old('bank_holder', $settings['bank_holder']) }}"
                     placeholder="Ex: NeoShop Angola, Lda"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Banco *
              </label>
              <input type="text" name="bank_name" required
                     value="{{ old('bank_name', $settings['bank_name']) }}"
                     placeholder="Ex: BAI, BFA, BIC"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Número da Conta *
              </label>
              <input type="text" name="bank_account" required
                     value="{{ old('bank_account', $settings['bank_account']) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:monospace;">
            </div>

            <div style="grid-column: 1 / -1;">
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                IBAN *
              </label>
              <input type="text" name="bank_iban" required
                     value="{{ old('bank_iban', $settings['bank_iban']) }}"
                     placeholder="AO06 0000 0000 0000 0000 0000 0"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:monospace;">
            </div>

            <div style="grid-column: 1 / -1;">
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Multicaixa Express
              </label>
              <input type="text" name="multicaixa_express"
                     value="{{ old('multicaixa_express', $settings['multicaixa_express']) }}"
                     placeholder="Ex: 923 000 000"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

          </div>
        </div>
      </div>

      {{-- ============================================================ --}}
      {{-- SAQUES                                                       --}}
      {{-- ============================================================ --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title">
            <i class="bi bi-wallet2"></i> Saques
          </h3>
        </div>
        <div class="admin-card-body">
          <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
            Valor Mínimo para Saque (R$) *
          </label>
          <input type="number" name="min_withdrawal" step="0.01" min="0" required
                 value="{{ old('min_withdrawal', $settings['min_withdrawal']) }}"
                 style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
          <small style="color:var(--admin-text-mut); font-size:11px;">
            O vendedor só pode sacar acima desse valor
          </small>
        </div>
      </div>

      {{-- ============================================================ --}}
      {{-- LOJA                                                         --}}
      {{-- ============================================================ --}}
      <div class="admin-card">
        <div class="admin-card-header">
          <h3 class="admin-card-title">
            <i class="bi bi-shop"></i> Dados da Loja
          </h3>
        </div>
        <div class="admin-card-body">
          <div style="display:flex; flex-direction:column; gap:16px;">

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Nome da Loja *
              </label>
              <input type="text" name="store_name" required
                     value="{{ old('store_name', $settings['store_name']) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                E-mail de Contato
              </label>
              <input type="email" name="store_email"
                     value="{{ old('store_email', $settings['store_email']) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                Telefone
              </label>
              <input type="text" name="store_phone"
                     value="{{ old('store_phone', $settings['store_phone']) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

          </div>
        </div>
      </div>

      {{-- BOTÃO SALVAR --}}
      <div style="grid-column: 1 / -1; display:flex; gap:10px;">
        <button type="submit" class="admin-btn admin-btn-primary" style="padding:14px 32px; font-size:14px;">
          <i class="bi bi-check-lg"></i> Salvar Configurações
        </button>
        <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary" style="padding:14px 32px;">
          Cancelar
        </a>
      </div>

    </div>
  </form>

@endsection

@push('scripts')
<script>
  // Simulador de comissão
  function updateSimulator() {
    const rate = parseFloat(document.querySelector('input[name="commission_rate"]').value) || 0;
    const min  = parseFloat(document.querySelector('input[name="commission_min"]').value) || 0;

    [1000, 5000, 10000].forEach(function (base, i) {
      const percentComm = base * (rate / 100);
      const commission  = Math.max(percentComm, min);
      const sellerNet   = base - commission;

      document.getElementById('simCommission' + (i + 1)).textContent =
        'R$ ' + commission.toFixed(2).replace('.', ',');
      document.getElementById('simSeller' + (i + 1)).textContent =
        'R$ ' + sellerNet.toFixed(2).replace('.', ',');
    });
  }

  document.querySelector('input[name="commission_rate"]').addEventListener('input', updateSimulator);
  document.querySelector('input[name="commission_min"]').addEventListener('input', updateSimulator);
  updateSimulator();
</script>
@endpush