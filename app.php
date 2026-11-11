<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/data/menu-repositorio.php';
date_default_timezone_set('America/Chihuahua');
if (session_status()!==PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode','1'); ini_set('session.gc_maxlifetime','2592000');
    session_name('snacks_cats');
    session_set_cookie_params(['lifetime'=>2592000,'path'=>rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/')),'/').'/', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: strict-origin-when-cross-origin'); header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; img-src 'self' https://images.openpetfoodfacts.org https://images.pexels.com data:; style-src 'self'; script-src 'self'; frame-src https://maps.google.com https://www.google.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
$_SESSION['csrf']??=bin2hex(random_bytes(32));
function e(mixed $s): string { return htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function precio(int $c): string { return '$'.number_format($c/100,2,'.',','); }
function campoCsrf(): string { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function verificarCsrf(): void {
    if (!is_string($_POST['csrf']??null) || !hash_equals($_SESSION['csrf'],$_POST['csrf'])) { http_response_code(403); exit('El formulario expiró. Regresa, recarga la página e inténtalo de nuevo.'); }
}
function ir(string $ruta): never { header('Location: '.$ruta,true,303); exit; }
function aviso(string $s,string $tipo='exito'): void { $_SESSION['aviso']=['texto'=>$s,'tipo'=>$tipo]; }
function usuarioId(): int { return (int)($_SESSION['usuario_id']??0); }
function requerirUsuario(): void { if (!usuarioId()) { aviso('Inicia sesión para consultar tus pedidos.','info'); ir('login.php'); } }
function icono(string $nombre): string {
    $p=[
      'flecha'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
      'bolsa'=>'<path d="M5 7h14l1 14H4L5 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
      'usuario'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
      'pin'=>'<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
      'cafe'=>'<path d="M3 8h14v7a6 6 0 0 1-12 0V8m14 1h1a3 3 0 1 1 0 6h-2M3 22h16M7 2v2m5-2v2"/>',
      'corazon'=>'<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8L12 22l8.8-9.6a5.5 5.5 0 0 0 0-7.8Z"/>',
      'buscar'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
      'mas'=>'<path d="M12 5v14M5 12h14"/>',
      'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
      'reloj'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
      'check'=>'<path d="m5 12 4 4L19 6"/>',
      'salir'=>'<path d="M9 3H4v18h5m5-14 5 5-5 5m-6-5h11"/>',
      'huella'=>'<ellipse cx="12" cy="16" rx="5" ry="4"/><ellipse cx="5" cy="9" rx="2" ry="3"/><ellipse cx="10" cy="5" rx="2" ry="3"/><ellipse cx="16" cy="6" rx="2" ry="3"/><ellipse cx="20" cy="11" rx="2" ry="3"/>'
    ];
    return '<svg class="icono" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($p[$nombre]??$p['huella']).'</svg>';
}
function catalogoRespaldo(): array { return json_decode(file_get_contents(__DIR__.'/data/catalogo.json'),true,512,JSON_THROW_ON_ERROR); }
function catalogo(): array {
    try { return obtenerConexion()->query('SELECT * FROM sc_productos WHERE activo=1 ORDER BY id')->fetchAll(); }
    catch (PDOException $e) { error_log('Catálogo no disponible: '.$e->getMessage()); return catalogoRespaldo(); }
}
function carritoCrudo(): array {
    if (!usuarioId()) return $_SESSION['carrito']??[];
    $q=obtenerConexion()->prepare('SELECT producto_id,cantidad FROM sc_carrito WHERE usuario_id=? ORDER BY producto_id');
    $q->execute([usuarioId()]); return array_map('intval',array_column($q->fetchAll(),'cantidad','producto_id'));
}
function carritoDetalle(): array {
    $cantidades=carritoCrudo(); $items=[]; $total=0;
    foreach (catalogo() as $p) { $n=(int)($cantidades[$p['id']]??0); if ($n>0) { $p['cantidad']=$n; $p['subtotal']=$n*(int)$p['precio_centavos']; $items[]=$p; $total+=$p['subtotal']; } }
    return ['items'=>$items,'total'=>$total];
}
function contadorCarrito(): int { try { return array_sum(carritoCrudo()); } catch (PDOException $e) { return 0; } }
function guardarCantidad(int $producto,int $cantidad): void {
    if (!usuarioId()) { if (!$cantidad) unset($_SESSION['carrito'][$producto]); else $_SESSION['carrito'][$producto]=$cantidad; return; }
    $db=obtenerConexion();
    if (!$cantidad) { $q=$db->prepare('DELETE FROM sc_carrito WHERE usuario_id=? AND producto_id=?'); $q->execute([usuarioId(),$producto]); }
    else { $q=$db->prepare('INSERT INTO sc_carrito(usuario_id,producto_id,cantidad) VALUES(?,?,?) ON DUPLICATE KEY UPDATE cantidad=VALUES(cantidad)'); $q->execute([usuarioId(),$producto,$cantidad]); }
}
function autenticar(array $u): void {
    $db=obtenerConexion(); $guest=$_SESSION['carrito']??[]; $db->beginTransaction();
    try {
        $q=$db->prepare('SELECT id FROM usuarios WHERE id=? FOR UPDATE'); $q->execute([$u['id']]);
        foreach ($guest as $id=>$n) {
            $q=$db->prepare('SELECT stock FROM sc_productos WHERE id=? AND activo=1'); $q->execute([(int)$id]); $stock=$q->fetchColumn();
            if ($stock===false || (int)$stock<1) continue;
            $max=min(20,(int)$stock);
            $q=$db->prepare('INSERT INTO sc_carrito(usuario_id,producto_id,cantidad) VALUES(?,?,?) ON DUPLICATE KEY UPDATE cantidad=LEAST(?,cantidad+VALUES(cantidad))');
            $q->execute([$u['id'],(int)$id,min($max,(int)$n),$max]);
        }
        $db->commit();
    } catch (Throwable $e) { $db->rollBack(); throw $e; }
    session_regenerate_id(true); $_SESSION['usuario_id']=(int)$u['id']; $_SESSION['nombre']=$u['nombre']; $_SESSION['usuario']=$u['correo'];
    unset($_SESSION['carrito']); $_SESSION['csrf']=bin2hex(random_bytes(32));
}
function confirmarPedido(string $clave): int {
    $db=obtenerConexion(); $db->beginTransaction();
    try {
        $q=$db->prepare('SELECT id FROM usuarios WHERE id=? FOR UPDATE'); $q->execute([usuarioId()]);
        $q=$db->prepare('SELECT id FROM sc_pedidos WHERE usuario_id=? AND clave=?'); $q->execute([usuarioId(),$clave]);
        if ($id=$q->fetchColumn()) { $db->commit(); return (int)$id; }
        $q=$db->prepare('SELECT p.*,c.cantidad FROM sc_carrito c JOIN sc_productos p ON p.id=c.producto_id WHERE c.usuario_id=? ORDER BY p.id FOR UPDATE'); $q->execute([usuarioId()]); $items=$q->fetchAll();
        if (!$items) throw new DomainException('Tu carrito está vacío. Agrega algo bonito para tu michi.');
        $total=0;
        foreach ($items as $p) {
            if (!$p['activo'] || $p['cantidad']>$p['stock'] || $p['cantidad']<1 || $p['cantidad']>20) {
                throw new DomainException('Revisa la disponibilidad de '.$p['nombre'].'.');
            }
            $total+=(int)$p['precio_centavos']*(int)$p['cantidad'];
        }
        $q=$db->prepare('INSERT INTO sc_pedidos(usuario_id,clave,total_centavos) VALUES(?,?,?)'); $q->execute([usuarioId(),$clave,$total]); $id=(int)$db->lastInsertId();
        $q=$db->prepare('INSERT INTO sc_pedido_items(pedido_id,producto_id,nombre,imagen,precio_centavos,cantidad) VALUES(?,?,?,?,?,?)');
        foreach ($items as $p) $q->execute([$id,$p['id'],$p['nombre'],$p['imagen'],$p['precio_centavos'],$p['cantidad']]);
        // Demostración: no se descuenta inventario comercial ni se realizan cobros.
        $q=$db->prepare('DELETE FROM sc_carrito WHERE usuario_id=?'); $q->execute([usuarioId()]);
        $db->commit(); return $id;
    } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); throw $e; }
}

/** Fotos ilustrativas seleccionadas por categoría, independientes del orden de destacados. */
function fotoBebida(string $categoria): array {
    return match ($categoria) {
        'cafe' => ['https://images.pexels.com/photos/2476344/pexels-photo-2476344.jpeg?auto=compress&cs=tinysrgb&w=1000', 'Latte servido en una taza de cristal'],
        'limonadas' => ['https://images.pexels.com/photos/33991092/pexels-photo-33991092.jpeg?auto=compress&cs=tinysrgb&w=1000', 'Limonada rosa en un vaso con hielo'],
        'sin-cafe' => ['https://images.pexels.com/photos/34491288/pexels-photo-34491288.jpeg?auto=compress&cs=tinysrgb&w=1000', 'Latte de matcha con fresas'],
        default => ['https://images.pexels.com/photos/2476344/pexels-photo-2476344.jpeg?auto=compress&cs=tinysrgb&w=1000', 'Bebida de referencia de cafetería'],
    };
}
