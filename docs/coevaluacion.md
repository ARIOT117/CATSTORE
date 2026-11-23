# Coevaluación de Snacks and Cats

Abre la dirección de `URL.txt`. Las pruebas en Railway se hicieron sobre la versión publicada el 12 de noviembre de 2026. Consulta el informe de entrega para identificar las diferencias con el ZIP.

| Criterio | Acción en la URL publicada | Resultado esperado | Comprobé realmente y pendiente |
| --- | --- | --- | --- |
| Estructura | Recorre Inicio, El menú, La cafetería, Tiendita y Carrito. Abre Acceder y entra en Mi cuenta. | Cada pantalla tiene un encabezado y los enlaces llevan a la sección indicada. | Verifiqué las siete rutas en Railway y en local, incluida la redirección de Mis pedidos como visitante. |
| Colores | Lee los precios de El menú y el buscador de Tiendita con luz normal. | Precios, textos y controles se distinguen sobre su fondo. | Axe no detectó fallos automáticos de contraste en ambos entornos y dejó elementos para revisión manual. La comodidad de lectura con luz real queda pendiente del compañero. Consulta el informe para los resultados de contraste. |
| Usabilidad | Abre la navegación móvil, busca Ratón Travieso y filtra por Juguetes. | El menú abre y cierra y las tarjetas responden a la búsqueda y al filtro. | Verifiqué el menú a 360 px, la navegación a 768 y 1366 px y los filtros en ambos entornos. La facilidad percibida queda pendiente. |
| Accesibilidad | Pulsa Tab desde Inicio y usa Enter y Escape en el menú móvil. | Aparece «Saltar al contenido», se distingue el foco y Escape devuelve el foco al botón. | Pasó en Chromium local y Railway. Queda pendiente una prueba con lector de pantalla y una persona usuaria. |
| Eficiencia | Localiza Latte de fresa en El menú y llega a la ubicación mediante «Planea tu visita». | Ves el precio y puedes llegar a la dirección desde el menú. | El precio era $70.00 MXN en ambos entornos. Medí tiempos de navegación. Queda pendiente cronometrar a una persona con su propio dispositivo y red. |
| Control de errores | Busca `zzzz-sin-producto`, pulsa «Ver todos los productos» y ensaya una contraseña incorrecta una sola vez. | Aparece un estado vacío y un control de recuperación. Acceder explica que los datos no coinciden. | Probado en Chromium en local y Railway. La validación de archivo y cantidades se amplió en la base temporal. |
| Permanencia de datos | Con una cuenta ficticia, agrega Ratón Travieso y cierra sesión. Accede otra vez desde una ventana privada y abre Carrito. | Sigue apareciendo el mismo artículo y Mis pedidos pertenece a la cuenta. | Carrito entre dos sesiones en Railway y local. Pedido demo recuperado desde MySQL/MariaDB en local. Pedido nuevo en Railway pendiente. |
| Propósito | Desde Inicio, identifica cómo consultar bebidas, planear la visita y ver artículos para gatos. | La portada conduce a El menú, La cafetería y Tiendita. | Verifiqué contenido y enlaces en Railway. Queda pendiente preguntar al compañero qué entendió sin orientarlo. |
| Satisfacción | Completa el recorrido y explica qué te gustó y qué cambiarías. | El compañero puede valorar si terminó con confianza y proponer una mejora. | Pendiente de una persona. |
| Memorabilidad | Luego de una pausa, vuelve a buscar el latte y la ubicación, sin usar el historial. | El compañero recuerda por dónde entrar y repite las tareas. | Pendiente de una persona. |

## Recorrido breve desde celular

1. Abre Inicio y usa el menú de navegación para entrar en **El menú**. Busca **Latte de fresa**, que durante la prueba costaba **$70.00 MXN**.
2. Abre **Planea tu visita**. Comprueba que la dirección indique Calle Segunda y de la Llave, Colonia Centro, Chihuahua. Usa **Abrir indicaciones** para continuar a Google Maps.
3. En **Tiendita**, escribe `Ratón Travieso`. Prueba `zzzz-sin-producto` y usa el botón del estado vacío para recuperar el listado.
4. Entra con una cuenta ficticia creada para la clase. Busca de nuevo el ratón, agrégalo y abre **Carrito**. Desde **Mi cuenta**, pulsa **Cerrar sesión** y vuelve a **Acceder**, preferiblemente en una ventana privada. Comprueba que el artículo siga en el carrito. Retíralo al acabar la demostración.

## Demostrar escritura en la base de datos

En una instalación local con una base temporal, registra una cuenta desde **Acceder → Crea tu cuenta**. Usa un nombre inventado y un correo como `clase@example.test`. Al registrarte, el formulario PHP escribe un usuario con contraseña almacenada como hash. Desde tu cliente SQL local puedes mostrar la fila y el indicador de hash, sin mostrar el hash completo.

```sql
SELECT id, nombre, correo, CHAR_LENGTH(password_hash) > 0 AS tiene_hash
FROM usuarios WHERE correo = 'clase@example.test';
```

Agrega un artículo con esa cuenta y consulta la persistencia del carrito antes de retirarlo.

```sql
SELECT c.producto_id, c.cantidad
FROM sc_carrito AS c JOIN usuarios AS u ON u.id = c.usuario_id
WHERE u.correo = 'clase@example.test';
```

Puedes confirmar un pedido demo en la instalación local y volver a encontrarlo en **Mis pedidos** tras iniciar sesión de nuevo. En la URL pública se verificó la persistencia con el carrito y se dejó el pedido sin confirmar.

## Demostrar gestión de archivos

En **Mi cuenta → La foto de tu michi**, usa una imagen de prueba generada para la clase, como un PNG de 24 × 24 px. Pulsa **Guardar foto**, verifica que aparezca en la cuenta y en la navegación, vuelve a acceder y comprueba que siga visible. Pulsa **Eliminar foto** y verifica que vuelva a aparecer el selector. Para mostrar control de errores una vez retirada la foto, intenta subir un archivo de texto renombrado a `prueba.png`. El servidor comprueba el tipo real y muestra el mensaje de formato.

La demostración usa permisos de una cuenta normal. Para proyectar la consulta SQL, abre previamente un cliente local con acceso a la base temporal. Crea las credenciales para la sesión de clase y mantenlas fuera del ZIP y de la grabación.
