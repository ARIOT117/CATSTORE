// Agrega aquí tus productos: { id, nombre, precio, cantidad, imagen, categoria }.
const datos = [
    {
        id: 1,
        nombre: "Croquetas de pollo",
        descripcion: "Croquetas crujientes sabor pollo, ideales para la alimentación diaria de gatos adultos. Bolsa de 1.5 kg.",
        precio: 499.90,
        cantidad: 10,
        imagen: "./assets/img/productos/croquetas-pollo.png",
        categoria: "Alimento"
    },
    {
        id: 2,
        nombre: "Ratón Travieso",
        descripcion: "Ratoncito suave relleno con catnip que estimula el instinto de juego y caza del gato.",
        precio: 220,
        cantidad: 2,
        imagen: "./assets/img/productos/raton-travieso.png",
        categoria: "Juguetes"
    },
    {
        id: 3,
        nombre: "Scratch & Play",
        descripcion: " Rascador compacto de cartón con una pelota integrada para que el gato rasque y juegue al mismo tiempo.",
        precio: 32,
        cantidad: 9,
        imagen: "./assets/img/productos/rascador-scratch-play.png",
        categoria: "Juguetes"
    }
];

const contenedor = document.getElementById("productos");
const contador = document.getElementById("resultado-productos");
const buscador = document.getElementById("buscador");
const formatoPrecio = new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" });

// Mensaje cuando no hay nada que mostrar
function mensaje(icono, titulo, texto) {
    return `
        <div class="catalogo-vacio">
            <span aria-hidden="true">${icono}</span>
            <h3>${titulo}</h3>
            <p>${texto}</p>
        </div>`;
}

// HTML de una tarjeta de producto
function tarjeta(p) {
    const stock = typeof p.cantidad === "number"
        ? (p.cantidad > 0 ? `${p.cantidad} disponibles` : "Agotado")
        : (p.cantidad || "");
    const imagen = p.imagen
        ? `<img src="${p.imagen}" alt="${p.nombre}" loading="lazy">`
        : `<span class="producto-sin-imagen">♡</span>`;
    const categoria = p.categoria ? `<span class="producto-categoria">${p.categoria}</span>` : "";
    const descripcion = p.descripcion ? `<p class="producto-descripcion">${p.descripcion}</p>` : "";

    return `
        <article class="producto">
            <div class="producto-imagen">${imagen}</div>
            <div class="producto-info">
                ${categoria}
                <h3>${p.nombre}</h3>
                ${descripcion}
                <div class="producto-meta">
                    <strong class="producto-precio">${formatoPrecio.format(p.precio)}</strong>

                    <span class="producto-stock">${stock}</span>
                </div>
            </div>
        </article>`;
}

function mostrarProductos(productos) {
    if (datos.length === 0) {
        contador.textContent = "Catálogo en preparación";
        contenedor.innerHTML = mensaje("♡", "Pronto habrá más para tu michi", "Aquí aparecerán los productos cuando los agregues al catálogo.");
    } else if (productos.length === 0) {
        contador.textContent = "0 productos";
        contenedor.innerHTML = mensaje("⌕", "No encontramos productos", "Prueba con otra palabra en el buscador.");
    } else {
        contador.textContent = `${productos.length} ${productos.length === 1 ? "producto" : "productos"}`;
        contenedor.innerHTML = productos.map(tarjeta).join("");
    }
}

// Filtra por nombre, id o categoría mientras se escribe
buscador.addEventListener("input", () => {
    const texto = buscador.value.trim().toLowerCase();
    const filtrados = datos.filter((p) =>
        `${p.nombre} ${p.id} ${p.categoria || ""} ${p.descripcion || ""}`.toLowerCase().includes(texto)
    );
    mostrarProductos(filtrados);
});

mostrarProductos(datos);
