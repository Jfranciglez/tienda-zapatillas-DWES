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

if ($accion === 'register') {
    $stmt = mysqli_prepare($conexion, 'SELECT id FROM usuarios WHERE username = ?');
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
        $user_id = mysqli_insert_id($conexion);
        $_SESSION['user_id'] = $user_id;
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Registrado y logueado', 'user_id' => $user_id]);
        exit;
    }
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Error al registrar']);
    exit;
}

if ($accion === 'login') {
    $stmt = mysqli_prepare($conexion, 'SELECT id, password, role FROM usuarios WHERE username = ?');
    mysqli_stmt_bind_param($stmt, 's', $usuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $hash, $dbrol);
    if (mysqli_stmt_fetch($stmt)) {
        if (password_verify($contrasena, $hash)) {
            $_SESSION['user_id'] = $id;
            mysqli_stmt_close($stmt);
            echo json_encode(['success' => true, 'message' => 'Login correcto', 'user_id' => $id, 'role' => $dbrol]);
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
