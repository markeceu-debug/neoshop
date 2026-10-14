@extends('layouts.admin')

@section('title', 'Editar Zona de Frete')

@section('content')
  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Editar Zona de Frete</h2>
      <p class="admin-page-sub">{{ $zone->full_location }}</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.shipping-zones.index') }}" class="admin-btn admin-btn-secondary">
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

  <form method="POST" action="{{ route('admin.shipping-zones.update', $zone) }}" class="admin-card" style="padding:24px;">
    @csrf
    @method('PUT')
    @include('admin.shipping-zones._form', ['zone' => $zone])

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <i class="bi bi-check-lg"></i> Atualizar Zona
      </button>
      <a href="{{ route('admin.shipping-zones.index') }}" class="admin-btn admin-btn-secondary">Cancelar</a>
    </div>
  </form>
@endsection