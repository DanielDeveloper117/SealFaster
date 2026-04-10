<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if (!isset($_POST['perfil'])) {
        echo json_encode(['error' => 'Parametro perfil requerido']);
        exit;
    }

    $perfil = $_POST['perfil'];

    // ============================================================
    // DATOS GENERALES DEL PERFIL + FAMILIA
    // ============================================================
    $stmtPerfil = $conn->prepare("
        SELECT
            p.id,
            p.nombre,
            p.detalles,
            p.cantidad_componentes,
            p.es_chevron,
            p.con_resorte_en,
            p.es_wiper_en,
            p.con_escalon_en,
            p.es_wiper_especial_en,
            p.grupo_herramienta_id,
            f.nombre      AS familia_nombre,
            f.nombre2     AS familia_nombre2
        FROM perfiles2 p
        JOIN familias f ON f.id = p.familia_id
        WHERE p.nombre = :perfil
        LIMIT 1
    ");
    $stmtPerfil->bindParam(':perfil', $perfil);
    $stmtPerfil->execute();
    $datosPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);

    if (!$datosPerfil) {
        echo json_encode(false);
        exit;
    }

    $perfilId            = $datosPerfil['id'];
    $grupoHerramientaId  = $datosPerfil['grupo_herramienta_id'];

    // ============================================================
    // PORCENTAJES DE COMPONENTES
    // Devuelve todas las filas de porcentajes_perfil para este perfil.
    // El JS los indexa como p_h_m1..m5, p_di_m1..m5, p_de_m1..m5
    // ============================================================
    $stmtPorcentajes = $conn->prepare("
        SELECT
            componente,
            tipo,
            porcentaje
        FROM porcentajes_perfil
        WHERE perfil_id = :perfil_id
        ORDER BY componente ASC, tipo ASC
    ");
    $stmtPorcentajes->bindParam(':perfil_id', $perfilId);
    $stmtPorcentajes->execute();
    $filasPorcentajes = $stmtPorcentajes->fetchAll(PDO::FETCH_ASSOC);

    // Construir estructura indexada por componente y tipo
    // para que el JS pueda acceder como data.porcentajes.H[1], data.porcentajes.DI[2], etc.
    $porcentajes = [
        'H'  => [],
        'DI' => [],
        'DE' => []
    ];
    foreach ($filasPorcentajes as $fila) {
        $porcentajes[$fila['tipo']][(int)$fila['componente']] = (float)$fila['porcentaje'];
    }

    // ============================================================
    // TOLERANCIAS DE COMPONENTES
    // Devuelve tolerancias DI y DE por componente.
    // Si no existe registro para un componente, el JS usa fallback de 4.00
    // ============================================================
    $stmtTolerancias = $conn->prepare("
        SELECT
            componente,
            tipo,
            tolerancia
        FROM tolerancias_perfil
        WHERE perfil_id = :perfil_id
        ORDER BY componente ASC, tipo ASC
    ");
    $stmtTolerancias->bindParam(':perfil_id', $perfilId);
    $stmtTolerancias->execute();
    $filasTolerancias = $stmtTolerancias->fetchAll(PDO::FETCH_ASSOC);

    $tolerancias = [
        'DI' => [],
        'DE' => []
    ];
    foreach ($filasTolerancias as $fila) {
        $tolerancias[$fila['tipo']][(int)$fila['componente']] = (float)$fila['tolerancia'];
    }

    // ============================================================
    // LIMITANTES DE HERRAMIENTA
    // Solo si el perfil tiene grupo_herramienta_id asignado.
    // Devuelve todas las herramientas del grupo separadas por dureza.
    // El JS reconstruye la misma estructura que tenia hardcodeada.
    // ============================================================
    $limitantes = null;

    if ($grupoHerramientaId) {
        $stmtLimitantes = $conn->prepare("
            SELECT
                h.numero        AS herramienta,
                lh.dureza,
                lh.di_min,
                lh.di_max,
                lh.de_min,
                lh.de_max,
                lh.seccion_min,
                lh.seccion_max,
                lh.h_min,
                lh.h_max
            FROM limitantes_herramienta lh
            JOIN herramientas h ON h.id = lh.herramienta_id
            WHERE lh.grupo_herramienta_id = :grupo_id
            ORDER BY lh.dureza ASC, h.numero ASC
        ");
        $stmtLimitantes->bindParam(':grupo_id', $grupoHerramientaId);
        $stmtLimitantes->execute();
        $filasLimitantes = $stmtLimitantes->fetchAll(PDO::FETCH_ASSOC);

        // Construir estructura equivalente al hardcoding:
        // limitantes['blandos']['112'] = { di_min, di_max, ... }
        // limitantes['duros']['112']   = { di_min, di_max, ... }
        $limitantes = [
            'blandos' => [],
            'duros'   => []
        ];
        foreach ($filasLimitantes as $fila) {
            $dureza      = $fila['dureza'];
            $herramienta = $fila['herramienta'];
            $limitantes[$dureza][$herramienta] = [
                'DI_MIN'      => (float)$fila['di_min'],
                'DI_MAX'      => (float)$fila['di_max'],
                'DE_MIN'      => (float)$fila['de_min'],
                'DE_MAX'      => (float)$fila['de_max'],
                'SECCION_MIN' => (float)$fila['seccion_min'],
                'SECCION_MAX' => (float)$fila['seccion_max'],
                'H_MIN'       => (float)$fila['h_min'],
                'H_MAX'       => (float)$fila['h_max'],
            ];
        }
    }

    // ============================================================
    // RESPUESTA FINAL
    // ============================================================
    echo json_encode([
        // datos generales
        'id'                   => (int)$datosPerfil['id'],
        'perfil'               => $datosPerfil['nombre'],
        'detalles'             => $datosPerfil['detalles'],
        'familia_nombre'       => $datosPerfil['familia_nombre'],
        'familia_nombre2'      => $datosPerfil['familia_nombre2'],
        'cantidad_componentes' => (int)$datosPerfil['cantidad_componentes'],
        'es_chevron'           => $datosPerfil['es_chevron'],
        'con_resorte_en'       => $datosPerfil['con_resorte_en'],
        'es_wiper_en'          => $datosPerfil['es_wiper_en'],
        'con_escalon_en'       => $datosPerfil['con_escalon_en'],
        'es_wiper_especial_en' => $datosPerfil['es_wiper_especial_en'],
        'tiene_limitantes'     => $grupoHerramientaId !== null,

        // porcentajes indexados por tipo y componente
        // JS accede como: data.porcentajes.H[1], data.porcentajes.DI[2]
        'porcentajes'          => $porcentajes,

        // tolerancias indexadas por tipo y componente
        // JS accede como: data.tolerancias.DI[1], data.tolerancias.DE[2]
        // si no existe el componente el JS usa fallback de 4.00
        'tolerancias'          => $tolerancias,

        // limitantes con la misma estructura que el hardcoding
        // null si el perfil no tiene grupo asignado
        'limitantes'           => $limitantes,
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>