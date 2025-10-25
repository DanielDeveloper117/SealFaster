<?php
require_once(__DIR__ . '/../../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'fpdf/fpdf.php');

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

    // Función para verificar si hay espacio suficiente para las firmas
    function CheckPageBreak($heightNeeded) {
        if($this->GetY() + $heightNeeded > $this->PageBreakTrigger) {
            $this->AddPage('P');
            return true;
        }
        return false;
    }

}

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../auth/cerrar_sesion.php");
    exit;
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
    $pdf->Cell(52, 6, utf8_decode($arregloRequisicion['fechahora']), 1, 1, 'L', 0);
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
    $pdf->Cell(10, 6, 'Cant.', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'Perfil', 1, 0, 'C', true);
    $pdf->Cell(23, 6, 'Material', 1, 0, 'C', true);
    //$pdf->Cell(12, 6, 'Medida', 1, 0, 'C', true);
    $pdf->Cell(33, 6, 'D. Interior', 1, 0, 'C', true);
    $pdf->Cell(33, 6, 'D. Exterior', 1, 0, 'C', true);
    $pdf->Cell(33, 6, 'Altura(s)', 1, 0, 'C', true);
    $pdf->Cell(43, 6, 'Lote Pedimento/Clave', 1, 1, 'C', true);
    foreach ($cotizacion_ids as $id_cotizacion) {
        $stmt->bindValue(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
        $stmt->execute();
        $cotizacionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cotizacionData)) continue;
        
        // === Tabla de información general del sello ===
        $cotGeneral = $cotizacionData[0]; // Solo una fila para esta cabecera

        // query para informacion del perfil
        $sqlPerfil = "SELECT * FROM perfiles WHERE perfil = :perfil";
        $stmtPerfil = $conn->prepare($sqlPerfil);
        $stmtPerfil->bindParam(':perfil', $cotGeneral['perfil_sello']);
        $stmtPerfil->execute();
        $arregoPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
        // DEFINIR VARIABLES DEL SELLO RESULTANTE
        $familiaPerfil = $arregoPerfil["tipo"];

        
        $pdf->SetFont('Arial', '', 8);

        $arrayDI = [];
        $arrayDE = [];
        //*************************ALTURAS******************************** */
        // === Calcular contenido de altura ===
        $lineHeight = 4;
        $alturaTexto = '';
        $numLineasAltura = 1;

        $alturas = [];
        $alturas[] = "Total:";
        $arrayDI[] = "";
        $arrayDE[] = "";
        $alturas[] = $cotGeneral['a_sello']."mm/".mm_a_pulgadas($cotGeneral['a_sello']).'"';


        $arrayDI[] = $cotGeneral['di_sello']."mm/".mm_a_pulgadas($cotGeneral['di_sello']).'"';

        $arrayDE[] = $cotGeneral['de_sello']."mm/".mm_a_pulgadas($cotGeneral['de_sello']).'"';
        
        if ($cotGeneral['altura_caja'] !== "0.00") {
            $alturas[] = "Caja:";
            $alturas[] = $cotGeneral['altura_caja']."mm/".mm_a_pulgadas($cotGeneral['altura_caja']).'"';
            $arrayDI[] = "";
            $arrayDE[] = "";
            $arrayDI[] = "";
            $arrayDE[] = "";
        }
        if ($cotGeneral['altura_escalon'] !== "0.00") {
            $alturas[] = "Escalón:";
            $alturas[] = $cotGeneral['altura_escalon']."mm/".mm_a_pulgadas($cotGeneral['altura_escalon']).'"';
            $arrayDI[] = "";
            $arrayDE[] = "";
            $arrayDI[] = "";
            $arrayDE[] = "";
        }
        if ($cotGeneral['altura_h2'] !== "0.00") {
            $alturas[] = "H2:";
            $alturas[] = $cotGeneral['altura_h2']."mm/".mm_a_pulgadas($cotGeneral['altura_h2']).'"';
            $arrayDI[] = "";
            $arrayDE[] = "";
            $arrayDI[] = "";
            $arrayDE[] = "";
        }
        if ($cotGeneral['altura_h3'] !== "0.00") {
            $alturas[] = "H3:";
            $alturas[] = $cotGeneral['altura_h3']."mm/".mm_a_pulgadas($cotGeneral['altura_h3']).'"';
            $arrayDI[] = "";
            $arrayDE[] = "";
            $arrayDI[] = "";
            $arrayDE[] = "";
        }


        $alturas[] = '              '.$cotGeneral['tipo_medida_h'];
        $arrayDI[] = $cotGeneral['tipo_medida_di'];

        $arrayDE[] = $cotGeneral['tipo_medida_di'];

        $alturaTexto = utf8_decode(implode("\n", $alturas));
        $numLineasAltura = count($alturas);
        //if ($familiaPerfil == "wipers") {

            // if($cotGeneral['tipo_medida']=="Sello"){
            // } else {
            //     $alturas[] = "Total:";
            //     $alturas[] = $cotGeneral['a_sello2']."mm/".mm_a_pulgadas($cotGeneral['a_sello2']).'"';
            // }
        //} else {
            // if($cotGeneral['tipo_medida']=="Sello"){
                //$alturaTexto = $cotGeneral['a_sello']."mm/".mm_a_pulgadas($cotGeneral['a_sello']).'"';
            // } else {
            //     $alturaTexto = $cotGeneral['a_sello2']."mm/".mm_a_pulgadas($cotGeneral['a_sello2']).'"';
            // }
        //}

        // === Calcular altura total del renglon ===
        //$rowHeight = ($familiaPerfil == "wipers") ? ($numLineasAltura * $lineHeight) : 6;
        $rowHeight = ($numLineasAltura * $lineHeight) ?? 6;

        // === Guardar posicion inicial
        $xStart = $pdf->GetX();
        $yStart = $pdf->GetY();

        // === Imprimir todas las celdas del renglón una por una, mismo height ===
        $pdf->Cell(10, $rowHeight, utf8_decode("-"), 1, 0, 'C');
        $pdf->Cell(15, $rowHeight, utf8_decode($cotGeneral['perfil_sello']), 1, 0, 'C');
        $pdf->Cell(23, $rowHeight, utf8_decode("-"), 1, 0, 'C');
        // $pdf->Cell(12, $rowHeight, utf8_decode($cotGeneral['tipo_medida']), 1, 0, 'C');
        //$pdf->Cell(33, $rowHeight, utf8_decode($cotGeneral['di_sello'].'mm/'.$cotGeneral['di_sello_inch'].'"'), 1, 0, 'C');
        //$pdf->Cell(33, $rowHeight, utf8_decode($cotGeneral['de_sello'].'mm/'.$cotGeneral['de_sello_inch'].'"'), 1, 0, 'C');

        $textoDI = utf8_decode(implode("\n", $arrayDI));
        $textoDE = utf8_decode(implode("\n", $arrayDE));
        $alturaTexto = utf8_decode(implode("\n", $alturas));
        // if($cotGeneral['tipo_medida']=="Sello"){
        //     $pdf->Cell(29, $rowHeight, utf8_decode($cotGeneral['di_sello'].'mm/'.$cotGeneral['di_sello_inch'].'"'), 1, 0, 'C');
        //     $pdf->Cell(29, $rowHeight, utf8_decode($cotGeneral['de_sello'].'mm/'.$cotGeneral['de_sello_inch'].'"'), 1, 0, 'C');
        // } else {
        //     $pdf->Cell(29, $rowHeight, utf8_decode($cotGeneral['di_sello2'].'mm/'.$cotGeneral['di_sello_inch2'].'"'), 1, 0, 'C');
        //     $pdf->Cell(29, $rowHeight, utf8_decode($cotGeneral['de_sello2'].'mm/'.$cotGeneral['de_sello_inch2'].'"'), 1, 0, 'C');
        // }

        // === Celda de altura (usa MultiCell si es wipers)
        //if ($familiaPerfil == "wipers") {
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell(33, $lineHeight, $textoDI, 1, 'C');
        $pdf->SetXY($x + 33, $y);
        $pdf->MultiCell(33, $lineHeight, $textoDE, 1, 'C');
        $pdf->SetXY($x + 66, $y);
        $pdf->MultiCell(33, $lineHeight, $alturaTexto, 1, 'L');
        $pdf->SetXY($x + 99, $y);
        // } else {
        //     $pdf->Cell(33, $rowHeight, utf8_decode($alturaTexto), 1, 0, 'C');
        // }

        // === Celda de Claves vacia
        $pdf->Cell(43, $rowHeight, utf8_decode("-"), 1, 1, 'C');
        //**************************************************************** */



        // === Tabla de materiales ===
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 9);

        //$pdf->Cell(29, 6, 'Precio unitario', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 8);
        foreach ($cotizacionData as $cot) {
            // Separar por comas los registros combinados
            $billets = array_map('trim', explode(',', $cot['billets_claves_lotes']));
            $CONTEO_CLAVES += count($billets);

            $bloques = [];
            foreach ($billets as $item) {
                // Ejemplo: "M280350-4 TU.MID6G.14329 (280/350) 7 pz"
                // Extraemos las partes principales
                if (preg_match('/^([^\s]+)\s+([^\(]+)\s*(\([^)]+\)\s*\d+\s*pz)?$/i', $item, $m)) {
                    $clave = trim($m[1] ?? '');
                    $lote = trim($m[2] ?? '');
                    $resto = trim($m[3] ?? '');
                } else {
                    // Si no coincide el formato esperado, lo dejamos completo en una sola línea
                    $clave = $item;
                    $lote = '';
                    $resto = '';
                }

                // Formato solicitado:
                // lote
                // clave
                // (di/de) n pz
                $bloques[] = trim($lote . "\n" . $clave . ($resto ? "\n" . $resto : ''));
            }

            // Añadimos separador visual entre cada bloque
            $textoFinal = utf8_decode(implode("\n_________________________\n", $bloques));

            // Calculamos la altura total en base al número de líneas
            $lineHeight = 5;
            $numLines = 0;
            foreach ($bloques as $b) {
                $numLines += substr_count($b, "\n") + 1;
            }
            // + líneas de separación
            $numLines += count($bloques) - 1;
            $rowHeight = $numLines * $lineHeight;

            // Celdas previas
            $pdf->Cell(10, $rowHeight, utf8_decode($cot['cantidad']." pz"), 1, 0, 'C');
            $pdf->Cell(15, $rowHeight, utf8_decode($cot['perfil_sello']), 1, 0, 'C');
            $pdf->Cell(23, $rowHeight, utf8_decode($cot['material']), 1, 0, 'C');
            $pdf->Cell(33, $rowHeight, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(33, $rowHeight, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(33, $rowHeight, utf8_decode(""), 1, 0, 'C');

            // Guardar posición actual antes del MultiCell
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // Celda con los bloques formateados
            $pdf->MultiCell(43, $lineHeight, $textoFinal, 1, 'L');

            // Regresar a la posición para mantener alineación de la tabla
            $pdf->SetXY($x + 43, $y);
            $pdf->Cell(0, $rowHeight, "", 1, 1, 'C');
        }
        // // Separacion entre cotizaciones
        $pdf->Ln(5); 
        // $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
        // $pdf->Cell(15, 6, utf8_decode(""), 1, 0, 'C');
        // $pdf->Cell(24, 6, utf8_decode(""), 1, 0, 'C');
        // $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
        // $pdf->Cell(28, 6, utf8_decode(""), 1, 0, 'C');
        // $pdf->Cell(28, 6, utf8_decode(""), 1, 0, 'C');
        // $pdf->Cell(28, 6, utf8_decode(""), 1, 0, 'C');
        // $pdf->Cell(43, 6, utf8_decode(""), 1, 1, 'C');
    }
    // VERIFICAR SI HAY ESPACIO SUFICIENTE PARA LAS FIRMAS (aprox. 50mm)
    $pdf->CheckPageBreak(50);
    // Espaciado antes de las firmas
    $pdf->Ln(17);

    // Verifica si las imágenes existen en disco antes de usarlas
    if (!empty($rutaFirmaGerente) && file_exists(ROOT_PATH . $rutaFirmaGerente)) {
        // X=30 Y=posición actual (antes del Cell) Ancho=50
        $pdf->Image(ROOT_PATH . $rutaFirmaGerente, 30, $pdf->GetY() - 16, 40); 
    }

    if (!empty($rutaFirmaDireccion) && file_exists(ROOT_PATH . $rutaFirmaDireccion)) {
        // X=130 Y=posición actual (antes del Cell) Ancho=50
        $pdf->Image(ROOT_PATH . $rutaFirmaDireccion, 130, $pdf->GetY() - 16, 40);
    }

    // if (!empty($rutaFirmaCnc) && file_exists(ROOT_PATH . $rutaFirmaCnc)) {
    //     // X=30 Y=posición actual (antes del Cell) Ancho=50
    //     $pdf->Image(ROOT_PATH . $rutaFirmaCnc, 30, $pdf->GetY() + 16, 40); 
    // }

    // Líneas de firma
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(80, 8, '_____________________________________', 0, 0, 'C');
    $pdf->Cell(20, 1, '', 0, 0); // Espacio entre celdas
    $pdf->Cell(80, 8, '_____________________________________', 0, 1, 'C');

    // Descripciones debajo
    $pdf->Cell(80, 2, 'AUTORIZA GERENCIA', 0, 0, 'C');
    $pdf->Cell(20, 1, '', 0, 0);
    $pdf->Cell(80, 2, utf8_decode('AUTORIZA DIRECCIÓN'), 0, 1, 'C');

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
    $pdf->Cell(190, 6,"Info: MM Ent. (MM Entrada), MM Us. (MM Usados), MM. Ret. (MM Retorno), L.T.S. (Longitud Total de Sellos)", 0, 1, 'L');
    $pdf->Ln(2); 

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(190, 6, utf8_decode('BARRAS/BILLETS'), 1, 1, 'C', 0);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(12, 6, 'BARRAS', 1, 0, 'C', true);
    $pdf->Cell(43, 6, 'CLAVE', 1, 0, 'C', true);
    $pdf->Cell(30, 6, 'LOTE PEDIMENTO', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'MM ENT.', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'MM US.', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'MM RET.', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'L. T. S.', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'MERMA', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'SCRAP PZ', 1, 0, 'C', true);
    $pdf->Cell(15, 6, 'SCRAP MM', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 8);

    if (count($datosControl) > 0) {
        $barrasExtra = [];
        foreach ($datosControl as $fila) {
            $esExtra = "";
            if($fila['es_extra']){
                $barrasExtra[] = $fila['clave']." (".$fila['lote_pedimento'].")";
                $esExtra = "*";
            }
            $pdf->Cell(12, 6, utf8_decode($fila['cantidad_barras']), 1, 0, 'C');
            $pdf->Cell(43, 6, utf8_decode($fila['clave'].$esExtra), 1, 0, 'C');
            $pdf->Cell(30, 6, utf8_decode($fila['lote_pedimento']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['mm_entrega']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['mm_usados']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['mm_retorno']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['total_sellos']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['merma_corte']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['scrap_pz']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['scrap_mm']), 1, 1, 'C');
        }
    } else {
        // Si no hay registros, usar los renglones vacíos como en el código original
        for ($i = 1; $i <= $CONTEO_CLAVES; $i++) {
            $pdf->Cell(12, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(43, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(30, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode(""), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode(""), 1, 1, 'C');
        }
    }
    $pdf->Ln(2); 
    $pdf->SetFont('Arial', 'I', 8);
    if(!empty($barrasExtra)){
        $pdf->Cell(190, 6,"*La o las barras ".utf8_decode(implode(", ",$barrasExtra)." fueron agregadas como barras extra."), 0, 1, 'L');
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
        $pdf->Cell(12, 6, 'BARRAS', 1, 0, 'C', true);
        $pdf->Cell(43, 6, 'CLAVE', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'LOTE PEDIMENTO', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'MM ENT.', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'MM US.', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'MM RET.', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'L. T. S.', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'MERMA', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'SCRAP PZ', 1, 0, 'C', true);
        $pdf->Cell(15, 6, 'SCRAP MM', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 8);

        
        foreach ($datosControlMerma as $fila) {
            $pdf->Cell(12, 6, utf8_decode($fila['cantidad_barras']), 1, 0, 'C');
            $pdf->Cell(43, 6, utf8_decode($fila['clave']), 1, 0, 'C');
            $pdf->Cell(30, 6, utf8_decode($fila['lote_pedimento']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['mm_entrega']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['mm_usados']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['mm_retorno']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['total_sellos']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['merma_corte']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['scrap_pz']), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($fila['scrap_mm']), 1, 1, 'C');
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