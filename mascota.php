<?php
/**
 * Gestión de archivos de la cuenta: subir, servir y eliminar la foto del michi.
 *
 * - Solo usuarios autenticados.
 * - Solo POST con CSRF para cambiar archivos.
 * - Valida tamaño, tipo MIME real y dimensiones antes de guardar.
 * - El archivo se guarda en data/mascotas (no público) y se sirve por este script.
 */

declare(strict_types=1);
require __DIR__ . '/app.php';

const MASCOTA_TAMANO_MAXIMO = 2 * 1024 * 1024;
const MASCOTA_LADO_MAXIMO = 4000;
const MASCOTA_LADO_MINIMO = 20;

$tiposPermitidos = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

/** Almacenamiento local por defecto; en Railway se usa un volumen fuera de la web. */
function carpetaMascotas(): string
{
    $configurada = getenv('CATSTORE_UPLOAD_DIR');
    return $configurada !== false && $configurada !== ''
        ? rtrim($configurada, '/')
        : __DIR__ . '/data/mascotas';
}

$carpetaMascotas = carpetaMascotas();

function rutaMascota(string $archivo): string
{
    return carpetaMascotas() . '/' . basename($archivo);
}

function borrarMascota(string $archivo): void
{
    $ruta = rutaMascota($archivo);
    if (is_file($ruta) && !unlink($ruta)) {
        error_log('No se pudo borrar la foto de mascota: ' . $ruta);
    }
}

if (!usuarioId()) {
    aviso('Inicia sesión para administrar la foto de tu michi.', 'info');
    ir('login.php?continuar=pedidos');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $q = obtenerConexion()->prepare(
            'SELECT archivo, tipo, tamano FROM sc_mascotas WHERE usuario_id=? LIMIT 1',
        );
        $q->execute([usuarioId()]);
        $mascota = $q->fetch();
    } catch (Throwable $error) {
        error_log((string) $error);
        http_response_code(503);
        exit('No pudimos consultar la foto.');
    }

    if (!$mascota) {
        http_response_code(404);
        exit('Foto no encontrada.');
    }

    $ruta = rutaMascota($mascota['archivo']);
    if (!is_file($ruta) || !isset($tiposPermitidos[$mascota['tipo']])) {
        http_response_code(404);
        exit('Foto no encontrada.');
    }

    header('Content-Type: ' . $mascota['tipo']);
    header('Content-Length: ' . filesize($ruta));
    header('Cache-Control: private, max-age=60');
    header('X-Content-Type-Options: nosniff');
    readfile($ruta);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: GET, POST');
    exit();
}

verificarCsrf();

$accion = is_string($_POST['accion'] ?? null) ? $_POST['accion'] : '';

if ($accion === 'eliminar') {
    try {
        $db = obtenerConexion();
        $q = $db->prepare('SELECT archivo FROM sc_mascotas WHERE usuario_id=? LIMIT 1');
        $q->execute([usuarioId()]);
        $archivo = $q->fetchColumn();

        $q = $db->prepare('DELETE FROM sc_mascotas WHERE usuario_id=?');
        $q->execute([usuarioId()]);

        if ($archivo) {
            borrarMascota((string) $archivo);
        }

        aviso('La foto de tu michi se eliminó.');
    } catch (Throwable $error) {
        error_log((string) $error);
        aviso('No pudimos eliminar la foto. Inténtalo de nuevo.', 'error');
    }

    ir('pedidos.php#michi');
}

if ($accion !== 'subir') {
    aviso('Acción de archivo no válida.', 'error');
    ir('pedidos.php#michi');
}

$archivo = $_FILES['foto'] ?? null;

if (!is_array($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $codigo = is_array($archivo)
        ? (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE)
        : UPLOAD_ERR_NO_FILE;
    $mensaje =
        $codigo === UPLOAD_ERR_INI_SIZE || $codigo === UPLOAD_ERR_FORM_SIZE
            ? 'La foto supera el tamaño permitido de 2 MB.'
            : 'Selecciona una foto válida para continuar.';
    aviso($mensaje, 'error');
    ir('pedidos.php#michi');
}

$tamano = (int) ($archivo['size'] ?? 0);
$temporal = (string) ($archivo['tmp_name'] ?? '');

if ($tamano < 1 || $tamano > MASCOTA_TAMANO_MAXIMO) {
    aviso('La foto debe pesar entre 1 byte y 2 MB.', 'error');
    ir('pedidos.php#michi');
}

if (!is_uploaded_file($temporal)) {
    aviso('No pudimos comprobar el archivo subido.', 'error');
    ir('pedidos.php#michi');
}

$tipo = (new finfo(FILEINFO_MIME_TYPE))->file($temporal);

if (!is_string($tipo) || !isset($tiposPermitidos[$tipo])) {
    aviso('Formato no permitido. Usa una imagen JPG, PNG o WebP.', 'error');
    ir('pedidos.php#michi');
}

$medidas = @getimagesize($temporal);
if (
    !$medidas ||
    $medidas[0] < MASCOTA_LADO_MINIMO ||
    $medidas[1] < MASCOTA_LADO_MINIMO ||
    $medidas[0] > MASCOTA_LADO_MAXIMO ||
    $medidas[1] > MASCOTA_LADO_MAXIMO
) {
    aviso(
        'La imagen debe medir entre ' .
            MASCOTA_LADO_MINIMO .
            ' y ' .
            MASCOTA_LADO_MAXIMO .
            ' píxeles por lado.',
        'error',
    );
    ir('pedidos.php#michi');
}

if (
    !is_dir($carpetaMascotas) &&
    !mkdir($carpetaMascotas, 0755, true) &&
    !is_dir($carpetaMascotas)
) {
    error_log('No se pudo crear la carpeta data/mascotas.');
    aviso('No pudimos preparar el almacenamiento de la foto.', 'error');
    ir('pedidos.php#michi');
}

$extension = $tiposPermitidos[$tipo];
$nombreArchivo = 'michi-' . usuarioId() . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
$destino = $carpetaMascotas . '/' . $nombreArchivo;

if (!move_uploaded_file($temporal, $destino)) {
    error_log('No se pudo mover la foto subida a data/mascotas.');
    aviso('No pudimos guardar la foto. Revisa los permisos de la carpeta.', 'error');
    ir('pedidos.php#michi');
}

chmod($destino, 0640);

try {
    $db = obtenerConexion();
    $q = $db->prepare('SELECT archivo FROM sc_mascotas WHERE usuario_id=? LIMIT 1');
    $q->execute([usuarioId()]);
    $anterior = $q->fetchColumn();

    $q = $db->prepare(
        'INSERT INTO sc_mascotas(usuario_id, archivo, tipo, tamano)
         VALUES(?,?,?,?)
         ON DUPLICATE KEY UPDATE
            archivo=VALUES(archivo),
            tipo=VALUES(tipo),
            tamano=VALUES(tamano),
            creado_en=CURRENT_TIMESTAMP',
    );
    $q->execute([usuarioId(), $nombreArchivo, $tipo, $tamano]);

    if ($anterior) {
        borrarMascota((string) $anterior);
    }

    aviso('¡Foto guardada! Ya puedes ver a tu michi en tu cuenta.');
} catch (Throwable $error) {
    error_log((string) $error);
    borrarMascota($nombreArchivo);
    aviso('No pudimos guardar la foto. Inténtalo de nuevo.', 'error');
}

ir('pedidos.php#michi');
