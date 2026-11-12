<?php
/** Detalle del carrito y formularios para cantidades, eliminación y confirmación. */

require __DIR__ . '/app.php';

$titulo = 'Tu carrito';
$pagina = 'carrito';
$error = '';
$carrito = ['items' => [], 'total' => 0];

try {
    $carrito = carritoDetalle();
} catch (Throwable $ex) {
    error_log((string) $ex);
    $error =
        'Tu carrito está guardado. No pudimos consultarlo por ahora; comprueba que MySQL esté encendido y vuelve a intentarlo.';
}

$_SESSION['checkout'] ??= bin2hex(random_bytes(16));

$totalUnidades = array_sum(array_column($carrito['items'], 'cantidad'));
require __DIR__ . '/header.php';
?>
<main id="contenido" class="contenedor seccion">
    <div class="pagina-intro compacta">
        <p class="etiqueta">UNOS CUANTOS APAPACHOS</p>
        <h1>
            Su próxima
            <em>alegría.</em>
        </h1>
        <p>Revisa los pequeños gustos que elegiste para tu michi.</p>
    </div>

    <?php if ($error): ?>
        <div class="error-formulario" role="alert"><?= e($error) ?></div>
    <?php elseif (!$carrito['items']): ?>
        <div class="vacio">
            <img
                class="estado-gato"
                src="<?= e(personaje('carrito-vacio')) ?>"
                alt=""
                width="150"
                height="170"
            />
            <h2>Tu carrito está tomando una siesta.</h2>
            <p>Despiértalo con un detalle para tu gato.</p>
            <a class="boton" href="productos.php">Explorar la tiendita <?= icono('flecha') ?></a>
        </div>
    <?php else: ?>
        <div class="carrito-layout">
            <section class="carrito-lista" aria-label="Productos en tu carrito">
                <?php foreach ($carrito['items'] as $producto): ?>
                    <article class="carrito-item">
                        <img
                            src="<?= e($producto['imagen']) ?>"
                            alt="<?= e($producto['nombre']) ?>"
                            width="110"
                            height="110"
                        />
                        <div>
                            <p class="etiqueta"><?= e($producto['categoria']) ?></p>
                            <h2><?= e($producto['nombre']) ?></h2>
                            <p><?= precio((int) $producto['precio_centavos']) ?> por unidad</p>

                            <form method="post" action="acciones.php" class="cantidad-form">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="accion" value="cantidad" />
                                <input
                                    type="hidden"
                                    name="producto"
                                    value="<?= (int) $producto['id'] ?>"
                                />
                                <label for="cantidad-<?= (int) $producto['id'] ?>">Cantidad</label>
                                <input
                                    id="cantidad-<?= (int) $producto['id'] ?>"
                                    name="cantidad"
                                    type="number"
                                    min="0"
                                    max="<?= min(20, (int) $producto['stock']) ?>"
                                    value="<?= (int) $producto['cantidad'] ?>"
                                    required
                                />
                                <button class="enlace-boton" type="submit">Actualizar</button>
                            </form>

                            <form method="post" action="acciones.php">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="accion" value="eliminar" />
                                <input
                                    type="hidden"
                                    name="producto"
                                    value="<?= (int) $producto['id'] ?>"
                                />
                                <button class="enlace-boton eliminar" type="submit">
                                    Quitar producto
                                </button>
                            </form>
                        </div>
                        <strong><?= precio($producto['subtotal']) ?></strong>
                    </article>
                <?php endforeach; ?>

                <a class="enlace-flecha" href="productos.php">
                    Seguir explorando <?= icono('flecha') ?>
                </a>
            </section>

            <aside class="resumen">
                <p class="etiqueta">TODO LISTO PARA SU MICHI</p>
                <h2>Tu pedido</h2>

                <div class="resumen-fila">
                    <span>Productos (<?= $totalUnidades ?>)</span>
                    <span><?= precio($carrito['total']) ?></span>
                </div>
                <div class="resumen-fila">
                    <span>Entrega</span>
                    <span>No aplica · Demo</span>
                </div>
                <div class="resumen-total">
                    <span>Total MXN</span>
                    <strong><?= precio($carrito['total']) ?></strong>
                </div>

                <div class="demo-nota">
                    <?= icono('corazon') ?>
                    <p>Solo es una demostración. No se te cobrará ni se enviarán productos.</p>
                </div>

                <?php if (usuarioId()): ?>
                    <form method="post" action="acciones.php" data-confirmar>
                        <?= campoCsrf() ?>
                        <input type="hidden" name="accion" value="comprar" />
                        <input type="hidden" name="clave" value="<?= e($_SESSION['checkout']) ?>" />
                        <button class="boton ancho" type="submit">
                            Confirmar pedido demo <?= icono('flecha') ?>
                        </button>
                    </form>
                    <small>Se guardará en tu cuenta con sus productos y total.</small>
                <?php else: ?>
                    <a class="boton ancho" href="login.php?continuar=carrito">
                        Inicia sesión para continuar <?= icono('flecha') ?>
                    </a>
                    <small>Conservaremos los productos que elegiste.</small>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
