<?php
/** Exportación privada de un pedido a un archivo CSV; no crea archivos públicos. */
require __DIR__.'/app.php';
requerirUsuario();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); header('Allow: GET'); exit; }
$id=filter_var($_GET['pedido']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$id) { http_response_code(400); exit('Pedido no válido.'); }
try {
    $db=obtenerConexion();
    $q=$db->prepare('SELECT id,creado_en,total_centavos FROM sc_pedidos WHERE id=? AND usuario_id=?');
    $q->execute([$id,usuarioId()]); $pedido=$q->fetch();
    if (!$pedido) { http_response_code(404); exit('Pedido no encontrado.'); }
    $q=$db->prepare('SELECT nombre,cantidad,precio_centavos FROM sc_pedido_items WHERE pedido_id=? ORDER BY id');
    $q->execute([$id]); $items=$q->fetchAll();
    $archivo=tmpfile();
    if ($archivo===false) throw new RuntimeException('No se pudo crear el archivo.');
    // Evita que los nombres se interpreten como fórmulas en una hoja de cálculo.
    $texto=static fn(string $v): string => preg_match('/^[\s]*[=+@\-]/u',$v) ? "'".$v : $v;
    $fila=static function(array $datos) use ($archivo): void {
        if (fputcsv($archivo,$datos,',','"','')===false) throw new RuntimeException('No se pudo escribir el archivo.');
    };
    fwrite($archivo,"\xEF\xBB\xBF");
    $fila(['Snacks and Cats','Pedido SC-'.str_pad((string)$id,5,'0',STR_PAD_LEFT)]);
    $fila(['Demostración sin cobro','Fecha UTC',$pedido['creado_en']]);
    $fila(['Producto','Cantidad','Precio unitario MXN','Subtotal MXN']);
    foreach ($items as $item) $fila([$texto($item['nombre']),$item['cantidad'],number_format((int)$item['precio_centavos']/100,2,'.',''),number_format((int)$item['cantidad']*(int)$item['precio_centavos']/100,2,'.','')]);
    $fila(['TOTAL MXN','','',number_format((int)$pedido['total_centavos']/100,2,'.','')]);
    $tamano=ftell($archivo); rewind($archivo);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="pedido-SC-'.$id.'.csv"');
    header('Content-Length: '.$tamano);
    fpassthru($archivo); fclose($archivo);
} catch (Throwable $error) {
    error_log((string)$error); http_response_code(503);
    exit('No pudimos generar tu comprobante. Vuelve a intentarlo más tarde.');
}
