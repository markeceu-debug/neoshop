<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Conta - NeoShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="auth-body">

  <header class="auth-header">
    <div class="container">
      <div class="auth-header-content">
        <a href="" class="auth-logo">
          <i class="bi bi-lightning-charge-fill"></i> NeoShop
        </a>
        <span class="auth-header-text">
          Já tem conta? <a href="{{ route('login') }}">Entrar</a>
        </span>
      </div>
    </div>
  </header>

  <div class="auth-wrap">
    <aside class="auth-visual auth-visual-registro">
      <div class="auth-visual-bg"></div>
      <div class="auth-visual-content">
        <div class="auth-visual-badge">
          <i class="bi bi-gift-fill"></i> Novo por aqui?
        </div>
        <h2 class="auth-visual-title">
          Crie sua conta<br>
          e ganhe <span>10% OFF</span><br>
          na 1ª compra
        </h2>
        <p class="auth-visual-text">
          Cadastro rápido e gratuito. Aproveite benefícios exclusivos
          desde o primeiro pedido.
        </p>
      </div>
    </aside>

    <div class="auth-form-col">
      <div class="auth-card">
        <div class="auth-card-header">
          <h1 class="auth-card-title">Criar Conta</h1>
          <p class="auth-card-sub">Preencha os dados abaixo para começar</p>
        </div>

        {{-- Mensagens de erro (padrão Laravel) --}}
        @if ($errors->any())
          <div class="alert alert-danger" style="margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="auth-form" method="POST" action="{{ route('register') }}">
          @csrf

          <div class="auth-field">
            <label>Nome completo</label>
            <div class="auth-input-wrap">
              <i class="bi bi-person"></i>
              <input type="text" name="name" value="{{ old('name') }}" placeholder="Como podemos te chamar?" required autofocus>
            </div>
          </div>

          <div class="auth-field">
            <label>E-mail</label>
            <div class="auth-input-wrap">
              <i class="bi bi-envelope"></i>
              <input type="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required>
            </div>
          </div>

          <div class="auth-field">
            <label>Telefone <small style="color:var(--text-muted); font-weight:400;">(opcional)</small></label>
            <div class="auth-input-wrap">
              <i class="bi bi-telephone"></i>
              <input type="tel" name="telefone" value="{{ old('telefone') }}" placeholder="(11) 99999-9999">
            </div>
          </div>

          <div class="auth-field">
            <label>Senha</label>
            <div class="auth-input-wrap">
              <i class="bi bi-lock"></i>
              <input type="password" name="password" placeholder="Mínimo 8 caracteres" required>
            </div>
          </div>

          <div class="auth-field">
            <label>Confirmar senha</label>
            <div class="auth-input-wrap">
              <i class="bi bi-lock-fill"></i>
              <input type="password" name="password_confirmation" placeholder="Repita a senha" required>
            </div>
          </div>

          <label class="auth-checkbox auth-checkbox-block">
            <input type="checkbox" required>
            <span>
              Concordo com os
              <a href="#" class="auth-link">Termos de Uso</a>
              e a
              <a href="#" class="auth-link">Política de Privacidade</a>
            </span>
          </label>

          <button type="submit" class="auth-submit">
            <i class="bi bi-person-plus"></i> Criar Conta
          </button>

          <div class="auth-footer">
            Já tem uma conta? <a href="{{ route('login') }}" class="auth-link">Entrar</a>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>