@extends('layouts.user')

@section('title', 'Meu Perfil')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Meu Perfil</h2>
      <p class="admin-page-sub">Atualize seus dados e endereço padrão</p>
    </div>
  </div>

  @if ($errors->any())
    <div class="admin-card" style="padding:16px; border-left:3px solid var(--admin-red); margin-bottom:20px;">
      <ul style="margin:0; padding-left:18px; color:var(--admin-red);">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('user.profile.update') }}" class="admin-card" style="padding:24px;">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:20px;">

      <div style="grid-column: 1 / -1;">
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome completo *</label>
        <input type="text" name="name" required value="{{ old('name', $user->name) }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Telefone</label>
        <input type="tel" name="telefone" value="{{ old('telefone', $user->telefone) }}"
               placeholder="923 000 000"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">E-mail</label>
        <input type="email" readonly value="{{ $user->email }}"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text-mut); font-family:inherit; cursor:not-allowed;">
      </div>

      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Província *</label>
        <input type="text" name="province" required value="{{ old('province', $user->province) }}"
               placeholder="Ex: Luanda"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Município *</label>
        <input type="text" name="municipality" required value="{{ old('municipality', $user->municipality) }}"
               placeholder="Ex: Belas"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Bairro</label>
        <input type="text" name="neighborhood" value="{{ old('neighborhood', $user->neighborhood) }}"
               placeholder="Ex: Talatona"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

      <div>
        <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Referência</label>
        <input type="text" name="address_reference" value="{{ old('address_reference', $user->address_reference) }}"
               placeholder="Ex: Perto do Kero"
               style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
      </div>

    </div>

    <div style="margin-top:24px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <i class="bi bi-check-lg"></i> Salvar Perfil
      </button>
    </div>

  </form>

@endsection