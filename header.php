<?php
/** Cabecera común: metadatos, estilos, navegación y accesos a cuenta y carrito. */

require_once __DIR__ . '/app.php';

$titulo = $titulo ?? 'Snacks and Cats';
$pagina = $pagina ?? 'inicio';

// La versión en la URL evita reutilizar estilos o scripts de una entrega anterior.
$versionCss = substr(hash_file('sha256', __DIR__ . '/CSS/style.css'), 0, 12);
$versionJs = substr(hash_file('sha256', __DIR__ . '/JS/app.js'), 0, 12);

$enlacesNavegacion = [
    'inicio' => ['index.php', 'Inicio'],
    'menu' => ['menu.php', 'El menú'],
    'nosotros' => ['nosotros.php', 'La cafetería'],
    'productos' => ['productos.php', 'Tiendita'],
];

$avatarCuenta = null;
try {
    $avatarCuenta = fotoCuenta();
} catch (Throwable $errorAvatar) {
    error_log((string) $errorAvatar);
}

$cuentaActiva = in_array($pagina, ['login', 'registro', 'pedidos'], true);
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
        <meta name="theme-color" content="#f8f3e9" />
        <meta
            name="description"
            content="Snacks and Cats: café, antojos y compañía gatuna en el centro de Chihuahua. Explora el menú y planea tu visita."
        />
        <title><?= e($titulo) ?> | Snacks and Cats</title>
        <link rel="icon" href="assets/img/marca/favicon-snacks-cats.ico" sizes="any" />
        <link rel="icon" href="assets/img/marca/favicon-snacks-cats.png" type="image/png" />
        <link rel="apple-touch-icon" href="assets/img/marca/favicon-snacks-cats.png" />
        <link rel="stylesheet" href="CSS/style.css?v=<?= e($versionCss) ?>" />
        <script src="JS/app.js?v=<?= e($versionJs) ?>" defer></script>
    </head>
    <body data-pagina="<?= e($pagina) ?>">
        <a class="saltar" href="#contenido">Saltar al contenido</a>

        <div class="anuncio">
            <span><?= icono('huella') ?> Café, antojos y compañía gatuna.</span>
            <span>Chihuahua, Centro <?= icono('pin') ?></span>
        </div>

        <header class="cabecera">
            <div class="contenedor nav-interior">
                <a class="marca" href="index.php" aria-label="Snacks and Cats, inicio">
                    <img
                        src="assets/img/marca/logo-snacks-cats.webp"
                        alt=""
                        width="48"
                        height="48"
                    />
                    <span>
                        snacks
                        <i>&amp;</i>
                        cats
                        <small>CAFÉ · ANTOJOS · MICHIS</small>
                    </span>
                </a>

                <button
                    class="boton-icono menu-movil"
                    type="button"
                    aria-label="Abrir navegación"
                    aria-expanded="false"
                    aria-controls="navegacion"
                >
                    <?= icono('menu') ?>
                </button>

                <nav id="navegacion" aria-label="Navegación principal">
                    <?php foreach ($enlacesNavegacion as $clave => [$url, $texto]): ?>
                        <?php $activo = $pagina === $clave; ?>
                        <a href="<?= e($url) ?>"<?= $activo ? ' aria-current="page"' : '' ?>>
                            <?= e($texto) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="nav-cuenta">
                    <a
                        class="acceso"
                        aria-label="<?= usuarioId() ? 'Mi cuenta' : 'Acceder a mi cuenta' ?>"
                        href="<?= usuarioId() ? 'pedidos.php' : 'login.php' ?>"
                        <?= $cuentaActiva ? ' aria-current="page"' : '' ?>
                    >
                        <?php if ($avatarCuenta): ?>
                            <img
                                class="avatar-cuenta"
                                src="<?= e(urlFotoCuenta($avatarCuenta)) ?>"
                                alt=""
                                width="32"
                                height="32"
                            />
                        <?php else: ?>
                            <?= icono('usuario') ?>
                        <?php endif; ?>
                        <span><?= usuarioId() ? 'Mi cuenta' : 'Acceder' ?></span>
                    </a>
                    <a
                        class="carrito-enlace"
                        href="carrito.php"
                        aria-label="Carrito, <?= contadorCarrito() ?> artículos"
                        <?= $pagina === 'carrito' ? ' aria-current="page"' : '' ?>
                    >
                        <?= icono('bolsa') ?>
                        <span class="cuenta-carrito"><?= contadorCarrito() ?></span>
                    </a>
                </div>
            </div>
        </header>

        <?php if ($aviso = $_SESSION['aviso'] ?? null): ?>
            <?php unset($_SESSION['aviso']); ?>
            <div class="contenedor aviso <?= e($aviso['tipo']) ?>" role="status">
                <?= icono('check') ?>
                <span><?= e($aviso['texto']) ?></span>
            </div>
        <?php endif; ?>
