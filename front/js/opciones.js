function showSection(id) {
    ['profile', 'password', 'orders'].forEach(s =>
        document.getElementById(s).classList.add('hidden')
    );
    document.getElementById(id).classList.remove('hidden');
}

async function loadOrders() {
    showSection('orders');

    let data = [];
    try {
        const res = await fetch('../back/hist_pedidos.php', { credentials: 'same-origin' });
        const text = await res.text();
        try { data = JSON.parse(text); } catch (e) {
            console.error('hist_pedidos did not return JSON:', text);
            document.getElementById('ordersList').innerHTML = '<p>Error al obtener pedidos.</p>';
            return;
        }
    } catch (e) {
        console.error('Failed fetching hist_pedidos', e);
        document.getElementById('ordersList').innerHTML = '<p>Error de red.</p>';
        return;
    }

    const cont = document.getElementById('ordersList');
    cont.innerHTML = '';

    if (!data.length) {
        cont.innerHTML = '<p>No tienes pedidos.</p>';
        return;
    }

    data.forEach(p => {
        cont.innerHTML += `
            <div class="border rounded p-4">
                <p><strong>Pedido #${p.id}</strong></p>
                <p>Fecha: ${p.fecha}</p>
                <p>Total: ${p.total} €</p>
            </div>
        `;
    });
}
