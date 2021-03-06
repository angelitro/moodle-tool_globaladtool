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
 
defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/formslib.php");

class globaladtool_form extends moodleform {
 
    public function definition() { // Form configuration tool.

        global $DB, $CFG;

        $versionm = $CFG->version;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('headerconfig', 'tool_globaladtool'));
        $mform->addElement('html', '<div class="globaladtool mform fitem fitemtitle">');
        $mform->addElement('static', 'description', get_string('desc_dashboard', 'tool_globaladtool'));
        $mform->addElement('html', '<span class="globaladtool lighter">');
        $mform->addElement('checkbox', 'dashb',  get_string('dashb', 'tool_globaladtool'));
        $mform->addElement('html', '</span>');

        $mform->addElement('static', 'description', get_string('descblock_dashboard', 'tool_globaladtool'));

        $opcionesh = array (
            'izq' => get_string('left', 'tool_globaladtool'),
            'der' => get_string('right', 'tool_globaladtool')
        );

        $opcionesv = array (
            'arr' => get_string('up', 'tool_globaladtool'),
            'aba' => get_string('botton', 'tool_globaladtool')
        );

        $mform->disabledIf('poshd', 'dashb');
        $mform->disabledIf('posvd', 'dashb');

        $mform->addElement('html', '<div class="globaladtool lighter">');
        $mform->addElement('select', 'poshd', get_string('block_positionh', 'tool_globaladtool'), $opcionesh);
        $mform->addElement('select', 'posvd', get_string('block_positionv', 'tool_globaladtool'), $opcionesv);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<hr>');      
        
        $mform->addElement('static', 'description', get_string('descblock_position', 'tool_globaladtool'));

        $mform->addElement('html', '<div class="globaladtool lighter">');
        $mform->addElement('select', 'posh', get_string('block_positionh', 'tool_globaladtool'), $opcionesh);              
        $mform->addElement('select', 'posv', get_string('block_positionv', 'tool_globaladtool'), $opcionesv);
        $mform->addElement('html', '</div>');
        $mform->addElement('static', 'description', get_string('desccategorias', 'tool_globaladtool'));
        
        if ($versionm < 2018120300) {

            $categorias = coursecat::make_categories_list();

        } else {

            $categorias = core_course_category::make_categories_list();
        }

		$numb = 0;
		$categories = $DB->get_records('course_categories', array());

        $mform->addElement('html', '<div class="globaladtool lighter">');        

        foreach ($categorias as $categoria) {

            $categoriasin = str_replace(' ', '', $categoria);

            $categoriaarrsin = explode("/", $categoriasin);

            $numb = count($categoriaarrsin);

            if ($numb == 1) {

                foreach ($categories as $categorie) {

                    $catsin = str_replace(' ', '', $categorie->name);

                    if($catsin == $categoriasin ) {

                        if ($categorie->coursecount > 0) {

                	        $mform->addElement('checkbox', $categoriasin,  $categoria . " (" . $categorie->coursecount . ")");  

                        }

                    }

                }
                  
            } else {

                foreach ($categories as $categorie) {

                    $catsin = str_replace(' ', '', $categorie->name);

                    if($catsin == $categoriaarrsin[$numb - 1] ) {

                        if ($categorie->coursecount > 0) {

                            $mform->addElement('checkbox', $categoriaarrsin[$numb - 1], $categoria . " (" . $categorie->coursecount . ")");  
                        }

                    }

                }                	

            }           
                                
        } 
  	    $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');	
        $this->add_action_buttons();
		
        $mform->addElement('hidden','id','0');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cate');
        $mform->setType('cate', PARAM_TEXT);
        
        $mform->addElement('hidden', 'sesskey', null);
        $mform->setType('sesskey', PARAM_RAW);
        $mform->setDefault('sesskey', sesskey());
    }
}