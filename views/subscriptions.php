<?php
require_once '../components/head.php';
require_once '../models/order.php';

$user = $_SESSION['user'] ?? null;
$supportEmail = getenv('DEFAULT_CONTACT_EMAIL') ?: 'contact@alphasupps.com';

function parseSubscriptionItems(array $orders): array {
    $subscriptions = [];

    foreach ($orders as $order) {
        $createdAt = $order['created_at'] ?? null;
        if (!$createdAt) {
            continue;
        }
        $timestamp = strtotime($createdAt);
        $productText = (string)($order['product'] ?? '');
        if ($productText === '') {
            continue;
        }

        $items = preg_split('/\\s*,\\s*/', $productText, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            if (!preg_match('/^(.*?)\\s*\\(Suscripci[oó]n cada (\\d+) meses\\)\\s*x(\\d+)$/iu', $item, $matches)) {
                continue;
            }

            $name = trim($matches[1]);
            $interval = intval($matches[2]);
            $qty = intval($matches[3]);
            if ($name === '' || $interval <= 0 || $qty <= 0) {
                continue;
            }

            $key = strtolower($name) . '|' . $interval;
            if (!isset($subscriptions[$key]) || $timestamp > $subscriptions[$key]['timestamp']) {
                $lastDate = new DateTime($createdAt);
                $nextDate = (clone $lastDate)->modify('+' . $interval . ' months');

                $subscriptions[$key] = [
                    'name' => $name,
                    'interval' => $interval,
                    'qty' => $qty,
                    'order_id' => $order['id'] ?? null,
                    'last_order_at' => $lastDate->format('d/m/Y'),
                    'next_renewal' => $nextDate->format('d/m/Y'),
                    'timestamp' => $timestamp,
                    'status_text' => 'Activa',
                    'status_class' => ''
                ];
            }
        }
    }

    return array_values($subscriptions);
}

$statusLabels = [
    'cancel' => ['text' => 'Cancelación solicitada', 'class' => 'pending'],
    'pause' => ['text' => 'Pausada en trámite', 'class' => 'paused'],
    'change_interval' => ['text' => 'Cambio pendiente', 'class' => 'pending']
];

function getSubscriptionStatuses(int $userId): array {
    $logFile = __DIR__ . '/../cache/subscription_requests.log';
    if (!file_exists($logFile) || !is_readable($logFile)) {
        return [];
    }

    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $statuses = [];

    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (!is_array($entry)) continue;
        if (intval($entry['user_id'] ?? 0) !== $userId) continue;
        $sub = $entry['subscription'] ?? [];
        $name = strtolower(trim((string)($sub['name'] ?? '')));
        $interval = intval($sub['interval'] ?? 0);
        if ($name === '' || $interval <= 0) continue;
        $key = $name . '|' . $interval;
        $statuses[$key] = [
            'action' => $entry['action'] ?? '',
            'new_interval' => intval($entry['new_interval'] ?? 0)
        ];
    }

    return $statuses;
}

$subscriptions = [];
if ($user && !empty($user['email'])) {
    $orders = getOrdersByEmail($user['email']);
    $subscriptions = parseSubscriptionItems($orders);
    if (!empty($subscriptions) && isset($_SESSION['user']['id'])) {
        $statusMap = getSubscriptionStatuses(intval($_SESSION['user']['id']));
        foreach ($subscriptions as $idx => &$sub) {
            $key = strtolower($sub['name']) . '|' . $sub['interval'];
            if (!isset($statusMap[$key])) {
                continue;
            }

            $action = $statusMap[$key]['action'];

            // Si se pidió cancelación, no mostramos más la suscripción
            if ($action === 'cancel') {
                unset($subscriptions[$idx]);
                continue;
            }

            if ($action === 'change_interval' && !empty($statusMap[$key]['new_interval'])) {
                $newInterval = intval($statusMap[$key]['new_interval']);
                $sub['interval'] = $newInterval;
                $sub['status_text'] = 'Activa';
                $sub['status_class'] = '';

                $lastDateObj = DateTime::createFromFormat('d/m/Y', $sub['last_order_at']);
                if ($lastDateObj) {
                    $nextDate = (clone $lastDateObj)->modify('+' . $newInterval . ' months');
                    $sub['next_renewal'] = $nextDate->format('d/m/Y');
                }
            } elseif (isset($statusLabels[$action])) {
                $sub['status_text'] = $statusLabels[$action]['text'];
                $sub['status_class'] = $statusLabels[$action]['class'];
            }
        }
        unset($sub);
        // Reindexar después de posibles eliminaciones
        $subscriptions = array_values($subscriptions);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<body>
<?php require_once '../components/header.php'; ?>

<main class="subscriptions-page">
  <section class="subscriptions-hero">
    <h1>Mis suscripciones</h1>
    <p>Gestiona la frecuencia, pausa o cancela cuando quieras. Procesamos cada solicitud en menos de 48h.</p>
  </section>

  <?php if (!$user): ?>
    <section class="empty-state">
      <h2>Inicia sesión para ver tus suscripciones</h2>
      <p>Necesitas tu cuenta para ver el estado y gestionar cambios.</p>
      <a class="cta-btn" href="../views/login.php">Acceder a mi cuenta</a>
    </section>
  <?php elseif (empty($subscriptions)): ?>
    <section class="empty-state">
      <h2>No tienes suscripciones activas</h2>
      <p>Cuando compres con suscripción, aparecerá aquí para que puedas gestionarla.</p>
      <a class="cta-btn" href="../views/supplements.php">Explorar suplementos</a>
    </section>
  <?php else: ?>
    <section class="subscriptions-grid">
      <?php foreach ($subscriptions as $subscription): ?>
        <?php
          $payload = [
            'name' => $subscription['name'],
            'interval' => $subscription['interval'],
            'qty' => $subscription['qty'],
            'order_id' => $subscription['order_id'],
            'last_order_at' => $subscription['last_order_at']
          ];
          $payloadJson = htmlspecialchars(json_encode($payload), ENT_QUOTES, 'UTF-8');
        ?>
        <div class="subscription-card" data-subscription="<?= $payloadJson ?>">
          <div class="subscription-header">
            <div class="subscription-title"><?= htmlspecialchars($subscription['name']) ?></div>
            <span class="status-pill <?= htmlspecialchars($subscription['status_class'] ?? '') ?>">
              <?= htmlspecialchars($subscription['status_text'] ?? 'Activa') ?>
            </span>
          </div>

          <div class="subscription-meta">
            <div><span class="meta-strong">Frecuencia:</span> <span data-frequency><?= $subscription['interval'] ?></span> meses</div>
            <div><span class="meta-strong">Cantidad:</span> <?= $subscription['qty'] ?></div>
            <div><span class="meta-strong">Último pedido:</span> #<?= htmlspecialchars((string)$subscription['order_id']) ?> · <?= $subscription['last_order_at'] ?></div>
            <div><span class="meta-strong">Próximo envío estimado:</span> <?= $subscription['next_renewal'] ?></div>
          </div>

          <div class="subscription-actions">
            <div class="action-row">
              <label>Frecuencia</label>
              <select class="action-interval">
                <option value="1" <?= $subscription['interval'] === 1 ? 'selected' : '' ?>>Cada 1 mes</option>
                <option value="2" <?= $subscription['interval'] === 2 ? 'selected' : '' ?>>Cada 2 meses</option>
                <option value="3" <?= $subscription['interval'] === 3 ? 'selected' : '' ?>>Cada 3 meses</option>
              </select>
              <button class="action-btn" data-action="change_interval">Actualizar frecuencia</button>
            </div>

            <div class="action-row">
              <button class="action-btn ghost" data-action="pause">Pausar 1 ciclo</button>
              <button class="action-btn danger" data-action="cancel">Cancelar suscripción</button>
            </div>

            <label class="note-label">Nota opcional</label>
            <textarea class="subscription-note" placeholder="Ej: pausar por vacaciones o ajustar cantidades."></textarea>
            <div class="request-feedback" aria-live="polite"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </section>

    <div class="subscription-help">
      ¿Necesitas algo más rápido? Escríbenos a
      <a href="mailto:<?= htmlspecialchars($supportEmail) ?>"><?= htmlspecialchars($supportEmail) ?></a>
      y lo gestionamos manualmente en el mismo día.
    </div>
  <?php endif; ?>
</main>

<script>
function localToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  toast.setAttribute('role', 'status');
  toast.setAttribute('aria-live', 'polite');
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3200);
}

function showRequestFeedback(target, message, type = 'success') {
  if (!target) {
    localToast(message, type);
    return;
  }
  target.classList.remove('success', 'error');
  target.classList.add(type);
  target.textContent = message;
}

document.querySelectorAll('.subscription-card').forEach(card => {
  const raw = card.dataset.subscription;
  const payload = raw ? JSON.parse(raw) : null;
  if (!payload) return;

  const feedback = card.querySelector('.request-feedback');
  const noteField = card.querySelector('.subscription-note');
  const intervalSelect = card.querySelector('.action-interval');
  const frequencyEl = card.querySelector('[data-frequency]');
  const statusPill = card.querySelector('.status-pill');

  card.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const action = btn.dataset.action;
      const note = noteField ? noteField.value.trim() : '';
      const newInterval = intervalSelect ? parseInt(intervalSelect.value, 10) : null;

      if (action === 'change_interval' && (!newInterval || newInterval < 1 || newInterval > 3)) {
        showRequestFeedback(feedback, 'Selecciona una frecuencia válida.', 'error');
        return;
      }

      btn.disabled = true;
      showRequestFeedback(feedback, 'Enviando solicitud…');

      try {
        const res = await fetch('../api/subscription.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify({
            action,
            subscription: payload,
            new_interval: newInterval,
            note
          })
        });

        const data = await res.json();
        if (!data.success) {
          throw new Error(data.message || 'No se pudo guardar la solicitud.');
        }

        if (action === 'change_interval' && frequencyEl) {
          frequencyEl.textContent = newInterval;
        }

        if (action === 'cancel') {
          card.querySelectorAll('[data-action]').forEach(b => b.disabled = true);
          if (statusPill) {
            statusPill.textContent = 'Cancelación solicitada';
            statusPill.classList.add('pending');
          }
        } else if (action === 'pause') {
          if (statusPill) {
            statusPill.textContent = 'Pausada 1 ciclo';
            statusPill.classList.add('paused');
          }
        }

        showRequestFeedback(feedback, data.message || 'Solicitud enviada.', 'success');
        localToast(data.message || 'Solicitud enviada.', 'success');
      } catch (err) {
        showRequestFeedback(feedback, err.message || 'Error al enviar la solicitud.', 'error');
      } finally {
        btn.disabled = false;
      }
    });
  });
});
</script>

<?php require_once '../components/footer.php'; ?>
</body>
</html>
