<?php
/** Migración CLI del menú inicial; no sobrescribe ediciones posteriores. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require dirname(__DIR__) . '/auth.php';
require dirname(__DIR__) . '/data/menu-repositorio.php';
try {
    $db = obtenerConexion();
    $db->exec(file_get_contents(__DIR__ . '/../data/schema-menu.sql'));
    sembrarMenu($db, __DIR__ . '/../data/menu.json');
    echo 'Menú listo: ' .
        $db->query('SELECT COUNT(*) FROM sc_menu_items')->fetchColumn() .
        " bebidas. No se sobrescribieron cambios existentes.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'No se pudo migrar el menú: ' . $e->getMessage() . "\n");
    exit(1);
}
