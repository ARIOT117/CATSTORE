<?php
/** Experiencia de la cafetería, convivencia, preguntas frecuentes y ubicación. */

$titulo = 'La cafetería';
$pagina = 'nosotros';
require __DIR__ . '/header.php';
?>
<main id="contenido">
    <section class="contenedor pagina-intro">
        <p class="etiqueta">SNACKS AND CATS · CHIHUAHUA</p>
        <h1>
            Donde el café
            <br />
            encuentra
            <em>compañía.</em>
        </h1>
        <p>
            Un rinconcito para hacer una pausa, descubrir una bebida diferente y compartir tu amor
            por los gatos.
        </p>
    </section>

    <section class="contenedor historia">
        <div class="historia-arte">
            <img
                src="<?= e(personaje('historia')) ?>"
                alt="Gato blanco que ilustra la compañía felina"
                width="440"
                height="440"
            />
            <span class="etiqueta">MENOS PRISA, MÁS RONRONEOS</span>
        </div>
        <div>
            <p class="etiqueta">UN PLAN CON PERSONALIDAD</p>
            <h2>
                Quédate por
                <br />
                un
                <em>ratito más.</em>
            </h2>
            <p>
                Una bebida que se antoja, una conversación sin prisa y la curiosidad de un michi.
                Snacks and Cats reúne café y compañía felina en el centro de Chihuahua.
            </p>
            <p>
                Descubre las bebidas del menú y planea una visita a tu ritmo. Las ilustraciones de
                este sitio nos ayudan a contar la experiencia; no son fotografías de los gatos del
                establecimiento.
            </p>
            <a class="boton" href="menu.php">Encontrar mi bebida <?= icono('flecha') ?></a>
        </div>
    </section>

    <section class="contenedor seccion convivencia">
        <div class="encabezado-seccion">
            <div>
                <p class="etiqueta">BUENA COMPAÑÍA, CON CUIDADO</p>
                <h2>
                    A su ritmo.
                    <br />
                    Y con
                    <em>mucho cariño.</em>
                </h2>
            </div>
            <p>Pequeños gestos para disfrutar la compañía de los gatos respetando su espacio.</p>
        </div>

        <div class="convivencia-grid">
            <article>
                <span>01</span>
                <h3>Deja que se acerquen</h3>
                <p>
                    Un michi curioso decide cuándo saludar. Dale tiempo y espacio para sentirse
                    cómodo.
                </p>
            </article>
            <article>
                <span>02</span>
                <h3>Respeta su descanso</h3>
                <p>
                    Si está durmiendo o se aleja, déjalo tranquilo. También ellos disfrutan sus
                    pausas.
                </p>
            </article>
            <article>
                <span>03</span>
                <h3>Pregunta al equipo</h3>
                <p>
                    Antes de cargarlo o darle comida, consulta al personal y sigue las indicaciones
                    del lugar.
                </p>
            </article>
        </div>
    </section>

    <section class="contenedor preguntas seccion">
        <div class="preguntas-intro">
            <p class="etiqueta">ANTES DE VENIR</p>
            <h2>
                Una duda,
                <br />
                un
                <em>cafecito.</em>
            </h2>
            <p>Lo que puedes consultar aquí para planear tu visita.</p>
            <img
                src="<?= e(personaje('preguntas')) ?>"
                alt=""
                width="170"
                height="190"
                loading="lazy"
            />
        </div>

        <div class="preguntas-lista">
            <details>
                <summary>¿Dónde está la cafetería?</summary>
                <p>
                    En Calle Segunda y de la Llave, Colonia Centro, Chihuahua. Abre la ubicación de
                    Google Maps para consultar la ruta hasta el negocio.
                </p>
            </details>
            <details>
                <summary>¿Dónde consulto los horarios actuales?</summary>
                <p>
                    Consulta la
                    <a
                        href="https://maps.app.goo.gl/ud9W22f4zkyRXqPMA"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        ficha del negocio en Google Maps
                    </a>
                    antes de salir. Así puedes comprobar los horarios publicados para el día de tu
                    visita.
                </p>
            </details>
            <details>
                <summary>¿Puedo pedir bebidas desde esta página?</summary>
                <p>
                    El menú de bebidas es para consultar y elegir antes de visitar la cafetería. No
                    se reciben pedidos de bebidas desde este sitio.
                </p>
            </details>
            <details>
                <summary>¿Las compras de la tiendita son reales?</summary>
                <p>
                    La tiendita es una demostración: puedes guardar productos y pedidos en tu
                    cuenta, pero no se realizan cobros, envíos ni reservas de inventario.
                </p>
            </details>
            <details>
                <summary>¿Qué debo saber para convivir con los gatos?</summary>
                <p>
                    Respeta su espacio y sigue las indicaciones del personal. Consulta directamente
                    con la cafetería cualquier condición de acceso antes de tu visita.
                </p>
            </details>
        </div>
    </section>

    <section id="ubicacion" class="contenedor seccion">
        <div class="encabezado-seccion">
            <div>
                <p class="etiqueta">HAZTE UN ESPACIO</p>
                <h2>
                    Te esperamos
                    <em>por aquí.</em>
                </h2>
            </div>
        </div>

        <div class="ubicacion-grid">
            <div class="ubicacion-datos">
                <span class="icono-circulo"><?= icono('pin') ?></span>
                <h3>En el corazón de Chihuahua</h3>
                <p>
                    Calle Segunda y de la Llave
                    <br />
                    Colonia Centro, 31000
                    <br />
                    Chihuahua, Chih.
                </p>
                <p class="horario-nota">
                    <?= icono('reloj') ?> Consulta los horarios actualizados en Google Maps antes
                    de venir.
                </p>
                <a
                    class="boton"
                    href="https://maps.app.goo.gl/ud9W22f4zkyRXqPMA"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Abrir indicaciones <?= icono('flecha') ?>
                </a>
            </div>
            <div class="mapa">
                <iframe
                    title="Mapa de Snacks and Cats en Chihuahua"
                    src="https://maps.google.com/maps?q=Snacks%20and%20Cats%20Chihuahua&amp;output=embed"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                ></iframe>
                <p>Si el mapa no carga, usa el botón de indicaciones.</p>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
