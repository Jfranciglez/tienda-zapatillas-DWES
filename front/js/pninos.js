 const productos = [
        {id:'ninos-01', name:'Zapatillas Baby', price:20.00, img:'../css/img/closeup-foto-de-zapatos-de-bebe-en-una-cama.jpg', desc:'Perfectas 0-3 meses', sizes:['17','18','19']},
        {id:'ninos-02', name:'Zapatillas Tiny', price:34.90, img:'../css/img/concepto-de-recien-nacido-con-dedos-sujetando-pequenos-zapatos.jpg', desc:'Cómodas 3-6 meses', sizes:['19','20','21']},
        {id:'ninos-03', name:'Zapatillas Play', price:29.50, img:'../css/img/nino-con-bicicleta-al-aire-libre.jpg', desc:'Para jugar al aire libre', sizes:['22','23','24']},
        {id:'ninos-04', name:'Zapatillas Soft', price:24.00, img:'../css/img/piernas-de-companeros-de-clase-sentados-en-el-parque-infantil.jpg', desc:'Suaves y ligeras', sizes:['20','21','22']}
    ];

    function renderGallery(){
        const g = document.getElementById('gallery');
        g.innerHTML = '';
        productos.forEach(p=>{
            const a = document.createElement('article');
            a.className='product-card';
            a.dataset.productId = p.id;
            a.innerHTML = `
                <img src="${p.img}" class="product-img" alt="${p.name}">
                <h4 class="product-name">${p.name}</h4>
                <p class="product-desc">${p.desc}</p>
                <div class="product-meta"><span class="price">€${p.price.toFixed(2)}</span>
                <button class="add-to-cart" type="button">Añadir</button></div>`;
            a.addEventListener('click', (e)=>{
                if (e.target && e.target.classList.contains('add-to-cart')) return; // dejar botón manejar
                openModal(p);
            });
            // botón añadir rápido
            a.querySelector('.add-to-cart').addEventListener('click', (ev)=>{
                ev.stopPropagation();
                addToCart({id:p.id, name:p.name, price:p.price, image:p.img, qty:1});
                updateCartCount();
                ev.currentTarget.textContent = 'Añadido';
                setTimeout(()=> ev.currentTarget.textContent = 'Añadir',800);
            });
            g.appendChild(a);
        });
    }

    function openModal(p){
        document.getElementById('modal-img').src = p.img;
        document.getElementById('modal-name').textContent = p.name;
        document.getElementById('modal-desc').textContent = p.desc;
        document.getElementById('modal-price').textContent = '€' + p.price.toFixed(2);
        const sz = document.getElementById('modal-size'); sz.innerHTML='';
        (p.sizes||[]).forEach(s=>{ const o=document.createElement('option'); o.value=s; o.textContent=s; sz.appendChild(o); });
        document.getElementById('modal-qty').value = 1;
        const modal = document.getElementById('product-modal'); modal.style.display='flex'; modal.setAttribute('aria-hidden','false');
        // attach add handler
        const addBtn = document.getElementById('modal-add');
        addBtn.onclick = function(){
            const qty = parseInt(document.getElementById('modal-qty').value) || 1;
            addToCart({id:p.id + '-' + (document.getElementById('modal-size').value||''), name: p.name + ' (Talla ' + (document.getElementById('modal-size').value||'') + ')', price: p.price, image: p.img, qty});
            updateCartCount();
            modal.style.display='none';
            modal.setAttribute('aria-hidden','true');
        };
    }

    document.addEventListener('DOMContentLoaded', ()=>{
        renderGallery();
        document.getElementById('close-modal').addEventListener('click', ()=>{ document.getElementById('product-modal').style.display='none'; document.getElementById('product-modal').setAttribute('aria-hidden','true'); });
        // cerrar al hacer click fuera del panel
        document.getElementById('product-modal').addEventListener('click', (e)=>{ if (e.target.id === 'product-modal') { e.currentTarget.style.display='none'; e.currentTarget.setAttribute('aria-hidden','true'); } });
    });