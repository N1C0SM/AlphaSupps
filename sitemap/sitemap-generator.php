<?php
require_once './models/supplement.php';
require_once './models/post.php';

function generateSitemap() {
    $baseUrl = 'https://alphasupps.alwaysdata.net';
    $currentDate = date('Y-m-d');

    $staticUrls = [
        ['loc' => '/views/index.php', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['loc' => '/landing/index.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => '/views/supplements.php', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['loc' => '/views/blog.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['loc' => '/views/story.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
        ['loc' => '/views/collection.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['loc' => '/views/cookies.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => '/views/privacy-policy.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => '/views/terms.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => '/views/legal-advise.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => '/views/envios.php', 'priority' => '0.4', 'changefreq' => 'yearly'],
        ['loc' => '/views/desistimiento.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ];

    $supplements = getAllSupplements();
    $supplementUrls = [];
    foreach ($supplements as $supplement) {
        $supplementUrls[] = [
            'loc' => '/views/supplement.php?id=' . $supplement['id'],
            'lastmod' => date('Y-m-d', strtotime($supplement['created_at'])),
            'priority' => '0.8',
            'changefreq' => 'monthly'
        ];
    }

    $posts = getAllPosts();
    $postUrls = [];
    foreach ($posts as $post) {
        $postUrls[] = [
            'loc' => '/views/post.php?id=' . $post['id'],
            'lastmod' => date('Y-m-d', strtotime($post['created_at'])),
            'priority' => '0.6',
            'changefreq' => 'monthly'
        ];
    }

    $allUrls = array_merge($staticUrls, $supplementUrls, $postUrls);

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<?xml-stylesheet type="text/xsl" href="./sitemap.xsl"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($allUrls as $url) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($baseUrl . $url['loc']) . "</loc>\n";
        $xml .= "    <lastmod>" . ($url['lastmod'] ?? $currentDate) . "</lastmod>\n";
        $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
        $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>' . "\n";

    return $xml;
}

$sitemapContent = generateSitemap();
file_put_contents('sitemap.xml', $sitemapContent);

echo "Sitemap generado exitosamente con " . substr_count($sitemapContent, '<url>') . " URLs.\n";
?>
