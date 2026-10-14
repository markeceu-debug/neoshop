@extends('layouts.admin')

@section('title', 'Editar Promoção')

@section('content')
  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Editar Promoção</h2>
      <p class="admin-page-sub">{{ $promotion->name }}</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.promotions.index') }}" class="admin-btn admin-btn-secondary">
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

  <form method="POST" action="{{ route('admin.promotions.update', $promotion) }}" class="admin-card" style="padding:24px;">
    @csrf
    @method('PUT')
    @include('admin.promotions._form', ['promotion' => $promotion])

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <i class="bi bi-check-lg"></i> Atualizar Promoção
      </button>
      <a href="{{ route('admin.promotions.index') }}" class="admin-btn admin-btn-secondary">Cancelar</a>
    </div>
  </form>
@endsection<