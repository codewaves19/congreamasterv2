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
 * Settings used by the congrea module
 *
 * @package mod_congrea
 * @copyright  2014 Pinky Sharma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 * */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('mod_congrea/heading', get_string('congreaconfiguration', 'congrea'),
                                            get_string('congreaconfigurationd', 'congrea'), ''));
    // Api key and Secret key settings.
    $settings->add(new admin_setting_configtext('mod_congrea/cgapi', get_string('cgapi', 'congrea'),
                                           get_string('cgapid', 'congrea'), ''));
    $settings->add(new admin_setting_configpasswordunmask('mod_congrea/cgsecretpassword', get_string('cgsecret', 'congrea'),
                                                        get_string('cgsecretd', 'congrea'), ''));
    // Colourpicker Settings.
    $choices = array('#021317' => 'Black Pearl', '#003056' => 'Prussian Blue', '#424f9b' => 'Chambray',
            '#001e67' => 'Midnight Blue', '#692173' => 'Honey Flower', '#511030' => 'Heath', '#0066b0' => 'Endeavour');
    $settings->add(new admin_setting_configselect('mod_congrea/preset', get_string('preset', 'congrea'),
                                                get_string('presetd', 'congrea'), '#34404c', $choices));
    $PAGE->requires->js_call_amd('mod_congrea/congrea', 'presetColor');
    $previewconfig = null;
    $settings->add(new admin_setting_configcolourpicker('mod_congrea/colorpicker',
            get_string('colorpicker', 'congrea'), get_string('colorpickerd', 'congrea'), '#021317', $previewconfig));
    // Override Section.
    $settings->add(new admin_setting_heading('mod_congrea/override_section', get_string('overrideheading', 'congrea'), ''));
    // Congrea allowoverride default on.
    $settings->add(new admin_setting_heading('mod_congrea/heading', get_string('congreaconfiguration', 'congrea'),
                                            get_string('congreaconfigurationd', 'congrea'), ''));
    $settings->add(new admin_setting_configcheckbox('mod_congrea/allowoverride', get_string('cgallowoverride', 'mod_congrea'),
                                                      get_string('cgallowoverride_help', 'mod_congrea'), 1));
    // Student management.
    $settings->add(new admin_setting_heading('mod_congrea/student_management', get_string('studentm', 'congrea'), ''));
    // Congrea disable attendee audio default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/studentaudio',
    get_string('studentaudio', 'mod_congrea'), get_string('studentaudio_help', 'mod_congrea'), 1));
    // Congrea disable attendee video default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/studentvideo',
    get_string('studentvideo', 'mod_congrea'), get_string('studentvideo_help', 'mod_congrea'), 1));
    // Congrea disable attendee pc default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/studentpc', get_string('studentpc', 'mod_congrea'),
                                                      get_string('studentpc_help', 'mod_congrea'), 1));
    // Congrea disable attendee gc default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/studentgc', get_string('studentgc', 'mod_congrea'),
                                                      get_string('studentgc_help', 'mod_congrea'), 1));
    // Congrea disable raise hand default on.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/raisehand', get_string('raisehand', 'mod_congrea'),
                                                      get_string('raisehand_help', 'mod_congrea'), 1));
    // Congrea disable user list default on.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/userlist', get_string('userlist', 'mod_congrea'),
                                                      get_string('userlist_help', 'mod_congrea'), 1));
    // Recordings Section.
    $settings->add(new admin_setting_heading('mod_congrea/recording_header', get_string('recordingsection', 'congrea'), ''));
    // Congrea recording default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/enablerecording', get_string('enablerecording', 'congrea'),
                                                        get_string('enablerecording_help', 'congrea'), 0));
    // Session recording for teacher.
    $settings->add(new admin_setting_heading('mod_congrea/trecording_header',
    get_string('trecordingsection', 'congrea'), ''));
    // Congrea recAllowpresentorAVcontrol default on.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/recAllowpresentorAVcontrol',
    get_string('recAllowpresentorAVcontrol', 'congrea'), get_string('recAllowpresentorAVcontrol_help', 'congrea'), 1));
    // Congrea recShowPresentorRecordingStatus default on.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/recShowPresentorRecordingStatus',
    get_string('recShowPresentorRecordingStatus', 'congrea'), get_string('recShowPresentorRecordingStatus_help', 'congrea'), 1));
    // Student recording session.
    $settings->add(new admin_setting_heading('mod_congrea/srecording_header', get_string('srecordingsection', 'congrea'), ''));
    // Congrea attendeerecording default on.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/attendeerecording',
    get_string('attendeerecording', 'congrea'), get_string('attendeerecording_help', 'congrea'), 1));
    // Congrea recattendeeav default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/recattendeeav',
    get_string('recattendeeav', 'congrea'), get_string('recattendeeav_help', 'congrea'), 1));
    // Congrea recAllowattendeeAVcontrol default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/recAllowattendeeAVcontrol',
    get_string('recAllowattendeeAVcontrol', 'congrea'), get_string('recAllowattendeeAVcontrol_help', 'congrea'), 0));
    // Congrea recAllowattendeeAVcontrol default off.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/showAttendeeRecordingStatus',
    get_string('showAttendeeRecordingStatus', 'congrea'), get_string('showAttendeeRecordingStatus_help', 'congrea'), 0));
    $settings->add(new admin_setting_heading('mod_congrea/recordingcontrol_header', get_string('recordingcontrol', 'congrea'), ''));
    // Congrea trimRecordings default on.
    $settings->add(new admin_setting_configcheckbox('mod_congrea/trimRecordings', get_string('trimRecordings', 'congrea'),
                                                        get_string('trimRecordings_help', 'congrea'), 1));
}