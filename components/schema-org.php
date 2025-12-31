<?php
$schemaBaseUrl = $baseUrl ?? (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'alphasupps.com'));
$schemaLogo = $schemaBaseUrl . '/images/' . ($uniqueFavicon ?? 'favicon.svg');
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "AlphaSupps",
  "url": "<?= htmlspecialchars($schemaBaseUrl) ?>",
  "logo": "<?= htmlspecialchars($schemaLogo) ?>",
  "description": "Tienda de suplementos premium con calidad y ciencia para tu rendimiento deportivo.",
  "foundingDate": "2024",
  "contactPoint": {
    "@type": "ContactPoint",
    "contactType": "customer service",
    "availableLanguage": "Spanish"
  },
  "sameAs": [
    "https://www.instagram.com/alphasupps",
    "https://www.facebook.com/alphasupps"
  ],
  "hasOfferCatalog": {
    "@type": "OfferCatalog",
    "name": "Catálogo de Suplementos",
    "itemListElement": [
      {
        "@type": "Offer",
        "itemOffered": {
          "@type": "Product",
          "name": "Proteínas",
          "description": "Proteínas de alta calidad para el desarrollo muscular"
        }
      },
      {
        "@type": "Offer",
        "itemOffered": {
          "@type": "Product",
          "name": "Creatina",
          "description": "Suplementos de creatina para mejorar el rendimiento"
        }
      },
      {
        "@type": "Offer",
        "itemOffered": {
          "@type": "Product",
          "name": "Aminoácidos",
          "description": "Aminoácidos esenciales para la recuperación muscular"
        }
      }
    ]
  }
}
</script>
