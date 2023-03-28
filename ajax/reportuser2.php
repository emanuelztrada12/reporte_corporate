<?php

require_once "../modelos/Querymodelos.php";
//llamado  a la base de datos
$reporte1 = new Consultas();
//Variables a utilizar
$fecha_inicio=$_REQUEST["fecha_inicio"];
$fecha_fin=$_REQUEST["fecha_fin"];
//Trasformacion de fecha
$inicio=strtotime($fecha_inicio);
$fin=strtotime($fecha_fin);
$curso=$_REQUEST["curso"];
$vp=$_REQUEST["vp"]; 

if($curso =='seleccionacurso'){
  $curso="";
}

if($vp =='seleccionarvp'){
  $vp="";
}

$rspta=$reporte1->reportUserTwo($inicio, $fin, $curso, $vp );
        $data= Array(); 
          foreach ($rspta as $key => $valor) {
            // TODO: cambiar enlace por el sitio original
            $link = "<a href='http://localhost:8080/grade/report/grader/index.php?id=$valor->courseid' target='_blank'>Visualizar alumnos</a>";
              $data[]=array(
                "0"=>$valor->id,
                "1"=>$valor->vp,
                "2"=>$valor->emp,
                "3"=>$valor->curso, 
                "4"=>$valor->firstname, 
                "5"=>$valor->lastname, 
                "6"=>$valor->department,
                "7"=>$valor->puest,
                "8"=>$valor->gen,
                "9"=>$valor->dpi,
                "10"=>$valor->status,
                );
              }

        $results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);
        echo json_encode($results);

  exit;
  