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

function wherecreate($globalad) { // Returns an array with the courses where to create the block.

    global $DB, $COURSE, $PAGE;
    
    $context = context_system::instance();
    require_capability('moodle/site:config', $context);
            
    $courses = get_courses();

    $catconf = $DB->get_record('tool_globaladtool', array('id' => 1));
    $categoriessaved = explode(",", $catconf->cate);

    foreach ($categoriessaved as $categorias => $value) {
        if($value != ""){

          $catconf->$value = 1;

        }  

    }

    $categorias = $DB->get_records('course_categories',array());

    $createcourses = array();

    foreach ($catconf as $nom => $act) {

        if($act) {

            foreach ($categorias as $cat) {

                $categoria = str_replace(' ', '', $cat->name);
                $idcategorie = $cat->id;
                $table = 'course';
                $select = 'category = ?';
              
                if ($nom == $categoria){

                    $result = $DB->get_records_select_menu($table, $select, array($idcategorie));          

                    foreach ($result as $idcurso => $categ) {

                        $create = true;

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

                                        $create = false;

                                    } // end if.

                                } // end if.
                      
                            } // en foreach.
                      
                        } // en foreach.
                  
                        if ($create) {

                            array_push($createcourses, $idcurso);

                        } // end if.

                    } // en foreach.
            
                } // end if.        

            } // end foreach.

        } // end if.
         
    } // end foreach.

    return $createcourses;
}

function wheredelete($globalad) { // Returns an array with the courses where to clear the block.

    global $DB, $COURSE, $PAGE;

    $context = context_system::instance();
    require_capability('moodle/site:config', $context);

    $courses = get_courses();

    $catconf = $DB->get_record('tool_globaladtool', array('id' => 1));
    $categoriessaved = explode(",", $catconf->cate);
    $categorias = $DB->get_records('course_categories',array());
    $coursesdelete = array();    

    foreach ($categorias as $cat) {

        $categoria = str_replace(' ', '', $cat->name);
        $idcategorie = $cat->id;
        $table = 'course';
        $select = 'category = ?';

        foreach ($categoriessaved as $key => $nom) {
                            
            if ($categoria != $nom) {

                $result = $DB->get_records_select_menu($table, $select, array($idcategorie));                

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
      
    return $coursesdelete;

} // end function

function blocks ($globalad) {

    global $DB, $PAGE;

    wheredelete($globalad);

    $coursescreate = wherecreate($globalad);

    $catconf = $DB->get_record('tool_globaladtool', array('id' => 1));
    $categoriessaved = explode(",", $catconf->cate);

    foreach ($categoriessaved as $categorias => $value) {

        if ($value != "") {

          $catconf->$value = 1;

        }  

    }

    $posicionh = $catconf->posh;
    $posicionv = $catconf->posv;

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


    foreach ($coursescreate as $key => $cursoid) {

        $coursecontext = context_course::instance($cursoid);
        $PAGE->set_context($coursecontext);

        if ($PAGE->blocks->is_known_region($posicionh)) {

            $PAGE->blocks->add_block($globalad, $posicionh, $posicionv, 0, 'course-view-*'); 

        } // end if.
     
    } // en foreach.

    createdash($globalad); // Create or delete in the Dashboard of all users and default Dashboard.
    $message = get_string('changessaved', 'tool_globaladtool');
    $url = new moodle_url('/admin/tool/globaladtool/index.php', array());
    redirect($url, $message);

} // en function.

function createdash($globalad, $pagetype='my-index', $private=PRIV) { // Create or delete in the Dashboard of all users and default Dashboard.

    global $DB, $PAGE;

    $catconf = $DB->get_record('tool_globaladtool', array('id' => 1));

    if ($catconf->dashb == 1) {

        $posicionhd = $catconf->poshd;
        $posicionvd = $catconf->posvd;

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

        $create = true;
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

                            $create = false;

                        } // end if.

                    } // end if.
                      
                } // en foreach.
                      
        } // en foreach.    

        if ($create) {

            $PAGE->blocks->add_block($globalad, $posicionhd, $posicionvd, 0, $pagetype, $systempage->id);
      
            $sqlall = "SELECT id, userid FROM {my_pages} where private = :priv and userid IS NOT NULL";
            $paramall = array('priv' => $private);
            $all = $DB->get_recordset_sql($sqlall, $paramall);

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

            $instanceglobalad = $DB->get_record('block_instances', array('parentcontextid' => $systemcontext->id,'pagetypepattern' => $pagetype,'subpagepattern' => $systempage->id,'blockname' => $globalad));
               
            foreach ($all as $subpagepall) {

                $exist = null;

                foreach ($con as $subpagepcon) {

                    if ($subpagepall->id == $subpagepcon) {

                        $exist = true;                 

                    } // end if.

                } // end foreach.

                if (!$exist) {

                    $usercontext = context_user::instance($subpagepall->userid);
                    $originalid = $instanceglobalad->id;
                    unset($instanceglobalad->id);
                    $instanceglobalad->parentcontextid = $usercontext->id;
                    $instanceglobalad->subpagepattern = $subpagepall->id;
                    $instanceglobalad->timecreated = time();
                    $instanceglobalad->timemodified = $instanceglobalad->timecreated;
                    $instanceglobalad->id = $DB->insert_record('block_instances', $instanceglobalad);
                    $blockcontext = context_block::instance($instanceglobalad->id);  // Just creates the context record
                    $block = block_instance($instanceglobalad->blockname, $instanceglobalad);

                    if (!$block->instance_copy($originalid)) {
                       debugging("Unable to copy block-specific data for original block instance: $originalid to new block instance: $instanceglobalad->id", DEBUG_DEVELOPER);

                    } // end if.

                } // end if.
            
            } // end foreach.

            $all->close();

        } // end if.    

    } else {

        $blocks = $DB->get_records('block_instances', array('blockname' => $globalad, 'pagetypepattern' => $pagetype));

        foreach ($blocks as $block) {

            blocks_delete_instance($block);

        } // end foreach.

    } // end if.

} // end function.