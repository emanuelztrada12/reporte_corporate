<?php
require_once("{$CFG->libdir}/formslib.php");
require_once('../modelos/Querymodelos.php');
class administrador extends moodleform
{
    
    function definition()
    {
        global $USER, $DB;
        $reporte1 = new Consultas(); 
        
        $alum = $reporte1->getRol($USER->id);
        $rol1 = $alum["icasa-individual"]->rol;
        $rol2 = $alum["icasa-completo"]->rol; 
        $nombre  = $USER->id;        
        $admin   = is_siteadmin();   
        
        $cursosinfo = $reporte1->cursos();
        $cursoinfoTwo = $reporte1->cursoTwo(); 
        $vpinfo = $reporte1->vp(); 
        $vpinfotwo = $reporte1->vpTwo(); 
        $arraycurso['seleccionacurso'] = 'Selecciona tu curso';
        $arrayvp['seleccionarvp'] = 'Selecciona vicepresidencia'; 

        if($rol1 == 'icasa-individual'){ 
            foreach ($cursosinfo as $key => $value) {
                $arraycurso[$key] = $value->shortname;
            }

            foreach ($vpinfotwo as $key => $value) {
                $arrayvp[$key] = $value->data;
            }

        }else if($admin = 1 || $rol2 == 'icasa-completo'){
            foreach ($cursosinfo as $key => $value) {
                $arraycurso[$key] = $value->shortname;
            }

            foreach ($vpinfo as $key => $value) {
                $arrayvp[$key] = $value->data;
            }

        }

        $mform =& $this->_form;
        $mform->addElement('header', 'displayinfo', get_string('textfields', 'block_report'));
        $mform->addElement('date_selector', 'fecha_inicio', get_string('from', 'block_report'));
        $mform->addElement('date_selector', 'fecha_fin', get_string('to',  'block_report'));
        $mform->addElement('select', 'curso', get_string('curso', 'block_report'), $arraycurso);
        $mform->addElement('select', 'vp', get_string('vp', 'block_report'), $arrayvp);
        
    }
    
}

?>
