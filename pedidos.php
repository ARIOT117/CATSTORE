<?php
/** Historial privado de pedidos del usuario autenticado y foto de su michi. */

require __DIR__ . '/app.php';
requerirUsuario();

$titulo = 'Mis pedidos';
$pagina = 'pedidos';
$pedidos = [];
$lineas = [];
$mascota = null;
$error = '';
$errorMascota = '';

try {
    $db = obtenerConexion();

    $q = $db->prepare(
        'SELECT * FROM sc_pedidos WHERE usuario_id=? ORDER BY creado_en DESC, id DESC',
    );
    $q->execute([usuarioId()]);
    $pedidos = $q->fetchAll();

    $q = $db->prepare(
        'SELECT i.*
         FROM sc_pedido_items i
         JOIN sc_pedidos p ON p.id=i.pedido_id
         WHERE p.usuario_id=?
         ORDER BY i.id',
    );
    $q->execute([usuarioId()]);

    foreach ($q->fetchAll() as $item) {
        $lineas[$item['pedido_id']][] = $item;
    }
} catch (Throwable $ex) {
    error_log((string) $ex);
    $error =
        'No pudimos consultar tus pedidos. Tus datos siguen guardados; comprueba la conexión con MySQL e inténtalo de nuevo.';
}

try {
    $q = obtenerConexion()->prepare(
        'SELECT archivo, tipo, tamano, creado_en FROM sc_mascotas WHERE usuario_id=? LIMIT 1',
    );
    $q->execute([usuarioId()]);
    $mascota = $q->fetch() ?: null;
} catch (Throwable $ex) {
    error_log((string) $ex);
    $errorMascota =
        'La sección de fotos necesita la actualización de la base. Ejecuta scripts/instalar.php.';
}

$etiquetasTipo = [
    'image/jpeg' => 'JPG',
    'image/png' => 'PNG',
    'image/webp' => 'WebP',
];
$tipoMascota = $mascota ? ($etiquetasTipo[$mascota['tipo']] ?? 'Imagen') : '';
$tamanoMascota = $mascota ? (int) $mascota['tamano'] : 0;
$tamanoMascota =
    $tamanoMascota < 1024
        ? $tamanoMascota . ' B'
        : number_format($tamanoMascota / 1024, 0) . ' KB';

$nombreUsuario = mb_strtoupper((string) ($_SESSION['nombre'] ?? ''));
require __DIR__ . '/header.php';
?>
<main id="contenido" class="contenedor seccion">
    <div class="cuenta-encabezado">
        <div>
            <p class="etiqueta">QUÉ BUENO VERTE, <?= e($nombreUsuario) ?></p>
            <h1>
                Tus pequeños
                <em>gustos.</em>
            </h1>
            <p>Aquí viven los pedidos de tu cuenta.</p>
        </div>
        <form action="acciones.php" method="post">
            <?= campoCsrf() ?>
            <input type="hidden" name="accion" value="salir" />
            <button class="boton secundario" type="submit">
                <?= icono('salir') ?> Cerrar sesión
            </button>
        </form>
    </div>

    <section id="michi" class="michi-cuenta" aria-labelledby="titulo-michi">
        <div class="michi-cuenta-texto">
            <p class="etiqueta">UN RECUERDO PARA TU CUENTA</p>
            <h2 id="titulo-michi">La foto de tu michi.</h2>
            <p>
                Sube una foto de tu compañero. Se guarda en un espacio privado y solo se muestra
                cuando inicias sesión.
            </p>
            <p class="michi-cuenta-ayuda">JPG, PNG o WebP · máximo 2 MB · mínimo 20 × 20 px.</p>
        </div>

        <div class="michi-cuenta-archivo">
            <?php if ($errorMascota): ?>
                <div class="error-formulario" role="alert"><?= e($errorMascota) ?></div>
            <?php endif; ?>

            <?php if ($mascota): ?>
                <figure class="michi-foto">
                    <img
                        src="mascota.php?ver=<?= (int) strtotime((string) $mascota['creado_en']) ?>"
                        alt="Foto de tu michi"
                        width="240"
                        height="240"
                    />
                    <figcaption><?= e($tipoMascota) ?> · <?= e($tamanoMascota) ?></figcaption>
                </figure>
                <form action="mascota.php" method="post" class="form-archivo form-archivo-eliminar">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="accion" value="eliminar" />
                    <button class="enlace-boton eliminar" type="submit">Eliminar foto</button>
                </form>
            <?php endif; ?>

            <form action="mascota.php" method="post" enctype="multipart/form-data" class="form-archivo">
                <?= campoCsrf() ?>
                <input type="hidden" name="accion" value="subir" />
                <label for="foto-michi">
                    <?= $mascota ? 'Cambiar la foto' : 'Elegir una foto' ?>
                </label>
                <input
                    id="foto-michi"
                    name="foto"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    required
                />
                <button class="boton" type="submit">Guardar foto</button>
            </form>
        </div>
    </section>

    <h2 class="titulo-pedidos">Pedidos guardados</h2>

    <?php if ($error): ?>
        <div class="error-formulario" role="alert"><?= e($error) ?></div>
    <?php elseif (!$pedidos): ?>
        <div class="vacio">
            <?= icono('bolsa') ?>
            <h2>Tu primer pedido está por llegar.</h2>
            <p>Cuando confirmes una compra de demostración, la encontrarás aquí.</p>
            <a class="boton" href="productos.php">Descubrir la tiendita <?= icono('flecha') ?></a>
        </div>
    <?php else: ?>
        <div class="pedidos-lista">
            <?php foreach ($pedidos as $pedido): ?>
                <?php
                $folio = 'SC-' . str_pad((string) $pedido['id'], 5, '0', STR_PAD_LEFT);
                $fecha = (new DateTimeImmutable(
                    (string) $pedido['creado_en'],
                    new DateTimeZone('UTC'),
                ))
                    ->setTimezone(new DateTimeZone('America/Chihuahua'))
                    ->format('d/m/Y · H:i');
                $lineasPedido = $lineas[$pedido['id']] ?? [];
                ?>
                <article class="pedido" id="pedido-<?= (int) $pedido['id'] ?>">
                    <header class="pedido-cabecera">
                        <div>
                            <p class="etiqueta">PEDIDO <?= e($folio) ?></p>
                            <p><?= e($fecha) ?></p>
                        </div>
                        <span class="estado">
                            <?= icono('check') ?> <?= e($pedido['estado']) ?>
                        </span>
                    </header>

                    <div class="pedido-lineas">
                        <?php foreach ($lineasPedido as $item): ?>
                            <?php
                            $cantidad = (int) $item['cantidad'];
                            $precioUnitario = (int) $item['precio_centavos'];
                            $subtotal = $cantidad * $precioUnitario;
                            ?>
                            <div class="pedido-linea">
                                <img
                                    src="<?= e($item['imagen']) ?>"
                                    alt=""
                                    width="64"
                                    height="64"
                                    loading="lazy"
                                />
                                <div>
                                    <h2><?= e($item['nombre']) ?></h2>
                                    <p>
                                        <?= $cantidad ?> × <?= precio($precioUnitario) ?>
                                    </p>
                                </div>
                                <strong><?= precio($subtotal) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <footer class="pedido-total">
                        <span>Demostración · Sin cobro</span>
                        <a
                            class="enlace-boton"
                            href="comprobante.php?pedido=<?= (int) $pedido['id'] ?>"
                        >
                            Descargar comprobante CSV
                        </a>
                        <strong>Total <?= precio((int) $pedido['total_centavos']) ?> MXN</strong>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
