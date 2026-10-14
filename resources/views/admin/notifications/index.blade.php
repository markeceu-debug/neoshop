@extends('layouts.admin')

@section('title', 'Notificações')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Notificações</h2>
      <p class="admin-page-sub">Alertas de estoque, pedidos e sistema</p>
    </div>
    <div class="admin-page-actions">
      <form method="POST" action="{{ route('admin.notifications.readAll') }}">
        @csrf @method('PUT')
        <button type="submit" class="admin-btn admin-btn-secondary">
          <i class="bi bi-check2-all"></i> Marcar todas como lidas
        </button>
      </form>
    </div>
  </div>

  <div class="admin-table-card">
    <div class="admin-table-wrap">
      <table style="width:100%; border-collapse:collapse;">
        <tbody>
          @forelse ($notifications as $n)
            @php $data = $n->data; @endphp
            <tr style="border-bottom:1px solid var(--admin-border); {{ $n->read_at ? 'opacity:.6;' : '' }}">
              <td style="padding:14px 18px; width:60px;">
                <div class="admin-notif-icon {{ ($data['severity'] ?? '') === 'critical' ? 'critical' : 'warning' }}">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
              </td>
              <td style="padding:14px 18px;">
                <div style="font-weight:800; color:var(--admin-text); font-size:13px; margin-bottom:4px;">
                  {{ $data['title'] ?? 'Alerta' }}
                </div>
                <div style="color:var(--admin-text-sec); font-size:13px;">
                  {{ $data['message'] ?? '' }}
                </div>
                <div style="color:var(--admin-text-mut); font-size:11px; text-transform:uppercase; letter-spacing:.5px; font-weight:700; margin-top:6px;">
                  {{ $n->created_at->diffForHumans() }}
                </div>
              </td>
              <td style="padding:14px 18px; text-align:right; width:180px;">
                @if (!empty($data['url']))
                  <a href="{{ $data['url'] }}" class="admin-row-btn" title="Ver">
                    <i class="bi bi-arrow-right"></i>
                  </a>
                @endif
                @if (!$n->read_at)
                  <a href="{{ route('admin.notifications.read', $n->id) }}" class="admin-row-btn success" title="Marcar como lida">
                    <i class="bi bi-check-lg"></i>
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" style="padding:48px; text-align:center; color:var(--admin-text-mut);">
                <i class="bi bi-bell-slash" style="font-size:40px; display:block; margin-bottom:12px; opacity:.5;"></i>
                Nenhuma notificação.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin-top:16px;">{{ $notifications->links() }}</div>

@endsection