<?php
$isAdminPanel = true;

require_once '../models/db.php';
require_once '../models/sales.php';
require_once '../components/head.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$salesSummary = getSalesSummary();
$monthlySales = getMonthlySales();

function movingAverage(array $data, int $period = 3): array {
  $values = array_values($data);
  $avg = [];
  for ($i = 0; $i < count($values); $i++) {
    $window = array_slice($values, max(0, $i - $period + 1), $period);
    $avg[] = array_sum($window) / count($window);
  }
  return $avg;
}
$trendLine = array_combine(array_keys($monthlySales), movingAverage($monthlySales, 3));
?>
<!DOCTYPE html>
<html lang="es">
<?php require_once '../components/head.php'; ?>
<body>
<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">
  <h1 class="admin-title">📈 Evolución de ventas</h1>

  <section class="stats-grid">
    <div class="card">📦 Pedidos completados<br><b><?= $salesSummary['totalOrders'] ?></b></div>
    <div class="card">💶 Total de ventas<br><b>€<?= number_format($salesSummary['totalSales'], 2) ?></b></div>
  </section>

  <div class="admin-card" style="margin-top:2rem;">
    <div id="ventasChart" style="width: 100%;height:420px;"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
  <script>
    const salesData = <?= json_encode($monthlySales) ?>;
    const trendData = Object.values(<?= json_encode($trendLine) ?>);
    const labels = Object.keys(salesData);
    const values = Object.values(salesData);

    if (labels.length === 0) {
      document.getElementById('ventasChart').innerHTML =
        "<p style='text-align:center;color:#777;'>No hay datos de ventas todavía.</p>";
    } else {
      const maxValue = Math.max(...values, 1);
      const scaledMax = Math.max(100, maxValue * 3);

      const chart = echarts.init(document.getElementById('ventasChart'));
      chart.setOption({
        title: { text: 'Tendencia de ventas (€)', left: 'center' },
        tooltip: {
          trigger: 'axis',
          backgroundColor: 'rgba(50,50,50,0.8)',
          borderRadius: 6,
          textStyle: { color: '#fff' },
          formatter: params => {
            const v = params.find(p => p.seriesName === 'Ventas');
            const m = params.find(p => p.seriesName === 'Media móvil');
            return `
              <b>${v.axisValue}</b><br>
              Ventas: €${v.value.toFixed(2)}<br>
              Promedio: €${m.value.toFixed(2)}
            `;
          }
        },
        legend: { data: ['Ventas', 'Media móvil'], top: 30 },
        xAxis: { type: 'category', data: labels },
        yAxis: {
          type: 'value',
          min: 0,
          max: scaledMax,
          axisLabel: { formatter: v => '€' + v }
        },
        series: [
          {
            name: 'Ventas',
            type: 'line',
            data: values,
            smooth: true,
            symbol: 'circle',
            symbolSize: 8,
            lineStyle: { width: 3 },
            areaStyle: { opacity: 0.2 }
          },
          {
            name: 'Media móvil',
            type: 'line',
            data: trendData,
            smooth: true,
            symbol: 'circle',
            symbolSize: 6,
            lineStyle: { width: 2, type: 'dashed' }
          }
        ]
      });

      window.addEventListener('resize', () => chart.resize());
    }
  </script>
</main>
</body>
</html>
