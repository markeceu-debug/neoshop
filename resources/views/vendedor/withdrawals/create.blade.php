@extends('layouts.vendor')

@section('title', 'Novo Saque')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Solicitar Saque</h2>
      <p class="admin-page-sub">Saldo disponível: <strong style="color:var(--admin-green);">R$ {{ number_format($wallet->balance, 2, ',', '.') }}</strong></p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('vendor.withdrawals.index') }}" class="admin-btn admin-btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
      </a>
    </div>
  </div>

  @if ($errors->any())
    <div class="admin-card" style="padding:16px; border-left:3px solid var(--admin-red); margin-bottom:20px;">
      <ul style="margin:0; padding-left:18px; color:var(--admin-red);">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('vendor.withdrawals.store') }}" class="admin-card" style="padding:24px;">
    @csrf

    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

      {{-- VALOR --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Valor do Saque (R$) *
        </label>
        <input type="number" name="amount" step="0.01" required
               min="{{ $minWithdrawal }}"
               max="{{ $wallet->balance }}"
               value="{{ old('amount', $wallet->balance) }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
        <small style="color:var(--admin-text-mut); font-size:11px;">
          Mínimo: R$ {{ number_format($minWithdrawal, 2, ',', '.') }} · Máx: R$ {{ number_format($wallet->balance, 2, ',', '.') }}
        </small>
      </div>

      {{-- MÉTODO --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Método *
        </label>
        <select name="method" required
                style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
          <option value="bank_transfer">Transferência Bancária</option>
          <option value="multicaixa_express">Multicaixa Express</option>
        </select>
      </div>

      {{-- TITULAR --}}
      <div style="grid-column: 1 / -1;">
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Nome do Titular *
        </label>
        <input type="text" name="bank_holder" required
               value="{{ old('bank_holder', auth()->user()->name) }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      {{-- BANCO --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Banco *
        </label>
        <input type="text" name="bank_name" required
               placeholder="Ex: BAI, BFA, BIC, Standard Bank..."
               value="{{ old('bank_name') }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      {{-- Nº CONTA --}}
      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Número da Conta *
        </label>
        <input type="text" name="bank_account" required
               value="{{ old('bank_account') }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      {{-- IBAN --}}
      <div style="grid-column: 1 / -1;">
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          IBAN *
        </label>
        <input type="text" name="bank_iban" required
               placeholder="AO06 0000 0000 0000 0000 0000 0"
               value="{{ old('bank_iban') }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:monospace;">
      </div>

      {{-- MULTICAIXA EXPRESS --}}
      <div style="grid-column: 1 / -1;">
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
          Multicaixa Express (opcional)
        </label>
        <input type="text" name="bank_express"
               placeholder="Ex: 923 000 000"
               value="{{ old('bank_express') }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

    </div>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <i class="bi bi-send"></i> Solicitar Saque
      </button>
      <a href="{{ route('vendor.withdrawals.index') }}" class="admin-btn admin-btn-secondary">Cancelar</a>
    </div>

    <div style="margin-top:20px; padding:14px 16px; background:rgba(251,191,36,0.08); border-left:3px solid var(--admin-orange); font-size:12px; color:var(--admin-text-sec);">
      <strong style="color:var(--admin-orange); display:block; margin-bottom:4px;">
        <i class="bi bi-info-circle-fill"></i> Como funciona
      </strong>
      Após solicitar, o admin analisa e faz o pagamento em até 3 dias úteis. O valor fica reservado (não disponível) até o pagamento ser confirmado.
    </div>

  </form>

@endsection