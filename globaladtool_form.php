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
 * @package   block_globaladtool
 * @copyright 2018, angelitr0 <angelluisfraile@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/formslib.php");

class globaladtool_form extends moodleform {
 
    public function definition() {

        global $DB, $CFG;

        $versionM = $CFG->version;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('headerconfig', 'tool_globaladtool'));
        $opcionesh = array (

	       	'izq' => get_string('left', 'tool_globaladtool'),
        	'der' => get_string('right', 'tool_globaladtool')

        );

        $mform->addElement('static', 'description', get_string('descblock_position', 'tool_globaladtool'));
        $mform->addElement('select', 'posh', get_string('block_positionh', 'tool_globaladtool'), $opcionesh);
                
        $opcionesv = array (

	       	'arr' => get_string('up', 'tool_globaladtool'),
        	'aba' => get_string('botton', 'tool_globaladtool')

        );

        $mform->addElement('select', 'posv', get_string('block_positionv', 'tool_globaladtool'), $opcionesv);
        $mform->addElement('html', '<hr>');
        $mform->addElement('static', 'description', get_string('desccategorias', 'tool_globaladtool'));
        
        if ($versionM < 2018120300) {

            $categorias = coursecat::make_categories_list();

        } else {

            $categorias = core_course_category::make_categories_list();
        }

		$numb = 0;
		$categories = $DB->get_records('course_categories', array());        

        foreach ($categorias as $categoria) {

            $categoriaSin = str_replace(' ', '', $categoria);

            $categoriaArrSin = explode("/", $categoriaSin);

            $numb = count($categoriaArrSin);

            if ($numb == 1) {

                foreach ($categories as $categorie) {

                    $catSin = str_replace(' ', '', $categorie->name);

                        if($catSin == $categoriaSin ) {

                            if ($categorie->coursecount > 0) {

                				$mform->addElement('checkbox', $categoriaSin,  $categoria . " (" . $categorie->coursecount . ")");  

                			}

                		}

                }
                  
            } else {

                foreach ($categories as $categorie) {

                    $catSin = str_replace(' ', '', $categorie->name);

                        if($catSin == $categoriaArrSin[$numb - 1] ) {

                            if ($categorie->coursecount > 0) {

                                $mform->addElement('checkbox', $categoriaArrSin[$numb - 1], $categoria . " (" . $categorie->coursecount . ")");  

                            }

                        }

                }                	

            }           
                                
        } 
  	    	
        $this->add_action_buttons();
		
        $mform->addElement('hidden','id','0');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cate');
        $mform->setType('cate', PARAM_TEXT);
    }
}