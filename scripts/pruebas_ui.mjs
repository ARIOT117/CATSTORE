// Ejecutar solo contra una instalación de prueba. Dependencia externa: playwright.
const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');
import assert from 'node:assert/strict';
const base = process.env.CATSTORE_TEST_URL;
if (!base) throw new Error('Indica CATSTORE_TEST_URL de una instalación de prueba.');
const browser = await chromium.launch({
  headless: true,
  ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}),
  args: ['--no-sandbox'],
});
const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
await page.goto(base + '/menu.php');
await page.locator('.menu-movil').click();
assert.equal(await page.locator('.menu-movil').getAttribute('aria-expanded'), 'true');
await page.keyboard.press('Escape');
assert.equal(await page.locator('.menu-movil').getAttribute('aria-expanded'), 'false');
const navCount = await page.evaluate(() => performance.getEntriesByType('navigation').length);
await page.locator('.menu-categorias a[href="#limonadas"]').click();
await page.waitForFunction(
  () =>
    Math.abs(
      document.getElementById('limonadas').getBoundingClientRect().top -
        parseFloat(
          getComputedStyle(document.documentElement).getPropertyValue('--menu-scroll-offset'),
        ),
    ) < 4,
);
assert.equal(await page.locator('.menu-grupo').count(), 6);
assert.equal(
  await page.evaluate(() => performance.getEntriesByType('navigation').length),
  navCount,
);
assert.equal(
  await page.locator('.menu-categorias a[href="#limonadas"]').getAttribute('aria-current'),
  'true',
);
await page.locator('.menu-categorias a[href="#sin-cafe"]').focus();
await page.keyboard.press('Enter');
assert.equal(await page.evaluate(() => document.activeElement.id), 'sin-cafe');
await page.emulateMedia({ reducedMotion: 'reduce' });
await page.goto(base + '/menu.php#frappes');
assert.ok(await page.locator('#frappes').isVisible());
await page.goto(base + '/productos.php');
await page.locator('#buscador').fill('zzzz-sin-producto');
assert.ok(await page.locator('#sin-resultados').isVisible());
await page.locator('#limpiar-filtros').click();
assert.equal(await page.locator('.producto:visible').count(), 11);
await page.locator('[data-filtro="Juguetes"]').click();
assert.ok((await page.locator('.producto:visible').count()) > 0);
await page.locator('#solo-disponibles').check();
assert.equal(
  await page
    .locator('.producto:visible')
    .evaluateAll((cards) => cards.every((c) => Number(c.dataset.stock) > 0)),
  true,
);
await page.locator('[data-filtro="Todos"]').click();
await page.locator('[data-agregar] button').first().click();
await page.waitForFunction(() => document.querySelector('.cuenta-carrito').textContent === '1');
await page.goto(base + '/carrito.php');
assert.ok(await page.locator('.carrito-item').isVisible());
await page.goto(base + '/login.php');
await page.locator('[data-ver-password]').click();
assert.equal(await page.locator('#contrasena').getAttribute('type'), 'text');
await page.goto(base + '/nosotros.php');
await page.locator('summary').first().click();
assert.ok((await page.locator('details').first().getAttribute('open')) !== null);
assert.deepEqual(errors, []);
// No JS: navegación interna, formularios y contenido siguen accesibles.
const plain = await browser.newPage({
  javaScriptEnabled: false,
  viewport: { width: 390, height: 844 },
});
await plain.goto(base + '/menu.php');
assert.equal(await plain.locator('.menu-grupo').count(), 6);
await plain.locator('.menu-categorias a[href="#sin-cafe"]').click();
assert.ok(plain.url().endsWith('#sin-cafe'));
await plain.close();
// Cambios visuales solicitados: enlace de contacto, personajes y movimiento opcional.
await page.emulateMedia({ reducedMotion: 'no-preference' });
await page.goto(base + '/index.php');
assert.ok(
  await page.evaluate(() =>
    document.getAnimations().some((a) => a.animationName === 'cinta-deslizar'),
  ),
);
assert.equal(await page.locator('[data-pausar-movimiento]').count(), 0);
await page.emulateMedia({ reducedMotion: 'reduce' });
assert.equal(await page.evaluate(() => document.getAnimations().length), 0);
const linkWhatsApp = new URL(await page.locator('.contacto-whatsapp').getAttribute('href'));
assert.equal(linkWhatsApp.pathname, '/526141205926');
assert.ok(linkWhatsApp.searchParams.get('text').includes('Tengo una duda'));
await page.goto(base + '/menu.php');
assert.equal(await page.locator('.menu-gato-sello').count(), 6);
// Carrito vacío en una sesión nueva: el gato descansa sobre fondo negro.
const vacio = await browser.newPage({ viewport: { width: 390, height: 844 } });
await vacio.goto(base + '/carrito.php');
assert.equal(
  await vacio.locator('.estado-gato').evaluate((el) => getComputedStyle(el).backgroundColor),
  'rgb(0, 0, 0)',
);
await vacio.close();
console.log(
  'OK: navegación sin recarga, anclas, selección activa, teclado, menú móvil, filtros, disponibilidad, carrito AJAX, contraseña, FAQ y contenido sin JS.',
);
await browser.close();
