<?php
$mainImage = (!empty($pack['images']) && is_array($pack['images']))
    ? $pack['images'][0]
    : "/images/default.png";

$label = $pack['label'] ?: ($pack['category'] ?? 'Sin categoría');
$labelClass = 'label-' . strtolower(preg_replace('/[^a-z0-9]+/', '-', $label));

$description = trim($pack['description'] ?? 'Pack diseñado por la comunidad');
$summary = strlen($description) > 110 ? substr($description, 0, 107) . '...' : $description;

$itemsCount = (isset($pack['features']) && is_array($pack['features']))
    ? count($pack['features'])
    : 0;

$priceFormatted = is_numeric($pack['price'])
    ? number_format((float)$pack['price'], 2, ',', '.')
    : $pack['price'];
?>

<div class="pack-card pack-public">
    <div class="pack-card-top">
        <span class="pack-card-badge <?= htmlspecialchars($labelClass) ?>">
            <?= htmlspecialchars($label) ?>
        </span>
    </div>

    <div class="pack-card-image">
        <img src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($pack['name']) ?>">
    </div>

    <div class="pack-card-content">
        <h3><?= htmlspecialchars($pack['name']) ?></h3>

        <p class="pack-card-text"><?= htmlspecialchars($summary) ?></p>

        <div class="pack-card-meta">
            <span class="pack-price">€<?= $priceFormatted ?></span>

            <?php if ($itemsCount > 0): ?>
                <span class="pack-items"><?= $itemsCount ?> productos</span>
            <?php endif; ?>
        </div>
    </div>

    <a href="/views/pack.php?id=<?= $pack['id'] ?>" class="pack-card-cta">Ver pack</a>
</div>