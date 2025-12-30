<?php
$isAdminPanel = true;

require_once '../models/collection.php';
require_once '../components/head.php';

$conn = connectToDatabase();

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$collections = getAllCollections($conn);

// === GUARDAR (CREAR / EDITAR) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id = $_POST['id'] ?? null;
  $name = trim($_POST['name']);
  $description = trim($_POST['description']);

  if ($id) {
    updateCollection($id, $name, $description, $conn);
  } else {
    createCollection($name, $description, $conn);
  }

  header("Location: categories.php");
  exit;
}

// === ELIMINAR ===
if (isset($_GET['delete'])) {
  deleteCollection($_GET['delete'], $conn);
  header("Location: categories.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<?php require_once '../components/head.php'; ?>
<body>
<?php require_once '../components/header.php'; ?>

<main class="admin-dashboard">

  <div class="admin-header">
    <h1 class="admin-title">🧩 Categorías</h1>
    <button class="btn btn-primary" onclick="openModal()">➕ Nueva categoría</button>
  </div>

  <div class="admin-card table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th style="text-align:center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($collections as $c): ?>
        <tr>
          <td class="title"><?= $c['id'] ?></td>
          <td class="title"><?= htmlspecialchars($c['name']) ?></td>
          <td class="title"><?= htmlspecialchars($c['description']) ?></td>

          <td style="text-align:center;">
            <div class="row-actions" style="justify-content:center;">

              <!-- BOTÓN EDITAR -->
              <button class="btn-warning btn-small"
                      onclick="openModal(
                        <?= $c['id'] ?>,
                        '<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($c['description'], ENT_QUOTES) ?>'
                      )">
                ✏️
              </button>

              <!-- BOTÓN ELIMINAR (ABRE MODAL) -->
              <button class="btn-danger btn-small"
                      onclick="openDeleteModal(
                        <?= $c['id'] ?>,
                        '<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>'
                      )">
                🗑
              </button>

            </div>
          </td>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- ===================== -->
<!-- MODAL CREAR / EDITAR -->
<!-- ===================== -->

<div class="modal-bg" id="modal-edit">
  <div class="modal">
    <button class="close-btn" onclick="closeModal()">✖</button>
    <h2 id="modal-title">Nueva categoría</h2>

    <form method="POST" class="admin-form table-style">
      <input type="hidden" name="id" id="cat-id">

      <div class="form-row">
        <label>Nombre</label>
        <input type="text" name="name" id="cat-name" required>
      </div>

      <div class="form-row">
        <label>Descripción</label>
        <textarea name="description" id="cat-desc"></textarea>
      </div>

      <button class="btn" style="margin-top:10px;">💾 Guardar</button>
    </form>
  </div>
</div>


<!-- ===================== -->
<!-- MODAL ELIMINAR -->
<!-- ===================== -->

<div class="modal-bg" id="modal-delete">
  <div class="modal">
    <button class="close-btn" onclick="closeDeleteModal()">✖</button>
    <h2 style="margin-bottom:10px;">⚠️ Confirmar eliminación</h2>

    <p id="delete-text" style="margin-bottom:20px;opacity:.8;"></p>

    <a id="delete-link">
      <button class="delete-confirm-btn">Sí, eliminar</button>
    </a>
  </div>
</div>


<script>
/* === MODAL EDITAR / CREAR === */

const modalEdit = document.getElementById("modal-edit");
const modalDelete = document.getElementById("modal-delete");

function openModal(id = null, name = "", desc = "") {
  modalEdit.style.display = "flex";

  if (id) {
    document.getElementById("modal-title").textContent = "✏️ Editar categoría";
    document.getElementById("cat-id").value = id;
    document.getElementById("cat-name").value = name;
    document.getElementById("cat-desc").value = desc;
  } else {
    document.getElementById("modal-title").textContent = "➕ Nueva categoría";
    document.getElementById("cat-id").value = "";
    document.getElementById("cat-name").value = "";
    document.getElementById("cat-desc").value = "";
  }
}

function closeModal() {
  modalEdit.style.display = "none";
}

/* === MODAL ELIMINAR === */

function openDeleteModal(id, name) {
  modalDelete.style.display = "flex";
  document.getElementById("delete-text").textContent =
    "¿Seguro que deseas eliminar la categoría \"" + name + "\"?";
  document.getElementById("delete-link").href = "?delete=" + id;
}

function closeDeleteModal() {
  modalDelete.style.display = "none";
}
</script>

</body>
</html>
