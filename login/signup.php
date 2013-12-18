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

$PAGE->set_url('/login/signup.php');
$PAGE->set_context(context_system::instance());

$mform_signup = $authplugin->signup_form();

if ($mform_signup->is_cancelled()) {
    redirect(get_login_url());

} else if ($user = $mform_signup->get_data()) {
    if ($user->manualfio != '') {
	    $fio = $user->manualfio;
    } else { 
	    $fio = $DB->get_record('abit_students', array('id'=>$user->fio))->name;
    }
    list($user->lastname, $user->firstname) = explode(' ', $fio, 2);
    $user->confirmed   = 1;
    $user->lang        = current_language();
    $user->firstaccess = time();
    $user->timecreated = time();
    $user->mnethostid  = $CFG->mnet_localhost_id;
    $user->secret      = random_string(15);
    $user->auth        = $CFG->registerauth;

    if (empty($user->email)) {
	$user->email = "email@not.exist";
    }

    $cohortid = $DB->get_record('cohort', array('name'=>$user->cohstream))->id;

    $authplugin->user_signup($user, false);
    $user = $DB->get_record('user', array('username'=>$user->username, 'mnethostid'=>$CFG->mnet_localhost_id));

    cohort_add_member($cohortid, $user->id);

    authenticate_user_login($user->username, $user->password);
    complete_user_login($user);
    set_moodle_cookie($user->username);
    redirect("$CFG->wwwroot/index.php");
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
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/login/ajax_signup.js') );

echo $OUTPUT->header();
echo $OUTPUT->heading('[ <strong>Для первокурсников</strong> / <a href="/login/signup1.php">Для студентов 3, 4, 5 и 6 курсов</a>]',3);
$mform_signup->display();
echo $OUTPUT->footer();
