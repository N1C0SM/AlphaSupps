/**
 * Sistema Avanzado de Lazy Loading + Preloading - AlphaSupps
 * Optimizado para Core Web Vitals y rendimiento extremo
 */

// Intersection Observer con mejor configuración
const imageObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      loadImage(entry.target);
      observer.unobserve(entry.target);
    }
  });
}, {
  rootMargin: '100px 50px', // Carga antes de que sea visible
  threshold: 0.1
});

// Función optimizada para cargar imágenes
function loadImage(img) {
  const src = img.dataset.src;
  if (!src) return;

  // Crear nueva imagen para preload
  const newImg = new Image();

  newImg.onload = () => {
    // Una vez cargada, reemplazar la imagen lazy
    img.src = src;
    img.classList.remove('lazy', 'loading');
    img.classList.add('loaded');

    // Trigger para animaciones
    img.dispatchEvent(new CustomEvent('imageLoaded'));
  };

  newImg.onerror = () => {
    // Fallback a imagen por defecto
    img.src = img.dataset.fallback || '/images/default.png';
    img.classList.remove('lazy', 'loading');
    img.classList.add('error');
  };

  // Mostrar estado de carga
  img.classList.add('loading');

  // Iniciar carga
  newImg.src = src;
}

// Función para inicializar lazy loading
function initLazyLoading() {
  const lazyImages = document.querySelectorAll('img[data-src]');

  lazyImages.forEach(img => {
    imageObserver.observe(img);
  });
}

// Sistema inteligente de preloading
function preloadCriticalImages() {
  // Preload imágenes críticas (hero, primeros productos)
  const criticalSelectors = [
    '.hero img',
    '.product-gallery img',
    '.featured-product img',
    'img[data-critical]'
  ];

  criticalSelectors.forEach(selector => {
    const images = document.querySelectorAll(selector);
    images.forEach(img => {
      const src = img.dataset.src || img.src;
      if (src) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = src;
        link.fetchPriority = 'high';
        document.head.appendChild(link);
      }
    });
  });
}

// Preloading predictivo basado en comportamiento del usuario
function setupPredictivePreloading() {
  let scrollDirection = 'down';
  let lastScrollY = window.scrollY;

  window.addEventListener('scroll', () => {
    const currentScrollY = window.scrollY;
    scrollDirection = currentScrollY > lastScrollY ? 'down' : 'up';
    lastScrollY = currentScrollY;

    // Preload imágenes en dirección del scroll
    if (scrollDirection === 'down') {
      preloadNextImages();
    }
  }, { passive: true });
}

function preloadNextImages() {
  // Encontrar las siguientes 3 imágenes lazy que no están visibles aún
  const lazyImages = Array.from(document.querySelectorAll('img[data-src].lazy'));
  const nextImages = lazyImages.slice(0, 3);

  nextImages.forEach(img => {
    const src = img.dataset.src;
    if (src && !img.hasAttribute('data-preloading')) {
      img.setAttribute('data-preloading', 'true');

      const link = document.createElement('link');
      link.rel = 'preload';
      link.as = 'image';
      link.href = src;
      link.fetchPriority = 'low';
      document.head.appendChild(link);
    }
  });
}

// Optimización de Core Web Vitals - Largest Contentful Paint
function optimizeLCP() {
  // Asegurar que el contenido principal se cargue rápido
  const heroContent = document.querySelector('.hero, .main-content');
  if (heroContent) {
    heroContent.style.contentVisibility = 'auto';
  }
}

// Optimización de Cumulative Layout Shift
function preventLayoutShift() {
  // Reservar espacio para imágenes lazy
  const lazyImages = document.querySelectorAll('img[data-src]');

  lazyImages.forEach(img => {
    if (img.dataset.width && img.dataset.height) {
      img.style.aspectRatio = `${img.dataset.width} / ${img.dataset.height}`;
    }
  });
}

// Inicializar todas las optimizaciones cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  preloadCriticalImages();
  preventLayoutShift();
  initLazyLoading();
  optimizeLCP();
  setupPredictivePreloading();

  // Performance monitoring
  if ('PerformanceObserver' in window) {
    setupPerformanceMonitoring();
  }
});

// Monitor de rendimiento
function setupPerformanceMonitoring() {
  // Largest Contentful Paint
  const lcpObserver = new PerformanceObserver((list) => {
    const entries = list.getEntries();
    const lastEntry = entries[entries.length - 1];
    console.log('LCP:', lastEntry.startTime);
  });
  lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });

  // Cumulative Layout Shift
  const clsObserver = new PerformanceObserver((list) => {
    let clsValue = 0;
    for (const entry of list.getEntries()) {
      if (!entry.hadRecentInput) {
        clsValue += entry.value;
      }
    }
    console.log('CLS:', clsValue);
  });
  clsObserver.observe({ entryTypes: ['layout-shift'] });
}

// Función para observar cambios en el DOM (útil para contenido dinámico)
function observeNewImages() {
  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      mutation.addedNodes.forEach(node => {
        if (node.nodeType === 1) { // Element node
          const newImages = node.querySelectorAll ? node.querySelectorAll('img[data-src]') : [];
          newImages.forEach(img => imageObserver.observe(img));
        }
      });
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
}

// Inicializar observer para imágenes dinámicas
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', observeNewImages);
} else {
  observeNewImages();
}
