<?php
/** Inicio de sesión: valida credenciales, limita intentos y recupera el carrito. */

require __DIR__ . '/app.php';

$titulo = 'Bienvenido de nuevo';
$pagina = 'login';
$mensaje = '';
$correo = '';
$continuar =
    ($_GET['continuar'] ?? ($_POST['continuar'] ?? '')) === 'carrito' ? 'carrito' : 'pedidos';

if (usuarioId()) {
    ir($continuar . '.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $correo = normalizarCorreo(is_string($_POST['correo'] ?? null) ? $_POST['correo'] : '');
    $password = is_string($_POST['contrasena'] ?? null) ? $_POST['contrasena'] : '';

    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($correo) > 254 ||
        $password === '' ||
        strlen($password) > 72
    ) {
        $mensaje = 'Escribe un correo y una contraseña válidos.';
    } else {
        try {
            $db = obtenerConexion();
            $clave = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'local') . '|' . $correo);

            $q = $db->prepare(
                'SELECT COUNT(*) FROM sc_intentos
                 WHERE clave=? AND creado_en > DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
            );
            $q->execute([$clave]);
            $intentos = (int) $q->fetchColumn();

            if ($intentos >= 10) {
                $mensaje = 'Demasiados intentos. Espera 15 minutos antes de volver a intentar.';
            } else {
                $usuario = buscarUsuarioPorCorreo($correo);
                $hash =
                    $usuario['password_hash'] ??
                    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

                if ($usuario && password_verify($password, $hash)) {
                    autenticar($usuario);
                    $q = $db->prepare('DELETE FROM sc_intentos WHERE clave=?');
                    $q->execute([$clave]);
                    aviso('Qué gusto verte, ' . $usuario['nombre'] . '.');
                    ir($continuar . '.php');
                }

                $q = $db->prepare('INSERT INTO sc_intentos(clave) VALUES(?)');
                $q->execute([$clave]);
                $db->exec(
                    'DELETE FROM sc_intentos WHERE creado_en < DATE_SUB(NOW(), INTERVAL 1 DAY)',
                );
                $mensaje = 'El correo o la contraseña no coinciden. Inténtalo de nuevo.';
            }
        } catch (Throwable $ex) {
            error_log((string) $ex);
            $mensaje =
                'No pudimos iniciar sesión. Comprueba que MySQL esté encendido y la base esté instalada.';
        }
    }
}

$descripcionError = $mensaje ? ' aria-describedby="error-cuenta"' : '';
require __DIR__ . '/header.php';
?>
<main id="contenido" class="contenedor cuenta-layout">
    <aside class="cuenta-arte">
        <p class="etiqueta">TU RINCONCITO PERSONAL</p>
        <h1>
            Qué gusto
            <br />
            verte
            <em>por aquí.</em>
        </h1>
        <p>Entra a tu cuenta y encuentra tus pequeños gustos guardados.</p>
        <img
            src="<?= e(personaje('acceso')) ?>"
            alt=""
            width="380"
            height="380"
        />
    </aside>

    <section class="formulario-tarjeta">
        <span class="icono-circulo"><?= icono('usuario') ?></span>
        <h2>Bienvenido de nuevo</h2>
        <p>Tus pedidos y tu carrito, siempre a la mano.</p>

        <?php if ($mensaje): ?>
            <div id="error-cuenta" class="error-formulario" role="alert" tabindex="-1">
                <?= e($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php"<?= $descripcionError ?>>
            <?= campoCsrf() ?>
            <input type="hidden" name="continuar" value="<?= e($continuar) ?>" />

            <label for="correo">Correo electrónico</label>
            <input
                id="correo"
                name="correo"
                type="email"
                autocomplete="email"
                maxlength="254"
                placeholder="tu@correo.com"
                value="<?= e($correo) ?>"
                required
            />

            <label for="contrasena">Contraseña</label>
            <div class="campo-password">
                <input
                    id="contrasena"
                    name="contrasena"
                    type="password"
                    autocomplete="current-password"
                    maxlength="72"
                    required
                />
                <button type="button" data-ver-password="contrasena" aria-pressed="false">
                    Ver
                </button>
            </div>

            <button class="boton ancho" type="submit">
                Entrar a mi cuenta <?= icono('flecha') ?>
            </button>
        </form>

        <p class="formulario-pie">
            ¿Primera visita?
            <a href="registro.php?continuar=<?= e($continuar) ?>">Crea tu cuenta</a>
        </p>
        <small>No se requieren datos bancarios para comprar en esta demostración.</small>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
