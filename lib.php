<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * @package   tool_globaladtool
 * @copyright 2018, angelitr0 <angelluisfraile@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/adminlib.php');
//require_once($CFG->libdir . '/blocklib.php');

////////////////////////////////////////////////////


function dondeCrear() {

    global $DB, $COURSE, $PAGE;
    
    $context = context_system::instance();
    require_capability('moodle/site:config', $context);
            
    $courses = get_courses();

    $catConf = $DB->get_record('tool_globaladtool', array('id' => 1));
    $categoriasGuardadas = explode(",", $catConf->cate);

    foreach ($categoriasGuardadas as $categorias => $value) {
        if($value != ""){

          $catConf->$value = 1;

        }  

    }

    $categorias = $DB->get_records('course_categories',array());

    $crearCursos = array();

    foreach ($catConf as $nom => $act) {

        if($act) {

            foreach ($categorias as $cat) {

                $categoria = str_replace(' ', '', $cat->name);
                $idCategoria = $cat->id;
                $table = 'course';
                $select = 'category = ?'; //is put into the where clause
              
                if ($nom == $categoria){

                    $result = $DB->get_records_select_menu($table, $select, array($idCategoria));          

                    foreach ($result as $idcurso => $categ) {

                        $crear = true;

                        $coursecontext = context_course::instance($idcurso);

                        $PAGE = new \moodle_page();
                        $PAGE->set_url('/course/view.php', array('id' => $idcurso));
                        $PAGE->set_context($coursecontext);
                        $PAGE->set_pagelayout('course');
                        $course = get_course($idcurso);
                        $PAGE->set_course($course);
                        $block_manager = $PAGE->blocks;
                        $block_manager->load_blocks(true);

                        foreach ($block_manager->get_regions() as $region) {

                            $bloques = $block_manager->get_blocks_for_region($region);
                  
                            foreach ($bloques as $bloque) {
                      
                                if ($bloque->title == "globalad") {

                                    $crear = false;

                                } // end if.
                      
                            } // en foreach.
                      
                        } // en foreach.
                  
                        if ($crear) {

                            array_push($crearCursos, $idcurso);

                        } // end if.

                    } // en foreach.
            
                } // end if.        

            } // end foreach.

        } // end if.
         
    } // end foreach.

    return $crearCursos;
}

//////////////////////////////////


function dondeBorrar() {

    global $DB, $COURSE, $PAGE;

    $context = context_system::instance();
    require_capability('moodle/site:config', $context);

    $courses = get_courses();

    $catConf = $DB->get_record('tool_globaladtool', array('id' => 1));
    $categoriasGuardadas = explode(",", $catConf->cate);
    $categorias = $DB->get_records('course_categories',array());
    $borrarCursos = array();    

    foreach ($categorias as $cat) {

        $categoria = str_replace(' ', '', $cat->name);
        $idCategoria = $cat->id;
        $table = 'course';
        $select = 'category = ?'; //is put into the where clause

        foreach ($categoriasGuardadas as $key => $nom) {
                            
            if ($categoria != $nom) {

                $result = $DB->get_records_select_menu($table, $select, array($idCategoria));                

                foreach ($result as $idcurso => $categ) {

                    $coursecontext = context_course::instance($idcurso);
                    $PAGE = new \moodle_page();
                    $PAGE->set_url('/course/view.php', array('id' => $idcurso));
                    $PAGE->set_context($coursecontext);
                    $PAGE->set_pagelayout('course');
                    $course = get_course($idcurso);
                    $PAGE->set_course($course);
                    $block_manager = $PAGE->blocks;
                    $block_manager->load_blocks(true);

                    foreach ($block_manager->get_regions() as $region) {

                        $bloques = $block_manager->get_blocks_for_region($region);
                                         
                        foreach ($bloques as $bloque) {

                            if (isset($bloque->blockname)) {

                                if ($bloque->blockname == "block_globalad") {

                                    $idinstanciabloque = $bloque->instance->id;

                                    $instance = $DB->get_record('block_instances', array('id' => $idinstanciabloque));

                                    blocks_delete_instance($instance);

                                } // end if.

                            } //end if.
                
                        } // end foreach.

                    }  // end foreach.

                } // end foreach.

            } // end if.
              
        } // end foreach.             

    } // end foreach.         
      
    return $borrarCursos;

} // end function

////////////////////////////////////////////

function bloques () {

    global $DB, $PAGE;

    $contextActual = context_system::instance();


/////////////////////////////////////////////  Borra cursos  

    dondeBorrar();

///////////////////////////////////////////    Crea cursos

    $cursosCrear = dondeCrear();

    $catConf = $DB->get_record('tool_globaladtool', array('id' => 1));
    $categoriasGuardadas = explode(",", $catConf->cate);

    foreach ($categoriasGuardadas as $categorias => $value) {

        if ($value != "") {

          $catConf->$value = 1;

        }  

    }

    $posicionh = $catConf->posh;
    $posicionv = $catConf->posv;
    $posh = null;
    $posv = null;

    if (!$posicionh || $posicionh == 'izq') {
        $posh = BLOCK_POS_LEFT;
    } else {
        $posh = BLOCK_POS_RIGHT;
    }

    if (!$posicionv || $posicionv == 'arr') {
        $posv = -10;
    } else {
        $posv = 10;
    }    



    foreach ($cursosCrear as $key => $cursoid) {

        $coursecontext = context_course::instance($cursoid);
        $PAGE->set_context($coursecontext);

        if ($PAGE->blocks->is_known_region($posh)) {

            $PAGE->blocks->add_block('globalad', $posh, $posv, 0, 'course-view-*'); 

        }
     
    } // en foreach.

//////////////////// Vuelve al contexto

    $url = new moodle_url('/admin/tool/globaladtool/index.php', array());
    redirect($url);

} // en function.