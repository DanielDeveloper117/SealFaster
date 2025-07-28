<?php
    include(ROOT_PATH . 'includes/backend_info_user.php');
?>
<!-- USUARIO ADMINISTRADOR -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= controlCache('../assets/css/menu.css'); ?>">
<script src="<?= controlCache('../assets/js/menu.js'); ?>"></script>

<div id="topbar">
  <div class="logo-area">
    <img src="../assets/img/general/sealfaster.png" alt="Logo SealFaster" />
  </div>

  <button id="burgerBtn" class="bi bi-list"></button>

  <nav id="navbar">
    <ul class="nav-links">
      <li><a href="../modules/welcome.php">Inicio</a></li>
      <li><a href="../modules/filtros_inventario_cnc.php">Inventario CNC</a></li>
      <li><a href="../modules/selectTipoSello.php">Cotizador</a></li>
      <li><a href="../modules/cotizaciones.php">Cotizaciones</a></li>
      <li><a class="" href="../modules/produccion_vn.php">Producción VN</a></li>
      <li><a class="" href="../modules/produccion_cnc.php">Producción CNC</a></li>
      <li><a class="" href="../modules/parametros_cotizador.php" >Parametros cotizador</a></li>
      <li><a class="" href="../modules/precios.php" >Precios</a></li>
      <!-- <li><a class="" href="../modules/ingresar.php">Parametros sellos</a></li>
      <li><a class="" href="../modules/desencriptar.php" >Desencriptar</a></li> -->
      <li><a class="" href="../modules/users.php" >Usuarios</a></li>
      <!-- <li><a class="" href="../modules/validarcodigo.php" >Validar código</a></li>
      <li><a class="" href="../modules/busqueda-folio.php" >Busqueda</a></li> -->
    </ul>

    <div class="user-menu">
      <i class="bi bi-person-circle" id="userIcon"></i>
      <div id="userDropdown">
        <span><?= htmlspecialchars($nombreUser); ?></span>
        <a href="../modules/configuracion.php">Configuración</a>
        <a href="../auth/cerrar_sesion.php" class="logout">Cerrar sesión</a>
      </div>
    </div>
  </nav>
</div>

