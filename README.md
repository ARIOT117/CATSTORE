# CAOTITOS

Pequeños gatos, grandes travesuras. Proyecto académico de Desarrollo Web, Unidad 2, construido con HTML, CSS y JavaScript.

## Ejecutar

```bash
python3 -m http.server 8765 --bind 127.0.0.1
```

Abre `http://127.0.0.1:8765/`. Incluye Inicio, Productos, Nosotros y un formulario demostrativo de acceso; no autentica ni guarda credenciales.

## Verificar

```bash
python3 scripts/verificar.py
node --check JS/script.js
node --check JS/login.js
```

Consulta [REPORTE.md](REPORTE.md), [imágenes](assets/img/README.md), [evidencias de código](docs/codigo/) y [capturas de interfaz](docs/capturas/).

La marca visible es **CAOTITOS**. La copia local independiente está en `Escritorio/CAOTITOS`, con su propio Git. Repositorio de publicación: [ARIOT117/CATSTORE](https://github.com/ARIOT117/CATSTORE), independiente de INGENERIA. Vercel publica desde la raíz del repositorio, sin compilación, en [catstore-puce.vercel.app](https://catstore-puce.vercel.app).

## Derechos de uso

Copyright © 2026 ARIOT117. Todos los derechos reservados. Consulta [LICENSE](LICENSE) en el repositorio independiente. El código y los recursos pueden verse públicamente, pero no se concede permiso para reutilizarlos fuera de los usos permitidos por la ley y los términos de GitHub.

## Rendimiento de imágenes

Las 14 imágenes que se usan en la web están redimensionadas y convertidas a WebP: 20,05 MiB antes, 0,75 MiB después. El gato principal tiene prioridad de carga y las fotografías del catálogo cargan al acercarse a la pantalla. Los originales anteriores siguen disponibles en el historial de Git.
