<h1>Panel de Administración</h1>
    <p>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></p>

    <h2>Usuarios</h2>
    <table>
        <thead><tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['user_id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td>
                    <form method="post" style="display:inline-block">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <input name="username" value="<?= htmlspecialchars($u['username']) ?>" required>
                        <select name="role">
                            <option value="cliente" <?= $u['role']==='cliente' ? 'selected' : '' ?>>cliente</option>
                            <option value="administrador" <?= $u['role']==='administrador' ? 'selected' : '' ?>>administrador</option>
                        </select>
                        <input type="password" name="password" placeholder="Nueva contraseña (opcional)">
                        <button type="submit">Actualizar</button>
                    </form>
                    <form method="post" style="display:inline-block" onsubmit="return confirm('Eliminar usuario?')">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Pedidos</h2>
    <table>
        <thead><tr><th>ID</th><th>Usuario</th><th>Total</th><th>Fecha</th><th>Ver</th></tr></thead>
        <tbody>
        <?php foreach ($pedidos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['username']) ?></td>
                <td><?= number_format($p['total'],2) ?></td>
                <td><?= $p['fecha'] ?></td>
                <td><a href="pedido_ver.php?id=<?= $p['id'] ?>">Ver items</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
