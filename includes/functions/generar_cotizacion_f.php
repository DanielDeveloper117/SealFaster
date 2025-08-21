<?php
require_once(__DIR__ . '/../../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'fpdf/fpdf.php');
class PDF extends FPDF {
    function Header() {
        $this->Image('../../assets/img/general/logo-copia.png', 250, 4, 30); //logo de la empresa, moverDerecha, moverAbajo, tamañoIMG
        $this->SetFont('Arial', 'B', 16); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
        $this->Cell(45); // Movernos a la derecha
        $this->SetTextColor(0, 0, 0); //color
        $this->Cell(190, 12, utf8_decode('Cotización'), 0, 1, 'C', 0); // AnchoCelda, AltoCelda, titulo, borde(1-0), saltoLinea(1-0), posicion(L-C-R), ColorFondo(1-0)
        $this->Ln(3); // Salto de línea
    } 
    function Footer() {
        $this->SetY(-15); // Posición: a 1,5 cm del final
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 15, "Sellos y Retenes de San Luis S.A. de C.V.", 0, 0, 'L');
        $this->SetX(-60); // Ajusta según el ancho de la URL
        $this->Cell(0, 15, "www.sellosyretenes.com", 0, 0, 'R');
    }
}
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../auth/cerrar_sesion.php");
    exit;
}

$pdf = new PDF();
$pdf->AddPage('L'); // orientación horizontal
$pdf->AliasNbPages(); // muestra la página actual y el total de páginas

if (isset($_GET['id_fusion'])) {
    $id_fusion = $_GET['id_fusion'];
    $sql = "SELECT DISTINCT id_cotizacion FROM cotizacion_materiales WHERE id_fusion = :id_fusion";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_fusion', $id_fusion, PDO::PARAM_INT);
    $stmt->execute();
    $arregloCotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$arregloCotizaciones) {
        echo 'No se encontro la agrupación de cotizaciones.';
        exit;
    }
    $conteoCotizaciones = 1;

    foreach ($arregloCotizaciones as $cotizacion) {
        $sql = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_cotizacion', $cotizacion["id_cotizacion"], PDO::PARAM_INT);
        $stmt->execute();
        $arregloCotizacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($arregloCotizacion)) {
            $arregloSelectCotizacion = $arregloCotizacion[0];
            $id_cotizacion = $arregloSelectCotizacion['id_cotizacion'];
            $perfil_sello = $arregloSelectCotizacion['perfil_sello'];
            $cantidad_material = $arregloSelectCotizacion['cantidad_material'];
            $vendedor = $arregloSelectCotizacion['vendedor'];
            $cliente = $arregloSelectCotizacion['cliente'];
            $tipo_cliente = $arregloSelectCotizacion['tipo_cliente'];
            $fecha = $arregloSelectCotizacion['fecha'];
            $hora = $arregloSelectCotizacion['hora'];
            $estatus_completado = $arregloSelectCotizacion['estatus_completado'];

            // Información general de la cotización
            $pageWidth = $pdf->GetPageWidth();
            $cellWidth = $pageWidth - 20; // margenes de 10 unidades

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(100, 8, utf8_decode($conteoCotizaciones.".- Id cotización: ". $id_cotizacion), 0, 0, '', 0);
            $pdf->Ln(6);
            
            $pdf->SetTextColor(103); // color
            $pdf->SetFont('Arial', '', 13);
            $pdf->Cell(100, 8, utf8_decode("Cotizado por: ". $vendedor), 0, 0, '', 0);
            $pdf->Ln(6);

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(38, 8, utf8_decode("Fecha: ". $fecha), 0, 0, '', 0);
            $pdf->Cell(100, 8, utf8_decode("Hora: ". $hora), 0, 0, '', 0);
            $pdf->Ln(8);

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(100, 8, utf8_decode("Estatus: ". $estatus_completado), 0, 0, '', 0);
            $pdf->Ln(10);
            
            $pdf->SetDrawColor(0, 0, 0); // color negro
            $pdf->SetLineWidth(0.8);
            $pdf->Line(10, 53, $pageWidth - 10, 53); // linea horizontal
            $pdf->SetLineWidth(0.2);

            $sqlPerfil = "SELECT * FROM perfiles WHERE perfil = :perfil";
            $stmtPerfil = $conn->prepare($sqlPerfil);
            $stmtPerfil->bindParam(':perfil', $perfil_sello);
            $stmtPerfil->execute();
            $arregoPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);

            // tabla informacion del sello CLIENTE
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(220, 220, 220); // gris claro
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(40, 6, 'Familia', 1, 0, 'C', true);
            //$pdf->Cell(40, 6, 'Tipo de medida', 1, 0, 'C', true);
            $pdf->Cell(37, 6, 'D. Interior', 1, 0, 'C', true);
            $pdf->Cell(37, 6, 'D. Exterior', 1, 0, 'C', true);
            // altura normal total
            $pdf->Cell(37, 6, 'Altura total', 1, 1, 'C', true);

            $pdf->Cell(40, 6, utf8_decode($arregloCotizacion[0]["familia_perfil"]), 1, 0, 'C');
            //$pdf->Cell(40, 6, utf8_decode($arregloCotizacion[0]["tipo_medida"]), 1, 0, 'C');
            $di_sello = 0.00;
            $de_sello = 0.00;
            $a_sello = 0.00;
            $di_sello = $arregloCotizacion[0]["di_sello"];
            $de_sello = $arregloCotizacion[0]["de_sello"];
            $a_sello = $arregloCotizacion[0]["a_sello"];
            // if($arregloCotizacion[0]["tipo_medida"] == "Sello"){
            //     $di_sello = $arregloCotizacion[0]["di_sello"];
            //     $de_sello = $arregloCotizacion[0]["de_sello"];
            //     $a_sello = $arregloCotizacion[0]["a_sello"];
            // }else{
            //     $di_sello = $arregloCotizacion[0]["di_sello2"];
            //     $de_sello = $arregloCotizacion[0]["de_sello2"];
            //     $a_sello = $arregloCotizacion[0]["a_sello2"];                               
            // }
            $pdf->Cell(37, 6, utf8_decode($di_sello.' '.$arregloCotizacion[0]["tipo_medida_di"]), 1, 0, 'C');
            $pdf->Cell(37, 6, utf8_decode($de_sello.' '.$arregloCotizacion[0]["tipo_medida_de"]), 1, 0, 'C');
            // altura normal total
            $pdf->Cell(37, 6, utf8_decode($a_sello.' '.$arregloCotizacion[0]["tipo_medida_h"]), 1, 0, 'C');

            $pdf->Ln(8);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(100, 8, utf8_decode("Materiales del perfil"), 0, 0, '', 0);
            $pdf->Ln(8);
            // Cabecera de la tabla de materiales
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(220, 220, 220); // gris claro
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(43, 6, utf8_decode('Num. material'), 1, 0, 'C', true);
            $pdf->Cell(46, 6, 'Cantidad de piezas', 1, 0, 'C', true);
            $pdf->Cell(46, 6, 'Material', 1, 0, 'C', true);
            $pdf->Cell(46, 6, 'Total unitarios', 1, 0, 'C', true);
            $pdf->Cell(46, 6, 'Descuentos', 1, 0, 'C', true);
            $pdf->Cell(50, 6, 'Total', 1, 1, 'C', true);

            $totalCotizacion = 0;

            foreach ($arregloCotizacion as $cotizacion) {
                $pdf->Cell(43, 6, utf8_decode($cotizacion['cantidad_material']), 1, 0, 'C');
                $pdf->Cell(46, 6, $cotizacion['cantidad'] . " pz", 1, 0, 'C');            
                $pdf->Cell(46, 6, utf8_decode($cotizacion['material']), 1, 0, 'C');
                $pdf->Cell(46, 6, "$" . number_format($cotizacion['total_unitarios'], 2), 1, 0, 'C');
                $pdf->Cell(46, 6, "$" . number_format($cotizacion['total_descuentos'], 2), 1, 0, 'C');
                $pdf->Cell(50, 6, "$" . number_format($cotizacion['total_material'], 2), 1, 1, 'C');
                $totalCotizacion += $cotizacion['total_material'];
            }
            $iva = $totalCotizacion*0.16;
            $pdf->Ln(2);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell($cellWidth, 8, "Subtotal = $" . number_format($totalCotizacion, 2), 0, 0,'R',true);

            $pdf->Ln(6);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell($cellWidth, 8, "IVA 16% = $" . number_format($iva, 2), 0, 0,'R',true);

            $totalCotizacion += $iva;
            $pdf->Ln(6);
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->Cell($cellWidth, 8, utf8_decode("Total cotización = $" . number_format($totalCotizacion, 2)), 0, 0,'R',true);
            // Manejo de imágenes
            $pdf->Ln(10);
            $marginRight = 10;

            foreach ($arregloCotizacion as $cotizacion) {
                $yPosition = $pdf->GetY();
                $pageHeight = $pdf->GetPageHeight();
                $footerHeight = 20;
                $safeMargin = 5;
                $defaultHeight = 50;
                $reducedHeight = round($defaultHeight * 0.7); // 70% del tamaño

                $spaceAvailable = $pageHeight - $yPosition - $footerHeight - $safeMargin;

                // Decidir tamaño de imagen
                if ($spaceAvailable >= $defaultHeight) {
                    // Cabe normal
                    $imageHeight = $defaultHeight;
                } elseif ($spaceAvailable >= $reducedHeight) {
                    // Cabe reducida
                    $imageHeight = $reducedHeight;
                } else {
                    // No cabe ni reducida → nueva página
                    $pdf->AddPage('L');
                    $marginRight = 10;
                    $yPosition = 25;
                    $imageHeight = $defaultHeight;
                }

                // Insertar imagen con altura elegida
                $pdf->Image('../' . $cotizacion['img'], $marginRight, $yPosition, 0, $imageHeight);
                $marginRight += 60;

                // Si ya no cabe otra imagen horizontalmente, hacer salto vertical
                if ($marginRight + 50 > $pdf->GetPageWidth()) {
                    $marginRight = 10;
                    $pdf->SetY($yPosition + $imageHeight + 10);
                }
            }


        } else {
            echo 'No se encontro una de las cotizaciones.';
            exit;
        }
        $conteoCotizaciones ++;
        //if ($conteoCotizaciones < count($arregloCotizaciones)) {
            $pdf->AddPage('L');
        //}

    }
    // RESUMEN ULTIMA PAGINA
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(100, 8, utf8_decode("Resumen"), 0, 0, '', 0);
    $pdf->Ln(10);

    // cabecera resumen
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(36, 6, utf8_decode('Id cotización'), 1, 0, 'C', true);
    $pdf->Cell(36, 6, 'Familia', 1, 0, 'C', true);
    $pdf->Cell(34, 6, 'Perfil', 1, 0, 'C', true);
    //$pdf->Cell(34, 6, 'Tipo de medida', 1, 0, 'C', true);
    $pdf->Cell(42, 6, 'D. Interior', 1, 0, 'C', true);
    $pdf->Cell(42, 6, 'D. Exterior', 1, 0, 'C', true);
    $pdf->Cell(42, 6, 'Altura', 1, 0, 'C', true);
    $pdf->Cell(45, 6, utf8_decode('Total cotización'), 1, 1, 'C', true);

    $GRAN_TOTAL = 0.0;
    $procesados = []; // set de ids ya mostrados

    foreach ($arregloCotizaciones as $cotizacion) {
        $idCot = (int)$cotizacion['id_cotizacion'];

        // evitar filas duplicadas en el resumen
        if (isset($procesados[$idCot])) {
            continue;
        }
        $procesados[$idCot] = true;

        // obtener todas las filas de ese id_cotizacion
        $sql = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_cotizacion', $idCot, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            continue;
        }

        // tomar datos de cabecera desde la primera fila
        $r0 = $rows[0];

        $di = $r0['di_sello'];
        $de = $r0['de_sello'];
        $a  = $r0['a_sello'];
        // elegir medidas segun tipo_medida
        // if ($r0['tipo_medida'] === 'Sello') {
        //     $di = $r0['di_sello'];
        //     $de = $r0['de_sello'];
        //     $a  = $r0['a_sello'];
        // } else {
        //     $di = $r0['di_sello2'];
        //     $de = $r0['de_sello2'];
        //     $a  = $r0['a_sello2'];
        // }

        // calcular subtotal correcto: suma de total_material (sin IVA por material)
        $subtotal = 0.0;
        foreach ($rows as $r) {
            $subtotal += (float)$r['total_material'];
        }

        // aplicar IVA una sola vez al subtotal
        $iva   = round($subtotal * 0.16, 2);     // mismo criterio de redondeo que usas en otras secciones
        $total = round($subtotal + $iva, 2);

        // imprimir fila del resumen
        $pdf->Cell(36, 6, utf8_decode($r0['id_cotizacion']), 1, 0, 'C');
        $pdf->Cell(36, 6, utf8_decode($r0['familia_perfil']), 1, 0, 'C');
        $pdf->Cell(34, 6, utf8_decode($r0['perfil_sello']), 1, 0, 'C');
        //$pdf->Cell(34, 6, utf8_decode($r0['tipo_medida']), 1, 0, 'C');
        $pdf->Cell(42, 6, utf8_decode($di.' '.$r0["tipo_medida_di"]), 1, 0, 'C');
        $pdf->Cell(42, 6, utf8_decode($de.' '.$r0["tipo_medida_de"]), 1, 0, 'C');
        $pdf->Cell(42, 6, utf8_decode($a.' '.$r0["tipo_medida_h"]), 1, 0, 'C');
        $pdf->Cell(45, 6, "$" . number_format($total, 2), 1, 1, 'C');

        // acumular gran total (incluye IVA por cotizacion)
        $GRAN_TOTAL += $total;
    }

    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell($cellWidth, 10, utf8_decode("Total final = $" . number_format($GRAN_TOTAL, 2)), 0, 0, 'R', true);

}else{
    header("Location: ../../modules/welcome.php");
    exit;
}
$id_fusion2 = $_GET['id_fusion'];

$pdf->Output('I', 'agrupacion_cotizaciones_' . $id_fusion2 . '.pdf');
?>