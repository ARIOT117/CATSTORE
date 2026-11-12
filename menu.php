<?php
/** Menú de bebidas leído desde MySQL, con navegación por anclas y categorías. */

$titulo = 'Nuestro menú';
$pagina = 'menu';
require __DIR__ . '/header.php';

$categorias = [];
$errorMenu = '';

try {
    $categorias = leerMenu();
} catch (Throwable $error) {
    error_log((string) $error);
    $errorMenu =
        'No pudimos consultar el menú en este momento. Vuelve a intentarlo en unos minutos.';
    http_response_code(503);
}

$totalBebidas = array_sum(
    array_map(static fn(array $categoria): int => count($categoria['items']), $categorias),
);
?>
<main id="contenido">
    <section class="contenedor pagina-intro menu-intro">
        <p class="etiqueta">PARA TU MOMENTO FAVORITO DEL DÍA</p>
        <h1>
            Un sorbito de
            <em>felicidad.</em>
        </h1>
        <p>
            Con café, sin café, fresquito o con un toque diferente.
            <br />
            Tu próximo favorito está por aquí.
        </p>
        <div class="menu-resumen">
            <span class="etiqueta">PARA ELEGIR SIN PRISA</span>
            <strong><?= $totalBebidas ?> bebidas</strong>
            <span><?= count($categorias) ?> categorías · Precios en MXN</span>
        </div>
    </section>

    <section class="contenedor menu-contenido" id="todo-menu">
        <nav class="menu-categorias" aria-label="Categorías del menú">

            <span class="etiqueta">¿QUÉ SE TE ANTOJA?</span>
            <a href="#todo-menu" aria-current="true">
                Todo el menú
                <span><?= $totalBebidas ?></span>
            </a>

            <?php foreach ($categorias as $categoria): ?>
                <a href="#<?= e($categoria['id']) ?>">
                    <?= e($categoria['nombre']) ?>
                    <span><?= count($categoria['items']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="menu-listado">
            <?php if ($errorMenu): ?>
                <div class="error-formulario" role="alert"><?= e($errorMenu) ?></div>
            <?php elseif (!$categorias): ?>
                <div class="vacio">
                    <h2>Pronto habrá algo rico por aquí.</h2>
                    <p>Estamos preparando las opciones disponibles del menú.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($categorias as $categoria): ?>
                <?php
                $idCategoria = $categoria['id'];
                $gatoCategoria = personaje('menu-' . $idCategoria);
                ?>
                <section
                    class="menu-grupo categoria-<?= e($idCategoria) ?>"
                    id="<?= e($idCategoria) ?>"
                >
                    <div class="menu-grupo-titulo">
                        <h2><?= e($categoria['nombre']) ?></h2>
                        <?php if ($gatoCategoria): ?>
                            <span class="menu-gato-sello" aria-hidden="true">
                                <img src="<?= e($gatoCategoria) ?>" alt="" width="88" height="88" />
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="menu-nota"><?= e(notaCategoria($categoria)) ?></p>

                    <?php foreach ($categoria['items'] as $item): ?>
                        <article class="menu-item">
                            <div>
                                <h3><?= e($item['nombre']) ?></h3>
                                <?php if ($item['descripcion']): ?>
                                    <p><?= e($item['descripcion']) ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="menu-puntos" aria-hidden="true"></span>
                            <strong><?= precio($item['precio_centavos']) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>

            <div class="menu-aclaracion">
                <p><?= icono('corazon') ?> Este menú se disfruta en la cafetería.</p>
                <p>
                    Precios en pesos mexicanos. Consulta con el personal los ingredientes y la
                    disponibilidad al visitar.
                </p>
                <a class="enlace-flecha" href="nosotros.php#ubicacion">
                    Planea tu visita <?= icono('flecha') ?>
                </a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
