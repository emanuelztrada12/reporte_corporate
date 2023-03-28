<?php require_once('../../../config.php');
require_once('../forms/administrador.php');
require_once('../modelos/Querymodelos.php');

 global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$blockid = required_param('blockid', PARAM_INT);
// $blockid = required_param('blockid', PARAM_INT);

// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_report', $courseid);
}

require_login($course);

$PAGE->set_url('/blocks/report/vistas/tableroadministrador.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_context(\context_system::instance());
//$PAGE->set_heading(get_string('edithtml', 'block_report'));
$settingsnode = $PAGE->settingsnav->add(get_string('reportsetting', 'block_report'));
$editurl = new moodle_url('/blocks/report/vistas/tableroadministrador.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('editpage', 'block_report'), $editurl);
$editnode->make_active();
$actualizateform = new administrador();
$getRola = new Consultas();
$alum = $getRola->getRol($USER->id);
$rol1 = $alum["icasa-individual"]->rol;
$rol2 = $alum["icasa-completo"]->rol; 
$nombre = $USER->id;
$admin = is_siteadmin();
?>

<?php
echo $OUTPUT->header();
$actualizateform->display();
?>


<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="../DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="../DataTables/Buttons-1.5.6/css/buttons.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="../DataTables/datatables.min.css" />
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

<style media="screen">
hr {
    width: 100%;
    background: #e5e5e5;
    height: 1px;
}

.col-sm-6 {
    border-left: #e5e5e5 1px solid;
    border-right: #e5e5e5 1px solid;
    padding: 10px;
}

label {
    color: #13284B;
}
</style>
<tbody>
    <div class="panel-body">
        <!--  Formulario -->
        <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" align="center">
            <button id="mostrar" class="fitemtitle fitem fitem_fbutton femptylabel clicklistar"
                align="center">Mostrar</button>
        </div>
        <br>
        <hr>
        <!--  Información #13284B-->
        <table id="tbllistado" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <th class="select-filter">ID</th>
                <th class="select-filter">vicepresidencia</th>
                <th class="select-filter">Empresa</th>
                <th class="select-filter">Curso</th>
                <th class="select-filter">Nombres</th>
                <th class="select-filter">Apellidos</th>
                <th class="select-filter">Departamento</th>
                <th class="select-filter">Puesto</th>
                <th class="select-filter">Género</th>
                <th class="select-filter">DPI</th>
                <th class="select-filter">Status</th>
        </table>
</tbody>
<div id="graph"></div>

<!--  scripts  -->
<script src="../DataTables/jQuery-3.3.1/jquery-3.3.1.min.js"></script>
<script src="../DataTables/dataTables.min.js"></script>
<script src="../DataTables/Buttons-1.5.6/js/dataTables.buttons.min.js"></script>
<script src="../DataTables/Buttons-1.5.6/js/buttons.flash.min.js"></script>
<script src="../DataTables/Buttons-1.5.6/js/buttons.html5.min.js"></script>
<script src="../DataTables/Buttons-1.5.6/js/buttons.print.min.js"></script>
<script src="../DataTables/Buttons-1.5.6/js/jszip.min.js"></script>
<script src="../DataTables/Buttons-1.5.6/js/pdfmake.min.js"></script>
<script src="../DataTables/Buttons-1.5.6/js/vfs_fonts.js"></script>
<script src="../js/Chart.min.js"></script>
<script src="../js/Chart.bundle.min.js"></script>
<script src="../js/chartjs-plugin-datalabels.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../js/tableroadmin.js"></script>
<script src="../js/jspdf.min.js"></script>
<script src="../librerias/plotly-2.16.1.min.js"></script>
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

<script type="text/javascript">
rol1 = '<?php echo $rol1;?>';
rol3 = '<?php echo $rol2;?>';
rol2 = '<?php echo $admin;  ?>';

if (rol1 == 'icasa-individual') {
    url1 = '../ajax/reportuser2.php';
} else if (rol2 = 1 || rol3 == 'icasa-completo') {
    url1 = '../ajax/reportuser.php';
}

$(document).ready(function() {
    selected = $("#id_fecha_inicio_month").val();
    d = new Date();
    n = (d.getMonth() + 1) - 3;
    childs = $("#id_fecha_inicio_month").children();
    for (i = 0; i < childs.length; i++) {
        if ($(childs[i]).val() == n) {
            $(childs[i]).attr("selected", true);
            $(childs[i]).prop("selected", true);
        }
    }
});


$(document).ready(function() {
    selected = $("#id_fecha_fin_day").val();
    d = new Date();
    n = (d.getDate() + 1);
    childs = $("#id_fecha_fin_day").children();
    for (i = 0; i < childs.length; i++) {
        if ($(childs[i]).val() == n) {
            $(childs[i]).attr("selected", true);
            $(childs[i]).prop("selected", true);
        }
    }
});



$.noConflict();
jQuery(document).ready(function($) {

    jQuery('#mostrar').click(function() {
        //Fecha de inicio
        var startnew = $("#id_fecha_inicio_day").val();
        var startmonth = $("#id_fecha_inicio_month").val();
        var startyear = $("#id_fecha_inicio_year").val();

        //Fecha de final
        var finishnew = $("#id_fecha_fin_day").val();
        var finishmonth = $("#id_fecha_fin_month").val();
        var finishyear = $("#id_fecha_fin_year").val();

        //Union de fecha
        var fecha_inicio = startyear + '-' + startmonth + '-' + startnew;
        var fecha_fin = finishyear + '-' + finishmonth + '-' + finishnew;

        //variables de formulario
        var curso = $("#id_curso").val();
        var vp = $("#id_vp").val();

        let parametros = {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            curso: curso,
            vp: vp
        };

        //Datatable
        var tabla = $("#tbllistado").DataTable({
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                "infoEmpty": "Mostrando del 0 al 0 de 0 Entradas",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"

                }
            },
            "aProcessing": true, //Activamos el procesamiento del datatables
            "aServerSide": true, //Paginación y filtrado realizados por el servidor
            "scrollX": true,

            dom: 'Bfrtip', //Definimos los elementos del control de tabla
            buttons: [{
                extend: 'excel',
                text: 'Descargar Excel',
                filename: 'ReporteUsuarios',
                title: 'Reporte de usuarios'
            }],
            "ajax": {
                url: url1,
                data: parametros,
                type: "get",
                dataType: "json",
                error: function(e) {
                    console.log(e.responseText);
                }
            },
            "bDestroy": true,
            "iDisplayLength": 10, //Paginación
            "order": [
                [0, "asc"]
            ] //Ordenar (columna,orden)
        });

    })

    jQuery('.clicklistar').click();

});
</script>

<?php
echo $OUTPUT->footer();
?>