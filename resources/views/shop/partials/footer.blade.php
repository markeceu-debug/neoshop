{{-- ============================================================
     FOOTER GLOBAL — NeoShop
     Usado em todas as páginas da loja.
     Uso: @include('shop.partials.footer')
     ============================================================ --}}
<footer class="neo-footer">
  <div class="container">
    <div class="row g-4">

      {{-- ============ ATENDIMENTO ============ --}}
      <div class="col-md-3 col-6">
        <h6>ATENDIMENTO</h6>
        <a href="{{ url('/ajuda') }}">Central de Ajuda</a>
        <a href="{{ url('/faq') }}">FAQ</a>
        <a href="{{ url('/contato') }}">Contato</a>
        <a href="{{ route('tracking.index') }}">Rastrear Pedido</a>
        <a href="#">Devoluções</a>
      </div>

      {{-- ============ SOBRE ============ --}}
      <div class="col-md-3 col-6">
        <h6>SOBRE</h6>
        <a href="{{ url('/sobre') }}">Sobre Nós</a>
        <a href="{{ url('/carreiras') }}">Carreiras</a>
        <a href="{{ url('/blog') }}">Blog</a>
        <a href="{{ url('/termos') }}">Termos</a>
      </div>

      {{-- ============ PAGAMENTO ============ --}}
      <div class="col-md-3 col-6">
        <h6>PAGAMENTO</h6>
        <a href="#">Cartão</a>
        <a href="#">PIX</a>
        <a href="#">Boleto</a>
      </div>

      {{-- ============ REDES SOCIAIS ============ --}}
      <div class="col-md-3 col-6">
        <h6>SIGA-NOS</h6>
        <a href="#" target="_blank" rel="noopener">
          <i class="bi bi-facebook"></i> Facebook
        </a>
        <a href="#" target="_blank" rel="noopener">
          <i class="bi bi-instagram"></i> Instagram
        </a>
        <a href="#" target="_blank" rel="noopener">
          <i class="bi bi-twitter-x"></i> Twitter
        </a>
      </div>

    </div>

    <hr>

    <p class="text-center mb-0" style="color: var(--text-muted);">
      © {{ date('Y') }} NeoShop — Todos os direitos reservados.
    </p>
  </div>
</footer>