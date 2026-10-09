"""Comprueba consistencia de navegación y rutas locales sin dependencias externas."""
from pathlib import Path
from html.parser import HTMLParser
import re

ROOT = Path(__file__).resolve().parents[1]

class Recursos(HTMLParser):
    def __init__(self):
        super().__init__()
        self.rutas = []
    def handle_starttag(self, tag, attrs):
        for key, value in attrs:
            if key in ('src', 'href') and value and not re.match(r'^(https?:|mailto:|#)', value):
                self.rutas.append(value)

headers, footers = [], []
for page in sorted(ROOT.glob('*.html')):
    source = page.read_text()
    header = re.search(r'<header>.*?</header>', source, re.S)[0]
    assert header.count('aria-current="page"') == 1, page.name
    assert f'href="./{page.name}" aria-current="page"' in header, page.name
    headers.append(header.replace(' aria-current="page"', ''))
    footers.append(re.search(r'<footer.*?</footer>', source, re.S)[0])
    parser = Recursos()
    parser.feed(source)
    for url in parser.rutas:
        assert (page.parent / url).is_file(), (page.name, url)
    assert 'CAOTITOS' in source
assert len(set(headers)) == 1, 'Encabezados diferentes'
assert len(set(footers)) == 1, 'Pies diferentes'
for path in [ROOT/'CSS/style.css', ROOT/'JS/script.js']:
    for url in re.findall(r'[\"\']([^\"\']+\.(?:png|jpg|jpeg|webp))[\"\']', path.read_text()):
        base = path.parent if path.suffix == '.css' else ROOT
        assert (base / url).is_file(), (path, url)
images = [p for p in (ROOT/'assets/img').rglob('*') if p.suffix in ('.png','.jpg','.jpeg','.webp')]
assert len(images) == 19, 'Faltan imágenes del proyecto'
assert len([p for p in images if p.suffix == '.webp']) == 14, 'Faltan imágenes optimizadas'
print('OK: 4 páginas, headers equivalentes, footers idénticos, enlace activo correcto, rutas válidas y 19 imágenes.')
