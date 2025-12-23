<?php
session_start();
require_once __DIR__ . '/../database/conexion.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (($input['action'] ?? '') !== 'checkout') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

if (empty($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

$cart = $input['cart'] ?? [];
if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit;
}

if (!$conexion) {
    echo json_encode(['success'=>false,'message'=>'Error de conexión a la base de datos']);
    exit;
}

$usuario = $_SESSION['username'];
$user_id = null;
$total = 0.0;
$items = [];
foreach ($cart as $it) {
    $cantidad = intval($it['qty'] ?? $it['cantidad'] ?? 1);
    $precio = floatval($it['price'] ?? $it['precio'] ?? 0);
    $nombre = $it['name'] ?? $it['producto'] ?? ($it['id'] ?? 'Producto');
    $subtotal = $precio * $cantidad;
    $total += $subtotal;
    $items[] = ['producto' => $nombre, 'precio' => $precio, 'cantidad' => $cantidad, 'subtotal' => $subtotal];
}

// crear tablas si no existen (schema sencillo)
$sql1 = "CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) DEFAULT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";
mysqli_query($conexion, $sql1);

/* si la tabla ya existía pero no tiene columna `username`, añadirla
$col = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'username'");
if ($col && mysqli_num_rows($col) === 0) {
    mysqli_query($conexion, "ALTER TABLE pedidos ADD COLUMN username VARCHAR(100) DEFAULT NULL");
}*/

$sql2 = "CREATE TABLE IF NOT EXISTS pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB";
mysqli_query($conexion, $sql2);

// insertar pedido (guardamos username)
$stmt = mysqli_prepare($conexion, 'INSERT INTO pedidos (username, total) VALUES (?, ?)');
mysqli_stmt_bind_param($stmt, 'sd', $usuario, $total);
if (!mysqli_stmt_execute($stmt)) { echo json_encode(['success'=>false,'message'=>'Error al crear pedido']); exit; }
$pedido_id = mysqli_insert_id($conexion);
mysqli_stmt_close($stmt);

// insertar items
$stmt = mysqli_prepare($conexion, 'INSERT INTO pedido_items (pedido_id, producto, precio, cantidad, subtotal) VALUES (?,?,?,?,?)');
foreach ($items as $it) {
    mysqli_stmt_bind_param($stmt, 'isidd', $pedido_id, $it['producto'], $it['precio'], $it['cantidad'], $it['subtotal']);
    mysqli_stmt_execute($stmt);
}
mysqli_stmt_close($stmt);

echo json_encode(['success'=>true,'message'=>'Pedido realizado correctamente','pedido_id'=>$pedido_id]);
