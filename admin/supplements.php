<?php
 require_once '../components/head.php'; 
$isAdminPanel = true;

require_once '../models/db.php';
require_once '../models/supplement.php';
require_once '../models/brand.php';
require_once '../models/collection.php';
require_once '../components/head.php';

$conn = connectToDatabase();

// Seguridad
$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ==========================
// Cargar marcas y categorías
// ==========================
$brands = getAllBrands();
$collections = getAllCollections($conn);

$brandsById = [];
foreach ($brands as $b) $brandsById[$b['id']] = $b['name'];

$collectionsById = [];
foreach ($collections as $c) $collectionsById[$c['id']] = $c['name'];


// ==========================
// GUARDAR CREA/EDITA
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id      = $_POST['id'] ?? null;
    $name    = trim($_POST['name']);
    $desc    = trim($_POST['description']);
    $price   = trim($_POST['price']) !== '' ? floatval($_POST['price']) : null;
    $brandId = $_POST['idBrand'] !== '' ? intval($_POST['idBrand']) : null;
    $colId   = $_POST['idCollection'] !== '' ? intval($_POST['idCollection']) : null;

    // IMÁGENES (URL por línea)
    $images = [];
    if (!empty($_POST['image_urls'])) {
        foreach (preg_split('/\r\n|\n|\r/', $_POST['image_urls']) as $line) {
            $line = trim($line);
            if ($line !== "") $images[] = $line;
        }
    }

    // Subida de archivo
    if (!empty($_FILES['image_upload']['name'])) {
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['image_upload']['name']);
        $uploadDir = __DIR__ . '/../images/supplements/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $targetPath)) {
            $images[] = '/images/supplements/' . $fileName;
        }
    }

    $imagesJson = json_encode($images);

    // BENEFICIOS (lista)
    $benefits = [];
    if (!empty($_POST['benefits'])) {
        foreach (preg_split('/\r\n|\n|\r/', $_POST['benefits']) as $line) {
            $line = trim($line);
            if ($line !== "") $benefits[] = $line;
        }
    }

    $benefitsJson = json_encode($benefits);

    // Crear o actualizar
    if ($id) {
        updateSupplement($id, $name, $desc, $price, $brandId, $colId, $imagesJson, $benefitsJson);
        header("Location: supplements.php?updated=1");
        exit;
    } else {
        createSupplement($name, $desc, $price, $brandId, $colId, $imagesJson, $benefitsJson);
        header("Location: supplements.php?created=1");
        exit;
    }
}

// ==========================
// ELIMINAR
// ==========================
if (isset($_GET['delete'])) {
    deleteSupplement((int)$_GET['delete']);
    header("Location: supplements.php?deleted=1");
    exit;
}

// ==========================
// LISTAR SUPLEMENTOS (sin cache para ver cambios al instante)
// ==========================
$supplements = getAllSupplements(null, null, false);
?>
<!DOCTYPE html>
<html lang="es">


<body>

<?php require '../components/header.php'; ?>

<main class="admin-dashboard">

  <div class="admin-header">
    <h1 class="admin-title">💊 Suplementos</h1>
    <button class="btn btn-primary" onclick="openModal()">➕ Nuevo suplemento</button>
  </div>

  <!-- ALERTAS -->
  <?php if(isset($_GET['created'])): ?><div class="admin-alert success">✔ Suplemento creado.</div><?php endif; ?>
  <?php if(isset($_GET['updated'])): ?><div class="admin-alert success">✔ Suplemento actualizado.</div><?php endif; ?>
  <?php if(isset($_GET['deleted'])): ?><div class="admin-alert danger">🗑 Suplemento eliminado.</div><?php endif; ?>


  <!-- TABLA -->
  <div class="admin-card table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Marca</th>
          <th>Categoría</th>
          <th>Precio</th>
          <th>Fecha</th>
          <th style="text-align:center;">Acciones</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($supplements as $s): ?>
          <?php
            $imgs = json_decode($s['images'], true) ?: [];
            $thumb = $imgs[0] ?? null;
            $benefits = json_decode($s['benefits'], true) ?: [];
          ?>
          <tr>
            <td class="title"><?= $s['id'] ?></td>
            <td>
              <?php if ($thumb): ?>
                <img src="<?= htmlspecialchars($thumb) ?>" class="thumb">
              <?php else: ?> - <?php endif; ?>
            </td>
            <td class="title"><?= htmlspecialchars($s['name']) ?></td>
            <td class="title"><?= htmlspecialchars($brandsById[$s['idBrand']] ?? '-') ?></td>
            <td class="title"><?= htmlspecialchars($collectionsById[$s['idCollection']] ?? '-') ?></td>
            <td class="title"> <?= $s['price'] !== null ? $s['price'] . " €" : "-" ?></td>
            <td class="title"><?= htmlspecialchars($s['created_at']) ?></td>

            <td style="text-align:center;">
              <div class="row-actions" style="justify-content:center;">

                <!-- EDITAR -->
                <button class="btn-warning btn-small"
                  onclick='openModal(
                    <?= (int)$s["id"] ?>,
                    <?= json_encode($s["name"]) ?>,
                    <?= json_encode($s["description"]) ?>,
                    <?= json_encode($s["idBrand"]) ?>,
                    <?= json_encode($s["idCollection"]) ?>,
                    <?= json_encode($s["price"]) ?>,
                    <?= json_encode($imgs) ?>,
                    <?= json_encode($benefits) ?>
                  )'>
                  ✏️
                </button>

                <!-- ELIMINAR -->
                <button class="btn-danger btn-small"
                  onclick="openDeleteModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>')">
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


<!-- ========================== -->
<!-- MODAL CREAR / EDITAR -->
<!-- ========================== -->

<div class="modal-bg" id="modal-edit">
  <div class="modal">
    <button class="close-btn" onclick="closeModal()">✖</button>
    <h2 id="modal-title">Nuevo suplemento</h2>

    <form method="POST" enctype="multipart/form-data" class="admin-form">

      <input type="hidden" name="id" id="supp-id">

      <div class="form-block">
        <h3 class="block-title">Datos principales</h3>
        <div class="form-row">
          <label>Nombre</label>
          <input type="text" name="name" id="supp-name" placeholder="Ej. Proteína whey 1kg" required>
        </div>
        <div class="form-row">
          <label>Descripción</label>
          <textarea name="description" id="supp-desc" placeholder="Descripción breve del producto"></textarea>
        </div>
      </div>

      <div class="form-block">
        <h3 class="block-title">Clasificación y precio</h3>
        <div class="form-grid-2">
          <div class="form-row">
            <label>Marca</label>
            <select name="idBrand" id="supp-brand">
              <option value="">(Sin marca)</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <label>Categoría</label>
            <select name="idCollection" id="supp-collection">
              <option value="">(Sin categoría)</option>
              <?php foreach ($collections as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <label>Precio (€)</label>
            <input type="number" name="price" id="supp-price" step="0.01" min="0" placeholder="29.90">
          </div>
        </div>
      </div>

      <div class="form-block">
        <h3 class="block-title">Beneficios</h3>
        <div class="form-row">
          <label>Beneficios (uno por línea)</label>
          <textarea name="benefits" id="supp-benefits" placeholder="Mejora la recuperación&#10;Aporta proteína completa&#10;Sabor chocolate cremosa"></textarea>
          <p class="inline-hint">Cada línea se guarda como un beneficio individual.</p>
        </div>
      </div>

      <div class="form-block">
        <h3 class="block-title">Imágenes</h3>
        <div class="form-grid-2">
          <div class="form-row">
            <label>URL(s) de imagen (una por línea)</label>
            <textarea name="image_urls" id="supp-image-urls" placeholder="https://.../foto1.jpg&#10;https://.../foto2.jpg"></textarea>
          </div>
          <div class="form-row">
            <label>Subir imagen</label>
            <input type="file" name="image_upload" id="supp-image-upload" accept="image/*">
            <p class="inline-hint">La imagen subida se añade junto con las URLs.</p>
          </div>
        </div>
      </div>

      <button class="btn btn-primary" style="margin-top:14px;">💾 Guardar</button>
    </form>
  </div>
</div>



<!-- ========================== -->
<!-- MODAL ELIMINAR -->
<!-- ========================== -->

<div class="modal-bg" id="modal-delete">
  <div class="modal">
    <button class="close-btn" onclick="closeDeleteModal()">✖</button>

    <h2>⚠️ Confirmar eliminación</h2>
    <p id="delete-text"></p>

    <a id="delete-link">
      <button class="delete-confirm-btn">Sí, eliminar</button>
    </a>
  </div>
</div>



<script>
const modalEdit = document.getElementById("modal-edit");
const modalDelete = document.getElementById("modal-delete");

/* =====================================================
   MODAL EDITAR / CREAR
   ===================================================== */
function openModal(id = null, name = "", desc = "", brandId = "", colId = "", price = "", images = [], benefits = []) {
  modalEdit.style.display = "flex";

  document.getElementById("modal-title").textContent = id ? "✏️ Editar suplemento" : "➕ Nuevo suplemento";

  document.getElementById("supp-id").value = id || "";
  document.getElementById("supp-name").value = name;
  document.getElementById("supp-desc").value = desc;
  document.getElementById("supp-brand").value = brandId || "";
  document.getElementById("supp-collection").value = colId || "";
  document.getElementById("supp-price").value = price || "";

  document.getElementById("supp-benefits").value = (benefits || []).join("\n");
  document.getElementById("supp-image-urls").value = (images || []).join("\n");

  const fileInput = document.getElementById("supp-image-upload");
  if (fileInput) fileInput.value = "";

  document.getElementById("supp-name").focus();
}

function closeModal() {
  modalEdit.style.display = "none";
}

/* =====================================================
   MODAL ELIMINAR
   ===================================================== */
function openDeleteModal(id, name) {
  modalDelete.style.display = "flex";
  document.getElementById("delete-text").textContent =
    `¿Seguro que deseas eliminar el suplemento "${name}"?`;
  document.getElementById("delete-link").href = "?delete=" + id;
}

function closeDeleteModal() {
  modalDelete.style.display = "none";
}
</script>

</body>
</html>
