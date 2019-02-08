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

define('PRIV', 1);

function dondeCrear($globalad) { // Devuelve un array con los cursos donde crear el bloque.

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
                      
                                if (isset($bloque->blockname)) {

                                    if ($bloque->blockname == "block_" . $globalad) {

                                        $crear = false;

                                    } // end if.

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

function dondeBorrar($globalad) { // Devuelve un array con los cursos donde borrar el bloque.

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

                                if ($bloque->blockname == "block_" . $globalad) {

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

function bloques ($globalad) {

    global $DB, $PAGE;

    dondeBorrar($globalad);

    $cursosCrear = dondeCrear($globalad);

    $catConf = $DB->get_record('tool_globaladtool', array('id' => 1));
    $categoriasGuardadas = explode(",", $catConf->cate);

    foreach ($categoriasGuardadas as $categorias => $value) {

        if ($value != "") {

          $catConf->$value = 1;

        }  

    }

    $posicionh = $catConf->posh;
    $posicionv = $catConf->posv;

    if (!$posicionh || $posicionh == 'izq') {
        $posicionh = BLOCK_POS_LEFT;
    } else {
        $posicionh = BLOCK_POS_RIGHT;
    }

    if (!$posicionv || $posicionv == 'arr') {
        $posicionv = -10;
    } else {
        $posicionv = 10;
    }    


    foreach ($cursosCrear as $key => $cursoid) {

        $coursecontext = context_course::instance($cursoid);
        $PAGE->set_context($coursecontext);

        if ($PAGE->blocks->is_known_region($posicionh)) {

            $PAGE->blocks->add_block($globalad, $posicionh, $posicionv, 0, 'course-view-*'); 

        } // end if.
     
    } // en foreach.

    crearDash($globalad); // Crear o borrar en el Dashboard de todos los usuarios y en el default.
    $message = get_string('datosguardados', 'tool_globaladtool');
    $url = new moodle_url('/admin/tool/globaladtool/index.php', array());
    redirect($url, $message);

} // en function.

function crearDash($globalad, $pagetype='my-index', $private=PRIV) { // Crear o borrar en el Dashboard de todos los usuarios y en el default.

    global $DB, $PAGE;

    $catConf = $DB->get_record('tool_globaladtool', array('id' => 1));

    if ($catConf->dashb == 1) {

        $posicionhd = $catConf->poshd;
        $posicionvd = $catConf->posvd;

        if (!$posicionhd || $posicionhd == 'izq') {
            $posicionhd = BLOCK_POS_LEFT;
        } else {
            $posicionhd = BLOCK_POS_RIGHT;
        }

        if (!$posicionvd || $posicionvd == 'arr') {
            $posicionvd = -10;
        } else {
            $posicionvd = 10;
        }

        $crear = true;
        $PAGE = new \moodle_page();
        $PAGE->set_url('/my/index.php', array());
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $PAGE->set_pagelayout('mydashboard');
        $systempage = $DB->get_record('my_pages', array('userid' => null, 'name' => '__default', 'private' => $private));
        $PAGE->set_subpage($systempage->id);
        $block_manager = $PAGE->blocks;
        $block_manager->load_blocks(true);    

        foreach ($block_manager->get_regions() as $region) {
    
            $bloques = $block_manager->get_blocks_for_region($region);
                  
                foreach ($bloques as $bloque) {
                   
                    if (isset($bloque->blockname)) {

                        if ($bloque->blockname == "block_" . $globalad) {

                            $crear = false;

                        } // end if.

                    } // end if.
                      
                } // en foreach.
                      
        } // en foreach.    

        if ($crear) {

            $PAGE->blocks->add_block($globalad, $posicionhd, $posicionvd, 0, $pagetype, $systempage->id);
      
            $sqltodas = "SELECT id, userid FROM {my_pages} where private = :priv and userid IS NOT NULL";
            $paramtodas = array('priv' => $private);
            $todas = $DB->get_recordset_sql($sqltodas, $paramtodas);

            $sqlcon = "SELECT bi.subpagepattern
            FROM {my_pages} p
            JOIN {context} ctx ON ctx.instanceid = p.userid AND ctx.contextlevel = :usercontextlevel
            JOIN {block_instances} bi ON bi.parentcontextid = ctx.id AND
                bi.pagetypepattern = :pagetypepattern AND bi.blockname = :blockname WHERE p.private = :private AND p.userid IS NOT NULL";

            $paramcon = array('private' => $private,
            'usercontextlevel' => CONTEXT_USER,
            'pagetypepattern' => $pagetype,
            'blockname' => $globalad);

            $con = $DB->get_fieldset_sql($sqlcon, $paramcon);

            $instanceGlobalad = $DB->get_record('block_instances', array('parentcontextid' => $systemcontext->id,'pagetypepattern' => $pagetype,'subpagepattern' => $systempage->id,'blockname' => $globalad));
               
            foreach ($todas as $subpageptodas) {

                $existe = null;

                foreach ($con as $subpagepcon) {

                    if ($subpageptodas->id == $subpagepcon) {

                        $existe = true;                 

                    } // end if.

                } // end foreach.

                if (!$existe) {

                    $usercontext = context_user::instance($subpageptodas->userid);
                    $originalid = $instanceGlobalad->id;
                    unset($instanceGlobalad->id);
                    $instanceGlobalad->parentcontextid = $usercontext->id;
                    $instanceGlobalad->subpagepattern = $subpageptodas->id;
                    $instanceGlobalad->timecreated = time();
                    $instanceGlobalad->timemodified = $instanceGlobalad->timecreated;
                    $instanceGlobalad->id = $DB->insert_record('block_instances', $instanceGlobalad);
                    $blockcontext = context_block::instance($instanceGlobalad->id);  // Just creates the context record
                    $block = block_instance($instanceGlobalad->blockname, $instanceGlobalad);

                    if (!$block->instance_copy($originalid)) {
                       debugging("Unable to copy block-specific data for original block instance: $originalid to new block instance: $$instanceGlobalad->id", DEBUG_DEVELOPER);

                    } // end if.

                } // end if.
            
            } // end foreach.

            $todas->close();

        } // end if.    

    } else {

        $blocks = $DB->get_records('block_instances', array('blockname' => $globalad, 'pagetypepattern' => $pagetype));

        foreach ($blocks as $block) {

            blocks_delete_instance($block);

        } // end foreach.

    } // end if.

} // end function.