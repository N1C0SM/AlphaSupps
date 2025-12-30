<?php
require_once __DIR__ . '/db.php';

/**
 * Normaliza la fila del pack
 */
function optimizePackImagePath(string $src): string {
    if ($src === '') {
        return '/images/default.png';
    }

    // Quitar dominio y normalizar rutas relativas
    $normalized = preg_replace('#^https?://[^/]+#i', '', $src);
    $normalized = preg_replace('#^(\\.\\./)+#', '', ltrim((string)$normalized, '/'));

    // Mapear imágenes pesadas conocidas a versiones comprimidas
    if (str_contains($normalized, 'images/hero.png')) {
        return '/images/hero-900.webp';
    }

    // Si existe versión WebP local, usarla
    $webpCandidate = preg_replace('/\\.(png|jpe?g)$/i', '.webp', $normalized);
    if ($webpCandidate !== $normalized) {
        $localPath = dirname(__DIR__) . '/' . $webpCandidate;
        if (file_exists($localPath)) {
            return '/' . ltrim($webpCandidate, '/');
        }
    }

    return $src;
}

/**
 * Normaliza la fila del pack
 */
function normalizePack($row) {

    $row['visibility'] = strtolower(trim($row['visibility'] ?? ''));
    if ($row['visibility'] === '') {
        $row['visibility'] = 'private';
    }

    $row['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $row['name'])));

    $row['images']   = !empty($row['images'])   ? json_decode($row['images'], true) : [];
    if (!empty($row['images']) && is_array($row['images'])) {
        $row['images'] = array_values(array_filter(array_map('optimizePackImagePath', $row['images'])));
    }

    $row['benefits'] = !empty($row['benefits']) ? json_decode($row['benefits'], true) : [];
    $row['features'] = !empty($row['features']) ? json_decode($row['features'], true) : [];
    if (!empty($row['link'])) {
        $decoded = json_decode($row['link'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $row['link'] = $decoded;
        } else {
            $row['link'] = ['token' => $row['link'], 'created' => null];
        }
    } else {
        $row['link'] = null;
    }

    $row['rating'] = floatval($row['rating'] ?? 0);
    $row['highlighted'] = (int)($row['highlighted'] ?? 0);

    return $row;
}


/** Imagen principal */
function packMainImage(array $pack): string {
    return optimizePackImagePath($pack['images'][0] ?? '/images/default.png');
}

/** Label (categoría o texto) */
function packLabel(array $pack): string {
    return $pack['label'] ?? $pack['category'] ?? 'Sin categoría';
}

/** Descripción resumida */
function packSummary(array $pack, bool $isPrivate): string {
    $default = $isPrivate ? 'Tu pack personalizado' : 'Pack diseñado por AlphaSupps';
    $desc = trim($pack['description'] ?? $default);
    return strlen($desc) > 110 ? substr($desc, 0, 107) . '...' : $desc;
}

/** Precio formateado */
function packPrice(array $pack): string {
    return number_format((float)($pack['price'] ?? 0), 2, ',', '.');
}

/** Icono según visibilidad */
function packVisibilityIcon(array $pack, bool $isPrivate): string {
    if (!$isPrivate) return '';
    return match ($pack['visibility'] ?? 'private') {
        'shared_link' => '🔗',
        'public'      => '🌍',
        default       => '🔒',
    };
}

/** Texto del estado */
function packVisibilityText(array $pack): string {
    return match ($pack['visibility'] ?? 'private') {
        'shared_link' => 'Compartido mediante enlace',
        'public'      => 'Publicado',
        default       => 'Solo tú puedes verlo',
    };
}

/** Clase CSS del estado */
function packVisibilityClass(array $pack): string {
    return match ($pack['visibility'] ?? 'private') {
        'shared_link' => 'status-shared',
        'public'      => 'status-public',
        default       => 'status-private',
    };
}
/**
 * INSERTAR PACK (owner_id)
 */
function insertPack($conn,$owner_id, $name,$description,$images,$benefits,$price,$category,$visibility,$features,$label = null,$link = null) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO packs (
                owner_id,
                name,
                category,
                description,
                images,
                benefits,
                price,
                visibility,
                features,
                label,
                link,
                created_at,
                updated_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        if (!$stmt) {
            return "Error en la preparación: " . $conn->error;
        }

        // Preparar variables para bind_param (requiere referencias)
        $images_json = json_encode($images);
        $benefits_json = json_encode($benefits);
        $features_json = json_encode($features);

        $stmt->bind_param("isssssdssss",
            $owner_id,
            $name,
            $category,
            $description,
            $images_json,
            $benefits_json,
            $price,
            $visibility,
            $features_json,
            $label,
            $link
        );

        if ($stmt->execute()) {
            return intval($conn->insert_id);
        }

        return "Error al ejecutar: " . $stmt->error;

    } catch (Exception $e) {
        return $e->getMessage();
    }
}



/**
 * PACKS PÚBLICOS
 */
function getPublicPacks($conn) {
    $sql = "SELECT * FROM packs WHERE LOWER(TRIM(visibility)) = 'public' ORDER BY id ASC";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("getPublicPacks query failed: " . $conn->error);
        return [];
    }

    $packs = [];
    while ($row = $result->fetch_assoc()) {
        $packs[] = normalizePack($row);
    }

    error_log("Found " . count($packs) . " public packs");
    return $packs;
}


/**
 * PACKS PRIVADOS
 */
function getPacksByOwner($conn, $ownerId) {
    error_log("getPacksByOwner called with ownerId: " . ($ownerId ?? 'null'));

    if (!$ownerId) {
        error_log("No ownerId provided, returning empty array");
        return [];
    }

    $stmt = $conn->prepare("SELECT * FROM packs WHERE owner_id = ? ORDER BY id ASC");
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return [];
    }

    $stmt->bind_param("i", $ownerId);

    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        return [];
    }

    $result = $stmt->get_result();
    $packs = [];
    while ($row = $result->fetch_assoc()) {
        error_log("Pack ID {$row['id']}: visibility = '{$row['visibility']}', link = '{$row['link']}'");

        // Verificar si hay visibilidades inesperadas
        if (!in_array($row['visibility'], ['public', 'private'])) {
            error_log("WARNING: Pack {$row['id']} tiene visibility '{$row['visibility']}' que no está en el enum!");
        }
        $packs[] = normalizePack($row);
    }

    error_log("Found " . count($packs) . " packs for owner $ownerId");
    return $packs;
}


/**
 * PACKS POR USUARIO (devuelve todos los packs del usuario - públicos y privados)
 */
function getPacksByUser($conn, $ownerId) {
    $stmt = $conn->prepare("SELECT * FROM packs WHERE owner_id = ? ORDER BY id ASC");

    if (!$stmt) {
        die("SQL ERROR en getPacksByUser: " . $conn->error);
    }

    $stmt->bind_param("i", $ownerId);
    $stmt->execute();

    $result = $stmt->get_result();

    $packs = [];
    while ($row = $result->fetch_assoc()) {
        $packs[] = normalizePack($row);
    }

    return $packs;
}


/**
 * PACK POR ID
 */
function getPackById($conn, $id) {

    $stmt = $conn->prepare("SELECT * FROM packs WHERE id = ? LIMIT 1");

    if (!$stmt) {
        die("SQL ERROR en getPackById: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) return null;

    $pack = normalizePack($result->fetch_assoc());

    return $pack;
}

/**
 * ACTUALIZAR RATING DE UN PACK
 */
function updatePackRating($conn, $packId, $newRating) {
    // Verificar que el rating esté entre 1 y 5
    $newRating = max(1, min(5, floatval($newRating)));

    $stmt = $conn->prepare("
        UPDATE packs
        SET rating = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("si", $newRating, $packId);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function updatePackVisibility($conn, $packId, $visibility, $link) {
    $stmt = mysqli_prepare($conn, "
        UPDATE packs
        SET visibility = ?, link = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt, "ssi", $visibility, $link, $packId);
    return mysqli_stmt_execute($stmt);
}

/**
 * Marca como destacados todos los packs que tengan el mayor número de votos.
 * Si hay empate se destacan varios; el resto se desmarca.
 */
function updateHighlightedPackFromVotes($conn): void {
    $results = $conn->query("
        SELECT idPack, COUNT(*) AS total
        FROM reviews
        WHERE idPack IS NOT NULL
        GROUP BY idPack
        ORDER BY total DESC
    ");

    $topPacks = [];
    $maxVotes = -1;

    if ($results) {
        while ($row = $results->fetch_assoc()) {
            $packId = (int) ($row['idPack'] ?? 0);
            $votes = (int) ($row['total'] ?? 0);

            if ($votes <= 0 || $packId <= 0) {
                continue;
            }

            if ($votes > $maxVotes) {
                $maxVotes = $votes;
                $topPacks = [$packId];
            } elseif ($votes === $maxVotes) {
                $topPacks[] = $packId;
            }
        }
    }

    // Resetear destacados y etiquetas automáticas
    $conn->query("UPDATE packs SET highlighted = 0");
    $conn->query("UPDATE packs SET label = NULL WHERE label IN ('Más vendido','Más valorado','Más vendido · Más valorado')");

    // Destacar los que tienen el máximo de votos
    foreach ($topPacks as $packId) {
        $stmt = $conn->prepare("UPDATE packs SET highlighted = 1 WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $packId);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Calcular packs más vendidos
    $topSoldPacks = [];
    $maxSold = -1;
    $soldResult = $conn->query("SELECT id, COALESCE(sold, 0) AS sold FROM packs ORDER BY sold DESC");
    if ($soldResult) {
        while ($row = $soldResult->fetch_assoc()) {
            $packId = (int) ($row['id'] ?? 0);
            $sold = (int) ($row['sold'] ?? 0);
            if ($packId <= 0) {
                continue;
            }
            if ($sold > $maxSold) {
                $maxSold = $sold;
                $topSoldPacks = [$packId];
            } elseif ($sold === $maxSold) {
                $topSoldPacks[] = $packId;
            }
        }
    }

    // Calcular packs más valorados
    $topRatedPacks = [];
    $maxRating = -1;
    $ratingResult = $conn->query("SELECT id, rating FROM packs");
    if ($ratingResult) {
        while ($row = $ratingResult->fetch_assoc()) {
            $packId = (int) ($row['id'] ?? 0);
            $rating = floatval($row['rating'] ?? 0);
            if ($packId <= 0) {
                continue;
            }
            if ($rating > $maxRating) {
                $maxRating = $rating;
                $topRatedPacks = [$packId];
            } elseif ($rating === $maxRating) {
                $topRatedPacks[] = $packId;
            }
        }
    }

    // Asignar etiquetas automáticas
    $labelsByPack = [];

    if ($maxSold >= 0) {
        foreach ($topSoldPacks as $packId) {
            $labelsByPack[$packId][] = '🔥 Más vendido';
        }
    }

    if ($maxRating > 0) {
        foreach ($topRatedPacks as $packId) {
            $labelsByPack[$packId][] = '⭐ Más valorado';
        }
    }

    foreach ($labelsByPack as $packId => $parts) {
        $label = implode(' · ', array_unique($parts));
        $stmt = $conn->prepare("UPDATE packs SET label = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $label, $packId);
            $stmt->execute();
            $stmt->close();
        }
    }
}
