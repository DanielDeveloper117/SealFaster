<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');

// ============================================================
// CONSULTA PRINCIPAL: perfiles con familia y grupo
// ============================================================
$sqlPerfiles = "
    SELECT
        p.id,
        p.nombre,
        p.cantidad_componentes,
        p.con_resorte_en,
        p.es_wiper_en,
        p.con_escalon_en,
        p.es_wiper_especial_en,
        p.detalles,
        p.grupo_herramienta_id,
        p.updated_at,
        p.created_at,
        f.nombre  AS familia,
        f.nombre2 AS familia2,
        gh.nombre AS grupo_nombre
    FROM perfiles2 p
    JOIN familias f ON f.id = p.familia_id
    LEFT JOIN grupos_herramienta gh ON gh.id = p.grupo_herramienta_id
    ORDER BY f.nombre ASC, p.nombre ASC
";
$stmtPerfiles = $conn->prepare($sqlPerfiles);
$stmtPerfiles->execute();
$arregloPerfiles = $stmtPerfiles->fetchAll(PDO::FETCH_ASSOC);

// Grupos para el selector del formulario de edicion
$sqlGrupos = "SELECT id, nombre FROM grupos_herramienta ORDER BY nombre ASC";
$stmtGrupos = $conn->prepare($sqlGrupos);
$stmtGrupos->execute();
$arregloGrupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/bootstrap-icons.min.css'); ?>">
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>">
    <title>Perfiles de Sello</title>
    <style>
        .dt-scroll { margin-top:10px !important; margin-bottom:10px !important; }
        .badge-grupo {
            font-size: 0.7rem; padding: 3px 8px; border-radius: 10px; font-weight: 600;
            background:#e3f2fd; color:#1565c0; border:1px solid #90caf9;
        }
        .badge-sin-grupo {
            font-size: 0.7rem; padding: 3px 8px; border-radius: 10px; font-weight: 600;
            background:#fff8e1; color:#f57f17; border:1px solid #ffe082;
        }
        .flag-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 0.68rem; padding: 2px 7px; border-radius: 8px; font-weight: 600;
        }
        .flag-activo  { background:#f3e5f5; color:#6a1b9a; border:1px solid #ce93d8; }
        .flag-inactivo{ background:#fafafa; color:#bdbdbd; border:1px solid #e0e0e0; }
    </style>
</head>
<body>
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<div id="overlay">
    <div class="loading-message">
        <span>Cargando perfiles, por favor espere...</span>
    </div>
</div>

<!-- Pasar grupos al JS para el selector de edicion -->
<script>
    window.GRUPOS_HERRAMIENTA = <?= json_encode($arregloGrupos); ?>;
    window.ES_ADMINISTRADOR   = <?= ($tipo_usuario === 'Administrador' || $tipo_usuario === 'Sistemas') ? 'true' : 'false'; ?>;
</script>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Perfiles de Sello</h1>
        </div>

        <div class="table-container">
            <table id="perfilesTable" class="mainTable table table-striped table-bordered" style="width:100%;">
                <thead>
                    <tr>
                        <th style="background-color:#55ad9b52;">Acciones</th>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Familia</th>
                        <th>Componentes</th>
                        <th>Grupo herramienta</th>
                        <th>Actualización</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($arregloPerfiles as $row):
                    // Construir flags activos para columna resumen
                    $flags = [];
                    if ($row['con_resorte_en']      != '0') $flags[] = '<span class="flag-badge flag-activo"><i class="bi bi-arrow-repeat"></i> Resorte</span>';
                    if ($row['es_wiper_en']          != '0') $flags[] = '<span class="flag-badge flag-activo"><i class="bi bi-droplet"></i> Wiper</span>';
                    if ($row['con_escalon_en']       != '0') $flags[] = '<span class="flag-badge flag-activo"><i class="bi bi-layers"></i> Escalón</span>';
                    if ($row['es_wiper_especial_en'] != '0') $flags[] = '<span class="flag-badge flag-activo"><i class="bi bi-stars"></i> W. Especial</span>';
                ?>
                <tr id="tr_perfil_<?= $row['id']; ?>">
                    <td class="td-first-actions">
                        <div class="d-flex gap-1 container-actions">
                            <?php if ($tipo_usuario === "Administrador" || $tipo_usuario === "Sistemas"
                                   || ($tipo_usuario === "CNC" && $rol_usuario === "Gerente")): ?>

                                <!-- Ver detalles -->
                                <button class="btn-archive btn-ver-detalle"
                                        title="Ver detalles del perfil"
                                        data-id="<?= $row['id']; ?>"
                                        data-nombre="<?= htmlspecialchars($row['nombre']); ?>"
                                        data-familia="<?= htmlspecialchars($row['familia']); ?>"
                                        data-familia2="<?= htmlspecialchars($row['familia2'] ?? ''); ?>"
                                        data-componentes="<?= (int)$row['cantidad_componentes']; ?>"
                                        data-detalles="<?= htmlspecialchars($row['detalles'] ?? ''); ?>"
                                        data-resorte="<?= htmlspecialchars($row['con_resorte_en']); ?>"
                                        data-wiper="<?= htmlspecialchars($row['es_wiper_en']); ?>"
                                        data-escalon="<?= htmlspecialchars($row['con_escalon_en']); ?>"
                                        data-wiper-especial="<?= htmlspecialchars($row['es_wiper_especial_en']); ?>"
                                        data-grupo-id="<?= (int)($row['grupo_herramienta_id'] ?? 0); ?>"
                                        data-grupo-nombre="<?= htmlspecialchars($row['grupo_nombre'] ?? ''); ?>"
                                        data-updated="<?= htmlspecialchars($row['updated_at'] ?? ''); ?>"
                                        data-created="<?= htmlspecialchars($row['created_at'] ?? ''); ?>">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- Editar perfil -->
                                <button class="btn-general btn-editar-perfil"
                                        title="Editar perfil"
                                        data-id="<?= $row['id']; ?>"
                                        data-nombre="<?= htmlspecialchars($row['nombre']); ?>"
                                        data-componentes="<?= (int)$row['cantidad_componentes']; ?>"
                                        data-detalles="<?= htmlspecialchars($row['detalles'] ?? ''); ?>"
                                        data-resorte="<?= htmlspecialchars($row['con_resorte_en']); ?>"
                                        data-wiper="<?= htmlspecialchars($row['es_wiper_en']); ?>"
                                        data-escalon="<?= htmlspecialchars($row['con_escalon_en']); ?>"
                                        data-wiper-especial="<?= htmlspecialchars($row['es_wiper_especial_en']); ?>"
                                        data-grupo-id="<?= (int)($row['grupo_herramienta_id'] ?? 0); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <!-- Gestionar parametros (porcentajes y tolerancias) -->
                                <button class="btn-thunder btn-gestionar-params"
                                        title="Gestionar porcentajes y tolerancias"
                                        data-id="<?= $row['id']; ?>"
                                        data-nombre="<?= htmlspecialchars($row['nombre']); ?>"
                                        data-familia="<?= htmlspecialchars(strtolower($row['familia'])); ?>"
                                        data-componentes="<?= (int)$row['cantidad_componentes']; ?>">
                                    <i class="bi bi-sliders2"></i>
                                </button>

                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['id']); ?></td>
                    <td><strong><?= htmlspecialchars($row['nombre']); ?></strong></td>
                    <td><?= htmlspecialchars($row['familia']); ?></td>
                    <td class="text-center"><?= (int)$row['cantidad_componentes']; ?></td>
                    <td>
                        <?php if (!empty($row['grupo_nombre'])): ?>
                            <span class="badge-grupo"><i class="bi bi-link-45deg"></i> <?= htmlspecialchars($row['grupo_nombre']); ?></span>
                        <?php else: ?>
                            <span class="badge-sin-grupo"><i class="bi bi-exclamation-triangle"></i> Sin grupo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= !empty($row['updated_at']) ? date("d/m/Y H:i", strtotime($row['updated_at'])) : '—'; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include(ROOT_PATH . 'includes/modales_perfil.php'); ?>

</body>
</html>