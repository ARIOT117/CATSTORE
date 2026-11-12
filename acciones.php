<?php
/** Operaciones POST protegidas con CSRF: carrito, compra y cierre de sesión. */

require __DIR__ . '/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit();
}

verificarCsrf();

$accion = is_string($_POST['accion'] ?? null) ? $_POST['accion'] : '';
$respuestaJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
$destino = in_array($accion, ['agregar', 'cantidad', 'eliminar'], true)
    ? 'carrito.php'
    : 'pedidos.php';

try {
    if ($accion === 'salir') {
        cerrarSesion();
        session_start();
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        aviso('Sesión cerrada. Tus pedidos y carrito siguen guardados en tu cuenta.');
        ir('index.php');
    }

    if ($accion === 'comprar') {
        if (!usuarioId()) {
            aviso(
                'Inicia sesión para guardar tu pedido. Tu carrito te espera.',
                'info',
            );
            ir('login.php?continuar=carrito');
        }

        $clave = $_POST['clave'] ?? '';
        if (
            !is_string($clave) ||
            !preg_match('/^[a-f0-9]{32}$/', $clave) ||
            !hash_equals($_SESSION['checkout'] ?? '', $clave)
        ) {
            throw new DomainException(
                'Recarga el carrito antes de confirmar tu pedido.',
            );
        }

        $id = confirmarPedido($clave);
        $folio = str_pad((string) $id, 5, '0', STR_PAD_LEFT);
        aviso(
            '¡Pedido SC-' .
                $folio .
                ' guardado! Es una demostración: no se realizó ningún cobro.',
        );
        ir('pedidos.php?pedido=' . $id);
    }

    if (!in_array($accion, ['agregar', 'cantidad', 'eliminar'], true)) {
        throw new DomainException('Acción no válida.');
    }

    $id = filter_var($_POST['producto'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if (!$id) {
        throw new DomainException('Selecciona un producto válido.');
    }

    $db = obtenerConexion();
    $db->beginTransaction();

    if (usuarioId()) {
        $q = $db->prepare('SELECT id FROM usuarios WHERE id=? FOR UPDATE');
        $q->execute([usuarioId()]);
    }

    $q = $db->prepare(
        'SELECT id, nombre, stock FROM sc_productos WHERE id=? AND activo=1',
    );
    $q->execute([$id]);
    $producto = $q->fetch();

    if (!$producto) {
        throw new DomainException('Este producto ya no está disponible.');
    }

    $actual = (int) (carritoCrudo()[$id] ?? 0);

    if ($accion === 'eliminar') {
        $cantidad = 0;
    } elseif ($accion === 'agregar') {
        $cantidad = $actual + 1;
    } else {
        $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 20],
        ]);
    }

    if ($cantidad === false || $cantidad > min(20, (int) $producto['stock'])) {
        throw new DomainException(
            'Solo puedes agregar hasta ' .
                min(20, (int) $producto['stock']) .
                ' unidades de este producto.',
        );
    }

    guardarCantidad((int) $id, (int) $cantidad);
    $db->commit();
    unset($_SESSION['checkout']);

    if ($accion === 'agregar') {
        $mensaje = $producto['nombre'] . ' se agregó a tu carrito.';
    } elseif ($cantidad === 0) {
        $mensaje = 'Producto eliminado del carrito.';
    } else {
        $mensaje = 'Carrito actualizado.';
    }

    if ($respuestaJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            [
                'ok' => true,
                'mensaje' => $mensaje,
                'cantidad' => contadorCarrito(),
            ],
            JSON_UNESCAPED_UNICODE,
        );
        exit();
    }

    aviso($mensaje);
    ir($destino);
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    $esperado = $error instanceof DomainException;
    if (!$esperado) {
        error_log((string) $error);
    }

    $mensaje = $esperado
        ? $error->getMessage()
        : 'No pudimos guardar el cambio. Comprueba que MySQL esté encendido y que la base esté instalada; después vuelve a intentarlo.';

    if ($respuestaJson) {
        http_response_code($esperado ? 422 : 503);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['ok' => false, 'mensaje' => $mensaje],
            JSON_UNESCAPED_UNICODE,
        );
        exit();
    }

    aviso($mensaje, 'error');
    ir($destino);
}
