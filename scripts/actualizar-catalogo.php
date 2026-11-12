<?php
/** Actualización CLI desde la API y reconstrucción del catálogo JSON. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require dirname(__DIR__) . '/auth.php';
$root = dirname(__DIR__);
$url =
    'https://world.openpetfoodfacts.org/api/v2/search?categories_tags=en%3Acat-foods&page_size=24&fields=code,product_name,product_name_es,brands,image_front_url,categories_tags,quantity';
try {
    if (!extension_loaded('curl')) {
        throw new RuntimeException('Activa cURL en PHP para actualizar el catálogo.');
    }
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_USERAGENT => 'SnacksAndCatsDemo/1.0 (local educational project)',
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
    ]);
    $body = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if (!$body || $status !== 200) {
        throw new RuntimeException('La API no respondió correctamente. ' . $error);
    }
    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    $catalogo = json_decode(
        file_get_contents($root . '/data/catalogo.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $porCodigo = [];
    $max = 100;
    foreach ($catalogo as $i => $p) {
        $max = max($max, $p['id']);
        if (!empty($p['codigo'])) {
            $porCodigo[$p['codigo']] = $i;
        }
    }
    $cantidad = 0;
    $actualizados = [];
    foreach ($data['products'] ?? [] as $p) {
        $nombre = trim($p['product_name_es'] ?? ($p['product_name'] ?? ''));
        $codigo = (string) ($p['code'] ?? '');
        $imagen = $p['image_front_url'] ?? '';
        if (
            !$nombre ||
            strlen($nombre) > 255 ||
            !preg_match('/^[0-9]{6,20}$/', $codigo) ||
            !in_array('en:cat-food', $p['categories_tags'] ?? [], true) ||
            stripos($nombre, 'prescription') !== false
        ) {
            continue;
        }
        if (
            !filter_var($imagen, FILTER_VALIDATE_URL) ||
            parse_url($imagen, PHP_URL_SCHEME) !== 'https' ||
            parse_url($imagen, PHP_URL_HOST) !== 'images.openpetfoodfacts.org'
        ) {
            continue;
        }
        $i = $porCodigo[$codigo] ?? null;
        $producto = [
            'id' => $i !== null ? $catalogo[$i]['id'] : ++$max,
            'nombre' => $nombre,
            'descripcion' =>
                trim($p['brands'] ?? '') .
                (!empty($p['quantity']) ? ' · ' . $p['quantity'] : '') .
                '. Datos de Open Pet Food Facts.',
            'categoria' => in_array('en:cat-biscuits', $p['categories_tags'], true)
                ? 'Premios'
                : 'Alimento',
            'precio_centavos' => $i !== null ? $catalogo[$i]['precio_centavos'] : 14900,
            'stock' => $i !== null ? $catalogo[$i]['stock'] : 20,
            'imagen' => $imagen,
            'fuente' => 'Open Pet Food Facts',
            'codigo' => $codigo,
        ];
        if ($i === null) {
            $porCodigo[$codigo] = count($catalogo);
            $catalogo[] = $producto;
        } else {
            $catalogo[$i] = $producto;
        }
        $actualizados[$codigo] = $producto;
        if (++$cantidad >= 12) {
            break;
        }
    }
    if (!$cantidad) {
        throw new RuntimeException(
            'La respuesta no contiene alimentos para gatos válidos. Se conserva el catálogo anterior.',
        );
    }
    $db = obtenerConexion();
    $db->beginTransaction();
    $q = $db->prepare(
        'INSERT INTO sc_productos(id,nombre,descripcion,categoria,precio_centavos,stock,imagen,fuente,codigo) VALUES(?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),descripcion=VALUES(descripcion),imagen=VALUES(imagen),categoria=VALUES(categoria)',
    );
    foreach ($actualizados as $p) {
        $q->execute([
            $p['id'],
            $p['nombre'],
            $p['descripcion'],
            $p['categoria'],
            $p['precio_centavos'],
            $p['stock'],
            $p['imagen'],
            $p['fuente'],
            $p['codigo'],
        ]);
    }
    // MySQL es la fuente de verdad: confirmar primero y reconstruir el respaldo
    // desde los registros guardados, para conservar precios editados en MySQL.
    $db->commit();
    $catalogo = $db->query('SELECT * FROM sc_productos WHERE activo=1 ORDER BY id')->fetchAll();
    $tmp = tempnam($root . '/data', 'catalogo-');
    if ($tmp === false) {
        throw new RuntimeException('MySQL actualizado; no se pudo crear el respaldo.');
    }
    try {
        if (
            file_put_contents(
                $tmp,
                json_encode(
                    $catalogo,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                ) . "\n",
                LOCK_EX,
            ) === false
        ) {
            throw new RuntimeException('MySQL actualizado; no se pudo escribir el respaldo.');
        }
        if (!rename($tmp, $root . '/data/catalogo.json')) {
            throw new RuntimeException('MySQL actualizado; no se pudo reemplazar el respaldo.');
        }
    } finally {
        if (is_file($tmp)) {
            unlink($tmp);
        }
    }
    echo $cantidad .
        " productos consultados. Precios y existencias existentes conservados.\nFuente: Open Pet Food Facts (ODbL; imágenes CC BY-SA).\n";
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(
        STDERR,
        'La actualización no se completó: ' .
            $e->getMessage() .
            "\nConsulta el detalle anterior; MySQL puede haberse actualizado aunque falle el respaldo.\n",
    );
    exit(1);
}
