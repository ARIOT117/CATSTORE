<?php
/** Catálogo de demostración: tarjetas, búsqueda, filtros y formularios de carrito. */

$titulo = 'La tiendita';
$pagina = 'productos';
require __DIR__ . '/header.php';

$productos = catalogo();
$categoriasProductos = array_values(array_unique(array_column($productos, 'categoria')));
$totalProductos = count($productos);
?>
<main id="contenido">
    <section class="contenedor tienda-intro">
        <div>
            <p class="etiqueta">DE NUESTRA TIENDITA A SU RINCÓN FAVORITO</p>
            <h1>
                Para el michi
                <br />
                que te espera
                <em>en casa.</em>
            </h1>
            <p>
                Un juguete, un premio, un pequeño apapacho. Porque ellos también merecen un
                detalle.
            </p>
        </div>
        <img src="<?= e(personaje('tienda')) ?>" alt="" width="240" height="240" />
    </section>

    <section class="contenedor catalogo seccion">
        <div class="catalogo-herramientas">
            <div class="filtros" role="group" aria-label="Filtrar productos por categoría">
                <?php $opcionesCategoria = array_merge(['Todos'], $categoriasProductos); ?>
                <?php foreach ($opcionesCategoria as $indice => $categoria): ?>
                    <button
                        type="button"
                        data-filtro="<?= e($categoria) ?>"
                        aria-pressed="<?= $indice === 0 ? 'true' : 'false' ?>"
                    >
                        <?= e($categoria) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <label class="busqueda">
                <?= icono('buscar') ?>
                <span class="sr-only">Buscar productos</span>
                <input type="search" id="buscador" placeholder="Encuentra algo para tu michi…" />
            </label>
        </div>

        <div class="catalogo-opciones">
            <label class="control-check">
                <input type="checkbox" id="solo-disponibles" />
                Solo disponibles
            </label>
            <span>Tu carrito se guarda al iniciar sesión.</span>
        </div>

        <div class="catalogo-subtitulo">
            <p id="resultado-productos" aria-live="polite">
                <?= $totalProductos ?> pequeños gustos para tu gato
            </p>
            <span>Precios de demostración · MXN</span>
        </div>

        <div class="productos-grid" id="productos">
            <?php foreach ($productos as $producto): ?>
                <?php
                $stock = (int) $producto['stock'];
                $disponible = $stock > 0;
                $esLocal = $producto['fuente'] === 'local';
                $fuente = $esLocal ? 'SELECCIÓN DE LA TIENDITA' : 'OPEN PET FOOD FACTS';
                $textoBoton = $disponible ? 'Agregar' : 'Agotado';
                $textoStock = $disponible ? 'Disponible para demo' : 'Agotado';
                $busqueda =
                    $producto['nombre'] .
                    ' ' .
                    $producto['descripcion'] .
                    ' ' .
                    $producto['categoria'];
                ?>
                <article
                    class="producto"
                    data-stock="<?= $stock ?>"
                    data-categoria="<?= e($producto['categoria']) ?>"
                    data-busqueda="<?= e($busqueda) ?>"
                >
                    <div class="producto-imagen">
                        <span class="producto-categoria"><?= e($producto['categoria']) ?></span>
                        <img
                            src="<?= e($producto['imagen']) ?>"
                            alt="<?= e($producto['nombre']) ?>"
                            width="320"
                            height="280"
                            loading="lazy"
                        />
                        <span class="imagen-fallback" hidden>
                            <?= icono('bolsa') ?>Imagen no disponible
                        </span>
                    </div>

                    <div class="producto-info">
                        <p class="producto-fuente"><?= e($fuente) ?></p>
                        <h2><?= e($producto['nombre']) ?></h2>
                        <p><?= e($producto['descripcion']) ?></p>

                        <div class="producto-base">
                            <div>
                                <strong><?= precio((int) $producto['precio_centavos']) ?></strong>
                                <small><?= e($textoStock) ?></small>
                            </div>
                            <form action="acciones.php" method="post" data-agregar>
                                <?= campoCsrf() ?>
                                <input type="hidden" name="accion" value="agregar" />
                                <input
                                    type="hidden"
                                    name="producto"
                                    value="<?= (int) $producto['id'] ?>"
                                />
                                <button
                                    class="agregar"
                                    type="submit"
                                    aria-label="Agregar <?= e($producto['nombre']) ?> al carrito"
                                    <?= $disponible ? '' : 'disabled' ?>
                                >
                                    <?= icono('mas') ?>
                                    <span><?= e($textoBoton) ?></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div id="sin-resultados" class="vacio" hidden>
            <?= icono('buscar') ?>
            <h2>Ese michi se escondió.</h2>
            <p>No encontramos productos. Intenta con otra palabra o categoría.</p>
            <button class="boton secundario" type="button" id="limpiar-filtros">
                Ver todos los productos
            </button>
        </div>

        <div class="nota-catalogo">
            <p>
                <strong>Una tiendita de demostración.</strong>
                Los pedidos se guardan en tu cuenta; no se realizan cobros ni envíos. Los precios y
                las existencias son ficticios.
            </p>
            <p>
                Datos de alimentos:
                <a
                    href="https://world.openpetfoodfacts.org/"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Open Pet Food Facts
                </a>
                ·
                <a
                    href="https://opendatacommons.org/licenses/odbl/1-0/"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    ODbL
                </a>
                . Fotografías de productos de sus colaboradores, bajo
                <a
                    href="https://creativecommons.org/licenses/by-sa/3.0/"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    CC BY-SA
                </a>
                . Consulta la
                <a href="fuentes.php">procedencia de cada producto</a>
                .
            </p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
