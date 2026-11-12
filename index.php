<?php
/** Portada: bienvenida, presentación de la experiencia, bebidas destacadas y visita. */

$titulo = 'Café, antojos y compañía gatuna';
$pagina = 'inicio';
require __DIR__ . '/header.php';

$destacados = [];
try {
    $destacados = destacadosMenu();
} catch (Throwable $error) {
    error_log((string) $error);
}
?>
<main id="contenido">
    <section class="contenedor hero">
        <div class="hero-texto">
            <p class="etiqueta">
                <span class="punto"></span>
                TU CAT CAFÉ EN CHIHUAHUA
            </p>
            <h1>
                Un cafecito.
                <br />
                Un antojo.
                <br />
                <em>Muchos michis.</em>
            </h1>
            <p class="hero-descripcion">
                Bebidas, antojos y un ratito en buena compañía. Conoce nuestro cat café en el
                centro de Chihuahua y encuentra tu próxima pausa favorita.
            </p>
            <div class="acciones">
                <a class="boton" href="menu.php">Ver el menú <?= icono('flecha') ?></a>
                <a class="enlace-subrayado" href="nosotros.php#ubicacion">
                    Cómo llegar <?= icono('pin') ?>
                </a>
            </div>
            <div class="hero-nota">
                <span class="mini-huella"><?= icono('huella') ?></span>
                <span>
                    Para amantes del café.
                    <br />
                    <strong>Y de quienes ronronean.</strong>
                </span>
            </div>
        </div>

        <div class="hero-arte">
            <div class="hero-arco">
                <svg
                    class="arco-texto"
                    viewBox="0 0 440 220"
                    role="img"
                    aria-label="Tu pausa favorita, con bigotes"
                >
                    <defs>
                        <path id="curva-bienvenida" d="M 45,165 A 185,185 0 0,1 395,165" />
                    </defs>
                    <text aria-hidden="true">
                        <textPath href="#curva-bienvenida" startOffset="50%" text-anchor="middle">
                            TU PAUSA FAVORITA, CON BIGOTES
                        </textPath>
                    </text>
                </svg>
                <img
                    src="<?= e(personaje('bienvenida')) ?>"
                    alt="Gato con moño rosa que te da la bienvenida"
                    width="500"
                    height="750"
                    fetchpriority="high"
                />
            </div>
            <span class="hero-flor" aria-hidden="true">✳</span>
            <div class="hero-postal">
                <?= icono('huella') ?>
                <span>
                    Un ratito para ti.
                    <br />
                    <em>Y muchos ronroneos.</em>
                </span>
            </div>
        </div>
    </section>

    <section class="cinta-seccion" aria-label="Lo que encontrarás en Snacks and Cats">
        <div class="cinta">
            <div class="cinta-pista">
                <?php for ($copia = 0; $copia < 2; $copia++): ?>
                    <div class="cinta-grupo" <?= $copia ? ' aria-hidden="true"' : '' ?>>
                        <span><?= icono('cafe') ?> Bebidas con personalidad</span>
                        <span><?= icono('corazon') ?> Antojos para compartir</span>
                        <span><?= icono('huella') ?> Compañía gatuna</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <section class="contenedor seccion experiencia">
        <div class="introduccion">
            <p class="etiqueta">MÁS QUE UN CAFECITO</p>
            <h2>
                Tu pausa favorita
                <br />
                tiene
                <em>bigotes.</em>
            </h2>
            <p>
                Un plan sencillo: escoger tu bebida, darte un gusto y disfrutar el momento.
                Bienvenido a Snacks and Cats.
            </p>
            <a class="enlace-flecha" href="nosotros.php">
                Conoce la cafetería <?= icono('flecha') ?>
            </a>
        </div>

        <div class="experiencia-tarjetas-zona">
            <div class="experiencia-tarjetas">
                <article>
                    <?= icono('cafe') ?>
                    <h3>Encuentra tu bebida</h3>
                    <p>
                        Del latte de siempre a una limonada mariposa azul. Encuentra tu nuevo
                        favorito.
                    </p>
                </article>
                <article>
                    <?= icono('huella') ?>
                    <h3>Un plan con bigotes</h3>
                    <p>
                        Una experiencia para quienes encuentran felicidad en los pequeños
                        momentos gatunos.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <?php if ($destacados): ?>
        <section class="menu-adelanto">
            <div class="contenedor seccion">
                <div class="encabezado-seccion">
                    <div>
                        <p class="etiqueta">DEL MENÚ A TU PRÓXIMA VISITA</p>
                        <h2>
                            ¿Qué se te
                            <em>antoja?</em>
                        </h2>
                    </div>
                    <a class="enlace-flecha" href="menu.php">
                        Explora el menú <?= icono('flecha') ?>
                    </a>
                </div>

                <div class="favoritos">
                    <?php foreach ($destacados as $bebida): ?>
                        <?php
                        $foto = fotoBebida($bebida['categoria_id']);
                        $categoria = mb_strtoupper($bebida['categoria_nombre']);
                        $descripcion =
                            $bebida['descripcion'] !== ''
                                ? $bebida['descripcion']
                                : 'Descúbrelo en nuestro menú.';
                        ?>
                        <a
                            class="favorito"
                            href="menu.php#<?= e(rawurlencode($bebida['categoria_id'])) ?>"
                        >
                            <div class="bebida-arte bebida-foto">
                                <img
                                    src="<?= e($foto[0]) ?>"
                                    alt="<?= e($foto[1]) ?>"
                                    width="900"
                                    height="650"
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                />
                                <span>Foto ilustrativa</span>
                            </div>
                            <div>
                                <span><?= e($categoria) ?></span>
                                <h3><?= e($bebida['nombre']) ?></h3>
                                <p><?= e($descripcion) ?></p>
                                <strong>
                                    <?= precio($bebida['precio_centavos']) ?>
                                    <small>MXN</small>
                                </strong>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <p class="nota-menu">
                    Fotografías ilustrativas de bebidas; la presentación puede variar. Precios en
                    MXN. Consulta con el personal la disponibilidad al visitar.
                </p>
            </div>
        </section>
    <?php endif; ?>

    <section class="contenedor seccion visita-banner">
        <div>
            <p class="etiqueta">NOS VEMOS EN EL CENTRO</p>
            <h2>
                El próximo plan
                <br />
                ya tiene
                <em>lugar.</em>
            </h2>
            <p>
                Calle Segunda y de la Llave, Colonia Centro.
                <br />
                Chihuahua, Chih.
            </p>
            <a
                class="boton"
                href="https://maps.app.goo.gl/ud9W22f4zkyRXqPMA"
                target="_blank"
                rel="noopener noreferrer"
            >
                Cómo llegar <?= icono('pin') ?>
            </a>
        </div>
        <div class="visita-gato">
            <img
                src="<?= e(personaje('visita')) ?>"
                alt="Gato con sombrero, café y croissant"
                loading="lazy"
                width="400"
                height="400"
            />
            <span>¿Vienes por un café?</span>
        </div>
    </section>

    <section class="contenedor tienda-invitacion">
        <span><?= icono('bolsa') ?></span>
        <div>
            <p class="etiqueta">UN DETALLITO EXTRA</p>
            <h2>Y para el michi que te espera en casa…</h2>
            <p>Descubre nuestra tiendita de productos para gatos.</p>
        </div>
        <a class="enlace-flecha" href="productos.php">Ir a la tiendita <?= icono('flecha') ?></a>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
