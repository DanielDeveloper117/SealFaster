<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');
    // Verificar si los datos se han enviado por POST
    if (isset($_POST['id_cotizacion'], $_POST['id_usuario'], $_POST['familia_perfil'], $_POST['perfil_sello'], $_POST['cantidad_material'], $_POST['material'], 
              $_POST['claves'], $_POST['billets'], $_POST['billets_lotes'],
              $_POST['altura_mm'], $_POST['altura_caja_mm'], $_POST['altura_h2_mm'], $_POST['altura_h3_mm'], 
              $_POST['diametro_interior_mm'], $_POST['diametro_exterior_mm'],
              $_POST['tipo_medida_di'], $_POST['tipo_medida_de'], $_POST['tipo_medida_h'],
              $_POST['a_sello'], $_POST['di_sello'], $_POST['de_sello'], $_POST['cantidad'], 
              $_POST['total_unitarios'], $_POST['desc_cliente'], $_POST['desc_cantidad'], $_POST['desc_mayoreo'], $_POST['total_descuentos'], $_POST['total_material'], 
              $_POST['estatus_completado'], $_POST['vendedor'], $_POST['img'],
              $_POST['cliente'], $_POST['tipo_cliente'], $_POST['codigo_cliente'], $_POST['correo_cliente'])) {

        // Obtener los valores enviados por POST
        $id_cotizacion = $_POST['id_cotizacion'];
        $id_usuario = $_POST['id_usuario'];
        $familia_perfil = $_POST['familia_perfil'];
        $perfil_sello = $_POST['perfil_sello'];
        $cantidad_material = $_POST['cantidad_material'];
        $material = $_POST['material'];
        $proveedor = $_POST['proveedor'] ?? "Innecesario";
        $claves = $_POST['claves'];
        $billets = $_POST['billets'];
        $billets_lotes = $_POST['billets_lotes'];
        $billets_string = $_POST['billets_string'];
        $tipo_medida = $_POST['tipo_medida'];
        $altura = $_POST['altura_mm'];
        $altura_caja = $_POST['altura_caja_mm'];
        $altura_escalon = $_POST['altura_escalon_mm'];
        $altura_h2 = $_POST['altura_h2_mm'];
        $altura_h3 = $_POST['altura_h3_mm'];
        $diametro_int = $_POST['diametro_interior_mm'];
        $diametro_ext = $_POST['diametro_exterior_mm'];
        $tipo_medida_di = $_POST['tipo_medida_di'];
        $tipo_medida_de = $_POST['tipo_medida_de'];
        $tipo_medida_h = $_POST['tipo_medida_h'];
        $a_sello = $_POST['a_sello'];
        $di_sello = $_POST['di_sello'];
        $de_sello = $_POST['de_sello'];
        $cantidad = $_POST['cantidad'];
        $total_unitarios = $_POST['total_unitarios'];
        $desc_cliente = $_POST['desc_cliente'];
        $desc_cantidad = $_POST['desc_cantidad'];
        $desc_mayoreo = $_POST['desc_mayoreo'];
        $total_descuentos = $_POST['total_descuentos'];
        $total_material = $_POST['total_material'];
        $estatus_completado = $_POST['estatus_completado'];
        $vendedor = $_POST['vendedor'];
        $img = $_POST['img'];
        $cliente = $_POST['cliente'];
        $tipo_cliente = $_POST['tipo_cliente'];
        $codigo_cliente = $_POST['codigo_cliente'];
        $correo_cliente = $_POST['correo_cliente'];

        $a_sello_inch = $_POST['a_sello_inch'];
        $di_sello_inch = $_POST['di_sello_inch'];
        $de_sello_inch = $_POST['de_sello_inch'];

        $a_sello2 = $_POST['a_sello2'];
        $di_sello2 = $_POST['di_sello2'];
        $de_sello2 = $_POST['de_sello2'];

        $a_sello_inch2 = $_POST['a_sello_inch2'];
        $di_sello_inch2 = $_POST['di_sello_inch2'];
        $de_sello_inch2 = $_POST['de_sello_inch2'];
        
        // Convertimos las entradas completas en array, manteniendo el formato original
        $entries_array = array_map('trim', explode(',', $billets_string));

        // Preparamos la consulta PDO
        $stmt = $conn->prepare("SELECT proveedor FROM parametros WHERE clave = :clave");

        // Creamos un array para almacenar los resultados con proveedor
        $billets_array_with_proveedor = [];

        foreach ($entries_array as $entry) {
            // Extraemos la clave del entry (primer segmento antes del espacio)
            $parts = explode(' ', $entry, 2);
            $clave = $parts[0];
            
            // Buscamos el proveedor para esta clave específica
            $stmt->execute([':clave' => $clave]);
            $proveedor = $stmt->fetchColumn();
            
            // Si no se encuentra proveedor, asignamos uno por defecto
            if (!$proveedor) {
                $proveedor = "Desconocido";
            }
            
            // Agregamos el entry con su proveedor correspondiente
            $billets_array_with_proveedor[] = $proveedor . ' ' . $entry;
        }

        // Unimos el nuevo array en una sola cadena
        $billets_string2 = implode(',', $billets_array_with_proveedor);

        // Ahora $billets_string2 contiene algo como:
        // "SKF TU.F1.31860 (40/67) 2 pz,TRYGONAL TU.F1.31637 (0/95) 2 pz"

        // --------------------------
        // FUSIONAR billets + billets_string EN billets_claves_lotes
        // --------------------------
        $arrClaves = array_map('trim', explode(',', $billets));
        $arrStrings = array_map('trim', explode(',', $billets_string));

        $minLen = min(count($arrClaves), count($arrStrings));
        $billets_claves_lotes_arr = [];

        for ($i = 0; $i < $minLen; $i++) {
            $billets_claves_lotes_arr[] = $arrClaves[$i] . ' ' . $arrStrings[$i];
        }

        $billets_claves_lotes = implode(',', $billets_claves_lotes_arr);

        // Preparar la consulta SQL para insertar los datos
        $stmt = $conn->prepare("
            INSERT INTO cotizacion_materiales (
                id_cotizacion, id_usuario, familia_perfil, perfil_sello, cantidad_material, material, proveedor, 
                claves, billets, billets_lotes, billets_claves_lotes, billets_string, billets_string2, tipo_medida, 
                altura, altura_caja, altura_escalon, altura_h2, altura_h3, 
                diametro_int, diametro_ext, 
                a_sello, tipo_medida_h, di_sello, tipo_medida_di, de_sello, tipo_medida_de, cantidad, 
                total_unitarios, desc_cliente, desc_cantidad, desc_mayoreo, total_descuentos, total_material, 
                estatus_completado, vendedor, img, cliente, tipo_cliente, codigo_cliente, correo_cliente,
                a_sello_inch, di_sello_inch, de_sello_inch, a_sello2, di_sello2, de_sello2, a_sello_inch2, di_sello_inch2, de_sello_inch2
                ) 
            VALUES (
                :id_cotizacion, :id_usuario, :familia_perfil, :perfil_sello, :cantidad_material, :material, :proveedor, 
                :claves, :billets, :billets_lotes, :billets_claves_lotes, :billets_string, :billets_string2, :tipo_medida, 
                :altura, :altura_caja, :altura_escalon, :altura_h2, :altura_h3, 
                :diametro_int, :diametro_ext, 
                :a_sello, :tipo_medida_h, :di_sello, :tipo_medida_di, :de_sello, :tipo_medida_de, :cantidad, 
                :total_unitarios, :desc_cliente, :desc_cantidad, :desc_mayoreo, :total_descuentos, :total_material, 
                :estatus_completado, :vendedor, :img, :cliente, :tipo_cliente, :codigo_cliente, :correo_cliente, 
                :a_sello_inch, :di_sello_inch, :de_sello_inch, :a_sello2, :di_sello2, :de_sello2, :a_sello_inch2, :di_sello_inch2, :de_sello_inch2
        )");

        // Vincular los parámetros con los valores
        $stmt->bindParam(':id_cotizacion', $id_cotizacion);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':familia_perfil', $familia_perfil);
        $stmt->bindParam(':perfil_sello', $perfil_sello);
        $stmt->bindParam(':cantidad_material', $cantidad_material);
        $stmt->bindParam(':material', $material);
        $stmt->bindParam(':proveedor', $proveedor);
        $stmt->bindParam(':claves', $claves);
        $stmt->bindParam(':billets', $billets);
        $stmt->bindParam(':billets_lotes', $billets_lotes);
        $stmt->bindParam(':billets_claves_lotes', $billets_claves_lotes);
        $stmt->bindParam(':billets_string', $billets_string);
        $stmt->bindParam(':billets_string2', $billets_string2);
        $stmt->bindParam(':tipo_medida', $tipo_medida);
        $stmt->bindParam(':altura', $altura);
        $stmt->bindParam(':altura_caja', $altura_caja);
        $stmt->bindParam(':altura_escalon', $altura_escalon);
        $stmt->bindParam(':altura_h2', $altura_h2);
        $stmt->bindParam(':altura_h3', $altura_h3);
        $stmt->bindParam(':diametro_int', $diametro_int);
        $stmt->bindParam(':diametro_ext', $diametro_ext);
        $stmt->bindParam(':a_sello', $a_sello);
        $stmt->bindParam(':tipo_medida_h', $tipo_medida_h);
        $stmt->bindParam(':di_sello', $di_sello);
        $stmt->bindParam(':tipo_medida_di', $tipo_medida_di);
        $stmt->bindParam(':de_sello', $de_sello);
        $stmt->bindParam(':tipo_medida_de', $tipo_medida_de);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':total_unitarios', $total_unitarios);
        $stmt->bindParam(':desc_cliente', $desc_cliente);
        $stmt->bindParam(':desc_cantidad', $desc_cantidad);
        $stmt->bindParam(':desc_mayoreo', $desc_mayoreo);
        $stmt->bindParam(':total_descuentos', $total_descuentos);
        $stmt->bindParam(':total_material', $total_material);
        $stmt->bindParam(':estatus_completado', $estatus_completado);
        $stmt->bindParam(':vendedor', $vendedor);
        $stmt->bindParam(':img', $img);
        $stmt->bindParam(':cliente', $cliente);
        $stmt->bindParam(':tipo_cliente', $tipo_cliente);
        $stmt->bindParam(':codigo_cliente', $codigo_cliente);
        $stmt->bindParam(':correo_cliente', $correo_cliente);

        $stmt->bindParam(':a_sello_inch', $a_sello_inch);
        $stmt->bindParam(':di_sello_inch', $di_sello_inch);
        $stmt->bindParam(':de_sello_inch', $de_sello_inch);

        $stmt->bindParam(':a_sello2', $a_sello2);
        $stmt->bindParam(':di_sello2', $di_sello2);
        $stmt->bindParam(':de_sello2', $de_sello2);

        $stmt->bindParam(':a_sello_inch2', $a_sello_inch2);
        $stmt->bindParam(':di_sello_inch2', $di_sello_inch2);
        $stmt->bindParam(':de_sello_inch2', $de_sello_inch2);
        // Ejecutar la consulta
        $stmt->execute();

        // Responder con un mensaje de éxito
        echo json_encode(['success' => true, 'message' => 'Datos insertados correctamente.']);
    } else {
        // Si falta algún campo en el POST, enviar un error
        echo json_encode(['error' => 'Faltan datos en la solicitud.']);
    }

} catch (PDOException $e) {
    // Capturar cualquier error de base de datos
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>
