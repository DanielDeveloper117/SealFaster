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
    <link rel="icon" type="image/svg+xml" href="../assets/img/general/favicon.ico?v=2" />
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css'); ?>">

    <title>Panel | Claves</title>

    <style>
        /* ===== ESTILOS DEL PANEL DE FUNCIONES ===== */
        .dashboard-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow:
                0 20px 40px var(--shadow-color),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            padding: 2rem;
            position: relative;
            overflow: hidden;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .dashboard-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--glow-color), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }
        .dashboard-title {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(180deg, var(--glow-color), #95D2B3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 2rem 0;
            text-align: center;
        }
        .functions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .function-card {
            background: var(--surface-bg);
            border-radius: 15px;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .function-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(85, 173, 155, 0.2);
            border-color: var(--glow-color);
        }
        .function-icon {
            font-size: 2.5rem;
            color: var(--glow-color);
            margin-bottom: 1rem;
            text-align: center;
        }
        .function-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            text-align: center;
        }
        .function-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            flex-grow: 1;
            text-align: center;
        }
        .function-button {
            width: 100%;
            text-align: center;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: var(--glow-color);
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }
        .function-button:hover {
            background-color: #95D2B3;
            box-shadow: 0 4px 8px rgba(85, 173, 155, 1.2);
            transform: translateY(-3px);
            color: white;
            text-decoration: none;
        }
        .dashboard-section { margin-bottom: 2.5rem; }
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        @media (max-width: 768px) {
            .functions-grid { grid-template-columns: 1fr; }
            .dashboard-title { font-size: 1.8rem; }
            .dashboard-container { padding: 1.5rem; margin: 1rem; }
        }
        /* ===== FIN ESTILOS ===== */
    </style>
</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section class="section-table flex-column d-flex col-12 justify-content-center align-items-center">
    <div class="col-11 dashboard-container">
        <h1 class="dashboard-title">Panel de Gestión de Claves</h1>

        <!-- -------------------------------------------------- -->
        <!-- Seccion: Consultas                                  -->
        <!-- -------------------------------------------------- -->
        <div class="dashboard-section">
            <h2 class="section-title">Consultas</h2>
            <div class="functions-grid">

                <!-- Busqueda por material -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-layers"></i></div>
                    <h3 class="function-title">Busqueda por Material</h3>
                    <p class="function-description">
                        Filtra el catalogo de claves por tipo de material.
                        Los resultados se muestran en la tabla de claves.
                    </p>
                    <button type="button" class="function-button"
                            data-bs-toggle="modal" data-bs-target="#modalBuscarMaterial">
                        Seleccionar material
                    </button>
                </div>

                <!-- Busqueda por clave -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-input-cursor-text"></i></div>
                    <h3 class="function-title">Busqueda por Clave</h3>
                    <p class="function-description">
                        Ingresa la clave exacta para localizar su registro.
                        Los resultados se muestran en la tabla de claves.
                    </p>
                    <button type="button" class="function-button"
                            data-bs-toggle="modal" data-bs-target="#modalBuscarClave">
                        Digitar clave
                    </button>
                </div>

                <!-- Claves validas: ahora redirige a claves.php con filtros GET -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-rulers"></i></div>
                    <h3 class="function-title">Proveedor y Medida</h3>
                    <p class="function-description">
                        Consulta las claves existentes por proveedor y medidas.
                        Los resultados se abren en la tabla de claves para permitir
                        edicion directa sobre los registros encontrados.
                    </p>
                    <button type="button" class="function-button"
                            data-bs-toggle="modal" data-bs-target="#modalClavesValidas">
                        Abrir buscador
                    </button>
                </div>

                <!-- Catalogo completo -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-table"></i></div>
                    <h3 class="function-title">Catalogo Completo</h3>
                    <p class="function-description">
                        Carga todos los registros de claves en la tabla.
                        Puede tardar dependiendo del volumen de datos.
                    </p>
                    <a href="claves.php?all" class="function-button" target="_blank">
                        Ver catalogo completo <i class="bi bi-arrow-up-right mx-1"></i>
                    </a>
                </div>

            </div>
        </div>

        <!-- -------------------------------------------------- -->
        <!-- Seccion: Administracion                             -->
        <!-- -------------------------------------------------- -->
        <div class="dashboard-section">
            <h2 class="section-title">Administracion</h2>
            <div class="functions-grid">

                <!-- Agregar registro -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-plus-circle"></i></div>
                    <h3 class="function-title">Nueva Clave</h3>
                    <p class="function-description">
                        Accede al formulario para agregar un nuevo registro de clave.
                    </p>
                    <button type="button" class="btnAgregarAlmacen function-button" data-bs-toggle="modal" data-bs-target="#modalClave">
                        Abrir formulario
                    </button>
                    
                </div>

                <!-- Carga masiva CSV: ahora asincrona via modal -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
                    <h3 class="function-title">Carga Masiva CSV</h3>
                    <p class="function-description">
                        Sube un archivo .csv con multiples claves para actualizar
                        o insertar registros de forma masiva. El proceso podría tardar dependiendo del volumen de datos.
                    </p>
                    <button type="button" class="function-button"
                            data-bs-toggle="modal" data-bs-target="#modalCsvClaves">
                        Subir archivo .csv
                    </button>
                </div>

                <!-- Claves pendientes -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-clock-history"></i></div>
                    <h3 class="function-title">Claves sin precio</h3>
                    <p class="function-description">
                        Revisa claves con precio pendiente para actualizar la informacion y habilitar barras al cotizar.
                    </p>
                    <a href="claves.php?sin_precio" class="function-button" target="_blank">
                        Cargar tabla <i class="bi bi-arrow-up-right mx-1"></i>
                    </a>
                </div>

                <!-- Barras pendientes -->
                <div class="function-card">
                    <div class="function-icon"><i class="bi bi-database-exclamation"></i></div>
                    <h3 class="function-title">Barras Pendientes de Clave</h3>
                    <p class="function-description">
                        Revisa registros del inventario que requieren correccion de clave para habilitar
                        las barras al cotizar. La tabla puede tardar en cargar dependiendo de la cantidad de coincidencias.
                    </p>
                    <a href="inventario.php?pendientes" class="function-button" target="_blank">
                        Cargar tabla <i class="bi bi-arrow-up-right mx-1"></i>
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>


<!-- ============================================================
     MODAL: BUSCAR POR MATERIAL  →  redirige a claves.php?material=
     ============================================================ -->
<div class="modal fade" id="modalBuscarMaterial" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar claves por material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="claves.php" method="GET" target="_blank">
                    <div class="mb-4">
                        <label for="selectorMaterialPanel" class="lbl-general">
                            Material <span class="text-danger">*</span>
                        </label>
                        <select id="selectorMaterialPanel" class="selector selectorMaterialesParametros" name="material" required>
                            <option value="" disabled selected>Seleccionar...</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-general">
                        <i class="bi bi-search me-1"></i> Consultar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: BUSCAR POR CLAVE  →  redirige a claves.php?clave=
     ============================================================ -->
<div class="modal fade" id="modalBuscarClave" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar clave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="claves.php" method="GET" target="_blank">
                    <div class="mb-4">
                        <label for="inputClavePanel" class="lbl-general">
                            Clave (puede ser clave principal o clave alterna)<span class="text-danger">*</span>
                        </label>
                        <input type="text" id="inputClavePanel" class="input-text"
                               name="clave" required placeholder="Ingrese la clave exacta">
                    </div>
                    <button type="submit" class="btn-general">
                        <i class="bi bi-search me-1"></i> Consultar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================
     MODAL: CLAVES VALIDAS
     Ahora redirige a claves.php con filtros GET en lugar de
     mostrar resultados inline en el mismo modal.
     ============================================================ -->
<div class="modal fade" id="modalClavesValidas" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar claves validas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- El form usa GET y apunta a claves.php -->
                <form action="claves.php" method="GET" id="formClavesValidas" target="_blank">
                    <div class="mb-3">
                        <label for="selectorProveedorCV" class="lbl-general">
                            Proveedor <span class="text-danger">*</span>
                        </label>
                        <select id="selectorProveedorCV" class="selector selectorProveedoresParametros" name="proveedor" required>
                            <option value="" disabled selected>Seleccionar...</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="inputInteriorCV" class="lbl-general">
                                Medida interior <span class="text-danger">*</span>
                            </label>
                            <input id="inputInteriorCV" type="number" class="input-text"
                                   name="interior" placeholder="Ej: 30" min="0" required>
                        </div>
                        <div class="col-6">
                            <label for="inputExteriorCV" class="lbl-general">
                                Medida exterior <span class="text-danger">*</span>
                            </label>
                            <input id="inputExteriorCV" type="number" class="input-text"
                                   name="exterior" placeholder="Ej: 50" min="0" required>
                        </div>
                    </div>
                    <p class="text-muted" style="font-size:0.83rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Los resultados se abriran en la tabla de claves para que pueda
                        editarlos directamente.
                    </p>
                    <button type="submit" class="btn-general">
                        <i class="bi bi-search me-1"></i> Consultar en tabla de claves
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include(ROOT_PATH . 'includes/modal_clave.php'); ?>
<!-- ============================================================
     MODAL CSV (asincrono con barra de progreso)
     ============================================================ -->
<?php include(ROOT_PATH . 'includes/modal_csv_claves.php'); ?>


<!-- ============================================================
     JAVASCRIPT del panel
     ============================================================ -->
<script>
$(document).ready(function () {


    // ---- Validacion del formulario de claves validas antes de redirigir ----
    $('#formClavesValidas').on('submit', function (e) {
        var proveedor = $('#selectorProveedorCV').val();
        var interior  = $('#inputInteriorCV').val();
        var exterior  = $('#inputExteriorCV').val();

        if (!proveedor || !interior || !exterior) {
            e.preventDefault();
            sweetAlertResponse('warning', 'Campos incompletos',
                'Complete proveedor, medida interior y medida exterior.', 'none');
        }
    });

});
</script>

</body>
</html>

<script src="../assets/js/dynamic_selectors.js?v=<?= time() ?>"></script>