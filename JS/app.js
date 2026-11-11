'use strict';
const menuButton = document.querySelector('.menu-movil');
const navigation = document.getElementById('navegacion');
menuButton?.addEventListener('click', () => {
    const open = menuButton.getAttribute('aria-expanded') !== 'true';
    menuButton.setAttribute('aria-expanded', String(open));
    menuButton.setAttribute('aria-label', open ? 'Cerrar navegación' : 'Abrir navegación');
    navigation.classList.toggle('abierto', open);
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && menuButton?.getAttribute('aria-expanded') === 'true') {
        menuButton.click(); menuButton.focus();
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
const search = document.getElementById('buscador');
const filters = [...document.querySelectorAll('[data-filtro]')];
const cards = [...document.querySelectorAll('.producto[data-categoria]')];
let category = 'Todos';
const normalize = (text) => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
function filterProducts() {
    const query = normalize(search?.value.trim() || '');
    let count = 0;
    cards.forEach((card) => {
        const visible = (category === 'Todos' || card.dataset.categoria === category) && normalize(card.dataset.busqueda).includes(query);
        card.hidden = !visible; if (visible) count++;
    });
    const result = document.getElementById('resultado-productos');
    if (result) result.textContent = `${count} ${count === 1 ? 'producto encontrado' : 'productos encontrados'}`;
    const empty = document.getElementById('sin-resultados');
    if (empty) empty.hidden = count !== 0;
}
search?.addEventListener('input', filterProducts);
filters.forEach((button) => button.addEventListener('click', () => {
    category = button.dataset.filtro;
    filters.forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
    filterProducts();
}));
document.getElementById('limpiar-filtros')?.addEventListener('click', () => {
    search.value = ''; filters[0].click(); search.focus();
});
let notificationTimeout;
function notify(message) {
    const node = document.getElementById('notificacion');
    node.textContent = message; node.classList.add('visible');
    clearTimeout(notificationTimeout);
    notificationTimeout = setTimeout(() => node.classList.remove('visible'), 6500);
}
document.querySelectorAll('[data-agregar]').forEach((form) => form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('button');
    if (button.disabled) return;
    button.disabled = true;
    try {
        const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {Accept: 'application/json'}, credentials: 'same-origin'});
        const type = response.headers.get('content-type') || '';
        if (!type.includes('application/json')) throw new Error('No pudimos comprobar el cambio. Recarga la página y revisa tu carrito antes de intentar otra vez.');
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.mensaje || 'No pudimos guardar el cambio.');
        document.querySelectorAll('.cuenta-carrito').forEach((node) => { node.textContent = data.cantidad; });
        document.querySelector('.carrito-enlace').setAttribute('aria-label', `Carrito, ${data.cantidad} artículos`);
        notify(data.mensaje);
    } catch (error) { notify(error.message === 'Failed to fetch' ? 'Se perdió la conexión. Revisa tu carrito antes de volver a agregar el producto.' : error.message); }
    finally { button.disabled = false; }
}));
document.querySelectorAll('img').forEach((img) => {
    const fallback = () => {
        if (img.closest('.producto-imagen')) {
            img.hidden = true;
            img.parentElement.querySelector('.imagen-fallback').hidden = false;
        }
    };
    img.addEventListener('error', fallback);
    if (img.complete && img.naturalWidth === 0) fallback();
});
document.querySelectorAll('[data-confirmar]').forEach((form) => form.addEventListener('submit', () => {
    const button = form.querySelector('button[type=submit]');
    button.disabled = true; button.textContent = 'Guardando tu pedido…';
}));
window.addEventListener('pageshow', () => document.querySelectorAll('[data-confirmar] button').forEach((button) => { button.disabled = false; button.textContent = 'Confirmar pedido demo →'; }));


// Movimiento decorativo: sigue las preferencias del sistema sin añadir controles visuales.
if (document.querySelector('.hero-arte')) {
    const root = document.documentElement;
    const updateMotion = () => root.toggleAttribute('data-motion-hidden', document.hidden);
    updateMotion();
    document.addEventListener('visibilitychange', updateMotion);
    root.setAttribute('data-motion-ready', '');
}

const motionButton = document.querySelector('[data-pausar-movimiento]');
if (motionButton) {
    let paused = false;
    try { paused = sessionStorage.getItem('movimiento-pausado') === '1'; } catch {}
    const applyPause = () => {
        document.documentElement.toggleAttribute('data-motion-paused', paused);
        motionButton.setAttribute('aria-pressed', String(paused));
        motionButton.textContent = paused ? 'Reanudar movimiento' : 'Pausar movimiento';
    };
    applyPause();
    motionButton.addEventListener('click', () => {
        paused = !paused; applyPause();
        try { sessionStorage.setItem('movimiento-pausado', paused ? '1' : '0'); } catch {}
    });
}
document.querySelectorAll('.bebida-foto img').forEach((img) => {
    img.addEventListener('error', () => {
        img.hidden = true;
        img.parentElement.classList.add('foto-no-disponible');
        img.parentElement.querySelector('span').textContent = 'Fotografía no disponible';
    });
});

// Navegación local del menú y categoría activa durante el recorrido.
const menuCategories = document.querySelector('.menu-categorias');
if (menuCategories) {
    const links = [...menuCategories.querySelectorAll('a[href^="#"]')];
    const groups = [...document.querySelectorAll('.menu-grupo')];
    let scrollFrame = 0;
    const offset = () => {
        const header = document.querySelector('.cabecera');
        const top = Math.ceil(header?.getBoundingClientRect().height || 0);
        const mobile = matchMedia('(max-width:760px)').matches;
        document.documentElement.style.setProperty('--menu-nav-top', `${top}px`);
        const value = top + (mobile ? menuCategories.offsetHeight : 0) + 20;
        document.documentElement.style.setProperty('--menu-scroll-offset', `${value}px`);
        return value;
    };
    const activate = (id) => {
        links.forEach(link => {
            if (link.hash === `#${id}`) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
        });
    };
    const updateCategory = () => {
        scrollFrame = 0;
        const threshold = offset() + 60;
        let current = 'todo-menu';
        for (const group of groups) {
            if (group.getBoundingClientRect().top <= threshold) current = group.id;
        }
        activate(current);
    };
    links.forEach(link => link.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const target = document.getElementById(link.hash.slice(1));
        if (!target) return;
        event.preventDefault(); offset();
        history.pushState(null, '', link.hash);
        target.scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start'});
        activate(target.id);
    }));
    window.addEventListener('scroll', () => {
        if (!scrollFrame) scrollFrame = requestAnimationFrame(updateCategory);
    }, {passive:true});
    window.addEventListener('resize', updateCategory);
    // Compatibilidad con enlaces antiguos: muestran todo y llevan a la categoría.
    const legacy = new URLSearchParams(location.search).get('categoria');
    const initial = location.hash.slice(1) || legacy;
    offset();
    if (initial && groups.some(group => group.id === initial)) {
        requestAnimationFrame(() => {
            document.getElementById(initial).scrollIntoView({behavior:'instant', block:'start'});
            activate(initial);
        });
    } else updateCategory();
}

// Entrada discreta de secciones; el contenido ya visible nunca se oculta.
if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const reveal = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.dataset.revelar = 'visible';
                reveal.unobserve(entry.target);
            }
        });
    }, {rootMargin:'0px 0px 60px 0px', threshold:0});
    document.querySelectorAll('.menu-grupo,.favorito,.experiencia-tarjetas article,.visita-banner,.michi-despedida,.historia').forEach(node => {
        if (node.getBoundingClientRect().top > innerHeight && !node.contains(document.querySelector(':target'))) {
            node.dataset.revelar = 'pendiente'; reveal.observe(node);
        }
    });
    matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', event => {
        if (event.matches) {
            reveal.disconnect();
            document.querySelectorAll('[data-revelar]').forEach(node => node.dataset.revelar = 'visible');
        }
    });
}
