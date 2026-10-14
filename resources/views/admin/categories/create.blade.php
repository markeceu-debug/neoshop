@extends('layouts.admin')

@section('title', 'Nova Categoria')

@section('content')

  <div class="admin-page-header">
    <div>
      <h2 class="admin-page-title">Nova Categoria</h2>
      <p class="admin-page-sub">Preencha os dados da categoria</p>
    </div>
    <div class="admin-page-actions">
      <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">
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

  <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="admin-card" style="padding:24px;">
    @csrf
    @include('admin.categories._form', ['category' => null])

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <i class="bi bi-check-lg"></i> Salvar Categoria
      </button>
      <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">Cancelar</a>
    </div>
  </form>

@endsection