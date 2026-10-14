@extends('layouts.admin')

@section('title', 'Meu Perfil')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Meu Perfil</h2>
      <p class="admin-page-sub">Informações da sua conta</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.profile.edit') }}" class="admin-btn admin-btn-primary">
        <i class="bi bi-pencil"></i> Editar Perfil
      </a>
    </div>
  </div>

  <div class="admin-charts-grid">

    {{-- COLUNA ESQUERDA: DADOS --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-person-badge"></i> Dados da Conta</h3>
      </div>
      <div class="admin-card-body">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div>
            <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Nome</div>
            <div style="margin-top:6px; color:var(--admin-text); font-weight:700;">{{ $user->name }}</div>
          </div>

          <div>
            <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">E-mail</div>
            <div style="margin-top:6px; color:var(--admin-text); font-weight:700;">{{ $user->email }}</div>
          </div>

          <div>
            <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">phone</div>
            <div style="margin-top:6px; color:var(--admin-text); font-weight:700;">{{ $user->phone ?: '—' }}</div>
          </div>

          <div>
            <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Função</div>
            <div style="margin-top:6px;">
              <span class="admin-badge admin-badge-purple">
                <i class="bi bi-shield-fill"></i> {{ ucfirst($user->role) }}
              </span>
            </div>
          </div>

          <div>
            <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">Cadastrado em</div>
            <div style="margin-top:6px; color:var(--admin-text); font-weight:700;">{{ $user->created_at->format('d/m/Y H:i') }}</div>
          </div>

          <div>
            <div style="font-size:11px; color:var(--admin-text-mut); text-transform:uppercase; letter-spacing:1px; font-weight:800;">E-mail verificado</div>
            <div style="margin-top:6px;">
              @if ($user->email_verified_at)
                <span class="admin-badge admin-badge-success"><i class="bi bi-check-circle-fill"></i> Verificado</span>
              @else
                <span class="admin-badge admin-badge-warning"><i class="bi bi-exclamation-triangle-fill"></i> Pendente</span>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- COLUNA DIREITA: AVATAR --}}
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="admin-card-title"><i class="bi bi-image"></i> Foto de Perfil</h3>
      </div>
      <div class="admin-card-body" style="text-align:center;">

        <div style="
          width:160px; height:160px; margin:0 auto;
          border:3px solid var(--admin-accent);
          background-image:url('{{ $user->avatar_url }}');
          background-size:cover; background-position:center;
          box-shadow:0 0 32px var(--admin-accent-glow);
        "></div>

        <h3 style="margin:20px 0 6px; font-size:18px; font-weight:900; color:var(--admin-text);">
          {{ $user->name }}
        </h3>
        <p style="margin:0 0 20px; color:var(--admin-text-mut); font-size:13px;">
          {{ $user->email }}
        </p>

        <a href="{{ route('admin.profile.edit') }}" class="admin-btn admin-btn-secondary" style="width:100%; justify-content:center;">
          <i class="bi bi-upload"></i> Trocar Avatar
        </a>

        @if ($user->avatar)
          <form method="POST" action="{{ route('admin.profile.avatar.destroy') }}" onsubmit="return confirm('Remover o avatar atual?')" style="margin-top:8px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-danger" style="width:100%; justify-content:center;">
              <i class="bi bi-trash"></i> Remover Avatar
            </button>
          </form>
        @endif

      </div>
    </div>

  </div>

@endsection