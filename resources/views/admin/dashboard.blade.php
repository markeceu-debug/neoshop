@extends('layouts.admin')

@section('content')

 <!-- HEADER -->
      <div class="admin-page-header">
        <div>
          <h2 class="admin-page-title">Visão Geral</h2>
          <p class="admin-page-sub">Bem-vindo de volta! Aqui está o resumo do dia.</p>
        </div>
        <div class="admin-page-actions">
          <button class="admin-btn admin-btn-secondary">
            <i class="bi bi-calendar3"></i> Últimos 30 dias
          </button>
          <button class="admin-btn admin-btn-primary">
            <i class="bi bi-download"></i> Exportar
          </button>
        </div>
      </div>

      <!-- STATS -->
      <div class="admin-stats-grid">

        <div class="admin-stat stat-purple">
          <div class="admin-stat-head">
            <span class="admin-stat-label">Receita Total</span>
            <div class="admin-stat-icon"><i class="bi bi-currency-dollar"></i></div>
          </div>
          <div class="admin-stat-value">R$ 1.245.890</div>
          <div class="admin-stat-trend up">
            <i class="bi bi-arrow-up-right"></i> +18.2%
            <span>vs mês passado</span>
          </div>
        </div>

        <div class="admin-stat stat-cyan">
          <div class="admin-stat-head">
            <span class="admin-stat-label">Pedidos</span>
            <div class="admin-stat-icon"><i class="bi bi-cart-check"></i></div>
          </div>
          <div class="admin-stat-value">8.542</div>
          <div class="admin-stat-trend up">
            <i class="bi bi-arrow-up-right"></i> +12.5%
            <span>vs mês passado</span>
          </div>
        </div>

        <div class="admin-stat stat-green">
          <div class="admin-stat-head">
            <span class="admin-stat-label">Clientes</span>
            <div class="admin-stat-icon"><i class="bi bi-people"></i></div>
          </div>
          <div class="admin-stat-value">15.234</div>
          <div class="admin-stat-trend up">
            <i class="bi bi-arrow-up-right"></i> +8.3%
            <span>vs mês passado</span>
          </div>
        </div>

        <div class="admin-stat stat-orange">
          <div class="admin-stat-head">
            <span class="admin-stat-label">Ticket Médio</span>
            <div class="admin-stat-icon"><i class="bi bi-graph-up"></i></div>
          </div>
          <div class="admin-stat-value">R$ 145,80</div>
          <div class="admin-stat-trend down">
            <i class="bi bi-arrow-down-right"></i> -2.1%
            <span>vs mês passado</span>
          </div>
        </div>

      </div>

      <!-- GRÁFICOS -->
      <div class="admin-charts-grid">

        <!-- GRÁFICO PRINCIPAL -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="bi bi-graph-up-arrow"></i> Receita nos Últimos 12 Meses</h3>
            <div class="admin-card-actions">
              <select class="admin-card-select">
                <option>Receita</option>
                <option>Pedidos</option>
                <option>Clientes</option>
              </select>
            </div>
          </div>
          <div class="admin-card-body">
            <div class="chart-canvas chart-canvas-tall">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>
        </div>

        <!-- GRÁFICO ROSCA -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="bi bi-pie-chart-fill"></i> Vendas por Categoria</h3>
          </div>
          <div class="admin-card-body">
            <div class="chart-canvas chart-canvas-tall">
              <canvas id="categoryChart"></canvas>
            </div>
          </div>
        </div>

      </div>

      <div class="admin-charts-grid">

        <!-- BARRAS -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="bi bi-bar-chart-fill"></i> Pedidos por Mês</h3>
          </div>
          <div class="admin-card-body">
            <div class="chart-canvas">
              <canvas id="ordersChart"></canvas>
            </div>
          </div>
        </div>

        <!-- LINHA MÚLTIPLA -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title"><i class="bi bi-activity"></i> Tráfego vs Vendas</h3>
          </div>
          <div class="admin-card-body">
            <div class="chart-canvas">
              <canvas id="trafficChart"></canvas>
            </div>
          </div>
        </div>

      </div>
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
@endsection