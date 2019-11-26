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
 * Prints a particular instance of congrea
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_congrea
 * @copyright  2018 Ravi Kumar
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/session_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID.
$n = optional_param('n', 0, PARAM_INT); // Congrea instance ID - it should be named as the first character of the module.
$sessionsettings = optional_param('sessionsettings', 0, PARAM_INT);
$edit = optional_param('edit', 0, PARAM_INT);
$action = optional_param('action', ' ', PARAM_CLEANHTML);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
//echo $action; exit;
if ($id) {
    $cm = get_coursemodule_from_id('congrea', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $congrea = $DB->get_record('congrea', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $congrea = $DB->get_record('congrea', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $congrea->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('congrea', $congrea->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$returnurl = new moodle_url('/mod/congrea/view.php', array('id' => $cm->id));
$settingsreturnurl = new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'action' => 'addsession'));
// Print the page header.
$PAGE->set_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'action' => 'addsession'));
$PAGE->set_title(format_string($congrea->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if ($delete) {
    require_login($course, false, $cm);
    //$modcontext = context_module::instance($cm->id);
    $submiturl = new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'upcomingsession' => $sessionsettings));
    $returnurl = new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'upcomingsession' => $sessionsettings));
    if ($confirm != $delete) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($congrea->name));
        $optionsyes = array('delete' => $delete, 'confirm' => $delete, 'sesskey' => sesskey());
        echo $OUTPUT->confirm(
            get_string('deleteschedule', 'mod_congrea'),
            new moodle_url($submiturl, $optionsyes),
            $returnurl
        );
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {
        $DB->delete_records('event', array('repeatid' => $delete, 'modulename' => 'congrea'));
        //$DB->delete_records('event', array('modulename' => 'congrea', 'eventtype' => $delete));
    }
}
    $mform = new mod_congrea_session_form(null, array('id' => $id, 'action' => $action, 'congreaid' => $congrea->id, 'edit' => $edit));
    if ($mform->is_cancelled()) {
        // Do nothing.
        redirect(new moodle_url('/mod/congrea/view.php', array('id' => $id)));
    } else if ($fromform = $mform->get_data()) {
        //echo '<pre>'; print_r($fromform); exit;
        $event = new stdClass();
        $event->name = $congrea->name;
        //echo $fromform->allowconflicts; exit;
        if (!empty($fromform->addmultiply)) { // Repeat sessions.
            if($fromform->radiogroup['repeattill'] == 1) { // Repeattill.
                $until = $fromform->radiogroup['repeatdatetill'];
            } else {
                $until = $fromform->radiogroup['occurrences'];
            }
            if (!empty($fromform->days)) {
                 $prefix = $daylist = '';
                 foreach ($fromform->days as $keys => $daysname) {
                     $daylist .= $prefix . '"' . $keys . '"';
                     $prefix = ', ';
                 }
                 $daysnames = str_replace('"', '', $daylist);
                 $description = $until.' weekly : '.$daysnames;
                 $event->description = $until.' weekly : '.$daysnames;
            }
        } else { // Single Event.
            $until = false;
            $event->description = 'Single session';
            $daysnames = false; 
        }
        $event->courseid = $COURSE->id;
        $event->timestart = $fromform->fromsessiondate; // Change because of sessionsettings.
        $event->groupid = 0;
        $teacherid = $fromform->moderatorid;
        $event->userid = $teacherid;
        $event->modulename = 'congrea';
        $event->instance = $congrea->id;
        $event->eventtype = 'session start';// TODO:
        $timeduration = round($fromform->timeinminutes*60);
        $event->timeduration = $timeduration;
        $endtime = ($fromform->fromsessiondate + $timeduration);
        $conflictstatus = check_conflicts($congrea->id, $fromform->fromsessiondate, $endtime, $until, $daysnames, $timeduration, $edit);
        if(!empty($conflictstatus) and empty($fromform->allowconflicts)) {
            //$conflictmsg = '';
            foreach($conflictstatus as $conflictsdate) {
                //echo '<pre>'; print_r($conflictsdate); exit;
                //$conflictmsg .= userdate($conflictsdate);
                \core\notification::warning(userdate($conflictsdate));                
                //\core\notification::warning($conflictsdate);
            }
            //redirect($settingsreturnurl);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($congrea->name));
            $mform->set_data($event);
            if (has_capability('mod/congrea:sessionesetting', $context)) {
                $mform->display();
            }
            echo $OUTPUT->footer();
            return false;
        }
        if(!$edit) {
            if(empty($fromform->radiogroup['occurrences'])) {
                $eventobject = calendar_event::create($event);
                $eventid = $eventobject->id; // TODO: -using api return id.
            }
        if (!empty($fromform->days)) {
            if(empty($fromform->radiogroup['occurrences'])) {
                $dataobject = new stdClass();
                $dataobject->repeatid = $eventid;
                $dataobject->id = $eventid;
                $DB->update_record('event', $dataobject);
            }
            $datelist = reapeat_date_list(date('Y-m-d H:i:s', $fromform->fromsessiondate), $until, $daysnames);
            if(!empty($fromform->radiogroup['occurrences'])) {
                $event->timestart = strtotime($datelist[0]);
                //echo $event->timestart; exit;
                $eventobject = calendar_event::create($event);
                $eventid = $eventobject->id; // TODO: -using api return id.
                $dataobject = new stdClass();
                $dataobject->repeatid = $eventid;
                $dataobject->id = $eventid;
                $DB->update_record('event', $dataobject); 
                array_shift($datelist);
            }
            foreach ($datelist as $startdate) {
                repeat_calendar($congrea, $description, $eventid, $startdate, $congrea->id, $timeduration, $teacherid);
            } 
    }
    } else { // Handle edit cases.
        if($fromform->sessionstatus == 'changesessiononly') {
            if($fromform->fromsessiondate < time()) {
                echo 'Past events cannot be changed'; // TODO: instring file.
            } else {
                $DB->delete_records('event', array('modulename' => 'congrea', 'id' => $edit));
                calendar_event::create($event);
            }

        } else if($fromform->sessionstatus == 'changeallsession') {
            if($fromform->fromsessiondate < time()) {
                echo 'Past events cannot be changed';
            } else {
                $repeatid = $DB->get_field('event', 'repeatid', array('modulename' => 'congrea', 'id' => $edit));
                if($repeatid) {
                    $whereclause1 = "modulename = ? AND repeatid = ? AND timestart > ?";
                    $DB->delete_records_select('event', $whereclause1, array('congrea', $repeatid, time()));
                } else {
                    $whereclause = "modulename = ? AND id = ? AND timestart > ?";
                    $DB->delete_records_select('event', $whereclause, array('congrea', $edit, time()));
                }
                if(empty($fromform->radiogroup['occurrences'])) {
                    $eventobject = calendar_event::create($event);
                    $eventid = $eventobject->id;
                    $dataobject = new stdClass();
                    $dataobject->repeatid = $eventid;
                    $dataobject->id = $eventid;
                    $DB->update_record('event', $dataobject);
                }
                if (!empty($fromform->days)) {
                    $datelist = reapeat_date_list(date('Y-m-d H:i:s', $fromform->fromsessiondate), $until, $daysnames);
                    if(!empty($fromform->radiogroup['occurrences'])) {
                        $event->timestart = strtotime($datelist[0]);
                        $eventobject = calendar_event::create($event);
                        $eventid = $eventobject->id; // TODO: -using api return id.
                        $dataobject = new stdClass();
                        $dataobject->repeatid = $eventid;
                        $dataobject->id = $eventid;
                        $DB->update_record('event', $dataobject); 
                        array_shift($datelist);
                    }                    
                    foreach ($datelist as $startdate) {
                        repeat_calendar($congrea, $description, $eventid, $startdate, $congrea->id, $timeduration, $teacherid);
                    } 
                }
            }
              // TODO:
        } else if($fromform->sessionstatus == 'changeforthissessionfollowing') {
            if($fromform->fromsessiondate < time()) {
                echo 'Past events cannot be changed';
            } else {
                //$deletesql = "delete * from {event} where modulename = congrea and id = $edit and timestart >= $fromform->fromsessiondate";
                $repeatid = $DB->get_field('event', 'repeatid', array('modulename' => 'congrea', 'id' => $edit));
                if($repeatid) {
                    $whereclause1 = "modulename = ? AND repeatid = ? AND timestart > ?";
                    $DB->delete_records_select('event', $whereclause1, array('congrea', $repeatid, $fromform->fromsessiondate));
                } else {
                    $whereclause = "modulename = ? AND id = ? AND timestart > ?";
                    $DB->delete_records_select('event', $whereclause, array('congrea', $edit, $fromform->fromsessiondate));
                    //$DB->delete_records('event', array('modulename' => 'congrea', 'id' => $edit));
                }
                if(empty($fromform->radiogroup['occurrences'])) {
                    $eventobject = calendar_event::create($event);
                    $eventid = $eventobject->id;
                    $dataobject = new stdClass();
                    $dataobject->repeatid = $eventid;
                    $dataobject->id = $eventid;
                    $DB->update_record('event', $dataobject);
                }
                if (!empty($fromform->days)) {
                    $datelist = reapeat_date_list(date('Y-m-d H:i:s', $fromform->fromsessiondate), $until, $daysnames);
                    if(!empty($fromform->radiogroup['occurrences'])) {
                        $event->timestart = strtotime($datelist[0]);
                        $eventobject = calendar_event::create($event);
                        $eventid = $eventobject->id; // TODO: -using api return id.
                        $dataobject = new stdClass();
                        $dataobject->repeatid = $eventid;
                        $dataobject->id = $eventid;
                        $DB->update_record('event', $dataobject); 
                        array_shift($datelist);
                    }      
                    foreach ($datelist as $startdate) {
                        repeat_calendar($congrea, $description, $eventid, $startdate, $congrea->id, $timeduration, $teacherid);
                    } 
                }
            }
        }
        
    }
    //if(empty($conflictstatus)) {
        redirect($returnurl);
    //}
    //redirect($returnurl);
}
// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading($congrea->name);
echo $OUTPUT->heading('Scheduled Sessions');
if ($edit) {
    $list = $DB->get_records('event', array('id' => $edit));
    $days = array();
    foreach ($list as $formdata) {
        $data = new stdClass;
        $data->fromsessiondate = $formdata->timestart;
        $data->timeinminutes = ($formdata->timeduration/60);
        $data->moderatorid = $formdata->userid;
        if($formdata->description != 'Single session') {
            $data->addmultiply = 1;
        } else {
            $data->addmultiply = 0;
        }
        //if($formdata->description !== 'Single session') {
            $daysname = explode(':', $formdata->description);
            $nameofdays = explode(',', $daysname[1]);
            $till = (int) filter_var($formdata->description , FILTER_SANITIZE_NUMBER_INT);
            if($till > 1000) {
                $data->radiogroup['repeattill'] = 1;
                $data->radiogroup['repeatdatetill'] = $till;
            } else {
                $data->radiogroup['repeattill'] = 1;
                $data->radiogroup['occurrences'] = $till;
            }
        //}
        //echo '<pre>'; print_r($nameofdays); exit;
        foreach ($nameofdays as $d) {
                $key = trim($d);
                $days[$key] = 1;
        }
        $data->days = $days;
    }
    $mform->set_data($data);
}

if ($edit || $action == 'addsession') {
    if (has_capability('mod/congrea:sessionesetting', $context)) {
        $mform->display();
    }
}
// Finish the page.
if(!$edit) {
    $PAGE->requires->js_call_amd('mod_congrea/congrea', 'setSelectedDate');
}
//$PAGE->requires->js_call_amd('mod_congrea/congrea', 'setSelectedDate');
$PAGE->requires->js_call_amd('mod_congrea/congrea', 'disableRepeatTill');
echo $OUTPUT->footer();

