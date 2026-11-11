<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/auth.php';
require dirname(__DIR__).'/data/menu-repositorio.php';
require __DIR__.'/integridad.php';
try {
    $c=configuracion(); $db=obtenerConexion(true);
    $db->exec('CREATE DATABASE IF NOT EXISTS `'.$c['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $db=obtenerConexion(); $db->exec(file_get_contents(dirname(__DIR__).'/data/schema.sql'));
    $productos=json_decode(file_get_contents(dirname(__DIR__).'/data/catalogo.json'),true,512,JSON_THROW_ON_ERROR);
    $q=$db->prepare('INSERT IGNORE INTO sc_productos(id,nombre,descripcion,categoria,precio_centavos,stock,imagen,fuente,codigo) VALUES(?,?,?,?,?,?,?,?,?)');
    foreach ($productos as $p) $q->execute([$p['id'],$p['nombre'],$p['descripcion'],$p['categoria'],$p['precio_centavos'],$p['stock'],$p['imagen'],$p['fuente'],$p['codigo']??null]);
    $db->exec(file_get_contents(dirname(__DIR__).'/data/schema-menu.sql'));
    sembrarMenu($db,dirname(__DIR__).'/data/menu.json');
    reforzarIntegridad($db);
    echo 'Base preparada: '.$c['database'].'. Productos: '.$db->query('SELECT COUNT(*) FROM sc_productos')->fetchColumn().".\nNo se borraron cuentas ni pedidos.\n";
} catch (Throwable $e) { fwrite(STDERR,'No se pudo instalar: '.$e->getMessage()."\nEnciende MySQL de XAMPP y revisa config.local.php.\n"); exit(1); }
