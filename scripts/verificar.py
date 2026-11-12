"""Verificación local de PHP, JS, contenido y recursos; sin modificar MySQL."""

from pathlib import Path
import subprocess, shutil, json, re

ROOT = Path(__file__).resolve().parents[1]
php = shutil.which("php") or (
    "/opt/lampp/bin/php" if Path("/opt/lampp/bin/php").is_file() else None
)
if not php:
    raise SystemExit("No se encontró PHP. Agrega PHP de XAMPP al PATH.")
for file in ROOT.rglob("*.php"):
    result = subprocess.run([php, "-l", str(file)], capture_output=True, text=True)
    assert result.returncode == 0, result.stdout + result.stderr
subprocess.run(["node", "--check", str(ROOT / "JS/app.js")], check=True)
products = json.loads((ROOT / "data/catalogo.json").read_text())
assert len(products) >= 11
assert len({p["id"] for p in products}) == len(products)
assert len({p.get("codigo") for p in products if p.get("codigo")}) == len(
    [p for p in products if p.get("codigo")]
)
for product in products:
    assert (
        isinstance(product["precio_centavos"], int) and product["precio_centavos"] > 0
    )
    if not product["imagen"].startswith("https://"):
        assert (ROOT / product["imagen"]).is_file(), product["imagen"]
menu = json.loads((ROOT / "data/menu.json").read_text())
assert len(menu) == 6 and sum(len(c["items"]) for c in menu) == 33
for file in ROOT.glob("*.php"):
    for path in re.findall(
        r'(?:src|href|action)="((?:assets/|CSS/|JS/)[^"?]+)"', file.read_text()
    ):
        assert (ROOT / path).is_file(), (file.name, path)
assert "JS/app.js" in (ROOT / "header.php").read_text()
assert "leerMenu()" in (ROOT / "menu.php").read_text()
assert "destacadosMenu()" in (ROOT / "index.php").read_text()
assert (ROOT / "data/schema-menu.sql").is_file()
assert "Require all denied" in (ROOT / "data/.htaccess").read_text()
assert "Require all denied" in (ROOT / "scripts/.htaccess").read_text()
print(
    "OK: sintaxis PHP y JavaScript, 11+ productos, seis categorías / 33 bebidas, recursos locales y protección de carpetas."
)

# El registro editorial impide asignar el mismo gato a dos ubicaciones.
resultado_personajes = subprocess.run(
    [php, "-r", 'echo json_encode(require "data/personajes.php");'],
    cwd=ROOT, capture_output=True, text=True, check=True,
)
personajes = json.loads(resultado_personajes.stdout)
assert len(set(personajes.values())) == len(personajes), "Hay gatos repetidos en el registro."
for ruta in personajes.values():
    assert (ROOT / ruta).is_file(), ruta
print("OK: personajes únicos y recursos de cada ubicación presentes.")
