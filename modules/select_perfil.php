<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/select-perfil.css'); ?>">

    <title>Tipo de sello</title>
</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<?php
function filter_images($file) {
    $extensiones_validas = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    // Verifica que sea una imagen válida y que termine con "_0.jpg"
    return in_array($extension, $extensiones_validas) && str_ends_with($file, '_0.jpg');
}


// Función para obtener imágenes en la nueva estructura de subcarpetas
function get_seal_images($baseDir) {
    // Busca en todas las subcarpetas (*) cualquier archivo que termine en _0.jpg
    // Ejemplo: ../assets/img/rotary/*/ *_0.jpg
    $pattern = $baseDir . '*/*_0.jpg';
    $files = glob($pattern);
    
    // Solo necesitamos el nombre del archivo para mantener la compatibilidad con tu lógica de chunks
    return array_map(function($path) {
        return basename($path);
    }, $files);
}

// Obtener imágenes para cada categoría
$dirRotary = '../assets/img/rotary/';  
$imagesRotary = get_seal_images($dirRotary);
$chunksRotary = array_chunk($imagesRotary, 6);

$dirPiston = '../assets/img/piston/';
$imagesPiston = get_seal_images($dirPiston);
$chunksPiston = array_chunk($imagesPiston, 6);

$dirRod = '../assets/img/rod/';  
$imagesRod = get_seal_images($dirRod);
$chunksRod = array_chunk($imagesRod, 6);

$dirBackup = '../assets/img/backup/';
$imagesBackup = get_seal_images($dirBackup);
$chunksBackup = array_chunk($imagesBackup, 6);

$dirGuide = '../assets/img/guide/';
$imagesGuide = get_seal_images($dirGuide);
$chunksGuide = array_chunk($imagesGuide, 6);

$dirWipers = '../assets/img/wiper/';
$imagesWipers = get_seal_images($dirWipers);
$chunksWipers = array_chunk($imagesWipers, 6);


/// tipo de sello
if (isset($_GET['fam'])) {

    $familia = $_GET['fam'];

    function display_seals($title, $chunks, $dir, $familia) {
        echo '
            <div class="d-flex col-12 justify-content-center mt-4">
                <div class="titulo d-flex col-10 justify-content-between">
                    <h2>Seleccione un perfil ' . $title . '</h2>
                    <a id="btnBack" href="select_familia.php" class="btn-general"
                    ><- Regresar</a>
                </div>
            </div>
        ';
        echo '
        <section id="sectionPerfiles" class="d-flex flex-column col-12 justify-content-center align-items-center">';
    
        // Agregar un padding en caso de que el número de imágenes no sea múltiplo de 8
        foreach ($chunks as $chunk) {
            echo '<div class="row-perfiles d-flex flex-column flex-md-row col-11 mt-4 align-items-center align-items-md-stretch" style="">';
    
            // Número de imágenes que hay en el chunk
            $imageCount = count($chunk);
    
            // Agregar imágenes y elementos vacíos si es necesario para completar 8 elementos
            for ($i = 0; $i < 6; $i++) {
                if ($i < $imageCount) {
                    // Hay una imagen en esta posición
                    $image = $chunk[$i];
                    $ultimoCaracter = substr($image, -1);
                    $selloOriginal = substr($image, 0, -6);
                    echo '<a class="a-card d-flex flex-column align-items-center justify-content-center p-2" href="estimador.php?fam=' . urlencode($familia) . '&perfil=' . urlencode($selloOriginal) . '">';
                        echo '<img src="' . htmlspecialchars($dir . $selloOriginal.'/'.$selloOriginal.'_0.jpg') . '" alt="' . htmlspecialchars(pathinfo($image, PATHINFO_FILENAME)) . '" loading="lazy" style="height:100%;width:100%;">
                              <h5>' . htmlspecialchars($selloOriginal) . '</h5>';
                    echo '</a>';
                } else {
                    // No hay más imágenes, se inserta un espacio vacío
                    echo '<div class="a-card" style="
                            background-color: transparent !important;
                            border: none !important;
                            box-shadow: none !important;
                        "></div>';
                }
            }
    
            echo '</div>';
        }
    
        echo '</section>';
    }
    

    switch ($familia) {
        case 'rotary':
            display_seals('rotativo', $chunksRotary, $dirRotary, $familia);
            break;
        case 'piston':
            display_seals('pistón', $chunksPiston, $dirPiston, $familia);
            break;
        case 'rod':
            display_seals('vástago', $chunksRod, $dirRod, $familia);
            break;
        case 'backup':
            display_seals('respaldo', $chunksBackup, $dirBackup, $familia);
            break;
        case 'guide':
            display_seals('guía', $chunksGuide, $dirGuide, $familia);
            break;
        case 'wipers':
            display_seals('limpiador', $chunksWipers, $dirWipers, $familia);
            break;
        default:
            header('Location: select_familia.php');
            exit();
    }
} else {
    header('Location: select_familia.php');
    exit();
}
?>
<div style="height:500px;"></div>
<?php include(ROOT_PATH . 'includes/footer.php'); ?>
<script>
    $(document).ready(function(){
        $("#buttonUser").on("click", function(){
            $("#btnBack").toggleClass("d-none");
        });
    });
</script>
</body>
</html>
