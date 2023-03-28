<?php 
    require_once "../modelos/Querymodelos.php";
    $reporte2 = new Consultas();

    $fecha_inicio=$_GET["fecha_inicio"];
    $fecha_fin=$_GET["fecha_fin"];
    $inicio=strtotime($fecha_inicio);
    $fin=strtotime($fecha_fin);
    
    $curso = $_GET["curso"]; 
    if($curso=='seleccionacurso'){
        $curso="";
      }
    
    $vp = $_GET["vp"]; 
    if($vp =='seleccionarvp'){
      $vp="";
    }
      
      
    $rspta=$reporte2->reportegeneralTwo($inicio, $fin, $curso, $vp);

    $valoresY= array(); 
    $valoresX= array(); 
    $cursosName= array(); 
    $cursoN = array(); 
    $nameVp = array(); 
    
    foreach ($rspta as $key => $valor) {
        $valoresY[] = $valor->avancetotal;
        $valoresX[] = $valor->porfinusuarios; 
        $cursosName[] = $valor->id; 
        $nameVp[] = $valor->rolicasa; 
        $cursoN[] = $valor->curso;
    }

    $datosX=json_encode($valoresX);
    $datosY=json_encode($valoresY);
    $cursos=json_encode($cursosName);
    $vp=json_encode($nameVp); 
    $cursoNa=json_encode($cursoN); 

    
?>

<div id="myPlot"></div>
<script type="text/javascript">
function crearCadenaLineal(json) {
    var parsed = JSON.parse(json);
    var arr = [];
    for (var x in parsed) {
        arr.push(parsed[x]);
    }
    return arr;
}
</script>

<script>
datosX = crearCadenaLineal(' <?php echo $datosX ?>');
datosY = crearCadenaLineal(' <?php echo $datosY ?>');
cursos = crearCadenaLineal(' <?php echo $cursos ?>');
vp = crearCadenaLineal(' <?php echo $vp ?>');
cursoN = crearCadenaLineal(' <?php echo $cursoNa ?>');

var trace = {
    type: 'bar',
    x: [cursos, vp],
    y: datosY,
    name: 'Porcentaje de usuarios finalizados'
};

var trace2 = {
    type: 'bar',
    x: [cursos, vp],
    y: datosX,
    name: 'Porcentaje de usuarios no finalizados'
};

var data = [trace, trace2];

var layout = {
    title: "Avance de curso",
    yaxis: {
        title: 'Porcentaje'
    },
    xaxis: {
        title: 'Nombre de curso'
    },
};

var config = {
    responsive: true
};

Plotly.newPlot("myPlot", data, layout, config, {
    displayModeBar: false
});
</script>