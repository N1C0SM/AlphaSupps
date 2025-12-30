<?php
$isAdminPanel = true;

require_once '../models/analytics.php';
require_once '../components/head.php';

$conn = connectToDatabase();

$user = $_SESSION['user'] ?? null;

if (!$user || $user['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

/* =============================
   MÉTRICAS
   ============================= */

$totals = getTotals($conn);
$allOrders = getAllOrders($conn); // Si aún se requiere
?>
<!DOCTYPE html>
<html lang="es">
<body>

<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">

  <?php if ($totals['pendingOrders'] > 0): ?>
    <div class="admin-alert">
      🔔 Tienes <?= $totals['pendingOrders'] ?>
      <?= $totals['pendingOrders'] == 1 ? 'pedido pendiente' : 'pedidos pendientes' ?>.
    </div>
  <?php endif; ?>

  <h1 class="admin-title">📊 Panel general</h1>

  <section class="stats-grid" style="margin-top:2rem;">

    <a href="./orders.php">
      <div class="card">📦 Pedidos<br><b><?= $totals['totalOrders'] ?></b></div>
    </a>

    <a href="./orders.php">
      <div class="card">⏳ Pendientes<br><b><?= $totals['pendingOrders'] ?></b></div>
    </a>

    <a href="./orders.php">
      <div class="card">✔️ Completados<br><b><?= $totals['completedOrders'] ?></b></div>
    </a>

    <a href="./users.php">
      <div class="card">👥 Usuarios<br><b><?= $totals['totalUsers'] ?></b></div>
    </a>

    <a href="./posts.php">
      <div class="card">📝 Posts<br><b><?= $totals['totalPosts'] ?></b></div>
    </a>

    <a href="./supplements.php">
      <div class="card">💊 Suplementos<br><b><?= $totals['totalSupplements'] ?></b></div>
    </a>

    <a href="./sales.php">
      <div class="card">💶 Ventas<br><b>€<?= number_format($totals['totalSales'], 2) ?></b></div>
    </a>

  </section>

  <section class="admin-card" style="max-width:100%;margin-top:3rem;">
    <h2 class="admin-title" style="margin-bottom:10px;">📈 Resumen general</h2>
    <div id="summaryChart"></div>
  </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>

<script>
const chart = echarts.init(document.getElementById('summaryChart'));

const totals = {
  usuarios: <?= $totals['totalUsers'] ?>,
  posts: <?= $totals['totalPosts'] ?>,
  pedidos: <?= $totals['totalOrders'] ?>,
  pendientes: <?= $totals['pendingOrders'] ?>,
  completados: <?= $totals['completedOrders'] ?>,
  suplementos: <?= $totals['totalSupplements'] ?>,
  ventas: <?= $totals['totalSales'] ?>
};

chart.setOption({
  backgroundColor: '#1a1a1a',
  textStyle: { color: '#ddd' },
  animationDuration: 900,

  tooltip: {
    trigger: 'axis',
    backgroundColor: '#111',
    borderColor: '#333',
    borderWidth: 1,
    textStyle: { color: '#eee' }
  },

  xAxis: {
    type: 'category',
    data: [
      'Usuarios',
      'Posts',
      'Pedidos',
      'Pendientes',
      'Completados',
      'Suplementos',
      'Ventas (€)'
    ],
    axisLine: { lineStyle: { color: '#444' } },
    axisLabel: { color: '#ccc', rotate: 18 }
  },

  yAxis: {
    type: 'value',
    axisLine: { lineStyle: { color: '#444' } },
    axisLabel: { color: '#ccc' },
    splitLine: { lineStyle: { color: '#333' } }
  },

  series: [{
    name: 'Totales',
    type: 'bar',
    barWidth: '45%',
    itemStyle: {
      borderRadius: [6, 6, 0, 0],
      color: (params) => {
        const palette = [
          '#4c9aff',
          '#ff8b4c',
          '#37d67a',
          '#ffcc00',
          '#5fff59',
          '#9d71fa',
          '#4cd1ff'
        ];
        return palette[params.dataIndex];
      }
    },
    data: [
      totals.usuarios,
      totals.posts,
      totals.pedidos,
      totals.pendientes,
      totals.completados,
      totals.suplementos,
      totals.ventas
    ]
  }]
});

window.addEventListener('resize', () => chart.resize());
</script>

</body>
</html>
