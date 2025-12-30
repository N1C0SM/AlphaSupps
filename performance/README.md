# 🚀 AlphaSupps - Suite de Pruebas de Rendimiento

Esta carpeta contiene todas las herramientas de testing y diagnóstico para medir y verificar el rendimiento de tu sitio web AlphaSupps.

## 📁 Archivos de Testing

### ⚡ **Tests de Rendimiento**

#### `speed-test.php`
**Propósito:** Medir métricas de rendimiento generales
- ✅ Tamaños de archivos CSS/JS
- ✅ Variables CSS definidas
- ✅ Cache funcionando
- ✅ Recomendaciones de optimización

**Uso:** `http://localhost:8888/AlphaSupps/performance/speed-test.php`

#### `quick-test.php`
**Propósito:** Verificación rápida de archivos críticos
- ✅ Sintaxis PHP correcta
- ✅ Archivos principales existen
- ✅ Variables básicas definidas
- ✅ Estado general del proyecto

**Uso:** `http://localhost:8888/AlphaSupps/performance/quick-test.php`

### 🔧 **Tests de Funcionalidades**

#### `test-core-functions.php`
**Propósito:** Probar funcionalidades críticas del sistema
- 🗄️ Conexión a base de datos (opcional)
- 📦 Modelos de datos (solo si BD disponible)
- ⚙️ Configuración del sistema
- 🔍 SEO y páginas

**Uso:** `http://localhost:8888/AlphaSupps/performance/test-core-functions.php`

**Nota:** Los tests de base de datos se saltan automáticamente si MySQL no está disponible.

#### `test-functionality.php`
**Propósito:** Verificación completa de funcionalidades
- 📁 Sistema de archivos
- 💾 Sistema de cache
- 🖼️ Optimización de imágenes
- 🔧 Sintaxis de archivos

**Uso:** `http://localhost:8888/AlphaSupps/performance/test-functionality.php`

### 🔍 **Diagnóstico Especializado**

#### `diagnose-css-vars.php`
**Propósito:** Diagnosticar problemas con variables CSS
- 🎨 Variables PHP disponibles
- 📄 Uso de variables en archivos CSS
- 🎯 Definiciones en head.php
- 💡 Soluciones para problemas

**Uso:** `http://localhost:8888/AlphaSupps/performance/diagnose-css-vars.php`

#### `health-check.php`
**Propósito:** Verificación completa del estado del sistema
- 📁 Estructura de archivos completa
- 🔧 Sintaxis PHP de archivos críticos
- 🎨 Variables CSS configuradas
- ⚡ Optimizaciones implementadas
- 📊 Puntuación general del sistema

**Uso:** `http://localhost:8888/AlphaSupps/performance/health-check.php`

## 📊 Métricas Esperadas

### Rendimiento Óptimo
- **CSS Crítico:** < 20 KB ✅ (15.9 KB actual)
- **JS Crítico:** < 20 KB ✅ (14.4 KB actual)
- **Tiempo de carga:** < 2 segundos
- **Core Web Vitals:** > 90 Lighthouse

### Funcionalidades Críticas
- ✅ Base de datos conectada
- ✅ Archivos principales existen
- ✅ Variables CSS definidas
- ✅ Cache funcionando
- ✅ SEO configurado

## 🚀 Guía de Uso

### 1. Verificación Diaria
```bash
# Health check completo del sistema
http://localhost:8888/AlphaSupps/performance/health-check.php

# Test rápido alternativo
http://localhost:8888/AlphaSupps/performance/quick-test.php
```

### 2. Optimización de Rendimiento
```bash
# Métricas detalladas de velocidad
http://localhost:8888/AlphaSupps/performance/speed-test.php
```

### 3. Diagnóstico de Problemas
```bash
# Si hay problemas con CSS
http://localhost:8888/AlphaSupps/performance/diagnose-css-vars.php
```

### 4. Verificación Completa
```bash
# Test completo de funcionalidades
http://localhost:8888/AlphaSupps/performance/test-core-functions.php
http://localhost:8888/AlphaSupps/performance/test-functionality.php
```

## 🎯 Interpretación de Resultados

### ✅ VERDE - Todo Correcto
- Archivos existen y funcionan
- Variables definidas correctamente
- Rendimiento óptimo

### ⚠️ AMARILLO - Atención Menor
- Algunos archivos opcionales faltan
- Variables con valores por defecto

### ❌ ROJO - Acción Requerida
- Archivos críticos faltan
- Variables no definidas
- Errores de sintaxis

### 💾 BASE DE DATOS NO DISPONIBLE
Cuando MySQL/MAMP no está ejecutándose:
- ⚠️ Tests de BD muestran "Saltando test - Base de datos no disponible"
- ✅ Todos los demás tests funcionan normalmente
- 🎯 Enfócate en archivos, CSS y configuración

## 🔧 Mantenimiento

### Eliminación en Producción
```bash
# Antes de subir a producción, elimina esta carpeta
rm -rf /path/to/alphasupps/performance/
```

### Actualización de Tests
- Agregar nuevos tests cuando implementes nuevas funcionalidades
- Actualizar métricas esperadas según evolución del proyecto

## 📞 Soporte

Si encuentras problemas:
1. **Ejecuta health-check.php** para diagnóstico completo del sistema
2. Verifica que MAMP esté ejecutándose (requerido para tests de BD)
3. Revisa la configuración de base de datos en config.php
4. Confirma que todos los archivos principales existen
5. Ejecuta diagnose-css-vars.php si hay problemas visuales

### 📊 Códigos de Estado
- **🟢 VERDE (90-100)**: Sistema saludable, listo para producción
- **🟡 AMARILLO (70-89)**: Atención requerida, funcional pero optimizable
- **🔴 ROJO (<70)**: Acción inmediata requerida

---
**AlphaSupps Performance Suite v1.0** 🚀

