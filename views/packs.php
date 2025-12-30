<?php
require_once '../models/db.php';
require_once '../models/pack.php';
require_once '../components/head.php';

$conn = connectToDatabase();

$userId = $_SESSION['user']['id'] ?? null;
$userRole = $_SESSION['user']['role'] ?? null;
$isAdmin = $userRole === 'admin';

error_log("User ID: " . ($userId ?? 'null'));
error_log("Session user data: " . print_r($_SESSION['user'] ?? [], true));

$conn = connectToDatabase();
$result = $conn->query("SELECT id, visibility, link FROM packs LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        error_log("PACK_DB: ID {$row['id']} - visibility: '{$row['visibility']}', link: '{$row['link']}'");
    }
}

$publicPacks = getPublicPacks($conn);
$privatePacks = getPacksByOwner($conn, $userId);
$pendingPacks = [];

if ($isAdmin) {
    // solicitudes pendientes: por visibilidad custom o metadatos en link
    $pendingStmt = $conn->query("
        SELECT * FROM packs
        WHERE visibility = 'pending_public'
           OR (visibility = 'private' AND link LIKE '%publish_request%')
        ORDER BY updated_at DESC
    ");
    if ($pendingStmt) {
        while ($row = $pendingStmt->fetch_assoc()) {
            $pendingPacks[] = normalizePack($row);
        }
    }
}

error_log("Public packs count: " . count($publicPacks));
error_log("Private packs count: " . count($privatePacks));

foreach ($privatePacks as $pack) {
    if (!in_array($pack['visibility'], ['public', 'private'])) {
        error_log("PACK INCONSISTENTE: ID {$pack['id']} tiene visibility '{$pack['visibility']}'");
    }
}
?>
<!DOCTYPE html>
<html lang="es">


<body>

<?php require_once '../components/header.php'; ?>

<main class="packs-page">

<section class="hero">
  <h1>La Comunidad Fitness Comparte sus Fórmulas Ganadoras</h1>
  <?php if (!$userId): ?>
      <p>Descubre cómo Carlos de Madrid ganó 5kg de músculo con creatina + beta alanina, o cómo Ana de Barcelona optimizó su recuperación con glutamina + zinc. Fórmulas reales de atletas reales.</p>
      <a href="./register.php" class="cta-btn big">Ver Todas las Fórmulas →</a>
  <?php else: ?>
      <p>Comparte tu protocolo que funcionó (25g proteína + 5g creatina post-entreno) y recibe feedback de la comunidad. O guarda packs privados para uso personal.</p>
      <a href="./custom-pack.php" class="cta-btn big">Crear Mi Protocolo Personal</a>
  <?php endif; ?>
</section>

<div class="container">

    <div class="filter-tabs">
        <?php if ($userId): ?>
        <button class="filter-btn active" data-filter="public" title="Packs creados por la comunidad fitness">Packs de la Comunidad</button>
            <button class="filter-btn" data-filter="private" title="Tus creaciones personalizadas">Mis Creaciones</button>
        <?php endif; ?>
    </div>
<section class="packs" id="packs-list">
        <?php if ($isAdmin && !empty($pendingPacks)): ?>
        <div class="admin-approvals">
            <h3>Solicitudes de publicación</h3>
            <div class="cards pending-cards">
                <?php foreach ($pendingPacks as $pack): ?>
                    <?php
                        $mainImage = (!empty($pack['images']) && is_array($pack['images']))
                                    ? $pack['images'][0]
                                    : "/images/default.png";
                        $label = $pack['label'] ?: ($pack['category'] ?? 'Sin categoría');
                        $description = trim($pack['description'] ?? 'Pack pendiente de aprobación');
                        $summary = strlen($description) > 110 ? substr($description, 0, 107) . '...' : $description;
                        $itemsCount = (isset($pack['features']) && is_array($pack['features'])) ? count($pack['features']) : 0;
                        $itemLabel = $itemsCount === 1 ? 'producto' : 'productos';
                        $priceFormatted = is_numeric($pack['price']) ? number_format((float)$pack['price'], 2, ',', '.') : $pack['price'];
                    ?>
                    <div class="pack-card pack-approval" data-pack-id="<?= $pack['id'] ?>">
                        <div class="pack-card-top">
                            <span class="pack-card-badge"><?= htmlspecialchars($label) ?></span>
                            <span class="status-pending">⏳ Pendiente</span>
                        </div>
                        <div class="pack-card-image">
                            <img loading="lazy" decoding="async" width="1200" height="800" src="<?= $mainImage ?>" alt="<?= htmlspecialchars($pack['name']) ?>">
                        </div>
                        <div class="pack-card-content">
                            <h3><?= htmlspecialchars($pack['name']) ?></h3>
                            <p class="pack-card-text"><?= htmlspecialchars($summary) ?></p>
                            <div class="pack-card-meta">
                                <span class="pack-price"><?= $priceFormatted ?> €</span>
                                <?php if ($itemsCount > 0): ?>
                                    <span class="pack-items"><?= $itemsCount ?> <?= $itemLabel ?></span>
                                <?php endif; ?>
                            </div>
                            <label class="form-label" for="feedback-<?= $pack['id'] ?>">Feedback</label>
                            <textarea id="feedback-<?= $pack['id'] ?>" class="share-link-input admin-feedback" placeholder="Notas para el creador (opcional)"></textarea>
                            <div class="approval-actions">
                                <button type="button" class="approve-btn" data-approve="<?= $pack['id'] ?>">Aprobar</button>
                                <button type="button" class="reject-btn" data-reject="<?= $pack['id'] ?>">Rechazar</button>
                            </div>
                        </div>
                        <a href="./pack.php?id=<?= $pack['id'] ?>" class="pack-card-cta">Ver pack</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php foreach ($publicPacks as $pack): ?>
            <?php
                $mainImage = (!empty($pack['images']) && is_array($pack['images']))
                            ? $pack['images'][0]
                            : "/images/default.png";
                $label = $pack['label'] ?: ($pack['category'] ?? 'Sin categoría');
                $description = trim($pack['description'] ?? 'Pack diseñado por AlphaSupps');
                $summary = strlen($description) > 110 ? substr($description, 0, 107) . '...' : $description;
                $itemsCount = (isset($pack['features']) && is_array($pack['features'])) ? count($pack['features']) : 0;
                $itemLabel = $itemsCount === 1 ? 'producto' : 'productos';
                $priceFormatted = is_numeric($pack['price']) ? number_format((float)$pack['price'], 2, ',', '.') : $pack['price'];
            ?>
            <div class="pack-card pack-public">
                <div class="pack-card-top">
                    <span class="pack-card-badge"><?= htmlspecialchars($label) ?></span>
                </div>

                <div class="pack-card-image">
                    <img loading="lazy" decoding="async" width="1200" height="800" src="<?= $mainImage ?>" alt="<?= htmlspecialchars($pack['name']) ?>">
                </div>

                <div class="pack-card-content">
                    <h3><?= htmlspecialchars($pack['name']) ?></h3>
                    <p class="pack-card-text"><?= htmlspecialchars($summary) ?></p>
                    <div class="pack-card-meta">
                        <span class="pack-price"><?= $priceFormatted ?> €</span>
                        <?php if ($itemsCount > 0): ?>
                            <span class="pack-items"><?= $itemsCount ?> <?= $itemLabel ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="./pack.php?id=<?= $pack['id'] ?>" class="pack-card-cta">Ver pack</a>
            </div>
        <?php endforeach; ?>

        <?php if ($userId): ?>
            <?php foreach ($privatePacks as $pack): ?>
                <?php
                    $visibility = $pack['visibility'] ?? 'private';
                    $linkData = is_array($pack['link'] ?? null) ? $pack['link'] : null;
                    if (($linkData['publish_request'] ?? null) && $visibility === 'private') {
                        $visibility = 'pending_public';
                    }
                    if (($linkData['publish_feedback'] ?? null) && $visibility === 'private') {
                        $visibility = 'rejected_public';
                    }
                    $mainImage = (!empty($pack['images']) && is_array($pack['images']))
                                ? $pack['images'][0]
                                : "/images/default.png";
                    $hasLink = !empty($pack['link']) && is_array($pack['link']);
                    $currentVisibility = $visibility;
                    $packLink = $hasLink ? htmlspecialchars(json_encode($pack['link'])) : '';
                    $label = $pack['label'] ?: ($pack['category'] ?? 'Sin categoría');
                    $description = trim($pack['description'] ?? 'Tu pack personalizado');
                    $summary = strlen($description) > 110 ? substr($description, 0, 107) . '...' : $description;
                    $itemsCount = (isset($pack['features']) && is_array($pack['features'])) ? count($pack['features']) : 0;
                    $itemLabel = $itemsCount === 1 ? 'producto' : 'productos';
                    $priceFormatted = is_numeric($pack['price']) ? number_format((float)$pack['price'], 2, ',', '.') : $pack['price'];

                    $visibilityIcon = match($visibility) {
                        'shared_link' => '🔗',
                        'public' => '🌍',
                        'pending_public' => '⏳',
                        'rejected_public' => '❌',
                        default => '🔒',
                    };
                    $statusText = match($visibility) {
                        'shared_link' => 'Compartido con enlace',
                        'public' => 'Publicado',
                        'pending_public' => 'Pendiente de aprobación',
                        'rejected_public' => 'Rechazado por el admin',
                        default => 'Solo tú puedes verlo',
                    };
                    $statusClass = match($visibility) {
                        'shared_link' => 'status-shared',
                        'public' => 'status-public',
                        'pending_public' => 'status-pending',
                        'rejected_public' => 'status-rejected',
                        default => 'status-private',
                    };
                ?>
                <div class="pack-card pack-private" style="display: none;">
                    <div class="pack-card-top">
                        <span class="pack-card-badge"><?= htmlspecialchars($label) ?></span>
                        <button class="visibility-btn"
                            data-pack-id="<?= $pack['id'] ?>"
                            data-current-visibility="<?= $currentVisibility ?>"
                            data-pack-link="<?= $packLink ?>"
                            title="Cambiar visibilidad">
                            <?= $visibilityIcon ?>
                        </button>
                </div>

                <div class="pack-card-image">
                    <img loading="lazy" decoding="async" width="1200" height="800" src="<?= $mainImage ?>" alt="<?= htmlspecialchars($pack['name']) ?>">
                </div>

                    <div class="pack-card-content">
                        <h3><?= htmlspecialchars($pack['name']) ?></h3>
                        <p class="pack-card-text"><?= htmlspecialchars($summary) ?></p>
                        <div class="pack-card-meta">
                            <span class="pack-price">€<?= $priceFormatted ?></span>
                            <?php if ($itemsCount > 0): ?>
                                <span class="pack-items"><?= $itemsCount ?> <?= $itemLabel ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="pack-card-status <?= $statusClass ?>">
                            <?= htmlspecialchars($statusText) ?>
                        </div>
                    </div>

                    <a href="./pack.php?id=<?= $pack['id'] ?>" class="pack-card-cta">Ver / editar</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<div id="visibility-modal" class="visibility-modal">
    <div class="visibility-modal-content">
        <h3 id="modal-title">Cambiar Visibilidad</h3>
        <p id="modal-description">¿Quieres hacer este pack público o mantenerlo privado?</p>

        <div class="visibility-options">
            <button class="visibility-option public-option" data-visibility="public">
                <span>🌍</span>
                <div>
                    Público
                    <small>Visible para todos</small>
                </div>
            </button>
            <button class="visibility-option shared-link-option" data-visibility="shared_link">
                <span>🔗</span>
                <div>
                    Compartido
                    <small>Acceso solo con enlace</small>
                </div>
            </button>
            <button class="visibility-option private-option" data-visibility="private">
                <span>🔒</span>
                <div>
                    Privado
                    <small>Solo tú puedes verlo</small>
                </div>
            </button>
        </div>

        <div class="share-link-section" id="share-link-section" style="display: none;">
            <label class="form-label">Enlace para compartir</label>
            <div class="share-link-container">
                <input type="text" id="pack-link" readonly class="share-link-input">
                <button type="button" id="copy-link-btn" class="copy-btn">📋 Copiar</button>
            </div>
            <small class="share-info">Comparte este enlace con tus amigos para que puedan ver el pack</small>
        </div>

        <div class="share-link-section" id="feedback-section" style="display: none; margin-top: 12px;">
            <label class="form-label" for="visibility-feedback">Feedback / nota</label>
            <textarea id="visibility-feedback" class="share-link-input" style="height: 80px;" placeholder=""></textarea>
            <small class="share-info">El admin puede añadir feedback al aprobar o rechazar.</small>
        </div>

        <div class="modal-buttons">
            <button class="cancel-btn">Cancelar</button>
            <button class="accept-btn" id="accept-visibility-btn">Guardar cambios</button>
        </div>
    </div>
</div>

</main>
<?php require_once '../components/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btns = document.querySelectorAll('.filter-btn');
    const publicCards = document.querySelectorAll('.pack-public');
    const privateCards = document.querySelectorAll('.pack-private');
    const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
    const feedbackBox = document.getElementById('visibility-feedback');
    const approveBtns = document.querySelectorAll('[data-approve]');
    const rejectBtns = document.querySelectorAll('[data-reject]');

    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            btns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.dataset.filter;

            if (filter === 'public') {
                publicCards.forEach(c => c.style.display = "block");
                privateCards.forEach(c => c.style.display = "none");
            } else if (filter === 'private') {
                publicCards.forEach(c => c.style.display = "none");
                privateCards.forEach(c => c.style.display = "block");
            }
        });
    });

    async function handleAdminDecision(packId, visibility) {
        const card = document.querySelector(`.pack-approval[data-pack-id="${packId}"]`);
        const feedback = card?.querySelector('.admin-feedback')?.value.trim() || '';

        if (visibility === 'rejected_public' && !feedback) {
            alert('Incluye un motivo de rechazo.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'change_visibility');
        formData.append('pack_id', packId);
        formData.append('visibility', visibility);
        formData.append('feedback', feedback);

        try {
            const response = await fetch('../controllers/pack.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (!result.success) {
                alert(result.message || 'No se pudo actualizar el pack');
                return;
            }
            showPackToast(result.visibility === 'public' ? 'Aprobado y publicado' : 'Rechazado');
            if (card) card.remove();
        } catch (err) {
            console.error(err);
            alert('Error al actualizar el pack');
        }
    }

    approveBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            handleAdminDecision(btn.dataset.approve, 'public');
        });
    });

    rejectBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            handleAdminDecision(btn.dataset.reject, 'rejected_public');
        });
    });

const visibilityBtns = document.querySelectorAll('.visibility-btn');
const visibilityModal = document.getElementById('visibility-modal');
const visibilityOptions = document.querySelectorAll('.visibility-option');
const cancelBtn = document.querySelector('.cancel-btn');
const acceptBtn = document.getElementById('accept-visibility-btn');

let currentPackId = null;
let selectedVisibility = null;

function showPackToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 3200);
}

visibilityBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        document.body.classList.add('visibility-open');
        currentPackId = btn.dataset.packId;
        const currentVisibility = btn.dataset.currentVisibility;
        selectedVisibility = currentVisibility; // valor por defecto

        visibilityOptions.forEach(opt => {
            opt.classList.remove('selected');
            if (opt.dataset.visibility === currentVisibility) {
                opt.classList.add('selected');
            }
        });

        const shareSection = document.getElementById('share-link-section');

        // Inicialmente ocultar la sección de compartir
        // Solo se mostrará cuando se seleccione la opción "público" o "shared_link"
        if (currentVisibility === 'shared_link') {
            shareSection.style.display = 'block';
        } else {
            shareSection.style.display = 'none';
            document.getElementById('pack-link').value = '';
        }

        document.getElementById('modal-title').textContent = 'Cambiar Visibilidad';
        document.getElementById('modal-description').textContent = '¿Quieres hacer este pack público o mantenerlo privado?';

        const cancelBtn = document.querySelector('.cancel-btn');
        const closeBtn = document.querySelector('.close-share-modal-btn');
        if (closeBtn) closeBtn.remove();
        if (cancelBtn) cancelBtn.style.display = 'block';

        visibilityModal.classList.add('open');
    });
});

visibilityOptions.forEach(option => {
    option.addEventListener('click', () => {
        console.log('Opción clickeada:', option.dataset.visibility);

        visibilityOptions.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');

        const newVisibility = option.dataset.visibility;
        selectedVisibility = newVisibility;
        const shareSection = document.getElementById('share-link-section');
        const feedbackBox = document.getElementById('visibility-feedback');

        if (newVisibility === 'shared_link') {
            // Mostrar sección solo para compartir con enlace
            if (shareSection) {
                shareSection.style.display = 'block';

                // Generar URL de preview temporal
                const baseUrl = window.location.origin;
                const previewToken = 'preview_' + Date.now();
                const previewUrl = `${baseUrl}/views/pack.php?id=${currentPackId}&token=${previewToken}`;
                document.getElementById('pack-link').value = previewUrl;
            }

            document.getElementById('modal-title').textContent = 'Pack para compartir';
            document.getElementById('modal-description').textContent = 'Accesible solo con el enlace. Comparte este link:';
            if (isAdmin) {
                feedbackBox?.setAttribute('placeholder', 'Nota opcional para compartir');
                feedbackBox?.parentElement?.style.setProperty('display', 'block');
            } else {
                feedbackBox?.parentElement?.style.setProperty('display', 'none');
            }
        } else {
            // Ocultar sección de compartir para público o privado
            if (shareSection) {
                shareSection.style.display = 'none';
            }
            document.getElementById('pack-link').value = '';
            if (newVisibility === 'public') {
                document.getElementById('modal-title').textContent = 'Pack público';
                document.getElementById('modal-description').textContent = 'Cualquiera puede ver este pack.';
                feedbackBox?.parentElement?.style.setProperty('display', isAdmin ? 'block' : 'none');
                if (isAdmin) {
                    feedbackBox?.setAttribute('placeholder', 'Feedback para el usuario (opcional)');
                }
            } else {
                document.getElementById('modal-title').textContent = 'Cambiar Visibilidad';
                document.getElementById('modal-description').textContent = 'Elige la visibilidad del pack:';
                if (isAdmin && newVisibility === 'rejected_public') {
                    feedbackBox?.setAttribute('placeholder', 'Indica por qué fue rechazado');
                    feedbackBox?.parentElement?.style.setProperty('display', 'block');
                } else {
                    feedbackBox?.parentElement?.style.setProperty('display', 'none');
                }
            }
        }
    });
});

// BOTÓN ACEPTAR - GUARDAR CAMBIOS
acceptBtn.addEventListener('click', async () => {
    console.log('Botón aceptar clickeado');
    console.log('selectedVisibility:', selectedVisibility);
    console.log('currentPackId:', currentPackId);
    console.log('currentPackId type:', typeof currentPackId);

        if (!selectedVisibility || !currentPackId) {
            alert('Por favor selecciona una opción de visibilidad');
            return;
        }

    // Validar que selectedVisibility sea válido
    const validVisibilities = ['public', 'private', 'shared_link', 'pending_public', 'rejected_public'];
    if (!validVisibilities.includes(selectedVisibility)) {
        alert('Opción de visibilidad inválida');
        return;
    }

    // Validar que currentPackId sea un número
    if (isNaN(currentPackId) || currentPackId <= 0) {
        alert('ID de pack inválido');
        return;
    }

    try {
        console.log('Llamando a changePackVisibility con:', currentPackId, selectedVisibility);
        await changePackVisibility(currentPackId, selectedVisibility);
        visibilityModal.classList.remove('open');
        selectedVisibility = null;
        currentPackId = null;
        resetShareModal();
    } catch (error) {
        console.error('Error al guardar cambios:', error);
        alert('Error al guardar los cambios. Inténtalo de nuevo.');
    }
});

cancelBtn.addEventListener('click', () => {
    visibilityModal.classList.remove('open');
    document.body.classList.remove('visibility-open');
    currentPackId = null;
    resetShareModal();
});

visibilityModal.addEventListener('click', (e) => {
    if (e.target === visibilityModal) {
        visibilityModal.classList.remove('open');
        document.body.classList.remove('visibility-open');
        currentPackId = null;
        resetShareModal();
    }
});

function resetShareModal() {
    document.getElementById('pack-link').value = '';
    document.getElementById('share-link-section').style.display = 'none';
    document.getElementById('modal-title').textContent = 'Cambiar Visibilidad';
    document.getElementById('modal-description').textContent = '¿Quieres hacer este pack público o mantenerlo privado?';

    const cancelBtn = document.querySelector('.cancel-btn');
    const closeBtn = document.querySelector('.close-share-modal-btn');
    if (cancelBtn) cancelBtn.style.display = 'block';
    if (closeBtn) closeBtn.remove();
}

async function changePackVisibility(packId, newVisibility) {
    try {
        console.log('changePackVisibility called with:', packId, newVisibility);

        const linkInput = document.getElementById('pack-link');
        const link = linkInput ? linkInput.value.trim() : '';
        console.log('link value:', link);
        const feedback = isAdmin ? (document.getElementById('visibility-feedback')?.value.trim() || '') : '';

        const formData = new FormData();
        formData.append('action', 'change_visibility');
        formData.append('pack_id', packId);
        formData.append('visibility', newVisibility);
        formData.append('link', link);
        if (isAdmin) {
            formData.append('feedback', feedback);
        }
        if (!isAdmin && newVisibility === 'public') {
            formData.append('request_publish', '1');
            // Para usuarios, tratamos como solicitud: backend lo deja privado
            formData.set('visibility', 'public');
        }

        console.log('Enviando petición a:', '../controllers/pack.php');
        console.log('FormData contents:', Array.from(formData.entries()));

        const response = await fetch('../controllers/pack.php', {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        const result = await response.json();
        console.log('Respuesta del servidor:', result);

            if (result.success) {
                const appliedVisibility = result.visibility || newVisibility;
                const requestedPublish = !isAdmin && newVisibility === 'public' && appliedVisibility !== 'public';
                const successMessages = {
                    public: 'Pack actualizado: ahora es público',
                    private: 'Pack actualizado: ahora es privado',
                    shared_link: 'Pack actualizado: compartido con enlace'
                };
                const toastMessage = requestedPublish
                    ? 'Solicitud de publicación enviada'
                    : (successMessages[appliedVisibility] || 'Pack actualizado');

                showPackToast(toastMessage);

                const btn = document.querySelector(`.visibility-btn[data-pack-id="${packId}"]`);
                if (btn) {
                    btn.dataset.currentVisibility = appliedVisibility;
                    if (appliedVisibility === 'public') {
                        btn.textContent = '🌍';
                    } else if (appliedVisibility === 'shared_link') {
                        btn.textContent = '🔗';
                    } else {
                        btn.textContent = '🔒';
                    }
                }

                visibilityOptions.forEach(opt => {
                    opt.classList.remove('selected');
                    if (opt.dataset.visibility === appliedVisibility) {
                        opt.classList.add('selected');
                    }
                });

                const shareSection = document.getElementById('share-link-section');
                const shareLinkInput = document.getElementById('pack-link');

                if (appliedVisibility === 'shared_link') {
                    console.log('Pack compartido con enlace - mostrando sección de compartir');
                    document.getElementById('modal-title').textContent = 'Pack para compartir';
                    document.getElementById('modal-description').textContent = 'Accesible solo con el enlace. Comparte este link:';

                    if (shareSection) {
                        shareSection.style.display = 'block';
                    }

                    if (shareLinkInput && result.share_url) {
                        shareLinkInput.value = result.share_url;
                        console.log('URL actualizada con servidor:', result.share_url);
                    }
                    if (feedbackBox) {
                        feedbackBox.value = '';
                        feedbackBox.placeholder = 'Nota opcional para compartir';
                        feedbackBox.parentElement.style.display = 'block';
                    }
                } else {
                    console.log('Pack hecho público o privado - ocultando sección de enlace');
                    if (shareSection) {
                        shareSection.style.display = 'none';
                    }
                    if (shareLinkInput) {
                        shareLinkInput.value = '';
                    }
                    if (feedbackBox) {
                        feedbackBox.value = '';
                        feedbackBox.placeholder = isAdmin ? 'Feedback para el usuario (opcional)' : '';
                        if (appliedVisibility === 'rejected_public' || (!isAdmin && newVisibility === 'public') || (isAdmin && appliedVisibility === 'public')) {
                            feedbackBox.parentElement.style.display = 'block';
                        } else {
                            feedbackBox.parentElement.style.display = 'none';
                        }
                    }

                if (appliedVisibility === 'public') {
                        document.getElementById('modal-title').textContent = '¡Pack hecho público!';
                        document.getElementById('modal-description').textContent = 'Cualquiera puede ver este pack ahora';
                    } else if (requestedPublish) {
                        document.getElementById('modal-title').textContent = 'Solicitud enviada';
                        document.getElementById('modal-description').textContent = 'Revisaremos tu pack antes de publicarlo.';
                    } else {
                        document.getElementById('modal-title').textContent = 'Cambiar Visibilidad';
                        document.getElementById('modal-description').textContent = 'Elige la visibilidad del pack:';
                    }
                }

                // Cerrar modal después de 1 segundo para todas las visibilidades
                setTimeout(() => {
                    visibilityModal.classList.remove('open');
                    document.body.classList.remove('visibility-open');
                    currentPackId = null;
                    resetShareModal();
                }, 1000);

        } else {
            alert('Error al cambiar la visibilidad: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cambiar la visibilidad del pack');
    }
}

// FUNCIONALIDAD COPIAR LINK
document.getElementById('copy-link-btn').addEventListener('click', () => {
    const linkInput = document.getElementById('pack-link');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // Para móviles

    try {
        document.execCommand('copy');
        const btn = document.getElementById('copy-link-btn');
        const originalText = btn.textContent;
        btn.textContent = '✅ Copiado!';
        btn.style.background = '#4CAF50';

        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = '';
        }, 2000);
    } catch (err) {
        console.error('Error al copiar:', err);
        alert('No se pudo copiar el enlace. Cópialo manualmente.');
    }
});
}); // DOMContentLoaded
</script>
<style>
.filter-tabs {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin: 2rem 0;
}

.filter-btn {
  padding: 10px 24px;
  border-radius: 50px;
  border: 1px solid var(--accent);
  background: transparent;
  color: var(--accent);
  font-weight: 700;
  cursor: pointer;
  transition: .25s;
}

.filter-btn.active {
  background: var(--accent);
  color: #000;
  box-shadow: var(--shadow-gold);
}

/* PACK CARDS */
.pack-card {
  position: relative;
}

/* BADGE DE CATEGORÍA */
.pack-category-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: var(--accent);
  color: #000;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  z-index: 5;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* BOTÓN DE VISIBILIDAD */
.visibility-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: linear-gradient(135deg, rgba(64,64,64,0.8), rgba(128,128,128,0.9));
  border: 1px solid rgba(255,255,255,0.4);
  border-radius: 12px;
  width: 38px;
  height: 38px;
  color: white;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 15;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  backdrop-filter: blur(4px);
}

.visibility-btn:hover {
  background: linear-gradient(135deg, rgba(80,80,80,0.9), rgba(150,150,150,0.95));
  transform: translateY(-1px) scale(1.05);
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  border-color: rgba(255,255,255,0.6);
}

.visibility-btn:disabled {
  cursor: default;
  opacity: 0.7;
}

.visibility-btn:disabled:hover {
  transform: none;
  background: rgba(128,128,128,0.7);
  box-shadow: 0 2px 8px rgba(243, 210, 124, 0.3);
}

.visibility-btn:active {
  transform: translateY(0) scale(0.98);
  box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}

/* MODAL DE VISIBILIDAD */
.visibility-modal {
  position: fixed;
  inset: 0;
  display: flex;
  padding: 40px 24px 24px;
  background: rgba(0,0,0,0.75);
  z-index: 2147483600; /* sit above header/nav */
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.2s ease, visibility 0.2s ease;
}

.visibility-modal-content {
  position: relative;
  z-index: 2147483601;
  margin: 0 auto;
  background: #0f0f10;
  border-radius: 24px;
  border: 1px solid rgba(255,255,255,0.08);
  width: min(520px, calc(100% - 48px));
  max-width: 520px;
  height: fit-content;
  max-height: calc(100vh - 120px);
  overflow: auto;
  padding: 32px;
  box-shadow: 0 30px 80px rgba(0,0,0,0.65);
  text-align: left;
}

.visibility-modal.open {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}

/* Bajar el header visualmente cuando el modal está activo */
.visibility-open .main-header {
  pointer-events: none;
  filter: blur(1px);
  opacity: 0.35;
  transition: opacity 0.15s ease, filter 0.15s ease;
}

.visibility-modal h3 {
  margin: 0 0 8px;
  color: #fff;
  font-size: 1.45rem;
  font-weight: 600;
}

.visibility-modal p {
  margin: 0 0 22px;
  color: rgba(255,255,255,0.7);
  font-size: 0.95rem;
  line-height: 1.45;
}

.visibility-options {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 26px;
}

.visibility-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 18px;
  border-radius: 18px;
  border: 1px solid rgba(255,255,255,0.09);
  background: rgba(255,255,255,0.02);
  color: #fff;
  cursor: pointer;
  transition: all .2s ease;
  font-size: 1rem;
  font-weight: 600;
  text-align: left;
}

.visibility-option span {
  font-size: 1.8rem;
}

.visibility-option small {
  display: block;
  margin-top: 2px;
  font-weight: 400;
  font-size: 0.85rem;
  color: rgba(255,255,255,0.6);
}

.visibility-option:hover,
.visibility-option.selected {
  border-color: var(--accent);
  background: rgba(240,199,94,0.08);
  box-shadow: 0 16px 34px rgba(0,0,0,0.45);
  transform: translateY(-2px);
}

.modal-buttons {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

.modal-buttons button {
  flex: 1;
  padding: 14px 18px;
  border-radius: 14px;
  border: none;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all .2s ease;
}

.cancel-btn {
  background: rgba(255,255,255,0.08);
  color: #fff;
}

.cancel-btn:hover {
  background: rgba(255,255,255,0.18);
}

.accept-btn {
  background: var(--accent);
  color: #000;
  box-shadow: 0 12px 25px rgba(240,199,94,0.35);
}

.accept-btn:hover {
  background: var(--accent-light);
  transform: translateY(-1px);
}

/* SECCIÓN DE LINK DE COMPARTIR */
.share-link-section {
  margin-bottom: 24px;
  padding: 18px;
  border-radius: 18px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.03);
}
.pack-private {
  display: none;
}
.status-pending { color: #f0c75e; }
.status-rejected { color: #ff6b6b; }
.pack-approval {
  border: 1px solid rgba(255,255,255,0.08);
}
.approval-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}
.approve-btn, .reject-btn {
  flex: 1;
  padding: 10px 12px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  font-weight: 700;
}
.approve-btn { background: var(--accent); color: #000; }
.reject-btn { background: #ff6b6b; color: #fff; }

.share-link-section .form-label {
  display: block;
  color: rgba(255,255,255,0.75);
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 10px;
}

.share-link-container {
  display: flex;
  gap: 10px;
  margin-bottom: 8px;
}

.share-link-input {
  flex: 1;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(0,0,0,0.35);
  color: #fff;
  font-size: 0.95rem;
  font-family: monospace;
}

.share-link-input:focus {
  outline: none;
  border-color: var(--accent);
}

.copy-btn {
  padding: 0 18px;
  border-radius: 14px;
  background: var(--accent);
  border: none;
  color: #000;
  font-weight: 600;
  cursor: pointer;
  transition: .15s ease;
  white-space: nowrap;
}

.copy-btn:hover {
  background: var(--accent-light);
  transform: translateY(-2px);
}

.share-info {
  display: block;
  color: rgba(255,255,255,0.6);
  font-size: 0.8rem;
}

/* BOTÓN CERRAR MODAL DE COMPARTIR */
.close-share-modal-btn {
  margin-top: 15px;
  padding: 10px 20px;
  background: var(--accent);
  border: none;
  border-radius: 8px;
  color: #000;
  font-weight: 600;
  cursor: pointer;
  transition: .25s ease;
  width: 100%;
}

.close-share-modal-btn:hover {
  background: var(--accent-light);
  transform: scale(1.02);
}

/* Toast de confirmación */
.toast {
  position: fixed;
  bottom: 22px;
  left: 50%;
  transform: translateX(-50%) translateY(10px);
  padding: 14px 22px;
  background: var(--accent);
  color: #0d0d0d;
  font-weight: 700;
  border-radius: 12px;
  box-shadow: 0 12px 30px rgba(0,0,0,0.35), 0 0 12px rgba(240,199,94,0.45);
  opacity: 0;
  animation: fadeToast 0.25s forwards;
  z-index: 1100;
  letter-spacing: 0.01em;
}

.toast-success {
  background: var(--accent);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes fadeToast {
  from { opacity: 0; transform: translateX(-50%) translateY(12px); }
  to { opacity: 1; transform: translateX(-50%) translateY(0); }
}
</style>
</body>
</html>
