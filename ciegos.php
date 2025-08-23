<?php
require('fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);

// Titulo
$pdf->Cell(0,10,utf8_decode('Puntos Ciegos de Validación CNC'),0,1,'C');
$pdf->Ln(5);

// Explicación general
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6,utf8_decode(
    "A continuación se documentan los puntos ciegos detectados en el bloque de validaciones principales de CNC, considerando medidas que actualmente no se validan o podrían generar problemas en el taller:\n\n".
    "- Altura máxima H no definida: cliente puede ingresar valores imposibles de mecanizar.\n".
    "- Altura mínima H no definida: piezas muy delgadas no se sujetan ni se tornean.\n".
    "- Sección radial mínima no definida: paredes extremadamente delgadas pasan la validación.\n".
    "- Proporciones H/DI críticas no evaluadas: piezas altas y de diámetro pequeño pueden vibrar o ser inviables.\n".
    "- Tolerancias de la máquina real no consideradas (capacidad del mandril, máxima altura de herramienta).\n".
    "- Reglas de H vs DI independientes pueden rechazar piezas maquinables (zonas grises).\n"
));
$pdf->Ln(5);

// Tabla simple con casos
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,8,utf8_decode('Caso'),1,0,'C');
$pdf->Cell(30,8,utf8_decode('DI (mm)'),1,0,'C');
$pdf->Cell(30,8,utf8_decode('DE (mm)'),1,0,'C');
$pdf->Cell(30,8,utf8_decode('Altura (mm)'),1,1,'C');

// Datos de ejemplo de puntos ciegos
$casos = [
    ['Caso 1', 10, 15, 60],
    ['Caso 2', 15, 20, 0.2],
    ['Caso 3', 99.5, 100, 10],
    ['Caso 4', 15, 25, 10],
    ['Caso 5', 25, 30, 200],
    ['Caso 6', 12, 14, 24],
    ['Caso 7', 8, 10, 4],
];

$pdf->SetFont('Arial','',10);
foreach($casos as $fila) {
    $pdf->Cell(30,8,utf8_decode($fila[0]),1,0,'C');
    $pdf->Cell(30,8,$fila[1],1,0,'C');
    $pdf->Cell(30,8,$fila[2],1,0,'C');
    $pdf->Cell(30,8,$fila[3],1,1,'C');
}

$pdf->Ln(5);

// Comentarios detallados por caso
$pdf->SetFont('Arial','',10);
$comentarios = [
    'Caso 1' => 'Altura H=60 con DI=10 mm: geometría alta y delgada, posible vibración o sujeción insuficiente.',
    'Caso 2' => 'Altura H=0.2 mm: pieza demasiado delgada para tornear, no se valida H mínima.',
    'Caso 3' => 'DI=99.5, DE=100 → sección radial 0.25 mm, demasiado delgada para mecanizar.',
    'Caso 4' => 'Zona gris H=10, DI=15: reglas H vs DI independientes no aplican claramente, pieza potencialmente válida.',
    'Caso 5' => 'Altura H=200 mm: H máxima no definida, puede exceder capacidad de portaherramientas.',
    'Caso 6' => 'Proporción H/DI crítica: H=24, DI=12 → H demasiado grande respecto a DI, riesgo de vibración.',
    'Caso 7' => 'Altura H=4 con DI=8: pieza muy delgada, riesgo mecánico no detectado.'
];

foreach($comentarios as $caso => $texto){
    $pdf->MultiCell(0,6,utf8_decode("Comentario $caso:\n$texto\n"));
    $pdf->Ln(2);
}

$pdf->Output('I','puntos_ciegos_cnc_utf8.pdf');
