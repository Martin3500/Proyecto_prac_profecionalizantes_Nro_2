# 🏋️ Sistema de Gestión de Gimnasio (Sis_Gestion_GYM)
### Proyecto de Prácticas Profesionalizantes II — Tecnicatura Superior

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla%20ES6%2B-F7DF1E?logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-Modern%20Design%20System-1572B6?logo=css3&logoColor=white)
![Licencia](https://img.shields.io/badge/Estado-Aprobado%20para%20Producci%C3%B3n-success)

---

## 📌 1. Descripción General del Proyecto

**Sis_Gestion_GYM** es una aplicación web integral diseñada y desarrollada para la administración eficiente, control de socios, gestión financiera y control de acceso en tiempo real de un gimnasio o centro de entrenamiento deportivo.

El sistema fue concebido en el marco de las **Prácticas Profesionalizantes de Nivel Superior**, cumpliendo con los estándares de ingeniería de software: persistencia robusta, integridad referencial de base de datos, medidas de ciberseguridad, experiencia de usuario (UX/UI) moderna y generación de reportes contables para la toma de decisiones.

---

## 🚀 2. Características Principales

* **Control de Acceso Rápido por DNI (Recepción):** Validación instantánea en molinete/mostrador con semáforo de estado (Verde = Habilitado, Naranja = Por vencer, Rojo = Cuota vencida/Inactivo) y registro automático de presente diario con protección contra doble fichaje accidental.
* **Control Inteligente de Cuotas y Vencimientos:** Asignación automática de fecha de vencimiento (por defecto 30 días) y reactivación inmediata del cliente al registrar el pago.
* **Comprobantes y Recibos Oficiales Imprimibles:** Generación de recibos con detalle del plan, vigencia, método de pago y formato adaptado para impresión directa (`@media print` / PDF) o envío automático por WhatsApp.
* **Reportes en Formato CSV (Excel):** Exportación con codificación UTF-8 con BOM y delimitador `;` para apertura nativa e inmediata en Microsoft Excel de clientes, pagos y asistencias.
* **Menú Lateral con Carrusel Paginado:** Interfaz limpia que agrupa la navegación en bloques de 3 enlaces (*Operaciones*, *Gestión*, *Administración*) con botones de avance y detección automática de la página activa.
* **Dashboard Estadístico en Tiempo Real:** Métricas clave, gráfico de barras de facturación mensual (últimos 6 meses), accesos del día y tabla de alertas de vencimientos a 7 días.
* **Landing Page Pública Dinámica:** Presentación web institucional con disciplinas cargadas dinámicamente desde el panel administrativo y fallback estático ante bases vacías.
* **Paginación Real:** Navegación optimizada mediante `LIMIT` y `OFFSET` en todos los listados del panel, conservando filtros de búsqueda activos.

---

## 🛠️ 3. Arquitectura y Tecnologías

| Componente | Tecnología | Justificación Técnica |
| :--- | :--- | :--- |
| **Backend** | PHP 8.x (Nativo) | Alto rendimiento, portabilidad en servidores Apache y sin sobrecarga de dependencias externas innecesarias. |
| **Base de Datos** | MySQL / MariaDB (InnoDB) | Motor relacional con integridad referencial estricta (`FOREIGN KEY`), transacciones (`ACID`) y juego de caracteres `utf8mb4_unicode_ci`. |
| **Acceso a Datos** | PDO (PHP Data Objects) | Consultas preparadas parametrizadas que garantizan inmunidad contra ataques de Inyección SQL (SQLi). |
| **Frontend** | HTML5 Semántico + CSS3 Modular | Sistema de diseño oscuro (*Dark Mode / Glassmorphism*) con variables CSS nativas, sin librerías pesadas como Bootstrap. |
| **JavaScript** | Vanilla JS (ES6+) | Reloj en tiempo real, consumo de API de clima geolocalizada y control del carrusel de navegación del sidebar. |
| **Seguridad** | Bcrypt + Protección Fuerza Bruta | Hash de contraseñas con `password_hash()` / `password_verify()` y bloqueo temporal de 15 minutos tras 3 intentos fallidos consecutivos. |

---

## 📂 4. Estructura de Scripts SQL (Limpieza y Nomenclatura)

Los scripts de base de datos han sido ordenados de forma secuencial y limpia para su comprensión y ejecución:

```text
├── 01_crear_base_datos.sql      -> Script DDL: Crea la base de datos `Sis_Gestion_GYM` con charset utf8mb4.
├── 02_crear_tablas.sql          -> Script DDL: Estructura de tablas (clientes, estados, entrenadores, disciplinas, pagos, etc.).
├── 03_roles_y_usuarios.sql      -> Script DML: Catálogo de roles ('admin', 'staff') y usuarios predeterminados.
└── 04_datos_de_prueba.sql       -> Script DML: Semilla de datos (55 socios, pagos, asistencias y clases para testing).
```

> **Instalación Automática:** El sistema cuenta con detección inteligente en [`config.php`](config.php). Si la base de datos aún no existe o no tiene las tablas, se ejecutan automáticamente `02_crear_tablas.sql` y `03_roles_y_usuarios.sql` en la primera visita al sistema.

---

## 👥 5. Roles y Credenciales de Acceso por Defecto

El sistema implementa control de acceso basado en roles (**RBAC**):

| Rol | Usuario | Contraseña | Alcance y Permisos |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin` | `admin123` | **Control total:** Dashboard, Clientes, Pagos, Entrenadores, Disciplinas, Tarifas, Control de Acceso y Gestión de Usuarios/Cuentas. |
| **Personal (Staff)** | `staff` | `staff123` | **Operativo:** Dashboard, Control de Acceso, Clientes, Pagos, Entrenadores, Disciplinas y Tarifas (sin acceso al módulo de Usuarios). |

---

## 💻 6. Guía de Instalación y Puesta en Marcha

### Requisitos Previos
1. Tener instalado un entorno de servidor web local como **XAMPP**, **WampServer** o **Laragon**.
2. **PHP 8.0** o superior con extensiones `pdo_mysql` y `mbstring` habilitadas.
3. Servidor de base de datos **MySQL 5.7+** o **MariaDB 10.4+**.

### Paso 1: Ubicación del Código
Clonar o mover la carpeta del proyecto dentro del directorio raíz del servidor web:
* Para XAMPP en Windows: `C:\xampp\htdocs\Base_datos_proyecto_gestion_GYM`

### Paso 2: Iniciar Servicios
Abrir el **Panel de Control de XAMPP** e iniciar los módulos:
* **Apache** (Servidor Web)
* **MySQL** (Motor de Base de Datos)

### Paso 3: Configuración de Parámetros (Opcional)
En el archivo [`config.php`](config.php) se pueden ajustar las constantes de conexión y de contacto comercial:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'Sis_Gestion_GYM');
define('DB_USER', 'root');
define('DB_PASS', '');

// Datos del gimnasio
define('GYM_NOMBRE', 'Mi Gimnasio');
define('GYM_WHATSAPP', '5491100000000'); // Formato: código de país + número
```

### Paso 4: Inicialización de la Base de Datos

Tenés dos alternativas igualmente válidas:

* **Opción A (100% Automática - Recomendada):**  
  Abrí el navegador y accedé directamente a:
  `http://localhost/Base_datos_proyecto_gestion_GYM/`  
  El archivo `config.php` creará la base de datos `Sis_Gestion_GYM`, las tablas principales y los usuarios iniciales de manera desatendida.

* **Opción B (Manual vía phpMyAdmin o Consola MySQL):**  
  Ingresá a `http://localhost/phpmyadmin` e importá los scripts en el siguiente orden estricto:
  1. `01_crear_base_datos.sql`
  2. `02_crear_tablas.sql`
  3. `03_roles_y_usuarios.sql`
  4. *(Opcional)* `04_datos_de_prueba.sql` (Carga 55 clientes con diferentes estados para pruebas de paginación y vencimientos).

### Paso 5: Ingreso al Sistema
* **Página pública institucional:** `http://localhost/Base_datos_proyecto_gestion_GYM/index.php`
* **Acceso al panel administrativo:** `http://localhost/Base_datos_proyecto_gestion_GYM/login.php`

---

## 📊 7. Módulos del Sistema en Detalle

### 1. Panel de Control (Dashboard)
* **Métricas en tiempo real:** Cantidad de clientes totales, clientes activos, recaudación del mes en curso, cantidad de entrenadores y disciplinas activas.
* **Gráfico Financiero Semestral:** Gráfico de barras interactivo con tooltip de valores que compara la recaudación de los últimos 6 meses.
* **Alertas Tempranas:** Alerta de clientes con cuotas vencidas o con vencimiento en los próximos 7 días con botón de renovación directa.

### 2. Control de Acceso Rápido (`acceso.php`)
* Formulario con foco automático para tipeo numérico o lectura de código de barras/DNI.
* Semáforo de validación:
  * **Verde (Acceso Autorizado):** Ficha la asistencia en la base de datos, muestra plan y días restantes de membresía.
  * **Naranja (Advertencia de Vencimiento):** Ficha la asistencia y alerta que la cuota expira en menos de 5 días.
  * **Rojo (Acceso Denegado):** Informa motivo (cuota vencida o socio inactivo). Permite cobrar la cuota o registrar un ingreso excepcional supervisado.
* Historial en vivo de los socios que ingresaron en la jornada actual.

### 3. Administración de Socios / Clientes (`clientes.php`)
* Listado con paginación de 15 registros por página y buscador multi-criterio (DNI, Nombre, Apellido, Email, Estado).
* Visualización directa del estado de su última cuota (*Al día*, *Por vencer*, *Vencida*, *Sin pagos*).
* Alta, modificación, baja lógica preventiva y reactivación sin eliminación destructiva de datos históricos.
* Exportación de la nómina completa a formato CSV para Excel.

### 4. Cobranzas y Comprobantes (`pagos.php` y `pago_recibo.php`)
* Registro de cobro con selector de cliente (incluyendo clientes inactivos para reactivación automática inmediata).
* Selección de tarifa/plan con autocompletado del importe y selección del **Método de Pago** (*Efectivo*, *Transferencia*, *MercadoPago / QR*, *Débito*, *Crédito*).
* Establecimiento de fecha de vencimiento de la cuota.
* Visualización de comprobante individual numerado con diseño para impresión y botón de envío directo por WhatsApp.

### 5. Control de Asistencias (`asistencias_cliente.php`)
* Ficha histórica individual del socio.
* Contador exacto de presentes históricos y presentes registrados en el mes actual.
* Registro manual de presentes/ausencias y exportación del historial a CSV.

### 6. Entrenadores, Disciplinas y Tarifas
* Asignación de múltiples disciplinas por entrenador (relación N:M).
* Gestión de tarifas asociadas a profesores específicos.
* Las disciplinas cargadas nutren automáticamente la sección de servicios de la Landing Page pública.

---

## 🔒 8. Medidas de Seguridad Implementadas

1. **Inyección SQL (SQLi):** Todas las consultas con parámetros dinámicos se ejecutan mediante sentencias preparadas de PDO (`prepare()` y `execute()`).
2. **Cross-Site Scripting (XSS):** Todos los datos de salida provenientes de la base de datos o entradas de usuario se sanitizan mediante `htmlspecialchars()` con flags de codificación UTF-8.
3. **Fuerza Bruta en Autenticación:** Se implementó un limitador en `login.php` que cuenta los intentos fallidos. Al 3er intento erróneo, el acceso se bloquea por 15 minutos en la sesión.
4. **Protección de Sesiones:** Verificación obligatoria de sesión activa en `auth.php` y cabeceras HTTP anti-caché (`Cache-Control: no-cache, no-store, must-revalidate`) para evitar retrocesos de historial luego de cerrar sesión.
5. **Control de Autorización Jerárquica:** El acceso a la administración de usuarios del sistema está protegido por `auth_admin.php`, restringido exclusivamente al rol `admin`.

---

## 📑 9. Estructura de Directorios

```text
Base_datos_proyecto_gestion_GYM/
├── 01_crear_base_datos.sql          # Creación de base de datos
├── 02_crear_tablas.sql              # Definición de tablas y claves foráneas
├── 03_roles_y_usuarios.sql          # Catálogo de roles y usuarios iniciales
├── 04_datos_de_prueba.sql           # Carga masiva de datos para demostración
├── config.php                       # Configuración de BD, constantes y arranque de sesión
├── index.php                        # Landing page institucional pública
├── login.php                        # Formulario y lógica de autenticación con bloqueo
├── esquema_relaciones_tablas.txt    # Documentación técnica del modelo relacional
├── README.md                        # Documentación principal del proyecto
│
├── assets/
│   ├── css/
│   │   ├── base.css                 # Reset, variables globales y tipografías
│   │   ├── components.css           # Botones, alertas, formularios y paginación
│   │   ├── dashboard.css            # Gráficos de barras, métricas y widgets
│   │   ├── landing.css              # Hero, grilla de servicios y footer
│   │   ├── layout.css               # Sidebar con carrusel, topbar y diseño adaptable
│   │   ├── login.css                # Estilos del formulario de ingreso
│   │   └── style.css                # Importador unificado de hojas de estilo
│   └── img/
│       └── logo.svg                 # Isotipo vectorial del gimnasio
│
└── panel/
    ├── acceso.php                   # Terminal de control de acceso rápido por DNI
    ├── asistencias_cliente.php      # Ficha y registro de asistencias por socio
    ├── cambiar_password.php         # Cambio seguro de clave del usuario en sesión
    ├── cambiar_password_guardar.php # Procesamiento del cambio de contraseña
    ├── clientes.php                 # Listado y control de estados de socios
    ├── clientes_form.php            # Alta y edición de cliente
    ├── clientes_guardar.php         # Guardado y validación de clientes
    ├── clientes_baja.php            # Baja lógica / reactivación de clientes
    ├── dashboard.php                # Panel de control estadístico y financiero
    ├── disciplinas.php              # Catálogo de actividades deportivas
    ├── disciplinas_form.php         # Formulario de disciplinas
    ├── disciplinas_guardar.php      # Guardado de disciplinas
    ├── disciplinas_baja.php         # Validación de integridad y baja de disciplinas
    ├── entrenadores.php             # Nómina de profesores
    ├── entrenadores_form.php        # Formulario de entrenadores y asignación de disciplinas
    ├── entrenadores_guardar.php     # Guardado de profesores y tabla asociativa
    ├── entrenadores_baja.php        # Baja controlada de entrenadores
    ├── exportar_csv.php             # Generador de reportes en formato CSV
    ├── logout.php                   # Destrucción segura de sesión
    ├── pagos.php                    # Historial de cobranzas y vigencias
    ├── pagos_form.php               # Formulario de registro de cobro
    ├── pagos_guardar.php            # Transacción de cobro y reactivación automática
    ├── pago_recibo.php              # Comprobante oficial de pago imprimible
    ├── tarifas_entrenadores.php     # Tarifarios y honorarios por entrenador
    ├── usuarios.php                 # Administración de operadores del sistema (Solo Admin)
    ├── usuarios_form.php            # Formulario de usuarios y roles
    ├── usuarios_guardar.php         # Guardado de cuentas con hash seguro
    ├── usuarios_baja.php            # Desactivación / reactivación de usuarios
    └── includes/
        ├── auth.php                 # Middleware de protección de sesión general
        ├── auth_admin.php           # Middleware de protección para rol Administrador
        ├── layout_inicio.php        # Estructura superior HTML, topbar y sidebar con carrusel
        ├── layout_fin.php           # Cierre de contenedores HTML
        └── paginacion.php           # Helper reutilizable para paginación de consultas
```

---

## 🎓 10. Consideraciones para la Defensa Académica

Al exponer el proyecto ante el tribunal docente de Prácticas Profesionalizantes, se destacan los siguientes argumentos técnicos:

1. **¿Por qué bajas lógicas en lugar de físicas (`DELETE`) en clientes y usuarios?**  
   Para preservar la integridad histórica contable. Eliminar un socio borraría en cascada sus pagos y asistencias pasadas, falseando balances financieros.
2. **¿Por qué transacciones SQL (`beginTransaction` / `commit` / `rollBack`)?**  
   Garantizan la consistencia: al registrar un pago o vincular un entrenador con múltiples disciplinas, si alguna operación intermedia falla, la base de datos vuelve a su estado íntegro anterior sin dejar registros huérfanos.
3. **¿Por qué el control de acceso valida la última cuota y no solo el estado?**  
   Automatiza la labor del recepcionista: el sistema alerta de forma proactiva quién tiene la membresía vencida sin necesidad de chequear planillas manuales.
4. **¿Cómo se previene el ataque de fijación de sesión o robo de credenciales?**  
   Mediante almacenamiento de hashes irreversibles (`bcrypt`), regeneración de identificadores de sesión y destrucción total de cookies al cerrar sesión.

---

Desarrollado como proyecto integral para **Prácticas Profesionalizantes II**.

