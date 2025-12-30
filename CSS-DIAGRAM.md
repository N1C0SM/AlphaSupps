# 📊 Diagrama de Estructura CSS - AlphaSupps

## 🎯 Jerarquía de Importación

```
┌─────────────────────────────────────────────────────┐
│                   main.css (PUNTO DE ENTRADA)       │
│                      (importa todo)                  │
└──────────────────────┬────────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ▼              ▼              ▼
    ┌────────┐    ┌────────┐    ┌──────────┐
    │GLOBAL  │    │LAYOUT  │    │FEATURES  │
    │ BASE   │    │ESTRUC. │    │INTERAC.  │
    └────────┘    └────────┘    └──────────┘
        │              │              │
    ┌───┴───┐      ┌───┴───┐     ┌───┴────┐
    │       │      │       │     │        │
    ▼       ▼      ▼       ▼     ▼        ▼
  vars  type  head foot sidebar cart modal
  color spac nav  responsive  subscription
                            search filters
```

---

## 📁 Árbol Completo

```
css/
│
├── 📄 main.css ⭐ PRINCIPAL
│   └─ Importa todos los demás en orden
│
├── 📄 variables.css
├── 📄 reset.css
│
├── 📁 global/
│   ├── typography.css  (fuentes, tamaños)
│   ├── colors.css      (paleta, gradientes)
│   └── spacing.css     (márgenes, padding)
│
├── 📁 components/
│   ├── buttons.css     (.btn, .btn-primary)
│   ├── cards.css       (.card, .card-hover)
│   └── forms.css       (inputs, selects)
│
├── 📁 layout/
│   ├── header.css      (navegación, logo)
│   ├── footer.css      (pie de página)
│   ├── sidebar.css     (barra lateral)
│   └── responsive.css  (media queries)
│
├── 📁 pages/
│   ├── landing.css     (página inicio)
│   ├── product.css     (producto individual)
│   ├── products.css    (listado catálogo)
│   ├── blog.css        (artículos)
│   ├── user.css        (perfil)
│   └── checkout.css    (carrito/compra)
│
├── 📁 features/
│   ├── cart.css        (carrito interactivo)
│   ├── modal.css       (pop-ups)
│   ├── cookies.css     (banner cookies)
│   ├── subscription.css (suscripción) ⭐
│   ├── search.css      (búsqueda)
│   ├── filters.css     (filtros)
│   └── favorites.css   (favoritos)
│
├── 📁 admin/
│   ├── dashboard.css   (dashboard)
│   ├── forms.css       (formularios)
│   └── tables.css      (tablas)
│
├── 📁 utilities/
│   ├── spacing.css     (.mt-2, .p-3, etc)
│   ├── animations.css  (@keyframes, .fade-in)
│   ├── visibility.css  (.hidden, .show)
│   └── print.css       (estilos impresión)
│
├── 📄 marketing-enhancements.css
├── 📄 core.css
└── 📄 media.css
```

---

## 🔄 Flujo de Estilos

```
┌─────────────────────────────────────────┐
│       Usuario abre página               │
└──────────────────┬──────────────────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │  Carga main.css      │
        └──────────┬───────────┘
                   │
        ┌──────────┴──────────┐
        │ main.css importa:   │
        │ 1. variables.css    │
        │ 2. reset.css        │
        │ 3. global/*.css     │
        │ 4. components/*.css │
        │ 5. layout/*.css     │
        │ 6. pages/*.css      │
        │ 7. features/*.css   │
        │ 8. admin/*.css      │
        │ 9. utilities/*.css  │
        └──────────┬──────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │  CSS aplicado al DOM │
        │  en cascada correcta │
        └─────────────────────┘
```

---

## 📝 Matriz de Decisión

¿Dónde pongo mi nuevo CSS?

```
┌──────────────────────────┬────────────────────────────┐
│         TIPO             │          CARPETA           │
├──────────────────────────┼────────────────────────────┤
│ Variables, colores base  │ global/                    │
├──────────────────────────┼────────────────────────────┤
│ Botón reutilizable       │ components/                │
├──────────────────────────┼────────────────────────────┤
│ Header, footer           │ layout/                    │
├──────────────────────────┼────────────────────────────┤
│ Página /productos        │ pages/products.css         │
├──────────────────────────┼────────────────────────────┤
│ Carrito deslizable       │ features/cart.css          │
├──────────────────────────┼────────────────────────────┤
│ .hidden, .mt-2           │ utilities/                 │
├──────────────────────────┼────────────────────────────┤
│ Admin dashboard          │ admin/                     │
└──────────────────────────┴────────────────────────────┘
```

---

## 🎨 Paleta de Colores Disponibles

```
PRINCIPALES
├── --primary (#111111)      █████ Negro
├── --accent (#f0c75e)       ███ Dorado (MARCA)
└── --accent-dark (#d4a844)  ███ Dorado oscuro

SEMÁNTICOS
├── --success (#27ae60)      ███ Verde
├── --danger (#e74c3c)       ███ Rojo
├── --warning (#f39c12)      ███ Naranja
└── --info (#3498db)         ███ Azul

GRISES
├── --gray-50 (#f9f9f9)
├── --gray-500 (#666666)     ← Texto por defecto
└── --gray-900 (#111111)

FONDOS
├── --bg (#ffffff)
├── --bg-dark (#0a0a0a)      ← Por defecto
├── --bg-surface (#1a1a1a)
└── --bg-light (#f5f5f5)
```

---

## ⏱️ Transiciones Disponibles

```
RÁPIDAS
├── --transition-faster (0.1s)
└── --transition-fast (0.15s)

NORMALES
└── --transition (0.3s) ← ESTÁNDAR

LENTAS
├── --transition-slow (0.5s)
└── --transition-slower (0.75s)
```

---

## 🎯 Z-Index Hierarchy

```
1080  ┌─────────────┐
      │  TOAST      │ Notificaciones
      └─────────────┘

1070  ┌─────────────┐
      │ TOOLTIP     │ Ayudas
      └─────────────┘

1050  ┌─────────────┐
      │ POPOVER     │ Menús desplegables
      └─────────────┘

1000  ┌─────────────┐
      │  MODAL      │ Diálogos
      └─────────────┘

30    ┌─────────────┐
      │  FIXED      │ Fixed positioning
      └─────────────┘

20    ┌─────────────┐
      │  STICKY     │ Sticky positioning
      └─────────────┘

10    ┌─────────────┐
      │ DROPDOWN    │ Menús
      └─────────────┘
```

---

## 📱 Breakpoints Disponibles

```
480px   Móvil pequeño
├─ layout/responsive.css
├─ Mobile first approach
│
768px   Tablet
├─ 2 columnas
├─ Sidebar visible
│
1024px  Desktop
├─ 3-4 columnas
├─ Layout completo
│
1440px  Wide screen
└─ Grid de 5+ columnas
```

---

## ✨ Características Especiales

### 1. Variables Dinámicas

```css
:root {
  --accent: #f0c75e; /* Cambiar aquí = cambiar en TODO */
}
```

### 2. Modo Oscuro/Claro

```css
@media (prefers-color-scheme: dark) {
  :root {
    --text: #f0f0f0; /* Invierte automáticamente */
  }
}
```

### 3. Reducir Movimiento

```css
@media (prefers-reduced-motion: reduce) {
  * {
    --transition: 0.01ms; /* Respeta preferencias */
  }
}
```

### 4. Impresión

```css
@media print {
  header,
  footer {
    display: none;
  } /* Optimiza impresora */
}
```

---

## 🚀 Próximas Mejoras Recomendadas

1. **Minificar main.css** para producción
2. **Crítica CSS** para renderizado más rápido
3. **SASS/SCSS** para variables más potentes
4. **PostCSS** para autoprefixer automático
5. **CSS Grid Generator** para layouts complejos

---

## 📞 Preguntas Comunes

### P: ¿Dónde cambio el color principal?

A: En `global/colors.css` o `variables-complete.css`

### P: ¿Cómo agrego una nueva página?

A: Crea archivo en `pages/`, importa en `main.css`

### P: ¿Puedo usar Tailwind CSS junto a esto?

A: No recomendado, pero puedes agregar después de `main.css`

### P: ¿Qué pasa si cambio `main.css`?

A: Todos los estilos se actualizan automáticamente (cascada CSS)

---

## 🎉 ¡Listo para Producción!

Tu CSS es ahora:

- ✅ Modular (fácil de mantener)
- ✅ Escalable (fácil de crecer)
- ✅ Reutilizable (sin duplicados)
- ✅ Profesional (estándares web)
- ✅ Accesible (WCAG ready)

¡Adelante! 🚀
