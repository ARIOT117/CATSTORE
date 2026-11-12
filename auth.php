<?php
/** Configuración de MySQL y acceso a cuentas con consultas preparadas y contraseñas hash. */

declare(strict_types=1);

function configuracion(): array
{
    $local = is_file(__DIR__ . '/config.local.php')
        ? require __DIR__ . '/config.local.php'
        : [];

    $config = array_replace(
        [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'caotitos',
            'user' => 'root',
            'password' => '',
            'socket' => '',
        ],
        $local,
    );

    $variablesEntorno = [
        'host' => 'HOST',
        'port' => 'PORT',
        'database' => 'NAME',
        'user' => 'USER',
        'password' => 'PASSWORD',
        'socket' => 'SOCKET',
    ];

    foreach ($variablesEntorno as $clave => $sufijo) {
        $valor = getenv('CATSTORE_DB_' . $sufijo);
        if ($valor !== false) {
            $config[$clave] = $valor;
        }
    }

    return $config;
}

function obtenerConexion(bool $sinBase = false): PDO
{
    static $conexion;

    if (!$sinBase && $conexion instanceof PDO) {
        return $conexion;
    }

    $config = configuracion();

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $config['database'])) {
        throw new RuntimeException('Nombre de base no válido.');
    }

    $dsn = $config['socket']
        ? 'mysql:unix_socket=' . $config['socket']
        : 'mysql:host=' . $config['host'] . ';port=' . $config['port'];

    if (!$sinBase) {
        $dsn .= ';dbname=' . $config['database'];
    }

    $db = new PDO(
        $dsn . ';charset=utf8mb4',
        $config['user'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
    $db->exec("SET time_zone = '+00:00'");

    if (!$sinBase) {
        $conexion = $db;
    }

    return $db;
}

function normalizarCorreo(string $correo): string
{
    return strtolower(trim($correo));
}

function buscarUsuarioPorCorreo(string $correo): ?array
{
    $q = obtenerConexion()->prepare(
        'SELECT id, nombre, correo, password_hash
         FROM usuarios
         WHERE correo=?
         LIMIT 1',
    );
    $q->execute([$correo]);

    return $q->fetch() ?: null;
}

function crearUsuario(string $nombre, string $correo, string $contrasena): bool
{
    try {
        $q = obtenerConexion()->prepare(
            'INSERT INTO usuarios(nombre, correo, password_hash) VALUES(?, ?, ?)',
        );

        return $q->execute([
            $nombre,
            $correo,
            password_hash($contrasena, PASSWORD_DEFAULT),
        ]);
    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            return false;
        }
        throw $error;
    }
}
