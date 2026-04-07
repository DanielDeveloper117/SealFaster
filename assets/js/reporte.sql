-- Pedidos totales por sucursal en el mes por sucursal
SELECT sucursal, COUNT(*) AS total_pedidos
FROM sellosyr_sellosctd.requisiciones 
WHERE estatus IN ('Finalizada', 'Completada') 
  AND fecha_entrega_barras <= '2026-03-31'
  AND fecha_entrega_barras >= '2026-03-01'
GROUP BY sucursal;


-- Cantidad de sellos correctos/teoricos del mes por sucursal
SELECT 
    r.sucursal, 
    sum(ca.pz_teoricas) AS pz_buenas
FROM sellosyr_sellosctd.control_almacen AS ca
INNER JOIN sellosyr_sellosctd.requisiciones AS r 
    ON ca.id_requisicion = r.id_requisicion  
WHERE 
   r.estatus IN ('Finalizada', 'Completada')
and ca.fecha_registro BETWEEN '2026-03-01' AND '2026-03-31'
  AND (ca.causa_merma IS NULL OR ca.causa_merma = "")
GROUP BY r.sucursal;


-- Cantidad de sellos maquinados del mes 
SELECT 
    sum(ca.pz_teoricas) AS pz_teoricas,
    sum(ca.pz_maquinadas) AS pz_maquinadas,
    sum(ca.scrap_pz) AS pz_scrap
FROM sellosyr_sellosctd.control_almacen AS ca
INNER JOIN sellosyr_sellosctd.requisiciones AS r 
    ON ca.id_requisicion = r.id_requisicion  
WHERE 
r.estatus IN ('Finalizada', 'Completada', 'Detenida')
and ca.fecha_registro BETWEEN '2026-03-01' AND '2026-03-31';


-- Cantidad de sellos por medida del mes
SELECT 
    SUM(CASE WHEN ca.di_sello BETWEEN 0 AND 100 THEN ca.pz_teoricas ELSE 0 END) AS pz_di_0_100,
    SUM(CASE WHEN ca.di_sello BETWEEN 101 AND 169 THEN ca.pz_teoricas ELSE 0 END) AS pz_di_101_169,
    SUM(CASE WHEN ca.di_sello BETWEEN 170 AND 254 THEN ca.pz_teoricas ELSE 0 END) AS pz_di_170_254,
    SUM(CASE WHEN ca.di_sello BETWEEN 255 AND 330 THEN ca.pz_teoricas ELSE 0 END) AS pz_di_255_330,
    SUM(CASE WHEN ca.di_sello BETWEEN 331 AND 610 THEN ca.pz_teoricas ELSE 0 END) AS pz_di_331_610,
    SUM(CASE WHEN ca.di_sello BETWEEN 611 AND 850 THEN ca.pz_teoricas ELSE 0 END) AS pz_di_611_850
FROM sellosyr_sellosctd.control_almacen AS ca
INNER JOIN sellosyr_sellosctd.requisiciones AS r 
    ON ca.id_requisicion = r.id_requisicion  
WHERE 
    r.estatus IN ('Finalizada', 'Completada')
    AND ca.fecha_registro BETWEEN '2026-03-01' AND '2026-03-31';


-- Cantidad de pedidos por días de la semana en el mes
SELECT 
    COUNT(CASE WHEN WEEKDAY(r.fecha_entrega_barras) = 0 THEN 1 END) AS lunes,
    COUNT(CASE WHEN WEEKDAY(r.fecha_entrega_barras) = 1 THEN 1 END) AS martes,
    COUNT(CASE WHEN WEEKDAY(r.fecha_entrega_barras) = 2 THEN 1 END) AS miercoles,
    COUNT(CASE WHEN WEEKDAY(r.fecha_entrega_barras) = 3 THEN 1 END) AS jueves,
    COUNT(CASE WHEN WEEKDAY(r.fecha_entrega_barras) = 4 THEN 1 END) AS viernes,
    COUNT(CASE WHEN WEEKDAY(r.fecha_entrega_barras) = 5 THEN 1 END) AS sabado
FROM 
    sellosyr_sellosctd.requisiciones AS r
WHERE 
    r.estatus IN ('Finalizada', 'Completada')
    AND r.fecha_entrega_barras BETWEEN '2026-03-01' AND '2026-03-31';
	

-- Cantidad de sellos correctos por dia de la semana en el mes
SELECT 
    SUM(CASE WHEN WEEKDAY(ca.fecha_registro) = 0 THEN ca.pz_teoricas ELSE 0 END) AS lunes_piezas,
    SUM(CASE WHEN WEEKDAY(ca.fecha_registro) = 1 THEN ca.pz_teoricas ELSE 0 END) AS martes_piezas,
    SUM(CASE WHEN WEEKDAY(ca.fecha_registro) = 2 THEN ca.pz_teoricas ELSE 0 END) AS miercoles_piezas,
    SUM(CASE WHEN WEEKDAY(ca.fecha_registro) = 3 THEN ca.pz_teoricas ELSE 0 END) AS jueves_piezas,
    SUM(CASE WHEN WEEKDAY(ca.fecha_registro) = 4 THEN ca.pz_teoricas ELSE 0 END) AS viernes_piezas,
    SUM(CASE WHEN WEEKDAY(ca.fecha_registro) = 5 THEN ca.pz_teoricas ELSE 0 END) AS sabado_piezas
FROM 
    sellosyr_sellosctd.requisiciones AS r
inner join 
	sellosyr_sellosctd.control_almacen AS ca
ON ca.id_requisicion = r.id_requisicion 
WHERE 
    r.estatus IN ('Finalizada', 'Completada')
    AND r.fecha_entrega_barras BETWEEN '2026-03-01' AND '2026-03-31';


-- Comparación pz correctas vs pz scrap por maquina en el mes
select 
	sum(case when r.maquina = "Maquina 1" then ca.pz_maquinadas else 0 end ) as maquina_1_maquinadas,
	sum(case when r.maquina = "Maquina 1" then ca.pz_teoricas else 0 end ) as maquina_1_teoricas,
	sum(case when r.maquina = "Maquina 1" then ca.scrap_pz else 0 end ) as maquina_1_scrap,
	sum(case when r.maquina = "Maquina 2" then ca.pz_maquinadas else 0 end ) as maquina_2_maquinadas,
	sum(case when r.maquina = "Maquina 2" then ca.pz_teoricas else 0 end ) as maquina_2_teoricas,
	sum(case when r.maquina = "Maquina 2" then ca.scrap_pz else 0 end ) as maquina_2_scrap,
	sum(case when r.maquina = "Maquina 3" then ca.pz_maquinadas else 0 end ) as maquina_3_maquinadas,
	sum(case when r.maquina = "Maquina 3" then ca.pz_teoricas else 0 end ) as maquina_3_teoricas,
	sum(case when r.maquina = "Maquina 3" then ca.scrap_pz else 0 end ) as maquina_3_scrap,
	sum(case when r.maquina = "Maquina 4" then ca.pz_maquinadas else 0 end ) as maquina_4_maquinadas,
	sum(case when r.maquina = "Maquina 4" then ca.pz_teoricas else 0 end ) as maquina_4_teoricas,
	sum(case when r.maquina = "Maquina 4" then ca.scrap_pz else 0 end ) as maquina_4_scrap
from 
	sellosyr_sellosctd.requisiciones AS r
inner join 
	sellosyr_sellosctd.control_almacen as ca
	on ca.id_requisicion = r.id_requisicion 
WHERE 
    (r.estatus IN ('Finalizada', 'Completada', 'Detenida'))
    AND (r.fecha_entrega_barras BETWEEN '2026-03-01' AND '2026-03-31');


-- Comparación pz correctas vs pz scrap por persona en el mes
select 
	sum(case when r.operador_cnc like "%lexis%" then ca.pz_maquinadas else 0 end ) as alexis_maquinadas,
	sum(case when r.operador_cnc like "%lexis%" then ca.pz_teoricas else 0 end ) as alexis_teoricas,
	sum(case when r.operador_cnc like "%lexis%" then ca.scrap_pz else 0 end ) as alexis_scrap,
	sum(case when r.operador_cnc like "%uis%" then ca.pz_maquinadas else 0 end ) as luis_maquinadas,
	sum(case when r.operador_cnc like "%uis%" then ca.pz_teoricas else 0 end ) as luis_teoricas,
	sum(case when r.operador_cnc like "%uis%" then ca.scrap_pz else 0 end ) as luis_scrap,
	sum(case when r.operador_cnc like "%oram%" then ca.pz_maquinadas else 0 end ) as zoram_maquinadas,
	sum(case when r.operador_cnc like "%oram%" then ca.pz_teoricas else 0 end ) as zoram_teoricas,
	sum(case when r.operador_cnc like "%oram%" then ca.scrap_pz else 0 end ) as zoram_scrap,
	sum(case when r.operador_cnc like "%evin%" then ca.pz_maquinadas else 0 end ) as kevin_maquinadas,
	sum(case when r.operador_cnc like "%evin%" then ca.pz_teoricas else 0 end ) as kevin_teoricas,
	sum(case when r.operador_cnc like "%evin%" then ca.scrap_pz else 0 end ) as kevin_scrap,
	sum(case when r.operador_cnc like "%aniel%" then ca.pz_maquinadas else 0 end ) as daniel_maquinadas,
	sum(case when r.operador_cnc like "%aniel%" then ca.pz_teoricas else 0 end ) as daniel_teoricas,
	sum(case when r.operador_cnc like "%aniel%" then ca.scrap_pz else 0 end ) as daniel_scrap
from 
	sellosyr_sellosctd.requisiciones AS r
inner join 
	sellosyr_sellosctd.control_almacen as ca
	on ca.id_requisicion = r.id_requisicion 
WHERE 
    r.estatus IN ('Finalizada', 'Completada', 'Detenida')
    AND r.fecha_entrega_barras BETWEEN '2026-03-01' AND '2026-03-31';


-- Movimiento de materia prima
SELECT 
    material, 
    COUNT(*) AS veces_mes
FROM sellosyr_sellosctd.control_almacen
WHERE fecha_registro BETWEEN '2026-03-01' AND '2026-03-31'
GROUP BY material
ORDER BY veces_mes DESC; 


-- Top 10 de los perfiles mas pedidos
SELECT 
    perfil_sello, 
    COUNT(*) AS veces_mes
FROM sellosyr_sellosctd.control_almacen
where fecha_registro BETWEEN '2026-03-01' AND '2026-03-31'
GROUP BY perfil_sello
ORDER BY veces_mes desc
limit 10; 


-- Pedidos que no cumplen con la politica
SELECT 
    id_requisicion,
    fecha_autorizacion,
    fin_maquinado,
    HOUR(fecha_autorizacion) AS hora_autorizada
FROM sellosyr_sellosctd.requisiciones
WHERE estatus IN ('Finalizada', 'Completada')
    AND fecha_autorizacion BETWEEN '2026-03-01' AND '2026-03-31'
    -- Antes de las 3pm y NO se entregó el mismo día
    AND ((HOUR(fecha_autorizacion) < 15 AND DATE(fin_maquinado) > DATE(fecha_autorizacion)));

-- REPORTE MERMAS CONTROL ALMACEN PARA CONTABILIDAD
select
	ca.id_requisicion ,
	ca.material as Material,
	ca.clave as Clave,
	ca.medida as Medida_barra,
	ca.pz_teoricas as pz_teoricas,
	ca.altura_pz as altura_pz,
	ca.mm_teoricos as mm_teoricos,
	ca.mm_entrega ,
	ca.mm_retorno ,
	(ca.mm_entrega - ca.mm_retorno) as diferencia_entrega_retorno,
	ca.mm_usados as usados_por_operador,
	((ca.mm_entrega - ca.mm_retorno) - ca.mm_usados ) as merma_diferencia_vs_usados,
	ca.pz_maquinadas as pz_maquinadas,
	ca.scrap_pz,
	ca.mm_merma_real  as Merma_mm,
	ca.justificacion_merma ,
	ca.fecha_registro as Fecha
from 
	sellosyr_sellosctd.control_almacen as ca
where 
   (ca.mm_merma_real is not null and ca.mm_merma_real != "")
and ca.fecha_registro BETWEEN '2026-03-01' AND '2026-03-31'
AND ca.mm_merma_real > 0;


