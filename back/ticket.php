<?php
session_start();
// Guarda el carrito en la base de datos y muestra el ticket si se solicita
require_once __DIR__ . '/../database/conexion.php';

// Helper: leer JSON del body si llega
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Si se pide ver un pedido por GET
if (isset($_GET['ver'])) {
    $id = (int) $_GET['ver'];
    $res = mysqli_query($conexion, "SELECT * FROM pedidos WHERE id = $id");
    $pedido = mysqli_fetch_assoc($res);
    if (!$pedido) { echo "Pedido no encontrado"; exit; }
    echo "<h2>Pedido #{$pedido['id']}</h2>";
    echo "<p>Fecha: {$pedido['fecha']}</p>";
    if (!empty($pedido['user_id'])) echo "<p>Usuario ID: {$pedido['user_id']}</p>";
    $items = mysqli_query($conexion, "SELECT * FROM pedido_items WHERE pedido_id = {$pedido['id']}");
    echo "<table border=1 cellpadding=5><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr>";
    while ($it = mysqli_fetch_assoc($items)) {
        echo "<tr><td>{$it['producto']}</td><td>€{$it['precio']}</td><td>{$it['cantidad']}</td><td>€{$it['subtotal']}</td></tr>";
    }
    echo "<tr><td colspan=3><strong>Total</strong></td><td><strong>€{$pedido['total']}</strong></td></tr></table>";
    exit;
}

// Si viene JSON con action=checkout
if ($data && ($data['action'] ?? '') === 'checkout') {
    // requerir sesión
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'code' => 'need_login', 'message' => 'Necesitas iniciar sesión para realizar pedidos']);
        exit;
    }

    $user_id = intval($_SESSION['user_id']);
    $cart = $data['cart'] ?? [];
    if (empty($cart)) { echo json_encode(['success'=>false,'message'=>'Carrito vacío']); exit; }

    // crear tablas si no existen (incluye user_id)
    $sql1 = "CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        total DECIMAL(10,2) NOT NULL,
        user_id INT DEFAULT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    mysqli_query($conexion, $sql1);
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

    // calcular total
    $total = 0;
    foreach ($cart as $p) { $precio = floatval($p['price'] ?? $p['precio'] ?? 0); $qty = intval($p['qty'] ?? $p['cantidad'] ?? 1); $total += $precio * $qty; }

    // insertar pedido incluyendo user_id
    $stmt = mysqli_prepare($conexion, 'INSERT INTO pedidos (total, user_id) VALUES (?, ?)');
    mysqli_stmt_bind_param($stmt, 'di', $total, $user_id);
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) { echo json_encode(['success'=>false,'message'=>'Error al crear pedido']); exit; }
    $pedido_id = mysqli_insert_id($conexion);
    mysqli_stmt_close($stmt);

    // insertar items
    $stmt = mysqli_prepare($conexion, 'INSERT INTO pedido_items (pedido_id, producto, precio, cantidad, subtotal) VALUES (?,?,?,?,?)');
    foreach ($cart as $p) {
        $producto = $p['name'] ?? $p['producto'] ?? 'Producto';
        $precio = floatval($p['price'] ?? $p['precio'] ?? 0);
        $cantidad = intval($p['qty'] ?? $p['cantidad'] ?? 1);
        $subtotal = $precio * $cantidad;
        mysqli_stmt_bind_param($stmt, 'isdid', $pedido_id, $producto, $precio, $cantidad, $subtotal);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    echo json_encode(['success'=>true,'message'=>'Pedido guardado','pedido_id'=>$pedido_id]);
    exit;
}

// Fallback: mostrar un formulario simple si se accede por POST tradicional
if (isset($_POST['productos'])) {
    $productos = $_POST['productos'];
    echo "<h2>Su pedido (via POST):</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Comida/Bebida</th><th>PVP</th><th>Cantidad</th><th>Subtotal</th></tr>";
    $total = 0;
    foreach ($productos as $nombre => $cantidad) {
        $cantidad = (int)$cantidad;
        if ($cantidad>0) {
            $precio = floatval(0); // precio desconocido en este modo
            $subtotal = $cantidad * $precio; $total += $subtotal;
            echo "<tr><td>$nombre</td><td>€$precio</td><td>$cantidad</td><td>€$subtotal</td></tr>";
        }
    }
    echo "<tr><td colspan='3'><strong>Total</strong></td><td><strong>€ $total</strong></td></tr>";
    echo "</table>";
}
