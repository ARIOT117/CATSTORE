# Foto de cuenta y avatar

La sección de la foto usa una presentación compacta. Cuando existe una imagen, muestra el retrato y su acción de eliminación; el formulario de carga solo aparece sin foto. La eliminación devuelve la interfaz al estado inicial. El endpoint conserva la capacidad de reemplazo para compatibilidad, aunque la interfaz solicita eliminar primero.

`fotoCuenta()` centraliza la lectura privada de metadatos y evita repetir la consulta en cabecera y pedidos durante la misma petición. `urlFotoCuenta()` versiona la URL con un hash del nombre aleatorio del archivo. El avatar sustituye al icono de cuenta en todas las páginas; la imagen sigue sirviéndose desde `mascota.php`, con autorización mediante sesión.

Verificado en base temporal: carga válida, mismo avatar en cabecera y cuenta, formulario ausente con foto, avatar en otra página, eliminación que restaura formulario/icono y ausencia de desbordamientos a 1440/390/320 px. Pruebas HTTP existentes de cuentas, carrito, pedidos, archivos y CSV: pasan. No se modificaron fotos de cuentas reales para estas pruebas.
