<header class="admin-topbar">
  <button class="admin-topbar-toggle" id="adminToggle"><i class="bi bi-list"></i></button>

  <h1 class="admin-topbar-title">@yield('title', 'Dashboard')</h1>

  <nav class="admin-topbar-breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Admin</a>
    <span class="sep">/</span>
    <span class="current">@yield('title', 'Dashboard')</span>
  </nav>

  <div class="admin-topbar-actions">

    {{-- NOTIFICAÇÕES --}}
    <div class="admin-notif-wrap" id="notifWrap">
      <button class="admin-topbar-btn" id="notifBtn" title="Notificações">
        <i class="bi bi-bell"></i>
        @php $unread = auth()->user()->unreadNotifications->count(); @endphp
        @if ($unread > 0)
          <span class="admin-topbar-btn-badge" id="notifCount">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
      </button>

      <div class="admin-notif-dropdown" id="notifDropdown">
        <div class="admin-notif-header">
          <strong>Notificações</strong>
          @if ($unread > 0)
            <form method="POST" action="{{ route('admin.notifications.readAll') }}">
              @csrf @method('PUT')
              <button type="submit" class="admin-notif-mark">Marcar todas como lidas</button>
            </form>
          @endif
        </div>

        <div class="admin-notif-list">
          @forelse (auth()->user()->unreadNotifications->take(8) as $notif)
            @php $data = $notif->data; @endphp
            <a href="{{ $data['url'] ?? '#' }}" class="admin-notif-item">
              <div class="admin-notif-icon {{ ($data['severity'] ?? '') === 'critical' ? 'critical' : 'warning' }}">
                <i class="bi bi-exclamation-triangle-fill"></i>
              </div>
              <div class="admin-notif-body">
                <div class="admin-notif-title">{{ $data['title'] ?? 'Alerta' }}</div>
                <div class="admin-notif-msg">{{ Str::limit($data['message'] ?? '', 60) }}</div>
                <div class="admin-notif-time">{{ $notif->created_at->diffForHumans() }}</div>
              </div>
            </a>
          @empty
            <div class="admin-notif-empty">
              <i class="bi bi-bell-slash"></i>
              Nenhuma notificação
            </div>
          @endforelse
        </div>

        <div class="admin-notif-footer">
          <a href="{{ route('admin.notifications.index') }}">Ver todas</a>
        </div>
      </div>
    </div>

    <a href="{{ route('admin.profile.show') }}" class="admin-topbar-btn" title="Perfil">
      <i class="bi bi-person-circle"></i>
    </a>
  </div>
</header>