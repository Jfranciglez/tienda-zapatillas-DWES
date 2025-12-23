<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/conexion.php';

$accion = $_POST['action'] ?? '';
$usuario = trim($_POST['username'] ?? '');
$contrasena = $_POST['password'] ?? '';
$rol = $_POST['role'] ?? '';



if (!$conexion) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Si es una petición GET, devolver estado de sesión (si el usuario ya está logueado)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_SESSION['username'])) {
        echo json_encode(['success' => true, 'username' => $_SESSION['username'], 'role' => $_SESSION['role'] ?? null]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    }
    exit;
}

if ($accion === 'register') {
    $stmt = mysqli_prepare($conexion, 'SELECT username FROM usuarios WHERE username = ?');
    mysqli_stmt_bind_param($stmt, 's', $usuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
        exit;
    }
    mysqli_stmt_close($stmt);

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conexion, 'INSERT INTO usuarios (username, password, role) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sss', $usuario, $hash, $rol);
    $ok = mysqli_stmt_execute($stmt);
    if ($ok) {
        // la tabla `usuarios` puede no tener columna id; guardamos el username en sesión
        $_SESSION['username'] = $usuario;
        $_SESSION['role'] = $rol;
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Registrado y logueado', 'username' => $usuario, 'role' => $rol]);
        exit;
    }
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Error al registrar']);
    exit;
}

if ($accion === 'login') {
    // seleccionar password y role por username
    $stmt = mysqli_prepare($conexion, 'SELECT password, role FROM usuarios WHERE username = ?');
    mysqli_stmt_bind_param($stmt, 's', $usuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $hash, $dbrol);
    if (mysqli_stmt_fetch($stmt)) {
        // (logs temporales eliminados)

        if (password_verify($contrasena, $hash)) {
            // guardar username en la sesión
            $_SESSION['username'] = $usuario;
            $_SESSION['role'] = $dbrol;
            mysqli_stmt_close($stmt);
            echo json_encode(['success' => true, 'message' => 'Login correcto', 'username' => $usuario, 'role' => $dbrol]);
            exit;
        }
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        exit;
    }
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no válida']);
