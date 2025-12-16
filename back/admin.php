<?php
session_start(); //hace que sea accesible solo para admin y el resto los redirige a index.html

if (!isset($_SESSION['username']) || $_SESSION['username']['role'] !== 'administrador') {
    header("Location: index.html");
    exit;
}

// CRUD de usuarios solo para admin
echo "Bienvenido, administrador";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    

 <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Rol</th>
            <th>Usuario</th>
            <th>Contraseña</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <!--Aquí van las query de user y mas abajo el mismo crud  lo mismo pero con productos manteniedno odo en la misma pagina-->
        <?php
        require_once __DIR__ . '/../database/conexion.php';

        //CRUD de usuarios
        $query = "SELECT id, role, username, password FROM usuarios";
        $result = mysqli_query($conexion, $query);
      while ($registro = mysqli_fetch_array(result: $result)) {
       if (($accion == 'modificar') && ($id == $registro['id'])) {
                        //fila modificar
                        ?>
                        <tr class="fila-modificable">
                        <form action="#" method="post">
                            <td><input type="text" name="id" value="<?= $registro["id"] ?>"></td>
                            <td><input type="text" name="rol" value="<?= $registro["role"] ?>"></td>
                            <td><input type="text" name="usuario" value="<?= $registro["username"] ?>"></td>
                            <td><input type="text" name="contraseña" value="<?= $registro["password"] ?>"></td>

                            <td>
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="idAntiguo" value="<?= $registro["id"] ?>">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i>
                                    Aceptar
                                </button>
                    
                            </td>
                        </form>
                            <td>
                                <form action="#" method="post">
                                
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-lg"></i>
                                        Cancelar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    } else {
                        //fila normal
                       
                        ?>

                        <tr>
                            <td><?= $registro["id"] ?></td>
                            <td><?= $registro["role"] ?></td>
                            <td><?= $registro["username"] ?></td>
                            <td><?= $registro["password"] ?></td>
                            
                            <td>
                                <form action="#" method="post">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= $registro["id"] ?>">
                                    <button type="submit" class="btn btn-danger"
                                        <?= $accion == "modificar" ? "disabled" : ""?>>
                                        <i class="bi bi-trash"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form action="#" method="post">
                                    <input type="hidden" name="accion" value="modificar">
                                    <input type="hidden" name="id" value="<?= $registro["id"] ?>">
                                    <button type="submit" class="btn btn-primary"
                                        <?= $accion == "modificar" ? "disabled" : ""?>>
                                        <i class="bi bi-pencil"></i>
                                        Modificar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php

                    }

                }
                if ($accion != "modificar"){
                ?>
                <tr>
                    <form action="#" method="post">
                        <input type="hidden" name="accion" value="agregar">
                        <td><input name="id"></td>
                        <td><input name="role"></td>
                        <td><input name="username"></td>
                        <td><input name="password"></td>
                        <td>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus"></i>
                                Añadir
                            </button>
                        </td>
                </tr>
                <?php
                }
                ?>
                </form>
            </table>

            CRUD de productos
    
</body>
</html>