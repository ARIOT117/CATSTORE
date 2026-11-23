'use strict';
document.documentElement.setAttribute('data-js', '');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const menuButton = document.querySelector('.menu-movil');
const navigation = document.getElementById('navegacion');
function closeNavigation() {
  menuButton?.setAttribute('aria-expanded', 'false');
  menuButton?.setAttribute('aria-label', 'Abrir navegación');
  navigation?.classList.remove('abierto');
}
menuButton?.addEventListener('click', () => {
  const open = menuButton.getAttribute('aria-expanded') !== 'true';
  menuButton.setAttribute('aria-expanded', String(open));
  menuButton.setAttribute('aria-label', open ? 'Cerrar navegación' : 'Abrir navegación');
  navigation.classList.toggle('abierto', open);
});
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && menuButton?.getAttribute('aria-expanded') === 'true') {
    closeNavigation();
    menuButton.focus();
  }
});
navigation?.addEventListener('click', (event) => {
  if (event.target.closest('a')) closeNavigation();
});
matchMedia('(min-width:761px)').addEventListener('change', closeNavigation);
// En teléfono, tocar fuera del menú lo cierra sin bloquear el gesto del usuario.
document.addEventListener('pointerdown', (event) => {
  if (menuButton?.getAttribute('aria-expanded') === 'true' && !event.target.closest('.cabecera')) {
    closeNavigation();
  }
});

document.querySelectorAll('[data-ver-password]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.verPassword);
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.textContent = visible ? 'Ocultar' : 'Ver';
    button.setAttribute('aria-pressed', String(visible));
  });
});
const accountError = document.getElementById('error-cuenta');
if (accountError) {
  accountError.focus({ preventScroll: true });
  if (matchMedia('(max-width:760px)').matches) {
    accountError.scrollIntoView({
      block: 'center',
      behavior: reduceMotion.matches ? 'instant' : 'smooth',
    });
  }
}

const search = document.getElementById('buscador');
const filters = [...document.querySelectorAll('[data-filtro]')];
const cards = [...document.querySelectorAll('.producto[data-categoria]')];
const availableOnly = document.getElementById('solo-disponibles');
let category = 'Todos';
const normalize = (text) =>
  text
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase('es');
function filterProducts() {
  const query = normalize(search?.value.trim() || '');
  let count = 0;
  cards.forEach((card) => {
    const visible =
      (category === 'Todos' || card.dataset.categoria === category) &&
      normalize(card.dataset.busqueda).includes(query) &&
      (!availableOnly?.checked || Number(card.dataset.stock) > 0);
    card.hidden = !visible;
    if (visible) count++;
  });
  const result = document.getElementById('resultado-productos');
  if (result)
    result.textContent = `${count} ${count === 1 ? 'producto encontrado' : 'productos encontrados'}`;
  const empty = document.getElementById('sin-resultados');
  if (empty) empty.hidden = count !== 0;
}
search?.addEventListener('input', filterProducts);
availableOnly?.addEventListener('change', filterProducts);
filters.forEach((button) =>
  button.addEventListener('click', () => {
    category = button.dataset.filtro;
    filters.forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
    filterProducts();
  }),
);
document.getElementById('limpiar-filtros')?.addEventListener('click', () => {
  search.value = '';
  if (availableOnly) availableOnly.checked = false;
  filters[0]?.click();
  search.focus();
});

let notificationTimeout;
function notify(message) {
  const node = document.getElementById('notificacion');
  if (!node) return;
  node.textContent = message;
  node.classList.add('visible');
  clearTimeout(notificationTimeout);
  notificationTimeout = setTimeout(() => node.classList.remove('visible'), 6500);
}
document.querySelectorAll('[data-agregar]').forEach((form) =>
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('button');
    if (button.disabled) return;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });
      if (!(response.headers.get('content-type') || '').includes('application/json'))
        throw new Error(
          'No pudimos comprobar el cambio. Recarga la página y revisa tu carrito antes de intentar otra vez.',
        );
      const data = await response.json();
      if (!response.ok || !data.ok)
        throw new Error(data.mensaje || 'No pudimos guardar el cambio.');
      document.querySelectorAll('.cuenta-carrito').forEach((node) => {
        node.textContent = data.cantidad;
      });
      document
        .querySelector('.carrito-enlace')
        ?.setAttribute('aria-label', `Carrito, ${data.cantidad} artículos`);
      notify(data.mensaje);
    } catch (error) {
      notify(
        error.message === 'Failed to fetch'
          ? 'Se perdió la conexión. Revisa tu carrito antes de volver a agregar el producto.'
          : error.message,
      );
    } finally {
      button.disabled = false;
      button.removeAttribute('aria-busy');
    }
  }),
);
document.querySelectorAll('[data-confirmar]').forEach((form) =>
  form.addEventListener('submit', () => {
    const button = form.querySelector('button[type=submit]');
    button.disabled = true;
    button.textContent = 'Guardando tu pedido…';
  }),
);
window.addEventListener('pageshow', () =>
  document.querySelectorAll('[data-confirmar] button').forEach((button) => {
    button.disabled = false;
    button.textContent = 'Confirmar pedido demo →';
  }),
);

// Una imagen fallida nunca deja un recuadro roto o una acción inaccesible.
document.querySelectorAll('.producto-imagen img,.bebida-foto img').forEach((img) => {
  const fallback = () => {
    img.hidden = true;
    const label = img.parentElement.querySelector('.imagen-fallback');
    if (label) label.hidden = false;
    else {
      img.parentElement.classList.add('foto-no-disponible');
      img.parentElement.querySelector('span').textContent = 'Fotografía no disponible';
    }
  };
  img.addEventListener('error', fallback);
  if (img.complete && img.naturalWidth === 0) fallback();
});

// Se miden las barras al cambiar de tamaño; el scroll solo actualiza la selección.
const menuCategories = document.querySelector('.menu-categorias');
if (menuCategories) {
  const header = document.querySelector('.cabecera');
  const links = [...menuCategories.querySelectorAll('a[href^="#"]')];
  const groups = [...document.querySelectorAll('.menu-grupo')];
  let scrollOffset = 120;
  let scrollFrame = 0;
  let clickedUntil = 0;
  function measureBars() {
    const top = Math.ceil(header?.getBoundingClientRect().height || 0);
    scrollOffset =
      top + (matchMedia('(max-width:760px)').matches ? menuCategories.offsetHeight : 0) + 18;
    document.documentElement.style.setProperty('--menu-nav-top', `${top}px`);
    document.documentElement.style.setProperty('--menu-scroll-offset', `${scrollOffset}px`);
  }
  function activate(id) {
    for (const link of links) {
      const active = link.hash === `#${id}`;
      if (active) link.setAttribute('aria-current', 'true');
      else link.removeAttribute('aria-current');
      if (active && menuCategories.scrollWidth > menuCategories.clientWidth) {
        const left = link.offsetLeft - (menuCategories.clientWidth - link.offsetWidth) / 2;
        menuCategories.scrollTo({ left, behavior: 'instant' });
      }
    }
  }
  function updateCategory() {
    scrollFrame = 0;
    if (performance.now() < clickedUntil) return;
    let current = 'todo-menu';
    for (const group of groups)
      if (group.getBoundingClientRect().top <= scrollOffset + 70) current = group.id;
    activate(current);
  }
  for (const link of links)
    link.addEventListener('click', (event) => {
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      const target = document.getElementById(link.hash.slice(1));
      if (!target) return;
      event.preventDefault();
      measureBars();
      history.pushState(null, '', link.hash);
      clickedUntil = performance.now() + (reduceMotion.matches ? 0 : 900);
      target.scrollIntoView({
        behavior: reduceMotion.matches ? 'instant' : 'smooth',
        block: 'start',
      });
      if (event.detail === 0) {
        target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
      }
      activate(target.id);
    });
  window.addEventListener(
    'scroll',
    () => {
      if (!scrollFrame) scrollFrame = requestAnimationFrame(updateCategory);
    },
    { passive: true },
  );
  window.addEventListener('scrollend', () => {
    clickedUntil = 0;
    updateCategory();
  });
  window.addEventListener('hashchange', () => {
    clickedUntil = 0;
    updateCategory();
  });
  const resize = new ResizeObserver(measureBars);
  resize.observe(header);
  resize.observe(menuCategories);
  measureBars();
  const legacy = new URLSearchParams(location.search).get('categoria');
  const initial = location.hash.slice(1) || legacy;
  if (initial && groups.some((group) => group.id === initial)) {
    requestAnimationFrame(() => {
      measureBars();
      document.getElementById(initial).scrollIntoView({ behavior: 'instant', block: 'start' });
      activate(initial);
    });
  } else updateCategory();
}

// Solo se animan tarjetas editoriales; menú y formularios están siempre visibles.
if ('IntersectionObserver' in window && !reduceMotion.matches) {
  const reveal = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.dataset.revelar = 'visible';
          reveal.unobserve(entry.target);
        }
      });
    },
    { rootMargin: '0px 0px 80px 0px', threshold: 0 },
  );
  document
    .querySelectorAll('.favorito,.experiencia-tarjetas article,.convivencia-grid article')
    .forEach((node) => {
      if (node.getBoundingClientRect().top > innerHeight) {
        node.dataset.revelar = 'pendiente';
        reveal.observe(node);
      }
    });
  reduceMotion.addEventListener('change', (event) => {
    if (event.matches) {
      reveal.disconnect();
      document.querySelectorAll('[data-revelar]').forEach((node) => {
        node.dataset.revelar = 'visible';
      });
    }
  });
  window.addEventListener('beforeprint', () => {
    document.querySelectorAll('[data-revelar]').forEach((node) => {
      node.dataset.revelar = 'visible';
    });
  });
}

// Las animaciones se detienen automáticamente al ocultar la pestaña.
const updateVisibilityMotion = () =>
  document.documentElement.toggleAttribute('data-motion-hidden', document.hidden);
document.addEventListener('visibilitychange', updateVisibilityMotion);
updateVisibilityMotion();
