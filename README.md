# AlphaSupps 🏋️‍♂️

Proyecto de comercio electrónico de suplementos deportivos de Nicolás San Marcos, desarrollado con PHP, MySQL y JavaScript. Reúne catálogo, packs personalizados, suscripciones y administración, con una estructura de modelos, vistas y controladores.

Este proyecto conecta mi interés por el deporte con el desarrollo de aplicaciones con bases de datos e integraciones externas.

**Guías del proyecto:** [organización del CSS](CSS-ORGANIZATION.md) · [estilos](README-CSS.md) · [comprobaciones de rendimiento](performance/README.md).

Los flujos de compra, correo e IA requieren configuración propia y pruebas de sus servicios externos.

## 🚀 Características Principales

### 🛒 Comercio Electrónico
*   **Catálogo Completo**: Gestión avanzada de suplementos, colecciones y marcas líderes (Optimum Nutrition, MyProtein, HSN, Prozis)
*   **Packs Personalizados**: Sistema de creación de packs personalizados con precios dinámicos
*   **Carrito de Compras Inteligente**: Carrito persistente con fusión automática de sesiones
*   **Sistema de Suscripciones**: AlphaBox - suscripción mensual con descuentos automáticos

### 💳 Pagos y Transacciones
*   **Integración Stripe**: Procesamiento seguro de pagos con modo test/live
*   **Gestión de Pedidos**: Seguimiento completo de pedidos con estados y confirmaciones por email
*   **Facturación**: Sistema de facturas automático con datos fiscales

### 👥 Gestión de Usuarios
*   **Autenticación Completa**: Registro, login, recuperación de contraseña
*   **Perfiles de Usuario**: Gestión de datos personales y historial de compras
*   **Sistema de Roles**: Usuarios normales y administradores

### 📱 Experiencia de Usuario
*   **PWA (Progressive Web App)**: Instalable en dispositivos móviles con service worker avanzado
*   **Diseño Responsivo**: Optimizado para móviles, tablets y desktop
*   **SEO Optimizado**: Meta tags dinámicos, sitemap automático, structured data
*   **Lazy Loading**: Carga diferida de imágenes para mejor performance

### 📝 Contenido y Comunicación
*   **Blog Integrado**: Sistema de publicaciones con categorías y SEO
*   **Newsletter**: Suscripción por email con gestión de campañas
*   **Chatbot**: Sistema de mensajes automatizado para soporte
*   **Reviews y Comentarios**: Sistema de reseñas para productos

### ⚙️ Panel Administrativo
*   **Dashboard Analytics**: Métricas de ventas, usuarios y productos
*   **Gestión de Productos**: CRUD completo de suplementos, packs y colecciones
*   **Gestión de Pedidos**: Visualización y modificación de pedidos
*   **Configuración del Sitio**: Ajustes globales, colores, logos, información de empresa
*   **Gestión de Usuarios**: Administración de usuarios y roles

## 🛠️ Tecnologías Utilizadas

### Backend
*   **PHP 7.4+**: Lenguaje principal con arquitectura MVC
*   **MySQL**: Base de datos relacional con consultas optimizadas

### Frontend
*   **HTML5/CSS3**: Estructura y estilos responsivos
*   **JavaScript (Vanilla)**: Interacciones dinámicas sin frameworks
*   **PWA**: Service Worker, Web App Manifest, cache inteligente

### APIs y Servicios Externos
*   **Stripe API**: Procesamiento de pagos con webhooks
*   **PHPMailer**: Envío de emails SMTP
*   **Google Fonts**: Tipografías optimizadas

### Herramientas de Desarrollo
*   **Git**: Control de versiones
*   **Sitemap Generator**: Generación automática de sitemaps
*   **SEO Optimization**: Meta tags dinámicos y optimización

## 📊 Estructura de la Base de Datos

### Tablas Principales
- **`supplements`**: Catálogo de productos con imágenes JSON, beneficios y precios
- **`packs`**: Packs predefinidos con items, precios y beneficios
- **`custom_packs`**: Packs creados por usuarios con tokens de compartir
- **`users`**: Usuarios con carritos persistentes y roles
- **`orders`**: Pedidos con integración Stripe y estados
- **`collections`**: Categorización de productos
- **`brands`**: Marcas de suplementos
- **`posts`**: Sistema de blog
- **`reviews`**: Comentarios y calificaciones de productos
- **`subscriptions`**: Sistema de suscripciones AlphaBox
- **`newsletter_subscribers`**: Gestión de newsletter
- **`settings`**: Configuración global del sitio

### Características de BD
- **JSON Fields**: Almacenamiento de arrays (imágenes, beneficios) en JSON
- **Foreign Keys**: Relaciones normalizadas entre tablas
- **Indexes**: Optimización de consultas frecuentes
- **UTF8MB4**: Soporte completo para caracteres especiales

## 📂 Estructura del Proyecto

```
AlphaSupps/
├── admin/                 # Panel administrativo
├── api/                   # Endpoints REST/JSON
├── components/            # Componentes reutilizables
├── config/                # Configuración del sistema
├── controllers/           # Lógica de negocio (MVC)
├── css/                   # Estilos CSS modulares
├── images/                # Recursos gráficos
├── js/                    # JavaScript vanilla (incl. sw.js)
├── models/                # Modelos de datos (MVC)
├── phpMailer/             # Librería de emails
├── views/                 # Vistas públicas (MVC)
├── sitemap/               # Sitemap SEO (XML, XSL y generador)
├── manifest.json          # PWA Manifest
└── README.md
```

### Arquitectura MVC
- **Models**: Interacción pura con base de datos
- **Views**: Plantillas HTML/PHP con datos dinámicos
- **Controllers**: Lógica de negocio y preparación de datos

## 🔌 APIs Disponibles

### Endpoints Principales
- **`GET /api/cart.php`**: Gestión del carrito de compras
- **`POST /api/test-session.php`**: Testing de sesiones de usuario
- **`POST /api/generate-image.php`**: Generación de imágenes vía ChatGPT (solo admin, requiere prompt)

### Funcionalidades API
- **JSON Responses**: Todas las respuestas en formato JSON
- **CORS**: Configuración para desarrollo local
- **Error Handling**: Manejo de errores consistente

### Generación de imágenes (ChatGPT)
- Endpoint: `POST /api/generate-image.php` (requiere sesión admin).
- El prompt final se arma con `AI_IMAGE_PROMPT_PREFIX` del `.env` y se puede sobreescribir en cada request con `prompt_prefix` o añadir texto extra con `prompt_suffix`.
- Variaciones: por defecto se añade un token aleatorio para evitar siempre la misma imagen. Fija un seed con `seed` para repetir resultado o desactiva con `randomize:false` o `AI_IMAGE_AUTO_VARIATION=false`.
- Opciones útiles: `size`, `style` (`vivid|natural`), `quality` (`standard|hd`), `seed`, `randomize`, `n` (1-4), `response_format` (`url|b64_json`).
- Ejemplo rápido:
  ```bash
  curl -X POST http://localhost/AlphaSupps/api/generate-image.php \
    -H "Content-Type: application/json" \
    -b "PHPSESSID=TU_SESION" \
    -d '{
      "prompt": "shake de proteína en fondo oscuro, iluminación dramática",
      "size": "1024x1024",
      "style": "vivid",
      "seed": "demo123"
    }'
  ```

## ⚙️ Instalación y Configuración

### Prerrequisitos
- **PHP 7.4+** con extensiones: `mysqli`, `json`, `mbstring`
- **MySQL 5.7+** o **MariaDB 10.0+**
- **Apache/Nginx** o servidor web compatible

### Instalación Paso a Paso

1. **Clonar el Repositorio**
   ```bash
   git clone https://github.com/N1C0SM/AlphaSupps.git
   cd AlphaSupps
   ```

2. **Configurar Base de Datos**
   ```bash
   # Crear base de datos
   mysql -u root -p
   CREATE DATABASE alphasupps_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   EXIT;

   # Importar esquema
   mysql -u root -p alphasupps_db < config/bd.sql
   ```

3. **Configurar Variables de Entorno**
   Crea el archivo `.env` en la raíz:
   ```env
   # Base de Datos
   DB_HOST=localhost
   DB_USER=tu_usuario_mysql
   DB_PASS=tu_password_mysql
   DB_NAME=alphasupps_db
   DB_PORT=3306

   # Stripe (Pagos)
   STRIPE_PUBLIC_KEY_TEST=pk_test_tu_clave_publica_test
   STRIPE_SECRET_KEY_TEST=sk_test_tu_clave_secreta_test
   STRIPE_PUBLIC_KEY_LIVE=pk_live_tu_clave_publica_live
   STRIPE_SECRET_KEY_LIVE=sk_live_tu_clave_secreta_live

   # Configuración SMTP
   SMTP_HOST=smtp.gmail.com
   SMTP_USER=tu_email@gmail.com
   SMTP_PASS=tu_password_aplicacion
   SMTP_PORT=587

   # Información de Empresa
   USER_WEBSITE=https://tu-dominio.com/
   EMISOR_NAME=Tu Empresa
   EMISOR_NIF=B12345678
   EMISOR_ADDRESS=Calle Ejemplo 123, Ciudad
   EMISOR_PHONE=+34 600 123 456
   EMISOR_EMAIL=info@tu-empresa.com

   # Configuración del Sitio
   DEFAULT_SITE_NAME=AlphaSupps
   DEFAULT_CONTACT_EMAIL=contact@alphasupps.com
   DEFAULT_ACCENT_COLOR=#e0b94d
   DEFAULT_LOGO_URL=/images/logo.png
   DEFAULT_META_DESCRIPTION=Tienda de suplementos premium con calidad y ciencia.

   # Debug (false en producción)
   APP_DEBUG=true

   # IA (imágenes con ChatGPT)
   OPENAI_API_KEY=tu_api_key_openai
   OPENAI_API_BASE=https://api.openai.com/v1
   OPENAI_IMAGE_MODEL=gpt-image-1
   OPENAI_IMAGE_SIZE=1024x1024
   AI_IMAGE_PROMPT_PREFIX=Imagen lifestyle premium para suplementos deportivos:
   AI_IMAGE_AUTO_VARIATION=true
   ```

4. **Configurar Servidor Web**
   - **Apache**: Asegúrate de que `mod_rewrite` esté habilitado
   - **Nginx**: Configura rewrites para URLs amigables
   - Apunta el document root a la carpeta del proyecto

5. **Acceder al sitio**
   Si sirves el proyecto bajo `/AlphaSupps`, abre `http://localhost/AlphaSupps/views/index.php`. El panel está en `http://localhost/AlphaSupps/admin/dashboard.php` y requiere una cuenta con permisos. Adapta las rutas a tu servidor.

   Revisa `config/bd.sql` antes de importarlo en una base existente. El repositorio no incluye el antiguo script `migrate_packs.php`.

## 🚀 Despliegue en Producción

### Configuración Adicional
1. **SSL Certificate**: Obligatorio para pagos con Stripe
2. **Variables de Entorno**: Configurar `APP_DEBUG=false`
3. **Stripe Webhooks**: Configurar URLs de webhook para confirmaciones
4. **Backup Automático**: Configurar backups de base de datos
5. **CDN**: Considerar Cloudflare para recursos estáticos

### Optimizaciones de Performance
- **OPcache**: Habilitar en PHP para mejor rendimiento
- **Gzip Compression**: Comprimir respuestas
- **Browser Caching**: Headers apropiados para recursos estáticos
- **Database Indexing**: Verificar índices en tablas grandes

## 📈 Características Avanzadas

### Sistema PWA
- **Instalación**: Manifest completo con iconos y shortcuts
- **Offline**: Service worker con estrategias de cache inteligente
- **Push Notifications**: Preparado para notificaciones (extensible)

### Sistema de Packs
- **Packs Predefinidos**: Productos agrupados con descuentos
- **Packs Personalizados**: Creación por usuarios con precios calculados
- **Sistema de Compartir**: Tokens únicos para compartir packs

### AlphaBox (Suscripciones)
- **Suscripciones Mensuales**: Renovación automática con Stripe
- **Descuentos**: Precios reducidos para suscriptores
- **Gestión de Estados**: Activo, cancelado, expirado

### SEO y Marketing
- **Meta Tags Dinámicos**: Títulos y descripciones por página
- **Open Graph**: Compartir en redes sociales
- **Sitemap Automático**: Indexación completa
- **Schema.org**: Datos estructurados para buscadores

## 🐛 Solución de Problemas

### Problemas Comunes
1. **Error de Conexión DB**: Verificar credenciales en `.env`
2. **Stripe No Funciona**: Verificar modo test/live y claves API
3. **Emails No Se Envían**: Verificar configuración SMTP
4. **PWA No Se Instala**: Verificar HTTPS y manifest.json

### Logs y Debug
- Configurar `APP_DEBUG=true` para ver errores detallados
- Revisar logs del servidor web
- Verificar consola del navegador para errores JavaScript

## 🤝 Contribución

1. Fork el proyecto
2. Crear rama para feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

## 📝 Licencia

El README original indicaba MIT, pero no hay un archivo de licencia en el repositorio que permita verificar sus términos. Pendiente de formalización por el titular.

## 📞 Contacto

- **Email**: info@alphasupps.com
- **Sitio Web**: [https://alphasupps.alwaysdata.net](https://alphasupps.alwaysdata.net)
- **GitHub**: [https://github.com/N1C0SM/AlphaSupps](https://github.com/N1C0SM/AlphaSupps)

---

**Desarrollado con ❤️ para la comunidad fitness**
