// =======================
// Carrito con localStorage
// =======================

function getCart() {
    try {
        return JSON.parse(localStorage.getItem('cart') || '[]');
    } catch {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function updateCartCount() {
    const cart = getCart();
    const total = cart.reduce((s, i) => s + (i.qty || 0), 0);
    const el = document.getElementById('cart-count');
    if (el) el.textContent = total;
}

function addToCart(item) {
    const cart = getCart();
    const existing = cart.find(i => i.id === item.id);
    if (existing) {
        existing.qty += item.qty;
    } else {
        cart.push(item);
    }
    saveCart(cart);
    updateCartCount();
}

function clearCart() {
    saveCart([]);
    updateCartCount();
    renderCartPage();
}

// =======================
// Render carrito
// =======================

function renderCartPage() {
    const container = document.getElementById('cart-items');
    if (!container) return;

    const cart = getCart().filter(p => typeof p.price === 'number' && !isNaN(p.price));
    saveCart(cart);
    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = '<p>El carrito está vacío.</p>';
        return;
    }

    let total = 0;
    const list = document.createElement('div');
    list.className = 'cart-list';

    cart.forEach((p, idx) => {
        total += p.price * p.qty;

        const row = document.createElement('div');
        row.className = 'cart-row';
        row.innerHTML = `
            <img src="${p.image || '../img/placeholder.png'}" width="64">
            <strong>${p.name}</strong>
            <span>${p.qty} × €${p.price.toFixed(2)}</span>
            <button class="cart-remove" data-idx="${idx}">Eliminar</button>
        `;
        list.appendChild(row);
    });

    const footer = document.createElement('div');
    footer.className = 'cart-footer';
    footer.innerHTML = `
        <p>Total: €${total.toFixed(2)}</p>
        <button id="clear-cart">Vaciar carrito</button>
        <button id="checkout">Realizar pedido</button>
    `;

    container.appendChild(list);
    container.appendChild(footer);

    // eliminar
    container.querySelectorAll('.cart-remove').forEach(btn => {
        btn.addEventListener('click', e => {
            const idx = Number(e.target.dataset.idx);
            const cart = getCart();
            cart.splice(idx, 1);
            saveCart(cart);
            renderCartPage();
            updateCartCount();
        });
    });

    document.getElementById('clear-cart')
        ?.addEventListener('click', clearCart);
}

// =======================
// Inicialización
// =======================

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.product-card');
            if (!card) return;

            const item = {
                id: card.dataset.productId,
                name: card.querySelector('.product-name')?.textContent.trim(),
                price: Number(card.dataset.price),
                image: card.querySelector('.product-img img')?.src,
                qty: 1
            };

            addToCart(item);

            btn.textContent = 'Añadido';
            setTimeout(() => btn.textContent = 'Añadir', 800);
        });
    });

    updateCartCount();
    renderCartPage();
});
