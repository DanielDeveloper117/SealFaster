<?php
require('fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);

// Titulo
$pdf->Cell(0,10,'Validaciones CNC actuales',0,1,'C');
$pdf->Ln(5);

// Explicacion de reglas
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6,
    "Reglas de validacion actualmente implementadas (excluyendo puntos ciegos):\n".
    "1. DI (diametro interior) debe ser mayor a 5 mm.\n".
    "2. DE (diametro exterior) debe ser menor a 850 mm.\n".
    "3. La seccion radial (DE - DI) / 2 no puede ser mayor a 45 mm.\n".
    "4. Si Altura < 50.8 mm, entonces DI debe ser mayor a 20 mm.\n".
    "5. Si Altura < 9 mm, entonces DI debe ser mayor a 5 mm."
);
$pdf->Ln(10);

// Cabecera de tabla
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,8,'Caso',1,0,'C');
$pdf->Cell(30,8,'DI (mm)',1,0,'C');
$pdf->Cell(30,8,'DE (mm)',1,0,'C');
$pdf->Cell(30,8,'Altura (mm)',1,0,'C');
$pdf->Cell(50,8,'Resultado',1,1,'C');

// Datos de prueba balanceados
$casos = [
    // Aceptados
    ['Caso 1', 25, 100, 60, 'Aceptado'],
    ['Caso 2', 30, 200, 80, 'Aceptado'],
    ['Caso 3', 50, 300, 40, 'Aceptado'],
    ['Caso 4', 22, 150, 55, 'Aceptado'],
    ['Caso 5', 10, 50, 20, 'Aceptado'],
    ['Caso 6', 35, 120, 10, 'Aceptado'],
    ['Caso 7', 45, 500, 70, 'Aceptado'],

    // Rechazados
    ['Caso 8', 4, 100, 30, 'Rechazado (DI < 5)'],
    ['Caso 9', 30, 900, 60, 'Rechazado (DE > 850)'],
    ['Caso 10', 30, 130, 20, 'Rechazado (Seccion > 45)'],
    ['Caso 11', 15, 80, 40, 'Rechazado (H<50.8, DI<=20)'],
    ['Caso 12', 5, 60, 8, 'Rechazado (H<9, DI<=5)'],
    ['Caso 13', 20, 200, 5, 'Rechazado (H<9, DI<=5)'],
    ['Caso 14', 19, 60, 45, 'Rechazado (H<50.8, DI<=20)'],
];

// Imprimir tabla
$pdf->SetFont('Arial','',10);
foreach($casos as $fila) {
    $pdf->Cell(30,8,$fila[0],1,0,'C');
    $pdf->Cell(30,8,$fila[1],1,0,'C');
    $pdf->Cell(30,8,$fila[2],1,0,'C');
    $pdf->Cell(30,8,$fila[3],1,0,'C');
    $pdf->Cell(50,8,$fila[4],1,1,'C');
}

$pdf->Output('I','validaciones_cnc.pdf');
