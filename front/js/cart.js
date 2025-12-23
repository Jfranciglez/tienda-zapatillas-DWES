// Script de carrito usando localStorage
function getCart(){
    try{ return JSON.parse(localStorage.getItem('cart')||'[]'); }catch(e){ return []; }
}
function saveCart(cart){ localStorage.setItem('cart', JSON.stringify(cart)); }
function updateCartCount(){
    const cart = getCart();
    const total = cart.reduce((s,i)=>s + (i.qty||0),0);
    const el = document.getElementById('cart-count');
    if(el) el.textContent = total;
}
function addToCart(item){
    const cart = getCart();
    const existing = cart.find(i=>i.id === item.id && i.name===item.name);
    if(existing){ existing.qty = (existing.qty||1) + (item.qty||1); }
    else { cart.push(Object.assign({qty:1}, item)); }
    saveCart(cart);
    updateCartCount();
}
function clearCart(){ saveCart([]); updateCartCount(); renderCartPage(); }

function renderCartPage(){
    const container = document.getElementById('cart-items');
    if(!container) return;
    const cart = getCart();
    container.innerHTML = '';
    if(cart.length===0){ container.innerHTML = '<p>El carrito está vacío.</p>'; return; }
    const list = document.createElement('div');
    list.className = 'cart-list';
    let total = 0;
    cart.forEach((p, idx)=>{
        const row = document.createElement('div');
        row.className = 'cart-row';
        row.innerHTML = `
            <img src="${p.image||'../img/placeholder.png'}" alt="${p.name}" style="width:64px;height:auto;margin-right:8px;">
            <strong>${p.name}</strong>
            <span style="margin-left:8px">${p.qty} × €${Number(p.price).toFixed(2)}</span>
            <button data-idx="${idx}" class="cart-remove" style="margin-left:12px">Eliminar</button>
        `;
        list.appendChild(row);
        total += (Number(p.price)||0) * (p.qty||1);
    });
    const footer = document.createElement('div');
    footer.className = 'cart-footer';
    footer.innerHTML = `<p>Total: €${total.toFixed(2)}</p>
        <button id="clear-cart">Vaciar carrito</button>
        <button id="checkout" style="margin-left:8px">Realizar pedido</button>`;
    container.appendChild(list);
    container.appendChild(footer);

    // attach remove handlers
    container.querySelectorAll('.cart-remove').forEach(btn=>{
        btn.addEventListener('click', e=>{
            const idx = Number(e.currentTarget.dataset.idx);
            const c = getCart(); c.splice(idx,1); saveCart(c); renderCartPage(); updateCartCount();
        });
    });
    const clearBtn = document.getElementById('clear-cart');
    if(clearBtn) clearBtn.addEventListener('click', ()=>{ clearCart(); });

    const checkoutBtn = document.getElementById('checkout');
    if (checkoutBtn) checkoutBtn.addEventListener('click', async ()=>{
        const cart = getCart();
        if (!cart || cart.length === 0) { alert('El carrito está vacío.'); return; }
        // enviar al servidor
        checkoutBtn.disabled = true; checkoutBtn.textContent = 'Enviando...';
        try {
            function apiPath(file){
                const p = window.location.pathname;
                const idx = p.indexOf('/front/');
                if (idx !== -1) return window.location.origin + p.slice(0, idx) + '/back/' + file;
                const proj = '/tienda-zapatillas-DWES';
                if (p.indexOf(proj) !== -1) return window.location.origin + proj + '/back/' + file;
                return window.location.origin + '/back/' + file;
            }
            const url = apiPath('ticket.php');
            console.log('POST', url);
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'checkout', cart })
            });
            console.log('response status', res.status);
            const text = await res.text();
            console.log('response text', text);
            let data;
            try { data = JSON.parse(text); } catch (e) { throw new Error('Respuesta no JSON: ' + text); }
            if (data.success) {
                clearCart();
                alert('Pedido guardado. ID: ' + (data.pedido_id || 'N/A'));
                // redirigir a la página principal del front para que el usuario continúe navegando
                (function(){
                    const p = window.location.pathname;
                    const idx = p.indexOf('/front/');
                    if (idx !== -1) {
                        window.location.href = window.location.origin + p.slice(0, idx) + '/front/index.html';
                        return;
                    }
                    const proj = '/tienda-zapatillas-DWES';
                    if (p.indexOf(proj) !== -1) {
                        window.location.href = window.location.origin + proj + '/front/index.html';
                        return;
                    }
                    window.location.href = window.location.origin + '/';
                })();
            } else {
                if (data.code === 'need_login') {
                    if (confirm(data.message + '\n¿Deseas iniciar sesión ahora?')) {
                        // redirige al login calculando la ruta relativa al proyecto
                        const loginUrl = (function(){ const p = window.location.pathname; const idx = p.indexOf('/front/'); if (idx!==-1) return window.location.origin + p.slice(0, idx) + '/front/iniciosesion/index.html'; return window.location.origin + '/tienda-zapatillas-DWES/front/iniciosesion/index.html'; })();
                        location.href = loginUrl;
                        return;
                    }
                }
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        } catch (err) {
            alert('Error de red al enviar el pedido.');
        } finally {
            checkoutBtn.disabled = false; checkoutBtn.textContent = 'Realizar pedido';
        }
    });
}

document.addEventListener('DOMContentLoaded', ()=>{
    // wire up add-to-cart buttons
    document.querySelectorAll('.add-to-cart').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const card = btn.closest('.product-card');
            if(!card) return;
            const name = card.querySelector('.product-name')?.textContent?.trim() || 'Producto';
            const priceText = card.querySelector('.price')?.textContent || '0';
            const price = Number(priceText.replace(/[^0-9.,]/g,'').replace(',','.')) || 0;
            const img = card.querySelector('.product-img')?.getAttribute('src') || '';
            const id = card.dataset.productId || name;
            addToCart({id, name, price, image: img, qty: 1});
            // feedback mínimo
            btn.textContent = 'Añadido';
            setTimeout(()=> btn.textContent = 'Añadir al carrito', 900);
        });
    });

    updateCartCount();
    renderCartPage();
   
});
