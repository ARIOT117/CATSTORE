<?php
/** Revisión de relaciones y cantidades válidas en la base de datos. */
declare(strict_types=1);
/** Solo añade relaciones si los datos existentes lo permiten; nunca borra registros. */
function reforzarIntegridad(PDO $db): void
{
    $tipo = $db->query("SHOW COLUMNS FROM usuarios WHERE Field='id'")->fetch()['Type'];
    if (
        !preg_match(
            '/^(?:tinyint|smallint|mediumint|int|bigint)(?:\([0-9]+\))?(?: unsigned)?$/i',
            $tipo,
        )
    ) {
        throw new RuntimeException('Tipo de usuarios.id no compatible.');
    }
    foreach (
        [
            'sc_carrito' => 'carrito_usuario',
            'sc_pedidos' => 'pedido_usuario',
            'sc_mascotas' => 'mascota_usuario',
        ]
        as $tabla => $nombre
    ) {
        $q = $db->query(
            "SELECT COUNT(*) FROM $tabla t LEFT JOIN usuarios u ON u.id=t.usuario_id WHERE u.id IS NULL",
        );
        if ((int) $q->fetchColumn()) {
            throw new RuntimeException(
                "Hay registros huérfanos en $tabla. Revísalos antes de aplicar la relación; no se borró ninguno.",
            );
        }
        $q = $db->prepare(
            'SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? AND REFERENCED_TABLE_NAME=?',
        );
        $q->execute([$tabla, 'usuario_id', 'usuarios']);
        if (!(int) $q->fetchColumn()) {
            $db->exec(
                "ALTER TABLE $tabla MODIFY usuario_id $tipo NOT NULL, ADD CONSTRAINT $nombre FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT",
            );
        }
    }
    foreach (
        ['sc_carrito' => 'cantidad_carrito_valida', 'sc_pedido_items' => 'cantidad_item_valida']
        as $tabla => $nombre
    ) {
        $q = $db->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME=? AND CONSTRAINT_NAME=?',
        );
        $q->execute([$tabla, $nombre]);
        if (!(int) $q->fetchColumn()) {
            $db->exec(
                "ALTER TABLE $tabla ADD CONSTRAINT $nombre CHECK (cantidad BETWEEN 1 AND 20)",
            );
        }
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(404);
        exit();
    }
    require dirname(__DIR__) . '/auth.php';
    try {
        reforzarIntegridad(obtenerConexion());
        echo "Relaciones con usuarios verificadas.\n";
    } catch (Throwable $error) {
        fwrite(STDERR, $error->getMessage() . "\n");
        exit(1);
    }
}
