# 🎨 CSS AlphaSupps - Guía Completa de Organización

## 📦 Resumen de la Reorganización

Tu CSS ha sido **completamente reorganizado** de forma **modular, escalable y profesional**.

### Antes ❌

```
css/
├── admin.css
├── buttons.css
├── cards.css
├── cart.css
├── ... 30+ archivos sueltos
└── styles.css (caos total)
```

### Después ✅

```
css/
├── main.css ⭐ (importa todo)
├── global/ (base y variables)
├── components/ (reutilizables)
├── layout/ (estructura)
├── pages/ (páginas específicas)
├── features/ (funcionalidades)
├── admin/ (panel admin)
└── utilities/ (helpers)
```

---

## 🚀 Cómo Implementar

### Paso 1: Actualizar el HEAD del HTML

Cambiar de esto:

```html
<link rel="stylesheet" href="../css/reset.css" />
<link rel="stylesheet" href="../css/variables.css" />
<link rel="stylesheet" href="../css/header.css" />
<link rel="stylesheet" href="../css/cart.css" />
<!-- ... 20+ más -->
```

A esto:

```html
<!-- ✅ IMPORTAR SOLO UNA VEZ -->
<link rel="stylesheet" href="../css/main.css" />
```

### Paso 2: El archivo `main.css` se encarga del resto

`main.css` importa automáticamente todos los archivos en el orden correcto.

---

## 📂 Estructura Detallada

### 1️⃣ **global/** - Base del Sitio

```
global/
├── typography.css     → Fuentes, tamaños, estilos
├── colors.css        → Paleta de colores y gradientes
└── spacing.css       → Sistema de espaciado unificado
```

**Uso:** Cambios que afectan TODO el sitio

### 2️⃣ **components/** - Elementos Reutilizables

```
components/
├── buttons.css       → .btn, .btn-primary, .btn-outline
├── cards.css         → .card, .card-hover, .card-minimal
└── forms.css         → inputs, selects, textareas
```

**Uso:** Elementos que se repiten en múltiples páginas

### 3️⃣ **layout/** - Estructura Principal

```
layout/
├── header.css        → Navegación, logo, carrito
├── footer.css        → Pie de página, enlaces
├── sidebar.css       → Barra lateral, filtros
└── responsive.css    → Media queries y breakpoints
```

**Uso:** Cómo se distribuye el contenido en la página

### 4️⃣ **pages/** - Páginas Específicas

```
pages/
├── landing.css       → Página de inicio (hero, secciones)
├── product.css       → Página individual de producto
├── products.css      → Listado de catálogo
├── blog.css          → Artículos y posts
├── user.css          → Perfil y configuración
└── checkout.css      → Carrito y formulario de compra
```

**Uso:** Estilos ÚNICOS de cada página

### 5️⃣ **features/** - Funcionalidades Especiales

```
features/
├── cart.css          → Carrito deslizable
├── modal.css         → Pop-ups y diálogos
├── cookies.css       → Banner de cookies
├── subscription.css  → Sistema de suscripción ⭐
├── search.css        → Búsqueda y autocomplete
├── filters.css       → Sistema de filtros
└── favorites.css     → Sistema de favoritos/wishlist
```

**Uso:** Comportamientos interactivos

### 6️⃣ **admin/** - Panel Administrativo

```
admin/
├── dashboard.css     → Dashboard principal
├── forms.css         → Formularios de admin
└── tables.css        → Tablas de datos
```

**Uso:** Estilos del área privada de administración

### 7️⃣ **utilities/** - Clases Auxiliares

```
utilities/
├── spacing.css       → .mt-2, .p-3, .mx-auto (helpers)
├── animations.css    → @keyframes y clases de animación
├── visibility.css    → .hidden, .show, .sr-only
└── print.css         → Estilos para impresora
```

**Uso:** Ajustes puntuales sin crear estilos nuevos

---

## 🎯 Ejemplos de Uso

### Agregar Nuevo Componente

**Paso 1:** Crear archivo en `components/`

```
components/badges.css
```

**Paso 2:** Agregar importación en `main.css`

```css
@import url('./components/badges.css');
```

**Paso 3:** Usar en HTML

```html
<span class="badge badge-success">En stock</span>
```

---

### Agregar Nueva Página

**Paso 1:** Crear archivo en `pages/`

```
pages/invoice.css
```

**Paso 2:** Importar en `main.css`

```css
@import url('./pages/invoice.css');
```

**Paso 3:** Usar clases específicas

```html
<div class="invoice-container">
  <div class="invoice-header">...</div>
</div>
```

---

### Agregar Nueva Funcionalidad

**Paso 1:** Crear archivo en `features/`

```
features/notifications.css
```

**Paso 2:** Importar en `main.css`

```css
@import url('./features/notifications.css');
```

**Paso 3:** Usar en JavaScript

```javascript
showNotification('Éxito!', 'success');
```

---

## 📋 Variables CSS Disponibles

### Colores

```css
--primary: #111111           /* Negro principal */
--accent: #f0c75e            /* Dorado AlphaSupps */
--accent-dark: #d4a844       /* Dorado oscuro */
--text: variable según tema
--bg-dark: #0a0a0a
--bg-surface: #1a1a1a
--bg-light: #f5f5f5
--success: #27ae60           /* Verde */
--danger: #e74c3c            /* Rojo */
--warning: #f39c12           /* Naranja */
```

### Espaciado

```css
--spacing-xs: 0.25rem
--spacing-sm: 0.5rem
--spacing-md: 1rem
--spacing-lg: 1.5rem
--spacing-xl: 2rem
--spacing-2xl: 3rem
--spacing-3xl: 4rem

--gap-xs: 0.5rem
--gap-sm: 1rem
--gap-md: 1.5rem
--gap-lg: 2rem
--gap-xl: 3rem
```

### Transiciones

```css
--transition: 0.3s ease
--transition-slow: 0.5s ease
--transition-fast: 0.15s ease
```

### Z-index

```css
--z-header: 100
--z-modal: 1000
--z-toast: 1080
```

---

## ✅ Checklist de Implementación

- [ ] Actualizar `<link>` en componentes/head.php para usar solo `main.css`
- [ ] Verificar que todos los estilos sigan funcionando
- [ ] Probar en diferentes navegadores
- [ ] Revisar media queries en `layout/responsive.css`
- [ ] Documentar cambios de colores en `global/colors.css`
- [ ] Agregar nuevas páginas en la carpeta `pages/`
- [ ] Crear nuevos componentes en `components/`

---

## 💡 Mejores Prácticas

### ✨ DO (Haz esto)

```css
/* Usar variables */
background: var(--bg-dark);
color: var(--accent);

/* Usar clases reutilizables */
<button class="btn btn-primary">Comprar</button>

/* Seguir la estructura */
/* feature/ para funcionalidades, pages/ para páginas */

/* Nombrar clases semánticamente */
.product-card {
}
.modal-header {
}
```

### ❌ DON'T (No hagas esto)

```css
/* Evitar valores hardcodeados */
background: #0a0a0a;

/* Evitar IDs para estilos */
#main-header {
}

/* Evitar nesting profundo */
.sidebar .container .item span {
}

/* Evitar clases genéricas sin contexto */
.item {
}
.box {
}
```

---

## 🔧 Tareas Futuras

1. **Consolidar estilos antiguos**

   - Revisar archivos CSS viejos
   - Mover estilos a las nuevas carpetas
   - Eliminar duplicados

2. **Optimizar performance**

   - Minificar `main.css`
   - Usar crítico CSS para above-the-fold

3. **Implementar modo oscuro**

   - Extender variables en `global/colors.css`
   - Usar `prefers-color-scheme`

4. **Agregar SASS/SCSS**

   - Para anidamiento y mixins
   - Mejor mantenibilidad

---

## 📞 Contacto & Soporte

Si tienes dudas sobre dónde poner un estilo:

1. **¿Es base/variable?** → `global/`
2. **¿Es componente reutilizable?** → `components/`
3. **¿Es estructura de página?** → `layout/`
4. **¿Es estilo de una página?** → `pages/`
5. **¿Es funcionalidad interactiva?** → `features/`
6. **¿Es class auxiliar?** → `utilities/`
7. **¿Es admin?** → `admin/`

---

## 🎉 ¡Listo!

Tu CSS está **profesional, organizado y listo para escalar**.

Cada cambio será fácil de encontrar y mantener. 🚀
