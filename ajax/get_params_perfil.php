<?php
// ============================================================
// get_params_perfil.php
// Devuelve los parametros de un perfil agrupados por componente:
//
//   tipo=porcentajes  → devuelve array de {id, componente, di, de, h}
//                       donde id es el del registro DI (se usa como
//                       referencia unica de fila; el backend busca
//                       los tres tipos por perfil_id + componente)
//
//   tipo=tolerancias  → devuelve array de {id, componente, di, de}
//                       donde id es el del registro DI
//
// GET params: perfil_id, tipo (porcentajes|tolerancias)
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
header('Content-Type: application/json');

$perfilId = (int)($_GET['perfil_id'] ?? 0);
$tipo     = $_GET['tipo'] ?? '';

if ($perfilId <= 0 || !in_array($tipo, ['porcentajes', 'tolerancias'])) {
    echo json_encode([]); exit;
}

try {
    if ($tipo === 'porcentajes') {
        // Traer todos los registros del perfil y pivotar por componente
        $stmt = $conn->prepare("
            SELECT componente, tipo, porcentaje, id
            FROM porcentajes_perfil
            WHERE perfil_id = :p
            ORDER BY componente ASC, tipo ASC
        ");
        $stmt->execute([':p' => $perfilId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pivotear: una fila por componente con di, de, h
        $pivot = [];
        foreach ($rows as $r) {
            $c = (int)$r['componente'];
            if (!isset($pivot[$c])) {
                $pivot[$c] = ['componente' => $c, 'di' => 1.000, 'de' => 1.000, 'h' => 1.000, 'id' => null];
            }
            switch ($r['tipo']) {
                case 'DI': $pivot[$c]['di'] = (float)$r['porcentaje']; $pivot[$c]['id'] = (int)$r['id']; break;
                case 'DE': $pivot[$c]['de'] = (float)$r['porcentaje']; break;
                case 'H':  $pivot[$c]['h']  = (float)$r['porcentaje']; break;
            }
        }
        echo json_encode(array_values($pivot));

    } else {
        // Tolerancias: pivotear por componente con di, de
        $stmt = $conn->prepare("
            SELECT componente, tipo, tolerancia, id
            FROM tolerancias_perfil
            WHERE perfil_id = :p
            ORDER BY componente ASC, tipo ASC
        ");
        $stmt->execute([':p' => $perfilId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pivot = [];
        foreach ($rows as $r) {
            $c = (int)$r['componente'];
            if (!isset($pivot[$c])) {
                $pivot[$c] = ['componente' => $c, 'di' => 4.00, 'de' => 4.00, 'id' => null];
            }
            switch ($r['tipo']) {
                case 'DI': $pivot[$c]['di'] = (float)$r['tolerancia']; $pivot[$c]['id'] = (int)$r['id']; break;
                case 'DE': $pivot[$c]['de'] = (float)$r['tolerancia']; break;
            }
        }
        echo json_encode(array_values($pivot));
    }

} catch (PDOException $e) {
    echo json_encode([]);
} finally {
    $conn = null;
}
?>