# Despliegue del Proyecto en Hostinger

Este documento describe cómo desplegar el sistema **Control de Gastos & Trazabilidad - Bitácora de Campo** en un hosting compartido de **Hostinger**.

---

# 1. Estructura del proyecto local

El proyecto está organizado de la siguiente manera en desarrollo local:

```
my-project
│
├── app
│   ├── config
│   │   └── database.php
│   ├── controllers
│   │   └── VisitaController.php
│   ├── models
│   │   └── Visita.php
│   └── routes
│       └── web.php
│
├── api
│   └── visitas.php
│
├── public_html
│   ├── index.php
│   │
│   ├── api
│   │   └── visitas.php
│   │
│   └── assets
│       ├── css
│       │   └── styles.css
│       ├── js
│       │   └── app.js
│       └── images
│
├── docker-compose.yml
└── Dockerfile
```

---

# 2. Archivos que NO se deben subir al hosting

Los siguientes archivos se utilizan únicamente para desarrollo local con Docker y **no se suben al hosting**:

```
docker-compose.yml
Dockerfile
```

---

# 3. Estructura que debe quedar en Hostinger

En el servidor de Hostinger el proyecto debe quedar organizado así:

```
/home/usuario
│
├── app
│
├── api
│
└── public_html
    ├── index.php
    │
    ├── api
    │   └── visitas.php
    │
    └── assets
        ├── css
        │   └── styles.css
        ├── js
        │   └── app.js
        └── images
```

IMPORTANTE:

Solo la carpeta **public_html** es accesible desde internet.

Las carpetas:

```
app
api
```

quedan protegidas fuera del directorio público.

---

# 4. Pasos para subir el proyecto

1. Ingresar al panel de **Hostinger**.
2. Ir a **File Manager** o conectarse vía **FTP (FileZilla)**.
3. Subir las carpetas del proyecto manteniendo la estructura:

```
app
api
public_html
```

4. Verificar que el archivo principal esté ubicado en:

```
public_html/index.php
```

---

# 5. Configuración de la base de datos

En Hostinger:

1. Ir a **Databases → MySQL Databases**
2. Crear una base de datos
3. Crear un usuario
4. Asignar el usuario a la base de datos

Luego editar el archivo:

```
app/config/database.php
```

Cambiar el entorno a:

```php
private $environment = 'hostinger';
```

Configurar los datos de conexión reales:

```php
private $hostinger_config = [
    'host'   => 'localhost',
    'dbname' => 'nombre_base_datos',
    'user'   => 'usuario_db',
    'pass'   => 'password_db'
];
```

---

# 6. Verificación del sistema

Abrir el navegador y acceder a:

```
https://tudominio.com
```

El sistema debería cargar correctamente.

La API estará disponible en:

```
https://tudominio.com/api/visitas.php
```

---

# 7. Recomendaciones de seguridad

Antes de poner el sistema en producción se recomienda:

1. Desactivar visualización de errores en PHP

```php
ini_set('display_errors', 0);
```

2. Utilizar contraseñas seguras para la base de datos.
3. Mantener permisos correctos en archivos y carpetas:

```
Archivos → 644
Carpetas → 755
```

---

# 8. Arquitectura del proyecto

El sistema utiliza una arquitectura **MVC simplificada en PHP**:

```
assets      → Frontend (JS / CSS)
api         → Endpoints REST
routes      → Router
controllers → Lógica de negocio
models      → Acceso a base de datos
```

Esta arquitectura permite mantener el código organizado, escalable y fácil de mantener.
