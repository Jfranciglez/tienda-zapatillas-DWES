<?php
session_start();
require_once __DIR__ . '/../database/conexion.php';

if ($_SESSION['role'] !== 'administrador') {
    header('Location: ../front/index.html');
    exit;
}

$accion = $_POST['accion'] ?? '';
$id = intval($_POST['id'] ?? 0);

/* =======================
   CRUD USUARIOS
======================= */

if ($accion === 'eliminar_usuario') {
    $stmt = mysqli_prepare($conexion, "DELETE FROM usuarios WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

if ($accion === 'agregar_usuario') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO usuarios (username, password, role) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'sss', $username, $password, $role);
    mysqli_stmt_execute($stmt);
}

if ($accion === 'editar_usuario') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    $stmt = mysqli_prepare(
        $conexion,
        "UPDATE usuarios SET username=?, role=? WHERE user_id=?"
    );
    mysqli_stmt_bind_param($stmt, 'ssi', $username, $role, $id);
    mysqli_stmt_execute($stmt);
}

/* =======================
   CRUD PRODUCTOS
======================= */

if ($accion === 'eliminar_producto') {
    $stmt = mysqli_prepare($conexion, "DELETE FROM productos WHERE productos_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

if ($accion === 'agregar_producto') {
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $precio = floatval($_POST['precio']);

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO productos (nombre, categoria, precio) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'ssd', $nombre, $categoria, $precio);
    mysqli_stmt_execute($stmt);
}

if ($accion === 'editar_producto') {
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $precio = floatval($_POST['precio']);

    $stmt = mysqli_prepare(
        $conexion,
        "UPDATE productos SET nombre=?, categoria=?, precio=? WHERE productos_id=?"
    );
    mysqli_stmt_bind_param($stmt, 'ssdi', $nombre, $categoria, $precio, $id);
    mysqli_stmt_execute($stmt);
}

/* evitar reenvío */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShoesRelife</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../front/css/style.css">
</head>

<body>
    <div id="content">
        <header>
            <img src="../front/css/img/v1057-logo-24.png" alt="Logo Shoesrelife">

            <nav class="navbar">
                <ul>
                    <li><a href="../front/index.html" title="Página Principal"><i class="fas fa-home"></i></a></li>
                    <li><a href="../front/iniciosesion/index.html" title="Inicia Sesión"><i class="fas fa-user"></i></a>
                    </li>
                    <li><a href="../front/favoritos/index.html" title="Favoritos"><i class="fas fa-heart"></i></a></li>
                    <li><a href="../front/carrito/index.html" title="Carrito"><i class="fas fa-shopping-cart"></i></a>
                        <span id="cart-count">0</span></li>
                </ul>
            </nav>

            <nav class="navcat">
                <ul id="menu">
                    <li><a href="../front/categorias/hombre.html">Hombre</a></li>
                    <li><a href="../front/categorias/mujer.html">Mujer</a></li>
                    <li><a href="../front/categorias/ninos.html">Niños</a></li>
                </ul>
            </nav>

            <div id="barrabuscar">
                <input type="text" placeholder="Buscar productos...">
                <button>Buscar</button>
            </div>
        </header>
        <main>

            <h2>Gestión de Usuarios</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>

                <?php
                $editUser = intval($_GET['edit_user'] ?? 0);
                $res = mysqli_query($conexion, "SELECT user_id, username, role FROM usuarios");

                while ($u = mysqli_fetch_assoc($res)) {
                    if ($editUser === intval($u['user_id'])) {
                        ?>
                        <tr>
                            <form method="post">
                                <td><?= $u['user_id'] ?></td>
                                <td><input name="username" value="<?= htmlspecialchars($u['username']) ?>"></td>
                                <td>
                                    <select name="role">
                                        <option value="cliente" <?= $u['role'] == 'cliente' ? 'selected' : '' ?>>cliente</option>
                                        <option value="administrador" <?= $u['role'] == 'administrador' ? 'selected' : '' ?>>
                                            administrador
                                        </option>
                                    </select>
                                </td>
                                <td>
                                        <input type="hidden" name="accion" value="editar_usuario">
                                        <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                        <button class="btn btn-save">Guardar</button>
                                        <a class="btn btn-cancel" href="admin.php">Cancelar</a>
                                </td>
                            </form>
                        </tr>
                    <?php } else { ?>
                        <tr>
                            <td><?= $u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= $u['role'] ?></td>
                            <td>
                                <a class="btn btn-edit" href="?edit_user=<?= $u['user_id'] ?>">Editar</a>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="accion" value="eliminar_usuario">
                                    <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                    <button class="btn btn-delete">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php }
                } ?>
                <form method="post">
                    <input name="username" placeholder="Usuario" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <select name="role">
                        <option value="cliente">cliente</option>
                        <option value="administrador">administrador</option>
                    </select>
                    <input type="hidden" name="accion" value="agregar_usuario">
                    <button class="btn btn-save">Añadir usuario</button>
                </form>
            </table>

            <h2>Gestión de Productos</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>

                <?php
                $editProd = intval($_GET['edit_prod'] ?? 0);
                $res = mysqli_query($conexion, "SELECT * FROM productos");

                while ($p = mysqli_fetch_assoc($res)) {
                    if ($editProd === intval($p['productos_id'])) {
                        ?>
                        <tr>
                            <form method="post">
                                <td><?= $p['productos_id'] ?></td>
                                <td><input name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>"></td>
                                <td><input name="categoria" value="<?= htmlspecialchars($p['categoria']) ?>"></td>
                                <td><input type="number" step="0.01" name="precio" value="<?= $p['precio'] ?>"></td>
                                <td>
                                        <input type="hidden" name="accion" value="editar_producto">
                                        <input type="hidden" name="id" value="<?= $p['productos_id'] ?>">
                                        <button class="btn btn-save">Guardar</button>
                                        <a class="btn btn-cancel" href="admin.php">Cancelar</a>
                                </td>
                            </form>
                        </tr>
                    <?php } else { ?>
                        <tr>
                            <td><?= $p['productos_id'] ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><?= htmlspecialchars($p['categoria']) ?></td>
                            <td>€<?= number_format($p['precio'], 2) ?></td>
                            <td>
                                <a class="btn btn-edit" href="?edit_prod=<?= $p['productos_id'] ?>">Editar</a>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="accion" value="eliminar_producto">
                                    <input type="hidden" name="id" value="<?= $p['productos_id'] ?>">
                                    <button class="btn btn-delete">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php }
                } ?>
                <form method="post">
                    <input name="nombre" placeholder="Nombre" required>
                    <input name="categoria" placeholder="Categoría" required>
                    <input type="number" step="0.01" name="precio" placeholder="Precio" required>
                    <input type="hidden" name="accion" value="agregar_producto">
                    <button class="btn btn-save">Añadir producto</button>
                </form>
            </table>
        </main>
    </div>
</body>
<footer>
    <div class="social-links">
        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" title="Facebook">
            <i class="fab fa-facebook"></i>
        </a>
        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" title="YouTube">
            <i class="fab fa-youtube"></i>
        </a>
        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" title="Instagram">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="https://x.com" target="_blank" rel="noopener noreferrer" title="X (Twitter)">
            <i class="fab fa-x-twitter"></i>
        </a>
        <button class="btn btn-outline-primary" id="atencioncliente">Atención al cliente</button>
    </div>
    <div class="footer-links">
        <a href="#">Política de privacidad</a>
        <a href="#">Política de cookies</a>
        <a href="#">Aviso legal</a>
        <a href="#">Condiciones de compra</a>
    </div>
</footer>
<script src="../front/js/cart.js"></script>

</html>