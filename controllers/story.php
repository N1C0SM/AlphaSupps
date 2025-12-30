<?php
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/reviews.php';


// ============================
// 🔹 VARIABLES SEO
// ============================
$title = "AlphaSupps | Nuestra Historia y Filosofía";
$description = "Conoce la historia detrás de AlphaSupps: una marca creada para impulsar el rendimiento, la energía y la estética con suplementos basados en ciencia y resultados reales.";
$url = "https://alphasupps.alwaysdata.net/views/story.php";
$ogImage = "https://alphasupps.alwaysdata.net/assets/images/og-story.jpg";

// ============================
// 🔹 DATOS DINÁMICOS (modelo)
// ============================
$conn = connectToDatabase();
$reviews = getReviewsLimit($conn, 4);
