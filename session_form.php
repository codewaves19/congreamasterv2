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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/congrea/locallib.php');

/**
 * File update name form
 *
 * @copyright  2019 Ravi Kumar
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
        $action = $this->_customdata['action'];
        $congreaid = $this->_customdata['congreaid'];
        $edit = $this->_customdata['edit'];
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'edit', $edit);
        $mform->setType('edit', PARAM_INT);
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_CLEANHTML);
        $mform->addElement('header', 'sessionsheader', get_string('sessionsettings', 'mod_congrea'));
        $mform->setType('congreaid', PARAM_INT);
        $mform->addElement('hidden', 'congreaid', $congreaid);
        $mform->addElement('date_time_selector', 'fromsessiondate', get_string('fromsessiondate', 'congrea'));
        $mform->addHelpButton('fromsessiondate', 'fromsessiondate', 'congrea');
        $mform->addElement('text', 'timeinminutes', get_string('timeinminutes', 'congrea'), array('size' => 5));
        $mform->setType('timeinminutes', PARAM_INT);
        // Select teacher.
        $teacheroptions = congrea_course_teacher_list();
        $mform->addElement('select', 'moderatorid', get_string('selectteacher', 'congrea'), $teacheroptions);
        $mform->addHelpButton('moderatorid', 'selectteacher', 'congrea');
        // Repeat.
        $mform->addElement('header', 'headeraddmultiplesessions', get_string('addmultiplesessions', 'congrea'));
        $mform->addElement('checkbox', 'addmultiply', '', get_string('repeatsessions', 'congrea'));
        $mform->addHelpButton('addmultiply', 'repeatsessions', 'congrea');
        $group = array();
        $group[] =& $mform->createElement('radio', 'repeattill', '', get_string('repeattill', 'congrea'), 1);
        $group[] =& $mform->createElement('date_selector', 'repeatdatetill', '');
        $group[] =& $mform->createElement('radio', 'repeattill', '', get_string('occurrences', 'congrea'), 2);
        $group[] =& $mform->createElement('text', 'occurrences', get_string('occurrences', 'congrea'));
        $mform->addGroup($group, 'radiogroup', '', '<br />', true);// TODO : repeatgroup
        $mform->setDefault('radiogroup[repeattill]', 1);
        $mform->setType('radiogroup[occurrences]', PARAM_RAW);
        $mform->setType('occurrences', PARAM_INT);
        $mform->disabledIf('radiogroup', 'addmultiply', 'notchecked');
        //$mform->disabledIf('sessionstatus', 'addmultiply', 'notchecked');
        $days = array();
        $days[] = $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Mon', '', get_string('monday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Tue', '', get_string('tuesday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Wed', '', get_string('wednesday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Thu', '', get_string('thursday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Fri', '', get_string('friday', 'calendar'));
        $days[] = $mform->createElement('checkbox', 'Sat', '', get_string('saturday', 'calendar'));
        $mform->addGroup($days, 'days', get_string('repeaton', 'congrea'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), true);
        $mform->disabledIf('days', 'addmultiply', 'notchecked');
        if($edit) {
            $radio = array();
            $radio[] = $mform->createElement('radio', 'sessionstatus', null, get_string('changesessiononly', 'congrea'), 'changesessiononly');
            $radio[] = $mform->createElement('radio', 'sessionstatus', null, get_string('changeallsession', 'congrea'), 'changeallsession');
            $radio[] = $mform->createElement('radio', 'sessionstatus', null, get_string('changeforthissession', 'congrea'), 'changeforthissessionfollowing');
            $mform->addGroup($radio, 'sessionstatus', ' ', array('<br>'), false);
            //$mform->disabledIf('sessionstatus', 'addmultiply', 'notchecked');
        }
        $mform->addElement('advcheckbox', 'allowconflicts', get_string('allowconflicts', 'mod_congrea'), ' ', array('group' => 1), array(0, 1));
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
        global $DB;
        $errors = parent::validation($data, $files);
        if ((!empty($data['radiogroup']['repeatdatetill']) || !empty($data['radiogroup']['occurrences'])) && empty($data['days']) && !empty($data['addmultiply'])) {
            $errors['days'] = get_string('selectdays', 'congrea');
        }
        if (!empty($data['edit']) and empty($data['sessionstatus'])) {
            $errors['sessionstatus'] = get_string('editcase', 'congrea');
        }
        $currentdate = time();
        $previousday = strtotime(date('Y-m-d H:i:s', strtotime("-24 hours", $currentdate)));
        if ($data['fromsessiondate'] < $previousday) {
            $errors['fromsessiondate'] = get_string('esessiondate', 'congrea');
        }
        if ($data['timeinminutes'] == 0) {
            $errors['timeinminutes'] = get_string('errortimeduration', 'congrea');
        }
        $durationinminutes = $data['timeinminutes'];
        if ($durationinminutes < 10) {
            $errors['timeinminutes'] = get_string('errordurationlimit', 'congrea');
        }
        if ($durationinminutes > 1439) { // Minutes of 24 hours.
            $errors['timeinminutes'] = get_string('errortimeduration', 'congrea');
        }
        if(empty($data['moderatorid'])) {
            $errors['moderatorid'] = get_string('enrolteacher', 'congrea');
        }
        $starttime = $data['fromsessiondate'];
        $endtime = ($data['fromsessiondate'] + ($data['timeinminutes']*60));
        if (!empty($data['days'])) {
            $prefix = $daylist = '';
            foreach ($data['days'] as $keys => $daysname) {
                $daylist .= $prefix . '"' . $keys . '"';
                $prefix = ', ';
            }
            $additional = str_replace('"', '', $daylist);
        } else {
            $additional = 0;
        }
        if(!empty($data['period'])) {
            $countweeks = $data['period'];
        } else {
            $countweeks = 0;
        }
        //echo $additional; exit;
        if(!empty($data['radiogroup']['repeatdatetill'])) {
            $expecteddate = $data['radiogroup']['repeatdatetill'];

        } else {
            $expecteddate = $data['radiogroup']['occurrences']; // TODO;
        }
        //     $DB->delete_records('event', array('modulename' => 'congrea', 'eventtype' => $data['edit']));
        //     $DB->delete_records('event', array('modulename' => 'congrea', 'eventtype' => $data['edit']));
        // }
        //$test = array(1, 2, 3);
        //$conflictstatus = check_conflicts($data['congreaid'], $data['fromsessiondate'], $endtime, $expecteddate, $additional, $durationinminutes, $data['edit']);
        // if($conflictstatus) {
        //     $errors['fromsessiondate'] = get_string('conflictsdate', 'congrea');
        // }
        return $errors;
    }

}
