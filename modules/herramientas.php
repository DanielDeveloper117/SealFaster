<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');

// ============================================================
// CONSULTA PRINCIPAL: herramientas con conteo de relaciones
// Se cuenta cuantas veces aparece cada herramienta en
// limitantes_herramienta para saber si puede eliminarse o
// si editar su numero implica impacto en otras tablas.
// ============================================================
$sqlHerramientas = "
    SELECT
        h.id,
        h.numero,
        h.descripcion,
        h.updated_at,
        h.created_at,
        COUNT(lh.id) AS total_relaciones
    FROM herramientas h
    LEFT JOIN limitantes_herramienta lh ON lh.herramienta_id = h.id
    GROUP BY h.id
    ORDER BY h.numero ASC
";
$stmtHerramientas = $conn->prepare($sqlHerramientas);
$stmtHerramientas->execute();
$arregloHerramientas = $stmtHerramientas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- jQuery -->
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>

    <!-- Bootstrap -->
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>

    <!-- SweetAlert -->
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/bootstrap-icons.min.css'); ?>">

    <!-- DataTables -->
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>

    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>">

    <title>Catálogo Herramientas CNC</title>
    <style>
        .dt-scroll {
            margin-top: 10px !important;
            margin-bottom: 10px !important;
        }
        .badge-relaciones {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 12px;
        }
        .badge-con-relacion {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .badge-sin-relacion {
            background-color: #fafafa;
            color: #757575;
            border: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<div id="overlay">
    <div class="loading-message">
        <span>Cargando catálogo de herramientas, por favor espere...</span>
    </div>
</div>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Catálogo Herramientas CNC</h1>
            <div class="d-flex flex-row justify-content-start col-12 col-md-3 gap-2">
                <?php if ($tipo_usuario === "Administrador" || $tipo_usuario === "Sistemas"
                       || ($tipo_usuario === "CNC" && $rol_usuario === "Gerente")): ?>
                    <button type="button"
                            class="btn-general d-flex justify-content-center align-items-center gap-2"
                            id="btnAbrirModalNueva"
                            data-bs-toggle="modal"
                            data-bs-target="#modalHerramienta">
                        <i class="bi bi-plus-circle" style="font-size:22px;"></i>
                        Nueva herramienta
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-container">
            <table id="herramientasTable" class="mainTable table table-striped table-bordered" style="width:100%;">
                <thead>
                    <tr>
                        <th style="background-color:#55ad9b52;"></th>
                        <th>Id</th>
                        <th>Número</th>
                        <th>Descripción</th>
                        <th>En grupos</th>
                        <th>Actualización</th>
                        <th>Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloHerramientas as $row): ?>
                    <tr id="tr_<?= $row['id']; ?>">
                        <td class="td-first-actions">
                            <div class="d-flex gap-2 container-actions">
                                <?php if ($tipo_usuario === "Administrador" || $tipo_usuario === "Sistemas"
                                       || ($tipo_usuario === "CNC" && $rol_usuario === "Gerente")): ?>

                                    <!-- Boton editar -->
                                    <button class="btn-general edit-btn"
                                            title="Editar herramienta"
                                            data-id="<?= $row['id']; ?>"
                                            data-numero="<?= htmlspecialchars($row['numero']); ?>"
                                            data-descripcion="<?= htmlspecialchars($row['descripcion'] ?? ''); ?>"
                                            data-relaciones="<?= (int)$row['total_relaciones']; ?>">
                                        <i class="bi bi-pencil-square px-1"></i>
                                    </button>

                                    <!-- Boton eliminar: deshabilitado si tiene relaciones -->
                                    <?php if ((int)$row['total_relaciones'] > 0): ?>
                                        <button class="btn-eliminar"
                                                title="No se puede eliminar: está asignada a <?= (int)$row['total_relaciones']; ?> grupo(s)"
                                                disabled
                                                style="opacity:0.45; cursor:not-allowed;">
                                            <i class="bi bi-trash px-1"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-eliminar delete-btn"
                                                title="Eliminar herramienta"
                                                data-id="<?= $row['id']; ?>"
                                                data-numero="<?= htmlspecialchars($row['numero']); ?>">
                                            <i class="bi bi-trash px-1"></i>
                                        </button>
                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td><strong><?= htmlspecialchars($row['numero']); ?></strong></td>
                        <td><?= htmlspecialchars($row['descripcion'] ?? '—'); ?></td>
                        <td>
                            <?php if ((int)$row['total_relaciones'] > 0): ?>
                                <span class="badge-relaciones badge-con-relacion">
                                    <i class="bi bi-link-45deg"></i>
                                    <?= (int)$row['total_relaciones']; ?> grupo(s)
                                </span>
                            <?php else: ?>
                                <span class="badge-relaciones badge-sin-relacion">
                                    Sin asignar
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo !empty($row['updated_at'])
                                ? date("d/m/Y H:i", strtotime($row['updated_at']))
                                : '—'; ?>
                        </td>
                        <td>
                            <?php echo !empty($row['created_at'])
                                ? date("d/m/Y H:i", strtotime($row['created_at']))
                                : '—'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal agregar / editar herramienta -->
<?php include(ROOT_PATH . 'includes/modal_herramienta.php'); ?>

<script>
$(document).ready(function () {

    // ============================================================
    // ABRIR MODAL EN MODO NUEVA HERRAMIENTA
    // ============================================================
    $("#btnAbrirModalNueva").on("click", function () {
        $("#formHerramienta")[0].reset();
        $("#inputIdHerramienta").val("");
        $("#inputActionHerramienta").val("insert");
        $("#titleModalHerramienta").text("Nueva herramienta");
        $("#alertaImpacto").addClass("d-none");
        $("#inputNumeroHerramienta").prop("readonly", false);
    });

    // ============================================================
    // ABRIR MODAL EN MODO EDICION
    // ============================================================
    $("#herramientasTable").on("click", ".edit-btn", function () {
        var id           = $(this).data("id");
        var numero       = $(this).data("numero");
        var descripcion  = $(this).data("descripcion");
        var relaciones   = parseInt($(this).data("relaciones"));

        $("#formHerramienta")[0].reset();
        $("#inputIdHerramienta").val(id);
        $("#inputNumeroHerramienta").val(numero);
        $("#inputDescripcionHerramienta").val(descripcion);
        $("#inputActionHerramienta").val("update");
        $("#titleModalHerramienta").text("Editar herramienta: " + numero);

        // Si la herramienta esta relacionada mostrar alerta de impacto
        if (relaciones > 0) {
            $("#alertaImpacto").removeClass("d-none");
            $("#spanGruposAfectados").text(relaciones);
        } else {
            $("#alertaImpacto").addClass("d-none");
        }

        $("#modalHerramienta").modal("show");
    });

    // ============================================================
    // ELIMINAR HERRAMIENTA (solo las que no tienen relaciones)
    // La logica PHP tambien lo verifica como segunda capa de seguridad
    // ============================================================
    $("#herramientasTable").on("click", ".delete-btn", function () {
        var id     = $(this).data("id");
        var numero = $(this).data("numero");

        Swal.fire({
            title: "Eliminar herramienta",
            html: "¿Confirma eliminar la herramienta <strong>" + numero + "</strong>?<br><small>Esta acción no se puede deshacer.</small>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#ca4747",
            cancelButtonColor: "#6c757d",
            allowOutsideClick: false,
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "../ajax/post_herramienta.php",
                    type: "POST",
                    data: { id: id, action: "delete" },
                    dataType: "json",
                    success: function (data) {
                        if (data.success) {
                            sweetAlertResponse("success", "Herramienta eliminada", data.message, "self");
                        } else {
                            sweetAlertResponse("warning", "No se pudo eliminar", data.message, "none");
                        }
                    },
                    error: function (xhr, status, error) {
                        sweetAlertResponse("error", "Error", "Error al procesar la solicitud. " + error, "none");
                    }
                });
            }
        });
    });

});
</script>
</body>
</html>