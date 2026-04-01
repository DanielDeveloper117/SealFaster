<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

// ============================================================
// CONSULTA PRINCIPAL: grupos con conteo de perfiles y limitantes
// ============================================================
$sqlGrupos = "
    SELECT
        gh.id,
        gh.nombre,
        gh.descripcion,
        gh.updated_at,
        gh.created_at,
        COUNT(DISTINCT p.id)  AS total_perfiles,
        COUNT(DISTINCT lh.id) AS total_limitantes
    FROM grupos_herramienta gh
    LEFT JOIN perfiles2 p  ON p.grupo_herramienta_id = gh.id
    LEFT JOIN limitantes_herramienta lh ON lh.grupo_herramienta_id = gh.id
    GROUP BY gh.id
    ORDER BY gh.nombre ASC
";
$stmtGrupos = $conn->prepare($sqlGrupos);
$stmtGrupos->execute();
$arregloGrupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Grupos y Limitantes de Herramientas</title>
    <style>
        .dt-scroll { margin-top:10px !important; margin-bottom:10px !important; }
        .badge-count {
            font-size: 0.72rem; padding: 3px 8px; border-radius: 10px; font-weight: 600;
        }
        .badge-perfiles   { background:#e3f2fd; color:#1565c0; border:1px solid #90caf9; }
        .badge-limitantes { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
        .badge-sin        { background:#fafafa; color:#9e9e9e; border:1px solid #e0e0e0; }
        .btn-accion-del:disabled { opacity:0.4; cursor:not-allowed; }
    </style>
</head>
<body>
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<div id="overlay">
    <div class="loading-message">
        <span>Cargando grupos de herramienta, por favor espere...</span>
    </div>
</div>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Grupos y Limitantes de Herramientas</h1>
            <div class="d-flex flex-row justify-content-start col-12 col-md-3 gap-2">
                <?php if ($tipo_usuario === "Administrador" || $tipo_usuario === "Sistemas"
                       || ($tipo_usuario === "CNC" && $rol_usuario === "Gerente")): ?>
                    <button type="button"
                            class="btn-general d-flex justify-content-center align-items-center gap-2"
                            id="btnNuevoGrupo">
                        <i class="bi bi-plus-circle" style="font-size:22px;"></i>
                        Nuevo grupo
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-container">
            <table id="gruposTable" class="mainTable table table-striped table-bordered" style="width:100%;">
                <thead>
                    <tr>
                        <th>Acciones</th>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Perfiles</th>
                        <th>Limitantes</th>
                        <th>Actualización</th>
                        <th>Creación</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($arregloGrupos as $row):
                    $totalPerfiles   = (int)$row['total_perfiles'];
                    $totalLimitantes = (int)$row['total_limitantes'];
                    $tieneRelaciones = $totalPerfiles > 0;
                    $tieneLimitantes = $totalLimitantes > 0;
                ?>
                <tr id="tr_grupo_<?= $row['id']; ?>">
                    <td class="td-first-actions">
                        <div class="d-flex gap-1 container-actions">

                            <?php if ($tipo_usuario === "Administrador" || $tipo_usuario === "Sistemas"
                                   || ($tipo_usuario === "CNC" && $rol_usuario === "Gerente")): ?>

                                <!-- Eliminar grupo -->
                                <?php if ($tieneRelaciones): ?>
                                    <button class="btn-cancel btn-accion-del"
                                            disabled
                                            title="No se puede eliminar: tiene <?= $totalPerfiles; ?> perfil(es) asignado(s)"
                                            style="opacity:0.4; cursor:not-allowed;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-cancel btn-accion-del btn-eliminar-grupo"
                                            title="Eliminar grupo"
                                            data-id="<?= $row['id']; ?>"
                                            data-nombre="<?= htmlspecialchars($row['nombre']); ?>"
                                            data-limitantes="<?= $totalLimitantes; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>

                                <!-- Editar grupo -->
                                <button class="btn-general btn-accion-edit btn-editar-grupo"
                                        title="Editar nombre y descripción"
                                        data-id="<?= $row['id']; ?>"
                                        data-nombre="<?= htmlspecialchars($row['nombre']); ?>"
                                        data-descripcion="<?= htmlspecialchars($row['descripcion'] ?? ''); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <!-- Ver/gestionar limitantes -->
                                <button class="btn-thunder btn-accion-lim btn-ver-limitantes"
                                        title="Ver y gestionar limitantes de herramienta"
                                        data-id="<?= $row['id']; ?>"
                                        data-nombre="<?= htmlspecialchars($row['nombre']); ?>">
                                    <i class="bi bi-sliders2"></i>
                                </button>

                                <!-- Asignar perfiles al grupo (solo si tiene al menos 1 limitante) -->
                                <?php if ($tieneLimitantes): ?>
                                    <button class="btn-blue btn-accion-perfiles btn-gestionar-perfiles"
                                            title="Asignar o desvincular perfiles"
                                            data-id="<?= $row['id']; ?>"
                                            data-nombre="<?= htmlspecialchars($row['nombre']); ?>"
                                            data-perfiles="<?= $totalPerfiles; ?>">
                                        <i class="bi bi-diagram-3"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-blue btn-accion-perfiles"
                                            disabled
                                            title="Primero agregue al menos una limitante para poder asignar perfiles"
                                            style="opacity:0.4; cursor:not-allowed;">
                                        <i class="bi bi-diagram-3"></i>
                                    </button>
                                <?php endif; ?>



                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['id']); ?></td>
                    <td><strong><?= htmlspecialchars($row['nombre']); ?></strong></td>
                    <td><?= htmlspecialchars($row['descripcion'] ?? '—'); ?></td>
                    <td>
                        <?php if ($totalPerfiles > 0): ?>
                            <span class="badge-count badge-perfiles">
                                <i class="bi bi-link-45deg"></i> <?= $totalPerfiles; ?> perfil(es)
                            </span>
                        <?php else: ?>
                            <span class="badge-count badge-sin">Sin perfiles</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($totalLimitantes > 0): ?>
                            <span class="badge-count badge-limitantes">
                                <i class="bi bi-check-circle"></i> <?= $totalLimitantes; ?> limitante(s)
                            </span>
                        <?php else: ?>
                            <span class="badge-count badge-sin">Sin limitantes</span>
                        <?php endif; ?>
                    </td>
                    <td><?= !empty($row['updated_at']) ? date("d/m/Y H:i", strtotime($row['updated_at'])) : '—'; ?></td>
                    <td><?= !empty($row['created_at']) ? date("d/m/Y H:i", strtotime($row['created_at'])) : '—'; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include(ROOT_PATH . 'includes/modal_grupo_herramientas.php'); ?>
<?php include(ROOT_PATH . 'includes/modal_limitantes_herramientas.php'); ?>
<?php include(ROOT_PATH . 'includes/modal_perfiles_grupo.php'); ?>

<script>
$(document).ready(function () {


});
</script>
<style>
    /* Variables */
    :root {
        --mghp-bg:           #f0f8f0;
        --mghp-surface:      #fff;
        --mghp-surface-2:    #e6f2e6;
        --mghp-surface-3:    #d1e7d1;
        --mghp-border:       rgba(255,255,255,0.07);
        --mghp-border-hover: rgba(255,255,255,0.14);
        --mghp-text-primary: #e8eaf0;
        --mghp-text-muted:   #5a6070;
        --mghp-text-label:   #7c8494;
        --mghp-accent-blue:  #3b82f6;
        --mghp-accent-cyan:  #06b6d4;
        --mghp-accent-green: #22c55e;
        --mghp-accent-amber: #f59e0b;
        --mghp-radius:       10px;
        --mghp-radius-sm:    6px;
        --mghp-shadow:       0 24px 64px rgba(0,0,0,0.6);
    }

    /* Dialog */
    .mghp-dialog {
        max-width: 960px;
    }

    /* Content */
    .mghp-content {
        background: var(--mghp-surface);
        border: 1px solid var(--mghp-border);
        border-radius: 14px !important;
        box-shadow: var(--mghp-shadow);
        overflow: hidden;
    }


    /* ---- BODY ---- */
    .mghp-body {
        background: var(--mghp-surface);
        padding: 1.5rem;
    }

    /* ---- TABS ---- */
    .mghp-tabs {
        display: flex;
        gap: 4px;
        padding-bottom: 0;
        list-style: none;
        padding-left: 0;
    }

    .mghp-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0.55rem 1rem;
        font-size: 16px;
        font-weight: 500;
        color: #0a0f0a;
        background: transparent;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: var(--mghp-radius-sm) var(--mghp-radius-sm) 0 0;
        cursor: pointer;
        transition: color 0.15s, background 0.15s, border-color 0.15s;
        position: relative;
        bottom: -1px;
        white-space: nowrap;
    }

    .mghp-tab-btn:hover {
        color: #55ad9b;
        background: var(--mghp-surface-2);
    }

    .mghp-tab-btn.active {
        color: #55ad9b;
        font-weight: 700;
        background: var(--mghp-surface);
        border-color: var(--mghp-border);
        border-bottom-color: var(--mghp-surface);
    }

    .mghp-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        font-size: 14px;
        font-weight: 600;
        background: rgba(85, 173, 155, 0.1);
        color: #55ad9b;
        border-radius: 20px;
        border: 1px solid #55ad9b;
    }

    /* ---- SECCIONES ---- */
    .mghp-section {
        background: #fff;
        border: 1px solid var(--mghp-border);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .mghp-section:last-child {
        margin-bottom: 0;
    }

    .mghp-section-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.7rem 1.1rem;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        background-color: #607d8b;
        border-bottom: 1px solid rgba(59,130,246,0.15);
        color: #fff;
    }

    .mghp-section-body {
        padding: 1.25rem 1.1rem;
    }

    /* ---- GRID DE CAMPOS ---- */
    .mghp-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }

    .mghp-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .mghp-field--full {
        grid-column: 1 / -1;
    }

    .mghp-field-label {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #333;
    }

    .mghp-field-value {
        font-size: 14px;
        font-weight: 500;
        color: #555;
        margin: 0;
        padding: 0.45rem 0.65rem;
        background: var(--mghp-surface-2);
        border: 1px solid var(--mghp-border);
        border-radius: var(--mghp-radius-sm);
        line-height: 1.4;
        min-height: 34px;
        display: flex;
        align-items: center;
    }


    /* ---- IMÁGENES ---- */
    .mghp-images-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .mghp-image-block {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mghp-img-viewer {
        min-height: 220px;
        background: var(--mghp-surface-2);
        border: 1px dashed var(--mghp-border-hover);
        border-radius: var(--mghp-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .mghp-img-viewer:hover {
        border-color: rgba(59,130,246,0.3);
    }

    .mghp-img-viewer img {
        max-width: 100%;
        max-height: 280px;
        object-fit: contain;
        border-radius: var(--mghp-radius-sm);
    }

    .mghp-img-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: var(--mghp-text-muted);
        letter-spacing: 0.02em;
    }

    /* ---- TABLA BARRAS ---- */
    .mghp-barras-toolbar {
        display: flex;
        align-items: center;
    }

    .mghp-table-wrapper {
        max-height: 300px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid var(--mghp-border);
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mghp-table-wrapper::-webkit-scrollbar { width: 6px; }
    .mghp-table-wrapper::-webkit-scrollbar-track { background: transparent; }
    .mghp-table-wrapper::-webkit-scrollbar-thumb { background: #2a2e3a; border-radius: 4px; }

    .mghp-table {
        width: 100%;
        margin: 0 !important;
        background: var(--mghp-bg) !important;
    }

    .mghp-table thead tr th {
        background: #b8d4b8 !important;
        color: #0a0f0a !important;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.65rem 0.85rem;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .mghp-table tbody tr {
        background: #e8f5e8 !important;
        transition: background 0.12s;
    }

    .mghp-table tbody td {
        font-size: 14px;
        font-weight: 400;
        color: #0a0f0a !important;
        padding: 0.55rem 0.85rem;
        vertical-align: middle;
    }

    /* Badges del JS — sobrescribir colores para tema oscuro */
    .mghp-table .badge.bg-info {
        background: rgba(6,182,212,0.15) !important;
        color: #67e8f9 !important;
        border: 1px solid rgba(6,182,212,0.25);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mghp-table .badge.bg-warning {
        background: rgba(245,158,11,0.15) !important;
        color: #fcd34d !important;
        border: 1px solid rgba(245,158,11,0.25);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mghp-table .badge.bg-secondary {
        background: rgba(100,116,139,0.2) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(100,116,139,0.25);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mghp-table .badge.bg-danger {
        background: rgba(239,68,68,0.12) !important;
        color: #fca5a5 !important;
        border: 1px solid rgba(239,68,68,0.22);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mghp-table .badge.bg-light {
        background: rgba(255,255,255,0.05) !important;
        color: #9ca3af !important;
        border: 1px solid rgba(255,255,255,0.1);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    /* Badges fuera de la tabla (estado de recepción) */
    .badge.bg-success {
        background: rgba(34,197,94,0.12) !important;
        /* #86efac */
        color: #00876cde !important;
        border: 1px solid rgba(34,197,94,0.22);
        font-size: 14px;
        font-weight: 700;
        padding: 10px 0.65em;
        border-radius: 4px;
    }

    .badge.bg-warning {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #ffbf00 !important;
        border: 1px solid rgba(245, 158, 11, 0.22);
        font-size: 14px;
        font-weight: 700;
        padding: 10px 0.65em;
        border-radius: 4px;
    }

    /* ---- RESPONSIVO ---- */
    @media (max-width: 768px) {
        .mghp-grid {
            grid-template-columns: 1fr;
        }
        .mghp-field--full {
            grid-column: 1;
        }
        .mghp-images-grid {
            grid-template-columns: 1fr;
        }
        .mghp-body {
            padding: 1rem;
        }
    }
</style>
</body>
</html>