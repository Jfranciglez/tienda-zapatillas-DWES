"use strict";

document.addEventListener('DOMContentLoaded', function () {
    const msg = document.getElementById('message');

    async function send(action) {
        const roleInput = document.querySelector('input[name="role"]:checked');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        if (!roleInput || !usernameInput.value.trim() || !passwordInput.value.trim()) {
            msg.textContent = 'Introduce usuario, contraseña y rol.';
            msg.style.color = 'red';
            return;
        }

        const role = roleInput.value;
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        msg.textContent = 'Enviando...';
        msg.style.color = 'black';

        try {
            const res = await fetch('iniciosesion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action,
                    role,
                    username,
                    password
                })
            });

            const data = await res.json();

            msg.textContent = data.message;
            msg.style.color = data.success ? 'green' : 'red';

            if (data.success && action === 'login') {
                if (data.role === 'administrador') {
                    window.location.href = 'admin.php';
                } else {
                    // Cliente: se queda en index.html
                    msg.textContent = 'Bienvenido ' + username;
                    msg.style.color = 'green';

                    //  ocultar el formulario de login
                    // document.getElementById('login-form').style.display = 'none';
                }
            }

        } catch (error) {
            msg.textContent = 'Error de red. Intenta nuevamente.';
            msg.style.color = 'red';
        }
    }

    document.getElementById('btnLogin')
        .addEventListener('click', () => send('login'));

    document.getElementById('btnRegister')
        .addEventListener('click', () => send('register'));
});
