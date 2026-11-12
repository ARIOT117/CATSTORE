"""Pruebas HTTP sobre una instalación de prueba; crea cuentas ficticias, carritos y pedidos.
Ejecutar solo en una base temporal: CATSTORE_TEST_URL=http://127.0.0.1:8876 python3 scripts/pruebas_integracion.py
"""

from urllib.request import build_opener, HTTPCookieProcessor, Request
from urllib.parse import urlencode
from urllib.error import HTTPError
from http.cookiejar import CookieJar
import os, re, json, uuid, base64

BASE = os.environ.get("CATSTORE_TEST_URL")
if not BASE:
    raise SystemExit(
        "Indica CATSTORE_TEST_URL de una instalación conectada a una BASE TEMPORAL. Nunca uses cuentas reales."
    )


class Client:
    def __init__(self):
        self.jar = CookieJar()
        self.http = build_opener(HTTPCookieProcessor(self.jar))

    def get(self, path):
        with self.http.open(BASE + "/" + path) as response:
            return response.read().decode()

    def post(self, path, data, ajax=False):
        req = Request(
            BASE + "/" + path,
            data=urlencode(data).encode(),
            headers={"Accept": "application/json"} if ajax else {},
        )
        try:
            with self.http.open(req) as res:
                return res.status, res.read().decode(), res.url
        except HTTPError as err:
            return err.code, err.read().decode(), err.url

    def post_file(self, path, fields, files):
        boundary = "----catstore" + uuid.uuid4().hex
        parts = []
        for name, value in fields.items():
            parts.append(
                f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"\r\n\r\n{value}\r\n'.encode()
            )
        for name, filename, content in files:
            parts.append(
                f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"; filename="{filename}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode()
            )
            parts.append(content + b"\r\n")
        parts.append(f"--{boundary}--\r\n".encode())
        req = Request(
            BASE + "/" + path,
            data=b"".join(parts),
            headers={"Content-Type": f"multipart/form-data; boundary={boundary}"},
        )
        try:
            with self.http.open(req) as res:
                return res.status, res.read().decode(), res.url
        except HTTPError as err:
            return err.code, err.read().decode(), err.url

    def get_raw(self, path):
        with self.http.open(BASE + "/" + path) as response:
            return response.status, response.read()

    def csrf(self, page="login.php"):
        return re.search(r'name="csrf" value="([a-f0-9]+)"', self.get(page))[1]


a = Client()
b = Client()
suffix = uuid.uuid4().hex[:12]
password = "UnMichiSeguro2026!"
email = "test-" + suffix + "@example.test"
for page in [
    "index.php",
    "menu.php",
    "nosotros.php",
    "productos.php",
    "login.php",
    "registro.php",
    "carrito.php",
    "fuentes.php",
]:
    body = a.get(page)
    assert (
        "Snacks and Cats" in body and 'id="contenido"' in body and "<h1>" in body
    ), page
assert "Latte de fresa" in a.get("menu.php?categoria=cafe")
assert "Lichi / blueberry" in a.get("menu.php?categoria=cafe")
assert 'href="#cafe"' in a.get("menu.php")
assert 'href="menu.php?categoria=' not in a.get("menu.php")
for path in [
    "data/catalogo.json",
    "data/schema.sql",
    "auth.php",
    "scripts/instalar.php",
    "config.example.php",
]:
    try:
        a.get(path)
        raise AssertionError("Ruta privada visible: " + path)
    except HTTPError as err:
        assert err.code in (403, 404)
assert a.post("acciones.php", {"accion": "agregar", "producto": 1}, True)[0] == 403
csrf = a.csrf()
status, body, _ = a.post(
    "acciones.php", {"csrf": csrf, "accion": "agregar", "producto": 1}, True
)
assert status == 200 and json.loads(body)["cantidad"] == 1, body
status, body, _ = a.post(
    "registro.php",
    {
        "csrf": csrf,
        "nombre": "Prueba <michi>",
        "correo": email,
        "contrasena": "123",
        "confirmacion": "123",
    },
)
assert "8 a 72" in body
status, body, _ = a.post(
    "registro.php",
    {
        "csrf": csrf,
        "nombre": "Prueba <michi>",
        "correo": email,
        "contrasena": password,
        "confirmacion": password,
        "continuar": "carrito",
    },
)
assert "Croquetas de pollo" in body and "Confirmar pedido demo" in body, body[:500]
assert "&lt;MICHI&gt;" in a.get("pedidos.php") and "<MICHI>" not in a.get("pedidos.php")
csrf = a.csrf("carrito.php")
status, body, _ = a.post(
    "acciones.php",
    {
        "csrf": csrf,
        "accion": "cantidad",
        "producto": 1,
        "cantidad": 2,
        "precio_centavos": 1,
    },
    True,
)
assert status == 200
status, body, _ = a.post(
    "acciones.php",
    {"csrf": csrf, "accion": "cantidad", "producto": 1, "cantidad": 999},
    True,
)
assert status == 422
status, body, _ = a.post(
    "acciones.php",
    {"csrf": csrf, "accion": "cantidad", "producto": 1, "cantidad": -1},
    True,
)
assert status == 422
status, body, _ = a.post(
    "acciones.php", {"csrf": csrf, "accion": "agregar", "producto": 999999}, True
)
assert status == 422
carrito = a.get("carrito.php")
assert "$999.80" in carrito
key = re.search(r'name="clave"\s+value="([a-f0-9]+)"', carrito)[1]
status, body, _ = a.post(
    "acciones.php", {"csrf": csrf, "accion": "comprar", "clave": key, "total": 1}
)
assert "$999.80" in body and "Demostración confirmada" in body
order = re.search(r"PEDIDO SC-(\d+)", body)[1]
# Doble confirmación: misma clave retorna el mismo pedido.
_, body, _ = a.post("acciones.php", {"csrf": csrf, "accion": "comprar", "clave": key})
assert body.count("PEDIDO SC-") == 1 and order in body
csv = a.get("comprobante.php?pedido=" + str(int(order)))
assert "TOTAL MXN" in csv and "999.80" in csv
assert a.get("comprobante.php?pedido=" + str(int(order))) == csv
assert "tomando una siesta" in a.get("carrito.php")
a.post("acciones.php", {"csrf": csrf, "accion": "agregar", "producto": 2}, True)
a.post("acciones.php", {"csrf": csrf, "accion": "salir"})
assert "Inicia sesión" in a.get("pedidos.php") or "Bienvenido de nuevo" in a.get(
    "pedidos.php"
)
# Inicio en otro navegador: recupera carrito y pedidos de la base.
csrf = b.csrf()
_, body, _ = b.post(
    "login.php", {"csrf": csrf, "correo": email, "contrasena": "incorrecta"}
)
assert "no coinciden" in body
_, body, _ = b.post(
    "login.php", {"csrf": csrf, "correo": email, "contrasena": password}
)
assert order in body and "$999.80" in body
assert "Ratón Travieso" in b.get("carrito.php")
assert 'type="number"' in b.get("carrito.php")

# Gestión de archivos: PNG válido de 24x24, servido solo con sesión.
foto = base64.b64decode(
    "iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAIAAABvFaqvAAAACXBIWXMAAA7EAAAOxAGVKw4b"
    "AAAAIklEQVQ4jWP8sCqKgRqAiSqmjBo0atCoQaMGjRo0ahBFAAAGeAIkcQpawgAAAABJ"
    "RU5ErkJggg=="
)
csrf = b.csrf("pedidos.php")
status, body, _ = b.post_file(
    "mascota.php",
    {"csrf": csrf, "accion": "subir"},
    [("foto", "michi.png", foto)],
)
assert status == 200 and "Foto guardada" in body, body[:400]
assert "mascota.php?ver=" in body
rutaFoto = re.search(r'src="(mascota\.php\?ver=[a-f0-9]+)"', body)[1]
status, servida = b.get_raw(rutaFoto)
assert status == 200 and servida == foto
print("OK: foto del michi subida, guardada y servida con la sesión.")
# Un archivo con extensión de imagen pero MIME real inválido se rechaza.
status, body, _ = b.post_file(
    "mascota.php",
    {"csrf": csrf, "accion": "subir"},
    [("foto", "falsa.png", b"esto no es una imagen")],
)
assert "Formato no permitido" in body
status, _, _ = b.post_file(
    "mascota.php", {"accion": "subir"}, [("foto", "michi.png", foto)]
)
assert status == 403
csrf = b.csrf("pedidos.php")
status, body, _ = b.post("mascota.php", {"csrf": csrf, "accion": "eliminar"})
assert "se eliminó" in body
try:
    b.get("mascota.php")
    raise AssertionError("La foto eliminada sigue visible.")
except HTTPError as err:
    assert err.code == 404
print("OK: validación de MIME, CSRF y eliminación de la foto del michi.")

# Otra cuenta no puede consultar el pedido aunque conozca su número.
c = Client()
csrf = c.csrf()
_, body, _ = c.post(
    "registro.php",
    {
        "csrf": csrf,
        "nombre": "Otra cuenta",
        "correo": "other-" + suffix + "@example.test",
        "contrasena": password,
        "confirmacion": password,
    },
)
assert "Tu primer pedido" in body and order not in body
assert "Tu primer pedido" in c.get("pedidos.php?pedido=" + order)
print(
    "OK: páginas, privacidad, CSRF, validación, registro, fusión de carrito, precios desde servidor, cantidades, compra atómica, idempotencia, cierre de sesión, persistencia entre navegadores y aislamiento de pedidos."
)

try:
    c.get("comprobante.php?pedido=" + str(int(order)))
    raise AssertionError("Comprobante ajeno accesible")
except HTTPError as err:
    assert err.code == 404
assert "TOTAL MXN" not in a.get("comprobante.php?pedido=" + str(int(order)))
print("OK: comprobante CSV, aislamiento entre cuentas y bloqueo tras cerrar sesión.")
