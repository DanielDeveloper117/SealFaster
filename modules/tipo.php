<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-tipo.css'); ?>">

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


//-----------------------------------------------------------------------------------------------------------
//                                ROTATIVOS
$dirRotary = '../assets/img/rotary/';  
$filesRotary = scandir($dirRotary);
$imagesRotary = array_filter($filesRotary, 'filter_images');
$chunksRotary = array_chunk($imagesRotary, 6);

//-----------------------------------------------------------------------------------------------------------
//                                PISTON
$dirPiston = '../assets/img/piston/';
$filesPiston = scandir($dirPiston);
$imagesPiston = array_filter($filesPiston, 'filter_images');
$chunksPiston = array_chunk($imagesPiston, 6);

//-----------------------------------------------------------------------------------------------------------
//                                ROD
$dirRod = '../assets/img/rod/';  
$filesRod = scandir($dirRod);
$imagesRod = array_filter($filesRod, 'filter_images');
$chunksRod = array_chunk($imagesRod, 6);

//-----------------------------------------------------------------------------------------------------------
//                                BACKUP
$dirBackup = '../assets/img/backup/';
$filesBackup = scandir($dirBackup);
$imagesBackup = array_filter($filesBackup, 'filter_images');
$chunksBackup = array_chunk($imagesBackup, 6);

//-----------------------------------------------------------------------------------------------------------
//                                GUIDE
$dirGuide = '../assets/img/guide/';
$filesGuide = scandir($dirGuide);
$imagesGuide = array_filter($filesGuide, 'filter_images');
$chunksGuide = array_chunk($imagesGuide, 6);

//-----------------------------------------------------------------------------------------------------------
//                                WIPERS
$dirWipers = '../assets/img/wipers/';
$filesWipers = scandir($dirWipers);
$imagesWipers = array_filter($filesWipers, 'filter_images');
$chunksWipers = array_chunk($imagesWipers, 6);


/// tipo de sello
if (isset($_GET['tipo'])) {

    $tipo = $_GET['tipo'];

    function display_seals($title, $chunks, $dir, $tipo) {
        echo '
            <div class="d-flex col-12 justify-content-center mt-4">
                <div class="titulo d-flex col-10 justify-content-between">
                    <h2>Seleccione un perfil ' . $title . '</h2>
                    <a id="btnBack" href="selectTipoSello.php" class="btn-general"
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
                    echo '<a class="a-card d-flex flex-column align-items-center justify-content-center p-2" href="estimador.php?tipo=' . urlencode($tipo) . '&sello=' . urlencode(pathinfo($image, PATHINFO_FILENAME)) . '">';
                        echo '<img src="' . htmlspecialchars($dir . $selloOriginal.'_0.jpg') . '" alt="' . htmlspecialchars(pathinfo($image, PATHINFO_FILENAME)) . '" loading="lazy" style="height:100%;width:100%;">
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
    

    switch ($tipo) {
        case 'rotary':
            display_seals('rotativo', $chunksRotary, $dirRotary, $tipo);
            break;
        case 'piston':
            display_seals('pistón', $chunksPiston, $dirPiston, $tipo);
            break;
        case 'rod':
            display_seals('vástago', $chunksRod, $dirRod, $tipo);
            break;
        case 'backup':
            display_seals('respaldo', $chunksBackup, $dirBackup, $tipo);
            break;
        case 'guide':
            display_seals('guía', $chunksGuide, $dirGuide, $tipo);
            break;
        case 'wipers':
            display_seals('limpiador', $chunksWipers, $dirWipers, $tipo);
            break;
        default:
            header('Location: selectTipoSello.php');
            exit();
    }
} else {
    header('Location: selectTipoSello.php');
    exit();
}
?>
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
