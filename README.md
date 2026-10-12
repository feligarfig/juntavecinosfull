# Junta de Vecinos Full (`juntavecinosfull`)

Sistema web integral desarrollado a medida para la administración y gestión eficiente de las operaciones de una junta de vecinos. Permite controlar socios, reservas de sedes, eventos comunitarios, inventario y registros financieros.

## 🚀 Características Principales

- **Gestión de Socios:** Registro, actualización y control de estado de los miembros de la comunidad.
- **Reservas de Sede:** Sistema de agenda para solicitar y aprobar espacios comunitarios.
- **Control de Finanzas y Pagos:** Seguimiento de movimientos financieros y cuotas sociales.
- **Módulo de Eventos:** Publicación, galería y administración de actividades vecinales.
- **Inventario:** Control de activos y recursos disponibles en la sede.
- **Generación de Reportes PDF:** Exportación de informes detallados mediante Dompdf.
- **Notificaciones por Correo:** Envío de alertas y avisos usando PHPMailer.

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP (con programación modular y control de sesiones seguro).
- **Base de Datos:** MySQL / MariaDB.
- **Frontend:** HTML5, CSS3, JavaScript.
- **Librerías / Dependencias:** 
  - [Dompdf](https://github.com/dompdf/dompdf) (Generación de PDFs)
  - [PHPMailer](https://github.com/PHPMailer/PHPMailer) (Gestión de correos)

## ⚙️ Instalación y Configuración Local

Si deseas probar este proyecto en tu entorno local (por ejemplo, con **Laragon** en Windows):

1. Clona este repositorio o descarga el código en tu directorio local (`C:\laragon\www\`).
2. Configura una base de datos MySQL e importa el archivo `socios.sql` incluido en la raíz del proyecto.
3. Configura los parámetros de conexión a tu base de datos en el archivo de conexión (`includes/db.php` o similar).
4. Inicia tu servidor local en Laragon y accede desde el navegador a `http://juntavecinosfull.test` (o la ruta configurada).

---
Desarrollado con dedicación por **Feli Figueroa** para potenciar la gestión comunitaria.
