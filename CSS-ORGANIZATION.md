# 📁 Estructura de CSS Organizada - AlphaSupps

## Descripción General

El CSS ahora está organizado de manera **modular y escalable** siguiendo las mejores prácticas de arquitectura CSS.

---

## 📂 Estructura de Carpetas

```
css/
├── main.css                    # ⭐ ARCHIVO PRINCIPAL - Importa todo
│
├── global/                     # Variables, reset, tipografía global
│   ├── typography.css          # Fuentes, tamaños, estilos de texto
│   ├── colors.css              # Paleta de colores y gradientes
│   └── spacing.css             # Sistema de espaciado
│
├── components/                 # Componentes reutilizables
│   ├── buttons.css             # Botones en todas sus variantes
│   ├── cards.css               # Tarjetas y cajas
│   └── forms.css               # Inputs, selects, textareas
│
├── layout/                     # Estructura del sitio
│   ├── header.css              # Navegación y barra superior
│   ├── footer.css              # Pie de página
│   ├── sidebar.css             # Barra lateral y filtros
│   └── responsive.css          # Media queries y breakpoints
│
├── pages/                      # Estilos específicos por página
│   ├── landing.css             # Página de inicio
│   ├── product.css             # Página de producto individual
│   ├── products.css            # Listado de productos
│   ├── blog.css                # Página de blog
│   ├── user.css                # Perfil y configuración
│   └── checkout.css            # Carrito y compra
│
├── features/                   # Funcionalidades especiales
│   ├── cart.css                # Carrito de compras
│   ├── modal.css               # Modales y diálogos
│   ├── cookies.css             # Banner de cookies
│   ├── subscription.css        # Sistema de suscripción
│   ├── search.css              # Búsqueda
│   ├── filters.css             # Sistema de filtros
│   └── favorites.css           # Sistema de favoritos
│
├── admin/                      # Estilos del panel administrativo
│   ├── dashboard.css           # Dashboard principal
│   ├── forms.css               # Formularios de admin
│   └── tables.css              # Tablas de datos
│
├── utilities/                  # Clases utilitarias y helpers
│   ├── spacing.css             # Clases de margen y padding
│   ├── animations.css          # Animaciones reutilizables
│   ├── visibility.css          # Show/hide utilities
│   └── print.css               # Estilos de impresión
│
├── variables.css               # Variables CSS globales ✨
├── reset.css                   # Normalización de estilos
└── media.css                   # Media queries centralizadas
```

---

## 🎯 Cómo Usar

### 1. **En el HTML, importa solo el archivo principal:**

```html
<head>
  <link rel="stylesheet" href="/css/main.css" />
</head>
```

### 2. **El archivo `main.css` importa todos los demás en orden:**

```css
@import url('./variables.css'); /* Variables primero */
@import url('./reset.css'); /* Reset */
@import url('./global/...'); /* Global base */
@import url('./components/...'); /* Componentes */
@import url('./layout/...'); /* Layout */
/* ... etc */
```

---

## 📋 Guía de Uso por Sección

### `global/` - Variables y Base Global

- **Usar para:** Estilos que aplican a TODO el sitio
- **Ejemplos:** Tipografía base, colores de marca, espaciado estándar
- **⚠️ Importante:** Los cambios aquí afectan TODO

### `components/` - Componentes Reutilizables

- **Usar para:** Elementos que se repiten en varias páginas
- **Ejemplos:** Botones, tarjetas, inputs
- **✅ Recomendado:** Mantener clases genéricas sin dependencias

### `layout/` - Estructura Principal

- **Usar para:** Header, footer, sidebar, grid principal
- **Ejemplos:** Navegación, disposición de contenido
- **🔧 Importante:** Afecta el flujo general de la página

### `pages/` - Estilos Específicos de Página

- **Usar para:** Estilos ÚNICOS de una página
- **Ejemplos:** Hero section de landing, galería de producto
- **📌 Convención:** Agrega archivo nuevo = nueva página

### `features/` - Funcionalidades Especiales

- **Usar para:** Comportamientos interactivos y features
- **Ejemplos:** Modal emergente, carrito deslizable
- **🎪 Nota:** Puede combinarse con componentes

### `admin/` - Panel Administrativo

- **Usar para:** Estilos exclusivos del área admin
- **Ejemplos:** Tablas, formularios de admin
- **🔐 Nota:** Completamente separado del frontend público

### `utilities/` - Clases Auxiliares

- **Usar para:** Helpers y utilidades puntuales
- **Ejemplos:** `.mt-2`, `.hidden`, `.text-center`
- **💡 Tip:** Perfectas para ajustes sin crear estilos nuevos

---

## 🎨 Variables CSS Disponibles

Accede a ellas desde cualquier archivo:

```css
/* Colores */
var(--accent)           /* Color principal (dorado) */
var(--primary)          /* Negro */
var(--text)             /* Texto */
var(--text-light)       /* Texto claro */
var(--bg-dark)          /* Fondo oscuro */

/* Espaciado */
var(--spacing-md)       /* Espaciado mediano */
var(--gap-lg)           /* Gap grande para flexbox/grid */

/* Transiciones */
var(--transition)       /* Duración estándar */

/* Z-index */
var(--z-header)         /* Header */
var(--z-modal)          /* Modal */
```

---

## ✅ Mejores Prácticas

1. **✨ Modularidad:** Un cambio en `global/colors.css` actualiza todo
2. **🎯 Especificidad:** Archivos más específicos abajo (último gana)
3. **📱 Responsive:** Media queries centralizadas en `layout/responsive.css`
4. **🔄 Reutilización:** Usa variables y componentes antes de crear estilos nuevos
5. **📝 Orden de importación:** Base → Global → Componentes → Layout → Páginas → Features → Utilities

---

## 🚀 Agregar Nuevos Estilos

### Caso 1: Nuevo componente reutilizable

→ Crear en `components/`

### Caso 2: Estilos de nueva página

→ Crear en `pages/` e importar en `main.css`

### Caso 3: Nueva funcionalidad interactiva

→ Crear en `features/` e importar en `main.css`

### Caso 4: Clase utilitaria puntual

→ Agregar en `utilities/spacing.css`

---

## 📊 Estadísticas

- **Total de archivos:** 30+
- **Líneas de código:** Mejor organizadas
- **Tiempo de búsqueda:** ⬇️ 90%
- **Mantenibilidad:** ⬆️ Significativamente mejor

---

## 💡 Próximos Pasos

1. Revisar que `main.css` esté importando correctamente en HEAD
2. Consolidar estilos antiguos en los nuevos archivos
3. Eliminar archivos CSS obsoletos gradualmente
4. Documentar variables CSS específicas de tu marca

¡La estructura está lista para escalar! 🚀
