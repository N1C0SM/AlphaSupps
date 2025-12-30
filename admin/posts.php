<?php
require_once '../components/head.php';
$isAdminPanel = true;
require_once '../models/db.php';
require_once '../models/post.php';

$conn = connectToDatabase();

$currentAdmin = $_SESSION['user'] ?? null;
if (!$currentAdmin || $currentAdmin['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// === GUARDAR / ACTUALIZAR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id    = $_POST['id'] ?? null;
  $title = trim($_POST['title']);
  $short = trim($_POST['short_description']);
  $cont  = trim($_POST['content']);
  $image = trim($_POST['image'] ?? '');
  $link  = trim($_POST['link'] ?? '');

  // Checkbox editorial (solo para crear)
  $notify = isset($_POST['notify_subscribers']) ? true : false;

  if ($id) {
      updatePost($id, $title, $short, $cont, $image, $link);
      header("Location: posts.php?updated=1");
  } else {
      addPost($title, $short, $cont, $image, $link, $notify, $conn);
      header("Location: posts.php?created=1");
  }
  exit;
}

// === ELIMINAR ===
if (isset($_GET['delete'])) {
    deletePost((int)$_GET['delete']);
    header("Location: posts.php?deleted=1");
    exit;
}

$posts = getAllPosts($conn);
?>
<!DOCTYPE html>
<html lang="es">
<body>

<?php require '../components/header.php'; ?>

<main class="admin-dashboard">

  <div class="admin-header">
    <h1 class="admin-title">📝 Posts del blog</h1>
    <button class="btn btn-primary" onclick="openModal()">➕ Nuevo post</button>
  </div>

  <?php if(isset($_GET['updated'])): ?>
    <div class="admin-alert success">✔ Post actualizado.</div>
  <?php endif; ?>

  <?php if(isset($_GET['created'])): ?>
    <div class="admin-alert success">✔ Post creado.</div>
  <?php endif; ?>

  <?php if(isset($_GET['deleted'])): ?>
    <div class="admin-alert danger">🗑 Post eliminado.</div>
  <?php endif; ?>

  <div class="admin-card table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Imagen</th>
          <th>Título</th>
          <th>Descripción corta</th>
          <th>Fecha</th>
          <th style="text-align:center;">Acciones</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($posts as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>

          <td>
            <?php if ($p['image']): ?>
              <img src="<?= htmlspecialchars($p['image']) ?>" class="thumb">
            <?php else: ?> - <?php endif; ?>
          </td>

          <td><?= htmlspecialchars($p['title']) ?></td>
          <td><?= htmlspecialchars($p['short_description']) ?></td>
          <td><?= $p['created_at'] ?></td>

          <td style="text-align:center;">
            <div class="row-actions" style="justify-content:center;">

              <button class="btn-warning btn-small"
                onclick="openModal(
                  <?= $p['id'] ?>,
                  '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>',
                  '<?= htmlspecialchars($p['short_description'], ENT_QUOTES) ?>',
                  `<?= str_replace('`', '\`', $p['content']) ?>`,
                  '<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>',
                  '<?= htmlspecialchars($p['link'], ENT_QUOTES) ?>'
                )">✏️</button>

              <button class="btn-danger btn-small"
                onclick="openDeleteModal(
                  <?= $p['id'] ?>,
                  '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>'
                )">🗑</button>

            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</main>

<!-- MODAL CREAR / EDITAR -->
<div class="modal-bg" id="modal-edit">
  <div class="modal">
    <button class="close-btn" onclick="closeModal()">✖</button>
    <h2 id="modal-title">Nuevo post</h2>

    <form method="POST" class="admin-form table-style">
      <input type="hidden" name="id" id="post-id">

      <div class="form-row">
        <label>Título</label>
        <input type="text" name="title" id="post-title" required>
      </div>

      <div class="form-row">
        <label>Descripción corta</label>
        <input type="text" name="short_description" id="post-short" required>
      </div>

      <div class="form-row">
        <label>Contenido</label>
        <textarea name="content" id="post-content" rows="6"></textarea>
      </div>

      <div class="form-row">
        <label>URL Imagen</label>
        <input type="text" name="image" id="post-image">
      </div>

      <div class="form-row">
        <label>Link externo</label>
        <input type="text" name="link" id="post-link">
      </div>

      <!-- CONTROL EDITORIAL -->
      <div class="form-row" style="margin-top:10px;">
        <label style="display:flex;gap:8px;align-items:center;">
          <input type="checkbox" name="notify_subscribers">
          Avisar a suscriptores (solo si es realmente relevante)
        </label>
      </div>

      <button class="btn" style="margin-top:15px;">💾 Guardar</button>
    </form>
  </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal-bg" id="modal-delete">
  <div class="modal">
    <button class="close-btn" onclick="closeDeleteModal()">✖</button>
    <h2>Confirmar eliminación</h2>
    <p id="delete-text" style="opacity:.8;margin-bottom:20px;"></p>
    <a id="delete-link">
      <button class="delete-confirm-btn">Eliminar</button>
    </a>
  </div>
</div>

<script>
const modalEdit = document.getElementById("modal-edit");
const modalDelete = document.getElementById("modal-delete");

function openModal(id = null, title = "", shortDesc = "", content = "", image = "", link = "") {
  modalEdit.style.display = "flex";

  const t = document.getElementById;

  if (id) {
    document.getElementById("modal-title").textContent = "Editar post";
    t("post-id").value = id;
    t("post-title").value = title;
    t("post-short").value = shortDesc;
    t("post-content").value = content;
    t("post-image").value = image;
    t("post-link").value = link;
  } else {
    document.getElementById("modal-title").textContent = "Nuevo post";
    t("post-id").value = "";
    t("post-title").value = "";
    t("post-short").value = "";
    t("post-content").value = "";
    t("post-image").value = "";
    t("post-link").value = "";
  }
}

function closeModal() {
  modalEdit.style.display = "none";
}

function openDeleteModal(id, title) {
  modalDelete.style.display = "flex";
  document.getElementById("delete-text").textContent =
    `¿Eliminar el post "${title}"?`;
  document.getElementById("delete-link").href = "?delete=" + id;
}

function closeDeleteModal() {
  modalDelete.style.display = "none";
}
</script>

</body>
</html>