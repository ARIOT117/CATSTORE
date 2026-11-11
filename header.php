<?php
require_once __DIR__.'/app.php';
$titulo=$titulo??'Snacks and Cats'; $pagina=$pagina??'inicio';
// La URL cambia con el contenido para evitar reutilizar los estilos de una versión anterior.
$versionCss=substr(hash_file('sha256',__DIR__.'/CSS/style.css'),0,12);
$versionJs=substr(hash_file('sha256',__DIR__.'/JS/app.js'),0,12);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#f8f3e9"><meta name="description" content="Snacks and Cats: café, antojos y compañía gatuna en el centro de Chihuahua. Explora el menú y planea tu visita.">
<title><?= e($titulo) ?> | Snacks and Cats</title>
<link rel="icon" href="assets/img/marca/logo-gato.webp" type="image/webp">
<link rel="stylesheet" href="CSS/style.css?v=<?= e($versionCss) ?>"><script src="JS/app.js?v=<?= e($versionJs) ?>" defer></script>
</head>
<body>
<a class="saltar" href="#contenido">Saltar al contenido</a>
<div class="anuncio"><span><?= icono('huella') ?> Un café sabe mejor en buena compañía.</span><span>Chihuahua, Centro <?= icono('pin') ?></span></div>
<header class="cabecera">
<div class="contenedor nav-interior">
<a class="marca" href="index.php" aria-label="Snacks and Cats, inicio"><img src="assets/img/marca/logo-gato.webp" alt="" width="48" height="48"><span>snacks <i>&</i> cats<small>CAFÉ · ANTOJOS · MICHIS</small></span></a>
<button class="boton-icono menu-movil" type="button" aria-label="Abrir navegación" aria-expanded="false" aria-controls="navegacion"><?= icono('menu') ?></button>
<nav id="navegacion" aria-label="Navegación principal">
<?php foreach (['inicio'=>['index.php','Inicio'],'menu'=>['menu.php','El menú'],'nosotros'=>['nosotros.php','La cafetería'],'productos'=>['productos.php','Tiendita']] as $key=>$item): ?>
<a href="<?= $item[0] ?>"<?= $pagina===$key?' aria-current="page"':'' ?>><?= $item[1] ?></a>
<?php endforeach; ?>
</nav>
<div class="nav-cuenta">
<a class="acceso" href="<?= usuarioId()?'pedidos.php':'login.php' ?>"<?= in_array($pagina,['login','registro','pedidos'],true)?' aria-current="page"':'' ?>><?= icono('usuario') ?><span><?= usuarioId()?'Mi cuenta':'Acceder' ?></span></a>
<a class="carrito-enlace" href="carrito.php" aria-label="Carrito, <?= contadorCarrito() ?> artículos"<?= $pagina==='carrito'?' aria-current="page"':'' ?>><?= icono('bolsa') ?><span class="cuenta-carrito"><?= contadorCarrito() ?></span></a>
</div>
</div>
</header>
<?php if ($aviso=$_SESSION['aviso']??null): unset($_SESSION['aviso']); ?>
<div class="contenedor aviso <?= e($aviso['tipo']) ?>" role="status"><?= icono('check') ?><span><?= e($aviso['texto']) ?></span></div>
<?php endif; ?>
