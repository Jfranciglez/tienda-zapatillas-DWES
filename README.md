# Tienda Zapatillas - DWES

Proyecto para el trabajo de enfoque DWES: tienda de zapatillas con frontend estático en `front/` y panel de administración en `back/`.

<img src="./front/css/img/Captura de pantalla 2026-01-09 145833.png" width ="400" heigth = "300">
<img src="./front/css/img/Captura de pantalla 2026-01-09 145852.png" width ="400" heigth = "300">
<img src="./front/css/img/Captura de pantalla 2026-01-09 145921.png" width ="400" heigth = "300">



Tecnologías usadas
- PHP (backend simple)
- MySQL / MariaDB
- HTML, CSS, JavaScript (frontend)
- Font Awesome (iconos)
- localStorage para favoritos, carrito

Estructura del proyecto 
- `front/` – Frontend estático (HTML, CSS, JS, imágenes)
- `back/` – Panel de administración en PHP
- `database/` – Conexión a la base de datos

Requisitos
- XAMPP (Apache + PHP + MySQL) o entorno equivalente

Instalación rápida
1. Copia la carpeta `tienda-zapatillas-DWES` dentro de la carpeta `htdocs` de XAMPP.
2. Configura la conexión a la base de datos en `database/conexion.php` (host, usuario, contraseña, base de datos).
3. Importa el esquema y datos iniciales (si tienes el SQL). Ejemplo mínimo:

```sql
CREATE DATABASE tienda;
USE tienda;
-- tabla usuarios
CREATE TABLE usuarios (
	user_id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(100) NOT NULL,
	password VARCHAR(255) NOT NULL,
	role VARCHAR(50) NOT NULL
);
-- tabla productos
CREATE TABLE productos (
	productos_id INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(255) NOT NULL,
	categoria VARCHAR(100),
	precio DECIMAL(10,2)
);
```

4. Inicia Apache y MySQL desde XAMPP.
5. Abre el frontend en el navegador: `http://localhost/tienda-zapatillas-DWES/front/index.html`
6. Panel administración: `http://localhost/tienda-zapatillas-DWES/back/admin.php` (requiere sesión de administrador).

Notas importantes
- Los activos (CSS, JS, imágenes) están en `front/`.
- Favoritos se guardan en `localStorage` bajo la clave `favorites` y ahora incluyen `name` e `img`.
- Si no ves estilos en `back/admin.php`, confirma que la ruta hacia `front/css/style.css` es correcta.
