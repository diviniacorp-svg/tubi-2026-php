# TuBi 2026 - Sistema de Gestión de Bicicletas

Sistema de gestión para el programa "Tu Bicicleta San Luis" - Gobierno de San Luis, Argentina

## Descripción

TuBi es un sistema web desarrollado en PHP para la gestión integral del programa de entrega de bicicletas a estudiantes de San Luis. Permite gestionar el flujo completo desde el proveedor hasta el alumno, pasando por las escuelas.

## Características

- 🚲 Gestión completa de bicicletas (registro, armado, suministro, entrega)
- 👥 Múltiples roles: Alumno, Tutor, Escuela, Proveedor, Administrador
- 🤖 Asistente IA integrado (Google Gemini) contextualizado por rol
- 🎮 Sistema de gamificación para alumnos (retos, logros, módulos)
- 📊 Dashboards en tiempo real con estadísticas
- 🌓 Modo claro/oscuro en todos los paneles
- 📱 Diseño responsive para móvil

## Requisitos del Sistema

- PHP 7.4 o superior
- Servidor web Apache con mod_rewrite
- Sesiones PHP habilitadas

## Instalación

1. **Copiar archivos**
   ```bash
   # Descomprimir el ZIP en el directorio del servidor web
   unzip tubi-php.zip -d /var/www/html/tubi
   ```

2. **Configurar variables de entorno**
   ```bash
   # Copiar archivo de ejemplo
   cp .env.example .env

   # Editar y configurar variables
   nano .env
   ```

3. **Configurar BASE_URL**
   - Si está en subdirectorio: `BASE_URL=/tubi/`
   - Si está en raíz: `BASE_URL=/`
   - Editar en `config/config.php` línea 49

4. **Configurar API de Gemini** (opcional para chat IA)
   - Obtener API Key en: https://makersuite.google.com/app/apikey
   - Agregar en `.env`: `GEMINI_API_KEY=tu_clave_aqui`

5. **Verificar permisos**
   ```bash
   chmod 755 -R /var/www/html/tubi
   ```

## Credenciales de Demo

El sistema incluye usuarios de demostración para cada rol:

| Rol | Email | Password |
|-----|-------|----------|
| Alumno | alumno@tubi.com | demo123 |
| Tutor | tutor@tubi.com | demo123 |
| Escuela | escuela@tubi.com | demo123 |
| Proveedor | proveedor@tubi.com | demo123 |
| Administrador | admin@tubi.com | admin123 |
| Master (todos) | tubi | tubi2026 |

## Estructura del Proyecto

```
tubi-php/
├── config/
│   ├── config.php          # Configuración principal
│   └── data.php            # Sistema de datos en sesión
├── pages/
│   ├── admin/              # Dashboard administrador
│   ├── alumno/             # Dashboard alumno
│   ├── escuela/            # Dashboard escuela
│   ├── proveedor/          # Dashboard proveedor
│   └── tutor/              # Dashboard tutor
├── api/
│   └── chat.php            # Endpoint API de chat
├── services/
│   └── GeminiService.php   # Servicio de IA
├── includes/
│   ├── header.php          # Header común
│   ├── footer.php          # Footer común
│   └── tutorial.php        # Overlay de tutorial
├── assets/
│   ├── css/                # Estilos
│   ├── js/                 # JavaScript
│   └── img/                # Imágenes
├── login.php               # Pantalla de login
├── selector.php            # Selector de roles
├── logout.php              # Cerrar sesión
├── index.php               # Intro/Landing page
└── .htaccess               # Configuración Apache
```

## Uso del Sistema

### Flujo de Trabajo

1. **Proveedor**: Registra y arma bicicletas → Suministra a escuelas
2. **Escuela**: Recibe bicicletas → Asigna a alumnos
3. **Alumno**: Recibe bicicleta → Accede a módulos educativos
4. **Administrador**: Supervisa todo el proceso → Genera reportes
5. **Tutor**: Monitorea progreso de alumnos a cargo

### Cambiar Tema (Claro/Oscuro)

- Click en el botón sol/luna en la esquina superior derecha
- El tema se guarda en localStorage del navegador

### Usar Chat IA

- Click en el botón flotante de chat (esquina inferior derecha)
- Hacer preguntas contextualizadas según tu rol
- El asistente tiene conocimiento específico del programa TuBi

## Notas Importantes

⚠️ **Este es un sistema de DEMOSTRACIÓN**

- Los datos se almacenan en **sesión PHP** (no en base de datos)
- Los datos **NO persisten** al cerrar el navegador
- Para producción real, se debe implementar base de datos MySQL/PostgreSQL

## Migración a Base de Datos (Futuro)

El sistema está preparado para migrar a base de datos:

1. Las funciones CRUD están en `config/data.php`
2. Cambiar implementación de sesión a queries SQL
3. Configurar credenciales en `.env`
4. Crear tablas: bicicletas, alumnos, escuelas, proveedores, etc.

## Soporte

Para problemas o consultas:
- Email: soporte@tubi.gov.ar
- GitHub Issues: (configurar repositorio)

## Licencia

© 2026 Gobierno de San Luis - Todos los derechos reservados

---

**Desarrollado con ❤️ para el programa Tu Bicicleta San Luis**
