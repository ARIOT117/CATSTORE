CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS sc_productos (
    id INT UNSIGNED PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    categoria VARCHAR(40) NOT NULL,
    precio_centavos INT UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 20,
    imagen VARCHAR(1024) NOT NULL,
    fuente VARCHAR(40) NOT NULL DEFAULT 'local',
    codigo VARCHAR(40) NULL,
    activo TINYINT NOT NULL DEFAULT 1
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS sc_carrito (
    usuario_id BIGINT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    CONSTRAINT cantidad_carrito_valida CHECK (cantidad BETWEEN 1 AND 20),
    PRIMARY KEY (usuario_id, producto_id),
    FOREIGN KEY (producto_id) REFERENCES sc_productos (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS sc_pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    clave CHAR(32) NOT NULL,
    total_centavos INT UNSIGNED NOT NULL,
    estado VARCHAR(40) NOT NULL DEFAULT 'Demostración confirmada',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY pedido_unico (usuario_id, clave),
    KEY usuario_fecha (usuario_id, creado_en)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS sc_pedido_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    imagen VARCHAR(1024) NOT NULL,
    precio_centavos INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    CONSTRAINT cantidad_item_valida CHECK (cantidad BETWEEN 1 AND 20),
    FOREIGN KEY (pedido_id) REFERENCES sc_pedidos (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS sc_intentos (
    clave CHAR(64) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY intentos_fecha (clave, creado_en)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Foto opcional del michi de cada cuenta. El archivo vive fuera de la carpeta
-- pública y se sirve únicamente desde mascota.php con la sesión iniciada.
CREATE TABLE IF NOT EXISTS sc_mascotas (
    usuario_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    archivo VARCHAR(255) NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    tamano INT UNSIGNED NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

