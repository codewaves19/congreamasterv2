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
 * @copyright  2019 Manisha Dayal

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
$returnurl = new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'sessionsettings' => true));
$settingsreturnurl = new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'action' => 'addsession')); // added MD:
// Print the page header.
$PAGE->set_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'sessionsettings' => $sessionsettings));
$PAGE->set_title(format_string($congrea->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if ($delete) {
    require_login($course, false, $cm);
    //$modcontext = context_module::instance($cm->id);
    $submiturl = new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'sessionsettings' => $sessionsettings));
    $returnurl = new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'sessionsettings' => $sessionsettings));
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
        $DB->delete_records('congrea_sessions', array('id' => $delete)); // MD:
        $DB->delete_records('event', array('modulename' => 'congrea', 'eventtype' => $delete));
        //$DB->delete_records('event', array('id' => $delete, 'repeatid' => $delete, 'modulename' => 'congrea'));
    }
}

$mform = new mod_congrea_session_form(null, array('id' => $id, 'sessionsettings' => $sessionsettings, 'edit' => $edit, 'action' => $action, 'congreaid' => $congrea->id));
//$fromform = $mform->get_data();
if ($mform->is_cancelled()) {
    // Do nothing.
    redirect(new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'sessionsettings' => true)));
} else if ($fromform = $mform->get_data()) {
    $data = new stdClass();
    $data->starttime = $fromform->fromsessiondate;
    
    $durationinminutes = round($fromform->timeduration / 60);
    $expecteddate = strtotime(date('Y-m-d H:i:s', strtotime("+$durationinminutes minutes", $data->starttime)));
    $data->endtime = $expecteddate;
    $timeduration = round((abs($data->endtime - $data->starttime) / 60));
    $data->timeduration = $timeduration;
    if (!empty($fromform->addmultiply)) {
        $data->isrepeat = $fromform->addmultiply; // MD: $data->multiple = $fromform->addmultiply;
        $data->repeattype = $fromform->period; // MD: $data->repeatid = $fromform->period;
        /* if (!empty($fromform->days)) {
            $prefix = $daylist = '';
            foreach ($fromform->days as $keys => $daysname) {
                $daylist .= $prefix . '"' . $keys . '"';
                $prefix = ', ';
            } */
        /*             if (!empty($fromform->days)) {
                $daysnames = implode(", ", array_keys($fromform->days));
                $event->description = $until . ' weekly: ' . $daysnames;
            } */
        //$data->additional = str_replace('"', '', $daylist); // MD:

        //} else {
        // $startdate = date('Y-m-d', strtotime($fromform->fromsessiondate));
        //$additional = date('D', $startdate);
        //$d2 = date('D', 1579933800);
        $startdate = date('Y-m-d', $data->starttime);
        $additional = date('D', strtotime($startdate));
        $data->additional = $additional; // MD:
        //}
    } else { // Single Event.
        /* $until = false;
        $event->description = 'Single session';
        $daysnames = false; */
        $data->isrepeat = 0;
        $data->repeattype = 0;
        $data->additional = '-';
    }
    $data->teacherid = $fromform->moderatorid; // MD:
    $data->congreaid = $congrea->id;
    if ($action == 'addsession') {
        $sessionid = $DB->insert_record('congrea_sessions', $data); // Insert record in congrea table.
        if(empty($fromform->period)) { // No repeat. MD:
            mod_congrea_update_calendar($congrea, $fromform->fromsessiondate, $expecteddate, $timeduration, $sessionid);
        }
        //echo $OUTPUT->notification(get_string('newsessionadded', 'mod_congrea'));
    }
    if ($edit) { // Handle edit condition of schedule.
        //$conflictstatus = check_conflicts($congrea->id, $data->starttime, $data->endtime,  $data->repeattype, $data->additional, $timeduration, $edit); // MD:
       // if(!$conflictstatus) {
            $sessionid = $DB->insert_record('congrea_sessions', $data); // MD:
            if($sessionid) {
                if(empty($fromform->period)) { // No repeat.
                    mod_congrea_update_calendar($congrea, $fromform->fromsessiondate, $expecteddate, $timeduration,  $sessionid);
                }
                $DB->delete_records('congrea_sessions', array('id' => $edit)); // MD:
                $DB->delete_records('event', array('modulename' => 'congrea', 'eventtype' => $edit));
            }
        //} else {
            //echo 'conflicts in dates';
        //}
    }
    if (!empty($fromform->addmultiply)) {
        if ($fromform->period > 0) { // Here need to calculate repeate dates. MD:
            $params = array('modulename' => 'congrea', 'instance' => $congrea->id, 'eventtype' => $sessionid);
            $eventid = $DB->get_field('event', 'id', $params);
            $expecteddate = date(
                'Y-m-d H:i:s',
                strtotime(date('Y-m-d H:i:s', $fromform->fromsessiondate) . "+$fromform->period weeks")
            );
            $datelist = reapeat_date_list(date('Y-m-d H:i:s', $fromform->fromsessiondate), $expecteddate, $additional); // MD:
            $fromdate = date('Y-m-d H:i:s', $fromform->fromsessiondate);
            array_unshift($datelist, $fromdate); // // From start to repeat.
            foreach ($datelist as $startdate) {
                repeat_calendar($congrea, $eventid, $startdate, $sessionid, $timeduration);
            }
        }
    }
    redirect($returnurl);
} // Else if end
// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading($congrea->name);
if (!empty($sessionsettings)) {
    $currenttab = 'sessionsettings';
}
congrea_print_tabs($currenttab, $context, $cm, $congrea);
if (has_capability('mod/congrea:sessionesetting', $context)) {
    $options = array();
    echo $OUTPUT->single_button(
        $returnurl->out(
            true,
            array('action' => 'addsession', 'cmid' => $cm->id)
        ),
        get_string('addsessions', 'congrea'),
        'get',
        $options
    );
}
echo $OUTPUT->heading('Schedules');
$table = new html_table();
$table->head = array('Date and time of first session', 'Session duration', 'Teacher', 'Repeat for', 'Repeat days', 'Action');
$sessionlist = $DB->get_records('congrea_sessions', array('congreaid' => $congrea->id)); // MD:
usort($sessionlist, "compare_dates_scheduled_list");
$currenttime = time();
if (!empty($sessionlist)) {
    foreach ($sessionlist as $list) {
        $buttons = array();
        $row = array();       
        $row[] = userdate($list->starttime);
        if (($list->endtime < $currenttime) && empty($list->repeattype)){ //past sessions
            continue;
        }
        if (!empty($list->repeattype)) { // Repeated past sessions
            $days = 7 * ($list->repeattype - 1);
            $date = date('Y-m-d H:i:s', $list->endtime);
            $past = date('Y-m-d  H:i:s', strtotime($date . '+' . $days . ' days'));
            if (strtotime($past) < $currenttime) {
                continue;
            }
        }
        $row[] = $list->timeduration . ' ' . 'Minutes';
        $teachername = $DB->get_record('user', array('id' => $list->teacherid));
        if (!empty($teachername)) {
            $username = $teachername->firstname . ' ' . $teachername->lastname; // Todo-for function.
        } else {
            $username = get_string('nouser', 'mod_congrea');
        }
        $row[] = $username;
        if (!empty($list->repeattype)) {
            $row[] = $list->repeattype . ' ' . 'Week';
        } else {
            $row[] = '-';
        }
        $row[] = str_replace('"', '', $list->additional);
        $buttons[] = html_writer::link(
            new moodle_url(
                '/mod/congrea/sessionsettings.php',
                array('id' => $cm->id, 'edit' => $list->id, 'sessionsettings' => $sessionsettings)
            ),
            'Edit',
            array('class' => 'actionlink exportpage')

        );
        $buttons[] = html_writer::link(
            new moodle_url(
                '/mod/congrea/sessionsettings.php',
                array('id' => $cm->id, 'delete' => $list->id, 'sessionsettings' => $sessionsettings)
            ),
            'Delete',
            array('class' => 'actionlink exportpage')
        );
        $row[] = implode(' ', $buttons);
        //if ($list->endtime < $currenttime) { //past sessions
            //$row->attributes['id'] = 'pastsessions';  
            //$table->data[] = $row;   
        //}else{
            $table->data[] = $row;   
        //}    
    }
    if (!empty($table->data)) {
        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo html_writer::table($table);
        echo html_writer::start_tag('br');
        echo html_writer::end_tag('div');
    } else {
        echo 'no session';
    }
} else {
   /*  if ($action == "addsession"){
        echo $OUTPUT->notification(get_string('newsessionadded', 'mod_congrea'));
    } else if ($edit) {
        echo $OUTPUT->notification(get_string('sessionedited', 'mod_congrea')); 
    } else if ($delete) {
        echo $OUTPUT->notification(get_string('sessiondeleted', 'mod_congrea'));
    } else { */
        echo $OUTPUT->notification(get_string('nosession', 'mod_congrea'));  // add session notification
    //}
}
if ($edit) {
    $list = $DB->get_records('congrea_sessions', array('id' => $edit)); // congrea sessions table MD:
    usort($list->starttime, "compare_dates_scheduled_list");
    $days = array();
    foreach ($list as $formdata) {
        $data = new stdClass;
        $data->fromsessiondate = $formdata->starttime;
        $data->timeduration = ($formdata->timeduration * 60);
        $data->period = $formdata->repeattype; // MD:
        $data->moderatorid = $formdata->teacherid;
        $data->addmultiply = $formdata->isrepeat; // MD: 
        $dayname = (explode(", ", $formdata->additional));
        foreach ($dayname as $d) {
            $key = trim($d, '"');
            $days[$key] = 1;
        }
        $data->days = $days;
        $data->moderatorid = $formdata->teacherid;
    }
    $mform->set_data($data);
}

if ($edit || $action == 'addsession') {
    if (has_capability('mod/congrea:sessionesetting', $context)) {
        if (!$edit){
            echo html_writer::start_tag('br');
            echo $OUTPUT->heading('Add a new schedule');
        } else{
            echo html_writer::start_tag('br');
            echo $OUTPUT->heading('Edit an existing schedule');
        }
        $mform->display();
    }
}
// Finish the page.
echo $OUTPUT->footer();