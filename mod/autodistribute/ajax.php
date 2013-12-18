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
 * Provide interface for topics AJAX course formats
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package course
 */

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/group/lib.php');


$id        = required_param('id', PARAM_INT);               // Course Module ID
$action    = optional_param('action', 'none', PARAM_ALPHA);
$edit      = optional_param('edit', -1, PARAM_BOOL);        // Edit mode

if ($id != NULL) {
    $cm = get_coursemodule_from_id('autodistribute', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/autodistribute:distribute', $context);

// Initialise ALL the incoming parameters here, up front.
$action     = required_param('action', PARAM_ALPHA);

$PAGE->set_url('/mod/autodistribute/ajax.php', array('action'=>$action));

$output = array();

switch($action) {
	case 'movestudent':
		$student_id    	= required_param('student', PARAM_INT);
		$from_group_id  = required_param('from', PARAM_INT);
		$to_group_id    = required_param('to', PARAM_INT);
		groups_remove_member($from_group_id, $student_id);
		groups_add_member($to_group_id, $student_id);
		$output = 'ok';
	break;
}


echo $OUTPUT->header(); // send headers

echo json_encode(array('output' => $output));

