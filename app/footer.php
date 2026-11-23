<?php
/** Pie compartido: navegación y un único punto de contacto por WhatsApp. */
$mensajeWhatsApp = 'Hola, Snacks and Cats. Tengo una duda sobre la cafetería, ¿me pueden ayudar?';
$enlaceWhatsApp = 'https://wa.me/526141205926?text=' . rawurlencode($mensajeWhatsApp);
$telefonoWhatsApp = '614 120 5926';
?>
        <footer class="pie">
            <div class="contenedor pie-principal">
                <div>
                    <a class="marca marca-pie" href="index.php">
                        <img src="assets/img/marca/logo-snacks-cats.webp" alt="" width="44" height="44" />
                        <span>
                            snacks
                            <i>&amp;</i>
                            cats
                            <small>UN RINCONCITO PARA QUEDARTE</small>
                        </span>
                    </a>
                    <p>
                        Café en tu taza.
                        <br />
                        Ronroneos en tu día.
                    </p>
                </div>
                <div>
                    <h2>Ven a conocernos</h2>
                    <p>
                        Calle Segunda y de la Llave
                        <br />
                        Colonia Centro, Chihuahua, Chih.
                    </p>
                    <a
                        class="enlace-flecha"
                        href="https://maps.app.goo.gl/ud9W22f4zkyRXqPMA"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Abrir en Google Maps <?= icono('flecha') ?>
                    </a>
                    <a
                        class="contacto-whatsapp"
                        href="<?= e($enlaceWhatsApp) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?= icono('whatsapp') ?>
                        <span>
                            ¿Tienes una duda?
                            <strong>WhatsApp · <?= e($telefonoWhatsApp) ?></strong>
                        </span>
                        <?= icono('flecha') ?>
                    </a>
                </div>
                <div>
                    <h2>Explora a tu ritmo</h2>
                    <a href="menu.php">Nuestro menú</a>
                    <a href="nosotros.php">La cafetería</a>
                    <a href="productos.php">Un detalle para tu michi</a>
                    <a href="pedidos.php">Mis pedidos</a>
                </div>
            </div>
            <div class="contenedor pie-final">
                <span>
                    © <?= date('Y') ?> Snacks and Cats · Hecho con cariño y un poquito de catnip.
                </span>
                <span>Propuesta de sitio · Compras de demostración</span>
            </div>
        </footer>
        <div id="notificacion" class="notificacion" role="status" aria-live="polite"></div>
    </body>
</html>
