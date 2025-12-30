<?php
require_once '../models/db.php';
require_once  '../models/collection.php';
require_once  '../models/pack.php';
require_once '../models/reviews.php';
$isAdminPanel = false;
$conn = connectToDatabase();
$collections = getAllCollections($conn);
$packs = getPublicPacks($conn);
$reviews = getReviewsLimit($conn, 4);
