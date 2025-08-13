<?php
require_once(__DIR__ . '/../../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->Image('../../assets/img/general/logo-copia.png', 250, 4, 30);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(45);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(190, 12, utf8_decode('Cotizaciones fusionadas'), 0, 1, 'C', 0);
        $this->Ln(3);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 15, "Sellos y Retenes de San Luis S.A. de C.V.", 0, 0, 'L');
        $this->SetX(-60);
        $this->Cell(0, 15, "www.sellosyretenes.com", 0, 0, 'R');
    }
}

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../auth/cerrar_sesion.php");
    exit;
}

$pdf = new PDF();
$pdf->AddPage('L');
$pdf->AliasNbPages();

if (isset($_GET['id_fusion'])) {
    $id_fusion = $_GET['id_fusion'];

    // Consultar todas las cotizaciones con este id_fusion ordenadas (puedes ajustar el ORDER BY si quieres)
    $sql = "SELECT * FROM cotizacion_materiales WHERE id_fusion = :id_fusion ORDER BY id_cotizacion ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_fusion', $id_fusion, PDO::PARAM_INT);
    $stmt->execute();

    $cotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($cotizaciones)) {
        // Datos generales: puedes tomar info del primer registro para mostrar cliente, vendedor, etc.
        $primeraCot = $cotizaciones[0];

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(100, 8, utf8_decode("ID Fusion: " . $id_fusion), 0, 0, '', 0);
        $pdf->Ln(6);

        $pdf->SetFont('Arial', '', 13);
        $pdf->Cell(100, 8, utf8_decode("Cotizado por: " . $primeraCot['vendedor']), 0, 0, '', 0);
        $pdf->Ln(6);

        $pdf->Cell(100, 8, utf8_decode("Cliente: " . $primeraCot['cliente']), 0, 0, '', 0);
        $pdf->Ln(6);

        $pdf->Cell(100, 8, utf8_decode("Tipo de cliente: " . $primeraCot['tipo_cliente']), 0, 0, '', 0);
        $pdf->Ln(6);

        $pdf->Cell(38, 8, utf8_decode("Fecha: " . $primeraCot['fecha']), 0, 0, '', 0);
        $pdf->Cell(100, 8, utf8_decode("Hora: " . $primeraCot['hora']), 0, 0, '', 0);
        $pdf->Ln(10);

        // Aquí puedes poner una tabla con los id_cotizacion relacionados para referencia rápida
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, "Cotizaciones relacionadas:", 0, 1);

        $pdf->SetFont('Arial', '', 11);
        foreach ($cotizaciones as $cot) {
            $pdf->Cell(0, 6, "ID Cotizacion: " . $cot['id_cotizacion'], 0, 1);
        }
        $pdf->Ln(10);

        // A partir de aquí puedes generar una tabla detallada con materiales, alturas, perfiles, etc.
        // Ejemplo simplificado:

        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 6, '# Material', 1, 0, 'C', true);
        $pdf->Cell(20, 6, 'Cantidad', 1, 0, 'C', true);
        $pdf->Cell(42, 6, 'Material', 1, 0, 'C', true);
        $pdf->Cell(93, 6, 'Billets(s)', 1, 0, 'C', true);
        $pdf->Cell(34, 6, 'Total unitarios', 1, 0, 'C', true);
        $pdf->Cell(29, 6, 'Descuentos', 1, 0, 'C', true);
        $pdf->Cell(39, 6, 'Total', 1, 1, 'C', true);

        $totalGeneral = 0;
        foreach ($cotizaciones as $cot) {
            $clavesFormateadas = array_map('trim', explode(',', $cot['billets_string2']));
            $clavesVertical = utf8_decode(implode("\n", $clavesFormateadas));
            $lineHeight = 6;
            $numLinesClaves = count($clavesFormateadas);
            $rowHeightClaves = $numLinesClaves * $lineHeight;

            $pdf->Cell(20, $rowHeightClaves, utf8_decode($cot['cantidad_material']), 1, 0, 'C');
            $pdf->Cell(20, $rowHeightClaves, $cot['cantidad'] . " pz", 1, 0, 'C');
            $pdf->Cell(42, $rowHeightClaves, utf8_decode($cot['material']), 1, 0, 'C');

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell(93, $lineHeight, $clavesVertical, 1, 'C');
            $pdf->SetXY($x + 93, $y);

            $pdf->Cell(34, $rowHeightClaves, "$" . number_format($cot['total_unitarios'], 2), 1, 0, 'C');
            $pdf->Cell(29, $rowHeightClaves, "$" . number_format($cot['total_descuentos'], 2), 1, 0, 'C');
            $pdf->Cell(39, $rowHeightClaves, "$" . number_format($cot['total_material'], 2), 1, 1, 'C');

            $totalGeneral += $cot['total_material'];
        }

        $iva = $totalGeneral * 0.16;
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, "Subtotal = $" . number_format($totalGeneral, 2), 0, 1, 'R', true);

        $pdf->Cell(0, 8, "IVA 16% = $" . number_format($iva, 2), 0, 1, 'R', true);

        $totalGeneral += $iva;
        $pdf->Cell(0, 8, "Total final = $" . number_format($totalGeneral, 2), 0, 1, 'R', true);

        // Aquí puedes agregar las imágenes o cualquier otro detalle similar como en tu código original
        // ...
    } else {
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "No se encontraron cotizaciones para el id_fusion: " . $id_fusion, 0, 1, 'C');
    }

} else {
    header("Location: ../../modules/welcome.php");
    exit;
}

$pdf->Output('I', 'cotizaciones_fusionadas_' . $id_fusion . '.pdf');
