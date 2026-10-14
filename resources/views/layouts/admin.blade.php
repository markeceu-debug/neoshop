<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') - NeoShop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
  @stack('styles')
</head>
<body class="admin-body">

  @include('admin.partials.sidebar')
  <div class="admin-overlay" id="adminOverlay"></div>
  @include('admin.partials.topbar', ['title' => trim($__env->yieldContent('title'))])

  <div class="admin-content">
    <div class="admin-main">
      @yield('content')
    </div>
  </div>

  {{-- TOASTS via SESSION --}}
  @if (session('success'))
    <div class="admin-toast" id="adminToast">
      <i class="bi bi-check-circle-fill"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif
  @if (session('error'))
    <div class="admin-toast danger" id="adminToast">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span>{{ session('error') }}</span>
    </div>
  @endif
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Remove o toast após 3s (JS mínimo, apenas UX)
    setTimeout(function () {
      var t = document.getElementById('adminToast');
      if (t) { t.style.transition = 'all .3s'; t.style.opacity = '0'; setTimeout(function(){ t.remove(); }, 300); }
    }, 3000);

    // Sidebar mobile
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('adminOverlay');
    var toggle  = document.getElementById('adminToggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
      });
      overlay.addEventListener('click', function () {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
      });
    }
  </script>
 <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <script>
    /* ============================================================
       SIDEBAR MOBILE
       ============================================================ */
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    const toggle  = document.getElementById('adminToggle');

    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });

    /* ============================================================
       CONFIG GLOBAL DO CHART.JS
       ============================================================ */
    Chart.defaults.color = '#8a93a6';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 16;
    Chart.defaults.plugins.legend.labels.color = '#e8ecf4';
    Chart.defaults.plugins.tooltip.backgroundColor = '#151a26';
    Chart.defaults.plugins.tooltip.borderColor = '#2a3344';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.titleColor = '#e8ecf4';
    Chart.defaults.plugins.tooltip.bodyColor = '#8a93a6';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 0;

    /* ============================================================
       1. RECEITA — Linha suave com gradiente
       ============================================================ */
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 380);
    revenueGradient.addColorStop(0, 'rgba(124,92,255,0.35)');
    revenueGradient.addColorStop(1, 'rgba(124,92,255,0)');

    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        datasets: [{
          label: 'Receita (R$)',
          data: [45000, 52000, 48000, 62000, 71000, 68000, 82000, 91000, 88000, 105000, 98000, 125000],
          borderColor: '#7c5cff',
          backgroundColor: revenueGradient,
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#7c5cff',
          pointBorderColor: '#0f131c',
          pointBorderWidth: 3,
          pointRadius: 5,
          pointHoverRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                return 'R$ ' + context.parsed.y.toLocaleString('pt-BR');
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false }
          },
          y: {
            grid: { color: '#1e2532' },
            border: { display: false },
            ticks: {
              callback: function (v) { return 'R$ ' + (v / 1000) + 'k'; }
            }
          }
        }
      }
    });

    /* ============================================================
       2. CATEGORIAS — Doughnut
       ============================================================ */
    new Chart(document.getElementById('categoryChart'), {
      type: 'doughnut',
      data: {
        labels: ['Eletrônicos', 'Moda', 'Casa', 'Games', 'Outros'],
        datasets: [{
          data: [35, 25, 18, 12, 10],
          backgroundColor: [
            '#7c5cff',
            '#00e5ff',
            '#ff2c9c',
            '#fbbf24',
            '#22c55e'
          ],
          borderColor: '#0f131c',
          borderWidth: 3,
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { padding: 16 }
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return context.label + ': ' + context.parsed + '%';
              }
            }
          }
        }
      }
    });

    /* ============================================================
       3. PEDIDOS — Barras
       ============================================================ */
    new Chart(document.getElementById('ordersChart'), {
      type: 'bar',
      data: {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        datasets: [{
          label: 'Pedidos',
          data: [4200, 4800, 4500, 5200, 5800, 6100, 6800, 7400, 7100, 8200, 7800, 8542],
          backgroundColor: 'rgba(0,229,255,0.7)',
          borderColor: '#00e5ff',
          borderWidth: 0,
          hoverBackgroundColor: '#00e5ff',
          barThickness: 20
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false }
          },
          y: {
            grid: { color: '#1e2532' },
            border: { display: false }
          }
        }
      }
    });

    /* ============================================================
       4. TRÁFEGO VS VENDAS — Linha múltipla
       ============================================================ */
    new Chart(document.getElementById('trafficChart'), {
      type: 'line',
      data: {
        labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
        datasets: [
          {
            label: 'Visitas',
            data: [12500, 14200, 13800, 16400],
            borderColor: '#7c5cff',
            backgroundColor: 'rgba(124,92,255,0.1)',
            borderWidth: 3,
            tension: 0.4,
            pointBackgroundColor: '#7c5cff',
            pointRadius: 4
          },
          {
            label: 'Vendas',
            data: [8200, 9400, 8900, 10800],
            borderColor: '#00e5ff',
            backgroundColor: 'rgba(0,229,255,0.1)',
            borderWidth: 3,
            tension: 0.4,
            pointBackgroundColor: '#00e5ff',
            pointRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            align: 'end'
          }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false }
          },
          y: {
            grid: { color: '#1e2532' },
            border: { display: false }
          }
        }
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @stack('scripts')
  <script>
 $(document).ready(function(){
  $.ajaxSetup({
    headers:{
      'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')
    }
  });
  $('body').on('click' , '.delete-Item', function (event) {
  event.preventDefault()
  let Urldelete = $(this).attr('href')

    
 Swal.fire({
  title: "Tem certeza?",
  text: "Você não poderá reverter isso!",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#1e5e2f",
  cancelButtonColor: "#d33",
  confirmButtonText: "Sim, exclua-o!"
}).then((result) => {
  if (result.isConfirmed) {
  $.ajax({
    'type' :  'DELETE',
    url:  Urldelete,

    success : function (data) {
      if(data.status == 'success') {
         Swal.fire({
            title: "Excluído!",
            text: "Seu arquivo foi excluído com sucesso!",
            icon: "success"
          });
          window.location.reload()
      }
    },
    error: function (xhr, status , error) {
      console.log(error);
      
    }
  });
 window.location.reload()


  }
  
   

    });
  

  })
 }) 
 
  // Dropdown de notificações
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('notifBtn');
    var dd  = document.getElementById('notifDropdown');
    if (!btn || !dd) return;

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      dd.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
      if (!dd.contains(e.target) && !btn.contains(e.target)) {
        dd.classList.remove('open');
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') dd.classList.remove('open');
    });
  });
</script>
</body>
</html>