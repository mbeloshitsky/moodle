<?php

require_once('../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

$context = context_system::instance();

function GetPassword($passwordLength=8)
{
    return substr(base64_encode(crypt(microtime())),rand(1,45-$passwordLength),$passwordLength);
}

function cohort_get_members($cohortid) {
    global $DB;

    $params = array('cohortid'=>$cohortid, 'cohortid1'=>$cohortid);
    $fields = 'SELECT u.id, u.auth, username, email, firstname, lastname ';

    $sql = " FROM {user} u
             INNER JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
             WHERE cm.cohortid = :cohortid1 ORDER BY u.lastname";

    return $DB->get_records_sql($fields . $sql, $params);
}

function cohort_get_name($cohortid) {
    global $DB;
    return $DB->get_record_sql("select name from {cohort} where id=:id", array('id'=>$cohortid))->name;
}

class multireset_password_form extends moodleform {

    function definition() {
        global $USER, $CFG, $COURSE, $context;

        $mform =& $this->_form;

        $cohorts_result = cohort_get_cohorts($context->id, 0, 0);
        $cohorts_options = array();
        foreach($cohorts_result['cohorts'] as $id => $cohort) {
            $cohorts_options[$id] = $cohort->name;
        }

        $cohort_select = $mform->addElement('select', 'cohorts',
            'Cohorts',
            $cohorts_options,
            array('multiple'=>'multiple', 'size'=>16));
        $cohort_select->setMultiple(true);


        $this->add_action_buttons();

    }
}

$multireset_form = new multireset_password_form();

echo $OUTPUT->header();

require_capability('moodle/user:create', $context);

if ($cohorts_to_reset = $multireset_form->get_data()) {
    foreach ($cohorts_to_reset->cohorts as $cohortid) {
        echo $OUTPUT->heading(cohort_get_name($cohortid));
        $user_table = new html_table();
        $user_table->head = array('Last Name', 'First Name', 'Email', 'Login', 'Password');
        $html_data = array();
        foreach(cohort_get_members($cohortid) as $userid=>$userinfo) {
            $newpass = GetPassword();
            $authplugin = get_auth_plugin($userinfo->auth);
            $authplugin->user_update_password($userinfo, $newpass);
            array_push($html_data, array($userinfo->lastname,
                                         $userinfo->firstname,
                                         $userinfo->email,
                                         $userinfo->username,
                                         $newpass));
        }
        $user_table->data = $html_data;

        echo '<center>';
        echo html_writer::table($user_table);
        echo '</center>';
    }
} else {
    $multireset_form->display();
}

echo $OUTPUT->footer();



