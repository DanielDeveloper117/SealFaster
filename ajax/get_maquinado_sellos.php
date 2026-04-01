<?php
session_start();

require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_GET['id_requisicion'])) {
    echo json_encode(['error' => 'ID de requisición no proporcionado']);
    exit;
}

$id_requisicion = intval($_GET['id_requisicion']);

// Obtener IDs de cotizaciones asociadas a la requisición
$sqlCotizaciones = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
$stmtCotizaciones = $conn->prepare($sqlCotizaciones);
$stmtCotizaciones->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
$stmtCotizaciones->execute();
$result = $stmtCotizaciones->fetch(PDO::FETCH_ASSOC);

if (!$result || empty($result['cotizaciones'])) {
    echo json_encode(['error' => 'No se encontraron cotizaciones']);
    exit;
}

$cotizacion_ids = array_map('trim', explode(',', $result['cotizaciones']));

$cotizacionesData = [];

// Consultar información de cada cotización
$sql = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion ORDER BY cantidad_material ASC";
$stmt = $conn->prepare($sql);

foreach ($cotizacion_ids as $id_cotizacion) {
    $stmt->bindValue(':id_cotizacion', intval($id_cotizacion), PDO::PARAM_INT);
    $stmt->execute();
    $cotizacionMateriales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cotizacionMateriales)) continue;
    
    $cotGeneral = $cotizacionMateriales[0];
    
    // Obtener información del perfil
    $sqlPerfil = "SELECT * FROM perfiles WHERE perfil = :perfil";
    $stmtPerfil = $conn->prepare($sqlPerfil);
    $stmtPerfil->bindParam(':perfil', $cotGeneral['perfil_sello']);
    $stmtPerfil->execute();
    $arregloPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
    $familiaPerfil = $arregloPerfil["tipo"] ?? '';
    
    // Estructura de datos de una cotización
    $cotizacionInfo = [
        'id_cotizacion' => $cotGeneral['id_cotizacion'],
        'perfil_sello' => $cotGeneral['perfil_sello'],
        'familia_perfil' => $familiaPerfil,
        'cantidad' => $cotGeneral['cantidad'],
        'material' => $cotGeneral['material'],
        'cantidad_componentes' => count($cotizacionMateriales),
        'componentes' => [],
        'nominales_generales' => [
            'a_sello' => $cotGeneral['a_sello'],
            'di_sello' => $cotGeneral['di_sello'],
            'de_sello' => $cotGeneral['de_sello'],
            'tipo_medida_h' => $cotGeneral['tipo_medida_h'],
            'tipo_medida_di' => $cotGeneral['tipo_medida_di'],
            'tipo_medida_de' => $cotGeneral['tipo_medida_de']
        ]
    ];
    
    // Agregar alturas adicionales
    $alturasAdicionales = [
        'altura_caja' => 'Caja',
        'altura_escalon' => 'Escalón',
        'altura_h2' => 'H2',
        'altura_h3' => 'H3'
    ];
    
    foreach ($alturasAdicionales as $campo => $etiqueta) {
        if (isset($cotGeneral[$campo]) && $cotGeneral[$campo] !== "0.00" && $cotGeneral[$campo] !== "0") {
            $cotizacionInfo['nominales_generales'][$campo] = [
                'label' => $etiqueta,
                'valor' => $cotGeneral[$campo]
            ];
        }
    }
    
    // Procesar componentes
    foreach ($cotizacionMateriales as $componente) {
        $componenteData = [
            'cantidad_material' => $componente['cantidad_material'],
            'billets_claves_lotes' => $componente['billets_claves_lotes'],
            'billets_manualmente' => $componente['billets_manualmente'] ?? '',
            'nominales' => [
                'a_sello' => $componente['altura'],
                'di_sello' => $componente['diametro_int'],
                'de_sello' => $componente['diametro_ext'],
                'tipo_medida_h' => $componente['tipo_medida_h'],
                'tipo_medida_di' => $componente['tipo_medida_di'],
                'tipo_medida_de' => $componente['tipo_medida_de'],
                'altura_caja' => (isset($componente['altura_caja']) && $componente['altura_caja'] !== "0.00" && $componente['altura_caja'] !== "0") ? ['label' => 'Caja', 'valor' => $componente['altura_caja']] : null,
                'altura_escalon' => (isset($componente['altura_escalon']) && $componente['altura_escalon'] !== "0.00" && $componente['altura_escalon'] !== "0") ? ['label' => 'Escalón', 'valor' => $componente['altura_escalon']] : null,
                'altura_h2' => (isset($componente['altura_h2']) && $componente['altura_h2'] !== "0.00" && $componente['altura_h2'] !== "0") ? ['label' => 'H2', 'valor' => $componente['altura_h2']] : null,
                'altura_h3' => (isset($componente['altura_h3']) && $componente['altura_h3'] !== "0.00" && $componente['altura_h3'] !== "0") ? ['label' => 'H3', 'valor' => $componente['altura_h3']] : null,
            ],
            'img' => $componente['img'],
            'barras' => []
        ];
        
        // Procesar información de barras (billets_claves_lotes) - Lógica unificada
        if (!empty($componente['billets_claves_lotes'])) {
            $billetes = explode(',', $componente['billets_claves_lotes']);
            
            $billetesManual = !empty($componente['billets_manualmente']) 
                ? array_map(fn($v) => strtoupper(trim($v)), explode(',', $componente['billets_manualmente'])) 
                : [];
            
            foreach ($billetes as $billete) {
                $billete = trim($billete);
                if (empty($billete)) continue;
                
                // Extraer el lote para identificar si es una selección manual
                // El formato esperado es: CLAVE LOTE (MEDIDA) CANTIDAD
                $partes = explode(' ', $billete);
                $loteIdentificado = isset($partes[0]) ? strtoupper(trim($partes[0])) : '';
                
                $esManual = in_array($loteIdentificado, $billetesManual);
                
                $componenteData['barras'][] = [
                    'barra' => $billete,
                    'tipo' => $esManual ? 'manual' : 'sistema'
                ];
            }
        }
        
        $cotizacionInfo['componentes'][] = $componenteData;
    }
    
    // Obtener comentarios de la cotización
    $sqlComentarios = "SELECT * FROM comentarios_adjuntos WHERE id_cotizacion = :id_cotizacion";
    $stmtComentarios = $conn->prepare($sqlComentarios);
    $stmtComentarios->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
    $stmtComentarios->execute();
    $arrayComentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
    
    $cotizacionInfo['comentarios'] = $arrayComentarios;
    
    $cotizacionesData[] = $cotizacionInfo;
}

echo json_encode([
    'success' => true,
    'cotizaciones' => $cotizacionesData
]);
?>
