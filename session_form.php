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
 * Form to edit uploaded file name
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_congrea
 * @copyright  2015 Pinky Sharma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//echo 'ravi'; exit;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/congrea/locallib.php');

/**
 * File update name form
 *
 * @copyright  2014 Pinky Sharma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_congrea_session_form extends moodleform {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $sessionsettings = $this->_customdata['sessionsettings'];
        $edit = $this->_customdata['edit'];
        $mform->addElement('hidden', 'sessionsettings', $sessionsettings);
        $mform->setType('sessionsettings', PARAM_INT);
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'edit', $edit);
        $mform->setType('edit', PARAM_INT);           
        $mform->addElement('header', 'sessionsheader', get_string('sessionsettings', 'mod_congrea'));
        
        $mform->addElement('date_time_selector', 'fromsessiondate', get_string('fromsessiondate', 'congrea'));
        $mform->addHelpButton('fromsessiondate', 'fromsessiondate', 'congrea');
        $mform->addElement('date_time_selector', 'tosessiondate', get_string('tosessiondate', 'congrea'));
        $mform->addHelpButton('tosessiondate', 'tosessiondate', 'congrea');
        // Select teacher.
        $teacheroptions = congrea_course_teacher_list();
        $mform->addElement('select', 'moderatorid', get_string('selectteacher', 'congrea'), $teacheroptions);
        $mform->addHelpButton('moderatorid', 'selectteacher', 'congrea');
        // Repeat.
        $mform->addElement('header', 'headeraddmultiplesessions', get_string('addmultiplesessions', 'congrea'));
        //$mform->closeHeaderBefore('headeraddmultiplesessions');
        $mform->addElement('checkbox', 'addmultiply', '', get_string('repeatsessions', 'congrea'));
        $mform->addHelpButton('addmultiply', 'repeatsessions', 'congrea');
        $period = array(1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
            21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36);
        
        $periodgroup = array();
        $periodgroup[] = $mform->createElement('select', 'period', '', $period, false, true);
        $periodgroup[] = $mform->createElement('static', 'perioddesc', '', get_string('week', 'congrea'));
        $mform->addGroup($periodgroup, 'periodgroup', get_string('repeatevery', 'congrea'), array(' '), false);
        $mform->disabledIf('periodgroup', 'addmultiply', 'notchecked'); 
        //$mform->disabledIf('periodgroup', 'repeatsessions', 'notchecked');
        $days = array();
        $days[] = $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Mon', '', get_string('monday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Tue', '', get_string('tuesday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Wed', '', get_string('wednesday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Thu', '', get_string('thursday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Fri', '', get_string('friday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Sat', '', get_string('saturday', 'calendar'));
        //echo '<pre>'; print_r($days); exit;
        $mform->addGroup($days, 'days', get_string('repeaton', 'congrea'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), true);
        $mform->disabledIf('days', 'addmultiply', 'notchecked');   
        $this->add_action_buttons();
    }

    /**
     * Validate this form.
     *
     * @param array $data submitted data
     * @param array $files not used
     * @return array errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Check open and close times are consistent.
        if ($data['fromsessiondate'] != 0 && $data['tosessiondate'] != 0 &&
                $data['tosessiondate'] < $data['fromsessiondate']) {
            $errors['tosessiondate'] = get_string('closebeforeopen', 'congrea');
        }
        if ($data['fromsessiondate'] != 0 && $data['tosessiondate'] == 0) {
            $errors['tosessiondate'] = get_string('closenotset', 'congrea');
        }
        if ($data['fromsessiondate'] != 0 && $data['tosessiondate'] != 0 &&
                $data['tosessiondate'] == $data['fromsessiondate']) {
            $errors['tosessiondate'] = get_string('closesameopen', 'congrea');
        }
        if(!empty($data['period']) && $data['period'] > 0 && empty($data['days'])) {
            $errors['days'] =  get_string('selectdays', 'congrea');
        }
        return $errors;
    }

}

