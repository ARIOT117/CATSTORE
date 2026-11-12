<?php
/** Consultas y migración del menú: categorías, bebidas y destacados desde MySQL. */

declare(strict_types=1);

/** Una consulta por petición; la siguiente recarga vuelve a leer MySQL. */
function leerMenu(): array
{
    static $menu;

    if ($menu !== null) {
        return $menu;
    }

    $filas = obtenerConexion()
        ->query(
            'SELECT
                c.id AS categoria_id,
                c.nombre AS categoria_nombre,
                c.nota,
                i.id,
                i.nombre,
                i.descripcion,
                i.precio_centavos,
                i.destacado_orden
             FROM sc_menu_categorias c
             JOIN sc_menu_items i ON i.categoria_id=c.id
             WHERE c.activa=1 AND i.disponible=1
             ORDER BY c.orden, c.id, i.orden, i.id',
        )
        ->fetchAll();

    $categorias = [];
    foreach ($filas as $fila) {
        $id = $fila['categoria_id'];

        if (!isset($categorias[$id])) {
            $categorias[$id] = [
                'id' => $id,
                'nombre' => $fila['categoria_nombre'],
                'nota' => $fila['nota'],
                'items' => [],
            ];
        }

        $categorias[$id]['items'][] = [
            'id' => (int) $fila['id'],
            'nombre' => $fila['nombre'],
            'descripcion' => $fila['descripcion'],
            'precio_centavos' => (int) $fila['precio_centavos'],
            'destacado_orden' =>
                $fila['destacado_orden'] === null ? null : (int) $fila['destacado_orden'],
        ];
    }

    return $menu = array_values($categorias);
}

function notaCategoria(array $categoria): string
{
    $nota = trim($categoria['nota']);
    $precios = array_unique(array_column($categoria['items'], 'precio_centavos'));

    if (count($precios) === 1 && count($categoria['items']) > 1) {
        $nota .=
            ($nota !== '' ? ' · ' : '') .
            'Todas las opciones a ' .
            precio((int) reset($precios)) .
            ' MXN';
    }

    return $nota;
}

function destacadosMenu(): array
{
    $destacados = [];

    foreach (leerMenu() as $categoria) {
        foreach ($categoria['items'] as $item) {
            if ($item['destacado_orden'] === null) {
                continue;
            }

            $item['categoria_id'] = $categoria['id'];
            $item['categoria_nombre'] = $categoria['nombre'];
            $destacados[] = $item;
        }
    }

    usort(
        $destacados,
        static fn(array $a, array $b): int =>
            [$a['destacado_orden'], $a['id']] <=> [$b['destacado_orden'], $b['id']],
    );

    return array_slice($destacados, 0, 3);
}

/** Semilla inicial transaccional: repetir el instalador no revierte ediciones ni bajas. */
function sembrarMenu(PDO $db, string $archivo): void
{
    $db->beginTransaction();

    try {
        $q = $db->prepare('INSERT IGNORE INTO sc_migraciones(clave) VALUES(?)');
        $q->execute(['menu_inicial_v1']);

        if ($q->rowCount() === 0) {
            $db->commit();
            return;
        }

        $categorias = json_decode(
            file_get_contents($archivo),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $insertarCategoria = $db->prepare(
            'INSERT IGNORE INTO sc_menu_categorias(id, nombre, nota, orden)
             VALUES(?, ?, ?, ?)',
        );
        $insertarItem = $db->prepare(
            'INSERT IGNORE INTO sc_menu_items(
                clave,
                categoria_id,
                nombre,
                descripcion,
                precio_centavos,
                orden,
                destacado_orden
             ) VALUES(?, ?, ?, ?, ?, ?, ?)',
        );

        foreach ($categorias as $orden => $categoria) {
            // Las frases con precios se calculan con los importes actuales.
            $nota = preg_replace(
                '/(?:\s*·\s*)?(?:Todos|Todas) a \$[0-9]+(?:\s*·\s*MXN)?/u',
                '',
                $categoria['nota'],
            );

            $insertarCategoria->execute([
                $categoria['id'],
                $categoria['nombre'],
                trim((string) $nota),
                $orden + 1,
            ]);

            foreach ($categoria['items'] as $posicion => $item) {
                $clave = $categoria['id'] . '-' . ($posicion + 1);
                $destacados = [
                    'cafe-5' => 1,
                    'limonadas-1' => 2,
                    'sin-cafe-3' => 3,
                ];
                $destacado = $destacados[$clave] ?? null;

                $insertarItem->execute([
                    $clave,
                    $categoria['id'],
                    $item['nombre'],
                    $item['descripcion'],
                    (int) round($item['precio'] * 100),
                    $posicion + 1,
                    $destacado,
                ]);
            }
        }

        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}
