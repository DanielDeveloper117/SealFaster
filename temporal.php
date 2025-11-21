<?php
$sqlCotizaciones = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
$stmtCotizaciones = $conn->prepare($sqlCotizaciones);
$stmtCotizaciones->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
$stmtCotizaciones->execute();
$result = $stmtCotizaciones->fetch(PDO::FETCH_ASSOC);

if (!$result || empty($result['cotizaciones'])) {
    echo 'No se encontraron cotizaciones.';
    exit;
}

$cotizacion_ids = explode(', ', $result['cotizaciones']);

$sql = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion ORDER BY cantidad_material ASC";
$stmt = $conn->prepare($sql);
$CONTEO_CLAVES = 0;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('Arial', 'B', 9);

// CABECERA DE LA TABLA
$pdf->Cell(10, 6, 'Cant.', 1, 0, 'C', true);
$pdf->Cell(15, 6, 'Perfil', 1, 0, 'C', true);
$pdf->Cell(23, 6, 'Material', 1, 0, 'C', true);
$pdf->Cell(33, 6, 'D. Interior', 1, 0, 'C', true);
$pdf->Cell(33, 6, 'D. Exterior', 1, 0, 'C', true);
$pdf->Cell(33, 6, 'Altura(s)', 1, 0, 'C', true);
$pdf->Cell(43, 6, 'Lote Pedimento/Clave', 1, 1, 'C', true);

foreach ($cotizacion_ids as $id_cotizacion) {
    $stmt->bindValue(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
    $stmt->execute();
    $cotizacionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cotizacionData)) continue;
    
    $cotGeneral = $cotizacionData[0];
    
    // Query para información del perfil
    $sqlPerfil = "SELECT * FROM perfiles WHERE perfil = :perfil";
    $stmtPerfil = $conn->prepare($sqlPerfil);
    $stmtPerfil->bindParam(':perfil', $cotGeneral['perfil_sello']);
    $stmtPerfil->execute();
    $arregoPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
    $familiaPerfil = $arregoPerfil["tipo"] ?? '';
    
    $pdf->SetFont('Arial', '', 8);

    // === PREPARAR DATOS PARA LAS COLUMNAS (COMUNES) ===
    $arrayDI = [];
    $arrayDE = [];
    $alturas = [];
    
    // Llenar arrays con los datos
    $alturas[] = "Total:";
    $arrayDI[] = "";
    $arrayDE[] = "";
    $alturas[] = $cotGeneral['a_sello']."mm/".mm_a_pulgadas($cotGeneral['a_sello']).'"';
    $arrayDI[] = $cotGeneral['di_sello']."mm/".mm_a_pulgadas($cotGeneral['di_sello']).'"';
    $arrayDE[] = $cotGeneral['de_sello']."mm/".mm_a_pulgadas($cotGeneral['de_sello']).'"';
    
    // Agregar alturas adicionales si existen
    $alturasAdicionales = [
        'altura_caja' => 'Caja:',
        'altura_escalon' => 'Escalón:', 
        'altura_h2' => 'H2:',
        'altura_h3' => 'H3:'
    ];
    
    foreach ($alturasAdicionales as $campo => $etiqueta) {
        if ($cotGeneral[$campo] !== "0.00" && $cotGeneral[$campo] !== "0") {
            $alturas[] = $etiqueta;
            $alturas[] = $cotGeneral[$campo]."mm/".mm_a_pulgadas($cotGeneral[$campo]).'"';
            $arrayDI[] = "";
            $arrayDE[] = "";
            $arrayDI[] = "";
            $arrayDE[] = "";
        }
    }
    
    $alturas[] = '              '.$cotGeneral['tipo_medida_h'];
    $arrayDI[] = $cotGeneral['tipo_medida_di'];
    $arrayDE[] = $cotGeneral['tipo_medida_de'];
    
    // === CALCULAR ALTURA MÁXIMA PARA LAS COLUMNAS MULTILÍNEA ===
    $lineHeight = 4;
    $numLineasAltura = count($alturas);
    $numLineasDI = count($arrayDI);
    $numLineasDE = count($arrayDE);
    $maxLineas = max($numLineasAltura, $numLineasDI, $numLineasDE);
    $rowHeightGeneral = $maxLineas * $lineHeight;

    // =============================================
    // ESTRATEGIA 1: MÚLTIPLES REGISTROS (> 1)
    // =============================================
    if (count($cotizacionData) > 1) {
        
        // === RENGLÓN GENERAL (PRIMER RENGLÓN) ===
        $xStart = $pdf->GetX();
        $yStart = $pdf->GetY();
        
        // Celda 1: Cantidad (guion)
        $pdf->Cell(10, $rowHeightGeneral, utf8_decode("-"), 1, 0, 'C');
        
        // Celda 2: Perfil (dato real)
        $pdf->Cell(15, $rowHeightGeneral, utf8_decode($cotGeneral['perfil_sello']), 1, 0, 'C');
        
        // Celda 3: Material (guion)
        $pdf->Cell(23, $rowHeightGeneral, utf8_decode("-"), 1, 0, 'C');
        
        // Celda 4: Diámetro Interior (MultiCell - datos reales)
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell(33, $lineHeight, utf8_decode(implode("\n", $arrayDI)), 1, 'C');
        $pdf->SetXY($x + 33, $y);
        
        // Celda 5: Diámetro Exterior (MultiCell - datos reales)
        $pdf->MultiCell(33, $lineHeight, utf8_decode(implode("\n", $arrayDE)), 1, 'C');
        $pdf->SetXY($x + 66, $y);
        
        // Celda 6: Alturas (MultiCell - datos reales)
        $pdf->MultiCell(33, $lineHeight, utf8_decode(implode("\n", $alturas)), 1, 'L');
        $pdf->SetXY($x + 99, $y);
        
        // Celda 7: Claves (guion)
        $pdf->Cell(43, $rowHeightGeneral, utf8_decode("-"), 1, 1, 'C');
        
        // === RENGLONES INDIVIDUALES (REGISTROS ESPECÍFICOS) ===
        foreach ($cotizacionData as $cot) {
            // Preparar datos de billets para este registro específico
            $billets = array_map('trim', explode(',', $cot['billets_claves_lotes']));
            $CONTEO_CLAVES += count($billets);
            
            $bloques = [];
            foreach ($billets as $item) {
                if (preg_match('/^([^\s]+)\s+([^\s]+)\s*(\([^)]+\)\s*\d+\s*pz)?$/i', $item, $m)) {
                    $lote = trim($m[1] ?? '');
                    $clave = trim($m[2] ?? '');
                    $resto = trim($m[3] ?? '');
                } else {
                    $lote = trim($item);
                    $clave = '';
                    $resto = '';
                }
                
                $manuales = !empty($cot['billets_manualmente'])
                    ? array_map(fn($v) => strtoupper(trim($v)), explode(',', $cot['billets_manualmente']))
                    : [];
                    
                $lote_normalizado = strtoupper(trim($lote));
                if (in_array($lote_normalizado, $manuales)) {
                    $lote .= '*';
                }
                
                $bloques[] = trim($clave . "\n" . $lote . ($resto ? "\n" . $resto : ''));
            }
            
            $textoFinal = utf8_decode(implode("\n_________________________\n", $bloques));
            
            // Calcular altura para la columna de claves
            $numLinesClaves = 0;
            foreach ($bloques as $b) {
                $numLinesClaves += substr_count($b, "\n") + 1;
            }
            $numLinesClaves += count($bloques) - 1;
            $rowHeightClaves = $numLinesClaves * $lineHeight;
            
            // === DIBUJAR RENGLÓN INDIVIDUAL ===
            $xStart = $pdf->GetX();
            $yStart = $pdf->GetY();
            
            // Celda 1: Cantidad (dato real)
            $pdf->Cell(10, $rowHeightClaves, utf8_decode($cot['cantidad']." pz"), 1, 0, 'C');
            
            // Celda 2: Perfil (dato real)
            $pdf->Cell(15, $rowHeightClaves, utf8_decode($cot['perfil_sello']), 1, 0, 'C');
            
            // Celda 3: Material (dato real)
            $pdf->Cell(23, $rowHeightClaves, utf8_decode($cot['material']), 1, 0, 'C');
            
            // Celda 4: Diámetro Interior (vacío)
            $pdf->Cell(33, $rowHeightClaves, "", 1, 0, 'C');
            
            // Celda 5: Diámetro Exterior (vacío)
            $pdf->Cell(33, $rowHeightClaves, "", 1, 0, 'C');
            
            // Celda 6: Alturas (vacío)
            $pdf->Cell(33, $rowHeightClaves, "", 1, 0, 'C');
            
            // Celda 7: Claves (MultiCell - datos reales)
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell(43, $lineHeight, $textoFinal, 1, 'L');
            $pdf->SetXY($x + 43, $y + $rowHeightClaves);
        }
        
    } 
    // =============================================
    // ESTRATEGIA 2: UN SOLO REGISTRO (= 1)
    // =============================================
    else {
        $cot = $cotizacionData[0]; // Único registro
        
        // Preparar datos de billets
        $billets = array_map('trim', explode(',', $cot['billets_claves_lotes']));
        $CONTEO_CLAVES += count($billets);
        
        $bloques = [];
        foreach ($billets as $item) {
            if (preg_match('/^([^\s]+)\s+([^\s]+)\s*(\([^)]+\)\s*\d+\s*pz)?$/i', $item, $m)) {
                $lote = trim($m[1] ?? '');
                $clave = trim($m[2] ?? '');
                $resto = trim($m[3] ?? '');
            } else {
                $lote = trim($item);
                $clave = '';
                $resto = '';
            }
            
            $manuales = !empty($cot['billets_manualmente'])
                ? array_map(fn($v) => strtoupper(trim($v)), explode(',', $cot['billets_manualmente']))
                : [];
                
            $lote_normalizado = strtoupper(trim($lote));
            if (in_array($lote_normalizado, $manuales)) {
                $lote .= '*';
            }
            
            $bloques[] = trim($clave . "\n" . $lote . ($resto ? "\n" . $resto : ''));
        }
        
        $textoFinal = utf8_decode(implode("\n_________________________\n", $bloques));
        
        // Calcular altura para la columna de claves
        $numLinesClaves = 0;
        foreach ($bloques as $b) {
            $numLinesClaves += substr_count($b, "\n") + 1;
        }
        $numLinesClaves += count($bloques) - 1;
        $rowHeightClaves = $numLinesClaves * $lineHeight;
        
        // USAR LA MISMA ALTURA PARA TODAS LAS CELDAS (la máxima)
        $finalRowHeight = max($rowHeightGeneral, $rowHeightClaves);
        
        // === DIBUJAR ÚNICO RENGLÓN COMPLETO ===
        $xStart = $pdf->GetX();
        $yStart = $pdf->GetY();
        
        // Celda 1: Cantidad (dato real)
        $pdf->Cell(10, $finalRowHeight, utf8_decode($cot['cantidad']." pz"), 1, 0, 'C');
        
        // Celda 2: Perfil (dato real)
        $pdf->Cell(15, $finalRowHeight, utf8_decode($cot['perfil_sello']), 1, 0, 'C');
        
        // Celda 3: Material (dato real)
        $pdf->Cell(23, $finalRowHeight, utf8_decode($cot['material']), 1, 0, 'C');
        
        // Celda 4: Diámetro Interior (MultiCell - datos reales)
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell(33, $lineHeight, utf8_decode(implode("\n", $arrayDI)), 1, 'C');
        $pdf->SetXY($x + 33, $y);
        
        // Celda 5: Diámetro Exterior (MultiCell - datos reales)
        $pdf->MultiCell(33, $lineHeight, utf8_decode(implode("\n", $arrayDE)), 1, 'C');
        $pdf->SetXY($x + 66, $y);
        
        // Celda 6: Alturas (MultiCell - datos reales)
        $pdf->MultiCell(33, $lineHeight, utf8_decode(implode("\n", $alturas)), 1, 'L');
        $pdf->SetXY($x + 99, $y);
        
        // Celda 7: Claves (MultiCell - datos reales)
        $pdf->MultiCell(43, $lineHeight, $textoFinal, 1, 'L');
        
        // Mover a siguiente línea
        $pdf->SetXY($xStart, $yStart + $finalRowHeight);
    }
    
    // === COMENTARIOS Y NOTAS (COMÚN PARA AMBOS CASOS) ===
    $sqlComentarios = "SELECT * FROM comentarios_adjuntos WHERE id_cotizacion = :id_cotizacion";
    $stmtComentarios = $conn->prepare($sqlComentarios);
    $stmtComentarios->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
    $stmtComentarios->execute();
    $arrayComentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($arrayComentarios) > 0){
        foreach($arrayComentarios as $comentario){
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(25, 6, utf8_decode("Comentario:"), 1, 0, 'R', 1);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(165, 6, utf8_decode($comentario["comentario"]), 1, 1, 'L', 0);
        }
        $pdf->Ln(5);
    }
    
    if(!empty($cotGeneral['billets_manualmente'])){
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(190, 6,utf8_decode("*Esta cotización cuenta con barras seleccionadas manualmente y no fueron sugeridas por el sistema."), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 8);
    }
    
    // Separación entre cotizaciones
    $pdf->Ln(5); 
}
?>