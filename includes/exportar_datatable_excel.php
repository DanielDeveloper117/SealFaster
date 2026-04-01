<!-- DataTables Buttons -->
<link href="<?= controlCache('../assets/dependencies/buttons.dataTables.min.css'); ?>" rel="stylesheet">

<script src="<?= controlCache('../assets/dependencies/dataTables.buttons.min.js'); ?>"></script>

<!-- JSZip para Excel -->
<script src="<?= controlCache('../assets/dependencies/jszip.min.js'); ?>"></script>

<!-- Botones HTML5 -->
<script src="<?= controlCache('../assets/dependencies/buttons.html5.min.js'); ?>"></script>
<script>
    $(document).ready(function() {
        // Necesario para exportar excel
        $('.dt-length, .dt-search').wrapAll('<div class="d-flex flex-row justify-content-between"></div>');
        $('.dt-info, .dt-paging').wrapAll('<div class="d-flex flex-row justify-content-between"></div>');
        
        $('#btnExportarDatos').on('click', function() {
            $(".buttons-excel").trigger("click");
        });
    });   
</script>
<style>
    .buttons-excel{
        display: none !important;
    }    
</style>