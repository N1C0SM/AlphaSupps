<?php
session_start();
require_once '../models/user.php';
require_once '../models/order.php';
require_once '../modules/email-system.php';

/**
 * Punto de entrada del controlador.
 */
$action = $_POST['action'] ?? $_GET['action'] ?? null;
if (!$action) exit("No se especificó ninguna acción.");

switch ($action) {
    case 'login': handleLogin(); break;
    case 'register': handleRegister(); break;
    case 'logout': handleLogout(); break;
    case 'update_role': handleUpdateRole(); break;
    case 'delete_user': handleDeleteUser(); break;
    case 'prelaunch': handleRegisterPrelaunch(); break;
    case 'newsletter': handleSuscribe(); break;
    default: exit("Acción no válida.");
}

/* ===========================================================
   LOGIN
   =========================================================== */
function handleLogin(): void {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password)
        redirect('../views/login.php?error=campos_vacios');

    $result = loginUser($email, $password);

    if ($result['success']) {
        $user = $_SESSION['user'];
        $url = ($user['role'] === 'admin')
            ? '../admin/dashboard.php'
            : '/';

        redirect($url);
    } else {
        redirect('../views/login.php?error=' . urlencode($result['message']));
    }
}

/* ===========================================================
   REGISTRO
   =========================================================== */
function handleRegister(): void {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirmPassword'] ?? '');

    if (!$name || !$email || !$pass || !$confirm)
        redirect('../views/register.php?error=campos_vacios');

    $result = registerUser($name, $email, $pass, $confirm);

    $url = $result['success']
        ? "../views/login.php?success=" . urlencode($result['message'])
        : "../views/register.php?error=" . urlencode($result['message']);

    redirect($url);
}

/* ===========================================================
   LOGOUT
   =========================================================== */
function handleLogout(): void {
    logoutUser();
    redirect('/');
}

/* ===========================================================
   ACTUALIZAR ROL
   =========================================================== */
function handleUpdateRole(): void {
    checkAdmin();
    $id = intval($_POST['id'] ?? 0);
    $role = trim($_POST['role'] ?? '');

    if ($id <= 0 || !$role) exit("Datos inválidos.");

    $ok = updateUserRole($id, $role);
    redirect("../admin/users.php?updated=" . ($ok ? 1 : 0));
}

/* ===========================================================
   ELIMINAR USUARIO
   =========================================================== */
function handleDeleteUser(): void {
    checkAdmin();
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) exit("ID inválido.");

    $ok = deleteUser($id);
    redirect("../admin/users.php?deleted=" . ($ok ? 1 : 0));
}

/* ===========================================================
   NEWSLETTER (FOOTER) → Responde TEXTO
   =========================================================== */
function handleSuscribe(): void {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        echo "⚠️ Debes ingresar un correo válido.";
        exit;
    }

    $result = guardarSuscribers($email);

    echo $result["success"]
        ? "✅ ¡Gracias por suscribirte!"
        : "❌ " . $result["message"];

    exit;
}

/* ===========================================================
   PRELAUNCH (LANDING) → Responde JSON
   =========================================================== */
   function handleRegisterPrelaunch(): void {
    header('Content-Type: application/json');

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plan  = trim($_POST['plan'] ?? '');
    $freq  = trim($_POST['frequency'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$name || !$email) {
        echo json_encode([
            "success" => false,
            "message" => "Completa todos los campos."
        ]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "success" => false,
            "message" => " El correo electrónico no es válido."
        ]);
        return;
    }

    // Guardar plan/frecuencia/notas junto al nombre para trazabilidad básica
    $nameForDb = $name;
    $extras = [];
    if ($plan !== '') $extras[] = "Plan: $plan";
    if ($freq !== '') $extras[] = "Frecuencia: $freq";
    if ($notes !== '') $extras[] = "Notas: $notes";
    if (!empty($extras)) {
        $nameForDb .= ' | ' . implode(' | ', $extras);
    }
    // Evitar cadenas excesivamente largas
    $nameForDb = mb_substr($nameForDb, 0, 240);

    $dbResult = guardarRegistroPrelanzamiento($nameForDb, $email);

    // Intentar enviar confirmación sin afectar al resultado principal
    $confirmationSent = false;
    $adminNoticeSent = false;

    if ($dbResult["success"]) {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeNotes = htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');

        $planSafe = htmlspecialchars($plan ?: 'Sin especificar', ENT_QUOTES, 'UTF-8');
        $freqSafe = htmlspecialchars($freq ?: 'Sin especificar', ENT_QUOTES, 'UTF-8');

        $subject = "Solicitud AlphaBox recibida";
        $htmlUser = "
            <p>Hola {$safeName},</p>
            <p>Hemos recibido tu interés en AlphaBox. Aún no activamos nada: te escribiremos para confirmar disponibilidad y detalles.</p>
            <div style='margin:14px 0; padding:12px; border:1px solid #eee; border-radius:10px;'>
                <p style='margin:0;'><strong>Plan:</strong> {$planSafe}</p>
                <p style='margin:0;'><strong>Frecuencia:</strong> {$freqSafe}</p>
                " . ($notes ? "<p style='margin:0;'><strong>Notas:</strong> {$safeNotes}</p>" : "") . "
            </div>
            <p>Responderemos por email con los siguientes pasos. Gracias por la paciencia.</p>
        ";
        $confirmationSent = sendMail($email, $subject, $htmlUser);

        $adminEmail = DEFAULT_CONTACT_EMAIL ?: EMISOR_EMAIL;
        $htmlAdmin = "
            <p>Nueva solicitud de AlphaBox:</p>
            <ul>
                <li><strong>Nombre:</strong> " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</li>
                <li><strong>Email:</strong> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</li>
                <li><strong>Plan:</strong> {$planSafe}</li>
                <li><strong>Frecuencia:</strong> {$freqSafe}</li>
                " . ($notes ? "<li><strong>Notas:</strong> {$safeNotes}</li>" : "") . "
            </ul>
        ";
        $adminNoticeSent = sendMail($adminEmail, "Nueva solicitud AlphaBox", $htmlAdmin);
    }

    // Si falló la BD, pero enviamos aviso, no bloquear al usuario
    $success = $dbResult["success"] || $confirmationSent || $adminNoticeSent;
    $message = $dbResult["success"]
        ? htmlspecialchars($dbResult["message"], ENT_QUOTES, 'UTF-8')
        : "Solicitud recibida. Revisaremos manualmente y te escribiremos para confirmar.";

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "db_saved" => $dbResult["success"],
        "email_sent" => $confirmationSent,
        "admin_notified" => $adminNoticeSent
    ]);
}

/* ===========================================================
   AUXILIARES
   =========================================================== */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function checkAdmin(): void {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        exit("No autorizado.");
    }
}
