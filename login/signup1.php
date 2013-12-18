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
 * user signup page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once $CFG->dirroot.'/cohort/lib.php';
require_once($CFG->dirroot.'/login/signup_form1.php');

// Try to prevent searching for sites that allow sign-up.
if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}
$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';

if (empty($CFG->registerauth)) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}
$authplugin = get_auth_plugin($CFG->registerauth);

if (!$authplugin->can_signup()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_url('/login/signup1.php');
$PAGE->set_context(context_system::instance());

$mform_signup = new login_signup_form(null, null, 'post', '', array('autocomplete'=>'on'));

if ($mform_signup->is_cancelled()) {
    redirect(get_login_url());

} else if ($user = $mform_signup->get_data()) {
    if ($user->manualfio != '') {
	    $fio = $user->manualfio;
    } else { 
	    $fio = $DB->get_record('stud_students', array('id'=>$user->fio))->name;
    }
    list($user->lastname, $user->firstname) = explode(' ', $fio, 2);
    $user->confirmed   = 0;
    $user->lang        = current_language();
    $user->firstaccess = time();
    $user->timecreated = time();
    $user->mnethostid  = $CFG->mnet_localhost_id;
    $user->secret      = random_string(15);
    $user->auth        = $CFG->registerauth;

    $authplugin->user_signup($user, false);

    $enteredGroupName = $DB->get_record('stud_groups', array('id'=>$user->group))->name;
    $groupName = "2013.$enteredGroupName";

    $user = $DB->get_record('user', array('username'=>$user->username, 'mnethostid'=>$CFG->mnet_localhost_id));

    if(!$DB->record_exists('cohort', array('idnumber'=>$groupName))) {

        $newCohort = new stdClass();
        $newCohort->name = $groupName;
        $newCohort->idnumber = $groupName;
        $newCohort->contextid = $PAGE->context->id;

        $cohortid = cohort_add_cohort($newCohort);
    } else {
        $cohortid = $DB->get_record('cohort', array('idnumber'=>$groupName))->id;
    }

    cohort_add_member($cohortid, $user->id);

    $emailconfirm = get_string('emailconfirm');
    $PAGE->navbar->add($emailconfirm);
    $PAGE->set_title($emailconfirm);
    $PAGE->set_heading($PAGE->course->fullname);
    echo $OUTPUT->header();
    notice(get_string('emailconfirmsent', '', $user->email), "$CFG->wwwroot/index.php");
    exit; //never reached
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();


$newaccount = get_string('newaccount');
$login      = get_string('login');

$PAGE->navbar->add($login);
$PAGE->navbar->add($newaccount);

$PAGE->set_title($newaccount);
$PAGE->set_heading($SITE->fullname);
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/login/ajax_signup1.js') );

echo $OUTPUT->header();
echo $OUTPUT->heading('[ <a href="/login/signup.php">Для первокурсников</a> / <strong>Для студентов 3, 4, 5 и 6 курсов</strong>]',3);
$mform_signup->display();
echo $OUTPUT->footer();
