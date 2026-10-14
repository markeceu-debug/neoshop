<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar - NeoShop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="auth-body">

  {{-- ============ HEADER SIMPLES ============ --}}
  <header class="auth-header">
    <div class="container">
      <div class="auth-header-content">
        <a href="{{ url('/') }}" class="auth-logo">
          <i class="bi bi-lightning-charge-fill"></i> NeoShop
        </a>
        <span class="auth-header-text">
          Ainda não tem conta?
          <a href="{{ route('register') }}">Cadastre-se</a>
        </span>
      </div>
    </div>
  </header>

  {{-- ============ LOGIN ============ --}}
  <div class="auth-wrap">

    {{-- COLUNA ESQUERDA: BRANDING --}}
    <aside class="auth-visual">
      <div class="auth-visual-bg"></div>
      <div class="auth-visual-content">
        <div class="auth-visual-badge">
          <i class="bi bi-stars"></i> Bem-vindo de volta
        </div>
        <h2 class="auth-visual-title">
          Compre com<br>
          <span>segurança</span><br>
          e agilidade
        </h2>
        <p class="auth-visual-text">
          Acesse sua conta e continue aproveitando as melhores ofertas,
          rastreamento em tempo real e cashback exclusivo.
        </p>

        <div class="auth-visual-features">
          <div class="auth-visual-feature">
            <i class="bi bi-shield-check"></i>
            <div>
              <strong>Compra Garantida</strong>
              <span>Reembolso em 7 dias</span>
            </div>
          </div>
          <div class="auth-visual-feature">
            <i class="bi bi-truck"></i>
            <div>
              <strong>Frete Grátis</strong>
              <span>Acima de R$ 50</span>
            </div>
          </div>
          <div class="auth-visual-feature">
            <i class="bi bi-coin"></i>
            <div>
              <strong>Cashback 20%</strong>
              <span>Em compras selecionadas</span>
            </div>
          </div>
        </div>
      </div>
    </aside>

    {{-- COLUNA DIREITA: FORMULÁRIO --}}
    <div class="auth-form-col">
      <div class="auth-card">

        <div class="auth-card-header">
          <h1 class="auth-card-title">Entrar</h1>
          <p class="auth-card-sub">Digite seus dados para acessar sua conta</p>
        </div>

        {{-- Sessão status (ex: link reenviado) --}}
        @if (session('status'))
          <div class="admin-toast" style="position:static; margin-bottom:20px;">
            <i class="bi bi-info-circle-fill"></i>
            <span>{{ session('status') }}</span>
          </div>
        @endif

        {{-- Erros de validação --}}
        @if ($errors->any())
          <div style="background:rgba(239,68,68,0.1); border-left:3px solid #ef4444; padding:14px 18px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px; color:#ef4444; font-size:13px;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="auth-form" method="POST" action="{{ route('login') }}">
          @csrf

          {{-- E-mail --}}
          <div class="auth-field">
            <label>E-mail</label>
            <div class="auth-input-wrap">
              <i class="bi bi-envelope"></i>
              <input type="email"
                     name="email"
                     value="{{ old('email') }}"
                     placeholder="seu@email.com"
                     required
                     autofocus>
            </div>
          </div>

          {{-- Senha --}}
          <div class="auth-field">
            <label>Senha</label>
            <div class="auth-input-wrap">
              <i class="bi bi-lock"></i>
              <input type="password"
                     name="password"
                     id="loginPass"
                     placeholder="Digite sua senha"
                     required>
              <button type="button"
                      class="auth-input-toggle"
                      onclick="togglePass('loginPass', this)">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          {{-- Extras --}}
          <div class="auth-extras">
            <label class="auth-checkbox">
              <input type="checkbox" name="remember">
              <span>Lembrar de mim</span>
            </label>
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="auth-link">Esqueci minha senha</a>
            @endif
          </div>

          {{-- Botão --}}
          <button type="submit" class="auth-submit">
            <i class="bi bi-box-arrow-in-right"></i> Entrar
          </button>

          {{-- Divisor --}}
          <div class="auth-divider">
            <span>ou entre com</span>
          </div>

          {{-- Social --}}
          <div class="auth-social">
            <button type="button" class="auth-social-btn auth-social-google">
              <i class="bi bi-google"></i> Google
            </button>
            <button type="button" class="auth-social-btn auth-social-facebook">
              <i class="bi bi-facebook"></i> Facebook
            </button>
          </div>

          {{-- Cadastro --}}
          <div class="auth-footer">
            Não tem uma conta?
            <a href="{{ route('register') }}" class="auth-link">Criar agora</a>
          </div>

        </form>
      </div>
    </div>
  </div>

  <script>
    function togglePass(id, btn) {
      const input = document.getElementById(id);
      const icon  = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
      }
    }
  </script>
</body>
</html>