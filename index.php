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

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('globaladtool_form.php');
require('lib.php');


global $DB, $OUTPUT, $PAGE;

$id = optional_param('id', 0, PARAM_INT);

admin_externalpage_setup('tool_globaladtool');

// Page settings.
$title = get_string('pluginname', 'tool_globaladtool');

$context = context_system::instance();
$PAGE->set_url(new \moodle_url('/admin/tool/globaladtool/index.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname. ': ' . $title);

$settingsg = new globaladtool_form();

if ($settingsg->is_cancelled()) {

    $courseurl = new moodle_url('/admin/category.php', array('category' => 'appearance'));
    redirect($courseurl);

} else if ($fromform = $settingsg->get_data() and $settingsg = data_submitted() and confirm_sesskey()) {

    $data = $DB->get_record('tool_globaladtool', array('id' => 1));

    $fromform->cate = "";

    if (!$data) {

        foreach ($fromform as $categorias => $categ) {

            if ($categorias!='dashb' && $categorias!='posvd' && $categorias!='poshd' && $categorias!='posh' && $categorias!='posv' && $categorias!='id' && $categorias!='cate' && $categorias!='submitbutton' && $categorias!='') {

                $fromform->cate .= ",".$categorias;

            }
        }


        if (!$DB->insert_record('tool_globaladtool', $fromform)) {

            print_error('inserterror', 'tool_globaladtool');

        } else {

            blocks('globalad');

        }

                
    } else {

        $data->cate = "";
        $categoriessaved = explode(",", $data->cate);

        foreach ($categoriessaved as $categorias => $value) {

            if ($value != "") {

                $fromform->$value = 1;

            }

        }

      
        foreach ($fromform as $categorias => $value) {

            if ($categorias!='dashb' && $categorias!='posvd' && $categorias!='poshd' && $categorias!='posh' && $categorias!='posv' && $categorias!='id' && $categorias!='cate' && $categorias!='submitbutton' && $categorias!='') {

                $fromform->cate .= $categorias.",";
            }
            if (!isset($fromform->dashb)) {
                $fromform->dashb = 0;
            }

        }


        if (!$DB->update_record('tool_globaladtool', $fromform)) {

            print_error('updateerror', 'tool_globaladtool');

        } else {

            blocks('globalad');
            
        }
        
    } // end if.
     
    $site = get_site();    
    echo $OUTPUT->header();
    $data = $DB->get_record('tool_globaladtool', array('id' => 1));

    $categoriessaved = explode("," , $data->cate);

    foreach ($categoriessaved as $categorias => $value) {

        if ($value != "") {

          $fromform->$value = 1;

        }
        
    }

    $settingsg->set_data($fromform);
    $settingsg->display();
    echo $OUTPUT->footer();     

} else {

    $site = get_site();
    echo $OUTPUT->header();
    
    if ($data = $DB->get_record('tool_globaladtool', array('id' => 1))) {

        $categoriessaved = explode("," , $data->cate);

        foreach ($categoriessaved as $categorias => $value) {

            if ($value != "") {

                $data->$value = 1;

            }

        }
    
        $settingsg->set_data($data);

    }

    $settingsg->display();
    echo $OUTPUT->footer();

} // end if.