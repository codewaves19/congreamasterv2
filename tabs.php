<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 2005 Martin Dougiamas  http://dougiamas.com             //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

// This file to be included so we can assume config.php has already been included.
// We also assume that $user, $course, $currenttab have been set


//    if (empty($currenttab)) {
//        print_error('cannotcallscript');
//    }

$context = context_module::instance($cm->id);
$row = array();
$row[] = new tabobject('upcomingsession', new moodle_url('/mod/congrea/view.php', array('id' => $cm->id, 'upcomingsession' => $congrea->id)), get_string('upcomingsession', 'mod_congrea'));
$row[] = new tabobject('psession', new moodle_url('/mod/congrea/view.php', array('id' => $cm->id, 'psession' => $congrea->id)), get_string('psession', 'mod_congrea'));
if (has_capability('mod/congrea:sessionesetting', $context)) {
    $row[] = new tabobject('sessionsettings', new moodle_url('/mod/congrea/sessionsettings.php', array('id' => $cm->id, 'sessionsettings' => $congrea->id)), get_string('sessionsettings', 'mod_congrea'));
}
// Print out the tabs and continue!
echo $OUTPUT->tabtree($row, $currenttab);
