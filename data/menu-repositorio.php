<?php
declare(strict_types=1);

/** Una consulta por petición; la siguiente recarga lee de nuevo MySQL. */
function leerMenu(): array {
    static $menu;
    if ($menu!==null) return $menu;
    $filas=obtenerConexion()->query('SELECT c.id AS categoria_id,c.nombre AS categoria_nombre,c.nota,i.id,i.nombre,i.descripcion,i.precio_centavos,i.destacado_orden FROM sc_menu_categorias c JOIN sc_menu_items i ON i.categoria_id=c.id WHERE c.activa=1 AND i.disponible=1 ORDER BY c.orden,c.id,i.orden,i.id')->fetchAll();
    $categorias=[];
    foreach ($filas as $f) {
        $id=$f['categoria_id'];
        if (!isset($categorias[$id])) $categorias[$id]=['id'=>$id,'nombre'=>$f['categoria_nombre'],'nota'=>$f['nota'],'items'=>[]];
        $categorias[$id]['items'][]=['id'=>(int)$f['id'],'nombre'=>$f['nombre'],'descripcion'=>$f['descripcion'],'precio_centavos'=>(int)$f['precio_centavos'],'destacado_orden'=>$f['destacado_orden']===null?null:(int)$f['destacado_orden']];
    }
    return $menu=array_values($categorias);
}

function notaCategoria(array $categoria): string {
    $nota=trim($categoria['nota']);
    $precios=array_unique(array_column($categoria['items'],'precio_centavos'));
    if (count($precios)===1 && count($categoria['items'])>1) $nota.=($nota!==''?' · ':'').'Todas las opciones a '.precio((int)reset($precios)).' MXN';
    return $nota;
}

function destacadosMenu(): array {
    $destacados=[];
    foreach (leerMenu() as $c) foreach ($c['items'] as $i) {
        if ($i['destacado_orden']===null) continue;
        $i['categoria_id']=$c['id']; $i['categoria_nombre']=$c['nombre']; $destacados[]=$i;
    }
    usort($destacados,fn($a,$b)=>[$a['destacado_orden'],$a['id']]<=>[$b['destacado_orden'],$b['id']]);
    return array_slice($destacados,0,3);
}

/** Semilla inicial transaccional: repetir el instalador no revierte ediciones ni bajas. */
function sembrarMenu(PDO $db,string $archivo): void {
    $db->beginTransaction();
    try {
        $q=$db->prepare('INSERT IGNORE INTO sc_migraciones(clave) VALUES(?)'); $q->execute(['menu_inicial_v1']);
        if ($q->rowCount()===0) { $db->commit(); return; }
        $categorias=json_decode(file_get_contents($archivo),true,512,JSON_THROW_ON_ERROR);
        $categoria=$db->prepare('INSERT IGNORE INTO sc_menu_categorias(id,nombre,nota,orden) VALUES(?,?,?,?)');
        $item=$db->prepare('INSERT IGNORE INTO sc_menu_items(clave,categoria_id,nombre,descripcion,precio_centavos,orden,destacado_orden) VALUES(?,?,?,?,?,?,?)');
        foreach ($categorias as $orden=>$c) {
            // Las frases con precios se calculan con los importes actuales, nunca con texto fijo.
            $nota=preg_replace('/(?:\s*·\s*)?(?:Todos|Todas) a \$[0-9]+(?:\s*·\s*MXN)?/u','',$c['nota']);
            $categoria->execute([$c['id'],$c['nombre'],trim($nota),$orden+1]);
            foreach ($c['items'] as $pos=>$i) {
                $clave=$c['id'].'-'.($pos+1);
                $destacado=['cafe-5'=>1,'limonadas-1'=>2,'sin-cafe-3'=>3][$clave]??null;
                $item->execute([$clave,$c['id'],$i['nombre'],$i['descripcion'],(int)round($i['precio']*100),$pos+1,$destacado]);
            }
        }
        $db->commit();
    } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); throw $e; }
}
