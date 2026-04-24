<?php
require_once(__DIR__ . '/../../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'fpdf/fpdf.php');
//ANCHO VISIBLE DE RENGLONES 190
class PDF extends FPDF {
    // Cabecera de página
    function Header() {
        //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
        $this->SetFont('Arial', '', 8); 
        // AnchoCelda, AltoCelda, titulo, borde(1-0), saltoLinea(1-0), posicion(L-C-R), ColorFondo(1-0)
        $fechaEmision = new DateTime();
        $fechaVigencia = (clone $fechaEmision)->modify('+1 year');
        $meses = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
        ];
        $textoEmision = "Emision: " . $meses[$fechaEmision->format('F')] . " " . $fechaEmision->format('Y');
        $textoVigencia = "Vigencia: " . $meses[$fechaVigencia->format('F')] . " " . $fechaVigencia->format('Y');
        $this->Cell(30, 0, utf8_decode($textoEmision), 0, 0, '', 0);
        $this->SetFont('Arial', '', 12); 
        $this->Cell(45);
        $this->Cell(30, 0, utf8_decode("DEPARTAMENTO"), 0, 0, '', 0);
        $this->Cell(68);
        $this->SetFont('Arial', '', 8); 
        $this->Cell(30, 0, utf8_decode("Version: 001"), 0, 1, '', 0);
        $this->Cell(30, 8, utf8_decode($textoVigencia), 0, 0, 'L', 0);
        $this->Cell(42);
        $this->SetFont('Arial', '', 14); 
        $this->Cell(30, 10, utf8_decode("Sellos Maquinados"), 0, 0, '', 0);
        $this->Cell(70);
        $this->SetFont('Arial', '', 8); 
        $this->Cell(30, 8, utf8_decode("Revisión: 001"), 0, 0, 'L', 0);
        $this->Ln(10); 
        $this->SetFont('Arial', 'B', 16); 
        $this->SetFillColor(23, 47, 91);    // Fondo azul oscuro
        $this->SetTextColor(255, 255, 255); // Texto blanco
        $this->Cell(42, 20, utf8_decode(''), 0, 0, '', true);
        $this->Image('../../assets/img/general/logo-srs.jpg', 11, 20, 30); //logo de la empresa, moverDerecha, moverAbajo, tamañoIMG
        $this->Cell(148, 20, utf8_decode('REQUISICIÓN PARA MAQUINADO DE SELLOS'), 0, 1, 'L', true);
        $this->Ln(3); 
    } 

    // Pie de página
    function Footer() {
        $this->SetY(-15); // Posición: a 1,5 cm del final
        $this->SetFont('Arial', 'B', 12);

        // Celda para la parte izquierda (nombre de la empresa)
        $this->Cell(0, 15, "Sellos y Retenes de San Luis S.A. de C.V.", 0, 0, 'L');

        // Mover posición X para la siguiente celda (derecha)
        $this->SetX(-60); // Ajusta según el ancho de la URL

        // Celda para la parte derecha (URL)
        $this->Cell(0, 15, "www.sellosyretenes.com", 0, 0, 'R');
    }

    // Obtiene el valor de PageBreakTrigger (es protegida, necesitamos un getter)
    function GetPageBreakTrigger() {
        return $this->PageBreakTrigger;
    }

    // Función para verificar si hay espacio suficiente para las firmas
    function CheckPageBreak($heightNeeded) {
        if($this->GetY() + $heightNeeded > $this->GetPageBreakTrigger()) {
            $this->AddPage('P');
            return true;
        }
        return false;
    }

    // Obtiene el espacio disponible en la página actual hasta el footer
    function GetAvailableHeight() {
        $pageHeight = $this->h; // altura de la página
        $marginBottom = $this->bMargin; // margen inferior
        $footerSpace = 20; // espacio reservado para footer (aproximado)
        return $pageHeight - $this->GetY() - $marginBottom - $footerSpace;
    }

    // Divide el contenido de las claves en múltiples páginas si es necesario
    function RenderBilletesConPaginacion($billetesTexto, $x, $y, $ancho, $alto, $lineHeight) {
        $lineas = explode("\n", $billetesTexto);
        $lineasActuales = [];
        $paginasContenido = [];
        
        foreach ($lineas as $linea) {
            $lineasActuales[] = $linea;
            $alturaRequerida = count($lineasActuales) * $lineHeight;
            
            if ($alturaRequerida > ($this->GetPageBreakTrigger() - $this->GetY() - 20)) {
                // Guardar contenido de la página actual
                $paginasContenido[] = [
                    'lineas' => array_slice($lineasActuales, 0, -1),
                    'esUltima' => false
                ];
                $lineasActuales = [$linea];
                $this->AddPage('P');
            }
        }
        
        // Guardar última página
        if (!empty($lineasActuales)) {
            $paginasContenido[] = [
                'lineas' => $lineasActuales,
                'esUltima' => true
            ];
        }
        
        return $paginasContenido;
    }

}

$pdf = new PDF();
$pdf->AddPage('P'); // orientación horizontal
$pdf->AliasNbPages(); // muestra la página actual y el total de páginas

function mm_a_pulgadas($mm) {
    return round($mm / 25.4, 4);
}

// Obtener los datos de la cotización
if (isset($_GET['id_requisicion'])) {
    $id_requisicion = $_GET['id_requisicion'];

    $sqlRequisicion = "SELECT * FROM requisiciones WHERE id_requisicion = :id_requisicion";
    $stmtRequisicion = $conn->prepare($sqlRequisicion);
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();
    $arregloRequisicion = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);
    
    if (!$arregloRequisicion) {
        echo 'No se encontro la requisicion.';
        exit;
    }

    $rutaFirmaGerente = $arregloRequisicion['ruta_firma'] ?? '';
    $rutaFirmaDireccion = $arregloRequisicion['ruta_firma_admin'] ?? '';
    $rutaFirmaCnc = $arregloRequisicion['ruta_firma_cnc'] ?? '';

    $fechasMaquinado = "";
    $widthMaquinado = 80;
    if(empty($arregloRequisicion['inicio_maquinado']) && empty($arregloRequisicion['fin_maquinado'])){
        $fechasMaquinado = "";
    }
    if(!empty($arregloRequisicion['inicio_maquinado']) && empty($arregloRequisicion['fin_maquinado'])){
        $fechasMaquinado = $arregloRequisicion['inicio_maquinado'];
        $widthMaquinado = 80;
    }
    if(!empty($arregloRequisicion['inicio_maquinado']) && !empty($arregloRequisicion['fin_maquinado'])){
        $fechasMaquinado = $arregloRequisicion['inicio_maquinado']." - ".$arregloRequisicion['fin_maquinado'];
        $widthMaquinado = 120;
    }
    // tabla informacion del sello CLIENTE
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(220, 220, 220); // gris claro
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 6, 'SUCURSAL:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(162, 6, utf8_decode($arregloRequisicion['sucursal']), 1, 1, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 6, 'VENDEDOR:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(80, 6, utf8_decode($arregloRequisicion['nombre_vendedor']), 1, 0, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 6, 'FOLIO:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(52, 6, utf8_decode($arregloRequisicion['folio']), 1, 1, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 6, 'CLIENTE:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(80, 6, utf8_decode($arregloRequisicion['cliente']), 1, 0, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 6, 'NUM. PEDIDO:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(52, 6, utf8_decode($arregloRequisicion['num_pedido']), 1, 1, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 6, 'PAQUETERIA:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(80, 6, utf8_decode($arregloRequisicion['paqueteria']), 1, 0, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 6, 'FECHA Y HORA:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(52, 6, utf8_decode($arregloRequisicion['fecha_insercion']), 1, 1, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 6, 'FACTURA/REMISION/NOTA:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(140, 6, utf8_decode($arregloRequisicion['factura']), 1, 1, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 6, 'COMENTARIO:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(140, 6, utf8_decode($arregloRequisicion['comentario']), 1, 1, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 6, 'OPERADOR CNC:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(140, 6, utf8_decode($arregloRequisicion['maquina']." ".$arregloRequisicion['operador_cnc']), 1, 1, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 6, 'INICIO MAQUINADO:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(45, 6, utf8_decode($arregloRequisicion['inicio_maquinado']), 1, 0, 'L', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 6, 'FIN MAQUINADO:', 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(45, 6, utf8_decode($arregloRequisicion['fin_maquinado']), 1, 1, 'L', 0);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(100, 8, utf8_decode("Perfiles a maquinar"), 0, 0, '', 0);
    $pdf->Ln(7);


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
    $pdf->Cell(43, 6, 'Barras', 1, 1, 'C', true);

    foreach ($cotizacion_ids as $id_cotizacion) {
        $stmt->bindValue(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
        $stmt->execute();
        $cotizacionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cotizacionData)) continue;
        
        $cotGeneral = $cotizacionData[0];
        
        // Query para información del perfil
        $sqlPerfil = "SELECT * FROM perfiles2 WHERE nombre = :perfil";
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
        if($cotGeneral['perfil_sello']=="A07-A"){
            $alturasAdicionales = [
                'altura_caja' => 'Caja:',
                'altura_escalon' => 'Caja + Ángulo:', 
                'altura_h2' => 'H2:',
                'altura_h3' => 'H3:'
            ];
        }else{
            $alturasAdicionales = [
                'altura_caja' => 'Caja:',
                'altura_escalon' => 'Caja + Escalón:', 
                'altura_h2' => 'H2:',
                'altura_h3' => 'H3:'
            ];
        }
        
        
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
        
        // === CALCULAR ALTURAS BASE ===
        $lineHeight = 4;
        $numLineasAltura = count($alturas);
        $numLineasDI = count($arrayDI);
        $numLineasDE = count($arrayDE);
        $maxLineasMedidas = max($numLineasAltura, $numLineasDI, $numLineasDE);
        $rowHeightMedidas = $maxLineasMedidas * $lineHeight;

        // =============================================
        // ESTRATEGIA 1: MÚLTIPLES REGISTROS (> 1)
        // =============================================
        if (count($cotizacionData) > 1) {
            
            // Verificar espacio para la fila general
            if ($pdf->GetY() + $rowHeightMedidas + 5 > $pdf->GetPageBreakTrigger()) {
                $pdf->AddPage('P');
                // Redibuja los encabezados de la tabla
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(10, 6, 'Cant.', 1, 0, 'C', true);
                $pdf->Cell(15, 6, 'Perfil', 1, 0, 'C', true);
                $pdf->Cell(23, 6, 'Material', 1, 0, 'C', true);
                $pdf->Cell(33, 6, 'D. Interior', 1, 0, 'C', true);
                $pdf->Cell(33, 6, 'D. Exterior', 1, 0, 'C', true);
                $pdf->Cell(33, 6, 'Altura(s)', 1, 0, 'C', true);
                $pdf->Cell(43, 6, 'Barras', 1, 1, 'C', true);
            }
            
            // === RENGLÓN GENERAL (PRIMER RENGLÓN) ===
            // REGLA: Altura determinada por altura de alturas (no lleva claves)
            $xStartGeneral = $pdf->GetX();
            $yStartGeneral = $pdf->GetY();
            
            // Celda 1: Cantidad (guion)
            $pdf->Cell(10, $rowHeightMedidas, utf8_decode("-"), 1, 0, 'C');
            
            // Celda 2: Perfil (dato real)
            $pdf->Cell(15, $rowHeightMedidas, utf8_decode($cotGeneral['perfil_sello']), 1, 0, 'C');
            
            // Celda 3: Material (guion)
            $pdf->Cell(23, $rowHeightMedidas, utf8_decode("-"), 1, 0, 'C');
            
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
            $pdf->Cell(43, $rowHeightMedidas, utf8_decode("-"), 1, 1, 'C');
            
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
                
                // REGLA: Para renglones individuales, altura determinada por altura de claves
                $rowHeightIndividual = $rowHeightClaves;
                
                // Verificar espacio disponible y agregar página si es necesario
                $espacioDisponible = $pdf->GetPageBreakTrigger() - $pdf->GetY() - 20;
                
                if ($rowHeightIndividual > $espacioDisponible) {
                    // El contenido es demasiado grande, necesita múltiples páginas
                    // Dividir el contenido en bloques que quepan en una página
                    
                    $bloquesActuales = [];
                    $heightAcumulada = 0;
                    $primerBloqueEnPagina = true;
                    
                    foreach ($bloques as $bloqueIndex => $bloque) {
                        $bloquesActuales[] = $bloque;
                        $alturaBloque = (substr_count($bloque, "\n") + 1) * $lineHeight;
                        $heightAcumulada += $alturaBloque;
                        
                        // Agregar altura de separador si no es el último bloque
                        if ($bloqueIndex < count($bloques) - 1) {
                            $heightAcumulada += 4; // altura del separador
                        }
                        
                        // Verificar si el siguiente bloque causaría un desbordamiento
                        $proximoIndex = $bloqueIndex + 1;
                        $alturaProxima = ($proximoIndex < count($bloques)) ? 
                            (substr_count($bloques[$proximoIndex], "\n") + 1) * $lineHeight + 4 : 0;
                        
                        if ($pdf->GetY() + $heightAcumulada + $alturaProxima > $pdf->GetPageBreakTrigger() - 20 || 
                            $bloqueIndex == count($bloques) - 1) {
                            
                            // Render fila actual
                            $xStart = $pdf->GetX();
                            $yStart = $pdf->GetY();
                            
                            if ($primerBloqueEnPagina) {
                                // Primera fila en la página: mostrar cantidad, perfil, material
                                $pdf->Cell(10, $heightAcumulada, utf8_decode($cot['cantidad']." pz"), 1, 0, 'C');
                                $pdf->Cell(15, $heightAcumulada, utf8_decode($cot['perfil_sello'] . "-" . $cot['cantidad_material']), 1, 0, 'C');
                                $pdf->Cell(23, $heightAcumulada, utf8_decode($cot['material']), 1, 0, 'C');
                                // Diámetro Interior (vacío)
                                $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                                // Diámetro Exterior (vacío)
                                $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                                // Alturas (vacío)
                                $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                                $primerBloqueEnPagina = false;
                            } else {
                                // Continuación: mostrar celdas vacías para mantener alineación
                                $pdf->Cell(10, $heightAcumulada, "", 1, 0, 'C');
                                $pdf->Cell(15, $heightAcumulada, "", 1, 0, 'C');
                                $pdf->Cell(23, $heightAcumulada, "", 1, 0, 'C');
                                $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                                $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                                $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                            }
                            
                            // Claves (MultiCell)
                            $x = $pdf->GetX();
                            $y = $pdf->GetY();
                            $textoBloque = utf8_decode(implode("\n_________________________\n", $bloquesActuales));
                            $pdf->MultiCell(43, $lineHeight, $textoBloque, 1, 'L');
                            
                            // Asegurar posición correcta
                            $pdf->SetXY($xStart, $yStart + $heightAcumulada);
                            
                            // Reiniciar para siguiente bloque
                            $bloquesActuales = [];
                            $heightAcumulada = 0;
                            
                            // Agregar nueva página si hay más bloques
                            if ($bloqueIndex < count($bloques) - 1) {
                                $pdf->AddPage('P');
                                // Reposicionar Y para evitar solapamiento con header
                                $pdf->SetY(43);
                            }
                        }
                    }
                } else {
                    // El contenido cabe en una sola fila
                    // === DIBUJAR RENGLÓN INDIVIDUAL ===
                    $xStart = $pdf->GetX();
                    $yStart = $pdf->GetY();
                    
                    // Celda 1: Cantidad (dato real)
                    $pdf->Cell(10, $rowHeightIndividual, utf8_decode($cot['cantidad']." pz"), 1, 0, 'C');
                    
                    // Celda 2: Perfil (dato real)
                    $pdf->Cell(15, $rowHeightIndividual, utf8_decode($cot['perfil_sello'] . "-" . $cot['cantidad_material']), 1, 0, 'C');
                    
                    // Celda 3: Material (dato real)
                    $pdf->Cell(23, $rowHeightIndividual, utf8_decode($cot['material']), 1, 0, 'C');
                    
                    // Celda 4: Diámetro Interior (vacío)
                    $pdf->Cell(33, $rowHeightIndividual, "", 1, 0, 'C');
                    
                    // Celda 5: Diámetro Exterior (vacío)
                    $pdf->Cell(33, $rowHeightIndividual, "", 1, 0, 'C');
                    
                    // Celda 6: Alturas (vacío)
                    $pdf->Cell(33, $rowHeightIndividual, "", 1, 0, 'C');
                    
                    // Celda 7: Claves (MultiCell - datos reales)
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                    $pdf->MultiCell(43, $lineHeight, $textoFinal, 1, 'L');
                    
                    // Asegurar que el cursor quede en la posición correcta para el siguiente renglón
                    $currentY = $pdf->GetY();
                    $expectedY = $yStart + $rowHeightIndividual;
                    
                    if ($currentY != $expectedY) {
                        $pdf->SetXY($xStart, $expectedY);
                    }
                }
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
            
            // REGLA: Para único registro, altura determinada por el MÁXIMO entre alturas y claves
            $finalRowHeight = max($rowHeightMedidas, $rowHeightClaves);
            
            // Verificar espacio disponible
            $espacioDisponible = $pdf->GetPageBreakTrigger() - $pdf->GetY() - 20;
            
            if ($finalRowHeight > $espacioDisponible) {
                // El contenido es demasiado grande para una sola fila
                // Dividir los billets en múltiples filas/páginas
                
                $bloquesActuales = [];
                $heightAcumulada = 0;
                $primerBloque = true;
                
                foreach ($bloques as $bloqueIndex => $bloque) {
                    $bloquesActuales[] = $bloque;
                    $alturaBloque = (substr_count($bloque, "\n") + 1) * $lineHeight;
                    $heightAcumulada += $alturaBloque;
                    
                    // Agregar altura de separador si no es el último bloque
                    if ($bloqueIndex < count($bloques) - 1) {
                        $heightAcumulada += 4; // altura del separador
                    }
                    
                    // Verificar si el siguiente bloque causaría un desbordamiento
                    $proximoIndex = $bloqueIndex + 1;
                    $alturaProxima = ($proximoIndex < count($bloques)) ? 
                        (substr_count($bloques[$proximoIndex], "\n") + 1) * $lineHeight + 4 : 0;
                    
                    if ($pdf->GetY() + $heightAcumulada + $alturaProxima > $pdf->GetPageBreakTrigger() - 20 || 
                        $bloqueIndex == count($bloques) - 1) {
                        
                        // Render la fila con los bloques acumulados
                        $xStart = $pdf->GetX();
                        $yStart = $pdf->GetY();
                        
                        // Celda 1: Cantidad (solo en el primer bloque)
                        if ($primerBloque) {
                            $pdf->Cell(10, $heightAcumulada, utf8_decode($cot['cantidad']." pz"), 1, 0, 'C');
                        } else {
                            // En continuaciones, celda vacía
                            $pdf->Cell(10, $heightAcumulada, "", 1, 0, 'C');
                        }
                        
                        // Celda 2: Perfil (solo en el primer bloque)
                        if ($primerBloque) {
                            $pdf->Cell(15, $heightAcumulada, utf8_decode($cot['perfil_sello']), 1, 0, 'C');
                        } else {
                            $pdf->Cell(15, $heightAcumulada, "", 1, 0, 'C');
                        }
                        
                        // Celda 3: Material (solo en el primer bloque)
                        if ($primerBloque) {
                            $pdf->Cell(23, $heightAcumulada, utf8_decode($cot['material']), 1, 0, 'C');
                        } else {
                            $pdf->Cell(23, $heightAcumulada, "", 1, 0, 'C');
                        }
                        
                        // Celdas de dimensiones (solo en el primer bloque)
                        if ($primerBloque) {
                            // Diámetro Interior
                            $x = $pdf->GetX();
                            $y = $pdf->GetY();
                            $pdf->Rect($x, $y, 33, $heightAcumulada);
                            $textHeightDI = count($arrayDI) * $lineHeight;
                            $startYDI = $y + (($heightAcumulada - $textHeightDI) / 2);
                            $pdf->SetXY($x, $startYDI);
                            foreach ($arrayDI as $line) {
                                $pdf->Cell(33, $lineHeight, utf8_decode($line), 0, 0, 'C');
                                $pdf->SetXY($x, $pdf->GetY() + $lineHeight);
                            }
                            $pdf->SetXY($x + 33, $y);
                            
                            // Diámetro Exterior
                            $x = $pdf->GetX();
                            $y = $pdf->GetY();
                            $pdf->Rect($x, $y, 33, $heightAcumulada);
                            $textHeightDE = count($arrayDE) * $lineHeight;
                            $startYDE = $y + (($heightAcumulada - $textHeightDE) / 2);
                            $pdf->SetXY($x, $startYDE);
                            foreach ($arrayDE as $line) {
                                $pdf->Cell(33, $lineHeight, utf8_decode($line), 0, 0, 'C');
                                $pdf->SetXY($x, $pdf->GetY() + $lineHeight);
                            }
                            $pdf->SetXY($x + 33, $y);
                            
                            // Alturas
                            $x = $pdf->GetX();
                            $y = $pdf->GetY();
                            $pdf->Rect($x, $y, 33, $heightAcumulada);
                            $textHeightAlturas = count($alturas) * $lineHeight;
                            $startYAlturas = $y + (($heightAcumulada - $textHeightAlturas) / 2);
                            $pdf->SetXY($x, $startYAlturas);
                            foreach ($alturas as $line) {
                                $pdf->Cell(33, $lineHeight, utf8_decode($line), 0, 0, 'L');
                                $pdf->SetXY($x, $pdf->GetY() + $lineHeight);
                            }
                            $pdf->SetXY($x + 33, $y);
                        } else {
                            // Continuación: celdas vacías de dimensiones
                            $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                            $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                            $pdf->Cell(33, $heightAcumulada, "", 1, 0, 'C');
                        }
                        
                        // Claves (MultiCell)
                        $x = $pdf->GetX();
                        $y = $pdf->GetY();
                        $pdf->Rect($x, $y, 43, $heightAcumulada);

                        $textoBloques = utf8_decode(implode("\n_________________________\n", $bloquesActuales));

                        // IMPORTANTE: Para continuaciones ($primerBloque == false), ALINEAR AL TOP
                        // Solo centrar verticalmente en el PRIMER bloque
                        if ($primerBloque) {
                            // Calcular altura del texto
                            $textHeightClaves = 0;
                            foreach ($bloquesActuales as $bloque) {
                                $textHeightClaves += (substr_count($bloque, "\n") + 1) * $lineHeight;
                            }
                            // Agregar altura de separadores
                            $textHeightClaves += (count($bloquesActuales) - 1) * $lineHeight;
                            
                            // Centrar solo en el primer bloque
                            if ($textHeightClaves < $heightAcumulada) {
                                $startYClaves = $y + (($heightAcumulada - $textHeightClaves) / 2);
                            } else {
                                $startYClaves = $y;
                            }
                        } else {
                            // Para continuaciones: ALINEAR AL TOP siempre
                            $startYClaves = $y;
                        }

                        $pdf->SetXY($x, $startYClaves);
                        $pdf->MultiCell(43, $lineHeight, $textoBloques, 0, 'L');
                        
                        // Mover a la siguiente línea
                        $pdf->SetXY($xStart, $yStart + $heightAcumulada);
                        
                        // Preparar para siguiente bloque
                        $bloquesActuales = [];
                        $heightAcumulada = 0;
                        $primerBloque = false;
                        
                        // Agregar nueva página si hay más bloques
                        if ($bloqueIndex < count($bloques) - 1) {
                            $pdf->AddPage('P');
                            // Reposicionar Y para evitar solapamiento con header
                            $pdf->SetY(43);
                        }
                    }
                }
            } else {
                // El contenido cabe en una sola fila (comportamiento original)
                // === DIBUJAR ÚNICO RENGLÓN COMPLETO ===
                $xStart = $pdf->GetX();
                $yStart = $pdf->GetY();
                
                // Celda 1: Cantidad (dato real)
                $pdf->Cell(10, $finalRowHeight, utf8_decode($cot['cantidad']." pz"), 1, 0, 'C');
                
                // Celda 2: Perfil (dato real)
                $pdf->Cell(15, $finalRowHeight, utf8_decode($cot['perfil_sello'] . "-" . $cot['cantidad_material']), 1, 0, 'C');
                
                // Celda 3: Material (dato real)
                $pdf->Cell(23, $finalRowHeight, utf8_decode($cot['material']), 1, 0, 'C');
                
                // === CELDAS MULTILÍNEA CON ALTURA FORZADA ===
                
                // Celda 4: Diámetro Interior (MultiCell - datos reales)
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                
                // Dibujar borde manualmente para controlar altura exacta
                $pdf->Rect($x, $y, 33, $finalRowHeight);
                
                // Calcular posición Y para centrar verticalmente el texto
                $textHeightDI = count($arrayDI) * $lineHeight;
                $startYDI = $y + (($finalRowHeight - $textHeightDI) / 2);
                $pdf->SetXY($x, $startYDI);
                
                foreach ($arrayDI as $line) {
                    $pdf->Cell(33, $lineHeight, utf8_decode($line), 0, 0, 'C');
                    $pdf->SetXY($x, $pdf->GetY() + $lineHeight);
                }
                $pdf->SetXY($x + 33, $y);
                
                // Celda 5: Diámetro Exterior (MultiCell - datos reales)
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                
                // Dibujar borde manualmente
                $pdf->Rect($x, $y, 33, $finalRowHeight);
                
                // Centrar verticalmente el texto
                $textHeightDE = count($arrayDE) * $lineHeight;
                $startYDE = $y + (($finalRowHeight - $textHeightDE) / 2);
                $pdf->SetXY($x, $startYDE);
                
                foreach ($arrayDE as $line) {
                    $pdf->Cell(33, $lineHeight, utf8_decode($line), 0, 0, 'C');
                    $pdf->SetXY($x, $pdf->GetY() + $lineHeight);
                }
                $pdf->SetXY($x + 33, $y);
                
                // Celda 6: Alturas (MultiCell - datos reales)
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                
                // Dibujar borde manualmente
                $pdf->Rect($x, $y, 33, $finalRowHeight);
                
                // Centrar verticalmente el texto
                $textHeightAlturas = count($alturas) * $lineHeight;
                $startYAlturas = $y + (($finalRowHeight - $textHeightAlturas) / 2);
                $pdf->SetXY($x, $startYAlturas);
                
                foreach ($alturas as $line) {
                    $pdf->Cell(33, $lineHeight, utf8_decode($line), 0, 0, 'L');
                    $pdf->SetXY($x, $pdf->GetY() + $lineHeight);
                }
                $pdf->SetXY($x + 33, $y);
                
                // Celda 7: Claves (MultiCell - datos reales)
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                
                // Dibujar borde manualmente
                $pdf->Rect($x, $y, 43, $finalRowHeight);
                
                // Centrar verticalmente el texto (o alinear al top si es muy grande)
                $textHeightClaves = $numLinesClaves * $lineHeight;
                $startYClaves = $y;
                if ($textHeightClaves < $finalRowHeight) {
                    $startYClaves = $y + (($finalRowHeight - $textHeightClaves) / 2);
                }
                $pdf->SetXY($x, $startYClaves);
                
                // Usar MultiCell normal para las claves (ya que puede ser multilínea)
                $pdf->MultiCell(43, $lineHeight, $textoFinal, 0, 'L');
                
                // Mover a la siguiente línea con la altura correcta
                $pdf->SetXY($xStart, $yStart + $finalRowHeight);
            }
        }
    
        // === COMENTARIOS Y NOTAS (COMÚN PARA AMBOS CASOS) ===
        $sqlComentarios = "SELECT * FROM comentarios_adjuntos WHERE id_cotizacion = :id_cotizacion";
        $stmtComentarios = $conn->prepare($sqlComentarios);
        $stmtComentarios->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
        $stmtComentarios->execute();
        $arrayComentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($arrayComentarios) > 0){
            foreach($arrayComentarios as $comentario){
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(25, 6, utf8_decode("Comentario:"), 1, 0, 'R', 1);
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(165, 6, utf8_decode($comentario["comentario"]), 1, 1, 'L', 0);
            }
        
        }
        
        if(!empty($cotGeneral['billets_manualmente'])){
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(190, 6,utf8_decode("*Esta cotización cuenta con barras seleccionadas manualmente y no fueron sugeridas por el sistema."), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 8);
        }
        
        // Separación entre cotizaciones
        $pdf->Ln(8); 
    }

    // VERIFICAR SI HAY ESPACIO SUFICIENTE PARA LAS FIRMAS (aprox. 50mm)
    $pdf->CheckPageBreak(50);
    // Espaciado antes de las firmas
    $pdf->Ln(17);

    // Verifica si las imágenes existen en disco antes de usarlas
    if (!empty($rutaFirmaGerente) && file_exists(ROOT_PATH . $rutaFirmaGerente)) {
        // X=30 Y=posición actual (antes del Cell) Ancho=50
        $pdf->Image(ROOT_PATH . $rutaFirmaGerente, 88, $pdf->GetY() - 16, 40); 
    }

    if (!empty($rutaFirmaDireccion) && file_exists(ROOT_PATH . $rutaFirmaDireccion)) {
        // X=130 Y=posición actual (antes del Cell) Ancho=50
        $pdf->Image(ROOT_PATH . $rutaFirmaDireccion, 88, $pdf->GetY() - 16, 40);
    }

    // if (!empty($rutaFirmaCnc) && file_exists(ROOT_PATH . $rutaFirmaCnc)) {
    //     // X=30 Y=posición actual (antes del Cell) Ancho=50
    //     $pdf->Image(ROOT_PATH . $rutaFirmaCnc, 30, $pdf->GetY() + 16, 40); 
    // }

    // Líneas de firma
    $pdf->SetFont('Arial', '', 8);

    
    //$pdf->Cell(20, 1, '', 0, 0); // Espacio entre celdas
    //$pdf->Cell(80, 8, '_____________________________________', 0, 1, 'C');

    $pdf->Cell(190, 8, '______________________________________________________________________________', 0, 1, 'C');
    // Descripciones debajo
    if(!empty($arregloRequisicion['fecha_autorizacion']) && !empty($arregloRequisicion['ruta_firma'])){
        $pdf->Cell(40, 1, '', 0, 0);
        $pdf->Cell(40, 2, utf8_decode('AUTORIZA GERENCIA'), 0, 0, 'L');
        $pdf->Cell(35, 2, utf8_decode(utf8_decode($arregloRequisicion['fecha_autorizacion'])), 0, 0, 'L');
        $pdf->Cell(0, 2, utf8_decode(utf8_decode($arregloRequisicion['autorizo'])), 0, 0, 'L');

    }elseif(!empty($arregloRequisicion['fecha_autorizacion']) && !empty($arregloRequisicion['ruta_firma_admin'])){
        $pdf->Cell(40, 1, '', 0, 0);
        $pdf->Cell(40, 2, utf8_decode('AUTORIZA DIRECCIÓN'), 0, 0, 'L');
        $pdf->Cell(35, 2, utf8_decode(utf8_decode($arregloRequisicion['fecha_autorizacion'])), 0, 0, 'L');
        $pdf->Cell(0, 2, utf8_decode(utf8_decode($arregloRequisicion['autorizo'])), 0, 0, 'L');
        
    }else{
        $pdf->Cell(190, 2, utf8_decode('AUTORIZACIÓN'), 0, 1, 'C');
    }
    
    //$pdf->Cell(180, 2, utf8_decode('AUTORIZA DIRECCIÓN'), 0, 1, 'C');

    // Espacio antes de la segunda fila
    // $pdf->Ln(22);

    // Segunda fila de firmas
    // $pdf->Cell(80, 8, '_____________________________________', 0, 0, 'C');
    // $pdf->SetFont('Arial', '', 9);
    // $pdf->Cell($widthMaquinado, 3, utf8_decode($fechasMaquinado), 0, 1, 'C');
    // $pdf->SetFont('Arial', '', 8);
    // // Descripciones debajo
    // $pdf->Cell(80, 10, 'OPERADOR CNC', 0, 0, 'C');
    // $pdf->Cell(20, 1, '', 0, 0);
    // $pdf->Cell(80, 2, '_____________________________________', 0, 1, 'C');
    // $pdf->Cell(280, 6, 'FECHA Y HORA DE MAQUINADO', 0, 1, 'C');
    // tabla de control de almacen
    $sql = "SELECT *
            FROM control_almacen
            WHERE id_requisicion = :id_requisicion AND es_merma = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();
    $datosControl = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf->AddPage('P'); // orientación horizontal
    $pdf->AliasNbPages(); // muestra la página actual y el total de páginas

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(190, 6,"CONTROL DE BARRAS", 0, 1, 'C');
    $pdf->Ln(4); 
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(190, 6,"Info: MM Ent. (MM Entrada), MM Us. (MM Usados), MM. Ret. (MM Retorno), L.T.S. (Longitud Total de Sellos), M.C. (Merma por Corte)", 0, 1, 'L');
    $pdf->Ln(2); 

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(190, 6, utf8_decode('BARRAS/BILLETS'), 1, 1, 'C', 0);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(18, 6, 'MATERIAL', 1, 0, 'C', true);
    $pdf->Cell(43, 6, 'CLAVE', 1, 0, 'C', true);
    $pdf->Cell(29, 6, 'LOTE', 1, 0, 'C', true);
    $pdf->Cell(12, 6, 'MEDIDA', 1, 0, 'C', true);
    $pdf->Cell(12, 6, 'MM ENT.', 1, 0, 'C', true);
    $pdf->Cell(12, 6, 'MM US.', 1, 0, 'C', true);
    $pdf->Cell(12, 6, 'MM RET.', 1, 0, 'C', true);
    $pdf->Cell(12, 6, 'L. T. S.', 1, 0, 'C', true);
    $pdf->Cell(12, 6, 'M.C.', 1, 0, 'C', true);
    $pdf->Cell(14, 6, 'SCRAP PZ', 1, 0, 'C', true);
    $pdf->Cell(14, 6, 'SCRAP MM', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 8);

    if (count($datosControl) > 0) {
        $barrasExtra = [];
        $barrasRemplazo = [];
        $barrasEliminacion = [];
        foreach ($datosControl as $fila) {
            $esExtra = "";
            $esRemplazo = "";
            $esEliminacion = "";
            if($fila['es_extra']){
                $barrasExtra[] = $fila['clave']." (".$fila['lote_pedimento'].")";
                $esExtra = "+";
            }
            $clave = "";
            $lote_pedimento = "";
            $medida = "";
            if($fila['es_remplazo'] == 1 && $fila['es_remplazo_auth'] == 1){
                $barrasRemplazo[] = $fila['clave']." (".$fila['lote_pedimento'].")";
                $clave = $fila['clave_remplazo'];
                $lote_pedimento = $fila['lp_remplazo'];
                $medida = $fila['medida_remplazo'];
                $esRemplazo = "* ";
            }else{
                $clave = $fila['clave'];
                $lote_pedimento = $fila['lote_pedimento'];
                $medida = $fila['medida'];
            }
            if($fila['es_eliminacion'] == 1 && $fila['es_eliminacion_auth'] == 1){
                $barrasEliminacion[] = $fila['clave']." (".$fila['lote_pedimento'].")";
                $esEliminacion = "! ";
            }else{
                $clave = $fila['clave'];
                $lote_pedimento = $fila['lote_pedimento'];
                $medida = $fila['medida'];
            }
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(18, 6, utf8_decode($fila['material']), 1, 0, 'C');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(43, 6, utf8_decode($esExtra.$esRemplazo.$esEliminacion.$clave), 1, 0, 'C');
            $pdf->Cell(29, 6, utf8_decode($lote_pedimento), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($medida), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['mm_entrega']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['mm_total_usados']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['mm_retorno']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['total_sellos']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['merma_corte']), 1, 0, 'C');
            $pdf->Cell(14, 6, utf8_decode($fila['scrap_pz']), 1, 0, 'C');
            $pdf->Cell(14, 6, utf8_decode($fila['scrap_mm']), 1, 1, 'C');
        }
    } else {
        // Si no hay registros, usar los renglones vacíos como en el código original
        for ($i = 1; $i <= $CONTEO_CLAVES; $i++) {
            $pdf->Cell(18, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(43, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(29, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(14, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(14, 6, utf8_decode(""), 1, 1, 'C');
        }
    }
    $pdf->Ln(2); 
    $pdf->SetFont('Arial', 'I', 8);
    if(!empty($barrasExtra)){
        //$pdf->Cell(190, 6,"*La o las barras ".utf8_decode(implode(", ",$barrasExtra)." fueron agregadas como barras extra."), 0, 1, 'L');
        $pdf->Cell(190, 6,utf8_decode('La o las barras marcadas con + fueron agregadas como barras extra por razones de desición de inventarios.'), 0, 1, 'L');
    }
    if(!empty($barrasRemplazo)){
        $pdf->Cell(190, 6,utf8_decode('La o las barras marcadas con * fueron reemplazadas por razones de desición de inventarios.'), 0, 1, 'L');
    }
    if(!empty($barrasEliminacion)){
        $pdf->Cell(190, 6,utf8_decode('La o las barras marcadas con ! fueron eliminadas por razones de desición de inventarios.'), 0, 1, 'L');
    }

    $pdf->Ln(4); 
    $sql = "SELECT *
            FROM control_almacen
            WHERE id_requisicion = :id_requisicion AND es_merma = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();
    $datosControlMerma = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($datosControlMerma) > 0) {
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(190, 6, utf8_decode('BARRAS/BILLETS EN MERMA'), 1, 1, 'C', 0);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(18, 6, 'MATERIAL', 1, 0, 'C', true);
        $pdf->Cell(43, 6, 'CLAVE', 1, 0, 'C', true);
        $pdf->Cell(29, 6, 'LOTE', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'MEDIDA', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'MM ENT.', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'MM US.', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'MM RET.', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'L. T. S.', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'M.C.', 1, 0, 'C', true);
        $pdf->Cell(14, 6, 'SCRAP PZ', 1, 0, 'C', true);
        $pdf->Cell(14, 6, 'SCRAP MM', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 8);

        
        foreach ($datosControlMerma as $fila) {
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(18, 6, utf8_decode($fila['material']), 1, 0, 'C');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(43, 6, utf8_decode($fila['clave'].$esExtra), 1, 0, 'C');
            $pdf->Cell(29, 6, utf8_decode($fila['lote_pedimento']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['medida']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['mm_entrega']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['mm_total_usados']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['mm_retorno']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['total_sellos']), 1, 0, 'C');
            $pdf->Cell(12, 6, utf8_decode($fila['merma_corte']), 1, 0, 'C');
            $pdf->Cell(14, 6, utf8_decode($fila['scrap_pz']), 1, 0, 'C');
            $pdf->Cell(14, 6, utf8_decode($fila['scrap_mm']), 1, 1, 'C');
        }
    } else {

    }

}else{
    header("Location: ../../modules/welcome.php");
    exit;
}
$id_requisicion2 = $_GET['id_requisicion'];

$pdf->Output('I', 'requisicion_' . $id_requisicion2 . '.pdf');
?>