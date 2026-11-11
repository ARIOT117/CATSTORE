<?php
// Integración exclusiva para una base temporal: cambia datos, comprueba HTML y restaura los originales.
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/auth.php';
require dirname(__DIR__).'/data/menu-repositorio.php';
if (!str_starts_with(configuracion()['database'],'snacks_cats_test') || !getenv('CATSTORE_TEST_URL')) exit("Configura una base snacks_cats_test y CATSTORE_TEST_URL.\n");
$db=obtenerConexion();
function comprobar(bool $ok,string $mensaje): void { if (!$ok) throw new RuntimeException($mensaje); }
function pagina(string $ruta): string { return file_get_contents(rtrim(getenv('CATSTORE_TEST_URL'),'/').'/'.$ruta); }
function restaurar(PDO $db,string $tabla,array $fila): void {
    $campos=array_keys($fila);
    $sql='INSERT INTO '.$tabla.' (`'.implode('`,`',$campos).'`) VALUES ('.implode(',',array_fill(0,count($campos),'?')).') ON DUPLICATE KEY UPDATE '.implode(',',array_map(fn($c)=>'`'.$c.'`=VALUES(`'.$c.'`)',$campos));
    $db->prepare($sql)->execute(array_values($fila));
}
$original=$db->query("SELECT * FROM sc_menu_items WHERE clave='cafe-5'")->fetch();
$matcha=$db->query("SELECT * FROM sc_menu_items WHERE clave='sin-cafe-3'")->fetch();
$eliminada=$db->query("SELECT * FROM sc_menu_items WHERE clave='otras-6'")->fetch();
$categoria=$db->query("SELECT * FROM sc_menu_categorias WHERE id='sin-cafe'")->fetch();
comprobar((bool)$original && (bool)$matcha && (bool)$eliminada && (bool)$categoria,'Falta la semilla inicial de prueba.');
try {
    comprobar(substr_count(pagina('menu.php'),'class="menu-item"')===33,'No se ven las 33 bebidas.');
    $db->prepare('UPDATE sc_menu_items SET nombre=?,descripcion=?,precio_centavos=? WHERE id=?')->execute(['Lavanda prueba <michi>','Descripción de prueba',8250,$original['id']]);
    $menu=pagina('menu.php?categoria=cafe');$inicio=pagina('index.php');
    comprobar(str_contains($menu,'$82.50') && str_contains($inicio,'$82.50'),'El precio no se actualiza en menú e inicio.');
    comprobar(str_contains($menu,'Lavanda prueba &lt;michi&gt;') && str_contains($inicio,'Lavanda prueba &lt;michi&gt;'),'Nombre sin actualizar o sin escape HTML.');
    comprobar(str_contains($menu,'Descripción de prueba') && str_contains($inicio,'Descripción de prueba'),'Descripción sin actualizar.');
    sembrarMenu($db,dirname(__DIR__).'/data/menu.json');
    comprobar((int)$db->query("SELECT precio_centavos FROM sc_menu_items WHERE clave='cafe-5'")->fetchColumn()===8250,'La reinstalación sobrescribió el precio.');
    $db->prepare('UPDATE sc_menu_items SET disponible=0 WHERE id=?')->execute([$original['id']]);
    comprobar(!str_contains(pagina('index.php'),'Lavanda prueba') && !str_contains(pagina('menu.php'),'Lavanda prueba'),'Una bebida oculta sigue publicada.');
    comprobar(substr_count(pagina('menu.php'),'class="menu-item"')===32,'Contador/listado no refleja la baja.');
    $db->prepare('UPDATE sc_menu_items SET precio_centavos=8250 WHERE id=?')->execute([$matcha['id']]);
    comprobar(!str_contains(pagina('menu.php?categoria=sin-cafe'),'Todas las opciones a'),'Leyenda de precio común desactualizada.');
    $db->exec("UPDATE sc_menu_categorias SET activa=0 WHERE id='sin-cafe'");
    comprobar(!str_contains(pagina('index.php'),'Matcha fresa'),'Categoría oculta sigue en destacados.');
    $db->prepare('DELETE FROM sc_menu_items WHERE id=?')->execute([$eliminada['id']]);
    sembrarMenu($db,dirname(__DIR__).'/data/menu.json');
    comprobar((int)$db->query("SELECT COUNT(*) FROM sc_menu_items WHERE clave='otras-6'")->fetchColumn()===0,'La reinstalación revivió una bebida eliminada.');
    $db->prepare('INSERT INTO sc_menu_items(clave,categoria_id,nombre,descripcion,precio_centavos,orden) VALUES(?,?,?,?,?,?)')->execute(['prueba-menu-auto','cafe','Bebida nueva de prueba','Nueva descripción',9050,-1]);
    comprobar(str_contains(pagina('menu.php?categoria=cafe'),'Bebida nueva de prueba') && str_contains(pagina('menu.php?categoria=cafe'),'$90.50'),'La bebida nueva no aparece.');
    echo "OK: cambios de nombre, descripción y precio en menú e inicio; escape HTML; disponibilidad, categorías, contadores y precios comunes; altas automáticas e instalación idempotente.\n";
} finally {
    $db->exec("DELETE FROM sc_menu_items WHERE clave='prueba-menu-auto'");
    restaurar($db,'sc_menu_categorias',$categoria);
    foreach ([$original,$matcha,$eliminada] as $fila) restaurar($db,'sc_menu_items',$fila);
    echo "Datos de prueba restaurados.\n";
}
