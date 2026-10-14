@extends('layouts.admin')

@section('title', 'Editar Perfil')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Editar Perfil</h2>
      <p class="admin-page-sub">Atualize seus dados e senha</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.profile.show') }}" class="admin-btn admin-btn-secondary">
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

  <div class="admin-charts-grid">

    {{-- FORM: DADOS BÁSICOS --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-person"></i> Dados Básicos</h3>
      </div>
      <div class="admin-card-body">

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div style="display:grid; gap:20px;">

            {{-- Avatar preview + input --}}
            <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
              <div style="
                width:96px; height:96px; flex-shrink:0;
                border:2px solid var(--admin-accent);
                background-image:url('{{ $user->avatar_url }}');
                background-size:cover; background-position:center;
              "></div>

              <div style="flex:1; min-width:200px;">
                <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">
                  Novo Avatar (JPG/PNG · máx 2MB)
                </label>
                <input type="file" name="avatar" accept="image/*"
                       style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
              </div>
            </div>

            {{-- Nome --}}
            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome Completo *</label>
              <input type="text" name="name" required
                     value="{{ old('name', $user->name) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            {{-- E-mail --}}
            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">E-mail *</label>
              <input type="email" name="email" required
                     value="{{ old('email', $user->email) }}"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            {{-- phone --}}
            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">phone</label>
              <input type="tel" name="phone"
                     value="{{ old('phone', $user->phone) }}"
                     placeholder="(11) 99999-9999"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" style="justify-content:center;">
              <i class="bi bi-check-lg"></i> Salvar Alterações
            </button>
          </div>
        </form>

      </div>
    </div>

    {{-- FORM: SENHA --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-shield-lock"></i> Alterar Senha</h3>
      </div>
      <div class="admin-card-body">

        <form method="POST" action="{{ route('admin.profile.password') }}">
          @csrf
          @method('PUT')

          <div style="display:grid; gap:20px;">

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Senha Atual *</label>
              <input type="password" name="current_password" required
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nova Senha *</label>
              <input type="password" name="password" required minlength="8"
                     placeholder="Mínimo 8 caracteres"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <div>
              <label style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Confirmar Nova Senha *</label>
              <input type="password" name="password_confirmation" required minlength="8"
                     style="width:100%; margin-top:8px; padding:12px; background:var(--admin-bg); border:1px solid var(--admin-border-strong); color:var(--admin-text); font-family:inherit;">
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" style="justify-content:center;">
              <i class="bi bi-shield-check"></i> Atualizar Senha
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>

@endsection