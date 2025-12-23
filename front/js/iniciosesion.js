"use strict";
console.log('iniciosesion.js loaded');

document.addEventListener('DOMContentLoaded', function () {
    const msg = document.getElementById('message');

    // construir ruta al backend a partir de la URL actual (reutilizable)
    function apiPath(file){
        const p = window.location.pathname;
        const idx = p.indexOf('/front/');
        if (idx !== -1) return window.location.origin + p.slice(0, idx) + '/back/' + file;
        // fallback: buscar carpeta del proyecto si existe
        const proj = '/tienda-zapatillas-DWES';
        if (p.indexOf(proj) !== -1) return window.location.origin + proj + '/back/' + file;
        return window.location.origin + '/back/' + file;
    }

    function showOnlyMessage(text){
        if (msg) { msg.textContent = text; msg.style.color = 'green'; }
        const form = document.querySelector('form');
        if (form) form.style.display = 'none';
        const btns = document.querySelectorAll('#btnLogin, #btnRegister');
        btns.forEach(b=> b.style.display = 'none');
        // mostrar sección de bienvenida si existe
        const welcome = document.getElementById('welcome-section');
        if (welcome) {
            welcome.style.display = '';
            const nameSpan = document.getElementById('user-name');
            if (nameSpan) nameSpan.textContent = text.replace(/^Bienvenido\s*/i, '');
            const existingOut = document.getElementById('btnLogout');
            if (existingOut) {
                existingOut.style.display = '';
                existingOut.removeEventListener && existingOut.addEventListener('click', async function(){
                    try{
                        const res = await fetch(apiPath('logout.php'), { method: 'GET', credentials: 'same-origin' });
                        const d = await res.json();
                        if (d.success) location.reload();
                    }catch(e){ location.reload(); }
                });
            } else {
                // crear boton si no existe
                const out = document.createElement('button');
                out.id = 'btnLogout';
                out.textContent = 'Cerrar sesión';
                out.style.marginLeft = '8px';
                welcome.appendChild(out);
                out.addEventListener('click', async function(){
                    try{
                        const res = await fetch(apiPath('logout.php'), { method: 'GET', credentials: 'same-origin' });
                        const d = await res.json();
                        if (d.success) location.reload();
                    }catch(e){ location.reload(); }
                });
            }
        } else {
            // fallback: añadir botón junto al mensaje
            if (!document.getElementById('btnLogout')) {
                const out = document.createElement('button');
                out.id = 'btnLogout';
                out.textContent = 'Cerrar sesión';
                out.style.marginLeft = '8px';
                if (msg && msg.parentNode) msg.parentNode.appendChild(out);
                out.addEventListener('click', async function(){
                    try{
                        const res = await fetch(apiPath('logout.php'), { method: 'GET', credentials: 'same-origin' });
                        const d = await res.json();
                        if (d.success) location.reload();
                    }catch(e){ location.reload(); }
                });
            }
        }
    }

    // comprobar si ya hay sesión activa al cargar
    (async function checkSession(){
        try{
            const url = apiPath('iniciosesion.php');
            const res = await fetch(url, { method: 'GET', credentials: 'same-origin' });
            const data = await res.json();
            if (data.success && data.username) showOnlyMessage('Bienvenido ' + data.username);
        }catch(e){ /* ignore */ }
    })();

    async function send(action) {
        const roleInput = document.querySelector('input[name="role"]:checked');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const btnLogin = document.getElementById('btnLogin');
        const btnRegister = document.getElementById('btnRegister');

        // validación
        if (!roleInput) { msg.textContent = 'Selecciona un rol.'; msg.style.color = 'red'; return; }
        if (!usernameInput.value.trim()) { msg.textContent = 'Introduce el usuario.'; msg.style.color = 'red'; usernameInput.focus(); return; }
        if (!passwordInput.value.trim()) { msg.textContent = 'Introduce la contraseña.'; msg.style.color = 'red'; passwordInput.focus(); return; }

        const role = roleInput.value;
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        // feedback + disable botones
        msg.textContent = 'Enviando...'; msg.style.color = 'black';
        if (btnLogin) btnLogin.disabled = true;
        if (btnRegister) btnRegister.disabled = true;

        try {
            const url = apiPath('iniciosesion.php');
            console.log('POST', url);
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action, role, username, password})
            });
            console.log('response status', res.status);
            const text = await res.text();
            console.log('response text', text);
            let data;
            try { data = JSON.parse(text); } catch (e) { throw new Error('Respuesta no JSON: ' + text); }
            msg.textContent = data.message || 'Respuesta inesperada';
            msg.style.color = data.success ? 'green' : 'red';

            if (!data.success) {
                // mostrar errores específicos
                if (data.code === 'need_login') {
                    msg.textContent = data.message || 'Necesitas iniciar sesión.';
                }
                return;
            }

            // éxito
            if (action === 'register') {
                // registrar -> ya iniciamos sesión en el backend
                showOnlyMessage('Registrado y conectado. Bienvenido ' + username);
                // si es administrador, redirigir al panel
                if (data.role === 'administrador') {
                    window.location.href = '/projectmedacphp/tienda-zapatillas-DWES/back/admin.php';
                    return;
                }
            }

            if (action === 'login') {
                if (data.role === 'administrador') {
                    window.location.href = '/projectmedacphp/tienda-zapatillas-DWES/back/admin.php';
                } else {
                    showOnlyMessage('Bienvenido ' + username);
                }
            }

        } catch (error) {
            msg.textContent = 'Error de red. Intenta nuevamente.'; msg.style.color = 'red';
        } finally {
            if (btnLogin) btnLogin.disabled = false;
            if (btnRegister) btnRegister.disabled = false;
        }
    }

    document.getElementById('btnLogin')
        .addEventListener('click', () => send('login'));

    document.getElementById('btnRegister')
        .addEventListener('click', () => send('register'));
});
