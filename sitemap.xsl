<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="html" indent="yes" encoding="UTF-8"/>

<xsl:template match="/">

<html>
<head>
  <title>Sitemap Mindmap</title>

  <style>
    /* SISTEMA MODULAR UNIFICADO PARA SITEMAP */
    :root {
      --primary-gold: #e0b94d;
      --primary-gold-light: #f4c842;
      --bg-primary: #0a0a0a;
      --bg-secondary: #1a1a1a;
      --bg-panel: rgba(255, 255, 255, 0.05);
      --text-primary: #ffffff;
      --text-secondary: rgba(255, 255, 255, 0.9);
      --text-muted: rgba(255, 255, 255, 0.6);
      --border-primary: rgba(224, 185, 77, 0.2);
      --radius: 16px;
      --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 50%, var(--bg-primary) 100%);
      color: var(--text-primary);
      margin: 0;
      padding: 40px;
      line-height: 1.6;
    }

    .sitemap-header {
      text-align: center;
      margin-bottom: 40px;
      padding: 30px;
      background: var(--bg-panel);
      backdrop-filter: blur(20px);
      border: 1px solid var(--border-primary);
      border-radius: var(--radius);
    }

    .sitemap-title {
      font-size: 2.5rem;
      font-weight: 700;
      background: linear-gradient(135deg, var(--primary-gold) 0%, var(--primary-gold-light) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
    }

    .sitemap-subtitle {
      color: var(--text-secondary);
      font-size: 1.1rem;
    }

    .node {
      margin-left: 20px;
      padding: 15px 0;
      position: relative;
      border-radius: 8px;
      transition: var(--transition);
    }

    .node:hover {
      background: var(--bg-panel);
      padding-left: 25px;
    }

    .node:before {
      content: "📄";
      position: absolute;
      left: -25px;
      top: 18px;
      color: var(--primary-gold);
      font-size: 1.2rem;
    }

    .node.parent:before {
      content: "📁";
    }

    .children {
      margin-left: 30px;
      border-left: 2px solid var(--border-primary);
      padding-left: 25px;
      position: relative;
    }

    .children:before {
      content: "";
      position: absolute;
      left: -2px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: linear-gradient(to bottom, var(--primary-gold), transparent);
    }

    a {
      text-decoration: none;
      color: var(--text-secondary);
      font-weight: 600;
      transition: var(--transition);
      display: inline-block;
    }

    a:hover {
      color: var(--primary-gold);
      transform: translateX(5px);
    }

    .meta {
      color: var(--text-muted);
      font-size: 0.9rem;
      font-weight: 400;
      margin-top: 5px;
    }

    .lastmod {
      background: var(--bg-panel);
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
    }

    .priority {
      background: var(--primary-gold);
      color: var(--bg-primary);
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .changefreq {
      background: rgba(224, 185, 77, 0.1);
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
    }

    .stats {
      background: var(--bg-panel);
      backdrop-filter: blur(20px);
      border: 1px solid var(--border-primary);
      border-radius: var(--radius);
      padding: 25px;
      margin-top: 40px;
      text-align: center;
    }

    .stats h3 {
      color: var(--primary-gold);
      margin-bottom: 20px;
      font-size: 1.5rem;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .stat-item {
      background: rgba(255, 255, 255, 0.05);
      padding: 15px;
      border-radius: 12px;
      border: 1px solid rgba(224, 185, 77, 0.1);
    }

    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary-gold);
      display: block;
      margin-bottom: 5px;
    }

    .stat-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
    }
      margin-top: 5px;
    }

    .toggle {
      cursor: pointer;
      color: #333;
      font-size: 14px;
      margin-right: 5px;
      user-select: none;
    }
  </style>

  <script>
    function toggleChildren(id) {
      const block = document.getElementById(id);
      block.style.display = block.style.display === "none" ? "block" : "none";
    }
  </script>

</head>

<body>

<h1>Sitemap – Mindmap</h1>

<div class="mindmap">

  <div class="node">
    <span class="toggle" onclick="toggleChildren('root')">[+/-]</span>
    <strong>Raíz del sitio</strong>
  </div>

  <div class="children" id="root">

    <xsl:for-each select="urlset/url">
      <div class="node">
        <a href="{loc}">
          <xsl:value-of select="loc"/>
        </a>

        <div class="meta">
          Última modificación:
          <xsl:value-of select="lastmod"/> |
          Frec: <xsl:value-of select="changefreq"/> |
          Prio: <xsl:value-of select="priority"/>
        </div>
      </div>
    </xsl:for-each>

  </div>

</div>

</body>
</html>

</xsl:template>
</xsl:stylesheet>