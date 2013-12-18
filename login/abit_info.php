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
require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot.'/course/lib.php');

// Initialise ALL the incoming parameters here, up front.
$action     = required_param('action', PARAM_ALPHA);

$PAGE->set_url('/login/abit_info.php', array('action'=>$action));

$output = array();

switch($action) {
	case 'listfilials':
		$output = $DB->get_records('abit_filials', null, 'name ASC');
	break;

	case 'listdirections':
		$filid      = required_param('filial', PARAM_INT);
		$output = $DB->get_records('abit_dirs', array('fil_id' => $filid), 'name ASC');
	break;

	case 'listspecialities':
		$dirid      = required_param('direction', PARAM_INT);
		$output     = $DB->get_records('abit_specs', array('dir_id' => $dirid), 'name ASC');
	break;

	case 'listlearnforms':
		$specid     = required_param('speciality', PARAM_INT);
		$output     = $DB->get_records('abit_lforms', array('spec_id' => $specid), 'name ASC');
	break;

	case 'listfio':
		$lformid    = required_param('learnform', PARAM_INT);
		$output     = $DB->get_records('abit_students', array('lform_id' => $lformid), 'name ASC');
	break;

	case 'listfilialss':
		$output = $DB->get_records('stud_filials', null, 'name ASC');
	break;

	case 'listgroupss':
		$filid      = required_param('filial', PARAM_INT);
		$output     = $DB->get_records('stud_groups', array('filial_id' => $filid), 'name ASC');
	break;

	case 'listfios':
		$lformid    = required_param('group', PARAM_INT);
		$output     = $DB->get_records('stud_students', array('group_id' => $lformid), 'name ASC');
	break;

	case 'checkusername':
		$username = required_param('username', PARAM_ALPHA);
		$output = $DB->record_exists('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id));
	break;
}


echo $OUTPUT->header(); // send headers

echo json_encode(array('output' => $output));

