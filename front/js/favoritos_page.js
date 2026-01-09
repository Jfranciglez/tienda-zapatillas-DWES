'use strict';
(function () {
  
  
  const LS_KEY = 'favorites';

  function loadFavorites() {
    try {
      const raw = localStorage.getItem(LS_KEY);
      const data = raw ? JSON.parse(raw) : {};
      console.log('[FavoritosPage] Loaded:', data);
      return data;
    } catch (e) {
      console.error('[FavoritosPage] Error loading:', e);
      return {};
    }
  }

  function removeFavorite(id) {
    const favs = loadFavorites();
    delete favs[id];
    try {
      localStorage.setItem(LS_KEY, JSON.stringify(favs));
      console.log('[FavoritosPage] Removed favorite:', id);
    } catch (e) {
      console.error('[FavoritosPage] Error saving:', e);
    }
    return favs;
  }

  function renderFavorites() {
    console.log('[FavoritosPage] Rendering...');
    const favorites = loadFavorites();
    const container = document.getElementById('favoritos-list');
    const noFavsMsg = document.getElementById('sin-favoritos');

    if (!container) {
      console.warn('[FavoritosPage] Container not found');
      return;
    }

    container.innerHTML = '';

    const entries = Object.entries(favorites);
    console.log('[FavoritosPage] Found ' + entries.length + ' favorites');
    
    if (entries.length === 0) {
      if (noFavsMsg) noFavsMsg.style.display = 'block';
      container.innerHTML = '<p style="text-align: center; padding: 40px;">No hay favoritos aún</p>';
      return;
    }

    if (noFavsMsg) noFavsMsg.style.display = 'none';

    entries.forEach(([id, val]) => {
      const card = document.createElement('article');
      card.className = 'product-card';
      card.dataset.productId = id;

      // support migrated format where value may be string or object
      const name = (typeof val === 'string') ? val : (val && val.name) ? val.name : id;
      const imgSrc = (val && val.img) ? val.img : '../css/img/placeholder.png';

      card.innerHTML = `
        <img src="${imgSrc}" alt="${name}" class="product-img">
        <h2 class="product-name">${name}</h2>
        <div class="product-meta">
          <button class="fav-btn favorited" type="button" aria-label="Eliminar de favoritos" data-fav-id="${id}">
            <i class="fas fa-heart"></i>
          </button>
        </div>
      `;

      const removeBtn = card.querySelector('[data-fav-id]');
      if (removeBtn) {
        removeBtn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          const idToRemove = this.getAttribute('data-fav-id');
          console.log('[FavoritosPage] Removing:', idToRemove);
          // remove from localStorage using same structure as favorites.js
          const favs = loadFavorites();
          delete favs[idToRemove];
          try {
            localStorage.setItem(LS_KEY, JSON.stringify(favs));
            window.dispatchEvent(new CustomEvent('favoritesUpdated', { detail: { id: idToRemove, data: favs } }));
          } catch (e) {
            console.error('[FavoritosPage] Error saving:', e);
          }
          renderFavorites();
        });
      }

      container.appendChild(card);
    });
  }

  function init() {
    console.log('[FavoritosPage] Initializing...');
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        renderFavorites();
      });
    } else {
      renderFavorites();
    }

    // escuchar eventos de actualización de favoritos
    window.addEventListener('favoritesUpdated', function () {
      console.log('[FavoritosPage] Favorites updated, re-rendering...');
      renderFavorites();
    });
  }

  window._favoritos_page = {
    render: renderFavorites,
    load: loadFavorites,
    remove: removeFavorite
  };

  init();

})();
