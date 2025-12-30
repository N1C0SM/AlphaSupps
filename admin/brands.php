<?php
$isAdminPanel = true;

require_once '../models/brand.php';
require_once '../components/head.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
  header('Location: ../login.php');
  exit;
}

$brands = getAllBrands();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id = $_POST['id'] ?? null;
  $name = trim($_POST['name']);

  if ($id) {
    updateBrand($id, $name);
  } else {
    addBrand($name);
  }

  header("Location: brands.php");
  exit;
}

// === ELIMINAR ===
if (isset($_GET['delete'])) {
  deleteBrand($_GET['delete']);
  header("Location: brands.php");
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
    <h1 class="admin-title">🏭 Marcas</h1>
    <button class="btn btn-primary" onclick="openModal()">➕ Nueva marca</button>
  </div>

  <div class="admin-card table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th style="text-align:center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($brands as $b): ?>
        <tr>
          <td class="title"><?= $b['id'] ?></td>
          <td class="title"><?= htmlspecialchars($b['name']) ?></td>

          <td style="text-align:center;">
            <div class="row-actions" style="justify-content:center;">

              <!-- BOTÓN EDITAR -->
              <button class="btn-warning btn-small"
                      onclick="openModal(
                        <?= $b['id'] ?>,
                        '<?= htmlspecialchars($b['name'], ENT_QUOTES) ?>'
                      )">
                ✏️
              </button>

              <!-- BOTÓN ELIMINAR (ABRE MODAL) -->
              <button class="btn-danger btn-small"
                      onclick="openDeleteModal(
                        <?= $b['id'] ?>,
                        '<?= htmlspecialchars($b['name'], ENT_QUOTES) ?>'
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
    <h2 id="modal-title">Nueva marca</h2>

    <form method="POST">

      <input type="hidden" name="id" id="brand-id">

      <label>Nombre</label>
      <input type="text" name="name" id="brand-name" required>

      <button class="btn btn-primary" style="margin-top:18px;">💾 Guardar</button>
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

function openModal(id = null, name = "") {
  modalEdit.style.display = "flex";

  if (id) {
    document.getElementById("modal-title").textContent = "✏️ Editar marca";
    document.getElementById("brand-id").value = id;
    document.getElementById("brand-name").value = name;
  } else {
    document.getElementById("modal-title").textContent = "➕ Nueva marca";
    document.getElementById("brand-id").value = "";
    document.getElementById("brand-name").value = "";
  }
}

function closeModal() {
  modalEdit.style.display = "none";
}

/* === MODAL ELIMINAR === */

function openDeleteModal(id, name) {
  modalDelete.style.display = "flex";
  document.getElementById("delete-text").textContent =
    `¿Seguro que deseas eliminar la marca "${name}"?`;
  document.getElementById("delete-link").href = "?delete=" + id;
}

function closeDeleteModal() {
  modalDelete.style.display = "none";
}
</script>

</body>
</html>
