<?php
session_start();
require_once __DIR__ . '/../database/conexion.php';
if (empty($_SESSION['username']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'administrador') {
    header('Location: ../front/index.html');
    exit;
}
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo "ID inválido"; exit; }

$stmt = mysqli_prepare($conexion, 'SELECT id, username, total, fecha FROM pedidos WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$pedido = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$items = [];
$stmt2 = mysqli_prepare($conexion, 'SELECT producto, precio, cantidad, subtotal FROM pedido_items WHERE pedido_id = ?');
mysqli_stmt_bind_param($stmt2, 'i', $id);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);
while ($r = mysqli_fetch_assoc($res2)) $items[] = $r;
mysqli_stmt_close($stmt2);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Pedido <?= $id ?></title>
<style>table{border-collapse:collapse}td,th{border:1px solid #ccc;padding:6px}</style>
</head>
<body>
<h1>Pedido #<?= $id ?></h1>
<?php if ($pedido): ?>
<p>Usuario: <?= htmlspecialchars($pedido['username']) ?> — Total: <?= number_format($pedido['total'],2) ?> — Fecha: <?= $pedido['fecha'] ?></p>
<table>
<thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
<tbody>
<?php foreach ($items as $it): ?>
<tr>
<td><?= htmlspecialchars($it['producto']) ?></td>
<td><?= number_format($it['precio'],2) ?></td>
<td><?= intval($it['cantidad']) ?></td>
<td><?= number_format($it['subtotal'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>Pedido no encontrado.</p>
<?php endif; ?>
<p><a href="admin.php">Volver al panel</a></p>
</body>
</html>