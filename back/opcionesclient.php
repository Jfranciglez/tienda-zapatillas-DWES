<?php
session_start();
// comprobar sesión usando el username/role que establecemos en iniciosesion.php
if (empty($_SESSION['username'])) {
    // redirigir al formulario de inicio de sesión del front
    header('Location: ../front/iniciosesion/index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi cuenta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto mt-10 bg-white rounded-lg shadow flex">

    <!-- SIDEBAR -->
    <aside class="w-64 border-r p-6">
        <h2 class="text-lg font-semibold mb-6">Mi cuenta</h2>

        <ul class="space-y-4 text-sm">
            <li>
                <button onclick="showSection('profile')" class="w-full text-left hover:font-semibold">
                    Perfil
                </button>
            </li>
            <li>
                <button onclick="showSection('password')" class="w-full text-left hover:font-semibold">
                    Cambiar contraseña
                </button>
            </li>
            <li>
                <button onclick="loadOrders()" class="w-full text-left hover:font-semibold">
                    Mis pedidos
                </button>
            </li>
            <li>
                <a href="../front/index.html" class="w-full text-left hover:font-semibold">Página Principal</a>
            </li>
             <li>
                <a href="../back/logout.php" class="text-red-500">Cerrar sesión</a>
            </li>
        </ul>
    </aside>

    <!-- CONTENIDO -->
    <main class="flex-1 p-8">

        <!-- PERFIL -->
        <section id="profile">
            <h3 class="text-xl font-semibold mb-4">Perfil</h3>
            <p><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
            <p><strong>Rol:</strong> <?= htmlspecialchars($_SESSION['role']) ?></p>
        </section>

        <!-- PASSWORD -->
        <section id="password" class="hidden">
            <h3 class="text-xl font-semibold mb-4">Cambiar contraseña</h3>

            <form id="passwordForm" class="space-y-4 max-w-sm">
                <input type="password" name="current" placeholder="Contraseña actual"
                       class="w-full border rounded px-3 py-2">

                <input type="password" name="new" placeholder="Nueva contraseña"
                       class="w-full border rounded px-3 py-2">

                <button class="bg-black text-white px-4 py-2 rounded">
                    Guardar
                </button>
            </form>
            <p id="passMsg" class="mt-3 text-sm"></p>
        </section>

        <!-- PEDIDOS -->
        <section id="orders" class="hidden">
            <h3 class="text-xl font-semibold mb-4">Mis pedidos</h3>
            <div id="ordersList" class="space-y-4"></div>
        </section>

        
    </main>
</div>

<script src="../front/js/opciones.js"></script>
</body>
</html> 