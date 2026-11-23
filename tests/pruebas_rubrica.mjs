import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';

const base = process.env.CATSTORE_TEST_URL?.replace(/\/$/, '');
if (!base) throw new Error('Configura CATSTORE_TEST_URL.');
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
const browser = await chromium.launch({
  args: ['--no-sandbox'],
  ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}),
});
const rutas = ['/', '/menu.php', '/nosotros.php', '/productos.php', '/login.php', '/carrito.php', '/pedidos.php'];
const evidencia = [];

try {
  for (const width of [360, 768, 1366]) {
    const page = await browser.newPage({ viewport: { width, height: 900 }, reducedMotion: 'reduce' });
    const fallos = [];
    page.on('pageerror', error => fallos.push(error.message));
    page.on('response', response => {
      if (response.url().startsWith(base + '/') && response.status() >= 400) fallos.push(response.url());
    });

    const tiempos = {};
    for (const ruta of rutas) {
      const inicio = performance.now();
      const respuesta = await page.goto(base + ruta, { waitUntil: 'domcontentloaded' });
      assert.equal(respuesta.status(), 200, ruta);
      assert.equal(await page.locator('main#contenido').count(), 1, ruta);
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), ruta + ' desborda');
      if (ruta === '/pedidos.php') assert.ok(page.url().endsWith('/login.php'));
      tiempos[ruta] = Math.round(performance.now() - inicio);
    }

    await page.goto(base);
    await page.keyboard.press('Tab');
    const foco = await page.evaluate(() => {
      const activo = document.activeElement;
      const estilo = getComputedStyle(activo);
      return { enlace: activo.getAttribute('href'), ancho: estilo.outlineWidth, estilo: estilo.outlineStyle };
    });
    assert.deepEqual(foco, { enlace: '#contenido', ancho: '3px', estilo: 'solid' });

    const movil = page.locator('.menu-movil');
    const menuMovil = await movil.isVisible();
    if (menuMovil) {
      await movil.focus();
      await page.keyboard.press('Enter');
      assert.equal(await movil.getAttribute('aria-expanded'), 'true');
      await page.keyboard.press('Escape');
      assert.equal(await movil.getAttribute('aria-expanded'), 'false');
      assert.equal(await movil.evaluate(el => el === document.activeElement), true);
      await movil.click();
    }
    await page.locator('#navegacion a[href="menu.php"]').click();
    const latte = await page.locator('.menu-item').filter({ hasText: 'Latte de fresa' }).locator('strong').innerText();
    assert.match(latte, /^\$[\d,.]+$/);
    await page.getByRole('link', { name: 'Planea tu visita' }).click();
    assert.ok(page.url().endsWith('/nosotros.php#ubicacion'));
    assert.match(await page.locator('#ubicacion').innerText(), /Calle Segunda y de la Llave/);
    assert.match(await page.getByRole('link', { name: 'Abrir indicaciones' }).getAttribute('href'), /^https:\/\/maps\.app\.goo\.gl\//);

    await page.goto(base + '/productos.php');
    const productos = page.locator('.producto:visible');
    const total = await productos.count();
    await page.locator('#buscador').fill('Ratón Travieso');
    assert.equal(await productos.count(), 1);
    await page.locator('#buscador').fill('zzzz-sin-producto');
    assert.ok(await page.locator('#sin-resultados').isVisible());
    await page.locator('#limpiar-filtros').click();
    assert.equal(await productos.count(), total);
    await page.locator('[data-filtro="Juguetes"]').click();
    assert.ok((await productos.count()) > 0 && (await productos.count()) < total);
    await page.locator('#solo-disponibles').check();
    assert.ok(await productos.evaluateAll(cards => cards.every(card => Number(card.dataset.stock) > 0)));
    assert.deepEqual(fallos, []);
    evidencia.push({ width, latte, foco, menuMovil, tiempos });
    await page.close();
  }

  // Esta opción crea una cuenta ficticia. Retira el producto y la foto al terminar.
  if (process.env.CATSTORE_TEST_WRITE === '1') {
    const correo = 'rubrica-' + randomBytes(6).toString('hex') + '@example.test';
    const clave = randomBytes(18).toString('hex');
    const page = await browser.newPage({ viewport: { width: 360, height: 850 } });
    await page.goto(base + '/registro.php');
    await page.locator('#nombre').fill('Cuenta de rúbrica');
    await page.locator('#correo').fill(correo);
    await page.locator('#contrasena').fill(clave);
    await page.locator('#confirmacion').fill(clave);
    await page.locator('form[action="registro.php"] button[type="submit"]').click();
    await page.waitForURL('**/pedidos.php');
    await page.goto(base + '/productos.php');
    await page.locator('#buscador').fill('Ratón Travieso');
    await page.locator('.producto:visible [data-agregar] button').click();
    await page.waitForFunction(() => document.querySelector('.cuenta-carrito').textContent === '1');
    await page.goto(base + '/pedidos.php');
    await page.getByRole('button', { name: 'Cerrar sesión' }).click();
    await page.goto(base + '/pedidos.php');
    await page.waitForURL('**/login.php');
    await page.close();

    const nueva = await browser.newPage({ viewport: { width: 360, height: 850 } });
    await nueva.goto(base + '/login.php');
    await nueva.locator('#correo').fill(correo);
    await nueva.locator('#contrasena').fill('ClaveIncorrecta');
    await nueva.locator('form[action="login.php"] button[type="submit"]').click();
    assert.match(await nueva.locator('#error-cuenta').innerText(), /no coinciden/);
    await nueva.locator('#contrasena').fill(clave);
    await nueva.locator('form[action="login.php"] button[type="submit"]').click();
    await nueva.waitForURL('**/pedidos.php');
    await nueva.goto(base + '/carrito.php');
    assert.match(await nueva.locator('.carrito-item').innerText(), /Ratón Travieso/);

    await nueva.goto(base + '/pedidos.php');
    const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAIAAABvFaqvAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAIklEQVQ4jWP8sCqKgRqAiSqmjBo0atCoQaMGjRo0ahBFAAAGeAIkcQpawgAAAABJRU5ErkJggg==', 'base64');
    await nueva.locator('#foto-michi').setInputFiles({ name: 'prueba.png', mimeType: 'image/png', buffer: png });
    await nueva.getByRole('button', { name: 'Guardar foto' }).click();
    const foto = nueva.getByRole('img', { name: 'Foto de tu michi' });
    const urlFoto = new URL(await foto.getAttribute('src'), base);
    const archivo = await nueva.request.get(urlFoto.href);
    assert.equal(archivo.headers()['content-type'], 'image/png');
    assert.deepEqual(await archivo.body(), png);
    await nueva.getByRole('button', { name: 'Eliminar foto' }).click();
    await nueva.locator('#foto-michi').waitFor();
    assert.equal((await nueva.request.get(urlFoto.href)).status(), 404);

    await nueva.goto(base + '/carrito.php');
    await nueva.locator('.carrito-item form').filter({ has: nueva.locator('input[value="eliminar"]') }).locator('button').click();
    await nueva.locator('.carrito-item').waitFor({ state: 'detached' });
    await nueva.goto(base + '/pedidos.php');
    await nueva.getByRole('button', { name: 'Cerrar sesión' }).click();
    await nueva.close();
    evidencia.push({ cuenta: correo, flujo: 'registro, carrito persistente, contraseña errónea, pedidos privados, subida y eliminación de PNG, carrito vaciado y cierre' });
  }
  console.log(JSON.stringify(evidencia, null, 2));
} finally {
  await browser.close();
}
