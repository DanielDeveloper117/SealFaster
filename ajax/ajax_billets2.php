<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if (isset($_POST['material'])) {

        $material = $_POST['material'];
        $altura_mm = $_POST['altura_mm'];
        $diametro_interior_mm = $_POST['diametro_interior_mm'];
        $diametro_exterior_mm = $_POST['diametro_exterior_mm'];

        $arreglo_excluir = isset($_POST['arreglo_excluir']) ? $_POST['arreglo_excluir'] : [];
        if (!is_array($arreglo_excluir)) {
            $arreglo_excluir = [];
        }

        // ------------------ CONSULTA BASE ------------------
        $sql = "
            SELECT *
            FROM inventario_cnc
            WHERE material = :material
              AND pre_stock >= :pre_stock
              AND interior <= :interior_filtro
              AND exterior >= :exterior_filtro
              AND estatus != 'Eliminado'
        ";

        // ------------------ EXCLUSIÓN DE LOTES ------------------
        if (!empty($arreglo_excluir)) {
            $placeholders = [];
            foreach ($arreglo_excluir as $index => $valor) {
                $placeholders[] = ":excluir_$index";
            }
            $sql .= " AND lote_pedimento NOT IN (" . implode(',', $placeholders) . ")";
        }

        // ------------------ ORDENAMIENTO ------------------
        $sql .= "
            ORDER BY
                ABS(interior - :interior_order) ASC,
                ABS(exterior - :exterior_order) ASC
        ";

        // ------------------ PREPARE ------------------
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':material', $material);
        $stmt->bindValue(':pre_stock', $altura_mm);
        $stmt->bindValue(':interior_filtro', $diametro_interior_mm);
        $stmt->bindValue(':exterior_filtro', $diametro_exterior_mm);

        $stmt->bindValue(':interior_order', $diametro_interior_mm);
        $stmt->bindValue(':exterior_order', $diametro_exterior_mm);

        if (!empty($arreglo_excluir)) {
            foreach ($arreglo_excluir as $index => $valor) {
                $stmt->bindValue(":excluir_$index", $valor);
            }
        }

        $stmt->execute();
        $billets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ------------------ VALIDAR COTIZACIONES VIGENTES ------------------
        if (!empty($billets)) {

            $lotes_inventario = array_column($billets, 'lote_pedimento');

            $sql_cotizaciones = "
                SELECT id_cotizacion, billets, vendedor, fecha_vencimiento
                FROM cotizacion_materiales
                WHERE archivada = 0
                  AND fecha_vencimiento > NOW()
                  AND material = :material
            ";

            $stmt_cotizaciones = $conn->prepare($sql_cotizaciones);
            $stmt_cotizaciones->bindValue(':material', $material);
            $stmt_cotizaciones->execute();

            $cotizaciones_vigentes = $stmt_cotizaciones->fetchAll(PDO::FETCH_ASSOC);

            $lotes_en_cotizacion = [];

            foreach ($cotizaciones_vigentes as $cotizacion) {
                $billets_cotizacion = explode(',', $cotizacion['billets']);

                foreach ($billets_cotizacion as $lote_cotizacion) {
                    $lote_cotizacion = trim($lote_cotizacion);

                    if (in_array($lote_cotizacion, $lotes_inventario)) {
                        if (!isset($lotes_en_cotizacion[$lote_cotizacion])) {
                            $lotes_en_cotizacion[$lote_cotizacion] = [
                                'id_cotizacion' => $cotizacion['id_cotizacion'],
                                'vendedor' => $cotizacion['vendedor'],
                                'fecha_vencimiento' => $cotizacion['fecha_vencimiento']
                            ];
                        }
                    }
                }
            }

            foreach ($billets as &$billet) {
                $lote = $billet['lote_pedimento'];

                if (
                    isset($lotes_en_cotizacion[$lote]) &&
                    $billet['estatus'] === 'Disponible para cotizar'
                ) {
                    $billet['estatus'] = 'En cotización';
                    $billet['id_cotizacion'] = $lotes_en_cotizacion[$lote]['id_cotizacion'];
                    $billet['vendedor'] = $lotes_en_cotizacion[$lote]['vendedor'];
                    $billet['fecha_vencimiento'] = $lotes_en_cotizacion[$lote]['fecha_vencimiento'];
                }
            }
            unset($billet);
        }

        echo json_encode($billets);
    }

} catch (PDOException $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
